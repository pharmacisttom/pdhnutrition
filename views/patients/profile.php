<?php
$pageTitle = "ประวัติผู้ป่วย HN " . htmlspecialchars($patient['hn']) . " - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
use App\Helpers\DateHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Top Patient Banner -->
    <div class="patient-header-banner">
      <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
          <span class="badge bg-warning text-dark me-2">HN: <?= htmlspecialchars($patient['hn']) ?></span>
          <span class="badge bg-light text-dark me-2">CID: <?= SanitizerHelper::maskCid($patient['cid']) ?></span>
          <h2 class="d-inline fw-bold align-middle"><?= htmlspecialchars($patient['fullname']) ?></h2>
          <div class="mt-2 text-white-50">
            <span><i class="fa-solid fa-venus-mars me-1"></i> เพศ: <?= htmlspecialchars($patient['gender']) ?></span>
            <span class="ms-3"><i class="fa-solid fa-cake-candles me-1"></i> อายุ: <?= $patient['age'] ?> ปี</span>
            <span class="ms-3"><i class="fa-solid fa-phone me-1"></i> เบอร์โทร: <?= htmlspecialchars($patient['phone'] ?: '-') ?></span>
          </div>
        </div>

        <div class="mt-3 mt-md-0 d-flex gap-2">
          <?php if (!empty($allergies['has_allergy'])): ?>
            <div class="badge bg-danger fs-6 p-2 align-self-center me-2">
              <i class="fa-solid fa-triangle-exclamation me-1"></i> แพ้ยา: <?= htmlspecialchars($allergies['data'][0]['allergy_item'] ?? '') ?>
            </div>
          <?php endif; ?>

          <a href="<?= $baseUrl ?>/naf/create?hn=<?= $patient['hn'] ?>" class="btn btn-success fw-bold shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> ประเมิน NAF ใหม่
          </a>

          <a href="<?= $baseUrl ?>/diet/create?hn=<?= $patient['hn'] ?>" class="btn btn-primary fw-bold shadow-sm">
            <i class="fa-solid fa-utensils me-1"></i> สั่ง Diet Order
          </a>
        </div>
      </div>
    </div>

    <!-- Main Profile Tabs -->
    <ul class="nav nav-tabs fw-bold mb-4" id="patientTabs">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview"><i class="fa-solid fa-house-medical me-1"></i> ภาพรวม (Overview)</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nafHistory"><i class="fa-solid fa-timeline me-1"></i> ประวัติ NAF Assessment (<?= count($nafHistory) ?>)</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dietOrders"><i class="fa-solid fa-utensils me-1"></i> Diet Orders (<?= count($dietOrders) ?>)</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#clinicalNotes"><i class="fa-solid fa-notes-medical me-1"></i> Clinical Notes (SOAP) (<?= count($clinicalNotes) ?>)</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#diagnoses"><i class="fa-solid fa-stethoscope me-1"></i> การวินิจฉัย (HIS)</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#labs"><i class="fa-solid fa-flask me-1"></i> ผล Lab (HIS)</button>
      </li>
    </ul>

    <div class="tab-content" id="patientTabsContent">

      <!-- TAB 1: Overview -->
      <div class="tab-pane fade show active" id="overview">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="card p-3 h-100">
              <h5 class="fw-bold text-pdh-blue border-bottom pb-2"><i class="fa-solid fa-weight-scale me-2"></i> ข้อมูล Anthropometric</h5>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">น้ำหนักตัว (Weight):</span>
                <span class="fw-bold text-primary fs-5"><?= $patient['weight'] ?: '-' ?> kg</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">ส่วนสูง (Height):</span>
                <span class="fw-bold fs-5"><?= $patient['height'] ?: '-' ?> cm</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">ดัชนีมวลกาย (BMI):</span>
                <span class="badge bg-primary fs-6"><?= $patient['bmi'] ?: '-' ?> kg/m²</span>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card p-3 h-100">
              <h5 class="fw-bold text-pdh-blue border-bottom pb-2"><i class="fa-solid fa-vial me-2"></i> ผล Lab ล่าสุด</h5>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Albumin:</span>
                <span class="fw-bold"><?= $latestLabs['albumin'] ?? '-' ?> g/dL</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Total Lymphocyte Count (TLC):</span>
                <span class="fw-bold"><?= $latestLabs['tlc'] ?? '-' ?> cells/mm³</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">WBC:</span>
                <span class="fw-bold"><?= $latestLabs['wbc'] ?? '-' ?> cells/mm³</span>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card p-3 h-100">
              <h5 class="fw-bold text-pdh-blue border-bottom pb-2"><i class="fa-solid fa-shield-heart me-2"></i> สถานะ NAF & Registry</h5>
              <?php if (!empty($nafHistory)): ?>
                <?php $latest = $nafHistory[0]; ?>
                <div class="mb-2">
                  <span class="text-muted">NAF Grade ล่าสุด:</span>
                  <span class="badge badge-naf-<?= strtolower(substr($latest['naf_grade'], -1)) ?> fs-6 ms-2">
                    <?= htmlspecialchars($latest['naf_grade']) ?> (Score: <?= $latest['total_score'] ?>)
                  </span>
                </div>
                <div class="fs-7 text-muted">ประเมินเมื่อ: <?= DateHelper::formatThaiDate($latest['assessment_date']) ?> โดย <?= htmlspecialchars($latest['assessor_name']) ?></div>
              <?php else: ?>
                <div class="text-muted">ยังไม่มีประวัติการประเมิน NAF</div>
              <?php endif; ?>

              <?php if ($registryItem): ?>
                <div class="mt-3 p-2 bg-warning-subtle rounded border border-warning">
                  <i class="fa-solid fa-clipboard-check text-warning me-1"></i>
                  <strong>อยู่ในกลุ่มติดตามโภชนาการ (Registry)</strong>
                  <div class="fs-7 text-muted"><?= htmlspecialchars($registryItem['reason']) ?></div>
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
            <table class="table table-hover align-middle">
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
                      <a href="<?= $baseUrl ?>/naf/print/<?= $n['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i> พิมพ์</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: Diet Orders -->
      <div class="tab-pane fade" id="dietOrders">
        <div class="card p-3 border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>วันที่สั่ง</th>
                  <th>IBW (kg)</th>
                  <th>Energy Target</th>
                  <th>Protein Target</th>
                  <th>รายการ Diet</th>
                  <th>ผู้สั่งสั่งการ</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($dietOrders as $d): ?>
                  <tr>
                    <td><?= DateHelper::formatThaiDate(substr($d['created_at'], 0, 10)) ?></td>
                    <td class="fw-bold"><?= $d['ibw_kg'] ?> kg</td>
                    <td><span class="badge bg-primary"><?= number_format($d['total_energy_kcal']) ?> kcal/day</span></td>
                    <td><span class="badge bg-success"><?= $d['total_protein_g'] ?> g/day</span></td>
                    <td><?= htmlspecialchars($d['diet_types_json']) ?></td>
                    <td><?= htmlspecialchars($d['ordered_by_name']) ?></td>
                    <td class="text-center">
                      <a href="<?= $baseUrl ?>/diet/print/<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i> พิมพ์ Diet Order Form</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 4: Clinical Notes -->
      <div class="tab-pane fade" id="clinicalNotes">
        <div class="row g-3">
          <?php foreach ($clinicalNotes as $c): ?>
            <div class="col-md-6">
              <div class="card p-3 border-start border-info border-4">
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
        </div>
      </div>

      <!-- TAB 5: Diagnoses -->
      <div class="tab-pane fade" id="diagnoses">
        <div class="card p-3 border-0 shadow-sm">
          <ul class="list-group">
            <?php foreach ($diagnoses as $diag): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <span class="badge bg-primary me-2"><?= htmlspecialchars($diag['icd10']) ?></span>
                  <strong><?= htmlspecialchars($diag['diagnosis_name']) ?></strong>
                </div>
                <span class="badge bg-light text-dark border"><?= htmlspecialchars($diag['category']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!-- TAB 6: Labs -->
      <div class="tab-pane fade" id="labs">
        <div class="card p-3 border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>รายการตรวจ (Lab Test)</th>
                  <th>ค่าผลลัพธ์ (Result)</th>
                  <th>หน่วย (Unit)</th>
                  <th>วันที่ตรวจ</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($latestLabs['albumin'])): ?>
                  <tr>
                    <td>Albumin</td>
                    <td class="fw-bold"><?= $latestLabs['albumin'] ?></td>
                    <td>g/dL</td>
                    <td><?= $latestLabs['result_datetime'] ?? '-' ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($latestLabs['wbc'])): ?>
                  <tr>
                    <td>WBC</td>
                    <td class="fw-bold"><?= $latestLabs['wbc'] ?></td>
                    <td>cells/mm³</td>
                    <td><?= $latestLabs['result_datetime'] ?? '-' ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($latestLabs['lymphocyte'])): ?>
                  <tr>
                    <td>Lymphocyte (%)</td>
                    <td class="fw-bold"><?= $latestLabs['lymphocyte'] ?></td>
                    <td>%</td>
                    <td><?= $latestLabs['result_datetime'] ?? '-' ?></td>
                  </tr>
                <?php endif; ?>
                <?php if (!empty($latestLabs['tlc'])): ?>
                  <tr>
                    <td class="fw-bold text-primary">Total Lymphocyte Count (TLC)</td>
                    <td class="fw-bold text-primary"><?= $latestLabs['tlc'] ?></td>
                    <td>cells/mm³</td>
                    <td>คำนวณอัตโนมัติ (WBC x Lym / 100)</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
