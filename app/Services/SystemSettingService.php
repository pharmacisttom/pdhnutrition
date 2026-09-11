<?php
namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class SystemSettingService {
    private static ?array $cache = null;

    /**
     * Get setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed {
        if (self::$cache === null) {
            self::loadAll();
        }
        return self::$cache[$key] ?? $default;
    }

    /**
     * Get all system settings
     */
    public static function getAll(): array {
        if (self::$cache === null) {
            self::loadAll();
        }
        return self::$cache;
    }

    /**
     * Load all settings from DB into memory cache
     */
    private static function loadAll(): void {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            self::$cache = $rows ?: [];
        } catch (Exception $e) {
            self::$cache = [];
        }
    }

    /**
     * Save/Update settings key-value pair array
     */
    public static function setMany(array $settings): bool {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value, updated_at)
                VALUES (:k, :v, NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
            ");

            foreach ($settings as $key => $val) {
                $stmt->execute([
                    'k' => $key,
                    'v' => is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)$val
                ]);
                self::$cache[$key] = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : (string)$val;
            }
            return true;
        } catch (Exception $e) {
            error_log("SystemSettingService::setMany Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active clinics list
     */
    public static function getClinics(): array {
        $json = self::get('active_clinics_json');
        if (!empty($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Default initial clinics if not configured in DB yet
        $defaults = [
            ['id' => 'c1', 'name' => 'คลินิกเบาหวาน (NCD)', 'code' => 'NCD_DM', 'active' => true, 'department' => 'OPD'],
            ['id' => 'c2', 'name' => 'คลินิกโรคเรื้อรัง (NCD)', 'code' => 'NCD_CHRONIC', 'active' => true, 'department' => 'OPD'],
            ['id' => 'c3', 'name' => 'คลินิกผู้ป่วยใน (IPD)', 'code' => 'IPD', 'active' => true, 'department' => 'IPD'],
            ['id' => 'c4', 'name' => 'คลินิกโรคไต (CKD)', 'code' => 'CKD', 'active' => true, 'department' => 'OPD'],
            ['id' => 'c5', 'name' => 'คลินิกความดันโลหิตสูง', 'code' => 'HT', 'active' => true, 'department' => 'OPD'],
            ['id' => 'c6', 'name' => 'คลินิกผู้สูงอายุ', 'code' => 'GERIATRIC', 'active' => true, 'department' => 'OPD'],
            ['id' => 'c7', 'name' => 'คลินิกโภชนาการ', 'code' => 'NUTRITION', 'active' => true, 'department' => 'OPD']
        ];
        return $defaults;
    }

    /**
     * Get only active clinic names as flat array
     */
    public static function getActiveClinicNames(): array {
        $clinics = self::getClinics();
        $active = [];
        foreach ($clinics as $c) {
            if (!empty($c['active'])) {
                $active[] = $c['name'];
            }
        }
        return $active;
    }
}
