<?php
session_start();
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

$todayName = date('l');
$currentTime = date('H:i:s');

// Auto-sync doctor status based on schedule and current time
$conn->query("
    UPDATE doctors d
    SET d.status = CASE
        WHEN EXISTS (
            SELECT 1 FROM doctorSchedules ds 
            WHERE ds.doctorId = d.id 
            AND ds.dayOfWeek = '$todayName'
            AND '$currentTime' >= ds.shiftStart
            AND '$currentTime' <= ds.shiftEnd
        ) THEN 'On Duty'
        ELSE 'Off Duty'
    END
    WHERE d.employmentStatus = 'Active' AND d.status != 'Break'
");
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors WHERE employmentStatus != 'Inactive'")->fetch_row()[0];
$onDuty       = $conn->query("SELECT COUNT(*) FROM doctors WHERE status = 'On Duty' AND employmentStatus = 'Active'")->fetch_row()[0];
$onLeave      = $conn->query("SELECT COUNT(*) FROM doctors WHERE employmentStatus = 'On Leave'")->fetch_row()[0];
$totalSpecs   = $conn->query("SELECT COUNT(DISTINCT specialization) FROM doctors WHERE employmentStatus != 'Inactive'")->fetch_row()[0];

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

    .panel-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .45);
        z-index: 9000;
        backdrop-filter: blur(2px);
    }

    .panel-overlay.show {
        display: block;
    }

    .view-panel {
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        height: 100%;
        width: 400px;
        max-width: 100vw;
        background: #fff;
        box-shadow: -8px 0 40px rgba(0, 0, 0, .14);
        z-index: 9001;
        flex-direction: column;
        overflow: hidden;
        font-family: 'DM Sans', sans-serif;
        transform: translateX(100%);
        transition: transform .28s cubic-bezier(.4, 0, .2, 1);
    }

    .view-panel.show {
        display: flex;
        transform: translateX(0);
    }

    .vp-hero {
        background: linear-gradient(135deg, var(--blue-600) 0%, var(--blue-700) 100%);
        padding: 1.25rem 1.5rem 1rem;
        flex-shrink: 0;
    }

    .vp-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: .85rem;
    }

    .vp-identity {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vp-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, .3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .vp-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: -.02em;
    }

    .vp-spec {
        font-size: .78rem;
        color: rgba(255, 255, 255, .7);
        margin-top: 2px;
        font-weight: 500;
    }

    .vp-close-btn {
        background: rgba(255, 255, 255, .15);
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        color: #fff;
        font-size: .8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background .15s;
    }

    .vp-close-btn:hover {
        background: rgba(255, 255, 255, .28);
    }

    .vp-shift-box {
        background: rgba(255, 255, 255, .13);
        border-radius: 10px;
        padding: .6rem .85rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .vp-shift-box i {
        color: rgba(255, 255, 255, .8);
        font-size: .85rem;
    }

    .vp-shift-lbl {
        font-size: .6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: rgba(255, 255, 255, .6);
        margin-bottom: 2px;
    }

    .vp-shift-val {
        font-size: .82rem;
        font-weight: 600;
        color: #fff;
    }

    .vp-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.25rem;
    }

    .vp-load-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1rem;
    }

    .vp-load-track {
        flex: 1;
        height: 5px;
        background: var(--blue-100);
        border-radius: 99px;
        overflow: hidden;
    }

    .vp-load-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--blue-500), var(--blue-400));
        border-radius: 99px;
        transition: width .4s ease;
    }

    .vp-load-txt {
        font-size: .73rem;
        font-weight: 600;
        color: var(--text-body);
        white-space: nowrap;
    }

    .vp-section-hd {
        font-size: .6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: .6rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid var(--border);
    }

    .vp-appt-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 1.1rem;
    }

    .vp-appt-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .55rem .7rem;
        background: var(--surface);
        border-radius: 9px;
    }

    .vp-appt-num {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--blue-100);
        color: var(--blue-700);
        font-size: .72rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .vp-appt-info {
        flex: 1;
        min-width: 0;
    }

    .vp-appt-name {
        font-size: .82rem;
        font-weight: 600;
        color: var(--text-dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .vp-appt-time {
        font-size: .7rem;
        color: var(--text-muted);
    }

    .vp-appt-badge {
        font-size: .62rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 20px;
        flex-shrink: 0;
    }

    .vp-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-bottom: 1rem;
    }

    .vp-info-item {
        background: var(--surface);
        border-radius: 9px;
        padding: .55rem .7rem;
    }

    .vp-info-lbl {
        font-size: .58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: 2px;
    }

    .vp-info-val {
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .vp-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: .75rem;
        border-top: 1px solid var(--border);
    }

    .vp-btn-edit {
        background: var(--blue-600);
        border: none;
        border-radius: 9px;
        padding: .45rem 1.2rem;
        font-size: .8rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: background .15s;
    }

    .vp-btn-edit:hover {
        background: var(--blue-700);
    }

    .vp-btn-close {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 9px;
        padding: .45rem 1.2rem;
        font-size: .8rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        color: var(--text-body);
        transition: background .15s;
    }

    .vp-btn-close:hover {
        background: var(--border);
    }

    .vp-empty {
        font-size: .8rem;
        color: var(--text-muted);
        padding: .75rem 0;
        text-align: center;
    }

    .vp-error {
        font-size: .8rem;
        color: var(--red);
        padding: .5rem 0;
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
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
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
            <a href="add_doctors.php" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> Add Doctor</a>
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
                            'Break'   => 'bg-warning',
                            default   => 'bg-danger'
                        };
                        $todayShift   = $d['hasToday']
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
                            <td style="white-space:nowrap">
                                <span style="color:#2563eb;font-weight:600;font-size:.83rem;cursor:pointer;"
                                    onclick="viewDoctor(this.closest('tr'))">
                                    <?= htmlspecialchars($d['doctorCode']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="doc-cell">
                                    <div class="doc-avatar" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $initials ?></div>
                                    <div>
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

<div class="panel-overlay" id="panelOverlay" onclick="closePanel()"></div>

<div class="view-panel" id="viewPanel">

    <!-- Hero -->
    <div class="vp-hero">
        <div class="vp-hero-top">
            <div class="vp-identity">
                <div class="vp-avatar" id="vpAvatar"></div>
                <div>
                    <div class="vp-name" id="vpName"></div>
                    <div class="vp-spec" id="vpSpec"></div>
                </div>
            </div>
            <button class="vp-close-btn" onclick="closePanel()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="vp-shift-box">
            <i class="bi bi-clock"></i>
            <div>
                <div class="vp-shift-lbl">Today's shift — <?= date('l, F j') ?></div>
                <div class="vp-shift-val" id="vpShift">—</div>
            </div>
        </div>
    </div>

    <div class="vp-body">

        <div class="vp-load-row">
            <div class="vp-load-track">
                <div class="vp-load-fill" id="vpLoadFill" style="width:0%"></div>
            </div>
            <span class="vp-load-txt" id="vpLoadTxt"></span>
        </div>

        <div class="vp-section-hd">Today's Appointments</div>
        <div class="vp-appt-list" id="vpApptList">
            <div class="vp-empty">Loading appointments…</div>
        </div>

        <div class="vp-section-hd">Doctor Info</div>
        <div class="vp-info-grid">
            <div class="vp-info-item">
                <div class="vp-info-lbl">Doctor ID</div>
                <div class="vp-info-val" id="vpId"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">Contact No.</div>
                <div class="vp-info-val" id="vpContact"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">Email</div>
                <div class="vp-info-val" id="vpEmail"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">License No.</div>
                <div class="vp-info-val" id="vpLicense"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">Experience</div>
                <div class="vp-info-val" id="vpExp"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">Employment</div>
                <div class="vp-info-val" id="vpEmp"></div>
            </div>
            <div class="vp-info-item" style="grid-column:1/-1;">
                <div class="vp-info-lbl">Full Schedule</div>
                <div class="vp-info-val" id="vpSchedule"></div>
            </div>
        </div>

        <div class="vp-footer">
            <button class="vp-btn-close" onclick="closePanel()">Close</button>
        </div>

    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
    const ROWS_PER_PAGE = 5;
    let currentPage = 1;

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

        document.querySelectorAll('#doctorTbody tr.filler-row').forEach(r => r.remove());
        document.querySelectorAll('#doctorTbody tr:not(.filler-row)').forEach(r => r.style.display = 'none');
        rows.forEach((r, i) => {
            r.style.display = (i >= start && i < end) ? '' : 'none';
        });

        const shown = Math.min(end, total) - start;
        const tbody = document.getElementById('doctorTbody');

        document.getElementById('paginationInfo').textContent = total === 0 ?
            'No doctors found' :
            `Showing ${start + 1}–${Math.min(end, total)} of ${total} doctor${total !== 1 ? 's' : ''}`;

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

        fetch('/Clinic_Appointment_System/app/controllers/update_doctor_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${doctorId}&status=${encodeURIComponent(label)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    recount();
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
        window.location.href = 'edit_doctor.php?id=' + id;
    }

    const apptBadgeStyles = {
        'Confirmed': 'background:#d1fae5;color:#065f46',
        'Pending': 'background:#fef3c7;color:#92400e',
        'Arrived': 'background:#cffafe;color:#155e75',
        'Completed': 'background:#ede9fe;color:#5b21b6',
        'Cancelled': 'background:#fee2e2;color:#991b1b',
    };

    function viewDoctor(row) {
        const d = row.dataset;
        const badge = row.querySelector('.badge');

        const avatar = document.getElementById('vpAvatar');
        avatar.textContent = d.avatar;
        avatar.style.background = d.avatarBg;
        avatar.style.color = d.avatarColor;
        document.getElementById('vpName').textContent = d.name;
        document.getElementById('vpSpec').textContent = d.spec;

        document.getElementById('vpShift').textContent =
            d.todayHas === 'true' ? d.todayShift : 'Not scheduled today';

        const parts = (d.load || '0/20').split('/');
        const cur = parseInt(parts[0]) || 0;
        const cap = parseInt(parts[1]) || 20;
        const pct = cap > 0 ? Math.min(100, Math.round(cur / cap * 100)) : 0;
        document.getElementById('vpLoadFill').style.width = pct + '%';
        document.getElementById('vpLoadTxt').textContent = cur + ' / ' + cap + ' patients today';

        document.getElementById('vpId').textContent = d.id || '—';
        document.getElementById('vpContact').textContent = d.contact || '—';
        document.getElementById('vpEmail').textContent = d.email || '—';
        document.getElementById('vpLicense').textContent = d.license || '—';
        document.getElementById('vpExp').textContent = d.experience || '—';
        document.getElementById('vpEmp').textContent = d.empStatus || '—';
        document.getElementById('vpSchedule').textContent = d.schedule || '—';

        document.getElementById('panelOverlay').classList.add('show');

        const panel = document.getElementById('viewPanel');
        panel.style.display = 'flex';
        requestAnimationFrame(() => panel.classList.add('show'));

        const apptList = document.getElementById('vpApptList');
        apptList.innerHTML = '<div class="vp-empty">Loading appointments…</div>';

        fetch('/Clinic_Appointment_System/app/controllers/get_doctor_appointments.php?doctor_id=' + d.dbid)
            .then(r => r.json())
            .then(appts => {
                if (!appts.length) {
                    apptList.innerHTML = '<div class="vp-empty">No appointments today.</div>';
                    return;
                }
                apptList.innerHTML = appts.map((a, i) => {
                    const bs = apptBadgeStyles[a.status] || 'background:var(--surface);color:var(--text-muted)';
                    return `
                    <div class="vp-appt-item">
                        <div class="vp-appt-num">${i + 1}</div>
                        <div class="vp-appt-info">
                            <div class="vp-appt-name">${escHtml(a.patientName)}</div>
                            <div class="vp-appt-time">${escHtml(a.time)} · ${escHtml(a.reason || 'Consultation')}</div>
                        </div>
                        <span class="vp-appt-badge" style="${bs}">${escHtml(a.status)}</span>
                    </div>`;
                }).join('');
            })
            .catch(() => {
                apptList.innerHTML = '<div class="vp-error">Could not load appointments.</div>';
            });
    }

    function closePanel() {
        const panel = document.getElementById('viewPanel');
        panel.classList.remove('show');
        document.getElementById('panelOverlay').classList.remove('show');
        setTimeout(() => {
            panel.style.display = 'none';
        }, 280);
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showToast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = 'toast-msg ' + type;
        el.innerHTML = msg;
        document.getElementById('toastWrap').appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }
</script>

<?php include('./includes/footer.php'); ?>