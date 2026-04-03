<?php
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: patients.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) {
    header('Location: patients.php');
    exit;
}

$fullname = $p['firstName'] . ' ' . ($p['middleName'] ? $p['middleName'] . ' ' : '') . $p['lastName'];
$age = $p['dateOfBirth'] ? floor((time() - strtotime($p['dateOfBirth'])) / 31557600) : null;
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
        --gray: #9ca3af;
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

    .add-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 1.25rem;
        align-items: start;
    }

    @media(max-width:900px) {
        .add-layout {
            grid-template-columns: 1fr;
        }
    }

    .form-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.75rem;
        animation: fadeUp .32s ease both;
    }

    .side-card {
        animation-delay: .05s;
    }

    .main-form-card {
        animation-delay: .1s;
    }

    .section-label {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: var(--text-muted);
        margin-bottom: 1.1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    .profile-banner {
        height: 80px;
        background: linear-gradient(135deg, var(--blue-600), var(--blue-700));
        border-radius: var(--radius) var(--radius) 0 0;
        margin: -1.75rem -1.75rem 0;
    }

    .profile-avatar {
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

    .profile-name {
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-top: .75rem;
        text-align: center;
        letter-spacing: -.02em;
    }

    .profile-sub {
        font-size: .75rem;
        color: var(--text-muted);
        margin-top: 2px;
        text-align: center;
    }

    .profile-code {
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

    .side-meta {
        margin-top: 1rem;
    }

    .radio-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: .5rem;
    }

    .radio-option {
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
    }

    .radio-option:hover {
        border-color: var(--blue-200);
        background: var(--blue-50);
    }

    .radio-option input[type="radio"] {
        accent-color: var(--blue-600);
        cursor: pointer;
    }

    .radio-option.selected {
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

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media(max-width:600px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .form-grid.cols-1 {
        grid-template-columns: 1fr;
    }

    .form-grid.cols-3 {
        grid-template-columns: 1fr 1fr 1fr;
    }

    @media(max-width:700px) {
        .form-grid.cols-3 {
            grid-template-columns: 1fr 1fr;
        }
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .field label {
        font-size: .72rem;
        font-weight: 600;
        color: var(--text-body);
        letter-spacing: .02em;
    }

    .field label .req {
        color: var(--red);
        margin-left: 2px;
    }

    .field input,
    .field select,
    .field textarea {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .5rem .75rem;
        font-size: .83rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-dark);
        background: var(--surface);
        outline: none;
        transition: border-color .2s, background .2s;
        width: 100%;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: var(--blue-400);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .12);
    }

    .field textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-divider {
        height: 1px;
        background: var(--border);
        margin: 1.5rem 0;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 1.75rem;
        flex-wrap: wrap;
    }

    .btn-secondary {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .55rem 1.4rem;
        font-size: .85rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        color: var(--text-body);
        transition: all .15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-secondary:hover {
        background: var(--border);
        color: var(--text-dark);
    }

    .btn-primary {
        background: var(--blue-600);
        border: none;
        border-radius: var(--radius-sm);
        padding: .55rem 1.6rem;
        font-size: .85rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        color: #fff;
        transition: all .15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, .28);
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
    <h1>Edit Patient</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="patients">Patients</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<section class="section page-edit">
    <div class="add-layout">

        <!-- ── Side Card ── -->
        <div class="form-card side-card" style="padding-top:0;overflow:hidden;">
            <div class="profile-banner"></div>
            <div style="padding:0 1.25rem 1.5rem;text-align:center;">
                <div class="profile-avatar" id="sideAvatar">
                    <?= strtoupper(substr($p['firstName'], 0, 1) . substr($p['lastName'], 0, 1)) ?>
                </div>
                <div class="profile-name" id="sideName"><?= htmlspecialchars($fullname) ?></div>
                <div class="profile-sub" id="sideSub">
                    <?= $p['dateOfBirth'] ? htmlspecialchars($p['dateOfBirth']) . ($age ? ' · ' . $age . ' yrs' : '') : '—' ?>
                </div>
                <div class="profile-code"><?= htmlspecialchars($p['patientCode']) ?></div>
            </div>

            <div style="padding:0 1.25rem 1.5rem;">
                <div class="section-label">Patient Status</div>
                <div class="radio-options">
                    <?php foreach (['Active' => 'var(--green)', 'Discharged' => 'var(--gray)', 'Inactive' => 'var(--red)'] as $val => $col): ?>
                        <label class="radio-option <?= $p['status'] === $val ? 'selected' : '' ?>">
                            <input type="radio" name="status" value="<?= $val ?>"
                                <?= $p['status'] === $val ? 'checked' : '' ?>
                                onchange="selectRadio(this)">
                            <span class="status-dot" style="background:<?= $col ?>"></span> <?= $val ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-divider"></div>

                <div class="section-label">Patient Condition</div>
                <div class="radio-options">
                    <?php
                    $condColors = ['Stable' => 'var(--green)', 'Critical' => 'var(--red)', 'Under Observation' => 'var(--amber)', 'Recovering' => 'var(--blue-500)'];
                    foreach ($condColors as $val => $col): ?>
                        <label class="radio-option <?= $p['patientCondition'] === $val ? 'selected' : '' ?>">
                            <input type="radio" name="condition" value="<?= $val ?>"
                                <?= $p['patientCondition'] === $val ? 'checked' : '' ?>
                                onchange="selectRadio(this)">
                            <span class="status-dot" style="background:<?= $col ?>"></span> <?= $val ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ── Main Form ── -->
        <div class="form-card main-form-card">
            <form id="editForm" onsubmit="savePatient(event)">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">

                <div class="section-label">Personal Information</div>
                <div class="form-grid cols-3">
                    <div class="field">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($p['firstName']) ?>" required oninput="updateSide()">
                    </div>
                    <div class="field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($p['middleName'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($p['lastName']) ?>" required oninput="updateSide()">
                    </div>
                </div>
                <div class="form-grid" style="margin-top:1rem;">
                    <div class="field">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" value="<?= htmlspecialchars($p['dateOfBirth'] ?? '') ?>" oninput="updateSide()">
                    </div>
                    <div class="field">
                        <label>Gender <span class="req">*</span></label>
                        <select name="gender" required>
                            <option <?= $p['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option <?= $p['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option <?= $p['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid cols-1" style="margin-top:1rem;">
                    <div class="field">
                        <label>Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($p['address'] ?? '') ?>" placeholder="Street, Barangay, City, Province">
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="section-label">Contact Information</div>
                <div class="form-grid">
                    <div class="field">
                        <label>Contact Number</label>
                        <input type="tel" name="contact" value="<?= htmlspecialchars($p['contactNumber'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($p['emailAddress'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="section-label">Medical Information</div>
                <div class="form-grid">
                    <div class="field">
                        <label>Follow-up Date</label>
                        <input type="date" name="follow_up_date" value="<?= htmlspecialchars($p['followUpDate'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-divider"></div>

                <div class="section-label">Additional Notes</div>
                <div class="form-grid cols-1">
                    <div class="field">
                        <label>Notes / Remarks</label>
                        <textarea name="notes"><?= htmlspecialchars($p['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="patients" class="btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
                    <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="toast-wrap" id="toastWrap"></div>

<script>
    function selectRadio(radio) {
        document.querySelectorAll(`input[name="${radio.name}"]`).forEach(r => {
            r.closest('.radio-option')?.classList.remove('selected');
        });
        radio.closest('.radio-option')?.classList.add('selected');
    }

    function updateSide() {
        const first = document.querySelector('[name="first_name"]').value;
        const last = document.querySelector('[name="last_name"]').value;
        const dob = document.querySelector('[name="dob"]').value;
        document.getElementById('sideName').textContent = (first + ' ' + last).trim() || '—';
        document.getElementById('sideAvatar').textContent = ((first[0] || '') + (last[0] || '')).toUpperCase() || '?';
        if (dob) {
            const age = Math.floor((Date.now() - new Date(dob)) / 31557600000);
            document.getElementById('sideSub').textContent = dob + ' · ' + age + ' yrs';
        }
    }

    function savePatient(e) {
        e.preventDefault();
        const data = new FormData(e.target);
        data.append('status', document.querySelector('input[name="status"]:checked')?.value || 'Active');
        data.append('condition', document.querySelector('input[name="condition"]:checked')?.value || 'Stable');
        fetch('update_patient.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showToast('<i class="bi bi-check-lg"></i> Changes saved successfully.', 'success');
                    setTimeout(() => window.location.href = 'patients', 1500);
                } else showToast('<i class="bi bi-exclamation-octagon"></i> ' + res.message, 'error');
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