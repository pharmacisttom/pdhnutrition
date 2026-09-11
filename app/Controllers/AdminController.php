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
}
