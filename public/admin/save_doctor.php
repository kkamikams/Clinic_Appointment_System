<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('../../app/config/config.php');
header('Content-Type: application/json');

$firstName      = trim($_POST['firstname']       ?? '');
$lastName       = trim($_POST['lastname']        ?? '');
$middleName     = trim($_POST['middlename']      ?? '');
$gender         = trim($_POST['gender']          ?? '');
$dob            = ($_POST['dob'] ?? '') ?: null;
$license        = trim($_POST['license']         ?? '');
$specialization = trim($_POST['specialization']  ?? '');
$department     = trim($_POST['department']      ?? '');
$experience     = (int)($_POST['experience']    ?? 0);
$capacity       = (int)($_POST['capacity']      ?? 20);
$contact        = trim($_POST['contact']         ?? '');
$email          = trim($_POST['email']           ?? '');
$address        = trim($_POST['address']         ?? '');
$shiftStart     = $_POST['shiftStart']          ?? '08:00';
$shiftEnd       = $_POST['shiftEnd']            ?? '17:00';
$notes          = trim($_POST['notes']           ?? '');
$status         = trim($_POST['status']          ?? 'Off Duty');
$empStatus      = trim($_POST['empStatus']       ?? 'Active');
$days           = $_POST['days']                ?? [];

if (!$firstName || !$lastName || !$gender || !$license || !$specialization || !$contact) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

// Generate doctor code
$lastRow = $conn->query("SELECT doctorCode FROM doctors ORDER BY id DESC LIMIT 1");
$lastCode = $lastRow ? $lastRow->fetch_row() : null;
$nextNum  = $lastCode ? ((int)filter_var($lastCode[0], FILTER_SANITIZE_NUMBER_INT) + 1) : 1;

// Make sure the generated code doesn't already exist
while ($conn->query("SELECT id FROM doctors WHERE doctorCode = 'D-" . str_pad($nextNum, 3, '0', STR_PAD_LEFT) . "'")->num_rows > 0) {
    $nextNum++;
}
$doctorCode = 'D-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);;

// Handle photo upload
// Use an absolute path to the web root so the file always lands in the right place
// __FILE__ = /your/project/admin/save_doctor.php  →  go up to web root
$webRoot  = rtrim($_SERVER['DOCUMENT_ROOT'], '/');          // e.g. /var/www/html
$uploadDir = $webRoot . '/uploads/doctors/';                // absolute disk path

$photoUrl = null;
if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

    // Create the folder if it doesn't exist yet
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: jpg, jpeg, png, gif, webp.']);
        exit;
    }

    $filename = $doctorCode . '_' . time() . '.' . $ext;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
        // Store only the web-accessible relative path (from web root)
        $photoUrl = 'uploads/doctors/' . $filename;
    } else {
        // move_uploaded_file failed — likely a permissions issue on the folder
        echo json_encode(['success' => false, 'message' => 'Failed to save photo. Check folder permissions.']);
        exit;
    }
}

// Insert doctor
$stmt = $conn->prepare("
    INSERT INTO doctors
        (doctorCode, firstName, middleName, lastName, gender, dateOfBirth,
         prcLicenseNo, specialization, department, yearsOfExperience, patientCapacity,
         contactNumber, emailAddress, address, notes, status, employmentStatus, photoUrl)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");
$stmt->bind_param(
    'ssssssssssiissssss',
    $doctorCode,
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
    $status,
    $empStatus,
    $photoUrl
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
    exit;
}
$doctorId = $conn->insert_id;
$stmt->close();

// Insert schedules
if (!empty($days)) {
    $sched = $conn->prepare(
        "INSERT IGNORE INTO doctorSchedules (doctorId, dayOfWeek, shiftStart, shiftEnd) VALUES (?,?,?,?)"
    );
    foreach ($days as $day) {
        $day = trim($day);
        $sched->bind_param('isss', $doctorId, $day, $shiftStart, $shiftEnd);
        $sched->execute();
    }
    $sched->close();
}

// Log activity
$desc    = "New doctor added: Dr. $firstName $lastName ($doctorCode)";
$type    = 'Doctor Added';
$refType = 'Doctor';
$log = $conn->prepare(
    "INSERT INTO recentActivity (activityType, description, referenceId, referenceType) VALUES (?,?,?,?)"
);
$log->bind_param('ssis', $type, $desc, $doctorId, $refType);
$log->execute();
$log->close();

echo json_encode(['success' => true, 'doctorId' => $doctorId, 'doctorCode' => $doctorCode]);
