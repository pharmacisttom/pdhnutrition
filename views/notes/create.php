<?php
$pageTitle = "บันทึก Nutrition Clinical Note (SOAP) - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Patient Header Banner -->
    <div class="patient-header-banner mb-4">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <span class="badge bg-warning text-dark me-2">HN: <?= htmlspecialchars($patient['hn']) ?></span>
          <?php if (!empty($vn)): ?><span class="badge bg-light text-dark me-2">VN: <?= htmlspecialchars($vn) ?></span><?php endif; ?>
          <h3 class="d-inline fw-bold align-middle"><?= htmlspecialchars($patient['fullname']) ?></h3>
        </div>
        <a href="<?= $baseUrl ?>/patient/<?= $patient['hn'] ?>" class="btn btn-outline-light btn-sm">
          <i class="fa-solid fa-arrow-left me-1"></i> ย้อนกลับไป Profile
        </a>
      </div>
    </div>

    <div class="card p-4 border-0 shadow-sm">
      <h4 class="fw-bold text-pdh-blue border-bottom pb-3 mb-4">
        <i class="fa-solid fa-notes-medical me-2"></i> บันทึกโภชนาการทางคลินิก (SOAP Note)
      </h4>

      <form id="soapForm" method="POST" action="<?= $baseUrl ?>/notes/store">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="hn" value="<?= htmlspecialchars($patient['hn']) ?>">
        <input type="hidden" name="vn" value="<?= htmlspecialchars($vn) ?>">
        <input type="hidden" name="assessment_id" value="<?= htmlspecialchars($assessmentId) ?>">

        <div class="mb-3">
          <label class="form-label fw-bold text-primary">S : Subjective (ข้อมูลจากการซักถามผู้ป่วย/ญาติ)</label>
          <textarea name="subjective" class="form-control" rows="3" placeholder="ระบุอาการ, ปัญหาการรับประทานอาหาร, ความรู้สึกของผู้ป่วย..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold text-success">O : Objective (ข้อมูลเชิงวัตถุประสงค์ / Anthro / Lab / NAF Auto-populated)</label>
          <textarea name="objective" class="form-control" rows="4" required><?= htmlspecialchars($objText) ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold text-warning">A : Assessment (การประเมินและวิเคราะห์ภาวะโภชนาการ)</label>
          <textarea name="assessment_text" class="form-control" rows="3" placeholder="ระบุการวิเคราะห์ปัญหาโภชนาการ, ภาวะทุพโภชนาการ..."></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold text-danger">P : Plan (แผนการให้โภชนบำบัดและการติดตาม)</label>
          <textarea name="plan_text" class="form-control" rows="3" placeholder="ระบุแผนการจัดอาหาร, Diet Order, การติดตามผล..."></textarea>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-primary btn-lg fw-bold px-4 shadow-sm">
            <i class="fa-solid fa-floppy-disk me-2"></i> บันทึก Clinical Note
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$('#soapForm').on('submit', function(e) {
  e.preventDefault();
  $.ajax({
    url: '<?= $baseUrl ?>/notes/store',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire('สำเร็จ', res.message, 'success').then(() => {
          window.location.href = res.redirect;
        });
      }
    }
  });
});
</script>
