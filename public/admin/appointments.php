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

    .page-appointments,
    .page-appointments * {
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

    /* ── Stat Strip ── */
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
        border-left-color: var(--amber);
        animation-delay: .14s;
    }

    .stat-card:nth-child(4) {
        border-left-color: var(--red);
        animation-delay: .19s;
    }

    .stat-card .sc-label {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-muted);
        margin-bottom: .45rem;
    }

    .stat-card .sc-num {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -.05em;
        line-height: 1;
    }

    .stat-card .sc-sub {
        font-size: .7rem;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    /* ── Main Card ── */
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

    /* Date picker toolbar item */
    .date-picker-wrap {
        position: relative;
    }

    .date-picker-wrap i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: .8rem;
        pointer-events: none;
    }

    .date-picker-wrap input[type=date] {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .42rem .75rem .42rem 2rem;
        font-size: .8rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-body);
        background: var(--surface);
        outline: none;
        cursor: pointer;
        transition: border-color .2s;
    }

    .date-picker-wrap input[type=date]:focus {
        border-color: var(--blue-400);
        background: #fff;
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
    }

    .btn-primary-sm:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, .25);
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

    .appt-id {
        font-weight: 700;
        color: var(--blue-700);
        font-size: .8rem;
        text-decoration: none;
    }

    .appt-id:hover {
        color: var(--blue-600);
    }

    .pat-cell {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .pat-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid var(--border);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .68rem;
        font-weight: 700;
        background: var(--blue-50);
        color: var(--blue-700);
        overflow: hidden;
    }

    .pat-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .pat-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: .82rem;
    }

    .time-cell {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: .8rem;
        color: var(--text-body);
    }

    .time-cell i {
        color: var(--text-muted);
        font-size: .75rem;
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

    .bg-violet {
        background: var(--violet-light) !important;
        color: var(--violet-dark) !important;
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
    }

    .btn-act:hover {
        background: var(--blue-50);
        color: var(--blue-600);
        border-color: var(--blue-200);
    }

    .btn-act.del:hover {
        background: var(--red-light);
        color: var(--red);
        border-color: #fca5a5;
    }

    /* ── Loading overlay ── */
    .tbl-loading {
        display: none;
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .7);
        z-index: 5;
        align-items: center;
        justify-content: center;
    }

    .tbl-wrap {
        position: relative;
        overflow-x: auto;
    }

    /* ── Empty state ── */
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

    /* ── Modal ── */
    .modal-content {
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-lg);
        font-family: 'DM Sans', sans-serif;
    }

    .modal-header {
        border-bottom: 1px solid var(--border);
        padding: 1.1rem 1.4rem;
    }

    .modal-title {
        font-size: .95rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .modal-body {
        padding: 1.4rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border);
        padding: .9rem 1.4rem;
    }

    .form-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: var(--text-muted);
        margin-bottom: .35rem;
        display: block;
    }

    .form-control,
    .form-select {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .5rem .75rem;
        font-size: .83rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-dark);
        background: var(--surface);
        outline: none;
        width: 100%;
        transition: border-color .2s, background .2s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--blue-400);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .08);
    }

    .btn-secondary-sm {
        background: var(--surface);
        color: var(--text-body);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .42rem 1rem;
        font-size: .8rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: background .15s;
    }

    .btn-secondary-sm:hover {
        background: var(--border);
    }

    /* View modal details */
    .detail-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 6px 12px;
        font-size: .83rem;
        padding: .45rem 0;
        border-bottom: 1px solid var(--border);
        align-items: start;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: var(--text-muted);
        font-weight: 600;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .detail-value {
        color: var(--text-dark);
        font-weight: 500;
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
    <h1>Appointments</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Appointments</li>
        </ol>
    </nav>
</div>

<section class="section page-appointments">

    <!-- Stat strip -->
    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Today's Total</div>
            <div class="sc-num" id="statTotal">—</div>
            <div class="sc-sub" id="statTotalSub">Loading…</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Completed</div>
            <div class="sc-num" id="statCompleted">—</div>
            <div class="sc-sub" id="statCompletedSub">—</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Pending</div>
            <div class="sc-num" id="statPending">—</div>
            <div class="sc-sub">Awaiting service</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Cancelled</div>
            <div class="sc-num" id="statCancelled">—</div>
            <div class="sc-sub">Selected date</div>
        </div>
    </div>

    <!-- Table card -->
    <div class="main-card">
        <div class="table-toolbar">
            <h5>Appointment List <span id="toolbarDate">| <?php echo date('F j, Y'); ?></span></h5>

            <!-- Date picker -->
            <div class="date-picker-wrap">
                <i class="bi bi-calendar3"></i>
                <input type="date" id="apptDate" value="<?php echo date('Y-m-d'); ?>" onchange="loadAppointments(1)">
            </div>

            <!-- Search -->
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="apptSearch" placeholder="Search patient or doctor…" oninput="debounceSearch()">
            </div>

            <!-- Status filter -->
            <select class="filter-select" id="apptStatus" onchange="loadAppointments(1)">
                <option value="">All Status</option>
                <option>Completed</option>
                <option>In Progress</option>
                <option>Pending</option>
                <option>Cancelled</option>
            </select>

            <!-- Doctor filter (populated dynamically) -->
            <select class="filter-select" id="apptDoctor" onchange="loadAppointments(1)">
                <option value="">All Doctors</option>
            </select>

            <button class="btn-primary-sm" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i>New Appointment
            </button>
        </div>

        <div class="tbl-wrap">
            <div class="tbl-loading" id="tblLoading" style="display:flex;">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>
            <table class="table" id="apptTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="apptTbody"></tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex align-items-center justify-content-between mt-3" style="flex-wrap:wrap;gap:8px;" id="paginationWrap">
            <span style="font-size:.75rem;color:var(--text-muted);" id="paginationInfo"></span>
            <div style="display:flex;gap:5px;" id="paginationBtns"></div>
        </div>
    </div>

</section>

<!-- ══════════════════════════════════════════════════
     ADD / EDIT MODAL
══════════════════════════════════════════════════ -->
<div class="modal fade" id="apptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apptModalTitle">New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <select class="form-select" id="fPatient" required>
                            <option value="">Select patient…</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Doctor</label>
                        <select class="form-select" id="fDoctor" required>
                            <option value="">Select doctor…</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="fDate" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time</label>
                        <input type="time" class="form-control" id="fTime" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Channel</label>
                        <select class="form-select" id="fChannel">
                            <option>Walk-in</option>
                            <option>Online</option>
                            <option>Phone</option>
                            <option>Referral</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="fStatus">
                            <option>Pending</option>
                            <option>In Progress</option>
                            <option>Completed</option>
                            <option>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" id="fRemarks" rows="2" placeholder="Optional notes…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-primary-sm" onclick="saveAppointment()">
                    <i class="bi bi-check-lg"></i> <span id="saveBtnLabel">Save Appointment</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     VIEW MODAL
══════════════════════════════════════════════════ -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <!-- filled dynamically -->
            </div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════
     CONFIRM CANCEL MODAL
══════════════════════════════════════════════════ -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.85rem;color:var(--text-body);margin:0;">This will mark the appointment as <strong>Cancelled</strong>. Continue?</p>
                <input type="hidden" id="cancelId">
            </div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">No</button>
                <button class="btn-primary-sm" style="background:var(--red);" onclick="confirmCancel()">
                    <i class="bi bi-x-lg"></i> Yes, Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ────────────────────────────────────────────────────────────
    // Config
    // ────────────────────────────────────────────────────────────
    const HANDLER = 'appointments_handler.php';
    let currentPage = 1;
    let searchTimer = null;

    // ────────────────────────────────────────────────────────────
    // Init
    // ────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        loadDoctors();
        loadPatientsForForm();
        loadAppointments(1);
    });

    // ────────────────────────────────────────────────────────────
    // Load doctors dropdown (toolbar + form)
    // ────────────────────────────────────────────────────────────
    function loadDoctors() {
        fetch(`${HANDLER}?action=get_doctors`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const toolbar = document.getElementById('apptDoctor');
                const form = document.getElementById('fDoctor');
                res.data.forEach(d => {
                    toolbar.insertAdjacentHTML('beforeend',
                        `<option value="${d.id}">${d.name} — ${d.specialization}</option>`);
                    form.insertAdjacentHTML('beforeend',
                        `<option value="${d.id}" data-spec="${d.specialization}">${d.name} (${d.specialization})</option>`);
                });
            });
    }

    // ────────────────────────────────────────────────────────────
    // Load patients for form dropdown
    // ────────────────────────────────────────────────────────────
    function loadPatientsForForm() {
        fetch(`${HANDLER}?action=get_patients&q=`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const sel = document.getElementById('fPatient');
                res.data.forEach(p => {
                    sel.insertAdjacentHTML('beforeend',
                        `<option value="${p.id}">${p.name} (${p.patientCode})</option>`);
                });
            });
    }

    // ────────────────────────────────────────────────────────────
    // Load / refresh appointments table
    // ────────────────────────────────────────────────────────────
    function loadAppointments(page) {
        currentPage = page || 1;

        const date = document.getElementById('apptDate').value;
        const search = encodeURIComponent(document.getElementById('apptSearch').value.trim());
        const status = encodeURIComponent(document.getElementById('apptStatus').value);
        const doctor = encodeURIComponent(document.getElementById('apptDoctor').value);

        // update toolbar date label
        const d = new Date(date + 'T00:00:00');
        document.getElementById('toolbarDate').textContent =
            '| ' + d.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });

        // show loader
        document.getElementById('tblLoading').style.display = 'flex';

        const url = `${HANDLER}?action=list&date=${date}&search=${search}&status=${status}&doctor=${doctor}&page=${currentPage}`;

        fetch(url)
            .then(r => r.json())
            .then(res => {
                document.getElementById('tblLoading').style.display = 'none';
                if (!res.success) return;
                renderRows(res.rows);
                renderStats(res.stats);
                renderPagination(res.total, res.page, res.limit);
            })
            .catch(() => {
                document.getElementById('tblLoading').style.display = 'none';
            });
    }

    // ────────────────────────────────────────────────────────────
    // Render table rows
    // ────────────────────────────────────────────────────────────
    const avatarColors = [
        ['#dbeafe', '#1d4ed8'],
        ['#d1fae5', '#065f46'],
        ['#fef3c7', '#92400e'],
        ['#ede9fe', '#5b21b6'],
        ['#cffafe', '#155e75'],
        ['#fee2e2', '#991b1b'],
    ];

    function initials(name) {
        return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    }

    function statusBadge(s) {
        const map = {
            'Completed': 'bg-success',
            'In Progress': 'bg-info',
            'Pending': 'bg-warning',
            'Cancelled': 'bg-danger',
        };
        return `<span class="badge ${map[s]||'bg-secondary'}">${s}</span>`;
    }

    function fmtTime(t) {
        if (!t) return '—';
        const [h, m] = t.split(':');
        const hr = parseInt(h);
        return `${hr > 12 ? hr-12 : hr || 12}:${m} ${hr >= 12 ? 'PM' : 'AM'}`;
    }

    function fmtDate(d) {
        if (!d) return '—';
        const dt = new Date(d + 'T00:00:00');
        return dt.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function renderRows(rows) {
        const tbody = document.getElementById('apptTbody');
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="9">
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>No appointments found for the selected filters.</p>
            </div>
        </td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map((r, i) => {
            const [bg, fg] = avatarColors[i % avatarColors.length];
            const ini = initials(r.patientName);
            const avatarHtml = r.patPhoto ?
                `<img src="${r.patPhoto}" alt="">` :
                ini;

            return `<tr>
            <td><a href="#" class="appt-id">${r.appointmentCode}</a></td>
            <td>
                <div class="pat-cell">
                    <div class="pat-avatar" style="background:${bg};color:${fg}">${avatarHtml}</div>
                    <span class="pat-name">${r.patientName}</span>
                </div>
            </td>
            <td>${r.doctorName}</td>
            <td>${r.specialization || '—'}</td>
            <td>${fmtDate(r.appointmentDate)}</td>
            <td>
                <div class="time-cell">
                    <i class="bi bi-clock"></i>${fmtTime(r.appointmentTime)}
                </div>
            </td>
            <td><span class="channel-chip">${r.channel}</span></td>
            <td>${statusBadge(r.status)}</td>
            <td>
                <div class="action-btns">
                    <button class="btn-act" title="View"   onclick="viewAppt(${r.id})"><i class="bi bi-eye"></i></button>
                    <button class="btn-act" title="Edit"   onclick="editAppt(${r.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn-act del" title="Cancel" onclick="openCancel(${r.id})"><i class="bi bi-x-lg"></i></button>
                </div>
            </td>
        </tr>`;
        }).join('');
    }

    // ────────────────────────────────────────────────────────────
    // Render stats
    // ────────────────────────────────────────────────────────────
    function renderStats(s) {
        document.getElementById('statTotal').textContent = s.total;
        document.getElementById('statCompleted').textContent = s['Completed'] || 0;
        document.getElementById('statPending').textContent = s['Pending'] || 0;
        document.getElementById('statCancelled').textContent = s['Cancelled'] || 0;

        const rate = s.total ? Math.round((s['Completed'] / s.total) * 100) : 0;
        document.getElementById('statTotalSub').textContent = 'Appointments today';
        document.getElementById('statCompletedSub').textContent = `${rate}% completion rate`;
    }

    // ────────────────────────────────────────────────────────────
    // Pagination
    // ────────────────────────────────────────────────────────────
    function renderPagination(total, page, limit) {
        const pages = Math.ceil(total / limit);
        const start = (page - 1) * limit + 1;
        const end = Math.min(page * limit, total);

        document.getElementById('paginationInfo').textContent =
            total ? `Showing ${start}–${end} of ${total} appointments` : 'No appointments';

        const wrap = document.getElementById('paginationBtns');
        wrap.innerHTML = '';

        if (pages <= 1) return;

        const btn = (label, p, active = false) => {
            const b = document.createElement('button');
            b.className = 'btn-act';
            b.innerHTML = label;
            b.style.cssText = `border-radius:8px;padding:4px 12px;font-size:.78rem;${active?'background:var(--blue-600);color:#fff;border-color:var(--blue-600);':''}`;
            if (p) b.onclick = () => loadAppointments(p);
            else b.disabled = true;
            return b;
        };

        wrap.appendChild(btn('‹ Prev', page > 1 ? page - 1 : null));
        for (let i = 1; i <= pages; i++) {
            wrap.appendChild(btn(i, i, i === page));
        }
        wrap.appendChild(btn('Next ›', page < pages ? page + 1 : null));
    }

    // ────────────────────────────────────────────────────────────
    // Debounce search
    // ────────────────────────────────────────────────────────────
    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadAppointments(1), 350);
    }

    // ────────────────────────────────────────────────────────────
    // ADD modal
    // ────────────────────────────────────────────────────────────
    function openAddModal() {
        document.getElementById('apptModalTitle').textContent = 'New Appointment';
        document.getElementById('saveBtnLabel').textContent = 'Save Appointment';
        document.getElementById('editId').value = '';
        document.getElementById('fPatient').value = '';
        document.getElementById('fDoctor').value = '';
        document.getElementById('fDate').value = document.getElementById('apptDate').value;
        document.getElementById('fTime').value = '';
        document.getElementById('fChannel').value = 'Walk-in';
        document.getElementById('fStatus').value = 'Pending';
        document.getElementById('fRemarks').value = '';
        new bootstrap.Modal(document.getElementById('apptModal')).show();
    }

    // ────────────────────────────────────────────────────────────
    // EDIT modal
    // ────────────────────────────────────────────────────────────
    function editAppt(id) {
        fetch(`${HANDLER}?action=get&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return alert('Could not load appointment.');
                const d = res.data;
                document.getElementById('apptModalTitle').textContent = 'Edit Appointment';
                document.getElementById('saveBtnLabel').textContent = 'Update Appointment';
                document.getElementById('editId').value = d.id;
                document.getElementById('fPatient').value = d.patientId;
                document.getElementById('fDoctor').value = d.doctorId;
                document.getElementById('fDate').value = d.appointmentDate;
                document.getElementById('fTime').value = d.appointmentTime.slice(0, 5);
                document.getElementById('fChannel').value = d.channel;
                document.getElementById('fStatus').value = d.status;
                document.getElementById('fRemarks').value = d.remarks || '';
                new bootstrap.Modal(document.getElementById('apptModal')).show();
            });
    }

    // ────────────────────────────────────────────────────────────
    // SAVE (add or edit)
    // ────────────────────────────────────────────────────────────
    function saveAppointment() {
        const id = document.getElementById('editId').value;
        const payload = {
            id: id || undefined,
            patientId: document.getElementById('fPatient').value,
            doctorId: document.getElementById('fDoctor').value,
            appointmentDate: document.getElementById('fDate').value,
            appointmentTime: document.getElementById('fTime').value,
            channel: document.getElementById('fChannel').value,
            status: document.getElementById('fStatus').value,
            remarks: document.getElementById('fRemarks').value,
        };

        if (!payload.patientId || !payload.doctorId || !payload.appointmentDate || !payload.appointmentTime) {
            alert('Please fill in all required fields.');
            return;
        }

        const action = id ? 'edit' : 'add';

        fetch(`${HANDLER}?action=${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload),
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('apptModal')).hide();
                    loadAppointments(currentPage);
                } else {
                    alert('Failed to save. Please try again.');
                }
            });
    }

    // ────────────────────────────────────────────────────────────
    // VIEW modal
    // ────────────────────────────────────────────────────────────
    function viewAppt(id) {
        fetch(`${HANDLER}?action=get&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return alert('Could not load appointment.');
                const d = res.data;
                document.getElementById('viewModalBody').innerHTML = `
                <div class="detail-row"><span class="detail-label">Code</span>       <span class="detail-value">${d.appointmentCode}</span></div>
                <div class="detail-row"><span class="detail-label">Patient</span>    <span class="detail-value">${d.patientName}</span></div>
                <div class="detail-row"><span class="detail-label">Doctor</span>     <span class="detail-value">${d.doctorName}</span></div>
                <div class="detail-row"><span class="detail-label">Specialization</span><span class="detail-value">${d.specialization||'—'}</span></div>
                <div class="detail-row"><span class="detail-label">Date</span>       <span class="detail-value">${fmtDate(d.appointmentDate)}</span></div>
                <div class="detail-row"><span class="detail-label">Time</span>       <span class="detail-value">${fmtTime(d.appointmentTime)}</span></div>
                <div class="detail-row"><span class="detail-label">Channel</span>    <span class="detail-value">${d.channel}</span></div>
                <div class="detail-row"><span class="detail-label">Status</span>     <span class="detail-value">${statusBadge(d.status)}</span></div>
                <div class="detail-row"><span class="detail-label">Remarks</span>    <span class="detail-value">${d.remarks||'—'}</span></div>
            `;
                new bootstrap.Modal(document.getElementById('viewModal')).show();
            });
    }

    // ────────────────────────────────────────────────────────────
    // CANCEL
    // ────────────────────────────────────────────────────────────
    function openCancel(id) {
        document.getElementById('cancelId').value = id;
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    function confirmCancel() {
        const id = document.getElementById('cancelId').value;
        const fd = new FormData();
        fd.append('id', id);

        fetch(`${HANDLER}?action=cancel`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
                    loadAppointments(currentPage);
                } else {
                    alert('Failed to cancel. Please try again.');
                }
            });
    }
</script>

<?php include('./includes/footer.php'); ?>