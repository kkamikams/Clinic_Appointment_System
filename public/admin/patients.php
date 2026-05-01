<?php
session_start();
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

$totalPatients = $conn->query("SELECT COUNT(*) FROM patients WHERE status != 'Inactive'")->fetch_row()[0];
$activeCount   = $conn->query("SELECT COUNT(*) FROM patients WHERE status = 'Active'")->fetch_row()[0];
$critical      = $conn->query("SELECT COUNT(*) FROM patients WHERE patientCondition = 'Critical'")->fetch_row()[0];

$sql = "
    SELECT
        p.id, p.patientCode, p.firstName, p.middleName, p.lastName,
        p.gender, p.dateOfBirth, p.contactNumber, p.emailAddress, p.address,
        p.status, p.patientCondition,
        TIMESTAMPDIFF(YEAR, p.dateOfBirth, CURDATE()) AS age,
        MAX(a.appointmentDate) AS lastVisit,
        d.firstName AS docFirst, d.lastName AS docLast
    FROM patients p
    LEFT JOIN appointments a ON a.patientId = p.id AND a.status = 'Completed'
    LEFT JOIN doctors d ON d.id = (
        SELECT doctorId FROM appointments
        WHERE patientId = p.id AND status = 'Completed'
        ORDER BY appointmentDate DESC LIMIT 1
    )
    GROUP BY p.id
    ORDER BY p.lastName, p.firstName
