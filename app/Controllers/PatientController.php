<?php
namespace App\Controllers;

use App\Config\Database;
use App\Middleware\AuthMiddleware;
use App\Services\PdhApiService;
use App\Services\AuditService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;
use PDO;

class PatientController {
    public function search(): void {
        AuthMiddleware::check();
        $query = SanitizerHelper::escape($_GET['q'] ?? '');
        $results = [];

        if (!empty($query)) {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                SELECT p.*, n.naf_grade as last_naf_grade, n.assessment_date as last_naf_date
                FROM patients_cache p
                LEFT JOIN (
                    SELECT hn, naf_grade, assessment_date,
                           ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                    FROM naf_assessments
                ) n ON p.hn = n.hn AND n.rn = 1
                WHERE p.hn LIKE :q OR p.cid LIKE :q OR p.fullname LIKE :q
                LIMIT 30
            ");
            $stmt->execute(['q' => "%{$query}%"]);
            $results = $stmt->fetchAll();

            AuditService::log('SEARCH_PATIENT', 'PATIENTS', null, null, null, null, ['query' => $query]);
        }

        require __DIR__ . '/../../views/patients/search.php';
    }

    public function todayVisits(): void {
        AuthMiddleware::check();
        $api = new PdhApiService();
        $visitsRes = $api->getTodayVisits();
        $visits = $visitsRes['data'] ?? [];

        $pdo = Database::getConnection();
        foreach ($visits as &$v) {
            if (empty($v['fullname']) || !isset($v['age'])) {
                $stmtP = $pdo->prepare("SELECT fullname, age FROM patients_cache WHERE hn = :hn");
                $stmtP->execute(['hn' => $v['hn']]);
                $p = $stmtP->fetch();
                if ($p) {
                    $v['fullname'] = $v['fullname'] ?? $p['fullname'];
                    $v['age']      = $v['age'] ?? $p['age'];
                }
            }
            if (!array_key_exists('last_naf_grade', $v)) {
                $stmtN = $pdo->prepare("
                    SELECT naf_grade FROM naf_assessments 
                    WHERE hn = :hn ORDER BY assessment_date DESC, id DESC LIMIT 1
                ");
                $stmtN->execute(['hn' => $v['hn']]);
                $v['last_naf_grade'] = $stmtN->fetchColumn() ?: null;
            }
        }
        unset($v);

        AuditService::log('VIEW_TODAY_VISITS', 'PATIENTS');

        require __DIR__ . '/../../views/patients/today.php';
    }

    public function profile(string $hn): void {
        AuthMiddleware::check();
        $api = new PdhApiService();
        $patientRes = $api->getPatient($hn);
        $patient = $patientRes['data'] ?? null;

        if (!$patient) {
            http_response_code(404);
            echo "<h1>404 Not Found</h1><p>ไม่พบข้อมูลผู้ป่วย HN: " . htmlspecialchars($hn) . "</p>";
            exit;
        }

        $pdo = Database::getConnection();

        // 1. Diagnosis
        $diagRes = $api->getDiagnosis($hn);
        $diagnoses = $diagRes['data'] ?? [];

        // 2. Labs
        $labRes = $api->getLatestLabs($hn);
        $latestLabs = $labRes['data'] ?? [];

        // 3. Allergies
        $allergyRes = $api->getAllergies($hn);
        $allergies = $allergyRes['data'] ?? [];

        // 4. NAF History
        $stmtNaf = $pdo->prepare("
            SELECT a.*, u.fullname as assessor_name 
            FROM naf_assessments a 
            JOIN users u ON a.assessor_id = u.id 
            WHERE a.hn = :hn 
            ORDER BY a.assessment_date DESC, a.id DESC
        ");
        $stmtNaf->execute(['hn' => $hn]);
        $nafHistory = $stmtNaf->fetchAll();

        // 5. Diet Orders
        $stmtDiet = $pdo->prepare("
            SELECT d.*, u.fullname as ordered_by_name 
            FROM diet_orders d 
            JOIN users u ON d.ordered_by = u.id 
            WHERE d.hn = :hn 
            ORDER BY d.created_at DESC
        ");
        $stmtDiet->execute(['hn' => $hn]);
        $dietOrders = $stmtDiet->fetchAll();

        // 6. Clinical Notes
        $stmtNotes = $pdo->prepare("
            SELECT n.*, u.fullname as dietitian_name 
            FROM nutrition_notes n 
            JOIN users u ON n.dietitian_id = u.id 
            WHERE n.hn = :hn 
            ORDER BY n.created_at DESC
        ");
        $stmtNotes->execute(['hn' => $hn]);
        $clinicalNotes = $stmtNotes->fetchAll();

        // 7. Registry Status
        $stmtReg = $pdo->prepare("SELECT * FROM nutrition_registry WHERE hn = :hn AND active = 1");
        $stmtReg->execute(['hn' => $hn]);
        $registryItem = $stmtReg->fetch();

        AuditService::log('VIEW_PATIENT_PROFILE', 'PATIENTS', null, $hn);

        require __DIR__ . '/../../views/patients/profile.php';
    }
}
