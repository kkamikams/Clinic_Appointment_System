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
        <a class="nav-link <? ($page == 'book_appointment.php') ? '' : 'collapsed' ?>" href="book_appointment">
          <i class="bi bi-envelope"></i>
          <span>Book Appointments</span>
        </a>
      </li>

      <li class="nav-heading">Manage</li>

      <li class="nav-item">
        <a class="nav-link <? ($page == 'my_appointment.php') ? '' : 'collapsed' ?>" href="my_appointment">
          <i class="bi bi-person-vcard"></i>
          <span>My Appointments</span>
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