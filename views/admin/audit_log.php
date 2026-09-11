<?php
$pageTitle = "Audit Log System - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="mb-4">
      <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-shield-halved me-2"></i> Audit Log System (บันทึกประวัติการใช้งาน)</h3>
      <p class="text-muted mb-0">บันทึกทุกการเข้าใช้งาน LOGIN, VIEW_PATIENT, CREATE_NAF, CREATE_DIET, EXPORT_REPORT พร้อม IP Address และ User-Agent</p>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th>วัน-เวลา</th>
                <th>ผู้ใช้งาน</th>
                <th>Role</th>
                <th>Action</th>
                <th>Module</th>
                <th>HN / VN</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td><small class="text-muted"><?= htmlspecialchars($l['created_at']) ?></small></td>
                  <td class="fw-bold"><?= htmlspecialchars($l['username'] ?: 'GUEST') ?></td>
                  <td><span class="badge bg-secondary"><?= htmlspecialchars($l['role'] ?: 'SYSTEM') ?></span></td>
                  <td><span class="badge bg-primary"><?= htmlspecialchars($l['action']) ?></span></td>
                  <td><?= htmlspecialchars($l['module']) ?></td>
                  <td><?= htmlspecialchars($l['hn'] ?: $l['vn'] ?: '-') ?></td>
                  <td><code><?= htmlspecialchars($l['ip_address']) ?></code></td>
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
