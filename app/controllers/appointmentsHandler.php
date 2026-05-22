<?php

session_start();
if (empty($_SESSION['authUser']) && empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/../models/appointmentModel.php');

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$model  = new appointmentModel($conn);

switch ($action) {

    case 'get_doctors':
        echo json_encode(['success' => true, 'data' => $model->getDoctors()]);
        break;

    case 'get_patients':
        $q = $_GET['q'] ?? '';
        echo json_encode(['success' => true, 'data' => $model->searchPatients($q)]);
        break;

    case 'get_slots':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        $date     = $_GET['date'] ?? '';
        if (!$doctorId || !$date) {
            echo json_encode(['success' => true, 'slots' => []]);
            break;
        }
        $slots = getAvailableSlots($conn, $doctorId, $date);
        echo json_encode(['success' => true, 'slots' => $slots]);
        break;

    case 'get_doctor_schedule':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        if (!$doctorId) {
            echo json_encode(['success' => false]);
            break;
        }
        echo json_encode(['success' => true, 'data' => getDoctorSchedule($conn, $doctorId)]);
        break;

    case 'list':
        $date = $_GET['date'] ?? '';

        // FIX: Convert DD/MM/YYYY to YYYY-MM-DD for MySQL
        if (!empty($date) && strpos($date, '/') !== false) {
            $parts = explode('/', $date);
            if (count($parts) === 3) {
                $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }

        $search  = trim($_GET['search'] ?? '');
        $status  = $_GET['status'] ?? '';
        $channel = $_GET['channel'] ?? '';
        $doctor  = $_GET['doctor'] ?? '';
        $page    = max(1, (int)($_GET['page'] ?? 1));

        $result  = $model->list([
            'date' => $date,
            'search' => $search,
            'status' => $status,
            'channel' => $channel,
            'doctor' => $doctor,
            'page' => $page,
        ]);
        echo json_encode(array_merge(['success' => true], $result));
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        $row = $model->get($id);
        echo json_encode($row ? ['success' => true, 'data' => $row] : ['success' => false, 'message' => 'Not found']);
        break;

    case 'get_followup':
        $id = (int)($_GET['id'] ?? 0);
        $row = $model->getFollowUp($id);
        echo json_encode($row ? ['success' => true, 'data' => $row] : ['success' => false]);
        break;

    case 'get_linked_record':
        $apptId = (int)($_GET['apptId'] ?? 0);
        echo json_encode(['success' => true, 'data' => $model->getLinkedRecord($apptId)]);
        break;

    case 'get_linked_followup':
        $apptId = (int)($_GET['apptId'] ?? 0);
        $exclude = (int)($_GET['excludeId'] ?? 0);
        $stmt = $conn->prepare("SELECT id, followUpCode, followUpDate, status FROM followUps WHERE appointmentId = ?" . ($exclude ? " AND id != ?" : "") . " ORDER BY followUpDate ASC");
        if ($exclude) $stmt->bind_param('ii', $apptId, $exclude);
        else $stmt->bind_param('i', $apptId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $newId = $model->add($body);
        if ($newId) echo json_encode(['success' => true, 'id' => $newId]);
        else echo json_encode(['success' => false]);
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        echo json_encode(['success' => (bool)$model->edit($body)]);
        break;

    case 'edit_followup':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        echo json_encode(['success' => (bool)$model->editFollowUp($body)]);
        break;

    case 'update_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $ok = $model->updateStatus($id, $status);
        $stats = $model->getStats();
        echo json_encode(['success' => (bool)$ok, 'stats' => $stats]);
        break;

    case 'update_followup_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $ok = $model->updateFollowUpStatus($id, $status);
        $stats = $model->getStats();
        echo json_encode(['success' => (bool)$ok, 'stats' => $stats]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => "Unknown action: '$action'"]);
}
