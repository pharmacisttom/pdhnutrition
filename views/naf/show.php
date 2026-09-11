<?php
$pageTitle = "ผลการประเมิน NAF ID: " . $assessment['id'] . " - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\DateHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">ผลการประเมิน Nutrition Alert Form (NAF)</h3>
        <p class="text-muted mb-0">HN: <?= htmlspecialchars($assessment['hn']) ?> | ผู้ป่วย: <?= htmlspecialchars($assessment['fullname']) ?></p>
      </div>

      <div>
        <a href="<?= $baseUrl ?>/naf/print/<?= $assessment['id'] ?>" target="_blank" class="btn btn-secondary fw-bold me-2">
          <i class="fa-solid fa-print me-1"></i> พิมพ์แบบประเมิน NAF (Print Form)
        </a>
        <a href="<?= $baseUrl ?>/patient/<?= $assessment['hn'] ?>" class="btn btn-outline-primary">
          <i class="fa-solid fa-arrow-left me-1"></i> กลับไป Profile
        </a>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-8">
        <div class="card p-4 border-0 shadow-sm">
          <h5 class="fw-bold text-pdh-blue border-bottom pb-2 mb-3">รายละเอียดการประเมิน</h5>
          <div class="row g-3">
            <div class="col-md-4"><strong>วันที่ประเมิน:</strong> <?= DateHelper::formatThaiDate($assessment['assessment_date']) ?> <?= $assessment['assessment_time'] ?></div>
            <div class="col-md-4"><strong>น้ำหนัก:</strong> <?= $assessment['weight_kg'] ?> kg</div>
            <div class="col-md-4"><strong>ส่วนสูง:</strong> <?= $assessment['height_cm'] ?> cm</div>
            <div class="col-md-4"><strong>BMI:</strong> <?= $assessment['bmi'] ?> kg/m²</div>
            <div class="col-md-4"><strong>Albumin:</strong> <?= $assessment['albumin'] ?: '-' ?> g/dL</div>
            <div class="col-md-4"><strong>TLC:</strong> <?= $assessment['tlc'] ?: '-' ?> cells/mm³</div>
          </div>

          <h5 class="fw-bold text-pdh-blue border-bottom pb-2 mt-4 mb-3">หัวข้อคำตอบและคะแนนที่ได้</h5>
          <ul class="list-group">
            <?php foreach ($answers as $ans): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <span class="badge bg-secondary me-2">Section <?= $ans['section_number'] ?></span>
                  <strong><?= htmlspecialchars($ans['label_th'] ?: $ans['item_code']) ?></strong>
                </div>
                <span class="badge bg-danger fs-6">+<?= $ans['score_given'] ?> คะแนน</span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card p-4 border-0 shadow-sm text-center">
          <h5 class="fw-bold text-pdh-blue border-bottom pb-2">คะแนนรวม NAF Total Score</h5>
          <h1 class="display-2 fw-bold text-pdh-blue my-3"><?= $assessment['total_score'] ?></h1>
          <div class="badge badge-naf-<?= strtolower(substr($assessment['naf_grade'], -1)) ?> fs-4 py-2 w-100 mb-3">
            <?= htmlspecialchars($assessment['naf_grade']) ?>
          </div>
          <div class="fs-7 text-muted">ผู้ประเมิน: <?= htmlspecialchars($assessment['assessor_name']) ?></div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
