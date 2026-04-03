<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$id           = (int)($_POST['id'] ?? 0);
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
$followUpDate = trim($_POST['follow_up_date'] ?? '') ?: null;

if (!$id || !$firstName || !$lastName || !$gender) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$stmt = $conn->prepare("
    UPDATE patients SET
        firstName=?,middleName=?,lastName=?,gender=?,dateOfBirth=?,
        contactNumber=?,emailAddress=?,address=?,
        status=?,patientCondition=?,followUpDate=?,
        updatedAt=NOW()
    WHERE id=?
");
$stmt->bind_param(
    'ssssssssiissssssi', // Corrected: 17 characters total
    $firstName,      // s
    $middleName,     // s
    $lastName,       // s
    $gender,         // s
    $dob,            // s (or 'b' if binary, but 's' works for dates)
    $contact,        // s
    $email, // s
    $address,     // s
    $status,     // i
    $condition,       // i
    $followUpDate,        // s
    $id,          // s          // i
);
if ($stmt->execute()) {
    $desc = "Patient updated: {$firstName} {$lastName} (ID:{$id})";
    $conn->query("INSERT INTO recentActivity (activityType,description,referenceId,referenceType) VALUES ('Patient Updated','" . addslashes($desc) . "',$id,'Patient')");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
