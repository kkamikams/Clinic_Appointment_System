<?php
session_start();
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

// ══════════════════════════════════════════════════════
// LIVE STATS — all pulled from DB
// ══════════════════════════════════════════════════════

$today = date('Y-m-d');

// Appointments today
$apptToday     = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate = '$today'")->fetch_row()[0];
$apptYesterday = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate = DATE_SUB('$today', INTERVAL 1 DAY)")->fetch_row()[0];
$apptTrend     = $apptYesterday > 0 ? round((($apptToday - $apptYesterday) / $apptYesterday) * 100) : 0;

// Patients this month
$patMonth     = $conn->query("SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(CURDATE()) AND YEAR(createdAt)=YEAR(CURDATE())")->fetch_row()[0];
$patLastMonth = $conn->query("SELECT COUNT(*) FROM patients WHERE MONTH(createdAt)=MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)) AND YEAR(createdAt)=YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))")->fetch_row()[0];
$patTrend     = $patLastMonth > 0 ? round((($patMonth - $patLastMonth) / $patLastMonth) * 100) : 0;

// Total active patients & doctors
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients WHERE status='Active'")->fetch_row()[0];
$totalDoctors  = $conn->query("SELECT COUNT(*) FROM doctors WHERE employmentStatus='Active'")->fetch_row()[0];
$onDutyNow     = $conn->query("SELECT COUNT(*) FROM doctors WHERE status='On Duty'")->fetch_row()[0];

// Appointment chart data (last 7 days)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
  $d         = date('Y-m-d', strtotime("-$i days"));
  $label     = date('Y-m-d\TH:i:s.000\Z', strtotime($d));
  $total     = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$d'")->fetch_row()[0];
  $completed = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$d' AND status='Completed'")->fetch_row()[0];
  $cancelled = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$d' AND status='Cancelled'")->fetch_row()[0];
  $chartData[] = ['date' => $label, 'total' => (int)$total, 'completed' => (int)$completed, 'cancelled' => (int)$cancelled];
}

