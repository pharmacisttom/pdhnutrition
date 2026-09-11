<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Services\QueueManagerService;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Middleware\CsrfMiddleware;

class NutritionQueueController {
    public function index(): void {
        AuthMiddleware::check();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'clinic' => $_GET['clinic'] ?? ''
        ];

        $queueTasks = QueueManagerService::getTodayQueueList($filters);
        AuditService::log('VIEW_PRE_DOCTOR_QUEUE', 'QUEUE');

        require __DIR__ . '/../../views/queue/index.php';
    }

    public function startAssessment(int $taskId): void {
        AuthMiddleware::check();
        CsrfMiddleware::verify();
        $userId = (int)$_SESSION['user_id'];

        $locked = QueueManagerService::lockTask($taskId, $userId);
        if ($locked) {
            AuditService::log('START_ASSESSMENT_TASK', 'QUEUE', (string)$taskId);
            ResponseHelper::json(['success' => true, 'message' => 'เริ่มการประเมินโภชนาการสำหรับคิวนี้แล้ว']);
        } else {
            ResponseHelper::json(['success' => false, 'message' => 'คิวนี้ถูกเจ้าหน้าที่ท่านอื่นประเมินอยู่'], 400);
        }
    }

    public function completeTask(int $taskId): void {
        AuthMiddleware::check();
        CsrfMiddleware::verify();

        $completed = QueueManagerService::completeTask($taskId);
        if ($completed) {
            AuditService::log('COMPLETE_ASSESSMENT_TASK', 'QUEUE', (string)$taskId);
            ResponseHelper::json(['success' => true, 'message' => 'เสร็จสิ้นการประเมินโภชนาการก่อนพบแพทย์ (READY FOR DOCTOR)']);
        } else {
            ResponseHelper::json(['success' => false, 'message' => 'ไม่สามารถอัปเดตสถานะคิวได้'], 400);
        }
    }
}
