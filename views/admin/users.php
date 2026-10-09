<?php
$pageTitle = "จัดการผู้ใช้งาน - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
$currentUserId = $_SESSION['user_id'] ?? 0;
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
                <th class="text-end pe-3">จัดการ (Actions)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= $u['id'] ?></td>
                  <td class="fw-bold text-pdh-blue">
                    <i class="fa-solid fa-user-circle me-1 text-secondary"></i>
                    <?= htmlspecialchars($u['username']) ?>
                  </td>
                  <td class="fw-semibold"><?= htmlspecialchars($u['fullname']) ?></td>
                  <td><?= htmlspecialchars($u['email'] ?: '-') ?></td>
                  <td>
                    <?php
                      $roleBadge = 'bg-primary';
                      if ($u['role'] === 'ADMIN' || $u['role'] === 'SUPER_ADMIN') $roleBadge = 'bg-danger';
                      else if ($u['role'] === 'DOCTOR') $roleBadge = 'bg-success';
                      else if ($u['role'] === 'NURSE') $roleBadge = 'bg-info text-dark';
                      else if ($u['role'] === 'PHARMACIST') $roleBadge = 'bg-warning text-dark';
                    ?>
                    <span class="badge <?= $roleBadge ?> px-2 py-1"><?= htmlspecialchars($u['role']) ?></span>
                  </td>
                  <td>
                    <?php if ($u['status'] === 'ACTIVE'): ?>
                      <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>ACTIVE</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><i class="fa-solid fa-circle-xmark me-1"></i><?= htmlspecialchars($u['status']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="fs-7 text-muted"><?= htmlspecialchars($u['created_at']) ?></td>
                  <td class="text-end pe-3">
                    <!-- Action Buttons -->
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-primary btn-edit-user" 
                              data-id="<?= $u['id'] ?>"
                              data-username="<?= htmlspecialchars($u['username']) ?>"
                              data-fullname="<?= htmlspecialchars($u['fullname']) ?>"
                              data-email="<?= htmlspecialchars($u['email']) ?>"
                              data-role="<?= htmlspecialchars($u['role']) ?>"
                              data-status="<?= htmlspecialchars($u['status']) ?>"
                              title="แก้ไขข้อมูล">
                        <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                      </button>
                      <button class="btn btn-outline-warning btn-reset-pass"
                              data-id="<?= $u['id'] ?>"
                              data-username="<?= htmlspecialchars($u['username']) ?>"
                              title="เปลี่ยนรหัสผ่าน">
                        <i class="fa-solid fa-key"></i> รหัสผ่าน
                      </button>
                      <button class="btn btn-outline-<?= $u['status'] === 'ACTIVE' ? 'secondary' : 'success' ?> btn-toggle-user"
                              data-id="<?= $u['id'] ?>"
                              data-username="<?= htmlspecialchars($u['username']) ?>"
                              title="สลับสถานะ">
                        <i class="fa-solid fa-power-off"></i> <?= $u['status'] === 'ACTIVE' ? 'ปิด' : 'เปิด' ?>
                      </button>
                      <?php if ((int)$u['id'] !== (int)$currentUserId): ?>
                        <button class="btn btn-outline-danger btn-delete-user"
                                data-id="<?= $u['id'] ?>"
                                data-username="<?= htmlspecialchars($u['username']) ?>"
                                title="ลบผู้ใช้งาน">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      <?php endif; ?>
                    </div>
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

<!-- 1. Create User Modal -->
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
            <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" required placeholder="ระบุ username">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
            <input type="text" name="fullname" class="form-control" required placeholder="ระบุชื่อ-นามสกุล">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">อีเมล</label>
            <input type="email" name="email" class="form-control" placeholder="example@pluakdaeng.go.th">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">รหัสผ่าน (Password) <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required placeholder="ระบุรหัสผ่าน">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">สิทธิ์การใช้งาน (Role)</label>
            <select name="role" class="form-select">
              <option value="DIETITIAN" selected>DIETITIAN (นักโภชนาการ)</option>
              <option value="DOCTOR">DOCTOR (แพทย์)</option>
              <option value="NURSE">NURSE (พยาบาล)</option>
              <option value="PHARMACIST">PHARMACIST (เภสัชกร)</option>
              <option value="ADMIN">ADMIN (ผู้ดูแลระบบ)</option>
              <option value="SUPER_ADMIN">SUPER_ADMIN (ผู้ดูแลระบบสูงสุด)</option>
              <option value="VIEWER">VIEWER (ผู้ดูข้อมูล)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึกผู้ใช้งาน</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editUserForm" method="POST" action="<?= $baseUrl ?>/admin/users/update">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="id" id="edit_user_id">
        
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i> แก้ไขข้อมูลผู้ใช้งาน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input type="text" id="edit_username" class="form-control bg-light" readonly disabled>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
            <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">อีเมล</label>
            <input type="email" name="email" id="edit_email" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">สิทธิ์การใช้งาน (Role)</label>
            <select name="role" id="edit_role" class="form-select">
              <option value="DIETITIAN">DIETITIAN (นักโภชนาการ)</option>
              <option value="DOCTOR">DOCTOR (แพทย์)</option>
              <option value="NURSE">NURSE (พยาบาล)</option>
              <option value="PHARMACIST">PHARMACIST (เภสัชกร)</option>
              <option value="ADMIN">ADMIN (ผู้ดูแลระบบ)</option>
              <option value="SUPER_ADMIN">SUPER_ADMIN (ผู้ดูแลระบบสูงสุด)</option>
              <option value="VIEWER">VIEWER (ผู้ดูข้อมูล)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">สถานะการใช้งาน</label>
            <select name="status" id="edit_status" class="form-select">
              <option value="ACTIVE">ACTIVE (เปิดใช้งาน)</option>
              <option value="INACTIVE">INACTIVE (ปิดใช้งาน)</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึกการแก้ไข</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 3. Reset Password Modal -->
<div class="modal fade" id="resetPassModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="resetPassForm" method="POST" action="<?= $baseUrl ?>/admin/users/reset-password">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="id" id="reset_user_id">
        
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-key me-2"></i> เปลี่ยนรหัสผ่านผู้ใช้งาน</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <p class="text-muted">กำลังเปลี่ยนรหัสผ่านสำหรับผู้ใช้: <strong id="reset_pass_username" class="text-pdh-blue"></strong></p>

          <div class="mb-3">
            <label class="form-label fw-bold">รหัสผ่านใหม่ (New Password) <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required placeholder="ระบุรหัสผ่านใหม่">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-warning fw-bold"><i class="fa-solid fa-check me-1"></i> ยืนยันเปลี่ยนรหัสผ่าน</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$(document).ready(function() {
  // Create User
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
      },
      error: function(xhr) {
        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถเพิ่มผู้ใช้ได้';
        Swal.fire('ข้อผิดพลาด', msg, 'error');
      }
    });
  });

  // Edit User Click
  $(document).on('click', '.btn-edit-user', function() {
    const id = $(this).data('id');
    const username = $(this).data('username');
    const fullname = $(this).data('fullname');
    const email = $(this).data('email');
    const role = $(this).data('role');
    const status = $(this).data('status');

    $('#edit_user_id').val(id);
    $('#edit_username').val(username);
    $('#edit_fullname').val(fullname);
    $('#edit_email').val(email);
    $('#edit_role').val(role);
    $('#edit_status').val(status);

    $('#editUserModal').modal('show');
  });

  // Edit User Form Submit
  $('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: '<?= $baseUrl ?>/admin/users/update',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          Swal.fire('สำเร็จ', res.message, 'success').then(() => location.reload());
        }
      },
      error: function(xhr) {
        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถแก้ไขข้อมูลได้';
        Swal.fire('ข้อผิดพลาด', msg, 'error');
      }
    });
  });

  // Reset Password Click
  $(document).on('click', '.btn-reset-pass', function() {
    const id = $(this).data('id');
    const username = $(this).data('username');

    $('#reset_user_id').val(id);
    $('#reset_pass_username').text(username);

    $('#resetPassModal').modal('show');
  });

  // Reset Password Form Submit
  $('#resetPassForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: '<?= $baseUrl ?>/admin/users/reset-password',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          Swal.fire('สำเร็จ', res.message, 'success').then(() => {
            $('#resetPassModal').modal('hide');
          });
        }
      },
      error: function(xhr) {
        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถเปลี่ยนรหัสผ่านได้';
        Swal.fire('ข้อผิดพลาด', msg, 'error');
      }
    });
  });

  // Toggle User Status
  $(document).on('click', '.btn-toggle-user', function() {
    const id = $(this).data('id');
    const username = $(this).data('username');

    Swal.fire({
      title: 'ยืนยันเปลี่ยนสถานะ?',
      text: 'ต้องการสลับสถานะเปิด/ปิดการใช้งานผู้ใช้: ' + username,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'ยืนยัน',
      cancelButtonText: 'ยกเลิก'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '<?= $baseUrl ?>/admin/users/toggle-status',
          type: 'POST',
          data: { id: id, csrf_token: '<?= htmlspecialchars($csrfToken) ?>' },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              Swal.fire('สำเร็จ', res.message, 'success').then(() => location.reload());
            }
          }
        });
      }
    });
  });

  // Delete User Click
  $(document).on('click', '.btn-delete-user', function() {
    const id = $(this).data('id');
    const username = $(this).data('username');

    Swal.fire({
      title: 'ยืนยันการลบผู้ใช้?',
      text: 'คุณต้องการลบบัญชีผู้ใช้ "' + username + '" ออกจากระบบหรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'ลบทันที',
      cancelButtonText: 'ยกเลิก'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '<?= $baseUrl ?>/admin/users/delete',
          type: 'POST',
          data: { id: id, csrf_token: '<?= htmlspecialchars($csrfToken) ?>' },
          dataType: 'json',
          success: function(res) {
            if (res.success) {
              Swal.fire('สำเร็จ', res.message, 'success').then(() => location.reload());
            }
          },
          error: function(xhr) {
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถลบผู้ใช้งานได้';
            Swal.fire('ข้อผิดพลาด', msg, 'error');
          }
        });
      }
    });
  });
});
</script>
