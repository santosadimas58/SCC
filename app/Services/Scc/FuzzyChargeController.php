<?php

namespace App\Services\Scc;

class FuzzyChargeController
{
    public const BULK_TARGET = 14.4;
    public const ABSORPTION_TARGET = 14.4;
    public const FLOAT_TARGET = 13.6;

    private const OUTPUT_VALUES = [
        'NB' => 5.0,
        'NS' => 22.0,
        'ZO' => 45.0,
        'PS' => 70.0,
        'PB' => 92.0,
    ];

    private const RULES = [
        'NB' => ['NB' => 'NB', 'NS' => 'NB', 'ZO' => 'NB', 'PS' => 'NS', 'PB' => 'ZO'],
        'NS' => ['NB' => 'NB', 'NS' => 'NS', 'ZO' => 'NS', 'PS' => 'ZO', 'PB' => 'PS'],
        'ZO' => ['NB' => 'NS', 'NS' => 'NS', 'ZO' => 'ZO', 'PS' => 'PS', 'PB' => 'PS'],
        'PS' => ['NB' => 'NS', 'NS' => 'ZO', 'ZO' => 'PS', 'PS' => 'PS', 'PB' => 'PB'],
        'PB' => ['NB' => 'ZO', 'NS' => 'PS', 'ZO' => 'PB', 'PS' => 'PB', 'PB' => 'PB'],
    ];

    public static function rules(): array
    {
        return self::RULES;
    }

    public static function outputValues(): array
    {
        return self::OUTPUT_VALUES;
    }

    public function evaluate(array $input, ?float $previousError = null): array
    {
        $vpv = (float) $input['vpv'];
        $vbat = (float) $input['vbat'];
        $soc = $this->clamp((float) $input['soc'], 0.0, 100.0);
        $phase = $this->phase($vpv, $vbat, $soc);
        $target = $this->targetVoltage($phase, $vbat);
        $error = $target - $vbat;
        $deltaError = $previousError === null ? 0.0 : $error - $previousError;
        $labelE = $this->labelError($error);
        $labelDe = $this->labelDeltaError($deltaError);
        $outputLabel = self::RULES[$labelE][$labelDe];
        $dutyCycle = $this->dutyCycle($phase, $outputLabel, $vpv, $vbat);

        return [
            'vpv' => $vpv,
            'ipv' => max(0.0, (float) $input['ipv']),
            'vbat' => $vbat,
            'ibat' => max(0.0, (float) $input['ibat']),
            'soc' => $soc,
            'duty_cycle' => round($dutyCycle, 2),
            'fase' => $phase,
            'label_e' => $labelE,
            'label_de' => $labelDe,
        ];
    }

    private function phase(float $vpv, float $vbat, float $soc): string
    {
        if ($vpv < 15.0 || $vpv < $vbat + 1.0) {
            return 'Standby';
        }

        if ($soc >= 95.0 || $vbat >= 14.35) {
            return 'Float';
        }

        if ($soc >= 80.0 || $vbat >= 14.10) {
            return 'Absorption';
        }

        return 'Bulk';
    }

    private function targetVoltage(string $phase, float $vbat): float
    {
        return match ($phase) {
            'Bulk' => self::BULK_TARGET,
            'Absorption' => self::ABSORPTION_TARGET,
            'Float' => self::FLOAT_TARGET,
            default => $vbat,
        };
    }

    private function labelError(float $error): string
    {
        return match (true) {
            $error <= -0.60 => 'NB',
            $error <= -0.20 => 'NS',
            $error < 0.20 => 'ZO',
            $error < 0.60 => 'PS',
            default => 'PB',
        };
    }

    private function labelDeltaError(float $deltaError): string
    {
        return match (true) {
            $deltaError <= -0.20 => 'NB',
            $deltaError <= -0.05 => 'NS',
            $deltaError < 0.05 => 'ZO',
            $deltaError < 0.20 => 'PS',
            default => 'PB',
        };
    }

    private function dutyCycle(string $phase, string $outputLabel, float $vpv, float $vbat): float
    {
        if ($phase === 'Standby') {
            return 0.0;
        }

        $duty = self::OUTPUT_VALUES[$outputLabel];

        if ($phase === 'Absorption') {
            $duty *= 0.72;
        }

        if ($phase === 'Float') {
            $duty *= 0.38;
        }

        if ($vpv > 0.0) {
            $buckLimit = ($vbat / $vpv) * 100.0;
            $duty = min($duty, $buckLimit + 8.0);
        }

        return $this->clamp($duty, 0.0, 96.0);
    }

    private function clamp(float $value, float $minimum, float $maximum): float
    {
        return max($minimum, min($maximum, $value));
    }
}
