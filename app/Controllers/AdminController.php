<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\RbacMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;
use PDO;

class AdminController {
    public function users(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/admin/users.php';
    }

    public function createUser(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $username = $data['username'] ?? '';
        $fullname = $data['fullname'] ?? '';
        $password = $data['password'] ?? '';
        $role     = $data['role'] ?? 'DIETITIAN';

        if (empty($username) || empty($fullname) || empty($password)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], 400);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password_hash, fullname, email, role, status, created_at)
            VALUES (:usr, :hash, :name, :email, :role, 'ACTIVE', NOW())
        ");
        $stmt->execute([
            'usr'   => $username,
            'hash'  => $hash,
            'name'  => $fullname,
            'email' => $data['email'] ?? '',
            'role'  => $role
        ]);

        AuditService::log('CREATE_USER', 'ADMIN', (string)$pdo->lastInsertId(), null, null, null, ['username' => $username, 'role' => $role]);

        ResponseHelper::json(['success' => true, 'message' => 'เพิ่มผู้ใช้งานใหม่สำเร็จ']);
    }

    public function updateUser(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $id       = (int)($data['id'] ?? 0);
        $fullname = $data['fullname'] ?? '';
        $email    = $data['email'] ?? '';
        $role     = $data['role'] ?? 'DIETITIAN';
        $status   = $data['status'] ?? 'ACTIVE';

        if ($id <= 0 || empty($fullname)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE users 
            SET fullname = :name, email = :email, role = :role, status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            'name'   => $fullname,
            'email'  => $email,
            'role'   => $role,
            'status' => $status,
            'id'     => $id
        ]);

        AuditService::log('UPDATE_USER', 'ADMIN', (string)$id, null, null, null, ['fullname' => $fullname, 'role' => $role, 'status' => $status]);

        ResponseHelper::json(['success' => true, 'message' => 'แก้ไขข้อมูลผู้ใช้งานสำเร็จ']);
    }

    public function resetUserPassword(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $id       = (int)($data['id'] ?? 0);
        $password = $data['password'] ?? '';

        if ($id <= 0 || empty($password)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณาระบุรหัสผ่านใหม่'], 400);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $stmt->execute(['hash' => $hash, 'id' => $id]);

        AuditService::log('RESET_USER_PASSWORD', 'ADMIN', (string)$id);

        ResponseHelper::json(['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ']);
    }

    public function toggleUserStatus(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            ResponseHelper::json(['success' => false, 'message' => 'ID ไม่ถูกต้อง'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            ResponseHelper::json(['success' => false, 'message' => 'ไม่พบผู้ใช้งาน'], 404);
        }

        $newStatus = ($user['status'] === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        $updateStmt = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        $updateStmt->execute(['status' => $newStatus, 'id' => $id]);

        AuditService::log('TOGGLE_USER_STATUS', 'ADMIN', (string)$id, null, null, null, ['new_status' => $newStatus]);

        ResponseHelper::json(['success' => true, 'message' => "เปลี่ยนสถานะเป็น {$newStatus} สำเร็จ", 'new_status' => $newStatus]);
    }

    public function deleteUser(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            ResponseHelper::json(['success' => false, 'message' => 'ID ไม่ถูกต้อง'], 400);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            ResponseHelper::json(['success' => false, 'message' => 'ไม่สามารถลบบัญชีผู้ใช้ที่กำลังเข้าสู่ระบบอยู่ได้'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);

        AuditService::log('DELETE_USER', 'ADMIN', (string)$id);

        ResponseHelper::json(['success' => true, 'message' => 'ลบผู้ใช้งานสำเร็จ']);
    }

    public function createRule(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $section       = (int)($data['section'] ?? 1);
        $item_code     = $data['item_code'] ?? '';
        $label_th      = $data['label_th'] ?? '';
        $label_en      = $data['label_en'] ?? '';
        $score         = (int)($data['score'] ?? 0);
        $condition_type= $data['condition_type'] ?? 'FLAG';
        $operator      = $data['operator'] ?? '=';
        $version       = (int)($data['version'] ?? 1);

        if (empty($item_code) || empty($label_th)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณากรอก Item Code และ คำอธิบาย'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO naf_rules (section, item_code, label_th, label_en, score, condition_type, operator, version, active)
            VALUES (:sec, :code, :th, :en, :score, :ctype, :op, :ver, 1)
        ");
        $stmt->execute([
            'sec'   => $section,
            'code'  => $item_code,
            'th'    => $label_th,
            'en'    => $label_en,
            'score' => $score,
            'ctype' => $condition_type,
            'op'    => $operator,
            'ver'   => $version
        ]);

        AuditService::log('CREATE_NAF_RULE', 'ADMIN', (string)$pdo->lastInsertId(), null, null, null, ['item_code' => $item_code, 'score' => $score]);

        ResponseHelper::json(['success' => true, 'message' => 'เพิ่มกฎ NAF Rule สำเร็จ']);
    }

    public function updateRule(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $id            = (int)($data['id'] ?? 0);
        $label_th      = $data['label_th'] ?? '';
        $label_en      = $data['label_en'] ?? '';
        $score         = (int)($data['score'] ?? 0);
        $condition_type= $data['condition_type'] ?? 'FLAG';
        $operator      = $data['operator'] ?? '=';

        if ($id <= 0 || empty($label_th)) {
            ResponseHelper::json(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE naf_rules
            SET label_th = :th, label_en = :en, score = :score, condition_type = :ctype, operator = :op
            WHERE id = :id
        ");
        $stmt->execute([
            'th'    => $label_th,
            'en'    => $label_en,
            'score' => $score,
            'ctype' => $condition_type,
            'op'    => $operator,
            'id'    => $id
        ]);

        AuditService::log('UPDATE_NAF_RULE', 'ADMIN', (string)$id, null, null, null, ['label_th' => $label_th, 'score' => $score]);

        ResponseHelper::json(['success' => true, 'message' => 'แก้ไข NAF Rule สำเร็จ']);
    }

    public function toggleRuleStatus(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $data = SanitizerHelper::cleanInput($_POST);
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            ResponseHelper::json(['success' => false, 'message' => 'ID ไม่ถูกต้อง'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT active FROM naf_rules WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $rule = $stmt->fetch();

        if (!$rule) {
            ResponseHelper::json(['success' => false, 'message' => 'ไม่พบกฎ NAF Rule'], 404);
        }

        $newStatus = $rule['active'] ? 0 : 1;
        $updateStmt = $pdo->prepare("UPDATE naf_rules SET active = :active WHERE id = :id");
        $updateStmt->execute(['active' => $newStatus, 'id' => $id]);

        AuditService::log('TOGGLE_NAF_RULE_STATUS', 'ADMIN', (string)$id, null, null, null, ['new_active' => $newStatus]);

        ResponseHelper::json(['success' => true, 'message' => "เปลี่ยนสถานะกฎเรียบร้อยแล้ว", 'new_active' => $newStatus]);
    }

    public function rules(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM naf_rules ORDER BY version DESC, section ASC, id ASC");
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/admin/rules.php';
    }

    public function auditLog(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 200");
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../views/admin/audit_log.php';
    }

    public function settings(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        $settings = \App\Services\SystemSettingService::getAll();
        $clinics = \App\Services\SystemSettingService::getClinics();
        $csrfToken = CsrfMiddleware::generateToken();

        require __DIR__ . '/../../views/admin/settings.php';
    }

    public function saveSettings(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        CsrfMiddleware::verify();

        $input = SanitizerHelper::cleanInput($_POST);

        $settingsMap = [
            'hospital_name_th'              => $input['hospital_name_th'] ?? 'โรงพยาบาลปลวกแดง',
            'hospital_name_en'              => $input['hospital_name_en'] ?? 'Pluakdaeng Hospital',
            'hospital_code'                 => $input['hospital_code'] ?? '11467',
            'department_name'               => $input['department_name'] ?? 'กลุ่มงานโภชนวิทยา',
            'his_driver'                    => $input['his_driver'] ?? 'himpro',
            'his_api_url'                   => $input['his_api_url'] ?? 'http://192.168.111.240/pdhapi',
            'his_api_key'                   => $input['his_api_key'] ?? 'PDHAPI-CHANGE-THIS-KEY',
            'his_timeout'                   => (int)($input['his_timeout'] ?? 10),
            'followup_default_interval_days'=> (int)($input['followup_default_interval_days'] ?? 14),
            'auto_create_queue_task'        => isset($input['auto_create_queue_task']) ? '1' : '0',
            'high_risk_alert_threshold'     => (int)($input['high_risk_alert_threshold'] ?? 8),
            'strict_active_clinics_only'   => isset($input['strict_active_clinics_only']) ? '1' : '0'
        ];

        // Process Clinics JSON if submitted
        if (isset($_POST['clinics_json'])) {
            $rawClinics = json_decode($_POST['clinics_json'], true);
            if (is_array($rawClinics)) {
                $settingsMap['active_clinics_json'] = json_encode($rawClinics, JSON_UNESCAPED_UNICODE);
            }
        }

        \App\Services\SystemSettingService::setMany($settingsMap);
        AuditService::log('UPDATE_SETTINGS', 'ADMIN', 'SYSTEM', null, null, null, ['updated_keys' => array_keys($settingsMap)]);

        ResponseHelper::json(['success' => true, 'message' => 'บันทึกการตั้งค่าระบบและคลินิกสำเร็จ']);
    }

    public function clinics(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN']);
        $clinics = \App\Services\SystemSettingService::getClinics();
        $csrfToken = CsrfMiddleware::generateToken();

        require __DIR__ . '/../../views/admin/clinics.php';
    }
}
