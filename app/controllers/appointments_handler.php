<?php

include('../middleware/admin.php');
require_once('../config/config.php');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$limit  = 10;

function logActivity($conn, $type, $desc, $refId = null, $refType = null)
{
    $stmt = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)"
    );
    $stmt->bind_param('ssis', $type, $desc, $refId, $refType);
    $stmt->execute();
}

function generateCode($conn, $date = null)
{
    $max = (int) $conn->query("SELECT MAX(id) FROM appointments")->fetch_row()[0];
    return 'APP-' . date('Y') . '-' . str_pad($max + 1, 4, '0', STR_PAD_LEFT);
}
switch ($action) {

    case 'get_linked_record':
        $apptId = (int)($_GET['apptId'] ?? 0);

        // First try: directly linked by appointmentId
        $row = $conn->query("
        SELECT m.id, m.recordCode, m.recordType, m.diagnosis,
               m.icdCode, m.status, m.createdAt
        FROM medicalRecords m
        WHERE m.appointmentId = $apptId
        LIMIT 1
    ")->fetch_assoc();

        // Fallback: find by patientId + date match, or just latest record for that patient
        if (!$row) {
            $row = $conn->query("
            SELECT m.id, m.recordCode, m.recordType, m.diagnosis,
                   m.icdCode, m.status, m.createdAt
            FROM medicalRecords m
            JOIN appointments a ON a.patientId = m.patientId
            WHERE a.id = $apptId
            ORDER BY ABS(DATEDIFF(DATE(m.createdAt), a.appointmentDate)) ASC, m.createdAt DESC
            LIMIT 1
        ")->fetch_assoc();
        }

        echo json_encode([
            'success' => true,
            'data'    => $row ?: null
        ]);
        break;

    case 'get_followups':
        $rows = $conn->query("
        SELECT 
            mr.id AS recordId,
            mr.recordCode,
            mr.followUpDate,
            mr.diagnosis,
            CONCAT(p.firstName,' ',p.lastName) AS patientName,
            p.id AS patientId,
            p.patientCode,
            CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName,
            d.id AS doctorId
        FROM medicalRecords mr
        JOIN patients p ON p.id = mr.patientId
        JOIN doctors d ON d.id = mr.doctorId
        WHERE mr.followUpDate IS NOT NULL
          AND mr.followUpDate >= CURDATE()
          AND NOT EXISTS (
              SELECT 1 FROM appointments a
              WHERE a.patientId = mr.patientId
                AND a.appointmentDate = mr.followUpDate
                AND a.status NOT IN ('Cancelled')
          )
        ORDER BY mr.followUpDate ASC
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'list':
        $date   = $conn->real_escape_string($_GET['date']   ?? '');
        $search = '%' . $conn->real_escape_string($_GET['search'] ?? '') . '%';
        $status = $conn->real_escape_string($_GET['status'] ?? '');
        $doctor = (int) ($_GET['doctor'] ?? 0);
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1";
        if ($date)            $where .= " AND a.appointmentDate = '$date'";
        if ($search !== '%%') $where .= " AND (CONCAT(COALESCE(p.firstName,''),' ',COALESCE(p.lastName,'')) LIKE '$search' OR CONCAT('Dr. ',COALESCE(d.firstName,''),' ',COALESCE(d.lastName,'')) LIKE '$search' OR a.appointmentCode LIKE '$search')";
        if ($status)          $where .= " AND a.status = '$status'";
        if ($doctor)          $where .= " AND a.doctorId = $doctor";

        $countRes = $conn->query("
            SELECT COUNT(*) FROM appointments a
            LEFT JOIN patients p ON p.id = a.patientId
            LEFT JOIN doctors  d ON d.id = a.doctorId
            $where
        ");
        $total = $countRes ? (int) $countRes->fetch_row()[0] : 0;

        $rows = $conn->query("
    SELECT 
        a.id, a.appointmentCode, a.appointmentDate, a.appointmentTime,
        a.channel, a.status, a.remarks,
        a.patientId, a.doctorId,
        CONCAT(COALESCE(p.firstName,''),' ',COALESCE(p.lastName,'')) AS patientName,
        p.photoUrl AS patPhoto,
        CONCAT('Dr. ',COALESCE(d.firstName,''),' ',COALESCE(d.lastName,'')) AS doctorName,
        d.specialization,
        NULL AS followUpDate,
NULL AS followUpCode,
0 AS isFollowUp

    FROM appointments a
    LEFT JOIN patients p ON p.id = a.patientId
    LEFT JOIN doctors  d ON d.id = a.doctorId
    $where

    UNION ALL

    SELECT
        a.id, a.appointmentCode, f.followUpDate AS appointmentDate, a.appointmentTime,
        'Follow-up' AS channel, f.status, f.reason AS remarks,
        a.patientId, a.doctorId,
        CONCAT(COALESCE(p.firstName,''),' ',COALESCE(p.lastName,'')) AS patientName,
        p.photoUrl AS patPhoto,
        CONCAT('Dr. ',COALESCE(d.firstName,''),' ',COALESCE(d.lastName,'')) AS doctorName,
        d.specialization,
        f.followUpDate AS followUpDate,
        f.followUpCode AS followUpCode,
        1 AS isFollowUp

    FROM followups f
    JOIN appointments a ON a.id = f.appointmentId
    LEFT JOIN patients p ON p.id = f.patientId
    LEFT JOIN doctors  d ON d.id = a.doctorId
    $where

    ORDER BY 
        CASE WHEN appointmentDate = CURDATE() AND status = 'Pending' THEN 0 ELSE 1 END ASC,
        appointmentDate DESC,
        appointmentTime ASC
    LIMIT $limit OFFSET $offset
")->fetch_all(MYSQLI_ASSOC);

        $statWhere = $date ? "WHERE appointmentDate = '$date'" : "WHERE 1=1";
        $statRes   = $conn->query("
            SELECT status, COUNT(*) AS cnt FROM appointments
            $statWhere GROUP BY status
        ")->fetch_all(MYSQLI_ASSOC);

        $stats       = [];
        $total_today = 0;
        foreach ($statRes as $s) {
            $stats[$s['status']] = (int) $s['cnt'];
            $total_today        += $s['cnt'];
        }
        $stats['total'] = $total_today;

        echo json_encode([
            'success' => true,
            'rows'    => $rows,
            'stats'   => $stats,
            'total'   => $total,
            'page'    => $page,
            'limit'   => $limit,
        ]);
        break;

    case 'get':
        $id  = (int) ($_GET['id'] ?? 0);
        $row = $conn->query("
    SELECT a.*,
           CONCAT(COALESCE(p.firstName,''),' ',COALESCE(p.lastName,'')) AS patientName,
           CONCAT('Dr. ',COALESCE(d.firstName,''),' ',COALESCE(d.lastName,'')) AS doctorName,
           d.specialization,
           (SELECT mr.followUpDate FROM medicalRecords mr
 WHERE mr.patientId = a.patientId
 AND mr.appointmentId = a.id
 ORDER BY mr.createdAt DESC LIMIT 1) AS followUpDate
    FROM appointments a
    LEFT JOIN patients p ON p.id = a.patientId
    LEFT JOIN doctors  d ON d.id = a.doctorId
    WHERE a.id = $id
")->fetch_assoc();

        echo json_encode(['success' => !!$row, 'data' => $row]);
        break;

    case 'add':
        $body            = json_decode(file_get_contents('php://input'), true);
        $doctorId        = (int) ($body['doctorId'] ?? 0);
        $appointmentDate = $conn->real_escape_string($body['appointmentDate'] ?? '');
        $appointmentTime = $conn->real_escape_string($body['appointmentTime'] ?? '');
        $channel         = $conn->real_escape_string($body['channel'] ?? 'Walk-in');
        $status          = $conn->real_escape_string($body['status']  ?? 'Pending');
        $remarks         = $conn->real_escape_string($body['remarks'] ?? '');
        $code = generateCode($conn, $appointmentDate);

        $patientId = (int) ($body['patientId'] ?? 0);
        if (!$patientId && !empty($body['patientName'])) {
            $fullName  = $conn->real_escape_string(trim($body['patientName']));
            $parts     = explode(' ', $fullName, 2);
            $firstName = $conn->real_escape_string($parts[0]);
            $lastName  = $conn->real_escape_string($parts[1] ?? $parts[0]);
            $contact   = $conn->real_escape_string(trim($body['patientContact'] ?? ''));
            $email     = $conn->real_escape_string(trim($body['patientEmail']   ?? ''));
            $gender    = in_array($body['patientGender'] ?? '', ['Male', 'Female', 'Other'])
                ? $body['patientGender'] : 'Other';
            $max       = (int) $conn->query("SELECT MAX(id) FROM patients")->fetch_row()[0];
            $pCode     = 'PAT-' . date('Y') . '-' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);
            $conn->query("
                INSERT INTO patients (patientCode, firstName, lastName, gender, contactNumber, emailAddress, status, patientCondition)
                VALUES (
                    '$pCode','$firstName','$lastName','$gender',
                    " . ($contact ? "'$contact'" : 'NULL') . ",
                    " . ($email   ? "'$email'"   : 'NULL') . ",
                    'Active','Stable'
                )
            ");
            $patientId = $conn->insert_id;
        }

        if (!$patientId || !$doctorId || !$appointmentDate || !$appointmentTime) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            break;
        }

        $stmt = $conn->prepare(
            "INSERT INTO appointments (appointmentCode, patientId, doctorId, appointmentDate, appointmentTime, channel, status, remarks)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param('siisssss', $code, $patientId, $doctorId, $appointmentDate, $appointmentTime, $channel, $status, $remarks);

        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            $pat   = $conn->query("SELECT CONCAT(firstName,' ',lastName) FROM patients WHERE id=$patientId")->fetch_row()[0] ?? '';
            logActivity($conn, 'appointment', "New appointment $code booked for $pat", $newId, 'Appointment');
            echo json_encode(['success' => true, 'id' => $newId, 'code' => $code]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    case 'edit':
        $body            = json_decode(file_get_contents('php://input'), true);
        $id              = (int) ($body['id']              ?? 0);
        $patientId       = (int) ($body['patientId']       ?? 0);
        $doctorId        = (int) ($body['doctorId']        ?? 0);
        $appointmentDate = $conn->real_escape_string($body['appointmentDate'] ?? '');
        $appointmentTime = $conn->real_escape_string($body['appointmentTime'] ?? '');
        $channel         = $conn->real_escape_string($body['channel']  ?? 'Walk-in');
        $status          = $conn->real_escape_string($body['status']   ?? 'Pending');
        $remarks         = $conn->real_escape_string($body['remarks']  ?? '');

        if (!$id) {
            echo json_encode(['success' => false]);
            break;
        }

        $stmt = $conn->prepare(
            "UPDATE appointments SET patientId=?, doctorId=?, appointmentDate=?, appointmentTime=?,
             channel=?, status=?, remarks=?, updatedAt=NOW() WHERE id=?"
        );
        $stmt->bind_param('iisssssi', $patientId, $doctorId, $appointmentDate, $appointmentTime, $channel, $status, $remarks, $id);

        if ($stmt->execute()) {
            $appt = $conn->query("SELECT appointmentCode FROM appointments WHERE id=$id")->fetch_row()[0] ?? '';
            logActivity($conn, 'appointment_update', "Appointment $appt updated (status: $status)", $id, 'Appointment');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    case 'cancel':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false]);
            break;
        }
        $conn->query("UPDATE appointments SET status='Cancelled', updatedAt=NOW() WHERE id=$id");
        $appt = $conn->query("SELECT appointmentCode FROM appointments WHERE id=$id")->fetch_row()[0] ?? '';
        logActivity($conn, 'cancel', "Appointment $appt cancelled", $id, 'Appointment');
        echo json_encode(['success' => true]);
        break;

    case 'update_status':
        $id      = (int) ($_POST['id']     ?? 0);
        $status  = $conn->real_escape_string($_POST['status'] ?? '');
        $allowed = ['Pending', 'In Progress', 'Completed', 'Cancelled'];

        if (!$id || !in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            break;
        }

        $conn->query("UPDATE appointments SET status='$status', updatedAt=NOW() WHERE id=$id");
        $appt = $conn->query("SELECT appointmentCode FROM appointments WHERE id=$id")->fetch_row()[0] ?? '';
        logActivity($conn, 'appointment_update', "Appointment $appt status changed to $status", $id, 'Appointment');

        $statRes = $conn->query("
            SELECT status, COUNT(*) AS cnt FROM appointments GROUP BY status
        ")->fetch_all(MYSQLI_ASSOC);

        $stats = [];
        $total = 0;
        foreach ($statRes as $s) {
            $stats[$s['status']] = (int) $s['cnt'];
            $total += $s['cnt'];
        }
        $stats['total'] = $total;

        echo json_encode(['success' => true, 'stats' => $stats]);
        break;

    case 'get_doctors':
        $rows = $conn->query("
            SELECT id, CONCAT('Dr. ', firstName, ' ', lastName) AS name, specialization
            FROM doctors WHERE employmentStatus = 'Active'
            ORDER BY firstName
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_patients':
        $q    = '%' . $conn->real_escape_string($_GET['q'] ?? '') . '%';
        $rows = $conn->query("
            SELECT id, CONCAT(firstName,' ',lastName) AS name, patientCode
            FROM patients
            WHERE status != 'Inactive'
              AND (firstName LIKE '$q' OR lastName LIKE '$q' OR patientCode LIKE '$q')
            ORDER BY firstName LIMIT 100
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_doctor_schedule':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        if (!$doctorId) {
            echo json_encode(['success' => false]);
            break;
        }
        $rows = $conn->query("
            SELECT dayOfWeek, shiftStart, shiftEnd
            FROM doctorSchedules
            WHERE doctorId = $doctorId
            ORDER BY FIELD(dayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_slots':
        $doctorId = (int) ($_GET['doctorId'] ?? 0);
        $date     = $conn->real_escape_string($_GET['date'] ?? '');

        if (!$doctorId || !$date) {
            echo json_encode(['success' => true, 'slots' => []]);
            break;
        }

        $dow      = date('l', strtotime($date));
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

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
