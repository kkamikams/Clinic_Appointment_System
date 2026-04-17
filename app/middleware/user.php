<?php
session_start();
$root = dirname(__DIR__);
include_once($root . '/config/config.php');

if (!isset($_SESSION['authUser'])) {
    $_SESSION['message'] = "You must be logged in to access this page.";
    $_SESSION['code'] = "error";
    header("Location: /Clinic_Appointment_System/public/login");
    exit();
} else {
    if ($_SESSION['userRole'] !== 'user') {
        $_SESSION['message'] = "You do not have permission to access this page.";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/login");
        exit();
    }
}
