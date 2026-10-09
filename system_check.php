<?php
/** Standalone, read-only deployment check. No patient records are queried. */
ini_set('display_errors', '0');
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');
$checks = [];
function checkResult($name, $status, $detail) {
    global $checks;
    $checks[] = ['name' => $name, 'status' => $status, 'detail' => $detail];
}
function esc($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
checkResult('PHP 8.0 ขึ้นไป', version_compare(PHP_VERSION, '8.0', '>=' ) ? 'pass' : 'fail', 'เวอร์ชันที่ทำงาน: ' . PHP_VERSION);
foreach (['pdo', 'pdo_mysql', 'curl', 'session', 'json'] as $extension) {
    checkResult('PHP extension: ' . $extension, extension_loaded($extension) ? 'pass' : 'fail', extension_loaded($extension) ? 'พร้อมใช้งาน' : 'ไม่พบส่วนขยายนี้');
}
$required = [
    'index.php', 'config.php', '.htaccess', 'app/Config/AppConfig.php',
    'app/Config/Database.php', 'app/Controllers/DashboardController.php',
    'app/Controllers/AuthController.php', 'views/dashboard/index.php',
    'views/auth/login.php', 'views/layouts/header.php', 'views/layouts/footer.php',
    'views/layouts/navbar.php', 'views/layouts/sidebar.php',
    'public/assets/css/custom.css', 'public/assets/css/print.css',
    'public/assets/js/naf-calculator.js', 'public/assets/js/diet-calculator.js',
    'public/assets/img/logo.png', 'database/pdhnutrition_full_install.sql'
];
foreach ($required as $file) {
    $exists = is_file(__DIR__ . '/' . $file) && is_readable(__DIR__ . '/' . $file);
    checkResult('ไฟล์: ' . $file, $exists ? 'pass' : 'fail', $exists ? 'พบและอ่านได้' : 'ไม่พบไฟล์หรือไม่มีสิทธิ์อ่าน');
}
checkResult('ไฟล์ .env', is_readable(__DIR__ . '/.env') ? 'pass' : 'warn', is_readable(__DIR__ . '/.env') ? 'พบไฟล์ (ไม่แสดงค่าภายใน)' : 'ไม่พบหรืออ่านไม่ได้ ตรวจว่าค่าที่ต้องใช้กำหนดไว้ใน config.php แล้ว');
checkResult('โฟลเดอร์ storage', is_dir(__DIR__ . '/storage') && is_writable(__DIR__ . '/storage') ? 'pass' : 'warn', is_dir(__DIR__ . '/storage') ? (is_writable(__DIR__ . '/storage') ? 'มีโฟลเดอร์และเขียนได้' : 'มีโฟลเดอร์แต่เขียนไม่ได้') : 'ไม่พบโฟลเดอร์ (จำเป็นหากใช้ SQLite หรือจัดเก็บไฟล์ที่นี่)');
if (function_exists('apache_get_modules')) {
    $rewrite = in_array('mod_rewrite', apache_get_modules(), true);
    checkResult('Apache mod_rewrite', $rewrite ? 'pass' : 'warn', $rewrite ? 'โหลดโมดูลแล้ว แต่ต้องทดสอบ URL ว่าอ่าน .htaccess จริงหรือไม่' : 'ไม่พบโมดูล ตรวจผลทดสอบ URL และ fallback');
} else {
    checkResult('Apache mod_rewrite', 'warn', 'PHP รูปแบบนี้อ่านรายการโมดูลไม่ได้ ใช้ผลทดสอบ URL ด้านล่างแทน');
}
$rewriteText = is_readable(__DIR__ . '/.htaccess') ? file_get_contents(__DIR__ . '/.htaccess') : '';
checkResult('กฎส่งเส้นทางไป index.php', preg_match('/^\s*RewriteRule\s+[^\r\n]*index\.php/m', $rewriteText) ? 'pass' : 'fail', 'ตรวจเฉพาะว่ามีกฎในไฟล์ ไม่ยืนยันว่า Apache อนุญาตหรือใช้งานกฎนี้');
// Connect only to the configured MySQL database; no fallback, migrations or writes.
if (version_compare(PHP_VERSION, '8.0', '>=') && is_readable(__DIR__ . '/app/Config/AppConfig.php') && is_readable(__DIR__ . '/config.php')) {
    try {
        require_once __DIR__ . '/config.php';
        require_once __DIR__ . '/app/Config/AppConfig.php';
        \App\Config\AppConfig::load();
        $driver = \App\Config\AppConfig::get('DB_DRIVER', 'mysql');
        if ($driver !== 'mysql') {
            checkResult('ฐานข้อมูล', 'warn', 'ไฟล์นี้ตรวจการเชื่อมต่อเฉพาะ MySQL เพื่อหลีกเลี่ยงการสร้างไฟล์ฐานข้อมูลใหม่');
        } elseif (!extension_loaded('pdo_mysql')) {
            checkResult('ฐานข้อมูล', 'fail', 'ตรวจไม่ได้เพราะไม่มี pdo_mysql');
        } else {
            $host = defined('DB_HOST') ? DB_HOST : \App\Config\AppConfig::get('DB_HOST', 'localhost');
            $port = defined('DB_PORT') ? DB_PORT : \App\Config\AppConfig::get('DB_PORT', '3306');
            $dbName = defined('DB_NAME') ? DB_NAME : \App\Config\AppConfig::get('DB_DATABASE', '');
            $user = defined('DB_USER') ? DB_USER : \App\Config\AppConfig::get('DB_USERNAME', '');
            $password = defined('DB_PASS') ? DB_PASS : \App\Config\AppConfig::get('DB_PASSWORD', '');
            $db = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $password, [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $db->query('SELECT 1');
            checkResult('ฐานข้อมูล MySQL', 'pass', 'เชื่อมต่อด้วยค่าหลักที่กำหนดไว้สำเร็จ (ไม่ใช้บัญชีสำรอง)');
            $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $schemaFile = __DIR__ . '/database/pdhnutrition_full_install.sql';
            if (is_readable($schemaFile)) {
                preg_match_all('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`([^`]+)`/i', file_get_contents($schemaFile), $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $table) {
                        checkResult('ตาราง: ' . $table, in_array($table, $tables, true) ? 'pass' : 'fail', in_array($table, $tables, true) ? 'พบตาราง' : 'ไม่พบตาราง ตรวจการนำเข้า SQL');
                    }
                } else {
                    checkResult('โครงสร้างตาราง', 'warn', 'อ่านรายชื่อตารางจากไฟล์ SQL ไม่ได้');
                }
            }
            checkResult('ขอบเขตฐานข้อมูล', 'warn', 'ตรวจเฉพาะการเชื่อมต่อและชื่อตาราง ยังไม่ตรวจคอลัมน์ ข้อมูลตั้งต้น หรือข้อมูลผู้ป่วย');
        }
    } catch (Throwable $error) {
        $code = (string) $error->getCode();
        $details = ['1045' => 'บัญชีหรือรหัสผ่านฐานข้อมูลไม่ถูกต้อง หรือไม่มีสิทธิ์เชื่อมต่อ', '1049' => 'ไม่พบฐานข้อมูลที่กำหนดไว้', '2002' => 'ติดต่อ MySQL ไม่ได้ ตรวจบริการ ที่อยู่ และพอร์ต'];
        checkResult('การตั้งค่า / ฐานข้อมูล', 'fail', $details[$code] ?? 'ตรวจไม่สำเร็จ ตรวจค่าเชื่อมต่อและสิทธิ์ผู้ใช้จากบันทึกของเซิร์ฟเวอร์ (ไม่แสดงข้อความที่อาจมีข้อมูลลับ)');
    }
} else {
    checkResult('ฐานข้อมูล', 'warn', 'ข้ามการตรวจ เนื่องจากเวอร์ชัน PHP หรือไฟล์ตั้งค่าไม่พร้อม');
}
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/pdhnutrition/system_check.php')), '/');
?>
<!doctype html>
<html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ตรวจสอบการติดตั้ง PDH Nutrition</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f4f7fb;color:#203348;font:16px/1.65 system-ui,sans-serif}main{max-width:1000px;margin:24px auto;padding:16px}h1{font-size:1.6rem}section{background:white;border:1px solid #dce4ed;border-radius:12px;padding:20px;margin:16px 0}.row{display:grid;grid-template-columns:80px minmax(0,1fr);gap:12px;padding:12px 0;border-bottom:1px solid #edf0f4}.row:last-child{border:0}.pass{color:#11643b}.fail{color:#b42318}.warn{color:#805600}small{display:block;color:#526174;overflow-wrap:anywhere}button{padding:12px 18px;background:#0d3b66;color:white;border:0;border-radius:8px;font:inherit;cursor:pointer}button:disabled{opacity:.6}pre{white-space:pre-wrap;overflow-wrap:anywhere}p{overflow-wrap:anywhere}@media(max-width:600px){main{margin:0;padding:12px}section{padding:14px}.row{grid-template-columns:65px minmax(0,1fr)}}
</style></head><body><main>
<h1>ตรวจสอบการติดตั้ง PDH Nutrition</h1>
<p>ตรวจแบบอ่านอย่างเดียว ไม่แก้ค่าระบบ ไม่เขียนฐานข้อมูล และไม่แสดงรหัสผ่านหรือข้อมูลผู้ป่วย</p>
<section id="server"><h2>ไฟล์ PHP และฐานข้อมูล</h2>
<?php foreach ($checks as $check): ?>
<div class="row"><strong class="<?= esc($check['status']) ?>"><?= ['pass'=>'ผ่าน','fail'=>'ไม่ผ่าน','warn'=>'ตรวจเพิ่ม'][$check['status']] ?></strong><div><?= esc($check['name']) ?><small><?= esc($check['detail']) ?></small></div></div>
<?php endforeach; ?>
</section>
<section><h2>ทดสอบเส้นทาง URL</h2><p>กดเพื่อทดสอบไฟล์จริงและเส้นทางเสมือนจากเซิร์ฟเวอร์เดียวกัน โดยไม่ส่งแบบฟอร์มหรือแก้ข้อมูล</p><button id="run" type="button">ทดสอบ URL</button><div id="routes" aria-live="polite"></div><p id="conclusion"></p></section>
<section><h2>ขอบเขตการตรวจ</h2><p>ไฟล์นี้ใช้หาส่วนที่ขาดในการติดตั้ง ไม่ได้ทดสอบทุกกระบวนการ เช่น การบันทึก NAF การสั่งอาหาร สิทธิ์แต่ละบทบาท หรือ HIS API และไม่สามารถอ่านค่า AllowOverride ของ Apache ได้โดยตรง</p><p>ลบไฟล์ทดสอบออกจากเซิร์ฟเวอร์เมื่อใช้งานเสร็จ</p><button id="copy" type="button">คัดลอกผลตรวจ</button><pre id="copyStatus" aria-live="polite"></pre></section>
<script>
const base = <?= json_encode($basePath, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const initial = <?= json_encode($checks, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let routeResults = [];
document.getElementById('run').addEventListener('click', async function() {
  this.disabled = true;
  routeResults = [];
  const output = document.getElementById('routes'); output.replaceChildren();
  const results = {};
  for (const path of ['/index.php', '/public/assets/css/custom.css', '/index.php?route=/login', '/index.php?route=/dashboard']) {
    let status = 'fail', detail = '';
    try {
      const response = await fetch(base + path, {cache:'no-store', credentials:'same-origin', signal:AbortSignal.timeout(10000)});
      const body = await response.text();
      const appPage = /<title>[^<]*(?:PDH Nutrition|เข้าสู่ระบบ)/i.test(body);
      const valid = path.endsWith('.css') ? response.ok && /--pdh-blue\s*:/.test(body) : response.ok && appPage;
      status = valid ? 'pass' : 'fail';
      detail = 'HTTP ' + response.status + (valid ? ' — ได้เนื้อหาที่คาดไว้' : ' — ไม่ได้หน้าระบบที่คาดไว้');
      results[path] = {ok: valid, code:response.status};
    } catch (error) { detail = 'ติดต่อไม่ได้หรือเกินเวลา 10 วินาที'; results[path] = {ok:false,code:0}; }
    routeResults.push({name:path,status,detail});
    const row = document.createElement('div'); row.className = 'row';
    const badge = document.createElement('strong'); badge.className = status; badge.textContent = status === 'pass' ? 'ผ่าน' : 'ไม่ผ่าน';
    const text = document.createElement('div'); text.textContent = base + path;
    const small = document.createElement('small'); small.textContent = detail; text.append(small); row.append(badge,text); output.append(row);
  }
  const conclusion = document.getElementById('conclusion');
  if (results['/index.php'].ok && (!results['/index.php?route=/login'].ok || !results['/index.php?route=/dashboard'].ok)) {
    conclusion.textContent = 'index.php เปิดได้ แต่เส้นทางแบบ query ยังไม่ผ่าน ตรวจว่าได้อัปโหลด index.php และไฟล์แอปเวอร์ชันที่รองรับ route ครบแล้ว';
  } else if (results['/index.php?route=/login'].ok && results['/index.php?route=/dashboard'].ok) {
    conclusion.textContent = 'เส้นทางระบบตอบกลับได้ หากยังไม่ได้เข้าสู่ระบบ ผลนี้อาจเป็นหน้าเข้าสู่ระบบ ยังไม่ยืนยันการแสดงข้อมูล Dashboard หลังเข้าสู่ระบบ';
  } else { conclusion.textContent = 'ยังพบเส้นทางที่ไม่ผ่าน ใช้ผล HTTP ร่วมกับรายการไฟล์และฐานข้อมูลด้านบนเพื่อหาสาเหตุ'; }
  this.disabled = false;
});
document.getElementById('copy').addEventListener('click', async function() {
  const report = initial.concat(routeResults).map(r => '[' + r.status + '] ' + r.name + ': ' + r.detail).join('\n') + '\n' + document.getElementById('conclusion').textContent;
  try { await navigator.clipboard.writeText(report); document.getElementById('copyStatus').textContent = 'คัดลอกแล้ว'; }
  catch (error) { document.getElementById('copyStatus').textContent = report; }
});
</script></main></body></html>
