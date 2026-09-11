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
            $dbName   = defined('DB_NAME') ? DB_NAME : AppConfig::get('DB_DATABASE', 'pdhnutrition_dev');
            $user     = defined('DB_USER') ? DB_USER : AppConfig::get('DB_USERNAME', 'root');
            $password = defined('DB_PASS') ? DB_PASS : AppConfig::get('DB_PASSWORD', '');

            try {
                if ($driver === 'sqlite') {
                    $dbPath = __DIR__ . '/../../storage/' . $dbName . '.sqlite';
                    self::$instance = new PDO("sqlite:" . $dbPath);
                } else {
                    // Try Primary Connection
                    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $password, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::ATTR_TIMEOUT            => 5
                    ]);
                }
            } catch (PDOException $e) {
                // If primary host failed and host was localhost, attempt automatic intranet server fallback to 192.168.111.240 DB pdhnutrition
                if ($driver === 'mysql' && $host === 'localhost') {
                    try {
                        $fallbackDsn = "mysql:host=192.168.111.240;port={$port};dbname=pdhnutrition;charset=utf8mb4";
                        self::$instance = new PDO($fallbackDsn, 'root', '', [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                            PDO::ATTR_TIMEOUT            => 5
                        ]);
                        return self::$instance;
                    } catch (PDOException $fallbackErr) {
                        // Fallback also failed
                    }
                }
                throw new Exception("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
