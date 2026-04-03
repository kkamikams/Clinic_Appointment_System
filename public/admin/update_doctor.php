<?php
require_once('../../app/config/config.php');
header('Content-Type: application/json');

$id             = (int)($_POST['id']            ?? 0);
$firstName      = trim($_POST['first_name']     ?? '');
$lastName       = trim($_POST['last_name']      ?? '');
$middleName     = trim($_POST['middle_name']    ?? '');
$gender         = trim($_POST['gender']         ?? '');
$dob            = ($_POST['dob'] ?? '') ?: null;
$license        = trim($_POST['license']        ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$department     = trim($_POST['department']     ?? '');
$experience     = (int)($_POST['experience']   ?? 0);
$capacity       = (int)($_POST['capacity']     ?? 20);
$contact        = trim($_POST['contact']        ?? '');
$email          = trim($_POST['email']          ?? '');
$address        = trim($_POST['address']        ?? '');
$shiftStart     = $_POST['shiftStart']         ?? '08:00';
$shiftEnd       = $_POST['shiftEnd']           ?? '17:00';
$notes          = trim($_POST['notes']          ?? '');
$empStatus      = trim($_POST['emp_status']     ?? 'Active');
$dutyStatus     = trim($_POST['duty_status']    ?? 'Off Duty');
$days           = $_POST['days']               ?? [];

if (!$id || !$firstName || !$lastName) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Update doctor
$stmt = $conn->prepare("
    UPDATE doctors SET
        firstName = ?, middleName = ?, lastName = ?, gender = ?, dateOfBirth = ?,
        prcLicenseNo = ?, specialization = ?, department = ?,
        yearsOfExperience = ?, patientCapacity = ?,
        contactNumber = ?, emailAddress = ?, address = ?,
        notes = ?, employmentStatus = ?, status = ?
    WHERE id = ?
");
$stmt->bind_param(
    'ssssssssiissssssi',
    $firstName,
    $middleName,
    $lastName,
    $gender,
    $dob,
    $license,
    $specialization,
    $department,
    $experience,
    $capacity,
    $contact,
    $email,
    $address,
    $notes,
    $empStatus,
    $dutyStatus,
    $id
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
    exit;
}
$stmt->close();

// Replace schedules: delete old, insert new
$del = $conn->prepare("DELETE FROM doctorSchedules WHERE doctorId = ?");
$del->bind_param('i', $id);
$del->execute();
$del->close();

if (!empty($days)) {
    $sched = $conn->prepare(
        "INSERT IGNORE INTO doctorSchedules (doctorId, dayOfWeek, shiftStart, shiftEnd) VALUES (?,?,?,?)"
    );
    foreach ($days as $day) {
        $day = trim($day);
        $sched->bind_param('isss', $id, $day, $shiftStart, $shiftEnd);
        $sched->execute();
    }
    $sched->close();
}

// Log activity
$desc    = "Doctor updated: Dr. $firstName $lastName (ID: $id)";
$type    = 'Doctor Updated';
$refType = 'Doctor';
$log = $conn->prepare(
    "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)"
);
$log->bind_param('ssis', $type, $desc, $id, $refType);
$log->execute();
$log->close();

echo json_encode(['success' => true]);
