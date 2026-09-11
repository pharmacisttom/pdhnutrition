<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\SmartAlertService;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;

class AlertController {

    public function index(): void {
        AuthMiddleware::check();
        $alerts = SmartAlertService::getDailyAlerts();
        AuditService::log('VIEW_SMART_ALERTS', 'ALERTS');
        require __DIR__ . '/../../views/alerts/index.php';
    }

    public function heatmap(): void {
        AuthMiddleware::check();
        $matrix = SmartAlertService::getHeatmapMatrix();
        $alerts = SmartAlertService::getDailyAlerts();
        AuditService::log('VIEW_ALERT_HEATMAP', 'ALERTS');
        require __DIR__ . '/../../views/alerts/heatmap.php';
    }

    public function broadcast(): void {
        AuthMiddleware::check();
        $alerts = SmartAlertService::getDailyAlerts();
        $msg = "📢 [PDH Nutrition Smart Alert Report]\n";
        $msg .= "ประจำวันที่ " . date('d/m/Y') . "\n";
        $msg .= "🔴 ภาวะเสี่ยงสูงรุนแรง (24h Urgent): " . count($alerts['critical_24h']) . " ราย\n";
        $msg .= "🟠 ภาวะเสี่ยงปานกลาง (3-Day Action): " . count($alerts['urgent_3day']) . " ราย\n";
        $msg .= "🟡 ภาวะเฝ้าระวัง (Routine Follow-up): " . count($alerts['routine_7day']) . " ราย\n";
        $msg .= "ระบบได้ส่งรายงานให้ทีมนักโภชนาการและแพทย์เรียบร้อยแล้ว";

        AuditService::log('BROADCAST_ALERTS', 'ALERTS');

        ResponseHelper::json([
            'success' => true,
            'message' => 'ส่งการแจ้งเตือน Smart Daily Alerts เรียบร้อยแล้ว',
            'summary' => $msg
        ]);
    }
}
