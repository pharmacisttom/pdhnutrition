<?php
namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class ReportService {

    // 1. Pre-Doctor Queue Report
    public static function getPreDoctorReport(array $params = []): array {
        $pdo = Database::getConnection();
        $date = $params['date'] ?? date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT t.*, p.fullname, p.age, p.gender, p.cid,
                   n.naf_grade as last_naf_grade, n.assessment_date as last_naf_date,
                   r.reason as registry_reason, u.fullname as dietitian_name
            FROM nutrition_tasks t
            JOIN patients_cache p ON t.hn = p.hn
            LEFT JOIN nutrition_registry r ON t.hn = r.hn
            LEFT JOIN users u ON t.locked_by = u.id
            LEFT JOIN (
                SELECT hn, naf_grade, assessment_date,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                FROM naf_assessments
            ) n ON t.hn = n.hn AND n.rn = 1
            WHERE t.visit_date = :date
            ORDER BY t.priority ASC, t.created_at ASC
        ");
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Pending Assessments Report
    public static function getPendingReport(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT t.*, p.fullname, p.age, p.cid, r.reason
            FROM nutrition_tasks t
            JOIN patients_cache p ON t.hn = p.hn
            LEFT JOIN nutrition_registry r ON t.hn = r.hn
            WHERE t.status IN ('WAITING_NUTRITION', 'IN_ASSESSMENT')
            ORDER BY t.priority ASC, t.created_at ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Daily Assessment Log
    public static function getDailyReport(string $date): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, p.fullname, p.age, u.fullname as assessor_name,
                   d.total_energy_kcal, d.total_protein_g
            FROM naf_assessments a
            JOIN patients_cache p ON a.hn = p.hn
            JOIN users u ON a.assessor_id = u.id
            LEFT JOIN diet_orders d ON a.id = d.assessment_id
            WHERE a.assessment_date = :date
            ORDER BY a.assessment_time ASC
        ");
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. NAF Classification (A/B/C)
    public static function getNafClassificationReport(?string $grade = null, ?string $month = null): array {
        $pdo = Database::getConnection();
        $month = $month ?: date('Y-m');
        $sql = "
            SELECT a.*, p.fullname, p.age, p.gender, u.fullname as assessor_name
            FROM naf_assessments a
            JOIN patients_cache p ON a.hn = p.hn
            JOIN users u ON a.assessor_id = u.id
            WHERE DATE_FORMAT(a.assessment_date, '%Y-%m') = :month
        ";
        $params = ['month' => $month];

        if ($grade && in_array($grade, ['NAF A', 'NAF B', 'NAF C'])) {
            $sql .= " AND a.naf_grade = :grade";
            $params['grade'] = $grade;
        }

        $sql .= " ORDER BY a.assessment_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. High Risk Patients (NAF C)
    public static function getHighRiskReport(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT a.*, p.fullname, p.age, p.phone, u.fullname as assessor_name,
                   r.reason as registry_reason, d.total_energy_kcal, d.total_protein_g
            FROM naf_assessments a
            JOIN patients_cache p ON a.hn = p.hn
            JOIN users u ON a.assessor_id = u.id
            LEFT JOIN nutrition_registry r ON a.hn = r.hn
            LEFT JOIN diet_orders d ON a.id = d.assessment_id
            WHERE a.naf_grade = 'NAF C'
            ORDER BY a.assessment_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. Follow-up Overdue Patients
    public static function getOverdueReport(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT f.*, p.fullname, p.age, p.phone,
                   DATEDIFF(CURRENT_DATE(), f.next_follow_up_date) as days_overdue
            FROM nutrition_followups f
            JOIN patients_cache p ON f.hn = p.hn
            WHERE f.next_follow_up_date < CURRENT_DATE() AND f.active = 1 AND f.status != 'COMPLETED'
            ORDER BY days_overdue DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. Patient History Timeline
    public static function getPatientTimeline(string $hn): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT a.*, u.fullname as assessor_name,
                   d.total_energy_kcal, d.total_protein_g, d.diet_types_json
            FROM naf_assessments a
            JOIN users u ON a.assessor_id = u.id
            LEFT JOIN diet_orders d ON a.id = d.assessment_id
            WHERE a.hn = :hn
            ORDER BY a.assessment_date DESC, a.assessment_time DESC
        ");
        $stmt->execute(['hn' => $hn]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Monthly Summary & Compliance %
    public static function getMonthlySummary(string $yearMonth): array {
        $pdo = Database::getConnection();

        // Total assessments in month
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM naf_assessments WHERE DATE_FORMAT(assessment_date, '%Y-%m') = :ym");
        $stmt->execute(['ym' => $yearMonth]);
        $totalAssessments = (int)$stmt->fetchColumn();

        // Grades breakdown
        $stmt = $pdo->prepare("
            SELECT naf_grade, COUNT(*) as cnt 
            FROM naf_assessments 
            WHERE DATE_FORMAT(assessment_date, '%Y-%m') = :ym 
            GROUP BY naf_grade
        ");
        $stmt->execute(['ym' => $yearMonth]);
        $grades = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Pre-doctor Compliance
        $stmtTask = $pdo->prepare("
            SELECT 
                COUNT(*) as required_cnt,
                SUM(CASE WHEN status IN ('NUTRITION_COMPLETED', 'READY_FOR_DOCTOR') THEN 1 ELSE 0 END) as completed_cnt
            FROM nutrition_tasks
            WHERE DATE_FORMAT(visit_date, '%Y-%m') = :ym
        ");
        $stmtTask->execute(['ym' => $yearMonth]);
        $taskStats = $stmtTask->fetch();

        $required = (int)($taskStats['required_cnt'] ?? 0);
        $completed = (int)($taskStats['completed_cnt'] ?? 0);
        $compliancePct = $required > 0 ? round(($completed / $required) * 100, 1) : 100.0;

        return [
            'year_month' => $yearMonth,
            'total_assessments' => $totalAssessments,
            'naf_a_count' => (int)($grades['NAF A'] ?? 0),
            'naf_b_count' => (int)($grades['NAF B'] ?? 0),
            'naf_c_count' => (int)($grades['NAF C'] ?? 0),
            'pre_doctor_required' => $required,
            'pre_doctor_completed' => $completed,
            'compliance_pct' => $compliancePct
        ];
    }

    // 9. Clinic Breakdown
    public static function getClinicReport(string $yearMonth): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT clinic, 
                   COUNT(*) as total_tasks,
                   SUM(CASE WHEN status IN ('NUTRITION_COMPLETED', 'READY_FOR_DOCTOR') THEN 1 ELSE 0 END) as completed_tasks,
                   SUM(CASE WHEN risk_level = 'SEVERE' OR risk_level = 'HIGH' THEN 1 ELSE 0 END) as high_risk_tasks
            FROM nutrition_tasks
            WHERE DATE_FORMAT(visit_date, '%Y-%m') = :ym
            GROUP BY clinic
        ");
        $stmt->execute(['ym' => $yearMonth]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 10. Dietitian Workload
    public static function getDietitianWorkload(string $yearMonth): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT u.fullname as dietitian_name,
                   COUNT(a.id) as total_assessments,
                   SUM(CASE WHEN a.naf_grade = 'NAF C' THEN 1 ELSE 0 END) as severe_count,
                   SUM(CASE WHEN a.naf_grade = 'NAF B' THEN 1 ELSE 0 END) as moderate_count
            FROM users u
            LEFT JOIN naf_assessments a ON u.id = a.assessor_id AND DATE_FORMAT(a.assessment_date, '%Y-%m') = :ym
            WHERE u.role = 'DIETITIAN' OR u.role = 'ADMIN'
            GROUP BY u.id, u.fullname
        ");
        $stmt->execute(['ym' => $yearMonth]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 11. Clinical Lab Abnormalities Report
    public static function getLabAbnormalitiesReport(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT p.hn, p.fullname, p.age, p.gender,
                   l.test_name, l.result_value, l.result_unit, l.result_date
            FROM lab_cache l
            JOIN patients_cache p ON l.hn = p.hn
            ORDER BY l.result_date DESC, l.id DESC
        ");
        $labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($labs as $l) {
            $tKey = strtolower(trim($l['test_name']));
            $eval = SmartAlertService::evaluateLabAlerts([$tKey => (float)$l['result_value']]);
            if (!empty($eval)) {
                $results[] = [
                    'hn' => $l['hn'],
                    'fullname' => $l['fullname'],
                    'age' => $l['age'] . ' ปี',
                    'gender' => $l['gender'],
                    'test_name' => $l['test_name'],
                    'result_value' => $l['result_value'] . ' ' . ($l['result_unit'] ?? ''),
                    'severity' => $eval[0]['level'],
                    'alert_title' => $eval[0]['title'],
                    'clinical_recommendation' => $eval[0]['action_th'],
                    'result_date' => $l['result_date']
                ];
            }
        }
        return $results;
    }

    // 12. Diet Orders & Clinical Nutrition Summary Report
    public static function getDietOrdersReport(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT d.id, d.hn, p.fullname, d.weight_kg, d.height_cm, d.ibw_kg,
                   d.total_energy_kcal, d.total_protein_g, d.diet_types_json,
                   u.fullname as ordered_by_name, d.created_at
            FROM diet_orders d
            JOIN patients_cache p ON d.hn = p.hn
            JOIN users u ON d.ordered_by = u.id
            ORDER BY d.created_at DESC
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($orders as &$o) {
            $types = json_decode($o['diet_types_json'] ?? '[]', true);
            $o['diet_types'] = is_array($types) ? implode(', ', $types) : $o['diet_types_json'];
            unset($o['diet_types_json']);
        }
        return $orders;
    }

    // 13. Nutrition Outcomes & Weight Trends Report
    public static function getOutcomesReport(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT a.hn, p.fullname, a.assessment_date, a.weight_kg, a.height_cm, a.bmi, a.naf_grade, a.total_score,
                   u.fullname as assessor_name
            FROM naf_assessments a
            JOIN patients_cache p ON a.hn = p.hn
            JOIN users u ON a.assessor_id = u.id
            ORDER BY a.hn ASC, a.assessment_date ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Export Handler for CSV
    public static function exportCsv(array $data, array $headers, string $filename): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility with Thai language
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, $headers);
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }
        fclose($output);
        exit;
    }
}
