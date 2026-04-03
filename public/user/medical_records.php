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
        --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        --shadow-md: 0 4px 16px rgba(0,0,0,.07);
        --shadow-lg: 0 8px 30px rgba(0,0,0,.10);
    }

    .page-rec, .page-rec * {
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
    .stat-card:nth-child(3) { border-left-color: var(--teal);      animation-delay: .14s; }
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

    .rec-id {
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

    .doc-name { font-weight: 600; color: var(--text-dark); font-size: .82rem; }
    .doc-spec  { font-size: .66rem; color: var(--text-muted); }

    /* ── DIAGNOSIS PILL ── */
    .diag-pill {
        display: inline-block;
        background: var(--teal-light);
        color: var(--teal-dark);
        border-radius: 6px;
        font-size: .63rem;
        font-weight: 600;
        padding: 2px 8px;
        letter-spacing: .03em;
    }

    /* ── ACTION BUTTONS ── */
    .action-btns { display: flex; gap: 5px; flex-wrap: wrap; }

    .btn-act {
        background: none;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 4px 9px;
        font-size: .75rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: all .15s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-act:hover { background: var(--blue-50); color: var(--blue-600); border-color: var(--blue-200); }
    .btn-act.dl:hover { background: var(--green-light); color: var(--green-dark); border-color: #6ee7b7; }

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

    .detail-value { font-size: .875rem; font-weight: 600; color: var(--text-dark); }
    .detail-text  { font-size: .85rem; color: var(--text-body); line-height: 1.6; }

    /* Prescription box */
    .rx-box {
        background: var(--blue-50);
        border: 1px solid var(--blue-100);
        border-radius: var(--radius-sm);
        padding: .85rem 1rem;
        font-size: .83rem;
        color: var(--text-body);
        line-height: 1.7;
    }

    .rx-box strong { color: var(--blue-700); }

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

    .btn-dl-sm {
        background: var(--green);
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

    .btn-dl-sm:hover { background: var(--green-dark); }

    /* Download toast */
    .dl-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        background: var(--green-light);
        border: 1px solid #6ee7b7;
        color: var(--green-dark);
        border-radius: var(--radius-sm);
        padding: .75rem 1.25rem;
        font-size: .875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: var(--shadow-md);
        z-index: 9999;
        animation: fadeUp .2s ease;
    }

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
    <h1>Medical Records</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Medical Records</li>
        </ol>
    </nav>
</div>

<section class="section page-rec">

    <!-- STAT STRIP -->
    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Records</div>
            <div class="sc-num">6</div>
            <div class="sc-sub">All medical records</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">This Month</div>
            <div class="sc-num">2</div>
            <div class="sc-sub"><?php echo date('F Y'); ?></div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Doctors Seen</div>
            <div class="sc-num">4</div>
            <div class="sc-sub">Unique physicians</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Departments</div>
            <div class="sc-num">4</div>
            <div class="sc-sub">Specializations</div>
        </div>
    </div>

    <!-- MAIN TABLE CARD -->
    <div class="main-card">
        <div class="table-toolbar">
            <h5>All Medical Records <span>| <?php echo date('F j, Y'); ?></span></h5>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="recSearch" placeholder="Search diagnosis or doctor…" oninput="filterRecords()"/>
            </div>
            <select class="filter-select" id="deptFilter" onchange="filterRecords()">
                <option value="">All Departments</option>
                <option>Dermatology</option>
                <option>Internal Medicine</option>
                <option>Pediatrics</option>
                <option>Orthopedics</option>
                <option>Cardiology</option>
            </select>
        </div>

        <div style="overflow-x:auto;">
            <table class="table" id="recTable">
                <thead>
                    <tr>
                        <th>Record ID</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="recTbody">

                    <tr data-doctor="Dr. Princess Mary Lapura" data-dept="Dermatology" data-diag="Acne Vulgaris">
                        <td><span class="rec-id">REC-001</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#dbeafe;color:#1d4ed8;">PL</div>
                                <div>
                                    <div class="doc-name">Dr. Princess Mary Lapura</div>
                                    <div class="doc-spec">Dermatology</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="diag-pill">Acne Vulgaris</span></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Tretinoin 0.025% cream, Doxycycline 100mg">
                            Tretinoin 0.025% cream, Doxycycline 100mg
                        </td>
                        <td>March 28, 2026</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" onclick="openViewRecord('REC-001','Dr. Princess Mary Lapura','Dermatology','March 28, 2026','Acne Vulgaris','Tretinoin 0.025% cream nightly · Doxycycline 100mg twice daily for 2 weeks · Avoid sun exposure · Use SPF 30+ daily','Skin improved after initial treatment. Schedule follow-up in 4 weeks.')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn-act dl" onclick="fakeDownload('REC-001')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Jose Reyes" data-dept="Internal Medicine" data-diag="Hypertension">
                        <td><span class="rec-id">REC-002</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#d1fae5;color:#065f46;">JR</div>
                                <div>
                                    <div class="doc-name">Dr. Jose Reyes</div>
                                    <div class="doc-spec">Internal Medicine</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="diag-pill" style="background:#fee2e2;color:#991b1b;">Hypertension</span></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Amlodipine 5mg, Losartan 50mg">
                            Amlodipine 5mg, Losartan 50mg
                        </td>
                        <td>March 15, 2026</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" onclick="openViewRecord('REC-002','Dr. Jose Reyes','Internal Medicine','March 15, 2026','Hypertension (Stage 1)','Amlodipine 5mg once daily · Losartan 50mg once daily · Low sodium diet · Daily walking 30 min · Monitor BP twice a week','BP: 145/92 mmHg at visit. Lifestyle modification counseling provided.')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn-act dl" onclick="fakeDownload('REC-002')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Maria Santos" data-dept="Pediatrics" data-diag="Upper Respiratory Tract Infection">
                        <td><span class="rec-id">REC-003</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#fef3c7;color:#92400e;">MS</div>
                                <div>
                                    <div class="doc-name">Dr. Maria Santos</div>
                                    <div class="doc-spec">Pediatrics</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="diag-pill" style="background:#fef3c7;color:#92400e;">URTI</span></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Amoxicillin 250mg, Cetirizine 5mg">
                            Amoxicillin 250mg, Cetirizine 5mg
                        </td>
                        <td>March 10, 2026</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" onclick="openViewRecord('REC-003','Dr. Maria Santos','Pediatrics','March 10, 2026','Upper Respiratory Tract Infection (URTI)','Amoxicillin 250mg 3x daily for 7 days · Cetirizine 5mg once at night · Increase fluid intake · Steam inhalation','Mild fever, runny nose, and sore throat. No bacterial culture needed at this stage.')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn-act dl" onclick="fakeDownload('REC-003')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Ramon Cruz" data-dept="Orthopedics" data-diag="Patellofemoral Pain Syndrome">
                        <td><span class="rec-id">REC-004</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#fee2e2;color:#991b1b;">RC</div>
                                <div>
                                    <div class="doc-name">Dr. Ramon Cruz</div>
                                    <div class="doc-spec">Orthopedics</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="diag-pill" style="background:#ede9fe;color:#5b21b6;">PFPS</span></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Ibuprofen 400mg, Physical Therapy">
                            Ibuprofen 400mg, Physical Therapy
                        </td>
                        <td>Feb 22, 2026</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" onclick="openViewRecord('REC-004','Dr. Ramon Cruz','Orthopedics','Feb 22, 2026','Patellofemoral Pain Syndrome (Left Knee)','Ibuprofen 400mg 3x daily after meals for 5 days · Physical therapy 3x per week · Knee compression sleeve · Avoid stairs and squatting initially','X-ray showed no structural damage. MRI recommended if pain persists after 4 weeks of PT.')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn-act dl" onclick="fakeDownload('REC-004')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Angela Villanueva" data-dept="Cardiology" data-diag="Sinus Bradycardia">
                        <td><span class="rec-id">REC-005</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#ede9fe;color:#5b21b6;">AV</div>
                                <div>
                                    <div class="doc-name">Dr. Angela Villanueva</div>
                                    <div class="doc-spec">Cardiology</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="diag-pill" style="background:#cffafe;color:#155e75;">Sinus Bradycardia</span></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Observation, Lifestyle changes">
                            Observation, Lifestyle changes
                        </td>
                        <td>Feb 10, 2026</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" onclick="openViewRecord('REC-005','Dr. Angela Villanueva','Cardiology','Feb 10, 2026','Sinus Bradycardia (Mild)','No pharmacologic intervention at this stage · Increase aerobic activity gradually · Limit caffeine intake · Monitor heart rate daily · Return if HR < 45 bpm or syncope occurs','HR: 52 bpm on ECG. Asymptomatic. Likely exercise-induced. 24-hour Holter monitoring scheduled.')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn-act dl" onclick="fakeDownload('REC-005')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr data-doctor="Dr. Princess Mary Lapura" data-dept="Dermatology" data-diag="Contact Dermatitis">
                        <td><span class="rec-id">REC-006</span></td>
                        <td>
                            <div class="doc-cell">
                                <div class="doc-avatar" style="background:#dbeafe;color:#1d4ed8;">PL</div>
                                <div>
                                    <div class="doc-name">Dr. Princess Mary Lapura</div>
                                    <div class="doc-spec">Dermatology</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="diag-pill">Contact Dermatitis</span></td>
                        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="Hydrocortisone cream, Loratadine 10mg">
                            Hydrocortisone cream, Loratadine 10mg
                        </td>
                        <td>Jan 18, 2026</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-act" onclick="openViewRecord('REC-006','Dr. Princess Mary Lapura','Dermatology','Jan 18, 2026','Allergic Contact Dermatitis','Hydrocortisone 1% cream apply to affected area twice daily · Loratadine 10mg once daily · Avoid known allergen (nickel jewelry) · Moisturize with fragrance-free lotion','Patch test positive for nickel. Rash localized to neck and wrists. Expected resolution in 2–3 weeks.')">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button class="btn-act dl" onclick="fakeDownload('REC-006')">
                                    <i class="bi bi-download"></i> Download
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>

            <div class="empty-state" id="emptyState" style="display:none;">
                <i class="bi bi-file-medical"></i>
                <p>No records found matching your search.</p>
            </div>
        </div>

        <div class="tbl-footer">
            <span id="showingLabel">Showing 6 of 6 records</span>
            <div class="pg-btns">
                <button>‹ Prev</button>
                <button class="active">1</button>
                <button>Next ›</button>
            </div>
        </div>
    </div>

</section>

<!-- VIEW RECORD MODAL -->
<div class="modal fade" id="viewRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-medical me-2" style="color:var(--teal);"></i>
                    Medical Record Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Record ID</div>
                            <div class="detail-value" id="mrec-id">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Date</div>
                            <div class="detail-value" id="mrec-date">—</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-group">
                            <div class="detail-label">Department</div>
                            <div class="detail-value" id="mrec-dept">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Attending Physician</div>
                            <div class="detail-value" id="mrec-doctor">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Diagnosis</div>
                            <div class="detail-value" id="mrec-diag" style="color:var(--teal-dark);"></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Prescription / Treatment Plan</div>
                            <div class="rx-box" id="mrec-rx">—</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-group">
                            <div class="detail-label">Physician's Notes</div>
                            <div class="detail-text" id="mrec-notes">—</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-close" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i> Close
                </button>
                <button class="btn-dl-sm" id="modalDlBtn">
                    <i class="bi bi-download"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DOWNLOAD TOAST -->
<div id="dlToast" class="dl-toast" style="display:none;">
    <i class="bi bi-check-circle-fill"></i>
    <span id="dlToastMsg">Record downloaded successfully!</span>
</div>

<script>
    function filterRecords() {
        const q    = document.getElementById('recSearch').value.toLowerCase();
        const dept = document.getElementById('deptFilter').value;
        let visible = 0;

        document.querySelectorAll('#recTbody tr').forEach(row => {
            const doc  = (row.dataset.doctor || '').toLowerCase();
            const diag = (row.dataset.diag   || '').toLowerCase();
            const d    = row.dataset.dept    || '';
            const matchQ = !q    || doc.includes(q) || diag.includes(q);
            const matchD = !dept || d === dept;
            const show   = matchQ && matchD;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('emptyState').style.display = visible === 0 ? '' : 'none';
        const total = document.querySelectorAll('#recTbody tr').length;
        document.getElementById('showingLabel').textContent = `Showing ${visible} of ${total} records`;
    }

    function openViewRecord(id, doctor, dept, date, diag, rx, notes) {
        document.getElementById('mrec-id').textContent     = id;
        document.getElementById('mrec-doctor').textContent = doctor;
        document.getElementById('mrec-dept').textContent   = dept;
        document.getElementById('mrec-date').textContent   = date;
        document.getElementById('mrec-diag').textContent   = diag;
        document.getElementById('mrec-notes').textContent  = notes;

        const lines = rx.split('·').map(l => l.trim()).filter(Boolean);
        document.getElementById('mrec-rx').innerHTML =
            lines.map(l => `<div style="padding:3px 0;">• ${l}</div>`).join('');

        document.getElementById('modalDlBtn').onclick = () => fakeDownload(id);

        new bootstrap.Modal(document.getElementById('viewRecordModal')).show();
    }

    let toastTimer;
    function fakeDownload(id) {
        document.getElementById('dlToastMsg').textContent = `${id} downloaded successfully!`;
        const toast = document.getElementById('dlToast');
        toast.style.display = 'flex';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }
</script>

<?php include('./includes/footer.php'); ?>
