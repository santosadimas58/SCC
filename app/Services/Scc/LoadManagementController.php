<?php

namespace App\Services\Scc;

class LoadManagementController
{
    private const LOADS = [
        [
            'name' => 'Lampu DC',
            'priority' => 1,
            'base_power' => 4.0,
            'minimum_score' => 25.0,
            'limited_score' => 18.0,
        ],
        [
            'name' => 'Kipas DC',
            'priority' => 2,
            'base_power' => 8.0,
            'minimum_score' => 55.0,
            'limited_score' => 42.0,
        ],
        [
            'name' => 'Pompa DC',
            'priority' => 3,
            'base_power' => 24.0,
            'minimum_score' => 82.0,
            'limited_score' => 70.0,
        ],
    ];

    public function evaluate(array $input): array
    {
        $vpv = (float) ($input['vpv'] ?? 0.0);
        $ipv = max(0.0, (float) ($input['ipv'] ?? 0.0));
        $vbat = max(0.0, (float) ($input['vbat'] ?? 0.0));
        $soc = max(0.0, min(100.0, (float) ($input['soc'] ?? 0.0)));
        $phase = (string) ($input['fase'] ?? 'Standby');
        $labelE = (string) ($input['label_e'] ?? 'ZO');
        $panelPower = $vpv * $ipv;
        $score = $this->energyScore($soc, $panelPower, $vpv, $phase, $labelE);
        $loads = $this->loads($score, $panelPower, $vbat, $phase);
        $loadPower = collect($loads)->sum('load_power');
        $loadCurrent = $vbat > 0.0 ? $loadPower / $vbat : 0.0;
        $netPower = $panelPower - $loadPower;
        $status = $this->aggregateStatus($loads);
        $activeLoads = collect($loads)
            ->filter(fn (array $load) => $load['load_status'] !== 'OFF')
            ->pluck('name')
            ->all();

        return [
            'load_name' => $activeLoads !== [] ? implode(', ', $activeLoads) : 'Semua beban OFF',
            'load_status' => $status,
            'load_power' => round($loadPower, 2),
            'load_current' => round($loadCurrent, 3),
            'net_power' => round($netPower, 2),
            'load_reason' => $this->reason($score, $soc, $panelPower, $phase, $status),
            'load_score' => round($score, 1),
            'load_items' => $loads,
        ];
    }

    private function energyScore(float $soc, float $panelPower, float $vpv, string $phase, string $labelE): float
    {
        $score = 0.0;
        $score += $this->clamp($soc, 0.0, 100.0) * 0.45;
        $score += $this->clamp($panelPower / 50.0, 0.0, 1.0) * 35.0;
        $score += $this->clamp(($vpv - 12.0) / 8.0, 0.0, 1.0) * 12.0;

        $score += match ($phase) {
            'Float' => 10.0,
            'Absorption' => 4.0,
            'Bulk' => -4.0,
            default => -12.0,
        };

        $score += match ($labelE) {
            'NB', 'NS' => 6.0,
            'ZO' => 2.0,
            'PS' => -2.0,
            'PB' => -8.0,
            default => 0.0,
        };

        return $this->clamp($score, 0.0, 100.0);
    }

    private function loads(float $score, float $panelPower, float $vbat, string $phase): array
    {
        $loads = [];
        $allocatedPower = 0.0;

        foreach (self::LOADS as $load) {
            $status = 'OFF';
            $factor = 0.0;

            if ($score >= $load['minimum_score']) {
                $status = 'ON';
                $factor = 1.0;
            } elseif ($score >= $load['limited_score']) {
                $status = 'LIMITED';
                $factor = $load['priority'] === 1 ? 0.5 : 0.4;
            }

            if ($phase === 'Standby' && $load['priority'] > 1) {
                $status = 'OFF';
                $factor = 0.0;
            }

            $loadPower = $load['base_power'] * $factor;
            $projectedPower = $allocatedPower + $loadPower;

            if ($status === 'ON' && $load['priority'] > 1 && $projectedPower > $panelPower * 0.9) {
                $status = 'LIMITED';
                $factor = 0.45;
                $loadPower = $load['base_power'] * $factor;
            }

            if ($status === 'LIMITED' && $load['priority'] === 3 && ($allocatedPower + $loadPower) > $panelPower) {
                $status = 'OFF';
                $factor = 0.0;
                $loadPower = 0.0;
            }

            $allocatedPower += $loadPower;

            $loads[] = [
                'name' => $load['name'],
                'priority' => $load['priority'],
                'base_power' => $load['base_power'],
                'load_status' => $status,
                'load_power' => round($loadPower, 2),
                'load_current' => round($vbat > 0.0 ? $loadPower / $vbat : 0.0, 3),
                'allocation' => round($factor * 100.0),
                'reason' => $this->loadReason($load['name'], $status, $score),
            ];
        }

        return $loads;
    }

    private function aggregateStatus(array $loads): string
    {
        $statuses = collect($loads)->pluck('load_status');

        if ($statuses->every(fn (string $status) => $status === 'OFF')) {
            return 'OFF';
        }

        if ($statuses->contains('LIMITED') || $statuses->contains('OFF')) {
            return 'LIMITED';
        }

        return 'ON';
    }

    private function reason(float $score, float $soc, float $panelPower, string $phase, string $status): string
    {
        return match ($status) {
            'ON' => "Skor fuzzy beban {$score} tinggi: SoC {$soc}% dan daya panel ".round($panelPower, 1)." W cukup untuk menyalakan Lampu, Kipas, dan Pompa DC.",
            'LIMITED' => "Skor fuzzy beban {$score} menengah: fase {$phase} tetap menjaga charging, sehingga beban prioritas rendah dibatasi atau dimatikan.",
            default => "Skor fuzzy beban {$score} rendah: SoC {$soc}% dan daya panel ".round($panelPower, 1)." W belum aman, energi diprioritaskan untuk proteksi baterai.",
        };
    }

    private function loadReason(string $name, string $status, float $score): string
    {
        return match ($status) {
            'ON' => "{$name} ON karena skor energi {$score} melewati ambang prioritasnya.",
            'LIMITED' => "{$name} dibatasi agar total beban tidak mengalahkan proses charging.",
            default => "{$name} OFF karena prioritasnya belum terpenuhi oleh skor fuzzy saat ini.",
        };
    }

    private function clamp(float $value, float $minimum, float $maximum): float
    {
        return max($minimum, min($maximum, $value));
    }
}
