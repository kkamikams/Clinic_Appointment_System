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
        --violet: #8b5cf6;
        --violet-light: #ede9fe;
        --violet-dark: #5b21b6;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md: 0 4px 16px rgba(0,0,0,.07);
        --shadow-lg: 0 8px 30px rgba(0,0,0,.10);
    }

    .page-appt, .page-appt * {
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

    /* ── STAT STRIP ── */
    .stat-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 1.5rem;
    }

    @media(max-width:768px) {
        .stat-strip { grid-template-columns: repeat(2, 1fr); }
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

    .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
    .stat-card:nth-child(1) { border-left-color: var(--blue-500);  animation-delay: .04s; }
    .stat-card:nth-child(2) { border-left-color: var(--green);     animation-delay: .09s; }
    .stat-card:nth-child(3) { border-left-color: var(--amber);     animation-delay: .14s; }
    .stat-card:nth-child(4) { border-left-color: var(--violet);    animation-delay: .19s; }

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

    /* ── MAIN TABLE CARD ── */
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

    .search-box { position: relative; }

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
        width: 210px;
        transition: border-color .2s;
    }

    .search-box input:focus { border-color: var(--blue-400); background: #fff; }

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
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-primary-sm:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37,99,235,.25);
        color: #fff;
    }

    /* ── TABLE ── */
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
        padding: .75rem .6rem;
    }

    .table tbody tr:last-child td { border-bottom: none; }
    .table tbody tr:hover td { background: var(--blue-50); transition: background .15s; }

    .appt-id {
        font-weight: 700;
        color: var(--blue-700);
        font-size: .8rem;
    }

    .doc-cell { display: flex; align-items: center; gap: 9px; }

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
        font-weight: 700;
    }

    .doc-name {
        font-weight: 600;
        color: var(--text-dark);
        font-size: .82rem;
    }

    .doc-spec { font-size: .66rem; color: var(--text-muted); }

    /* ── BADGES ── */
    .badge {
        font-family: 'DM Sans', sans-serif;
        font-size: .63rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 9px;
        letter-spacing: .03em;
    }

    .badge-confirmed  { background: var(--green-light);  color: var(--green-dark); }
    .badge-pending    { background: var(--amber-light);  color: var(--amber-dark); }
    .badge-cancelled  { background: var(--red-light);    color: var(--red-dark); }
    .badge-completed  { background: var(--blue-100);     color: var(--blue-700); }

    /* ── ACTION BUTTONS ── */
    .action-btns { display: flex; gap: 5px; }

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

    .btn-act:hover { background: var(--blue-50); color: var(--blue-600); border-color: var(--blue-200); }
    .btn-act.del:hover { background: var(--red-light); color: var(--red); border-color: #fca5a5; }
    .btn-act:disabled { opacity: .4; cursor: not-allowed; }

    /* ── PAGINATION ── */
    .tbl-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 1rem;
    }

    .tbl-footer span { font-size: .75rem; color: var(--text-muted); }
    .pg-btns { display: flex; gap: 5px; }

    .pg-btns button {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 4px 12px;
        font-size: .78rem;
        font-family: 'DM Sans', sans-serif;
        background: #fff;
        color: var(--text-body);
        cursor: pointer;
        transition: background .15s;
    }

    .pg-btns button.active {
        background: var(--blue-600);
        color: #fff;
        border-color: var(--blue-600);
    }

    .pg-btns button:hover:not(.active) { background: var(--surface); }

    /* ── MODAL ── */
    .modal-content {
        border-radius: var(--radius);
        border: 1px solid var(--border);
        font-family: 'DM Sans', sans-serif;
    }

    .modal-header { border-bottom: 1px solid var(--border); padding: 1.1rem 1.5rem; }
    .modal-footer { border-top: 1px solid var(--border); padding: .85rem 1.5rem; }
    .modal-body   { padding: 1.5rem; }

    .modal-title {
        font-weight: 700;
        font-size: 1rem;
        color: var(--text-dark);
    }

    .detail-group { margin-bottom: 1rem; }

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

    .btn-modal-close:hover { background: var(--surface); }

    /* ── CANCEL CONFIRM MODAL ── */
    .cancel-confirm-body {
        text-align: center;
        padding: 2rem 1.5rem;
    }

    .cancel-confirm-body .cc-icon {
        width: 56px;
        height: 56px;
        background: var(--red-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        color: var(--red);
    }

    .cancel-confirm-body h5 { font-weight: 700; color: var(--text-dark); margin-bottom: .4rem; }
    .cancel-confirm-body p  { font-size: .875rem; color: var(--text-muted); }

    .btn-danger-sm {
        background: var(--red);
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
        transition: background .15s;
    }

    .btn-danger-sm:hover { background: var(--red-dark); }

    /* ── EMPTY STATE ── */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }

    .empty-state i { font-size: 2.5rem; margin-bottom: .75rem; display: block; }
    .empty-state p { font-size: .875rem; margin: 0; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- PAGE TITLE -->
<div class="pagetitle">
    <h1>My Appointments</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">My Appointments</li>
        </ol>
    </nav>
</div>

<section class="section page-appt">

    <!-- STAT STRIP -->
    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Appointments</div>
            <div class="sc-num">7</div>
            <div class="sc-sub">All time bookings</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Confirmed</div>
            <div class="sc-num">2</div>
            <div class="sc-sub">Upcoming sessions</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Pending</div>
            <div class="sc-num">2</div>
            <div class="sc-sub">Awaiting approval</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Completed</div>
            <div class="sc-num">2</div>
            <div class="sc-sub">Past visits</div>
        </div>
    </div>

    <!-- MAIN TABLE CARD -->
    <div class="main-card">
        <div class="table-toolbar">
            <h5>All Appointments <span>| <?php echo date('F j, Y'); ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="apptSearch" placeholder="Search doctor or dept…" oninput="filterAppt()"/>
            </div>
            <select class="filter-select" id="apptStatus" onchange="filterAppt()">
                <option value="">All Status</option>
                <option>Confirmed</option>
                <option>Pending</option>
                <option>Cancelled</option>
                <option>Completed</option>
            </select>
            <a href="book_appointment.php" class="btn-primary-sm">
                <i class="bi bi-plus-lg"></i> New Appointment
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="table" id="apptTable">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="apptTbody">

                    <tr data-doctor="Dr. Princess Mary Lapura" data-dept="Dermatology" data-status="Confirmed">
                        <td><span class="appt-id">APT-001</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#dbeafe;color:#1d4ed8;">PL</div>
                                <div>
                                    <div class="doc-name">Dr. Princess Mary Lapura</div>
                                    <div class="doc-spec">Dermatology</div>
                                </div>
                            </div>
                        </td>
                        <td>Dermatology</td>
                        <td>April 5, 2026</td>
                        <td>10:00 AM</td>
                        <td><span class="badge badge-confirmed">Confirmed</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-001','Dr. Princess Mary Lapura','Dermatology','April 5, 2026','10:00 AM','Confirmed','Follow-up for skin allergy treatment.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" onclick="openCancelModal(this, 'APT-001')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Jose Reyes" data-dept="Internal Medicine" data-status="Pending">
                        <td><span class="appt-id">APT-002</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#d1fae5;color:#065f46;">JR</div>
                                <div>
                                    <div class="doc-name">Dr. Jose Reyes</div>
                                    <div class="doc-spec">Internal Medicine</div>
                                </div>
                            </div>
                        </td>
                        <td>Internal Medicine</td>
                        <td>April 8, 2026</td>
                        <td>02:30 PM</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-002','Dr. Jose Reyes','Internal Medicine','April 8, 2026','02:30 PM','Pending','General check-up and blood pressure monitoring.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" onclick="openCancelModal(this, 'APT-002')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Maria Santos" data-dept="Pediatrics" data-status="Completed">
                        <td><span class="appt-id">APT-003</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#fef3c7;color:#92400e;">MS</div>
                                <div>
                                    <div class="doc-name">Dr. Maria Santos</div>
                                    <div class="doc-spec">Pediatrics</div>
                                </div>
                            </div>
                        </td>
                        <td>Pediatrics</td>
                        <td>March 28, 2026</td>
                        <td>09:00 AM</td>
                        <td><span class="badge badge-completed">Completed</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-003','Dr. Maria Santos','Pediatrics','March 28, 2026','09:00 AM','Completed','Routine pediatric checkup.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" disabled style="opacity:.4;cursor:not-allowed;"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Ramon Cruz" data-dept="Orthopedics" data-status="Cancelled">
                        <td><span class="appt-id">APT-004</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#fee2e2;color:#991b1b;">RC</div>
                                <div>
                                    <div class="doc-name">Dr. Ramon Cruz</div>
                                    <div class="doc-spec">Orthopedics</div>
                                </div>
                            </div>
                        </td>
                        <td>Orthopedics</td>
                        <td>March 20, 2026</td>
                        <td>11:30 AM</td>
                        <td><span class="badge badge-cancelled">Cancelled</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-004','Dr. Ramon Cruz','Orthopedics','March 20, 2026','11:30 AM','Cancelled','Left knee pain assessment.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" disabled style="opacity:.4;cursor:not-allowed;"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Princess Mary Lapura" data-dept="Dermatology" data-status="Confirmed">
                        <td><span class="appt-id">APT-005</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#dbeafe;color:#1d4ed8;">PL</div>
                                <div>
                                    <div class="doc-name">Dr. Princess Mary Lapura</div>
                                    <div class="doc-spec">Dermatology</div>
                                </div>
                            </div>
                        </td>
                        <td>Dermatology</td>
                        <td>April 12, 2026</td>
                        <td>03:00 PM</td>
                        <td><span class="badge badge-confirmed">Confirmed</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-005','Dr. Princess Mary Lapura','Dermatology','April 12, 2026','03:00 PM','Confirmed','Acne treatment follow-up.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" onclick="openCancelModal(this, 'APT-005')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Angela Villanueva" data-dept="Cardiology" data-status="Pending">
                        <td><span class="appt-id">APT-006</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#ede9fe;color:#5b21b6;">AV</div>
                                <div>
                                    <div class="doc-name">Dr. Angela Villanueva</div>
                                    <div class="doc-spec">Cardiology</div>
                                </div>
                            </div>
                        </td>
                        <td>Cardiology</td>
                        <td>April 15, 2026</td>
                        <td>08:00 AM</td>
                        <td><span class="badge badge-pending">Pending</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-006','Dr. Angela Villanueva','Cardiology','April 15, 2026','08:00 AM','Pending','Annual cardiac screening and ECG.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" onclick="openCancelModal(this, 'APT-006')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Maria Santos" data-dept="Pediatrics" data-status="Completed">
                        <td><span class="appt-id">APT-007</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#fef3c7;color:#92400e;">MS</div>
                                <div>
                                    <div class="doc-name">Dr. Maria Santos</div>
                                    <div class="doc-spec">Pediatrics</div>
                                </div>
                            </div>
                        </td>
                        <td>Pediatrics</td>
                        <td>March 10, 2026</td>
                        <td>01:00 PM</td>
                        <td><span class="badge badge-completed">Completed</span></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" title="View" onclick="openViewModal('APT-007','Dr. Maria Santos','Pediatrics','March 10, 2026','01:00 PM','Completed','Vaccination schedule update.')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn-act del" title="Cancel" disabled style="opacity:.4;cursor:not-allowed;"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

            <div class="empty-state" id="emptyState" style="display:none;">
                <i class="bi bi-calendar-x"></i>
                <p>No appointments found matching your filter.</p>
            </div>
        </div>

        <div class="tbl-footer">
            <span id="showingLabel">Showing 7 of 7 appointments</span>
            <div class="pg-btns">
                <button>‹ Prev</button>
                <button class="active">1</button>
                <button>2</button>
                <button>Next ›</button>
            </div>
        </div>
    </div>

</section>

<!-- VIEW APPOINTMENT MODAL -->
<div class="modal fade" id="viewApptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check me-2" style="color:var(--blue-600);"></i>
                    Appointment Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="detail-group">
                            <div class="detail-label">Appointment ID</div>
                            <div class="detail-value" id="modal-appt-id">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-group">
                            <div class="detail-label">Status</div>
                            <div id="modal-status">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Doctor</div>
                            <div class="detail-value" id="modal-doctor">—</div>
                            <div style="font-size:.75rem;color:var(--text-muted);" id="modal-spec">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-group">
                            <div class="detail-label">Date</div>
                            <div class="detail-value" id="modal-date">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="detail-group">
                            <div class="detail-label">Time</div>
                            <div class="detail-value" id="modal-time">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Notes</div>
                            <div style="font-size:.85rem;color:var(--text-body);" id="modal-notes">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-close" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CANCEL CONFIRM MODAL -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body cancel-confirm-body">
                <div class="cc-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h5>Cancel Appointment?</h5>
                <p>This will mark <strong id="cancelApptId"></strong> as Cancelled. This action cannot be undone.</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn-modal-close" data-bs-dismiss="modal">Go Back</button>
                    <button class="btn-danger-sm" id="confirmCancelBtn">
                        <i class="bi bi-x-circle"></i> Yes, Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function filterAppt() {
        const q  = document.getElementById('apptSearch').value.toLowerCase();
        const st = document.getElementById('apptStatus').value;
        let visible = 0;

        document.querySelectorAll('#apptTbody tr').forEach(row => {
            const doc    = (row.dataset.doctor || '').toLowerCase();
            const dept   = (row.dataset.dept   || '').toLowerCase();
            const status = row.dataset.status  || '';
            const matchQ = !q  || doc.includes(q) || dept.includes(q);
            const matchS = !st || status === st;
            const show   = matchQ && matchS;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('emptyState').style.display = visible === 0 ? '' : 'none';
        const total = document.querySelectorAll('#apptTbody tr').length;
        document.getElementById('showingLabel').textContent = `Showing ${visible} of ${total} appointments`;
    }

    function openViewModal(id, doctor, spec, date, time, status, notes) {
        document.getElementById('modal-appt-id').textContent = id;
        document.getElementById('modal-doctor').textContent  = doctor;
        document.getElementById('modal-spec').textContent    = spec;
        document.getElementById('modal-date').textContent    = date;
        document.getElementById('modal-time').textContent    = time;
        document.getElementById('modal-notes').textContent   = notes;

        const badgeMap = {
            'Confirmed':  'badge-confirmed',
            'Pending':    'badge-pending',
            'Cancelled':  'badge-cancelled',
            'Completed':  'badge-completed',
        };
        document.getElementById('modal-status').innerHTML =
            `<span class="badge ${badgeMap[status] || ''}">${status}</span>`;

        new bootstrap.Modal(document.getElementById('viewApptModal')).show();
    }

    let cancelTargetRow = null;

    function openCancelModal(btn, apptId) {
        cancelTargetRow = btn.closest('tr');
        document.getElementById('cancelApptId').textContent = apptId;
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    }

    document.getElementById('confirmCancelBtn').addEventListener('click', () => {
        if (!cancelTargetRow) return;

        const statusCell = cancelTargetRow.querySelector('td:nth-child(6)');
        statusCell.innerHTML = '<span class="badge badge-cancelled">Cancelled</span>';
        cancelTargetRow.dataset.status = 'Cancelled';

        const cancelBtn = cancelTargetRow.querySelector('.btn-act.del');
        if (cancelBtn) { cancelBtn.disabled = true; cancelBtn.style.opacity = '.4'; }

        bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
        cancelTargetRow = null;
        filterAppt();
    });
</script>

<?php include('./includes/footer.php'); ?>
