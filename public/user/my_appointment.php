<?php
session_start();
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

$userId = $_SESSION['authUser']['user_id'] ?? 0;
$userEmail = '';
if ($userId) {
    $uStmt = $conn->prepare("SELECT emailAddress FROM users WHERE id = ? LIMIT 1");
    $uStmt->bind_param('i', $userId);
    $uStmt->execute();
    $uRow = $uStmt->get_result()->fetch_assoc();
    $userEmail = $uRow['emailAddress'] ?? '';
}

$patientRow = null;
if ($userEmail) {
    $stmt = $conn->prepare("SELECT * FROM patients WHERE emailAddress=? AND status!='Inactive' LIMIT 1");
    $stmt->bind_param('s', $userEmail);
    $stmt->execute();
    $patientRow = $stmt->get_result()->fetch_assoc();
}

$patientId = $patientRow['id'] ?? 0;

$today = date('Y-m-d');

// Fetch ALL appointments booked by this user (regardless of which patient name was used)
$statTotal     = $conn->query("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=$userId")->fetch_row()[0];
$statUpcoming  = $conn->query("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=$userId AND appointmentDate>='$today' AND status IN ('Pending','In Progress')")->fetch_row()[0];
$statCompleted = $conn->query("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=$userId AND status='Completed'")->fetch_row()[0];
$statCancelled = $conn->query("SELECT COUNT(*) FROM appointments WHERE bookedByUserId=$userId AND status='Cancelled'")->fetch_row()[0];

