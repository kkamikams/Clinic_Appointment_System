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

if ($patientId) {
    $statTotal = $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE patientId=$patientId")->fetch_row()[0];
    $statMonth = $conn->query("SELECT COUNT(*) FROM medicalRecords WHERE patientId=$patientId AND MONTH(createdAt)=MONTH(CURDATE()) AND YEAR(createdAt)=YEAR(CURDATE())")->fetch_row()[0];
    $statDoctors = $conn->query("SELECT COUNT(DISTINCT doctorId) FROM medicalRecords WHERE patientId=$patientId")->fetch_row()[0];
    $statDepts   = $conn->query("SELECT COUNT(DISTINCT d.specialization) FROM medicalRecords mr JOIN doctors d ON d.id=mr.doctorId WHERE mr.patientId=$patientId")->fetch_row()[0];

    $records = $conn->query("
        SELECT mr.*,
               CONCAT('Dr. ',d.firstName,' ',d.lastName) AS doctorName,
               d.specialization, d.department
        FROM medicalRecords mr
        JOIN doctors d ON d.id=mr.doctorId
        WHERE mr.patientId=$patientId
        ORDER BY mr.createdAt DESC
    ")->fetch_all(MYSQLI_ASSOC);
} else {
    $statTotal = $statMonth = $statDoctors = $statDepts = 0;
    $records = [];
}

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

    .page-rec,
    .page-rec * {
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
        margin-bottom: 1.5rem
    }

    @media(max-width:768px) {
        .stat-strip {
            grid-template-columns: repeat(2, 1fr)
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
        animation: fadeUp .32s ease both
    }

    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px)
    }

    .stat-card:nth-child(1) {
        border-left-color: var(--blue-500);
        animation-delay: .04s
    }

    .stat-card:nth-child(2) {
        border-left-color: var(--green);
        animation-delay: .09s
    }

    .stat-card:nth-child(3) {
        border-left-color: var(--teal);
        animation-delay: .14s
    }

    .stat-card:nth-child(4) {
        border-left-color: var(--violet);
        animation-delay: .19s
    }

    .sc-label {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-muted);
        margin-bottom: .45rem
    }

    .sc-num {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -.05em;
        line-height: 1
    }

    .sc-sub {
        font-size: .7rem;
        color: var(--text-muted);
        margin-top: .25rem
    }

    .main-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.5rem;
        animation: fadeUp .32s .22s ease both
    }

    .table-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem
    }

    .table-toolbar h5 {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-body);
        margin: 0;
        flex: 1
    }

    .table-toolbar h5 span {
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: var(--text-muted);
        font-size: .7rem;
        margin-left: 4px
    }

    .search-box {
        position: relative
    }

    .search-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: .8rem;
        pointer-events: none
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
        transition: border-color .2s
    }

    .search-box input:focus {
        border-color: var(--blue-400);
        background: #fff
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
        cursor: pointer
    }

    .table {
        width: 100%;
        border-collapse: collapse
    }

    .table thead th {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        padding: .65rem .6rem;
        background: transparent
    }

    .table tbody td {
        font-size: .83rem;
        color: var(--text-body);
        vertical-align: middle;
        border-bottom: 1px solid var(--border);
        padding: .75rem .6rem
    }

    .table tbody tr:last-child td {
        border-bottom: none
    }

    .table tbody tr:hover td {
        background: var(--blue-50)
    }

    .rec-id {
        font-weight: 700;
        color: var(--blue-700);
        font-size: .8rem
    }

    .doc-cell {
        display: flex;
        align-items: center;
        gap: 9px
    }

    .doc-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid var(--border);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .68rem;
        font-weight: 700
    }

    .doc-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: .82rem
    }

    .doc-spec {
        font-size: .66rem;
        color: var(--text-muted)
    }

    .diag-pill {
        display: inline-block;
        background: var(--teal-light);
        color: var(--teal-dark);
        border-radius: 6px;
        font-size: .63rem;
        font-weight: 600;
        padding: 2px 8px;
        letter-spacing: .03em
    }

    .action-btns {
        display: flex;
        gap: 5px;
        flex-wrap: wrap
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
        white-space: nowrap
    }

    .btn-act:hover {
        background: var(--blue-50);
        color: var(--blue-600);
        border-color: var(--blue-200)
    }

    .btn-act.dl:hover {
        background: var(--green-light);
        color: var(--green-dark);
        border-color: #6ee7b7
    }

    .tbl-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 1rem
    }

    .tbl-footer span {
        font-size: .75rem;
        color: var(--text-muted)
    }

    .pg-btns {
        display: flex;
        gap: 5px
    }

    .pg-btns button {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 4px 12px;
        font-size: .78rem;
        font-family: 'DM Sans', sans-serif;
        background: #fff;
        color: var(--text-body);
        cursor: pointer;
        transition: background .15s
    }

    .pg-btns button.active {
        background: var(--blue-600);
        color: #fff;
        border-color: var(--blue-600)
    }

    .modal-content {
        border-radius: var(--radius);
        border: 1px solid var(--border);
        font-family: 'DM Sans', sans-serif
    }

    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 1.1rem 1.5rem
    }

    .modal-footer {
        border-top: 1px solid var(--border);
        padding: .85rem 1.5rem
    }

    .modal-body {
        padding: 1.5rem
    }

    .modal-title {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-dark)
    }

    .detail-group {
        margin-bottom: 1rem
    }

    .detail-label {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: 3px
    }

    .detail-value {
        font-size: .875rem;
        font-weight: 600;
        color: var(--text-dark)
    }

    .detail-text {
        font-size: .85rem;
        color: var(--text-body);
        line-height: 1.6
    }

    .rx-box {
        background: var(--blue-50);
        border: 1px solid var(--blue-100);
        border-radius: var(--radius-sm);
        padding: .85rem 1rem;
        font-size: .83rem;
        color: var(--text-body);
        line-height: 1.7
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
        gap: 6px
    }

    .btn-modal-close:hover {
        background: var(--surface)
    }

    .btn-dl-sm {
        background: var(--green);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .45rem 1.2rem;
        font-size: .84rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .15s
    }

    .btn-dl-sm:hover {
        background: var(--green-dark)
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted)
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: .75rem;
        display: block;
        opacity: .4
    }

    .empty-state p {
        font-size: .875rem;
        margin: 0
    }

    .dl-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        background: var(--green-light);
        border: 1px solid #6ee7b7;
        color: var(--green-dark);
        border-radius: var(--radius-sm);
        padding: .75rem 1.25rem;
        font-size: .875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: var(--shadow-md);
        z-index: 9999;
        animation: fadeUp .2s ease
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
    <h1>Medical Records</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Medical Records</li>
        </ol>
    </nav>
