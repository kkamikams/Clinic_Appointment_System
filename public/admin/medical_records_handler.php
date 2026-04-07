<?php
header('Content-Type: application/json');
require_once('../../app/config/config.php');

$action = $_GET['action'] ?? '';

// ══════════════════════════════════════════════════════════════════
//  HELPER — run a SELECT and return all rows as assoc array
// ══════════════════════════════════════════════════════════════════
function dbRows($conn, $sql, $types = '', $params = [])
{
    if ($params) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    if (!$result || $result === true) return [];
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ── return a single scalar value (COUNT, etc.) ────────────────────
function dbVal($conn, $sql)
{
    $result = $conn->query($sql);
    if (!$result) return 0;
    $row = $result->fetch_row();
    return $row ? $row[0] : 0;
}

// ── build the 4 stat counters ─────────────────────────────────────
function getStats($conn)
{
    $today = date('Y-m-d');
    $ym    = date('Y-m');
    return [
        'total'         => (int)dbVal($conn, "SELECT COUNT(*) FROM medicalRecords"),
        'today'         => (int)dbVal($conn, "SELECT COUNT(*) FROM medicalRecords WHERE DATE(updatedAt)='$today'"),
        'labPending'    => (int)dbVal($conn, "SELECT COUNT(*) FROM medicalRecords WHERE recordType='Lab Result' AND status='Draft'"),
        'prescriptions' => (int)dbVal($conn, "SELECT COUNT(*) FROM medicalRecords WHERE recordType='Prescription' AND DATE_FORMAT(createdAt,'%Y-%m')='$ym'"),
    ];
}

// ══════════════════════════════════════════════════════════════════
//  get_patients  — live search in modal
// ══════════════════════════════════════════════════════════════════
if ($action === 'get_patients') {
    $q = '%' . $conn->real_escape_string(trim($_GET['q'] ?? '')) . '%';
    $rows = dbRows($conn, "
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
    ");
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  get_doctors  — live search in modal
// ══════════════════════════════════════════════════════════════════
if ($action === 'get_doctors') {
    $q = '%' . $conn->real_escape_string(trim($_GET['q'] ?? '')) . '%';
    $rows = dbRows($conn, "
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
    ");
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  get_appointments  — live search in modal (optional link)
// ══════════════════════════════════════════════════════════════════
if ($action === 'get_appointments') {
    $q         = '%' . $conn->real_escape_string(trim($_GET['q'] ?? '')) . '%';
    $patientId = (int)($_GET['patientId'] ?? 0);
    $patClause = $patientId ? "AND a.patientId = $patientId" : '';

    $rows = dbRows($conn, "
        SELECT a.id,
               a.appointmentCode,
               a.appointmentDate,
               TRIM(CONCAT(d.firstName,' ',d.lastName)) AS doctorName
        FROM appointments a
        JOIN doctors d ON d.id = a.doctorId
        WHERE a.appointmentCode LIKE '$q'
          $patClause
        ORDER BY a.appointmentDate DESC
        LIMIT 10
    ");
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  list  — paginated table
// ══════════════════════════════════════════════════════════════════
if ($action === 'list') {
    $searchRaw = trim($_GET['search'] ?? '');
    $type      = trim($_GET['type']   ?? '');
    $status    = trim($_GET['status'] ?? '');
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

    $total = (int)dbVal($conn, "
        SELECT COUNT(*)
        FROM medicalRecords m
        JOIN patients p ON p.id = m.patientId
        $whereSQL
    ");

    $rows = dbRows($conn, "
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
        ORDER BY m.createdAt DESC
        LIMIT $limit OFFSET $offset
    ");

    echo json_encode([
        'success' => true,
        'rows'    => $rows,
        'total'   => $total,
        'page'    => $page,
        'limit'   => $limit,
        'stats'   => getStats($conn),
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  get  — single record for view / edit modal
// ══════════════════════════════════════════════════════════════════
if ($action === 'get') {
    $id   = (int)($_GET['id'] ?? 0);
    $rows = dbRows($conn, "
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
    ");
    $row = $rows[0] ?? null;
    echo json_encode(
        $row
            ? ['success' => true,  'data' => $row]
            : ['success' => false, 'message' => 'Record not found']
    );
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  add
// ══════════════════════════════════════════════════════════════════
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $count = (int)dbVal($conn, "SELECT COUNT(*) FROM medicalRecords");
    $code  = 'REC-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

    $patientId     = (int)($body['patientId']     ?? 0)  ?: 'NULL';
    $doctorId      = (int)($body['doctorId']      ?? 0)  ?: 'NULL';
    $appointmentId = (int)($body['appointmentId'] ?? 0)  ?: 'NULL';
    $recordType    = $conn->real_escape_string($body['recordType']   ?? 'Consultation');
    $diagnosis     = $conn->real_escape_string($body['diagnosis']    ?? '');
    $icdCode       = $conn->real_escape_string($body['icdCode']      ?? '');
    $prescription  = $conn->real_escape_string($body['prescription'] ?? '');
    $notes         = $conn->real_escape_string($body['notes']        ?? '');
    $status        = $conn->real_escape_string($body['status']       ?? 'Draft');

    $ok = $conn->query("
        INSERT INTO medicalRecords
            (recordCode, patientId, doctorId, appointmentId,
             recordType, diagnosis, icdCode, prescription, notes, status)
        VALUES
            ('$code', $patientId, $doctorId, $appointmentId,
             '$recordType', '$diagnosis', '$icdCode', '$prescription', '$notes', '$status')
    ");

    if ($ok) {
        $newId = $conn->insert_id;
        // activity log — safe to fail if table doesn't exist
        @$conn->query("
            INSERT INTO recentActivity (activityType, description, referenceId, referenceType)
            VALUES ('New Record', 'Medical record $code created', $newId, 'Record')
        ");
    }

    echo json_encode(['success' => (bool)$ok, 'error' => $ok ? null : $conn->error]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  edit
// ══════════════════════════════════════════════════════════════════
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $id            = (int)($body['id']            ?? 0);
    $patientId     = (int)($body['patientId']     ?? 0)  ?: 'NULL';
    $doctorId      = (int)($body['doctorId']      ?? 0)  ?: 'NULL';
    $appointmentId = (int)($body['appointmentId'] ?? 0)  ?: 'NULL';
    $recordType    = $conn->real_escape_string($body['recordType']   ?? 'Consultation');
    $diagnosis     = $conn->real_escape_string($body['diagnosis']    ?? '');
    $icdCode       = $conn->real_escape_string($body['icdCode']      ?? '');
    $prescription  = $conn->real_escape_string($body['prescription'] ?? '');
    $notes         = $conn->real_escape_string($body['notes']        ?? '');
    $status        = $conn->real_escape_string($body['status']       ?? 'Draft');

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
            status        = '$status'
        WHERE id = $id
    ");

    echo json_encode(['success' => (bool)$ok, 'error' => $ok ? null : $conn->error]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  update_status  — inline badge dropdown
// ══════════════════════════════════════════════════════════════════
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!in_array($status, ['Draft', 'Finalized'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    $s  = $conn->real_escape_string($status);
    $ok = $conn->query("UPDATE medicalRecords SET status='$s' WHERE id=$id");

    echo json_encode(['success' => (bool)$ok, 'stats' => getStats($conn)]);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  delete
// ══════════════════════════════════════════════════════════════════
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $ok = $conn->query("DELETE FROM medicalRecords WHERE id=$id");
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

// ── fallback ──────────────────────────────────────────────────────
echo json_encode(['success' => false, 'message' => "Unknown action: '$action'"]);
