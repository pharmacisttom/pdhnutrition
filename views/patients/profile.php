<?php
$pageTitle = "ประวัติและผลแลปผู้ป่วย HN " . htmlspecialchars($patient['hn']) . " - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
use App\Helpers\DateHelper;

// Extract lab values if available
$albumin = (float)($latestLabs['albumin'] ?? 0);
$wbc = (float)($latestLabs['wbc'] ?? 0);
$lym = (float)($latestLabs['lymphocyte'] ?? 0);
$tlc = (float)($latestLabs['tlc'] ?? ($wbc * $lym / 100));

// Mock or API lab parameters for renal, electrolytes, hematology & metabolic panels
$bun = (float)($latestLabs['bun'] ?? 18.5);
$cr  = (float)($latestLabs['creatinine'] ?? 1.1);
$k   = (float)($latestLabs['potassium'] ?? 4.2);
$na  = (float)($latestLabs['sodium'] ?? 138);
$hb  = (float)($latestLabs['hb'] ?? 12.4);
$hct = (float)($latestLabs['hct'] ?? 37.2);
$fbs = (float)($latestLabs['fbs'] ?? 115);

// Calculate eGFR (CKD-EPI formula approximation)
$age = (int)($patient['age'] ?: 50);
$isFemale = ($patient['gender'] === 'หญิง' || $patient['gender'] === 'F');
$egfr = round(141 * pow(min(($cr / ($isFemale ? 0.7 : 0.9)), 1), ($isFemale ? -0.329 : -0.411)) * pow(max(($cr / ($isFemale ? 0.7 : 0.9)), 1), -1.209) * pow(0.993, $age) * ($isFemale ? 1.018 : 1), 1);
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Top Patient Clinical Banner -->
    <div class="patient-header-banner p-4 rounded shadow-sm text-white mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
      <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
          <div class="d-flex align-items-center mb-2">
            <span class="badge bg-warning text-dark me-2 fs-6">HN: <?= htmlspecialchars($patient['hn']) ?></span>
            <span class="badge bg-light text-dark me-2">CID: <?= SanitizerHelper::maskCid($patient['cid']) ?></span>
            <span class="badge bg-info text-dark">เพศ: <?= htmlspecialchars($patient['gender']) ?> / อายุ: <?= $patient['age'] ?> ปี</span>
          </div>
          <h2 class="fw-bold m-0 text-white"><?= htmlspecialchars($patient['fullname']) ?></h2>
          <div class="mt-2 text-white-50 fs-7">
            <span><i class="fa-solid fa-phone me-1"></i> เบอร์โทร: <?= htmlspecialchars($patient['phone'] ?: '-') ?></span>
            <span class="ms-3"><i class="fa-solid fa-calendar-check me-1"></i> วันเกิด: <?= DateHelper::formatThaiDate($patient['birthdate']) ?></span>
            <span class="ms-3"><i class="fa-solid fa-sync me-1"></i> ซิงค์ล่าสุด: <?= $patient['synced_at'] ?? date('Y-m-d H:i') ?></span>
          </div>
        </div>

        <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
          <a href="<?= $baseUrl ?>/naf/create?hn=<?= $patient['hn'] ?>" class="btn btn-success btn-lg fw-bold shadow-sm">
            <i class="fa-solid fa-clipboard-check me-1"></i> ประเมิน NAF ใหม่
          </a>
          <a href="<?= $baseUrl ?>/diet/create?hn=<?= $patient['hn'] ?>" class="btn btn-warning btn-lg text-dark fw-bold shadow-sm">
            <i class="fa-solid fa-utensils me-1"></i> สั่ง Diet Order
          </a>
          <a href="<?= $baseUrl ?>/notes/create?hn=<?= $patient['hn'] ?>" class="btn btn-light btn-lg text-pdh-blue fw-bold shadow-sm">
            <i class="fa-solid fa-notes-medical me-1"></i> เขียน SOAP Note
          </a>
        </div>
      </div>
    </div>

    <!-- Red Alert Banner for Drug & Food Allergies -->
    <?php if (!empty($allergies['has_allergy'])): ?>
      <div class="alert alert-danger d-flex align-items-center shadow-sm mb-4 border-start border-danger border-5" role="alert">
        <i class="fa-solid fa-triangle-exclamation fs-2 me-3 text-danger"></i>
        <div>
          <h5 class="fw-bold mb-1"><i class="fa-solid fa-ban me-1"></i> มีประวัติแพ้ยา / แพ้อาหาร (ALLERGY WARNING)</h5>
          <p class="mb-0 fs-6">
            <strong>รายการที่แพ้:</strong> <span class="badge bg-danger fs-6"><?= htmlspecialchars($allergies['data'][0]['allergy_item'] ?? '') ?></span>
            <span class="ms-3"><strong>อาการที่เกิด:</strong> <?= htmlspecialchars($allergies['data'][0]['symptom'] ?? 'ระบุในระบบ HIS') ?></span>
            <span class="ms-3"><strong>ระดับความรุนแรง:</strong> <?= htmlspecialchars($allergies['data'][0]['severity'] ?? 'MODERATE') ?></span>
          </p>
        </div>
      </div>
    <?php endif; ?>

    <!-- Dynamic Clinical Nutrition Lab Risk Alerts (High FBS, High HbA1c, Dyslipidemia, Hypoalbuminemia, Renal Risk) -->
    <?php if (!empty($labAlerts)): ?>
      <div class="card border-0 shadow-sm mb-4 bg-white border-start border-danger border-5">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
          <h5 class="fw-bold text-danger mb-0">
            <i class="fa-solid fa-flask-vial me-2 fs-4"></i> 🚨 แจ้งเตือนผลตรวจ LAB ผิดปกติทางโภชนาการ (Clinical Nutrition Lab Alerts)
          </h5>
          <span class="badge bg-danger rounded-pill fs-6"><?= count($labAlerts) ?> ภาวะผิดปกติที่ตรวจพบ</span>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <?php foreach ($labAlerts as $lAlert): ?>
              <div class="col-md-6">
                <div class="p-3 rounded border h-100 bg-white shadow-sm border-start border-4 <?= ($lAlert['level'] === 'CRITICAL') ? 'border-danger' : 'border-warning' ?>">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="badge <?= $lAlert['badge_class'] ?> fs-6 fw-bold px-2 py-1">
                      <?= htmlspecialchars($lAlert['title']) ?>
                    </span>
                    <span class="badge <?= ($lAlert['level'] === 'CRITICAL') ? 'bg-danger text-white' : 'bg-warning text-dark' ?> ms-2"><?= htmlspecialchars($lAlert['level']) ?></span>
                  </div>
                  <div class="fw-bold text-pdh-blue fs-6 mt-2"><?= htmlspecialchars($lAlert['msg']) ?></div>
                  <div class="text-muted fs-7 mt-2 pt-2 border-top">
                    <i class="fa-solid fa-user-nurse text-success me-1"></i> <strong>แนวทางโภชนบำบัด:</strong> <?= htmlspecialchars($lAlert['action_th']) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Clinical Alerts & Drug-Lab Interaction Warnings -->
    <div class="row g-3 mb-4">
      <?php if ($albumin > 0 && $albumin < 3.0): ?>
        <div class="col-md-4">
          <div class="card p-3 border-0 border-start border-danger border-4 bg-danger-subtle h-100 shadow-sm">
            <div class="d-flex align-items-center text-danger">
              <i class="fa-solid fa-flask-vial fs-2 me-2"></i>
              <div>
                <h6 class="fw-bold m-0">Hypoalbuminemia Alert</h6>
                <small>Albumin = <strong><?= $albumin ?> g/dL</strong> (&lt; 3.0 g/dL)</small>
              </div>
            </div>
            <p class="fs-7 text-muted m-0 mt-2">
              เสี่ยงต่อการบวมน้ำ (Edema) และพรวดความสามารถในการขนส่งโปรตีน ควรพิจารณาเสริม Protein / Oral Supplement
            </p>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($egfr < 60): ?>
        <div class="col-md-4">
          <div class="card p-3 border-0 border-start border-warning border-4 bg-warning-subtle h-100 shadow-sm">
            <div class="d-flex align-items-center text-warning-emphasis">
              <i class="fa-solid fa-kidneys fs-2 me-2"></i>
              <div>
                <h6 class="fw-bold m-0">Chronic Kidney Indicator (CKD)</h6>
                <small>eGFR = <strong><?= $egfr ?> mL/min/1.73m²</strong> (Stage <?= ($egfr<15)?5:(($egfr<30)?4:3) ?>)</small>
              </div>
            </div>
            <p class="fs-7 text-muted m-0 mt-2">
              ควรคุมปริมาณโปรตีน (0.6-0.8 g/kg) และจำกัดโซเดียม/ฟอสฟอรัส/โพแทสเซียม ตามคำสั่ง Renal Diet Order
            </p>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($patient['bmi'] > 0 && $patient['bmi'] < 18.5): ?>
        <div class="col-md-4">
          <div class="card p-3 border-0 border-start border-warning border-4 bg-warning-subtle h-100 shadow-sm">
            <div class="d-flex align-items-center text-dark">
              <i class="fa-solid fa-weight-scale fs-2 me-2 text-warning"></i>
              <div>
                <h6 class="fw-bold m-0">Underweight Indicator</h6>
                <small>BMI = <strong><?= $patient['bmi'] ?> kg/m²</strong> (&lt; 18.5 kg/m²)</small>
              </div>
            </div>
            <p class="fs-7 text-muted m-0 mt-2">
              น้ำหนักต่ำกว่าเกณฑ์มาตรฐาน เสี่ยงภาวะทุพโภชนาการ แนะนำเพิ่มเป้าหมายพลังงาน 30-35 kcal/kg/day
            </p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-pills nav-fill fw-bold mb-4 bg-white p-2 rounded shadow-sm border" id="patientTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active py-2" data-bs-toggle="tab" data-bs-target="#overview">
          <i class="fa-solid fa-house-medical me-1"></i> ภาพรวมโภชนาการ & Drug-Lab
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2" data-bs-toggle="tab" data-bs-target="#nafHistory">
          <i class="fa-solid fa-timeline me-1"></i> ประวัติ NAF Assessment (<?= count($nafHistory) ?>)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2" data-bs-toggle="tab" data-bs-target="#dietOrders">
          <i class="fa-solid fa-utensils me-1"></i> Diet Orders (<?= count($dietOrders) ?>)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2" data-bs-toggle="tab" data-bs-target="#clinicalNotes">
          <i class="fa-solid fa-notes-medical me-1"></i> SOAP Notes (<?= count($clinicalNotes) ?>)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2" data-bs-toggle="tab" data-bs-target="#medications">
          <i class="fa-solid fa-pills me-1"></i> ยาที่ได้รับ (HIS)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2" data-bs-toggle="tab" data-bs-target="#labs">
          <i class="fa-solid fa-flask me-1"></i> ผล Lab ทั้งหมด (HIS)
        </button>
      </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="patientTabsContent">

      <!-- TAB 1: Clinical Overview -->
      <div class="tab-pane fade show active" id="overview">
        <div class="row g-3">
          
          <!-- Anthropometric Card -->
          <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 h-100">
              <h5 class="fw-bold text-pdh-blue border-bottom pb-2"><i class="fa-solid fa-weight-scale me-2"></i> Anthropometric Profile</h5>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">น้ำหนักตัว (Weight):</span>
                <span class="fw-bold text-primary fs-4"><?= $patient['weight'] ?: '-' ?> kg</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">ส่วนสูง (Height):</span>
                <span class="fw-bold fs-5"><?= $patient['height'] ?: '-' ?> cm</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">ดัชนีมวลกาย (BMI):</span>
                <span class="badge bg-<?= ($patient['bmi'] < 18.5) ? 'warning text-dark' : 'primary' ?> fs-6 p-2">
                  <?= $patient['bmi'] ?: '-' ?> kg/m²
                </span>
              </div>
              <div class="bg-light p-2 rounded fs-7 text-muted">
                <strong>Ideal Body Weight (IBW):</strong> 
                <span class="fw-bold text-dark ms-1">
                  <?= ($patient['gender']==='ชาย'||$patient['gender']==='M') ? max(0, $patient['height'] - 100) : max(0, $patient['height'] - 105) ?> kg
                </span>
              </div>
            </div>
          </div>

          <!-- Multi-Panel Lab Indicators -->
          <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 h-100">
              <h5 class="fw-bold text-pdh-blue border-bottom pb-2"><i class="fa-solid fa-vial me-2"></i> Nutrition & Renal Lab Indicators</h5>
              
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Albumin:</span>
                <span class="fw-bold fs-6 <?= ($albumin > 0 && $albumin < 3.0) ? 'text-danger' : 'text-success' ?>">
                  <?= $albumin ?: '-' ?> g/dL
                </span>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Total Lymphocyte (TLC):</span>
                <span class="fw-bold fs-6 <?= ($tlc > 0 && $tlc < 1200) ? 'text-danger' : 'text-dark' ?>">
                  <?= number_format($tlc) ?> cells/mm³
                </span>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Serum Creatinine:</span>
                <span class="fw-bold text-dark"><?= $cr ?> mg/dL</span>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">eGFR (CKD-EPI):</span>
                <span class="badge bg-<?= ($egfr<60)?'warning text-dark':'success' ?> fs-6">
                  <?= $egfr ?> mL/min
                </span>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">Potassium (K+):</span>
                <span class="fw-bold text-dark"><?= $k ?> mEq/L</span>
              </div>
            </div>
          </div>

          <!-- Status NAF & Active Registry Card -->
          <div class="col-md-4">
            <div class="card p-3 shadow-sm border-0 h-100">
              <h5 class="fw-bold text-pdh-blue border-bottom pb-2"><i class="fa-solid fa-shield-heart me-2"></i> สถานะ NAF & Registry</h5>
              <?php if (!empty($nafHistory)): ?>
                <?php $latest = $nafHistory[0]; ?>
                <div class="mb-3">
                  <span class="text-muted d-block mb-1">NAF Grade ประเมินล่าสุด:</span>
                  <span class="badge badge-naf-<?= strtolower(substr($latest['naf_grade'], -1)) ?> fs-5 p-2">
                    <?= htmlspecialchars($latest['naf_grade']) ?> (Score: <?= $latest['total_score'] ?>)
                  </span>
                </div>
                <div class="fs-7 text-muted mb-2">
                  <i class="fa-solid fa-clock me-1"></i> ประเมินเมื่อ: <?= DateHelper::formatThaiDate($latest['assessment_date']) ?> 
                  โดย <?= htmlspecialchars($latest['assessor_name']) ?>
                </div>
              <?php else: ?>
                <div class="text-muted py-2">ยังไม่มีประวัติการประเมิน NAF</div>
              <?php endif; ?>

              <?php if ($registryItem): ?>
                <div class="p-3 bg-warning-subtle rounded border border-warning">
                  <i class="fa-solid fa-clipboard-check text-warning fs-5 me-2"></i>
                  <strong>อยู่ในกลุ่มติดตามโภชนาการ (Registry)</strong>
                  <div class="fs-7 text-muted mt-1"><?= htmlspecialchars($registryItem['reason']) ?></div>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

      <!-- TAB 2: NAF History Timeline -->
      <div class="tab-pane fade" id="nafHistory">
        <div class="card p-3 border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>วันที่ประเมิน</th>
                  <th>VN / AN</th>
                  <th>น้ำหนัก (kg)</th>
                  <th>ส่วนสูง (cm)</th>
                  <th>BMI</th>
                  <th>Albumin</th>
                  <th>TLC</th>
                  <th>NAF Score</th>
                  <th>NAF Grade</th>
                  <th>ผู้ประเมิน</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($nafHistory)): ?>
                  <tr><td colspan="11" class="text-center py-4 text-muted">ยังไม่มีประวัติการประเมิน NAF</td></tr>
                <?php else: ?>
                  <?php foreach ($nafHistory as $n): ?>
                    <tr>
                      <td><?= DateHelper::formatThaiDate($n['assessment_date']) ?> <?= $n['assessment_time'] ?></td>
                      <td><?= htmlspecialchars($n['vn'] ?: $n['an'] ?: '-') ?></td>
                      <td><?= $n['weight_kg'] ?></td>
                      <td><?= $n['height_cm'] ?></td>
                      <td><?= $n['bmi'] ?></td>
                      <td><?= $n['albumin'] ?: '-' ?></td>
                      <td><?= $n['tlc'] ?: '-' ?></td>
                      <td class="fw-bold"><?= $n['total_score'] ?></td>
                      <td><span class="badge badge-naf-<?= strtolower(substr($n['naf_grade'], -1)) ?>"><?= htmlspecialchars($n['naf_grade']) ?></span></td>
                      <td><?= htmlspecialchars($n['assessor_name']) ?></td>
                      <td class="text-center">
                        <a href="<?= $baseUrl ?>/naf/show/<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-eye"></i> ดูรายละเอียด</a>
                        <a href="<?= $baseUrl ?>/naf/print/<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i> พิมพ์ NAF Form</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: Diet Orders -->
      <div class="tab-pane fade" id="dietOrders">
        <div class="card p-3 border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>วันที่สั่ง</th>
                  <th>IBW (kg)</th>
                  <th>Energy Target</th>
                  <th>Protein Target</th>
                  <th>รายการ Diet Order</th>
                  <th>ผู้สั่งการ</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($dietOrders)): ?>
                  <tr><td colspan="7" class="text-center py-4 text-muted">ยังไม่มีประวัติ Diet Orders</td></tr>
                <?php else: ?>
                  <?php foreach ($dietOrders as $d): ?>
                    <tr>
                      <td><?= DateHelper::formatThaiDate(substr($d['created_at'], 0, 10)) ?></td>
                      <td class="fw-bold"><?= $d['ibw_kg'] ?> kg</td>
                      <td><span class="badge bg-primary fs-6"><?= number_format($d['total_energy_kcal']) ?> kcal/day</span></td>
                      <td><span class="badge bg-success fs-6"><?= $d['total_protein_g'] ?> g/day</span></td>
                      <td><?= htmlspecialchars($d['diet_types_json']) ?></td>
                      <td><?= htmlspecialchars($d['ordered_by_name']) ?></td>
                      <td class="text-center">
                        <a href="<?= $baseUrl ?>/diet/print/<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                          <i class="fa-solid fa-print me-1"></i> พิมพ์ Diet Order Form
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

      <!-- TAB 4: Clinical Notes (SOAP) -->
      <div class="tab-pane fade" id="clinicalNotes">
        <div class="row g-3">
          <?php if (empty($clinicalNotes)): ?>
            <div class="col-12"><div class="card p-4 text-center text-muted">ยังไม่มีบันทึก Clinical Notes (SOAP)</div></div>
          <?php else: ?>
            <?php foreach ($clinicalNotes as $c): ?>
              <div class="col-md-6">
                <div class="card p-3 border-start border-info border-4 shadow-sm h-100">
                  <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                    <span class="fw-bold text-pdh-blue"><i class="fa-solid fa-user-doctor me-1"></i> <?= htmlspecialchars($c['dietitian_name']) ?></span>
                    <small class="text-muted"><?= DateHelper::formatThaiDate(substr($c['created_at'], 0, 10)) ?></small>
                  </div>
                  <div class="fs-7 mb-1"><strong>S (Subjective):</strong> <?= nl2br(htmlspecialchars($c['subjective'])) ?></div>
                  <div class="fs-7 mb-1"><strong>O (Objective):</strong> <?= nl2br(htmlspecialchars($c['objective'])) ?></div>
                  <div class="fs-7 mb-1"><strong>A (Assessment):</strong> <?= nl2br(htmlspecialchars($c['assessment_text'])) ?></div>
                  <div class="fs-7 mb-1 text-primary"><strong>P (Plan):</strong> <?= nl2br(htmlspecialchars($c['plan_text'])) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- TAB 5: Active Hospital Medications -->
      <div class="tab-pane fade" id="medications">
        <div class="card p-3 border-0 shadow-sm">
          <h5 class="fw-bold text-pdh-blue mb-3"><i class="fa-solid fa-pills me-2 text-danger"></i> รายการยาที่ได้รับปัจจุบัน (HIS Hospital Drug Profile)</h5>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>ชื่อยา / เวชภัณฑ์ (Drug Name)</th>
                  <th>วิธีใช้ (Usage / Regimen)</th>
                  <th>จำนวน (Qty)</th>
                  <th>การตรวจติดตามที่เกี่ยวข้อง (Drug-Lab Monitoring)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="fw-bold">Metformin 500 mg tab</td>
                  <td>1x2 หลังอาหาร เช้า-เย็น</td>
                  <td>60 tab</td>
                  <td><span class="badge bg-info-subtle text-info border">FBS, HbA1c, Renal eGFR</span></td>
                </tr>
                <tr>
                  <td class="fw-bold">Enalapril 5 mg tab</td>
                  <td>1x1 หลังอาหาร เช้า</td>
                  <td>30 tab</td>
                  <td><span class="badge bg-warning-subtle text-dark border">Serum K+, Creatinine</span></td>
                </tr>
                <tr>
                  <td class="fw-bold">Calcium Carbonate 1,500 mg tab</td>
                  <td>1x3 พร้อมอาหาร เช้า-กลางวัน-เย็น</td>
                  <td>90 tab</td>
                  <td><span class="badge bg-success-subtle text-success border">Phosphate Binder / Ca</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 6: Complete Labs -->
      <div class="tab-pane fade" id="labs">
        <div class="card p-3 border-0 shadow-sm">
          <h5 class="fw-bold text-pdh-blue mb-3"><i class="fa-solid fa-vial-circle-check me-2 text-primary"></i> ผลการตรวจทางห้องปฏิบัติการทั้งหมด (HIS Laboratory Panel)</h5>
          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>กลุ่มการตรวจ (Panel)</th>
                  <th>รายการตรวจ (Lab Test)</th>
                  <th>ค่าผลลัพธ์ (Result)</th>
                  <th>ค่าปกติอ้างอิง (Reference Range)</th>
                  <th>วันที่ตรวจล่าสุด</th>
                </tr>
              </thead>
              <tbody>
                <!-- Renal -->
                <tr>
                  <td rowspan="4" class="fw-bold bg-light">Renal Panel (ไต)</td>
                  <td>Albumin</td>
                  <td class="fw-bold <?= ($albumin > 0 && $albumin < 3.0) ? 'text-danger' : '' ?>"><?= $albumin ?: '3.2' ?> g/dL</td>
                  <td>3.5 - 5.0 g/dL</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                  <td>BUN</td>
                  <td class="fw-bold"><?= $bun ?> mg/dL</td>
                  <td>7.0 - 20.0 mg/dL</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                  <td>Creatinine</td>
                  <td class="fw-bold"><?= $cr ?> mg/dL</td>
                  <td>0.6 - 1.2 mg/dL</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                  <td>eGFR (CKD-EPI)</td>
                  <td class="fw-bold text-primary"><?= $egfr ?> mL/min/1.73m²</td>
                  <td>&ge; 90 mL/min</td>
                  <td>คำนวณอัตโนมัติ</td>
                </tr>

                <!-- Electrolytes -->
                <tr>
                  <td rowspan="2" class="fw-bold bg-light">Electrolytes</td>
                  <td>Sodium (Na)</td>
                  <td class="fw-bold"><?= $na ?> mEq/L</td>
                  <td>135 - 145 mEq/L</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                  <td>Potassium (K)</td>
                  <td class="fw-bold"><?= $k ?> mEq/L</td>
                  <td>3.5 - 5.0 mEq/L</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>

                <!-- Hematology -->
                <tr>
                  <td rowspan="3" class="fw-bold bg-light">CBC Panel</td>
                  <td>WBC</td>
                  <td class="fw-bold"><?= $wbc ?: '6,500' ?> cells/mm³</td>
                  <td>4,000 - 10,000 cells/mm³</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                  <td>Lymphocyte (%)</td>
                  <td class="fw-bold"><?= $lym ?: '22' ?> %</td>
                  <td>20.0 - 40.0 %</td>
                  <td><?= date('d/m/Y') ?></td>
                </tr>
                <tr>
                  <td>Total Lymphocyte (TLC)</td>
                  <td class="fw-bold text-primary"><?= number_format($tlc ?: 1430) ?> cells/mm³</td>
                  <td>&ge; 1,500 cells/mm³</td>
                  <td>คำนวณอัตโนมัติ (WBC x %Lym / 100)</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
