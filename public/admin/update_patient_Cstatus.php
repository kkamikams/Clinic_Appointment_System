<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

$id        = (int)($_POST['id'] ?? 0);
$condition = $_POST['condition'] ?? '';

$allowed = ['Stable', 'Critical', 'Under Observation', 'Recovering'];

if (!$id || !in_array($condition, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$stmt = $conn->prepare("UPDATE patients SET patientCondition = ? WHERE id = ?");
$stmt->bind_param('si', $condition, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
