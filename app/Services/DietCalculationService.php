<?php
namespace App\Services;

class DietCalculationService {
    public static function calculateIbw(float $heightCm, string $gender = 'MALE'): float {
        if ($heightCm <= 0) return 0.0;
        if (strtoupper($gender) === 'FEMALE') {
            return max(0.0, round($heightCm - 105, 2));
        }
        return max(0.0, round($heightCm - 100, 2));
    }

    public static function calculateEnergy(float $ibwKg, string|float $kcalFactor): float {
        $factor = is_numeric($kcalFactor) ? (float)$kcalFactor : 30.0;
        return round($ibwKg * $factor, 2);
    }

    public static function calculateProtein(float $ibwKg, string|float $proteinFactor): float {
        $factor = is_numeric($proteinFactor) ? (float)$proteinFactor : 1.2;
        return round($ibwKg * $factor, 2);
    }

    public static function computeFullDietPlan(array $input): array {
        $heightCm = (float)($input['height_cm'] ?? 160);
        $gender   = strtoupper($input['gender'] ?? 'MALE');
        $ibw      = self::calculateIbw($heightCm, $gender);

        $kcalFactor    = $input['energy_kcal_per_ibw'] ?? '30';
        $proteinFactor = $input['protein_g_per_ibw'] ?? '1.2';

        $totalEnergy  = self::calculateEnergy($ibw, $kcalFactor);
        $totalProtein = self::calculateProtein($ibw, $proteinFactor);

        return [
            'height_cm' => $heightCm,
            'gender' => $gender,
            'ibw_kg' => $ibw,
            'energy_kcal_per_ibw' => $kcalFactor,
            'total_energy_kcal' => $totalEnergy,
            'protein_g_per_ibw' => $proteinFactor,
            'total_protein_g' => $totalProtein
        ];
    }
}
