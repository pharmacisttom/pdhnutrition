<?php
/**
 * PDH Nutrition System - Clear Mock Data Script
 * Clears all mock patients, visits, diagnoses, labs, assessments, diet orders, notes, tasks, and registry records
 * Preserves roles, users, naf_rules, and system_settings.
 */

require_once __DIR__ . '/../config.php';

echo "========================================================\n";
echo "PDH Nutrition System - Clearing Mock Patient Data\n";
echo "========================================================\n\n";

$tablesToClear = [
    'patients_cache',
    'visits_cache',
    'admissions_cache',
    'diagnosis_cache',
    'lab_cache',
    'naf_answers',
    'naf_assessments',
    'anthropometric_records',
    'diet_oral_supplements',
    'diet_tube_feedings',
    'diet_orders',
    'nutrition_notes',
    'nutrition_tasks',
    'nutrition_registry',
    'audit_logs'
];

$databases = ['pdhnutrition_dev', 'pdhnutrition'];

foreach ($databases as $dbName) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname={$dbName};charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "Connecting to database [{$dbName}]...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        
        foreach ($tablesToClear as $table) {
            try {
                $pdo->exec("TRUNCATE TABLE `{$table}`");
                echo "  [ OK ] Cleared table: `{$table}`\n";
            } catch (Exception $ex) {
                echo "  [ NOTICE ] Could not truncate `{$table}`: " . $ex->getMessage() . "\n";
            }
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        echo "Successfully cleared all mock data from [{$dbName}]!\n\n";

    } catch (Exception $e) {
        echo "  [ SKIP ] Database [{$dbName}] not available or error: " . $e->getMessage() . "\n\n";
    }
}

echo "========================================================\n";
echo "CLEAR MOCK DATA COMPLETED!\n";
echo "Now the system will fetch live patient data from HIMPRO Gateway.\n";
echo "========================================================\n";
