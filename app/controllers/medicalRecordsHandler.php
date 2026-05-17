<?php

session_start();
if (empty($_SESSION['authUser']) && empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
require_once('../config/config.php');
require_once(__DIR__ . '/../models/MedicalRecordModel.php');

header('Content-Type: application/json');

$action    = $_GET['action'] ?? '';
$model     = new MedicalRecordModel($conn);
$changedBy = !empty($_SESSION['authUser'])
    ? 'Admin ' . (explode(' ', $_SESSION['authUser']['fullName'] ?? '')[0] ?: 'Unknown')
    : 'User ' . ($_SESSION['user_id'] ?? 'Unknown');

switch ($action) {

    case 'get_patients':
        echo json_encode(['success' => true, 'data' => $model->searchPatients($_GET['q'] ?? '')]);
        break;

    case 'get_doctors':
        echo json_encode(['success' => true, 'data' => $model->searchDoctors($_GET['q'] ?? '')]);
        break;

    case 'get_patient_doctor':
        $row = $model->getPatientDoctor((int)($_GET['patientId'] ?? 0));
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
        break;

    case 'get_appointments':
        $rows = $model->getAppointments(
            (int)($_GET['patientId'] ?? 0),
            $_GET['q'] ?? ''
        );
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_appointment_details':
        $row = $model->getAppointmentDetails((int)($_GET['apptId'] ?? 0));
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
        break;

    case 'get_followups':
        echo json_encode(['success' => true, 'data' => $model->getFollowUps()]);
        break;

    case 'list':
        $result = $model->list([
            'search' => $_GET['search'] ?? '',
            'type'   => $_GET['type']   ?? '',
            'status' => $_GET['status'] ?? '',
            'page'   => $_GET['page']   ?? 1,
        ]);
        echo json_encode(['success' => true, 'stats' => $model->getStats(), ...$result]);
        break;

    case 'get':
        $row = $model->get((int)($_GET['id'] ?? 0));

        if (empty($_SESSION['authUser']) && !empty($_SESSION['user_id'])) {
            $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
            $uStmt->bind_param('i', $_SESSION['user_id']);
            $uStmt->execute();
            $userEmail = $uStmt->get_result()->fetch_row()[0] ?? '';

            $pStmt = $conn->prepare("
    SELECT DISTINCT p.id FROM patients p
    WHERE p.emailAddress = ? AND p.status != 'Inactive'

    UNION

    SELECT DISTINCT a.patientId FROM appointments a
    JOIN patients p ON p.id = a.patientId
    WHERE a.bookedByUserId = ? AND p.status != 'Inactive'
");
            $pStmt->bind_param('si', $userEmail, $_SESSION['user_id']);
            $pStmt->execute();
            $allowed = array_column($pStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');

            if (!$row || !in_array((int)$row['patientId'], $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Record not found']);
                break;
            }
        }

        echo json_encode(
            $row
                ? ['success' => true,  'data' => $row]
                : ['success' => false, 'message' => 'Record not found']
        );
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $model->add($body, $changedBy);
        echo json_encode($result);
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $model->edit($body, $changedBy);
        echo json_encode($result);
        break;

    case 'get_patient_by_id':
        $patientId = (int)($_GET['patientId'] ?? 0);
        if (!$patientId) {
            echo json_encode(['success' => false]);
            break;
        }
        $row = $conn->query("
        SELECT id, patientCode,
               TRIM(CONCAT(firstName,' ',COALESCE(NULLIF(middleName,''),''),' ',lastName)) AS name
        FROM patients
        WHERE id = $patientId LIMIT 1
    ")->fetch_assoc();
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
        break;

    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['Draft', 'Finalized'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            break;
        }
        echo json_encode($model->updateStatus($id, $status, $changedBy));
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(['success' => $model->delete($id)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => "Unknown action: '$action'"]);
}
