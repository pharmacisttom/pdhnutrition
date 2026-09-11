<?php
namespace App\Helpers;

use DateTime;
use Exception;

class DateHelper {
    public static function formatThaiDate(?string $dateStr, bool $shortMonth = false): string {
        if (empty($dateStr) || $dateStr === '0000-00-00') return '-';
        try {
            $dt = new DateTime($dateStr);
            $year = (int)$dt->format('Y') + 543;
            $day = (int)$dt->format('d');
            $monthIndex = (int)$dt->format('m') - 1;

            $fullMonths = [
                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
            ];
            $shortMonths = [
                'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
            ];

            $monthText = $shortMonth ? $shortMonths[$monthIndex] : $fullMonths[$monthIndex];
            return "{$day} {$monthText} {$year}";
        } catch (Exception $e) {
            return $dateStr;
        }
    }

    public static function calculateAge(?string $birthdate): int {
        if (empty($birthdate)) return 0;
        try {
            $birth = new DateTime($birthdate);
            $today = new DateTime('today');
            return $birth->diff($today)->y;
        } catch (Exception $e) {
            return 0;
        }
    }
}
