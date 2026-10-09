<?php
/**
 * PDH Nutrition System - Global Configuration File
 * Pluakdaeng Hospital Clinical Nutrition Management System
 * Matches exact PDHTelemed / PDHTawan Database Connection Schema
 */

$serverIps = ['192.168.111.240'];
$currentHost = $_SERVER['HTTP_HOST'] ?? '';
$currentServerAddr = $_SERVER['SERVER_ADDR'] ?? ($_SERVER['LOCAL_ADDR'] ?? '');
$isServer = in_array($currentServerAddr, $serverIps, true) || strpos($currentHost, '192.168.111.240') === 0;

if ($isServer) {
    // ========================================================
    // Intranet Server Environment (192.168.111.240)
    // ========================================================
    defined('APP_ENV') || define('APP_ENV', 'production');
    defined('APP_URL') || define('APP_URL', 'http://192.168.111.240/pdhnutrition');
    defined('DB_HOST') || define('DB_HOST', 'localhost');
    defined('DB_PORT') || define('DB_PORT', '3306');
    defined('DB_NAME') || define('DB_NAME', 'pdhnutrition');
    defined('DB_USER') || define('DB_USER', 'webtomdb');
    defined('DB_PASS') || define('DB_PASS', '@TOM$DataBase10832');
    defined('HIS_DRIVER') || define('HIS_DRIVER', 'himpro');
} else {
    // ========================================================
    // Local Development Environment (XAMPP Localhost)
    // Connects to Server 240 DB via 'tomwebdbnavicat'
    // ========================================================
    defined('APP_ENV') || define('APP_ENV', 'development');
    defined('APP_URL') || define('APP_URL', 'http://localhost/pdhnutrition');
    defined('DB_HOST') || define('DB_HOST', '192.168.111.240');
    defined('DB_PORT') || define('DB_PORT', '3306');
    defined('DB_NAME') || define('DB_NAME', 'pdhnutrition');
    defined('DB_USER') || define('DB_USER', 'tomwebdbnavicat');
    defined('DB_PASS') || define('DB_PASS', '@TOM$NavicatDB10832');
    defined('HIS_DRIVER') || define('HIS_DRIVER', 'himpro');
}

// PDH API Gateway Config
defined('PDH_API_BASE_URL') || define('PDH_API_BASE_URL', 'http://192.168.111.240/pdhapi');
defined('PDH_API_KEY') || define('PDH_API_KEY', 'PDHAPI-CHANGE-THIS-KEY');
defined('TIMEZONE') || define('TIMEZONE', 'Asia/Bangkok');
