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

    .field-tag {
        font-size: .6rem;
        font-weight: 600;
        padding: 1px 6px;
        border-radius: 4px;
        margin-left: 5px;
        vertical-align: middle;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .field-tag.req {
        background: #fee2e2;
        color: #991b1b;
    }

    .field-tag.opt {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Audit trail timeline */
    .audit-trail {
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .audit-items-wrap {
        max-height: 180px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .audit-trail-title {
        font-size: .64rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: .75rem;
    }

    .audit-item {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: .35rem 0;
        font-size: .78rem;
        border-left: 2px solid var(--border);
        padding-left: 12px;
        margin-left: 4px;
        position: relative;
    }

    .audit-item::before {
        content: '';
        position: absolute;
        left: -5px;
        top: 10px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--border);
        border: 2px solid #fff;
    }

    .audit-item.create::before {
        background: var(--green);
    }

    .audit-item.edit::before {
        background: var(--blue-500);
    }

    .audit-item.status::before {
        background: var(--amber);
    }

    .audit-action {
        font-weight: 600;
        color: var(--text-dark);
    }

    .audit-who {
        color: var(--text-muted);
        font-size: .72rem;
    }

    .audit-arrow {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .7rem;
    }

    .audit-arrow .from {
        background: #fef3c7;
        color: #92400e;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .audit-arrow .to {
        background: #d1fae5;
        color: #065f46;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .page-records,
    .page-records * {
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
        border-left-color: var(--violet);
        animation-delay: .14s;
    }

    .stat-card:nth-child(4) {
        border-left-color: var(--amber);
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

    .rec-id {
        font-weight: 700;
        color: var(--blue-700);
        font-size: .8rem;
        text-decoration: none;
    }

    .rec-id:hover {
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

    .pat-sub {
        font-size: .66rem;
        color: var(--text-muted);
    }

    .diagnosis-cell {
        max-width: 180px;
    }

    .diag-text {
        font-size: .81rem;
        color: var(--text-body);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .diag-icd {
        font-size: .65rem;
        color: var(--text-muted);
        font-weight: 600;
        letter-spacing: .04em;
    }

    .rec-type-chip {
        font-size: .63rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 5px;
    }

    .chip-consultation {
        background: var(--blue-50);
        color: var(--blue-700);
        border: 1px solid var(--blue-100);
    }

    .chip-lab {
        background: var(--violet-light);
        color: var(--violet-dark);
        border: 1px solid #ddd6fe;
    }

    .chip-imaging {
        background: var(--teal-light);
        color: var(--teal-dark);
        border: 1px solid #a5f3fc;
    }

    .chip-prescription {
        background: var(--green-light);
        color: var(--green-dark);
        border: 1px solid #a7f3d0;
    }

    .chip-other {
        background: var(--amber-light);
        color: var(--amber-dark);
        border: 1px solid #fde68a;
    }

    /* ── Status dropdown (identical to patients/appointments pages) ── */
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
        min-width: 145px;
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

    .rec-badge {
        font-family: 'DM Sans', sans-serif;
        font-size: .63rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 9px;
        letter-spacing: .03em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
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

    .tbl-wrap {
        position: relative;
        overflow-x: auto;
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

    /* Modal */
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

    .live-search-wrap {
        position: relative;
    }

    .live-results {
        display: none;
        position: absolute;
        top: calc(100% + 3px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-md);
        z-index: 600;
        max-height: 200px;
        overflow-y: auto;
    }

    .live-results.open {
        display: block;
    }

    .live-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .5rem .85rem;
        cursor: pointer;
        font-size: .82rem;
        border-bottom: 1px solid var(--border);
        transition: background .1s;
    }

    .live-item:last-child {
        border-bottom: none;
    }

    .live-item:hover {
        background: var(--blue-50);
    }

    .live-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--blue-50);
        color: var(--blue-700);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .62rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .live-name {
        font-weight: 600;
        color: var(--text-dark);
    }

    .live-sub {
        font-size: .7rem;
        color: var(--text-muted);
    }

    .selected-pill {
        display: none;
        background: var(--blue-50);
        border: 1px solid var(--blue-200);
        border-radius: var(--radius-sm);
        padding: .45rem .75rem;
        font-size: .82rem;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 6px;
    }

    .selected-pill.locked {
        background: var(--surface);
        border-color: var(--border);
        cursor: not-allowed;
        opacity: .85;
    }

    .selected-pill.locked::after {
        content: '';
    }

    .finalized-notice {
        display: none;
        align-items: center;
        gap: 10px;
        background: var(--amber-light);
        border: 1px solid #fde68a;
        border-radius: var(--radius-sm);
        padding: .65rem 1rem;
        font-size: .8rem;
        font-weight: 600;
        color: var(--amber-dark);
        margin-bottom: .75rem;
    }

    .finalized-notice.show {
        display: flex;
    }

    .finalized-notice i {
        font-size: .95rem;
        flex-shrink: 0;
    }

    .selected-pill.show {
        display: flex;
    }

    .pill-clear {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: .85rem;
        padding: 0 2px;
        line-height: 1;
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
    <h1>Medical Records</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Medical Records</li>
        </ol>
    </nav>
</div>

<section class="section page-records">

    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Records</div>
            <div class="sc-num" id="statTotal">—</div>
            <div class="sc-sub">All time</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Updated Today</div>
            <div class="sc-num" id="statToday">—</div>
            <div class="sc-sub">Records modified</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Lab Results</div>
            <div class="sc-num" id="statLab">—</div>
            <div class="sc-sub">Pending review</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Prescriptions</div>
            <div class="sc-num" id="statRx">—</div>
            <div class="sc-sub">Active this month</div>
        </div>
    </div>

    <div class="main-card">
        <div class="table-toolbar">
            <h5>All Records <span>| <?php echo date('F j, Y'); ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="recSearch" placeholder="Search patient or diagnosis…" oninput="debounceSearch()">
            </div>
            <select class="filter-select" id="recType" onchange="loadRecords(1)">
                <option value="">All Types</option>
                <option>Consultation</option>
                <option>Lab Result</option>
                <option>Imaging</option>
                <option>Prescription</option>
                <option>Other</option>
            </select>
            <select class="filter-select" id="recStatus" onchange="loadRecords(1)">
                <option value="">All Status</option>
                <option>Draft</option>
                <option>Finalized</option>
            </select>
            <button class="btn-primary-sm" onclick="openAddModal()">
                <i class="bi bi-plus-lg"></i>New Record
            </button>
        </div>

        <div class="tbl-wrap">
            <div class="tbl-loading" id="tblLoading" style="display:flex;">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Type</th>
                        <th>Diagnosis</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="recTbody"></tbody>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between mt-3" style="flex-wrap:wrap;gap:8px;">
            <span style="font-size:.75rem;color:var(--text-muted);" id="paginationInfo"></span>
            <div style="display:flex;gap:5px;" id="paginationBtns"></div>
        </div>
    </div>

</section>

<div class="modal fade" id="recModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recModalTitle">New Medical Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="finalized-notice" id="finalizedNotice">
                    <i class="bi bi-lock-fill"></i>
                    <span>Record is Finalized. Patient, Doctor, Linked Appointment, Type, Diagnosis, ICD Code, and Prescription are locked.</span>
                </div>
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Patient</label>
                        <div class="live-search-wrap">
                            <div style="position:relative;">
                                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
                                <input type="text" id="patientInput" class="form-control"
                                    placeholder="Type patient name or code…"
                                    style="padding-left:2rem;"
                                    oninput="debounceField('patient')" autocomplete="off">
                            </div>
                            <div class="live-results" id="patientResults"></div>
                            <div class="selected-pill" id="patientPill">
                                <div>
                                    <div style="font-weight:700;color:var(--text-dark);" id="patientPillName"></div>
                                    <div style="font-size:.7rem;color:var(--text-muted);" id="patientPillSub"></div>
                                </div>
                                <button class="pill-clear" onclick="clearField('patient')"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <input type="hidden" id="fPatient">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Doctor</label>
                        <div class="live-search-wrap">
                            <div style="position:relative;">
                                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
                                <input type="text" id="doctorInput" class="form-control"
                                    placeholder="Type doctor name…"
                                    style="padding-left:2rem;"
                                    oninput="debounceField('doctor')" autocomplete="off">
                            </div>
                            <div class="live-results" id="doctorResults"></div>
                            <div class="selected-pill" id="doctorPill">
                                <div>
                                    <div style="font-weight:700;color:var(--text-dark);" id="doctorPillName"></div>
                                    <div style="font-size:.7rem;color:var(--text-muted);" id="doctorPillSub"></div>
                                </div>
                                <button class="pill-clear" onclick="clearField('doctor')"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <input type="hidden" id="fDoctor">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            Linked Appointment
                            <small style="text-transform:none;letter-spacing:0;font-weight:400;">(optional)</small>
                        </label>
                        <div class="live-search-wrap">
                            <div id="apptInputWrap" style="position:relative;">
                                <i class="bi bi-search" id="apptSearchIcon" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.8rem;pointer-events:none;"></i>
                                <input type="text" id="apptInput" class="form-control"
                                    placeholder="Type appointment code…"
                                    style="padding-left:2rem;"
                                    oninput="debounceField('appt')" autocomplete="off">
                            </div>
                            <div class="live-results" id="apptResults"></div>
                            <div class="selected-pill" id="apptPill">
                                <div>
                                    <div style="font-weight:700;color:var(--text-dark);" id="apptPillName"></div>
                                    <div style="font-size:.7rem;color:var(--text-muted);" id="apptPillSub"></div>
                                </div>
                                <button class="pill-clear" onclick="clearField('appt')"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <input type="hidden" id="fAppointment">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Record Type</label>
                        <select class="form-select" id="fType">
                            <option>Consultation</option>
                            <option>Lab Result</option>
                            <option>Prescription</option>
                            <option>Imaging</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Diagnosis <span class="field-tag req">Required</span></label>
                        <input type="text" class="form-control" id="fDiagnosis" placeholder="e.g. Hypertension Stage 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ICD Code <span class="field-tag opt">Optional</span></label>
                        <input type="text" class="form-control" id="fIcdCode" placeholder="e.g. I10">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Prescription</label>
                        <textarea class="form-control" id="fPrescription" rows="2" placeholder="Medications, dosage…"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="fNotes" rows="2" placeholder="Additional notes…"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Follow-Up Date <span class="field-tag opt">Optional</span></label>
                        <input type="date" class="form-control" id="fFollowUpDate">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="fStatus">
                            <option>Draft</option>
                            <option>Finalized</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-primary-sm" onclick="saveRecord()">
                    <i class="bi bi-check-lg"></i><span id="saveBtnLabel">Save Record</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"></div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Record?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.85rem;color:var(--text-body);margin:0;">
                    This will permanently delete the medical record. Continue?
                </p>
                <input type="hidden" id="deleteId">
            </div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">No</button>
                <button class="btn-primary-sm" style="background:var(--red);" onclick="confirmDelete()">
                    <i class="bi bi-trash3"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const HANDLER = '../../app/controllers/medical_records_handler.php';
    let currentPage = 1;
    let searchTimer = null;

    const STATUS_CONFIG = {
        'Draft': {
            dot: '#f59e0b',
            bg: '#fef3c7',
            color: '#92400e'
        },
        'Finalized': {
            dot: '#10b981',
            bg: '#d1fae5',
            color: '#065f46'
        },
    };

    document.addEventListener('click', e => {
        if (!e.target.closest('.status-cell'))
            document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!e.target.closest('.live-search-wrap'))
            document.querySelectorAll('.live-results.open').forEach(el => el.classList.remove('open'));
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadRecords(1);

        const params = new URLSearchParams(window.location.search);
        const apptId = params.get('apptId');
        const patientId = params.get('patientId');

        if (apptId) {
            openAddModal();

            // Pre-fill appointment
            fetch(`${HANDLER}?action=get_appointment_details&apptId=${apptId}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) return;
                    const appt = res.data;

                    // Set appointment pill
                    document.getElementById('fAppointment').value = appt.id;
                    document.getElementById('apptPillName').textContent = appt.appointmentCode;
                    document.getElementById('apptPillSub').textContent = appt.doctorName + ' · ' + fmtDate(appt.appointmentDate);
                    document.getElementById('apptPill').classList.add('show');

                    // Set doctor pill
                    document.getElementById('fDoctor').value = appt.doctorId;
                    document.getElementById('doctorPillName').textContent = appt.doctorName;
                    document.getElementById('doctorPillSub').textContent = appt.specialization || '';
                    document.getElementById('doctorPill').classList.add('show');

                    // Pre-fill diagnosis from remarks
                    if (appt.remarks) {
                        document.getElementById('fDiagnosis').value = appt.remarks;
                    }
                });

            // Pre-fill patient
            if (patientId) {
                fetch(`${HANDLER}?action=get_patients&q=`)
                    .then(r => r.json())
                    .then(res => {
                        if (!res.success) return;
                        const patient = res.data.find(p => p.id == patientId);
                        if (!patient) return;
                        document.getElementById('fPatient').value = patient.id;
                        document.getElementById('patientPillName').textContent = patient.name;
                        document.getElementById('patientPillSub').textContent = patient.patientCode;
                        document.getElementById('patientPill').classList.add('show');
                    });
            }
        }
    });


    const fieldTimers = {};

    function debounceField(field) {
        clearTimeout(fieldTimers[field]);
        fieldTimers[field] = setTimeout(() => runFieldSearch(field), 280);
    }

    function runFieldSearch(field) {
        const inputId = field === 'patient' ? 'patientInput' : field === 'doctor' ? 'doctorInput' : 'apptInput';
        const resultsId = field === 'patient' ? 'patientResults' : field === 'doctor' ? 'doctorResults' : 'apptResults';
        const q = document.getElementById(inputId).value.trim();
        const box = document.getElementById(resultsId);

        if (!q) {
            box.classList.remove('open');
            box.innerHTML = '';
            return;
        }

        let url = '';
        if (field === 'patient') url = `${HANDLER}?action=get_patients&q=${encodeURIComponent(q)}`;
        if (field === 'doctor') url = `${HANDLER}?action=get_doctors&q=${encodeURIComponent(q)}`;
        if (field === 'appt') {
            const patId = document.getElementById('fPatient').value;
            url = `${HANDLER}?action=get_appointments&q=${encodeURIComponent(q)}&patientId=${patId}`;
        }

        fetch(url)
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data.length) {
                    box.innerHTML = `<div style="padding:.6rem 1rem;font-size:.8rem;color:var(--text-muted);">No results found.</div>`;
                    box.classList.add('open');
                    return;
                }
                box.innerHTML = res.data.map(item => {
                    if (field === 'patient') {
                        return `<div class="live-item" onclick="selectField('patient',${item.id},'${esc(item.name)}','${esc(item.patientCode)}','${esc(item.patientCode)}')">
                            <div class="live-avatar">${inits(item.name)}</div>
                            <div><div class="live-name">${item.name}</div><div class="live-sub">${item.patientCode}</div></div>
                        </div>`;
                    }
                    if (field === 'doctor') {
                        return `<div class="live-item" onclick="selectField('doctor',${item.id},'${esc(item.name)}','${esc(item.specialization)}','')">
                            <div class="live-avatar" style="background:var(--green-light);color:var(--green-dark);">${inits(item.name)}</div>
                            <div><div class="live-name">${item.name}</div><div class="live-sub">${item.specialization}</div></div>
                        </div>`;
                    }
                    // appointment
                    return `<div class="live-item" onclick="selectAppt(${item.id},'${esc(item.appointmentCode)}','${esc(item.doctorName+' · '+fmtDate(item.appointmentDate))}')">
    <div class="live-avatar" style="background:var(--violet-light);color:var(--violet-dark);"><i class="bi bi-calendar2-check" style="font-size:.7rem;"></i></div>
    <div><div class="live-name">${item.appointmentCode}</div><div class="live-sub">${item.doctorName} · ${fmtDate(item.appointmentDate)}</div></div>
</div>`;
                }).join('');
                box.classList.add('open');
            });
    }

    function selectField(field, id, name, sub) {
        const hiddenId = field === 'patient' ? 'fPatient' : field === 'doctor' ? 'fDoctor' : 'fAppointment';
        const inputId = field === 'patient' ? 'patientInput' : field === 'doctor' ? 'doctorInput' : 'apptInput';
        const resultsId = field === 'patient' ? 'patientResults' : field === 'doctor' ? 'doctorResults' : 'apptResults';
        const pillId = field === 'patient' ? 'patientPill' : field === 'doctor' ? 'doctorPill' : 'apptPill';
        const nameId = field === 'patient' ? 'patientPillName' : field === 'doctor' ? 'doctorPillName' : 'apptPillName';
        const subId = field === 'patient' ? 'patientPillSub' : field === 'doctor' ? 'doctorPillSub' : 'apptPillSub';

        document.getElementById(hiddenId).value = id;
        document.getElementById(inputId).value = '';
        document.getElementById(resultsId).classList.remove('open');
        document.getElementById(nameId).textContent = name;
        document.getElementById(subId).textContent = sub;
        document.getElementById(pillId).classList.add('show');

        if (field === 'patient') {
            clearField('appt');
            clearField('doctor');

            fetch(`${HANDLER}?action=get_patient_doctor&patientId=${id}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) return;
                    const doc = res.data;
                    document.getElementById('fDoctor').value = doc.doctorId;
                    document.getElementById('doctorPillName').textContent = doc.doctorName;
                    document.getElementById('doctorPillSub').textContent = doc.specialization || '';
                    document.getElementById('doctorPill').classList.add('show');
                });
        }
    }

    function clearField(field) {
        const hiddenId = field === 'patient' ? 'fPatient' : field === 'doctor' ? 'fDoctor' : 'fAppointment';
        const inputId = field === 'patient' ? 'patientInput' : field === 'doctor' ? 'doctorInput' : 'apptInput';
        const resultsId = field === 'patient' ? 'patientResults' : field === 'doctor' ? 'doctorResults' : 'apptResults';
        const pillId = field === 'patient' ? 'patientPill' : field === 'doctor' ? 'doctorPill' : 'apptPill';

        document.getElementById(hiddenId).value = '';
        document.getElementById(inputId).value = '';
        document.getElementById(resultsId).classList.remove('open');
        document.getElementById(pillId).classList.remove('show');
    }

    function statusDropdown(id, current) {
        const cfg = STATUS_CONFIG[current] || {
            dot: '#9ca3af',
            bg: '#f3f4f6',
            color: '#374151'
        };

        const STATUS_FLOW = {
            'Draft': ['Draft', 'Finalized'],
            'Finalized': ['Finalized'],
        };
        const allowed = STATUS_FLOW[current] || [current];

        const opts = Object.entries(STATUS_CONFIG).map(([label, c]) => {
            const isAllowed = allowed.includes(label);
            const isActive = label === current;
            return `
            <div class="status-opt"
                style="${!isAllowed ? 'opacity:.35;pointer-events:none;cursor:not-allowed;' : ''}"
                onclick="${isAllowed && !isActive ? `pickRecStatus(this,'${label}',${id})` : ''}">
                <span class="dot" style="background:${c.dot};"></span>
                ${label}${isActive ? ' <span style="font-size:.68rem;opacity:.6;">(Current)</span>' : ''}
            </div>`;
        }).join('');

        return `<div class="status-cell">
            <button class="badge-btn" onclick="toggleStatusDrop(this)">
                <span class="rec-badge badge-label" style="background:${cfg.bg};color:${cfg.color};font-family:'DM Sans',sans-serif;font-size:.63rem;font-weight:600;border-radius:6px;padding:3px 9px;letter-spacing:.03em;">
                    ${current}
                </span>
                <span class="badge-caret">▾</span>
            </button>
            <div class="status-dropdown">${opts}</div>
        </div>`;
    }

    function toggleStatusDrop(btn) {
        const dd = btn.nextElementSibling;
        const isOpen = dd.classList.contains('open');
        document.querySelectorAll('.status-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) dd.classList.add('open');
    }

    function pickRecStatus(optEl, newStatus, id) {
        const cfg = STATUS_CONFIG[newStatus];
        const dd = optEl.closest('.status-dropdown');
        const badge = dd.previousElementSibling.querySelector('.rec-badge');

        badge.style.background = cfg.bg;
        badge.style.color = cfg.color;
        badge.textContent = newStatus;
        dd.classList.remove('open');

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
                    renderStats(res.stats);
                    showToast('Status updated to "' + newStatus + '"', 'success');
                } else {
                    loadRecords(currentPage);
                }
            })
            .catch(() => loadRecords(currentPage));
    }

    function selectAppt(id, name, sub) {
        selectField('appt', id, name, sub);

        fetch(`${HANDLER}?action=get_appointment_details&apptId=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success || !res.data) return;
                const appt = res.data;

                // Auto-fill diagnosis from remarks
                if (!document.getElementById('fDiagnosis').value && appt.remarks) {
                    document.getElementById('fDiagnosis').value = appt.remarks;
                }

                // Auto-fill doctor
                if (appt.doctorId) {
                    document.getElementById('fDoctor').value = appt.doctorId;
                    document.getElementById('doctorPillName').textContent = appt.doctorName;
                    document.getElementById('doctorPillSub').textContent = appt.specialization || '';
                    document.getElementById('doctorPill').classList.add('show');
                }

                // Auto-fill patient
                if (appt.patientId) {
                    document.getElementById('fPatient').value = appt.patientId;
                    document.getElementById('patientPillName').textContent = appt.patientName;
                    document.getElementById('patientPillSub').textContent = appt.patientCode || '';
                    document.getElementById('patientPill').classList.add('show');
                }
            });
    }

    function loadRecords(page) {
        currentPage = page || 1;
        const search = encodeURIComponent(document.getElementById('recSearch').value.trim());
        const type = encodeURIComponent(document.getElementById('recType').value);
        const status = encodeURIComponent(document.getElementById('recStatus').value);
        const url = `${HANDLER}?action=list&search=${search}&type=${type}&status=${status}&page=${currentPage}`;

        document.getElementById('tblLoading').style.display = 'flex';

        fetch(url)
            .then(r => r.json())
            .then(res => {
                document.getElementById('tblLoading').style.display = 'none';
                if (!res.success) return;
                renderRows(res.rows);
                renderStats(res.stats);
                renderPagination(res.total, res.page, res.limit);
            })
            .catch(() => document.getElementById('tblLoading').style.display = 'none');
    }

    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadRecords(1), 350);
    }

    const avatarColors = [
        ['#dbeafe', '#1d4ed8'],
        ['#d1fae5', '#065f46'],
        ['#fef3c7', '#92400e'],
        ['#ede9fe', '#5b21b6'],
        ['#cffafe', '#155e75'],
        ['#fee2e2', '#991b1b'],
    ];

    function inits(name) {
        return (name || '').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    }

    function typeChip(t) {
        const map = {
            'Consultation': 'chip-consultation',
            'Lab Result': 'chip-lab',
            'Imaging': 'chip-imaging',
            'Prescription': 'chip-prescription',
            'Other': 'chip-other',
        };
        return `<span class="rec-type-chip ${map[t]||'chip-other'}">${t}</span>`;
    }

    function fmtDate(d) {
        if (!d) return '—';
        return new Date(d + 'T00:00:00').toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function esc(str) {
        return String(str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    function renderRows(rows) {
        const tbody = document.getElementById('recTbody');
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state">
                <i class="bi bi-folder-x"></i><p>No records found for the selected filters.</p>
            </div></td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map((r, i) => {
            const [bg, fg] = avatarColors[i % avatarColors.length];
            const avatarHtml = r.patPhoto ? `<img src="${r.patPhoto}" alt="">` : inits(r.patientName);
            return `<tr>
                <td><a href="#" class="rec-id">${r.recordCode}</a></td>
                <td><div class="pat-cell">
                    <div class="pat-avatar" style="background:${bg};color:${fg}">${avatarHtml}</div>
                    <div>
                        <div class="pat-name">${r.patientName}</div>
                        <div class="pat-sub">${r.patientCode}</div>
                    </div>
                </div></td>
                <td>${typeChip(r.recordType)}</td>
                <td class="diagnosis-cell">
                    <div class="diag-text">${r.diagnosis||'—'}</div>
                    ${r.icdCode ? `<div class="diag-icd">ICD: ${r.icdCode}</div>` : ''}
                </td>
                <td>${r.doctorName}</td>
                <td>${fmtDate(r.createdAt?.slice(0,10))}</td>
                <td>${statusDropdown(r.id, r.status)}</td>
                <td><div class="action-btns">
                    <button class="btn-act" title="Edit" onclick="editRecord(${r.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn-act" title="View" onclick="viewRecord(${r.id})"><i class="bi bi-eye"></i></button>
                </div></td>
            </tr>`;
        }).join('');
    }

    function renderStats(s) {
        document.getElementById('statTotal').textContent = s.total ?? '—';
        document.getElementById('statToday').textContent = s.today ?? '—';
        document.getElementById('statLab').textContent = s.labPending ?? '—';
        document.getElementById('statRx').textContent = s.prescriptions ?? '—';
    }

    function renderPagination(total, page, limit) {
        const pages = Math.ceil(total / limit);
        const start = (page - 1) * limit + 1,
            end = Math.min(page * limit, total);
        document.getElementById('paginationInfo').textContent =
            total ? `Showing ${start}–${end} of ${total} records` : 'No records';
        const wrap = document.getElementById('paginationBtns');
        wrap.innerHTML = '';
        if (pages <= 1) return;
        const btn = (label, p, active = false) => {
            const b = document.createElement('button');
            b.className = 'btn-act';
            b.innerHTML = label;
            b.style.cssText = `border-radius:8px;padding:4px 12px;font-size:.78rem;${active?'background:var(--blue-600);color:#fff;border-color:var(--blue-600);':''}`;
            if (p) b.onclick = () => loadRecords(p);
            else b.disabled = true;
            return b;
        };
        wrap.appendChild(btn('‹ Prev', page > 1 ? page - 1 : null));
        for (let i = 1; i <= pages; i++) wrap.appendChild(btn(i, i, i === page));
        wrap.appendChild(btn('Next ›', page < pages ? page + 1 : null));
    }

    function lockAppt(lock) {
        const wrap = document.getElementById('apptInputWrap');
        const pill = document.getElementById('apptPill');
        const clearBtn = document.querySelector('#apptPill .pill-clear');
        const input = document.getElementById('apptInput');

        input.disabled = lock;
        wrap.style.display = lock ? 'none' : '';
        if (clearBtn) clearBtn.style.display = lock ? 'none' : '';

        if (lock) {
            // Show pill without blue styling — plain locked look
            pill.classList.remove('locked');
            pill.style.background = 'var(--surface)';
            pill.style.border = '1px solid var(--border)';
            pill.style.cursor = 'not-allowed';
        } else {
            // Restore normal pill styling
            pill.style.background = '';
            pill.style.border = '';
            pill.style.cursor = '';
            pill.classList.remove('locked');
        }
    }

    function showFinalizedNotice(show) {
        document.getElementById('finalizedNotice').classList.toggle('show', show);
    }

    function lockPatientDoctor(lock) {
        ['patient', 'doctor'].forEach(field => {
            const inputId = field === 'patient' ? 'patientInput' : 'doctorInput';
            const pillId = field === 'patient' ? 'patientPill' : 'doctorPill';
            const nameId = field === 'patient' ? 'patientPillName' : 'doctorPillName';
            const subId = field === 'patient' ? 'patientPillSub' : 'doctorPillSub';
            const clearBtn = document.querySelector(`#${pillId} .pill-clear`);
            const inputWrap = document.getElementById(inputId).closest('div');

            if (lock) {
                // Hide search input wrapper and pill, show plain text box
                inputWrap.style.display = 'none';
                document.getElementById(pillId).classList.remove('show');

                const name = document.getElementById(nameId).textContent;
                const sub = document.getElementById(subId).textContent;
                const existingBox = document.getElementById(field + 'LockedBox');
                if (!existingBox) {
                    const box = document.createElement('input');
                    box.type = 'text';
                    box.id = field + 'LockedBox';
                    box.className = 'form-control';
                    box.value = name + (sub ? ' (' + sub + ')' : '');
                    box.disabled = true;
                    box.style.cssText = 'background:var(--surface);color:var(--text-dark);font-weight:600;cursor:not-allowed;';
                    inputWrap.parentNode.appendChild(box);
                } else {
                    existingBox.value = name + (sub ? ' (' + sub + ')' : '');
                    existingBox.style.display = '';
                }
            } else {
                // Restore search input, hide locked box
                inputWrap.style.display = '';
                const existingBox = document.getElementById(field + 'LockedBox');
                if (existingBox) existingBox.style.display = 'none';
                if (clearBtn) clearBtn.style.display = '';
                document.getElementById(pillId).classList.remove('show', 'locked');
            }
        });
    }

    function resetModal() {
        ['patient', 'doctor', 'appt'].forEach(f => clearField(f));
        document.getElementById('fType').value = 'Consultation';
        document.getElementById('fDiagnosis').value = '';
        document.getElementById('fIcdCode').value = '';
        document.getElementById('fPrescription').value = '';
        document.getElementById('fNotes').value = '';
        document.getElementById('fStatus').value = 'Draft';
        document.getElementById('editId').value = '';
        document.getElementById('fFollowUpDate').value = '';
    }

    function openAddModal() {
        resetModal();
        lockPatientDoctor(false);
        lockAppt(false);
        showFinalizedNotice(false);
        // also unlock any finalized-locked fields
        ['fDiagnosis', 'fIcdCode', 'fPrescription', 'fType', 'fStatus'].forEach(fid => {
            const el = document.getElementById(fid);
            el.disabled = false;
            el.style.opacity = '';
            el.style.cursor = '';
            el.style.background = '';
        });
        document.getElementById('recModalTitle').textContent = 'New Medical Record';
        document.getElementById('saveBtnLabel').textContent = 'Save Record';
        new bootstrap.Modal(document.getElementById('recModal')).show();
    }

    function editRecord(id) {
        fetch(`${HANDLER}?action=get&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return alert('Could not load record.');
                const d = res.data;
                resetModal();
                document.getElementById('recModalTitle').textContent = 'Edit Medical Record';
                document.getElementById('saveBtnLabel').textContent = 'Update Record';
                document.getElementById('editId').value = d.id;
                document.getElementById('fStatus').value = d.status || 'Draft';
                document.getElementById('fStatus').dataset.original = d.status || 'Draft';
                document.getElementById('fType').value = d.recordType;
                document.getElementById('fDiagnosis').value = d.diagnosis || '';
                document.getElementById('fIcdCode').value = d.icdCode || '';
                document.getElementById('fPrescription').value = d.prescription || '';
                document.getElementById('fNotes').value = d.notes || '';
                document.getElementById('fFollowUpDate').value = d.followUpDate || '';

                if (d.patientId) {
                    document.getElementById('fPatient').value = d.patientId;
                    document.getElementById('patientPillName').textContent = d.patientName || '—';
                    document.getElementById('patientPillSub').textContent = d.patientCode || '';
                    document.getElementById('patientPill').classList.add('show');
                }

                if (d.doctorId) {
                    document.getElementById('fDoctor').value = d.doctorId;
                    document.getElementById('doctorPillName').textContent = d.doctorName || '—';
                    document.getElementById('doctorPillSub').textContent = d.specialization || '';
                    document.getElementById('doctorPill').classList.add('show');
                }

                if (d.appointmentId) {
                    document.getElementById('fAppointment').value = d.appointmentId;
                    document.getElementById('apptPillName').textContent = d.appointmentCode || '—';
                    document.getElementById('apptPillSub').textContent = d.doctorName + ' · ' + fmtDate(d.appointmentDate);
                    document.getElementById('apptPill').classList.add('show');
                }

                const isFinalized = d.status === 'Finalized';

                lockPatientDoctor(true);
                lockAppt(true); // always lock Linked Appointment on edit, regardless of status
                showFinalizedNotice(isFinalized);
                // Lock clinical fields when Finalized
                ['fDiagnosis', 'fIcdCode', 'fPrescription', 'fType'].forEach(fid => {
                    const el = document.getElementById(fid);
                    el.disabled = isFinalized;
                    el.style.opacity = isFinalized ? '.7' : '';
                    el.style.cursor = isFinalized ? 'not-allowed' : '';
                    el.style.background = isFinalized ? 'var(--surface)' : '';
                });

                // Lock Status (prevent Finalized → Draft regression)
                const fStatus = document.getElementById('fStatus');
                fStatus.disabled = isFinalized;
                fStatus.style.opacity = isFinalized ? '.7' : '';
                fStatus.style.cursor = isFinalized ? 'not-allowed' : '';
                fStatus.style.background = isFinalized ? 'var(--surface)' : '';

                // Notes & Follow-up Date remain editable in both states

                new bootstrap.Modal(document.getElementById('recModal')).show();
            });
    }

    function saveRecord() {
        const id = document.getElementById('editId').value;
        const diagnosis = document.getElementById('fDiagnosis').value.trim();
        const icdCode = document.getElementById('fIcdCode').value.trim();
        const followUp = document.getElementById('fFollowUpDate').value;
        const status = document.getElementById('fStatus').value;

        // ── Validation 1: Diagnosis required ────────────────────────────────────
        if (!diagnosis) {
            showFieldError('fDiagnosis', 'Diagnosis is required before saving.');
            return;
        }

        // ── Validation 2: ICD Code format check when provided ───────────────────
        if (icdCode) {
            // ICD-10 format: letter + 2 digits + optional decimal + more digits
            const icdPattern = /^[A-Z][0-9]{1,3}(\.[0-9A-Z]{0,4})?$/i;
            if (!icdPattern.test(icdCode)) {
                showFieldError('fIcdCode', 'ICD code format looks wrong (e.g. I10, J18.9). Double-check it matches your diagnosis.');
                return;
            }
        }

        // ── Validation 3: Follow-up date must be in the future ──────────────────
        if (followUp) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const followDt = new Date(followUp + 'T00:00:00');
            if (followDt <= today) {
                showFieldError('fFollowUpDate', 'Follow-up date must be a future date.');
                return;
            }
        }

        // ── Finalize confirmation ────────────────────────────────────────────────
        if (status === 'Finalized' && !id) {
            if (!confirm('Finalizing will lock this record and prevent editing of clinical fields. Continue?')) return;
        }
        if (status === 'Finalized' && id) {
            const origStatus = document.getElementById('fStatus').dataset.original;
            if (origStatus !== 'Finalized') {
                if (!confirm('Finalizing will lock this record permanently. Are you sure?')) return;
            }
        }

        const payload = {
            id: id || undefined,
            patientId: document.getElementById('fPatient').value,
            doctorId: document.getElementById('fDoctor').value,
            appointmentId: document.getElementById('fAppointment').value || null,
            recordType: document.getElementById('fType').value,
            diagnosis,
            icdCode,
            prescription: document.getElementById('fPrescription').value,
            notes: document.getElementById('fNotes').value,
            status,
            followUpDate: followUp || null,
        };

        if (!payload.patientId || !payload.doctorId) {
            alert('Patient and Doctor are required.');
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
                    bootstrap.Modal.getInstance(document.getElementById('recModal')).hide();
                    loadRecords(currentPage);
                    showToast(id ? 'Record updated!' : 'Record saved!', 'success');
                } else {
                    alert('Failed to save. Please try again.');
                }
            });
    }

    function showFieldError(fieldId, msg) {
        const el = document.getElementById(fieldId);
        el.style.borderColor = 'var(--red)';
        el.style.background = 'var(--red-light)';
        el.focus();

        // Remove existing error tip if any
        const prev = el.parentNode.querySelector('.field-err');
        if (prev) prev.remove();

        const tip = document.createElement('div');
        tip.className = 'field-err';
        tip.style.cssText = 'font-size:.72rem;color:var(--red-dark);margin-top:4px;display:flex;align-items:center;gap:5px;';
        tip.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${msg}`;
        el.parentNode.appendChild(tip);

        // Auto-clear on next input
        el.addEventListener('input', function clear() {
            el.style.borderColor = '';
            el.style.background = '';
            tip.remove();
            el.removeEventListener('input', clear);
        });
    }

    function viewRecord(id, print = false) {
        fetch(`${HANDLER}?action=get&id=${id}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return alert('Could not load record.');
                const d = res.data;
                const cfg = STATUS_CONFIG[d.status] || {
                    bg: '#f3f4f6',
                    color: '#374151',
                    dot: '#9ca3af'
                };
                document.getElementById('viewModalBody').innerHTML = `
                    <div class="detail-row"><span class="detail-label">Code</span>          <span class="detail-value">${d.recordCode}</span></div>
                    <div class="detail-row"><span class="detail-label">Patient</span>        <span class="detail-value">${d.patientName} (${d.patientCode})</span></div>
                    <div class="detail-row"><span class="detail-label">Doctor</span>         <span class="detail-value">${d.doctorName}</span></div>
                    <div class="detail-row"><span class="detail-label">Specialization</span> <span class="detail-value">${d.specialization||'—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Type</span>           <span class="detail-value">${typeChip(d.recordType)}</span></div>
                    <div class="detail-row"><span class="detail-label">Diagnosis</span>      <span class="detail-value">${d.diagnosis||'—'}</span></div>
                    <div class="detail-row"><span class="detail-label">ICD Code</span>       <span class="detail-value">${d.icdCode||'—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Prescription</span>   <span class="detail-value" style="white-space:pre-line">${d.prescription||'—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Notes</span>          <span class="detail-value" style="white-space:pre-line">${d.notes||'—'}</span></div>
                    <div class="detail-row"><span class="detail-label">Status</span>
    <span class="detail-value">
        <span style="background:${cfg.bg};color:${cfg.color};font-size:.63rem;font-weight:600;border-radius:6px;padding:3px 9px;font-family:'DM Sans',sans-serif;display:inline-flex;align-items:center;gap:5px;">
            <span style="width:7px;height:7px;border-radius:50%;background:${cfg.dot};display:inline-block;"></span>${d.status}
        </span>
    </span>
</div>
<div class="detail-row"><span class="detail-label">Follow-Up Date</span><span class="detail-value">${d.followUpDate ? fmtDate(d.followUpDate) : '—'}</span></div>
<div class="detail-row"><span class="detail-label">Created</span><span class="detail-value">${fmtDate(d.createdAt?.slice(0,10))}</span></div>
${renderAuditTrail(d.auditLog || [])}
                `;
                const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                modal.show();
                if (print) {
                    document.getElementById('viewModal').addEventListener('shown.bs.modal', () => window.print(), {
                        once: true
                    });
                }
            });
    }

    function printRecord() {
        window.print();
    }

    function openDelete(id) {
        document.getElementById('deleteId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function confirmDelete() {
        const id = document.getElementById('deleteId').value;
        const fd = new FormData();
        fd.append('id', id);
        fetch(`${HANDLER}?action=delete`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    loadRecords(currentPage);
                    showToast('Record deleted.', 'success');
                } else {
                    alert('Failed to delete. Please try again.');
                }
            });
    }

    function showToast(msg, type = 'success') {
        const colors = {
            success: '#065f46',
            error: '#991b1b',
            info: '#155e75'
        };
        const el = document.createElement('div');
        el.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:99999;background:${colors[type]||colors.success};color:#fff;border-radius:10px;padding:.65rem 1.1rem;font-size:.82rem;font-family:'DM Sans',sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.1);display:flex;align-items:center;gap:8px;animation:fadeUp .25s ease both;`;
        el.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${msg}`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    function renderAuditTrail(log) {
        if (!log.length) return '';
        const typeClass = {
            'create': 'create',
            'edit': 'edit',
            'status': 'status'
        };
        const items = log.map(entry => {
            let detail = '';
            if (entry.type === 'status') {
                detail = `<span class="audit-arrow">
                <span class="from">${entry.from}</span>
                <i class="bi bi-arrow-right" style="font-size:.65rem;color:var(--text-muted);"></i>
                <span class="to">${entry.to}</span>
            </span>`;
            }
            return `<div class="audit-item ${typeClass[entry.type] || 'edit'}">
            <div>
                <div class="audit-action">${entry.action} ${detail}</div>
                <div class="audit-who">${entry.by} &middot; ${entry.at}</div>
            </div>
        </div>`;
        }).join('');
        return `<div class="audit-trail">
        <div class="audit-trail-title"><i class="bi bi-clock-history"></i> Change History (${log.length})</div>
        <div class="audit-items-wrap">${items}</div>
    </div>`;
    }
</script>

<?php include('./includes/footer.php'); ?>