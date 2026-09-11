<?php
$pageTitle = "Nutrition Pre-Doctor Queue - PDH Nutrition";
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
          <i class="fa-solid fa-user-clock text-warning me-2"></i> คิวประเมินโภชนาการก่อนพบแพทย์ (Pre-Doctor Queue)
        </h3>
        <p class="text-muted mb-0">รายการผู้ป่วยกลุ่มติดตามโภชนาการที่มา โรงพยาบาลปลวกแดง วันนี้ และต้องได้รับการประเมินก่อนพบแพทย์</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button onclick="location.reload();" class="btn btn-outline-primary fw-bold shadow-sm">
          <i class="fa-solid fa-rotate me-1"></i> รีเฟรชข้อมูลคิว
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card p-3 mb-4 bg-white shadow-sm border-0">
      <form method="GET" action="<?= $baseUrl ?>/nutrition-queue" class="row g-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">กรองตามสถานะ</label>
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">-- แสดงทุกสถานะ --</option>
            <option value="WAITING_NUTRITION" <?= ($filters['status'] === 'WAITING_NUTRITION') ? 'selected' : '' ?>>รอประเมิน (WAITING_NUTRITION)</option>
            <option value="IN_ASSESSMENT" <?= ($filters['status'] === 'IN_ASSESSMENT') ? 'selected' : '' ?>>กำลังประเมิน (IN_ASSESSMENT)</option>
            <option value="NUTRITION_COMPLETED" <?= ($filters['status'] === 'NUTRITION_COMPLETED') ? 'selected' : '' ?>>ประเมินแล้ว (READY FOR DOCTOR)</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-bold">กรองตามคลินิก</label>
          <select name="clinic" class="form-select" onchange="this.form.submit()">
            <option value="">-- แสดงทุกคลินิก --</option>
            <option value="คลินิกโรคไต (CKD)" <?= ($filters['clinic'] === 'คลินิกโรคไต (CKD)') ? 'selected' : '' ?>>คลินิกโรคไต (CKD)</option>
            <option value="คลินิกโรคเรื้อรัง (NCD)" <?= ($filters['clinic'] === 'คลินิกโรคเรื้อรัง (NCD)') ? 'selected' : '' ?>>คลินิกโรคเรื้อรัง (NCD)</option>
            <option value="คลินิกอายุรกรรม" <?= ($filters['clinic'] === 'คลินิกอายุรกรรม') ? 'selected' : '' ?>>คลินิกอายุรกรรม</option>
            <option value="คลินิกเบาหวาน" <?= ($filters['clinic'] === 'คลินิกเบาหวาน') ? 'selected' : '' ?>>คลินิกเบาหวาน</option>
          </select>
        </div>
      </form>
    </div>

    <!-- Task Table Card -->
    <div class="card shadow-sm border-0">
      <div class="card-header bg-pdh-blue text-white py-3">
        <i class="fa-solid fa-list-check me-2"></i> รายการคิวประเมินโภชนาการประจำวัน (<?= count($queueTasks) ?> รายการ)
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th width="5%">Priority</th>
                <th width="10%">VN / เวลา</th>
                <th width="10%">HN</th>
                <th width="18%">ชื่อ - นามสกุล</th>
                <th width="12%">คลินิก / แพทย์</th>
                <th width="12%">NAF ล่าสุด</th>
                <th width="13%">สถานะการประเมิน</th>
                <th width="20%" class="text-center">Action / เครื่องมือประเมิน</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($queueTasks)): ?>
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">ไม่พบรายการคิวประเมินโภชนาการในขณะนี้</td>
                </tr>
              <?php else: ?>
                <?php foreach ($queueTasks as $t): ?>
                  <tr>
                    <td>
                      <span class="badge bg-<?= ($t['priority'] === 1) ? 'danger' : 'secondary' ?> fs-7">P<?= $t['priority'] ?></span>
                    </td>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($t['vn']) ?></div>
                      <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= date('H:i', strtotime($t['created_at'])) ?></small>
                    </td>
                    <td>
                      <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($t['hn']) ?>" class="fw-bold text-decoration-none">
                        <?= htmlspecialchars($t['hn']) ?>
                      </a>
                    </td>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($t['fullname']) ?></div>
                      <small class="text-muted">อายุ <?= $t['age'] ?> ปี (CID: <?= SanitizerHelper::maskCid($t['cid']) ?>)</small>
                    </td>
                    <td>
                      <div><?= htmlspecialchars($t['clinic']) ?></div>
                      <small class="text-muted"><i class="fa-solid fa-user-doctor me-1"></i><?= htmlspecialchars($t['doctor']) ?></small>
                    </td>
                    <td>
                      <?php if ($t['last_naf_grade']): ?>
                        <span class="badge badge-naf-<?= strtolower(substr($t['last_naf_grade'], -1)) ?>">
                          <?= htmlspecialchars($t['last_naf_grade']) ?> (Score: <?= $t['last_naf_score'] ?>)
                        </span>
                        <div class="fs-8 text-muted"><?= DateHelper::formatThaiDate($t['last_naf_date'], true) ?></div>
                      <?php else: ?>
                        <span class="text-muted fs-7">ยังไม่เคยประเมิน</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($t['status'] === 'WAITING_NUTRITION'): ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-7"><i class="fa-solid fa-clock me-1"></i> รอประเมิน</span>
                      <?php elseif ($t['status'] === 'IN_ASSESSMENT'): ?>
                        <span class="badge bg-primary px-3 py-2 fs-7"><i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังประเมิน (<?= htmlspecialchars($t['locked_by_name'] ?? 'เจ้าหน้าที่') ?>)</span>
                      <?php elseif ($t['status'] === 'NUTRITION_COMPLETED' || $t['status'] === 'READY_FOR_DOCTOR'): ?>
                        <span class="badge bg-success px-3 py-2 fs-7"><i class="fa-solid fa-circle-check me-1"></i> READY FOR DOCTOR</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <?php if ($t['status'] === 'WAITING_NUTRITION'): ?>
                          <button onclick="startAssessment(<?= $t['id'] ?>)" class="btn btn-sm btn-warning fw-bold">
                            <i class="fa-solid fa-play me-1"></i> เริ่มประเมิน
                          </button>
                        <?php endif; ?>

                        <a href="<?= $baseUrl ?>/naf/create?hn=<?= $t['hn'] ?>&vn=<?= $t['vn'] ?>" class="btn btn-sm btn-outline-success">
                          <i class="fa-solid fa-clipboard-check me-1"></i> NAF
                        </a>

                        <a href="<?= $baseUrl ?>/diet/create?hn=<?= $t['hn'] ?>&vn=<?= $t['vn'] ?>" class="btn btn-sm btn-outline-primary">
                          <i class="fa-solid fa-utensils me-1"></i> Diet Order
                        </a>

                        <a href="<?= $baseUrl ?>/notes/create?hn=<?= $t['hn'] ?>&vn=<?= $t['vn'] ?>" class="btn btn-sm btn-outline-info">
                          <i class="fa-solid fa-file-pen me-1"></i> SOAP
                        </a>

                        <?php if ($t['status'] === 'IN_ASSESSMENT'): ?>
                          <button onclick="completeAssessment(<?= $t['id'] ?>)" class="btn btn-sm btn-success fw-bold">
                            <i class="fa-solid fa-check me-1"></i> เสร็จสิ้น
                          </button>
                        <?php endif; ?>
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

