<?php
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
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
        --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md: 0 4px 16px rgba(0,0,0,.07);
        --shadow-lg: 0 8px 30px rgba(0,0,0,.10);
    }

    .page-book, .page-book * {
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

    /* ── SECTION DIVIDER ── */
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

    .form-section-label i { color: var(--blue-500); font-size: .75rem; }

    /* ── MAIN CARD ── */
    .main-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        animation: fadeUp .32s .1s ease both;
    }

    /* ── FORM CONTROLS ── */
    .form-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-body);
        margin-bottom: .4rem;
    }

    .form-label .req { color: #ef4444; margin-left: 2px; }

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
        box-shadow: 0 0 0 3px rgba(96,165,250,.15);
        outline: none;
    }

    .form-control::placeholder { color: var(--text-muted); font-size: .84rem; }
    textarea.form-control { min-height: 100px; resize: vertical; }

    /* ── SUBMIT BUTTON ── */
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
        box-shadow: 0 4px 14px rgba(37,99,235,.3);
        transform: translateY(-1px);
    }

    .btn-submit:active { transform: translateY(0); }

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

    .btn-cancel-form:hover { background: var(--surface); }

    /* ── SUCCESS ALERT ── */
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

    .alert-success-custom i { font-size: 1.1rem; }

    /* ── SUMMARY CARD ── */
    .summary-card {
        background: var(--blue-50);
        border: 1px solid var(--blue-100);
        border-radius: var(--radius);
        padding: 1.5rem;
        position: sticky;
        top: 1rem;
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

    .summary-item:last-child { border-bottom: none; }
    .summary-item .s-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); }
    .summary-item .s-value { font-size: .875rem; font-weight: 600; color: var(--text-dark); }
    .summary-item .s-placeholder { font-size: .82rem; color: var(--text-muted); font-style: italic; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- PAGE TITLE -->
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

    <!-- SUCCESS ALERT (hidden by default) -->
    <div id="successAlert" class="alert-success-custom mb-4" style="display:none;">
        <i class="bi bi-check-circle-fill"></i>
        <div>
            <strong>Appointment booked successfully!</strong>
            Your appointment has been submitted and is awaiting confirmation.
        </div>
    </div>

    <div class="row g-4">

        <!-- MAIN FORM CARD -->
        <div class="col-lg-8">
            <div class="main-card">

                <!-- SECTION: PATIENT INFO -->
                <div class="form-section-label mb-3">
                    <i class="bi bi-person-fill"></i> Patient Information
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Patient Name <span class="req">*</span></label>
                        <input type="text" id="patientName" class="form-control" placeholder="e.g. Juan dela Cruz" oninput="updateSummary()"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" placeholder="e.g. 09171234567"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" placeholder="e.g. patient@email.com"/>
                    </div>
                </div>

                <!-- SECTION: APPOINTMENT DETAILS -->
                <div class="form-section-label mb-3">
                    <i class="bi bi-calendar2-check-fill"></i> Appointment Details
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Department <span class="req">*</span></label>
                        <select id="deptSelect" class="form-select" onchange="updateSummary(); syncDoctor()">
                            <option value="">Select Department</option>
                            <option>Dermatology</option>
                            <option>Internal Medicine</option>
                            <option>Pediatrics</option>
                            <option>Orthopedics</option>
                            <option>Cardiology</option>
                            <option>Neurology</option>
                            <option>Obstetrics &amp; Gynecology</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Doctor <span class="req">*</span></label>
                        <select id="doctorSelect" class="form-select" onchange="updateSummary()">
                            <option value="">Select Doctor</option>
                            <option>Dr. Princess Mary Lapura</option>
                            <option>Dr. Jose Reyes</option>
                            <option>Dr. Maria Santos</option>
                            <option>Dr. Ramon Cruz</option>
                            <option>Dr. Angela Villanueva</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Appointment Date <span class="req">*</span></label>
                        <input type="date" id="apptDate" class="form-control" onchange="updateSummary()"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Appointment Time <span class="req">*</span></label>
                        <input type="time" id="apptTime" class="form-control" onchange="updateSummary()"/>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Type of Visit</label>
                        <select class="form-select">
                            <option>Consultation</option>
                            <option>Follow-up</option>
                            <option>Procedure</option>
                            <option>Emergency</option>
                        </select>
                    </div>
                </div>

                <!-- SECTION: NOTES -->
                <div class="form-section-label mb-3">
                    <i class="bi bi-card-text"></i> Additional Notes
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Notes / Remarks</label>
                        <textarea id="apptNotes" class="form-control" placeholder="Describe your symptoms or reason for visit…" oninput="updateSummary()"></textarea>
                    </div>
                </div>

                <!-- FOOTER BUTTONS -->
                <div class="d-flex justify-content-end gap-2 pt-2 border-top" style="border-color: var(--border) !important;">
                    <button class="btn-cancel-form" onclick="resetForm()">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                    <button class="btn-submit" onclick="submitForm()">
                        <i class="bi bi-calendar-check"></i> Book Appointment
                    </button>
                </div>

            </div>
        </div>

        <!-- SUMMARY SIDEBAR -->
        <div class="col-lg-4">
            <div class="summary-card">
                <h6><i class="bi bi-clipboard2-pulse-fill"></i> Appointment Summary</h6>

                <div class="summary-item">
                    <span class="s-label">Patient</span>
                    <span id="sum-patient" class="s-placeholder">Not entered</span>
                </div>
                <div class="summary-item">
                    <span class="s-label">Department</span>
                    <span id="sum-dept" class="s-placeholder">Not selected</span>
                </div>
                <div class="summary-item">
                    <span class="s-label">Doctor</span>
                    <span id="sum-doctor" class="s-placeholder">Not selected</span>
                </div>
                <div class="summary-item">
                    <span class="s-label">Date</span>
                    <span id="sum-date" class="s-placeholder">Not selected</span>
                </div>
                <div class="summary-item">
                    <span class="s-label">Time</span>
                    <span id="sum-time" class="s-placeholder">Not selected</span>
                </div>
                <div class="summary-item">
                    <span class="s-label">Notes</span>
                    <span id="sum-notes" class="s-placeholder">None</span>
                </div>
            </div>

            <!-- Reminders Card -->
            <div class="main-card mt-3" style="padding: 1.25rem;">
                <div class="form-section-label mb-2">
                    <i class="bi bi-info-circle-fill"></i> Reminders
                </div>
                <ul style="font-size:.8rem; color:var(--text-body); padding-left: 1.1rem; margin:0; line-height:1.8;">
                    <li>Arrive <strong>15 minutes</strong> before your schedule.</li>
                    <li>Bring a valid <strong>ID and insurance card</strong>.</li>
                    <li>Cancellations must be done <strong>24 hrs</strong> in advance.</li>
                    <li>Your appointment is subject to <strong>doctor availability</strong>.</li>
                </ul>
            </div>
        </div>

    </div><!-- /row -->

</section>

<script>
    function updateSummary() {
        const set = (id, val, fallback) => {
            const el = document.getElementById(id);
            if (val && val.trim()) {
                el.textContent = val;
                el.className = 's-value';
            } else {
                el.textContent = fallback;
                el.className = 's-placeholder';
            }
        };

        set('sum-patient', document.getElementById('patientName').value, 'Not entered');
        set('sum-dept',    document.getElementById('deptSelect').value,   'Not selected');
        set('sum-doctor',  document.getElementById('doctorSelect').value, 'Not selected');
        set('sum-notes',   document.getElementById('apptNotes').value,    'None');

        // Date formatting
        const rawDate = document.getElementById('apptDate').value;
        if (rawDate) {
            const d = new Date(rawDate + 'T00:00:00');
            set('sum-date', d.toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'}), '');
        } else {
            set('sum-date', '', 'Not selected');
        }

        // Time formatting
        const rawTime = document.getElementById('apptTime').value;
        if (rawTime) {
            const [h, m] = rawTime.split(':');
            const hh = parseInt(h), ampm = hh >= 12 ? 'PM' : 'AM';
            const hf = hh % 12 || 12;
            set('sum-time', `${hf}:${m} ${ampm}`, '');
        } else {
            set('sum-time', '', 'Not selected');
        }
    }

    function syncDoctor() {
        const dept = document.getElementById('deptSelect').value;
        const docMap = {
            'Dermatology':    'Dr. Princess Mary Lapura',
            'Internal Medicine': 'Dr. Jose Reyes',
            'Pediatrics':     'Dr. Maria Santos',
            'Orthopedics':    'Dr. Ramon Cruz',
            'Cardiology':     'Dr. Angela Villanueva',
        };
        const sel = document.getElementById('doctorSelect');
        sel.value = docMap[dept] || '';
        updateSummary();
    }

    function submitForm() {
        const name   = document.getElementById('patientName').value.trim();
        const doctor = document.getElementById('doctorSelect').value;
        const date   = document.getElementById('apptDate').value;
        const time   = document.getElementById('apptTime').value;

        if (!name || !doctor || !date || !time) {
            alert('Please fill in all required fields (Patient Name, Doctor, Date, Time).');
            return;
        }

        document.getElementById('successAlert').style.display = 'flex';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(() => { document.getElementById('successAlert').style.display = 'none'; }, 5000);
    }

    function resetForm() {
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
            else el.value = '';
        });
        updateSummary();
        document.getElementById('successAlert').style.display = 'none';
    }

    // Set today as min date
    document.getElementById('apptDate').min = new Date().toISOString().split('T')[0];
</script>

<?php include('./includes/footer.php'); ?>
