<?php
session_start();
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: doctors.php');
    exit;
}

$stmt = $conn->prepare("
        SELECT d.*,
            GROUP_CONCAT(DISTINCT ds.dayOfWeek ORDER BY FIELD(ds.dayOfWeek,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') SEPARATOR ',') AS workingDays,
            MIN(ds.shiftStart) AS shiftStart,
            MAX(ds.shiftEnd)   AS shiftEnd
        FROM doctors d
        LEFT JOIN doctorSchedules ds ON ds.doctorId = d.id
        WHERE d.id = ?
        GROUP BY d.id
    ");
$stmt->bind_param('i', $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    header('Location: doctors.php');
    exit;
}

$workingDays = $doc['workingDays'] ? explode(',', $doc['workingDays']) : [];
$fullname = 'Dr. ' . $doc['firstName'] . ' ' . ($doc['middleName'] ? $doc['middleName'] . ' ' : '') . $doc['lastName'];

$specs = ['General Medicine', 'Cardiology', 'Pediatrics', 'Dermatology', 'Orthopedics', 'OB-GYN', 'Neurology', 'Psychiatry', 'Ophthalmology', 'ENT', 'Oncology', 'Radiology', 'Other'];
$depts = ['Internal Medicine', 'Cardiology', 'Pediatrics', 'Dermatology', 'Orthopedics', 'OB-GYN', 'Neurology', 'Emergency', 'Surgery', 'Other'];
$allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$dayAbbr = ['Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed', 'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat', 'Sunday' => 'Sun'];
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
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
    }

    .page-edit,
    .page-edit * {
        font-family: 'DM Sans', sans-serif;
        box-sizing: border-box;
    }

    .pagetitle h1 {
        font-family: 'DM Sans', sans-serif;
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

    .edit-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 1.25rem;
        align-items: start;
    }

    @media(max-width:900px) {
        .edit-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Left column stacks profile card + status card */
    .edit-left-col {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .profile-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: visible;
        animation: fadeUp .3s ease both;
    }

    .profile-card-banner {
        height: 80px;
        background: linear-gradient(135deg, var(--blue-600), var(--blue-700));
        border-radius: var(--radius) var(--radius) 0 0;
        overflow: hidden;
    }

    .profile-card-body {
        padding: 0 1.25rem 1.5rem;
        text-align: center;
    }

    .profile-card-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: var(--shadow-md);
        background: var(--blue-100);
        color: var(--blue-700);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        margin: -36px auto 0;
        position: relative;
    }

    .profile-card-name {
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-top: .75rem;
        letter-spacing: -.02em;
    }

    .profile-card-spec {
        font-size: .75rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .profile-card-id {
        display: inline-block;
        margin-top: .6rem;
        background: var(--blue-50);
        color: var(--blue-700);
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .08em;
        border-radius: 6px;
        padding: 2px 10px;
    }

    .emp-status-badge {
        display: inline-block;
        margin-top: .75rem;
        font-size: .7rem;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 14px;
        letter-spacing: .04em;
    }

    .emp-status-badge.active {
        background: var(--green-light);
        color: var(--green-dark);
    }

    .emp-status-badge.on-leave {
        background: var(--amber-light);
        color: var(--amber-dark);
    }

    .emp-status-badge.inactive {
        background: var(--red-light);
        color: var(--red-dark);
    }

    .profile-meta {
        margin-top: 1.1rem;
        text-align: left;
    }

    .profile-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: .45rem 0;
        border-bottom: 1px solid var(--border);
        font-size: .78rem;
    }

    .profile-meta-item:last-child {
        border-bottom: none;
    }

    .profile-meta-item i {
        color: var(--text-muted);
        font-size: .8rem;
        margin-top: 1px;
        flex-shrink: 0;
    }

    .profile-meta-item span {
        color: var(--text-body);
    }

    /* ── Employment Status Radio Card ── */
    .status-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.1rem 1.25rem 1.25rem;
        animation: fadeUp .3s .05s ease both;
    }

    .status-card-title {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--text-muted);
        margin-bottom: .85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-card-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    .status-radio-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .status-radio-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .55rem .85rem;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all .15s;
        font-size: .82rem;
        color: var(--text-body);
        user-select: none;
    }

    .status-radio-option:hover {
        border-color: var(--blue-200);
        background: var(--blue-50);
    }

    .status-radio-option input[type="radio"] {
        accent-color: var(--blue-600);
        cursor: pointer;
        width: 15px;
        height: 15px;
        flex-shrink: 0;
    }

    .status-radio-option.selected {
        border-color: var(--blue-400);
        background: var(--blue-50);
        color: var(--blue-700);
        font-weight: 600;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* ── End Status Card ── */

    .form-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.75rem;
        animation: fadeUp .3s .08s ease both;
    }

    .form-section-title {
        font-size: .65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--text-muted);
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .85rem 1.1rem;
    }

    .form-grid.three {
        grid-template-columns: 1fr 1fr 1fr;
    }

    .form-grid.full {
        grid-template-columns: 1fr;
    }

    @media(max-width:600px) {

        .form-grid,
        .form-grid.three {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .form-group label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: var(--text-muted);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .48rem .75rem;
        font-size: .84rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-dark);
        background: var(--surface);
        outline: none;
        transition: border-color .2s, box-shadow .2s;
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: var(--blue-400);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .15);
    }

    .form-group textarea {
        resize: none;
        min-height: 30px;
    }

    .form-sep {
        border: none;
        border-top: 1px solid var(--border);
        margin: 1.5rem 0;
    }

    .days-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 4px;
    }

    .day-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border: 1px solid var(--border);
        border-radius: 99px;
        font-size: .75rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all .15s;
        user-select: none;
    }

    .day-chip input {
        display: none;
    }

    .day-chip:hover {
        border-color: var(--blue-200);
        color: var(--blue-600);
    }

    .day-chip.active {
        background: var(--blue-600);
        border-color: var(--blue-600);
        color: #fff;
    }

    select.emp-select {
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--border);
    }

    .btn-cancel-form {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .52rem 1.25rem;
        font-size: .84rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        color: var(--text-body);
        text-decoration: none;
        transition: background .15s;
    }

    .btn-cancel-form:hover {
        background: var(--border);
    }

    .btn-save {
        background: var(--blue-600);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .52rem 1.5rem;
        font-size: .84rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: background .15s, box-shadow .15s;
    }

    .btn-save:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, .25);
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

    .toast-msg.success {
        background: #065f46;
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
    <h1>Edit Doctor</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="doctors.php">Doctors</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<section class="section page-edit">
    <div class="edit-layout">

        <div class="edit-left-col">

            <div class="profile-card">
                <div class="profile-card-banner"></div>
                <div class="profile-card-body">
                    <div class="profile-card-avatar" id="sideAvatar">
                        <?= strtoupper(substr($doc['firstName'], 0, 1) . substr($doc['lastName'], 0, 1)) ?>
                    </div>
                    <div class="profile-card-name" id="sideName"><?= htmlspecialchars($fullname) ?></div>
                    <div class="profile-card-spec" id="sideSpec"><?= htmlspecialchars($doc['specialization']) ?></div>
                    <div class="profile-card-id"><?= htmlspecialchars($doc['doctorCode']) ?></div>
                    <?php
                    $es = $doc['employmentStatus'];
                    $esCls = $es === 'Active' ? 'active' : ($es === 'On Leave' ? 'on-leave' : 'inactive');
                    ?>
                    <div class="emp-status-badge <?= $esCls ?>" id="sideEmpStatus"><?= htmlspecialchars($es) ?></div>
                    <div class="profile-meta">
                        <div class="profile-meta-item"><i class="bi bi-telephone"></i><span><?= htmlspecialchars($doc['contactNumber'] ?? '—') ?></span></div>
                        <div class="profile-meta-item"><i class="bi bi-envelope"></i><span><?= htmlspecialchars($doc['emailAddress'] ?? '—') ?></span></div>
                        <div class="profile-meta-item"><i class="bi bi-award"></i><span><?= htmlspecialchars($doc['prcLicenseNo']) ?></span></div>
                    </div>
                </div>
            </div>

            <div class="status-card">
                <div class="status-card-title">Employment Status</div>
                <div class="status-radio-list" id="statusRadioList">

                    <?php
                    $statusOptions = [
                        ['value' => 'Active',   'color' => 'var(--green)'],
                        ['value' => 'On Leave', 'color' => 'var(--amber)'],
                        ['value' => 'Inactive', 'color' => 'var(--red)'],
                    ];
                    foreach ($statusOptions as $opt):
                        $isCurrent = ($es === $opt['value']);
                    ?>
                        <label class="status-radio-option <?= $isCurrent ? 'selected' : '' ?>"
                            onclick="selectStatus(this, '<?= $opt['value'] ?>')">
                            <input type="radio" name="emp_status_radio" value="<?= $opt['value'] ?>"
                                <?= $isCurrent ? 'checked' : '' ?>>
                            <span class="status-dot" style="background:<?= $opt['color'] ?>"></span>
                            <?= $opt['value'] ?>
                        </label>
                    <?php endforeach; ?>

                </div>
            </div>

        </div>

        <div class="form-card">
            <form id="editDoctorForm" onsubmit="saveDoctor(event)">
                <input type="hidden" name="id" value="<?= $doc['id'] ?>">

                <input type="hidden" name="emp_status" id="empStatusHidden" value="<?= htmlspecialchars($es) ?>">

                <div class="form-section-title"><i class="bi bi-person"></i> Personal Information</div>
                <div class="form-grid three">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($doc['firstName']) ?>" required oninput="updateSidebar()">
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($doc['middleName'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($doc['lastName']) ?>" required oninput="updateSidebar()">
                    </div>
                </div>
                <div class="form-grid" style="margin-top:.85rem;">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($doc['dateOfBirth'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option <?= $doc['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option <?= $doc['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option <?= $doc['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid full" style="margin-top:.85rem;">
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address"><?= htmlspecialchars($doc['address'] ?? '') ?></textarea>
                    </div>
                </div>

                <hr class="form-sep">

                <div class="form-section-title"><i class="bi bi-telephone"></i> Contact Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" name="contact" value="<?= htmlspecialchars($doc['contactNumber'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($doc['emailAddress'] ?? '') ?>">
                    </div>
                </div>

                <hr class="form-sep">

                <div class="form-section-title"><i class="bi bi-briefcase"></i> Professional Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Specialization</label>
                        <select name="specialization" onchange="updateSidebar()">
                            <?php foreach ($specs as $s): ?>
                                <option <?= $doc['specialization'] === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department">
                            <?php foreach ($depts as $d_): ?>
                                <option <?= ($doc['department'] ?? '') === $d_ ? 'selected' : '' ?>><?= htmlspecialchars($d_) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>PRC License No.</label>
                        <input type="text" name="license" value="<?= htmlspecialchars($doc['prcLicenseNo']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Years of Experience</label>
                        <input type="number" name="experience" min="0" max="60" value="<?= (int)$doc['yearsOfExperience'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Patient Capacity</label>
                        <input type="number" name="capacity" min="1" value="<?= (int)$doc['patientCapacity'] ?>">
                    </div>
                </div>

                <hr class="form-sep">

                <div class="form-section-title"><i class="bi bi-calendar3"></i> Schedule</div>
                <div class="form-grid">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Working Days</label>
                        <div class="days-grid">
                            <?php foreach ($allDays as $day): $active = in_array($day, $workingDays) ? 'active' : ''; ?>
                                <label class="day-chip <?= $active ?>" onclick="toggleDay(this)">
                                    <input type="checkbox" name="days[]" value="<?= $day ?>" <?= $active ? 'checked' : '' ?>>
                                    <?= $dayAbbr[$day] ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Shift Start</label>
                        <input type="time" name="shiftStart" value="<?= htmlspecialchars($doc['shiftStart'] ?? '08:00') ?>">
                    </div>
                    <div class="form-group">
                        <label>Shift End</label>
                        <input type="time" name="shiftEnd" value="<?= htmlspecialchars($doc['shiftEnd'] ?? '17:00') ?>">
                    </div>
                </div>

                <hr class="form-sep">

                <div class="form-section-title"><i class="bi bi-chat-left-text"></i> Notes</div>
                <div class="form-grid full">
                    <div class="form-group">
                        <label>Notes / Remarks</label>
                        <textarea name="notes"><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="doctors.php" class="btn-cancel-form">Cancel</a>
                    <button type="submit" class="btn-save"><i class="bi bi-check-lg"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="toast-wrap" id="toastWrap"></div>

<script>
    function updateSidebar() {
        const first = document.querySelector('[name="first_name"]').value;
        const last = document.querySelector('[name="last_name"]').value;
        const spec = document.querySelector('[name="specialization"]').value;
        document.getElementById('sideName').textContent = 'Dr. ' + first + ' ' + last;
        document.getElementById('sideSpec').textContent = spec;
        document.getElementById('sideAvatar').textContent = ((first[0] || '') + (last[0] || '')).toUpperCase();
    }

    function updateEmpBadge(val) {
        const badge = document.getElementById('sideEmpStatus');
        badge.textContent = val;
        badge.className = 'emp-status-badge ' + (val === 'Active' ? 'active' : val === 'On Leave' ? 'on-leave' : 'inactive');
    }

    function selectStatus(label, value) {
        document.querySelectorAll('.status-radio-option').forEach(el => {
            el.classList.remove('selected');
        });
        label.classList.add('selected');
        label.querySelector('input[type="radio"]').checked = true;
        document.getElementById('empStatusHidden').value = value;
        updateEmpBadge(value);
    }

    function toggleDay(label) {
        setTimeout(() => {
            label.classList.toggle('active', label.querySelector('input[type="checkbox"]').checked);
        }, 0);
    }

    function saveDoctor(e) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);

        fetch('/Clinic_Appointment_System/app/controllers/update_doctor.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showToast('<i class="bi bi-check-lg"></i> Changes saved successfully.', 'success');
                    setTimeout(() => window.location.href = 'doctors.php', 1500);
                } else {
                    showToast('<i class="bi bi-exclamation-octagon"></i> Error: ' + res.message, 'error');
                }
            })
            .catch(() => showToast('Network error.', 'error'));
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