<?php
$pageTitle = "ตั้งค่าระบบและคลินิก - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-sliders text-primary me-2"></i> ตั้งค่าระบบพื้นฐาน & กำหนดคลินิกผู้ป่วย
        </h3>
        <p class="text-muted mb-0">กำหนดคลินิกรับบริการ ดึงข้อมูล HIS ค่าตั้งต้นระบบโภชนบำบัด และข้อมูลองค์กร</p>
      </div>
      <div>
        <button type="button" onclick="saveAllSettings()" class="btn btn-primary btn-lg fw-bold shadow-sm">
          <i class="fa-solid fa-floppy-disk me-2"></i> บันทึกการตั้งค่าทั้งหมด
        </button>
      </div>
    </div>

    <!-- Alert Banner -->
    <div id="alertNotice" class="alert alert-success d-none shadow-sm mb-4" role="alert">
      <i class="fa-solid fa-circle-check me-2 fs-5"></i> <span id="alertMsg"></span>
    </div>

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-tabs nav-fill fw-bold mb-4 bg-white p-2 rounded shadow-sm border" id="settingsTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active py-3" id="tab-clinics" data-bs-toggle="tab" data-bs-target="#content-clinics" type="button">
          <i class="fa-solid fa-hospital-user text-success me-2 fs-5"></i>
          <div>กำหนดคลินิก & แผนกผู้ป่วย</div>
          <small class="text-muted fw-normal">เปิด/ปิด และเพิ่มคลินิกที่จะใช้ประเมิน</small>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link py-3" id="tab-hospital" data-bs-toggle="tab" data-bs-target="#content-hospital" type="button">
          <i class="fa-solid fa-building-hospital text-info me-2 fs-5"></i>
          <div>ข้อมูลองค์กร & โรงพยาบาล</div>
          <small class="text-muted fw-normal">ชื่อ รพ., HCODE, กลุ่มงานโภชนวิทยา</small>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link py-3" id="tab-his" data-bs-toggle="tab" data-bs-target="#content-his" type="button">
          <i class="fa-solid fa-network-wired text-warning me-2 fs-5"></i>
          <div>เชื่อมต่อระบบ HIS & Gateway</div>
          <small class="text-muted fw-normal">API URL, API Key, Driver Mode</small>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link py-3" id="tab-clinical" data-bs-toggle="tab" data-bs-target="#content-clinical" type="button">
          <i class="fa-solid fa-clipboard-check text-danger me-2 fs-5"></i>
          <div>เกณฑ์โภชนาการ & ระบบคิว</div>
          <small class="text-muted fw-normal">NAF Threshold, Interval, Auto Queue</small>
        </button>
      </li>
    </ul>

    <form id="settingsForm" onsubmit="event.preventDefault(); saveAllSettings();">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="clinics_json" id="clinicsJsonInput">

      <div class="tab-content" id="settingsTabContent">

        <!-- TAB 1: CLINIC MANAGEMENT -->
        <div class="tab-pane fade show active" id="content-clinics">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
              <div>
                <h5 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-hospital-user me-2 text-success"></i> รายการคลินิกและแผนกผู้ป่วยสำหรับโภชนาการ</h5>
                <small class="text-muted">คลินิกที่เปิดใช้งานจะถูกนำมาสร้างคิวประเมิน NAF และแยก Tab ข้อมูลผู้ป่วย Visit ประจำวัน</small>
              </div>
              <button type="button" class="btn btn-success fw-bold" onclick="openAddClinicModal()">
                <i class="fa-solid fa-plus-circle me-1"></i> เพิ่มคลินิกใหม่
              </button>
            </div>
            <div class="card-body p-3">

              <div class="row mb-3">
                <div class="col-md-12">
                  <div class="form-check form-switch card bg-light border p-3">
                    <input class="form-check-input ms-0 me-3 fs-5" type="checkbox" role="switch" id="strict_active_clinics_only" name="strict_active_clinics_only" <?= ($settings['strict_active_clinics_only'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="strict_active_clinics_only">
                      แสดงและสร้างคิวเฉพาะคลินิกที่เปิดใช้งานเท่านั้น (Strict Active Clinics Filtering)
                      <div class="text-muted fw-normal fs-7">หากเปิดใช้ ระบบจะกรองคิวและ Tab ให้เฉพาะผู้ป่วยที่ตรงกับคลินิกที่สวิตช์เปิดอยู่เท่านั้น</div>
                    </label>
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-hover align-middle border mb-0" id="clinicsTable">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 60px;" class="text-center">สถานะ</th>
                      <th>ชื่อคลินิก / แผนก (HIS Clinic Name)</th>
                      <th>รหัสคลินิก (Code)</th>
                      <th>ประเภท (Department)</th>
                      <th class="text-center" style="width: 120px;">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody id="clinicsListBody">
                    <!-- Populated via JS -->
                  </tbody>
                </table>
              </div>

            </div>
          </div>
        </div>

        <!-- TAB 2: HOSPITAL INFO -->
        <div class="tab-pane fade" id="content-hospital">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
              <h5 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-building-hospital me-2 text-info"></i> ข้อมูลองค์กรและโรงพยาบาล</h5>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">ชื่อโรงพยาบาล (ภาษาไทย)</label>
                  <input type="text" name="hospital_name_th" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['hospital_name_th'] ?? 'โรงพยาบาลปลวกแดง') ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">ชื่อโรงพยาบาล (ภาษาอังกฤษ)</label>
                  <input type="text" name="hospital_name_en" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['hospital_name_en'] ?? 'Pluakdaeng Hospital') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">รหัสสถานพยาบาล (HCODE 5 หลัก)</label>
                  <input type="text" name="hospital_code" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['hospital_code'] ?? '11467') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">ชื่อกลุ่มงาน / แผนก</label>
                  <input type="text" name="department_name" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['department_name'] ?? 'กลุ่มงานโภชนวิทยา') ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: HIS GATEWAY INTEGRATION -->
        <div class="tab-pane fade" id="content-his">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
              <h5 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-network-wired me-2 text-warning"></i> การเชื่อมต่อระบบ HIS & API Gateway</h5>
              <button type="button" onclick="testApiGateway()" class="btn btn-outline-warning fw-bold">
                <i class="fa-solid fa-plug-circle-bolt me-1"></i> ทดสอบเชื่อมต่อ API (Ping Test)
              </button>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label fw-bold">HIS Driver Mode</label>
                  <select name="his_driver" class="form-select form-select-lg">
                    <option value="himpro" <?= ($settings['his_driver'] ?? 'himpro') === 'himpro' ? 'selected' : '' ?>>HIMPRO API Gateway (Live Real System)</option>
                    <option value="mock" <?= ($settings['his_driver'] ?? 'himpro') === 'mock' ? 'selected' : '' ?>>Mock Data (Developer Mode)</option>
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label fw-bold">PDH API Gateway Base URL</label>
                  <input type="url" name="his_api_url" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['his_api_url'] ?? 'http://192.168.111.240/pdhapi') ?>" placeholder="http://192.168.111.240/pdhapi">
                </div>
                <div class="col-md-8">
                  <label class="form-label fw-bold">API Key / Token Authorization</label>
                  <input type="password" name="his_api_key" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['his_api_key'] ?? 'PDHAPI-CHANGE-THIS-KEY') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-bold">Request Timeout (วินาที)</label>
                  <input type="number" name="his_timeout" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['his_timeout'] ?? '10') ?>" min="2" max="60">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: CLINICAL RULES & QUEUE -->
        <div class="tab-pane fade" id="content-clinical">
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
              <h5 class="fw-bold text-pdh-blue mb-0"><i class="fa-solid fa-clipboard-check me-2 text-danger"></i> เกณฑ์ประเมิน NAF และระบบคิวโภชนาการ</h5>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">ระยะเวลาติดตามผลการประเมินมาตรฐาน (วัน)</label>
                  <input type="number" name="followup_default_interval_days" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['followup_default_interval_days'] ?? '14') ?>" min="1" max="90">
                  <small class="text-muted">จำนวนวันตั้งต้นเมื่อสร้างรายการใน Nutrition Registry หรือกำหนด Follow-up</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">คะแนน NAF เริ่มต้นสำหรับแจ้งเตือน High Risk Alert (คะแนน)</label>
                  <input type="number" name="high_risk_alert_threshold" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['high_risk_alert_threshold'] ?? '8') ?>" min="1" max="15">
                  <small class="text-muted">ตั้งแต่คะแนนนี้ขึ้นไป จะถูกจัดเป็น NAF C (Severe Malnutrition) / High Risk</small>
                </div>
                <div class="col-md-12">
                  <div class="form-check form-switch card bg-light border p-3">
                    <input class="form-check-input ms-0 me-3 fs-5" type="checkbox" role="switch" id="auto_create_queue_task" name="auto_create_queue_task" <?= ($settings['auto_create_queue_task'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label fw-bold" for="auto_create_queue_task">
                      สร้าง Nutrition Queue Task อัตโนมัติเมื่อผู้ป่วยใน Registry หรือผู้ป่วยกลุ่มเสี่ยงมา Visit
                      <div class="text-muted fw-normal fs-7">ระบบจะดึงผู้ป่วยเข้าคิว "รอประเมินก่อนพบแพทย์" โดยอัตโนมัติในตอนเช้าของวันที่มีการ Visit</div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<!-- Modal: Add/Edit Clinic -->
<div class="modal fade" id="clinicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-pdh-blue text-white">
        <h5 class="modal-title fw-bold" id="clinicModalTitle"><i class="fa-solid fa-hospital-user me-2"></i> เพิ่มคลินิกใหม่</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="clinicForm">
          <input type="hidden" id="clinicEditId">
          <div class="mb-3">
            <label class="form-label fw-bold">ชื่อคลินิก / แผนก (Clinic Name)</label>
            <input type="text" id="clinicName" class="form-control form-control-lg" placeholder="เช่น คลินิกเบาหวาน (NCD), วอร์ดศัลยกรรม" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">รหัสคลินิก (Clinic Code)</label>
            <input type="text" id="clinicCode" class="form-control" placeholder="เช่น NCD_DM, WARD_SURG">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">ประเภทแผนก (Department)</label>
            <select id="clinicDept" class="form-select form-select-lg">
              <option value="OPD">ผู้ป่วยนอก (OPD Clinic)</option>
              <option value="IPD">ผู้ป่วยใน (IPD Ward)</option>
              <option value="SPECIAL">คลินิกพิเศษ / เฉพาะทาง</option>
            </select>
          </div>
          <div class="form-check form-switch mt-3">
            <input class="form-check-input fs-5" type="checkbox" id="clinicActiveState" checked>
            <label class="form-check-label fw-bold" for="clinicActiveState">เปิดใช้งานคลินิกนี้ในระบบ</label>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" onclick="saveClinicItem()" class="btn btn-primary fw-bold">บันทึกข้อมูลคลินิก</button>
      </div>
    </div>
  </div>
</div>

<script>
let clinicsData = <?= json_encode($clinics, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function() {
  renderClinicsTable();
});

function renderClinicsTable() {
  const tbody = document.getElementById('clinicsListBody');
  tbody.innerHTML = '';

  if (clinicsData.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลคลินิก กรุณากดปุ่มเพิ่มคลินิกใหม่</td></tr>`;
    return;
  }

  clinicsData.forEach((c, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-center">
        <div class="form-check form-switch d-inline-block">
          <input class="form-check-input fs-5" type="checkbox" role="switch" ${c.active ? 'checked' : ''} onchange="toggleClinicActive(${idx}, this.checked)">
        </div>
      </td>
      <td class="fw-bold text-pdh-blue">${escapeHtml(c.name)}</td>
      <td><code class="px-2 py-1 bg-light rounded text-dark">${escapeHtml(c.code || '-')}</code></td>
      <td>
        <span class="badge ${c.department === 'IPD' ? 'bg-purple text-white' : 'bg-info-subtle text-info border border-info'}">
          ${escapeHtml(c.department || 'OPD')}
        </span>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editClinicItem(${idx})">
          <i class="fa-solid fa-pen-to-square"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteClinicItem(${idx})">
          <i class="fa-solid fa-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function toggleClinicActive(index, state) {
  clinicsData[index].active = state;
}

function openAddClinicModal() {
  document.getElementById('clinicEditId').value = '';
  document.getElementById('clinicModalTitle').innerHTML = '<i class="fa-solid fa-hospital-user me-2"></i> เพิ่มคลินิกใหม่';
  document.getElementById('clinicName').value = '';
  document.getElementById('clinicCode').value = '';
  document.getElementById('clinicDept').value = 'OPD';
  document.getElementById('clinicActiveState').checked = true;
  const modal = new bootstrap.Modal(document.getElementById('clinicModal'));
  modal.show();
}

function editClinicItem(index) {
  const item = clinicsData[index];
  document.getElementById('clinicEditId').value = index;
  document.getElementById('clinicModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i> แก้ไขข้อมูลคลินิก';
  document.getElementById('clinicName').value = item.name;
  document.getElementById('clinicCode').value = item.code || '';
  document.getElementById('clinicDept').value = item.department || 'OPD';
  document.getElementById('clinicActiveState').checked = !!item.active;
  const modal = new bootstrap.Modal(document.getElementById('clinicModal'));
  modal.show();
}

function saveClinicItem() {
  const name = document.getElementById('clinicName').value.trim();
  const code = document.getElementById('clinicCode').value.trim();
  const dept = document.getElementById('clinicDept').value;
  const active = document.getElementById('clinicActiveState').checked;
  const editId = document.getElementById('clinicEditId').value;

  if (!name) {
    alert('กรุณากรอกชื่อคลินิก');
    return;
  }

  if (editId !== '') {
    const idx = parseInt(editId);
    clinicsData[idx] = { ...clinicsData[idx], name, code, department: dept, active };
  } else {
    const newId = 'c' + (clinicsData.length + 1) + '_' + Date.now();
    clinicsData.push({ id: newId, name, code, department: dept, active });
  }

  renderClinicsTable();
  const modalEl = document.getElementById('clinicModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  modal.hide();
}

function deleteClinicItem(index) {
  if (confirm('คุณต้องการลบคลินิก "' + clinicsData[index].name + '" ออกจากรายการหรือไม่?')) {
    clinicsData.splice(index, 1);
    renderClinicsTable();
  }
}

function saveAllSettings() {
  document.getElementById('clinicsJsonInput').value = JSON.stringify(clinicsData);
  const form = document.getElementById('settingsForm');
  const formData = new FormData(form);

  fetch('<?= $baseUrl ?>/admin/settings/save', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      const notice = document.getElementById('alertNotice');
      document.getElementById('alertMsg').innerText = res.message;
      notice.classList.remove('d-none');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      setTimeout(() => notice.classList.add('d-none'), 4000);
    } else {
      alert(res.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }
  })
  .catch(err => {
    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + err.message);
  });
}

function testApiGateway() {
  fetch('<?= $baseUrl ?>/api/test-endpoint')
  .then(r => r.json())
  .then(res => {
    alert('ผลการทดสอบการเชื่อมต่อ API Gateway:\n' + JSON.stringify(res, null, 2));
  })
  .catch(err => alert('ไม่สามารถเชื่อมต่อได้: ' + err.message));
}

function escapeHtml(text) {
  if (!text) return '';
  return text.replace(/[&<>"']/g, function(m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
  });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
