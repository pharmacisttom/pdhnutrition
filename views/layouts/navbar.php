<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::routeBase();
$assetUrl = AppConfig::get('APP_URL', '/pdhnutrition');
$hisDriver = AppConfig::get('HIS_DRIVER', 'mock');
$userName = $_SESSION['fullname'] ?? 'ผู้ใช้งาน';
$userRole = $_SESSION['user_role'] ?? 'DIETITIAN';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm py-2 px-3 no-print">
  <div class="container-fluid">
    <div class="d-flex align-items-center">
      <button type="button" class="btn btn-outline-primary border-0 me-2 px-2 py-1" id="menu-toggle" title="ย่อ/ขยายเมนูด้านข้าง" aria-label="ย่อ/ขยายเมนูด้านข้าง" aria-controls="sidebar-wrapper" aria-expanded="false">
        <i class="fa-solid fa-bars fs-4"></i>
      </button>
      <span class="navbar-brand fw-bold text-pdh-blue fs-5 d-flex align-items-center mb-0">
        <img src="<?= $assetUrl ?>/public/assets/img/logo.png" alt="PDH Nutrition Logo" style="height: 35px; width: 35px; border-radius: 50%; object-fit: cover;" class="me-2 shadow-sm">
        โรงพยาบาลปลวกแดง
      </span>
    </div>

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
