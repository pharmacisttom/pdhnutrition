<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::get('APP_URL', '/pdhnutrition');
$hisDriver = AppConfig::get('HIS_DRIVER', 'mock');
$userName = $_SESSION['fullname'] ?? 'ผู้ใช้งาน';
$userRole = $_SESSION['user_role'] ?? 'DIETITIAN';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm py-2 px-3 no-print">
  <div class="container-fluid">
    <span class="navbar-brand fw-bold text-pdh-blue fs-5">
      <i class="fa-solid fa-hospital me-2 text-danger"></i> โรงพยาบาลปลวกแดง
    </span>

    <div class="d-flex align-items-center ms-auto">
      <!-- HIS Connection Status Badge -->
      <span class="badge bg-success-subtle text-success border border-success me-3 py-2 px-3 rounded-pill">
        <i class="fa-solid fa-signal me-1"></i> HIS: <?= strtoupper($hisDriver) ?> Mode
      </span>

      <!-- User Info -->
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
          <i class="fa-solid fa-user-doctor text-pdh-blue me-1"></i> <?= htmlspecialchars($userName) ?>
          <span class="badge bg-primary ms-1"><?= htmlspecialchars($userRole) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li><a class="dropdown-item" href="<?= $baseUrl ?>/logout"><i class="fa-solid fa-right-from-bracket text-danger me-2"></i> ออกจากระบบ</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>
