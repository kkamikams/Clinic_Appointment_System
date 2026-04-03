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

    .page-records,
    .page-records * {
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

    /* Layout */
    .records-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 16px;
        align-items: start;
    }

    @media(max-width:992px) {
        .records-layout {
            grid-template-columns: 1fr;
        }
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

    .chip-surgery {
        background: var(--red-light);
        color: var(--red-dark);
        border: 1px solid #fca5a5;
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

    /* Sidebar widgets */
    .widget-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.25rem;
        margin-bottom: 16px;
        animation: fadeUp .32s .28s ease both;
    }

    .widget-card:last-child {
        margin-bottom: 0;
    }

    .widget-title {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--text-muted);
        margin-bottom: .9rem;
    }

    /* Recent updates list */
    .update-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: .55rem 0;
        border-bottom: 1px solid var(--border);
    }

    .update-item:last-child {
        border-bottom: none;
    }

    .update-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }

    .update-body {
        flex: 1;
    }

    .update-text {
        font-size: .79rem;
        color: var(--text-body);
        line-height: 1.45;
    }

    .update-text strong {
        color: var(--text-dark);
    }

    .update-time {
        font-size: .65rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Record type breakdown */
    .type-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .4rem 0;
        border-bottom: 1px solid var(--border);
        font-size: .78rem;
    }

    .type-row:last-child {
        border-bottom: none;
    }

    .type-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-body);
    }

    .type-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .type-count {
        font-weight: 700;
        color: var(--text-dark);
    }

    .dropdown-menu {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        box-shadow: var(--shadow-lg);
        font-size: .82rem;
    }

    .dropdown-item {
        color: var(--text-body);
        font-size: .8rem;
        padding: .4rem 1rem;
    }

    .dropdown-item:hover {
        background: var(--blue-50);
        color: var(--blue-700);
    }

    .dropdown-header h6 {
        font-size: .67rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: var(--text-muted);
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
    <h1>Medical Records</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Medical Records</li>
        </ol>
    </nav>
</div>

<section class="section page-records">

    <!-- Stat strip -->
    <div class="stat-strip">
        <div class="stat-card">
            <div class="sc-label">Total Records</div>
            <div class="sc-num">4,821</div>
            <div class="sc-sub">All time</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Updated Today</div>
            <div class="sc-num">37</div>
            <div class="sc-sub">Records modified</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Lab Results</div>
            <div class="sc-num">128</div>
            <div class="sc-sub">Pending review</div>
        </div>
        <div class="stat-card">
            <div class="sc-label">Prescriptions</div>
            <div class="sc-num">94</div>
            <div class="sc-sub">Active this month</div>
        </div>
    </div>

    <div class="records-layout">

        <!-- Left — main table -->
        <div class="main-card">
            <div class="table-toolbar">
                <h5>All Records <span>| <?php echo date('F j, Y'); ?></span></h5>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="recSearch" placeholder="Search patient or diagnosis…" oninput="filterRecords()">
                </div>
                <select class="filter-select" id="recType" onchange="filterRecords()">
                    <option value="">All Types</option>
                    <option>Consultation</option>
                    <option>Lab Result</option>
                    <option>Imaging</option>
                    <option>Prescription</option>
                    <option>Surgery</option>
                </select>
                <button class="btn-primary-sm" onclick="openAddModal()">
                    <i class="bi bi-plus-lg"></i>New Record
                </button>
            </div>

            <div style="overflow-x:auto;">
                <table class="table" id="recTable">
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
                    <tbody id="recTbody">
                        <tr data-patient="Maria Santos" data-type="Consultation">
                            <td><a href="#" class="rec-id">#R-3041</a></td>
                            <td>
                                <div class="pat-cell">
                                    <div class="pat-avatar" style="background:#dbeafe;color:#1d4ed8">MS</div>
                                    <div>
                                        <div class="pat-name">Maria Santos</div>
                                        <div class="pat-sub">#P-1001</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="rec-type-chip chip-consultation">Consultation</span></td>
                            <td class="diagnosis-cell">
                                <div class="diag-text">Hypertension Stage 1</div>
                                <div class="diag-icd">ICD: I10</div>
                            </td>
                            <td>Dr. Reyes</td>
                            <td><?php echo date('M j, Y'); ?></td>
                            <td><span class="badge bg-success">Finalized</span></td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-act" title="View"><i class="bi bi-eye"></i></button>
                                    <button class="btn-act" title="Print"><i class="bi bi-printer"></i></button>
                                    <button class="btn-act" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button class="btn-act del" title="Delete"><i class="bi bi-trash3"></i></button>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-3" style="flex-wrap:wrap;gap:8px;">
                <span style="font-size:.75rem;color:var(--text-muted);">Showing 7 of 4,821 records</span>
                <div style="display:flex;gap:5px;">
                    <button class="btn-act" style="border-radius:8px;padding:4px 12px;font-size:.78rem;">‹ Prev</button>
                    <button class="btn-act" style="background:var(--blue-600);color:#fff;border-color:var(--blue-600);border-radius:8px;padding:4px 12px;font-size:.78rem;">1</button>
                    <button class="btn-act" style="border-radius:8px;padding:4px 12px;font-size:.78rem;">2</button>
                    <button class="btn-act" style="border-radius:8px;padding:4px 12px;font-size:.78rem;">3</button>
                    <button class="btn-act" style="border-radius:8px;padding:4px 12px;font-size:.78rem;">Next ›</button>
                </div>
            </div>
        </div>

    </div>
    </div>

</section>

<script>
    function filterRecords() {
        const q = document.getElementById('recSearch').value.toLowerCase();
        const type = document.getElementById('recType').value;
        document.querySelectorAll('#recTbody tr').forEach(row => {
            const pat = row.dataset.patient?.toLowerCase() || '';
            const rt = row.dataset.type || '';
            const matchQ = !q || pat.includes(q);
            const matchT = !type || rt === type;
            row.style.display = matchQ && matchT ? '' : 'none';
        });
    }

    function openAddModal() {
        alert('New Record modal — wire up your Bootstrap modal here.');
    }
</script>

<?php include('./includes/footer.php'); ?>