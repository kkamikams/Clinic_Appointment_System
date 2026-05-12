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

      <li class="nav-heading">Services</li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'bookAppointment.php') ? '' : 'collapsed' ?>" href="bookAppointment">
          <i class="bi bi-envelope"></i>
          <span>Book Appointment</span>
        </a>
      </li>

      <li class="nav-heading">Manage</li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'myAppointment.php') ? '' : 'collapsed' ?>" href="myAppointment">
          <i class="bi bi-person-vcard"></i>
          <span>My Appointments</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'medicalRecords.php') ? '' : 'collapsed' ?>" href="medicalRecords">
          <i class="bi bi-clipboard2-pulse"></i>
          <span>Medical Records</span>
        </a>
      </li>

    </ul>

  </aside>

  <main id="main" class="main">