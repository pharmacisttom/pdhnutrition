<?php
/**
 * PDH Nutrition System - Automated Test Suite
 * CLI Command: php tests/run_tests.php
 */

require_header();

function require_header() {
    echo "========================================================\n";
    echo "PDH Nutrition System - Automated Test Suite\n";
    echo "========================================================\n\n";
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

\App\Config\AppConfig::load();

$passCount = 0;
$failCount = 0;

function assertTest(string $testName, bool $condition): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "[ PASS ] {$testName}\n";
        $passCount++;
    } else {
        echo "[ FAIL ] {$testName}\n";
        $failCount++;
    }
}

use App\Services\NafScoringService;
use App\Services\DietCalculationService;
use App\Services\PdhApiService;
use App\Services\QueueManagerService;
use App\Config\Database;

echo "--- Running Unit Tests ---\n\n";

// 1. BMI Calculation Test
$bmi = NafScoringService::calculateBmi(48.5, 168.0);
assertTest("BMI Calculation (48.5 kg / 168 cm = 17.18)", $bmi === 17.18);

// 2. BMI Score Test
$bmiScore = NafScoringService::calculateBmiScore(16.5);
assertTest("BMI Score (< 17.0 = 2 points)", $bmiScore === 2);

// 3. TLC Calculation Test
$tlc = NafScoringService::calculateTlc(4800, 18.0);
assertTest("TLC Calculation (4800 WBC * 18% = 864 cells/mm³)", $tlc === 864.0);

// 4. TLC Score Test
$tlcScore = NafScoringService::calculateTlcScore(864.0);
assertTest("TLC Score (<= 1000 = 3 points)", $tlcScore === 3);

// 5. NAF Grade Translation Tests
assertTest("NAF Grade A Translation (Score 4 -> NAF A)", NafScoringService::translateGrade(4) === 'NAF A');
assertTest("NAF Grade B Translation (Score 8 -> NAF B)", NafScoringService::translateGrade(8) === 'NAF B');
assertTest("NAF Grade C Translation (Score 12 -> NAF C)", NafScoringService::translateGrade(12) === 'NAF C');

// 6. IBW Male Calculation Test
$ibwMale = DietCalculationService::calculateIbw(170.0, 'MALE');
assertTest("IBW Male Formula (Height 170 cm - 100 = 70 kg)", $ibwMale === 70.0);

// 7. IBW Female Calculation Test
$ibwFemale = DietCalculationService::calculateIbw(160.0, 'FEMALE');
assertTest("IBW Female Formula (Height 160 cm - 105 = 55 kg)", $ibwFemale === 55.0);

// 8. Energy & Protein Calculations Test
$energy = DietCalculationService::calculateEnergy(70.0, '30');
$protein = DietCalculationService::calculateProtein(70.0, '1.2');
assertTest("Energy Calculation (70 kg * 30 kcal = 2100 kcal)", $energy === 2100.0);
assertTest("Protein Calculation (70 kg * 1.2 g = 84 g)", $protein === 84.0);

// 9. Queue Sync Test
$taskCount = QueueManagerService::syncTodayQueueTasks();
assertTest("Pre-Doctor Queue Task Synchronization", is_numeric($taskCount));

// 10. API Service Response Format Test
$api = new PdhApiService();
$patientRes = $api->getPatient('0511037');
assertTest("HIS API Service Response Format", $patientRes['success'] === true && is_array($patientRes['data']));

// 11. System Clinics Management Test
$clinics = \App\Services\SystemSettingService::getClinics();
assertTest("System Clinics Master Retrieval", is_array($clinics) && count($clinics) > 0);

// 12. Active Clinic Names Extraction Test
$activeClinicNames = \App\Services\SystemSettingService::getActiveClinicNames();
assertTest("Active Clinic Names Extraction", is_array($activeClinicNames) && count($activeClinicNames) > 0);

// 13. Clinical Lab Alert Evaluator - Hyperglycemia Test
$dmLabAlerts = \App\Services\SmartAlertService::evaluateLabAlerts(['fbs' => 185.0, 'hba1c' => 8.2]);
assertTest("Clinical Lab Alert Evaluator (High FBS & HbA1c)", count($dmLabAlerts) >= 2);

// 14. Clinical Lab Alert Evaluator - Dyslipidemia Test
$lipidLabAlerts = \App\Services\SmartAlertService::evaluateLabAlerts(['cholesterol' => 250.0, 'triglyceride' => 320.0, 'ldl' => 145.0]);
assertTest("Clinical Lab Alert Evaluator (High Lipids)", count($lipidLabAlerts) === 3);

// 15. Clinical Lab Alert Evaluator - Hypoalbuminemia & Renal Risk Test
$renLabAlerts = \App\Services\SmartAlertService::evaluateLabAlerts(['albumin' => 2.4, 'egfr' => 45.0]);
assertTest("Clinical Lab Alert Evaluator (Low Albumin & eGFR Risk)", count($renLabAlerts) === 2);

// 16. Patient Keyword Search Test
$searchRes = $api->searchPatients('66000101');
assertTest("Patient Keyword Search Service", $searchRes['success'] === true && is_array($searchRes['data']));

// 17. Report Service - Monthly Summary Report Test
$summaryReport = \App\Services\ReportService::getMonthlySummary(date('Y-m'));
assertTest("Report Service (Monthly Summary)", is_array($summaryReport) && isset($summaryReport['compliance_pct']));

// 18. Report Service - Lab Abnormalities Report Test
$labReport = \App\Services\ReportService::getLabAbnormalitiesReport();
assertTest("Report Service (Lab Abnormalities Report)", is_array($labReport));

// 19. Report Service - Diet Orders Report Test
$dietReport = \App\Services\ReportService::getDietOrdersReport();
assertTest("Report Service (Diet Orders Summary Report)", is_array($dietReport));

// 20. Report Service - Nutrition Outcomes Report Test
$outcomesReport = \App\Services\ReportService::getOutcomesReport();
assertTest("Report Service (Nutrition Outcomes Report)", is_array($outcomesReport));

// 21. Heatmap Matrix Data Structure Test
$heatmapMatrix = \App\Services\SmartAlertService::getHeatmapMatrix();
assertTest("Clinical Risk Heatmap Matrix (DEFICIENCY & ELECTROLYTE Keys Exist)", isset($heatmapMatrix['DEFICIENCY']) && isset($heatmapMatrix['ELECTROLYTE']));

// 22. Multi-Visit Historical Lab Matrix & Trend Graph Service Test
$labHistRes = $api->getLabsHistory('66000101');
assertTest("Multi-Visit Historical Lab Matrix & Trend Graph Data Retrieval", $labHistRes['success'] === true && !empty($labHistRes['data']['dates']) && !empty($labHistRes['data']['matrix']));

echo "\n========================================================\n";
echo "TEST RESULTS SUMMARY:\n";
echo "PASSED: {$passCount} | FAILED: {$failCount}\n";
echo "========================================================\n";

exit($failCount > 0 ? 1 : 0);
