<?php
namespace App\Services;

use App\Config\AppConfig;
use App\Config\Database;
use PDO;
use Exception;

class PdhApiService {
    private string $driver;
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct() {
        $this->driver  = AppConfig::get('HIS_DRIVER', 'himpro');
        $this->baseUrl = rtrim(AppConfig::get('PDH_API_BASE_URL', 'http://192.168.111.240/pdhapi'), '/');
        $this->apiKey  = AppConfig::get('PDH_API_KEY', '');
        $this->timeout = (int)AppConfig::get('PDH_API_TIMEOUT', 10);
    }

    public function getPatient(string $hn): array {
        if ($this->driver === 'mock') {
            return $this->getMockPatient($hn);
        }
        return $this->fetchFromGateway("/patient/{$hn}", 'patients_cache', 'hn', $hn);
    }

    public function getTodayVisits(): array {
        if ($this->driver === 'mock') {
            return $this->getMockTodayVisits();
        }
        return $this->fetchFromGateway("/visits/today", 'visits_cache');
    }

    public function getPatientVisits(string $hn): array {
        if ($this->driver === 'mock') {
            return $this->getMockPatientVisits($hn);
        }
        return $this->fetchFromGateway("/patient/{$hn}/visits", 'visits_cache', 'hn', $hn);
    }

    public function getCurrentAdmissions(): array {
        if ($this->driver === 'mock') {
            return $this->getMockCurrentAdmissions();
        }
        return $this->fetchFromGateway("/admissions/current", 'admissions_cache');
    }

    public function getAdmission(string $an): array {
        if ($this->driver === 'mock') {
            return $this->getMockAdmission($an);
        }
        return $this->fetchFromGateway("/admission/{$an}", 'admissions_cache', 'an', $an);
    }

    public function getDiagnosis(string $hnOrVn): array {
        if ($this->driver === 'mock') {
            return $this->getMockDiagnosis($hnOrVn);
        }
        return $this->fetchFromGateway("/diagnosis/{$hnOrVn}", 'diagnosis_cache', 'hn', $hnOrVn);
    }

    public function getLabs(string $hn): array {
        if ($this->driver === 'mock') {
            return $this->getMockLabs($hn);
        }
        return $this->fetchFromGateway("/lab/{$hn}", 'lab_cache', 'hn', $hn);
    }

    public function getLatestLabs(string $hn): array {
        if ($this->driver === 'mock') {
            return $this->getMockLatestLabs($hn);
        }
        return $this->fetchFromGateway("/lab/{$hn}/latest", 'lab_cache', 'hn', $hn);
    }

    public function getAppointments(string $hn): array {
        if ($this->driver === 'mock') {
            return [
                'success' => true,
                'source' => 'MOCK',
                'data' => [
                    ['appointment_date' => date('Y-m-d', strtotime('+14 days')), 'clinic' => 'คลินิกโภชนาการ', 'doctor' => 'นพ. สมชาย ใจดี', 'reason' => 'Follow-up NAF Assessment']
                ]
            ];
        }
        return $this->fetchFromGateway("/appointment/{$hn}", 'patients_cache', 'hn', $hn);
    }

    public function getMedications(string $hn): array {
        if ($this->driver === 'mock') {
            return [
                'success' => true,
                'source' => 'MOCK',
                'data' => [
                    ['med_name' => 'Metformin 500 mg', 'usage' => '1x2 pc', 'qty' => 60],
                    ['med_name' => 'Enalapril 5 mg', 'usage' => '1x1 pc', 'qty' => 30]
                ]
            ];
        }
        return $this->fetchFromGateway("/medications/{$hn}", 'patients_cache', 'hn', $hn);
    }

    public function getAllergies(string $hn): array {
        if ($this->driver === 'mock') {
            if ($hn === '66000103') {
                return [
                    'success' => true,
                    'has_allergy' => true,
                    'source' => 'HIS',
                    'data' => [
                        ['allergy_item' => 'Penicillin', 'symptom' => 'Rash & Urticaria', 'severity' => 'MODERATE']
                    ]
                ];
            }
            return [
                'success' => true,
                'has_allergy' => false,
                'source' => 'HIS',
                'data' => []
            ];
        }
        return $this->fetchFromGateway("/allergy/{$hn}", 'patients_cache', 'hn', $hn);
    }

