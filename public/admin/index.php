<?php
include('../../app/middleware/admin.php');
include('./includes/header.php');
include('./includes/topbar.php');
include('./includes/sidebar.php');
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap');

  :root {
    --blue-50: #eff6ff;
    --blue-100: #dbeafe;
    --blue-200: #bfdbfe;
    --blue-300: #93c5fd;
    --blue-400: #60a5fa;
    --blue-500: #3b82f6;
    --blue-600: #2563eb;
    --blue-700: #1d4ed8;
    --blue-800: #1e40af;
    --blue-900: #1e3a8a;

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

    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
    --shadow-md: 0 4px 16px rgba(0, 0, 0, .07);
    --shadow-lg: 0 8px 30px rgba(0, 0, 0, .10);
  }

  /* ── Base ── */
  .section.dashboard,
  .section.dashboard * {
    font-family: 'DM Sans', sans-serif;
    box-sizing: border-box;
  }

  .section.dashboard {
    background: var(--surface);
    padding-bottom: 2.5rem;
  }

  /* ── Page title — now DM Sans (not serif italic) ── */
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

  /* ── Card base ── */
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    transition: box-shadow .2s ease, transform .2s ease;
    overflow: hidden;
  }

  .card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-1px);
  }

  /* ── Stat cards ── */
  .info-card .card-body {
    padding: 1.4rem 1.5rem 1.3rem;
    position: relative;
  }

  .info-card .card-title {
    font-size: .67rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--text-muted);
    margin-bottom: .85rem;
  }

  .info-card .card-title span {
    font-weight: 400;
    letter-spacing: 0;
    text-transform: none;
    font-size: .7rem;
    color: var(--text-muted);
    opacity: .75;
  }

  .info-card h6 {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-size: 2.1rem;
    color: var(--text-dark);
    margin: 0;
    line-height: 1;
    letter-spacing: -.05em;
  }

  .info-card .stat-trend {
    margin-top: .4rem;
    font-size: .72rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .info-card .stat-trend.up {
    color: var(--green);
  }

  .info-card .stat-trend.down {
    color: var(--red);
  }

  .info-card .stat-trend span {
    font-weight: 400;
    color: var(--text-muted);
  }

  .card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    font-size: 1.2rem;
    flex-shrink: 0;
  }

  .sales-card .card-icon {
    background: var(--blue-50);
    color: var(--blue-600);
  }

  .revenue-card .card-icon {
    background: #ecfdf5;
    color: var(--green);
  }

  .customers-card .card-icon {
    background: #f5f3ff;
    color: var(--violet);
  }

  .info-card {
    border-left: 3px solid transparent;
  }

  .sales-card {
    border-left-color: var(--blue-500);
  }

  .revenue-card {
    border-left-color: var(--green);
  }

  .customers-card {
    border-left-color: var(--violet);
  }

  /* ── Section card title ── */
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

  /* ── Filter icon ── */
  .filter .icon i {
    color: var(--text-muted);
    font-size: .9rem;
  }

  .filter .icon:hover i {
    color: var(--blue-600);
  }

  /* ── Tables ── */
  .table thead th {
    font-size: .65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border) !important;
    padding-bottom: .75rem;
    background: transparent;
  }

  .table tbody td,
  .table tbody th {
    font-size: .83rem;
    color: var(--text-body);
    vertical-align: middle;
    border-color: var(--border);
    padding: .7rem .5rem;
  }

  .table tbody tr:hover td,
  .table tbody tr:hover th {
    background: var(--blue-50);
  }

  .table tbody th a {
    color: var(--blue-700);
    font-weight: 600;
    text-decoration: none;
    font-size: .79rem;
  }

  .table .text-primary {
    color: var(--blue-600) !important;
    text-decoration: none;
    font-weight: 500;
  }

  /* ── Badges ── */
  .badge {
    font-family: 'DM Sans', sans-serif;
    font-size: .65rem;
    font-weight: 600;
    border-radius: 6px;
    padding: 3px 9px;
    letter-spacing: .03em;
  }

  .badge.bg-success {
    background: var(--green-light) !important;
    color: var(--green-dark) !important;
  }

  .badge.bg-warning {
    background: var(--amber-light) !important;
    color: var(--amber-dark) !important;
  }

  .badge.bg-danger {
    background: var(--red-light) !important;
    color: var(--red-dark) !important;
  }

  .badge.bg-info {
    background: var(--teal-light) !important;
    color: var(--teal-dark) !important;
  }

  .badge.bg-violet {
    background: #ede9fe !important;
    color: #5b21b6 !important;
  }

  /* ── Duty progress pill ── */
  .duty-progress {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .duty-bar {
    flex: 1;
    height: 5px;
    background: var(--blue-100);
    border-radius: 99px;
    overflow: hidden;
    max-width: 80px;
  }

  .duty-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--blue-500), var(--blue-400));
    border-radius: 99px;
    transition: width .4s ease;
  }

  .duty-fraction {
    font-size: .75rem;
    font-weight: 600;
    color: var(--text-body);
    white-space: nowrap;
  }

  .duty-fraction .done {
    color: var(--blue-700);
  }

  .duty-fraction .total {
    color: var(--text-muted);
    font-weight: 400;
  }

  /* ── Activity list ── */
  .activity {
    display: flex;
    flex-direction: column;
    gap: 0;
  }

  .activity-item {
    padding: .6rem 0;
    border-bottom: 1px solid var(--border);
    gap: 0;
  }

  .activity-item:last-child {
    border-bottom: none;
  }

  .activite-label {
    font-size: .65rem;
    font-weight: 600;
    color: var(--text-muted);
    min-width: 48px;
    padding-top: 3px;
    letter-spacing: .02em;
  }

  .activity-badge {
    font-size: .42rem;
    margin: 5px 14px 0;
    flex-shrink: 0;
  }

  .activity-content {
    font-size: .81rem;
    color: var(--text-body);
    line-height: 1.55;
  }

  .activity-content a {
    color: var(--text-dark);
    font-weight: 600;
    text-decoration: none;
  }

  .activity-content a:hover {
    color: var(--blue-700);
  }

  /* ── Top-selling thumb ── */
  .top-selling img {
    width: 36px;
    height: 36px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid var(--border);
  }

  /* ── Staggered fade-in ── */
  @keyframes fadeUp {
    from {
      opacity: 0;
      transform: translateY(12px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .col-xxl-4,
  .col-xl-12,
  .col-md-6 {
    animation: fadeUp .38s ease both;
  }

  .col-xxl-4:nth-child(1) {
    animation-delay: .04s;
  }

  .col-xxl-4:nth-child(2) {
    animation-delay: .10s;
  }

  .col-xxl-4:nth-child(3) {
    animation-delay: .16s;
  }

  .col-12 {
    animation: fadeUp .38s ease both;
    animation-delay: .22s;
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

  /* ── Scrollbar ── */
  .overflow-auto::-webkit-scrollbar {
    height: 4px;
  }

  .overflow-auto::-webkit-scrollbar-track {
    background: var(--blue-50);
    border-radius: 9px;
  }

  .overflow-auto::-webkit-scrollbar-thumb {
    background: var(--blue-200);
    border-radius: 9px;
  }

  /* ── Chart source legend ── */
  .source-legend {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: .5rem;
    padding: 0 .25rem;
  }

  .source-legend-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: .78rem;
  }

  .source-legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-right: 8px;
  }

  .source-legend-label {
    display: flex;
    align-items: center;
    color: var(--text-body);
  }

  .source-legend-val {
    font-weight: 700;
    color: var(--text-dark);
    font-size: .78rem;
  }

  .source-legend-pct {
    font-size: .68rem;
    color: var(--text-muted);
    margin-left: 4px;
  }

  /* ── Tasks widget ── */
  .task-add-row {
    display: flex;
    gap: 8px;
    margin-bottom: 1rem;
  }

  .task-add-row input[type="text"] {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .42rem .75rem;
    font-size: .82rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-dark);
    background: var(--surface);
    outline: none;
    transition: border-color .2s;
  }

  .task-add-row input[type="text"]:focus {
    border-color: var(--blue-400);
    background: #fff;
  }

  .task-add-row button {
    background: var(--blue-600);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    padding: .42rem .9rem;
    font-size: .8rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
  }

  .task-add-row button:hover {
    background: var(--blue-700);
  }

  .task-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 320px;
    overflow-y: auto;
  }

  .task-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: .52rem .7rem;
    border-radius: 9px;
    border: 1px solid var(--border);
    background: var(--surface);
    transition: background .15s;
    font-size: .82rem;
  }

  .task-item:hover {
    background: var(--blue-50);
  }

  .task-item input[type="checkbox"] {
    accent-color: var(--blue-600);
    width: 15px;
    height: 15px;
    cursor: pointer;
    flex-shrink: 0;
  }

  .task-item .task-label {
    flex: 1;
    color: var(--text-body);
    line-height: 1.4;
  }

  .task-item .task-label.done {
    text-decoration: line-through;
    color: var(--text-muted);
  }

  .task-item .task-priority {
    font-size: .6rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 5px;
    text-transform: uppercase;
    letter-spacing: .05em;
    flex-shrink: 0;
  }

  .task-item .task-priority.high {
    background: var(--red-light);
    color: var(--red-dark);
  }

  .task-item .task-priority.medium {
    background: var(--amber-light);
    color: var(--amber-dark);
  }

  .task-item .task-priority.low {
    background: var(--green-light);
    color: var(--green-dark);
  }

  .task-item .task-delete {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: .75rem;
    padding: 0 2px;
    line-height: 1;
    flex-shrink: 0;
    transition: color .15s;
  }

  .task-item .task-delete:hover {
    color: var(--red);
  }

  .task-empty {
    text-align: center;
    padding: 1.5rem 0;
    color: var(--text-muted);
    font-size: .8rem;
  }

  .task-stats {
    display: flex;
    gap: 12px;
    margin-bottom: .75rem;
  }

  .task-stat {
    flex: 1;
    background: var(--surface);
    border-radius: 8px;
    padding: .4rem .6rem;
    text-align: center;
    border: 1px solid var(--border);
  }

  .task-stat .ts-num {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-dark);
    letter-spacing: -.04em;
    line-height: 1;
  }

  .task-stat .ts-label {
    font-size: .62rem;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin-top: 2px;
  }

  .priority-select {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: .42rem .5rem;
    font-size: .78rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-body);
    background: var(--surface);
    outline: none;
    cursor: pointer;
  }
