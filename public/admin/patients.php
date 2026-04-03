<?php
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

// ── Stats ──────────────────────────────────────────────────────────
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients WHERE status != 'Inactive'")->fetch_row()[0];
$activeCount   = $conn->query("SELECT COUNT(*) FROM patients WHERE status = 'Active'")->fetch_row()[0];
$critical      = $conn->query("SELECT COUNT(*) FROM patients WHERE patientCondition = 'Critical'")->fetch_row()[0];
$followUp      = $conn->query("SELECT COUNT(*) FROM patients WHERE followUpDate IS NOT NULL AND followUpDate >= CURDATE() AND status != 'Inactive'")->fetch_row()[0];

// ── Patient rows ───────────────────────────────────────────────────
$sql = "
    SELECT
        p.id, p.patientCode, p.firstName, p.middleName, p.lastName,
        p.gender, p.dateOfBirth, p.contactNumber, p.emailAddress, p.address,
        p.status, p.patientCondition, p.followUpDate,
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

    /* ── Stat cards ── */
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

    /* ── Table ── */
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
        align-self: center;
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
        min-width: 150px;
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

    /* ── Action btns ── */
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

    /* ── View Modal ── */
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

    .pm-sub {
        font-size: .82rem;
        color: rgba(255, 255, 255, .75);
        margin-top: .25rem;
        font-weight: 500;
    }

    .pm-body {
        padding: 3rem 1.75rem 1.75rem;
    }

    .pm-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
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

    .btn-edit-profile {
        background: var(--blue-600);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .48rem 1.2rem;
        font-size: .82rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background .15s;
    }

    .btn-edit-profile:hover {
        background: var(--blue-700);
    }

    /* ── Toast ── */
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
            transform: translateY(10px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
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
    <!-- ── Stat Cards ── -->
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
        <div class="stat-card">
            <div class="sc-label">Follow-up</div>
            <div class="sc-num" id="stat-followup"><?= $followUp ?></div>
            <div class="sc-sub">Upcoming follow-ups</div>
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
                        $bg       = $avatarBgs[$i % count($avatarBgs)];
                        $col      = $avatarColors[$i % count($avatarColors)];
                        $initials = strtoupper(substr($p['firstName'], 0, 1) . substr($p['lastName'], 0, 1));
                        $fullName = $p['firstName'] . ' ' . $p['lastName'];
                        $age      = $p['age'] ?? '—';
                        $lastVisit = $p['lastVisit'] ? date('M j, Y', strtotime($p['lastVisit'])) : '—';
                        $doctor   = ($p['docFirst'] && $p['docLast']) ? 'Dr. ' . $p['docFirst'] . ' ' . $p['docLast'] : '—';
                        $statusCls = match ($p['status']) {
                            'Active' => 'badge-active',
                            'Discharged' => 'badge-discharged',
                            default => 'badge-inactive'
                        };
                        $condCls = match ($p['patientCondition']) {
                            'Critical' => 'badge-critical',
                            'Stable' => 'badge-stable',
                            'Recovering' => 'badge-recovering',
                            'Under Observation' => 'badge-observation',
                            default => 'badge-stable'
                        };
                    ?>
                        <tr
                            data-name="<?= htmlspecialchars(strtolower($fullName)) ?>"
                            data-status="<?= htmlspecialchars($p['status']) ?>"
                            data-condition="<?= htmlspecialchars($p['patientCondition']) ?>"
                            data-followup="<?= htmlspecialchars($p['followUpDate'] ?? '') ?>"
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
                            <td><?= htmlspecialchars($p['patientCode']) ?></td>
                            <td>
                                <div class="pat-cell">
                                    <div class="pat-avatar" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $initials ?></div>
                                    <div style="display:flex;flex-direction:column;justify-content:center;">
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
                                <button class="badge-btn" onclick="toggleStatusDropdown(this)">
                                    <span class="badge <?= $statusCls ?>"><?= htmlspecialchars($p['status']) ?></span>
                                    <span class="badge-caret">▾</span>
                                </button>
                                <div class="status-dropdown">
                                    <div class="status-opt" onclick="setStatus(this,'Active','badge-active',<?= $p['id'] ?>)"><span class="dot dot-active"></span>Active</div>
                                    <div class="status-opt" onclick="setStatus(this,'Discharged','badge-discharged',<?= $p['id'] ?>)"><span class="dot dot-discharged"></span>Discharged</div>
                                    <div class="status-opt" onclick="setStatus(this,'Inactive','badge-inactive',<?= $p['id'] ?>)"><span class="dot dot-inactive"></span>Inactive</div>
                                </div>
                            </td>
                            <td><span class="badge <?= $condCls ?>"><?= htmlspecialchars($p['patientCondition']) ?></span></td>
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

<!-- ── View Modal ── -->
<div class="modal-overlay" id="viewModal">
    <div class="profile-modal">
        <div class="pm-hero">
            <button class="pm-hero-close" onclick="closeModal()"><i class="bi bi-x"></i></button>
            <div class="pm-identity">
                <div class="pm-avatar-lg" id="pmAvatar"></div>
                <div class="pm-name-block">
                    <div class="pm-name" id="pmName"></div>
                    <div class="pm-sub" id="pmSub"></div>
                </div>
            </div>
        </div>
        <div class="pm-body">
            <div class="pm-badges" id="pmBadges"></div>
            <div class="pm-grid">
                <div class="pm-item">
                    <div class="pm-lbl">Patient ID</div>
                    <div class="pm-val" id="pmCode"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Age / Gender</div>
                    <div class="pm-val" id="pmAgeGender"></div>
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
                    <div class="pm-lbl">Last Visit</div>
                    <div class="pm-val" id="pmLastVisit"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Doctor</div>
                    <div class="pm-val" id="pmDoctor"></div>
                </div>
                <div class="pm-item" style="grid-column:1/-1;">
                    <div class="pm-lbl">Address</div>
                    <div class="pm-val" id="pmAddress"></div>
                </div>
                <div class="pm-item">
                    <div class="pm-lbl">Follow-up Date</div>
                    <div class="pm-val" id="pmFollowup"></div>
                </div>
            </div>
            <div class="pm-footer">
                <button class="btn-close-profile" onclick="closeModal()">Close</button>
                <button class="btn-edit-profile" id="pmEditBtn"><i class="bi bi-pencil"></i> Edit</button>
            </div>
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
        for (let f = 0; f < ROWS_PER_PAGE - shown && shown > 0; f++) {
            const tr = document.createElement('tr');
            tr.className = 'filler-row';
            tr.style.pointerEvents = 'none';
            for (let c = 0; c < 9; c++) {
                const td = document.createElement('td');
                td.innerHTML = '&nbsp;';
                tr.appendChild(td);
            }
            tbody.appendChild(tr);
        }
        document.getElementById('paginationInfo').textContent = total === 0 ?
            'No patients found' :
            `Showing ${start+1}–${Math.min(end,total)} of ${total} patient${total!==1?'s':''}`;
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

    // ── Status dropdown ──
    function toggleStatusDropdown(btn) {
        const dd = btn.nextElementSibling;
        const open = dd.classList.contains('open');
        document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!open) dd.classList.add('open');
    }

    function setStatus(optEl, label, cls, patientId) {
        const dd = optEl.closest('.status-dropdown');
        const badge = dd.previousElementSibling.querySelector('.badge');
        badge.className = 'badge ' + cls;
        badge.textContent = label;
        dd.classList.remove('open');
        const row = optEl.closest('tr');
        row.dataset.status = label;

        fetch('update_patient_status.php', {
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
    document.addEventListener('click', e => {
        if (!e.target.closest('.status-cell'))
            document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
    });

    // ── Live stat recount ──
    function recount() {
        const allRows = Array.from(document.querySelectorAll('#patTbody tr:not(.filler-row)'));
        let total = 0,
            active = 0,
            critical = 0,
            followup = 0;
        allRows.forEach(r => {
            const st = r.dataset.status || '';
            const cd = r.dataset.condition || '';
            const fu = r.dataset.followup || '';
            if (st !== 'Inactive') total++;
            if (st === 'Active') active++;
            if (cd === 'Critical') critical++;
            if (fu && fu >= new Date().toISOString().slice(0, 10) && st !== 'Inactive') followup++;
        });
        animateNum('stat-total', total);
        animateNum('stat-active', active);
        animateNum('stat-critical', critical);
        animateNum('stat-followup', followup);
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

    // ── View modal ──
    function viewPatient(row) {
        const d = row.dataset;
        const statusCls = {
            Active: 'badge-active',
            Discharged: 'badge-discharged',
            Inactive: 'badge-inactive'
        } [d.status] || 'badge-inactive';
        const condCls = {
            Critical: 'badge-critical',
            Stable: 'badge-stable',
            Recovering: 'badge-recovering',
            'Under Observation': 'badge-observation'
        } [d.condition] || 'badge-stable';

        document.getElementById('pmAvatar').textContent = d.avatar;
        document.getElementById('pmAvatar').style.background = d.avatarBg;
        document.getElementById('pmAvatar').style.color = d.avatarColor;
        document.getElementById('pmName').textContent = d.fullname;
        document.getElementById('pmSub').textContent = d.age + ' yrs · ' + d.gender;
        document.getElementById('pmCode').textContent = d.code;
        document.getElementById('pmAgeGender').textContent = d.age + ' / ' + d.gender;
        document.getElementById('pmContact').textContent = d.contact;
        document.getElementById('pmEmail').textContent = d.email;
        document.getElementById('pmLastVisit').textContent = d.lastvisit;
        document.getElementById('pmDoctor').textContent = d.doctor;
        document.getElementById('pmAddress').textContent = d.address;
        document.getElementById('pmFollowup').textContent = d.followup || '—';
        document.getElementById('pmEditBtn').onclick = () => window.location.href = 'edit_patient.php?id=' + d.id;
        document.getElementById('pmBadges').innerHTML =
            `<span class="badge ${statusCls}">${d.status}</span>
         <span class="badge ${condCls}">${d.condition}</span>`;
        document.getElementById('viewModal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('viewModal').classList.remove('show');
    }
    document.getElementById('viewModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
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