<?php
$pageTitle = "Clinical Risk Heatmap Alert Matrix - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
?>

<style>
  .heatmap-table { width: 100%; border-collapse: separate; border-spacing: 8px; }
  .heatmap-table th { text-align: center; font-weight: bold; font-size: 11pt; padding: 12px; }
  .heatmap-cell {
    height: 100px;
    border-radius: 8px;
    text-align: center;
    padding: 15px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .heatmap-cell:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  }
  .heat-critical { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }
  .heat-high { background: linear-gradient(135deg, #fd7e14 0%, #d9480f 100%); }
  .heat-moderate { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: #000 !important; }
  .heat-low { background: linear-gradient(135deg, #198754 0%, #146c43 100%); }
  .heat-zero { background: #f8f9fa; color: #adb5bd !important; border: 1px dashed #dee2e6; cursor: default; }
  .heat-zero:hover { transform: none; box-shadow: none; }
</style>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-fire-flame-curved text-warning me-2"></i> Clinical Risk Heatmap Alert Matrix
        </h3>
        <p class="text-muted mb-0">เมทริกซ์แผนภูมิความร้อนแสดงระดับความเสี่ยงโภชนาการแบบ 2 มิติ (ประเภทความเสี่ยง vs ระดับความรุนแรง)</p>
      </div>

      <div>
        <a href="<?= $baseUrl ?>/alerts" class="btn btn-outline-primary fw-bold">
          <i class="fa-solid fa-bell me-1"></i> ดูการแจ้งเตือนรายวัน (Daily Alerts Feed)
        </a>
      </div>
    </div>

    <!-- HEATMAP MATRIX GRID TABLE -->
    <div class="card p-4 shadow-sm border-0 mb-4 bg-white">
      <div class="table-responsive">
        <table class="heatmap-table">
          <thead>
            <tr>
              <th width="30%" class="text-start text-pdh-blue">หมวดหมู่ความเสี่ยงโภชนาการ (Risk Category)</th>
              <th width="17.5%" class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> CRITICAL (24h Urgent)</th>
              <th width="17.5%" class="text-warning-emphasis"><i class="fa-solid fa-triangle-exclamation me-1"></i> HIGH (3-Day Action)</th>
              <th width="17.5%" class="text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> MODERATE</th>
              <th width="17.5%" class="text-success"><i class="fa-solid fa-shield-heart me-1"></i> LOW / ROUTINE</th>
            </tr>
          </thead>
          <tbody>

            <!-- Row 1: UNDERNUTRITION -->
            <tr>
              <td class="fw-bold bg-light p-3 rounded">
                <i class="fa-solid fa-weight-scale text-danger me-2"></i>
                1. ภาวะพร่องสารอาหาร / ผอมรุนแรง<br>
                <small class="text-muted fw-normal">Marasmus, NAF C, BMI &lt; 16.0, TLC &lt; 1,000</small>
              </td>
              <td><?php renderHeatCell('UNDERNUTRITION', 'CRITICAL', $matrix['UNDERNUTRITION']['CRITICAL'] ?? 0, $matrix['UNDERNUTRITION']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('UNDERNUTRITION', 'HIGH', $matrix['UNDERNUTRITION']['HIGH'] ?? 0, $matrix['UNDERNUTRITION']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('UNDERNUTRITION', 'MODERATE', $matrix['UNDERNUTRITION']['MODERATE'] ?? 0, $matrix['UNDERNUTRITION']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('UNDERNUTRITION', 'LOW', $matrix['UNDERNUTRITION']['LOW'] ?? 0, $matrix['UNDERNUTRITION']['patients'] ?? []); ?></td>
            </tr>

            <!-- Row 2: OVERNUTRITION -->
            <tr>
              <td class="fw-bold bg-light p-3 rounded">
                <i class="fa-solid fa-person-fat text-warning me-2"></i>
                2. ภาวะโภชนาการเกิน / อ้วนรุนแรง<br>
                <small class="text-muted fw-normal">Obesity Class II/III, BMI &ge; 30.0, Metabolic Syndrome</small>
              </td>
              <td><?php renderHeatCell('OVERNUTRITION', 'CRITICAL', $matrix['OVERNUTRITION']['CRITICAL'] ?? 0, $matrix['OVERNUTRITION']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('OVERNUTRITION', 'HIGH', $matrix['OVERNUTRITION']['HIGH'] ?? 0, $matrix['OVERNUTRITION']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('OVERNUTRITION', 'MODERATE', $matrix['OVERNUTRITION']['MODERATE'] ?? 0, $matrix['OVERNUTRITION']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('OVERNUTRITION', 'LOW', $matrix['OVERNUTRITION']['LOW'] ?? 0, $matrix['OVERNUTRITION']['patients'] ?? []); ?></td>
            </tr>

            <!-- Row 3: DEFICIENCY -->
            <tr>
              <td class="fw-bold bg-light p-3 rounded">
                <i class="fa-solid fa-vial text-info me-2"></i>
                3. ภาวะขาดโปรตีน / สารอาหารบกพร่อง<br>
                <small class="text-muted fw-normal">Hypoalbuminemia (&lt; 2.5 g/dL), Protein Malnutrition</small>
              </td>
              <td><?php renderHeatCell('DEFICIENCY', 'CRITICAL', $matrix['DEFICIENCY']['CRITICAL'] ?? 0, $matrix['DEFICIENCY']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('DEFICIENCY', 'HIGH', $matrix['DEFICIENCY']['HIGH'] ?? 0, $matrix['DEFICIENCY']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('DEFICIENCY', 'MODERATE', $matrix['DEFICIENCY']['MODERATE'] ?? 0, $matrix['DEFICIENCY']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('DEFICIENCY', 'LOW', $matrix['DEFICIENCY']['LOW'] ?? 0, $matrix['DEFICIENCY']['patients'] ?? []); ?></td>
            </tr>

            <!-- Row 4: ELECTROLYTE -->
            <tr>
              <td class="fw-bold bg-light p-3 rounded">
                <i class="fa-solid fa-kidneys text-primary me-2"></i>
                4. ภาวะเกลือแร่เกิน / โรคไตล้มเหลว<br>
                <small class="text-muted fw-normal">Hyperkalemia (K+ &gt; 5.5), ESRD / CKD Stage 5 Fluid Overload</small>
              </td>
              <td><?php renderHeatCell('ELECTROLYTE', 'CRITICAL', $matrix['ELECTROLYTE']['CRITICAL'] ?? 0, $matrix['ELECTROLYTE']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('ELECTROLYTE', 'HIGH', $matrix['ELECTROLYTE']['HIGH'] ?? 0, $matrix['ELECTROLYTE']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('ELECTROLYTE', 'MODERATE', $matrix['ELECTROLYTE']['MODERATE'] ?? 0, $matrix['ELECTROLYTE']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('ELECTROLYTE', 'LOW', $matrix['ELECTROLYTE']['LOW'] ?? 0, $matrix['ELECTROLYTE']['patients'] ?? []); ?></td>
            </tr>

            <!-- Row 5: HYPERGLYCEMIA & DYSLIPIDEMIA -->
            <tr>
              <td class="fw-bold bg-light p-3 rounded">
                <i class="fa-solid fa-droplet text-danger me-2"></i>
                5. ภาวะน้ำตาลในเลือดสูง / ไขมันสูง<br>
                <small class="text-muted fw-normal">FBS &ge; 126 mg/dL, HbA1c &ge; 6.5%, Cholesterol &ge; 200 mg/dL</small>
              </td>
              <td><?php renderHeatCell('HYPERGLYCEMIA', 'CRITICAL', $matrix['HYPERGLYCEMIA']['CRITICAL'] ?? 0, $matrix['HYPERGLYCEMIA']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('HYPERGLYCEMIA', 'HIGH', $matrix['HYPERGLYCEMIA']['HIGH'] ?? 0, $matrix['HYPERGLYCEMIA']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('HYPERGLYCEMIA', 'MODERATE', $matrix['HYPERGLYCEMIA']['MODERATE'] ?? 0, $matrix['HYPERGLYCEMIA']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('HYPERGLYCEMIA', 'LOW', $matrix['HYPERGLYCEMIA']['LOW'] ?? 0, $matrix['HYPERGLYCEMIA']['patients'] ?? []); ?></td>
            </tr>

            <!-- Row 6: RENAL_RISK -->
            <tr>
              <td class="fw-bold bg-light p-3 rounded">
                <i class="fa-solid fa-flask-vial text-purple me-2"></i>
                6. ภาวะการทำงานของไตเสื่อมลง (CKD Risk)<br>
                <small class="text-muted fw-normal">eGFR &lt; 60 mL/min/1.73m², BUN &gt; 20.0 mg/dL, Creatinine &gt; 1.2</small>
              </td>
              <td><?php renderHeatCell('RENAL_RISK', 'CRITICAL', $matrix['RENAL_RISK']['CRITICAL'] ?? 0, $matrix['RENAL_RISK']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('RENAL_RISK', 'HIGH', $matrix['RENAL_RISK']['HIGH'] ?? 0, $matrix['RENAL_RISK']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('RENAL_RISK', 'MODERATE', $matrix['RENAL_RISK']['MODERATE'] ?? 0, $matrix['RENAL_RISK']['patients'] ?? []); ?></td>
              <td><?php renderHeatCell('RENAL_RISK', 'LOW', $matrix['RENAL_RISK']['LOW'] ?? 0, $matrix['RENAL_RISK']['patients'] ?? []); ?></td>
            </tr>

          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- PATIENT ROSTER MODAL -->
<div class="modal fade" id="heatmapModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-pdh-blue text-white">
        <h5 class="modal-title fw-bold" id="modalTitle"><i class="fa-solid fa-users me-2"></i> รายชื่อผู้ป่วยในกลุ่มความเสี่ยง</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="modalBody">
        <!-- Patient list populated via JS -->
      </div>
    </div>
  </div>
</div>

<?php
function renderHeatCell($cat, $level, $count = 0, $patients = []) {
    $count = (int)($count ?? 0);
    $patients = is_array($patients) ? $patients : [];

    if ($count === 0) {
        echo '<div class="heatmap-cell heat-zero">0 ราย</div>';
        return;
    }

    $cls = ($level === 'CRITICAL') ? 'heat-critical' : (($level === 'HIGH') ? 'heat-high' : (($level === 'MODERATE') ? 'heat-moderate' : 'heat-low'));
    $filteredPatients = array_filter($patients, fn($p) => is_array($p) && ($p['level'] ?? '') === $level);
    $json = htmlspecialchars(json_encode(array_values($filteredPatients)), ENT_QUOTES, 'UTF-8');

    echo "<div class=\"heatmap-cell {$cls}\" onclick=\"showPatientModal('{$cat}', '{$level}', {$json})\">";
    echo "<div class=\"fs-3 fw-bold\">{$count}</div>";
    echo "<div class=\"fs-7\">ราย (คลิกดูชื่อ)</div>";
    echo "</div>";
}
?>

<script>
function showPatientModal(cat, level, patients) {
  $('#modalTitle').html('<i class="fa-solid fa-users me-2"></i> ผู้ป่วยกลุ่มความเสี่ยง ' + cat + ' (' + level + ')');
  
  var html = '<div class="list-group">';
  if (patients.length === 0) {
    html += '<div class="p-3 text-muted text-center">ไม่พบผู้ป่วยในกลุ่มนี้</div>';
  } else {
    patients.forEach(function(p) {
      html += '<a href="<?= $baseUrl ?>/patient/' + p.hn + '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3">';
      html += '<div>';
      html += '<strong class="text-pdh-blue fs-6">HN: ' + p.hn + ' - ' + p.fullname + '</strong>';
      html += '<div class="fs-7 text-muted mt-1"><i class="fa-solid fa-hospital me-1"></i> ' + p.clinic + '</div>';
      html += '<div class="badge bg-danger mt-1">' + p.msg + '</div>';
      html += '</div>';
      html += '<span class="btn btn-sm btn-outline-primary fw-bold">ดูประวัติ <i class="fa-solid fa-chevron-right ms-1"></i></span>';
      html += '</a>';
    });
  }
  html += '</div>';
  
  $('#modalBody').html(html);
  var myModal = new bootstrap.Modal(document.getElementById('heatmapModal'));
  myModal.show();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
