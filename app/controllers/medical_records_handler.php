<?php

include('../middleware/admin.php');
require_once('../config/config.php');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$changedBy = trim(($_SESSION['authUser']['firstName'] ?? '') . ' ' . ($_SESSION['authUser']['lastName'] ?? 'Admin'));

function logActivity($conn, $type, $desc, $refId = null, $refType = null)
{
    $stmt = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)"
    );
    $stmt->bind_param('ssis', $type, $desc, $refId, $refType);
    $stmt->execute();
}

function getStats($conn)
{
    $today = date('Y-m-d');
    $ym    = date('Y-m');

    $total = (int) $conn->query("SELECT COUNT(*) FROM medicalRecords")->fetch_row()[0];
    $today_count = (int) $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE DATE(updatedAt)='$today'")->fetch_row()[0];
    $labPending  = (int) $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE recordType='Lab Result' AND status='Draft'")->fetch_row()[0];
    $prescriptions = (int) $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE recordType='Prescription' AND DATE_FORMAT(createdAt,'%Y-%m')='$ym'")->fetch_row()[0];

    return [
        'total'         => $total,
        'today'         => $today_count,
        'labPending'    => $labPending,
        'prescriptions' => $prescriptions,
    ];
}

switch ($action) {

    case 'get_patients':
        $q = '%' . $conn->real_escape_string(trim($_GET['q'] ?? '')) . '%';
        $rows = $conn->query("
            SELECT id,
                   patientCode,
                   TRIM(CONCAT(firstName,' ',COALESCE(NULLIF(middleName,''),''),' ',lastName)) AS name,
                   contactNumber AS contact,
                   dateOfBirth   AS dob
            FROM patients
            WHERE status != 'Inactive'
              AND (firstName   LIKE '$q'
                OR lastName    LIKE '$q'
                OR CONCAT(firstName,' ',lastName) LIKE '$q'
                OR patientCode LIKE '$q')
            ORDER BY firstName
            LIMIT 15
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_doctors':
        $q = '%' . $conn->real_escape_string(trim($_GET['q'] ?? '')) . '%';
        $rows = $conn->query("
            SELECT id,
                   doctorCode,
                   TRIM(CONCAT(firstName,' ',COALESCE(NULLIF(middleName,''),''),' ',lastName)) AS name,
                   specialization
            FROM doctors
            WHERE employmentStatus = 'Active'
              AND (firstName       LIKE '$q'
                OR lastName        LIKE '$q'
                OR CONCAT(firstName,' ',lastName) LIKE '$q'
                OR specialization  LIKE '$q'
                OR doctorCode      LIKE '$q')
            ORDER BY firstName
            LIMIT 15
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_patient_doctor':
        $patientId = (int)($_GET['patientId'] ?? 0);
        $row = $conn->query("
        SELECT a.doctorId,
               TRIM(CONCAT(d.firstName,' ',COALESCE(NULLIF(d.middleName,''),''),' ',d.lastName)) AS doctorName,
               d.specialization
        FROM appointments a
        JOIN doctors d ON d.id = a.doctorId
        WHERE a.patientId = $patientId
          AND a.status != 'Cancelled'
        ORDER BY a.appointmentDate DESC
        LIMIT 1
    ")->fetch_assoc();

        echo json_encode([
            'success' => (bool)$row,
            'data'    => $row ?: null
        ]);
        break;

    case 'get_appointments':
        $patientId = (int)($_GET['patientId'] ?? 0);
        $q = '%' . $conn->real_escape_string(trim($_GET['q'] ?? '')) . '%';
        $patFilter = $patientId ? "AND a.patientId = $patientId" : '';
        $rows = $conn->query("
            SELECT a.id,
                   a.appointmentCode,
                   a.appointmentDate,
                   a.remarks,
                   a.doctorId,
                   TRIM(CONCAT(d.firstName,' ',COALESCE(NULLIF(d.middleName,''),''),' ',d.lastName)) AS doctorName,
                   d.specialization
            FROM appointments a
            JOIN doctors d ON d.id = a.doctorId
            WHERE a.status != 'Cancelled'
              $patFilter
              AND (a.appointmentCode LIKE '$q'
                OR CONCAT(d.firstName,' ',d.lastName) LIKE '$q')
            ORDER BY a.appointmentDate DESC
            LIMIT 15
        ")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_appointment_details':
        $apptId = (int)($_GET['apptId'] ?? 0);
        $row = $conn->query("
    SELECT a.id,
           a.appointmentCode,
           a.remarks,
           a.appointmentDate,
           a.doctorId,
           TRIM(CONCAT(d.firstName,' ',COALESCE(NULLIF(d.middleName,''),''),' ',d.lastName)) AS doctorName,
           d.specialization,
           a.patientId,
           TRIM(CONCAT(p.firstName,' ',p.lastName)) AS patientName,
           p.patientCode
    FROM appointments a
    JOIN doctors d ON d.id = a.doctorId
    LEFT JOIN patients p ON p.id = a.patientId
    WHERE a.id = $apptId
    LIMIT 1
")->fetch_assoc();
        echo json_encode([
            'success' => (bool)$row,
            'data'    => $row ?: null
        ]);
        break;

    case 'list':
        $searchRaw = trim($_GET['search'] ?? '');
        $type      = trim($_GET['type']   ?? '');
        $status    = trim($_GET['status'] ?? '');
        $followUpDate = ($body['followUpDate'] ?? '') ?: null;
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $limit     = 15;
        $offset    = ($page - 1) * $limit;

        $where = [];

        if ($searchRaw !== '') {
            $s = '%' . $conn->real_escape_string($searchRaw) . '%';
            $where[] = "(CONCAT(p.firstName,' ',p.lastName) LIKE '$s'
                      OR m.recordCode LIKE '$s'
                      OR m.diagnosis  LIKE '$s')";
        }
        if ($type !== '') {
            $t = $conn->real_escape_string($type);
            $where[] = "m.recordType = '$t'";
        }
        if ($status !== '') {
            $st = $conn->real_escape_string($status);
            $where[] = "m.status = '$st'";
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $conn->query("
            SELECT COUNT(*)
            FROM medicalRecords m
            JOIN patients p ON p.id = m.patientId
            $whereSQL
        ")->fetch_row()[0];

        $rows = $conn->query("
            SELECT m.id, m.recordCode, m.recordType, m.diagnosis, m.icdCode,
                   m.status, m.createdAt,
                   p.patientCode,
                   TRIM(CONCAT(p.firstName,' ',p.lastName))  AS patientName,
                   p.photoUrl                                 AS patPhoto,
                   TRIM(CONCAT(d.firstName,' ',d.lastName))  AS doctorName,
                   d.specialization
            FROM medicalRecords m
            JOIN patients p ON p.id = m.patientId
            JOIN doctors  d ON d.id = m.doctorId
            $whereSQL
            ORDER BY COALESCE(m.updatedAt, m.createdAt) DESC
            LIMIT $limit OFFSET $offset
        ")->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            'success' => true,
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'limit'   => $limit,
            'stats'   => getStats($conn),
        ]);
        break;

    case 'get':
        $id  = (int)($_GET['id'] ?? 0);
        $row = $conn->query("
            SELECT m.*,
                   p.patientCode,
                   TRIM(CONCAT(p.firstName,' ',p.lastName))  AS patientName,
                   TRIM(CONCAT(d.firstName,' ',d.lastName))  AS doctorName,
                   d.specialization,
                   a.appointmentCode,
                   a.appointmentDate
            FROM medicalRecords m
            JOIN patients  p ON p.id  = m.patientId
            JOIN doctors   d ON d.id  = m.doctorId
            LEFT JOIN appointments a ON a.id = m.appointmentId
            WHERE m.id = $id
            LIMIT 1
        ")->fetch_assoc();

        if ($row) {
            $rid = (int)$row['id'];
            $auditLog = [];
            $logs = $conn->query("
                SELECT action, changedBy, changedAt, oldValue, newValue
                FROM medicalRecordAudit
                WHERE recordId = $rid
                ORDER BY changedAt DESC
            ");
            if ($logs) {
                while ($l = $logs->fetch_assoc()) {
                    $auditLog[] = [
                        'action' => $l['action'],
                        'by'     => $l['changedBy'] ?? 'Admin',
                        'at'     => date('M j, g:i A', strtotime($l['changedAt'])),
                        'type'   => $l['action'] === 'Created' ? 'create'
                            : ($l['action'] === 'Status Changed' ? 'status' : 'edit'),
                        'from'   => $l['oldValue'] ?? '',
                        'to'     => $l['newValue'] ?? '',
                    ];
                }
            }
            $row['auditLog'] = $auditLog;
        }

        echo json_encode(
            $row
                ? ['success' => true,  'data' => $row]
                : ['success' => false, 'message' => 'Record not found']
        );
        break;
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $body = json_decode(file_get_contents('php://input'), true);

        $lastCode = $conn->query("
    SELECT MAX(CAST(SUBSTRING_INDEX(recordCode, '-', -1) AS UNSIGNED)) 
    FROM medicalRecords
")->fetch_row()[0];
        $code = 'REC-' . date('Y') . '-' . str_pad((int)$lastCode + 1, 4, '0', STR_PAD_LEFT);

        $patientId     = (int)($body['patientId']     ?? 0) ?: 'NULL';
        $doctorId      = (int)($body['doctorId']      ?? 0) ?: 'NULL';
        $appointmentId = (int)($body['appointmentId'] ?? 0) ?: 'NULL';
        $recordType    = $conn->real_escape_string($body['recordType']   ?? 'Consultation');
        $diagnosis     = $conn->real_escape_string($body['diagnosis']    ?? '');
        $icdCode       = $conn->real_escape_string($body['icdCode']      ?? '');
        $prescription  = $conn->real_escape_string($body['prescription'] ?? '');
        $notes         = $conn->real_escape_string($body['notes']        ?? '');
        $status        = $conn->real_escape_string($body['status']       ?? 'Draft');
        $followUpRaw  = ($body['followUpDate'] ?? '') ?: null;
        $followUpDate = $followUpRaw ? "'" . $conn->real_escape_string($followUpRaw) . "'" : "NULL";

        $ok = $conn->query("
        INSERT INTO medicalRecords
            (recordCode, patientId, doctorId, appointmentId,
 recordType, diagnosis, icdCode, prescription, notes, status, followUpDate)
        VALUES
            ('$code', $patientId, $doctorId, $appointmentId,
 '$recordType', '$diagnosis', '$icdCode', '$prescription', '$notes', '$status', $followUpDate)
    ");

        if ($ok) {
            $newId = $conn->insert_id;
            $conn->query("
                INSERT INTO medicalRecordAudit (recordId, action, changedBy, changedAt)
                VALUES ($newId, 'Created', '$changedBy', NOW())
            ");

            if ($followUpRaw) {
                $folLast     = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(followUpCode, '-', -1) AS UNSIGNED)) FROM followups")->fetch_row()[0];
                $folCode     = 'FOL-' . date('Y') . '-' . str_pad((int)$folLast + 1, 4, '0', STR_PAD_LEFT);
                $followUpEsc = $conn->real_escape_string($followUpRaw);

                $conn->query("
        INSERT INTO followups (followUpCode, patientId, appointmentId, followUpDate, reason, status)
        VALUES ('$folCode', $patientId, $appointmentId, '$followUpEsc', 'Follow-up from record $code', 'Pending')
    ");

                logActivity($conn, 'New Follow-up', "Follow-up $folCode created from $code", $conn->insert_id, 'Followup');
            }

            echo json_encode(['success' => true, 'recordCode' => $code]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $body = json_decode(file_get_contents('php://input'), true);

        $id            = (int)($body['id']            ?? 0);
        $patientId     = (int)($body['patientId']     ?? 0) ?: 'NULL';
        $doctorId      = (int)($body['doctorId']      ?? 0) ?: 'NULL';
        $appointmentId = (int)($body['appointmentId'] ?? 0) ?: 'NULL';
        $recordType    = $conn->real_escape_string($body['recordType']   ?? 'Consultation');
        $diagnosis     = $conn->real_escape_string($body['diagnosis']    ?? '');
        $icdCode       = $conn->real_escape_string($body['icdCode']      ?? '');
        $prescription  = $conn->real_escape_string($body['prescription'] ?? '');
        $notes         = $conn->real_escape_string($body['notes']        ?? '');
        $status        = $conn->real_escape_string($body['status']       ?? 'Draft');
        $followUpRaw   = ($body['followUpDate'] ?? '') ?: null;
        $followUpDate  = $followUpRaw ? "'" . $conn->real_escape_string($followUpRaw) . "'" : "NULL";

        $ok = $conn->query("
        UPDATE medicalRecords SET
            patientId     = $patientId,
            doctorId      = $doctorId,
            appointmentId = $appointmentId,
            recordType    = '$recordType',
            diagnosis     = '$diagnosis',
            icdCode       = '$icdCode',
            prescription  = '$prescription',
            notes         = '$notes',
            status        = '$status',
            followUpDate  = $followUpDate,
            updatedAt     = NOW()
        WHERE id = $id
    ");

        // ── NEW: sync follow-up appointment on edit ──────────────────────────
        if ($ok && $followUpRaw && $patientId !== 'NULL' && $doctorId !== 'NULL') {
            $followUpEsc = $conn->real_escape_string($followUpRaw);
            $recCode     = $conn->query("SELECT recordCode FROM medicalRecords WHERE id=$id")->fetch_row()[0] ?? '';

            $exists = $conn->query("
    SELECT id FROM followups
    WHERE patientId = $patientId
      AND followUpDate = '$followUpEsc'
      AND status NOT IN ('Cancelled')
    LIMIT 1
")->fetch_row();

            if (!$exists) {
                $folLast  = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(followUpCode, '-', -1) AS UNSIGNED)) FROM followups")->fetch_row()[0];
                $folCode  = 'FOL-' . date('Y') . '-' . str_pad((int)$folLast + 1, 4, '0', STR_PAD_LEFT);

                $conn->query("
                    INSERT INTO followups (followUpCode, patientId, appointmentId, followUpDate, reason, status)
                    VALUES ('$folCode', $patientId, $appointmentId, '$followUpEsc', 'Follow-up from record $recCode', 'Pending')
                ");
                logActivity($conn, 'New Follow-up', "Follow-up $folCode created from $recCode (edit)", $conn->insert_id, 'Followup');
            }
        }
        // ────────────────────────────────────────────────────────────────────

        if ($ok) {
            $conn->query("
                INSERT INTO medicalRecordAudit (recordId, action, changedBy, changedAt)
                VALUES ($id, 'Edited', '$changedBy', NOW())
            ");
        }
        echo json_encode(['success' => (bool)$ok, 'error' => $ok ? null : $conn->error]);
        break;

    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['Draft', 'Finalized'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            break;
        }

        $s = $conn->real_escape_string($status);

        // Capture old status BEFORE updating
        $oldStatus = $conn->query("SELECT status FROM medicalRecords WHERE id=$id")->fetch_row()[0] ?? '';

        $ok = $conn->query("UPDATE medicalRecords SET status='$s', updatedAt=NOW() WHERE id=$id");

        if ($ok && $oldStatus !== $status) {
            $oldEsc = $conn->real_escape_string($oldStatus);
            $conn->query("
                INSERT INTO medicalRecordAudit (recordId, action, changedBy, changedAt, oldValue, newValue)
                VALUES ($id, 'Status Changed', '$changedBy', NOW(), '$oldEsc', '$s')
            ");
        }

        echo json_encode(['success' => (bool)$ok, 'stats' => getStats($conn)]);
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

        $id = (int)($_POST['id'] ?? 0);
        $ok = $conn->query("DELETE FROM medicalRecords WHERE id=$id");
        echo json_encode(['success' => (bool)$ok]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => "Unknown action: '$action'"]);
}
