<?php
require_once('../../app/config/config.php');

// Fetches a single user record by ID
function getUserById($conn, $userId)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

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
        // Ensure the new username isn't already taken by another account
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $checkStmt->bind_param("si", $username, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $error = "Username is already taken.";
        } else {
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
                    $uploadDir  = __DIR__ . '/../../app/uploads/profiles/';
                    $uploadUrl  = '/Clinic_Appointment_System/app/uploads/profiles/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext      = pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION);
                    $fileName = 'user_' . $userId . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $uploadDir . $fileName)) {
                        $profilePic = $uploadUrl . $fileName;
                    } else {
                        $error = "Failed to upload image. Tried path: " . ($uploadDir . $fileName);
                    }
                }
            }

            if (empty($error)) {
                if (!empty($newPassword)) {
                    if ($newPassword !== $confirmPass) {
                        $error = "Passwords do not match.";
                    } elseif (strlen($newPassword) < 6) {
                        $error = "Password must be at least 6 characters.";
                    }
                }

                if (empty($error)) {
                    $fields = [
                        'firstName'    => $firstName,
                        'middleName'   => $middleName,
                        'lastName'     => $lastName,
                        'username'     => $username,
                        'emailAddress' => $email,
                        'street'       => $street,
                        'barangay'     => $barangay,
                        'city'         => $city,
                    ];

                    if ($profilePic)        $fields['profilePic'] = $profilePic;
                    if (!empty($newPassword)) $fields['password']  = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Build the UPDATE query dynamically so password and photo are only included if provided
                    $setClauses = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
                    $values     = array_values($fields);
                    $values[]   = $userId;
                    $types      = str_repeat('s', count($fields)) . 'i';

                    $stmt = $conn->prepare("UPDATE users SET $setClauses WHERE id = ?");
                    $stmt->bind_param($types, ...$values);
                }

                if (empty($error)) {
                    if (isset($stmt) && $stmt->execute()) {
                        $_SESSION['authUser']['fullName'] = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                        $_SESSION['authUser']['username'] = $username;
                        $_SESSION['profile_success'] = "Profile updated successfully.";

                        // Re-fetch profilePic from DB to keep the session in sync after update
                        $syncStmt = $conn->prepare("SELECT profilePic FROM users WHERE id = ?");
                        $syncStmt->bind_param("i", $userId);
                        $syncStmt->execute();
                        $syncData = $syncStmt->get_result()->fetch_assoc();
                        $_SESSION['authUser']['profilePic'] = $syncData['profilePic'] ?? null;

                        echo "<script>window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
                        exit();
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                }
            }
        }
    }
}

// Load fresh user data to populate the profile form
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
