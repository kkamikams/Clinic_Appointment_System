<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/Clinic_Appointment_System/app/config/config.php');
header('Content-Type: application/json');

$firstName   = trim($_POST['first_name']    ?? '');
$middleName  = trim($_POST['middle_name']   ?? '');
$lastName    = trim($_POST['last_name']     ?? '');
$gender      = trim($_POST['gender']        ?? '');
$dob         = ($_POST['dob'] ?? '') ?: null;
$address     = trim($_POST['address']       ?? '');
$contact     = trim($_POST['contact']       ?? '');
$email       = trim($_POST['email']         ?? '');
$notes       = trim($_POST['notes']         ?? '');
$status      = in_array($_POST['status'] ?? '', ['Active', 'Discharged', 'Inactive']) ? $_POST['status'] : 'Active';
$condition   = in_array($_POST['condition'] ?? '', ['Stable', 'Critical', 'Under Observation', 'Recovering']) ? $_POST['condition'] : 'Stable';

if (!$firstName || !$lastName || !$gender || !$contact) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

$max = (int) $conn->query("SELECT MAX(id) FROM patients")->fetch_row()[0];
$patientCode = 'PAT-' . date('Y') . '-' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);

$photoUrl = null;
if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/patients/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
        exit;
    }
    $filename = $patientCode . '_' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
        $photoUrl = 'uploads/patients/' . $filename;
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
$dobVal     = $dob     ? "'" . $conn->real_escape_string($dob) . "'" : 'NULL';
$photoVal   = $photoUrl ? "'" . $conn->real_escape_string($photoUrl) . "'" : 'NULL';
$followVal  = 'NULL'; // no follow-up date input yet

$sql = "INSERT INTO patients
    (patientCode, firstName, middleName, lastName, gender, dateOfBirth,
     contactNumber, emailAddress, address, status, patientCondition,
     followUpDate, notes, photoUrl)
    VALUES
    ('$patientCode','$firstName','$middleName','$lastName','$gender',$dobVal,
     '$contact','$email','$address','$status','$condition',
     $followVal,'$notes',$photoVal)";

if ($conn->query($sql)) {
    $newId = $conn->insert_id;
    $desc = "New patient registered: $firstName $lastName ($patientCode)";
    $type = 'patient';
    $ref  = 'Patient';
    $stmt = $conn->prepare("INSERT INTO recentActivity (activityType,description,referenceId,referenceType) VALUES (?,?,?,?)");
    $stmt->bind_param('ssis', $type, $desc, $newId, $ref);
    $stmt->execute();
    echo json_encode(['success' => true, 'patientId' => $newId, 'patientCode' => $patientCode]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
