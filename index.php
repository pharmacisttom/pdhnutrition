<?php
/**
 * PDH Nutrition System - Front Controller & Lean Router
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

// 2. Environment Initialization
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
\App\Config\AppConfig::load();
date_default_timezone_set(\App\Config\AppConfig::get('TIMEZONE', 'Asia/Bangkok'));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Request Normalization
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
    $uri = substr($uri, strlen($scriptName));
}
$uri = '/' . trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 4. Static O(1) Route Map
$staticRoutes = [
    'GET' => [
        '/'                      => [\App\Controllers\AuthController::class, 'showLogin'],
        '/login'                 => [\App\Controllers\AuthController::class, 'showLogin'],
        '/logout'                => [\App\Controllers\AuthController::class, 'logout'],
        '/dashboard'             => [\App\Controllers\DashboardController::class, 'index'],
        '/api/dashboard/analytics'=> [\App\Controllers\DashboardController::class, 'getAnalyticsData'],
        '/patients/search'       => [\App\Controllers\PatientController::class, 'search'],
        '/patients/today'        => [\App\Controllers\PatientController::class, 'todayVisits'],
        '/alerts'                => [\App\Controllers\AlertController::class, 'index'],
        '/alerts/heatmap'        => [\App\Controllers\AlertController::class, 'heatmap'],
        '/nutrition-queue'       => [\App\Controllers\NutritionQueueController::class, 'index'],
        '/naf/create'            => [\App\Controllers\NafController::class, 'create'],
        '/diet/create'           => [\App\Controllers\DietOrderController::class, 'create'],
        '/notes/create'          => [\App\Controllers\ClinicalNoteController::class, 'create'],
        '/registry'              => [\App\Controllers\RegistryController::class, 'index'],
        '/reports'               => [\App\Controllers\ReportController::class, 'index'],
        '/admin/users'           => [\App\Controllers\AdminController::class, 'users'],
        '/admin/settings'        => [\App\Controllers\AdminController::class, 'settings'],
        '/admin/clinics'         => [\App\Controllers\AdminController::class, 'clinics'],
        '/admin/rules'           => [\App\Controllers\AdminController::class, 'rules'],
        '/admin/audit-log'       => [\App\Controllers\AdminController::class, 'auditLog'],
        '/admin/api-test'        => [\App\Controllers\ApiTestController::class, 'index'],
        '/api/test-endpoint'     => [\App\Controllers\ApiTestController::class, 'testEndpoint'],
    ],
    'POST' => [
        '/login/submit'          => [\App\Controllers\AuthController::class, 'login'],
        '/alerts/broadcast'      => [\App\Controllers\AlertController::class, 'broadcast'],
        '/naf/store'             => [\App\Controllers\NafController::class, 'store'],
        '/diet/store'            => [\App\Controllers\DietOrderController::class, 'store'],
        '/notes/store'           => [\App\Controllers\ClinicalNoteController::class, 'store'],
        '/registry/store'        => [\App\Controllers\RegistryController::class, 'store'],
        '/admin/users/create'    => [\App\Controllers\AdminController::class, 'createUser'],
        '/admin/settings/save'   => [\App\Controllers\AdminController::class, 'saveSettings'],
    ]
];

// Dynamic Parameterized Routes Regex List
$dynamicRoutes = [
    'GET' => [
        '#^/patient/([A-Za-z0-9]+)$#'            => [\App\Controllers\PatientController::class, 'profile'],
        '#^/naf/show/(\d+)$#'                    => [\App\Controllers\NafController::class, 'show'],
        '#^/naf/print/(\d+)$#'                   => [\App\Controllers\NafController::class, 'printView'],
        '#^/diet/print/(\d+)$#'                  => [\App\Controllers\DietOrderController::class, 'printView'],
        '#^/reports/([a-z_]+)$#'                 => [\App\Controllers\ReportController::class, 'show'],
    ],
    'POST' => [
        '#^/nutrition-queue/start/(\d+)$#'       => [\App\Controllers\NutritionQueueController::class, 'startAssessment'],
        '#^/nutrition-queue/complete/(\d+)$#'    => [\App\Controllers\NutritionQueueController::class, 'completeTask'],
        '#^/registry/toggle/(\d+)$#'             => [\App\Controllers\RegistryController::class, 'toggleStatus'],
    ]
];

// 5. Dispatch Request
try {
    // Check static routes first for fast lookup
    if (isset($staticRoutes[$method][$uri])) {
        [$class, $action] = $staticRoutes[$method][$uri];
        (new $class())->$action();
        exit;
    }

    // Check dynamic routes if static route not matched
    if (isset($dynamicRoutes[$method])) {
        foreach ($dynamicRoutes[$method] as $pattern => $target) {
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // remove full match
                [$class, $action] = $target;
                call_user_func_array([new $class(), $action], $matches);
                exit;
            }
        }
    }

    // Route not found
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>ไม่พบหน้าที่ท่านต้องการ ({$uri})</p>";
} catch (\Throwable $t) {
    http_response_code(500);
    echo "<h1>500 System Exception</h1><p>" . htmlspecialchars($t->getMessage()) . "</p>";
    if (\App\Config\AppConfig::get('APP_DEBUG', 'false') === 'true') {
        echo "<pre>" . htmlspecialchars($t->getTraceAsString()) . "</pre>";
    }
}
