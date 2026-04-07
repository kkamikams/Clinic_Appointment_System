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

    .date-picker-wrap {
        position: relative;
        display: flex;
        align-items: center;
        gap: 6px;
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

    .btn-all-dates {
        background: var(--surface);
        color: var(--text-body);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .42rem .75rem;
        font-size: .75rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        white-space: nowrap;
        transition: all .15s;
    }

    .btn-all-dates:hover,
    .btn-all-dates.active {
        background: var(--blue-50);
        color: var(--blue-600);
        border-color: var(--blue-200);
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

    /* ── Status dropdown (matches patients page) ── */
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
        min-width: 155px;
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
        font-family: 'DM Sans', sans-serif;
        transition: background .12s;
    }

    .status-opt:hover {
        background: var(--surface);
    }

    .status-opt .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-block;
    }

    /* Static badge used inside the button */
    .appt-badge {
        font-family: 'DM Sans', sans-serif;
        font-size: .63rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 9px;
        letter-spacing: .03em;
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

    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total</div>
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
            <div class="sc-sub">Selected filter</div>
        </div>
    </div>

    <div class="main-card">
        <div class="table-toolbar">
            <h5>Appointment List <span id="toolbarDate">| <?php echo date('F j, Y'); ?></span></h5>

            <div style="display:flex;align-items:center;gap:6px;">
                <div class="date-picker-wrap">
                    <i class="bi bi-calendar3"></i>
                    <input type="date" id="apptDate" value="<?php echo date('Y-m-d'); ?>" onchange="onDateChange()">
                </div>
                <button class="btn-all-dates" id="btnAllDates" onclick="toggleAllDates()">All Dates</button>
            </div>

            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="apptSearch" placeholder="Search patient or doctor…" oninput="debounceSearch()">
            </div>
            <select class="filter-select" id="apptStatus" onchange="loadAppointments(1)">
                <option value="">All Status</option>
                <option>Completed</option>
                <option>In Progress</option>
                <option>Pending</option>
                <option>Cancelled</option>
            </select>
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

        <div class="d-flex align-items-center justify-content-between mt-3" style="flex-wrap:wrap;gap:8px;" id="paginationWrap">
            <span style="font-size:.75rem;color:var(--text-muted);" id="paginationInfo"></span>
            <div style="display:flex;gap:5px;" id="paginationBtns"></div>
        </div>
    </div>

</section>

<!-- ══════════════════════════════════════════════════════════════════
     ADD / EDIT MODAL
═══════════════════════════════════════════════════════════════════ -->
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

                    <!-- ── PATIENT COLUMN ─────────────────────────── -->
                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <div id="patientSection">

                            <div style="display:flex;gap:0;border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;margin-bottom:10px;">
                                <button type="button" id="tabExisting" onclick="switchPatientTab('existing')"
                                    style="flex:1;padding:.42rem .75rem;font-size:.78rem;font-weight:600;font-family:'DM Sans',sans-serif;border:none;cursor:pointer;background:var(--blue-600);color:#fff;transition:all .15s;">
                                    <i class="bi bi-search"></i> Search Existing
                                </button>
                                <button type="button" id="tabNew" onclick="switchPatientTab('new')"
                                    style="flex:1;padding:.42rem .75rem;font-size:.78rem;font-weight:600;font-family:'DM Sans',sans-serif;border:none;cursor:pointer;background:var(--surface);color:var(--text-body);transition:all .15s;">
                                    <i class="bi bi-person-plus"></i> New Patient
                                </button>
                            </div>

                            <div id="containerExisting" style="display:flex;flex-direction:column;gap:8px;">
                                <div style="position:relative;">
                                    <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
                                    <input type="text" id="patientSearchInput" class="form-control"
                                        placeholder="Type name or patient code…"
                                        style="padding-left:2rem;"
                                        oninput="debouncePatientSearch()" autocomplete="off">
                                </div>
                                <div id="patientSearchResults"
                                    style="display:none;border:1px solid var(--border);border-radius:var(--radius-sm);background:#fff;max-height:180px;overflow-y:auto;"></div>
                                <div id="selectedPatientCard"
                                    style="display:none;background:var(--blue-50);border:1px solid var(--blue-200);border-radius:var(--radius-sm);padding:.6rem .85rem;font-size:.82rem;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;">
                                        <div>
                                            <div style="font-weight:700;color:var(--text-dark);" id="selPatName">—</div>
                                            <div style="color:var(--text-muted);font-size:.72rem;" id="selPatMeta">—</div>
                                        </div>
                                        <button type="button" onclick="clearSelectedPatient()"
                                            style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.85rem;padding:2px 6px;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="fPatient">
                            </div>

                            <div id="containerNew" style="display:none;flex-direction:column;gap:6px;">
                                <input type="text" class="form-control" id="fNewPatientName" placeholder="Full name *">
                                <label class="form-label" style="margin-bottom:0;margin-top:4px;">Date of Birth</label>
                                <input type="date" class="form-control" id="fNewPatientDOB">
                                <input type="tel" class="form-control" id="fNewPatientContact" placeholder="Contact number">
                                <input type="email" class="form-control" id="fNewPatientEmail" placeholder="Email address">
                                <select class="form-select" id="fNewPatientGender">
                                    <option value="">Select gender</option>
                                    <option>Male</option>
                                    <option>Female</option>
                                    <option>Other</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <!-- ── END PATIENT COLUMN ─────────────────────── -->

                    <div class="col-md-6">
                        <label class="form-label">Doctor</label>
                        <select class="form-select" id="fDoctor" required onchange="loadAdminSlots()">
                            <option value="">Select doctor…</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="fDate" required onchange="loadAdminSlots()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time</label>
                        <input type="hidden" id="fTime">
                        <div id="adminSlotsContainer">
                            <div style="font-size:.78rem;color:var(--text-muted);">Select a doctor and date first.</div>
                        </div>
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

<!-- ══════════════════════════════════════════════════════════════════
     VIEW MODAL
═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"></div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     CANCEL MODAL
═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.85rem;color:var(--text-body);margin:0;">
                    This will mark the appointment as <strong>Cancelled</strong>. Continue?
                </p>
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
    const HANDLER = 'appointments_handler.php';
    let currentPage = 1;
    let searchTimer = null;
    let showAllDates = false;

    // ── Status config (single source of truth) ────────────────────────
    const STATUS_CONFIG = {
        'Pending': {
            dot: '#f59e0b',
            bg: '#fef3c7',
            color: '#92400e'
        },
        'In Progress': {
            dot: '#06b6d4',
            bg: '#cffafe',
            color: '#155e75'
        },
        'Completed': {
            dot: '#10b981',
            bg: '#d1fae5',
            color: '#065f46'
        },
        'Cancelled': {
            dot: '#ef4444',
            bg: '#fee2e2',
            color: '#991b1b'
        },
    };

    document.addEventListener('DOMContentLoaded', () => {
        loadDoctors();
        loadAppointments(1);
    });

    // ── Close dropdowns when clicking outside ─────────────────────────
    document.addEventListener('click', e => {
        if (!e.target.closest('.status-cell'))
            document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
    });

    // ── Toggle dropdown open/close ────────────────────────────────────
    function toggleStatusDrop(btn) {
        const dd = btn.nextElementSibling;
        const isOpen = dd.classList.contains('open');
        document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) dd.classList.add('open');
    }

    // ── Pick a status from the dropdown ──────────────────────────────
    function pickApptStatus(optEl, newStatus, id) {
        const cfg = STATUS_CONFIG[newStatus];
        const dd = optEl.closest('.status-dropdown');
        const btn = dd.previousElementSibling;
        const badge = btn.querySelector('.appt-badge');

        // Update badge instantly
        badge.style.background = cfg.bg;
        badge.style.color = cfg.color;
        badge.textContent = newStatus;
        dd.classList.remove('open');

        // Save to server
        const fd = new FormData();
        fd.append('id', id);
        fd.append('status', newStatus);

        fetch(`${HANDLER}?action=update_status`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    renderStats(res.stats, showAllDates);
                    showToast('Status updated to "' + newStatus + '"', 'success');
                } else {
                    loadAppointments(currentPage);
                }
            })
            .catch(() => loadAppointments(currentPage));
    }

    // ── Build the status dropdown cell HTML ───────────────────────────
    function statusDropdown(id, current) {
        const cfg = STATUS_CONFIG[current] || {
            dot: '#9ca3af',
            bg: '#f3f4f6',
            color: '#374151'
        };
        const opts = Object.entries(STATUS_CONFIG).map(([label, c]) => `
            <div class="status-opt" onclick="pickApptStatus(this,'${label}',${id})">
                <span class="dot" style="background:${c.dot};"></span>
                ${label}
            </div>
        `).join('');

        return `
            <div class="status-cell">
                <button class="badge-btn" onclick="toggleStatusDrop(this)">
                    <span class="appt-badge"
                        style="background:${cfg.bg};color:${cfg.color};font-family:'DM Sans',sans-serif;font-size:.63rem;font-weight:600;border-radius:6px;padding:3px 9px;letter-spacing:.03em;">
                        ${current}
                    </span>
                    <span class="badge-caret">▾</span>
                </button>
                <div class="status-dropdown">
                    ${opts}
                </div>
            </div>
        `;
    }

    // ── All Dates toggle ──────────────────────────────────────────────
    function toggleAllDates() {
        showAllDates = !showAllDates;
        const btn = document.getElementById('btnAllDates');
        const dateInput = document.getElementById('apptDate');

        if (showAllDates) {
            btn.classList.add('active');
            dateInput.style.opacity = '0.4';
            dateInput.disabled = true;
            document.getElementById('toolbarDate').textContent = '| All Dates';
        } else {
            btn.classList.remove('active');
            dateInput.style.opacity = '1';
            dateInput.disabled = false;
            const d = new Date(dateInput.value + 'T00:00:00');
            document.getElementById('toolbarDate').textContent =
                '| ' + d.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
        }
        loadAppointments(1);
    }

    function onDateChange() {
        if (showAllDates) {
            showAllDates = false;
            document.getElementById('btnAllDates').classList.remove('active');
            document.getElementById('apptDate').style.opacity = '1';
            document.getElementById('apptDate').disabled = false;
        }
        loadAppointments(1);
    }

    // ── Doctors ───────────────────────────────────────────────────────
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

    // ── Patient tab switcher ──────────────────────────────────────────
    function switchPatientTab(tab) {
        const isExisting = tab === 'existing';
        document.getElementById('tabExisting').style.background = isExisting ? 'var(--blue-600)' : 'var(--surface)';
        document.getElementById('tabExisting').style.color = isExisting ? '#fff' : 'var(--text-body)';
        document.getElementById('tabNew').style.background = isExisting ? 'var(--surface)' : 'var(--blue-600)';
        document.getElementById('tabNew').style.color = isExisting ? 'var(--text-body)' : '#fff';
        document.getElementById('containerExisting').style.display = isExisting ? 'flex' : 'none';
        document.getElementById('containerNew').style.display = isExisting ? 'none' : 'flex';
        if (isExisting) clearNewPatientFields();
        else clearSelectedPatient();
    }

    // ── Existing patient live search ──────────────────────────────────
    let patientSearchTimer = null;

    function debouncePatientSearch() {
        clearTimeout(patientSearchTimer);
        patientSearchTimer = setTimeout(runPatientSearch, 300);
    }

    function runPatientSearch() {
        const q = document.getElementById('patientSearchInput').value.trim();
        const results = document.getElementById('patientSearchResults');
        if (!q) {
            results.style.display = 'none';
            return;
        }

        fetch(`${HANDLER}?action=get_patients&q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data.length) {
                    results.innerHTML = `<div style="padding:.6rem 1rem;font-size:.8rem;color:var(--text-muted);">No patients found.</div>`;
                    results.style.display = 'block';
                    return;
                }
                results.innerHTML = res.data.map(p => `
                    <div onclick="selectPatient(${p.id},'${escHtml(p.name)}','${escHtml(p.patientCode)}','${escHtml(p.contact||'')}','${escHtml(p.dob||'')}')"
                        style="padding:.55rem 1rem;font-size:.82rem;cursor:pointer;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;"
                        onmouseover="this.style.background='var(--blue-50)'" onmouseout="this.style.background='#fff'">
                        <div style="width:30px;height:30px;border-radius:50%;background:var(--blue-50);color:var(--blue-700);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;flex-shrink:0;">
                            ${initials(p.name)}
                        </div>
                        <div>
                            <div style="font-weight:600;color:var(--text-dark);">${p.name}</div>
                            <div style="font-size:.7rem;color:var(--text-muted);">${p.patientCode}${p.dob ? ' · ' + fmtDate(p.dob) : ''}${p.contact ? ' · ' + p.contact : ''}</div>
                        </div>
                    </div>
                `).join('');
                results.style.display = 'block';
            });
    }

    function selectPatient(id, name, code, contact, dob) {
        document.getElementById('fPatient').value = id;
        document.getElementById('patientSearchInput').value = '';
        document.getElementById('patientSearchResults').style.display = 'none';
        document.getElementById('selPatName').textContent = name;
        document.getElementById('selPatMeta').textContent =
            `${code}${dob ? ' · DOB: ' + fmtDate(dob) : ''}${contact ? ' · ' + contact : ''}`;
        document.getElementById('selectedPatientCard').style.display = 'block';
    }

    function clearSelectedPatient() {
        document.getElementById('fPatient').value = '';
        document.getElementById('patientSearchInput').value = '';
        document.getElementById('patientSearchResults').style.display = 'none';
        document.getElementById('selectedPatientCard').style.display = 'none';
    }

    function clearNewPatientFields() {
        ['fNewPatientName', 'fNewPatientDOB', 'fNewPatientContact', 'fNewPatientEmail'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const g = document.getElementById('fNewPatientGender');
        if (g) g.value = '';
    }

    function escHtml(str) {
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // ── Load appointments ─────────────────────────────────────────────
    function loadAppointments(page) {
        currentPage = page || 1;
        const date = showAllDates ? '' : document.getElementById('apptDate').value;
        const search = encodeURIComponent(document.getElementById('apptSearch').value.trim());
        const status = encodeURIComponent(document.getElementById('apptStatus').value);
        const doctor = encodeURIComponent(document.getElementById('apptDoctor').value);

        if (!showAllDates && date) {
            const d = new Date(date + 'T00:00:00');
            document.getElementById('toolbarDate').textContent =
                '| ' + d.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
        }

        document.getElementById('tblLoading').style.display = 'flex';

        fetch(`${HANDLER}?action=list&date=${date}&search=${search}&status=${status}&doctor=${doctor}&page=${currentPage}`)
            .then(r => r.json())
            .then(res => {
                document.getElementById('tblLoading').style.display = 'none';
                if (!res.success) return;
                renderRows(res.rows);
                renderStats(res.stats, showAllDates);
                renderPagination(res.total, res.page, res.limit);
            })
            .catch(() => {
                document.getElementById('tblLoading').style.display = 'none';
            });
    }

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

    function fmtTime(t) {
        if (!t) return '—';
        const [h, m] = t.split(':');
        const hr = parseInt(h);
        return `${hr > 12 ? hr - 12 : hr || 12}:${m} ${hr >= 12 ? 'PM' : 'AM'}`;
    }

    function fmtDate(d) {
        if (!d) return '—';
        return new Date(d + 'T00:00:00').toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function renderRows(rows) {
        const tbody = document.getElementById('apptTbody');
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>No appointments found for the selected filters.</p>
            </div></td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map((r, i) => {
            const [bg, fg] = avatarColors[i % avatarColors.length];
            const avatarHtml = r.patPhoto ?
                `<img src="${r.patPhoto}" alt="">` :
                initials(r.patientName || '??');
            return `<tr>
                <td><a href="#" class="appt-id">${r.appointmentCode}</a></td>
                <td><div class="pat-cell">
                    <div class="pat-avatar" style="background:${bg};color:${fg}">${avatarHtml}</div>
                    <span class="pat-name">${r.patientName || '—'}</span>
                </div></td>
                <td>${r.doctorName || '—'}</td>
                <td>${r.specialization || '—'}</td>
                <td>${fmtDate(r.appointmentDate)}</td>
                <td><div class="time-cell"><i class="bi bi-clock"></i>${fmtTime(r.appointmentTime)}</div></td>
                <td><span class="channel-chip">${r.channel}</span></td>
                <td>${statusDropdown(r.id, r.status)}</td>
                <td><div class="action-btns">
                    <button class="btn-act" title="View"   onclick="viewAppt(${r.id})"><i class="bi bi-eye"></i></button>
                    <button class="btn-act" title="Edit"   onclick="editAppt(${r.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn-act del" title="Cancel" onclick="openCancel(${r.id})"><i class="bi bi-x-lg"></i></button>
                </div></td>
            </tr>`;
        }).join('');
    }

    function renderStats(s, allDates) {
        document.getElementById('statTotal').textContent = s.total;
        document.getElementById('statCompleted').textContent = s['Completed'] || 0;
        document.getElementById('statPending').textContent = s['Pending'] || 0;
        document.getElementById('statCancelled').textContent = s['Cancelled'] || 0;
        const rate = s.total ? Math.round((s['Completed'] / s.total) * 100) : 0;
        document.getElementById('statTotalSub').textContent = allDates ? 'All appointments' : 'Appointments on selected date';
        document.getElementById('statCompletedSub').textContent = `${rate}% completion rate`;
    }

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
            b.style.cssText = `border-radius:8px;padding:4px 12px;font-size:.78rem;${active ? 'background:var(--blue-600);color:#fff;border-color:var(--blue-600);' : ''}`;
            if (p) b.onclick = () => loadAppointments(p);
            else b.disabled = true;
            return b;
        };

        wrap.appendChild(btn('‹ Prev', page > 1 ? page - 1 : null));
        for (let i = 1; i <= pages; i++) wrap.appendChild(btn(i, i, i === page));
        wrap.appendChild(btn('Next ›', page < pages ? page + 1 : null));
    }

    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadAppointments(1), 350);
    }

    // ── Open Add Modal ────────────────────────────────────────────────
    function openAddModal() {
        document.getElementById('apptModalTitle').textContent = 'New Appointment';
        document.getElementById('saveBtnLabel').textContent = 'Save Appointment';
        document.getElementById('editId').value = '';
        document.getElementById('fDoctor').value = '';
        document.getElementById('fDate').value = document.getElementById('apptDate').value || '';
        document.getElementById('fTime').value = '';
        document.getElementById('fChannel').value = 'Walk-in';
        document.getElementById('fStatus').value = 'Pending';
        document.getElementById('fRemarks').value = '';
        document.getElementById('adminSlotsContainer').innerHTML =
            '<div style="font-size:.78rem;color:var(--text-muted);">Select a doctor and date first.</div>';
        switchPatientTab('existing');
        clearSelectedPatient();
        clearNewPatientFields();
        new bootstrap.Modal(document.getElementById('apptModal')).show();
    }

    // ── Edit ──────────────────────────────────────────────────────────
    function editAppt(id) {
        fetch(`${HANDLER}?action=get&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return alert('Could not load appointment.');
                const d = res.data;
                document.getElementById('apptModalTitle').textContent = 'Edit Appointment';
                document.getElementById('saveBtnLabel').textContent = 'Update Appointment';
                document.getElementById('editId').value = d.id;
                document.getElementById('fDate').value = d.appointmentDate;
                document.getElementById('fDoctor').value = d.doctorId;
                document.getElementById('fChannel').value = d.channel;
                document.getElementById('fStatus').value = d.status;
                document.getElementById('fRemarks').value = d.remarks || '';

                switchPatientTab('existing');
                if (d.patientId) {
                    document.getElementById('fPatient').value = d.patientId;
                    document.getElementById('selPatName').textContent = d.patientName || '—';
                    document.getElementById('selPatMeta').textContent = '';
                    document.getElementById('selectedPatientCard').style.display = 'block';
                }

                loadAdminSlots();
                setTimeout(() => {
                    const existing = d.appointmentTime.slice(0, 5);
                    document.querySelectorAll('#adminSlotsContainer button').forEach(b => {
                        if (b.dataset.val === existing) selectAdminSlot(existing, b);
                    });
                }, 800);

                new bootstrap.Modal(document.getElementById('apptModal')).show();
            });
    }

    // ── Save ──────────────────────────────────────────────────────────
    function saveAppointment() {
        const id = document.getElementById('editId').value;
        const isNew = document.getElementById('containerNew').style.display !== 'none';
        const newName = document.getElementById('fNewPatientName')?.value.trim();

        if (isNew && !newName) {
            alert('Please enter the new patient name.');
            return;
        }
        if (!isNew && !document.getElementById('fPatient').value) {
            alert('Please search and select a patient.');
            return;
        }

        const payload = {
            id: id || undefined,
            patientId: isNew ? '' : document.getElementById('fPatient').value,
            patientName: isNew ? newName : '',
            patientDOB: isNew ? document.getElementById('fNewPatientDOB').value : '',
            patientContact: isNew ? document.getElementById('fNewPatientContact').value.trim() : '',
            patientEmail: isNew ? document.getElementById('fNewPatientEmail').value.trim() : '',
            patientGender: isNew ? document.getElementById('fNewPatientGender').value : '',
            doctorId: document.getElementById('fDoctor').value,
            appointmentDate: document.getElementById('fDate').value,
            appointmentTime: document.getElementById('fTime').value,
            channel: document.getElementById('fChannel').value,
            status: document.getElementById('fStatus').value,
            remarks: document.getElementById('fRemarks').value,
        };

        if (!payload.doctorId || !payload.appointmentDate || !payload.appointmentTime) {
            alert('Please fill in all required fields.');
            return;
        }

        fetch(`${HANDLER}?action=${id ? 'edit' : 'add'}`, {
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
                    if (showAllDates) {
                        showAllDates = false;
                        document.getElementById('btnAllDates').classList.remove('active');
                        document.getElementById('apptDate').disabled = false;
                        document.getElementById('apptDate').style.opacity = '1';
                    }
                    document.getElementById('apptDate').value = payload.appointmentDate;
                    loadAppointments(1);
                    showToast('Appointment saved successfully!', 'success');
                } else {
                    alert('Failed to save. Please try again.');
                }
            });
    }

    // ── View ──────────────────────────────────────────────────────────
    function viewAppt(id) {
        fetch(`${HANDLER}?action=get&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return alert('Could not load appointment.');
                const d = res.data;
                const cfg = STATUS_CONFIG[d.status] || {
                    bg: '#f3f4f6',
                    color: '#374151'
                };
                document.getElementById('viewModalBody').innerHTML = `
                    <div class="detail-row"><span class="detail-label">Code</span><span class="detail-value">${d.appointmentCode}</span></div>
                    <div class="detail-row"><span class="detail-label">Patient</span><span class="detail-value">${d.patientName || '—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Doctor</span><span class="detail-value">${d.doctorName || '—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Specialization</span><span class="detail-value">${d.specialization || '—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value">${fmtDate(d.appointmentDate)}</span></div>
                    <div class="detail-row"><span class="detail-label">Time</span><span class="detail-value">${fmtTime(d.appointmentTime)}</span></div>
                    <div class="detail-row"><span class="detail-label">Channel</span><span class="detail-value">${d.channel}</span></div>
                    <div class="detail-row"><span class="detail-label">Status</span>
                        <span class="detail-value">
                            <span style="background:${cfg.bg};color:${cfg.color};font-size:.63rem;font-weight:600;border-radius:6px;padding:3px 9px;letter-spacing:.03em;font-family:'DM Sans',sans-serif;">
                                ${d.status}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row"><span class="detail-label">Remarks</span><span class="detail-value">${d.remarks || '—'}</span></div>
                `;
                new bootstrap.Modal(document.getElementById('viewModal')).show();
            });
    }

    // ── Slots ─────────────────────────────────────────────────────────
    function loadAdminSlots() {
        const docId = document.getElementById('fDoctor').value;
        const date = document.getElementById('fDate').value;
        const container = document.getElementById('adminSlotsContainer');

        if (!docId || !date) {
            container.innerHTML = '<div style="font-size:.78rem;color:var(--text-muted);">Select a doctor and date first.</div>';
            document.getElementById('fTime').value = '';
            return;
        }

        container.innerHTML = '<div style="font-size:.78rem;color:var(--text-muted);">Loading slots…</div>';

        fetch(`${HANDLER}?action=get_slots&doctorId=${docId}&date=${date}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.slots.length) {
                    container.innerHTML = '<div style="font-size:.78rem;color:var(--text-muted);">No slots available for this day.</div>';
                    return;
                }
                let html = '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">';
                res.slots.forEach(slot => {
                    html += `<button type="button" data-val="${slot.value}"
                        style="border:1px solid var(--border);border-radius:8px;padding:4px 10px;font-size:.75rem;font-family:'DM Sans',sans-serif;background:var(--surface);color:var(--text-body);cursor:pointer;"
                        ${!slot.available ? 'disabled style="opacity:.45;cursor:not-allowed;"' : ''}
                        onclick="selectAdminSlot('${slot.value}', this)">${slot.label}</button>`;
                });
                html += '</div>';
                container.innerHTML = html;
            })
            .catch(() => {
                container.innerHTML = '<div style="font-size:.78rem;color:var(--red);">Failed to load slots.</div>';
            });
    }

    function selectAdminSlot(value, btn) {
        document.querySelectorAll('#adminSlotsContainer button').forEach(b => {
            b.style.background = 'var(--surface)';
            b.style.color = 'var(--text-body)';
            b.style.borderColor = 'var(--border)';
        });
        btn.style.background = 'var(--blue-600)';
        btn.style.color = '#fff';
        btn.style.borderColor = 'var(--blue-600)';
        document.getElementById('fTime').value = value;
    }

    // ── Cancel ────────────────────────────────────────────────────────
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

    // ── Toast ─────────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const colors = {
            success: '#065f46',
            error: '#991b1b',
            info: '#155e75'
        };
        const el = document.createElement('div');
        el.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:99999;background:${colors[type] || colors.success};color:#fff;border-radius:10px;padding:.65rem 1.1rem;font-size:.82rem;font-family:'DM Sans',sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.1);display:flex;align-items:center;gap:8px;animation:fadeUp .25s ease both;`;
        el.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${msg}`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }
</script>

<?php include('./includes/footer.php'); ?>