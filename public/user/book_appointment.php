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

$departments = $conn->query("
    SELECT DISTINCT department FROM doctors
    WHERE employmentStatus='Active' AND department IS NOT NULL AND department!=''
    ORDER BY department
")->fetch_all(MYSQLI_ASSOC);

$doctors = $conn->query("
    SELECT id, CONCAT(firstName,' ',lastName) AS name, specialization, department, patientCapacity
    FROM doctors WHERE employmentStatus='Active'
    ORDER BY lastName, firstName
")->fetch_all(MYSQLI_ASSOC);
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

    .page-book {
        font-family: 'DM Sans', sans-serif;
    }

    .page-book .form-label,
    .page-book .form-control,
    .page-book .form-select,
    .page-book .btn-submit,
    .page-book .btn-cancel-form,
    .page-book .summary-card,
    .page-book .main-card,
    .page-book .slot-btn,
    .page-book .form-section-label {
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

    .form-section-label {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-muted);
        margin-bottom: .75rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .form-section-label i {
        color: var(--blue-500);
        font-size: .75rem;
    }

    .slot-btn:disabled,
    .slot-btn.disabled-slot {
        opacity: .45;
        cursor: not-allowed;
        text-decoration: line-through;
        background: #e5e7eb;
        border-color: #d1d5db;
        color: var(--text-muted);
    }

    .main-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        animation: fadeUp .32s .1s ease both;
    }

    .form-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-body);
        margin-bottom: .4rem;
    }

    .form-label .req {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-control,
    .form-select {
        font-family: 'DM Sans', sans-serif;
        font-size: .875rem;
        color: var(--text-dark);
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .55rem .85rem;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--blue-400);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .15);
        outline: none;
    }

    .form-control::placeholder {
        color: var(--text-muted);
        font-size: .84rem;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .btn-submit {
        background: var(--blue-600);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .6rem 1.75rem;
        font-size: .875rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .15s, box-shadow .15s, transform .1s;
    }

    .btn-submit:hover {
        background: var(--blue-700);
        box-shadow: 0 4px 14px rgba(37, 99, 235, .3);
        transform: translateY(-1px);
    }

    .btn-cancel-form {
        background: #fff;
        color: var(--text-body);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .6rem 1.5rem;
        font-size: .875rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .15s;
    }

    .btn-cancel-form:hover {
        background: var(--surface);
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
        animation: fadeUp .25s ease;
    }

    .alert-success-custom i {
        font-size: 1.1rem;
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
    }

    .summary-card {
        background: var(--blue-50);
        border: 1px solid var(--blue-100);
        border-radius: var(--radius);
        padding: 1.5rem;
    }

    .summary-card h6 {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--blue-600);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .summary-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: .6rem 0;
        border-bottom: 1px solid var(--blue-100);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-item .s-label {
        font-size: .65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--text-muted);
    }

    .summary-item .s-value {
        font-size: .875rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .summary-item .s-placeholder {
        font-size: .82rem;
        color: var(--text-muted);
        font-style: italic;
    }

    .slot-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        max-height: 200px;
        overflow-y: auto;
        margin-top: .5rem;
    }

    .slot-btn {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: .4rem;
        font-size: .78rem;
        font-family: 'DM Sans', sans-serif;
        background: var(--surface);
        color: var(--text-body);
        cursor: pointer;
        text-align: center;
        transition: all .15s;
    }

    .slot-btn:hover:not(:disabled) {
        background: var(--blue-50);
        border-color: var(--blue-400);
        color: var(--blue-700);
    }

    .slot-btn.selected {
        background: var(--blue-600);
        color: #fff;
        border-color: var(--blue-600);
    }

    .slot-btn:disabled {
        opacity: .4;
        cursor: not-allowed;
        text-decoration: line-through;
    }

    .slots-loading {
        text-align: center;
        padding: 1rem;
        color: var(--text-muted);
        font-size: .8rem;
    }

    .sidebar-sticky-col {
        position: sticky;
        top: 80px;
        align-self: flex-start;
    }

    /* ── Field error styles ── */
    .field-wrap {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .field-wrap.field-error .form-control,
    .field-wrap.field-error .form-select {
        border-color: var(--red) !important;
        background: #fff8f8;
    }

    .field-err-msg {
        font-size: .68rem;
        color: var(--red);
        display: none;
        margin-top: 2px;
    }

    .field-wrap.field-error .field-err-msg {
        display: block;
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

    @media (min-width: 992px) {
        .col-lg-8 {
            flex: 0 0 auto;
            width: 66.66667%;
            max-width: 66.66667%;
        }

        .col-lg-4 {
            flex: 0 0 auto;
            width: 33.33333%;
            max-width: 33.33333%;
        }
    }
</style>

<div class="pagetitle">
    <h1>Book Appointment</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Book Appointment</li>
        </ol>
    </nav>
</div>

<section class="section page-book">

    <div id="successAlert" class="alert-success-custom mb-4" style="display:none">
        <i class="bi bi-check-circle-fill"></i>
        <div>
            <strong id="successMsg">Appointment booked successfully!</strong><br>
            <span id="successCode"></span> — We'll see you soon.
        </div>
    </div>
    <div id="errorAlert" class="alert-error-custom mb-4" style="display:none">
        <i class="bi bi-x-circle-fill"></i>
        <div id="errorMsg">Something went wrong. Please try again.</div>
    </div>

    <div class="row g-4 align-items-start">

        <!-- LEFT: Form -->
        <div class="col-lg-8">
            <div class="main-card">

                <!-- Patient Info -->
                <div class="form-section-label mb-3"><i class="bi bi-person-fill"></i> Patient Information</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-firstName">
                            <input type="text" id="firstName" class="form-control"
                                value="" placeholder="e.g. Juan"
                                oninput="updateSummary(); clearFieldError('wrap-firstName')">
                            <span class="field-err-msg">First name is required.</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" id="middleName" class="form-control"
                            value="" placeholder="e.g. Santos">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Last Name <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-lastName">
                            <input type="text" id="lastName" class="form-control"
                                value="" placeholder="e.g. dela Cruz"
                                oninput="updateSummary(); clearFieldError('wrap-lastName')">
                            <span class="field-err-msg">Last name is required.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-dateOfBirth">
                            <input type="date" id="dateOfBirth" class="form-control" value=""
                                onchange="clearFieldError('wrap-dateOfBirth')">
                            <span class="field-err-msg">Date of birth is required.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-contactNumber">
                            <input type="tel" id="contactNumber" class="form-control"
                                value="" placeholder="e.g. 09171234567"
                                oninput="clearFieldError('wrap-contactNumber')">
                            <span class="field-err-msg">Contact number is required.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-emailAddress">
                            <input type="email" id="emailAddress" class="form-control"
                                value="" placeholder="e.g. patient@email.com"
                                oninput="clearFieldError('wrap-emailAddress')">
                            <span class="field-err-msg">Email address is required.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-gender">
                            <select id="gender" class="form-select"
                                onchange="clearFieldError('wrap-gender')">
                                <option value="">Select Gender</option>
                                <option>Male</option>
                                <option>Female</option>
                                <option>Other</option>
                            </select>
                            <span class="field-err-msg">Please select a gender.</span>
                        </div>
                    </div>
                </div>

                <!-- Appointment Details -->
                <div class="form-section-label mb-3"><i class="bi bi-calendar2-check-fill"></i> Appointment Details</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Specialization <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-deptSelect">
                            <select id="deptSelect" class="form-select"
                                onchange="updateSummary(); filterDoctors(); clearFieldError('wrap-deptSelect')">
                                <option value="">Select Specialization</option>
                                <?php
                                $specs = $conn->query("
                                    SELECT DISTINCT specialization FROM doctors
                                    WHERE employmentStatus='Active' AND specialization IS NOT NULL AND specialization!=''
                                    ORDER BY specialization
                                ")->fetch_all(MYSQLI_ASSOC);
                                foreach ($specs as $spec): ?>
                                    <option><?= htmlspecialchars($spec['specialization']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="field-err-msg">Please select a specialization.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Doctor <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-doctorSelect">
                            <select id="doctorSelect" class="form-select"
                                onchange="updateSummary(); loadSlots(); loadDoctorSchedule(); clearFieldError('wrap-doctorSelect')">
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doc): ?>
                                    <option value="<?= $doc['id'] ?>"
                                        data-dept="<?= htmlspecialchars($doc['department'] ?? '') ?>"
                                        data-spec="<?= htmlspecialchars($doc['specialization']) ?>">
                                        Dr. <?= htmlspecialchars($doc['name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="field-err-msg">Please select a doctor.</span>
                        </div>
                        <div id="doctorScheduleBox" style="display:none;margin-top:8px;background:var(--blue-50);border:1px solid var(--blue-100);border-radius:var(--radius-sm);padding:.65rem .85rem;">
                            <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--blue-600);margin-bottom:.5rem;">
                                <i class="bi bi-clock"></i> Available Schedule
                            </div>
                            <div id="doctorScheduleList" style="display:flex;flex-direction:column;gap:4px;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Appointment Date <span class="req">*</span></label>
                        <div class="field-wrap" id="wrap-apptDate">
                            <input type="date" id="apptDate" class="form-control"
                                onchange="updateSummary(); loadSlots(); clearFieldError('wrap-apptDate')">
                            <span class="field-err-msg">Please select a date.</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Appointment Time <span class="req">*</span></label>
                        <input type="hidden" id="apptTime">
                        <div id="slotsContainer">
                            <div style="color:var(--text-muted);font-size:.8rem;padding:.5rem 0">
                                Select a doctor and date to see available slots.
                            </div>
                        </div>
                        <span class="field-err-msg" id="time-err-msg" style="display:none;">Please select a time slot.</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type of Visit</label>
                        <select id="channel" class="form-select">
                            <option value="Online">Online Booking</option>
                            <option value="Walk-in">Walk-in</option>
                            <option value="Phone">Phone</option>
                            <option value="Referral">Referral</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-section-label mb-3"><i class="bi bi-card-text"></i> Additional Notes</div>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea id="apptNotes" class="form-control"
                            placeholder="Describe your symptoms or reason for visit…"
                            oninput="updateSummary()"></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2 border-top" style="border-color:var(--border)!important">
                    <button class="btn-cancel-form" onclick="resetForm()"><i class="bi bi-x-lg"></i> Cancel</button>
                    <button class="btn-submit" id="submitBtn" onclick="submitForm()"><i class="bi bi-calendar-check"></i> Book Appointment</button>
                </div>

            </div><!-- /.main-card -->
        </div><!-- /.col-lg-8 -->

        <!-- RIGHT: Summary + Reminders (sticky) -->
        <div class="col-lg-4 sidebar-sticky-col">

            <div class="summary-card mb-3">
                <h6><i class="bi bi-clipboard2-pulse-fill"></i> Appointment Summary</h6>
                <div class="summary-item"><span class="s-label">Patient</span><span id="sum-patient" class="s-placeholder">Not entered</span></div>
                <div class="summary-item"><span class="s-label">Specialization</span><span id="sum-spec" class="s-placeholder">Not selected</span></div>
                <div class="summary-item"><span class="s-label">Doctor</span><span id="sum-doctor" class="s-placeholder">Not selected</span></div>
                <div class="summary-item"><span class="s-label">Date</span><span id="sum-date" class="s-placeholder">Not selected</span></div>
                <div class="summary-item"><span class="s-label">Time</span><span id="sum-time" class="s-placeholder">Not selected</span></div>
                <div class="summary-item"><span class="s-label">Notes</span><span id="sum-notes" class="s-placeholder">None</span></div>
            </div>

            <div class="main-card" style="padding:1.25rem">
                <div class="form-section-label mb-2"><i class="bi bi-info-circle-fill"></i> Reminders</div>
                <ul style="font-size:.8rem;color:var(--text-body);padding-left:1.1rem;margin:0;line-height:1.8">
                    <li>Arrive <strong>15 minutes</strong> before your schedule.</li>
                    <li>Bring a valid <strong>ID and insurance card</strong>.</li>
                    <li>Cancellations must be done <strong>24 hrs</strong> in advance.</li>
                    <li>Your appointment is subject to <strong>doctor availability</strong>.</li>
                </ul>
            </div>

        </div><!-- /.col-lg-4 -->

    </div><!-- /.row -->

</section>

<script>
    const HANDLER = '../../app/controllers/bookapp_handler.php';
    const allDoctors = <?= json_encode($doctors) ?>;

    function markFieldError(wrapperId) {
        document.getElementById(wrapperId)?.classList.add('field-error');
    }

    function clearFieldError(wrapperId) {
        document.getElementById(wrapperId)?.classList.remove('field-error');
    }

    function updateSummary() {
        const set = (id, val, fb) => {
            const el = document.getElementById(id);
            if (val && val.trim()) {
                el.textContent = val;
                el.className = 's-value';
            } else {
                el.textContent = fb;
                el.className = 's-placeholder';
            }
        };
        const fn = document.getElementById('firstName').value.trim();
        const mn = document.getElementById('middleName').value.trim();
        const ln = document.getElementById('lastName').value.trim();
        const fullName = [fn, mn, ln].filter(Boolean).join(' ');
        set('sum-patient', fullName, 'Not entered');
        set('sum-spec', document.getElementById('deptSelect').value, 'Not selected');
        const docSel = document.getElementById('doctorSelect');
        set('sum-doctor', docSel.options[docSel.selectedIndex]?.text || '', 'Not selected');
        set('sum-notes', document.getElementById('apptNotes').value, 'None');
        const rawDate = document.getElementById('apptDate').value;
        if (rawDate) {
            const d = new Date(rawDate + 'T00:00:00');
            set('sum-date', d.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }), '');
        } else set('sum-date', '', 'Not selected');
        const rawTime = document.getElementById('apptTime').value;
        if (rawTime) {
            const [h, m] = rawTime.split(':');
            const hh = parseInt(h),
                ap = hh >= 12 ? 'PM' : 'AM',
                hf = hh % 12 || 12;
            set('sum-time', `${hf}:${m} ${ap}`, '');
        } else set('sum-time', '', 'Not selected');
    }

    function loadDoctorSchedule() {
        const docId = document.getElementById('doctorSelect').value;
        const box = document.getElementById('doctorScheduleBox');
        const list = document.getElementById('doctorScheduleList');

        if (!docId) {
            box.style.display = 'none';
            return;
        }

        fetch(`${HANDLER}?action=get_doctor_schedule&doctorId=${docId}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data.length) {
                    box.style.display = 'none';
                    return;
                }
                list.innerHTML = res.data.map(s => {
                    const fmt = t => {
                        const [h, m] = t.split(':');
                        const hr = parseInt(h);
                        return `${hr > 12 ? hr - 12 : hr || 12}:${m} ${hr >= 12 ? 'PM' : 'AM'}`;
                    };
                    return `<div style="display:flex;justify-content:space-between;font-size:.78rem;">
                    <span style="font-weight:600;color:var(--text-dark);">${s.dayOfWeek}</span>
                    <span style="color:var(--text-body);">${fmt(s.shiftStart)} – ${fmt(s.shiftEnd)}</span>
                </div>`;
                }).join('');
                box.style.display = 'block';
            });
    }

    function filterDoctors() {
        const dept = document.getElementById('deptSelect').value;
        const sel = document.getElementById('doctorSelect');
        sel.innerHTML = '<option value="">Select Doctor</option>';
        allDoctors.filter(d => !dept || d.specialization === dept).forEach(d => {
            sel.insertAdjacentHTML('beforeend',
                `<option value="${d.id}" data-dept="${d.department||''}" data-spec="${d.specialization}">Dr. ${d.name} (${d.specialization})</option>`);
        });
        document.getElementById('apptTime').value = '';
        document.getElementById('slotsContainer').innerHTML = '<div style="color:var(--text-muted);font-size:.8rem;padding:.5rem 0">Select a doctor and date to see available slots.</div>';
        updateSummary();
    }

    function loadSlots() {
        const docId = document.getElementById('doctorSelect').value;
        const date = document.getElementById('apptDate').value;
        if (!docId || !date) return;
        document.getElementById('slotsContainer').innerHTML = '<div class="slots-loading"><i class="bi bi-clock"></i> Loading slots…</div>';
        fetch(`${HANDLER}?action=get_slots&doctorId=${docId}&date=${date}`)
            .then(r => r.json()).then(res => {
                if (!res.success || !res.slots.length) {
                    document.getElementById('slotsContainer').innerHTML = '<div style="color:var(--text-muted);font-size:.8rem;padding:.5rem 0">No slots available for this day.</div>';
                    return;
                }
                let html = '<div class="slot-grid">';
                res.slots.forEach(slot => {
                    html += `<button type="button" class="slot-btn${!slot.available ? ' disabled-slot' : ''}" ${!slot.available ? 'disabled' : ''}
    onclick="selectSlot('${slot.value}','${slot.label}',this)">${slot.label}</button>`;
                });
                html += '</div>';
                document.getElementById('slotsContainer').innerHTML = html;
            });
    }

    function selectSlot(value, label, btn) {
        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        document.getElementById('apptTime').value = value;
        document.getElementById('time-err-msg').style.display = 'none';
        updateSummary();
    }

    function submitForm() {
        window.scrollTo({ top: 0, behavior: 'smooth' });

        const firstName = document.getElementById('firstName').value.trim();
        const lastName  = document.getElementById('lastName').value.trim();
        const doctor    = document.getElementById('doctorSelect').value;
        const date      = document.getElementById('apptDate').value;
        const time      = document.getElementById('apptTime').value;
        const spec      = document.getElementById('deptSelect').value;

        const dob     = document.getElementById('dateOfBirth').value;
        const contact = document.getElementById('contactNumber').value.trim();
        const email   = document.getElementById('emailAddress').value.trim();
        const gender  = document.getElementById('gender').value;

        let hasError = false;

        if (!firstName) { markFieldError('wrap-firstName');    hasError = true; }
        if (!lastName)  { markFieldError('wrap-lastName');     hasError = true; }
        if (!dob)       { markFieldError('wrap-dateOfBirth');  hasError = true; }
        if (!contact)   { markFieldError('wrap-contactNumber'); hasError = true; }
        if (!email)     { markFieldError('wrap-emailAddress'); hasError = true; }
        if (!gender)    { markFieldError('wrap-gender');       hasError = true; }
        if (!spec)      { markFieldError('wrap-deptSelect');   hasError = true; }
        if (!doctor)    { markFieldError('wrap-doctorSelect'); hasError = true; }
        if (!date)      { markFieldError('wrap-apptDate');     hasError = true; }
        if (!time) {
            document.getElementById('time-err-msg').style.display = 'block';
            hasError = true;
        }

        if (hasError) {
            showAlert('error', 'Please fill in all required fields.');
            document.querySelector('.field-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const middleName = document.getElementById('middleName').value.trim();
        const name = [firstName, middleName, lastName].filter(Boolean).join(' ');

        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Booking…';

        const payload = {
            patientId: document.getElementById('patientId')?.value || '',
            patientName: name,
            firstName: firstName,
            middleName: middleName,
            lastName: lastName,
            dateOfBirth: document.getElementById('dateOfBirth').value,
            contact: document.getElementById('contactNumber').value,
            email: document.getElementById('emailAddress').value || '<?= $userEmail ?>',
            gender: document.getElementById('gender').value,
            doctorId: doctor,
            appointmentDate: date,
            appointmentTime: time,
            channel: document.getElementById('channel').value,
            remarks: document.getElementById('apptNotes').value,
        };

        fetch(`${HANDLER}?action=book`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-calendar-check"></i> Book Appointment';
            if (res.success) {
                document.getElementById('successMsg').textContent = 'Appointment booked successfully!';
                document.getElementById('successCode').textContent = `Reference: ${res.appointmentCode}`;
                showAlert('success');
                resetForm();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                showAlert('error', res.message || 'Failed to book. Please try again.');
            }
        }).catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-calendar-check"></i> Book Appointment';
            showAlert('error', 'Network error. Please try again.');
        });
    }

    function showAlert(type, msg = '') {
        document.getElementById('successAlert').style.display = 'none';
        document.getElementById('errorAlert').style.display = 'none';
        if (type === 'success') {
            document.getElementById('successAlert').style.display = 'flex';
            setTimeout(() => document.getElementById('successAlert').style.display = 'none', 6000);
        } else {
            document.getElementById('errorMsg').textContent = msg;
            document.getElementById('errorAlert').style.display = 'flex';
            setTimeout(() => document.getElementById('errorAlert').style.display = 'none', 5000);
        }
    }

    function resetForm() {
        document.querySelectorAll('#firstName,#middleName,#lastName,#dateOfBirth,#contactNumber,#apptNotes').forEach(el => el.value = '');
        document.getElementById('emailAddress').value = '';
        document.getElementById('gender').value = '';
        document.getElementById('deptSelect').value = '';
        document.getElementById('doctorSelect').value = '';
        document.getElementById('apptDate').value = '';
        document.getElementById('apptTime').value = '';
        document.getElementById('channel').value = 'Online';
        document.getElementById('slotsContainer').innerHTML = '<div style="color:var(--text-muted);font-size:.8rem;padding:.5rem 0">Select a doctor and date to see available slots.</div>';
        document.getElementById('time-err-msg').style.display = 'none';
        document.querySelectorAll('.field-wrap').forEach(w => w.classList.remove('field-error'));
        const patIdEl = document.getElementById('patientId');
        if (patIdEl) patIdEl.value = '';
        updateSummary();
    }

    document.getElementById('apptDate').min = new Date().toISOString().split('T')[0];
    updateSummary();
</script>

<?php include('./includes/footer.php'); ?>