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
        $query       = SanitizerHelper::escape($_GET['q'] ?? '');
        $typeFilter  = SanitizerHelper::escape($_GET['type'] ?? 'ALL');
        $nafFilter   = SanitizerHelper::escape($_GET['naf'] ?? 'ALL');
        $labFilter   = SanitizerHelper::escape($_GET['lab'] ?? 'ALL');
        $results     = [];

        $api = new PdhApiService();
        $pdo = Database::getConnection();

        // 1. If keyword search is entered, sync matching live patients from HIMPRO Gateway
        if (!empty($query)) {
            $gatewayRes = $api->searchPatients($query);
            if (!empty($gatewayRes['data']) && is_array($gatewayRes['data'])) {
                $stmtP = $pdo->prepare("
                    INSERT INTO patients_cache (hn, cid, prefix, first_name, last_name, fullname, gender, birthdate, age, phone, synced_at)
                    VALUES (:hn, :cid, :prefix, :fname, :lname, :fullname, :gender, :bdate, :age, :phone, NOW())
                    ON DUPLICATE KEY UPDATE fullname = VALUES(fullname), gender = VALUES(gender), phone = VALUES(phone), synced_at = NOW()
                ");
                foreach ($gatewayRes['data'] as $gp) {
                    if (empty($gp['hn'])) continue;
                    $prefix   = $gp['prefix'] ?? $gp['pname'] ?? '';
                    $fname    = $gp['first_name'] ?? $gp['fname'] ?? '';
                    $lname    = $gp['last_name'] ?? $gp['lname'] ?? '';
                    $fullname = $gp['fullname'] ?? trim("{$prefix} {$fname} {$lname}");

                    if (empty($fname) && !empty($fullname)) {
                        $parts = preg_split('/\s+/', trim($fullname));
                        if (count($parts) >= 3) {
                            $prefix = $parts[0];
                            $fname  = $parts[1];
                            $lname  = implode(' ', array_slice($parts, 2));
                        } elseif (count($parts) === 2) {
                            $fname  = $parts[0];
                            $lname  = $parts[1];
                        } else {
                            $fname  = $fullname;
                        }
                    }
                    if (empty($fname)) $fname = 'ผู้ป่วย';
                    if (empty($fullname)) $fullname = trim("{$prefix} {$fname} {$lname}");

                    $gender = SanitizerHelper::normalizeGender($gp['sex_label'] ?? $gp['gender'] ?? $gp['sex'] ?? '');
                    $bdate  = $gp['birth_date'] ?? null;
                    $age    = 0;
                    if (!empty($bdate) && $bdate !== '0000-00-00') {
                        $age = date_diff(date_create($bdate), date_create('today'))->y;
                    }
                    $rawPhone = $gp['informtel'] ?? $gp['tel'] ?? $gp['phone'] ?? $gp['mobile'] ?? $gp['hometel'] ?? null;
                    $phone    = PdhApiService::formatOrExtractPhone($rawPhone, $gp['hn']);

                    $stmtP->execute([
                        'hn'       => $gp['hn'],
                        'cid'      => $gp['cid'] ?? null,
                        'prefix'   => $prefix,
                        'fname'    => $fname,
                        'lname'    => $lname,
                        'fullname' => $fullname,
                        'gender'   => $gender,
                        'bdate'    => $bdate,
                        'age'      => $age,
                        'phone'    => $phone
                    ]);
                }
            }
        }

        // 2. Build dynamic multi-criteria filter query
        $sql = "
            SELECT p.*, 
                   n.naf_grade as last_naf_grade, n.assessment_date as last_naf_date, n.total_score as last_naf_score,
                   v.clinic as last_clinic, v.visit_date as last_visit_date,
                   r.risk_level as registry_risk
            FROM patients_cache p
            LEFT JOIN (
                SELECT hn, naf_grade, assessment_date, total_score,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                FROM naf_assessments
            ) n ON p.hn = n.hn AND n.rn = 1
            LEFT JOIN (
                SELECT hn, clinic, visit_date,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY visit_date DESC, vn DESC) as rn
                FROM visits_cache
            ) v ON p.hn = v.hn AND v.rn = 1
            LEFT JOIN nutrition_registry r ON p.hn = r.hn AND r.active = 1
            WHERE 1=1
        ";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (p.hn LIKE :q1 OR p.cid LIKE :q2 OR p.fullname LIKE :q3 OR p.phone LIKE :q4 OR v.clinic LIKE :q5)";
            $qVal = "%{$query}%";
            $params['q1'] = $qVal;
            $params['q2'] = $qVal;
            $params['q3'] = $qVal;
            $params['q4'] = $qVal;
            $params['q5'] = $qVal;
        }

        if ($typeFilter === 'OPD') {
            $sql .= " AND v.clinic IS NOT NULL";
        } elseif ($typeFilter === 'IPD') {
            $sql .= " AND v.clinic LIKE '%IPD%'";
        } elseif ($typeFilter === 'REGISTRY') {
            $sql .= " AND r.risk_level IS NOT NULL";
        }

        if ($nafFilter === 'NAF A') {
            $sql .= " AND n.naf_grade = 'NAF A'";
        } elseif ($nafFilter === 'NAF B') {
            $sql .= " AND n.naf_grade = 'NAF B'";
        } elseif ($nafFilter === 'NAF C') {
            $sql .= " AND n.naf_grade = 'NAF C'";
        } elseif ($nafFilter === 'UNASSESSED') {
            $sql .= " AND n.naf_grade IS NULL";
        }

        if ($labFilter === 'LOW_BMI') {
            $sql .= " AND p.bmi > 0 AND p.bmi < 18.5";
        }

        $sql .= " ORDER BY p.synced_at DESC, p.hn DESC LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        AuditService::log('SEARCH_PATIENT', 'PATIENTS', null, null, null, null, [
            'query' => $query,
            'type'  => $typeFilter,
            'naf'   => $nafFilter,
            'lab'   => $labFilter
        ]);

        require __DIR__ . '/../../views/patients/search.php';
    }

    public function todayVisits(): void {
        AuthMiddleware::check();
        $api = new PdhApiService();
        $visitsRes = $api->getTodayVisits();
        $visits = $visitsRes['data'] ?? [];

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

        // 2. Labs (Multi-Visit History & Matrix)
        $labHistoryRes = $api->getLabsHistory($hn);
        $labHistoryData = $labHistoryRes['data'] ?? [];
        $latestLabs = $labHistoryRes['latest_labs'] ?? [];
        $labAlerts = \App\Services\SmartAlertService::evaluateLabAlerts($latestLabs, $patient);

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
