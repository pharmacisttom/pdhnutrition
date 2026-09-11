<?php
namespace App\Middleware;

use App\Helpers\ResponseHelper;

class AuthMiddleware {
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                ResponseHelper::json(['success' => false, 'message' => 'Session expired. Please login again.'], 401);
            }
            $baseUrl = $_ENV['APP_URL'] ?? '/pdhnutrition';
            ResponseHelper::redirect($baseUrl . '/login');
        }
    }
}
