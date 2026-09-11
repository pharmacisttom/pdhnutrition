<?php
$pageTitle = "ผู้ป่วย Visit วันนี้ - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
use App\Helpers\DateHelper;

// Group visits by clinic
$clinicsMap = [];
foreach ($visits as $v) {
    $clinicName = trim($v['clinic'] ?: 'คลินิกทั่วไป');
    if (!isset($clinicsMap[$clinicName])) {
        $clinicsMap[$clinicName] = [];
    }
    $clinicsMap[$clinicName][] = $v;
}
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Title & Quick Search Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-calendar-day text-success me-2"></i> ผู้ป่วย OPD ที่มารับบริการวันนี้ (จำแนกตามคลินิก)
        </h3>
        <p class="text-muted mb-0">เลือกผู้ป่วยจาก Tab คลินิก หรือค้นหา HN/ชื่อ เพื่อทำการประเมินโภชนาการ (NAF)</p>
      </div>

      <div class="d-flex gap-2 mt-2 mt-md-0">
        <a href="<?= $baseUrl ?>/nutrition-queue" class="btn btn-warning fw-bold">
          <i class="fa-solid fa-user-clock me-1"></i> ไปที่คิวประเมินก่อนพบแพทย์
        </a>
      </div>
    </div>

    <!-- Quick Search Box (Requirement 2: หาคนไข้ไม่เจอที่คลินิกอื่น) -->
    <div class="card p-3 shadow-sm border-0 mb-4 bg-white border-start border-primary border-4">
      <form method="GET" action="<?= $baseUrl ?>/patients/search" class="row g-2 align-items-center">
        <div class="col-md-9">
          <div class="input-group">
            <span class="input-group-text bg-light text-pdh-blue"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="text" name="q" class="form-control form-control-lg" placeholder="ค้นหาผู้ป่วยไม่พบในคลินิก? พิมพ์ HN (เช่น 66000101), CID หรือชื่อ-นามสกุล..." required>
          </div>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
            <i class="fa-solid fa-search me-1"></i> ค้นหาคนไข้มาประเมิน
          </button>
        </div>
      </form>
    </div>

    <!-- Clinic Tabs (Requirement 1) -->
    <ul class="nav nav-pills nav-fill fw-bold mb-3 bg-white p-2 rounded shadow-sm border" id="clinicTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active py-2" id="tab-all" data-bs-toggle="tab" data-bs-target="#clinic-all" type="button">
          <i class="fa-solid fa-layer-group me-1"></i> แสดงทุกคลินิก (<?= count($visits) ?>)
        </button>
      </li>
      <?php $cIdx = 0; foreach ($clinicsMap as $cName => $cVisits): $cIdx++; ?>
        <li class="nav-item" role="presentation">
          <button class="nav-link py-2" id="tab-c<?= $cIdx ?>" data-bs-toggle="tab" data-bs-target="#clinic-c<?= $cIdx ?>" type="button">
            <i class="fa-solid fa-hospital-user me-1"></i> <?= htmlspecialchars($cName) ?> (<?= count($cVisits) ?>)
          </button>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="clinicTabContent">

      <!-- TAB ALL -->
      <div class="tab-pane fade show active" id="clinic-all">
        <div class="card shadow-sm border-0">
          <div class="card-body p-0">
            <?php renderVisitTable($visits, $baseUrl); ?>
          </div>
        </div>
      </div>

      <!-- TAB PER CLINIC -->
      <?php $cIdx = 0; foreach ($clinicsMap as $cName => $cVisits): $cIdx++; ?>
        <div class="tab-pane fade" id="clinic-c<?= $cIdx ?>">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-pdh-blue text-white fw-bold py-2">
              <i class="fa-solid fa-clinic-medical me-2"></i> <?= htmlspecialchars($cName) ?> (จำนวน <?= count($cVisits) ?> ราย)
            </div>
            <div class="card-body p-0">
              <?php renderVisitTable($cVisits, $baseUrl); ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    </div>

  </div>
</div>

<?php 
function renderVisitTable($visitList, $baseUrl) { ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 datatable">
      <thead class="table-light">
        <tr>
          <th>เวลา / Queue</th>
          <th>VN</th>
          <th>HN</th>
          <th>ชื่อ - นามสกุล</th>
          <th>อายุ</th>
          <th>แผนก / คลินิก</th>
          <th>แพทย์ผู้ตรวจ</th>
          <th>NAF ล่าสุด</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($visitList as $v): ?>
          <tr>
            <td>
              <span class="badge bg-secondary me-1"><?= htmlspecialchars($v['queue_number'] ?? 'Q-') ?></span>
              <small class="text-muted"><?= htmlspecialchars($v['visit_time'] ?? '') ?></small>
            </td>
            <td class="fw-bold"><?= htmlspecialchars($v['vn'] ?? '-') ?></td>
            <td class="fw-bold text-pdh-blue">
              <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($v['hn'] ?? '') ?>" class="text-decoration-none"><?= htmlspecialchars($v['hn'] ?? '') ?></a>
            </td>
            <td class="fw-bold"><?= htmlspecialchars($v['fullname'] ?? ($v['first_name'] ?? 'ผู้ป่วยทั่วไป')) ?></td>
            <td><?= isset($v['age']) ? htmlspecialchars($v['age']) . ' ปี' : '-' ?></td>
            <td><span class="badge bg-info-subtle text-info border border-info"><?= htmlspecialchars($v['clinic'] ?? 'คลินิกทั่วไป') ?></span></td>
            <td><?= htmlspecialchars($v['doctor'] ?? 'ไม่ระบุ') ?></td>
            <td>
              <?php if (!empty($v['last_naf_grade'])): ?>
                <span class="badge badge-naf-<?= strtolower(substr($v['last_naf_grade'], -1)) ?>">
                  <?= htmlspecialchars($v['last_naf_grade']) ?>
                </span>
              <?php else: ?>
                <span class="text-muted fs-7">ยังไม่เคยประเมิน</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <a href="<?= $baseUrl ?>/naf/create?hn=<?= $v['hn'] ?? '' ?>&vn=<?= $v['vn'] ?? '' ?>" class="btn btn-sm btn-success me-1 fw-bold">
                <i class="fa-solid fa-clipboard-check me-1"></i> เลือกประเมิน NAF
              </a>
              <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($v['hn'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-id-card me-1"></i> Profile
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php } ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
