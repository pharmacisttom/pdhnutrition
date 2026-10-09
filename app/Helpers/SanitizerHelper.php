<?php
namespace App\Helpers;

class SanitizerHelper {
    public static function escape(?string $value): string {
        if ($value === null) return '';
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function maskCid(?string $cid): string {
        if (empty($cid)) return '-';
        $cleaned = preg_replace('/[^0-9]/', '', $cid);
        if (strlen($cleaned) !== 13) {
            return htmlspecialchars($cid, ENT_QUOTES, 'UTF-8');
        }
        // Full formatted CID: 1-2345-67890-12-3 (Unmasked for clinical staff)
        $part1 = substr($cleaned, 0, 1);
        $part2 = substr($cleaned, 1, 4);
        $part3 = substr($cleaned, 5, 5);
        $part4 = substr($cleaned, 10, 2);
        $part5 = substr($cleaned, 12, 1);
        return "{$part1}-{$part2}-{$part3}-{$part4}-{$part5}";
    }

    public static function cleanInput(array $data): array {
        $cleaned = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = self::cleanInput($value);
            } elseif (is_string($value)) {
                $cleaned[$key] = trim($value);
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }

    public static function normalizeGender(?string $rawGender): string {
        if ($rawGender === null || $rawGender === '') return 'MALE';
        $g = strtoupper(trim($rawGender));
        if ($g === 'FEMALE' || $g === 'F' || $g === 'หญิง' || $g === '2' || $g === 'SX2') {
            return 'FEMALE';
        }
        if ($g === 'OTHER' || $g === '3') {
            return 'OTHER';
        }
        return 'MALE';
    }

    public static function formatGender(?string $gender): string {
        if (empty($gender)) return '-';
        $g = strtoupper(trim($gender));
        if ($g === 'FEMALE' || $g === 'หญิง') return 'หญิง';
        if ($g === 'OTHER') return 'อื่นๆ';
        return 'ชาย';
    }
}
