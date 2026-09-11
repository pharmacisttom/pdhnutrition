<?php
/**
 * PDH Nutrition System - Global Configuration File
 * Pluakdaeng Hospital Clinical Nutrition Management System
 * Supports both XAMPP Localhost and Intranet Server (192.168.111.240)
 */

$serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$serverIp   = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';

// Auto-detect environment based on Host Header or Server IP
if (strpos($serverHost, '192.168.111.240') !== false || $serverIp === '192.168.111.240') {
    // ========================================================
    // Intranet Server Environment (192.168.111.240)
    // ========================================================
    defined('APP_ENV') || define('APP_ENV', 'production');
    defined('APP_URL') || define('APP_URL', 'http://192.168.111.240/pdhnutrition');
    defined('DB_HOST') || define('DB_HOST', 'localhost');
    defined('DB_PORT') || define('DB_PORT', '3306');
    defined('DB_NAME') || define('DB_NAME', 'pdhnutrition');
    defined('DB_USER') || define('DB_USER', 'root');
    defined('DB_PASS') || define('DB_PASS', '');
    defined('HIS_DRIVER') || define('HIS_DRIVER', 'himpro');
} else {
    // ========================================================
    // Local Development Environment (XAMPP Localhost)
    // ========================================================
    defined('APP_ENV') || define('APP_ENV', 'development');
    defined('APP_URL') || define('APP_URL', 'http://localhost/pdhnutrition');
    defined('DB_HOST') || define('DB_HOST', 'localhost');
    defined('DB_PORT') || define('DB_PORT', '3306');
    defined('DB_NAME') || define('DB_NAME', 'pdhnutrition_dev');
    defined('DB_USER') || define('DB_USER', 'root');
    defined('DB_PASS') || define('DB_PASS', '');
    defined('HIS_DRIVER') || define('HIS_DRIVER', 'himpro');
}

// PDH API Gateway Config
defined('PDH_API_BASE_URL') || define('PDH_API_BASE_URL', 'http://192.168.111.240/pdhapi');
defined('PDH_API_KEY') || define('PDH_API_KEY', 'pdh_secret_key_2026');
defined('TIMEZONE') || define('TIMEZONE', 'Asia/Bangkok');
