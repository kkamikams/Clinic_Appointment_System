<?php
require_once(__DIR__ . '/../config/config.php');

if (isset($_POST['logoutButton'])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: /Clinic_Appointment_System/public/login");
    exit();
}

function getDashboardData($conn, $userId, $userEmail)
{
    $today = date('Y-m-d');

    $stmt = $conn->prepare("SELECT * FROM patients WHERE emailAddress = ? AND status != 'Inactive' LIMIT 1");
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
    $patientId  = $patientRow['id'] ?? 0;

    if (!$patientId) {
        $chartMonths = [];
        for ($i = 5; $i >= 0; $i--)
            $chartMonths[] = ['label' => date('M', strtotime("-$i months")), 'count' => 0];

        return [
            'patientRow'     => null,
            'patientId'      => 0,
            'totalAppts'     => 0,
            'upcomingAppts'  => 0,
            'pendingAppts'   => 0,
            'completedAppts' => 0,
            'totalRecords'   => 0,
            'nextAppt'       => null,
            'recentAppts'    => [],
            'recentRecords'  => [],
            'chartMonths'    => $chartMonths,
            'availDoctors'   => [],
        ];
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patientId=?");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $totalAppts = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patientId=? AND appointmentDate>=? AND status IN ('Pending','In Progress')");
    $stmt->bind_param('is', $patientId, $today);
    $stmt->execute();
    $upcomingAppts = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patientId=? AND status='Pending'");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $pendingAppts = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patientId=? AND status='Completed'");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $completedAppts = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM medicalRecords WHERE patientId=?");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $totalRecords = $stmt->get_result()->fetch_row()[0];

    $nextApptStmt = $conn->prepare("
        SELECT a.*, CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName, d.specialization
        FROM appointments a JOIN doctors d ON d.id=a.doctorId
        WHERE a.patientId = ? AND a.appointmentDate >= ? AND a.status IN ('Pending','In Progress')
        ORDER BY a.appointmentDate ASC, a.appointmentTime ASC LIMIT 1
    ");
    $nextApptStmt->bind_param('is', $patientId, $today);
    $nextApptStmt->execute();
    $nextApptResult = $nextApptStmt->get_result();
    $nextAppt = $nextApptResult->fetch_assoc();

    $recentApptsStmt = $conn->prepare("
        SELECT a.*, CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName, d.specialization
        FROM appointments a JOIN doctors d ON d.id=a.doctorId
        WHERE a.patientId = ?
        ORDER BY a.appointmentDate DESC, a.appointmentTime DESC LIMIT 5
    ");
    $recentApptsStmt->bind_param('i', $patientId);
    $recentApptsStmt->execute();
    $recentApptsResult = $recentApptsStmt->get_result();
    $recentAppts = $recentApptsResult->fetch_all(MYSQLI_ASSOC);

    $recentRecordsStmt = $conn->prepare("
        SELECT mr.*, CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName, d.specialization
        FROM medicalRecords mr JOIN doctors d ON d.id=mr.doctorId
        WHERE mr.patientId = ?
        ORDER BY mr.createdAt DESC LIMIT 3
    ");
    $recentRecordsStmt->bind_param('i', $patientId);
    $recentRecordsStmt->execute();
    $recentRecordsResult = $recentRecordsStmt->get_result();
    $recentRecords = $recentRecordsResult->fetch_all(MYSQLI_ASSOC);

    $chartMonths = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $cntStmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE patientId = ? AND DATE_FORMAT(appointmentDate,'%Y-%m') = ?");
        $cntStmt->bind_param('is', $patientId, $m);
        $cntStmt->execute();
        $cntResult = $cntStmt->get_result();
        $cntRow = $cntResult->fetch_row();
        $cnt = $cntRow ? $cntRow[0] : 0;
        $chartMonths[] = ['label' => date('M', strtotime("-$i months")), 'count' => (int)$cnt];
    }

    $dayOfWeek = date('l', strtotime($today)); // e.g., 'Monday'
    $availDoctorsStmt = $conn->prepare("
        SELECT d.id, d.firstName, d.lastName, d.specialization, d.department, d.status,
               COUNT(DISTINCT a.id) AS todayLoad, d.patientCapacity
        FROM doctors d
        LEFT JOIN appointments a ON a.doctorId=d.id AND a.appointmentDate = ? AND a.status != 'Cancelled'
        LEFT JOIN doctorSchedules ds ON ds.doctorId=d.id AND ds.dayOfWeek = ?
        WHERE d.employmentStatus='Active' AND ds.id IS NOT NULL AND d.status != 'Off Duty'
        GROUP BY d.id
        ORDER BY d.status='On Duty' DESC, d.lastName
        LIMIT 6
    ");
    $availDoctorsStmt->bind_param('ss', $today, $dayOfWeek);
    $availDoctorsStmt->execute();
    $availDoctorsResult = $availDoctorsStmt->get_result();
    $availDoctors = $availDoctorsResult->fetch_all(MYSQLI_ASSOC);

    return compact(
        'patientRow',
        'patientId',
        'totalAppts',
        'upcomingAppts',
        'pendingAppts',
        'completedAppts',
        'totalRecords',
        'nextAppt',
        'recentAppts',
        'recentRecords',
        'chartMonths',
        'availDoctors'
    );
}

function getMyAppointmentsData($conn, $userId, $today)
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $statTotal = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=? AND appointmentDate>=? AND status IN ('Pending','In Progress')");
    $stmt->bind_param('is', $userId, $today);
    $stmt->execute();
    $statUpcoming = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=? AND status='Completed'");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $statCompleted = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=? AND status='Cancelled'");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $statCancelled = $stmt->get_result()->fetch_row()[0];
    $appointments  = $conn->query("
        SELECT a.*,
               CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName,
               d.specialization, d.department,
               CONCAT(p.firstName,' ',p.lastName) AS patientName
        FROM appointments a
        JOIN doctors d ON d.id = a.doctorId
        JOIN patients p ON p.id = a.patientId
        WHERE a.bookedByUserId = $userId
        ORDER BY a.appointmentDate DESC, a.appointmentTime DESC
    ")->fetch_all(MYSQLI_ASSOC);

    return compact('statTotal', 'statUpcoming', 'statCompleted', 'statCancelled', 'appointments');
}

function getMedicalRecordsData($conn, $userId)
{
    // Get patientId from appointments booked by this user
    $stmt = $conn->prepare("
        SELECT DISTINCT patientId FROM appointments 
        WHERE bookedByUserId = ? LIMIT 1
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $patientId = (int)($stmt->get_result()->fetch_row()[0] ?? 0);

    if (!$patientId) {
        return ['statTotal' => 0, 'statMonth' => 0, 'statDoctors' => 0, 'statDepts' => 0, 'records' => []];
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM medicalRecords WHERE patientId = ? AND status = 'Finalized'");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $statTotal = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM medicalRecords WHERE patientId = ? AND status = 'Finalized' AND MONTH(createdAt)=MONTH(CURDATE()) AND YEAR(createdAt)=YEAR(CURDATE())");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $statMonth = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(DISTINCT doctorId) FROM medicalRecords WHERE patientId = ? AND status = 'Finalized'");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $statDoctors = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(DISTINCT d.specialization) FROM medicalRecords mr JOIN doctors d ON d.id = mr.doctorId WHERE mr.patientId = ? AND mr.status = 'Finalized'");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $statDepts = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("
        SELECT mr.*,
               CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
               d.specialization, d.department,
               CONCAT(p.firstName, ' ', p.lastName) AS patientName,
               CASE WHEN mr.followUpId IS NOT NULL THEN 1 ELSE 0 END AS isFollowUp
        FROM medicalRecords mr
        JOIN doctors  d ON d.id = mr.doctorId
        JOIN patients p ON p.id = mr.patientId
        WHERE mr.patientId = ? AND mr.status = 'Finalized'
        ORDER BY mr.createdAt DESC
    ");
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return compact('statTotal', 'statMonth', 'statDoctors', 'statDepts', 'records');
}

function getBookAppointmentData($conn, $userEmail)
{
    $stmt = $conn->prepare("SELECT * FROM patients WHERE emailAddress = ? AND status != 'Inactive' LIMIT 1");
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();

    $doctors = $conn->query("
        SELECT id, CONCAT(firstName,' ',lastName) AS name,
               specialization, department, patientCapacity
        FROM doctors WHERE employmentStatus='Active'
        ORDER BY lastName, firstName
    ")->fetch_all(MYSQLI_ASSOC);

    $specializations = $conn->query("
        SELECT DISTINCT specialization FROM doctors
        WHERE employmentStatus='Active'
        AND specialization IS NOT NULL AND specialization != ''
        ORDER BY specialization
    ")->fetch_all(MYSQLI_ASSOC);

    return compact('patientRow', 'doctors', 'specializations');
}
