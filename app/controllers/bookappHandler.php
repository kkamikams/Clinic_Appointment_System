<?php

include('../middleware/user.php');
require_once('../config/config.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/../models/patientModel.php');

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? '';

// Validates and normalizes date input to Y-m-d, returns empty string if invalid
function sanitizeDate(string $val): string
{
    if (!$val) return '';
    $d = DateTime::createFromFormat('Y-m-d', $val);
    return $d ? $d->format('Y-m-d') : '';
}
// Validates and normalizes time input to H:i:s, returns empty string if invalid
function sanitizeTime(string $val): string
{
    if (!$val) return '';
    $t = DateTime::createFromFormat('H:i', substr($val, 0, 5));
    return $t ? $t->format('H:i:s') : '';
}

switch ($action) {


    case 'get_departments':
        $result = $conn->query("
            SELECT DISTINCT department FROM doctors
            WHERE employmentStatus='Active' AND department IS NOT NULL AND department!=''
            ORDER BY department
        ");
        if (!$result) {
            echo json_encode(['success' => false]);
            break;
        }
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => array_column($rows, 'department')]);
        break;


    case 'get_doctors':
        $dept = $_GET['department'] ?? '';
        $stmt = $conn->prepare("
            SELECT id, CONCAT(firstName,' ',lastName) AS name, specialization, department, patientCapacity, photoUrl
            FROM doctors WHERE employmentStatus='Active' " . ($dept ? "AND department = ?" : "") . "
            ORDER BY lastName, firstName
        ");
        if ($dept) {
            $stmt->bind_param('s', $dept);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'book':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];

        $doctorId        = (int)($body['doctorId']        ?? 0);
        $appointmentDate = sanitizeDate($body['appointmentDate'] ?? '');
        $appointmentTime = sanitizeTime($body['appointmentTime'] ?? '');
        $channel         = in_array($body['channel'] ?? '', ['Walk-in', 'Online', 'Phone', 'Referral']) ? $body['channel'] : 'Online';
        $remarks         = $body['remarks'] ?? '';
        $address         = trim($body['address'] ?? '');

        if (!$doctorId || !$appointmentDate || !$appointmentTime || empty($body['patientName']) || !$address) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
            break;
        }

        if ($appointmentDate < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Appointment date cannot be in the past.']);
            break;
        }

        // Verify doctor exists and is still active before booking
        $docStmt = $conn->prepare("SELECT id FROM doctors WHERE id = ? AND employmentStatus = 'Active'");
        $docStmt->bind_param('i', $doctorId);
        $docStmt->execute();
        $docResult = $docStmt->get_result();
        $doc = $docResult->fetch_assoc();
        if (!$doc) {
            echo json_encode(['success' => false, 'message' => 'Doctor not available.']);
            break;
        }

        // Prevent double-booking the same doctor at the same date and time
        $takenStmt = $conn->prepare("
            SELECT id FROM appointments
            WHERE doctorId = ? AND appointmentDate = ? AND appointmentTime = ?
            AND status NOT IN ('Cancelled')
        ");
        $takenStmt->bind_param('iss', $doctorId, $appointmentDate, $appointmentTime);
        $takenStmt->execute();
        $takenResult = $takenStmt->get_result();
        $taken = $takenResult->fetch_assoc();
        if ($taken) {
            echo json_encode(['success' => false, 'message' => 'This slot is already booked. Please choose another.']);
            break;
        }

        // Reuse existing patient record if email and name match, otherwise create a new one
        $patientId = (int)($body['patientId'] ?? 0);
        if (!$patientId) {
            $email    = trim($body['email']   ?? '');
            $contact  = trim($body['contact'] ?? '');
            $fullName = trim($body['patientName'] ?? 'Unknown');

            $existing = null;
            if ($email) {
                $existingStmt = $conn->prepare("SELECT id, CONCAT(firstName,' ',lastName) AS name FROM patients WHERE emailAddress = ? AND status != 'Inactive' LIMIT 1");
                $existingStmt->bind_param('s', $email);
                $existingStmt->execute();
                $existingResult = $existingStmt->get_result();
                $existing = $existingResult->fetch_assoc();
            }

            // Only reuse existing patient if name also matches (same person)
            if ($existing && strtolower($existing['name']) === strtolower($fullName)) {
                $patientId = (int)$existing['id'];
                $address = trim($body['address'] ?? '');
                if ($address) {
                    $updateAddrStmt = $conn->prepare("UPDATE patients SET address = ? WHERE id = ?");
                    $updateAddrStmt->bind_param('si', $address, $patientId);
                    $updateAddrStmt->execute();
                }
            } else {

                $patientModel = new patientModel($conn);
                $patientId    = $patientModel->createFromBooking([
                    'firstName'  => trim($body['firstName']  ?? ''),
                    'middleName' => trim($body['middleName'] ?? ''),
                    'lastName'   => trim($body['lastName']   ?? ''),
                    'gender'     => $body['gender']       ?? 'Other',
                    'dob'        => !empty($body['dateOfBirth']) ? sanitizeDate($body['dateOfBirth']) : null,
                    'contact'    => $contact,
                    'email'      => $email,
                    'address'    => trim($body['address'] ?? ''),
                ]);
                $fullName = trim(($body['firstName'] ?? '') . ' ' . ($body['middleName'] ?? '') . ' ' . ($body['lastName'] ?? ''));
                logActivity($conn, 'patient', "New patient registered: $fullName", $patientId, 'Patient');
            }
        }

        if (!$patientId) {
            echo json_encode(['success' => false, 'message' => 'Could not resolve patient.']);
            break;
        }

        $code = generateAppointmentCode($conn);
        $sessionUserId = $_SESSION['authUser']['user_id'] ?? 0;
        $address = trim($body['address'] ?? '');
        $insertStmt = $conn->prepare("
            INSERT INTO appointments (appointmentCode, patientId, doctorId, appointmentDate, appointmentTime, channel, status, remarks, address, bookedByUserId)
            VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?)
        ");
        $insertStmt->bind_param('siisssssi', $code, $patientId, $doctorId, $appointmentDate, $appointmentTime, $channel, $remarks, $address, $sessionUserId);
        $insertStmt->execute();
        $newId = $conn->insert_id;

        $patStmt = $conn->prepare("SELECT CONCAT(firstName,' ',lastName) FROM patients WHERE id = ?");
        $patStmt->bind_param('i', $patientId);
        $patStmt->execute();
        $patResult = $patStmt->get_result();
        $patRow = $patResult->fetch_row();
        $patName = $patRow ? $patRow[0] : '';
        logActivity($conn, 'appointment', "Appointment $code booked for $patName via booking form.", $newId, 'Appointment');

        echo json_encode(['success' => true, 'appointmentCode' => $code, 'appointmentId' => $newId, 'message' => 'Appointment booked!']);
        break;


    case 'cancel_appointment':
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false]);
            break;
        }

        $userId = $_SESSION['authUser']['user_id'] ?? 0;
        $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
        $uStmt->bind_param('i', $userId);
        $uStmt->execute();
        $userResult = $uStmt->get_result();
        $userRow = $userResult->fetch_assoc();
        $userEmail = $userRow['emailAddress'] ?? '';

        // Ensure the user can only cancel their own appointments
        $checkStmt = $conn->prepare("
            SELECT a.id, a.appointmentCode, a.appointmentDate, a.status
            FROM appointments a
            JOIN patients p ON p.id = a.patientId
            WHERE a.id = ? AND (a.bookedByUserId = ? OR p.emailAddress = ?)
        ");
        $checkStmt->bind_param('iis', $id, $userId, $userEmail);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $check = $checkResult->fetch_assoc();

        if (!$check) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found.']);
            break;
        }
        if ($check['status'] === 'Cancelled') {
            echo json_encode(['success' => false, 'message' => 'Already cancelled.']);
            break;
        }
        if ($check['appointmentDate'] < date('Y-m-d')) {
            echo json_encode(['success' => false, 'message' => 'Cannot cancel past appointments.']);
            break;
        }

        $updateStmt = $conn->prepare("UPDATE appointments SET status='Cancelled', updatedAt=NOW() WHERE id = ?");
        $updateStmt->bind_param('i', $id);
        $updateStmt->execute();
        logActivity($conn, 'cancel', "Appointment {$check['appointmentCode']} cancelled by patient.", $id, 'Appointment');
        echo json_encode(['success' => true]);
        break;

    case 'my_appointments':
        $userId = $_SESSION['authUser']['user_id'] ?? 0;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'Not logged in.']);
            break;
        }

        $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
        $uStmt->bind_param('i', $userId);
        $uStmt->execute();
        $userResult = $uStmt->get_result();
        $userRow = $userResult->fetch_assoc();
        $userEmail = $userRow['emailAddress'] ?? '';

        $rowsStmt = $conn->prepare("
            SELECT a.id, a.appointmentCode, a.appointmentDate, a.appointmentTime,
                   a.channel, a.status, a.remarks,
                   CONCAT(p.firstName, ' ', p.lastName) AS patientName,
                   CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
                   d.specialization, d.department
            FROM appointments a
            JOIN patients p ON p.id = a.patientId
            JOIN doctors  d ON d.id = a.doctorId
            WHERE a.bookedByUserId = ? OR p.emailAddress = ?
            ORDER BY a.appointmentDate DESC, a.appointmentTime DESC
        ");
        $rowsStmt->bind_param('is', $userId, $userEmail);
        $rowsStmt->execute();
        $rowsResult = $rowsStmt->get_result();
        $rows = $rowsResult->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get_doctor_schedule':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        if (!$doctorId) {
            echo json_encode(['success' => false]);
            break;
        }
        echo json_encode(['success' => true, 'data' => getDoctorSchedule($conn, $doctorId)]);
        break;

    case 'get_slots':
        $doctorId = (int)($_GET['doctorId'] ?? 0);
        $date     = $conn->real_escape_string($_GET['date'] ?? '');
        if (!$doctorId || !$date) {
            echo json_encode(['success' => true, 'slots' => []]);
            break;
        }
        $slots = getAvailableSlots($conn, $doctorId, $date);
        echo json_encode($slots ? ['success' => true, 'slots' => $slots]
            : ['success' => true, 'slots' => [], 'message' => 'Doctor not available this day.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