<script>
function startAssessment(taskId) {
  Swal.fire({
    title: 'เริ่มการประเมินโภชนาการ?',
    text: 'ระบบจะทำการ ล็อกคิว นี้เพื่อป้องกันเจ้าหน้าที่ท่านอื่นประเมินซ้ำ',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'ยืนยันเริ่มประเมิน',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= $baseUrl ?>/nutrition-queue/start/' + taskId,
        type: 'POST',
        data: { csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('เริ่มประเมิน', res.message, 'success').then(() => location.reload());
          }
        },
        error: function(xhr) {
          Swal.fire('ข้อผิดพลาด', xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถล็อกคิวได้', 'error');
        }
      });
    }
  });
}

function completeAssessment(taskId) {
  Swal.fire({
    title: 'ยืนยันการประเมินเสร็จสิ้น?',
    text: 'ผู้ป่วยจะเปลี่ยนสถานะเป็น READY FOR DOCTOR พร้อมสำหรับแพทย์ทำการตรวจรักษา',
    icon: 'success',
    showCancelButton: true,
    confirmButtonText: 'เสร็จสิ้นการประเมิน',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '<?= $baseUrl ?>/nutrition-queue/complete/' + taskId,
        type: 'POST',
        data: { csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>' },
        dataType: 'json',
        success: function(res) {
          if (res.success) {
            Swal.fire('เรียบร้อย', res.message, 'success').then(() => location.reload());
          }
        }
      });
    }
  });
}
</script>
