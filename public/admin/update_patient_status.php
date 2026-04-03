<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$id     = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');
$allowed = ['Active', 'Discharged', 'Inactive'];

if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$stmt = $conn->prepare("UPDATE patients SET status=?,updatedAt=NOW() WHERE id=?");
$stmt->bind_param('si', $status, $id);

if ($stmt->execute()) {
    $desc = "Patient status → '{$status}' (ID:{$id})";
    $conn->query("INSERT INTO recentActivity (activityType,description,referenceId,referenceType) VALUES ('Status Change','" . addslashes($desc) . "',$id,'Patient')");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
