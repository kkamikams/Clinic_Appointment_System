<?php
date_default_timezone_set('Asia/Manila');
$host = "localhost";
$user = "root";
$pass = "";
$db   = "clinic_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