";
$patients = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$avatarBgs    = ['#dbeafe', '#d1fae5', '#fef3c7', '#ede9fe', '#fce7f3', '#cffafe'];
$avatarColors = ['#1d4ed8', '#065f46', '#92400e', '#5b21b6', '#9d174d', '#155e75'];
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap');

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
        --gray: #9ca3af;
        --gray-light: #f3f4f6;
        --gray-dark: #374151;
        --violet: #8b5cf6;
        --violet-light: #ede9fe;
        --violet-dark: #5b21b6;
        --teal: #06b6d4;
        --teal-light: #cffafe;
        --teal-dark: #155e75;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
    }

    .page-patients,
    .page-patients * {
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
        border-left-color: var(--red);
        animation-delay: .14s;
    }

    .stat-card:nth-child(4) {
        border-left-color: var(--amber);
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

    /* ── Main card ── */
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

    .filter-select {
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

    .pat-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pat-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 2px solid var(--border);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .73rem;
        font-weight: 700;
    }

    .pat-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: .83rem;
    }

    .pat-id {
        font-size: .67rem;
        color: var(--text-muted);
    }

    .badge {
        font-family: 'DM Sans', sans-serif;
        font-size: .63rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 9px;
        letter-spacing: .03em;
    }

    .badge-active {
        background: var(--green-light);
        color: var(--green-dark);
    }

    .badge-discharged {
        background: var(--gray-light);
        color: var(--gray-dark);
    }

    .badge-inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-critical {
        background: var(--red-light);
        color: var(--red-dark);
    }

    .badge-stable {
        background: var(--green-light);
        color: var(--green-dark);
    }

    .badge-recovering {
        background: var(--blue-50);
        color: var(--blue-700);
    }

    .badge-observation {
        background: var(--amber-light);
        color: var(--amber-dark);
    }

    /* ── Status dropdown ── */
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

    .badge-caret {
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
        min-width: 160px;
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

    .dot-active {
        background: var(--green);
    }

    .dot-discharged {
        background: #9ca3af;
    }

    .dot-inactive {
        background: #d1d5db;
    }

    .dot-critical {
        background: var(--red);
    }

    .dot-stable {
        background: var(--green);
    }

    .dot-recovering {
        background: var(--blue-500);
    }

    .dot-observation {
        background: var(--amber);
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

    /* ── Pagination ── */
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

    /* ── Side Panel (same as doctors) ── */
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

    .vp-badges-hero {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .vp-badge-pill {
        font-size: .65rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        letter-spacing: .03em;
    }

    .vp-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.25rem;
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
        word-break: break-word;
    }

    .vp-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: .75rem;
        border-top: 1px solid var(--border);
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

    .toast-msg.success {
        background: #065f46;
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
    <h1>Patients</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Patients</li>
        </ol>
    </nav>
</div>

<section class="section page-patients">

    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Patients</div>
            <div class="sc-num" id="stat-total"><?= $totalPatients ?></div>
            <div class="sc-sub">Active &amp; discharged</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Active</div>
            <div class="sc-num" id="stat-active"><?= $activeCount ?></div>
            <div class="sc-sub">Currently active</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Critical</div>
            <div class="sc-num" id="stat-critical"><?= $critical ?></div>
            <div class="sc-sub">Needs attention</div>
        </div>
    </div>

    <div class="main-card">
        <div class="table-toolbar">
            <h5>Patient Records <span>| <?= date('F j, Y') ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="patSearch" placeholder="Search patient…" oninput="applyFilters()">
            </div>
            <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Discharged">Discharged</option>
                <option value="Inactive">Inactive</option>
            </select>
            <select class="filter-select" id="conditionFilter" onchange="applyFilters()">
                <option value="">All Conditions</option>
                <option value="Stable">Stable</option>
                <option value="Critical">Critical</option>
                <option value="Under Observation">Under Observation</option>
                <option value="Recovering">Recovering</option>
            </select>
            <a href="add_patient" class="btn-primary-sm"><i class="bi bi-plus-lg"></i> Add Patient</a>
        </div>

        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Age / Gender</th>
                        <th>Contact</th>
                        <th>Last Visit</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patTbody">
                    <?php foreach ($patients as $i => $p):
                        $bg        = $avatarBgs[$i % count($avatarBgs)];
                        $col       = $avatarColors[$i % count($avatarColors)];
                        $initials  = strtoupper(substr($p['firstName'], 0, 1) . substr($p['lastName'], 0, 1));
                        $fullName  = $p['firstName'] . ' ' . $p['lastName'];
                        $age       = $p['age'] ?? '—';
                        $lastVisit = $p['lastVisit'] ? date('M j, Y', strtotime($p['lastVisit'])) : '—';
                        $doctor    = ($p['docFirst'] && $p['docLast']) ? 'Dr. ' . $p['docFirst'] . ' ' . $p['docLast'] : '—';
                        $statusCls = match ($p['status']) {
                            'Active'     => 'badge-active',
                            'Discharged' => 'badge-discharged',
                            default      => 'badge-inactive'
                        };
                        $condCls = match ($p['patientCondition']) {
                            'Critical'          => 'badge-critical',
                            'Stable'            => 'badge-stable',
                            'Recovering'        => 'badge-recovering',
                            'Under Observation' => 'badge-observation',
                            default             => 'badge-stable'
                        };
                    ?>
                        <tr
                            data-name="<?= htmlspecialchars(strtolower($fullName)) ?>"
                            data-status="<?= htmlspecialchars($p['status']) ?>"
                            data-condition="<?= htmlspecialchars($p['patientCondition']) ?>"
                            data-id="<?= $p['id'] ?>"
                            data-code="<?= htmlspecialchars($p['patientCode']) ?>"
                            data-fullname="<?= htmlspecialchars($fullName) ?>"
                            data-age="<?= $age ?>"
                            data-gender="<?= htmlspecialchars($p['gender']) ?>"
                            data-contact="<?= htmlspecialchars($p['contactNumber'] ?? '—') ?>"
                            data-email="<?= htmlspecialchars($p['emailAddress'] ?? '—') ?>"
                            data-address="<?= htmlspecialchars($p['address'] ?? '—') ?>"
                            data-lastvisit="<?= $lastVisit ?>"
                            data-doctor="<?= htmlspecialchars($doctor) ?>"
                            data-avatar="<?= $initials ?>"
                            data-avatar-bg="<?= $bg ?>"
                            data-avatar-color="<?= $col ?>">
                            <td>
                                <span style="color:#2563eb;font-weight:600;font-size:.83rem;cursor:pointer;"
                                    onclick="viewPatient(this.closest('tr'))">
                                    <?= htmlspecialchars($p['patientCode']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="pat-cell">
                                    <div class="pat-avatar" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $initials ?></div>
                                    <div>
                                        <div class="pat-name"><?= htmlspecialchars($fullName) ?></div>
                                        <div class="pat-id"><?= htmlspecialchars($p['patientCode']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= $age ?> / <?= htmlspecialchars($p['gender']) ?></td>
                            <td><?= htmlspecialchars($p['contactNumber'] ?? '—') ?></td>
                            <td><?= $lastVisit ?></td>
                            <td><?= htmlspecialchars($doctor) ?></td>

                            <td class="status-cell">
                                <button class="badge-btn" onclick="toggleDropdown(this)">
                                    <span class="badge <?= $statusCls ?>"><?= htmlspecialchars($p['status']) ?></span>
                                    <span class="badge-caret">▾</span>
                                </button>
                                <div class="status-dropdown">
                                    <div class="status-opt" onclick="setStatus(this,'Active','badge-active',<?= $p['id'] ?>)"><span class="dot dot-active"></span>Active</div>
                                    <div class="status-opt" onclick="setStatus(this,'Discharged','badge-discharged',<?= $p['id'] ?>)"><span class="dot dot-discharged"></span>Discharged</div>
                                    <div class="status-opt" onclick="setStatus(this,'Inactive','badge-inactive',<?= $p['id'] ?>)"><span class="dot dot-inactive"></span>Inactive</div>
                                </div>
                            </td>

                            <td class="status-cell">
                                <button class="badge-btn" onclick="toggleDropdown(this)">
                                    <span class="badge <?= $condCls ?>"><?= htmlspecialchars($p['patientCondition']) ?></span>
                                    <span class="badge-caret">▾</span>
                                </button>
                                <div class="status-dropdown">
                                    <div class="status-opt" onclick="setCondition(this,'Stable','badge-stable',<?= $p['id'] ?>)"><span class="dot dot-stable"></span>Stable</div>
                                    <div class="status-opt" onclick="setCondition(this,'Recovering','badge-recovering',<?= $p['id'] ?>)"><span class="dot dot-recovering"></span>Recovering</div>
                                    <div class="status-opt" onclick="setCondition(this,'Under Observation','badge-observation',<?= $p['id'] ?>)"><span class="dot dot-observation"></span>Under Observation</div>
                                    <div class="status-opt" onclick="setCondition(this,'Critical','badge-critical',<?= $p['id'] ?>)"><span class="dot dot-critical"></span>Critical</div>
                                </div>
                            </td>

                            <td>
                                <div class="action-btns">
                                    <button class="btn-act" title="Edit" onclick="window.location.href='edit_patient?id=<?= $p['id'] ?>'"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-act view" title="View" onclick="viewPatient(this.closest('tr'))"><i class="bi bi-eye"></i></button>
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

    <div class="vp-hero">
        <div class="vp-hero-top">
            <div class="vp-identity">
                <div class="vp-avatar" id="vpAvatar"></div>
                <div>
                    <div class="vp-name" id="vpName"></div>
                    <div class="vp-spec" id="vpSub"></div>
                </div>
            </div>
            <button class="vp-close-btn" onclick="closePanel()"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="vp-badges-hero" id="vpBadges"></div>
    </div>

    <div class="vp-body">

        <div class="vp-section-hd" style="margin-top:.25rem;">Patient Info</div>
        <div class="vp-info-grid">
            <div class="vp-info-item">
                <div class="vp-info-lbl">Patient ID</div>
                <div class="vp-info-val" id="vpCode"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">Age / Gender</div>
                <div class="vp-info-val" id="vpAgeGender"></div>
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
                <div class="vp-info-lbl">Last Visit</div>
                <div class="vp-info-val" id="vpLastVisit"></div>
            </div>
            <div class="vp-info-item">
                <div class="vp-info-lbl">Doctor</div>
                <div class="vp-info-val" id="vpDoctor"></div>
            </div>
            <div class="vp-info-item" style="grid-column:1/-1;">
                <div class="vp-info-lbl">Address</div>
                <div class="vp-info-val" id="vpAddress"></div>
            </div>
        </div>

        <div class="vp-footer">
            <button class="vp-btn-close" onclick="closePanel()">Close</button>
        </div>
    </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
    const ROWS_PER_PAGE = 10;
    let currentPage = 1;

    function getFilteredRows() {
        const q = document.getElementById('patSearch').value.toLowerCase();
        const st = document.getElementById('statusFilter').value;
        const cond = document.getElementById('conditionFilter').value;
        return Array.from(document.querySelectorAll('#patTbody tr:not(.filler-row)')).filter(row =>
            (!q || (row.dataset.name || '').includes(q)) &&
            (!st || row.dataset.status === st) &&
            (!cond || row.dataset.condition === cond)
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

        document.querySelectorAll('#patTbody tr.filler-row').forEach(r => r.remove());
        document.querySelectorAll('#patTbody tr:not(.filler-row)').forEach(r => r.style.display = 'none');
        rows.forEach((r, i) => {
            r.style.display = (i >= start && i < end) ? '' : 'none';
        });

        const shown = Math.min(end, total) - start;
        const tbody = document.getElementById('patTbody');

        document.getElementById('paginationInfo').textContent = total === 0 ?
            'No patients found' :
            `Showing ${start + 1}–${Math.min(end, total)} of ${total} patient${total !== 1 ? 's' : ''}`;

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

    function toggleDropdown(btn) {
        const dd = btn.nextElementSibling;
        const open = dd.classList.contains('open');
        document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!open) dd.classList.add('open');
    }

    document.addEventListener('click', e => {
        if (!e.target.closest('.status-cell'))
            document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
    });

    function setStatus(optEl, label, cls, patientId) {
        const dd = optEl.closest('.status-dropdown');
        const badge = dd.previousElementSibling.querySelector('.badge');
        badge.className = 'badge ' + cls;
        badge.textContent = label;
        dd.classList.remove('open');
        const row = optEl.closest('tr');
        row.dataset.status = label;

        fetch('/Clinic_Appointment_System/app/controllers/update_patient_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${patientId}&status=${encodeURIComponent(label)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    recount();
                    showToast('✔ Status updated to <strong>' + label + '</strong>', 'info');
                } else showToast('❌ Failed to update status', 'error');
            });
    }

    function setCondition(optEl, label, cls, patientId) {
        const dd = optEl.closest('.status-dropdown');
        const badge = dd.previousElementSibling.querySelector('.badge');
        badge.className = 'badge ' + cls;
        badge.textContent = label;
        dd.classList.remove('open');
        const row = optEl.closest('tr');
        row.dataset.condition = label;

        fetch('update_patient_condition.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${patientId}&condition=${encodeURIComponent(label)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    recount();
                    showToast('✔ Condition updated to <strong>' + label + '</strong>', 'info');
                } else showToast('❌ Failed to update condition', 'error');
            });
    }

    function recount() {
        const allRows = Array.from(document.querySelectorAll('#patTbody tr:not(.filler-row)'));
        let total = 0,
            active = 0,
            critical = 0;
        allRows.forEach(r => {
            const st = r.dataset.status || '';
            const cd = r.dataset.condition || '';
            if (st !== 'Inactive') total++;
            if (st === 'Active') active++;
            if (cd === 'Critical') critical++;
        });
        animateNum('stat-total', total);
        animateNum('stat-active', active);
        animateNum('stat-critical', critical);
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

    const statusBadgeStyle = {
        'Active': 'background:rgba(209,250,229,.25);color:#d1fae5;border:1px solid rgba(209,250,229,.4)',
        'Discharged': 'background:rgba(243,244,246,.2);color:#e5e7eb;border:1px solid rgba(229,231,235,.3)',
        'Inactive': 'background:rgba(243,244,246,.15);color:#d1d5db;border:1px solid rgba(209,213,219,.3)',
    };
    const condBadgeStyle = {
        'Critical': 'background:rgba(254,226,226,.25);color:#fecaca;border:1px solid rgba(252,165,165,.3)',
        'Stable': 'background:rgba(209,250,229,.2);color:#bbf7d0;border:1px solid rgba(134,239,172,.3)',
        'Recovering': 'background:rgba(219,234,254,.2);color:#bfdbfe;border:1px solid rgba(147,197,253,.3)',
        'Under Observation': 'background:rgba(254,243,199,.2);color:#fde68a;border:1px solid rgba(252,211,77,.3)',
    };

    function viewPatient(row) {
        const d = row.dataset;

        const avatar = document.getElementById('vpAvatar');
        avatar.textContent = d.avatar;
        avatar.style.background = d.avatarBg;
        avatar.style.color = d.avatarColor;

        document.getElementById('vpName').textContent = d.fullname;
        document.getElementById('vpSub').textContent = d.age + ' yrs · ' + d.gender;
        document.getElementById('vpCode').textContent = d.code;
        document.getElementById('vpAgeGender').textContent = d.age + ' / ' + d.gender;
        document.getElementById('vpContact').textContent = d.contact;
        document.getElementById('vpEmail').textContent = d.email;
        document.getElementById('vpLastVisit').textContent = d.lastvisit;
        document.getElementById('vpDoctor').textContent = d.doctor;
        document.getElementById('vpAddress').textContent = d.address;

        const sSt = statusBadgeStyle[d.status] || statusBadgeStyle['Inactive'];
        const sCd = condBadgeStyle[d.condition] || condBadgeStyle['Stable'];
        document.getElementById('vpBadges').innerHTML =
            `<span class="vp-badge-pill" style="${sSt}">${escHtml(d.status)}</span>
             <span class="vp-badge-pill" style="${sCd}">${escHtml(d.condition)}</span>`;

        document.getElementById('panelOverlay').classList.add('show');
        const panel = document.getElementById('viewPanel');
        panel.style.display = 'flex';
        requestAnimationFrame(() => panel.classList.add('show'));
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
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
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