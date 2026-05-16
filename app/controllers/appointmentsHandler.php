<?php

include('../middleware/admin.php');
require_once('../config/config.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/../models/appointmentModel.php');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$model  = new appointmentModel($conn);

switch ($action) {

    case 'get_linked_record':
        $apptId = (int)($_GET['apptId'] ?? 0);
        $row    = $model->getLinkedRecord($apptId);
        echo json_encode(['success' => true, 'data' => $row]);
        break;

    case 'get_linked_followup':
        $apptId = (int)($_GET['apptId'] ?? 0);
        $currentFollowUpId = (int)($_GET['excludeId'] ?? 0);
        $rows = [];
        if ($apptId) {
            $stmt = $conn->prepare("
            SELECT id, followUpCode, followUpDate, followUpTime AS appointmentTime, status, reason
            FROM followUps
            WHERE appointmentId = ?
            ORDER BY followUpDate ASC
        ");
            $stmt->bind_param('i', $apptId);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(['success' => true, 'data' => $rows, 'currentId' => $currentFollowUpId]);
        break;
    case 'get_linked_record_followup':
        $followUpId = (int)($_GET['followUpId'] ?? 0);
        $row = null;
        if ($followUpId) {
            $stmt = $conn->prepare("
            SELECT recordCode, diagnosis, recordType, status
            FROM medicalRecords
            WHERE followUpId = ?
            LIMIT 1
        ");
            $stmt->bind_param('i', $followUpId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
        }
        echo json_encode(['success' => true, 'data' => $row]);
        break;

    case 'list':
        $result = $model->list([
            'date'    => $_GET['date']    ?? '',
            'search'  => $_GET['search']  ?? '',
            'status'  => $_GET['status']  ?? '',
            'channel' => $_GET['channel'] ?? '',
            'doctor'  => $_GET['doctor']  ?? 0,
            'page'    => $_GET['page']    ?? 1,
        ]);
        echo json_encode(['success' => true, ...$result]);
        break;

    case 'get':
        $row = $model->get((int)($_GET['id'] ?? 0));
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
        break;

    case 'get_followup':
        $row = $model->getFollowUp((int)($_GET['id'] ?? 0));
        echo json_encode(['success' => (bool)$row, 'data' => $row]);
        break;

    case 'add':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = $model->add([
            'patientId'       => (int)($body['patientId']    ?? 0),
            'patientName'     => $body['patientName']         ?? '',
            'patientGender'   => $body['patientGender']       ?? 'Other',
            'patientDOB'      => $body['patientDOB']          ?? null,
            'patientContact'  => $body['patientContact']      ?? null,
            'patientEmail'    => $body['patientEmail']        ?? null,
            'patientAddress'  => $body['patientAddress']      ?? null,
            'doctorId'        => (int)($body['doctorId']      ?? 0),
            'appointmentDate' => $body['appointmentDate']     ?? '',
            'appointmentTime' => $body['appointmentTime']     ?? '',
            'channel'         => $body['channel']             ?? 'Walk-in',
            'status'          => $body['status']              ?? 'Pending',
            'remarks'         => $body['remarks']             ?? '',
        ]);
        echo json_encode(
            $id
                ? ['success' => true,  'id' => $id]
                : ['success' => false, 'message' => 'Could not create appointment']
        );
        break;

    case 'edit':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ok   = $model->edit([
            'id'              => (int)($body['id']        ?? 0),
            'patientId'       => (int)($body['patientId'] ?? 0),
            'doctorId'        => (int)($body['doctorId']  ?? 0),
            'appointmentDate' => $body['appointmentDate'] ?? '',
            'appointmentTime' => $body['appointmentTime'] ?? '',
            'channel'         => $body['channel']         ?? 'Walk-in',
            'status'          => $body['status']          ?? 'Pending',
            'remarks'         => $body['remarks']         ?? '',
        ]);
        echo json_encode(['success' => $ok]);
        break;

    case 'edit_followup':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ok   = $model->editFollowUp([
            'id'              => (int)($body['id']        ?? 0),
            'doctorId'        => (int)($body['doctorId']  ?? 0),
            'appointmentDate' => $body['appointmentDate'] ?? '',
            'appointmentTime' => $body['appointmentTime'] ?? '',
            'status'          => $body['status']          ?? 'Pending',
            'remarks'         => $body['remarks']         ?? '',
        ]);
        echo json_encode(['success' => $ok]);
        break;

    case 'cancel':
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(['success' => $model->cancel($id)]);
        break;

    // ── Update status for regular appointments ──────────
    case 'update_status':
        $id      = (int)($_POST['id']    ?? 0);
        $status  = trim($_POST['status'] ?? '');
        $allowed = ['Pending', 'In Progress', 'Completed', 'Cancelled'];

        if (!$id || !in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            break;
        }

        $ok = $model->updateStatus($id, $status);
        echo json_encode(['success' => $ok, 'stats' => $model->getStats()]);
        break;

    // ── Update status for follow-up rows ────────────────
    case 'update_followup_status':
        $id      = (int)($_POST['id']    ?? 0);
        $status  = trim($_POST['status'] ?? '');
        $allowed = ['Pending', 'In Progress', 'Completed', 'Cancelled'];

        if (!$id || !in_array($status, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            break;
        }

        $ok = $model->updateFollowUpStatus($id, $status);
        echo json_encode(['success' => $ok, 'stats' => $model->getStats()]);
        break;

    case 'get_doctors':
        echo json_encode(['success' => true, 'data' => $model->getDoctors()]);
        break;

    case 'get_patients':
        $rows = $model->searchPatients($_GET['q'] ?? '');
        echo json_encode(['success' => true, 'data' => $rows]);
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
        $date     = $_GET['date'] ?? '';
        if (!$doctorId || !$date) {
            echo json_encode(['success' => true, 'slots' => []]);
            break;
        }
        $slots = $model->getSlots($doctorId, $date);
        echo json_encode(['success' => true, 'slots' => $slots]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
