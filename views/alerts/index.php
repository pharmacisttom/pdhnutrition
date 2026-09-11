<?php
$pageTitle = "ระบบแจ้งเตือนโภชนาการอัจฉริยะรายวัน (Smart Daily Alerts) - PDH Nutrition";
require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
use App\Helpers\SanitizerHelper;
use App\Helpers\DateHelper;
?>

<div id="page-content-wrapper" class="w-100">
  <?php require __DIR__ . '/../layouts/navbar.php'; ?>

  <div class="container-fluid p-4">

    <!-- Header & Action Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h3 class="fw-bold text-pdh-blue mb-0">
          <i class="fa-solid fa-bell text-danger me-2"></i> ระบบแจ้งเตือนโภชนาการอัจฉริยะรายวัน (Smart Daily Alerts)
        </h3>
        <p class="text-muted mb-0">คัดกรองและแจ้งเตือนผู้ป่วยที่มีความเสี่ยงภาวะทุพโภชนาการ (ทั้งขาด/บกพร่อง และเกิน/เมแทบอลิก) ประจำวัน</p>
      </div>

      <div class="d-flex gap-2 mt-2 mt-md-0">
        <button onclick="triggerBroadcast()" class="btn btn-success fw-bold shadow-sm">
          <i class="fa-solid fa-paper-plane me-1"></i> สรุปส่งแจ้งเตือนรายวัน (Broadcast)
        </button>
        <a href="<?= $baseUrl ?>/alerts/heatmap" class="btn btn-warning text-dark fw-bold shadow-sm">
          <i class="fa-solid fa-fire-flame-curved me-1"></i> Clinical Risk Heatmap Matrix
        </a>
      </div>
    </div>

    <!-- Summary Count Cards -->
    <div class="row g-3 mb-4">
      
      <!-- Critical 24h Card -->
      <div class="col-md-4">
        <div class="card p-3 border-0 border-start border-danger border-5 bg-white shadow-sm h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-danger fs-6 mb-1">🔴 CRITICAL URGENT</span>
              <h3 class="fw-bold text-danger mb-0"><?= count($alerts['critical_24h']) ?> ราย</h3>
              <small class="text-muted">ต้องได้รับการประเมินและดูแลภายใน 24 ชั่วโมง</small>
            </div>
            <div class="bg-danger-subtle p-3 rounded-circle text-danger">
              <i class="fa-solid fa-triangle-exclamation fs-1"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Urgent 3-Day Card -->
      <div class="col-md-4">
        <div class="card p-3 border-0 border-start border-warning border-5 bg-white shadow-sm h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-warning text-dark fs-6 mb-1">🟠 HIGH RISK</span>
              <h3 class="fw-bold text-warning-emphasis mb-0"><?= count($alerts['urgent_3day']) ?> ราย</h3>
              <small class="text-muted">ต้องได้รับการประเมินและดูแลภายใน 3 วัน</small>
            </div>
            <div class="bg-warning-subtle p-3 rounded-circle text-warning-emphasis">
              <i class="fa-solid fa-user-doctor fs-1"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Routine Follow-up Card -->
      <div class="col-md-4">
        <div class="card p-3 border-0 border-start border-info border-5 bg-white shadow-sm h-100">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="badge bg-info text-dark fs-6 mb-1">🟡 ROUTINE FOLLOW-UP</span>
              <h3 class="fw-bold text-info mb-0"><?= count($alerts['routine_7day']) ?> ราย</h3>
              <small class="text-muted">กลุ่มเฝ้าระวังและติดตามใน Registry</small>
            </div>
            <div class="bg-info-subtle p-3 rounded-circle text-info">
              <i class="fa-solid fa-clipboard-check fs-1"></i>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Alert List Navigation Tabs -->
    <ul class="nav nav-tabs fw-bold mb-3" id="alertTabs" role="tablist">
      <li class="nav-item">
        <button class="nav-link active py-2 text-danger" data-bs-toggle="tab" data-bs-target="#criticalTab">
          <i class="fa-solid fa-circle-exclamation me-1"></i> รายการเสี่ยงสูงรุนแรง (Critical 24h) (<?= count($alerts['critical_24h']) ?>)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 text-warning-emphasis" data-bs-toggle="tab" data-bs-target="#urgentTab">
          <i class="fa-solid fa-triangle-exclamation me-1"></i> รายการเสี่ยงปานกลาง (High 3-Day) (<?= count($alerts['urgent_3day']) ?>)
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link py-2 text-info" data-bs-toggle="tab" data-bs-target="#routineTab">
          <i class="fa-solid fa-shield-heart me-1"></i> รายการเฝ้าระวัง (Routine 7-Day) (<?= count($alerts['routine_7day']) ?>)
        </button>
      </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="alertTabsContent">
      
      <!-- TAB 1: Critical 24h Alerts -->
      <div class="tab-pane fade show active" id="criticalTab">
        <div class="card shadow-sm border-0">
          <div class="card-body p-0">
            <?php renderAlertTable($alerts['critical_24h'], $baseUrl, 'danger'); ?>
          </div>
        </div>
      </div>

      <!-- TAB 2: Urgent 3-Day Alerts -->
      <div class="tab-pane fade" id="urgentTab">
        <div class="card shadow-sm border-0">
          <div class="card-body p-0">
            <?php renderAlertTable($alerts['urgent_3day'], $baseUrl, 'warning'); ?>
          </div>
        </div>
      </div>

      <!-- TAB 3: Routine Follow-up Alerts -->
      <div class="tab-pane fade" id="routineTab">
        <div class="card shadow-sm border-0">
          <div class="card-body p-0">
            <?php renderAlertTable($alerts['routine_7day'], $baseUrl, 'info'); ?>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?php
