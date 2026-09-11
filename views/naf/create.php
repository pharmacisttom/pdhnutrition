<?php
$pageTitle = "แบบประเมิน NAF Assessment - PDH Nutrition";
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

    <form id="nafForm" method="POST" action="<?= $baseUrl ?>/naf/store">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="hn" value="<?= htmlspecialchars($patient['hn']) ?>">
      <input type="hidden" name="vn" value="<?= htmlspecialchars($vn) ?>">
      <input type="hidden" name="an" value="<?= htmlspecialchars($an) ?>">

      <div class="row g-4">
        
        <!-- Left Side: NAF Sections 1-8 -->
        <div class="col-lg-8">

          <!-- Section 1 & 2: Anthropometric & Labs -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">
              <i class="fa-solid fa-weight-scale me-2"></i> ส่วนที่ 1 & 2: น้ำหนัก ส่วนสูง และผลตรวจห้องปฏิบัติการ
            </h5>

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">น้ำหนักตัว (Weight kg)</label>
                <input type="number" step="0.1" name="weight_kg" id="weight_kg" class="form-control form-control-lg" value="<?= $patient['weight'] ?: 50 ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">ส่วนสูง (Height cm)</label>
                <input type="number" step="0.1" name="height_cm" id="height_cm" class="form-control form-control-lg" value="<?= $patient['height'] ?: 160 ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">แหล่งที่มาของส่วนสูง</label>
                <select name="height_source" class="form-select">
                  <option value="HIS" selected>HIS Database</option>
                  <option value="Measured">วัดจริงขณะประเมิน (Measured)</option>
                  <option value="Arm span">Arm span (ความยาวช่วงแขน)</option>
                  <option value="Previous">จากประวัติการประเมินเดิม</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">Arm span (cm)</label>
                <input type="number" step="0.1" name="arm_span_cm" id="arm_span_cm" class="form-control" placeholder="กรอกกรณีวัดส่วนสูงไม่ได้">
              </div>

              <div class="col-md-4">
                <label class="form-label fw-bold">Albumin (g/dL)</label>
                <input type="number" step="0.1" name="albumin" id="albumin" class="form-control" value="<?= $latestLabs['albumin'] ?? '' ?>" placeholder="เช่น 3.2">
              </div>

              <div class="col-md-2">
                <label class="form-label fw-bold">WBC</label>
                <input type="number" name="wbc" id="wbc" class="form-control" value="<?= $latestLabs['wbc'] ?? '' ?>" placeholder="เช่น 6500">
              </div>

              <div class="col-md-2">
                <label class="form-label fw-bold">Lymphocyte (%)</label>
                <input type="number" step="0.1" name="lymphocyte" id="lymphocyte" class="form-control" value="<?= $latestLabs['lymphocyte'] ?? '' ?>" placeholder="เช่น 22">
              </div>
            </div>
          </div>

          <!-- Section 3: Body Build -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">ส่วนที่ 3: รูปร่างของผู้ป่วย (Body Build)</h5>
            <div class="row g-2">
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="BUILD_VERY_THIN" value="BUILD_VERY_THIN" data-score="2">
                  <label class="form-check-label fw-bold" for="BUILD_VERY_THIN">ผอมมาก (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="BUILD_THIN" value="BUILD_THIN" data-score="1">
                  <label class="form-check-label fw-bold" for="BUILD_THIN">ผอม (1 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="BUILD_NORMAL" value="BUILD_NORMAL" data-score="0" checked>
                  <label class="form-check-label fw-bold" for="BUILD_NORMAL">ปกติ (0 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="BUILD_OBESE" value="BUILD_OBESE" data-score="1">
                  <label class="form-check-label fw-bold" for="BUILD_OBESE">อ้วน (1 คะแนน)</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 4: Weight Change in 4 Weeks -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">ส่วนที่ 4: การเปลี่ยนแปลงน้ำหนักตัวใน 4 สัปดาห์ที่ผ่านมา</h5>
            <div class="row g-2">
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="WT_DECREASED" value="WT_DECREASED" data-score="2">
                  <label class="form-check-label fw-bold text-danger" for="WT_DECREASED">ลดลง / ผอมลง (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="WT_INCREASED" value="WT_INCREASED" data-score="1">
                  <label class="form-check-label fw-bold" for="WT_INCREASED">เพิ่มขึ้น / อ้วนขึ้น (1 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="WT_SAME" value="WT_SAME" data-score="0" checked>
                  <label class="form-check-label fw-bold" for="WT_SAME">เท่าเดิม (0 คะแนน)</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 5: Food Intake in 2 Weeks -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">ส่วนที่ 5: อาหารที่กินในช่วง 2 สัปดาห์ที่ผ่านมา</h5>
            
            <h6 class="fw-bold text-secondary mt-2">5.1 ลักษณะอาหาร</h6>
            <div class="row g-2 mb-3">
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="FOOD_WATER" value="FOOD_WATER" data-score="2">
                  <label class="form-check-label fw-bold" for="FOOD_WATER">อาหารน้ำ (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="FOOD_LIQUID" value="FOOD_LIQUID" data-score="2">
                  <label class="form-check-label fw-bold" for="FOOD_LIQUID">อาหารเหลว (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="FOOD_SOFT" value="FOOD_SOFT" data-score="1">
                  <label class="form-check-label fw-bold" for="FOOD_SOFT">อาหารนุ่มกว่าปกติ (1 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="FOOD_NORMAL" value="FOOD_NORMAL" data-score="0" checked>
                  <label class="form-check-label fw-bold" for="FOOD_NORMAL">ปกติ (0 คะแนน)</label>
                </div>
              </div>
            </div>

            <h6 class="fw-bold text-secondary">5.2 ปริมาณที่กิน</h6>
            <div class="row g-2">
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="AMOUNT_VERY_LITTLE" value="AMOUNT_VERY_LITTLE" data-score="2">
                  <label class="form-check-label fw-bold text-danger" for="AMOUNT_VERY_LITTLE">กินน้อยมาก < 25% (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="AMOUNT_LESS" value="AMOUNT_LESS" data-score="1">
                  <label class="form-check-label fw-bold text-warning" for="AMOUNT_LESS">กินน้อยลง 25-50% (1 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="radio" name="items[]" id="AMOUNT_NORMAL" value="AMOUNT_NORMAL" data-score="0" checked>
                  <label class="form-check-label fw-bold" for="AMOUNT_NORMAL">เท่าปกติ (0 คะแนน)</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 6: Continuous Symptoms (>2 Weeks) -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">ส่วนที่ 6: อาการต่อเนื่องมากกว่า 2 สัปดาห์ (เลือกได้หลายข้อ)</h5>
            <div class="row g-2">
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="SYM_CHOKING" value="SYM_CHOKING" data-score="2">
                  <label class="form-check-label fw-bold" for="SYM_CHOKING">สำลักอาหาร/น้ำ (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="SYM_SWALLOW_DIFFICULT" value="SYM_SWALLOW_DIFFICULT" data-score="2">
                  <label class="form-check-label fw-bold" for="SYM_SWALLOW_DIFFICULT">กลืนลำบาก/ใส่สายอาหาร (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="SYM_DIARRHEA" value="SYM_DIARRHEA" data-score="2">
                  <label class="form-check-label fw-bold" for="SYM_DIARRHEA">ท้องเสียเรื้อรัง (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="SYM_VOMITING" value="SYM_VOMITING" data-score="2">
                  <label class="form-check-label fw-bold" for="SYM_VOMITING">อาเจียนหลังอาหาร (2 คะแนน)</label>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="SYM_NAUSEA" value="SYM_NAUSEA" data-score="2">
                  <label class="form-check-label fw-bold" for="SYM_NAUSEA">คลื่นไส้เป็นประจำ (2 คะแนน)</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 8: Disease Severity -->
          <div class="card p-3 mb-4 shadow-sm border-0">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2">
              <i class="fa-solid fa-stethoscope me-2"></i> ส่วนที่ 8: โรคที่เป็นอยู่ (Disease Severity Rules)
            </h5>
            
            <h6 class="fw-bold text-warning border-bottom pb-1 mt-2">กลุ่มความรุนแรง 3 คะแนน (Disease Severity 3 points)</h6>
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_DM" value="DIS_DM" data-score="3">
                  <label class="form-check-label fw-bold" for="DIS_DM">โรคเบาหวาน (DM)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_CKD_ESRD" value="DIS_CKD_ESRD" data-score="3">
                  <label class="form-check-label fw-bold" for="DIS_CKD_ESRD">โรคไตเรื้อรัง/ไตวาย (CKD/ESRD)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_SEPTICEMIA" value="DIS_SEPTICEMIA" data-score="3">
                  <label class="form-check-label fw-bold" for="DIS_SEPTICEMIA">ติดเชื้อในกระแสเลือด (Septicemia)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_SOLID_CANCER" value="DIS_SOLID_CANCER" data-score="3">
                  <label class="form-check-label fw-bold" for="DIS_SOLID_CANCER">มะเร็งชนิดก้อน (Solid Cancer)</label>
                </div>
              </div>
            </div>

            <h6 class="fw-bold text-danger border-bottom pb-1">กลุ่มความรุนแรง 6 คะแนน (Disease Severity 6 points)</h6>
            <div class="row g-2">
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_SEVERE_PNEUMONIA" value="DIS_SEVERE_PNEUMONIA" data-score="6">
                  <label class="form-check-label fw-bold text-danger" for="DIS_SEVERE_PNEUMONIA">ปอดบวมรุนแรง (Severe Pneumonia)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_CRITICALLY_ILL" value="DIS_CRITICALLY_ILL" data-score="6">
                  <label class="form-check-label fw-bold text-danger" for="DIS_CRITICALLY_ILL">ผู้ป่วยภาวะวิกฤต (Critically Ill)</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check card p-2">
                  <input class="form-check-input" type="checkbox" name="items[]" id="DIS_STROKE_CVA" value="DIS_STROKE_CVA" data-score="6">
                  <label class="form-check-label fw-bold text-danger" for="DIS_STROKE_CVA">โรคหลอดเลือดสมอง (Stroke / CVA)</label>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Side: Sticky Real-time Score Panel -->
        <div class="col-lg-4">
          <div class="card p-3 bg-white sticky-naf-panel">
            <h5 class="fw-bold text-pdh-blue border-bottom pb-2 mb-3">
              <i class="fa-solid fa-calculator me-2"></i> สรุปผลการประเมิน NAF (Real-Time)
            </h5>

            <div class="d-flex justify-content-between mb-2">
              <span>BMI:</span>
              <span class="fw-bold" id="calc_bmi">-</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>BMI Score:</span>
              <span class="fw-bold text-primary" id="calc_bmi_score">0</span>
            </div>

            <div class="d-flex justify-content-between mb-2">
              <span>Albumin Score:</span>
              <span class="fw-bold text-primary" id="calc_alb_score">0</span>
            </div>

            <div class="d-flex justify-content-between mb-2">
              <span>TLC Score:</span>
              <span class="fw-bold text-primary" id="calc_tlc_score">0</span>
            </div>

            <hr>

            <div class="text-center my-3">
              <div class="text-muted fs-7">คะแนนรวม NAF Total Score</div>
              <h1 class="display-3 fw-bold text-pdh-blue my-1" id="calc_total_score">0</h1>
              <div id="calc_naf_grade" class="badge badge-naf-a fs-5 w-100 py-2">NAF A (Normal - Mild)</div>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm my-2">
              <i class="fa-solid fa-floppy-disk me-2"></i> บันทึกผล NAF Assessment
            </button>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script src="<?= $baseUrl ?>/public/assets/js/naf-calculator.js"></script>
<script>
$('#nafForm').on('submit', function(e) {
  e.preventDefault();
  $.ajax({
    url: '<?= $baseUrl ?>/naf/store',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'บันทึกสำเร็จ',
          text: res.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
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