</style>

<div class="pagetitle">
  <h1>Dashboard</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.html">Home</a></li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

<section class="section dashboard">
  <div class="row g-3">

    <!-- ═══ Left side ═══ -->
    <div class="col-lg-8">
      <div class="row g-3">

        <!-- Appointments Card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card sales-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Appointments <span>| Today</span></h5>
              <div class="d-flex align-items-center gap-3">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-calendar2-check"></i>
                </div>
                <div>
                  <h6>38</h6>
                  <div class="stat-trend up">
                    <i class="bi bi-arrow-up-short"></i>12%
                    <span>vs yesterday</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Patients Card -->
        <div class="col-xxl-4 col-md-6">
          <div class="card info-card revenue-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Patients <span>| This Month</span></h5>
              <div class="d-flex align-items-center gap-3">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-people"></i>
                </div>
                <div>
                  <h6>214</h6>
                  <div class="stat-trend up">
                    <i class="bi bi-arrow-up-short"></i>8%
                    <span>vs last month</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Doctors Card -->
        <div class="col-xxl-4 col-xl-12">
          <div class="card info-card customers-card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Doctors <span>| Active</span></h5>
              <div class="d-flex align-items-center gap-3">
                <div class="card-icon d-flex align-items-center justify-content-center">
                  <i class="bi bi-person-badge"></i>
                </div>
                <div>
                  <h6>18</h6>
                  <div class="stat-trend up">
                    <i class="bi bi-dot" style="font-size:1.1rem;"></i>5
                    <span>on duty now</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Appointment Reports Chart -->
        <div class="col-12">
          <div class="card">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Appointment Reports <span>/ This Week</span></h5>
              <div id="reportsChart"></div>
              <script>
                document.addEventListener("DOMContentLoaded", () => {
                  new ApexCharts(document.querySelector("#reportsChart"), {
                    series: [{
                        name: 'Appointments',
                        data: [31, 40, 28, 51, 42, 82, 56]
                      },
                      {
                        name: 'Completed',
                        data: [11, 32, 45, 32, 34, 52, 41]
                      },
                      {
                        name: 'Cancelled',
                        data: [15, 11, 32, 18, 9, 24, 11]
                      }
                    ],
                    chart: {
                      height: 300,
                      type: 'area',
                      toolbar: {
                        show: false
                      },
                      fontFamily: 'DM Sans, sans-serif',
                    },
                    markers: {
                      size: 3,
                      strokeWidth: 2,
                      strokeColors: '#fff'
                    },
                    colors: ['#2563eb', '#10b981', '#f59e0b'],
                    fill: {
                      type: "gradient",
                      gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.18,
                        opacityTo: 0.02,
                        stops: [0, 95, 100]
                      }
                    },
                    dataLabels: {
                      enabled: false
                    },
                    stroke: {
                      curve: 'smooth',
                      width: 2
                    },
                    grid: {
                      borderColor: '#eaecf4',
                      strokeDashArray: 4,
                      padding: {
                        left: 4,
                        right: 4
                      }
                    },
                    xaxis: {
                      type: 'datetime',
                      categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"],
                      labels: {
                        style: {
                          colors: '#9ca3af',
                          fontSize: '11px',
                          fontFamily: 'DM Sans'
                        }
                      },
                      axisBorder: {
                        show: false
                      },
                      axisTicks: {
                        show: false
                      }
                    },
                    yaxis: {
                      labels: {
                        style: {
                          colors: '#9ca3af',
                          fontSize: '11px',
                          fontFamily: 'DM Sans'
                        }
                      }
                    },
                    tooltip: {
                      x: {
                        format: 'dd/MM/yy HH:mm'
                      }
                    },
                    legend: {
                      position: 'top',
                      horizontalAlign: 'right',
                      fontSize: '12px',
                      fontFamily: 'DM Sans',
                      fontWeight: 600,
                      markers: {
                        width: 7,
                        height: 7,
                        radius: 4
                      }
                    }
                  }).render();
                });
              </script>
            </div>
          </div>
        </div>

        <!-- Today's Appointments Table (with date column) -->
        <div class="col-12">
          <div class="card recent-sales overflow-auto">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
            <div class="card-body">
              <h5 class="card-title">Today's Appointments <span>| <?php echo date('F j, Y'); ?></span></h5>
              <table class="table table-borderless datatable">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Patient</th>
                    <th scope="col">Doctor</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    <th scope="col">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row"><a href="#">#A-1021</a></th>
                    <td>Maria Santos</td>
                    <td><a href="#" class="text-primary">Dr. Reyes – General</a></td>
                    <td><?php echo date('M j, Y'); ?></td>
                    <td>08:00 AM</td>
                    <td><span class="badge bg-success">Completed</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Doctors on Duty -->
        <div class="col-12">
          <div class="card top-selling overflow-auto">
            <div class="filter">
              <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <li class="dropdown-header text-start">
                  <h6>Filter</h6>
                </li>
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
            <div class="card-body pb-0">
              <h5 class="card-title">Doctors on Duty <span>| Today</span></h5>
              <table class="table table-borderless">
                <thead>
                  <tr>
                    <th scope="col">Photo</th>
                    <th scope="col">Doctor</th>
                    <th scope="col">Specialization</th>
                    <th scope="col">Patient Load</th>
                    <th scope="col">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th scope="row"><a href="#"><img src="assets/img/product-1.jpg" alt="Dr. Jose Reyes"></a></th>
                    <td><a href="#" class="text-primary fw-bold">Dr. Jose Reyes</a></td>
                    <td>General Medicine</td>
                    <td>
                      <div class="duty-progress">
                        <div class="duty-bar">
                          <div class="duty-bar-fill" style="width:71%"></div>
                        </div>
                        <span class="duty-fraction"><span class="done">12</span><span class="total">/17</span></span>
                      </div>
                    </td>
                    <td><span class="badge bg-info">On Duty</span></td>
                  </tr>
                  <tr>

                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div><!-- End Left side -->

    <!-- ═══ Right side ═══ -->
    <div class="col-lg-4">

      <!-- Recent Activity -->
      <div class="card mb-3">
        <div class="filter">
          <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
              <h6>Filter</h6>
            </li>
            <li><a class="dropdown-item" href="#">Today</a></li>
            <li><a class="dropdown-item" href="#">This Month</a></li>
            <li><a class="dropdown-item" href="#">This Year</a></li>
          </ul>
        </div>
        <div class="card-body">
          <h5 class="card-title">Recent Activity <span>| Today</span></h5>
          <div class="activity">
            <div class="activity-item d-flex">
              <div class="activite-label">5 min</div>
              <i class='bi bi-circle-fill activity-badge align-self-start' style="color:var(--green)"></i>
              <div class="activity-content">New appointment booked by <a href="#">Maria Santos</a></div>
            </div>
            <div class="activity-item d-flex">
              <div class="activite-label">20 min</div>
              <i class='bi bi-circle-fill activity-badge align-self-start' style="color:var(--red)"></i>
              <div class="activity-content">Appointment #A-1024 cancelled by patient</div>
            </div>
            <div class="activity-item d-flex">
              <div class="activite-label">1 hr</div>
              <i class='bi bi-circle-fill activity-badge align-self-start' style="color:var(--blue-500)"></i>
              <div class="activity-content">Dr. Lim marked #A-1022 as <a href="#">In Progress</a></div>
            </div>
            <div class="activity-item d-flex">
              <div class="activite-label">2 hrs</div>
              <i class='bi bi-circle-fill activity-badge align-self-start' style="color:var(--teal)"></i>
              <div class="activity-content">Medical record updated for <a href="#">Ana Garcia</a></div>
            </div>
            <div class="activity-item d-flex">
              <div class="activite-label">3 hrs</div>
              <i class='bi bi-circle-fill activity-badge align-self-start' style="color:var(--amber)"></i>
              <div class="activity-content">New patient registered: Roberto Tan</div>
            </div>
            <div class="activity-item d-flex">
              <div class="activite-label">1 day</div>
              <i class='bi bi-circle-fill activity-badge align-self-start' style="color:var(--text-muted)"></i>
              <div class="activity-content">Dr. Flores updated availability for next week</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ Tasks Widget (replaces Appointment Status Radar) ══ -->
      <div class="card mb-3">
        <div class="filter">
          <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
              <h6>Options</h6>
            </li>
            <li><a class="dropdown-item" href="#" onclick="clearDoneTasks(event)">Clear Completed</a></li>
            <li><a class="dropdown-item" href="#" onclick="clearAllTasks(event)">Clear All</a></li>
          </ul>
        </div>
        <div class="card-body">
          <h5 class="card-title">Tasks <span>| Today</span></h5>

          <!-- Stats row -->
          <div class="task-stats" id="taskStats">
            <div class="task-stat">
              <div class="ts-num" id="tsTotal">0</div>
              <div class="ts-label">Total</div>
            </div>
            <div class="task-stat">
              <div class="ts-num" id="tsDone">0</div>
              <div class="ts-label">Done</div>
            </div>
            <div class="task-stat">
              <div class="ts-num" id="tsLeft">0</div>
              <div class="ts-label">Left</div>
            </div>
          </div>

          <!-- Add task row -->
          <div class="task-add-row">
            <input type="text" id="taskInput" placeholder="Add a new task…" maxlength="100">
            <select class="priority-select" id="taskPriority">
              <option value="medium">Med</option>
              <option value="high">High</option>
              <option value="low">Low</option>
            </select>
            <button onclick="addTask()"><i class="bi bi-plus-lg me-1"></i>Add</button>
          </div>

          <!-- Task list -->
          <div class="task-list" id="taskList">
            <!-- seeded tasks -->
          </div>
        </div>
      </div>

      <!-- Appointment Channel — horizontal bar chart -->
      <div class="card mb-3">
        <div class="filter">
          <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
              <h6>Filter</h6>
            </li>
            <li><a class="dropdown-item" href="#">Today</a></li>
            <li><a class="dropdown-item" href="#">This Month</a></li>
            <li><a class="dropdown-item" href="#">This Year</a></li>
          </ul>
        </div>
        <div class="card-body pb-2">
          <h5 class="card-title">Appointment Channel <span>| Today</span></h5>
          <div id="channelChart" style="min-height: 240px;" class="echart"></div>

          <div class="source-legend">
            <div class="source-legend-item">
              <div class="source-legend-label">
                <div class="source-legend-dot" style="background:#2563eb"></div>Online Booking
              </div>
              <div><span class="source-legend-val">480</span><span class="source-legend-pct">38%</span></div>
            </div>
            <div class="source-legend-item">
              <div class="source-legend-label">
                <div class="source-legend-dot" style="background:#10b981"></div>Walk-in
              </div>
              <div><span class="source-legend-val">320</span><span class="source-legend-pct">25%</span></div>
            </div>
            <div class="source-legend-item">
              <div class="source-legend-label">
                <div class="source-legend-dot" style="background:#f59e0b"></div>Phone Call
              </div>
              <div><span class="source-legend-val">210</span><span class="source-legend-pct">17%</span></div>
            </div>
            <div class="source-legend-item">
              <div class="source-legend-label">
                <div class="source-legend-dot" style="background:#8b5cf6"></div>Referral
              </div>
              <div><span class="source-legend-val">150</span><span class="source-legend-pct">12%</span></div>
            </div>
            <div class="source-legend-item">
              <div class="source-legend-label">
                <div class="source-legend-dot" style="background:#06b6d4"></div>Follow-up
              </div>
              <div><span class="source-legend-val">88</span><span class="source-legend-pct">7%</span></div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- End Right side -->
  </div>
