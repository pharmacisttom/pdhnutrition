<?php
$pageTitle = "HIS API Gateway Test - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Config\AppConfig;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="mb-4">
      <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-network-wired me-2"></i> PDH API Gateway Diagnostic & Test Page</h3>
      <p class="text-muted mb-0">ทดสอบการดึงข้อมูลจาก HIMPRO HIS ผ่าน PDH API Gateway ( Base URL: <code><?= AppConfig::get('PDH_API_BASE_URL') ?></code> )</p>
    </div>

    <div class="card p-4 shadow-sm border-0 mb-4 bg-white">
      <h5 class="fw-bold text-pdh-blue border-bottom pb-2 mb-3">ทดสอบเรียก API Endpoints</h5>

      <div class="row g-3 align-items-center mb-3">
        <div class="col-md-4">
          <select id="apiEndpoint" class="form-select">
            <option value="visits">GET /visits/today (ผู้ป่วยมา Visit วันนี้)</option>
            <option value="patient">GET /patient/{hn} (ข้อมูลผู้ป่วย)</option>
            <option value="labs">GET /lab/{hn}/latest (ผล Lab ล่าสุด)</option>
            <option value="diagnosis">GET /diagnosis/{hn} (การวินิจฉัยโรค)</option>
            <option value="allergies">GET /allergy/{hn} (ประวัติแพ้ยา)</option>
          </select>
        </div>

        <div class="col-md-3">
          <input type="text" id="apiParam" class="form-control" placeholder="HN (เช่น 66000101)" value="66000101">
        </div>

        <div class="col-md-3">
          <button onclick="testApi()" class="btn btn-primary fw-bold px-4">
            <i class="fa-solid fa-paper-plane me-1"></i> Send Request
          </button>
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label fw-bold">JSON Response Output:</label>
        <pre id="jsonOutput" class="bg-dark text-success p-3 rounded" style="max-height: 400px; overflow-y: auto;">// กด Send Request เพื่อทดสอบการดึงข้อมูล API</pre>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
function testApi() {
  const ep = $('#apiEndpoint').val();
  const param = $('#apiParam').val();

  $('#jsonOutput').text('Loading API response...');

  $.ajax({
    url: '<?= $baseUrl ?>/api/test-endpoint?endpoint=' + ep + '&param=' + param,
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      $('#jsonOutput').text(JSON.stringify(res, null, 2));
    },
    error: function(xhr) {
      $('#jsonOutput').text(JSON.stringify(xhr.responseJSON || { error: 'Failed to connect' }, null, 2));
    }
  });
}
</script>
