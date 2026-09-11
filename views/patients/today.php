<?php
$pageTitle = "ผู้ป่วย Visit วันนี้ - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-calendar-day text-success me-2"></i> ผู้ป่วย OPD ที่มา Visit โรงพยาบาลปลวกแดง วันนี้
        </h3>
        <p class="text-muted mb-0">ข้อมูลดึงตรงจาก HIMPRO HIS API ( visits_cache / gateway )</p>
      </div>

      <a href="<?= $baseUrl ?>/nutrition-queue" class="btn btn-warning fw-bold">
        <i class="fa-solid fa-user-clock me-1"></i> ไปที่คิวประเมินก่อนพบแพทย์
      </a>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-pdh-blue text-white py-3">
        <i class="fa-solid fa-hospital-user me-2"></i> รายการ Visit วันนี้ทั้งหมด (<?= count($visits) ?> รายการ)
      </div>
      <div class="card-body p-0">
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
              <?php foreach ($visits as $v): ?>
                <tr>
                  <td>
                    <span class="badge bg-secondary me-1"><?= htmlspecialchars($v['queue_number'] ?: 'Q-') ?></span>
                    <small class="text-muted"><?= htmlspecialchars($v['visit_time']) ?></small>
                  </td>
                  <td class="fw-bold"><?= htmlspecialchars($v['vn']) ?></td>
                  <td class="fw-bold text-pdh-blue"><?= htmlspecialchars($v['hn']) ?></td>
                  <td class="fw-bold"><?= htmlspecialchars($v['fullname']) ?></td>
                  <td><?= $v['age'] ?> ปี</td>
                  <td><?= htmlspecialchars($v['clinic']) ?></td>
                  <td><?= htmlspecialchars($v['doctor']) ?></td>
                  <td>
                    <?php if ($v['last_naf_grade']): ?>
                      <span class="badge badge-naf-<?= strtolower(substr($v['last_naf_grade'], -1)) ?>">
                        <?= htmlspecialchars($v['last_naf_grade']) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted fs-7">ยังไม่เคยประเมิน</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="<?= $baseUrl ?>/naf/create?hn=<?= $v['hn'] ?>&vn=<?= $v['vn'] ?>" class="btn btn-sm btn-success me-1">
                      <i class="fa-solid fa-clipboard-check me-1"></i> ประเมิน NAF
                    </a>
                    <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($v['hn']) ?>" class="btn btn-sm btn-outline-primary">
                      <i class="fa-solid fa-id-card me-1"></i> Profile
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
