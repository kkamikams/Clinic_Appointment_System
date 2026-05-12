<?php

include('../middleware/user.php');
require_once('../config/config.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/../models/patientModel.php');

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? '';

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
            $email    = $conn->real_escape_string(trim($body['email']   ?? ''));
            $contact  = $conn->real_escape_string(trim($body['contact'] ?? ''));
            $fullName = trim($body['patientName'] ?? 'Unknown');

            $existing = null;
            if ($email) $existing = $conn->query("SELECT id, CONCAT(firstName,' ',lastName) AS name FROM patients WHERE emailAddress='$email' AND status!='Inactive' LIMIT 1")->fetch_assoc();

            // Only reuse existing patient if name also matches (same person)
            if ($existing && strtolower($existing['name']) === strtolower($fullName)) {
                $patientId = (int)$existing['id'];
            } else {

                $patientModel = new patientModel($conn);
                $patientId    = $patientModel->createFromBooking([
                    'firstName'  => trim($body['firstName']  ?? ''),
                    'middleName' => trim($body['middleName'] ?? ''),
                    'lastName'   => trim($body['lastName']   ?? ''),
                    'gender'     => $body['gender']       ?? 'Other',
                    'dob'        => !empty($body['dateOfBirth']) ? sanitizeDate($body['dateOfBirth']) : null,
                    'contact'    => $contact,
                    'email'      => $email,
                ]);
                $fullName = trim(($body['firstName'] ?? '') . ' ' . ($body['middleName'] ?? '') . ' ' . ($body['lastName'] ?? ''));
                logActivity($conn, 'patient', "New patient registered: $fullName", $patientId, 'Patient');
            }
        }

        if (!$patientId) {
            echo json_encode(['success' => false, 'message' => 'Could not resolve patient.']);
            break;
        }

        $code = generateAppointmentCode($conn);
        $sessionUserId = $_SESSION['authUser']['user_id'] ?? 0;
        $conn->query("
    INSERT INTO appointments (appointmentCode,patientId,doctorId,appointmentDate,appointmentTime,channel,status,remarks,bookedByUserId)
    VALUES ('$code',$patientId,$doctorId,'$appointmentDate','$appointmentTime','$channel','Pending','$remarks',$sessionUserId)
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
    WHERE a.id=$id
      AND (a.bookedByUserId=$userId OR p.emailAddress='$userEmail')
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

        $rows = $conn->query("
        SELECT a.id, a.appointmentCode, a.appointmentDate, a.appointmentTime,
               a.channel, a.status, a.remarks,
               CONCAT(p.firstName, ' ', p.lastName) AS patientName,
               CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
               d.specialization, d.department
        FROM appointments a
        JOIN patients p ON p.id = a.patientId
        JOIN doctors  d ON d.id = a.doctorId
        WHERE a.bookedByUserId = $userId
           OR p.emailAddress = '$userEmail'
        ORDER BY a.appointmentDate DESC, a.appointmentTime DESC
    ")->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['success' => true, 'rows' => $rows]);
        break;

    case 'get_doctor_schedule':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        if (!$doctorId) {
            echo json_encode(['success' => false]);
            break;
        }
        echo json_encode(['success' => true, 'data' => getDoctorSchedule($conn, $doctorId)]);
        break;

    case 'get_slots':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        $date     = $conn->real_escape_string($_GET['date'] ?? '');
        if (!$doctorId || !$date) {
            echo json_encode(['success' => true, 'slots' => []]);
            break;
        }
        $slots = getAvailableSlots($conn, $doctorId, $date);
        echo json_encode($slots ? ['success' => true, 'slots' => $slots]
            : ['success' => true, 'slots' => [], 'message' => 'Doctor not available this day.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
