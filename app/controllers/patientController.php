<?php

include('../../app/middleware/admin.php');
require_once('../../app/config/config.php');
require_once('../../app/controllers/helpers.php');

header('Content-Type: application/json');

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

function handlePatientPhoto(string $patientCode): ?string
{
    if (empty($_FILES['photo']['tmp_name']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/patients/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return false;
    }
    $filename = $patientCode . '_' . time() . '.' . $ext;
    return move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)
        ? 'uploads/patients/' . $filename
        : null;
}

switch ($action) {

    case 'save':

        $firstName  = trim($_POST['first_name']  ?? '');
        $middleName = trim($_POST['middle_name']  ?? '');
        $lastName   = trim($_POST['last_name']    ?? '');
        $gender     = trim($_POST['gender']       ?? '');
        $dob        = ($_POST['dob']              ?? '') ?: null;
        $address    = trim($_POST['address']      ?? '');
        $contact    = trim($_POST['contact']      ?? '');
        $email      = trim($_POST['email']        ?? '');
        $notes      = trim($_POST['notes']        ?? '');
        $status     = in_array($_POST['status']    ?? '', ['Active', 'Discharged', 'Inactive'])
            ? $_POST['status'] : 'Active';
        $condition  = in_array($_POST['condition'] ?? '', ['Stable', 'Critical', 'Under Observation', 'Recovering'])
            ? $_POST['condition'] : 'Stable';

        if (!$firstName || !$lastName || !$gender || !$contact) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
            exit;
        }

        $max         = (int) $conn->query("SELECT MAX(id) FROM patients")->fetch_row()[0];
        $patientCode = 'PAT-' . date('Y') . '-' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);

        $photoResult = handlePatientPhoto($patientCode);
        if ($photoResult === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
            exit;
        }
        $photoUrl = $photoResult;

        $stmt = $conn->prepare("
            INSERT INTO patients
                (patientCode, firstName, middleName, lastName, gender, dateOfBirth,
                 contactNumber, emailAddress, address, status, patientCondition, notes, photoUrl)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            'sssssssssssss',
            $patientCode,
            $firstName,
            $middleName,
            $lastName,
            $gender,
            $dob,
            $contact,
            $email,
            $address,
            $status,
            $condition,
            $notes,
            $photoUrl
        );

        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            logActivity($conn, 'patient', "New patient registered: $firstName $lastName ($patientCode)", $newId, 'Patient');
            echo json_encode(['success' => true, 'patientId' => $newId, 'patientCode' => $patientCode]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
        break;


    case 'update':

        $id         = (int)($_POST['id']          ?? 0);
        $firstName  = trim($_POST['first_name']   ?? '');
        $middleName = trim($_POST['middle_name']   ?? '');
        $lastName   = trim($_POST['last_name']     ?? '');
        $gender     = trim($_POST['gender']        ?? '');
        $dob        = ($_POST['dob']               ?? '') ?: null;
        $address    = trim($_POST['address']       ?? '');
        $contact    = trim($_POST['contact']       ?? '');
        $email      = trim($_POST['email']         ?? '');
        $notes      = trim($_POST['notes']         ?? '');
        $status     = in_array($_POST['status']    ?? '', ['Active', 'Discharged', 'Inactive'])
            ? $_POST['status'] : 'Active';
        $condition  = in_array($_POST['condition'] ?? '', ['Stable', 'Critical', 'Under Observation', 'Recovering'])
            ? $_POST['condition'] : 'Stable';

        if (!$id || !$firstName || !$lastName || !$gender || !$contact) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
            exit;
        }

        $photoSql    = '';
        $photoParams = [];

        $codeRow     = $conn->query("SELECT patientCode FROM patients WHERE id=$id")->fetch_row();
        $patientCode = $codeRow[0] ?? 'PAT';

        $photoResult = handlePatientPhoto($patientCode);
        if ($photoResult === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
            exit;
        }
        if ($photoResult !== null) {
            $photoSql = ', photoUrl=?';
        }

        $sql = "UPDATE patients SET
            firstName=?, middleName=?, lastName=?, gender=?, dateOfBirth=?,
            contactNumber=?, emailAddress=?, address=?,
            status=?, patientCondition=?, notes=?,
            updatedAt=NOW()
            $photoSql
        WHERE id=?";

        $types  = 'sssssssssss' . ($photoResult ? 's' : '') . 'i';
        $params = [
            $firstName,
            $middleName,
            $lastName,
            $gender,
            $dob,
            $contact,
            $email,
            $address,
            $status,
            $condition,
            $notes
        ];
        if ($photoResult) $params[] = $photoResult;
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            logActivity($conn, 'patient', "Patient record updated: $firstName $lastName", $id, 'Patient');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
        break;


    case 'update_status':

        $id     = (int)($_POST['id']     ?? 0);
        $status = trim($_POST['status']  ?? '');

        if (!$id || !in_array($status, ['Active', 'Discharged', 'Inactive'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE patients SET status=?, updatedAt=NOW() WHERE id=?");
        $stmt->bind_param('si', $status, $id);

        if ($stmt->execute()) {
            $nameRow = $conn->query("SELECT CONCAT(firstName,' ',lastName) FROM patients WHERE id=$id")->fetch_row();
            $patName = $nameRow[0] ?? '';
            logActivity($conn, 'patient_status', "Patient $patName status changed to $status", $id, 'Patient');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
        break;


    case 'update_condition':

        $id        = (int)($_POST['id']        ?? 0);
        $condition = trim($_POST['condition']  ?? '');

        if (!$id || !in_array($condition, ['Stable', 'Critical', 'Under Observation', 'Recovering'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE patients SET patientCondition=? WHERE id=?");
        $stmt->bind_param('si', $condition, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
        $stmt->close();
        break;


    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
