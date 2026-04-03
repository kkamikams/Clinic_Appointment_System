<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

$id     = (int)($_POST['id']     ?? 0);
$status = trim($_POST['status']  ?? '');

$allowed = ['On Duty', 'Break', 'Off Duty'];
if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$stmt = $conn->prepare("UPDATE doctors SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $id);

if ($stmt->execute()) {
    // Log activity
    $desc    = "Doctor status changed to: $status (ID: $id)";
    $type    = 'Status Update';
    $refType = 'Doctor';
    $log = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)"
    );
    $log->bind_param('ssis', $type, $desc, $id, $refType);
    $log->execute();
    $log->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
