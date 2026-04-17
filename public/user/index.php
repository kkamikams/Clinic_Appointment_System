<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /Clinic_Appointment_System/public/login.php');
    exit();
}

if ($_SESSION['userRole'] !== 'user') {
    $_SESSION['message'] = 'You do not have permission to access this page.';
    $_SESSION['code'] = 'error';
    header('Location: /Clinic_Appointment_System/public/admin/index');
    exit();
}

include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');


$userEmail = $_SESSION['email'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

$patientRow = null;
if ($userId) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE emailAddress = ? AND status != 'Inactive' LIMIT 1");
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
}
$patientId = $patientRow['id'] ?? 0;
$patientName = $patientRow ? trim($patientRow['firstName'] . ' ' . $patientRow['lastName']) : ($_SESSION['name'] ?? 'Patient');

$today = date('Y-m-d');


if ($patientId) {
    $totalAppts    = $conn->query("SELECT COUNT(*) FROM appointments WHERE patientId=$patientId")->fetch_row()[0];
    $upcomingAppts = $conn->query("SELECT COUNT(*) FROM appointments WHERE patientId=$patientId AND appointmentDate >= '$today' AND status IN ('Pending','In Progress')")->fetch_row()[0];
    $pendingAppts  = $conn->query("SELECT COUNT(*) FROM appointments WHERE patientId=$patientId AND status='Pending'")->fetch_row()[0];
    $completedAppts = $conn->query("SELECT COUNT(*) FROM appointments WHERE patientId=$patientId AND status='Completed'")->fetch_row()[0];
    $totalRecords  = $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE patientId=$patientId")->fetch_row()[0];

    // Next upcoming appointment
    $nextAppt = $conn->query("
        SELECT a.*, CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName, d.specialization
        FROM appointments a JOIN doctors d ON d.id=a.doctorId
        WHERE a.patientId=$patientId AND a.appointmentDate >= '$today' AND a.status IN ('Pending','In Progress')
        ORDER BY a.appointmentDate ASC, a.appointmentTime ASC LIMIT 1
    ")->fetch_assoc();

    // Recent appointments (last 5)
    $recentAppts = $conn->query("
        SELECT a.*, CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName, d.specialization
        FROM appointments a JOIN doctors d ON d.id=a.doctorId
        WHERE a.patientId=$patientId
        ORDER BY a.appointmentDate DESC, a.appointmentTime DESC LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // Recent medical records
    $recentRecords = $conn->query("
        SELECT mr.*, CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName, d.specialization
        FROM medicalRecords mr JOIN doctors d ON d.id=mr.doctorId
        WHERE mr.patientId=$patientId
        ORDER BY mr.createdAt DESC LIMIT 3
    ")->fetch_all(MYSQLI_ASSOC);

    // Chart
    $chartMonths = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("-$i months"));
        $cnt = $conn->query("SELECT COUNT(*) FROM appointments WHERE patientId=$patientId AND DATE_FORMAT(appointmentDate,'%Y-%m')='$m'")->fetch_row()[0];
        $chartMonths[] = ['label' => date('M', strtotime("-$i months")), 'count' => (int)$cnt];
    }
} else {
    $totalAppts = $upcomingAppts = $pendingAppts = $completedAppts = $totalRecords = 0;
    $nextAppt = null;
    $recentAppts = [];
    $recentRecords = [];
    $chartMonths = [];
    for ($i = 5; $i >= 0; $i--) $chartMonths[] = ['label' => date('M', strtotime("-$i months")), 'count' => 0];
}

$availDoctors = $conn->query("
    SELECT d.id, d.firstName, d.lastName, d.specialization, d.department, d.status,
           COUNT(DISTINCT a.id) AS todayLoad, d.patientCapacity
    FROM doctors d
    LEFT JOIN appointments a ON a.doctorId=d.id AND a.appointmentDate='$today' AND a.status!='Cancelled'
    LEFT JOIN doctorSchedules ds ON ds.doctorId=d.id AND ds.dayOfWeek=DAYNAME('$today')
    WHERE d.employmentStatus='Active' AND ds.id IS NOT NULL
    GROUP BY d.id
    ORDER BY d.status='On Duty' DESC, d.lastName
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$avatarBgs    = ['#dbeafe', '#d1fae5', '#fef3c7', '#ede9fe', '#fce7f3', '#cffafe'];
$avatarColors = ['#1d4ed8', '#065f46', '#92400e', '#5b21b6', '#9d174d', '#155e75'];
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300;1,9..40,400&display=swap');

    :root {
        --blue-50: #eff6ff;
        --blue-100: #dbeafe;
        --blue-200: #bfdbfe;
        --blue-400: #60a5fa;
        --blue-500: #3b82f6;
        --blue-600: #2563eb;
        --blue-700: #1d4ed8;
        --surface: #f5f7fb;
        --card: #ffffff;
        --border: #eaecf4;
        --text-dark: #111827;
        --text-body: #4b5563;
        --text-muted: #9ca3af;
        --green: #10b981;
        --green-light: #d1fae5;
        --green-dark: #065f46;
        --amber: #f59e0b;
        --amber-light: #fef3c7;
        --amber-dark: #92400e;
        --red: #ef4444;
        --red-light: #fee2e2;
        --red-dark: #991b1b;
        --teal: #06b6d4;
        --teal-light: #cffafe;
        --teal-dark: #155e75;
        --violet: #8b5cf6;
        --violet-light: #ede9fe;
        --violet-dark: #5b21b6;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
    }

    .section.dashboard,
    .section.dashboard * {
        font-family: 'DM Sans', sans-serif;
        box-sizing: border-box;
    }

    .section.dashboard {
        background: var(--surface);
        padding-bottom: 2.5rem;
    }

    .pagetitle h1 {
        font-weight: 700;
        font-size: 1.75rem;
        color: var(--text-dark);
        letter-spacing: -.03em;
        margin-bottom: 2px;
    }

    .pagetitle .breadcrumb-item,
    .pagetitle .breadcrumb-item a {
        font-size: .78rem;
        color: var(--text-muted);
        text-decoration: none;
    }

    .pagetitle .breadcrumb-item.active {
        color: var(--blue-600);
        font-weight: 600;
    }

    /* ── Next appointment highlight ── */
    .next-appt-card {
        background: linear-gradient(135deg, #ecfdf5, #fff);
        border: 1px solid #a7f3d0;
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        animation: fadeUp .32s .05s ease both;
    }

    .next-appt-card .na-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: var(--green);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .next-appt-card .na-label {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--green-dark);
        margin-bottom: 3px;
    }

    .next-appt-card .na-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .next-appt-card .na-sub {
        font-size: .78rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .next-appt-card .na-badge {
        background: var(--green-light);
        color: var(--green-dark);
        border-radius: 6px;
        font-size: .65rem;
        font-weight: 700;
        padding: 3px 10px;
        letter-spacing: .04em;
        margin-left: 6px;
    }

    /* ── Stat cards ── */
    .stat-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 1.25rem;
    }

    @media(max-width:768px) {
        .stat-strip {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.1rem 1.25rem;
        border-left: 3px solid transparent;
        transition: box-shadow .2s, transform .2s;
        animation: fadeUp .32s ease both;
        text-decoration: none;
        display: block;
    }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .stat-card:nth-child(1) {
        border-left-color: var(--blue-500);
        animation-delay: .04s;
    }

    .stat-card:nth-child(2) {
        border-left-color: var(--green);
        animation-delay: .09s;
    }

    .stat-card:nth-child(3) {
        border-left-color: var(--amber);
        animation-delay: .14s;
    }

    .stat-card:nth-child(4) {
        border-left-color: var(--violet);
        animation-delay: .19s;
    }

    .sc-label {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-muted);
        margin-bottom: .45rem;
    }

    .sc-num {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -.05em;
        line-height: 1;
    }

    .sc-sub {
        font-size: .7rem;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    /* ── Card base ── */
    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        transition: box-shadow .2s, transform .2s;
        overflow: hidden;
    }

    .card:hover {
        box-shadow: var(--shadow-md);
    }

    .card-body {
        padding: 1.4rem 1.5rem;
    }

    .card-title {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-body);
        margin-bottom: 1rem;
    }

    .card-title span {
        font-size: .7rem;
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: var(--text-muted);
        margin-left: 4px;
    }

    /* ── Table ── */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead th {
        font-size: .65rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border) !important;
        padding: .65rem .5rem;
        background: transparent;
    }

    .table tbody td {
        font-size: .83rem;
        color: var(--text-body);
        vertical-align: middle;
        border-color: var(--border);
        padding: .7rem .5rem;
    }

    .table tbody tr:hover td {
        background: var(--blue-50);
    }

    /* ── Badges ── */
    .badge {
        font-family: 'DM Sans', sans-serif;
        font-size: .65rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 9px;
        letter-spacing: .03em;
    }

    .badge.bg-success {
        background: var(--green-light) !important;
        color: var(--green-dark) !important;
    }

    .badge.bg-warning {
        background: var(--amber-light) !important;
        color: var(--amber-dark) !important;
    }

    .badge.bg-danger {
        background: var(--red-light) !important;
        color: var(--red-dark) !important;
    }

    .badge.bg-info {
        background: var(--teal-light) !important;
        color: var(--teal-dark) !important;
    }

    .badge.bg-violet {
        background: var(--violet-light) !important;
        color: var(--violet-dark) !important;
    }

    .badge.bg-secondary {
        background: #f3f4f6 !important;
        color: #374151 !important;
    }

    .doctor-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    @media(max-width:576px) {
        .doctor-grid {
            grid-template-columns: 1fr;
        }
    }

    .doctor-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: .85rem 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background .15s, border-color .15s;
    }

    .doctor-card:hover {
        background: var(--blue-50);
        border-color: var(--blue-200);
    }

    .doc-avi {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        font-weight: 700;
        flex-shrink: 0;
        border: 2px solid var(--border);
    }

    .doc-info .doc-name {
        font-size: .83rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .doc-info .doc-spec {
        font-size: .68rem;
        color: var(--text-muted);
    }

    .doc-status {
        font-size: .6rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: .05em;
        white-space: nowrap;
    }

    .ds-duty {
        background: var(--teal-light);
        color: var(--teal-dark);
    }

    .ds-break {
        background: var(--amber-light);
        color: var(--amber-dark);
    }

    .ds-off {
        background: var(--red-light);
        color: var(--red-dark);
    }

    .rec-chip {
        font-size: .63rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 5px;
    }

    .chip-consultation {
        background: var(--blue-50);
        color: var(--blue-700);
        border: 1px solid var(--blue-100);
    }

    .chip-lab {
        background: var(--violet-light);
        color: var(--violet-dark);
        border: 1px solid #ddd6fe;
    }

    .chip-imaging {
        background: var(--teal-light);
        color: var(--teal-dark);
        border: 1px solid #a5f3fc;
    }

    .chip-prescription {
        background: var(--green-light);
        color: var(--green-dark);
        border: 1px solid #a7f3d0;
    }

    .chip-other {
        background: var(--amber-light);
        color: var(--amber-dark);
        border: 1px solid #fde68a;
    }

    .book-cta {
        background: linear-gradient(135deg, var(--blue-50), #fff);
        border: 1px solid var(--blue-200);
        border-radius: var(--radius);
        padding: 1.25rem 1.5rem;
        text-align: center;
    }

    .book-cta i {
        font-size: 2rem;
        color: var(--blue-400);
        display: block;
        margin-bottom: .5rem;
    }

    .book-cta p {
        font-size: .83rem;
        color: var(--text-muted);
        margin-bottom: .85rem;
    }

    .btn-book {
        background: var(--blue-600);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .55rem 1.4rem;
        font-size: .84rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: background .15s, box-shadow .15s;
        text-decoration: none;
    }

    .btn-book:hover {
        background: var(--blue-700);
        box-shadow: 0 3px 10px rgba(37, 99, 235, .3);
        color: #fff;
    }

    .no-data {
        text-align: center;
        padding: 1.5rem;
        color: var(--text-muted);
        font-size: .82rem;
    }

    .no-data i {
        font-size: 1.8rem;
        display: block;
        margin-bottom: .4rem;
        opacity: .3;
    }

    .filter .icon i {
        color: var(--text-muted);
        font-size: .9rem;
    }

    .filter .icon:hover i {
        color: var(--blue-600);
    }

    .dropdown-menu {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-lg);
        font-size: .82rem;
    }

    .dropdown-item {
        color: var(--text-body);
        font-size: .8rem;
        padding: .4rem 1rem;
    }

    .dropdown-item:hover {
        background: var(--blue-50);
        color: var(--blue-700);
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }
</style>

<div class="pagetitle">
    <h1>Dashboard</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>
</div>

<section class="section dashboard">

    <?php if ($nextAppt): ?>

        <div class="next-appt-card">
            <div class="na-icon"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="na-label">Next Appointment</div>
                <div class="na-title">
                    <?= htmlspecialchars($nextAppt['doctorName']) ?>
                    <span class="na-badge"><?= htmlspecialchars($nextAppt['specialization']) ?></span>
                </div>
                <div class="na-sub">
                    <?= date('l, F j, Y', strtotime($nextAppt['appointmentDate'])) ?> at
                    <?= date('g:i A', strtotime($nextAppt['appointmentTime'])) ?> ·
                    <?= htmlspecialchars($nextAppt['appointmentCode']) ?>
                </div>
            </div>
            <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
                <a href="my_appointments.php" style="background:var(--green);color:#fff;border:none;border-radius:var(--radius-sm);padding:.4rem 1rem;font-size:.78rem;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:5px;">
                    <i class="bi bi-eye"></i> View
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="stat-strip">
        <a href="my_appointments.php" class="stat-card">
            <div class="sc-label">Total Appointments</div>
            <div class="sc-num"><?= $totalAppts ?></div>
            <div class="sc-sub">All time bookings</div>
        </a>
        <a href="my_appointments.php?status=upcoming" class="stat-card">
            <div class="sc-label">Upcoming</div>
            <div class="sc-num"><?= $upcomingAppts ?></div>
            <div class="sc-sub">Scheduled sessions</div>
        </a>
        <a href="my_appointments.php?status=Pending" class="stat-card">
            <div class="sc-label">Pending</div>
            <div class="sc-num"><?= $pendingAppts ?></div>
            <div class="sc-sub">Awaiting confirmation</div>
        </a>
        <a href="medical_records.php" class="stat-card">
            <div class="sc-label">Medical Records</div>
            <div class="sc-num"><?= $totalRecords ?></div>
            <div class="sc-sub">Your health records</div>
        </a>
    </div>

    <div class="row g-3">

        <div class="col-lg-8">
            <div class="row g-3">

                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Actions</h6>
                                </li>
                                <li><a class="dropdown-item" href="my_appointments.php">View All Appointments</a></li>
                                <li><a class="dropdown-item" href="book_appointment.php">Book New</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Appointment History <span>/ Last 6 Months</span></h5>
                            <div id="apptChart"></div>
                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const raw = <?= json_encode($chartMonths) ?>;
                                    new ApexCharts(document.querySelector('#apptChart'), {
                                        series: [{
                                            name: 'Appointments',
                                            data: raw.map(r => r.count)
                                        }],
                                        chart: {
                                            height: 220,
                                            type: 'bar',
                                            toolbar: {
                                                show: false
                                            },
                                            fontFamily: 'DM Sans,sans-serif'
                                        },
                                        colors: ['#2563eb'],
                                        plotOptions: {
                                            bar: {
                                                borderRadius: 6,
                                                columnWidth: '45%'
                                            }
                                        },
                                        dataLabels: {
                                            enabled: false
                                        },
                                        grid: {
                                            borderColor: '#eaecf4',
                                            strokeDashArray: 4
                                        },
                                        xaxis: {
                                            categories: raw.map(r => r.label),
                                            labels: {
                                                style: {
                                                    colors: '#9ca3af',
                                                    fontSize: '11px'
                                                }
                                            },
                                            axisBorder: {
                                                show: false
                                            },
                                            axisTicks: {
                                                show: false
                                            }
                                        },
                                        yaxis: {
                                            labels: {
                                                style: {
                                                    colors: '#9ca3af',
                                                    fontSize: '11px'
                                                },
                                                formatter: v => Math.round(v)
                                            }
                                        },
                                        tooltip: {
                                            y: {
                                                formatter: v => `${v} appointment${v!==1?'s':''}`
                                            }
                                        }
                                    }).render();
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Actions</h6>
                                </li>
                                <li><a class="dropdown-item" href="my_appointments.php">View All</a></li>
                                <li><a class="dropdown-item" href="book_appointment.php">Book New</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Recent Appointments <span>| <?= date('F j, Y') ?></span></h5>

                            <?php if (empty($recentAppts)): ?>
                                <div class="no-data">
                                    <i class="bi bi-calendar-x"></i>
                                    No appointment history yet.<br>
                                    <a href="book_appointment.php" style="color:var(--blue-600);font-weight:600">Book your first appointment →</a>
                                </div>
                            <?php else: ?>
                                <table class="table table-borderless">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAppts as $appt):
                                            $statusMap = ['Completed' => 'bg-success', 'In Progress' => 'bg-info', 'Pending' => 'bg-warning', 'Cancelled' => 'bg-danger'];
                                            $sCls = $statusMap[$appt['status']] ?? 'bg-secondary';
                                        ?>
                                            <tr>
                                                <td><a href="my_appointments.php" style="font-weight:700;color:var(--blue-700);font-size:.8rem;text-decoration:none"><?= htmlspecialchars($appt['appointmentCode']) ?></a></td>
                                                <td>
                                                    <div style="font-weight:600;color:var(--text-dark);font-size:.82rem"><?= htmlspecialchars($appt['doctorName']) ?></div>
                                                    <div style="font-size:.67rem;color:var(--text-muted)"><?= htmlspecialchars($appt['specialization']) ?></div>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($appt['appointmentDate'])) ?></td>
                                                <td><?= date('g:i A', strtotime($appt['appointmentTime'])) ?></td>
                                                <td><span class="badge <?= $sCls ?>"><?= htmlspecialchars($appt['status']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div style="text-align:center;padding-top:.5rem">
                                    <a href="my_appointments.php" style="font-size:.78rem;color:var(--blue-600);font-weight:600">View all appointments →</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Actions</h6>
                                </li>
                                <li><a class="dropdown-item" href="medical_records.php">View All Records</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Recent Medical Records <span>| Latest</span></h5>

                            <?php if (empty($recentRecords)): ?>
                                <div class="no-data">
                                    <i class="bi bi-file-medical"></i>
                                    No medical records yet.
                                </div>
                            <?php else: ?>
                                <table class="table table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Record</th>
                                            <th>Doctor</th>
                                            <th>Type</th>
                                            <th>Diagnosis</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRecords as $rec):
                                            $typeMap = [
                                                'Consultation' => 'chip-consultation',
                                                'Lab Result' => 'chip-lab',
                                                'Imaging' => 'chip-imaging',
                                                'Prescription' => 'chip-prescription',
                                                'Other' => 'chip-other'
                                            ];
                                            $tCls = $typeMap[$rec['recordType']] ?? 'chip-other';
                                        ?>
                                            <tr>
                                                <td><span style="font-weight:700;color:var(--blue-700);font-size:.8rem"><?= htmlspecialchars($rec['recordCode']) ?></span></td>
                                                <td style="font-size:.82rem"><?= htmlspecialchars($rec['doctorName']) ?></td>
                                                <td><span class="rec-chip <?= $tCls ?>"><?= htmlspecialchars($rec['recordType']) ?></span></td>
                                                <td style="font-size:.82rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($rec['diagnosis'] ?? '—') ?></td>
                                                <td style="font-size:.82rem"><?= date('M j, Y', strtotime($rec['createdAt'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div style="text-align:center;padding-top:.5rem">
                                    <a href="medical_records.php" style="font-size:.78rem;color:var(--blue-600);font-weight:600">View all records →</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-4">

            <div class="card mb-3">
                <div class="card-body book-cta">
                    <i class="bi bi-calendar-plus"></i>
                    <h6 style="font-weight:700;color:var(--text-dark);margin-bottom:.3rem">Schedule an Appointment</h6>
                    <p>Choose from our available doctors and book your slot in minutes.</p>
                    <a href="book_appointment.php" class="btn-book"><i class="bi bi-plus-lg"></i> Book Now</a>
                </div>
            </div>

            <div class="card mb-3">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Actions</h6>
                        </li>
                        <li><a class="dropdown-item" href="book_appointment.php">Book Appointment</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <h5 class="card-title">Available Today <span>| <?= date('l') ?></span></h5>

                    <?php if (empty($availDoctors)): ?>
                        <div class="no-data"><i class="bi bi-person-slash"></i>No doctors scheduled today.</div>
                    <?php else: ?>
                        <div class="doctor-grid">
                            <?php foreach ($availDoctors as $i => $doc):
                                $bg  = $avatarBgs[$i % count($avatarBgs)];
                                $col = $avatarColors[$i % count($avatarColors)];
                                $ini = strtoupper(substr($doc['firstName'], 0, 1) . substr($doc['lastName'], 0, 1));
                                $statusCls = $doc['status'] === 'On Duty' ? 'ds-duty' : ($doc['status'] === 'Break' ? 'ds-break' : 'ds-off');
                            ?>
                                <div class="doctor-card">
                                    <div class="doc-avi" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $ini ?></div>
                                    <div class="doc-info" style="flex:1;min-width:0">
                                        <div class="doc-name">Dr. <?= htmlspecialchars($doc['firstName'] . ' ' . $doc['lastName']) ?></div>
                                        <div class="doc-spec"><?= htmlspecialchars($doc['specialization']) ?></div>
                                    </div>
                                    <span class="doc-status <?= $statusCls ?>"><?= htmlspecialchars($doc['status']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="text-align:center;margin-top:.85rem">
                            <a href="book_appointment.php" class="btn-book" style="font-size:.78rem;padding:.4rem 1rem">
                                <i class="bi bi-calendar-plus"></i> Book a Slot
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Quick Links</h5>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <?php
                        $links = [
                            ['book_appointment.php', 'bi-calendar-plus',  'Book Appointment',  'Schedule a new visit',    '#2563eb', '#dbeafe'],
                            ['my_appointments.php',  'bi-calendar2-check', 'My Appointments',   'View all your bookings',  '#10b981', '#d1fae5'],
                            ['medical_records.php',  'bi-file-medical',   'Medical Records',   'Your health history',     '#06b6d4', '#cffafe'],
                            ['users.php',            'bi-person-gear',    'Profile Settings',  'Manage your account',     '#8b5cf6', '#ede9fe'],
                        ];
                        foreach ($links as [$href, $icon, $label, $sub, $color, $bg]):
                        ?>
                            <a href="<?= $href ?>" style="display:flex;align-items:center;gap:10px;padding:.65rem .85rem;border-radius:10px;background:var(--surface);border:1px solid var(--border);text-decoration:none;transition:background .15s" onmouseover="this.style.background='<?= $bg ?>'" onmouseout="this.style.background='var(--surface)'">
                                <div style="width:34px;height:34px;border-radius:9px;background:<?= $bg ?>;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div>
                                    <div style="font-size:.82rem;font-weight:600;color:var(--text-dark)"><?= $label ?></div>
                                    <div style="font-size:.67rem;color:var(--text-muted)"><?= $sub ?></div>
                                </div>
                                <i class="bi bi-chevron-right" style="margin-left:auto;color:var(--text-muted);font-size:.7rem"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include('./includes/footer.php'); ?>