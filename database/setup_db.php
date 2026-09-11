<?php
/**
 * Database Setup Script for PDH Nutrition System
 * Can be run from CLI: php database/setup_db.php
 */

require_header();

function require_header() {
    echo "========================================================\n";
    echo "PDH Nutrition System - Database Setup Script\n";
    echo "========================================================\n\n";
}

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

$dbDriver = $_ENV['DB_DRIVER'] ?? 'mysql';
$dbHost   = $_ENV['DB_HOST'] ?? 'localhost';
$dbPort   = $_ENV['DB_PORT'] ?? '3306';
$dbName   = $_ENV['DB_DATABASE'] ?? 'pdhnutrition_dev';
$dbUser   = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass   = $_ENV['DB_PASSWORD'] ?? '';

echo "Connecting to $dbDriver host [$dbHost]...\n";

try {
    if ($dbDriver === 'sqlite') {
        $dbPath = __DIR__ . '/../storage/' . $dbName . '.sqlite';
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0777, true);
        }
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected to SQLite database file [$dbPath] successfully.\n";
    } else {
        // First connect without DB name to create DB
        $pdoServer = new PDO("mysql:host=$dbHost;port=$dbPort;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database `$dbName` verified/created successfully.\n";

        // Connect to the specific DB
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "Connected to MySQL database `$dbName` successfully.\n";
    }

    // Run Schema SQL
    echo "Executing schema.sql...\n";
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    if ($dbDriver === 'sqlite') {
        // Strip MySQL specific syntax if SQLite
        $schemaSql = str_replace(['AUTO_INCREMENT PRIMARY KEY', 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;', 'DATETIME NULL ON UPDATE CURRENT_TIMESTAMP'], ['PRIMARY KEY AUTOINCREMENT', ';', 'DATETIME NULL'], $schemaSql);
    }
    
    $statements = array_filter(array_map('trim', explode(';', $schemaSql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt) && strpos($stmt, '--') !== 0) {
            try {
                $pdo->exec($stmt);
            } catch (Exception $e) {
                // Ignore drop table errors if not exists
            }
        }
    }
    echo "Schema created successfully.\n";

    // Run Seeders SQL
    echo "Executing seeders.sql...\n";
    $seedersSql = file_get_contents(__DIR__ . '/seeders.sql');
    $seederStatements = array_filter(array_map('trim', explode(';', $seedersSql)));
    foreach ($seederStatements as $stmt) {
        if (!empty($stmt) && strpos($stmt, '--') !== 0 && strpos($stmt, 'USE ') !== 0) {
            try {
                $pdo->exec($stmt);
            } catch (Exception $e) {
                echo "Warning on seeder statement: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Seeders executed successfully.\n";
    echo "\nSetup completed successfully!\n";

} catch (PDOException $e) {
    echo "ERROR: Database setup failed: " . $e->getMessage() . "\n";
    echo "Note: If MySQL server is not running on XAMPP, please start MySQL service in XAMPP Control Panel.\n";
}
