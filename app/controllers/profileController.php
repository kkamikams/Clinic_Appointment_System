<?php
require_once('../../app/config/config.php');

if (!isset($_SESSION['authUser']['user_id'])) {
    echo "<script>window.location.href='../login.php';</script>";
    exit();
}

$userId  = $_SESSION['authUser']['user_id'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName  = trim($_POST['firstName']);
    $middleName = trim($_POST['middleName']);
    $lastName   = trim($_POST['lastName']);
    $username   = trim($_POST['username']);
    $email      = trim($_POST['emailAddress']);
    $street     = trim($_POST['street']);
    $barangay   = trim($_POST['barangay']);
    $city       = trim($_POST['city']);
    $newPassword = trim($_POST['newPassword']);
    $confirmPass = trim($_POST['confirmPassword']);

    if (empty($firstName) || empty($lastName) || empty($username) || empty($email)) {
        $error = "First name, last name, username, and email are required.";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Username is already taken.";
        } else {
            // Handle profile picture upload
            $profilePic = null;
            if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['profilePic']['type'];
                $fileSize = $_FILES['profilePic']['size'];

                if (!in_array($fileType, $allowedTypes)) {
                    $error = "Only JPG, PNG, GIF, and WEBP images are allowed.";
                } elseif ($fileSize > 2 * 1024 * 1024) {
                    $error = "Image size must be under 2MB.";
                } else {
                    $userRole   = $_SESSION['authUser']['role'] ?? $_SESSION['userRole'] ?? 'user';
                    $panel      = ($userRole === 'admin') ? 'admin' : 'user';
                    $uploadDir  = __DIR__ . '/../../public/' . $panel . '/assets/uploads/profiles/';
                    $uploadUrl  = '/Clinic_Appointment_System/public/' . $panel . '/assets/uploads/profiles/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext      = pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION);
                    $fileName = 'user_' . $userId . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $uploadDir . $fileName)) {
                        $profilePic = $uploadUrl . $fileName;
                    } else {
                        $error = "Failed to upload image. Tried path: " . ($uploadDir . $fileName);  // <-- changed
                    }
                }
            }

            if (empty($error)) {
                // Handle password change
                if (!empty($newPassword)) {
                    if ($newPassword !== $confirmPass) {
                        $error = "Passwords do not match.";
                    } elseif (strlen($newPassword) < 6) {
                        $error = "Password must be at least 6 characters.";
                    } else {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        if ($profilePic) {
                            $stmt = $conn->prepare("UPDATE users SET firstName=?, middleName=?, lastName=?, username=?, emailAddress=?, street=?, barangay=?, city=?, password=?, profilePic=? WHERE id=?");
                            $stmt->bind_param("ssssssssssi", $firstName, $middleName, $lastName, $username, $email, $street, $barangay, $city, $hashedPassword, $profilePic, $userId);
                        } else {
                            $stmt = $conn->prepare("UPDATE users SET firstName=?, middleName=?, lastName=?, username=?, emailAddress=?, street=?, barangay=?, city=?, password=? WHERE id=?");
                            $stmt->bind_param("sssssssssi", $firstName, $middleName, $lastName, $username, $email, $street, $barangay, $city, $hashedPassword, $userId);
                        }
                    }
                } else {
                    if ($profilePic) {
                        $stmt = $conn->prepare("UPDATE users SET firstName=?, middleName=?, lastName=?, username=?, emailAddress=?, street=?, barangay=?, city=?, profilePic=? WHERE id=?");
                        $stmt->bind_param("sssssssssi", $firstName, $middleName, $lastName, $username, $email, $street, $barangay, $city, $profilePic, $userId);
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET firstName=?, middleName=?, lastName=?, username=?, emailAddress=?, street=?, barangay=?, city=? WHERE id=?");
                        $stmt->bind_param("ssssssssi", $firstName, $middleName, $lastName, $username, $email, $street, $barangay, $city, $userId);
                    }
                }

                if (empty($error)) {
                    if ($stmt->execute()) {
                        $_SESSION['authUser']['fullName'] = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                        $_SESSION['authUser']['username'] = $username;
                        $success = "Profile updated successfully.";

                        // Always sync profilePic from DB after update
                        $syncStmt = $conn->prepare("SELECT profilePic FROM users WHERE id = ?");
                        $syncStmt->bind_param("i", $userId);
                        $syncStmt->execute();
                        $syncData = $syncStmt->get_result()->fetch_assoc();
                        $_SESSION['authUser']['profilePic'] = $syncData['profilePic'] ?? null;
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                }
            }
        }
    }
}

// Fetch latest user data
$fetchStmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$fetchStmt->bind_param("i", $userId);
$fetchStmt->execute();
$user = $fetchStmt->get_result()->fetch_assoc();

if (!$user) {
    echo "<script>window.location.href='../login.php';</script>";
    exit();
}

$fullName   = trim(
    (isset($user['firstName']) ? $user['firstName'] : '') . ' ' .
        (isset($user['middleName']) ? $user['middleName'] : '') . ' ' .
        (isset($user['lastName']) ? $user['lastName'] : '')
);
$initials   = strtoupper(
    substr(isset($user['firstName']) ? $user['firstName'] : 'U', 0, 1) .
        substr(isset($user['lastName']) ? $user['lastName'] : 'U', 0, 1)
);
$dateJoined = (!empty($user['dateCreated'])) ? date('F j, Y', strtotime($user['dateCreated'])) : 'N/A';
$dateJoined = (!empty($user['createdAt'])) ? date('F j, Y', strtotime($user['createdAt'])) : 'N/A';
