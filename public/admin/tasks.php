<?php
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
        --violet: #8b5cf6;
        --violet-light: #ede9fe;
        --violet-dark: #5b21b6;
        --teal: #06b6d4;
        --teal-light: #cffafe;
        --teal-dark: #155e75;
        --radius: 16px;
        --radius-sm: 10px;
        --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
    }

    .page-tasks,
    .page-tasks * {
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

    /* ── Stat summary strip ── */
    .task-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .task-summary {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .ts-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.1rem 1.25rem;
        border-left: 3px solid transparent;
        transition: box-shadow .2s, transform .2s;
        animation: fadeUp .3s ease both;
    }

    .ts-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .ts-card.all {
        border-left-color: var(--blue-500);
        animation-delay: .04s;
    }

    .ts-card.done {
        border-left-color: var(--green);
        animation-delay: .09s;
    }

    .ts-card.pending {
        border-left-color: var(--amber);
        animation-delay: .14s;
    }

    .ts-card.high {
        border-left-color: var(--red);
        animation-delay: .19s;
    }

    .ts-card .ts-label {
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-muted);
        margin-bottom: .5rem;
    }

    .ts-card .ts-num {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -.05em;
        line-height: 1;
    }

    .ts-card .ts-sub {
        font-size: .7rem;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    /* ── Layout ── */
    .tasks-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 16px;
        align-items: start;
    }

    @media (max-width: 992px) {
        .tasks-layout {
            grid-template-columns: 1fr;
        }
    }

    /* ── Card ── */
    .card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .card-title {
        font-size: .7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .11em;
        color: var(--text-body);
        margin-bottom: 1rem;
    }

    .card-title span {
        font-size: .7rem;
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: var(--text-muted);
        margin-left: 4px;
    }

    /* ── Add task form ── */
    .add-task-form {
        display: flex;
        gap: 8px;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .add-task-form input[type="text"] {
        flex: 1;
        min-width: 180px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .55rem .85rem;
        font-size: .84rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-dark);
        background: var(--surface);
        outline: none;
        transition: border-color .2s, background .2s;
    }

    .add-task-form input[type="text"]:focus {
        border-color: var(--blue-400);
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .add-task-form input[type="date"],
    .add-task-form select {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .55rem .7rem;
        font-size: .82rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-body);
        background: var(--surface);
        outline: none;
        cursor: pointer;
        transition: border-color .2s;
    }

    .add-task-form select:focus,
    .add-task-form input[type="date"]:focus {
        border-color: var(--blue-400);
    }

    .btn-add-task {
        background: var(--blue-600);
        color: #fff;
        border: none;
        border-radius: var(--radius-sm);
        padding: .55rem 1.1rem;
        font-size: .83rem;
        font-weight: 600;
        font-family: 'DM Sans', sans-serif;
        cursor: pointer;
        transition: background .15s, box-shadow .15s;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-add-task:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, .25);
    }

    .btn-add-task:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    /* ── Filter tabs ── */
    .task-filters {
        display: flex;
        gap: 6px;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: .3rem .75rem;
        border-radius: 99px;
        border: 1px solid var(--border);
        font-size: .75rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        background: var(--surface);
        transition: all .15s;
        user-select: none;
    }

    .filter-tab:hover {
        border-color: var(--blue-200);
        color: var(--blue-600);
    }

    .filter-tab.active {
        background: var(--blue-600);
        color: #fff;
        border-color: var(--blue-600);
    }

    /* ── Search ── */
    .task-search-wrap {
        position: relative;
        margin-bottom: 1rem;
    }

    .task-search-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: .85rem;
        pointer-events: none;
    }

    .task-search {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: .5rem .85rem .5rem 2.1rem;
        font-size: .82rem;
        font-family: 'DM Sans', sans-serif;
        color: var(--text-dark);
        background: var(--surface);
        outline: none;
        transition: border-color .2s;
    }

    .task-search:focus {
        border-color: var(--blue-400);
        background: #fff;
    }

    /* ── Task items ── */
    .task-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .task-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: .85rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
        transition: background .15s, border-color .15s;
        animation: slideIn .22s ease both;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .task-item:hover {
        background: var(--blue-50);
        border-color: var(--blue-200);
    }

    .task-item.done-item {
        opacity: .65;
    }

    .task-check {
        margin-top: 2px;
        accent-color: var(--blue-600);
        width: 16px;
        height: 16px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .task-body {
        flex: 1;
        min-width: 0;
    }

    .task-text {
        font-size: .85rem;
        color: var(--text-body);
        line-height: 1.5;
        word-break: break-word;
    }

    .task-text.struck {
        text-decoration: line-through;
        color: var(--text-muted);
    }

    .task-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: .35rem;
        flex-wrap: wrap;
    }

    .task-cat {
        font-size: .65rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .task-date-label {
        font-size: .65rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .task-assignee {
        font-size: .65rem;
        color: var(--blue-600);
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .priority-badge {
        font-size: .6rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: .05em;
        flex-shrink: 0;
    }

    .priority-badge.high {
        background: var(--red-light);
        color: var(--red-dark);
    }

    .priority-badge.medium {
        background: var(--amber-light);
        color: var(--amber-dark);
    }

    .priority-badge.low {
        background: var(--green-light);
        color: var(--green-dark);
    }

    .status-badge {
        font-size: .6rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: .04em;
        flex-shrink: 0;
    }

    .status-badge.done {
        background: var(--green-light);
        color: var(--green-dark);
    }

    .status-badge.pending {
        background: var(--amber-light);
        color: var(--amber-dark);
    }

    /* ── Task actions ── */
    .task-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }

    .btn-task-action {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 6px;
        font-size: .8rem;
        transition: color .15s, background .15s;
        line-height: 1;
    }

    .btn-task-action:hover {
        background: var(--blue-50);
        color: var(--blue-600);
    }

    .btn-task-action.del:hover {
        background: var(--red-light);
        color: var(--red);
    }

    /* ── Loading overlay ── */
    .list-loading {
        display: none;
        text-align: center;
        padding: 2rem;
        color: var(--text-muted);
        font-size: .82rem;
    }

    /* ── Empty state ── */
    .task-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--text-muted);
    }

    .task-empty i {
        font-size: 2.5rem;
        color: var(--blue-200);
        display: block;
        margin-bottom: .75rem;
    }

    .task-empty p {
        font-size: .85rem;
        margin: 0;
    }

    /* ── Widgets ── */
    .widget-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 1.25rem;
        margin-bottom: 16px;
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
        margin-bottom: .85rem;
    }

    .progress-wrap {
        margin-bottom: 1.1rem;
    }

    .progress-wrap:last-child {
        margin-bottom: 0;
    }

    .progress-head {
        display: flex;
        justify-content: space-between;
        font-size: .78rem;
        margin-bottom: .35rem;
    }

    .progress-label {
        color: var(--text-body);
        font-weight: 500;
    }

    .progress-pct {
        color: var(--text-muted);
        font-size: .72rem;
    }

    .progress-bar-track {
        height: 6px;
        background: var(--blue-100);
        border-radius: 99px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 99px;
        transition: width .5s ease;
    }

    /* ── Dropdown ── */
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

    .filter {
        position: absolute;
        top: 12px;
        right: 14px;
    }

    .filter .icon i {
        color: var(--text-muted);
        font-size: .9rem;
    }

    .filter .icon:hover i {
        color: var(--blue-600);
    }

    /* ── Edit modal ── */
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
    }

    .btn-primary-sm:hover {
        background: var(--blue-700);
        box-shadow: 0 2px 8px rgba(37, 99, 235, .25);
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
    <h1>Tasks</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Tasks</li>
        </ol>
    </nav>
</div>

<section class="section page-tasks">

    <!-- ── Summary strip ── -->
    <div class="task-summary">
        <div class="ts-card all">
            <div class="ts-label">Total Tasks</div>
            <div class="ts-num" id="sumTotal">—</div>
            <div class="ts-sub">All categories</div>
        </div>
        <div class="ts-card done">
            <div class="ts-label">Completed</div>
            <div class="ts-num" id="sumDone">—</div>
            <div class="ts-sub" id="sumDonePct">—</div>
        </div>
        <div class="ts-card pending">
            <div class="ts-label">Pending</div>
            <div class="ts-num" id="sumPending">—</div>
            <div class="ts-sub">To be done</div>
        </div>
        <div class="ts-card high">
            <div class="ts-label">High Priority</div>
            <div class="ts-num" id="sumHigh">—</div>
            <div class="ts-sub">Needs attention</div>
        </div>
    </div>

    <!-- ── Main layout ── -->
    <div class="tasks-layout">

        <!-- LEFT — task list -->
        <div class="card" style="padding:1.4rem 1.5rem; position:relative;">

            <!-- Bulk actions -->
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Bulk Actions</h6>
                    </li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('mark_all_done',event)"><i class="bi bi-check2-all me-2"></i>Mark All Done</a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('clear_done',event)"><i class="bi bi-trash me-2"></i>Clear Completed</a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkAction('clear_all',event)" style="color:var(--red)"><i class="bi bi-x-circle me-2"></i>Clear All</a></li>
                </ul>
            </div>

            <h5 class="card-title">All Tasks <span>| <?php echo date('F j, Y'); ?></span></h5>

            <!-- Add task form -->
            <div class="add-task-form">
                <input type="text" id="taskInput" placeholder="Add a new task…" maxlength="255">
                <select id="taskCategory">
                    <option value="General">General</option>
                    <option value="Clinical">Clinical</option>
                    <option value="Admin">Admin</option>
                    <option value="Follow-up">Follow-up</option>
                    <option value="Inventory">Inventory</option>
                </select>
                <select id="taskPriority">
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Low">Low</option>
                </select>
                <input type="date" id="taskDueDate" title="Due date (optional)">
                <button class="btn-add-task" id="addBtn" onclick="addTask()">
                    <i class="bi bi-plus-lg"></i>Add Task
                </button>
            </div>

            <!-- Filter tabs -->
            <div class="task-filters">
                <span class="filter-tab active" data-filter="all" onclick="setFilter('all',this)">All</span>
                <span class="filter-tab" data-filter="pending" onclick="setFilter('pending',this)">Pending</span>
                <span class="filter-tab" data-filter="done" onclick="setFilter('done',this)">Completed</span>
                <span class="filter-tab" data-filter="high" onclick="setFilter('high',this)">High</span>
                <span class="filter-tab" data-filter="medium" onclick="setFilter('medium',this)">Medium</span>
                <span class="filter-tab" data-filter="low" onclick="setFilter('low',this)">Low</span>
            </div>

            <!-- Search -->
            <div class="task-search-wrap">
                <i class="bi bi-search"></i>
                <input type="text" class="task-search" id="taskSearch" placeholder="Search tasks…" oninput="debounceLoad()">
            </div>

            <!-- Loading -->
            <div class="list-loading" id="listLoading">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span style="margin-left:8px;">Loading tasks…</span>
            </div>

            <!-- List -->
            <div class="task-list" id="taskList"></div>
        </div>

        <!-- RIGHT — widgets -->
        <div>
            <!-- Progress by category -->
            <div class="widget-card" style="position:relative;">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>View</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Week</a></li>
                    </ul>
                </div>
                <div class="widget-title">Progress by Category</div>
                <div id="categoryProgress"></div>
            </div>

            <!-- Completion donut -->
            <div class="widget-card" style="position:relative;">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                    </ul>
                </div>
                <div class="widget-title">Completion Rate</div>
                <div id="completionChart" style="min-height:200px;"></div>
            </div>
        </div>

    </div>
</section>

<!-- ════════════════════════════════════════
     EDIT MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="editTitle" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="editCategory">
                            <option>General</option>
                            <option>Clinical</option>
                            <option>Admin</option>
                            <option>Follow-up</option>
                            <option>Inventory</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <select class="form-select" id="editPriority">
                            <option>High</option>
                            <option>Medium</option>
                            <option>Low</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="editStatus">
                            <option>Pending</option>
                            <option>Done</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="editDueDate">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assigned To</label>
                        <select class="form-select" id="editAssignedTo">
                            <option value="">— Unassigned —</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-primary-sm" onclick="saveEdit()">
                    <i class="bi bi-check-lg"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ────────────────────────────────────────────
    // Config
    // ────────────────────────────────────────────
    const HANDLER = 'tasks_handler.php';
    let activeFilter = 'all';
    let searchTimer = null;
    let completionChart = null;

    const catColors = {
        Clinical: '#2563eb',
        Admin: '#10b981',
        'Follow-up': '#f59e0b',
        Inventory: '#8b5cf6',
        General: '#06b6d4'
    };

    // ────────────────────────────────────────────
    // Init
    // ────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
        loadTasks();
        document.getElementById('taskInput').addEventListener('keydown', e => {
            if (e.key === 'Enter') addTask();
        });
    });

    // ────────────────────────────────────────────
    // Load users for edit modal assignee dropdown
    // ────────────────────────────────────────────
    function loadUsers() {
        fetch(`${HANDLER}?action=get_users`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                const sel = document.getElementById('editAssignedTo');
                res.data.forEach(u => {
                    sel.insertAdjacentHTML('beforeend',
                        `<option value="${u.id}">${u.name}</option>`);
                });
            });
    }

    // ────────────────────────────────────────────
    // Load / refresh task list
    // ────────────────────────────────────────────
    function loadTasks() {
        const search = encodeURIComponent(document.getElementById('taskSearch').value.trim());
        const url = `${HANDLER}?action=list&filter=${activeFilter}&search=${search}`;

        document.getElementById('listLoading').style.display = 'block';
        document.getElementById('taskList').innerHTML = '';

        fetch(url)
            .then(r => r.json())
            .then(res => {
                document.getElementById('listLoading').style.display = 'none';
                if (!res.success) return;
                renderRows(res.rows);
                renderStats(res.stats);
                renderCategoryProgress(res.categories);
                renderCompletionChart(res.stats);
            })
            .catch(() => {
                document.getElementById('listLoading').style.display = 'none';
            });
    }

    function debounceLoad() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadTasks, 350);
    }

    // ────────────────────────────────────────────
    // Render rows
    // ────────────────────────────────────────────
    function esc(s) {
        if (!s) return '';
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmtDate(d) {
        if (!d || d === '0000-00-00') return null;
        const dt = new Date(d + 'T00:00:00');
        return dt.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function isDue(d) {
        if (!d || d === '0000-00-00') return false;
        return new Date(d) < new Date(new Date().toDateString());
    }

    function renderRows(rows) {
        const list = document.getElementById('taskList');

        if (!rows.length) {
            list.innerHTML = `<div class="task-empty">
            <i class="bi bi-clipboard-check"></i>
            <p>No tasks match this filter.<br>
            <span style="color:var(--blue-400);cursor:pointer" onclick="setFilter('all',document.querySelector('[data-filter=all]'))">Show all tasks</span></p>
        </div>`;
            return;
        }

        const isDone = t => t.status === 'Done';

        list.innerHTML = rows.map(t => {
            const pri = (t.priority || 'medium').toLowerCase();
            const done = isDone(t);
            const dueFmt = fmtDate(t.dueDate);
            const overdue = !done && isDue(t.dueDate);
            const dateColor = overdue ? 'color:var(--red)' : '';

            return `<div class="task-item ${done ? 'done-item' : ''}" id="ti-${t.id}">
            <input type="checkbox" class="task-check" ${done ? 'checked' : ''} onchange="toggleTask(${t.id})">
            <div class="task-body">
                <div class="task-text ${done ? 'struck' : ''}">${esc(t.title)}</div>
                <div class="task-meta">
                    <span class="task-cat">${esc(t.category)}</span>
                    ${dueFmt ? `<span class="task-date-label" style="${dateColor}">
                        <i class="bi bi-calendar3" style="font-size:.6rem"></i>${dueFmt}${overdue?' · Overdue':''}
                    </span>` : ''}
                    ${t.assigneeName ? `<span class="task-assignee"><i class="bi bi-person" style="font-size:.65rem"></i>${esc(t.assigneeName)}</span>` : ''}
                    <span class="priority-badge ${pri}">${esc(t.priority)}</span>
                    <span class="status-badge ${done ? 'done' : 'pending'}">${done ? 'done' : 'pending'}</span>
                </div>
            </div>
            <div class="task-actions">
                <button class="btn-task-action" onclick="openEdit(${t.id})" title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn-task-action del" onclick="deleteTask(${t.id})" title="Delete"><i class="bi bi-trash3"></i></button>
            </div>
        </div>`;
        }).join('');
    }

    // ────────────────────────────────────────────
    // Render summary stats
    // ────────────────────────────────────────────
    function renderStats(s) {
        const total = parseInt(s.total) || 0;
        const done = parseInt(s.done) || 0;
        const pct = total ? Math.round(done / total * 100) : 0;

        document.getElementById('sumTotal').textContent = total;
        document.getElementById('sumDone').textContent = done;
        document.getElementById('sumPending').textContent = parseInt(s.pending) || 0;
        document.getElementById('sumHigh').textContent = parseInt(s.high_pending) || 0;
        document.getElementById('sumDonePct').textContent = `${pct}% completion`;
    }

    // ────────────────────────────────────────────
    // Category progress bars
    // ────────────────────────────────────────────
    function renderCategoryProgress(cats) {
        const el = document.getElementById('categoryProgress');
        if (!cats || !cats.length) {
            el.innerHTML = '<p style="font-size:.78rem;color:var(--text-muted)">No tasks yet.</p>';
            return;
        }
        el.innerHTML = cats.map(c => {
            const pct = c.total ? Math.round((c.done / c.total) * 100) : 0;
            const col = catColors[c.category] || '#9ca3af';
            return `<div class="progress-wrap">
            <div class="progress-head">
                <span class="progress-label">${esc(c.category)}</span>
                <span class="progress-pct">${c.done}/${c.total} · ${pct}%</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill" style="width:${pct}%;background:${col}"></div>
            </div>
        </div>`;
        }).join('');
    }

    // ────────────────────────────────────────────
    // Completion donut (ECharts)
    // ────────────────────────────────────────────
    function renderCompletionChart(s) {
        const done = parseInt(s.done) || 0;
        const pending = parseInt(s.pending) || 0;
        const total = done + pending;
        const el = document.querySelector('#completionChart');
        if (!el) return;
        if (!completionChart) completionChart = echarts.init(el);

        completionChart.setOption({
            tooltip: {
                trigger: 'item',
                formatter: '{b}: {c} ({d}%)',
                textStyle: {
                    fontFamily: 'DM Sans',
                    fontSize: 12
                }
            },
            legend: {
                bottom: 0,
                left: 'center',
                textStyle: {
                    fontFamily: 'DM Sans',
                    fontSize: 11,
                    color: '#4b5563'
                },
                itemWidth: 10,
                itemHeight: 10,
                icon: 'circle'
            },
            series: [{
                type: 'pie',
                radius: ['48%', '72%'],
                center: ['50%', '44%'],
                avoidLabelOverlap: false,
                label: {
                    show: true,
                    position: 'center',
                    formatter: () => `${total ? Math.round(done/total*100) : 0}%`,
                    fontSize: 22,
                    fontWeight: 700,
                    fontFamily: 'DM Sans',
                    color: '#111827'
                },
                emphasis: {
                    label: {
                        show: true
                    }
                },
                labelLine: {
                    show: false
                },
                data: [{
                        value: done,
                        name: 'Completed',
                        itemStyle: {
                            color: '#10b981'
                        }
                    },
                    {
                        value: pending,
                        name: 'Pending',
                        itemStyle: {
                            color: '#dbeafe'
                        }
                    }
                ]
            }]
        });
    }

    // ────────────────────────────────────────────
    // Add task
    // ────────────────────────────────────────────
    function addTask() {
        const input = document.getElementById('taskInput');
        const title = input.value.trim();
        if (!title) {
            input.focus();
            return;
        }

        const btn = document.getElementById('addBtn');
        btn.disabled = true;

        fetch(`${HANDLER}?action=add`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    title,
                    category: document.getElementById('taskCategory').value,
                    priority: document.getElementById('taskPriority').value,
                    dueDate: document.getElementById('taskDueDate').value || null,
                    status: 'Pending',
                })
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                if (res.success) {
                    input.value = '';
                    document.getElementById('taskDueDate').value = '';
                    loadTasks();
                } else {
                    alert('Failed to add task. Try again.');
                }
            })
            .catch(() => {
                btn.disabled = false;
            });
    }

    // ────────────────────────────────────────────
    // Toggle done / pending
    // ────────────────────────────────────────────
    function toggleTask(id) {
        const fd = new FormData();
        fd.append('id', id);
        fetch(`${HANDLER}?action=toggle`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) loadTasks();
            });
    }

    // ────────────────────────────────────────────
    // Delete
    // ────────────────────────────────────────────
    function deleteTask(id) {
        if (!confirm('Delete this task?')) return;
        const fd = new FormData();
        fd.append('id', id);
        fetch(`${HANDLER}?action=delete`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) loadTasks();
            });
    }

    // ────────────────────────────────────────────
    // Edit modal
    // ────────────────────────────────────────────
    let allRows = [];

    function openEdit(id) {
        // Fetch fresh single record
        fetch(`${HANDLER}?action=list&filter=all&search=`)
            .then(r => r.json())
            .then(res => {
                const t = res.rows.find(r => r.id == id);
                if (!t) return;
                document.getElementById('editId').value = t.id;
                document.getElementById('editTitle').value = t.title;
                document.getElementById('editCategory').value = t.category;
                document.getElementById('editPriority').value = t.priority;
                document.getElementById('editStatus').value = t.status;
                document.getElementById('editDueDate').value = t.dueDate || '';
                document.getElementById('editAssignedTo').value = t.assignedTo || '';
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
    }

    function saveEdit() {
        const payload = {
            id: document.getElementById('editId').value,
            title: document.getElementById('editTitle').value.trim(),
            category: document.getElementById('editCategory').value,
            priority: document.getElementById('editPriority').value,
            status: document.getElementById('editStatus').value,
            dueDate: document.getElementById('editDueDate').value || null,
            assignedTo: document.getElementById('editAssignedTo').value || null,
        };

        if (!payload.title) {
            alert('Title is required.');
            return;
        }

        fetch(`${HANDLER}?action=edit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    loadTasks();
                } else {
                    alert('Failed to save. Try again.');
                }
            });
    }

    // ────────────────────────────────────────────
    // Bulk actions
    // ────────────────────────────────────────────
    function bulkAction(action, e) {
        e.preventDefault();
        if (action === 'clear_all' && !confirm('Delete ALL tasks? This cannot be undone.')) return;
        if (action === 'clear_done' && !confirm('Delete all completed tasks?')) return;

        fetch(`${HANDLER}?action=${action}`, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) loadTasks();
            });
    }

    // ────────────────────────────────────────────
    // Filter
    // ────────────────────────────────────────────
    function setFilter(f, el) {
        activeFilter = f;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        if (el) el.classList.add('active');
        loadTasks();
    }
</script>

<?php include('./includes/footer.php'); ?>