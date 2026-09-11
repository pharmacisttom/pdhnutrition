<?php
$pageTitle = "Nutrition Registry - PDH Nutrition";
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
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-clipboard-list text-primary me-2"></i> Nutrition Registry (ทะเบียบผู้ป่วยติดตามโภชนาการ)
        </h3>
        <p class="text-muted mb-0">ผู้ป่วยใน Registry ที่ตั้งค่า <code>must_review_every_visit = 1</code> จะปรากฏในคิวประเมินก่อนพบแพทย์อัตโนมัติเมื่อมาโรงพยาบาล</p>
      </div>

      <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#enrollModal">
        <i class="fa-solid fa-user-plus me-1"></i> เพิ่มผู้ป่วยเข้า Registry
      </button>
    </div>

    <!-- Registry Table Card -->
    <div class="card shadow-sm border-0">
      <div class="card-header bg-pdh-blue text-white py-3">
        <i class="fa-solid fa-users-rectangle me-2"></i> รายชื่อผู้ป่วยในกลุ่มติดตามโภชนาการ (<?= count($registryList) ?> รายการ)
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th>HN</th>
                <th>ชื่อ - นามสกุล</th>
                <th>อายุ</th>
                <th>ระดับความเสี่ยง</th>
                <th>เหตุผลในการติดตาม</th>
                <th>NAF ล่าสุด</th>
                <th>ประเมินก่อนพบแพทย์ทุกครั้ง</th>
                <th>สถานะ</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($registryList as $r): ?>
                <tr>
                  <td class="fw-bold text-pdh-blue">
                    <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($r['hn']) ?>" class="text-decoration-none"><?= htmlspecialchars($r['hn']) ?></a>
                  </td>
                  <td class="fw-bold"><?= htmlspecialchars($r['fullname']) ?></td>
                  <td><?= $r['age'] ?> ปี</td>
                  <td>
                    <span class="badge bg-<?= ($r['risk_level'] === 'SEVERE' || $r['risk_level'] === 'HIGH') ? 'danger' : 'warning' ?>">
                      <?= htmlspecialchars($r['risk_level']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($r['reason']) ?></td>
                  <td>
                    <?php if ($r['last_naf_grade']): ?>
                      <span class="badge badge-naf-<?= strtolower(substr($r['last_naf_grade'], -1)) ?>"><?= htmlspecialchars($r['last_naf_grade']) ?></span>
                    <?php else: ?>
                      <span class="text-muted fs-7">ยังไม่เคยประเมิน</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($r['must_review_every_visit']): ?>
                      <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-check me-1"></i> ทุกครั้ง</span>
                    <?php else: ?>
                      <span class="badge bg-light text-dark border">ตามรอบ</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($r['active']): ?>
                      <span class="badge bg-success px-2 py-1">ACTIVE</span>
                    <?php else: ?>
                      <span class="badge bg-secondary px-2 py-1">INACTIVE</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <button onclick="toggleStatus(<?= $r['id'] ?>)" class="btn btn-sm btn-outline-secondary me-1">
                      <i class="fa-solid fa-power-off"></i> สลับสถานะ
                    </button>
                    <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($r['hn']) ?>" class="btn btn-sm btn-outline-primary">
                      <i class="fa-solid fa-id-card"></i> Profile
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

<!-- Enroll Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="enrollForm" method="POST" action="<?= $baseUrl ?>/registry/store">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="modal-header bg-pdh-blue text-white">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i> เพิ่มผู้ป่วยเข้า Nutrition Registry</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">HN ผู้ป่วย</label>
            <input type="text" name="hn" class="form-control form-control-lg" placeholder="ระบุ HN เช่น 66000101" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">ระดับความเสี่ยง (Risk Level)</label>
            <select name="risk_level" class="form-select">
              <option value="MEDIUM">MEDIUM Risk</option>
              <option value="HIGH">HIGH Risk</option>
              <option value="SEVERE" selected>SEVERE Malnutrition</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">เหตุผลในการติดตาม</label>
            <input type="text" name="reason" class="form-control" placeholder="เช่น ผู้ป่วย ESRD มีภาวะโภชนาการต่ำ..." required>
          </div>

          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="must_review_every_visit" value="1" id="mrev" checked>
            <label class="form-check-label fw-bold" for="mrev">ต้องประเมินโภชนาการก่อนพบแพทย์ทุกครั้งที่มา visit</label>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">รอบติดตาม (จำนวนวัน)</label>
            <input type="number" name="follow_up_interval_days" class="form-control" value="14">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึกเข้า Registry</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$('#enrollForm').on('submit', function(e) {
  e.preventDefault();
  $.ajax({
    url: '<?= $baseUrl ?>/registry/store',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire('สำเร็จ', res.message, 'success').then(() => location.reload());
      }
    }
  });
});

function toggleStatus(id) {
  $.ajax({
    url: '<?= $baseUrl ?>/registry/toggle/' + id,
    type: 'POST',
    data: { csrf_token: '<?= $csrfToken ?>' },
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        location.reload();
      }
    }
  });
}
</script>
