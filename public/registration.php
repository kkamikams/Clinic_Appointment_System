<?php session_start() ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Registration - Queue Clinic</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->

  <!-- Queue Clinic – Custom Styles -->
  <style>
    body {
      font-family: 'DM Sans', sans-serif !important;
      background: #f5f7fb !important;
    }

    .section.register {
      background: #f5f7fb;
    }

    /* ── Clinic brand above card ── */
    .queue-brand {
      text-align: center;
      margin-bottom: 1.1rem;
    }

    .queue-brand .brand-name {
      font-family: 'DM Serif Display', serif;
      font-style: italic;
      font-size: 2.3rem;
      color: #2563eb;
      letter-spacing: -.01em;
      line-height: 1;
    }

    .queue-brand .brand-sub {
      font-size: .72rem;
      color: #9ca3af;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
      margin-top: 3px;
    }

    /* ── Card ── */
    .card {
      border: 1px solid #eaecf4 !important;
      border-radius: 16px !important;
      box-shadow: 0 4px 24px rgba(37, 99, 235, .08), 0 1px 4px rgba(0, 0, 0, .05) !important;
    }

    .card-body {
      padding: 1.75rem 1.75rem 1.5rem !important;
    }

    /* ── Title & subtitle ── */
    .card-title {
      font-family: 'DM Sans', sans-serif !important;
      font-weight: 700 !important;
      color: #111827 !important;
      letter-spacing: -.025em;
    }

    .card-body .pt-4 p.text-center.small {
      color: #9ca3af;
      font-size: .82rem;
    }

    /* ── Labels ── */
    .form-label {
      font-size: .78rem !important;
      font-weight: 600 !important;
      color: #4b5563 !important;
      margin-bottom: .3rem !important;
    }

    /* ── Inputs ── */
    .form-control {
      font-family: 'DM Sans', sans-serif !important;
      font-size: .84rem !important;
      border: 1.5px solid #eaecf4 !important;
      border-radius: 10px !important;
      padding: .55rem .85rem !important;
      background: #f5f7fb !important;
      color: #111827 !important;
      transition: border-color .2s, box-shadow .2s, background .2s !important;
    }

    .form-control:focus {
      border-color: #3b82f6 !important;
      background: #fff !important;
      box-shadow: 0 0 0 3.5px rgba(37, 99, 235, .1) !important;
    }

    .form-control::placeholder {
      color: #c4c9d4 !important;
    }

    /* ── Input group (@ prefix) ── */
    .input-group-text {
      border: 1.5px solid #eaecf4 !important;
      border-right: none !important;
      border-radius: 10px 0 0 10px !important;
      background: #f5f7fb !important;
      color: #9ca3af !important;
      font-size: .85rem !important;
    }

    .input-group .form-control {
      border-left: none !important;
      border-radius: 0 10px 10px 0 !important;
    }

    .input-group:focus-within .input-group-text {
      border-color: #3b82f6 !important;
      background: #fff !important;
    }

    /* ── Checkbox ── */
    .form-check-input:checked {
      background-color: #2563eb !important;
      border-color: #2563eb !important;
    }

    .form-check-label {
      font-size: .8rem !important;
      color: #4b5563 !important;
    }

    .form-check-label a {
      color: #2563eb !important;
      font-weight: 600 !important;
      text-decoration: none !important;
    }

    .form-check-label a:hover {
      text-decoration: underline !important;
    }

    /* ── Primary button ── */
    .btn-primary {
      font-family: 'DM Sans', sans-serif !important;
      font-weight: 700 !important;
      font-size: .87rem !important;
      letter-spacing: .02em !important;
      background: #2563eb !important;
      border-color: #2563eb !important;
      border-radius: 10px !important;
      padding: .6rem 1rem !important;
      transition: background .15s, box-shadow .15s, transform .1s !important;
    }

    .btn-primary:hover {
      background: #1d4ed8 !important;
      border-color: #1d4ed8 !important;
      box-shadow: 0 4px 14px rgba(37, 99, 235, .32) !important;
      transform: translateY(-1px) !important;
    }

    .btn-primary:active {
      transform: translateY(0) !important;
    }

    /* ── Footer link ── */
    .card-body p.small {
      color: #9ca3af !important;
    }

    .card-body p.small a {
      color: #2563eb !important;
      font-weight: 600 !important;
      text-decoration: none !important;
    }

    .card-body p.small a:hover {
      text-decoration: underline !important;
    }

    /* ── Validation ── */
    .invalid-feedback {
      font-size: .72rem !important;
    }
  </style>

