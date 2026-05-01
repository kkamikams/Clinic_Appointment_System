<?php
require_once('../config/config.php');

$doctorId = intval($_GET['doctor_id'] ?? 0);
if (!$doctorId) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT 
        CONCAT(p.firstName, ' ', p.lastName) AS patientName,
        DATE_FORMAT(a.appointmentTime, '%h:%i %p') AS time,
        a.remarks AS reason,
        a.status
    FROM appointments a
    JOIN patients p ON p.id = a.patientId
    WHERE a.doctorId = ?
      AND a.appointmentDate = CURDATE()
      AND a.status != 'Cancelled'
    ORDER BY a.appointmentTime ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => $conn->error]);
    exit;
}

$stmt->bind_param('i', $doctorId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($rows ?: []);
