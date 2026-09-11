<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\PdhApiService;
use App\Helpers\ResponseHelper;
use App\Helpers\SanitizerHelper;

class ApiTestController {
    public function index(): void {
        AuthMiddleware::check();
        $api = new PdhApiService();
        $todayVisits = $api->getTodayVisits();
        $samplePatient = $api->getPatient('66000101');
        $sampleLabs = $api->getLatestLabs('66000101');

        require __DIR__ . '/../../views/admin/api_test.php';
    }

    public function testEndpoint(): void {
        AuthMiddleware::check();
        $endpoint = SanitizerHelper::escape($_GET['endpoint'] ?? '');
        $param = SanitizerHelper::escape($_GET['param'] ?? '66000101');

        $api = new PdhApiService();
        $result = [];

        switch ($endpoint) {
            case 'patient':
                $result = $api->getPatient($param);
                break;
            case 'visits':
                $result = $api->getTodayVisits();
                break;
            case 'labs':
                $result = $api->getLatestLabs($param);
                break;
            case 'diagnosis':
                $result = $api->getDiagnosis($param);
                break;
            case 'allergies':
                $result = $api->getAllergies($param);
                break;
            default:
                ResponseHelper::json(['success' => false, 'message' => 'Unknown endpoint'], 400);
        }

        ResponseHelper::json($result);
    }
}