</section>

<script>
  /* ── Task Widget JS ── */
  let tasks = [{
      id: 1,
      label: 'Review morning appointment schedule',
      priority: 'high',
      done: false
    },
    {
      id: 2,
      label: 'Follow up on lab results for A-1021',
      priority: 'high',
      done: true
    },
    {
      id: 3,
      label: 'Update patient records for Ana Garcia',
      priority: 'medium',
      done: false
    },
    {
      id: 4,
      label: 'Send reminder SMS for afternoon slots',
      priority: 'medium',
      done: false
    },
    {
      id: 5,
      label: 'Check supply inventory – exam gloves',
      priority: 'low',
      done: false
    },
  ];

  let nextId = 6;

  function renderTasks() {
    const list = document.getElementById('taskList');
    if (tasks.length === 0) {
      list.innerHTML = '<div class="task-empty"><i class="bi bi-check2-all d-block mb-1" style="font-size:1.4rem;color:var(--blue-200)"></i>No tasks yet</div>';
    } else {
      list.innerHTML = tasks.map(t => `
        <div class="task-item" id="task-${t.id}">
          <input type="checkbox" ${t.done ? 'checked' : ''} onchange="toggleTask(${t.id})">
          <span class="task-label ${t.done ? 'done' : ''}">${escHtml(t.label)}</span>
          <span class="task-priority ${t.priority}">${t.priority}</span>
          <button class="task-delete" onclick="deleteTask(${t.id})" title="Remove"><i class="bi bi-x"></i></button>
        </div>
      `).join('');
    }
    const total = tasks.length;
    const done = tasks.filter(t => t.done).length;
    document.getElementById('tsTotal').textContent = total;
    document.getElementById('tsDone').textContent = done;
    document.getElementById('tsLeft').textContent = total - done;
  }

  function addTask() {
    const input = document.getElementById('taskInput');
    const label = input.value.trim();
    if (!label) {
      input.focus();
      return;
    }
    const priority = document.getElementById('taskPriority').value;
    tasks.unshift({
      id: nextId++,
      label,
      priority,
      done: false
    });
    input.value = '';
    renderTasks();
  }

  function toggleTask(id) {
    const t = tasks.find(t => t.id === id);
    if (t) t.done = !t.done;
    renderTasks();
  }

  function deleteTask(id) {
    tasks = tasks.filter(t => t.id !== id);
    renderTasks();
  }

  function clearDoneTasks(e) {
    e.preventDefault();
    tasks = tasks.filter(t => !t.done);
    renderTasks();
  }

  function clearAllTasks(e) {
    e.preventDefault();
    tasks = [];
    renderTasks();
  }

  function escHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  document.addEventListener('DOMContentLoaded', () => {
    renderTasks();

    // Allow Enter key to add task
    document.getElementById('taskInput').addEventListener('keydown', e => {
      if (e.key === 'Enter') addTask();
    });

    // Channel chart
    echarts.init(document.querySelector("#channelChart")).setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'none'
        }
      },
      grid: {
        left: '2%',
        right: '12%',
        top: '4%',
        bottom: '4%',
        containLabel: true
      },
      xAxis: {
        type: 'value',
        axisLine: {
          show: false
        },
        axisTick: {
          show: false
        },
        splitLine: {
          lineStyle: {
            color: '#eaecf4',
            type: 'dashed'
          }
        },
        axisLabel: {
          fontFamily: 'DM Sans',
          fontSize: 10,
          color: '#9ca3af'
        }
      },
      yAxis: {
        type: 'category',
        data: ['Follow-up', 'Referral', 'Phone Call', 'Walk-in', 'Online'],
        axisLine: {
          show: false
        },
        axisTick: {
          show: false
        },
        axisLabel: {
          fontFamily: 'DM Sans',
          fontSize: 11,
          color: '#4b5563'
        }
      },
      series: [{
        type: 'bar',
        data: [{
            value: 88,
            itemStyle: {
              color: '#06b6d4',
              borderRadius: [0, 6, 6, 0]
            }
          },
          {
            value: 150,
            itemStyle: {
              color: '#8b5cf6',
              borderRadius: [0, 6, 6, 0]
            }
          },
          {
            value: 210,
            itemStyle: {
              color: '#f59e0b',
              borderRadius: [0, 6, 6, 0]
            }
          },
          {
            value: 320,
            itemStyle: {
              color: '#10b981',
              borderRadius: [0, 6, 6, 0]
            }
          },
          {
            value: 480,
            itemStyle: {
              color: '#2563eb',
              borderRadius: [0, 6, 6, 0]
            }
          }
        ],
        barMaxWidth: 16,
        label: {
          show: true,
          position: 'right',
          fontFamily: 'DM Sans',
          fontSize: 11,
          fontWeight: 600,
          color: '#4b5563',
          formatter: '{c}'
        }
      }]
    });
  });
</script>

<?php include('./includes/footer.php'); ?>