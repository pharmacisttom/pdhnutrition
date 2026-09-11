<?php
use App\Helpers\DateHelper;
use App\Helpers\SanitizerHelper;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Guide to Make Diet Order - โรงพยาบาลปลวกแดง</title>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= $baseUrl ?>/public/assets/css/print.css" rel="stylesheet">
  <style>
    body { font-family: 'Sarabun', sans-serif; font-size: 10pt; color: #000; background: #fff; }
    .diet-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9.5pt; }
    .diet-table th, .diet-table td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
    .diet-table th { background-color: #e9ecef; text-align: center; font-weight: bold; }
    .check-box { display: inline-block; width: 13px; height: 13px; border: 1px solid #000; text-align: center; line-height: 11px; font-size: 10px; font-weight: bold; margin-right: 3px; }
    .check-box.checked::after { content: "✓"; }
    .ref-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-top: 8px; text-align: center; }
    .ref-table th, .ref-table td { border: 1px solid #666; padding: 2px 1px; }
    .ref-table th { background-color: #f0f0f0; }
    .grey-bg { background-color: #e9ecef; }
    @media print {
      .no-print { display: none !important; }
      body { padding: 0; margin: 0; }
    }
  </style>
</head>
<body class="p-3">

<div class="no-print mb-3 text-end">
  <button onclick="window.print()" class="btn btn-primary btn-lg fw-bold">
    <i class="fa-solid fa-print"></i> พิมพ์ใบสั่งอาหาร (Print Diet Order)
  </button>
  <a href="<?= $baseUrl ?>/patient/<?= $dietOrder['hn'] ?>" class="btn btn-outline-secondary btn-lg ms-2">
    ย้อนกลับ (Back)
  </a>
</div>

<div class="container m-auto p-0" style="max-width: 210mm;">

  <!-- TITLE HEADER -->
  <div class="text-center border-bottom pb-2 mb-3">
    <h3 class="fw-bold m-0" style="font-size: 18pt; letter-spacing: 1px;">GUIDE TO MAKE DIET ORDER</h3>
    <small class="text-muted">โรงพยาบาลปลวกแดง Pluakdaeng Hospital Clinical Nutrition Unit</small>
  </div>

  <!-- PATIENT DEMOGRAPHICS HEADER ROW -->
  <div class="border p-2 mb-3 bg-light" style="font-size: 10pt;">
    <div class="row g-2">
      <div class="col-4"><strong>HN:</strong> <?= htmlspecialchars($dietOrder['hn']) ?></div>
      <div class="col-5"><strong>ชื่อ-นามสกุล:</strong> <?= htmlspecialchars($dietOrder['fullname']) ?></div>
      <div class="col-3"><strong>เพศ/อายุ:</strong> <?= htmlspecialchars($dietOrder['gender']) ?> / <?= $dietOrder['age'] ?> ปี</div>
    </div>
  </div>

  <!-- ANTHROPOMETRIC SUMMARY ROW -->
  <div class="row g-2 mb-3 text-center align-items-center bg-grey p-2 border mx-0" style="background-color: #f8f9fa;">
    <div class="col-4">
      <strong>Weight:</strong> <u class="fw-bold text-primary fs-6"><?= $dietOrder['weight_kg'] ?></u> kg
    </div>
    <div class="col-4">
      <strong>Height:</strong> <u class="fw-bold text-primary fs-6"><?= $dietOrder['height_cm'] ?></u> cm
    </div>
    <div class="col-4">
      <strong>IBW*:</strong> <u class="fw-bold text-success fs-6"><?= $dietOrder['ibw_kg'] ?></u> kg
    </div>
  </div>

  <!-- MAIN DIET ORDER TABLE -->
  <table class="diet-table">
    <tbody>

      <!-- Energy Requirement -->
      <tr>
        <td width="25%" class="grey-bg fw-bold">
          Energy<br>Requirement**
        </td>
        <td width="45%">
          <span class="check-box <?= ($dietOrder['energy_kcal_per_ibw'] == 25) ? 'checked' : '' ?>"></span> 25 kcal/IBW<br>
          <span class="check-box <?= ($dietOrder['energy_kcal_per_ibw'] == 30) ? 'checked' : '' ?>"></span> 30 kcal/IBW<br>
          <span class="check-box <?= ($dietOrder['energy_kcal_per_ibw'] == 35) ? 'checked' : '' ?>"></span> 35 kcal/IBW<br>
          <span class="check-box <?= (!in_array($dietOrder['energy_kcal_per_ibw'], [25,30,35])) ? 'checked' : '' ?>"></span> Other <?= (!in_array($dietOrder['energy_kcal_per_ibw'], [25,30,35])) ? htmlspecialchars($dietOrder['energy_kcal_per_ibw']) : '' ?>
        </td>
        <td width="30%" class="text-center grey-bg">
          <div class="bg-white border p-2 fw-bold fs-5 text-primary">
            <?= number_format($dietOrder['total_energy_kcal']) ?> <span class="fs-6 text-dark">kcal/day</span>
          </div>
        </td>
      </tr>

      <!-- Protein Requirement -->
      <tr>
        <td class="grey-bg fw-bold">
          Protein<br>Requirement
        </td>
        <td>
          <span class="check-box <?= ($dietOrder['protein_g_per_ibw'] == 1.0) ? 'checked' : '' ?>"></span> 1.0 g/IBW &nbsp;&nbsp;&nbsp;
          <span class="check-box <?= ($dietOrder['protein_g_per_ibw'] == 1.2) ? 'checked' : '' ?>"></span> 1.2 g/IBW &nbsp;&nbsp;&nbsp;
          <span class="check-box <?= ($dietOrder['protein_g_per_ibw'] == 1.3) ? 'checked' : '' ?>"></span> 1.3 g/IBW<br>
          <span class="check-box <?= ($dietOrder['protein_g_per_ibw'] == 1.5) ? 'checked' : '' ?>"></span> 1.5 g/IBW &nbsp;&nbsp;&nbsp;
          <span class="check-box <?= (!in_array($dietOrder['protein_g_per_ibw'], [1.0,1.2,1.3,1.5])) ? 'checked' : '' ?>"></span> Other
        </td>
        <td class="text-center grey-bg">
          <div class="bg-white border p-2 fw-bold fs-5 text-primary">
            <?= number_format($dietOrder['total_protein_g'], 1) ?> <span class="fs-6 text-dark">g/day</span>
          </div>
        </td>
      </tr>

      <!-- Diet Selection -->
      <tr>
        <td class="grey-bg fw-bold">Diet</td>
        <td>
          <?php $dtList = explode(',', $dietOrder['diet_types_json'] ?? ''); ?>
          <div class="row g-1">
            <div class="col-6">
              <span class="check-box <?= in_array('Regular diet', $dtList) ? 'checked' : '' ?>"></span> Regular diet<br>
              <span class="check-box <?= in_array('Soft diet', $dtList) ? 'checked' : '' ?>"></span> Soft diet<br>
              <span class="check-box <?= in_array('Full liquid diet', $dtList) ? 'checked' : '' ?>"></span> Full liquid diet<br>
              <span class="check-box <?= in_array('Clear liquid diet', $dtList) ? 'checked' : '' ?>"></span> Clear liquid diet
            </div>
            <div class="col-6">
              <span class="check-box <?= in_array('DM', $dtList) ? 'checked' : '' ?>"></span> DM<br>
              <span class="check-box <?= in_array('Low salt', $dtList) ? 'checked' : '' ?>"></span> Low salt<br>
              <span class="check-box <?= in_array('Low fat', $dtList) ? 'checked' : '' ?>"></span> Low fat<br>
              <span class="check-box <?= in_array('Other', $dtList) ? 'checked' : '' ?>"></span> Other
            </div>
          </div>
        </td>
        <td class="grey-bg p-2">
          <strong>Re quired diet =</strong>
          <div class="bg-white border p-2 mt-1" style="min-height: 55px; font-size: 9pt;">
            <?= nl2br(htmlspecialchars($dietOrder['required_diet_note'] ?: '-')) ?>
          </div>
        </td>
      </tr>

      <!-- Oral Supplement -->
      <tr>
        <td class="grey-bg fw-bold">Oral<br>Supplement</td>
        <td>
          <?php $oForm = $oralSupp['formula_type'] ?? ''; ?>
          <span class="check-box <?= (str_contains($oForm, 'Standard')) ? 'checked' : '' ?>"></span> Standard formula (CHO:PRO:FAT=55:15:30*, Complete & Balance)<br>
          <span class="check-box <?= (str_contains($oForm, 'DM')) ? 'checked' : '' ?>"></span> DM formula (Low glycemic index)<br>
          <span class="check-box <?= (str_contains($oForm, 'Renal')) ? 'checked' : '' ?>"></span> Renal dialysis formula (Energy & Proteindense, Low Na, P, K, fluid)<br>
          <span class="check-box <?= (str_contains($oForm, 'Cancer')) ? 'checked' : '' ?>"></span> Cancer formula (Energydense, High protein, High EPA)<br>
          <span class="check-box <?= (!empty($oForm) && !str_contains($oForm, 'Standard') && !str_contains($oForm, 'DM') && !str_contains($oForm, 'Renal') && !str_contains($oForm, 'Cancer')) ? 'checked' : '' ?>"></span> Other <?= htmlspecialchars($oForm) ?>
        </td>
        <td class="grey-bg align-middle text-center">
          <?php if ($oralSupp): ?>
            <div class="bg-white border p-2">
              <strong><?= $oralSupp['ml_per_meal'] ?></strong> ml/meal<br>
              <strong><?= $oralSupp['frequency_per_day'] ?></strong> meal<br>
              <small class="text-muted">Frequency/day: <?= $oralSupp['frequency_per_day'] ?></small>
            </div>
          <?php else: ?>
            <span class="text-muted fs-7">- ไม่ได้สั่ง -</span>
          <?php endif; ?>
        </td>
      </tr>

      <!-- Tube Feeding -->
      <tr>
        <td class="grey-bg fw-bold">Tube Feeding</td>
        <td>
          <?php $tForm = $tubeFeed['formula_type'] ?? ''; ?>
          <span class="check-box <?= (str_contains($tForm, 'Standard')) ? 'checked' : '' ?>"></span> Standard formula (CHO:PRO:FAT=55:15:30*, Complete & Balance)<br>
          <span class="check-box <?= (str_contains($tForm, 'DM')) ? 'checked' : '' ?>"></span> DM formula (Low glycemic index)<br>
          <span class="check-box <?= (str_contains($tForm, 'Renal')) ? 'checked' : '' ?>"></span> Renal dialysis formula (Energy & Proteindense, Low Na, P, K, fluid)<br>
          <span class="check-box <?= (str_contains($tForm, 'Cancer')) ? 'checked' : '' ?>"></span> Cancer formula (Energydense, High protein, High EPA)<br>
          <span class="check-box <?= (str_contains($tForm, 'Pulmonary')) ? 'checked' : '' ?>"></span> Pulmonary disease formula (CHO:PRO:FAT=40:15:45)<br>
          <span class="check-box <?= (str_contains($tForm, 'Hepatic')) ? 'checked' : '' ?>"></span> Hepatic encephalopathy formula (High branched chain amino acid)<br>
          <span class="check-box <?= ($tForm === 'BD') ? 'checked' : '' ?>"></span> BD &nbsp;&nbsp;&nbsp;
          <span class="check-box <?= (!empty($tForm) && !in_array($tForm, ['BD', 'Standard', 'DM', 'Renal', 'Cancer', 'Pulmonary', 'Hepatic'])) ? 'checked' : '' ?>"></span> Other <?= htmlspecialchars($tForm) ?>
        </td>
        <td class="grey-bg align-middle text-center">
          <?php if ($tubeFeed): ?>
            <div class="bg-white border p-2">
              <strong><?= $tubeFeed['ml_per_meal'] ?></strong> ml/meal<br>
              <strong><?= $tubeFeed['frequency_per_day'] ?></strong> meal<br>
              <small class="text-muted">Frequency/day: <?= $tubeFeed['frequency_per_day'] ?></small>
            </div>
          <?php else: ?>
            <span class="text-muted fs-7">- ไม่ได้สั่ง -</span>
          <?php endif; ?>
        </td>
      </tr>

    </tbody>
  </table>

  <!-- FOOTNOTE EXPLANATION -->
  <div class="text-muted mb-2" style="font-size: 7.5pt;">
    *IBW : Male = Height (cm.) -100, Female = Height (cm.) -105<br>
    **Energy : 25 kcal/IBW for sedentary lifestyle, 30 kcal/IBW for normal, 35 kcal/IBW for active lifestyle
  </div>

  <!-- REFERENCE TABLE 1: Kcal/IBW -->
  <table class="ref-table">
    <thead>
      <tr>
        <th>Kcal/IBW</th>
        <?php for ($w = 40; $w <= 90; $w += 2): ?>
          <th><?= $w ?></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="fw-bold">25</td>
        <?php for ($w = 40; $w <= 90; $w += 2): ?>
          <td><?= $w * 25 ?></td>
        <?php endfor; ?>
      </tr>
      <tr>
        <td class="fw-bold">30</td>
        <?php for ($w = 40; $w <= 90; $w += 2): ?>
          <td><?= $w * 30 ?></td>
        <?php endfor; ?>
      </tr>
      <tr>
        <td class="fw-bold">35</td>
        <?php for ($w = 40; $w <= 90; $w += 2): ?>
          <td><?= $w * 35 ?></td>
        <?php endfor; ?>
      </tr>
    </tbody>
  </table>

  <!-- REFERENCE TABLE 2: Protein -->
  <div class="mt-1 text-muted" style="font-size: 7.5pt;">
    Protein : 1.0 g/IBW for normal, 1.2 g/IBW for hemodialysis, 1.3 g/IBW for peritoneal dialysis, 1.5 g/IBW for moderate-severe catabolism
  </div>
  <table class="ref-table mb-3">
    <thead>
      <tr>
        <th>g/IBW</th>
        <?php for ($w = 40; $w <= 90; $w += 2): ?>
          <th><?= $w ?></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ([1.0, 1.2, 1.3, 1.5] as $p): ?>
        <tr>
          <td class="fw-bold"><?= number_format($p, 1) ?></td>
          <?php for ($w = 40; $w <= 90; $w += 2): ?>
            <td><?= round($w * $p, 1) ?></td>
          <?php endfor; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- SIGN-OFF FOOTER -->
  <div class="d-flex justify-content-between align-items-end border-top pt-2" style="font-size: 9pt;">
    <div style="font-size: 7.5pt; max-width: 60%;">
      o ตารางปริมาณสารอ้างอิงที่ควรได้รับประจำวันสำหรับคนไทย พ.ศ. 2546<br>
      $American Diabetes Association: Diabetes Care 2005;28(suppl 1):S4-S36, American Diabetes Association: Diabetes Care 2006;29(suppl 1):S4-S42<br>
      Guide to Make Diet Order : ตรวจสอบและดัดแปลงข้อมูลโดย ศ.นพ.สุรัตน์ โคมินทร์ หน่วยโภชนวิทยาและชีวเคมีทางการแพทย์ ภาควิชาอายุรศาสตร์ รพ.รามาธิบดี
    </div>
    <div class="text-end">
      <strong>Ordered by:</strong> ....................................................<br>
      ( <?= htmlspecialchars($dietOrder['ordered_by_name']) ?> )<br>
      <strong>D/M/Y:</strong> <?= date('d/m/Y') ?> &nbsp;&nbsp; <strong>Time:</strong> <?= date('H:i') ?> น.
    </div>
  </div>

</div>

</body>
</html>
