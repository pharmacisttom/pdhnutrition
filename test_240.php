<?php
/**
 * PDH Nutrition System - Server 240 Comprehensive Diagnostic Test Script
 * Access via: http://192.168.111.240/pdhnutrition/test_240.php
 */

// Load Environment & Autoloader
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

\App\Config\AppConfig::load();
$baseUrl = \App\Config\AppConfig::get('APP_URL', '/pdhnutrition');

// Perform Tests
$results = [];

// 1. Server Environment
$results['env'] = [
    'server_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
    'server_ip'   => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
    'client_ip'   => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'php_version' => PHP_VERSION,
    'app_url'     => $baseUrl,
    'app_env'     => \App\Config\AppConfig::get('APP_ENV', 'unknown'),
    'timezone'    => date_default_timezone_get(),
    'current_time'=> date('Y-m-d H:i:s')
];

// 2. Database Connection Test
$dbTest = ['status' => false, 'message' => '', 'details' => []];
try {
    $pdo = \App\Config\Database::getConnection();
    $dbTest['status'] = true;
    $dbTest['message'] = "เชื่อมต่อฐานข้อมูลสำเร็จ!";
    
    // Fetch DB Server Version
    $verStmt = $pdo->query("SELECT VERSION() as ver");
    $dbTest['details']['db_version'] = $verStmt->fetchColumn();

    // Count Users
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $dbTest['details']['user_count'] = $userCount;

    // Check Admin Account
    $adminStmt = $pdo->prepare("SELECT username, role, status, password_hash FROM users WHERE username = 'admin'");
    $adminStmt->execute();
    $adminUser = $adminStmt->fetch(PDO::FETCH_ASSOC);
    if ($adminUser) {
        $isPassValid = password_verify('pdh10832', $adminUser['password_hash']);
        $dbTest['details']['admin_check'] = "พบผู้ใช้ admin (Role: {$adminUser['role']}, Status: {$adminUser['status']}, Password 'pdh10832': " . ($isPassValid ? "ถูกต้อง 🟢" : "ไม่ถูกต้อง 🔴") . ")";
    } else {
        $dbTest['details']['admin_check'] = "ไม่พบผู้ใช้ admin 🔴";
    }

    // Count Patients Cache
    $ptCount = $pdo->query("SELECT COUNT(*) FROM patients_cache")->fetchColumn();
    $dbTest['details']['patients_count'] = $ptCount;

    // Count NAF Assessments
    $nafCount = $pdo->query("SELECT COUNT(*) FROM naf_assessments")->fetchColumn();
    $dbTest['details']['naf_count'] = $nafCount;

} catch (Exception $e) {
    $dbTest['status'] = false;
    $dbTest['message'] = "ล้มเหลว: " . $e->getMessage();
}
$results['db'] = $dbTest;

// 3. HIS API Gateway Ping Test (http://192.168.111.240/pdhapi)
$hisUrl = \App\Config\AppConfig::get('PDH_API_BASE_URL', 'http://192.168.111.240/pdhapi');
$hisTest = ['status' => false, 'url' => $hisUrl, 'latency_ms' => 0, 'message' => ''];
$startTime = microtime(true);

