<?php
$pageTitle = "NAF Rules Engine - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-sliders me-2"></i> NAF Rules Engine Configurator</h3>
        <p class="text-muted mb-0">เกณฑ์การคำนวณคะแนน NAF Alert Form (เวอร์ชันปัจจุบัน: Version 1) พร้อมระบบ Versioning</p>
      </div>

      <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#ruleModal">
        <i class="fa-solid fa-plus me-1"></i> เพิ่มกฎ NAF ใหม่
      </button>
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
                <th class="text-end pe-3">จัดการ (Actions)</th>
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
                      <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>ACTIVE</span>
                    <?php else: ?>
                      <span class="badge bg-secondary"><i class="fa-solid fa-circle-xmark me-1"></i>INACTIVE</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end pe-3">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-primary btn-edit-rule"
                              data-id="<?= $r['id'] ?>"
                              data-code="<?= htmlspecialchars($r['item_code']) ?>"
                              data-th="<?= htmlspecialchars($r['label_th']) ?>"
                              data-en="<?= htmlspecialchars($r['label_en']) ?>"
                              data-score="<?= $r['score'] ?>"
                              data-ctype="<?= htmlspecialchars($r['condition_type']) ?>"
                              data-op="<?= htmlspecialchars($r['operator']) ?>"
                              title="แก้ไขกฎ">
                        <i class="fa-solid fa-pen-to-square"></i> แก้ไข
                      </button>
                      <button class="btn btn-outline-<?= $r['active'] ? 'secondary' : 'success' ?> btn-toggle-rule"
                              data-id="<?= $r['id'] ?>"
                              data-code="<?= htmlspecialchars($r['item_code']) ?>"
                              title="สลับสถานะ">
                        <i class="fa-solid fa-power-off"></i> <?= $r['active'] ? 'ปิด' : 'เปิด' ?>
                      </button>
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

<!-- 1. Add Rule Modal -->
<div class="modal fade" id="ruleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="ruleForm" method="POST" action="<?= $baseUrl ?>/admin/rules/create">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="modal-header bg-pdh-blue text-white">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2"></i> เพิ่มกฎ NAF Rule ใหม่</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Section NAF <span class="text-danger">*</span></label>
              <select name="section" class="form-select" required>
                <option value="1">Section 1: Weight Loss</option>
                <option value="2">Section 2: BMI Range</option>
                <option value="3">Section 3: Food Intake</option>
                <option value="4">Section 4: Symptoms</option>
                <option value="5">Section 5: Clinical Diagnosis</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Item Code <span class="text-danger">*</span></label>
              <input type="text" name="item_code" class="form-control" required placeholder="เช่น WEIGHT_LOSS_GT_10">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">คำอธิบาย (ภาษาไทย) <span class="text-danger">*</span></label>
            <input type="text" name="label_th" class="form-control" required placeholder="เช่น น้ำหนักลดมากกว่า 10% ใน 6 เดือน">
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Label EN</label>
            <input type="text" name="label_en" class="form-control" placeholder="เช่น Weight loss > 10%">
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">คะแนน (Score)</label>
              <input type="number" name="score" class="form-control" value="1" min="0" max="10" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Condition Type</label>
              <select name="condition_type" class="form-select">
                <option value="FLAG">FLAG</option>
                <option value="NUMERIC_RANGE">NUMERIC_RANGE</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Operator</label>
              <select name="operator" class="form-select">
                <option value="=">=</option>
                <option value="<">&lt;</option>
                <option value="<=">&lt;=</option>
                <option value=">">&gt;</option>
                <option value=">=">&gt;=</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i> บันทึกกฎ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editRuleForm" method="POST" action="<?= $baseUrl ?>/admin/rules/update">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="id" id="edit_rule_id">
        
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> แก้ไขกฎ NAF Rule</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Item Code</label>
            <input type="text" id="edit_rule_code" class="form-control bg-light" readonly disabled>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">คำอธิบาย (ภาษาไทย) <span class="text-danger">*</span></label>
            <input type="text" name="label_th" id="edit_rule_th" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Label EN</label>
            <input type="text" name="label_en" id="edit_rule_en" class="form-control">
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">คะแนน (Score)</label>
              <input type="number" name="score" id="edit_rule_score" class="form-control" min="0" max="10" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Condition Type</label>
              <select name="condition_type" id="edit_rule_ctype" class="form-select">
                <option value="FLAG">FLAG</option>
                <option value="NUMERIC_RANGE">NUMERIC_RANGE</option>
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label fw-bold">Operator</label>
              <select name="operator" id="edit_rule_op" class="form-select">
                <option value="=">=</option>
                <option value="<">&lt;</option>
                <option value="<=">&lt;=</option>
                <option value=">">&gt;</option>
                <option value=">=">&gt;=</option>
              </select>
            </div>
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

<?php require __DIR__ . '/../layouts/footer.php'; ?>
<script>
$(document).ready(function() {
  // Add Rule
  $('#ruleForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: '<?= $baseUrl ?>/admin/rules/create',
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

  // Edit Rule Click
  $(document).on('click', '.btn-edit-rule', function() {
    $('#edit_rule_id').val($(this).data('id'));
    $('#edit_rule_code').val($(this).data('code'));
    $('#edit_rule_th').val($(this).data('th'));
    $('#edit_rule_en').val($(this).data('en'));
    $('#edit_rule_score').val($(this).data('score'));
    $('#edit_rule_ctype').val($(this).data('ctype'));
    $('#edit_rule_op').val($(this).data('op'));

    $('#editRuleModal').modal('show');
  });

  // Edit Rule Submit
  $('#editRuleForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: '<?= $baseUrl ?>/admin/rules/update',
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

  // Toggle Rule Active
  $(document).on('click', '.btn-toggle-rule', function() {
    const id = $(this).data('id');
    const code = $(this).data('code');

    Swal.fire({
      title: 'ยืนยันเปลี่ยนสถานะกฎ?',
      text: 'สลับสถานะเปิด/ปิดใช้งานกฎ: ' + code,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'ยืนยัน'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '<?= $baseUrl ?>/admin/rules/toggle-status',
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
});
</script>
