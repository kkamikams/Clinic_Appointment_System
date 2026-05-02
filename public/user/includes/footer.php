</main><!-- End #main -->

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
  <div class="copyright">
    &copy; Copyright <strong><span>NiceAdmin</span></strong>. All Rights Reserved
  </div>
  <div class="credits">
    Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
  </div>
</footer><!-- End Footer -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="<?= $base ?>assets/vendor/apexcharts/apexcharts.min.js"></script>
<script src="<?= $base ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>assets/vendor/chart.js/chart.umd.js"></script>
<script src="<?= $base ?>assets/vendor/echarts/echarts.min.js"></script>
<script src="<?= $base ?>assets/vendor/quill/quill.js"></script>
<script src="<?= $base ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
<script src="<?= $base ?>assets/vendor/tinymce/tinymce.min.js"></script>
<script src="<?= $base ?>assets/vendor/php-email-form/validate.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?= $base ?>assets/js/main.js"></script>

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