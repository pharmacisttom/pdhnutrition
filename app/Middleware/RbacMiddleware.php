<?php
namespace App\Middleware;

use App\Helpers\ResponseHelper;

class RbacMiddleware {
    public static function authorize(array $allowedRoles): void {
        AuthMiddleware::check();

        $userRole = $_SESSION['user_role'] ?? 'VIEWER';
        if ($userRole === 'SUPER_ADMIN' || $userRole === 'ADMIN') {
            return; // Admins have global permission
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                ResponseHelper::json(['success' => false, 'message' => 'Unauthorized access. Insufficient permissions.'], 403);
            }
            http_response_code(403);
            echo "<h1>403 Forbidden</h1><p>คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้ (Role: " . htmlspecialchars($userRole) . ")</p>";
            exit;
        }
    }
}
