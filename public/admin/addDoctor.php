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
        --red: #ef4444;
        --red-light: #fee2e2;
        --red-dark: #991b1b;
        --teal: #14b8a6;
        --amber: #f59e0b;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
    }

    .page-add-doctor,
    .page-add-doctor * {
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

    .side-column {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
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

    /* ── Initials avatar ── */
    .initials-preview-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .55rem;
        margin-bottom: 1.4rem;
    }

    .initials-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--blue-100);
        color: var(--blue-700);
        font-size: 1.7rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid var(--blue-200);
        letter-spacing: -.04em;
        transition: background .2s, color .2s;
        user-select: none;
    }

    .initials-hint {
        font-size: .68rem;
        color: var(--text-muted);
        text-align: center;
        line-height: 1.4;
    }

    .photo-upload-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1.4rem;
    }

    .photo-preview-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid var(--blue-200);
        object-fit: cover;
        display: none;
    }

    .photo-upload-label {
        font-size: .72rem;
        font-weight: 600;
        color: var(--blue-600);
        cursor: pointer;
        text-decoration: underline;
    }

    #photoInput {
        display: none;
    }

    /* ── Employment status ── */
    .status-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .status-option {
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

    .status-option:hover {
        border-color: var(--blue-200);
        background: var(--blue-50);
    }

    .status-option input[type="radio"] {
        accent-color: var(--blue-600);
        cursor: pointer;
    }

    .status-option.selected {
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

    /* ── Form grid ── */
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
        background: #fff8f8 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, .10);
    }

    .field textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* ── Day chips ── */
    .days-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 2px;
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

    .toast-msg.success {
        background: #065f46;
    }

    .toast-msg.warn {
        background: #9b1c1c;
        border-radius: 999px;
        padding: .65rem 1.4rem;
        box-shadow: 0 4px 20px rgba(120, 20, 20, .25);
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
    <h1>Add Doctor</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item"><a href="doctors">Doctors</a></li>
            <li class="breadcrumb-item active">Add Doctor</li>
        </ol>
    </nav>
</div>

<section class="section page-add-doctor">
    <div class="add-layout">

        <div class="side-column">

            <div class="form-card side-card">

                <div class="initials-preview-wrap">
                    <img id="photoPreview" class="photo-preview-circle" src="" alt="Photo preview">
                    <div class="initials-circle" id="initialsCircle">?</div>
                    <div id="welcomeText" style="font-size:.9rem;font-weight:600;color:var(--text-body);text-align:center;">Welcome, Dr.</div>
                    <label class="photo-upload-label" for="photoInput"><i class="bi bi-camera"></i> Upload Photo</label>
                    <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewPhoto(this)">
                </div>

                <div class="section-label">Employment Status</div>
                <div class="status-options">
                    <label class="status-option selected">
                        <input type="radio" name="empStatus" value="Active" checked onchange="updateEmpStatus(this)">
                        <span class="status-dot" style="background:var(--green)"></span> Active
                    </label>
                    <label class="status-option">
                        <input type="radio" name="empStatus" value="On Leave" onchange="updateEmpStatus(this)">
                        <span class="status-dot" style="background:var(--amber)"></span> On Leave
                    </label>
                    <label class="status-option">
                        <input type="radio" name="empStatus" value="Inactive" onchange="updateEmpStatus(this)">
                        <span class="status-dot" style="background:var(--red)"></span> Inactive
                    </label>
                </div>
            </div>

        </div>

        <div class="form-card main-form-card">

            <div class="section-label">Personal Information</div>
            <div class="form-grid">
                <div class="field" id="field-firstname">
                    <label>First Name <span class="req">*</span></label>
                    <input type="text" id="firstname" placeholder="e.g. Jose"
                        oninput="clearError('field-firstname'); updateInitials()">
                    <span class="err-msg">First name is required.</span>
                </div>
                <div class="field" id="field-lastname">
                    <label>Last Name <span class="req">*</span></label>
                    <input type="text" id="lastname" placeholder="e.g. Reyes"
                        oninput="clearError('field-lastname'); updateInitials()">
                    <span class="err-msg">Last name is required.</span>
                </div>
                <div class="field">
                    <label>Middle Name</label>
                    <input type="text" id="middlename" placeholder="Optional">
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
                <div class="field" id="field-dob">
                    <label>Date of Birth <span class="req">*</span></label>
                    <input type="date" id="dob" onchange="clearError('field-dob')">
                    <span class="err-msg">Date of birth is required.</span>
                </div>
                <div class="field" id="field-license">
                    <label>PRC License No. <span class="req">*</span></label>
                    <input type="text" id="license" placeholder="e.g. 0123456"
                        oninput="clearError('field-license')">
                    <span class="err-msg">License number is required.</span>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Professional Information</div>
            <div class="form-grid">
                <div class="field" id="field-specialization">
                    <label>Specialization <span class="req">*</span></label>
                    <select id="specialization" onchange="clearError('field-specialization'); onSpecializationChange()">
                        <option value="">Select Specialization</option>
                        <option>General Medicine</option>
                        <option>Pediatrics</option>
                        <option>Dermatology</option>
                        <option>OB-GYN</option>
                        <option>Cardiology</option>
                    </select>
                    <span class="err-msg">Please select a specialization.</span>
                </div>
                <div class="field">
                    <label>Department</label>
                    <input type="text" id="department" placeholder="e.g. Internal Medicine">
                </div>
                <div class="field" id="field-experience">
                    <label>Years of Experience <span class="req">*</span></label>
                    <input type="number" id="experience" placeholder="e.g. 10" min="0" max="60"
                        oninput="clearError('field-experience')">
                    <span class="err-msg">Years of experience is required.</span>
                </div>
                <div class="field" id="field-capacity">
                    <label>Patient Capacity <span class="req">*</span></label>
                    <input type="number" id="capacity" placeholder="Max patients per day" min="1"
                        oninput="clearError('field-capacity')">
                    <span class="err-msg">Patient capacity is required.</span>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Contact Information</div>
            <div class="form-grid">
                <div class="field" id="field-contact">
                    <label>Contact Number <span class="req">*</span></label>
                    <input type="tel" id="contact" placeholder="e.g. 09171234567"
                        oninput="clearError('field-contact')">
                    <span class="err-msg">Contact number is required.</span>
                </div>
                <div class="field" id="field-email">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" id="email" placeholder="e.g. doctor@hospital.com"
                        oninput="clearError('field-email')">
                    <span class="err-msg">Email address is required.</span>
                </div>
                <div class="field" id="field-address" style="grid-column:1/-1;">
                    <label>Address <span class="req">*</span></label>
                    <input type="text" id="address" placeholder="Street, Barangay, City, Province"
                        oninput="clearError('field-address')">
                    <span class="err-msg">Address is required.</span>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Schedule</div>
            <div class="form-grid">
                <div class="field" id="field-days">
                    <label>Working Days <span class="req">*</span></label>
                    <div class="days-grid">
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Monday"> Mon</label>
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Tuesday"> Tue</label>
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Wednesday"> Wed</label>
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Thursday"> Thu</label>
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Friday"> Fri</label>
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Saturday"> Sat</label>
                        <label class="day-chip" onclick="toggleDay(this)"><input type="checkbox" value="Sunday"> Sun</label>
                        <span class="err-msg" id="days-err-msg">Please select at least one working day.</span>
                    </div>
                </div>
                <div class="field"></div>
                <div class="field" id="field-shiftStart">
                    <label>Shift Start <span class="req">*</span></label>
                    <input type="time" id="shiftStart" value="08:00" onchange="clearError('field-shiftStart')">
                    <span class="err-msg">Shift start is required.</span>
                </div>
                <div class="field" id="field-shiftEnd">
                    <label>Shift End <span class="req">*</span></label>
                    <input type="time" id="shiftEnd" value="17:00" onchange="clearError('field-shiftEnd')">
                    <span class="err-msg">Shift end is required.</span>
                </div>
            </div>

            <div class="form-divider"></div>

            <div class="section-label">Additional Notes</div>
            <div class="form-grid cols-1">
                <div class="field">
                    <label>Notes / Remarks</label>
                    <textarea id="notes" placeholder="Any additional information about this doctor…"></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="doctors" class="btn-secondary"><i class="bi bi-x-lg"></i> Cancel</a>
                <button type="button" class="btn-primary" onclick="submitForm()">
                    <i class="bi bi-check-lg"></i> Save Doctor
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
        document.getElementById('initialsCircle').textContent = ((fn[0] || '') + (ln[0] || '')).toUpperCase() || '?';
        document.getElementById('welcomeText').textContent = fn ? 'Welcome, Dr. ' + fn + '!' : 'Welcome, Dr.';
    }

    function previewPhoto(input) {
        const preview = document.getElementById('photoPreview');
        const circle = document.getElementById('initialsCircle');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                // Hide initials once a photo is uploaded
                circle.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Auto-fills the department field based on the selected specialization
    const SPEC_TO_DEPT = {
        'General Medicine': 'General',
        'Pediatrics': 'Child Health',
        'Dermatology': 'Skin & Hair',
        'OB-GYN': 'Maternal Care',
        'Cardiology': 'Internal Medicine',
    };

    function onSpecializationChange() {
        const spec = document.getElementById('specialization').value;
        const dept = document.getElementById('department');
        if (SPEC_TO_DEPT[spec]) {
            dept.value = SPEC_TO_DEPT[spec];
        } else {
            dept.value = '';
        }
    }

    function updateEmpStatus(radio) {
        document.querySelectorAll('input[name="empStatus"]').forEach(r => {
            r.closest('.status-option').classList.remove('selected');
        });
        radio.closest('.status-option').classList.add('selected');
    }

    function toggleDay(label) {
        // setTimeout defers reading checkbox.checked until after the browser processes the click
        setTimeout(() => {
            label.classList.toggle('active', label.querySelector('input[type="checkbox"]').checked);
            if (document.querySelectorAll('.day-chip input:checked').length) {
                document.getElementById('field-days').classList.remove('has-error');
                document.getElementById('days-err-msg').style.display = 'none';
            }
        }, 0);
    }

    function clearError(fieldId) {
        document.getElementById(fieldId)?.classList.remove('has-error');
    }

    function validateForm() {
        let valid = true;
        [{
                id: 'firstname',
                field: 'field-firstname'
            },
            {
                id: 'lastname',
                field: 'field-lastname'
            },
            {
                id: 'gender',
                field: 'field-gender'
            },
            {
                id: 'license',
                field: 'field-license'
            },
            {
                id: 'specialization',
                field: 'field-specialization'
            },
            {
                id: 'contact',
                field: 'field-contact'
            },
            {
                id: 'email',
                field: 'field-email'
            },
            {
                id: 'experience',
                field: 'field-experience'
            },
            {
                id: 'capacity',
                field: 'field-capacity'
            },
            {
                id: 'address',
                field: 'field-address'
            },
            {
                id: 'dob',
                field: 'field-dob'
            },
            {
                id: 'shiftStart',
                field: 'field-shiftStart'
            },
            {
                id: 'shiftEnd',
                field: 'field-shiftEnd'
            },
        ].forEach(({
            id,
            field
        }) => {
            if (!document.getElementById(id).value.trim()) {
                document.getElementById(field).classList.add('has-error');
                valid = false;
            }
        });
        const days = [...document.querySelectorAll('.day-chip input:checked')];
        if (!days.length) {
            document.getElementById('field-days').classList.add('has-error');
            document.getElementById('days-err-msg').style.display = 'block';
            valid = false;
        }
        return valid;
    }

    function submitForm() {
        if (!validateForm()) {
            showToast('<i class="bi bi-exclamation-circle-fill"></i> Please fill in all required fields.', 'warn');
            document.querySelector('.has-error')?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            return;
        }

        const days = [...document.querySelectorAll('.day-chip input:checked')].map(c => c.value);

        const formData = new FormData();
        formData.append('firstname', document.getElementById('firstname').value.trim());
        formData.append('lastname', document.getElementById('lastname').value.trim());
        formData.append('middlename', document.getElementById('middlename').value.trim());
        formData.append('gender', document.getElementById('gender').value);
        formData.append('dob', document.getElementById('dob').value);
        formData.append('license', document.getElementById('license').value.trim());
        formData.append('specialization', document.getElementById('specialization').value);
        formData.append('department', document.getElementById('department').value.trim());
        formData.append('experience', document.getElementById('experience').value);
        formData.append('capacity', document.getElementById('capacity').value);
        formData.append('contact', document.getElementById('contact').value.trim());
        formData.append('email', document.getElementById('email').value.trim());
        formData.append('address', document.getElementById('address').value.trim());
        formData.append('shiftStart', document.getElementById('shiftStart').value);
        formData.append('shiftEnd', document.getElementById('shiftEnd').value);
        formData.append('notes', document.getElementById('notes').value.trim());
        formData.append('empStatus', document.querySelector('input[name="empStatus"]:checked')?.value || 'Active');
        days.forEach(d => formData.append('days[]', d));

        const photoFile = document.getElementById('photoInput').files[0];
        // Photo is optional; only appended if the user selected a file
        if (photoFile) formData.append('photo', photoFile);

        fetch('../../app/controllers/doctorController.php?action=save', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showToast('<i class="bi bi-check-circle-fill"></i> Doctor saved successfully!', 'success');
                    setTimeout(() => window.location.href = 'doctors', 1800);
                } else {
                    showToast('<i class="bi bi-exclamation-triangle-fill"></i> Error: ' + res.message, 'error');
                }
            })
            .catch(() => showToast('Network error. Please try again.', 'error'));
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