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
    body { font-family: 'Sarabun', sans-serif; }
    .print-table th, .print-table td { border: 1px solid #000000; padding: 6px 8px; font-size: 11pt; }
  </style>
</head>
<body class="bg-white p-4">

<div class="no-print mb-3 text-end">
  <button onclick="window.print()" class="btn btn-primary btn-lg fw-bold"><i class="fa-solid fa-print"></i> พิมพ์คำสั่ง Diet Order (Print)</button>
</div>

<div class="print-page border p-4 m-auto">
  <div class="text-center border-bottom pb-2 mb-3">
    <h4 class="fw-bold mb-1">โรงพยาบาลปลวกแดง (Pluakdaeng Hospital)</h4>
    <h5 class="fw-bold mb-0">GUIDE TO MAKE DIET ORDER (แบบคำสั่งอาหารผู้ป่วย)</h5>
  </div>

  <table class="w-100 mb-3 fs-6">
    <tr>
      <td><strong>HN:</strong> <?= htmlspecialchars($dietOrder['hn']) ?></td>
      <td><strong>ชื่อ-นามสกุล:</strong> <?= htmlspecialchars($dietOrder['fullname']) ?></td>
      <td><strong>อายุ:</strong> <?= $dietOrder['age'] ?> ปี</td>
      <td><strong>เพศ:</strong> <?= htmlspecialchars($dietOrder['gender']) ?></td>
    </tr>
    <tr>
      <td><strong>VN:</strong> <?= htmlspecialchars($dietOrder['vn'] ?: '-') ?></td>
      <td><strong>CID:</strong> <?= SanitizerHelper::maskCid($dietOrder['cid']) ?></td>
      <td colspan="2"><strong>วันที่สั่ง:</strong> <?= DateHelper::formatThaiDate(substr($dietOrder['created_at'], 0, 10)) ?> เวลา <?= substr($dietOrder['created_at'], 11, 5) ?> น.</td>
    </tr>
  </table>

  <table class="table print-table mb-4">
    <thead>
      <tr class="table-secondary">
        <th colspan="2">1. ข้อมูลโภชนาการและการคำนวณเป้าหมาย (Calculated Targets)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td width="50%">น้ำหนักตัว (Weight) / ส่วนสูง (Height):</td>
        <td><strong><?= $dietOrder['weight_kg'] ?> kg / <?= $dietOrder['height_cm'] ?> cm</strong></td>
      </tr>
      <tr>
        <td>Ideal Body Weight (IBW):</td>
        <td><strong class="text-primary"><?= $dietOrder['ibw_kg'] ?> kg</strong></td>
      </tr>
      <tr>
        <td>Energy Requirement Target:</td>
        <td><strong><?= number_format($dietOrder['total_energy_kcal']) ?> kcal/day</strong> (<?= htmlspecialchars($dietOrder['energy_kcal_per_ibw']) ?> kcal/IBW)</td>
      </tr>
      <tr>
        <td>Protein Requirement Target:</td>
        <td><strong><?= $dietOrder['total_protein_g'] ?> g/day</strong> (<?= htmlspecialchars($dietOrder['protein_g_per_ibw']) ?> g/IBW)</td>
      </tr>
    </tbody>
  </table>

  <table class="table print-table mb-4">
    <thead>
      <tr class="table-secondary">
        <th colspan="2">2. รายการอาหารที่สั่งการ (Diet Orders)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td width="30%">ประเภทอาหาร (Diet Types):</td>
        <td><strong><?= htmlspecialchars($dietOrder['diet_types_json']) ?></strong></td>
      </tr>
      <?php if (!empty($dietOrder['required_diet_note'])): ?>
        <tr>
          <td>ข้อกำหนดเพิ่มเติม (Required Diet):</td>
          <td><?= nl2br(htmlspecialchars($dietOrder['required_diet_note'])) ?></td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($oralSupp): ?>
    <table class="table print-table mb-4">
      <thead>
        <tr class="table-secondary">
          <th colspan="2">3. คำสั่ง Oral Supplement</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td width="30%">สูตรอาหารเสริมทางปาก:</td>
          <td><strong><?= htmlspecialchars($oralSupp['formula_type']) ?></strong> (<?= $oralSupp['ml_per_meal'] ?> ml/มื้อ x <?= $oralSupp['frequency_per_day'] ?> ครั้ง/วัน = รวม <?= $oralSupp['total_daily_ml'] ?> ml/วัน)</td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if ($tubeFeed): ?>
    <table class="table print-table mb-4">
      <thead>
        <tr class="table-secondary">
          <th colspan="2">4. คำสั่ง Tube Feeding</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td width="30%">สูตรอาหารทางสายยาง:</td>
          <td><strong><?= htmlspecialchars($tubeFeed['formula_type']) ?></strong> (<?= $tubeFeed['ml_per_meal'] ?> ml/มื้อ x <?= $tubeFeed['frequency_per_day'] ?> ครั้ง/วัน = รวม <?= $tubeFeed['total_daily_ml'] ?> ml/วัน)</td>
        </tr>
      </tbody>
    </table>
  <?php endif; ?>

  <div class="row mt-5">
    <div class="col-6"></div>
    <div class="col-6 text-center">
      ลงชื่อ.........................................................................ผู้สั่งการ<br>
      ( <?= htmlspecialchars($dietOrder['ordered_by_name']) ?> )<br>
      นักโภชนาการ / แพทย์ผู้ตรวจรักษา โรงพยาบาลปลวกแดง
    </div>
  </div>
</div>

</body>
</html>
