<?php
use App\Config\AppConfig;
$baseUrl = AppConfig::routeBase();
$assetUrl = AppConfig::get('APP_URL', '/pdhnutrition');
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ - PDH Nutrition โรงพยาบาลปลวกแดง</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background: linear-gradient(135deg, #0d3b66 0%, #1d5b96 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .login-card {
      width: 100%;
      max-width: 440px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    @media (max-height: 700px) {
      body { align-items: flex-start; }
    }
    @media (max-width: 575.98px) {
      .login-card { padding: 1.25rem !important; }
      .form-control { font-size: 1rem; min-height: 44px; }
    }
  </style>
</head>
<body>

<div class="card login-card bg-white p-4">
  <div class="text-center mb-4">
    <img src="<?= $assetUrl ?>/public/assets/img/logo.png" alt="PDH Nutrition Logo" style="height: 90px; width: 90px; border-radius: 50%; object-fit: cover;" class="mb-3 shadow">
    <h4 class="fw-bold text-pdh-blue mb-1">PDH Nutrition System</h4>
    <p class="text-muted fs-6">ระบบบริหารจัดการภาวะโภชนาการผู้ป่วย โรงพยาบาลปลวกแดง</p>
  </div>

  <form id="loginForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    
    <div class="mb-3">
      <label class="form-label fw-bold"><i class="fa-solid fa-user me-1"></i> ชื่อผู้ใช้งาน (Username)</label>
      <input type="text" name="username" class="form-control form-control-lg" placeholder="ระบุชื่อผู้ใช้งาน" required autofocus value="admin">
    </div>

    <div class="mb-4">
      <label class="form-label fw-bold"><i class="fa-solid fa-key me-1"></i> รหัสผ่าน (Password)</label>
      <input type="password" name="password" class="form-control form-control-lg" placeholder="ระบุรหัสผ่าน" required value="pdh10832">
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-2 shadow-sm">
      <i class="fa-solid fa-right-to-bracket me-2"></i> เข้าสู่ระบบ
    </button>
  </form>

  <div class="mt-4 pt-3 border-top text-center text-muted fs-7">
    <div>บัญชีทดสอบ: admin (รหัสผ่าน: pdh10832)</div>
    <div>บัญชีอื่น: dietitian1, doctor1, nurse1 (รหัสผ่าน: password123)</div>
    <div class="mt-2 text-primary fw-semibold"><i class="fa-solid fa-code me-1"></i> พัฒนาโดย tomvis</div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
function doLoginSubmit(targetUrl, formData) {
  $.ajax({
    url: targetUrl,
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(res) {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'เข้าสู่ระบบสำเร็จ',
          text: res.message,
          timer: 1200,
          showConfirmButton: false
        }).then(() => {
          window.location.href = res.redirect;
        });
      }
    },
    error: function(xhr) {
      if (xhr.status === 404 && targetUrl.indexOf('index.php') === -1) {
        // Automatic fallback retry using index.php URL for Apache servers without mod_rewrite
        doLoginSubmit('<?= $baseUrl ?>/index.php/login/submit', formData);
      } else {
        const err = xhr.responseJSON ? xhr.responseJSON.message : 'ไม่สามารถเข้าสู่ระบบได้';
        Swal.fire('ข้อผิดพลาด', err, 'error');
      }
    }
  });
}

$('#loginForm').on('submit', function(e) {
  e.preventDefault();
  doLoginSubmit('<?= $baseUrl ?>/login/submit', $(this).serialize());
});
</script>
</body>
</html>
