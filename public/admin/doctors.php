<?php
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

// Stats (active doctors only for Total/OnDuty/OnLeave) 
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors WHERE employmentStatus != 'Inactive'")->fetch_row()[0];
$onDuty       = $conn->query("SELECT COUNT(*) FROM doctors WHERE status = 'On Duty' AND employmentStatus = 'Active'")->fetch_row()[0];
$onLeave      = $conn->query("SELECT COUNT(*) FROM doctors WHERE employmentStatus = 'On Leave'")->fetch_row()[0];
$totalSpecs   = $conn->query("SELECT COUNT(DISTINCT specialization) FROM doctors WHERE employmentStatus != 'Inactive'")->fetch_row()[0];

// Today's day name for schedule highlight
$todayName = date('l'); // e.g. "Monday"

// Doctor rows 
$sql = "
    SELECT
        d.id, d.doctorCode, d.firstName, d.middleName, d.lastName,
        d.specialization, d.contactNumber, d.patientCapacity,
        d.status, d.employmentStatus, d.emailAddress,
        d.prcLicenseNo, d.yearsOfExperience,
        COUNT(DISTINCT a.id) AS currentLoad,
        GROUP_CONCAT(DISTINCT ds.dayOfWeek
            ORDER BY FIELD(ds.dayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
            SEPARATOR ',') AS workingDays,
        MIN(ds.shiftStart) AS shiftStart,
        MAX(ds.shiftEnd)   AS shiftEnd,
        SUM(CASE WHEN ds.dayOfWeek = ? THEN 1 ELSE 0 END) AS hasToday,
        MAX(CASE WHEN ds.dayOfWeek = ? THEN ds.shiftStart ELSE NULL END) AS todayStart,
        MAX(CASE WHEN ds.dayOfWeek = ? THEN ds.shiftEnd   ELSE NULL END) AS todayEnd
    FROM doctors d
    LEFT JOIN appointments a
        ON a.doctorId = d.id AND a.appointmentDate = CURDATE() AND a.status NOT IN ('Cancelled')
    LEFT JOIN doctorSchedules ds ON ds.doctorId = d.id
    GROUP BY d.id
    ORDER BY d.lastName, d.firstName
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $todayName, $todayName, $todayName);
$stmt->execute();
$doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$avatarBgs    = ['#dbeafe', '#d1fae5', '#fef3c7', '#ede9fe', '#fce7f3', '#cffafe'];
$avatarColors = ['#1d4ed8', '#065f46', '#92400e', '#5b21b6', '#9d174d', '#155e75'];

