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

// 10. API Offline / Fallback Test
$api = new PdhApiService();
$patientRes = $api->getPatient('66000101');
assertTest("HIS API Service Response Format", $patientRes['success'] === true && !empty($patientRes['data']));

echo "\n========================================================\n";
echo "TEST RESULTS SUMMARY:\n";
echo "PASSED: {$passCount} | FAILED: {$failCount}\n";
echo "========================================================\n";

exit($failCount > 0 ? 1 : 0);
