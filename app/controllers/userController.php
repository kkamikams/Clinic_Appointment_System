<?php
require_once(__DIR__ . '/../config/config.php');

if (isset($_POST['logoutButton'])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: /Clinic_Appointment_System/public/login");
    exit();
}

// Fetches all data needed for the user dashboard: stats, next appointment, recent records, and available doctors
function getDashboardData($conn, $userId, $userEmail)
{
    $today = date('Y-m-d');

    $stmt = $conn->prepare("SELECT * FROM patients WHERE emailAddress = ? AND status != 'Inactive' LIMIT 1");
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
    $patientId  = $patientRow['id'] ?? 0;

    // No linked patient record found — return zeroed-out dashboard data
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

// Fetches appointment stats and full list (including follow-ups) for the logged-in user
function getMyAppointmentsData($conn, $userId, $today)
{
    $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
    $uStmt->bind_param('i', $userId);
    $uStmt->execute();
    $userEmail = $uStmt->get_result()->fetch_row()[0] ?? '';

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments a JOIN patients p ON p.id = a.patientId WHERE a.bookedByUserId = ? OR p.emailAddress = ?");
    $stmt->bind_param('is', $userId, $userEmail);
    $stmt->execute();
    $statTotal = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments a JOIN patients p ON p.id = a.patientId WHERE (a.bookedByUserId = ? OR p.emailAddress = ?) AND a.appointmentDate >= ? AND a.status IN ('Pending','In Progress')");
    $stmt->bind_param('iss', $userId, $userEmail, $today);
    $stmt->execute();
    $statUpcoming = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments a JOIN patients p ON p.id = a.patientId WHERE (a.bookedByUserId = ? OR p.emailAddress = ?) AND a.status = 'Completed'");
    $stmt->bind_param('is', $userId, $userEmail);
    $stmt->execute();
    $statCompleted = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments a JOIN patients p ON p.id = a.patientId WHERE (a.bookedByUserId = ? OR p.emailAddress = ?) AND a.status = 'Cancelled'");
    $stmt->bind_param('is', $userId, $userEmail);
    $stmt->execute();
    $statCancelled = $stmt->get_result()->fetch_row()[0];

    $stmt = $conn->prepare("
        SELECT a.id, a.appointmentCode, a.appointmentDate, a.appointmentTime,
               a.channel, a.status, a.remarks,
               CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
               d.specialization, d.department,
               CONCAT(p.firstName, ' ', p.lastName) AS patientName,
               COALESCE(p.photoUrl, IF(u.firstName = p.firstName AND u.lastName = p.lastName, u.profilePic, NULL)) AS patPhoto
        FROM appointments a
        JOIN doctors  d ON d.id = a.doctorId
        JOIN patients p ON p.id = a.patientId
        LEFT JOIN users u ON u.emailAddress = p.emailAddress
        WHERE a.bookedByUserId = ? OR p.emailAddress = ?

        UNION ALL

        SELECT fu.id, fu.followUpCode AS appointmentCode, fu.followUpDate AS appointmentDate,
               fu.followUpTime AS appointmentTime,
               'Follow-up' AS channel, IFNULL(fu.status, 'Pending') AS status,
               fu.reason AS remarks,
               CONCAT('Dr. ', COALESCE(fd.firstName, ad.firstName, ''), ' ', COALESCE(fd.lastName, ad.lastName, '')) AS doctorName,
               COALESCE(fd.specialization, ad.specialization, '—') AS specialization,
               COALESCE(fd.department, ad.department, '—') AS department,
               CONCAT(p.firstName, ' ', p.lastName) AS patientName,
               COALESCE(p.photoUrl, IF(u.firstName = p.firstName AND u.lastName = p.lastName, u.profilePic, NULL)) AS patPhoto
        FROM followUps fu
        JOIN appointments a  ON a.id  = fu.appointmentId
        JOIN patients     p  ON p.id  = fu.patientId
        LEFT JOIN users   u  ON u.emailAddress = p.emailAddress
        LEFT JOIN doctors fd ON fd.id = fu.doctorId
        LEFT JOIN doctors ad ON ad.id = a.doctorId
        WHERE a.bookedByUserId = ? OR p.emailAddress = ?

        ORDER BY appointmentDate DESC, appointmentTime DESC
    ");
    $stmt->bind_param('isis', $userId, $userEmail, $userId, $userEmail);
    $stmt->execute();
    $appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    return compact('statTotal', 'statUpcoming', 'statCompleted', 'statCancelled', 'appointments');
}

// Fetches finalized medical records and stats for all patients linked to the logged-in user
function getMedicalRecordsData($conn, $userId)
{
    $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
    $uStmt->bind_param('i', $userId);
    $uStmt->execute();
    $userEmail = $uStmt->get_result()->fetch_row()[0] ?? '';

    $stmt = $conn->prepare("
    SELECT DISTINCT p.id AS patientId FROM patients p
    WHERE p.emailAddress = ? AND p.status != 'Inactive'

    UNION

    SELECT DISTINCT a.patientId FROM appointments a
    JOIN patients p ON p.id = a.patientId
    WHERE a.bookedByUserId = ? AND p.status != 'Inactive'
");
    $stmt->bind_param('si', $userEmail, $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $patientIds = array_column($rows, 'patientId');

    if (empty($patientIds)) {
        return ['statTotal' => 0, 'statMonth' => 0, 'statDoctors' => 0, 'statDepts' => 0, 'records' => []];
    }

    $ids = implode(',', array_map('intval', $patientIds));

    $statTotal   = $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE patientId IN ($ids) AND status = 'Finalized'")->fetch_row()[0];
    $statMonth   = $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE patientId IN ($ids) AND status = 'Finalized' AND MONTH(createdAt)=MONTH(CURDATE()) AND YEAR(createdAt)=YEAR(CURDATE())")->fetch_row()[0];
    $statDoctors = $conn->query("SELECT COUNT(DISTINCT doctorId) FROM medicalRecords WHERE patientId IN ($ids) AND status = 'Finalized'")->fetch_row()[0];
    $statDepts   = $conn->query("SELECT COUNT(DISTINCT d.specialization) FROM medicalRecords mr JOIN doctors d ON d.id = mr.doctorId WHERE mr.patientId IN ($ids) AND mr.status = 'Finalized'")->fetch_row()[0];

    // Only get parent records (one per patient), with latest diagnosis info
    $records = $conn->query("
    SELECT 
        m.id, m.recordCode, m.status, m.createdAt, m.patientId,
        CONCAT(p.firstName, ' ', p.lastName) AS patientName,
        p.patientCode, COALESCE(p.photoUrl, IF(u.firstName = p.firstName AND u.lastName = p.lastName, u.profilePic, NULL)) AS patPhoto,
        CONCAT('Dr. ', d.firstName, ' ', d.lastName) AS doctorName,
        d.specialization, d.department,
        (SELECT COUNT(*) FROM medicalRecords c WHERE c.parentRecordId = m.id) + 1 AS entryCount,
        (SELECT c2.diagnosis FROM medicalRecords c2
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS latestDiagnosis,
        (SELECT c2.recordType FROM medicalRecords c2
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS latestType,
        (SELECT COALESCE(c2.updatedAt, c2.createdAt) FROM medicalRecords c2
         WHERE c2.parentRecordId = m.id OR c2.id = m.id
         ORDER BY c2.createdAt DESC LIMIT 1) AS lastUpdated
    FROM medicalRecords m
    JOIN patients p ON p.id = m.patientId
    JOIN doctors  d ON d.id = m.doctorId
    LEFT JOIN users u ON u.emailAddress = p.emailAddress
    WHERE m.patientId IN ($ids) AND m.status = 'Finalized' AND m.parentRecordId IS NULL
    ORDER BY lastUpdated DESC
")->fetch_all(MYSQLI_ASSOC);

    return compact('statTotal', 'statMonth', 'statDoctors', 'statDepts', 'records');
}

// Fetches the user's linked patient record, active doctors, and specializations for the booking form
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