</div>

<section class="section page-rec">
    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Records</div>
            <div class="sc-num"><?= $statTotal ?></div>
            <div class="sc-sub">All medical records</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">This Month</div>
            <div class="sc-num"><?= $statMonth ?></div>
            <div class="sc-sub"><?= date('F Y') ?></div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Doctors Seen</div>
            <div class="sc-num"><?= $statDoctors ?></div>
            <div class="sc-sub">Unique physicians</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Departments</div>
            <div class="sc-num"><?= $statDepts ?></div>
            <div class="sc-sub">Specializations</div>
        </div>
    </div>

    <div class="main-card">
        <div class="table-toolbar">
            <h5>All Medical Records <span>| <?= date('F j, Y') ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="recSearch" placeholder="Search diagnosis or doctor…" oninput="filterRecords()">
            </div>
            <select class="filter-select" id="deptFilter" onchange="filterRecords()">
                <option value="">All Departments</option>
                <?php
                $depts = array_unique(array_column($records, 'specialization'));
                sort($depts);
                foreach ($depts as $dept) echo "<option>" . htmlspecialchars($dept) . "</option>";
                ?>
            </select>
        </div>

        <div style="overflow-x:auto">
            <table class="table" id="recTable">
                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="recTbody">
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-file-medical"></i>
                                    <p>No medical records found.<br>Records will appear here after your appointments are completed.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $i => $rec):
                            $bg  = $avatarBgs[$i % count($avatarBgs)];
                            $col = $avatarColors[$i % count($avatarColors)];
                            $doctorParts = explode(' ', $rec['doctorName']);
                            $ini = strtoupper(substr($doctorParts[1] ?? 'D', 0, 1) . substr(end($doctorParts), 0, 1));
                            $typeMap = ['Consultation' => 'chip-consultation', 'Lab Result' => 'chip-lab', 'Imaging' => 'chip-imaging', 'Prescription' => 'chip-prescription', 'Other' => 'chip-other'];
                            $tCls = $typeMap[$rec['recordType']] ?? 'chip-other';
                            $rxLines = array_filter(array_map('trim', explode("\n", $rec['prescription'] ?? '')));
                            $rxPreview = implode(' · ', array_slice($rxLines, 0, 2)) ?: ($rec['prescription'] ?? '—');
                        ?>
                            <tr data-doctor="<?= htmlspecialchars(strtolower($rec['doctorName'])) ?>"
                                data-dept="<?= htmlspecialchars($rec['specialization']) ?>"
                                data-diag="<?= htmlspecialchars(strtolower($rec['diagnosis'] ?? '')) ?>">
                                <td><span class="rec-id"><?= htmlspecialchars($rec['recordCode']) ?></span></td>
                                <td>
                                    <div class="doc-cell">
                                        <div class="doc-avatar" style="background:<?= $bg ?>;color:<?= $col ?>"><?= $ini ?></div>
                                        <div>
                                            <div class="doc-name"><?= htmlspecialchars($rec['doctorName']) ?></div>
                                            <div class="doc-spec"><?= htmlspecialchars($rec['specialization']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php if ($rec['diagnosis']): ?><span class="diag-pill"><?= htmlspecialchars($rec['diagnosis']) ?></span><?php else: ?>—<?php endif; ?></td>
                                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($rec['prescription'] ?? '') ?>">
                                    <?= htmlspecialchars($rxPreview) ?>
                                </td>
                                <td><span class="btn-act" style="cursor:default;<?= $tCls === 'chip-lab' ? 'background:var(--violet-light);color:var(--violet-dark);border-color:#ddd6fe' : ($tCls === 'chip-prescription' ? 'background:var(--green-light);color:var(--green-dark);border-color:#a7f3d0' : ($tCls === 'chip-imaging' ? 'background:var(--teal-light);color:var(--teal-dark);border-color:#a5f3fc' : 'background:var(--blue-50);color:var(--blue-700);border-color:var(--blue-100)')) ?>"><?= htmlspecialchars($rec['recordType']) ?></span></td>
                                <td><?= date('M j, Y', strtotime($rec['createdAt'])) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="btn-act" onclick="openViewRecord(
                    '<?= htmlspecialchars($rec['recordCode']) ?>',
                    '<?= htmlspecialchars($rec['doctorName']) ?>',
                    '<?= htmlspecialchars($rec['specialization']) ?>',
                    '<?= date('F j, Y', strtotime($rec['createdAt'])) ?>',
                    '<?= htmlspecialchars(addslashes($rec['diagnosis'] ?? '—')) ?>',
                    '<?= htmlspecialchars(addslashes($rec['prescription'] ?? '—')) ?>',
                    '<?= htmlspecialchars(addslashes($rec['notes'] ?? '—')) ?>'
                )">View</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="empty-state" id="emptyState" style="display:none">
                <i class="bi bi-file-medical"></i>
                <p>No records match your search.</p>
            </div>
        </div>

        <div class="tbl-footer">
            <span id="showingLabel">Showing <?= count($records) ?> of <?= count($records) ?> records</span>
            <div class="pg-btns"><button class="active">1</button></div>
        </div>
    </div>
