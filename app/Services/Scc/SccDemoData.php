<?php

namespace App\Services\Scc;

use App\Models\SccData;
use Illuminate\Support\Facades\DB;

class SccDemoData
{
    public function __construct(
        private readonly FuzzyChargeController $controller,
    ) {
    }

    public function reset(int $records = 144): int
    {
        $rows = $this->rows($records);

        DB::transaction(function () use ($rows) {
            SccData::query()->delete();
            SccData::query()->insert($rows);
        });

        return count($rows);
    }

    public function rows(int $records = 144): array
    {
        $records = max(30, $records);
        $now = now();
        $rows = [];
        $previousError = null;

        for ($index = 0; $index < $records; $index++) {
            $createdAt = $now->copy()->subSeconds(($records - $index - 1) * 15);
            $payload = $this->payloadForStep($index, $records);
            $evaluated = $this->controller->evaluate($payload, $previousError);
            $target = $this->targetForPhase($evaluated['fase'], $evaluated['vbat']);
            $previousError = $target - $evaluated['vbat'];

            $rows[] = [
                ...$evaluated,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        return $rows;
    }

    private function payloadForStep(int $index, int $records): array
    {
        $position = $index / max(1, $records - 1);
        $wave = sin($position * pi() * 2);

        if ($position < 0.45) {
            $phaseProgress = $position / 0.45;

            return [
                'vpv' => round(18.2 + sin($phaseProgress * pi()) * 1.4 + $wave * 0.18, 2),
                'ipv' => round(1.8 + $phaseProgress * 1.45 + abs($wave) * 0.2, 2),
                'vbat' => round(11.85 + $phaseProgress * 2.2, 2),
                'ibat' => round(1.65 + $phaseProgress * 1.25, 2),
                'soc' => round(28 + $phaseProgress * 50, 2),
            ];
        }

        if ($position < 0.72) {
            $phaseProgress = ($position - 0.45) / 0.27;

            return [
                'vpv' => round(18.9 + sin($phaseProgress * pi()) * 0.65, 2),
                'ipv' => round(2.1 - $phaseProgress * 0.7, 2),
                'vbat' => round(14.12 + $phaseProgress * 0.25, 2),
                'ibat' => round(1.85 - $phaseProgress * 0.5, 2),
                'soc' => round(80 + $phaseProgress * 14, 2),
            ];
        }

        if ($position < 0.88) {
            $phaseProgress = ($position - 0.72) / 0.16;

            return [
                'vpv' => round(18.0 + $wave * 0.25, 2),
                'ipv' => round(0.72 - $phaseProgress * 0.25, 2),
                'vbat' => round(13.68 + sin($phaseProgress * pi()) * 0.06, 2),
                'ibat' => round(0.58 - $phaseProgress * 0.2, 2),
                'soc' => round(95 + $phaseProgress * 3.6, 2),
            ];
        }

        if ($position < 0.96) {
            $phaseProgress = ($position - 0.88) / 0.08;

            return [
                'vpv' => round(0.2 + $phaseProgress * 0.45, 2),
                'ipv' => 0.0,
                'vbat' => round(13.35 - $phaseProgress * 0.48, 2),
                'ibat' => 0.0,
                'soc' => round(97.5 - $phaseProgress * 1.4, 2),
            ];
        }

        $phaseProgress = ($position - 0.96) / 0.04;

        return [
            'vpv' => round(17.6 + $phaseProgress * 1.1, 2),
            'ipv' => round(1.25 + $phaseProgress * 0.85, 2),
            'vbat' => round(12.55 + $phaseProgress * 0.25, 2),
            'ibat' => round(1.1 + $phaseProgress * 0.55, 2),
            'soc' => round(45 + $phaseProgress * 5, 2),
        ];
    }

    private function targetForPhase(string $phase, float $vbat): float
    {
        return match ($phase) {
            'Bulk', 'Absorption' => FuzzyChargeController::BULK_TARGET,
            'Float' => FuzzyChargeController::FLOAT_TARGET,
            default => $vbat,
        };
    }
}
