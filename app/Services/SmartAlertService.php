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
        $api = new PdhApiService();

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

            // --- 1. UNDERNUTRITION & NAF EVALUATION ---
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

            // --- 2. LAB ABNORMALITIES EVALUATION (FBS, HbA1c, Lipids, Albumin, Renal) ---
            $labRes = $api->getLatestLabs($p['hn']);
            $latestLabs = $labRes['data'] ?? [];
            $labAlerts = self::evaluateLabAlerts($latestLabs, $p);

            foreach ($labAlerts as $lAlert) {
                $alerts[] = [
                    'type'  => $lAlert['type'],
                    'level' => $lAlert['level'],
                    'msg'   => $lAlert['title'] . ': ' . $lAlert['msg'] . ' (' . $lAlert['action_th'] . ')'
                ];
                if ($lAlert['level'] === 'CRITICAL') {
                    $severity = 'CRITICAL';
                } elseif ($lAlert['level'] === 'HIGH' && $severity !== 'CRITICAL') {
                    $severity = 'HIGH';
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
     * Evaluates detailed lab parameters for a single patient
     * Detects High FBS, High HbA1c, Dyslipidemia, Hypoalbuminemia, and Renal Risk
     */
    public static function evaluateLabAlerts(array $labs, array $patient = []): array {
        $alerts = [];

        // 1. Fasting Blood Sugar (FBS)
        $fbs = isset($labs['fbs']) ? (float)$labs['fbs'] : null;
        if ($fbs !== null && $fbs > 0) {
            if ($fbs >= 200) {
                $alerts[] = [
                    'type' => 'HYPERGLYCEMIA',
                    'level' => 'CRITICAL',
                    'title' => 'ภาวะน้ำตาลในเลือดสูงขั้นวิกฤต (Critical High FBS)',
                    'msg' => "FBS = {$fbs} mg/dL (≥ 200 mg/dL)",
                    'badge_class' => 'bg-danger text-white',
                    'action_th' => 'เสี่ยงภาวะ DKA/HHS ควรควบคุมคาร์โบไฮเดรตและน้ำตาลด่วน'
                ];
            } elseif ($fbs >= 126) {
                $alerts[] = [
                    'type' => 'HYPERGLYCEMIA',
                    'level' => 'HIGH',
                    'title' => 'ภาวะน้ำตาลในเลือดสูง (High FBS)',
                    'msg' => "FBS = {$fbs} mg/dL (≥ 126 mg/dL)",
                    'badge_class' => 'bg-danger-subtle text-danger border border-danger',
                    'action_th' => 'เกณฑ์เบาหวาน ควรจัดอาหารโภชนบำบัดเบาหวาน (DM Diet)'
                ];
            }
        }

        // 2. HbA1c (Glycated Hemoglobin)
        $hba1c = isset($labs['hba1c']) ? (float)$labs['hba1c'] : null;
        if ($hba1c !== null && $hba1c > 0) {
            if ($hba1c >= 8.0) {
                $alerts[] = [
                    'type' => 'HYPERGLYCEMIA',
                    'level' => 'CRITICAL',
                    'title' => 'ระดับน้ำตาลสะสมสูงรุนแรง (Uncontrolled HbA1c)',
                    'msg' => "HbA1c = {$hba1c}% (≥ 8.0%)",
                    'badge_class' => 'bg-danger text-white',
                    'action_th' => 'การคุมน้ำตาลล้มเหลวรุนแรง ต้องปรับแผนโภชนบำบัดเบาหวาน'
                ];
            } elseif ($hba1c >= 6.5) {
                $alerts[] = [
                    'type' => 'HYPERGLYCEMIA',
                    'level' => 'HIGH',
                    'title' => 'ระดับน้ำตาลสะสมสูงเกินเกณฑ์ (High HbA1c)',
                    'msg' => "HbA1c = {$hba1c}% (≥ 6.5%)",
                    'badge_class' => 'bg-warning-subtle text-warning-emphasis border border-warning',
                    'action_th' => 'เกณฑ์เบาหวาน ควรจำกัดหวานและควบคุมปริมาณแป้ง'
                ];
            }
        }

        // 3. Lipid Profile (Cholesterol, Triglycerides, LDL)
        $chol = isset($labs['cholesterol']) ? (float)$labs['cholesterol'] : null;
        if ($chol !== null && $chol >= 200) {
            $level = $chol >= 240 ? 'CRITICAL' : 'HIGH';
            $alerts[] = [
                'type' => 'DYSLIPIDEMIA',
                'level' => $level,
                'title' => 'โคเลสเตอรอลสูง (High Cholesterol)',
                'msg' => "Cholesterol = {$chol} mg/dL (≥ 200 mg/dL)",
                'badge_class' => 'bg-warning-subtle text-dark border border-warning',
                'action_th' => 'จำกัดไขมันอิ่มตัว และคอเลสเตอรอลในอาหาร'
            ];
        }

        $tg = isset($labs['triglyceride']) ? (float)$labs['triglyceride'] : null;
        if ($tg !== null && $tg >= 150) {
            $level = $tg >= 500 ? 'CRITICAL' : 'HIGH';
            $alerts[] = [
                'type' => 'DYSLIPIDEMIA',
                'level' => $level,
                'title' => 'ไตรกลีเซอไรด์ในเลือดสูง (High Triglycerides)',
                'msg' => "Triglyceride = {$tg} mg/dL (≥ 150 mg/dL)",
                'badge_class' => 'bg-warning-subtle text-dark border border-warning',
                'action_th' => 'จำกัดแป้งขัดขาว น้ำหวาน และแอลกอฮอล์'
            ];
        }

        $ldl = isset($labs['ldl']) ? (float)$labs['ldl'] : null;
        if ($ldl !== null && $ldl >= 130) {
            $alerts[] = [
                'type' => 'DYSLIPIDEMIA',
                'level' => 'HIGH',
                'title' => 'ไขมันชนิดเลวสูง (High LDL-Cholesterol)',
                'msg' => "LDL = {$ldl} mg/dL (≥ 130 mg/dL)",
                'badge_class' => 'bg-warning-subtle text-dark border border-warning',
                'action_th' => 'เสี่ยงหลอดเลือดอุดตัน เสริมใยอาหารละลายน้ำ'
            ];
        }

        // 4. Protein & Malnutrition (Albumin, Total Protein)
        $alb = isset($labs['albumin']) ? (float)$labs['albumin'] : null;
        if ($alb !== null && $alb > 0 && $alb < 3.5) {
            $level = $alb < 2.5 ? 'CRITICAL' : 'HIGH';
            $alerts[] = [
                'type' => 'HYPOALBUMINEMIA',
                'level' => $level,
                'title' => 'โปรตีนแอลบูมินต่ำ (Low Albumin / Hypoalbuminemia)',
                'msg' => "Albumin = {$alb} g/dL (< 3.5 g/dL)",
                'badge_class' => 'bg-danger text-white',
                'action_th' => 'เสี่ยงบวมน้ำและทุพโภชนาการ พิจารณาเสริม High Protein / ONS'
            ];
        }

        $tp = isset($labs['total_protein']) ? (float)$labs['total_protein'] : null;
        if ($tp !== null && $tp > 0 && $tp < 6.0) {
            $alerts[] = [
                'type' => 'HYPOALBUMINEMIA',
                'level' => 'HIGH',
                'title' => 'โปรตีนรวมในเลือดต่ำ (Low Total Protein)',
                'msg' => "Total Protein = {$tp} g/dL (< 6.0 g/dL)",
                'badge_class' => 'bg-danger-subtle text-danger border border-danger',
                'action_th' => 'บกพร่องโภชนาการโปรตีน ควรประเมิน NAF'
            ];
        }

        // 5. Renal Risk (eGFR, BUN, Creatinine)
        $egfr = isset($labs['egfr']) ? (float)$labs['egfr'] : null;
        if ($egfr !== null && $egfr > 0 && $egfr < 60) {
            $level = $egfr < 30 ? 'CRITICAL' : 'HIGH';
            $alerts[] = [
                'type' => 'RENAL_RISK',
                'level' => $level,
                'title' => 'การทำงานของไตเสื่อมลง (Low eGFR / CKD Risk)',
                'msg' => "eGFR = {$egfr} mL/min/1.73m² (< 60)",
                'badge_class' => 'bg-purple text-white',
                'action_th' => 'ปรับคุมโปรตีน โซเดียม โพแทสเซียม (CKD Diet Plan)'
            ];
        }

        $bun = isset($labs['bun']) ? (float)$labs['bun'] : null;
        if ($bun !== null && $bun > 20.0) {
            $alerts[] = [
                'type' => 'RENAL_RISK',
                'level' => 'HIGH',
                'title' => 'ค่าไนโตรเจนในเลือดสูง (High BUN)',
                'msg' => "BUN = {$bun} mg/dL (> 20.0 mg/dL)",
                'badge_class' => 'bg-purple-subtle text-purple border border-purple',
                'action_th' => 'ประเมินภาวะขาดน้ำ หรือการสลายตัวของโปรตีน'
            ];
        }

        return $alerts;
    }

    /**
     * Generates 2D Clinical Risk Heatmap Matrix Data
     * Axes: Category (Undernutrition / Overnutrition / Hyperglycemia / Dyslipidemia / Hypoalbuminemia / Renal Risk)
     */
    public static function getHeatmapMatrix(): array {
        $daily = self::getDailyAlerts();
        $allAlertPatients = array_merge($daily['critical_24h'], $daily['urgent_3day'], $daily['routine_7day']);

        $matrix = [
            'UNDERNUTRITION'  => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'OVERNUTRITION'   => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'HYPERGLYCEMIA'  => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'DYSLIPIDEMIA'    => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'HYPOALBUMINEMIA' => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
            'RENAL_RISK'      => ['CRITICAL' => 0, 'HIGH' => 0, 'MODERATE' => 0, 'LOW' => 0, 'patients' => []],
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
