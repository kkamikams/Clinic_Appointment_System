<?php
session_start();
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
require_once('../../app/config/config.php');
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
        --gray-light: #f3f4f6;
        --gray-dark: #374151;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
    }

    .page-add-patient,
    .page-add-patient * {
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

    .avatar-uploader {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--blue-100);
        border: 3px dashed var(--blue-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        color: var(--blue-500);
        transition: all .2s;
        position: relative;
        overflow: hidden;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        position: absolute;
        inset: 0;
    }

    .btn-upload {
        background: var(--blue-50);
        border: 1px solid var(--blue-200);
        border-radius: var(--radius-sm);
        padding: .4rem 1rem;
        font-size: .78rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        color: var(--blue-600);
        cursor: pointer;
        transition: all .15s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-upload:hover {
        background: var(--blue-100);
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

    .field .err-msg {
        font-size: .68rem;
        color: var(--red);
        display: none;
    }

    .field.has-error .err-msg {
        display: block;
    }

    .field.has-error input,
    .field.has-error select {
        border-color: var(--red);
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

    .toast-msg.warn {
        background: #92400e;
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
    <h1>Add Patient</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="patients">Patients</a></li>
            <li class="breadcrumb-item active">Add Patient</li>
        </ol>
    </nav>
</div>

<section class="section page-add-patient">
    <div class="add-layout">

        <!-- Side Card -->
        <div class="form-card side-card">
            <div class="avatar-uploader">
                <div class="avatar-preview" id="avatarPreview">
                    <span id="avatarInitials">?</span>
                </div>
                <div id="avatarWelcome" style="
    font-size: .82rem;
    font-weight: 600;
    color: var(--text-body);
    text-align: center;
    margin-top: .5rem;
    margin-bottom: 1rem;
">Welcome!</div>
            </div>

            <div class="section-label">Patient Condition</div>
            <div class="radio-options">
                <label class="radio-option selected">
                    <input type="radio" name="condition" value="Stable" checked onchange="selectRadio(this)">
                    <span class="status-dot" style="background:var(--green)"></span> Stable
                </label>
                <label class="radio-option">
                    <input type="radio" name="condition" value="Critical" onchange="selectRadio(this)">
                    <span class="status-dot" style="background:var(--red)"></span> Critical
                </label>
                <label class="radio-option">
                    <input type="radio" name="condition" value="Under Observation" onchange="selectRadio(this)">
                    <span class="status-dot" style="background:var(--amber)"></span> Under Observation
                </label>
                <label class="radio-option">
                    <input type="radio" name="condition" value="Recovering" onchange="selectRadio(this)">
                    <span class="status-dot" style="background:var(--blue-500)"></span> Recovering
                </label>
            </div>
        </div>

        <!-- Main Form -->
        <div class="form-card main-form-card">
            <div class="section-label">Personal Information</div>
            <div class="form-grid cols-3">
                <div class="field" id="field-firstname">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" id="firstname" placeholder="e.g. Maria" oninput="clearError('field-firstname');updateInitials()">
                    <span class="err-msg">First name is required.</span>
                </div>
                <div class="field">
                    <label>Middle Name</label>
                    <input type="text" id="middlename" placeholder="Optional">
                </div>
                <div class="field" id="field-lastname">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" id="lastname" placeholder="e.g. Santos" oninput="clearError('field-lastname');updateInitials()">
                    <span class="err-msg">Last name is required.</span>
                </div>
            </div>
            <div class="form-grid" style="margin-top:1rem;">
                <div class="field">
                    <label>Date of Birth</label>
                    <input type="date" id="dob">
                </div>
                <div class="field" id="field-gender">
                    <label>Gender <span class="req">*</span></label>
                    <select id="gender" onchange="clearError('field-gender')">
                        <option value="">Select gender</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                    <span class="err-msg">Please select a gender.</span>
                </div>
            </div>
            <div class="form-grid cols-1" style="margin-top:1rem;">
                <div class="field">
                    <label>Address</label>
                    <input type="text" id="address" placeholder="Street, Barangay, City, Province">
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Contact Information</div>
            <div class="form-grid">
                <div class="field" id="field-contact">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="tel" id="contact" placeholder="e.g. 09171234567" oninput="clearError('field-contact')">
                    <span class="err-msg">Contact number is required.</span>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" id="email" placeholder="e.g. patient@email.com">
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Medical Information</div>
            <div class="form-grid">
                <div class="field">
                    <label>Follow-up Date</label>
                    <input type="date" id="followUpDate">
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Additional Notes</div>
            <div class="form-grid cols-1">
                <div class="field">
                    <label>Notes / Remarks</label>
                    <textarea id="notes" placeholder="Any additional information about this patient…"></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="patients" class="btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="button" class="btn-primary" onclick="submitForm()">
                    <i class="bi bi-check-lg"></i> Save Patient
                </button>
            </div>
        </div>
    </div>
</section>

<div class="toast-wrap" id="toastWrap"></div>

<script>
    function updateInitials() {
        const fn = document.getElementById('firstname').value.trim();
        const ln = document.getElementById('lastname').value.trim();
        document.getElementById('avatarInitials').textContent = ((fn[0] || '') + (ln[0] || '')).toUpperCase() || '?';
        document.getElementById('avatarWelcome').textContent = fn ? 'Welcome, ' + fn + '!' : 'Welcome!';
    }

    function selectRadio(radio) {
        document.querySelectorAll(`input[name="${radio.name}"]`).forEach(r => {
            r.closest('.radio-option')?.classList.remove('selected');
        });
        radio.closest('.radio-option')?.classList.add('selected');
    }

    function clearError(fieldId) {
        document.getElementById(fieldId)?.classList.remove('has-error');
    }

    function validateForm() {
        let valid = true;
        [{
            id: 'firstname',
            field: 'field-firstname'
        }, {
            id: 'lastname',
            field: 'field-lastname'
        }, {
            id: 'gender',
            field: 'field-gender'
        }, {
            id: 'contact',
            field: 'field-contact'
        }]
        .forEach(({
            id,
            field
        }) => {
            if (!document.getElementById(id).value.trim()) {
                document.getElementById(field).classList.add('has-error');
                valid = false;
            }
        });
        return valid;
    }

    function submitForm() {
        if (!validateForm()) {
            showToast('Please fill in all required fields.', 'warn');
            document.querySelector('.has-error')?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return;
        }
        const fd = new FormData();
        fd.append('first_name', document.getElementById('firstname').value.trim());
        fd.append('middle_name', document.getElementById('middlename').value.trim());
        fd.append('last_name', document.getElementById('lastname').value.trim());
        fd.append('gender', document.getElementById('gender').value);
        fd.append('dob', document.getElementById('dob').value);
        fd.append('address', document.getElementById('address').value.trim());
        fd.append('contact', document.getElementById('contact').value.trim());
        fd.append('email', document.getElementById('email').value.trim());
        fd.append('follow_up_date', document.getElementById('followUpDate').value);
        fd.append('notes', document.getElementById('notes').value.trim());
        fd.append('status', document.querySelector('input[name="status"]:checked')?.value || 'Active');
        fd.append('condition', document.querySelector('input[name="condition"]:checked')?.value || 'Stable');

        fetch('/Clinic_Appointment_System/app/controllers/save_patient.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showToast('<i class="bi bi-check-circle-fill"></i> Patient saved successfully!', 'success');
                    setTimeout(() => window.location.href = 'patients', 1800);
                } else {
                    showToast('<i class="bi bi-exclamation-triangle-fill"></i> Error: ' + res.message, 'error');
                }
            }).catch(() => showToast('Network error. Please try again.', 'error'));
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