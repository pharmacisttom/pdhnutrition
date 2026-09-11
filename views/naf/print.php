<?php
use App\Helpers\DateHelper;
use App\Helpers\SanitizerHelper;

// Extract selected items for current assessment
$ansCodes = [];
foreach ($answers as $a) {
    $ansCodes[$a['item_code']] = $a['score_given'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Nutrition Alert Form (NAF) - โรงพยาบาลปลวกแดง</title>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= $baseUrl ?>/public/assets/css/print.css" rel="stylesheet">
  <style>
    body { font-family: 'Sarabun', sans-serif; font-size: 10pt; color: #000; background: #fff; }
    .naf-header { border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 8px; }
    .naf-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 9.5pt; }
    .naf-table th, .naf-table td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
    .naf-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
    .score-col { width: 48px; text-align: center; background-color: #fafafa; }
    .check-box { display: inline-block; width: 13px; height: 13px; border: 1px solid #000; text-align: center; line-height: 11px; font-size: 10px; font-weight: bold; margin-right: 3px; }
    .check-box.checked::after { content: "✓"; }
    .section-title { font-weight: bold; background-color: #f8f9fa; }
    .naf-action-box { border: 1px solid #000; padding: 6px; font-size: 8.5pt; border-radius: 4px; height: 100%; }
    .badge-circle { display: inline-flex; width: 28px; height: 28px; border-radius: 50%; color: #fff; align-items: center; justify-content: center; font-weight: bold; font-size: 14pt; }
    .badge-a { background-color: #198754; }
    .badge-b { background-color: #ffc107; color: #000 !important; }
    .badge-c { background-color: #dc3545; }
    @media print {
      .no-print { display: none !important; }
      body { padding: 0; margin: 0; }
      .container-print { width: 100%; max-width: 100%; padding: 0; margin: 0; }
    }
  </style>
</head>
<body class="p-3">

<div class="no-print mb-3 text-end">
  <button onclick="window.print()" class="btn btn-primary btn-lg fw-bold">
    <i class="fa-solid fa-print"></i> พิมพ์แบบประเมิน NAF (Print Form)
  </button>
  <a href="<?= $baseUrl ?>/naf/<?= $assessment['id'] ?>" class="btn btn-outline-secondary btn-lg ms-2">
    ย้อนกลับ (Back)
  </a>
</div>

<div class="container-print m-auto">

  <!-- HEADER -->
  <div class="d-flex justify-content-between align-items-center naf-header">
    <div class="d-flex align-items-center">
      <img src="<?= $baseUrl ?>/public/assets/img/logo.png" alt="PDH Logo" style="height: 45px;" class="me-2" onerror="this.style.display='none'">
      <div>
        <h4 class="fw-bold m-0" style="font-size: 15pt;">NUTRITION ALERT FORM แบบประเมินภาวะโภชนาการ</h4>
        <small class="text-muted">โรงพยาบาลปลวกแดง Pluakdaeng Hospital Clinical Nutrition</small>
      </div>
    </div>
    <div class="text-end" style="font-size: 8pt; line-height: 1.1;">
      <strong>SPNT</strong><br>
      <span class="text-muted">Better Nutrition for Better Life</span>
    </div>
  </div>

  <!-- PATIENT INFO METADATA BLOCK -->
  <div class="border p-2 mb-2 bg-light" style="font-size: 9.5pt;">
    <div class="row g-1">
      <div class="col-6">
        <strong>ชื่อ-สกุล:</strong> <?= htmlspecialchars($assessment['fullname']) ?>
        <span class="ms-3"><strong>เพศ:</strong>
          <span class="check-box <?= ($assessment['gender'] === 'ชาย' || $assessment['gender'] === 'M') ? 'checked' : '' ?>"></span> ชาย
          <span class="check-box <?= ($assessment['gender'] === 'หญิง' || $assessment['gender'] === 'F') ? 'checked' : '' ?>"></span> หญิง
        </span>
      </div>
      <div class="col-3"><strong>อายุ:</strong> <?= $assessment['age'] ?> ปี</div>
      <div class="col-3"><strong>HN:</strong> <?= htmlspecialchars($assessment['hn']) ?></div>
    </div>
    <div class="row g-1 mt-1">
      <div class="col-4"><strong>วัน/เดือน/ปีที่รับ:</strong> <?= DateHelper::formatThaiDate($assessment['assessment_date']) ?></div>
      <div class="col-4"><strong>การวินิจฉัยเบื้องต้น:</strong> <?= htmlspecialchars($assessment['diagnosis'] ?: '-') ?></div>
      <div class="col-4">
        <strong>ข้อมูลจาก:</strong>
        <span class="check-box checked"></span> ผู้ป่วย
        <span class="check-box"></span> ญาติ
        <span class="check-box"></span> อื่นๆ
      </div>
    </div>
  </div>

  <div class="text-muted mb-2" style="font-size: 8.5pt;">
    <strong>คำชี้แนะ:</strong> ทำเครื่องหมาย <span class="fw-bold">✓</span> ในช่องโดยเลือกเพียง 1 ช่องในแต่ละหัวข้อใหญ่และหัวข้อย่อย (ยกเว้น 6, 8 เลือกได้มากกว่า 1 ช่อง) และใส่คะแนนในช่อง
  </div>

  <!-- MAIN ASSESSMENT FORM TABLE -->
  <table class="naf-table">
    <thead>
      <tr>
        <th>หัวข้อการประเมิน (Assessment Items)</th>
        <th class="score-col">คะแนน<br>ครั้งที่ 1</th>
        <th class="score-col">คะแนน<br>ครั้งที่ 2</th>
        <th class="score-col">คะแนน<br>ครั้งที่ 3</th>
      </tr>
    </thead>
    <tbody>

      <!-- 1. ส่วนสูง/ Arm span -->
      <tr>
        <td>
          <strong class="section-title">1. ส่วนสูง/ ความยาวตัว/ ความยาวช่วงแขนจากปลายนิ้วกลางทั้ง 2 ข้าง (Arm span)</strong><br>
          <span class="ms-2">วัดส่วนสูง: <u><?= $assessment['height_cm'] ?: '......' ?></u> ซม.</span>
          <span class="ms-3">วัดความยาวตัว: <u>......</u> ซม.</span>
          <span class="ms-3">Arm span: <u><?= $assessment['arm_span_cm'] ?: '......' ?></u> ซม.</span>
          <span class="ms-3">ญาติบอก: <u>......</u> ซม.</span>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 2. น้ำหนักและ BMI -->
      <tr>
        <td>
          <strong class="section-title">2. น้ำหนักและค่าดรรชนีมวลกาย (BMI) = น้ำหนัก (กก.) / ส่วนสูง (ม.)²</strong><br>
          <div class="ms-2">
            <strong>2.1 น้ำหนัก:</strong>
            <span class="check-box checked"></span> ชั่งในท่านั่ง (1)
            <span class="check-box"></span> ชั่งในท่ายืน (0)
            <span class="check-box"></span> ชั่งไม่ได้ (0)
            <span class="check-box"></span> ญาติบอก (0)
            <span class="ms-3">น้ำหนัก: <strong><?= $assessment['weight_kg'] ?></strong> กก.</span>
          </div>
          <div class="ms-2 mt-1">
            <strong>2.2 BMI:</strong>
            <span class="check-box <?= (isset($ansCodes['BMI_LT_17'])) ? 'checked' : '' ?>"></span> BMI &lt; 17.0 (2)
            <span class="check-box <?= (isset($ansCodes['BMI_17_18'])) ? 'checked' : '' ?>"></span> BMI 17.0-18.0 (1)
            <span class="check-box <?= (isset($ansCodes['BMI_18_30'])) ? 'checked' : '' ?>"></span> BMI 18.1-29.9 (0)
            <span class="check-box <?= (isset($ansCodes['BMI_GE_30'])) ? 'checked' : '' ?>"></span> BMI &ge; 30.0 (1)
          </div>
          <div class="ms-2 text-muted mt-1" style="font-size: 8.5pt;">
            <em>หากไม่ทราบน้ำหนัก ใช้ผล Albumin หรือ ผล Total Lymphocyte Count (TLC)</em><br>
            <strong>2.1 ผล Albumin:</strong>
            <span class="check-box <?= (isset($ansCodes['ALB_LE_2_5'])) ? 'checked' : '' ?>"></span> &le; 2.5 g/dl (3)
            <span class="check-box <?= (isset($ansCodes['ALB_2_6_2_9'])) ? 'checked' : '' ?>"></span> 2.6-2.9 g/dl (2)
            <span class="check-box <?= (isset($ansCodes['ALB_3_0_3_5'])) ? 'checked' : '' ?>"></span> 3.0-3.5 g/dl (1)
            <span class="check-box <?= (isset($ansCodes['ALB_GT_3_5'])) ? 'checked' : '' ?>"></span> &gt; 3.5 g/dl (0)
            <br>
            <strong>2.2 ผล TLC:</strong>
            <span class="check-box <?= (isset($ansCodes['TLC_LE_1000'])) ? 'checked' : '' ?>"></span> &le; 1,000 (3)
            <span class="check-box <?= (isset($ansCodes['TLC_1001_1200'])) ? 'checked' : '' ?>"></span> 1,001-1,200 (2)
            <span class="check-box <?= (isset($ansCodes['TLC_1201_1500'])) ? 'checked' : '' ?>"></span> 1,201-1,500 (1)
            <span class="check-box <?= (isset($ansCodes['TLC_GT_1500'])) ? 'checked' : '' ?>"></span> &gt; 1,500 (0)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $sec2Score = 0;
            foreach ($ansCodes as $k => $s) {
                if (str_starts_with($k, 'BMI_') || str_starts_with($k, 'ALB_') || str_starts_with($k, 'TLC_')) $sec2Score += $s;
            }
            echo $sec2Score;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 3. รูปร่างของผู้ป่วย -->
      <tr>
        <td>
          <strong class="section-title">3. รูปร่างของผู้ป่วย</strong><br>
          <div class="ms-2">
            <span class="check-box <?= (isset($ansCodes['APPEARANCE_VERY_THIN'])) ? 'checked' : '' ?>"></span> ผอมมาก (2)
            <span class="check-box <?= (isset($ansCodes['APPEARANCE_THIN'])) ? 'checked' : '' ?>"></span> ผอม (1)
            <span class="check-box <?= (isset($ansCodes['APPEARANCE_VERY_OBESE'])) ? 'checked' : '' ?>"></span> อ้วนมาก (1)
            <span class="check-box <?= (isset($ansCodes['APPEARANCE_NORMAL'])) ? 'checked' : '' ?>"></span> ปกติ-อ้วนปานกลาง (0)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $s3 = 0;
            foreach (['APPEARANCE_VERY_THIN', 'APPEARANCE_THIN', 'APPEARANCE_VERY_OBESE', 'APPEARANCE_NORMAL'] as $k) {
                if (isset($ansCodes[$k])) $s3 += $ansCodes[$k];
            }
            echo $s3;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 4. น้ำหนักเปลี่ยนใน 4 สัปดาห์ -->
      <tr>
        <td>
          <strong class="section-title">4. น้ำหนักเปลี่ยนใน 4 สัปดาห์</strong><br>
          <div class="ms-2">
            <span class="check-box <?= (isset($ansCodes['WT_CHANGE_DECREASED'])) ? 'checked' : '' ?>"></span> ลดลง/ผอมลง (2)
            <span class="check-box <?= (isset($ansCodes['WT_CHANGE_INCREASED'])) ? 'checked' : '' ?>"></span> เพิ่มขึ้น/อ้วนขึ้น (1)
            <span class="check-box <?= (isset($ansCodes['WT_CHANGE_UNKNOWN'])) ? 'checked' : '' ?>"></span> ไม่ทราบ (0)
            <span class="check-box <?= (isset($ansCodes['WT_CHANGE_SAME'])) ? 'checked' : '' ?>"></span> คงเดิม (0)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $s4 = 0;
            foreach (['WT_CHANGE_DECREASED', 'WT_CHANGE_INCREASED', 'WT_CHANGE_UNKNOWN', 'WT_CHANGE_SAME'] as $k) {
                if (isset($ansCodes[$k])) $s4 += $ansCodes[$k];
            }
            echo $s4;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 5. อาหารที่กินในช่วง 2 สัปดาห์ที่ผ่านมา -->
      <tr>
        <td>
          <strong class="section-title">5. อาหารที่กินในช่วง 2 สัปดาห์ที่ผ่านมา</strong><br>
          <div class="ms-2">
            <strong>5.1 ลักษณะอาหาร:</strong>
            <span class="check-box <?= (isset($ansCodes['DIET_TYPE_LIQUID_WATER'])) ? 'checked' : '' ?>"></span> อาหารน้ำๆ (2)
            <span class="check-box <?= (isset($ansCodes['DIET_TYPE_FULL_LIQUID'])) ? 'checked' : '' ?>"></span> อาหารเหลวๆ (2)
            <span class="check-box <?= (isset($ansCodes['DIET_TYPE_SOFT'])) ? 'checked' : '' ?>"></span> อาหารนุ่มกว่าปกติ (1)
            <span class="check-box <?= (isset($ansCodes['DIET_TYPE_REGULAR'])) ? 'checked' : '' ?>"></span> อาหารเหมือนปกติ (0)
          </div>
          <div class="ms-2 mt-1">
            <strong>5.2 ปริมาณที่กิน:</strong>
            <span class="check-box <?= (isset($ansCodes['INTAKE_VERY_LITTLE'])) ? 'checked' : '' ?>"></span> กินน้อยมาก (2)
            <span class="check-box <?= (isset($ansCodes['INTAKE_LESS'])) ? 'checked' : '' ?>"></span> กินน้อยลง (1)
            <span class="check-box <?= (isset($ansCodes['INTAKE_MORE'])) ? 'checked' : '' ?>"></span> กินมากขึ้น (0)
            <span class="check-box <?= (isset($ansCodes['INTAKE_SAME'])) ? 'checked' : '' ?>"></span> กินเท่าเดิม (0)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $s5 = 0;
            foreach ($ansCodes as $k => $s) {
                if (str_starts_with($k, 'DIET_TYPE_') || str_starts_with($k, 'INTAKE_')) $s5 += $s;
            }
            echo $s5;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 6. อาการต่อเนื่อง > 2 สัปดาห์ที่ผ่านมา -->
      <tr>
        <td>
          <strong class="section-title">6. อาการต่อเนื่อง &gt; 2 สัปดาห์ที่ผ่านมา (เลือกได้มากกว่า 1 ช่อง)</strong><br>
          <div class="ms-2">
            <strong>6.1 ปัญหาการเคี้ยว/กลืน:</strong>
            <span class="check-box <?= (isset($ansCodes['SWALLOW_CHOKE'])) ? 'checked' : '' ?>"></span> สำลัก (2)
            <span class="check-box <?= (isset($ansCodes['SWALLOW_DIFFICULT_TUBE'])) ? 'checked' : '' ?>"></span> เคี้ยว/กลืนลำบาก/ทางสายยาง (2)
            <span class="check-box <?= (isset($ansCodes['SWALLOW_NORMAL'])) ? 'checked' : '' ?>"></span> กลืนได้ปกติ (0)
          </div>
          <div class="ms-2 mt-1">
            <strong>6.2 ปัญหาระบบทางเดินอาหาร:</strong>
            <span class="check-box <?= (isset($ansCodes['GI_DIARRHEA'])) ? 'checked' : '' ?>"></span> ท้องเสีย (2)
            <span class="check-box <?= (isset($ansCodes['GI_ABDOMINAL_PAIN'])) ? 'checked' : '' ?>"></span> ปวดท้อง (2)
            <span class="check-box <?= (isset($ansCodes['GI_NORMAL'])) ? 'checked' : '' ?>"></span> ปกติ (0)
          </div>
          <div class="ms-2 mt-1">
            <strong>6.3 ปัญหาระหว่างกินอาหาร:</strong>
            <span class="check-box <?= (isset($ansCodes['EATING_VOMITING'])) ? 'checked' : '' ?>"></span> อาเจียน (2)
            <span class="check-box <?= (isset($ansCodes['EATING_NAUSEA'])) ? 'checked' : '' ?>"></span> คลื่นไส้ (2)
            <span class="check-box <?= (isset($ansCodes['EATING_NORMAL'])) ? 'checked' : '' ?>"></span> ปกติ (0)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $s6 = 0;
            foreach ($ansCodes as $k => $s) {
                if (str_starts_with($k, 'SWALLOW_') || str_starts_with($k, 'GI_') || str_starts_with($k, 'EATING_')) $s6 += $s;
            }
            echo $s6;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 7. ความสามารถในการเข้าถึงอาหาร -->
      <tr>
        <td>
          <strong class="section-title">7. ความสามารถในการเข้าถึงอาหาร</strong><br>
          <div class="ms-2">
            <span class="check-box <?= (isset($ansCodes['ACCESS_BEDRIDDEN'])) ? 'checked' : '' ?>"></span> นอนติดเตียง (2)
            <span class="check-box <?= (isset($ansCodes['ACCESS_NEED_ASSIST'])) ? 'checked' : '' ?>"></span> ต้องมีผู้ช่วยบ้าง (1)
            <span class="check-box <?= (isset($ansCodes['ACCESS_SITTING'])) ? 'checked' : '' ?>"></span> นั่งๆ นอนๆ (0)
            <span class="check-box <?= (isset($ansCodes['ACCESS_NORMAL'])) ? 'checked' : '' ?>"></span> ปกติ (0)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $s7 = 0;
            foreach (['ACCESS_BEDRIDDEN', 'ACCESS_NEED_ASSIST', 'ACCESS_SITTING', 'ACCESS_NORMAL'] as $k) {
                if (isset($ansCodes[$k])) $s7 += $ansCodes[$k];
            }
            echo $s7;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- 8. โรคที่เป็นอยู่ -->
      <tr>
        <td>
          <strong class="section-title">8. โรคที่เป็นอยู่ โดยต้องแจ้งให้นักกำหนดอาหาร/นักโภชนาการทราบ (เลือกได้มากกว่า 1 ช่อง)</strong><br>
          <div class="ms-2">
            <strong>โรคที่มีความรุนแรงน้อยถึงปานกลาง (3 คะแนน):</strong><br>
            <span class="check-box <?= (isset($ansCodes['DISEASE_DM'])) ? 'checked' : '' ?>"></span> DM (เบาหวาน) (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_CKD_ESRD'])) ? 'checked' : '' ?>"></span> CKD-ESRD (ไตเรื้อรัง) (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_SEPTICEMIA'])) ? 'checked' : '' ?>"></span> Septicemia (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_SOLID_CANCER'])) ? 'checked' : '' ?>"></span> Solid cancer (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_CHF'])) ? 'checked' : '' ?>"></span> CHF (หัวใจล้มเหลว) (3)<br>
            <span class="check-box <?= (isset($ansCodes['DISEASE_HIP_FX'])) ? 'checked' : '' ?>"></span> Hip fracture (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_COPD'])) ? 'checked' : '' ?>"></span> COPD (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_HEAD_INJURY'])) ? 'checked' : '' ?>"></span> Severe head injury (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_BURN_GE_2'])) ? 'checked' : '' ?>"></span> &ge; 2° burn (3) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_CLD_CIRRHOSIS'])) ? 'checked' : '' ?>"></span> CLD/Cirrhosis (3)
          </div>
          <div class="ms-2 mt-1">
            <strong>โรคที่มีความรุนแรงมาก (6 คะแนน):</strong><br>
            <span class="check-box <?= (isset($ansCodes['DISEASE_SEVERE_PNEUMONIA'])) ? 'checked' : '' ?>"></span> Severe pneumonia (6) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_CRITICALLY_ILL'])) ? 'checked' : '' ?>"></span> Critically ill (6) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_MULTIPLE_FX'])) ? 'checked' : '' ?>"></span> Multiple fracture (6) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_STROKE_CVA'])) ? 'checked' : '' ?>"></span> Stroke/CVA (6) &nbsp;
            <span class="check-box <?= (isset($ansCodes['DISEASE_MALIGNANT_HEMA'])) ? 'checked' : '' ?>"></span> Malignant hematologic/BMT (6)
          </div>
        </td>
        <td class="score-col fw-bold">
          <?php
            $s8 = 0;
            foreach ($ansCodes as $k => $s) {
                if (str_starts_with($k, 'DISEASE_')) $s8 += $s;
            }
            echo $s8;
          ?>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- TOTAL SCORE ROW -->
      <tr class="fw-bold table-light" style="font-size: 10.5pt;">
        <td class="text-end">คะแนนรวมทั้งหมด (NAF Total Score)</td>
        <td class="score-col text-primary fs-6"><?= $assessment['total_score'] ?></td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

      <!-- NAF GRADE ROW -->
      <tr class="fw-bold" style="font-size: 11pt;">
        <td class="text-end">เกณฑ์แปลผล (NAF Grade)</td>
        <td class="score-col">
          <?php
            $gLetter = substr($assessment['naf_grade'], -1);
            $bgCls = ($gLetter === 'A') ? 'badge-a' : (($gLetter === 'B') ? 'badge-b' : 'badge-c');
          ?>
          <span class="badge-circle <?= $bgCls ?>"><?= $gLetter ?></span>
        </td>
        <td class="score-col">-</td>
        <td class="score-col">-</td>
      </tr>

    </tbody>
  </table>

  <!-- CLINICAL RECOMMENDATION GUIDELINE BOXES (A, B, C) -->
  <div class="row g-2 mt-2">
    <div class="col-4">
      <div class="naf-action-box bg-light">
        <div class="d-flex align-items-center mb-1">
          <span class="badge-circle badge-a me-2">A</span>
          <strong>0-5 คะแนน (NAF = A)</strong>
        </div>
        <small class="text-muted">Normal - Mild Malnutrition</small>
        <p class="m-0 mt-1" style="line-height: 1.2;">
          ไม่พบความเสี่ยงต่อการเกิดภาวะโภชนาการ พยาบาลจะทำหน้าที่ประเมินภาวะโภชนาการซ้ำภายใน 7 วัน
        </p>
      </div>
    </div>

    <div class="col-4">
      <div class="naf-action-box bg-light">
        <div class="d-flex align-items-center mb-1">
          <span class="badge-circle badge-b me-2">B</span>
          <strong>6-10 คะแนน (NAF = B)</strong>
        </div>
        <small class="text-muted">Moderate Malnutrition</small>
        <p class="m-0 mt-1" style="line-height: 1.2;">
          กรุณาแจ้งให้แพทย์และนักกำหนดอาหาร/นักโภชนาการทราบ ให้นักโภชนาการประเมินโภชนาการ และให้แพทย์ดูแลรักษาภายใน 3 วัน
        </p>
      </div>
    </div>

    <div class="col-4">
      <div class="naf-action-box bg-light">
        <div class="d-flex align-items-center mb-1">
          <span class="badge-circle badge-c me-2">C</span>
          <strong>&ge; 11 คะแนน (NAF = C)</strong>
        </div>
        <small class="text-muted">Severe Malnutrition</small>
        <p class="m-0 mt-1" style="line-height: 1.2;">
          กรุณาแจ้งให้แพทย์และนักกำหนดอาหาร/นักโภชนาการทราบ ให้นักโภชนาการประเมินโภชนาการ และให้แพทย์ดูแลรักษาภายใน 24 ชั่วโมง
        </p>
      </div>
    </div>
  </div>

  <!-- SIGNATURE & REFERENCE FOOTER -->
  <div class="d-flex justify-content-between align-items-end mt-3 border-top pt-2" style="font-size: 8.5pt;">
    <div>
      <em>Reference: Surat Komindr, et al. Simplified malnutrition tool for Thai patients Asia Pac J Clin Nutr 2013;22(4):516-521</em>
    </div>
    <div class="text-center" style="width: 250px;">
      ลงชื่อ.........................................................................ผู้ประเมิน<br>
      ( <?= htmlspecialchars($assessment['assessor_name']) ?> )<br>
      นักโภชนาการ / บุคลากรทางการแพทย์ โรงพยาบาลปลวกแดง
    </div>
  </div>

</div>

</body>
</html>
