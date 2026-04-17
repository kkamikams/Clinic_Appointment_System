<?php

include('../../app/middleware/admin.php');
require_once('../../app/config/config.php');

header('Content-Type: application/json');

$id     = (int)($_POST['id']     ?? 0);
$status = $_POST['status'] ?? '';

$allowed = ['On Duty', 'Break', 'Off Duty'];
if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$safeStatus = $conn->real_escape_string($status);
$conn->query("UPDATE doctors SET status='$safeStatus', updatedAt=NOW() WHERE id=$id");

if ($conn->affected_rows >= 0) {
    $doc = $conn->query("SELECT CONCAT('Dr. ',firstName,' ',lastName) AS n FROM doctors WHERE id=$id")->fetch_row()[0] ?? '';
    $stmt = $conn->prepare(
        "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES ('doctor_status',?,?,'Doctor')"
    );
    $desc = "$doc status changed to $status";
    $stmt->bind_param('si', $desc, $id);
    $stmt->execute();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
