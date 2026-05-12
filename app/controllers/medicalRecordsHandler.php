<?php

include('../middleware/admin.php');
require_once('../config/config.php');
require_once(__DIR__ . '/../models/MedicalRecordModel.php');

header('Content-Type: application/json');

$action    = $_GET['action'] ?? '';
$model     = new MedicalRecordModel($conn);
$changedBy = 'Admin ' . (explode(' ', $_SESSION['authUser']['fullName'] ?? '')[0] ?: 'Unknown');

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