    // ==========================================
    // GATEWAY HTTP CALL & CACHE FALLBACK LOGIC
    // ==========================================

    private function fetchFromGateway(string $endpoint, string $cacheTable, ?string $keyColumn = null, ?string $keyValue = null): array {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$this->apiKey}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && !empty($response)) {
            $json = json_decode($response, true);
            if (is_array($json)) {
                $payload = $json['data'] ?? $json;
                $this->updateLocalCache($cacheTable, $payload);
                return [
                    'success' => true,
                    'is_cached' => false,
                    'source' => 'PDH API Gateway (HIMPRO Live)',
                    'last_sync' => date('Y-m-d H:i:s'),
                    'data' => $payload
                ];
            }
        }

        // Fallback to local DB Cache if Gateway is unreachable
        return $this->getFromCache($cacheTable, $keyColumn, $keyValue, $curlError ?: "HTTP Status: {$httpCode}");
    }

    private function getFromCache(string $table, ?string $keyColumn, ?string $keyValue, string $errorReason): array {
        try {
            $pdo = Database::getConnection();
            if ($keyColumn && $keyValue) {
                $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$keyColumn} = :val");
                $stmt->execute(['val' => $keyValue]);
                $data = $stmt->fetchAll();
                if ($keyColumn === 'hn' && count($data) === 1) {
                    $data = $data[0];
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM {$table}");
                $data = $stmt->fetchAll();
            }

            return [
                'success' => true,
                'is_cached' => true,
                'source' => 'Database Cache (Offline/Gateway Error)',
                'last_sync' => date('Y-m-d H:i:s'),
                'warning' => "ไม่สามารถเชื่อมต่อ PDH API Gateway ได้ ({$errorReason}) แสดงข้อมูลล่าสุดจาก Cache",
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'is_cached' => false,
                'message' => "ไม่สามารถดึงข้อมูลได้: " . $e->getMessage(),
                'data' => []
            ];
        }
    }

    private function updateLocalCache(string $table, array $payload): void {
        try {
            $pdo = Database::getConnection();
            if ($table === 'patients_cache' && isset($payload['hn'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO patients_cache (hn, cid, prefix, first_name, last_name, fullname, gender, birthdate, age, weight, height, bmi, synced_at)
                    VALUES (:hn, :cid, :prefix, :fname, :lname, :fullname, :gender, :bdate, :age, :w, :h, :bmi, NOW())
                    ON DUPLICATE KEY UPDATE 
                        fullname = VALUES(fullname), weight = VALUES(weight), height = VALUES(height), bmi = VALUES(bmi), synced_at = NOW()
                ");
                $fname = $payload['first_name'] ?? $payload['fname'] ?? '';
                $lname = $payload['last_name'] ?? $payload['lname'] ?? '';
                $prefix = $payload['prefix'] ?? $payload['pname'] ?? '';
                $fullname = $payload['fullname'] ?? trim("{$prefix} {$fname} {$lname}");

                $stmt->execute([
                    'hn'       => $payload['hn'],
                    'cid'      => $payload['cid'] ?? $payload['idcard'] ?? null,
                    'prefix'   => $prefix,
                    'fname'    => $fname,
                    'lname'    => $lname,
                    'fullname' => $fullname,
                    'gender'   => strtoupper($payload['gender'] ?? 'MALE'),
                    'bdate'    => $payload['birthdate'] ?? null,
                    'age'      => (int)($payload['age'] ?? 0),
                    'w'        => (float)($payload['weight'] ?? 0),
                    'h'        => (float)($payload['height'] ?? 0),
                    'bmi'      => (float)($payload['bmi'] ?? 0)
                ]);
            }
        } catch (Exception $e) {
            // Log cache error silently
            error_log("updateLocalCache Error: " . $e->getMessage());
        }
    }

    // ==========================================
    // MOCK DRIVER IMPLEMENTATIONS
    // ==========================================

    private function getMockPatient(string $hn): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM patients_cache WHERE hn = :hn");
        $stmt->execute(['hn' => $hn]);
        $patient = $stmt->fetch();

        if ($patient) {
            return [
                'success' => true,
                'is_cached' => false,
                'source' => 'HIS (Mock)',
                'last_sync' => date('Y-m-d H:i:s'),
                'data' => $patient
            ];
        }

        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock Synthetic)',
            'last_sync' => date('Y-m-d H:i:s'),
            'data' => [
                'hn' => $hn,
                'cid' => '1-2199-' . rand(10000, 99999) . '-11-1',
                'prefix' => 'นาย',
                'first_name' => 'ผู้ป่วย',
                'last_name' => 'ทดสอบ (' . $hn . ')',
                'fullname' => 'นาย ผู้ป่วย ทดสอบ (' . $hn . ')',
                'gender' => 'MALE',
                'birthdate' => '1960-01-01',
                'age' => 66,
                'weight' => 50.0,
                'height' => 165.0,
                'bmi' => 18.37
            ]
        ];
    }

    private function getMockTodayVisits(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT v.*, p.fullname, p.age, p.gender, p.cid,
                   n.naf_grade as last_naf_grade, n.assessment_date as last_naf_date
            FROM visits_cache v
            JOIN patients_cache p ON v.hn = p.hn
            LEFT JOIN (
                SELECT hn, naf_grade, assessment_date,
                       ROW_NUMBER() OVER (PARTITION BY hn ORDER BY assessment_date DESC, id DESC) as rn
                FROM naf_assessments
            ) n ON v.hn = n.hn AND n.rn = 1
            WHERE v.visit_date = CURRENT_DATE()
            ORDER BY v.visit_time ASC
        ");
        $visits = $stmt->fetchAll();

        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'last_sync' => date('Y-m-d H:i:s'),
            'data' => $visits
        ];
    }

    private function getMockPatientVisits(string $hn): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM visits_cache WHERE hn = :hn ORDER BY visit_date DESC");
        $stmt->execute(['hn' => $hn]);
        $visits = $stmt->fetchAll();

        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'data' => $visits
        ];
    }

    private function getMockCurrentAdmissions(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("
            SELECT a.*, p.fullname, p.age, p.gender
            FROM admissions_cache a
            JOIN patients_cache p ON a.hn = p.hn
            WHERE a.status = 'ADMITTED'
        ");
        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'data' => $stmt->fetchAll()
        ];
    }

    private function getMockAdmission(string $an): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT a.*, p.fullname FROM admissions_cache a JOIN patients_cache p ON a.hn = p.hn WHERE a.an = :an");
        $stmt->execute(['an' => $an]);
        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'data' => $stmt->fetch() ?: []
        ];
    }

    private function getMockDiagnosis(string $hnOrVn): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM diagnosis_cache WHERE hn = :key OR vn = :key ORDER BY category ASC");
        $stmt->execute(['key' => $hnOrVn]);
        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'data' => $stmt->fetchAll()
        ];
    }

    private function getMockLabs(string $hn): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM lab_cache WHERE hn = :hn ORDER BY result_date DESC, result_time DESC");
        $stmt->execute(['hn' => $hn]);
        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'data' => $stmt->fetchAll()
        ];
    }

    private function getMockLatestLabs(string $hn): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM lab_cache 
            WHERE hn = :hn 
            ORDER BY result_date DESC, result_time DESC 
            LIMIT 5
        ");
        $stmt->execute(['hn' => $hn]);
        $labs = $stmt->fetchAll();

        $wbc = null;
        $lym = null;
        $alb = null;
        $labDate = null;

        foreach ($labs as $lab) {
            if ($lab['test_name'] === 'Albumin') $alb = (float)$lab['result_value'];
            if ($lab['test_name'] === 'WBC') $wbc = (float)$lab['result_value'];
            if ($lab['test_name'] === 'Lymphocyte') $lym = (float)$lab['result_value'];
            if (!$labDate) $labDate = $lab['result_date'] . ' ' . ($lab['result_time'] ?? '');
        }

        $tlc = ($wbc !== null && $lym !== null) ? round(($wbc * $lym) / 100, 2) : null;

        return [
            'success' => true,
            'is_cached' => false,
            'source' => 'HIS (Mock)',
            'data' => [
                'albumin' => $alb,
                'wbc' => $wbc,
                'lymphocyte' => $lym,
                'tlc' => $tlc,
                'result_datetime' => $labDate
            ]
        ];
    }
}
