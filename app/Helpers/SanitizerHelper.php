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
        // Format: 1-2345-XXXXX-XX-X
        $part1 = substr($cleaned, 0, 1);
        $part2 = substr($cleaned, 1, 4);
        $part5 = substr($cleaned, 12, 1);
        return "{$part1}-{$part2}-XXXXX-XX-{$part5}";
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
}
