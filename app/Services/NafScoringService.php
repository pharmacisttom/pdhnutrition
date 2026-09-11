<?php
namespace App\Services;

use App\Config\Database;
use PDO;
use Exception;

class NafScoringService {
    public static function calculateBmi(?float $weightKg, ?float $heightCm): ?float {
        if (empty($weightKg) || empty($heightCm) || $heightCm <= 0) {
            return null;
        }
        $heightM = $heightCm / 100.0;
        return round($weightKg / ($heightM * $heightM), 2);
    }

    public static function calculateBmiScore(?float $bmi): int {
        if ($bmi === null) return 0;
        if ($bmi < 17.0) return 2;
        if ($bmi >= 17.0 && $bmi <= 18.0) return 1;
        if ($bmi >= 18.1 && $bmi <= 29.9) return 0;
        if ($bmi >= 30.0) return 1;
        return 0;
    }

    public static function calculateAlbuminScore(?float $albumin): int {
        if ($albumin === null) return 0;
        if ($albumin < 2.5) return 3;
        if ($albumin >= 2.6 && $albumin <= 2.9) return 2;
        if ($albumin >= 3.0 && $albumin <= 3.5) return 1;
        if ($albumin > 3.5) return 0;
        return 0;
    }

    public static function calculateTlc(?float $wbc, ?float $lymphocytePct): ?float {
        if (empty($wbc) || $lymphocytePct === null) return null;
        return round(($wbc * $lymphocytePct) / 100.0, 2);
    }

    public static function calculateTlcScore(?float $tlc): int {
        if ($tlc === null) return 0;
        if ($tlc <= 1000) return 3;
        if ($tlc >= 1001 && $tlc <= 1200) return 2;
        if ($tlc >= 1201 && $tlc <= 1500) return 1;
        if ($tlc > 1500) return 0;
        return 0;
    }

    public static function translateGrade(int $totalScore): string {
        if ($totalScore <= 5) return 'NAF A';
        if ($totalScore <= 10) return 'NAF B';
        return 'NAF C';
    }

    public static function evaluateFullAssessment(array $input): array {
        $weightKg = !empty($input['weight_kg']) ? (float)$input['weight_kg'] : null;
        $heightCm = !empty($input['height_cm']) ? (float)$input['height_cm'] : null;
        $armSpan  = !empty($input['arm_span_cm']) ? (float)$input['arm_span_cm'] : null;

        // If height not measured, arm span can be used as height fallback
        $effectiveHeight = $heightCm ?: $armSpan;

        $bmi = self::calculateBmi($weightKg, $effectiveHeight);
        $bmiScore = self::calculateBmiScore($bmi);

        $albumin = !empty($input['albumin']) ? (float)$input['albumin'] : null;
        $albuminScore = self::calculateAlbuminScore($albumin);

        $wbc = !empty($input['wbc']) ? (float)$input['wbc'] : null;
        $lymphocyte = !empty($input['lymphocyte']) ? (float)$input['lymphocyte'] : null;
        $tlc = self::calculateTlc($wbc, $lymphocyte);
        $tlcScore = self::calculateTlcScore($tlc);

        $totalScore = $bmiScore + $albuminScore + $tlcScore;
        $answerItems = [];

        // Fetch Rule definitions from DB
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM naf_rules WHERE active = 1 AND version = 1");
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ruleMap = [];
        foreach ($rules as $r) {
            $ruleMap[$r['item_code']] = $r;
        }

        // Process selected options
        $selectedItems = $input['items'] ?? [];
        if (is_array($selectedItems)) {
            foreach ($selectedItems as $itemCode) {
                if (isset($ruleMap[$itemCode])) {
                    $score = (int)$ruleMap[$itemCode]['score'];
                    $totalScore += $score;
                    $answerItems[] = [
                        'section_number' => $ruleMap[$itemCode]['section'],
                        'item_code' => $itemCode,
                        'score_given' => $score,
                        'user_confirmed' => 1
                    ];
                }
            }
        }

        $grade = self::translateGrade($totalScore);

        return [
            'weight_kg' => $weightKg,
            'height_cm' => $heightCm,
            'arm_span_cm' => $armSpan,
            'effective_height_cm' => $effectiveHeight,
            'bmi' => $bmi,
            'bmi_score' => $bmiScore,
            'albumin' => $albumin,
            'albumin_score' => $albuminScore,
            'wbc' => $wbc,
            'lymphocyte' => $lymphocyte,
            'tlc' => $tlc,
            'tlc_score' => $tlcScore,
            'total_score' => $totalScore,
            'naf_grade' => $grade,
            'rule_version' => 1,
            'answers' => $answerItems
        ];
    }
}
