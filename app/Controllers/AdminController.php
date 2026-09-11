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
