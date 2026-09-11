<?php
$pageTitle = "จัดการผู้ใช้งาน - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-users text-primary me-2"></i> จัดการผู้ใช้งานและสิทธิ์ (User & RBAC Management)</h3>
        <p class="text-muted mb-0">ระบบกำหนดสิทธิ์ 7 ระดับ: SUPER_ADMIN, ADMIN, DIETITIAN, DOCTOR, NURSE, PHARMACIST, VIEWER</p>
      </div>

      <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal">
        <i class="fa-solid fa-user-plus me-1"></i> เพิ่มผู้ใช้งานใหม่
      </button>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>ชื่อ - นามสกุล</th>
                <th>อีเมล</th>
                <th>สิทธิ์การใช้งาน (Role)</th>
                <th>สถานะ</th>
                <th>วันที่สร้าง</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= $u['id'] ?></td>
                  <td class="fw-bold text-pdh-blue"><?= htmlspecialchars($u['username']) ?></td>
                  <td><?= htmlspecialchars($u['fullname']) ?></td>
                  <td><?= htmlspecialchars($u['email'] ?: '-') ?></td>
                  <td><span class="badge bg-primary px-2 py-1"><?= htmlspecialchars($u['role']) ?></span></td>
                  <td>
                    <?php if ($u['status'] === 'ACTIVE'): ?>
                      <span class="badge bg-success">ACTIVE</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?= htmlspecialchars($u['status']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($u['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="userForm" method="POST" action="<?= $baseUrl ?>/admin/users/create">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="modal-header bg-pdh-blue text-white">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i> เพิ่มผู้ใช้งานใหม่</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">ชื่อ - นามสกุล</label>
            <input type="text" name="fullname" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">รหัสผ่าน (Password)</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">สิทธิ์การใช้งาน (Role)</label>
            <select name="role" class="form-select">
              <option value="DIETITIAN" selected>DIETITIAN (นักโภชนาการ)</option>
              <option value="DOCTOR">DOCTOR (แพทย์)</option>
              <option value="NURSE">NURSE (พยาบาล)</option>
              <option value="PHARMACIST">PHARMACIST (เภสัชกร)</option>
              <option value="ADMIN">ADMIN (ผู้ดูแลระบบ)</option>
              <option value="VIEWER">VIEWER (ผู้ดูข้อมูล)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary fw-bold">บันทึกผู้ใช้งาน</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$('#userForm').on('submit', function(e) {
  e.preventDefault();
  $.ajax({
    url: '<?= $baseUrl ?>/admin/users/create',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire('สำเร็จ', res.message, 'success').then(() => location.reload());
      }
    }
  });
});
</script>