// Today's appointments table (limit 10)
$apptRows = $conn->query("
    SELECT a.appointmentCode, a.appointmentTime, a.status, a.channel,
           CONCAT(p.firstName,' ',p.lastName) AS patientName,
           CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName,
           d.specialization
    FROM appointments a
    JOIN patients p ON p.id=a.patientId
    JOIN doctors  d ON d.id=a.doctorId
    WHERE a.appointmentDate='$today'
    ORDER BY a.appointmentTime ASC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Doctors on duty today
$dutyDoctors = $conn->query("
    SELECT d.id, d.firstName, d.lastName, d.specialization,
           d.patientCapacity, d.status,
           COUNT(DISTINCT a.id) AS currentLoad,
           MIN(ds.shiftStart) AS shiftStart,
           MAX(ds.shiftEnd)   AS shiftEnd
    FROM doctors d
    LEFT JOIN appointments a  ON a.doctorId=d.id AND a.appointmentDate='$today' AND a.status!='Cancelled'
    LEFT JOIN doctorSchedules ds ON ds.doctorId=d.id AND ds.dayOfWeek=DAYNAME('$today')
    WHERE d.status='On Duty' AND d.employmentStatus='Active'
    GROUP BY d.id
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// Recent activity (last 8)
$activities = $conn->query("
    SELECT * FROM recentActivity ORDER BY createdAt DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// Appointment channels today
$channels = $conn->query("
    SELECT channel, COUNT(*) AS cnt FROM appointments
    WHERE appointmentDate='$today' GROUP BY channel
")->fetch_all(MYSQLI_ASSOC);
$channelMap = [];
foreach ($channels as $ch) $channelMap[$ch['channel']] = (int)$ch['cnt'];

// Status breakdown today
$apptCompleted  = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$today' AND status='Completed'")->fetch_row()[0];
$apptPending    = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$today' AND status='Pending'")->fetch_row()[0];
$apptInProgress = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$today' AND status='In Progress'")->fetch_row()[0];
$apptCancelled  = $conn->query("SELECT COUNT(*) FROM appointments WHERE appointmentDate='$today' AND status='Cancelled'")->fetch_row()[0];

$avatarBgs    = ['#dbeafe', '#d1fae5', '#fef3c7', '#ede9fe', '#fce7f3', '#cffafe'];
$avatarColors = ['#1d4ed8', '#065f46', '#92400e', '#5b21b6', '#9d174d', '#155e75'];
$todayDay     = date('l');
?>
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300;1,9..40,400&display=swap');

  :root {
    --blue-50: #eff6ff;
    --blue-100: #dbeafe;
    --blue-200: #bfdbfe;
    --blue-300: #93c5fd;
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

  /* Cards */
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
    transform: translateY(-1px);
  }

  /* Stat cards */
  .info-card .card-body {
    padding: 1.4rem 1.5rem 1.3rem;
    position: relative;
  }

  .info-card .card-title {
    font-size: .67rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--text-muted);
    margin-bottom: .85rem;
  }

  .info-card .card-title span {
    font-weight: 400;
    letter-spacing: 0;
    text-transform: none;
    font-size: .7rem;
    color: var(--text-muted);
    opacity: .75;
  }

  .info-card h6 {
    font-weight: 700;
    font-size: 2.1rem;
    color: var(--text-dark);
    margin: 0;
    line-height: 1;
    letter-spacing: -.05em;
  }

  .info-card .stat-trend {
    margin-top: .4rem;
    font-size: .72rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .info-card .stat-trend.up {
    color: var(--green);
  }

  .info-card .stat-trend.down {
    color: var(--red);
  }

  .info-card .stat-trend.neutral {
    color: var(--text-muted);
  }

  .info-card .stat-trend span {
    font-weight: 400;
    color: var(--text-muted);
  }

  .card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    font-size: 1.2rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sales-card .card-icon {
    background: var(--blue-50);
    color: var(--blue-600);
  }

  .revenue-card .card-icon {
    background: #ecfdf5;
    color: var(--green);
  }

  .customers-card .card-icon {
    background: #f5f3ff;
    color: var(--violet);
  }

  .info-card {
    border-left: 3px solid transparent;
  }

  .sales-card {
    border-left-color: var(--blue-500);
  }

  .revenue-card {
    border-left-color: var(--green);
  }

  .customers-card {
    border-left-color: var(--violet);
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

  .filter {
    position: absolute;
    top: 12px;
    right: 14px;
  }

  .filter .icon i {
    color: var(--text-muted);
    font-size: .9rem;
  }

  .filter .icon:hover i {
    color: var(--blue-600);
  }

  /* Tables */
  .table thead th {
    font-size: .65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border) !important;
    padding-bottom: .75rem;
    background: transparent;
  }

  .table tbody td,
  .table tbody th {
    font-size: .83rem;
    color: var(--text-body);
    vertical-align: middle;
    border-color: var(--border);
    padding: .7rem .5rem;
  }

  .table tbody tr:hover td,
  .table tbody tr:hover th {
    background: var(--blue-50);
  }

  .table tbody th a {
    color: var(--blue-700);
    font-weight: 600;
    text-decoration: none;
    font-size: .79rem;
  }

  .table .text-primary {
    color: var(--blue-600) !important;
    text-decoration: none;
    font-weight: 500;
  }

  /* Badges */
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

  .badge.bg-secondary {
    background: #f3f4f6 !important;
    color: #374151 !important;
  }

  /* Duty bar */
  .duty-progress {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .duty-bar {
    flex: 1;
    height: 5px;
    background: var(--blue-100);
    border-radius: 99px;
    overflow: hidden;
    max-width: 80px;
  }

  .duty-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--blue-500), var(--blue-400));
    border-radius: 99px;
    transition: width .4s ease;
  }

  .duty-fraction {
    font-size: .75rem;
    font-weight: 600;
    color: var(--text-body);
    white-space: nowrap;
  }

  .duty-fraction .done {
    color: var(--blue-700);
  }

  .duty-fraction .total {
    color: var(--text-muted);
    font-weight: 400;
  }

  /* Activity */
  .activity {
    display: flex;
    flex-direction: column;
    gap: 0;
  }

  .activity-item {
    padding: .6rem 0;
    border-bottom: 1px solid var(--border);
  }

  .activity-item:last-child {
    border-bottom: none;
  }

  .activite-label {
    font-size: .65rem;
    font-weight: 600;
    color: var(--text-muted);
    min-width: 48px;
    padding-top: 3px;
    letter-spacing: .02em;
  }

  .activity-badge {
    font-size: .42rem;
    margin: 5px 14px 0;
    flex-shrink: 0;
  }

  .activity-content {
    font-size: .81rem;
    color: var(--text-body);
    line-height: 1.55;
  }

  .activity-content a {
    color: var(--text-dark);
    font-weight: 600;
    text-decoration: none;
  }

  .activity-content a:hover {
    color: var(--blue-700);
  }

  /* Doctor avatar */
  .doc-avatar-sm {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .73rem;
    font-weight: 700;
    flex-shrink: 0;
  }

  /* Quick links */
  .quick-links {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 1.25rem;
  }

  @media(max-width:768px) {
    .quick-links {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .quick-link-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .8rem 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    transition: box-shadow .15s, transform .15s;
    animation: fadeUp .32s ease both;
  }

  .quick-link-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }

  .ql-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .ql-label {
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-dark);
  }

  .ql-sub {
    font-size: .66rem;
    color: var(--text-muted);
  }

  /* Tasks */
  .task-add-row {
    display: flex;
    gap: 8px;
    margin-bottom: 1rem;
  }

  .task-add-row input[type="text"] {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .42rem .75rem;
    font-size: .82rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-dark);
    background: var(--surface);
    outline: none;
    transition: border-color .2s;
  }

  .task-add-row input[type="text"]:focus {
    border-color: var(--blue-400);
    background: #fff;
  }

  .task-add-row button {
    background: var(--blue-600);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    padding: .42rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
  }

  .task-add-row button:hover {
    background: var(--blue-700);
  }

  .task-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 320px;
    overflow-y: auto;
  }

  .task-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: .52rem .7rem;
    border-radius: 9px;
    border: 1px solid var(--border);
    background: var(--surface);
    transition: background .15s;
    font-size: .82rem;
  }

  .task-item:hover {
    background: var(--blue-50);
  }

  .task-item input[type="checkbox"] {
    accent-color: var(--blue-600);
    width: 15px;
    height: 15px;
    cursor: pointer;
    flex-shrink: 0;
  }

  .task-item .task-label {
    flex: 1;
    color: var(--text-body);
    line-height: 1.4;
  }

  .task-item .task-label.done {
    text-decoration: line-through;
    color: var(--text-muted);
  }

  .task-item .task-priority {
    font-size: .6rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 5px;
    text-transform: uppercase;
    letter-spacing: .05em;
    flex-shrink: 0;
  }

  .task-item .task-priority.high {
    background: var(--red-light);
    color: var(--red-dark);
  }

  .task-item .task-priority.medium {
    background: var(--amber-light);
    color: var(--amber-dark);
  }

  .task-item .task-priority.low {
    background: var(--green-light);
    color: var(--green-dark);
  }

  .task-item .task-delete {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: .75rem;
    padding: 0 2px;
    line-height: 1;
    flex-shrink: 0;
    transition: color .15s;
  }

  .task-item .task-delete:hover {
    color: var(--red);
  }

  .task-empty {
    text-align: center;
    padding: 1.5rem 0;
    color: var(--text-muted);
    font-size: .8rem;
  }

  .task-stats {
    display: flex;
    gap: 12px;
    margin-bottom: .75rem;
  }

  .task-stat {
    flex: 1;
    background: var(--surface);
    border-radius: 8px;
    padding: .4rem .6rem;
    text-align: center;
    border: 1px solid var(--border);
  }

  .task-stat .ts-num {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-dark);
    letter-spacing: -.04em;
    line-height: 1;
  }

  .task-stat .ts-label {
    font-size: .62rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-top: 2px;
  }

  .priority-select {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .42rem .5rem;
    font-size: .78rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-body);
    background: var(--surface);
    outline: none;
    cursor: pointer;
  }

  /* Source legend */
  .source-legend {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: .5rem;
    padding: 0 .25rem;
  }

  .source-legend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: .78rem;
  }

  .source-legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-right: 8px;
  }

  .source-legend-label {
    display: flex;
    align-items: center;
    color: var(--text-body);
  }

  .source-legend-val {
    font-weight: 700;
    color: var(--text-dark);
    font-size: .78rem;
  }

  .source-legend-pct {
    font-size: .68rem;
    color: var(--text-muted);
    margin-left: 4px;
  }

  /* Live indicator */
  .live-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    background: var(--green);
    border-radius: 50%;
    margin-right: 5px;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {

    0%,
    100% {
      opacity: 1;
      transform: scale(1);
    }

    50% {
      opacity: .5;
      transform: scale(.8);
    }
  }

  /* Dropdown */
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

  .dropdown-header h6 {
    font-size: .67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: var(--text-muted);
  }

  @keyframes fadeUp {
    from {
      opacity: 0;
      transform: translateY(12px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .col-xxl-4,
  .col-xl-12,
  .col-md-6 {
    animation: fadeUp .38s ease both;
  }

  .col-xxl-4:nth-child(1) {
    animation-delay: .04s;
  }

  .col-xxl-4:nth-child(2) {
    animation-delay: .10s;
  }

  .col-xxl-4:nth-child(3) {
    animation-delay: .16s;
  }

  .col-12 {
    animation: fadeUp .38s ease both;
    animation-delay: .22s;
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
  <div class="row g-3">

    <!-- ═══ Left side ═══ -->
    <div class="col-lg-8">
      <div class="row g-3">

        <!-- Quick links -->
        <div class="col-12">
          <div class="quick-links">
            <a href="appointments" class="quick-link-card" style="animation-delay:.04s">
              <div class="ql-icon" style="background:var(--blue-50);color:var(--blue-600)"><i class="bi bi-calendar2-check"></i></div>
              <div>
                <div class="ql-label">Appointments</div>
                <div class="ql-sub" id="qlAppt"><?= $apptToday ?> today</div>
              </div>
            </a>
            <a href="patients" class="quick-link-card" style="animation-delay:.08s">
              <div class="ql-icon" style="background:#ecfdf5;color:var(--green)"><i class="bi bi-people"></i></div>
              <div>
                <div class="ql-label">Patients</div>
                <div class="ql-sub"><?= $totalPatients ?> active</div>
              </div>
            </a>
            <a href="doctors" class="quick-link-card" style="animation-delay:.12s">
              <div class="ql-icon" style="background:#f5f3ff;color:var(--violet)"><i class="bi bi-person-badge"></i></div>
              <div>
                <div class="ql-label">Doctors</div>
                <div class="ql-sub" id="qlDuty"><?= $onDutyNow ?> on duty</div>
              </div>
            </a>
            <a href="medical_records" class="quick-link-card" style="animation-delay:.16s">
              <div class="ql-icon" style="background:var(--teal-light);color:var(--teal-dark)"><i class="bi bi-file-medical"></i></div>
              <div>
                <div class="ql-label">Records</div>
                <div class="ql-sub">Medical records</div>
              </div>
            </a>
          </div>
        </div>

        <!-- Appointment stat card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card sales-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="appointments">View All Appointments</a></li>
                <li><a class="dropdown-item" href="appointments?status=Pending">Pending</a></li>
                <li><a class="dropdown-item" href="appointments?status=Completed">Completed</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Appointments <span>| Today</span></h5>
              <div class="d-flex align-items-center gap-3">
                <div class="card-icon"><i class="bi bi-calendar2-check"></i></div>
                <div>
                  <h6 id="statApptToday"><?= $apptToday ?></h6>
                  <?php if ($apptTrend >= 0): ?>
                    <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i><?= abs($apptTrend) ?>% <span>vs yesterday</span></div>
                  <?php else: ?>
                    <div class="stat-trend down"><i class="bi bi-arrow-down-short"></i><?= abs($apptTrend) ?>% <span>vs yesterday</span></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Patients stat card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card revenue-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="patients">View All Patients</a></li>
                <li><a class="dropdown-item" href="patients?status=Active">Active</a></li>
                <li><a class="dropdown-item" href="patients?condition=Critical">Critical</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Patients <span>| This Month</span></h5>
              <div class="d-flex align-items-center gap-3">
                <div class="card-icon"><i class="bi bi-people"></i></div>
                <div>
                  <h6><?= $patMonth ?></h6>
                  <?php if ($patTrend >= 0): ?>
                    <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i><?= abs($patTrend) ?>% <span>vs last month</span></div>
                  <?php else: ?>
                    <div class="stat-trend down"><i class="bi bi-arrow-down-short"></i><?= abs($patTrend) ?>% <span>vs last month</span></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Doctors stat card -->
        <div class="col-xxl-4 col-xl-12">
          <div class="card info-card customers-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="doctors">View All Doctors</a></li>
                <li><a class="dropdown-item" href="doctors?status=On+Duty">On Duty</a></li>
                <li><a class="dropdown-item" href="add_doctors">Add Doctor</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Doctors <span>| Active</span></h5>
              <div class="d-flex align-items-center gap-3">
                <div class="card-icon"><i class="bi bi-person-badge"></i></div>
                <div>
                  <h6><?= $totalDoctors ?></h6>
                  <div class="stat-trend neutral"><i class="bi bi-dot" style="font-size:1.1rem"></i><span id="statDuty"><?= $onDutyNow ?></span> <span>on duty now</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart -->
        <div class="col-12">
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Actions</h6>
                </li>
                <li><a class="dropdown-item" href="appointments">View Appointments</a></li>
                <li><a class="dropdown-item" href="appointments?status=Completed">Completed</a></li>
                <li><a class="dropdown-item" href="appointments?status=Cancelled">Cancelled</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Appointment Reports <span>/ Last 7 Days</span></h5>
              <div style="display:flex;gap:12px;margin-bottom:.85rem;flex-wrap:wrap;">
                <?php
                $statItems = [['Completed', $apptCompleted, '#10b981'], ['In Progress', $apptInProgress, '#06b6d4'], ['Pending', $apptPending, '#f59e0b'], ['Cancelled', $apptCancelled, '#ef4444']];
                foreach ($statItems as [$label, $count, $color]):
                ?>
                  <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:<?= $color ?>;display:inline-block;"></span>
                    <span style="color:var(--text-muted)"><?= $label ?>:</span>
                    <strong style="color:var(--text-dark)" id="stat<?= str_replace(' ', '', $label) ?>"><?= $count ?></strong>
                  </div>
                <?php endforeach; ?>
              </div>
              <div id="reportsChart"></div>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  const raw = <?= json_encode($chartData) ?>;
                  new ApexCharts(document.querySelector("#reportsChart"), {
                    series: [{
                      name: 'Appointments',
                      data: raw.map(r => r.total)
                    }, {
                      name: 'Completed',
                      data: raw.map(r => r.completed)
                    }, {
                      name: 'Cancelled',
                      data: raw.map(r => r.cancelled)
                    }],
                    chart: {
                      height: 280,
                      type: 'area',
                      toolbar: {
                        show: false
                      },
                      fontFamily: 'DM Sans,sans-serif'
                    },
                    markers: {
                      size: 3,
                      strokeWidth: 2,
                      strokeColors: '#fff'
                    },
                    colors: ['#2563eb', '#10b981', '#f59e0b'],
                    fill: {
                      type: 'gradient',
                      gradient: {
                        shadeIntensity: 1,
                        opacityFrom: .18,
                        opacityTo: .02,
                        stops: [0, 95, 100]
                      }
                    },
                    dataLabels: {
                      enabled: false
                    },
                    stroke: {
                      curve: 'smooth',
                      width: 2
                    },
                    grid: {
                      borderColor: '#eaecf4',
                      strokeDashArray: 4,
                      padding: {
                        left: 4,
                        right: 4
                      }
                    },
                    xaxis: {
                      type: 'datetime',
                      categories: raw.map(r => r.date),
                      labels: {
                        style: {
                          colors: '#9ca3af',
                          fontSize: '11px',
                          fontFamily: 'DM Sans'
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
                          fontSize: '11px',
                          fontFamily: 'DM Sans'
                        }
                      }
                    },
                    tooltip: {
                      x: {
                        format: 'MMM dd, yyyy'
                      }
                    },
                    legend: {
                      position: 'top',
                      horizontalAlign: 'right',
                      fontSize: '12px',
                      fontFamily: 'DM Sans',
                      fontWeight: 600,
                      markers: {
                        width: 7,
                        height: 7,
                        radius: 4
                      }
                    }
                  }).render();
                });
              </script>
            </div>
          </div>
        </div>

        <!-- Today's Appointments Table -->
        <div class="col-12">
          <div class="card recent-sales overflow-auto">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Actions</h6>
                </li>
                <li><a class="dropdown-item" href="appointments">View All</a></li>
                <li><a class="dropdown-item" href="appointments">New Appointment</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">
                Today's Appointments <span>| <?= date('F j, Y') ?></span>
                <span class="live-dot ms-2"></span><span style="font-size:.65rem;color:var(--green);font-weight:600">LIVE</span>
              </h5>
              <?php if (empty($apptRows)): ?>
                <div style="text-align:center;padding:2rem;color:var(--text-muted)">
                  <i class="bi bi-calendar-x" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
                  <p style="font-size:.85rem;margin:0">No appointments scheduled for today.</p>
                  <a href="appointments" style="font-size:.8rem;color:var(--blue-600)">Schedule one →</a>
                </div>
              <?php else: ?>
                <table class="table table-borderless" id="todayApptTable">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Patient</th>
                      <th>Doctor</th>
                      <th>Time</th>
                      <th>Channel</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="todayApptTbody">
                    <?php foreach ($apptRows as $i => $row):
                      $bg = $avatarBgs[$i % count($avatarBgs)];
                      $col = $avatarColors[$i % count($avatarColors)];
                      $ini = strtoupper(substr($row['patientName'], 0, 1) . substr(strrchr($row['patientName'], ' '), 1, 1));
                      $time12 = date('g:i A', strtotime($row['appointmentTime']));
                      $statusMap = ['Completed' => 'bg-success', 'In Progress' => 'bg-info', 'Pending' => 'bg-warning', 'Cancelled' => 'bg-danger'];
                      $badgeCls = $statusMap[$row['status']] ?? 'bg-secondary';
                    ?>
                      <tr>
                        <th scope="row"><a href="appointments" class="text-primary"><?= htmlspecialchars($row['appointmentCode']) ?></a></th>
                        <td>
                          <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:28px;height:28px;border-radius:50%;background:<?= $bg ?>;color:<?= $col ?>;display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;flex-shrink:0"><?= $ini ?></div>
                            <?= htmlspecialchars($row['patientName']) ?>
                          </div>
                        </td>
                        <td><a href="doctors" class="text-primary"><?= htmlspecialchars($row['doctorName']) ?> – <?= htmlspecialchars($row['specialization']) ?></a></td>
                        <td><?= $time12 ?></td>
                        <td><span style="font-size:.65rem;font-weight:600;padding:2px 8px;border-radius:5px;background:var(--blue-50);color:var(--blue-700);border:1px solid var(--blue-100)"><?= htmlspecialchars($row['channel']) ?></span></td>
                        <td><span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <?php if ($apptToday > 10): ?>
                  <div style="text-align:center;padding:.5rem 0"><a href="appointments" style="font-size:.78rem;color:var(--blue-600);font-weight:600">View all <?= $apptToday ?> appointments →</a></div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Doctors on Duty -->
        <div class="col-12">
          <div class="card top-selling overflow-auto">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Actions</h6>
                </li>
                <li><a class="dropdown-item" href="doctors">View All Doctors</a></li>
                <li><a class="dropdown-item" href="add_doctors">Add Doctor</a></li>
              </ul>
            </div>
            <div class="card-body pb-0">
              <h5 class="card-title">Doctors on Duty <span>| Today — <?= $todayDay ?></span></h5>
              <?php if (empty($dutyDoctors)): ?>
                <div style="text-align:center;padding:2rem;color:var(--text-muted)">
                  <i class="bi bi-person-slash" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
                  <p style="font-size:.85rem;margin:0">No doctors currently on duty.</p>
                  <a href="doctors" style="font-size:.8rem;color:var(--blue-600)">Manage doctors →</a>
                </div>
              <?php else: ?>
                <table class="table table-borderless">
                  <thead>
                    <tr>
                      <th>Doctor</th>
                      <th>Specialization</th>
                      <th>Shift</th>
                      <th>Patient Load</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($dutyDoctors as $i => $doc):
                      $bg = $avatarBgs[$i % count($avatarBgs)];
                      $col = $avatarColors[$i % count($avatarColors)];
                      $ini = strtoupper(substr($doc['firstName'], 0, 1) . substr($doc['lastName'], 0, 1));
                      $load = (int)$doc['currentLoad'];
                      $cap = (int)$doc['patientCapacity'] ?: 20;
                      $pct = min(100, round($load / $cap * 100));
                      $shift = ($doc['shiftStart'] && $doc['shiftEnd']) ? date('g:iA', strtotime($doc['shiftStart'])) . ' – ' . date('g:iA', strtotime($doc['shiftEnd'])) : 'All day';
                      $statusMap = ['On Duty' => 'bg-info', 'Break' => 'bg-warning', 'Off Duty' => 'bg-danger'];
                      $sCls = $statusMap[$doc['status']] ?? 'bg-secondary';
                    ?>
                      <tr>
                        <th scope="row">
                          <div style="display:flex;align-items:center;gap:9px">
                            <div class="doc-avatar-sm" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $ini ?></div>
                            <a href="doctors" class="text-primary fw-bold">Dr. <?= htmlspecialchars($doc['firstName'] . ' ' . $doc['lastName']) ?></a>
                          </div>
                        </th>
                        <td><?= htmlspecialchars($doc['specialization']) ?></td>
                        <td style="font-size:.78rem;color:var(--text-muted)"><?= $shift ?></td>
                        <td>
                          <div class="duty-progress">
                            <div class="duty-bar">
                              <div class="duty-bar-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span class="duty-fraction"><span class="done"><?= $load ?></span><span class="total">/<?= $cap ?></span></span>
                          </div>
                        </td>
                        <td><span class="badge <?= $sCls ?>"><?= htmlspecialchars($doc['status']) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div><!-- End left side -->

    <!-- ═══ Right side ═══ -->
    <div class="col-lg-4">

      <!-- Recent Activity — LIVE -->
      <div class="card mb-3">
        <div class="filter">
          <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
              <h6>Filter</h6>
            </li>
            <li><a class="dropdown-item" href="appointments">View Appointments</a></li>
            <li><a class="dropdown-item" href="patients">View Patients</a></li>
          </ul>
        </div>
        <div class="card-body">
          <h5 class="card-title">
            Recent Activity <span>| Today</span>
            <span class="live-dot ms-2"></span><span style="font-size:.65rem;color:var(--green);font-weight:600">LIVE</span>
          </h5>
          <div class="activity" id="activityFeed">
            <?php
            $actColors = ['appointment' => 'var(--green)', 'cancel' => 'var(--red)', 'progress' => 'var(--blue-500)', 'record' => 'var(--teal)', 'patient' => 'var(--amber)'];
            if (!empty($activities)):
              foreach ($activities as $act):
                $diff = time() - strtotime($act['createdAt']);
                $elapsed = $diff < 60 ? $diff . 's' : ($diff < 3600 ? round($diff / 60) . ' min' : round($diff / 3600) . ' hr');
                $type = strtolower($act['activityType'] ?? '');
                $color = 'var(--text-muted)';
                if (str_contains($type, 'appointment') && !str_contains($type, 'cancel')) $color = 'var(--green)';
                elseif (str_contains($type, 'cancel')) $color = 'var(--red)';
                elseif (str_contains($type, 'progress') || str_contains($type, 'update')) $color = 'var(--blue-500)';
                elseif (str_contains($type, 'record')) $color = 'var(--teal)';
                elseif (str_contains($type, 'patient') || str_contains($type, 'register')) $color = 'var(--amber)';
            ?>
                <div class="activity-item d-flex">
                  <div class="activite-label"><?= $elapsed ?></div>
                  <i class='bi bi-circle-fill activity-badge align-self-start' style="color:<?= $color ?>"></i>
                  <div class="activity-content"><?= htmlspecialchars($act['description']) ?></div>
                </div>
              <?php endforeach;
            else: ?>
              <div style="text-align:center;padding:1rem;color:var(--text-muted);font-size:.8rem">No activity today.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Appointment Channel Chart -->
      <div class="card mb-3">
        <div class="filter">
          <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
              <h6>Actions</h6>
            </li>
            <li><a class="dropdown-item" href="appointments">View Appointments</a></li>
          </ul>
        </div>
        <div class="card-body pb-2">
          <h5 class="card-title">Appointment Channel <span>| Today</span></h5>
          <div id="channelChart" style="min-height:240px;" class="echart"></div>
          <?php
          $channelDefs = ['Online' => ['#2563eb', 'Online Booking'], 'Walk-in' => ['#10b981', 'Walk-in'], 'Phone' => ['#f59e0b', 'Phone Call'], 'Referral' => ['#8b5cf6', 'Referral'], 'Follow-up' => ['#06b6d4', 'Follow-up']];
          $chanTotal = array_sum($channelMap) ?: 1;
          ?>
          <div class="source-legend">
            <?php foreach ($channelDefs as $key => [$color, $label]):
              $cnt = $channelMap[$key] ?? 0;
              $pct = round($cnt / $chanTotal * 100);
            ?>
              <div class="source-legend-item">
                <div class="source-legend-label">
                  <div class="source-legend-dot" style="background:<?= $color ?>"></div><?= $label ?>
                </div>
                <div><span class="source-legend-val"><?= $cnt ?></span><span class="source-legend-pct"><?= $pct ?>%</span></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div><!-- End right side -->
  </div>
</section>

<script>
  // ── Task Widget ──────────────────────────────────────
  const TASK_HANDLER = 'task_handler.php';

  function updateTaskCounts() {
    const items = document.querySelectorAll('#taskList .task-item');
    const total = items.length,
      done = document.querySelectorAll('#taskList .task-item input:checked').length;
    document.getElementById('tsTotal').textContent = total;
    document.getElementById('tsDone').textContent = done;
    document.getElementById('tsLeft').textContent = total - done;
    const el = document.getElementById('taskEmpty');
    if (el) el.style.display = total ? 'none' : '';
  }

  function addTask() {
    const input = document.getElementById('taskInput'),
      label = input.value.trim();
    if (!label) {
      input.focus();
      return;
    }
    const priority = document.getElementById('taskPriority').value;
    fetch(TASK_HANDLER + '?action=add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          title: label,
          priority,
          category: 'General',
          status: 'Pending'
        })
      })
      .then(r => r.json()).then(res => {
        if (!res.success) return;
        const pri = priority.toLowerCase();
        const list = document.getElementById('taskList');
        const div = document.createElement('div');
        div.className = 'task-item';
        div.id = 'task-' + res.id;
        div.innerHTML = `<input type="checkbox" onchange="toggleTask(${res.id},this)"><span class="task-label">${escHtml(label)}</span><span class="task-priority ${pri}">${priority}</span><button class="task-delete" onclick="deleteTask(${res.id},this.closest('.task-item'))" title="Remove"><i class="bi bi-x"></i></button>`;
        list.insertBefore(div, list.firstChild);
        input.value = '';
        updateTaskCounts();
      });
  }

  function toggleTask(id, checkbox) {
    const item = document.getElementById('task-' + id);
    if (!item) return;
    item.querySelector('.task-label').classList.toggle('done', checkbox.checked);
    const fd = new FormData();
    fd.append('id', id);
    fetch(TASK_HANDLER + '?action=toggle', {
      method: 'POST',
      body: fd
    });
    updateTaskCounts();
  }

  function deleteTask(id, el) {
    if (el) el.remove();
    const fd = new FormData();
    fd.append('id', id);
    fetch(TASK_HANDLER + '?action=delete', {
      method: 'POST',
      body: fd
    });
    updateTaskCounts();
  }

  function clearDone(e) {
    e.preventDefault();
    document.querySelectorAll('#taskList .task-item input:checked').forEach(cb => cb.closest('.task-item').remove());
    fetch(TASK_HANDLER + '?action=clear_done', {
      method: 'POST'
    });
    updateTaskCounts();
  }

  function escHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateTaskCounts();
    document.getElementById('taskInput').addEventListener('keydown', e => {
      if (e.key === 'Enter') addTask();
    });

    // ── Channel chart ──────────────────────────────────
    <?php
    $chartChannelData = [];
    $chartChannelColors = [];
    foreach ($channelDefs as $key => [$color, $label]) {
      $cnt = $channelMap[$key] ?? 0;
      $chartChannelData[] = ['value' => $cnt, 'name' => $label];
      $chartChannelColors[] = $color;
    }
    ?>
    const channelData = <?= json_encode($chartChannelData) ?>;
    const channelColors = <?= json_encode($chartChannelColors) ?>;
    echarts.init(document.querySelector("#channelChart")).setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'none'
        }
      },
      grid: {
        left: '2%',
        right: '12%',
        top: '4%',
        bottom: '4%',
        containLabel: true
      },
      xAxis: {
        type: 'value',
        axisLine: {
          show: false
        },
        axisTick: {
          show: false
        },
        splitLine: {
          lineStyle: {
            color: '#eaecf4',
            type: 'dashed'
          }
        },
        axisLabel: {
          fontFamily: 'DM Sans',
          fontSize: 10,
          color: '#9ca3af'
        }
      },
      yAxis: {
        type: 'category',
        data: channelData.map(d => d.name),
        axisLine: {
          show: false
        },
        axisTick: {
          show: false
        },
        axisLabel: {
          fontFamily: 'DM Sans',
          fontSize: 11,
          color: '#4b5563'
        }
      },
      series: [{
        type: 'bar',
        data: channelData.map((d, i) => ({
          value: d.value,
          itemStyle: {
            color: channelColors[i],
            borderRadius: [0, 6, 6, 0]
          }
        })),
        barMaxWidth: 16,
        label: {
          show: true,
          position: 'right',
          fontFamily: 'DM Sans',
          fontSize: 11,
          fontWeight: 600,
          color: '#4b5563',
          formatter: '{c}'
        }
      }]
    });

    // ── Auto-refresh activity feed every 15 seconds ────
    function refreshActivity() {
      fetch('activity_handler.php?action=recent')
        .then(r => r.json())
        .then(res => {
          if (!res.success || !res.rows) return;
          const feed = document.getElementById('activityFeed');
          if (!feed) return;
          const colorMap = {
            appointment: 'var(--green)',
            cancel: 'var(--red)',
            progress: 'var(--blue-500)',
            record: 'var(--teal)',
            patient: 'var(--amber)'
          };
          feed.innerHTML = res.rows.map(a => {
            const diff = Math.floor((Date.now() - new Date(a.createdAt)) / 1000);
            const elapsed = diff < 60 ? diff + 's' : diff < 3600 ? Math.round(diff / 60) + ' min' : Math.round(diff / 3600) + ' hr';
            const type = (a.activityType || '').toLowerCase();
            let color = 'var(--text-muted)';
            if (type.includes('appointment') && !type.includes('cancel')) color = colorMap.appointment;
            else if (type.includes('cancel')) color = colorMap.cancel;
            else if (type.includes('record')) color = colorMap.record;
            else if (type.includes('patient')) color = colorMap.patient;
            return `<div class="activity-item d-flex"><div class="activite-label">${elapsed}</div><i class='bi bi-circle-fill activity-badge align-self-start' style="color:${color}"></i><div class="activity-content">${a.description}</div></div>`;
          }).join('');
        }).catch(() => {});
    }
    setInterval(refreshActivity, 15000);

    // ── Auto-refresh today's appointment count every 30s ──
    function refreshApptCount() {
      fetch('appointments_handler.php?action=list&date=<?= $today ?>&page=1')
        .then(r => r.json())
        .then(res => {
          if (!res.success) return;
          const s = res.stats;
          const el = document.getElementById('statApptToday');
          if (el && s.total !== undefined) el.textContent = s.total;
          const qlEl = document.getElementById('qlAppt');
          if (qlEl && s.total !== undefined) qlEl.textContent = s.total + ' today';
          // Update status breakdown labels
          if (s.Completed !== undefined) {
            const e = document.getElementById('statCompleted');
            if (e) e.textContent = s.Completed || 0;
          }
          if (s.Pending !== undefined) {
            const e = document.getElementById('statPending');
            if (e) e.textContent = s.Pending || 0;
          }
          if (s.Cancelled !== undefined) {
            const e = document.getElementById('statCancelled');
            if (e) e.textContent = s.Cancelled || 0;
          }
        }).catch(() => {});
    }
    setInterval(refreshApptCount, 30000);
  });
</script>

<?php include('./includes/footer.php'); ?>