<?php session_start();
$root = dirname(__DIR__);
include_once($root . "/config/config.php");

if (isset($_POST['logoutButton'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['userRole']);
    unset($_SESSION['authUser']);
    session_destroy();
    header("Location: /Clinic_Appointment_System/public/login");
    exit(0);
}
