<?php

include('../middleware/user.php');
require_once('../config/config.php');

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? '';

function logActivity($conn, $type, $desc, $refId = null, $refType = null)
{
    $stmt = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)"
    );
    $stmt->bind_param('ssis', $type, $desc, $refId, $refType);
    $stmt->execute();
}

function sanitizeDate(string $val): string
{
    $d = DateTime::createFromFormat('Y-m-d', $val);
    return $d ? $d->format('Y-m-d') : date('Y-m-d');
}

function sanitizeTime(string $val): string
{
    $t = DateTime::createFromFormat('H:i', substr($val, 0, 5));
    return $t ? $t->format('H:i:s') : '00:00:00';
}

function generateCode($conn, $date): string
{
    $prefix = 'APT-' . str_replace('-', '', $date) . '-';
    $count  = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentCode LIKE '{$prefix}%'")->fetch_row()[0];
    return $prefix . str_pad((int)$count + 1, 4, '0', STR_PAD_LEFT);
}

switch ($action) {


    case 'get_departments':
        $rows = $conn->query("
            SELECT DISTINCT department FROM doctors
            WHERE employmentStatus='Active' AND department IS NOT NULL AND department!=''
            ORDER BY department
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => array_column($rows, 'department')]);
        break;


    case 'get_doctors':
        $dept = $conn->real_escape_string($_GET['department'] ?? '');
        $where = $dept ? "AND department='$dept'" : '';
        $rows = $conn->query("
            SELECT id, CONCAT(firstName,' ',lastName) AS name, specialization, department, patientCapacity, photoUrl
            FROM doctors WHERE employmentStatus='Active' $where
            ORDER BY lastName, firstName
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;


    case 'get_slots':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        $date     = $conn->real_escape_string($_GET['date'] ?? '');

        if (!$doctorId || !$date) {
            echo json_encode(['success' => true, 'slots' => []]);
            break;
        }

        $dow = date('l', strtotime($date)); // e.g. "Monday"
        $schedule = $conn->query("
            SELECT shiftStart, shiftEnd FROM doctorSchedules
            WHERE doctorId=$doctorId AND dayOfWeek='$dow'
            LIMIT 1
        ")->fetch_assoc();

        if (!$schedule) {
            echo json_encode(['success' => true, 'slots' => [], 'message' => 'Doctor not available this day.']);
            break;
        }

        $booked = $conn->query("
            SELECT TIME_FORMAT(appointmentTime,'%H:%i') AS t
            FROM appointments
            WHERE doctorId=$doctorId AND appointmentDate='$date' AND status NOT IN ('Cancelled')
        ")->fetch_all(MYSQLI_ASSOC);
        $bookedTimes = array_column($booked, 't');


        $slots    = [];
        $start    = strtotime($date . ' ' . $schedule['shiftStart']);
        $end      = strtotime($date . ' ' . $schedule['shiftEnd']);
        $interval = 30 * 60;

        for ($t = $start; $t < $end; $t += $interval) {
            $hhmm    = date('H:i', $t);
            $slots[] = [
                'value'     => $hhmm,
                'label'     => date('g:i A', $t),
                'available' => !in_array($hhmm, $bookedTimes),
            ];
        }

        echo json_encode(['success' => true, 'slots' => $slots]);
        break;

        $q = '%' . $conn->real_escape_string($_GET['q'] ?? '') . '%';
        if (strlen(trim($_GET['q'] ?? '')) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            break;
        }
        $rows = $conn->query("
            SELECT id, CONCAT(firstName,' ',lastName) AS name, patientCode, contactNumber, emailAddress, dateOfBirth
            FROM patients WHERE status='Active'
            AND (CONCAT(firstName,' ',lastName) LIKE '$q' OR contactNumber LIKE '$q' OR emailAddress LIKE '$q' OR patientCode LIKE '$q')
            ORDER BY lastName, firstName LIMIT 10
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'book':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        // Always try to use logged-in user's email to find existing patient
        $sessionUserId = $_SESSION['authUser']['user_id'] ?? 0;
        $sessionEmail  = '';
        if ($sessionUserId) {
            $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id=? LIMIT 1");
            $uStmt->bind_param('i', $sessionUserId);
            $uStmt->execute();
            $sessionEmail = $uStmt->get_result()->fetch_assoc()['emailAddress'] ?? '';
        }

        // Override email with session email if not provided
        if (!empty($sessionEmail)) {
            $body['email'] = $sessionEmail;
        }

        // Also check if patient already exists for this user
        $patientId = (int)($body['patientId'] ?? 0);
        if (!$patientId && $sessionEmail) {
            $esc = $conn->real_escape_string($sessionEmail);
            $existing = $conn->query("SELECT id FROM patients WHERE emailAddress='$esc' AND status!='Inactive' LIMIT 1")->fetch_assoc();
            if ($existing) {
                $patientId = (int)$existing['id'];
                $body['patientId'] = $patientId;
            }
        }

    case 'book':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorId        = (int)($body['doctorId']        ?? 0);
        $appointmentDate = sanitizeDate($body['appointmentDate'] ?? '');
        $appointmentTime = sanitizeTime($body['appointmentTime'] ?? '');
        $channel         = in_array($body['channel'] ?? '', ['Walk-in', 'Online', 'Phone', 'Referral']) ? $body['channel'] : 'Online';
        $remarks         = $conn->real_escape_string($body['remarks'] ?? '');

        if (!$doctorId || !$appointmentDate || !$appointmentTime || empty($body['patientName'])) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
            break;
        }

        if ($appointmentDate < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Appointment date cannot be in the past.']);
            break;
        }


        $doc = $conn->query("SELECT id FROM doctors WHERE id=$doctorId AND employmentStatus='Active'")->fetch_assoc();
        if (!$doc) {
            echo json_encode(['success' => false, 'message' => 'Doctor not available.']);
            break;
        }

        $taken = $conn->query("
            SELECT id FROM appointments
            WHERE doctorId=$doctorId AND appointmentDate='$appointmentDate' AND appointmentTime='$appointmentTime'
            AND status NOT IN ('Cancelled')
        ")->fetch_assoc();
        if ($taken) {
            echo json_encode(['success' => false, 'message' => 'This slot is already booked. Please choose another.']);
            break;
        }

        $patientId = (int)($body['patientId'] ?? 0);
        if (!$patientId) {
            $email   = $conn->real_escape_string(trim($body['email']   ?? ''));
            $contact = $conn->real_escape_string(trim($body['contact'] ?? ''));

            $existing = null;
            if ($email) $existing = $conn->query("SELECT id FROM patients WHERE emailAddress='$email' AND status!='Inactive' LIMIT 1")->fetch_assoc();
            if (!$existing && $contact) $existing = $conn->query("SELECT id FROM patients WHERE contactNumber='$contact' AND status!='Inactive' LIMIT 1")->fetch_assoc();
            if ($existing) {
                $patientId = (int)$existing['id'];
            } else {

                $fullName  = $conn->real_escape_string(trim($body['patientName'] ?? 'Unknown'));
                $parts     = explode(' ', $fullName, 2);
                $firstName = $conn->real_escape_string($parts[0]);
                $lastName  = $conn->real_escape_string($parts[1] ?? $parts[0]);
                $gender    = in_array($body['gender'] ?? '', ['Male', 'Female', 'Other']) ? $body['gender'] : 'Other';
                $dob       = !empty($body['dateOfBirth']) ? "'" . sanitizeDate($body['dateOfBirth']) . "'" : 'NULL';
                $count     = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0];
                $pCode     = 'P-' . date('Ymd') . '-' . str_pad((int)$count + 1, 4, '0', STR_PAD_LEFT);

                $conn->query("
                    INSERT INTO patients (patientCode,firstName,lastName,gender,dateOfBirth,contactNumber,emailAddress,status,patientCondition)
                    VALUES ('$pCode','$firstName','$lastName','$gender',$dob," . ($contact ? "'$contact'" : 'NULL') . "," . ($email ? "'$email'" : 'NULL') . ",'Active','Stable')
                ");
                $patientId = $conn->insert_id;
                logActivity($conn, 'patient', "New patient registered: $fullName ($pCode)", $patientId, 'Patient');
            }
        }

        if (!$patientId) {
            echo json_encode(['success' => false, 'message' => 'Could not resolve patient.']);
            break;
        }

        $code = generateCode($conn, $appointmentDate);
        $conn->query("
            INSERT INTO appointments (appointmentCode,patientId,doctorId,appointmentDate,appointmentTime,channel,status,remarks)
            VALUES ('$code',$patientId,$doctorId,'$appointmentDate','$appointmentTime','$channel','Pending','$remarks')
        ");
        $newId = $conn->insert_id;

        $patName = $conn->query("SELECT CONCAT(firstName,' ',lastName) FROM patients WHERE id=$patientId")->fetch_row()[0] ?? '';
        logActivity($conn, 'appointment', "Appointment $code booked for $patName via booking form.", $newId, 'Appointment');

        echo json_encode(['success' => true, 'appointmentCode' => $code, 'appointmentId' => $newId, 'message' => 'Appointment booked!']);
        break;


    case 'cancel_appointment':
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false]);
            break;
        }


        $userId = $_SESSION['authUser']['user_id'] ?? 0;
        $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
        $uStmt->bind_param('i', $userId);
        $uStmt->execute();
        $userEmail = $conn->real_escape_string($uStmt->get_result()->fetch_assoc()['emailAddress'] ?? '');
        $check = $conn->query("
            SELECT a.id, a.appointmentCode, a.appointmentDate, a.status
            FROM appointments a
            JOIN patients p ON p.id = a.patientId
            WHERE a.id=$id AND p.emailAddress='$userEmail'
        ")->fetch_assoc();

        if (!$check) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
            break;
        }
        if ($check['status'] === 'Cancelled') {
            echo json_encode(['success' => false, 'message' => 'Already cancelled.']);
            break;
        }
        if ($check['appointmentDate'] < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel past appointments.']);
            break;
        }

        $conn->query("UPDATE appointments SET status='Cancelled', updatedAt=NOW() WHERE id=$id");
        logActivity($conn, 'cancel', "Appointment {$check['appointmentCode']} cancelled by patient.", $id, 'Appointment');
        echo json_encode(['success' => true]);
        break;

    case 'my_appointments':
        $userId = $_SESSION['authUser']['user_id'] ?? 0;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not logged in.']);
            break;
        }

        $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
        $uStmt->bind_param('i', $userId);
        $uStmt->execute();
        $userEmail = $conn->real_escape_string(
            $uStmt->get_result()->fetch_assoc()['emailAddress'] ?? ''
        );

        if (!$userEmail) {
            echo json_encode(['success' => true, 'rows' => []]);
            break;
        }

        $rows = $conn->query("
        SELECT a.id, a.appointmentCode, a.appointmentDate, a.appointmentTime,
               a.channel, a.status, a.remarks,
               CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
               d.specialization, d.department
        FROM appointments a
        JOIN patients p ON p.id = a.patientId
        JOIN doctors  d ON d.id = a.doctorId
        WHERE p.emailAddress = '$userEmail'
        ORDER BY a.appointmentDate DESC, a.appointmentTime DESC
    ")->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['success' => true, 'rows' => $rows]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