function scheduleLabel($days, $start, $end)
{
    if (!$days) return '—';
    $abbr = ['Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed', 'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat', 'Sunday' => 'Sun'];
    $list = array_map(fn($d) => $abbr[$d] ?? $d, explode(',', $days));
    $time = ($start && $end) ? ', ' . date('g:iA', strtotime($start)) . '–' . date('g:iA', strtotime($end)) : '';
    return implode(', ', $list) . $time;
}
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

    .page-doctors,
    .page-doctors * {
        font-family: 'DM Sans', sans-serif;
        box-sizing: border-box;
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

    .stat-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 1.5rem;
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
        transition: all .4s;
    }

    .sc-sub {
        font-size: .7rem;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    .main-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
        animation: fadeUp .32s .22s ease both;
    }

    .table-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .table-toolbar h5 {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-body);
        margin: 0;
        flex: 1;
    }

    .table-toolbar h5 span {
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: var(--text-muted);
        font-size: .7rem;
        margin-left: 4px;
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: .8rem;
        pointer-events: none;
    }

    .search-box input {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .42rem .75rem .42rem 2rem;
        font-size: .8rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-dark);
        background: var(--surface);
        outline: none;
        width: 200px;
        transition: border-color .2s;
    }

    .search-box input:focus {
        border-color: var(--blue-400);
        background: #fff;
    }

    .spec-filter {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .42rem .65rem;
        font-size: .8rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-body);
        background: var(--surface);
        outline: none;
        cursor: pointer;
    }

    .btn-primary-sm {
        background: var(--blue-600);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .42rem 1rem;
        font-size: .8rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background .15s, box-shadow .15s;
        white-space: nowrap;
        text-decoration: none;
    }

    .btn-primary-sm:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, .25);
        color: #fff;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead th {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        padding: .65rem .6rem;
        background: transparent;
    }

    .table tbody td {
        font-size: .83rem;
        color: var(--text-body);
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
        padding: .7rem .6rem;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--blue-50);
    }

    .table tbody tr.filler-row:hover td {
        background: transparent;
    }

    .doc-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .doc-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid var(--border);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .75rem;
        font-weight: 700;
        align-self: center;
    }

    .doc-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: .83rem;
    }

    .doc-id {
        font-size: .67rem;
        color: var(--text-muted);
    }

    .load-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .load-track {
        flex: 1;
        height: 5px;
        background: var(--blue-100);
        border-radius: 99px;
        overflow: hidden;
        max-width: 80px;
    }

    .load-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--blue-500), var(--blue-400));
        border-radius: 99px;
    }

    .load-frac {
        font-size: .73rem;
        font-weight: 600;
        color: var(--text-body);
        white-space: nowrap;
    }

    .load-frac .ld {
        color: var(--blue-700);
    }

    .load-frac .lt {
        color: var(--text-muted);
        font-weight: 400;
    }

    .badge {
        font-family: 'DM Sans', sans-serif;
        font-size: .63rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 9px;
        letter-spacing: .03em;
    }

    .bg-info {
        background: var(--teal-light) !important;
        color: var(--teal-dark) !important;
    }

    .bg-warning {
        background: var(--amber-light) !important;
        color: var(--amber-dark) !important;
    }

    .bg-danger {
        background: var(--red-light) !important;
        color: var(--red-dark) !important;
    }

    .status-cell {
        position: relative;
    }

    .badge-btn {
        cursor: pointer;
        border: none;
        background: none;
        padding: 0;
        font-family: 'DM Sans', sans-serif;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: opacity .15s;
    }

    .badge-btn:hover {
        opacity: .8;
    }

    .badge-btn .badge-caret {
        font-size: .55rem;
        opacity: .65;
    }

    .status-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-md);
        z-index: 500;
        min-width: 130px;
        overflow: hidden;
        animation: fadeUp .18s ease both;
    }

    .status-dropdown.open {
        display: block;
    }

    .status-opt {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: .45rem .75rem;
        font-size: .78rem;
        font-weight: 600;
        cursor: pointer;
        color: var(--text-body);
        transition: background .12s;
    }

    .status-opt:hover {
        background: var(--surface);
    }

    .status-opt .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .dot-duty {
        background: var(--teal);
    }

    .dot-break {
        background: var(--amber);
    }

    .dot-off {
        background: var(--red);
    }

    .action-btns {
        display: flex;
        gap: 5px;
    }

    .btn-act {
        background: none;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 4px 9px;
        font-size: .75rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: all .15s;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-act:hover {
        background: var(--blue-50);
        color: var(--blue-600);
        border-color: var(--blue-200);
    }

    .btn-act.view:hover {
        background: var(--violet-light);
        color: var(--violet-dark);
        border-color: #c4b5fd;
    }

    .pagination-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: .75rem;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pagination-info {
        font-size: .75rem;
        color: var(--text-muted);
    }

    .pagination-btns {
        display: flex;
        gap: 5px;
    }

    .pg-btn {
        background: none;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 4px 12px;
        font-size: .78rem;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        color: var(--text-body);
        transition: all .15s;
    }

    .pg-btn:hover:not(:disabled) {
        background: var(--blue-50);
        border-color: var(--blue-200);
        color: var(--blue-600);
    }

    .pg-btn.active {
        background: var(--blue-600);
        color: #fff;
        border-color: var(--blue-600);
    }

    .pg-btn:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    /*Redesigned Profile Modal*/
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(3px);
    }

    .modal-overlay.show {
        display: flex;
    }

    .profile-modal {
        background: #fff;
        border-radius: 20px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .18);
        animation: fadeUp .25s ease both;
        overflow: hidden;
    }

    .pm-hero {
        background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
        padding: 1.5rem 1.75rem 3.5rem;
        position: relative;
    }

    .pm-hero-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(255, 255, 255, .18);
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .8rem;
        transition: background .15s;
    }

    .pm-hero-close:hover {
        background: rgba(255, 255, 255, .32);
    }

    .pm-identity {
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        margin-top: 1rem;
    }

    .pm-avatar-lg {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, .9);
        box-shadow: 0 4px 16px rgba(0, 0, 0, .2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-bottom: -2.8rem;
    }

    .pm-name-block {
        padding-bottom: .2rem;
    }

    .pm-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.15;
        letter-spacing: -.02em;
    }

    .pm-spec {
        font-size: .82rem;
        color: rgba(255, 255, 255, .75);
        margin-top: .25rem;
        font-weight: 500;
    }

    .pm-body {
        padding: 3rem 1.75rem 1.75rem;
    }

    .pm-today-box {
        background: linear-gradient(135deg, var(--blue-50), #fff);
        border: 1px solid var(--blue-200);
        border-radius: 12px;
        padding: .85rem 1rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: .85rem;
    }

    .pm-today-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--blue-600);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .pm-today-label {
        font-size: .6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--blue-600);
        margin-bottom: 2px;
    }

    .pm-today-val {
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .pm-today-off {
        background: var(--surface);
        border-color: var(--border);
    }

    .pm-today-off .pm-today-icon {
        background: var(--text-muted);
    }

    .pm-today-off .pm-today-label {
        color: var(--text-muted);
    }

    .pm-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .65rem 1.25rem;
    }

    .pm-item {
        padding: .6rem .75rem;
        background: var(--surface);
        border-radius: 10px;
    }

    .pm-item .pm-lbl {
        font-size: .58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .pm-item .pm-val {
        font-size: .83rem;
        font-weight: 600;
        color: var(--text-dark);
        word-break: break-word;
    }

    .pm-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .75rem;
        font-weight: 700;
        border-radius: 20px;
        padding: 3px 10px;
    }

    .pm-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
        margin-top: 1.25rem;
    }

    .btn-close-profile {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .48rem 1.2rem;
        font-size: .82rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        color: var(--text-body);
        transition: background .15s;
    }

    .btn-close-profile:hover {
        background: var(--border);
    }

    .toast-wrap {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .toast-msg {
        background: #1f2937;
        color: #fff;
        border-radius: 10px;
        padding: .65rem 1.1rem;
        font-size: .82rem;
        font-family: 'DM Sans', sans-serif;
        box-shadow: var(--shadow-lg);
        animation: fadeUp .25s ease both;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .toast-msg.info {
        background: #155e75;
    }

    .toast-msg.error {
        background: #991b1b;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(10px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }
</style>

<div class="pagetitle">
    <h1>Doctors</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Doctors</li>
        </ol>
    </nav>
</div>

<section class="section page-doctors">
    <!-- Stat cards — ids let JS update them live -->
    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Doctors</div>
            <div class="sc-num" id="stat-total"><?= $totalDoctors ?></div>
            <div class="sc-sub">Active &amp; on leave</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">On Duty</div>
            <div class="sc-num" id="stat-duty"><?= $onDuty ?></div>
            <div class="sc-sub">Active right now</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">On Leave</div>
            <div class="sc-num" id="stat-leave"><?= $onLeave ?></div>
            <div class="sc-sub">This period</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Specializations</div>
            <div class="sc-num" id="stat-specs"><?= $totalSpecs ?></div>
            <div class="sc-sub">Departments</div>
        </div>
    </div>

    <div class="main-card">
        <div class="table-toolbar">
            <h5>All Doctors <span>| <?= date('F j, Y') ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="doctorSearch" placeholder="Search doctor…" oninput="applyFilters()">
            </div>
            <select class="spec-filter" id="specFilter" onchange="applyFilters()">
                <option value="">All Specializations</option>
                <?php
                $specs = array_unique(array_column($doctors, 'specialization'));
                sort($specs);
                foreach ($specs as $s) echo "<option>" . htmlspecialchars($s) . "</option>";
                ?>
            </select>
            <a href="add_doctors" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> Add Doctor</a>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Contact</th>
                        <th>Schedule</th>
                        <th>Patient Load</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="doctorTbody">
                    <?php foreach ($doctors as $i => $d):
                        $bg       = $avatarBgs[$i % count($avatarBgs)];
                        $col      = $avatarColors[$i % count($avatarColors)];
                        $initials = strtoupper(substr($d['firstName'], 0, 1) . substr($d['lastName'], 0, 1));
                        $fullName = 'Dr. ' . $d['firstName'] . ' ' . $d['lastName'];
                        $load     = (int)$d['currentLoad'];
                        $cap      = (int)$d['patientCapacity'] ?: 20;
                        $pct      = $cap > 0 ? min(100, round($load / $cap * 100)) : 0;
                        $schedule = scheduleLabel($d['workingDays'], $d['shiftStart'], $d['shiftEnd']);
                        $statusCls = match ($d['status']) {
                            'On Duty' => 'bg-info',
                            'Break' => 'bg-warning',
                            default => 'bg-danger'
                        };

                        // Today's schedule for this doctor
                        $todayShift = $d['hasToday']
                            ? date('g:iA', strtotime($d['todayStart'])) . ' – ' . date('g:iA', strtotime($d['todayEnd']))
                            : 'Not scheduled today';
                        $hasTodayBool = $d['hasToday'] ? 'true' : 'false';
                    ?>
                        <tr
                            data-spec="<?= htmlspecialchars($d['specialization']) ?>"
                            data-name="<?= htmlspecialchars($fullName) ?>"
                            data-id="<?= htmlspecialchars($d['doctorCode']) ?>"
                            data-dbid="<?= $d['id'] ?>"
                            data-contact="<?= htmlspecialchars($d['contactNumber'] ?? '—') ?>"
                            data-schedule="<?= htmlspecialchars($schedule) ?>"
                            data-today-shift="<?= htmlspecialchars($todayShift) ?>"
                            data-today-has="<?= $hasTodayBool ?>"
                            data-email="<?= htmlspecialchars($d['emailAddress'] ?? '—') ?>"
                            data-license="<?= htmlspecialchars($d['prcLicenseNo']) ?>"
                            data-experience="<?= (int)$d['yearsOfExperience'] ?> yrs"
                            data-load="<?= $load ?>/<?= $cap ?>"
                            data-avatar="<?= $initials ?>"
                            data-avatar-bg="<?= $bg ?>"
                            data-avatar-color="<?= $col ?>"
                            data-emp-status="<?= htmlspecialchars($d['employmentStatus']) ?>">
                            <td><?= htmlspecialchars($d['doctorCode']) ?></td>
                            <td>
                                <div class="doc-cell">
                                    <div class="doc-avatar" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $initials ?></div>
                                    <div style="display:flex;flex-direction:column;justify-content:center;">
                                        <div class="doc-name"><?= htmlspecialchars($fullName) ?></div>
                                        <div class="doc-id"><?= htmlspecialchars($d['specialization']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($d['specialization']) ?></td>
                            <td><?= htmlspecialchars($d['contactNumber'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($schedule) ?></td>
                            <td>
                                <div class="load-bar">
                                    <div class="load-track">
                                        <div class="load-fill" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="load-frac"><span class="ld"><?= $load ?></span><span class="lt">/<?= $cap ?></span></span>
                                </div>
                            </td>
                            <td class="status-cell">
                                <button class="badge-btn" onclick="toggleStatusDropdown(this)">
                                    <span class="badge <?= $statusCls ?>"><?= htmlspecialchars($d['status']) ?></span>
                                    <span class="badge-caret">▾</span>
                                </button>
                                <div class="status-dropdown">
                                    <div class="status-opt" onclick="setStatus(this,'On Duty','bg-info',<?= $d['id'] ?>)"><span class="dot dot-duty"></span>On Duty</div>
                                    <div class="status-opt" onclick="setStatus(this,'Break','bg-warning',<?= $d['id'] ?>)"><span class="dot dot-break"></span>Break</div>
                                    <div class="status-opt" onclick="setStatus(this,'Off Duty','bg-danger',<?= $d['id'] ?>)"><span class="dot dot-off"></span>Off Duty</div>
                                </div>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-act" title="Edit" onclick="editDoctor(<?= $d['id'] ?>)"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-act view" title="View Profile" onclick="viewDoctor(this.closest('tr'))"><i class="bi bi-eye"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-bar">
            <span class="pagination-info" id="paginationInfo"></span>
            <div class="pagination-btns" id="paginationBtns"></div>
        </div>
    </div>
</section>

<!-- ── Redesigned Profile Modal ──────────────────────────────────── -->
<div class="modal-overlay" id="profileModal">
    <div class="profile-modal">
        <div class="pm-hero">
            <button class="pm-hero-close" onclick="closeProfileModal()"><i class="bi bi-x"></i></button>
            <div class="pm-identity">
                <div class="pm-avatar-lg" id="pmAvatar"></div>
                <div class="pm-name-block">
                    <div class="pm-name" id="pmName"></div>
                    <div class="pm-spec" id="pmSpec"></div>
                </div>
            </div>
        </div>
        <div class="pm-body">
            <!-- Today's schedule highlight -->
            <div class="pm-today-box" id="pmTodayBox">
                <div class="pm-today-icon"><i class="bi bi-clock"></i></div>
                <div>
                    <div class="pm-today-label">Today's Shift — <?= date('l, F j') ?></div>
                    <div class="pm-today-val" id="pmTodayShift">—</div>
                </div>
            </div>
            <!-- Info grid -->
            <div class="pm-grid">
                <div class="pm-item">
                    <div class="pm-lbl">Doctor ID</div>
                    <div class="pm-val" id="pmId"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Contact No.</div>
                    <div class="pm-val" id="pmContact"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Email</div>
                    <div class="pm-val" id="pmEmail"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">License No.</div>
                    <div class="pm-val" id="pmLicense"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Full Schedule</div>
                    <div class="pm-val" id="pmSchedule"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Experience</div>
                    <div class="pm-val" id="pmExperience"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Patient Load</div>
                    <div class="pm-val" id="pmLoad"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Duty Status</div>
                    <div class="pm-val" id="pmStatus"></div>
                </div>
            </div>
            <div class="pm-footer">
                <button class="btn-close-profile" onclick="closeProfileModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
    const ROWS_PER_PAGE = 5;
    let currentPage = 1;

    // Live stat counters
    function recount() {
        const rows = Array.from(document.querySelectorAll('#doctorTbody tr:not(.filler-row)'));
        let duty = 0,
            leave = 0;
        rows.forEach(r => {
            const badge = r.querySelector('.badge');
            const emp = r.dataset.empStatus || '';
            if (badge && badge.textContent.trim() === 'On Duty') duty++;
            if (emp === 'On Leave') leave++;
        });
        animateNum('stat-duty', duty);
        animateNum('stat-leave', leave);
    }

    function animateNum(id, target) {
        const el = document.getElementById(id);
        const from = parseInt(el.textContent) || 0;
        if (from === target) return;
        const steps = 12,
            step = (target - from) / steps;
        let cur = from,
            n = 0;
        const t = setInterval(() => {
            n++;
            cur += step;
            el.textContent = Math.round(n === steps ? target : cur);
            if (n >= steps) clearInterval(t);
        }, 30);
    }

    // Pagination 
    function getFilteredRows() {
        const q = document.getElementById('doctorSearch').value.toLowerCase();
        const spec = document.getElementById('specFilter').value;
        return Array.from(document.querySelectorAll('#doctorTbody tr:not(.filler-row)')).filter(row =>
            (!q || (row.dataset.name || '').toLowerCase().includes(q)) &&
            (!spec || row.dataset.spec === spec)
        );
    }

    function renderPage(page) {
        currentPage = page;
        const rows = getFilteredRows();
        const total = rows.length;
        const pages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
        if (currentPage > pages) currentPage = pages;
        const start = (currentPage - 1) * ROWS_PER_PAGE;
        const end = start + ROWS_PER_PAGE;

        document.querySelectorAll('#doctorTbody tr:not(.filler-row)').forEach(r => r.style.display = 'none');
        rows.forEach((r, i) => {
            r.style.display = (i >= start && i < end) ? '' : 'none';
        });

        document.getElementById('paginationInfo').textContent = total === 0 ?
            'No doctors found' :
            `Showing ${start+1}–${Math.min(end,total)} of ${total} doctor${total!==1?'s':''}`;

        const btns = document.getElementById('paginationBtns');
        btns.innerHTML = '';
        btns.appendChild(makeBtn('‹ Prev', currentPage === 1, () => renderPage(currentPage - 1)));
        for (let p = 1; p <= pages; p++) {
            const b = makeBtn(p, false, ((p) => () => renderPage(p))(p));
            if (p === currentPage) b.classList.add('active');
            btns.appendChild(b);
        }
        btns.appendChild(makeBtn('Next ›', currentPage === pages, () => renderPage(currentPage + 1)));
    }

    function makeBtn(label, disabled, fn) {
        const b = document.createElement('button');
        b.className = 'pg-btn';
        b.textContent = label;
        b.disabled = disabled;
        b.onclick = fn;
        return b;
    }

    function applyFilters() {
        renderPage(1);
    }
    renderPage(1);

    // Status dropdown 
    function toggleStatusDropdown(btn) {
        const dd = btn.nextElementSibling;
        const open = dd.classList.contains('open');
        document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!open) dd.classList.add('open');
    }

    function setStatus(optEl, label, cls, doctorId) {
        const dd = optEl.closest('.status-dropdown');
        const badge = dd.previousElementSibling.querySelector('.badge');
        badge.className = 'badge ' + cls;
        badge.textContent = label;
        dd.classList.remove('open');

        fetch('update_doctor_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${doctorId}&status=${encodeURIComponent(label)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    recount(); // ← live stat update
                    showToast('✔ Status updated to <strong>' + label + '</strong>', 'info');
                } else {
                    showToast('❌ Failed to update status', 'error');
                }
            });
    }

    document.addEventListener('click', e => {
        if (!e.target.closest('.status-cell'))
            document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
    });

    function editDoctor(id) {
        window.location.href = 'edit_doctor?id=' + id;
    }

    // ── View modal ─────────────────────────────────────────
    function viewDoctor(row) {
        const d = row.dataset;
        const badge = row.querySelector('.badge');

        // Avatar
        document.getElementById('pmAvatar').textContent = d.avatar;
        document.getElementById('pmAvatar').style.background = d.avatarBg;
        document.getElementById('pmAvatar').style.color = d.avatarColor;
        document.getElementById('pmName').textContent = d.name;
        document.getElementById('pmSpec').textContent = d.spec;

        // Info fields
        document.getElementById('pmId').textContent = d.id;
        document.getElementById('pmContact').textContent = d.contact;
        document.getElementById('pmEmail').textContent = d.email;
        document.getElementById('pmLicense').textContent = d.license;
        document.getElementById('pmSchedule').textContent = d.schedule;
        document.getElementById('pmExperience').textContent = d.experience;
        document.getElementById('pmLoad').textContent = d.load + ' patients';
        document.getElementById('pmStatus').textContent = badge ? badge.textContent : '—';

        // Today's schedule
        const todayBox = document.getElementById('pmTodayBox');
        const todayShift = document.getElementById('pmTodayShift');
        const hasToday = d.todayHas === 'true';
        todayShift.textContent = d.todayShift;
        todayBox.classList.toggle('pm-today-off', !hasToday);

        document.getElementById('profileModal').classList.add('show');
    }

    function closeProfileModal() {
        document.getElementById('profileModal').classList.remove('show');
    }
    document.getElementById('profileModal').addEventListener('click', function(e) {
        if (e.target === this) closeProfileModal();
    });

    function showToast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = 'toast-msg ' + type;
        el.innerHTML = msg;
        document.getElementById('toastWrap').appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }
</script>

<?php include('./includes/footer.php'); ?>