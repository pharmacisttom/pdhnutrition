<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Services\PdhApiService;
use App\Services\QueueManagerService;
use App\Helpers\ResponseHelper;
use PDO;

class DashboardController {
    public function index(): void {
        AuthMiddleware::check();

        // Sync queue tasks
        QueueManagerService::syncTodayQueueTasks();

        $pdo = Database::getConnection();
        $api = new PdhApiService();
        $todayVisits = $api->getTodayVisits();

        // Stat 1: Total OPD visits today
        $totalVisitsCount = count($todayVisits['data'] ?? []);

        // Stat 2: Pre-doctor Queue Tasks Stats Today
        $stmtTask = $pdo->query("
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'WAITING_NUTRITION' THEN 1 ELSE 0 END) as waiting_cnt,
                SUM(CASE WHEN status = 'IN_ASSESSMENT' THEN 1 ELSE 0 END) as assessing_cnt,
                SUM(CASE WHEN status IN ('NUTRITION_COMPLETED', 'READY_FOR_DOCTOR') THEN 1 ELSE 0 END) as completed_cnt,
                SUM(CASE WHEN risk_level IN ('HIGH', 'SEVERE') THEN 1 ELSE 0 END) as high_risk_cnt
            FROM nutrition_tasks
            WHERE visit_date = CURRENT_DATE()
        ");
        $taskStats = $stmtTask->fetch();

        // Stat 3: NAF Grade Distribution for Current Month
        $stmtNaf = $pdo->query("
            SELECT 
                SUM(CASE WHEN naf_grade = 'NAF A' THEN 1 ELSE 0 END) as naf_a,
                SUM(CASE WHEN naf_grade = 'NAF B' THEN 1 ELSE 0 END) as naf_b,
                SUM(CASE WHEN naf_grade = 'NAF C' THEN 1 ELSE 0 END) as naf_c
            FROM naf_assessments
            WHERE DATE_FORMAT(assessment_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
        ");
        $nafStats = $stmtNaf->fetch();

        // Stat 4: Overdue Follow-ups
        $stmtOverdue = $pdo->query("
            SELECT COUNT(*) 
            FROM nutrition_followups 
            WHERE next_follow_up_date < CURRENT_DATE() AND active = 1 AND status != 'COMPLETED'
        ");
        $overdueCount = (int)$stmtOverdue->fetchColumn();

        $stats = [
            'today_visits' => $totalVisitsCount,
            'queue_total' => (int)($taskStats['total_tasks'] ?? 0),
            'queue_waiting' => (int)($taskStats['waiting_cnt'] ?? 0),
            'queue_assessing' => (int)($taskStats['assessing_cnt'] ?? 0),
            'queue_completed' => (int)($taskStats['completed_cnt'] ?? 0),
            'high_risk' => (int)($taskStats['high_risk_cnt'] ?? 0),
            'naf_a' => (int)($nafStats['naf_a'] ?? 0),
            'naf_b' => (int)($nafStats['naf_b'] ?? 0),
            'naf_c' => (int)($nafStats['naf_c'] ?? 0),
            'overdue_count' => $overdueCount,
            'api_source' => $todayVisits['source'] ?? 'HIS',
            'api_last_sync' => $todayVisits['last_sync'] ?? date('Y-m-d H:i:s'),
            'api_warning' => $todayVisits['warning'] ?? null
        ];

        require __DIR__ . '/../../views/dashboard/index.php';
    }

    public function getAnalyticsData(): void {
        AuthMiddleware::check();
        $pdo = Database::getConnection();

        // Monthly NAF Trend (Last 6 Months)
        $stmtTrend = $pdo->query("
            SELECT DATE_FORMAT(assessment_date, '%Y-%m') as ym,
                   SUM(CASE WHEN naf_grade = 'NAF A' THEN 1 ELSE 0 END) as count_a,
                   SUM(CASE WHEN naf_grade = 'NAF B' THEN 1 ELSE 0 END) as count_b,
                   SUM(CASE WHEN naf_grade = 'NAF C' THEN 1 ELSE 0 END) as count_c
            FROM naf_assessments
            WHERE assessment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY ym
            ORDER BY ym ASC
        ");
        $trendData = $stmtTrend->fetchAll();

        // Clinic Breakdown
        $stmtClinic = $pdo->query("
            SELECT clinic, COUNT(*) as cnt 
            FROM nutrition_tasks 
            WHERE visit_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)
            GROUP BY clinic
        ");
        $clinicData = $stmtClinic->fetchAll();

        ResponseHelper::json([
            'success' => true,
            'trend' => $trendData,
            'clinics' => $clinicData
        ]);
    }
}