</section>

<div class="modal fade" id="viewRecordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-medical me-2" style="color:var(--teal)"></i>Medical Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Record ID</div>
                            <div class="detail-value" id="mrec-id">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Date</div>
                            <div class="detail-value" id="mrec-date">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Department</div>
                            <div class="detail-value" id="mrec-dept">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Attending Physician</div>
                            <div class="detail-value" id="mrec-doctor">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Diagnosis</div>
                            <div class="detail-value" id="mrec-diag" style="color:var(--teal-dark)">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Prescription / Treatment Plan</div>
                            <div class="rx-box" id="mrec-rx">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Physician's Notes</div>
                            <div class="detail-text" id="mrec-notes">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Close</button>
            </div>
        </div>
    </div>

    <div id="dlToast" class="dl-toast" style="display:none">
        <i class="bi bi-check-circle-fill"></i><span id="dlToastMsg">Downloaded successfully!</span>
    </div>

    <script>
        function filterRecords() {
            const q = document.getElementById('recSearch').value.toLowerCase();
            const dept = document.getElementById('deptFilter').value;
            let vis = 0;
            document.querySelectorAll('#recTbody tr[data-doctor]').forEach(row => {
                const doc = row.dataset.doctor || '';
                const diag = row.dataset.diag || '';
                const d = row.dataset.dept || '';
                const show = (!q || doc.includes(q) || diag.includes(q)) && (!dept || d === dept);
                row.style.display = show ? '' : 'none';
                if (show) vis++;
            });
            document.getElementById('emptyState').style.display = vis === 0 ? '' : 'none';
            const total = document.querySelectorAll('#recTbody tr[data-doctor]').length;
            document.getElementById('showingLabel').textContent = `Showing ${vis} of ${total} records`;
        }

        function openViewRecord(id, doctor, dept, date, diag, rx, notes) {
            document.getElementById('mrec-id').textContent = id;
            document.getElementById('mrec-doctor').textContent = doctor;
            document.getElementById('mrec-dept').textContent = dept;
            document.getElementById('mrec-date').textContent = date;
            document.getElementById('mrec-diag').textContent = diag;
            document.getElementById('mrec-notes').textContent = notes;
            const lines = rx.split(/[\n·]/).map(l => l.trim()).filter(Boolean);
            document.getElementById('mrec-rx').innerHTML = lines.length ?
                lines.map(l => `<div style="padding:3px 0">• ${l}</div>`).join('') :
                '<em style="color:var(--text-muted)">No prescription recorded.</em>';
            new bootstrap.Modal(document.getElementById('viewRecordModal')).show();
        }

        let toastTimer;

        function fakeDownload(id) {
            document.getElementById('dlToastMsg').textContent = `${id} downloaded successfully!`;
            const toast = document.getElementById('dlToast');
            toast.style.display = 'flex';
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>

    <?php include('./includes/footer.php'); ?>