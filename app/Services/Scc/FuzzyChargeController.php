<?php

namespace App\Services\Scc;

class FuzzyChargeController
{
    public const BULK_TARGET = 14.4;

    public const ABSORPTION_TARGET = 14.4;

    public const FLOAT_TARGET = 13.6;

    private const LABELS = ['NB', 'NS', 'ZO', 'PS', 'PB'];

    private const OUTPUT_VALUES = [
        'NB' => 10.0,
        'NS' => 27.5,
        'ZO' => 50.0,
        'PS' => 72.5,
        'PB' => 90.0,
    ];

    private const ERROR_SETS = [
        'NB' => ['type' => 'left', 'full_until' => -0.80, 'zero_at' => -0.40],
        'NS' => ['type' => 'triangle', 'left' => -0.80, 'peak' => -0.40, 'right' => 0.00],
        'ZO' => ['type' => 'triangle', 'left' => -0.40, 'peak' => 0.00, 'right' => 0.40],
        'PS' => ['type' => 'triangle', 'left' => 0.00, 'peak' => 0.40, 'right' => 0.80],
        'PB' => ['type' => 'right', 'zero_at' => 0.40, 'full_from' => 0.80],
    ];

    private const DELTA_ERROR_SETS = [
        'NB' => ['type' => 'left', 'full_until' => -0.30, 'zero_at' => -0.125],
        'NS' => ['type' => 'triangle', 'left' => -0.30, 'peak' => -0.125, 'right' => 0.00],
        'ZO' => ['type' => 'triangle', 'left' => -0.125, 'peak' => 0.00, 'right' => 0.125],
        'PS' => ['type' => 'triangle', 'left' => 0.00, 'peak' => 0.125, 'right' => 0.30],
        'PB' => ['type' => 'right', 'zero_at' => 0.125, 'full_from' => 0.30],
    ];

    private const OUTPUT_SETS = [
        'NB' => ['type' => 'left', 'full_until' => 10.0, 'zero_at' => 30.0],
        'NS' => ['type' => 'triangle', 'left' => 10.0, 'peak' => 27.5, 'right' => 50.0],
        'ZO' => ['type' => 'triangle', 'left' => 30.0, 'peak' => 50.0, 'right' => 70.0],
        'PS' => ['type' => 'triangle', 'left' => 50.0, 'peak' => 72.5, 'right' => 90.0],
        'PB' => ['type' => 'right', 'zero_at' => 70.0, 'full_from' => 90.0],
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
        $explanation = $this->explain($input, $previousError);
        $evaluated = $explanation['evaluated'];

        return [
            'vpv' => $evaluated['vpv'],
            'ipv' => $evaluated['ipv'],
            'vbat' => $evaluated['vbat'],
            'ibat' => $evaluated['ibat'],
            'soc' => $evaluated['soc'],
            'duty_cycle' => $evaluated['duty_cycle'],
            'fase' => $evaluated['fase'],
            'label_e' => $evaluated['label_e'],
            'label_de' => $evaluated['label_de'],
        ];
    }

