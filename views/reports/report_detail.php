<?php
$pageTitle = htmlspecialchars($reportTitle) . " - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
use App\Helpers\DateHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-file-lines me-2"></i> <?= htmlspecialchars($reportTitle) ?></h3>
        <p class="text-muted mb-0">โรงพยาบาลปลวกแดง (Pluakdaeng Hospital) | ออกรายงาน ณ วันที่ <?= DateHelper::formatThaiDate(date('Y-m-d')) ?></p>
      </div>

      <div class="no-print d-flex gap-2">
        <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="btn btn-success fw-bold">
          <i class="fa-solid fa-file-excel me-1"></i> Export Excel (Unicode)
        </a>
        <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'csv'])) ?>" class="btn btn-outline-success fw-bold">
          <i class="fa-solid fa-file-csv me-1"></i> Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-secondary fw-bold">
          <i class="fa-solid fa-print me-1"></i> พิมพ์รายงาน (A4)
        </button>
        <a href="<?= $baseUrl ?>/reports" class="btn btn-outline-primary">
          <i class="fa-solid fa-arrow-left me-1"></i> ย้อนกลับ
        </a>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="card p-3 mb-4 bg-white shadow-sm border-0 no-print">
      <form method="GET" class="row g-3">
        <div class="col-md-3">
          <label class="form-label fw-bold">เลือกวันที่</label>
          <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-bold">เลือกเดือน</label>
          <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month) ?>" onchange="this.form.submit()">
        </div>

        <?php if ($reportCode === 'naf_classification'): ?>
          <div class="col-md-3">
            <label class="form-label fw-bold">ระดับ NAF Grade</label>
            <select name="grade" class="form-select" onchange="this.form.submit()">
              <option value="">-- แสดงทุกระดับ (A+B+C) --</option>
              <option value="NAF A" <?= ($grade === 'NAF A') ? 'selected' : '' ?>>NAF A Only</option>
              <option value="NAF B" <?= ($grade === 'NAF B') ? 'selected' : '' ?>>NAF B Only</option>
              <option value="NAF C" <?= ($grade === 'NAF C') ? 'selected' : '' ?>>NAF C Only</option>
            </select>
          </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Special Compliance Summary Card for Monthly Report -->
    <?php if ($reportCode === 'monthly_summary' && is_array($reportData) && isset($reportData['compliance_pct'])): ?>
      <div class="card p-4 mb-4 bg-pdh-blue text-white shadow-sm border-0">
        <div class="row align-items-center text-center">
          <div class="col-md-3 border-end border-secondary">
            <div class="fs-7 text-white-50">จำนวนประเมินรวมประจำเดือน</div>
            <h1 class="display-4 fw-bold mb-0"><?= number_format($reportData['total_assessments']) ?></h1>
          </div>
          <div class="col-md-3 border-end border-secondary">
            <div class="fs-7 text-white-50">NAF C (Severe)</div>
            <h1 class="display-4 fw-bold text-danger mb-0"><?= number_format($reportData['naf_c_count']) ?></h1>
          </div>
          <div class="col-md-3 border-end border-secondary">
            <div class="fs-7 text-white-50">คิวต้องประเมินก่อนพบแพทย์</div>
            <h1 class="display-4 fw-bold text-warning mb-0"><?= number_format($reportData['pre_doctor_required']) ?></h1>
          </div>
          <div class="col-md-3">
            <div class="fs-7 text-white-50">Pre-Doctor Compliance Rate</div>
            <h1 class="display-4 fw-bold text-success mb-0"><?= $reportData['compliance_pct'] ?>%</h1>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Generic Data Table -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <?php if (!empty($reportData) && is_array($reportData) && isset($reportData[0])): ?>
                <tr>
                  <th>#</th>
                  <?php foreach (array_keys($reportData[0]) as $col): ?>
                    <th><?= htmlspecialchars(str_replace('_', ' ', strtoupper($col))) ?></th>
                  <?php endforeach; ?>
                </tr>
              <?php endif; ?>
            </thead>
            <tbody>
              <?php if (empty($reportData) || (is_array($reportData) && isset($reportData[0]) === false && empty($reportData))): ?>
                <tr>
                  <td colspan="15" class="text-center py-4 text-muted">ไม่พบข้อมูลรายงานสำหรับเงื่อนไขที่เลือก</td>
                </tr>
              <?php elseif (isset($reportData[0])): ?>
                <?php foreach ($reportData as $idx => $row): ?>
                  <tr>
                    <td><?= $idx + 1 ?></td>
                    <?php foreach ($row as $key => $val): ?>
                      <td>
                        <?php if (strpos($key, 'grade') !== false && $val): ?>
                          <span class="badge badge-naf-<?= strtolower(substr($val, -1)) ?>"><?= htmlspecialchars($val) ?></span>
                        <?php elseif ($key === 'cid'): ?>
                          <?= SanitizerHelper::maskCid($val) ?>
                        <?php else: ?>
                          <?= htmlspecialchars((string)$val) ?>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
