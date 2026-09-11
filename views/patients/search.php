<?php
$pageTitle = "ค้นหาผู้ป่วย - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">
    
    <div class="mb-4">
      <h3 class="fw-bold text-pdh-blue"><i class="fa-solid fa-magnifying-glass me-2"></i> ค้นหาข้อมูลผู้ป่วย</h3>
      <p class="text-muted">ค้นหาผู้ป่วยในระบบด้วย HN, CID หรือชื่อ-นามสกุล</p>
    </div>

    <!-- Search Form Card -->
    <div class="card p-4 shadow-sm border-0 mb-4 bg-white">
      <form method="GET" action="<?= $baseUrl ?>/patients/search">
        <div class="input-group input-group-lg">
          <input type="text" name="q" class="form-control" placeholder="พิมพ์ HN (เช่น 66000101), CID หรือชื่อผู้ป่วย..." value="<?= htmlspecialchars($query) ?>" autofocus required>
          <button class="btn btn-primary fw-bold px-4" type="submit">
            <i class="fa-solid fa-search me-1"></i> ค้นหา
          </button>
        </div>
      </form>
    </div>

    <!-- Search Results -->
    <?php if (!empty($query)): ?>
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold py-3">
          ผลการค้นหาสำหรับ "<?= htmlspecialchars($query) ?>" (พบ <?= count($results) ?> รายการ)
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
              <thead class="table-light">
                <tr>
                  <th>HN</th>
                  <th>CID</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>เพศ / อายุ</th>
                  <th>น้ำหนัก/ส่วนสูง</th>
                  <th>BMI</th>
                  <th>NAF ล่าสุด</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($results)): ?>
                  <tr>
                    <td colspan="8" class="text-center py-4 text-muted">ไม่พบข้อมูลผู้ป่วยที่ตรงตามเงื่อนไข</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($results as $p): ?>
                    <tr>
                      <td class="fw-bold text-pdh-blue"><?= htmlspecialchars($p['hn']) ?></td>
                      <td><?= SanitizerHelper::maskCid($p['cid']) ?></td>
                      <td class="fw-bold"><?= htmlspecialchars($p['fullname']) ?></td>
                      <td><?= htmlspecialchars($p['gender']) ?> / <?= $p['age'] ?> ปี</td>
                      <td><?= $p['weight'] ?: '-' ?> kg / <?= $p['height'] ?: '-' ?> cm</td>
                      <td><span class="badge bg-light text-dark border"><?= $p['bmi'] ?: '-' ?></span></td>
                      <td>
                        <?php if ($p['last_naf_grade']): ?>
                          <span class="badge badge-naf-<?= strtolower(substr($p['last_naf_grade'], -1)) ?>"><?= htmlspecialchars($p['last_naf_grade']) ?></span>
                        <?php else: ?>
                          <span class="text-muted fs-7">ยังไม่เคยประเมิน</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($p['hn']) ?>" class="btn btn-sm btn-primary">
                          <i class="fa-solid fa-id-card me-1"></i> ดูประวัติผู้ป่วย
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
