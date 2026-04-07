  <?php $page = basename($_SERVER['PHP_SELF']); ?>

  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

      <li class="nav-item">
        <a class="nav-link <? ($page == 'index.php') ? '' : 'collapsed' ?>" href="index">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li class="nav-heading">Manage</li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'doctors.php') ? '' : 'collapsed' ?>" href="doctors">
          <i class="bi bi-people-fill"></i>
          <span>Doctors</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'patients.php') ? '' : 'collapsed' ?>" href="patients">
          <i class="bi bi-people-fill"></i>
          <span>Patients</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'appointments.php') ? '' : 'collapsed' ?>" href="appointments">
          <i class="bi bi-envelope"></i>
          <span>Appointments</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'medical_records.php') ? '' : 'collapsed' ?>" href="medical_records">
          <i class="bi bi-clipboard2-pulse"></i>
          <span>Medical Records</span>
        </a>
      </li>

    </ul>

  </aside>

  <main id="main" class="main">