<?php session_start();
$root = dirname(__DIR__);
include_once('../../app/config/config.php');

function generate_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}
if (isset($_POST['loginButton'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $loginQuery = "SELECT `id`, `firstName`, `lastName`, `emailAddress`, `role` 
               FROM `users` 
               WHERE `username` = ? AND `password` = ? 
               LIMIT 1";
    $stmt = $conn->prepare($loginQuery);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);

            $user_id = $data['id'];
            $fullName = $data['firstName'] . ' ' . $data['lastName'];
            $username = $data['username'];
            $userRole = $data['role'];

            $_SESSION['user_id'] = $user_id;
            $_SESSION['userRole'] = $userRole;
            $_SESSION['authUser'] = [
                'user_id' => $user_id,
                'fullName' => $fullName,
                'username' => $username,
            ];

            $_SESSION['message'] = "Welcome $fullName";
            $_SESSION['code'] = "success";

            if ($userRole === 'admin') {
                header("Location: /Clinic_Appointment_System/public/admin/index");
                exit();
            } else {
                header("Location: /Clinic_Appointment_System/public/user/index");
                exit();
            }
        } else {
            $_SESSION['message'] = "Invalid username or password";
            $_SESSION['code'] = "error";
            header("Location: /Clinic_Appointment_System/public/login");
            exit();
        }
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/login");
        exit();
    }
}

if (isset($_POST['registerButton'])) {
    $firstName = $_POST['firstName'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $emailAddress = $_POST['emailAddress'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $street = $_POST['street'];
    $barangay = $_POST['barangay'];
    $city = $_POST['city'];
    $role = 'user';
    $uuid = generate_uuid();

    //validate email format
    if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/registration");
        exit();
    }
    //check if email already exists
    $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE emailAddress = '$emailAddress' LIMIT 1");
    if ($checkEmail && mysqli_num_rows($checkEmail) > 0) {
        $_SESSION['message'] = "Email already exists";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/registration");
        exit();
    }
    //check if username already exists
    $checkUsername = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if ($checkUsername && mysqli_num_rows($checkUsername) > 0) {
        $_SESSION['message'] = "Username already exists";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/registration");
        exit();
    }
    //check if password and confirm password match
    if ($password !== $confirmPassword) {
        $_SESSION['message'] = "Password do not match";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/registration");
        exit();
    }

    //insert user data into database
    $query = "INSERT INTO `users`(`uuid`, `firstName`, `middleName`, `lastName`, `emailAddress`, `username`, `password`, `street`, `barangay`, `city`, `role`) VALUES ('$uuid','$firstName','$middleName','$lastName','$emailAddress','$username','$password','$street','$barangay','$city','$role')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Registration successful. Please login.";
        $_SESSION['code'] = "success";
        header("Location: /Clinic_Appointment_System/public/login");
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        $_SESSION['code'] = "error";
        header("Location: /Clinic_Appointment_System/public/registration");
        exit();
    }
}
