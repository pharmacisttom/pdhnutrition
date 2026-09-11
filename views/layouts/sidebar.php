<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::get('APP_URL', '/pdhnutrition');
$currentRole = $_SESSION['user_role'] ?? 'VIEWER';
?>
<!-- Sidebar -->
<div class="bg-pdh-blue border-end no-print" id="sidebar-wrapper" style="min-width: 250px; max-width: 250px; min-height: 100vh;">
  <div class="sidebar-heading text-white fw-bold py-3 px-3 border-bottom fs-5 d-flex align-items-center">
    <i class="fa-solid fa-heart-pulse text-success me-2 fs-4"></i>
    <div>
      <div>PDH Nutrition</div>
      <small class="fw-normal text-white-50 fs-6">รพ.ปลวกแดง</small>
    </div>
  </div>

  <div class="list-group list-group-flush pt-2">
    <a href="<?= $baseUrl ?>/dashboard" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
      <i class="fa-solid fa-chart-line me-2 text-info"></i> Dashboard
    </a>
    
    <a href="<?= $baseUrl ?>/nutrition-queue" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 fw-bold text-warning hover-bg">
      <i class="fa-solid fa-user-clock me-2"></i> คิวประเมินก่อนพบแพทย์
      <span class="badge bg-danger rounded-pill float-end">CORE</span>
    </a>

    <a href="<?= $baseUrl ?>/patients/today" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
      <i class="fa-solid fa-calendar-day me-2 text-success"></i> ผู้ป่วย Visit วันนี้
    </a>

    <a href="<?= $baseUrl ?>/patients/search" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
      <i class="fa-solid fa-magnifying-glass me-2 text-warning"></i> ค้นหาผู้ป่วย
    </a>

    <a href="<?= $baseUrl ?>/registry" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
      <i class="fa-solid fa-clipboard-list me-2 text-primary"></i> Nutrition Registry
    </a>

    <a href="<?= $baseUrl ?>/reports" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
      <i class="fa-solid fa-file-invoice me-2 text-info"></i> รายงานโภชนาการ (10)
    </a>

    <?php if (in_array($currentRole, ['SUPER_ADMIN', 'ADMIN'])): ?>
      <div class="sidebar-heading text-white-50 text-uppercase px-3 pt-3 pb-1 fs-7">Administration</div>
      
      <a href="<?= $baseUrl ?>/admin/users" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
        <i class="fa-solid fa-users me-2"></i> จัดการผู้ใช้งาน
      </a>
      <a href="<?= $baseUrl ?>/admin/rules" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
        <i class="fa-solid fa-sliders me-2"></i> NAF Rules Engine
      </a>
      <a href="<?= $baseUrl ?>/admin/audit-log" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
        <i class="fa-solid fa-shield-halved me-2"></i> Audit Log
      </a>
      <a href="<?= $baseUrl ?>/admin/api-test" class="list-group-item list-group-item-action bg-transparent text-white border-0 py-2 px-3 hover-bg">
        <i class="fa-solid fa-network-wired me-2"></i> HIS API Gateway Test
      </a>
    <?php endif; ?>
  </div>

  <div class="mt-auto p-3 text-white-50 fs-7 border-top border-secondary">
    <div>PDH Nutrition v1.0</div>
    <div>โรงพยาบาลปลวกแดง</div>
  </div>
</div>
