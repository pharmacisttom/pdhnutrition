<?php
$pageTitle = "ระบบค้นหาข้อมูลผู้ป่วยขั้นสูง - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
use App\Helpers\DateHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">
    
    <!-- Title & Quick Nav -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-hospital-user text-primary me-2"></i> ระบบค้นหาผู้ป่วยและติดตามโภชนาการขั้นสูง (Clinical Search)
        </h3>
        <p class="text-muted mb-0">ค้นหาข้อมูลผู้ป่วยจากระบบ HIS (HIMPRO Live), ประวัติ NAF, ผล Lab และกลุ่มติดตามโภชนาการแบบจำแนกเงื่อนไข</p>
      </div>
      <div>
        <a href="<?= $baseUrl ?>/patients/today" class="btn btn-outline-primary fw-bold">
          <i class="fa-solid fa-calendar-day me-1"></i> ผู้ป่วย Visit วันนี้
        </a>
      </div>
    </div>

    <!-- Main Advanced Multi-Criteria Filter Panel -->
    <div class="card p-4 shadow-sm border-0 mb-4 bg-white border-start border-primary border-4">
      <form method="GET" action="<?= $baseUrl ?>/patients/search" id="advancedSearchForm">
        <div class="row g-3">
          
          <!-- Keyword Input -->
          <div class="col-md-5">
            <label class="form-label fw-bold text-pdh-blue"><i class="fa-solid fa-magnifying-glass me-1"></i> คำค้นหา (Keyword):</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-pdh-blue"><i class="fa-solid fa-id-card"></i></span>
              <input type="text" name="q" class="form-control form-control-lg" placeholder="พิมพ์ HN (เช่น 0008855), CID (13 หลัก), หรือชื่อ-นามสกุล..." value="<?= htmlspecialchars($query ?? '') ?>" autofocus>
            </div>
          </div>

          <!-- Patient Type Filter -->
          <div class="col-md-2">
            <label class="form-label fw-bold text-pdh-blue"><i class="fa-solid fa-hospital me-1"></i> ประเภทผู้ป่วย:</label>
            <select name="type" class="form-select form-select-lg">
              <option value="ALL" <?= ($typeFilter === 'ALL') ? 'selected' : '' ?>>แสดงทุกประเภท</option>
              <option value="OPD" <?= ($typeFilter === 'OPD') ? 'selected' : '' ?>>ผู้ป่วยนอก (OPD)</option>
              <option value="IPD" <?= ($typeFilter === 'IPD') ? 'selected' : '' ?>>ผู้ป่วยใน (IPD)</option>
              <option value="REGISTRY" <?= ($typeFilter === 'REGISTRY') ? 'selected' : '' ?>>กลุ่มติดตาม (Registry)</option>
            </select>
          </div>

          <!-- NAF Grade Filter -->
          <div class="col-md-2">
            <label class="form-label fw-bold text-pdh-blue"><i class="fa-solid fa-notes-medical me-1"></i> NAF Risk Grade:</label>
            <select name="naf" class="form-select form-select-lg">
              <option value="ALL" <?= ($nafFilter === 'ALL') ? 'selected' : '' ?>>ทุกระดับความเสี่ยง</option>
              <option value="NAF A" <?= ($nafFilter === 'NAF A') ? 'selected' : '' ?>>NAF A (Low Risk)</option>
              <option value="NAF B" <?= ($nafFilter === 'NAF B') ? 'selected' : '' ?>>NAF B (Moderate)</option>
              <option value="NAF C" <?= ($nafFilter === 'NAF C') ? 'selected' : '' ?>>NAF C (Severe)</option>
              <option value="UNASSESSED" <?= ($nafFilter === 'UNASSESSED') ? 'selected' : '' ?>>ยังไม่เคยประเมิน NAF</option>
            </select>
          </div>

          <!-- Lab Indicator Filter -->
          <div class="col-md-2">
            <label class="form-label fw-bold text-pdh-blue"><i class="fa-solid fa-weight-scale me-1"></i> ดัชนีโภชนาการ:</label>
            <select name="lab" class="form-select form-select-lg">
              <option value="ALL" <?= ($labFilter === 'ALL') ? 'selected' : '' ?>>ทั้งหมด</option>
              <option value="LOW_BMI" <?= ($labFilter === 'LOW_BMI') ? 'selected' : '' ?>>BMI &lt; 18.5 (ผอมเสี่ยง)</option>
            </select>
          </div>

          <!-- Submit Button -->
          <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
              <i class="fa-solid fa-filter me-1"></i> กรอง
            </button>
          </div>

        </div>
      </form>

      <!-- Quick Preset Filter Buttons -->
      <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top align-items-center">
        <small class="text-muted fw-bold me-2">ตัวกรองทางคลินิกรวดเร็ว (Quick Presets):</small>
        <a href="<?= $baseUrl ?>/patients/search?type=IPD" class="btn btn-sm btn-outline-info rounded-pill <?= ($typeFilter==='IPD')?'active':'' ?>">
          <i class="fa-solid fa-bed me-1"></i> ผู้ป่วยใน IPD
        </a>
        <a href="<?= $baseUrl ?>/patients/search?naf=NAF+C" class="btn btn-sm btn-outline-danger rounded-pill <?= ($nafFilter==='NAF C')?'active':'' ?>">
          <i class="fa-solid fa-triangle-exclamation me-1"></i> NAF C (เสี่ยงสูงรุนแรง)
        </a>
        <a href="<?= $baseUrl ?>/patients/search?type=REGISTRY" class="btn btn-sm btn-outline-warning text-dark rounded-pill <?= ($typeFilter==='REGISTRY')?'active':'' ?>">
          <i class="fa-solid fa-clipboard-check me-1"></i> อยู่ใน Nutrition Registry
        </a>
        <a href="<?= $baseUrl ?>/patients/search?lab=LOW_BMI" class="btn btn-sm btn-outline-secondary rounded-pill <?= ($labFilter==='LOW_BMI')?'active':'' ?>">
          <i class="fa-solid fa-weight-scale me-1"></i> BMI &lt; 18.5 kg/m²
        </a>
        <a href="<?= $baseUrl ?>/patients/search?q=" class="btn btn-sm btn-link text-decoration-none text-muted">
          <i class="fa-solid fa-rotate-left me-1"></i> ล้างตัวกรอง
        </a>
      </div>
    </div>

    <!-- Search Results Header Card -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-pdh-blue">
          <i class="fa-solid fa-list-check text-success me-2"></i> ผลการค้นหาและติดตามผู้ป่วย
          <span class="badge bg-primary ms-2"><?= count($results) ?> รายการ</span>
        </h5>
        <small class="text-muted">ข้อมูลซิงค์ตรงจากฐานข้อมูล HIS โรงพยาบาลปลวกแดง</small>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th>HN / CID</th>
                <th>ชื่อ - นามสกุล</th>
                <th>เพศ / อายุ</th>
                <th>แผนก / คลินิก / วอร์ด</th>
                <th>น้ำหนัก / ส่วนสูง / BMI</th>
                <th>NAF Assessment ล่าสุด</th>
                <th>กลุ่มติดตาม (Registry)</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($results)): ?>
                <?php foreach ($results as $p): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-pdh-blue fs-6">
                        <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($p['hn']) ?>" class="text-decoration-none">
                          <?= htmlspecialchars($p['hn']) ?>
                        </a>
                      </div>
                      <small class="text-muted"><?= SanitizerHelper::maskCid($p['cid'] ?? '') ?></small>
                    </td>

                    <td>
                      <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($p['fullname']) ?></div>
                      <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($p['phone'] ?? '-') ?></small>
                    </td>

                    <td>
                      <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($p['gender'] ?? '-') ?></span>
                      <span><?= $p['age'] ?? '-' ?> ปี</span>
                    </td>

                    <td>
                      <?php if (!empty($p['last_clinic'])): ?>
                        <span class="badge bg-info-subtle text-info border border-info">
                          <?= htmlspecialchars($p['last_clinic']) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted fs-7">ผู้ป่วยลงทะเบียน OPD/IPD</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <div>
                        <strong><?= $p['weight'] ? $p['weight'] . ' kg' : '-' ?></strong> / 
                        <span><?= $p['height'] ? $p['height'] . ' cm' : '-' ?></span>
                      </div>
                      <?php if (!empty($p['bmi'])): ?>
                        <span class="badge bg-<?= ($p['bmi'] < 18.5) ? 'warning text-dark' : (($p['bmi'] >= 25) ? 'info' : 'success') ?> mt-1">
                          BMI: <?= $p['bmi'] ?>
                        </span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <?php if (!empty($p['last_naf_grade'])): ?>
                        <span class="badge badge-naf-<?= strtolower(substr($p['last_naf_grade'], -1)) ?> fs-6">
                          <?= htmlspecialchars($p['last_naf_grade']) ?> (<?= $p['last_naf_score'] ?? '-' ?>)
                        </span>
                        <div class="fs-8 text-muted mt-1">
                          <?= DateHelper::formatThaiDate($p['last_naf_date'] ?? date('Y-m-d'), true) ?>
                        </div>
                      <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border">ยังไม่เคยประเมิน</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <?php if (!empty($p['registry_risk'])): ?>
                        <span class="badge bg-<?= ($p['registry_risk'] === 'SEVERE' || $p['registry_risk'] === 'HIGH') ? 'danger' : 'warning text-dark' ?>">
                          <i class="fa-solid fa-clipboard-check me-1"></i> <?= htmlspecialchars($p['registry_risk']) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted fs-7">-</span>
                      <?php endif; ?>
                    </td>

                    <td class="text-center">
                      <div class="btn-group">
                        <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($p['hn']) ?>" class="btn btn-sm btn-primary fw-bold">
                          <i class="fa-solid fa-id-card me-1"></i> ดูประวัติ
                        </a>
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                          <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                          <li>
                            <a class="dropdown-menu-item text-success fw-bold p-2 text-decoration-none d-block" href="<?= $baseUrl ?>/naf/create?hn=<?= $p['hn'] ?>">
                              <i class="fa-solid fa-clipboard-check me-2"></i> ประเมิน NAF ใหม่
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-menu-item text-primary fw-bold p-2 text-decoration-none d-block" href="<?= $baseUrl ?>/diet/create?hn=<?= $p['hn'] ?>">
                              <i class="fa-solid fa-utensils me-2"></i> สั่ง Diet Order
                            </a>
                          </li>
                          <li>
                            <a class="dropdown-menu-item text-info fw-bold p-2 text-decoration-none d-block" href="<?= $baseUrl ?>/notes/create?hn=<?= $p['hn'] ?>">
                              <i class="fa-solid fa-notes-medical me-2"></i> เขียน SOAP Note
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>

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
