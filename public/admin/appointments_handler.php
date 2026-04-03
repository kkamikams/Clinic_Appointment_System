<?php
// appointments_handler.php
// Handles all AJAX requests for the Appointments page

include('./includes/db.php'); // Your DB connection file (PDO or mysqli)
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// ─── Helper ──────────────────────────────────────────────────────────────────
function respond($data)
{
    echo json_encode($data);
    exit;
}

// ─── LIST appointments (with JOIN to patients & doctors) ─────────────────────
if ($action === 'list') {
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $limit    = 10;
    $offset   = ($page - 1) * $limit;
    $search   = '%' . trim($_GET['search'] ?? '') . '%';
    $status   = trim($_GET['status']  ?? '');
    $doctor   = trim($_GET['doctor']  ?? '');
    $date     = trim($_GET['date']    ?? date('Y-m-d'));   // default = today

    $where  = "WHERE a.appointmentDate = :date";
    $params = [':date' => $date];

    if ($search !== '%%') {
        $where .= " AND (CONCAT(p.firstName,' ',p.lastName) LIKE :search
                        OR CONCAT(d.firstName,' ',d.lastName) LIKE :search2)";
        $params[':search']  = $search;
        $params[':search2'] = $search;
    }
    if ($status !== '') {
        $where .= " AND a.status = :status";
        $params[':status'] = $status;
    }
    if ($doctor !== '') {
        $where .= " AND a.doctorId = :doctor";
        $params[':doctor'] = $doctor;
    }

    // total count
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM appointments a
        JOIN patients p ON p.id = a.patientId
        JOIN doctors  d ON d.id = a.doctorId
        $where");
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();

    // rows
    $sql = "SELECT
                a.id,
                a.appointmentCode,
                CONCAT(p.firstName,' ',p.lastName) AS patientName,
                CONCAT(p.firstName) AS patFirstName,
                CONCAT(p.lastName)  AS patLastName,
                p.photoUrl          AS patPhoto,
                a.doctorId,
                CONCAT('Dr. ', d.lastName) AS doctorName,
                d.specialization,
                a.appointmentDate,
                a.appointmentTime,
                a.channel,
                a.status,
                a.remarks
            FROM appointments a
            JOIN patients p ON p.id = a.patientId
            JOIN doctors  d ON d.id = a.doctorId
            $where
            ORDER BY a.appointmentTime ASC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // stats for selected date
    $stmtStats = $pdo->prepare("SELECT status, COUNT(*) AS cnt FROM appointments WHERE appointmentDate = :date GROUP BY status");
    $stmtStats->execute([':date' => $date]);
    $stats = ['total' => 0, 'Completed' => 0, 'In Progress' => 0, 'Pending' => 0, 'Cancelled' => 0];
    foreach ($stmtStats->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $stats[$s['status']] = (int)$s['cnt'];
        $stats['total'] += (int)$s['cnt'];
    }

    respond(['success' => true, 'rows' => $rows, 'total' => $total, 'page' => $page, 'limit' => $limit, 'stats' => $stats]);
}

// ─── GET single appointment ───────────────────────────────────────────────────
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT a.*, CONCAT(p.firstName,' ',p.lastName) AS patientName,
        CONCAT('Dr. ',d.lastName) AS doctorName, d.specialization
        FROM appointments a
        JOIN patients p ON p.id = a.patientId
        JOIN doctors  d ON d.id = a.doctorId
        WHERE a.id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    respond($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found']);
}

// ─── ADD appointment ──────────────────────────────────────────────────────────
if ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);

    // generate code e.g. A-2001
    $last = $pdo->query("SELECT MAX(id) FROM appointments")->fetchColumn();
    $code = 'A-' . (1000 + (int)$last + 1);

    $stmt = $pdo->prepare("INSERT INTO appointments
        (appointmentCode, patientId, doctorId, appointmentDate, appointmentTime, channel, status, remarks)
        VALUES (:code,:patientId,:doctorId,:date,:time,:channel,:status,:remarks)");
    $ok = $stmt->execute([
        ':code'      => $code,
        ':patientId' => $data['patientId'],
        ':doctorId'  => $data['doctorId'],
        ':date'      => $data['appointmentDate'],
        ':time'      => $data['appointmentTime'],
        ':channel'   => $data['channel']  ?? 'Walk-in',
        ':status'    => $data['status']   ?? 'Pending',
        ':remarks'   => $data['remarks']  ?? null,
    ]);
    respond(['success' => $ok, 'id' => $pdo->lastInsertId(), 'code' => $code]);
}

// ─── EDIT appointment ─────────────────────────────────────────────────────────
if ($action === 'edit') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("UPDATE appointments SET
        patientId=:patientId, doctorId=:doctorId,
        appointmentDate=:date, appointmentTime=:time,
        channel=:channel, status=:status, remarks=:remarks
        WHERE id=:id");
    $ok = $stmt->execute([
        ':id'        => $data['id'],
        ':patientId' => $data['patientId'],
        ':doctorId'  => $data['doctorId'],
        ':date'      => $data['appointmentDate'],
        ':time'      => $data['appointmentTime'],
        ':channel'   => $data['channel'],
        ':status'    => $data['status'],
        ':remarks'   => $data['remarks'] ?? null,
    ]);
    respond(['success' => $ok]);
}

// ─── DELETE / CANCEL appointment ─────────────────────────────────────────────
if ($action === 'cancel') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE appointments SET status='Cancelled' WHERE id=:id");
    $ok = $stmt->execute([':id' => $id]);
    respond(['success' => $ok]);
}

// ─── Dropdown helpers ─────────────────────────────────────────────────────────
if ($action === 'get_patients') {
    $q = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $pdo->prepare("SELECT id, CONCAT(firstName,' ',lastName) AS name, patientCode
        FROM patients WHERE status='Active' AND (firstName LIKE :q OR lastName LIKE :q)
        ORDER BY firstName LIMIT 40");
    $stmt->execute([':q' => $q]);
    respond(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

if ($action === 'get_doctors') {
    $stmt = $pdo->query("SELECT id, CONCAT('Dr. ',lastName) AS name, specialization FROM doctors ORDER BY lastName");
    respond(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

respond(['success' => false, 'message' => 'Unknown action']);
