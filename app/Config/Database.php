<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $driver   = AppConfig::get('DB_DRIVER', 'mysql');
            $host     = AppConfig::get('DB_HOST', 'localhost');
            $port     = AppConfig::get('DB_PORT', '3306');
            $dbName   = AppConfig::get('DB_DATABASE', 'pdhnutrition_dev');
            $user     = AppConfig::get('DB_USERNAME', 'root');
            $password = AppConfig::get('DB_PASSWORD', '');

            try {
                if ($driver === 'sqlite') {
                    $dbPath = __DIR__ . '/../../storage/' . $dbName . '.sqlite';
                    self::$instance = new PDO("sqlite:" . $dbPath);
                } else {
                    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                    self::$instance = new PDO($dsn, $user, $password, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                }
            } catch (PDOException $e) {
                throw new Exception("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