    public function explain(array $input, ?float $previousError = null): array
    {
        $vpv = (float) $input['vpv'];
        $vbat = (float) $input['vbat'];
        $soc = $this->clamp((float) $input['soc'], 0.0, 100.0);
        $phase = $this->phase($vpv, $vbat, $soc);
        $target = $this->targetVoltage($phase, $vbat);
        $error = $target - $vbat;
        $deltaError = $previousError === null ? 0.0 : $error - $previousError;
        $errorMemberships = $this->fuzzify($error, self::ERROR_SETS);
        $deltaMemberships = $this->fuzzify($deltaError, self::DELTA_ERROR_SETS);
        $labelE = $this->dominantLabel($errorMemberships);
        $labelDe = $this->dominantLabel($deltaMemberships);
        $ruleStrengths = $this->ruleStrengths($errorMemberships, $deltaMemberships);
        $mamdaniDuty = $this->mamdaniCentroidFromStrengths($ruleStrengths);
        $dutyCycle = $this->dutyCycle($phase, $mamdaniDuty, $vpv, $vbat);

        return [
            'evaluated' => [
                'vpv' => $vpv,
                'ipv' => max(0.0, (float) $input['ipv']),
                'vbat' => $vbat,
                'ibat' => max(0.0, (float) $input['ibat']),
                'soc' => $soc,
                'duty_cycle' => round($dutyCycle, 2),
                'fase' => $phase,
                'label_e' => $labelE,
                'label_de' => $labelDe,
            ],
            'target_voltage' => round($target, 2),
            'error' => round($error, 3),
            'delta_error' => round($deltaError, 3),
            'error_memberships' => $this->roundMemberships($errorMemberships),
            'delta_memberships' => $this->roundMemberships($deltaMemberships),
            'rule_strengths' => $this->roundMemberships($ruleStrengths),
            'mamdani_centroid' => round($mamdaniDuty, 2),
            'final_duty' => round($dutyCycle, 2),
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

    private function fuzzify(float $value, array $sets): array
    {
        $memberships = [];

        foreach ($sets as $label => $set) {
            $memberships[$label] = $this->membership($value, $set);
        }

        return $memberships;
    }

    private function membership(float $value, array $set): float
    {
        return match ($set['type']) {
            'left' => $value <= $set['full_until']
                ? 1.0
                : ($value >= $set['zero_at']
                    ? 0.0
                    : ($set['zero_at'] - $value) / ($set['zero_at'] - $set['full_until'])),
            'right' => $value >= $set['full_from']
                ? 1.0
                : ($value <= $set['zero_at']
                    ? 0.0
                    : ($value - $set['zero_at']) / ($set['full_from'] - $set['zero_at'])),
            default => $value <= $set['left'] || $value >= $set['right']
                ? 0.0
                : ($value === $set['peak']
                    ? 1.0
                    : ($value < $set['peak']
                        ? ($value - $set['left']) / ($set['peak'] - $set['left'])
                        : ($set['right'] - $value) / ($set['right'] - $set['peak']))),
        };
    }

    private function dominantLabel(array $memberships): string
    {
        $dominant = self::LABELS[0];

        foreach (self::LABELS as $label) {
            if ($memberships[$label] > $memberships[$dominant]) {
                $dominant = $label;
            }
        }

        return $dominant;
    }

    private function mamdaniCentroid(array $errorMemberships, array $deltaMemberships): float
    {
        return $this->mamdaniCentroidFromStrengths(
            $this->ruleStrengths($errorMemberships, $deltaMemberships)
        );
    }

    private function ruleStrengths(array $errorMemberships, array $deltaMemberships): array
    {
        $ruleStrengths = array_fill_keys(self::LABELS, 0.0);

        foreach (self::RULES as $errorLabel => $row) {
            foreach ($row as $deltaLabel => $outputLabel) {
                $strength = min($errorMemberships[$errorLabel], $deltaMemberships[$deltaLabel]);
                $ruleStrengths[$outputLabel] = max($ruleStrengths[$outputLabel], $strength);
            }
        }

        return $ruleStrengths;
    }

    private function mamdaniCentroidFromStrengths(array $ruleStrengths): float
    {
        $weightedSum = 0.0;
        $membershipSum = 0.0;

        for ($duty = 0.0; $duty <= 100.0; $duty += 0.25) {
            $aggregated = 0.0;

            foreach (self::OUTPUT_SETS as $label => $set) {
                $clipped = min($ruleStrengths[$label], $this->membership($duty, $set));
                $aggregated = max($aggregated, $clipped);
            }

            $weightedSum += $duty * $aggregated;
            $membershipSum += $aggregated;
        }

        return $membershipSum > 0.0 ? $weightedSum / $membershipSum : self::OUTPUT_VALUES['ZO'];
    }

    private function roundMemberships(array $memberships): array
    {
        return collect($memberships)
            ->map(fn (float $value) => round($value, 3))
            ->all();
    }

    private function dutyCycle(string $phase, float $mamdaniDuty, float $vpv, float $vbat): float
    {
        if ($phase === 'Standby') {
            return 0.0;
        }

        $duty = $mamdaniDuty;

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
