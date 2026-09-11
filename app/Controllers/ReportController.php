<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\ReportService;
use App\Services\AuditService;
use App\Helpers\SanitizerHelper;

class ReportController {
    public function index(): void {
        AuthMiddleware::check();
        AuditService::log('VIEW_REPORTS_INDEX', 'REPORTS');
        require __DIR__ . '/../../views/reports/index.php';
    }

    public function show(string $reportCode): void {
        AuthMiddleware::check();
        $date  = SanitizerHelper::escape($_GET['date'] ?? date('Y-m-d'));
        $month = SanitizerHelper::escape($_GET['month'] ?? date('Y-m'));
        $grade = SanitizerHelper::escape($_GET['grade'] ?? '');
        $hn    = SanitizerHelper::escape($_GET['hn'] ?? '');
        $format = SanitizerHelper::escape($_GET['export'] ?? '');

        $reportTitle = "รายงานด้านโภชนาการ";
        $reportData = [];

        switch ($reportCode) {
            case 'pre_doctor':
                $reportTitle = "รายงานผู้ป่วยที่ต้องได้รับการประเมินด้านโภชนาการก่อนพบแพทย์";
                $reportData = ReportService::getPreDoctorReport(['date' => $date]);
                break;
            case 'pending':
                $reportTitle = "รายงานผู้ป่วยค้างประเมินโภชนาการ";
                $reportData = ReportService::getPendingReport();
                break;
            case 'daily':
                $reportTitle = "รายงานการประเมินโภชนาการประจำวัน ({$date})";
                $reportData = ReportService::getDailyReport($date);
                break;
            case 'naf_classification':
                $reportTitle = "รายงานแยกตามระดับความเสี่ยง NAF (A / B / C)";
                $reportData = ReportService::getNafClassificationReport($grade, $month);
                break;
            case 'high_risk':
                $reportTitle = "รายงานผู้ป่วยกลุ่มความเสี่ยงสูง (High Risk Severe Malnutrition)";
                $reportData = ReportService::getHighRiskReport();
                break;
            case 'overdue':
                $reportTitle = "รายงานผู้ป่วยเลยกำหนดติดตามโภชนาการ (Follow-up Overdue)";
                $reportData = ReportService::getOverdueReport();
                break;
            case 'patient_history':
                $reportTitle = "รายงานประวัติการประเมินโภชนาการผู้ป่วยรายบุคคล (HN: {$hn})";
                $reportData = ReportService::getPatientTimeline($hn);
                break;
            case 'monthly_summary':
                $reportTitle = "รายงานสรุปการดำเนินงานโภชนาการประจำเดือน ({$month})";
                $reportData = ReportService::getMonthlySummary($month);
                break;
            case 'clinic_breakdown':
                $reportTitle = "รายงานสรุปงานโภชนาการจำแนกตามแผนก/คลินิก ({$month})";
                $reportData = ReportService::getClinicReport($month);
                break;
            case 'dietitian_workload':
                $reportTitle = "รายงานภาระงานนักโภชนาการ ({$month})";
                $reportData = ReportService::getDietitianWorkload($month);
                break;
            case 'lab_abnormalities':
                $reportTitle = "รายงานผู้ป่วยที่มีผล LAB ผิดปกติทางโภชนาการ (Clinical Lab Abnormalities)";
                $reportData = ReportService::getLabAbnormalitiesReport();
                break;
            case 'diet_orders_summary':
                $reportTitle = "รายงานสรุปการสั่งโภชนบำบัดและประเภทอาหารผู้ป่วย (Diet Orders Summary)";
                $reportData = ReportService::getDietOrdersReport();
                break;
            case 'nutrition_outcomes':
                $reportTitle = "รายงานผลการรักษาและแนวโน้มการเปลี่ยนแปลงโภชนาการ (Nutrition Outcomes)";
                $reportData = ReportService::getOutcomesReport();
                break;
            default:
                http_response_code(404);
                echo "<h1>404 Not Found</h1><p>ไม่พบรายงานรหัส: " . htmlspecialchars($reportCode) . "</p>";
                exit;
        }

        AuditService::log('VIEW_REPORT', 'REPORTS', $reportCode, $hn);

        if ($format === 'csv' || $format === 'excel') {
            AuditService::log('EXPORT_REPORT', 'REPORTS', $reportCode, $hn, null, null, ['format' => $format]);
            $headers = !empty($reportData) ? array_keys(reset($reportData)) : ['Data'];
            ReportService::exportCsv($reportData, $headers, "report_{$reportCode}_" . date('Ymd_His'));
            return;
        }

        require __DIR__ . '/../../views/reports/report_detail.php';
    }
}
