<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::routeBase();
$assetUrl = AppConfig::get('APP_URL', '/pdhnutrition');
?>
<footer class="footer mt-auto py-3 bg-white border-top text-center text-muted no-print">
  <div class="container-fluid fs-7">
    <span>PDH Nutrition System v1.0 &copy; <?= date('Y') ?> โรงพยาบาลปลวกแดง | พัฒนาโดย <strong class="text-primary">tomvis</strong></span>
  </div>
</footer>
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
  // Keep drawer visibility and keyboard access in sync with the viewport.
  var mobileMenu = window.matchMedia('(max-width: 991.98px)');
  var $sidebar = $('#sidebar-wrapper');
  var $backdrop = $('<button type="button" class="sidebar-backdrop no-print" aria-label="ปิดเมนู" hidden></button>').appendTo('body');
  function syncMenu() {
    var toggled = $('#wrapper').hasClass('toggled');
    var visible = mobileMenu.matches ? toggled : !toggled;
    $sidebar.prop('inert', !visible);
    $('#menu-toggle').attr('aria-expanded', String(visible));
    $backdrop.prop('hidden', !(mobileMenu.matches && visible));
    $('body').toggleClass('sidebar-open', mobileMenu.matches && visible);
  }
  function closeMenu() {
    $('#wrapper').removeClass('toggled');
    syncMenu();
    $('#menu-toggle').trigger('focus');
  }
  $('#sidebar-close').on('click', closeMenu);
  $backdrop.on('click', closeMenu);
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && mobileMenu.matches && $('#wrapper').hasClass('toggled')) closeMenu();
  });
  mobileMenu.addEventListener('change', function() {
    $('#wrapper').removeClass('toggled');
    syncMenu();
  });
  syncMenu();

  // Sidebar Toggle Event
  $('#menu-toggle').on('click', function(e) {
    e.preventDefault();
    $('#wrapper').toggleClass('toggled');
    syncMenu();
    if (mobileMenu.matches && $('#wrapper').hasClass('toggled')) {
      setTimeout(function() {
        if (mobileMenu.matches && $('#wrapper').hasClass('toggled')) $('#sidebar-close').trigger('focus');
      }, 260);
    }
    setTimeout(function() {
      if ($.fn.DataTable) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
      }
    }, 300);
  });

  // Window Resize Event for Tables
  $(window).on('resize', function() {
    if ($.fn.DataTable) {
      $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    }
  });

  if ($.fn.DataTable) {
    $('.datatable').each(function() {
      var $table = $(this);
      $table.addClass('w-100');

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
