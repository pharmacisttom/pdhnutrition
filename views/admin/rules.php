<?php
$pageTitle = "NAF Rules Engine - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="mb-4">
      <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-sliders me-2"></i> NAF Rules Engine Configurator</h3>
      <p class="text-muted mb-0">เกณฑ์การคำนวณคะแนน NAF Alert Form (เวอร์ชันปัจจุบัน: Version 1) พร้อมระบบ Versioning</p>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th>Section</th>
                <th>Item Code</th>
                <th>คำอธิบาย (ภาษาไทย)</th>
                <th>Label EN</th>
                <th>คะแนน (Score)</th>
                <th>เงื่อนไข / Operator</th>
                <th>Version</th>
                <th>Active</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rules as $r): ?>
                <tr>
                  <td><span class="badge bg-secondary">Sec <?= $r['section'] ?></span></td>
                  <td class="fw-bold text-pdh-blue"><code><?= htmlspecialchars($r['item_code']) ?></code></td>
                  <td class="fw-bold"><?= htmlspecialchars($r['label_th']) ?></td>
                  <td><?= htmlspecialchars($r['label_en'] ?: '-') ?></td>
                  <td><span class="badge bg-danger fs-6">+<?= $r['score'] ?></span></td>
                  <td><?= htmlspecialchars($r['condition_type']) ?> (<?= htmlspecialchars($r['operator'] ?: '=') ?>)</td>
                  <td>v<?= $r['version'] ?></td>
                  <td>
                    <?php if ($r['active']): ?>
                      <span class="badge bg-success">ACTIVE</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">INACTIVE</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
