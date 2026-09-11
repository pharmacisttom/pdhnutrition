<?php
/**
 * PDH Nutrition System - Front Controller & Router
 * Pluakdaeng Hospital Clinical Nutrition Management System
 */

// 1. PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 2. Initialize App Config & Environment
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
\App\Config\AppConfig::load();
date_default_timezone_set(\App\Config\AppConfig::get('TIMEZONE', 'Asia/Bangkok'));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Simple Clean Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName));
}
$uri = '/' . trim($uri, '/');

// Dispatch Routes
try {
    if ($uri === '/' || $uri === '/login') {
        (new \App\Controllers\AuthController())->showLogin();
    } elseif ($uri === '/login/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\AuthController())->login();
    } elseif ($uri === '/logout') {
        (new \App\Controllers\AuthController())->logout();
    } elseif ($uri === '/dashboard') {
        (new \App\Controllers\DashboardController())->index();
    } elseif ($uri === '/api/dashboard/analytics') {
        (new \App\Controllers\DashboardController())->getAnalyticsData();
    } elseif ($uri === '/patients/search') {
        (new \App\Controllers\PatientController())->search();
    } elseif ($uri === '/alerts') {
        (new \App\Controllers\AlertController())->index();
    } elseif ($uri === '/alerts/heatmap') {
        (new \App\Controllers\AlertController())->heatmap();
    } elseif ($uri === '/alerts/broadcast' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\AlertController())->broadcast();
    } elseif ($uri === '/patients/today') {
        (new \App\Controllers\PatientController())->todayVisits();
    } elseif (preg_match('#^/patient/([A-Za-z0-9]+)$#', $uri, $matches)) {
        (new \App\Controllers\PatientController())->profile($matches[1]);
    } elseif ($uri === '/nutrition-queue') {
        (new \App\Controllers\NutritionQueueController())->index();
    } elseif (preg_match('#^/nutrition-queue/start/(\d+)$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\NutritionQueueController())->startAssessment((int)$matches[1]);
    } elseif (preg_match('#^/nutrition-queue/complete/(\d+)$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\NutritionQueueController())->completeTask((int)$matches[1]);
    } elseif ($uri === '/naf/create') {
        (new \App\Controllers\NafController())->create();
    } elseif ($uri === '/naf/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\NafController())->store();
    } elseif (preg_match('#^/naf/show/(\d+)$#', $uri, $matches)) {
        (new \App\Controllers\NafController())->show((int)$matches[1]);
    } elseif (preg_match('#^/naf/print/(\d+)$#', $uri, $matches)) {
        (new \App\Controllers\NafController())->printView((int)$matches[1]);
    } elseif ($uri === '/diet/create') {
        (new \App\Controllers\DietOrderController())->create();
    } elseif ($uri === '/diet/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\DietOrderController())->store();
    } elseif (preg_match('#^/diet/print/(\d+)$#', $uri, $matches)) {
        (new \App\Controllers\DietOrderController())->printView((int)$matches[1]);
    } elseif ($uri === '/notes/create') {
        (new \App\Controllers\ClinicalNoteController())->create();
    } elseif ($uri === '/notes/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\ClinicalNoteController())->store();
    } elseif ($uri === '/registry') {
        (new \App\Controllers\RegistryController())->index();
    } elseif ($uri === '/registry/store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\RegistryController())->store();
    } elseif (preg_match('#^/registry/toggle/(\d+)$#', $uri, $matches) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\RegistryController())->toggleStatus((int)$matches[1]);
    } elseif ($uri === '/reports') {
        (new \App\Controllers\ReportController())->index();
    } elseif (preg_match('#^/reports/([a-z_]+)$#', $uri, $matches)) {
        (new \App\Controllers\ReportController())->show($matches[1]);
    } elseif ($uri === '/admin/users') {
        (new \App\Controllers\AdminController())->users();
    } elseif ($uri === '/admin/users/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\AdminController())->createUser();
    } elseif ($uri === '/admin/settings') {
        (new \App\Controllers\AdminController())->settings();
    } elseif ($uri === '/admin/settings/save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new \App\Controllers\AdminController())->saveSettings();
    } elseif ($uri === '/admin/clinics') {
        (new \App\Controllers\AdminController())->clinics();
    } elseif ($uri === '/admin/rules') {
        (new \App\Controllers\AdminController())->rules();
    } elseif ($uri === '/admin/audit-log') {
        (new \App\Controllers\AdminController())->auditLog();
    } elseif ($uri === '/admin/api-test') {
        (new \App\Controllers\ApiTestController())->index();
    } elseif ($uri === '/api/test-endpoint') {
        (new \App\Controllers\ApiTestController())->testEndpoint();
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>ไม่พบหน้าที่ท่านต้องการ ({$uri})</p>";
    }
} catch (\Throwable $t) {
    http_response_code(500);
    echo "<h1>500 System Exception</h1><p>" . htmlspecialchars($t->getMessage()) . "</p>";
    if (\App\Config\AppConfig::get('APP_DEBUG', 'false') === 'true') {
        echo "<pre>" . htmlspecialchars($t->getTraceAsString()) . "</pre>";
    }
}
