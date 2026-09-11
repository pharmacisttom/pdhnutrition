<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::get('APP_URL', '/pdhnutrition');
?>
</div> <!-- End Wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
$(document).ready(function() {
  if ($.fn.DataTable) {
    $('.datatable').each(function() {
      var $table = $(this);
      // Remove manual colspan rows inside tbody that break DataTables cell indexing
      $table.find('tbody tr').each(function() {
        if ($(this).children('td[colspan]').length > 0) {
          $(this).remove();
        }
      });

      $table.DataTable({
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
        },
        pageLength: 25,
        autoWidth: false,
        retrieve: true
      });
    });
  }
});
</script>
</body>
</html>
