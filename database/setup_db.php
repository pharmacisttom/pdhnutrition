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

function executeSqlScript(PDO $pdo, string $filePath, bool $isSqlite = false): void {
    $rawSql = file_get_contents($filePath);
    if ($isSqlite) {
        $rawSql = str_replace(['AUTO_INCREMENT PRIMARY KEY', 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;', 'DATETIME NULL ON UPDATE CURRENT_TIMESTAMP'], ['PRIMARY KEY AUTOINCREMENT', ';', 'DATETIME NULL'], $rawSql);
    }
    
    // Remove multi-line comments and single-line comments
    $lines = explode("\n", $rawSql);
    $cleanedSql = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, 'USE ') === 0) {
            continue;
        }
        $cleanedSql .= $line . "\n";
    }

    $statements = array_filter(array_map('trim', explode(';', $cleanedSql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
            } catch (Exception $e) {
                echo "Warning executing statement: " . substr($stmt, 0, 50) . "... Error: " . $e->getMessage() . "\n";
            }
        }
    }
}

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
        // Connect to MySQL server
        $pdoServer = new PDO("mysql:host=$dbHost;port=$dbPort;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database `$dbName` verified/created successfully.\n";

        // Connect to the target DB
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "Connected to MySQL database `$dbName` successfully.\n";
    }

    // Run Schema SQL
    echo "Executing schema.sql...\n";
    executeSqlScript($pdo, __DIR__ . '/schema.sql', $dbDriver === 'sqlite');
    echo "Schema executed.\n";

    // Run Seeders SQL
    echo "Executing seeders.sql...\n";
    executeSqlScript($pdo, __DIR__ . '/seeders.sql', $dbDriver === 'sqlite');
    echo "Seeders executed.\n";
    echo "\nSetup completed successfully!\n";

} catch (PDOException $e) {
    echo "ERROR: Database setup failed: " . $e->getMessage() . "\n";
}
