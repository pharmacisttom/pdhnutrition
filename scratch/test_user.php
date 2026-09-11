<?php
require __DIR__ . '/../config.php';
require __DIR__ . '/../app/Config/AppConfig.php';
require __DIR__ . '/../app/Config/Database.php';

use App\Config\AppConfig;
use App\Config\Database;

AppConfig::load();

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, password_hash, fullname, role, status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "USERS COUNT: " . count($users) . "\n";
    print_r($users);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
