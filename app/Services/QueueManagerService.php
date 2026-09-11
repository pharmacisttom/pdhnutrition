<?php
namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class QueueManagerService {
    public static function syncTodayQueueTasks(): int {
        $pdo = Database::getConnection();
        $api = new PdhApiService();
        $visitsResponse = $api->getTodayVisits();
        $visits = $visitsResponse['data'] ?? [];

        if (empty($visits)) return 0;

        $strictOnly = \App\Services\SystemSettingService::get('strict_active_clinics_only', '0') === '1';
        $activeClinicNames = \App\Services\SystemSettingService::getActiveClinicNames();

        $taskCreated = 0;
        foreach ($visits as $visit) {
            $hn = $visit['hn'];
            $vn = $visit['vn'];
            $clinic = $visit['clinic'] ?? 'OPD';

            if ($strictOnly && !empty($activeClinicNames) && !in_array($clinic, $activeClinicNames)) {
                continue;
            }

            // Check if patient is in Nutrition Registry with active flag & review requirement
            $stmt = $pdo->prepare("
                SELECT * FROM nutrition_registry 
                WHERE hn = :hn AND active = 1 AND must_review_every_visit = 1
            ");
            $stmt->execute(['hn' => $hn]);
            $registry = $stmt->fetch();

            if ($registry) {
                // Check if task already exists
                $stmtTask = $pdo->prepare("
                    SELECT id FROM nutrition_tasks 
                    WHERE hn = :hn AND vn = :vn AND task_type = 'PRE_DOCTOR_ASSESSMENT'
                ");
                $stmtTask->execute(['hn' => $hn, 'vn' => $vn]);
                if (!$stmtTask->fetch()) {
                    // Create new Pre-doctor Queue task
                    $stmtIns = $pdo->prepare("
                        INSERT INTO nutrition_tasks 
                        (hn, vn, visit_date, clinic, doctor, task_type, risk_level, status, priority, created_at)
                        VALUES (:hn, :vn, :visit_date, :clinic, :doctor, 'PRE_DOCTOR_ASSESSMENT', :risk_level, 'WAITING_NUTRITION', :priority, NOW())
                    ");
                    $priority = match($registry['risk_level']) {
                        'SEVERE', 'HIGH' => 1,
                        'MEDIUM' => 2,
                        default => 3
                    };
                    $stmtIns->execute([
                        'hn' => $hn,
                        'vn' => $vn,
                        'visit_date' => $visit['visit_date'] ?? date('Y-m-d'),
                        'clinic' => $visit['clinic'] ?? 'OPD',
                        'doctor' => $visit['doctor'] ?? 'Unassigned',
                        'risk_level' => $registry['risk_level'],
                        'priority' => $priority
                    ]);
                    $taskCreated++;
                }
            }
        }
        return $taskCreated;
    }

    public static function lockTask(int $taskId, int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE nutrition_tasks 
            SET status = 'IN_ASSESSMENT', locked_by = :user_id, started_at = NOW() 
            WHERE id = :id AND (status = 'WAITING_NUTRITION' OR locked_by = :user_id)
        ");
        return $stmt->execute(['id' => $taskId, 'user_id' => $userId]);
    }

    public static function completeTask(int $taskId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE nutrition_tasks 
            SET status = 'NUTRITION_COMPLETED', completed_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $taskId]);
    }

    public static function getTodayQueueList(array $filters = []): array {
        self::syncTodayQueueTasks();
        $pdo = Database::getConnection();

        $sql = "
            SELECT t.*, p.fullname, p.age, p.gender, p.cid,
                   n.naf_grade as last_naf_grade, n.assessment_date as last_naf_date, n.total_score as last_naf_score,
                   r.reason as registry_reason,
                   u.fullname as locked_by_name
            FROM nutrition_tasks t
            JOIN patients_cache p ON t.hn = p.hn
            LEFT JOIN nutrition_registry r ON t.hn = r.hn
            LEFT JOIN users u ON t.locked_by = u.id
            LEFT JOIN (
                SELECT hn, naf_grade, assessment_date, total_score,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                FROM naf_assessments
            ) n ON t.hn = n.hn AND n.rn = 1
            WHERE t.visit_date = CURRENT_DATE()
        ";

        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['clinic'])) {
            $sql .= " AND t.clinic = :clinic";
            $params['clinic'] = $filters['clinic'];
        }

        $sql .= " ORDER BY t.priority ASC, t.created_at ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
