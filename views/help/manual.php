<?php
$pageTitle = "คู่มือการใช้งานระบบ - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Header Banner -->
    <div class="bg-pdh-blue text-white rounded-3 p-4 mb-4 shadow-sm position-relative overflow-hidden">
      <div class="row align-items-center">
        <div class="col-lg-8 position-relative z-1">
          <span class="badge bg-warning text-dark px-3 py-2 fs-7 fw-bold mb-2">
            <i class="fa-solid fa-book-open me-1"></i> User Manual & Documentation
          </span>
          <h2 class="fw-bold mb-2">คู่มือการใช้งานระบบ PDH Nutrition</h2>
          <p class="mb-0 text-white-50">
            คู่มือขั้นตอนการดำเนินงานบริหารจัดการภาวะโภชนาการผู้ป่วย โรงพยาบาลปลวกแดง สำหรับนักโภชนาการ แพทย์ พยาบาล และบุคลากรทางการแพทย์
          </p>
        </div>
        <div class="col-lg-4 text-end d-none d-lg-block position-relative z-1">
          <i class="fa-solid fa-notes-medical display-1 text-white-50"></i>
        </div>
      </div>
    </div>

    <!-- Quick Navigation Pills -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body py-2">
        <ul class="nav nav-pills nav-fill" id="manualTabs" role="tablist">
          <li class="nav-item">
            <button class="nav-link active fw-bold" id="tab-overview-tab" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button">
              <i class="fa-solid fa-hospital me-1"></i> 1. ภาพรวมระบบ
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold text-warning" id="tab-queue-tab" data-bs-toggle="pill" data-bs-target="#tab-queue" type="button">
              <i class="fa-solid fa-user-clock me-1"></i> 2. คิวประเมินก่อนพบแพทย์
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold" id="tab-naf-tab" data-bs-toggle="pill" data-bs-target="#tab-naf" type="button">
              <i class="fa-solid fa-clipboard-check me-1"></i> 3. แบบประเมิน NAF
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold" id="tab-diet-tab" data-bs-toggle="pill" data-bs-target="#tab-diet" type="button">
              <i class="fa-solid fa-utensils me-1"></i> 4. ใบสั่งโภชนบำบัด
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold text-danger" id="tab-alert-tab" data-bs-toggle="pill" data-bs-target="#tab-alert" type="button">
              <i class="fa-solid fa-bell me-1"></i> 5. Smart Daily Alerts
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold" id="tab-reports-tab" data-bs-toggle="pill" data-bs-target="#tab-reports" type="button">
              <i class="fa-solid fa-file-invoice me-1"></i> 6. รายงานและการพิมพ์
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link fw-bold" id="tab-admin-tab" data-bs-toggle="pill" data-bs-target="#tab-admin" type="button">
              <i class="fa-solid fa-sliders me-1"></i> 7. ตั้งค่าระบบ (Admin)
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Manual Content Sections -->
    <div class="tab-content" id="manualTabsContent">

      <!-- 1. OVERVIEW -->
      <div class="tab-pane fade show active" id="tab-overview">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-hospital text-primary me-2"></i> 1. ภาพรวมระบบ (System Overview)
            </h4>
            <p class="text-muted fs-6">
              ระบบ **PDH Nutrition Management System** ถูกออกแบบเพื่อช่วยงานบุคลากรทางการแพทย์ โรงพยาบาลปลวกแดง ในการประเมิน คัดกรอง และติดตามภาวะโภชนาการของผู้ป่วย OPD/IPD อย่างเป็นระบบ มีประสิทธิภาพ และสอดคล้องกับมาตรฐานทางคลินิก
            </p>

            <div class="row g-3 mt-2">
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-pdh-blue"><i class="fa-solid fa-network-wired text-info me-2"></i> เชื่อมโยงระบบ HIS (HIMPRO)</h6>
                  <p class="fs-7 text-muted mb-0">ดึงข้อมูลผู้ป่วย Visit วันนี้, ผลตรวจห้องปฏิบัติการ (Lab Result), และข้อมูลสิทธิ์สุขภาพอัตโนมัติผ่าน HIS API Gateway</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-pdh-blue"><i class="fa-solid fa-shield-halved text-success me-2"></i> ความปลอดภัยและ RBAC</h6>
                  <p class="fs-7 text-muted mb-0">กำหนดสิทธิ์การเข้าถึง 7 ระดับ (SUPER_ADMIN, ADMIN, DIETITIAN, DOCTOR, NURSE, PHARMACIST, VIEWER) พร้อมระบบ Audit Log</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light h-100">
                  <h6 class="fw-bold text-pdh-blue"><i class="fa-solid fa-bolt text-warning me-2"></i> Smart Alert Engine</h6>
                  <p class="fs-7 text-muted mb-0">แจ้งเตือนอัตโนมัติเมื่อพบค่า Lab สุ่มเสี่ยง เช่น Albumin ต่ำ (<3.5), eGFR ต่ำ (<60), FBS สูง (>200) หรือ HbA1c สูง (>8.0)</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. PRE-DOCTOR QUEUE -->
      <div class="tab-pane fade" id="tab-queue">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-user-clock text-warning me-2"></i> 2. ขั้นตอนการใช้งานคิวประเมินก่อนพบแพทย์ (Pre-Doctor Queue)
            </h4>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
              <i class="fa-solid fa-star fs-3 me-3"></i>
              <div>
                <strong>ฟังก์ชันหลักสำหรับนักโภชนาการและพยาบาล:</strong> ทำการประเมินภาวะโภชนาการผู้ป่วยล่วงหน้าก่อนผู้ป่วยเข้าพบแพทย์ เพื่อให้แพทย์นำข้อมูลโภชนบำบัดไปใช้ประกอบการรักษาได้ทันที
              </div>
            </div>

            <div class="timeline ps-3">
              <div class="mb-4">
                <h6 class="fw-bold text-primary"><span class="badge bg-primary me-2">Step 1</span> เข้าหน้า "คิวประเมินก่อนพบแพทย์"</h6>
                <p class="text-muted fs-7">คลิกเมนู <strong class="text-warning">"คิวประเมินก่อนพบแพทย์"</strong> จาก Sidebar เมนูด้านข้าง ระบบจะแสดงรายการผู้ป่วยที่เดินทางมาโรงพยาบาลในวันนี้แยกตามคลินิก (เช่น คลินิกเบาหวาน, คลินิกไต, คลินิกโภชนาการ)</p>
              </div>
              <div class="mb-4">
                <h6 class="fw-bold text-primary"><span class="badge bg-primary me-2">Step 2</span> กดปุ่ม "เริ่มประเมิน"</h6>
                <p class="text-muted fs-7">กดปุ่ม <span class="badge bg-warning text-dark"><i class="fa-solid fa-stethoscope"></i> ประเมิน</span> บนการ์ดผู้ป่วยที่ต้องการ ระบบจะนำไปยังหน้าประเมินโภชนาการและบันทึกข้อมูล NAF</p>
              </div>
              <div class="mb-4">
                <h6 class="fw-bold text-primary"><span class="badge bg-primary me-2">Step 3</span> บันทึกน้ำหนัก ส่วนสูง และ NAF Score</h6>
                <p class="text-muted fs-7">ระบุน้ำหนัก ส่วนสูง (ระบบคำนวณ BMI อัตโนมัติ) และเลือกข้อประเมิน NAF 5 หมวด ระบบจะสรุประดับคะแนน NAF A, B หรือ C ให้อัตโนมัติ</p>
              </div>
              <div class="mb-0">
                <h6 class="fw-bold text-primary"><span class="badge bg-primary me-2">Step 4</span> กดส่งมอบงานไปยังแพทย์</h6>
                <p class="text-muted fs-7">เมื่อกดบันทึก สถานะคิวจะเปลี่ยนเป็น <span class="badge bg-success">COMPLETED</span> และข้อมูลโภชนบำบัดจะพร้อมให้แพทย์เปิดดูในห้องตรวจทันที</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. NAF FORM -->
      <div class="tab-pane fade" id="tab-naf">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-clipboard-check text-success me-2"></i> 3. แบบประเมิน NAF (Nutrition Alert Form)
            </h4>
            <p class="text-muted fs-6 mb-3">การประเมิน NAF แบ่งออกเป็น 5 หมวดสำคัญ เพื่อจัดเกรดความเสี่ยงทุพโภชนาการ:</p>

            <div class="table-responsive mb-4">
              <table class="table table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 150px;">ระดับ NAF</th>
                    <th>เกณฑ์คะแนน (Score)</th>
                    <th>แปลผลภาวะโภชนาการ</th>
                    <th>แนวทางโภชนบำบัดที่แนะนำ</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><span class="badge bg-success fs-6">NAF A</span></td>
                    <td>0 - 5 คะแนน</td>
                    <td class="fw-bold text-success">ภาวะโภชนาการปกติ / ความเสี่ยงต่ำ</td>
                    <td>ให้ความรู้ด้านโภชนาการทั่วไป และนัดติดตามตามรอบปกติ</td>
                  </tr>
                  <tr>
                    <td><span class="badge bg-warning text-dark fs-6">NAF B</span></td>
                    <td>6 - 10 คะแนน</td>
                    <td class="fw-bold text-warning">สงสัยภาวะทุพโภชนาการปานกลาง</td>
                    <td>นักโภชนาการวางแผนโภชนบำบัดเฉพาะราย + เสริมโภชนบำบัดทางการแพทย์ (ONS)</td>
                  </tr>
                  <tr>
                    <td><span class="badge bg-danger fs-6">NAF C</span></td>
                    <td>&ge; 11 คะแนน</td>
                    <td class="fw-bold text-danger">ภาวะทุพโภชนาการรุนแรง (Severe Malnutrition)</td>
                    <td>ปรึกษาแพทย์ + นักโภชนาการเข้าติดตามรายวัน + พิจารณาให้ Enteral / Parenteral Nutrition</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. DIET ORDERS -->
      <div class="tab-pane fade" id="tab-diet">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-utensils text-primary me-2"></i> 4. ใบสั่งโภชนบำบัดและการพิมพ์แบบฟอร์ม (Diet Orders)
            </h4>
            <p class="text-muted fs-6">
              แพทย์และนักโภชนาการสามารถสั่งอาหารและสารอาหารสำหรับผู้ป่วยได้ทั้งรูปแบบ **OPD Diet Advice** และ **IPD Diet Order**:
            </p>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light">
                  <h6 class="fw-bold text-pdh-blue"><i class="fa-solid fa-bowl-rice text-primary me-2"></i> ประเภทอาหารดัดแปลง (Therapeutic Diets)</h6>
                  <ul class="fs-7 text-muted mb-0 ps-3">
                    <li>Low Salt / Low Sodium Diet (อาหารจำกัดโซเดียม)</li>
                    <li>Diabetic Diet / Low Sugar (อาหารเบาหวาน)</li>
                    <li>Renal Diet / Low Protein / Low Potassium (อาหารโรคไต)</li>
                    <li>High Protein / High Energy Diet (อาหารโปรตีนสูง)</li>
                  </ul>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 border rounded-3 bg-light">
                  <h6 class="fw-bold text-pdh-blue"><i class="fa-solid fa-jar-wheat text-success me-2"></i> อาหารทางสายยาง (Enteral Nutrition)</h6>
                  <ul class="fs-7 text-muted mb-0 ps-3">
                    <li>สูตรมาตรฐาน (Standard Formula: BD, Blenderized)</li>
                    <li>สูตรเฉพาะโรค (Diabetes, Renal, Hepatic, Oncology)</li>
                    <li>คำนวณพลังงาน (Energy kcal/day) และ โปรตีน (g/day) อัตโนมัติ</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <h6 class="fw-bold text-pdh-blue"><i class="fa-solid fa-print text-dark me-2"></i> การพิมพ์ใบสั่งอาหาร (Print View)</h6>
              <p class="fs-7 text-muted mb-0">สามารถกดปุ่ม <span class="badge bg-secondary"><i class="fa-solid fa-print"></i> พิมพ์ใบสั่งอาหาร A4</span> เพื่อพิมพ์เอกสารแบบฟอร์มทางการแพทย์มาตรฐาน พร้อมช่องเซ็นชื่อแพทย์และนักโภชนาการ</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. SMART ALERTS -->
      <div class="tab-pane fade" id="tab-alert">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-bell text-danger me-2"></i> 5. ระบบแจ้งเตือน Smart Daily Alerts & Heatmap
            </h4>
            <p class="text-muted fs-6 mb-3">
              ระบบวิเคราะห์ผลตรวจทางห้องปฏิบัติการ (Clinical Lab Results) ล่าสุดของผู้ป่วยอัตโนมัติ เพื่อคัดกรองกลุ่มเสี่ยงส่งให้นักโภชนาการติดตาม:
            </p>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="card border-danger border-opacity-25 h-100">
                  <div class="card-header bg-danger bg-opacity-10 fw-bold text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> เกณฑ์การแจ้งเตือนผล Lab ผิดปกติ (Smart Daily Alerts)
                  </div>
                  <div class="card-body">
                    <ul class="fs-7 text-muted mb-0 ps-3">
                      <li><strong>Serum Albumin < 3.5 g/dL</strong> &rarr; เสี่ยงต่อภาวะโปรตีนในเลือดต่ำ / ทุพโภชนาการ</li>
                      <li><strong>eGFR < 60 mL/min/1.73m²</strong> &rarr; ผู้ป่วยโรคไตเรื้อรัง (CKD) ต้องการโภชนบำบัดเฉพาะ</li>
                      <li><strong>FBS > 200 mg/dL หรือ HbA1c > 8.0%</strong> &rarr; เบาหวานควบคุมไม่ได้</li>
                      <li><strong>Total Cholesterol ≥ 240 mg/dL</strong> &rarr; ภาวะไขมันในเลือดสูงรุนแรง</li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border-warning border-opacity-25 h-100">
                  <div class="card-header bg-warning bg-opacity-10 fw-bold text-dark">
                    <i class="fa-solid fa-fire-flame-curved me-1"></i> Clinical Risk Heatmap Matrix
                  </div>
                  <div class="card-body">
                    <p class="fs-7 text-muted mb-2">แสดงเมทริกซ์ระดับความเสี่ยงทางโภชนาการแบ่งตามกลุ่มโรค (เช่น Glycemic Control, Renal Nutrition, Malnutrition Deficiency) ในรูปแบบเฉดสี:</p>
                    <div class="d-flex gap-2">
                      <span class="badge bg-danger">CRITICAL (สีแดง)</span>
                      <span class="badge bg-warning text-dark">HIGH (สีส้ม)</span>
                      <span class="badge bg-info text-dark">MODERATE (สีฟ้า)</span>
                      <span class="badge bg-success">LOW (สีเขียว)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. REPORTS -->
      <div class="tab-pane fade" id="tab-reports">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-file-invoice text-info me-2"></i> 6. รายงานสารสนเทศและการส่งออกข้อมูล (Reports & Export)
            </h4>
            <p class="text-muted fs-6 mb-3">
              ระบบมีรายงานสารสนเทศทางการแพทย์ด้านโภชนาการรวม 13 รายงาน รองรับการส่งออกข้อมูลหลายรูปแบบ:
            </p>

            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light text-center">
                  <i class="fa-solid fa-file-excel text-success display-6 mb-2"></i>
                  <h6 class="fw-bold">Export Excel (Unicode)</h6>
                  <p class="fs-7 text-muted mb-0">ส่งออกไฟล์ Excel ภาษาไทยสมบูรณ์ ไม่เป็นภาษาต่างดาว</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light text-center">
                  <i class="fa-solid fa-file-csv text-primary display-6 mb-2"></i>
                  <h6 class="fw-bold">Export CSV</h6>
                  <p class="fs-7 text-muted mb-0">รองรับการนำข้อมูลไปวิเคราะห์ต่อด้วยโปรแกรมทางสถิติ (SPSS/R)</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 border rounded-3 bg-light text-center">
                  <i class="fa-solid fa-print text-dark display-6 mb-2"></i>
                  <h6 class="fw-bold">Print & PDF A4</h6>
                  <p class="fs-7 text-muted mb-0">สั่งพิมพ์รายงานสรุปผลงานลงกระดาษ A4 รูปแบบมาตรฐานโรงพยาบาล</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. ADMIN -->
      <div class="tab-pane fade" id="tab-admin">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h4 class="fw-bold text-pdh-blue mb-3">
              <i class="fa-solid fa-sliders text-danger me-2"></i> 7. การบริหารจัดการระบบสำหรับผู้ดูแลระบบ (Admin)
            </h4>
            
            <div class="accordion" id="adminAccordion">
              <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                <h2 class="accordion-header">
                  <button class="accordion-button fw-bold text-pdh-blue" type="button" data-bs-toggle="collapse" data-bs-target="#acc1">
                    <i class="fa-solid fa-users me-2 text-primary"></i> จัดการผู้ใช้งานและสิทธิ์ (User & RBAC Management)
                  </button>
                </h2>
                <div id="acc1" class="accordion-collapse collapse show" data-bs-parent="#adminAccordion">
                  <div class="accordion-body fs-7 text-muted">
                    ผู้ดูแลระบบสามารถ <strong>เพิ่มผู้ใช้งานใหม่ (Create)</strong>, <strong>แก้ไขสิทธิ์ (Edit Role)</strong>, <strong>เปลี่ยนรหัสผ่าน (Reset Password)</strong>, <strong>สลับสถานะเปิด/ปิด (Toggle Status)</strong> และ <strong>ลบผู้ใช้ (Delete)</strong> ได้จากหน้า <code>/admin/users</code>
                  </div>
                </div>
              </div>

              <div class="accordion-item border-0 mb-2 shadow-sm rounded">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed fw-bold text-pdh-blue" type="button" data-bs-toggle="collapse" data-bs-target="#acc2">
                    <i class="fa-solid fa-gears me-2 text-warning"></i> ตั้งค่า NAF Rules Engine
                  </button>
                </h2>
                <div id="acc2" class="accordion-collapse collapse" data-bs-parent="#adminAccordion">
                  <div class="accordion-body fs-7 text-muted">
                    ปรับแต่งเกณฑ์การให้คะแนน NAF Alert Form เพิ่มหรือแก้ไขคะแนนข้อถามตามแนวนโยบายของโรงพยาบาล พร้อมระบบ Versioning ที่หน้า <code>/admin/rules</code>
                  </div>
                </div>
              </div>

              <div class="accordion-item border-0 shadow-sm rounded">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed fw-bold text-pdh-blue" type="button" data-bs-toggle="collapse" data-bs-target="#acc3">
                    <i class="fa-solid fa-shield-halved me-2 text-success"></i> Audit Log System
                  </button>
                </h2>
                <div id="acc3" class="accordion-collapse collapse" data-bs-parent="#adminAccordion">
                  <div class="accordion-body fs-7 text-muted">
                    ตรวจสอบประวัติการเข้าใช้งานและกิจกรรมสำคัญในระบบ (LOGIN, VIEW_PATIENT, CREATE_NAF, CREATE_DIET, EXPORT_REPORT) พร้อม IP Address และ User-Agent ที่หน้า <code>/admin/audit-log</code>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