$ch = curl_init($hisUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

$hisTest['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
if ($response !== false || $httpCode > 0) {
    $hisTest['status'] = true;
    $hisTest['message'] = "สามารถเชื่อมต่อ HIS API Gateway ได้ (HTTP {$httpCode})";
} else {
    $hisTest['status'] = false;
    $hisTest['message'] = "ไม่สามารถเชื่อมต่อได้: " . ($curlErr ?: "Timeout");
}
$results['his'] = $hisTest;

// 4. File Permission Test
$storageDir = __DIR__ . '/storage';
$fileTest = ['status' => false, 'message' => ''];
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}
$testFile = $storageDir . '/test_write.tmp';
if (@file_put_contents($testFile, 'test') !== false) {
    @unlink($testFile);
    $fileTest['status'] = true;
    $fileTest['message'] = "ไดเรกทอรี storage สามารถเขียนไฟล์ได้ปกติ 🟢";
} else {
    $fileTest['status'] = false;
    $fileTest['message'] = "ไม่สามารถเขียนไฟล์ลงใน storage ได้ 🔴";
}
$results['file'] = $fileTest;
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Server 240 System Diagnostic Test - PDH Nutrition</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Sarabun', sans-serif; background-color: #f4f6f9; }
    .card { border-radius: 10px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .bg-pdh { background: linear-gradient(135deg, #0d3b66 0%, #1d5b96 100%); color: white; }
  </style>
</head>
<body>

<div class="container py-5">
  
  <div class="card bg-pdh p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-server me-2"></i> Server 240 System Diagnostic Suite</h3>
        <p class="mb-0 text-white-50">หน้าทดสอบความพร้อมของการเชื่อมต่อระบบ PDH Nutrition บน Server 192.168.111.240</p>
      </div>
      <a href="<?= $baseUrl ?>/login" class="btn btn-warning fw-bold"><i class="fa-solid fa-right-to-bracket me-1"></i> ไปหน้าเข้าสู่ระบบ</a>
    </div>
  </div>

  <div class="row g-4">

    <!-- 1. Server Environment -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white fw-bold py-3 text-pdh-blue border-bottom">
          <i class="fa-solid fa-desktop me-2 text-primary"></i> 1. สภาพแวดล้อมเซิร์ฟเวอร์ (Server Environment)
        </div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0 fs-7">
            <tr>
              <td class="text-muted" style="width: 150px;">Server Host:</td>
              <td class="fw-bold text-primary"><?= htmlspecialchars($results['env']['server_host']) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Server IP:</td>
              <td class="fw-bold"><?= htmlspecialchars($results['env']['server_ip']) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Client IP:</td>
              <td><?= htmlspecialchars($results['env']['client_ip']) ?></td>
            </tr>
            <tr>
              <td class="text-muted">PHP Version:</td>
              <td><span class="badge bg-secondary"><?= htmlspecialchars($results['env']['php_version']) ?></span></td>
            </tr>
            <tr>
              <td class="text-muted">APP_URL (Base):</td>
              <td class="fw-bold text-success"><?= htmlspecialchars($results['env']['app_url']) ?></td>
            </tr>
            <tr>
              <td class="text-muted">Environment Mode:</td>
              <td><span class="badge bg-info text-dark"><?= htmlspecialchars($results['env']['app_env']) ?></span></td>
            </tr>
            <tr>
              <td class="text-muted">Timezone / เวลา:</td>
              <td><?= htmlspecialchars($results['env']['timezone']) ?> (<?= htmlspecialchars($results['env']['current_time']) ?>)</td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- 2. Database Test -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white fw-bold py-3 text-pdh-blue border-bottom d-flex justify-content-between">
          <span><i class="fa-solid fa-database me-2 text-success"></i> 2. การเชื่อมต่อฐานข้อมูล MySQL</span>
          <?php if ($results['db']['status']): ?>
            <span class="badge bg-success">CONNECTED 🟢</span>
          <?php else: ?>
            <span class="badge bg-danger">FAILED 🔴</span>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="alert alert-<?= $results['db']['status'] ? 'success' : 'danger' ?> py-2 mb-3">
            <strong><?= $results['db']['message'] ?></strong>
          </div>
          <?php if ($results['db']['status']): ?>
            <table class="table table-sm table-borderless mb-0 fs-7">
              <tr>
                <td class="text-muted" style="width: 160px;">MySQL Version:</td>
                <td class="fw-semibold"><?= htmlspecialchars($results['db']['details']['db_version']) ?></td>
              </tr>
              <tr>
                <td class="text-muted">ผู้ใช้งานในระบบ (Users):</td>
                <td class="fw-bold"><?= $results['db']['details']['user_count'] ?> บัญชี</td>
              </tr>
              <tr>
                <td class="text-muted">สถานะบัญชี Admin:</td>
                <td><?= $results['db']['details']['admin_check'] ?></td>
              </tr>
              <tr>
                <td class="text-muted">แคชข้อมูลผู้ป่วย (Patients):</td>
                <td><?= $results['db']['details']['patients_count'] ?> รายการ</td>
              </tr>
              <tr>
                <td class="text-muted">แบบประเมิน NAF ที่บันทึก:</td>
                <td><?= $results['db']['details']['naf_count'] ?> ฉบับ</td>
              </tr>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 3. HIS API Gateway Test -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white fw-bold py-3 text-pdh-blue border-bottom d-flex justify-content-between">
          <span><i class="fa-solid fa-network-wired me-2 text-info"></i> 3. HIS API Gateway Connection</span>
          <?php if ($results['his']['status']): ?>
            <span class="badge bg-success">ONLINE 🟢</span>
          <?php else: ?>
            <span class="badge bg-warning text-dark">OFFLINE / TIMEOUT 🟠</span>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0 fs-7">
            <tr>
              <td class="text-muted" style="width: 140px;">Gateway URL:</td>
              <td class="fw-bold text-pdh-blue"><code><?= htmlspecialchars($results['his']['url']) ?></code></td>
            </tr>
            <tr>
              <td class="text-muted">Latency Time:</td>
              <td><span class="badge bg-light text-dark border"><?= $results['his']['latency_ms'] ?> ms</span></td>
            </tr>
            <tr>
              <td class="text-muted">ผลการทดสอบ:</td>
              <td class="fw-semibold text-<?= $results['his']['status'] ? 'success' : 'danger' ?>"><?= $results['his']['message'] ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <!-- 4. Storage & Ajax Self-Test -->
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-header bg-white fw-bold py-3 text-pdh-blue border-bottom">
          <i class="fa-solid fa-check-double me-2 text-warning"></i> 4. สิทธิ์ไฟล์ และ AJAX Connectivity Test
        </div>
        <div class="card-body">
          <div class="mb-3 fs-7">
            <span class="text-muted">สิทธิ์การเขียนไฟล์ storage:</span>
            <div class="fw-bold mt-1"><?= $results['file']['message'] ?></div>
          </div>
          <div class="border-top pt-3 fs-7">
            <span class="text-muted">ทดสอบ AJAX Post ไปยัง Server:</span>
            <div id="ajaxTestResult" class="mt-1 fw-bold text-secondary">
              <i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังทดสอบ AJAX Request...
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="text-center mt-5 text-muted fs-7">
    <div>PDH Nutrition System v1.0 Diagnostic Suite &copy; <?= date('Y') ?> โรงพยาบาลปลวกแดง</div>
    <div>พัฒนาโดย <strong>tomvis</strong></div>
  </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
  // Test AJAX connectivity from browser to login endpoint
  $.ajax({
    url: '<?= $baseUrl ?>/login/submit',
    type: 'POST',
    data: { username: '', password: '' },
    dataType: 'json',
    success: function(res) {
      $('#ajaxTestResult').html('<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> AJAX ตอบรับปกติ 🟢 (Server Endpoint ตอบสนองตามคาด)</span>');
    },
    error: function(xhr) {
      if (xhr.status === 400 || xhr.status === 401) {
        $('#ajaxTestResult').html('<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i> AJAX ตอบรับปกติ 🟢 (HTTP ' + xhr.status + ' - CORS Pass, Server Endpoint พร้อมใช้งาน)</span>');
      } else if (xhr.status === 404) {
        $.ajax({
          url: '<?= $baseUrl ?>/index.php/login/submit',
          type: 'POST',
          data: { username: '', password: '' },
          dataType: 'json',
          success: function(res) {
            $('#ajaxTestResult').html('<span class="text-warning text-dark"><i class="fa-solid fa-circle-exclamation me-1"></i> AJAX ผ่านทาง index.php 🟡 (แนะนำเปิด mod_rewrite หรือคัดลอกไฟล์ .htaccess ไปวางบน Server 240)</span>');
          },
          error: function(xhr2) {
            if (xhr2.status === 400 || xhr2.status === 401) {
              $('#ajaxTestResult').html('<span class="text-warning text-dark"><i class="fa-solid fa-circle-exclamation me-1"></i> AJAX ผ่านทาง index.php 🟡 (แนะนำเปิด mod_rewrite หรือคัดลอกไฟล์ .htaccess ไปวางบน Server 240)</span>');
            } else {
              $('#ajaxTestResult').html('<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> AJAX ล้มเหลว 🔴 (HTTP Status 404 - กรุณาตรวจสอบไฟล์ .htaccess บน Server 240)</span>');
            }
          }
        });
      } else {
        $('#ajaxTestResult').html('<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> AJAX ล้มเหลว 🔴 (HTTP Status: ' + xhr.status + ')</span>');
      }
    }
  });
});
</script>

</body>
</html>
