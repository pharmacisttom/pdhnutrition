<?php
use App\Helpers\DateHelper;
use App\Helpers\SanitizerHelper;
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
    body { font-family: 'Sarabun', sans-serif; }
    .print-table th, .print-table td { border: 1px solid #000000; padding: 4px 6px; font-size: 11pt; }
  </style>
</head>
<body class="bg-white p-4">

<div class="no-print mb-3 text-end">
  <button onclick="window.print()" class="btn btn-primary btn-lg fw-bold"><i class="fa-solid fa-print"></i> พิมพ์เอกสาร (Print)</button>
</div>

<div class="print-page border p-4 m-auto">
  <div class="text-center border-bottom pb-2 mb-3">
    <h4 class="fw-bold mb-1">โรงพยาบาลปลวกแดง (Pluakdaeng Hospital)</h4>
    <h5 class="fw-bold mb-0">NUTRITION ALERT FORM (แบบประเมินภาวะโภชนาการ)</h5>
  </div>

  <table class="w-100 mb-3 fs-6">
    <tr>
      <td><strong>HN:</strong> <?= htmlspecialchars($assessment['hn']) ?></td>
      <td><strong>ชื่อ-นามสกุล:</strong> <?= htmlspecialchars($assessment['fullname']) ?></td>
      <td><strong>อายุ:</strong> <?= $assessment['age'] ?> ปี</td>
      <td><strong>เพศ:</strong> <?= htmlspecialchars($assessment['gender']) ?></td>
    </tr>
    <tr>
      <td><strong>VN:</strong> <?= htmlspecialchars($assessment['vn'] ?: '-') ?></td>
      <td><strong>CID:</strong> <?= SanitizerHelper::maskCid($assessment['cid']) ?></td>
      <td colspan="2"><strong>วันที่ประเมิน:</strong> <?= DateHelper::formatThaiDate($assessment['assessment_date']) ?></td>
    </tr>
  </table>

  <!-- Longitudinal Assessment History Table (Up to 3 sessions) -->
  <table class="table print-table mb-4">
    <thead>
      <tr class="table-secondary text-center">
        <th width="40%">หัวข้อการประเมิน (Assessment Item)</th>
        <?php foreach ($history as $idx => $h): ?>
          <th width="20%">ครั้งที่ <?= $idx + 1 ?><br><small><?= DateHelper::formatThaiDate($h['assessment_date'], true) ?></small></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>น้ำหนักตัว (Weight kg)</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center"><?= $h['weight_kg'] ?> kg</td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td>ส่วนสูง (Height cm) / Arm span</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center"><?= $h['height_cm'] ?> cm</td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td>BMI (kg/m²)</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center"><?= $h['bmi'] ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td>Albumin (g/dL)</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center"><?= $h['albumin'] ?: '-' ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td>TLC (cells/mm³)</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center"><?= $h['tlc'] ?: '-' ?></td>
        <?php endforeach; ?>
      </tr>
      <tr class="fw-bold table-light">
        <td>คะแนนรวม (NAF Total Score)</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center fs-5"><?= $h['total_score'] ?></td>
        <?php endforeach; ?>
      </tr>
      <tr class="fw-bold">
        <td>ผลการแปลผล (NAF Grade)</td>
        <?php foreach ($history as $h): ?>
          <td class="text-center">
            <span class="badge bg-<?= ($h['naf_grade'] === 'NAF C') ? 'danger' : (($h['naf_grade'] === 'NAF B') ? 'warning' : 'success') ?>">
              <?= htmlspecialchars($h['naf_grade']) ?>
            </span>
          </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>

  <div class="row mt-4">
    <div class="col-6">
      <div class="border p-2 fs-7">
        <strong>เกณฑ์การแปลผล NAF Score:</strong><br>
        0 - 5 คะแนน = <strong>NAF A</strong> (Normal - Mild Malnutrition)<br>
        6 - 10 คะแนน = <strong>NAF B</strong> (Moderate Malnutrition)<br>
        >= 11 คะแนน = <strong>NAF C</strong> (Severe Malnutrition)
      </div>
    </div>
    <div class="col-6 text-center">
      <div style="margin-top: 40px;">
        ลงชื่อ.........................................................................ผู้ประเมิน<br>
        ( <?= htmlspecialchars($assessment['assessor_name']) ?> )<br>
        นักโภชนาการ / บุคลากรทางการแพทย์ โรงพยาบาลปลวกแดง
      </div>
    </div>
  </div>
</div>

</body>
</html>
