<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Check if root config.php defines constants
            if (file_exists(__DIR__ . '/../../config.php')) {
                require_once __DIR__ . '/../../config.php';
            }

            $driver   = AppConfig::get('DB_DRIVER', 'mysql');
            $host     = defined('DB_HOST') ? DB_HOST : AppConfig::get('DB_HOST', 'localhost');
            $port     = defined('DB_PORT') ? DB_PORT : AppConfig::get('DB_PORT', '3306');
            $dbName   = defined('DB_NAME') ? DB_NAME : AppConfig::get('DB_DATABASE', 'pdhnutrition');
            $user     = defined('DB_USER') ? DB_USER : AppConfig::get('DB_USERNAME', 'webtomdb');
            $password = defined('DB_PASS') ? DB_PASS : AppConfig::get('DB_PASSWORD', '@TOM$DataBase10832');

            if ($driver === 'sqlite') {
                $dbPath = __DIR__ . '/../../storage/' . $dbName . '.sqlite';
                self::$instance = new PDO("sqlite:" . $dbPath);
                return self::$instance;
            }

            // Connection Attempts matching PDHTelemed Schema
            $attempts = [
                // 1. Primary Configured Credentials
                ['host' => $host, 'db' => $dbName, 'user' => $user, 'pass' => $password],
                // 2. Server 240 Local
                ['host' => 'localhost', 'db' => 'pdhnutrition', 'user' => 'webtomdb', 'pass' => '@TOM$DataBase10832'],
                ['host' => '127.0.0.1', 'db' => 'pdhnutrition', 'user' => 'webtomdb', 'pass' => '@TOM$DataBase10832'],
                // 3. Remote Navicat 240
                ['host' => '192.168.111.240', 'db' => 'pdhnutrition', 'user' => 'tomwebdbnavicat', 'pass' => '@TOM$NavicatDB10832'],
                // 4. Local Development Fallback (XAMPP)
                ['host' => 'localhost', 'db' => 'pdhnutrition_dev', 'user' => 'root', 'pass' => ''],
                ['host' => '127.0.0.1', 'db' => 'pdhnutrition_dev', 'user' => 'root', 'pass' => '']
            ];

            $lastException = null;

            foreach ($attempts as $conn) {
                try {
                    $dsn = "mysql:host={$conn['host']};port={$port};dbname={$conn['db']};charset=utf8mb4";
                    self::$instance = new PDO($dsn, $conn['user'], $conn['pass'], [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::ATTR_TIMEOUT            => 3
                    ]);
                    return self::$instance;
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }

            throw new Exception("Database Connection Error: " . ($lastException ? $lastException->getMessage() : "Unable to connect to MySQL"));
        }
        return self::$instance;
    }
}
