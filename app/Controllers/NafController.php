<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RbacMiddleware;
use App\Services\PdhApiService;
use App\Services\NafScoringService;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;
use PDO;
use Exception;

class NafController {
    public function create(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'DIETITIAN', 'DOCTOR', 'NURSE']);

        $hn = SanitizerHelper::escape($_GET['hn'] ?? '');
        $vn = SanitizerHelper::escape($_GET['vn'] ?? '');
        $an = SanitizerHelper::escape($_GET['an'] ?? '');

        if (empty($hn)) {
            http_response_code(400);
            echo "<h1>400 Bad Request</h1><p>กรุณาระบุ HN ผู้ป่วย</p>";
            exit;
        }

        $api = new PdhApiService();
        $patientRes = $api->getPatient($hn);
        $patient = $patientRes['data'] ?? null;

        $labsRes = $api->getLatestLabs($hn);
        $latestLabs = $labsRes['data'] ?? [];

        $diagRes = $api->getDiagnosis($vn ?: $hn);
        $diagnoses = $diagRes['data'] ?? [];

        // Fetch Rule List
        $pdo = Database::getConnection();
        $stmtRules = $pdo->query("SELECT * FROM naf_rules WHERE active = 1 AND version = 1 ORDER BY section ASC, id ASC");
        $rules = $stmtRules->fetchAll(PDO::FETCH_ASSOC);

        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/naf/create.php';
    }

    public function store(): void {
        RbacMiddleware::authorize(['SUPER_ADMIN', 'ADMIN', 'DIETITIAN', 'DOCTOR', 'NURSE']);
        CsrfMiddleware::verify();

        $input = SanitizerHelper::cleanInput($_POST);
        $hn = $input['hn'] ?? '';
        $vn = $input['vn'] ?? '';
        $an = $input['an'] ?? '';

        if (empty($hn)) {
            ResponseHelper::json(['success' => false, 'message' => 'กรุณาระบุ HN ผู้ป่วย'], 400);
        }

        $evaluation = NafScoringService::evaluateFullAssessment($input);

        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            $assessorId = (int)$_SESSION['user_id'];
            $assessmentDate = date('Y-m-d');
            $assessmentTime = date('H:i:s');

            // 1. Insert NAF Assessment Record
            $stmtIns = $pdo->prepare("
                INSERT INTO naf_assessments 
                (hn, vn, an, assessment_date, assessment_time, height_cm, height_source, arm_span_cm,
                 weight_kg, weight_method, bmi, bmi_score, albumin, albumin_score, wbc, lymphocyte, tlc, tlc_score,
                 total_score, naf_grade, rule_version, assessor_id, status, created_at)
                VALUES 
                (:hn, :vn, :an, :adate, :atime, :h, :hsrc, :arm,
                 :w, :wm, :bmi, :bmis, :alb, :albs, :wbc, :lym, :tlc, :tlcs,
                 :score, :grade, :ver, :assessor, 'COMPLETED', NOW())
            ");

            $stmtIns->execute([
                'hn'       => $hn,
                'vn'       => $vn,
                'an'       => $an,
                'adate'    => $assessmentDate,
                'atime'    => $assessmentTime,
                'h'        => $evaluation['height_cm'],
                'hsrc'     => $input['height_source'] ?? 'HIS',
                'arm'      => $evaluation['arm_span_cm'],
                'w'        => $evaluation['weight_kg'],
                'wm'       => $input['weight_method'] ?? 'Standing',
                'bmi'      => $evaluation['bmi'],
                'bmis'     => $evaluation['bmi_score'],
                'alb'      => $evaluation['albumin'],
                'albs'     => $evaluation['albumin_score'],
                'wbc'      => $evaluation['wbc'],
                'lym'      => $evaluation['lymphocyte'],
                'tlc'      => $evaluation['tlc'],
                'tlcs'     => $evaluation['tlc_score'],
                'score'    => $evaluation['total_score'],
                'grade'    => $evaluation['naf_grade'],
                'ver'      => $evaluation['rule_version'],
                'assessor' => $assessorId
            ]);

            $assessmentId = (int)$pdo->lastInsertId();

            // 2. Insert NAF Answers
            if (!empty($evaluation['answers'])) {
                $stmtAns = $pdo->prepare("
                    INSERT INTO naf_answers (assessment_id, section_number, item_code, score_given, user_confirmed_diagnosis)
                    VALUES (:aid, :sec, :code, :score, :conf)
                ");
                foreach ($evaluation['answers'] as $ans) {
                    $stmtAns->execute([
                        'aid'   => $assessmentId,
                        'sec'   => $ans['section_number'],
                        'code'  => $ans['item_code'],
                        'score' => $ans['score_given'],
                        'conf'  => $ans['user_confirmed']
                    ]);
                }
            }

            // 3. Update Anthropometric Record History
            if (!empty($evaluation['weight_kg'])) {
                $stmtAnthro = $pdo->prepare("
                    INSERT INTO anthropometric_records (hn, vn, record_date, weight_kg, height_cm, bmi, arm_span_cm, source, created_at)
                    VALUES (:hn, :vn, :rdate, :w, :h, :bmi, :arm, 'NAF_ASSESSMENT', NOW())
                ");
                $stmtAnthro->execute([
                    'hn'    => $hn,
                    'vn'    => $vn,
                    'rdate' => $assessmentDate,
                    'w'     => $evaluation['weight_kg'],
                    'h'     => $evaluation['effective_height_cm'],
                    'bmi'   => $evaluation['bmi'],
                    'arm'   => $evaluation['arm_span_cm']
                ]);
            }

            // 4. Auto Registry Enrollment for NAF B and NAF C
            if ($evaluation['naf_grade'] === 'NAF B' || $evaluation['naf_grade'] === 'NAF C') {
                $riskLevel = $evaluation['naf_grade'] === 'NAF C' ? 'SEVERE' : 'HIGH';
                $stmtReg = $pdo->prepare("
                    INSERT INTO nutrition_registry 
                    (hn, active, reason, risk_level, start_date, follow_up_interval_days, must_review_every_visit, created_by, created_at)
                    VALUES (:hn, 1, :reason, :risk, :sdate, 14, 1, :cby, NOW())
                    ON DUPLICATE KEY UPDATE 
                        active = 1, risk_level = :risk, updated_at = NOW()
                ");
                $stmtReg->execute([
                    'hn'     => $hn,
                    'reason' => 'Auto-enrolled from NAF Grade ' . $evaluation['naf_grade'],
                    'risk'   => $riskLevel,
                    'sdate'  => $assessmentDate,
                    'cby'    => $assessorId
                ]);
            }

            // 5. Update Task Status if this was triggered from Queue
            if (!empty($vn)) {
                $stmtTask = $pdo->prepare("
                    UPDATE nutrition_tasks 
                    SET status = 'NUTRITION_COMPLETED', completed_at = NOW() 
                    WHERE hn = :hn AND vn = :vn
                ");
                $stmtTask->execute(['hn' => $hn, 'vn' => $vn]);
            }

            // 6. Audit Log
            AuditService::log('CREATE_NAF', 'NAF', (string)$assessmentId, $hn, $vn, null, $evaluation);

            $pdo->commit();

            ResponseHelper::json([
                'success' => true,
                'message' => 'บันทึกแบบประเมิน NAF สำเร็จ (ผลลัพธ์: ' . $evaluation['naf_grade'] . ' Score: ' . $evaluation['total_score'] . ')',
                'assessment_id' => $assessmentId,
                'naf_grade' => $evaluation['naf_grade'],
                'total_score' => $evaluation['total_score'],
                'redirect' => ($_ENV['APP_URL'] ?? '/pdhnutrition') . '/naf/show/' . $assessmentId
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            ResponseHelper::json(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage()], 500);
        }
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, p.fullname, p.age, p.gender, p.cid, u.fullname as assessor_name
            FROM naf_assessments a
            JOIN patients_cache p ON a.hn = p.hn
            JOIN users u ON a.assessor_id = u.id
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $assessment = $stmt->fetch();

        if (!$assessment) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>ไม่พบรายการประเมิน NAF ID: " . $id . "</p>";
            exit;
        }

        $stmtAnswers = $pdo->prepare("
            SELECT ans.*, r.label_th, r.section 
            FROM naf_answers ans 
            LEFT JOIN naf_rules r ON ans.item_code = r.item_code 
            WHERE ans.assessment_id = :id
        ");
        $stmtAnswers->execute(['id' => $id]);
        $answers = $stmtAnswers->fetchAll();

        AuditService::log('VIEW_NAF', 'NAF', (string)$id, $assessment['hn'], $assessment['vn']);

        require __DIR__ . '/../../views/naf/show.php';
    }

    public function printView(int $id): void {
        AuthMiddleware::check();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, p.fullname, p.age, p.gender, p.cid, u.fullname as assessor_name
            FROM naf_assessments a
            JOIN patients_cache p ON a.hn = p.hn
            JOIN users u ON a.assessor_id = u.id
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $assessment = $stmt->fetch();

        // Get last 3 historical assessments for side-by-side comparison on print form
        $stmtHist = $pdo->prepare("
            SELECT * FROM naf_assessments 
            WHERE hn = :hn 
            ORDER BY assessment_date DESC, id DESC 
            LIMIT 3
        ");
        $stmtHist->execute(['hn' => $assessment['hn']]);
        $history = array_reverse($stmtHist->fetchAll());

        $stmtAns = $pdo->prepare("
            SELECT ans.*, r.label_th, r.section 
            FROM naf_answers ans 
            LEFT JOIN naf_rules r ON ans.item_code = r.item_code 
            WHERE ans.assessment_id = :id
        ");
        $stmtAns->execute(['id' => $id]);
        $answers = $stmtAns->fetchAll();

        AuditService::log('PRINT_NAF', 'NAF', (string)$id, $assessment['hn'], $assessment['vn']);

        require __DIR__ . '/../../views/naf/print.php';
    }
}
