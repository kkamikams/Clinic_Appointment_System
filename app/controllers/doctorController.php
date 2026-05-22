<?php

include('../../app/middleware/admin.php');
require_once('../../app/config/config.php');
require_once('../../app/controllers/helpers.php');

header('Content-Type: application/json');

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

switch ($action) {

    case 'save':

        $firstName      = trim($_POST['firstname']       ?? '');
        $lastName       = trim($_POST['lastname']        ?? '');
        $middleName     = trim($_POST['middlename']      ?? '');
        $gender         = trim($_POST['gender']          ?? '');
        $dob            = ($_POST['dob']                 ?? '') ?: null;
        $license        = trim($_POST['license']         ?? '');
        $specialization = trim($_POST['specialization']  ?? '');
        $department     = trim($_POST['department']      ?? '');
        $experience     = (int)($_POST['experience']     ?? 0);
        $capacity       = (int)($_POST['capacity']       ?? 20);
        $contact        = trim($_POST['contact']         ?? '');
        $email          = trim($_POST['email']           ?? '');
        $address        = trim($_POST['address']         ?? '');
        $shiftStart     = $_POST['shiftStart']           ?? '08:00';
        $shiftEnd       = $_POST['shiftEnd']             ?? '17:00';
        $notes          = trim($_POST['notes']           ?? '');
        $status         = trim($_POST['status']          ?? 'Off Duty');
        $empStatus      = trim($_POST['empStatus']       ?? 'Active');
        $days           = is_array($_POST['days'] ?? null) ? $_POST['days'] : [];

        if (!$firstName || !$lastName || !$gender || !$license || !$specialization || !$contact) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
            exit;
        }

        // Wrap doctor insert and schedule insert in a transaction to keep data consistent
        $conn->begin_transaction();

        try {
            // Generate a sequential doctor code like DOC-2025-001
            $year    = date('Y');
            $lastRow = $conn->query("
                SELECT doctorCode FROM doctors
                WHERE doctorCode LIKE 'DOC-$year-%'
                ORDER BY id DESC LIMIT 1
            ");
            if ($lastRow && $lastRow->num_rows > 0) {
                $parts   = explode('-', $lastRow->fetch_row()[0]);
                $nextNum = (int)end($parts) + 1;
            } else {
                $nextNum = 1;
            }
            $doctorCode = 'DOC-' . $year . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

            $photoUrl  = null; // initialize as null
            $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/Clinic_Appointment_System/app/uploads/doctors/';

            if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                    throw new Exception('Image must be under 2MB.');
                }
                $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed)) {
                    throw new Exception('Invalid image type. Allowed: jpg, jpeg, png, gif, webp.');
                }
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = $doctorCode . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception('Failed to save photo. Check folder permissions.');
                }
                $photoUrl = '/Clinic_Appointment_System/app/uploads/doctors/' . $filename;
            }

            $stmt = $conn->prepare("
                INSERT INTO doctors
                    (doctorCode, firstName, middleName, lastName, gender, dateOfBirth,
                     prcLicenseNo, specialization, department, yearsOfExperience, patientCapacity,
                     contactNumber, emailAddress, address, notes, status, employmentStatus, photoUrl)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->bind_param(
                'sssssssssiisssssss',
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
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $doctorId = $conn->insert_id;
            $stmt->close();

            if (!empty($days)) {
                $sched = $conn->prepare("
                    INSERT INTO doctorSchedules (doctorId, dayOfWeek, shiftStart, shiftEnd)
                    VALUES (?,?,?,?)
                ");
                foreach ($days as $day) {
                    $day = trim($day);
                    $sched->bind_param('isss', $doctorId, $day, $shiftStart, $shiftEnd);
                    if (!$sched->execute()) throw new Exception($sched->error);
                }
                $sched->close();
            }

            logActivity($conn, 'Doctor Added', "New doctor added: Dr. $firstName $lastName ($doctorCode)", $doctorId, 'Doctor');

            $conn->commit();
            echo json_encode(['success' => true, 'doctorId' => $doctorId, 'doctorCode' => $doctorCode]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update':

        $id             = (int)($_POST['id']             ?? 0);
        $firstName      = trim($_POST['first_name']      ?? '');
        $lastName       = trim($_POST['last_name']       ?? '');
        $middleName     = trim($_POST['middle_name']     ?? '');
        $gender         = trim($_POST['gender']          ?? '');
        $dob            = ($_POST['dob']                 ?? '') ?: null;
        $license        = trim($_POST['license']         ?? '');
        $specialization = trim($_POST['specialization']  ?? '');
        $department     = trim($_POST['department']      ?? '');
        $experience     = (int)($_POST['experience']     ?? 0);
        $capacity       = (int)($_POST['capacity']       ?? 20);
        $contact        = trim($_POST['contact']         ?? '');
        $email          = trim($_POST['email']           ?? '');
        $address        = trim($_POST['address']         ?? '');
        $shiftStart     = $_POST['shiftStart']           ?? '08:00';
        $shiftEnd       = $_POST['shiftEnd']             ?? '17:00';
        $notes          = trim($_POST['notes']           ?? '');
        $empStatus      = trim($_POST['emp_status']      ?? 'Active');
        $dutyStatus     = trim($_POST['duty_status']     ?? 'Off Duty');
        $days           = is_array($_POST['days'] ?? null) ? $_POST['days'] : [];

        if (!$id || !$firstName || !$lastName) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE doctors SET
                firstName=?, middleName=?, lastName=?, gender=?, dateOfBirth=?,
                prcLicenseNo=?, specialization=?, department=?,
                yearsOfExperience=?, patientCapacity=?,
                contactNumber=?, emailAddress=?, address=?,
                notes=?, employmentStatus=?, status=?
            WHERE id=?
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

        // Remove the previous photo file from disk before saving the new one
        if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/Clinic_Appointment_System/app/uploads/doctors/';
            if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Image must be under 2MB.']);
                exit;
            }
            $ext     = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
                exit;
            }
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            // Delete old photo
            $oldRow = $conn->prepare("SELECT photoUrl FROM doctors WHERE id=?");
            $oldRow->bind_param('i', $id);
            $oldRow->execute();
            $oldRow->bind_result($oldPhoto);
            $oldRow->fetch();
            $oldRow->close();
            if ($oldPhoto) {
                $oldPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $oldPhoto;
                if (file_exists($oldPath)) unlink($oldPath);
            }

            $filename = 'DOC-' . $id . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $photoUrl = '/Clinic_Appointment_System/app/uploads/doctors/' . $filename;
                $upPhoto  = $conn->prepare("UPDATE doctors SET photoUrl=? WHERE id=?");
                $upPhoto->bind_param('si', $photoUrl, $id);
                $upPhoto->execute();
                $upPhoto->close();
            }
        }

        // Replace existing schedule by deleting all entries then reinserting
        $del = $conn->prepare("DELETE FROM doctorSchedules WHERE doctorId=?");
        $del->bind_param('i', $id);
        $del->execute();
        $del->close();

        if (!empty($days)) {
            $sched = $conn->prepare("
                INSERT IGNORE INTO doctorSchedules (doctorId, dayOfWeek, shiftStart, shiftEnd)
                VALUES (?,?,?,?)
            ");
            foreach ($days as $day) {
                $day = trim($day);
                $sched->bind_param('isss', $id, $day, $shiftStart, $shiftEnd);
                $sched->execute();
            }
            $sched->close();
        }

        logActivity($conn, 'Doctor Updated', "Doctor updated: Dr. $firstName $lastName (ID: $id)", $id, 'Doctor');

        echo json_encode(['success' => true]);
        break;

    // Auto-sync all active doctors' duty status based on today's schedule before applying the manual override
    case 'update_status':
        $todayName = date('l');
        $conn->query("
            UPDATE doctors d
            SET d.status = CASE
                WHEN EXISTS (
                    SELECT 1 FROM doctorSchedules ds
                    WHERE ds.doctorId = d.id AND ds.dayOfWeek = '$todayName'
                ) THEN 'On Duty'
                ELSE 'Off Duty'
            END
            WHERE d.employmentStatus = 'Active' AND d.status != 'Break'
        ");

        $id     = (int)($_POST['id']     ?? 0);
        $status = trim($_POST['status']  ?? '');

        if (!$id || !in_array($status, ['On Duty', 'Break', 'Off Duty'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE doctors SET status=?, updatedAt=NOW() WHERE id=?");
        $stmt->bind_param('si', $status, $id);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
            exit;
        }
        $stmt->close();

        $nameRow = $conn->prepare("SELECT CONCAT('Dr. ', firstName, ' ', lastName) FROM doctors WHERE id=?");
        $nameRow->bind_param('i', $id);
        $nameRow->execute();
        $nameRow->bind_result($docName);
        $nameRow->fetch();
        $nameRow->close();

        logActivity($conn, 'doctor_status', "$docName status changed to $status", $id, 'Doctor');

        echo json_encode(['success' => true]);
        break;

    case 'get_appointments':

        $doctorId = (int)($_GET['doctor_id'] ?? 0);

        if (!$doctorId) {
            echo json_encode([]);
            break;
        }

        $today = date('Y-m-d');
        $rows  = $conn->query("
        SELECT
            CONCAT(p.firstName, ' ', p.lastName) AS patientName,
            DATE_FORMAT(a.appointmentTime, '%h:%i %p')  AS time,
            a.status,
            a.remarks AS reason
        FROM appointments a
        JOIN patients p ON p.id = a.patientId
        WHERE a.doctorId = $doctorId
          AND a.appointmentDate = '$today'
          AND a.status != 'Cancelled'
        ORDER BY a.appointmentTime ASC
    ")->fetch_all(MYSQLI_ASSOC);

        echo json_encode($rows);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
