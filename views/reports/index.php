<?php
$pageTitle = "ระบบรายงานโภชนาการ - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="mb-4">
      <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-file-invoice text-info me-2"></i> ระบบรายงานด้านโภชนาการ (Clinical Reports System)</h3>
      <p class="text-muted mb-0">ศูนย์รวมรายงานการประเมิน ภาวะโภชนาการ และ compliance ประจำวัน รายเดือน รายปี โรงพยาบาลปลวกแดง</p>
    </div>

    <!-- 10 Report Cards Grid -->
    <div class="row g-3">
      
      <!-- Report 1: Primary Operational Report -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-warning border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-user-clock text-warning fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">1. ผู้ป่วยที่ต้องประเมินก่อนพบแพทย์</h5>
          </div>
          <p class="text-muted fs-7">รายงานหลักสำหรับการปฏิบัติงานประจำวัน แสดงรายการคิวที่ต้องประเมินโภชนาการก่อนพบแพทย์</p>
          <a href="<?= $baseUrl ?>/reports/pre_doctor" class="btn btn-warning btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 2 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-danger border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-hourglass-half text-danger fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">2. ผู้ป่วยค้างประเมินโภชนาการ</h5>
          </div>
          <p class="text-muted fs-7">แสดงผู้ป่วยค้างประเมิน เรียงลำดับตาม High Risk (NAF C/B) และระยะเวลารอคอย (waiting time)</p>
          <a href="<?= $baseUrl ?>/reports/pending" class="btn btn-outline-danger btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 3 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-success border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-calendar-check text-success fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">3. Daily Nutrition Assessment</h5>
          </div>
          <p class="text-muted fs-7">สรุปรายการผู้ป่วยที่ได้รับการประเมินโภชนาการเสร็จสิ้นประจำวัน พร้อม Diet Order และผู้ประเมิน</p>
          <a href="<?= $baseUrl ?>/reports/daily" class="btn btn-outline-success btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 4 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-info border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-layer-group text-info fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">4. NAF Risk Classification</h5>
          </div>
          <p class="text-muted fs-7">จำแนกผู้ป่วยตามระดับ NAF A (Normal-Mild), NAF B (Moderate), NAF C (Severe)</p>
          <a href="<?= $baseUrl ?>/reports/naf_classification" class="btn btn-outline-info btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 5 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-danger border-4 bg-danger-subtle">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-triangle-exclamation text-danger fa-2x me-3"></i>
            <h5 class="fw-bold text-danger mb-0">5. ผู้ป่วย High Risk (NAF C)</h5>
          </div>
          <p class="text-muted fs-7">รายงานเฉพาะผู้ป่วยภาวะทุพโภชนาการรุนแรง (Severe Malnutrition) เพื่อติดตามใกล้ชิด</p>
          <a href="<?= $baseUrl ?>/reports/high_risk" class="btn btn-danger btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 6 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-secondary border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-clock-rotate-left text-secondary fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">6. Follow-up Overdue</h5>
          </div>
          <p class="text-muted fs-7">ผู้ป่วยกลุ่มติดตามที่เลยกำหนดวันประเมินซ้ำ แสดงจำนวนวันเลยกำหนด (days overdue)</p>
          <a href="<?= $baseUrl ?>/reports/overdue" class="btn btn-outline-secondary btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 7 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-primary border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-timeline text-primary fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">7. Patient History Timeline</h5>
          </div>
          <p class="text-muted fs-7">แสดง Timeline ประวัติการประเมิน น้ำหนัก BMI Albumin NAF Grade ย้อนหลังรายบุคคล</p>
          <a href="<?= $baseUrl ?>/reports/patient_history" class="btn btn-outline-primary btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 8 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-success border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-chart-line text-success fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">8. Monthly Summary & Compliance</h5>
          </div>
          <p class="text-muted fs-7">สรุปภาพรวมประจำเดือน และคำนวณ Pre-Doctor Assessment Compliance Rate (%)</p>
          <a href="<?= $baseUrl ?>/reports/monthly_summary" class="btn btn-outline-success btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 9 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-info border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-hospital-user text-info fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">9. สรุปตาม Clinic/Department</h5>
          </div>
          <p class="text-muted fs-7">เปรียบเทียบจำนวนคิวและการประเมินแยกตามคลินิกตรวจโรค (OPD Clinics)</p>
          <a href="<?= $baseUrl ?>/reports/clinic_breakdown" class="btn btn-outline-info btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

      <!-- Report 10 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 p-3 shadow-sm border-start border-dark border-4">
          <div class="d-flex align-items-center mb-2">
            <i class="fa-solid fa-user-doctor text-dark fa-2x me-3"></i>
            <h5 class="fw-bold text-pdh-blue mb-0">10. ภาระงานนักโภชนาการ</h5>
          </div>
          <p class="text-muted fs-7">สรุปจำนวนผู้ป่วยและการประเมินแยกรายนักโภชนาการ (Dietitian Workload Summary)</p>
          <a href="<?= $baseUrl ?>/reports/dietitian_workload" class="btn btn-outline-dark btn-sm fw-bold mt-auto"><i class="fa-solid fa-arrow-right me-1"></i> เปิดรายงาน</a>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
