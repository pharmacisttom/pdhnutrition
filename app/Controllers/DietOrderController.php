<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RbacMiddleware;
use App\Services\PdhApiService;
use App\Services\DietCalculationService;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;
use PDO;
use Exception;

class DietOrderController {
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

        $weight = $patient['weight'] ?? 50.0;
        $height = $patient['height'] ?? 160.0;
        $gender = $patient['gender'] ?? 'MALE';

        // Precalculate defaults
        $dietCalc = DietCalculationService::computeFullDietPlan([
            'height_cm' => $height,
            'gender' => $gender,
            'energy_kcal_per_ibw' => '30',
            'protein_g_per_ibw' => '1.2'
        ]);

        $csrfToken = CsrfMiddleware::generateToken();
        require __DIR__ . '/../../views/diet/create.php';
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

        $weightKg = (float)($input['weight_kg'] ?? 50);
        $heightCm = (float)($input['height_cm'] ?? 160);
        $gender   = $input['gender'] ?? 'MALE';
        $ibw      = DietCalculationService::calculateIbw($heightCm, $gender);

        $kcalFactor    = $input['energy_kcal_per_ibw'] ?? '30';
        $proteinFactor = $input['protein_g_per_ibw'] ?? '1.2';

        $totalEnergy  = DietCalculationService::calculateEnergy($ibw, $kcalFactor);
        $totalProtein = DietCalculationService::calculateProtein($ibw, $proteinFactor);

        $dietTypes = $input['diet_types'] ?? [];
        $dietTypesJson = json_encode($dietTypes, JSON_UNESCAPED_UNICODE);

        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            $orderedBy = (int)$_SESSION['user_id'];
            $assessmentId = !empty($input['assessment_id']) ? (int)$input['assessment_id'] : null;

            // 1. Insert Diet Order
            $stmt = $pdo->prepare("
                INSERT INTO diet_orders 
                (assessment_id, hn, vn, weight_kg, height_cm, ibw_kg, energy_kcal_per_ibw, total_energy_kcal,
                 protein_g_per_ibw, total_protein_g, diet_types_json, required_diet_note, ordered_by, status, created_at)
                VALUES 
                (:aid, :hn, :vn, :w, :h, :ibw, :efactor, :total_e,
                 :pfactor, :total_p, :diets, :note, :by, 'ACTIVE', NOW())
            ");

            $stmt->execute([
                'aid'      => $assessmentId,
                'hn'       => $hn,
                'vn'       => $vn,
                'w'        => $weightKg,
                'h'        => $heightCm,
                'ibw'      => $ibw,
                'efactor'  => $kcalFactor,
                'total_e'  => $totalEnergy,
                'pfactor'  => $proteinFactor,
                'total_p'  => $totalProtein,
                'diets'    => $dietTypesJson,
                'note'     => $input['required_diet_note'] ?? '',
                'by'       => $orderedBy
            ]);

            $dietOrderId = (int)$pdo->lastInsertId();

            // 2. Insert Oral Supplement if specified
            if (!empty($input['oral_formula'])) {
                $mlMeal = (int)($input['oral_ml_per_meal'] ?? 200);
                $freq   = (int)($input['oral_frequency'] ?? 3);
                $stmtOs = $pdo->prepare("
                    INSERT INTO oral_supplements (diet_order_id, formula_type, ml_per_meal, meal, frequency_per_day, total_daily_ml, note)
                    VALUES (:did, :formula, :ml, :meal, :freq, :total, :note)
                ");
                $stmtOs->execute([
                    'did'     => $dietOrderId,
                    'formula' => $input['oral_formula'],
                    'ml'      => $mlMeal,
                    'meal'    => $input['oral_meal'] ?? 'TID',
                    'freq'    => $freq,
                    'total'   => $mlMeal * $freq,
                    'note'    => $input['oral_note'] ?? ''
                ]);
            }

            // 3. Insert Tube Feeding if specified
            if (!empty($input['tube_formula'])) {
                $mlMeal = (int)($input['tube_ml_per_meal'] ?? 250);
                $freq   = (int)($input['tube_frequency'] ?? 4);
                $stmtTf = $pdo->prepare("
                    INSERT INTO tube_feedings (diet_order_id, formula_type, ml_per_meal, meal, frequency_per_day, total_daily_ml, note)
                    VALUES (:did, :formula, :ml, :meal, :freq, :total, :note)
                ");
                $stmtTf->execute([
                    'did'     => $dietOrderId,
                    'formula' => $input['tube_formula'],
                    'ml'      => $mlMeal,
                    'meal'    => $input['tube_meal'] ?? 'QID',
                    'freq'    => $freq,
                    'total'   => $mlMeal * $freq,
                    'note'    => $input['tube_note'] ?? ''
                ]);
            }

            AuditService::log('CREATE_DIET_ORDER', 'DIET', (string)$dietOrderId, $hn, $vn);

            $pdo->commit();

            ResponseHelper::json([
                'success' => true,
                'message' => 'บันทึก Diet Order สำเร็จ',
                'diet_order_id' => $dietOrderId,
                'redirect' => ($_ENV['APP_URL'] ?? '/pdhnutrition') . '/diet/print/' . $dietOrderId
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            ResponseHelper::json(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึก Diet Order: ' . $e->getMessage()], 500);
        }
    }

    public function printView(int $id): void {
        AuthMiddleware::check();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT d.*, p.fullname, p.age, p.gender, p.cid, u.fullname as ordered_by_name
            FROM diet_orders d
            JOIN patients_cache p ON d.hn = p.hn
            JOIN users u ON d.ordered_by = u.id
            WHERE d.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $dietOrder = $stmt->fetch();

        if (!$dietOrder) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>ไม่พบรายการ Diet Order ID: " . $id . "</p>";
            exit;
        }

        // Fetch Oral Supplement
        $stmtOs = $pdo->prepare("SELECT * FROM oral_supplements WHERE diet_order_id = :id");
        $stmtOs->execute(['id' => $id]);
        $oralSupp = $stmtOs->fetch();

        // Fetch Tube Feeding
        $stmtTf = $pdo->prepare("SELECT * FROM tube_feedings WHERE diet_order_id = :id");
        $stmtTf->execute(['id' => $id]);
        $tubeFeed = $stmtTf->fetch();

        AuditService::log('PRINT_DIET_ORDER', 'DIET', (string)$id, $dietOrder['hn'], $dietOrder['vn']);

        require __DIR__ . '/../../views/diet/print.php';
    }
}
