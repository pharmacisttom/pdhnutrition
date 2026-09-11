<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RbacMiddleware;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;
use PDO;

class RegistryController {
    public function index(): void {
        AuthMiddleware::check();
        $pdo = Database::getConnection();

        $stmt = $pdo->query("
            SELECT r.*, p.fullname, p.age, p.gender, p.cid, u.fullname as creator_name,
                   n.naf_grade as last_naf_grade, n.assessment_date as last_naf_date
            FROM nutrition_registry r
            JOIN patients_cache p ON r.hn = p.hn
            LEFT JOIN users u ON r.created_by = u.id
            LEFT JOIN (
                SELECT hn, naf_grade, assessment_date,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                FROM naf_assessments
            ) n ON r.hn = n.hn AND n.rn = 1
            ORDER BY r.active DESC, r.risk_level DESC, r.updated_at DESC
        ");
        $registryList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        AuditService::log('VIEW_REGISTRY', 'REGISTRY');
        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/registry/index.php';
    }

    public function store(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'DIETITIAN', 'DOCTOR']);
        CsrfMiddleware::verify();

        $input = SanitizerHelper::cleanInput($_POST);
        $hn = $input['hn'] ?? '';

        if (empty($hn)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณาระบุ HN ผู้ป่วย'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO nutrition_registry 
            (hn, active, reason, risk_level, start_date, follow_up_interval_days, must_review_every_visit, note, created_by, created_at)
            VALUES (:hn, 1, :reason, :risk, CURRENT_DATE(), :interval, :review, :note, :cby, NOW())
            ON DUPLICATE KEY UPDATE 
                active = 1, reason = :reason, risk_level = :risk, follow_up_interval_days = :interval,
                must_review_every_visit = :review, note = :note, updated_at = NOW()
        ");

        $stmt->execute([
            'hn'       => $hn,
            'reason'   => $input['reason'] ?? 'Manual enrollment',
            'risk'     => $input['risk_level'] ?? 'MEDIUM',
            'interval' => (int)($input['follow_up_interval_days'] ?? 14),
            'review'   => isset($input['must_review_every_visit']) ? 1 : 0,
            'note'     => $input['note'] ?? '',
            'cby'      => (int)$_SESSION['user_id']
        ]);

        AuditService::log('ENROLL_REGISTRY', 'REGISTRY', null, $hn);

        ResponseHelper::json(['success' => true, 'message' => 'เพิ่มผู้ป่วยเข้าใน Nutrition Registry สำเร็จ']);
    }

    public function toggleStatus(int $id): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'DIETITIAN']);
        CsrfMiddleware::verify();

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE nutrition_registry SET active = NOT active, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);

        AuditService::log('TOGGLE_REGISTRY_STATUS', 'REGISTRY', (string)$id);

        ResponseHelper::json(['success' => true, 'message' => 'อัปเดตสถานะ Registry สำเร็จ']);
    }
}