$appointments = $conn->query("
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

$filterStatus = $_GET['status'] ?? '';
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
        --teal: #06b6d4;
        --teal-light: #cffafe;
        --teal-dark: #155e75;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
    }

    .page-myappt,
    .page-myappt * {
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
        cursor: pointer;
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
        border-left-color: var(--red);
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

    .btn-book {
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
        transition: background .15s;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-book:hover {
        background: var(--blue-700);
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
    }

    .table tbody td {
        font-size: .83rem;
        color: var(--text-body);
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
        padding: .75rem .6rem;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr:hover td {
        background: var(--blue-50);
    }

    .appt-code {
        font-weight: 700;
        color: var(--blue-700);
        font-size: .8rem;
    }

    .doc-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: .82rem;
    }

    .doc-spec {
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

    .bg-success {
        background: var(--green-light) !important;
        color: var(--green-dark) !important;
    }

    .bg-warning {
        background: var(--amber-light) !important;
        color: var(--amber-dark) !important;
    }

    .bg-danger {
        background: var(--red-light) !important;
        color: var(--red-dark) !important;
    }

    .bg-info {
        background: var(--teal-light) !important;
        color: var(--teal-dark) !important;
    }

    .bg-secondary {
        background: #f3f4f6 !important;
        color: #374151 !important;
    }

    .channel-chip {
        font-size: .65rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 5px;
        background: var(--blue-50);
        color: var(--blue-700);
        border: 1px solid var(--blue-100);
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
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-act:hover {
        background: var(--blue-50);
        color: var(--blue-600);
        border-color: var(--blue-200);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: .75rem;
        opacity: .3;
    }

    .empty-state p {
        font-size: .85rem;
        margin: 0;
    }

    .alert-success-custom {
        background: var(--green-light);
        border: 1px solid #6ee7b7;
        border-radius: var(--radius-sm);
        padding: .85rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: .875rem;
        color: var(--green-dark);
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .alert-error-custom {
        background: var(--red-light);
        border: 1px solid #fca5a5;
        border-radius: var(--radius-sm);
        padding: .85rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: .875rem;
        color: var(--red-dark);
        font-weight: 500;
        margin-bottom: 1rem;
    }

    /* Modal */
    .modal-content {
        border-radius: var(--radius);
        border: 1px solid var(--border);
        font-family: 'DM Sans', sans-serif;
    }

    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 1.1rem 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border);
        padding: .85rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-title {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-dark);
    }

    .detail-group {
        margin-bottom: 1rem;
    }

    .detail-label {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: 3px;
    }

    .detail-value {
        font-size: .875rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .btn-modal-close {
        background: #fff;
        color: var(--text-body);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .45rem 1.2rem;
        font-size: .84rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-modal-close:hover {
        background: var(--surface);
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
    <h1>My Appointments</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">My Appointments</li>
        </ol>
    </nav>
</div>

<section class="section page-myappt">

    <div id="successAlert" class="alert-success-custom" style="display:none">
        <i class="bi bi-check-circle-fill"></i>
        <div id="successMsg"></div>
    </div>
    <div id="errorAlert" class="alert-error-custom" style="display:none">
        <i class="bi bi-x-circle-fill"></i>
        <div id="errorMsg"></div>
    </div>

    <div class="stat-strip">
        <a href="my_appointments" class="stat-card">
            <div class="sc-label">Total</div>
            <div class="sc-num"><?= $statTotal ?></div>
            <div class="sc-sub">All appointments</div>
        </a>
        <a href="my_appointments?status=upcoming" class="stat-card">
            <div class="sc-label">Upcoming</div>
            <div class="sc-num"><?= $statUpcoming ?></div>
            <div class="sc-sub">Scheduled sessions</div>
        </a>
        <a href="my_appointments?status=Completed" class="stat-card">
            <div class="sc-label">Completed</div>
            <div class="sc-num"><?= $statCompleted ?></div>
            <div class="sc-sub">Finished visits</div>
        </a>
        <a href="my_appointments?status=Cancelled" class="stat-card">
            <div class="sc-label">Cancelled</div>
            <div class="sc-num"><?= $statCancelled ?></div>
            <div class="sc-sub">Cancelled bookings</div>
        </a>
    </div>

    <div class="main-card">
        <div class="table-toolbar">
            <h5>All Appointments <span>| <?= date('F j, Y') ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="apptSearch" placeholder="Search doctor or code…" oninput="filterTable()">
            </div>
            <select class="filter-select" id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="Pending" <?= $filterStatus === 'Pending'      ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= $filterStatus === 'In Progress'  ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= $filterStatus === 'Completed'    ? 'selected' : '' ?>>Completed</option>
                <option value="Cancelled" <?= $filterStatus === 'Cancelled'    ? 'selected' : '' ?>>Cancelled</option>
                <option value="upcoming" <?= $filterStatus === 'upcoming'     ? 'selected' : '' ?>>Upcoming</option>
            </select>
            <a href="book_appointment" class="btn-book"><i class="bi bi-plus-lg"></i> Book New</a>
        </div>

        <div style="overflow-x:auto">
            <table class="table" id="apptTable">
                <thead>
                    <tr>
                        <th># Code</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="apptTbody">
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-calendar-x"></i>
                                    <p>No appointments found.<br>
                                        <a href="book_appointment" style="color:var(--blue-600);font-weight:600">Book your first appointment →</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appt):
                            $statusMap = ['Completed' => 'bg-success', 'In Progress' => 'bg-info', 'Pending' => 'bg-warning', 'Cancelled' => 'bg-danger'];
                            $sCls = $statusMap[$appt['status']] ?? 'bg-secondary';
                            $isUpcoming = $appt['appointmentDate'] >= $today && in_array($appt['status'], ['Pending', 'In Progress']);
                        ?>
                            <tr data-code="<?= strtolower($appt['appointmentCode']) ?>"
                                data-doctor="<?= strtolower($appt['doctorName']) ?>"
                                data-status="<?= $appt['status'] ?>"
                                data-upcoming="<?= $isUpcoming ? '1' : '0' ?>">
                                <td><span class="appt-code"><?= htmlspecialchars($appt['appointmentCode']) ?></span></td>
                                <td style="font-size:.82rem;font-weight:600;color:var(--text-dark)"><?= htmlspecialchars($appt['patientName']) ?></td>
                                <td>
                                    <div class="doc-name"><?= htmlspecialchars($appt['doctorName']) ?></div>
                                    <div class="doc-spec"><?= htmlspecialchars($appt['specialization']) ?></div>
                                </td>
                                <td style="font-size:.8rem"><?= htmlspecialchars($appt['department'] ?? '—') ?></td>
                                <td style="font-size:.82rem"><?= date('M j, Y', strtotime($appt['appointmentDate'])) ?></td>
                                <td style="font-size:.82rem"><?= date('g:i A', strtotime($appt['appointmentTime'])) ?></td>
                                <td><span class="channel-chip"><?= htmlspecialchars($appt['channel']) ?></span></td>
                                <td><span class="badge <?= $sCls ?>"><?= htmlspecialchars($appt['status']) ?></span></td>
                                <td style="display:flex;gap:5px;align-items:center;">
                                    <button class="btn-act" onclick="openViewAppt(
        '<?= htmlspecialchars($appt['appointmentCode']) ?>',
        '<?= htmlspecialchars($appt['doctorName']) ?>',
        '<?= htmlspecialchars($appt['specialization']) ?>',
        '<?= htmlspecialchars($appt['department'] ?? '—') ?>',
        '<?= date('F j, Y', strtotime($appt['appointmentDate'])) ?>',
        '<?= date('g:i A', strtotime($appt['appointmentTime'])) ?>',
        '<?= htmlspecialchars($appt['status']) ?>',
        '<?= htmlspecialchars($appt['channel']) ?>'
    )"><i class="bi bi-eye"></i></button>

                                    <?php if ($isUpcoming): ?>
                                        <button class="btn-act"
                                            style="color:var(--red);border-color:#fca5a5;"
                                            onclick="doCancel(<?= $appt['id'] ?>, '<?= $appt['appointmentCode'] ?>')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:.75rem;font-size:.75rem;color:var(--text-muted)" id="tableInfo"></div>
    </div>

    <div class="modal fade" id="cancelConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius:16px;border:1px solid var(--border);">
                <div class="modal-body" style="padding:1.75rem 1.5rem;text-align:center;">
                    <div style="width:52px;height:52px;border-radius:50%;background:var(--red-light);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="bi bi-x-circle-fill" style="font-size:1.4rem;color:var(--red);"></i>
                    </div>
                    <div style="font-weight:700;font-size:1rem;color:var(--text-dark);margin-bottom:.4rem;">Cancel Appointment?</div>
                    <div style="font-size:.82rem;color:var(--text-muted);margin-bottom:1.5rem;" id="cancelConfirmMsg">
                        This action cannot be undone.
                    </div>
                    <div style="display:flex;gap:8px;justify-content:center;">
                        <button class="btn-modal-close" data-bs-dismiss="modal">Keep It</button>
                        <button id="cancelConfirmBtn"
                            style="background:var(--red);color:#fff;border:none;border-radius:var(--radius-sm);padding:.45rem 1.2rem;font-size:.84rem;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                            <i class="bi bi-x-lg"></i> Yes, Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<div class="modal fade" id="viewApptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-check me-2" style="color:var(--blue-500)"></i>Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Appointment Code</div>
                            <div class="detail-value" id="mappt-code">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Date</div>
                            <div class="detail-value" id="mappt-date">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Time</div>
                            <div class="detail-value" id="mappt-time">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Status</div>
                            <div class="detail-value" id="mappt-status">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Channel</div>
                            <div class="detail-value" id="mappt-channel">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Department</div>
                            <div class="detail-value" id="mappt-dept">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Attending Physician</div>
                            <div class="detail-value" id="mappt-doctor">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Specialization</div>
                            <div class="detail-value" id="mappt-spec">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const urlStatus = '<?= htmlspecialchars($filterStatus) ?>';
        if (urlStatus) {
            document.getElementById('statusFilter').value = urlStatus;
            filterTable();
        }
        updateTableInfo();
    });

    function openViewAppt(code, doctor, spec, dept, date, time, status, channel) {
        document.getElementById('mappt-code').textContent = code;
        document.getElementById('mappt-doctor').textContent = doctor;
        document.getElementById('mappt-spec').textContent = spec;
        document.getElementById('mappt-dept').textContent = dept;
        document.getElementById('mappt-date').textContent = date;
        document.getElementById('mappt-time').textContent = time;
        document.getElementById('mappt-status').textContent = status;
        document.getElementById('mappt-channel').textContent = channel;
        new bootstrap.Modal(document.getElementById('viewApptModal')).show();
    }

    function filterTable() {
        const q = document.getElementById('apptSearch').value.toLowerCase();
        const st = document.getElementById('statusFilter').value;
        let visible = 0;

        document.querySelectorAll('#apptTbody tr[data-code]').forEach(row => {
            const matchSearch = !q || row.dataset.code.includes(q) || row.dataset.doctor.includes(q);
            let matchStatus = true;
            if (st === 'upcoming') {
                matchStatus = row.dataset.upcoming === '1';
            } else if (st) {
                matchStatus = row.dataset.status === st;
            }
            const show = matchSearch && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        updateTableInfo(visible);
    }

    function updateTableInfo(visible) {
        const total = document.querySelectorAll('#apptTbody tr[data-code]').length;
        const shown = visible !== undefined ? visible : total;
        document.getElementById('tableInfo').textContent =
            total ? `Showing ${shown} of ${total} appointments` : '';
    }

    let _cancelId = null,
        _cancelCode = null;

    function doCancel(id, code) {
        _cancelId = id;
        _cancelCode = code;
        document.getElementById('cancelConfirmMsg').textContent =
            `Appointment ${code} will be marked as cancelled. This cannot be undone.`;
        new bootstrap.Modal(document.getElementById('cancelConfirmModal')).show();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('cancelConfirmBtn').addEventListener('click', () => {
            const fd = new FormData();
            fd.append('id', _cancelId);
            bootstrap.Modal.getInstance(document.getElementById('cancelConfirmModal')).hide();
            fetch('../../app/controllers/bookapp_handler.php?action=cancel_appointment', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('successMsg').textContent = `Appointment ${_cancelCode} cancelled successfully.`;
                        document.getElementById('successAlert').style.display = 'flex';
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        document.getElementById('errorMsg').textContent = res.message || 'Could not cancel. Please try again.';
                        document.getElementById('errorAlert').style.display = 'flex';
                        setTimeout(() => document.getElementById('errorAlert').style.display = 'none', 4000);
                    }
                })
                .catch(() => {
                    document.getElementById('errorMsg').textContent = 'Network error. Please try again.';
                    document.getElementById('errorAlert').style.display = 'flex';
                });
        });
    });
</script>

<?php include('./includes/footer.php'); ?>