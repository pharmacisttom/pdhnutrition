<?php
namespace App\Services;

use App\Config\Database;
use PDO;

class SmartAlertService {

    /**
     * Evaluates all patients and generates Smart Clinical Nutrition Alerts
     * Categorizes into Critical (24h), Urgent (3-Day), and Routine
     */
    public static function getDailyAlerts(): array {
        $pdo = Database::getConnection();

        // 1. Fetch patients with latest anthropometric, NAF, and lab data
        $stmt = $pdo->query("
            SELECT p.*,
                   n.id as naf_id, n.naf_grade as last_naf_grade, n.total_score as last_naf_score, n.assessment_date as last_naf_date,
                   v.clinic as last_clinic, v.visit_date as last_visit_date, v.vn,
                   r.risk_level as registry_risk
            FROM patients_cache p
            LEFT JOIN (
                SELECT id, hn, naf_grade, total_score, assessment_date,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                FROM naf_assessments
            ) n ON p.hn = n.hn AND n.rn = 1
            LEFT JOIN (
                SELECT hn, clinic, visit_date, vn,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY visit_date DESC, vn DESC) as rn
                FROM visits_cache
            ) v ON p.hn = v.hn AND v.rn = 1
            LEFT JOIN nutrition_registry r ON p.hn = r.hn AND r.active = 1
            ORDER BY p.synced_at DESC
        ");
        $patients = $stmt->fetchAll();

        $critical24h = [];
        $urgent3Day  = [];
        $routine7Day = [];

        foreach ($patients as $p) {
            $alerts = [];
            $severity = 'LOW'; // LOW, MODERATE, HIGH, CRITICAL

            // --- UNDERNUTRITION & DEFICIENCY EVALUATION ---
            if (($p['last_naf_grade'] ?? '') === 'NAF C' || ($p['last_naf_score'] ?? 0) >= 11) {
                $alerts[] = ['type' => 'UNDERNUTRITION', 'level' => 'CRITICAL', 'msg' => 'NAF Grade C (Severe Malnutrition - คะแนน ' . ($p['last_naf_score'] ?? 11) . ')'];
                $severity = 'CRITICAL';
            } elseif (($p['last_naf_grade'] ?? '') === 'NAF B') {
                $alerts[] = ['type' => 'UNDERNUTRITION', 'level' => 'HIGH', 'msg' => 'NAF Grade B (Moderate Malnutrition)'];
                if ($severity !== 'CRITICAL') $severity = 'HIGH';
            }

            if ($p['weight'] > 0 && $p['height'] > 0) {
                $bmi = round($p['weight'] / pow($p['height'] / 100, 2), 2);
                if ($bmi < 16.0) {
                    $alerts[] = ['type' => 'UNDERNUTRITION', 'level' => 'CRITICAL', 'msg' => 'Severe Underweight (BMI = ' . $bmi . ' kg/m² < 16.0)'];
                    $severity = 'CRITICAL';
                } elseif ($bmi < 18.5) {
                    $alerts[] = ['type' => 'UNDERNUTRITION', 'level' => 'HIGH', 'msg' => 'Underweight / Malnutrition Risk (BMI = ' . $bmi . ' kg/m²)'];
                    if ($severity !== 'CRITICAL') $severity = 'HIGH';
                } elseif ($bmi >= 30.0) {
                    // --- OVERNUTRITION & METABOLIC EXCESS EVALUATION ---
                    $alerts[] = ['type' => 'OVERNUTRITION', 'level' => 'HIGH', 'msg' => 'Obesity Class II/III (BMI = ' . $bmi . ' kg/m² ≥ 30.0)'];
                    if ($severity !== 'CRITICAL') $severity = 'HIGH';
                }
            }

            // Check if patient is in active registry
            if (!empty($p['registry_risk'])) {
                $alerts[] = ['type' => 'REGISTRY', 'level' => 'ROUTINE', 'msg' => 'อยู่ในกลุ่มติดตามโภชนาการ (Registry - Risk: ' . $p['registry_risk'] . ')'];
            }

            // Group into Daily Alert Lists
            if (!empty($alerts)) {
                $p['alert_items'] = $alerts;
                $p['max_severity'] = $severity;

                if ($severity === 'CRITICAL') {
                    $critical24h[] = $p;
                } elseif ($severity === 'HIGH') {
                    $urgent3Day[] = $p;
                } else {
                    $routine7Day[] = $p;
                }
            }
        }

        return [
            'critical_24h' => $critical24h,
            'urgent_3day'  => $urgent3Day,
            'routine_7day' => $routine7Day,
            'total_alerts' => count($critical24h) + count($urgent3Day) + count($routine7Day)
        ];
    }

    /**
     * Generates 2D Clinical Risk Heatmap Matrix Data
     * Axes: Category (Undernutrition / Overnutrition / Deficiency / Electrolyte Excess)
     * Severities: Critical, High, Moderate, Mild
     */
    public static function getHeatmapMatrix(): array {
        $daily = self::getDailyAlerts();
        $allAlertPatients = array_merge($daily['critical_24h'], $daily['urgent_3day'], $daily['routine_7day']);

        $matrix = [
            'UNDERNUTRITION' => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'OVERNUTRITION'  => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'DEFICIENCY'     => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'ELECTROLYTE'    => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
        ];

        foreach ($allAlertPatients as $p) {
            foreach ($p['alert_items'] as $item) {
                $cat   = $item['type'] ?? 'UNDERNUTRITION';
                $level = $item['level'] ?? 'LOW';
                if (isset($matrix[$cat][$level])) {
                    $matrix[$cat][$level]++;
                    $matrix[$cat]['patients'][] = [
                        'hn' => $p['hn'],
                        'fullname' => $p['fullname'],
                        'msg' => $item['msg'],
                        'level' => $level,
                        'clinic' => $p['last_clinic'] ?? 'OPD/IPD'
                    ];
                }
            }
        }

        return $matrix;
    }
}
