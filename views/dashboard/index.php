<?php
$pageTitle = "Executive Dashboard - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">
    
    <!-- Top Status Banner -->
    <?php if (!empty($stats['api_warning'])): ?>
      <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
        <strong>แจ้งเตือนการเชื่อมต่อ HIS:</strong> <?= htmlspecialchars($stats['api_warning']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">Dashboard บริหารจัดการภาวะโภชนาการ</h3>
        <p class="text-muted mb-0">ข้อมูลสรุปประจำวันที่ <?= \App\Helpers\DateHelper::formatThaiDate(date('Y-m-d')) ?> | แหล่งข้อมูล: <?= htmlspecialchars($stats['api_source']) ?></p>
      </div>
      <a href="<?= $baseUrl ?>/nutrition-queue" class="btn btn-warning btn-lg fw-bold shadow-sm">
        <i class="fa-solid fa-user-clock me-2"></i> คิวประเมินก่อนพบแพทย์ (<?= $stats['queue_waiting'] ?>)
      </a>
    </div>

    <!-- Executive Stat Cards (Row 1) -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card bg-white border-start border-primary border-4 p-3 h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted fs-7">ผู้ป่วยมา Visit วันนี้ (HIS)</div>
              <h2 class="fw-bold text-primary mb-0"><?= number_format($stats['today_visits']) ?></h2>
            </div>
            <div class="bg-primary-subtle p-3 rounded-circle text-primary"><i class="fa-solid fa-users fa-2x"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card bg-white border-start border-warning border-4 p-3 h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted fs-7">รอประเมินก่อนพบแพทย์</div>
              <h2 class="fw-bold text-warning mb-0"><?= number_format($stats['queue_waiting']) ?></h2>
            </div>
            <div class="bg-warning-subtle p-3 rounded-circle text-warning"><i class="fa-solid fa-clock fa-2x"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card bg-white border-start border-success border-4 p-3 h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted fs-7">ประเมินแล้ววันนี้ (Ready)</div>
              <h2 class="fw-bold text-success mb-0"><?= number_format($stats['queue_completed']) ?></h2>
            </div>
            <div class="bg-success-subtle p-3 rounded-circle text-success"><i class="fa-solid fa-circle-check fa-2x"></i></div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card bg-white border-start border-danger border-4 p-3 h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-muted fs-7">ผู้ป่วยเสี่ยงสูง (High Risk)</div>
              <h2 class="fw-bold text-danger mb-0"><?= number_format($stats['high_risk']) ?></h2>
            </div>
            <div class="bg-danger-subtle p-3 rounded-circle text-danger"><i class="fa-solid fa-triangle-exclamation fa-2x"></i></div>
          </div>
        </div>
      </div>
    </div>

    <!-- NAF Risk Cards (Row 2) -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card bg-success text-white p-3 shadow-sm">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-6 fw-bold">NAF A (Normal - Mild)</div>
              <h2 class="fw-bold mb-0"><?= number_format($stats['naf_a']) ?> ราย</h2>
            </div>
            <i class="fa-solid fa-shield-heart fa-3x opacity-75"></i>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card bg-warning text-white p-3 shadow-sm">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-6 fw-bold">NAF B (Moderate Malnutrition)</div>
              <h2 class="fw-bold mb-0"><?= number_format($stats['naf_b']) ?> ราย</h2>
            </div>
            <i class="fa-solid fa-triangle-exclamation fa-3x opacity-75"></i>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card bg-danger text-white p-3 shadow-sm">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="fs-6 fw-bold">NAF C (Severe Malnutrition)</div>
              <h2 class="fw-bold mb-0"><?= number_format($stats['naf_c']) ?> ราย</h2>
            </div>
            <i class="fa-solid fa-skull-crossbones fa-3x opacity-75"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
      <div class="col-md-5">
        <div class="card h-100 p-3">
          <h5 class="fw-bold text-pdh-blue mb-3"><i class="fa-solid fa-chart-pie me-2"></i> สัดส่วน NAF Grade ประจำเดือน</h5>
          <div style="height: 260px;">
            <canvas id="nafDonutChart"></canvas>
          </div>
        </div>
      </div>

      <div class="col-md-7">
        <div class="card h-100 p-3">
          <h5 class="fw-bold text-pdh-blue mb-3"><i class="fa-solid fa-chart-column me-2"></i> จำนวนการประเมินแยกตามแผนก/คลินิก</h5>
          <div style="height: 260px;">
            <canvas id="clinicBarChart"></canvas>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<script>
$(document).ready(function() {
  // Donut Chart
  const ctxDonut = document.getElementById('nafDonutChart').getContext('2d');
  new Chart(ctxDonut, {
    type: 'doughnut',
    data: {
      labels: ['NAF A (เขียว)', 'NAF B (เหลือง)', 'NAF C (แดง)'],
      datasets: [{
        data: [<?= $stats['naf_a'] ?>, <?= $stats['naf_b'] ?>, <?= $stats['naf_c'] ?>],
        backgroundColor: ['#06d6a0', '#f77f00', '#d62828']
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });

  // Fetch Analytics API for Clinic Bar Chart
  $.ajax({
    url: '<?= $baseUrl ?>/api/dashboard/analytics',
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        const labels = res.clinics.map(c => c.clinic);
        const dataValues = res.clinics.map(c => c.cnt);

        const ctxBar = document.getElementById('clinicBarChart').getContext('2d');
        new Chart(ctxBar, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'จำนวนคิวประเมิน',
              data: dataValues,
              backgroundColor: '#0d3b66'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: { beginAtZero: true }
            }
          }
        });
      }
    }
  });
});
</script>
