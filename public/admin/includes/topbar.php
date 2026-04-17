<!-- ======= Topbar ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

  <div class="d-flex align-items-center justify-content-between">
    <a href="index.html" class="logo d-flex align-items-center">
      <img src="assets/img/logo.png" alt="">
      <span class="d-none d-lg-block" style="font-family:'DM Serif Display',serif;font-style:italic;font-size:1.55rem;color:#2563eb;letter-spacing:-.01em;">Queue</span>
    </a>
    <i class="bi bi-list toggle-sidebar-btn"></i>
  </div><!-- End Logo -->

  <div class="search-bar">
    <form class="search-form d-flex align-items-center" method="POST" action="#">
      <input type="text" name="query" placeholder="Search" title="Enter search keyword">
      <button type="submit" title="Search"><i class="bi bi-search"></i></button>
    </form>
  </div><!-- End Search Bar -->

  <?php
  $fullName  = $_SESSION['authUser']['fullName'] ?? 'Guest';
  $userRole  = $_SESSION['userRole'] ?? 'user';

  // Short display name: "J. Dela Cruz"
  $nameParts = explode(' ', $fullName);
  $shortName = count($nameParts) >= 2
    ? strtoupper(substr($nameParts[0], 0, 1)) . '. ' . end($nameParts)
    : $fullName;

  // Initials for avatar: first + last initial
  $initials = strtoupper(substr($nameParts[0], 0, 1));
  if (count($nameParts) >= 2) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
  }
  ?>

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">

      <li class="nav-item d-block d-lg-none">
        <a class="nav-link nav-icon search-bar-toggle" href="#">
          <i class="bi bi-search"></i>
        </a>
      </li>

      <li class="nav-item dropdown pe-3">

        <a class="nav-link nav-profile d-flex align-items-center pe-0 gap-2" href="#" data-bs-toggle="dropdown">

          <div style="
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #2563eb;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            flex-shrink: 0;
          "><?= htmlspecialchars($initials) ?></div>

          <span class="d-none d-md-block dropdown-toggle" style="font-size: 1rem; font-weight: 600; color: #333;">
            <?= htmlspecialchars($shortName) ?>
          </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6><?= htmlspecialchars($fullName) ?></h6>
            <span><?= ucfirst(htmlspecialchars($userRole)) ?></span>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>

          <li>
            <form action="../../app/controllers/adminController.php" method="post">
              <button type="submit" name="logoutButton" class="dropdown-item d-flex align-items-center">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </button>
            </form>
          </li>

        </ul>
      </li>

    </ul>
  </nav>

</header>