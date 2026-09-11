<?php
namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class AuditService {
    public static function log(
        string $action,
        string $module,
        ?string $recordId = null,
        ?string $hn = null,
        ?string $vn = null,
        mixed $oldValue = null,
        mixed $newValue = null
    ): void {
        try {
            $pdo = Database::getConnection();
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? 'SYSTEM';
            $role = $_SESSION['user_role'] ?? 'SYSTEM';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown';

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs 
                (user_id, username, role, action, module, record_id, hn, vn, old_value, new_value, ip_address, user_agent, created_at)
                VALUES (:user_id, :username, :role, :action, :module, :record_id, :hn, :vn, :old_value, :new_value, :ip, :ua, NOW())
            ");

            $stmt->execute([
                'user_id'   => $userId,
                'username'  => $username,
                'role'      => $role,
                'action'    => $action,
                'module'    => $module,
                'record_id' => $recordId,
                'hn'        => $hn,
                'vn'        => $vn,
                'old_value' => is_array($oldValue) || is_object($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : (string)$oldValue,
                'new_value' => is_array($newValue) || is_object($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : (string)$newValue,
                'ip'        => $ip,
                'ua'        => substr($userAgent, 0, 255)
            ]);
        } catch (Exception $e) {
            // Fail safe - do not crash application if audit log insertion fails
            error_log("AuditService Error: " . $e->getMessage());
        }
    }
}