function renderAlertTable($list, $baseUrl, $badgeType) { ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 datatable">
      <thead class="table-light">
        <tr>
          <th>HN / CID</th>
          <th>ชื่อ - นามสกุล</th>
          <th>เพศ / อายุ</th>
          <th>แผนก / วอร์ด</th>
          <th>รายการแจ้งเตือนความเสี่ยง (Risk Flag)</th>
          <th>NAF Grade ล่าสุด</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $p): ?>
          <tr>
            <td>
              <div class="fw-bold text-pdh-blue fs-6">
                <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($p['hn']) ?>" class="text-decoration-none"><?= htmlspecialchars($p['hn']) ?></a>
              </div>
              <small class="text-muted"><?= SanitizerHelper::maskCid($p['cid'] ?? '') ?></small>
            </td>

            <td>
              <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($p['fullname']) ?></div>
              <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($p['phone'] ?? '-') ?></small>
            </td>

            <td>
              <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($p['gender'] ?? '-') ?></span>
              <span><?= $p['age'] ?? '-' ?> ปี</span>
            </td>

            <td>
              <span class="badge bg-info-subtle text-info border border-info">
                <?= htmlspecialchars($p['last_clinic'] ?? 'OPD/IPD') ?>
              </span>
            </td>

            <td>
              <?php foreach ($p['alert_items'] as $item): ?>
                <div class="mb-1">
                  <span class="badge bg-<?= ($item['level']==='CRITICAL')?'danger':(($item['level']==='HIGH')?'warning text-dark':'info') ?> fs-7">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($item['msg']) ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </td>

            <td>
              <?php if (!empty($p['last_naf_grade'])): ?>
                <span class="badge badge-naf-<?= strtolower(substr($p['last_naf_grade'], -1)) ?> fs-6">
                  <?= htmlspecialchars($p['last_naf_grade']) ?> (Score: <?= $p['last_naf_score'] ?? '-' ?>)
                </span>
              <?php else: ?>
                <span class="text-muted fs-7">ยังไม่เคยประเมิน</span>
              <?php endif; ?>
            </td>

            <td class="text-center">
              <a href="<?= $baseUrl ?>/naf/create?hn=<?= $p['hn'] ?>" class="btn btn-sm btn-success fw-bold me-1">
                <i class="fa-solid fa-clipboard-check me-1"></i> ประเมิน NAF
              </a>
              <a href="<?= $baseUrl ?>/patient/<?= htmlspecialchars($p['hn']) ?>" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-id-card me-1"></i> ดูประวัติ
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php } ?>

<script>
function triggerBroadcast() {
  Swal.fire({
    title: 'ส่งการแจ้งเตือนรายวัน (Broadcast Alert)',
    text: "ระบบจะทำการประมวลผลและกระจายสัญญาณการแจ้งเตือนไปยังทีมนักโภชนาการและแพทย์ที่เกี่ยวข้อง",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'ยืนยันส่งการแจ้งเตือน',
    cancelButtonText: 'ยกเลิก'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('<?= $baseUrl ?>/alerts/broadcast', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
          Swal.fire('ส่งแจ้งเตือนสำเร็จ!', data.summary, 'success');
        })
        .catch(err => {
          Swal.fire('เกิดข้อผิดพลาด!', err.message, 'error');
        });
    }
  });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
