<?php
$pageTitle = "Guide to Make Diet Order - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
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
          <div class="mt-2 text-white-50">
            <span>เพศ: <?= htmlspecialchars($patient['gender']) ?></span>
            <span class="ms-3">อายุ: <?= $patient['age'] ?> ปี</span>
          </div>
        </div>
        <a href="<?= $baseUrl ?>/patient/<?= $patient['hn'] ?>" class="btn btn-outline-light btn-sm">
          <i class="fa-solid fa-arrow-left me-1"></i> ย้อนกลับไป Profile
        </a>
      </div>
    </div>

    <form id="dietForm" method="POST" action="<?= $baseUrl ?>/diet/store">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="hn" value="<?= htmlspecialchars($patient['hn']) ?>">
      <input type="hidden" name="vn" value="<?= htmlspecialchars($vn) ?>">
      <input type="hidden" name="assessment_id" value="<?= htmlspecialchars($assessmentId) ?>">

      <div class="row g-4">
        
        <!-- Left Side: Calculation & Orders -->
        <div class="col-lg-8">

          <!-- Section 1: Anthropometrics & IBW -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">
              <i class="fa-solid fa-calculator me-2"></i> คำนวณ IBW, Energy & Protein Requirement
            </h5>

            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label fw-bold">น้ำหนักตัว (Weight kg)</label>
                <input type="number" step="0.1" name="weight_kg" id="weight_kg" class="form-control" value="<?= $weight ?>" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold">ส่วนสูง (Height cm)</label>
                <input type="number" step="0.1" name="height_cm" id="height_cm" class="form-control" value="<?= $height ?>" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold">เพศ (Gender)</label>
                <select name="gender" id="gender" class="form-select">
                  <option value="MALE" <?= ($gender === 'MALE') ? 'selected' : '' ?>>ชาย (IBW = H - 100)</option>
                  <option value="FEMALE" <?= ($gender === 'FEMALE') ? 'selected' : '' ?>>หญิง (IBW = H - 105)</option>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold text-primary">IBW (Ideal Body Weight)</label>
                <div class="form-control bg-light fw-bold text-primary" id="calc_ibw">-</div>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Energy Requirement (kcal/IBW)</label>
                <select name="energy_kcal_per_ibw" id="energy_kcal_per_ibw" class="form-select">
                  <option value="25">25 kcal / kg IBW / day</option>
                  <option value="30" selected>30 kcal / kg IBW / day (Standard)</option>
                  <option value="35">35 kcal / kg IBW / day (High Energy)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Protein Requirement (g/IBW)</label>
                <select name="protein_g_per_ibw" id="protein_g_per_ibw" class="form-select">
                  <option value="1.0">1.0 g / kg IBW / day</option>
                  <option value="1.2" selected>1.2 g / kg IBW / day (Standard)</option>
                  <option value="1.3">1.3 g / kg IBW / day</option>
                  <option value="1.5">1.5 g / kg IBW / day (High Protein)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Section 2: Diet Types -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">
              <i class="fa-solid fa-bowl-food me-2"></i> ประเภทอาหาร (Diet Orders)
            </h5>

            <div class="row g-2 mb-3">
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="diet_types[]" value="Regular diet" id="d1">
                  <label class="form-check-label fw-bold" for="d1">Regular diet (อาหารธรรมดา)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="diet_types[]" value="Soft diet" id="d2">
                  <label class="form-check-label fw-bold" for="d2">Soft diet (อาหารอ่อน)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="diet_types[]" value="Full liquid diet" id="d3">
                  <label class="form-check-label fw-bold" for="d3">Full liquid diet (อาหารเหลว)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="diet_types[]" value="DM diet" id="d4">
                  <label class="form-check-label fw-bold" for="d4">DM diet (อาหารเฉพาะโรคเบาหวาน)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="diet_types[]" value="Low salt diet" id="d5">
                  <label class="form-check-label fw-bold" for="d5">Low salt diet (อาหารจำกัดโซเดียม)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="diet_types[]" value="Low fat diet" id="d6">
                  <label class="form-check-label fw-bold" for="d6">Low fat diet (อาหารจำกัดไขมัน)</label>
                </div>
              </div>
            </div>

            <label class="form-label fw-bold">Required Diet (คำสั่งเพิ่มเติม Free Text)</label>
            <textarea name="required_diet_note" class="form-control" rows="2" placeholder="ระบุข้อกำหนดเพิ่มเติม เช่น จำกัดโปแตสเซียม, อาหารปั่นละเอียด..."></textarea>
          </div>

          <!-- Section 3: Oral Supplement & Tube Feeding -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">
              <i class="fa-solid fa-bottle-droplet me-2"></i> Oral Supplement & Tube Feeding Order
            </h5>

            <h6 class="fw-bold text-success border-bottom pb-1 mt-2">1. Oral Supplement</h6>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">สูตรอาหารเสริมทางปาก (Formula)</label>
                <select name="oral_formula" class="form-select">
                  <option value="">-- ไม่ระบุ --</option>
                  <option value="Standard formula">Standard formula (สูตรมาตรฐาน)</option>
                  <option value="DM formula">DM formula (สูตรสำหรับเบาหวาน)</option>
                  <option value="Renal dialysis formula">Renal dialysis formula (สูตรสำหรับโรคไต)</option>
                  <option value="Cancer formula">Cancer formula (สูตรสำหรับมะเร็ง)</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">ปริมาณ (ml/มื้อ)</label>
                <input type="number" name="oral_ml_per_meal" class="form-control" value="200">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">ความถี่ (ครั้ง/วัน)</label>
                <input type="number" name="oral_frequency" class="form-control" value="3">
              </div>
            </div>

            <h6 class="fw-bold text-danger border-bottom pb-1 mt-2">2. Tube Feeding</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">สูตรอาหารทางสายยาง (Tube Formula)</label>
                <select name="tube_formula" class="form-select">
                  <option value="">-- ไม่ระบุ --</option>
                  <option value="Standard formula">Standard formula (สูตรมาตรฐาน)</option>
                  <option value="DM formula">DM formula (สูตรเบาหวาน)</option>
                  <option value="Renal dialysis formula">Renal dialysis formula (สูตรโรคไต)</option>
                  <option value="Pulmonary disease formula">Pulmonary disease formula (สูตรโรคปอด)</option>
                  <option value="Hepatic encephalopathy formula">Hepatic formula (สูตรโรคตับ)</option>
                  <option value="BD (Blenderized Diet)">BD (Blenderized Diet อาหารปั่นผสม)</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">ปริมาณ (ml/มื้อ)</label>
                <input type="number" name="tube_ml_per_meal" class="form-control" value="250">
              </div>
              <div class="col-md-3">
                <label class="form-label fw-bold">ความถี่ (ครั้ง/วัน)</label>
                <input type="number" name="tube_frequency" class="form-control" value="4">
              </div>
            </div>
          </div>

        </div>

        <!-- Right Side: Summary Card -->
        <div class="col-lg-4">
          <div class="card p-3 bg-white sticky-naf-panel">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2 mb-3">
              <i class="fa-solid fa-list-check me-2"></i> สรุปเป้าหมายโภชนบำบัด
            </h5>

            <div class="mb-3">
              <span class="text-muted">เป้าหมายพลังงาน (Total Energy):</span>
              <h2 class="fw-bold text-primary my-1" id="calc_total_energy">-</h2>
            </div>

            <div class="mb-3">
              <span class="text-muted">เป้าหมายโปรตีน (Total Protein):</span>
              <h2 class="fw-bold text-success my-1" id="calc_total_protein">-</h2>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm my-2">
              <i class="fa-solid fa-floppy-disk me-2"></i> บันทึก Diet Order
            </button>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script src="<?= $baseUrl ?>/public/assets/js/diet-calculator.js"></script>
<script>
$('#dietForm').on('submit', function(e) {
  e.preventDefault();
  $.ajax({
    url: '<?= $baseUrl ?>/diet/store',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire('สำเร็จ', res.message, 'success').then(() => {
          window.location.href = res.redirect;
        });
      }
    },
    error: function(xhr) {
      Swal.fire('ข้อผิดพลาด', xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถบันทึกได้', 'error');
    }
  });
});
</script>
