<?php

require_once('../../app/config/config.php');
header('Content-Type: application/json');

$id          = (int)($_POST['id'] ?? 0);
$firstName   = trim($_POST['first_name']    ?? '');
$middleName  = trim($_POST['middle_name']   ?? '');
$lastName    = trim($_POST['last_name']     ?? '');
$gender      = trim($_POST['gender']        ?? '');
$dob         = ($_POST['dob']              ?? '') ?: null;
$address     = trim($_POST['address']       ?? '');
$contact     = trim($_POST['contact']       ?? '');
$email       = trim($_POST['email']         ?? '');
$notes       = trim($_POST['notes']         ?? '');
$status      = in_array($_POST['status']    ?? '', ['Active', 'Discharged', 'Inactive']) ? $_POST['status'] : 'Active';
$condition   = in_array($_POST['condition'] ?? '', ['Stable', 'Critical', 'Under Observation', 'Recovering']) ? $_POST['condition'] : 'Stable';

if (!$id || !$firstName || !$lastName || !$gender || !$contact) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

$photoSql = '';
if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/patients/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
        exit;
    }

    $code = $conn->query("SELECT patientCode FROM patients WHERE id=$id")->fetch_row()[0] ?? 'PAT';
    $filename = $code . '_' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
        $photoUrl = $conn->real_escape_string('uploads/patients/' . $filename);
        $photoSql = ", photoUrl='$photoUrl'";
    }
}

$firstName  = $conn->real_escape_string($firstName);
$middleName = $conn->real_escape_string($middleName);
$lastName   = $conn->real_escape_string($lastName);
$gender     = $conn->real_escape_string($gender);
$address    = $conn->real_escape_string($address);
$contact    = $conn->real_escape_string($contact);
$email      = $conn->real_escape_string($email);
$notes      = $conn->real_escape_string($notes);
$status     = $conn->real_escape_string($status);
$condition  = $conn->real_escape_string($condition);
$dobVal    = $dob     ? "'" . $conn->real_escape_string($dob) . "'"     : 'NULL';

$sql = "UPDATE patients SET
    firstName='$firstName', middleName='$middleName', lastName='$lastName',
    gender='$gender', dateOfBirth=$dobVal,
    contactNumber='$contact', emailAddress='$email', address='$address',
    status='$status', patientCondition='$condition',
    notes='$notes',
    updatedAt=NOW()
    $photoSql
WHERE id=$id";

if ($conn->query($sql)) {

    $fullName = "$firstName $lastName";
    $desc = "Patient record updated: $fullName";
    $type = 'patient';
    $ref  = 'Patient';
    $stmt = $conn->prepare("INSERT INTO recentActivity (activityType,description,referenceId,referenceType) VALUES (?,?,?,?)");
    $stmt->bind_param('ssis', $type, $desc, $id, $ref);
    $stmt->execute();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