</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <!-- Queue Clinic Brand -->
              <div class="queue-brand">
                <div class="brand-name">Queue</div>
                <div class="brand-sub">Clinic Management</div>
              </div>

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>
                    <p class="text-center small">Enter your personal details to create account</p>
                  </div>

                  <form class="row g-3 needs-validation" action="../app/controllers/LoginController.php" method="post" enctype="multipart/form-data" autocomplete="off" novalidate>
                    <div class="col-12">
                      <div class="row">
                        <div class="col-6">
                          <label for="yourName" class="form-label">First Name <span style="color: red;">*</span></label>
                          <input type="text" name="firstName" class="form-control" id="yourName" placeholder="Juan" required>
                          <div class="invalid-feedback">Please, enter your first name!</div>
                        </div>
                        <div class="col-6">
                          <label for="yourName" class="form-label">Middle Name</label>
                          <input type="text" name="middleName" class="form-control" id="yourName" placeholder="Santos">
                          <div class="invalid-feedback">Please, enter your middle name!</div>
                        </div>
                        <div class="col-12 mt-2">
                          <label for="yourName" class="form-label">Last Name <span style="color: red;">*</span></label>
                          <input type="text" name="lastName" class="form-control" id="yourName" placeholder="Dela Cruz" required>
                          <div class="invalid-feedback">Please, enter your last name!</div>
                        </div>
                        <div class="col-12 mt-2">
                          <label for="yourEmail" class="form-label">Email Address <span style="color: red;">*</span></label>
                          <input type="email" name="emailAddress" class="form-control" id="yourEmail"
                            placeholder="juan.delacruz@example.com"
                            autocomplete="off" required>
                          <div class="invalid-feedback">Please enter a valid email adddress!</div>
                        </div>
                        <div class="col-12 mt-2">
                          <label for="yourUsername" class="form-label">Username <span style="color: red;">*</span></label>
                          <div class="input-group has-validation">
                            <span class="input-group-text" id="inputGroupPrepend">@</span>
                            <input type="text" name="username" class="form-control" id="yourUsername" placeholder="juan_delacruz" required>
                            <div class="invalid-feedback">Please choose a username.</div>
                          </div>

                        </div>
                        <div class="col-12 mt-2">
                          <div class="row">
                            <div class="col-6">
                              <label for="yourPhone" class="form-label">Password <span style="color: red;">*</span></label>
                              <input type="password" name="password" class="form-control" id="yourPhone" placeholder="Enter password" required>
                              <div class="invalid-feedback">Please, enter your password!</div>
                            </div>
                            <div class="col-6">
                              <label for="yourAddress" class="form-label">Confirm Password <span style="color: red;">*</span></label>
                              <input type="password" name="confirmPassword" class="form-control" id="yourAddress" placeholder="Confirm password" required>
                              <div class="invalid-feedback">Please, confirm your password!</div>
                            </div>

                            <div class="col-12 mt-2">
                              <div class="col-12">
                                <label for="yourAddress" class="form-label">Street <span style="color: red;">*</span></label>
                                <input type="text" name="street" class="form-control" id="yourAddress" placeholder="Enter street" required>
                                <div class="invalid-feedback">Please, enter your street!</div>
                              </div>
                              <div class="col-12 mt-2">
                                <div class="col-12">
                                  <label for="yourAddress" class="form-label">Barangay <span style="color: red;">*</span></label>
                                  <input type="text" name="barangay" class="form-control" id="yourAddress" placeholder="Enter barangay" required>
                                  <div class="invalid-feedback">Please, enter your barangay!</div>
                                </div>
                              </div>
                              <div class="col-12 mt-2">
                                <div class="col-12">
                                  <label for="yourAddress" class="form-label">City <span style="color: red;">*</span></label>
                                  <input type="text" name="city" class="form-control" id="yourAddress" placeholder="Enter city" required>
                                  <div class="invalid-feedback">Please, enter your city!</div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-12 mt-2">
                            <div class="form-check">
                              <input class="form-check-input" name="terms" type="checkbox" value="" id="acceptTerms" required>
                              <label class="form-check-label" for="acceptTerms">I agree and accept the <a href="#">terms and conditions</a></label>
                              <div class="invalid-feedback">You must agree before submitting.</div>
                            </div>
                          </div>
                          <div class="col-12 mt-2">
                            <button class="btn btn-primary w-100" name="registerButton" type="submit">Create Account</button>
                          </div>
                          <div class="col-12 mt-2">
                            <!-- Fixed: was "login", matches controller redirect route "login" -->
                            <p class="small mb-0">Already have an account? <a href="login">Log in</a></p>
                          </div>
                  </form>

                </div>
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

  <?php
  if (isset($_SESSION['message']) && $_SESSION['code'] != '') {
  ?>
    <script>
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.onmouseenter = Swal.stopTimer
          toast.onmouseleave = Swal.resumeTimer
        }
      });
      Toast.fire({
        icon: "<?php echo $_SESSION['code']; ?>",
        title: "<?php echo $_SESSION['message']; ?>"
      });
    </script>
  <?php
    unset($_SESSION['message']);
    unset($_SESSION['code']);
  }
  ?>

</body>

</html>