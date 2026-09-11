<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RbacMiddleware;
use App\Services\PdhApiService;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;
use PDO;

class ClinicalNoteController {
    public function create(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'DIETITIAN', 'DOCTOR']);

        $hn = SanitizerHelper::escape($_GET['hn'] ?? '');
        $vn = SanitizerHelper::escape($_GET['vn'] ?? '');
        $assessmentId = !empty($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : null;

        if (empty($hn)) {
            http_response_code(400);
            echo "<h1>400 Bad Request</h1><p>กรุณาระบุ HN ผู้ป่วย</p>";
            exit;
        }

        $api = new PdhApiService();
        $patientRes = $api->getPatient($hn);
        $patient = $patientRes['data'] ?? null;

        $labsRes = $api->getLatestLabs($hn);
        $labs = $labsRes['data'] ?? [];

        $pdo = Database::getConnection();
        $latestNaf = null;
        if ($assessmentId) {
            $stmtNaf = $pdo->prepare("SELECT * FROM naf_assessments WHERE id = :id");
            $stmtNaf->execute(['id' => $assessmentId]);
            $latestNaf = $stmtNaf->fetch();
        } else {
            $stmtNaf = $pdo->prepare("SELECT * FROM naf_assessments WHERE hn = :hn ORDER BY assessment_date DESC, id DESC LIMIT 1");
            $stmtNaf->execute(['hn' => $hn]);
            $latestNaf = $stmtNaf->fetch();
        }

        // Build default Objective string
        $objText = "Weight: " . ($latestNaf['weight_kg'] ?? $patient['weight'] ?? '-') . " kg, ";
        $objText .= "Height: " . ($latestNaf['height_cm'] ?? $patient['height'] ?? '-') . " cm, ";
        $objText .= "BMI: " . ($latestNaf['bmi'] ?? $patient['bmi'] ?? '-') . " kg/m²\n";
        $objText .= "Albumin: " . ($labs['albumin'] ?? '-') . " g/dL, ";
        $objText .= "TLC: " . ($labs['tlc'] ?? '-') . " cells/mm³\n";
        $objText .= "NAF Assessment: Score " . ($latestNaf['total_score'] ?? '-') . " (" . ($latestNaf['naf_grade'] ?? '-') . ")";

        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/notes/create.php';
    }

    public function store(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'DIETITIAN', 'DOCTOR']);
        CsrfMiddleware::verify();

        $input = SanitizerHelper::cleanInput($_POST);
        $hn = $input['hn'] ?? '';
        $vn = $input['vn'] ?? '';

        if (empty($hn)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณาระบุ HN ผู้ป่วย'], 400);
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO nutrition_notes 
            (assessment_id, hn, vn, subjective, objective, assessment_text, plan_text, dietitian_id, created_at)
            VALUES (:aid, :hn, :vn, :s, :o, :a, :p, :did, NOW())
        ");

        $stmt->execute([
            'aid' => !empty($input['assessment_id']) ? (int)$input['assessment_id'] : null,
            'hn'  => $hn,
            'vn'  => $vn,
            's'   => $input['subjective'] ?? '',
            'o'   => $input['objective'] ?? '',
            'a'   => $input['assessment_text'] ?? '',
            'p'   => $input['plan_text'] ?? '',
            'did' => (int)$_SESSION['user_id']
        ]);

        $noteId = (int)$pdo->lastInsertId();
        AuditService::log('CREATE_CLINICAL_NOTE', 'NOTES', (string)$noteId, $hn, $vn);

        ResponseHelper::json([
            'success' => true,
            'message' => 'บันทึก Clinical Note (SOAP) สำเร็จ',
            'redirect' => ($_ENV['APP_URL'] ?? '/pdhnutrition') . '/patient/' . $hn
        ]);
    }
}
