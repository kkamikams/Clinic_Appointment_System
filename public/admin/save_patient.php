<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$firstName    = trim($_POST['first_name'] ?? '');
$middleName   = trim($_POST['middle_name'] ?? '');
$lastName     = trim($_POST['last_name'] ?? '');
$gender       = trim($_POST['gender'] ?? '');
$dob          = trim($_POST['dob'] ?? '') ?: null;
$address      = trim($_POST['address'] ?? '');
$contact      = trim($_POST['contact'] ?? '');
$email        = trim($_POST['email'] ?? '');
$notes        = trim($_POST['notes'] ?? '');
$status       = trim($_POST['status'] ?? 'Active');
$condition    = trim($_POST['condition'] ?? 'Stable');

if (!$firstName || !$lastName || !$gender || !$contact) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

// Auto-generate patient code P-YYYYMMDD-XXXX
$prefix = 'P-' . date('Ymd') . '-';
$lastRow = $conn->query("SELECT patientCode FROM patients WHERE patientCode LIKE '{$prefix}%' ORDER BY id DESC LIMIT 1")->fetch_row();
$seq = $lastRow ? (int)substr($lastRow[0], -4) + 1 : 1;
$patientCode = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

$stmt = $conn->prepare("
    INSERT INTO patients (patientCode,firstName,middleName,lastName,gender,dateOfBirth,
        contactNumber,emailAddress,address,status,patientCondition)
    VALUES (?,?,?,?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
    'sssssssssss',
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
    $condition
);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    $desc = "New patient: {$firstName} {$lastName} ({$patientCode})";
    $conn->query("INSERT INTO recentActivity (activityType,description,referenceId,referenceType) VALUES ('Patient Added','" . addslashes($desc) . "',$newId,'Patient')");
    echo json_encode(['success' => true, 'id' => $newId, 'code' => $patientCode]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
