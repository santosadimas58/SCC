<?php

namespace App\Livewire\Pages;

use App\Models\SccData;
use App\Services\Scc\FuzzyChargeController;
use Livewire\Component;

class SccAnalysis extends Component
{
    public array $summary = [];
    public array $phaseDistribution = [];
    public array $dutySocPoints = [];
    public array $mamdaniTimeline = [];
    public array $controlComparison = [];
    public array $mamdaniExample = [];
    public array $quickInsights = [];

    public function mount(): void
    {
        $this->summary = $this->buildSummary();
        $this->phaseDistribution = $this->buildPhaseDistribution();
        $this->dutySocPoints = $this->buildDutySocPoints();
        $this->mamdaniTimeline = $this->buildMamdaniTimeline();
        $this->controlComparison = $this->buildControlComparison();
        $this->mamdaniExample = $this->buildMamdaniExample();
        $this->quickInsights = $this->buildQuickInsights();
    }

    protected function buildSummary(): array
    {
        $rows = SccData::query()->latest()->take(200)->get();

        if ($rows->isEmpty()) {
            return [
                'records' => 0,
                'avg_soc' => null,
                'avg_duty' => null,
                'avg_panel_power' => null,
                'avg_efficiency' => null,
                'max_panel_power' => null,
            ];
        }

        $panelPowers = $rows->map(fn ($row) => $row->vpv * $row->ipv);
        $efficiencies = $rows
            ->map(function ($row) {
                $panelPower = $row->vpv * $row->ipv;
                $batteryPower = $row->vbat * $row->ibat;

                return $panelPower > 0 ? min(100, max(0, ($batteryPower / $panelPower) * 100)) : null;
            })
            ->filter(fn ($value) => $value !== null);

        return [
            'records' => $rows->count(),
            'avg_soc' => round($rows->avg('soc'), 2),
            'avg_duty' => round($rows->avg('duty_cycle'), 2),
            'avg_panel_power' => round($panelPowers->avg(), 2),
            'avg_efficiency' => $efficiencies->isNotEmpty() ? round($efficiencies->avg(), 2) : null,
            'max_panel_power' => round($panelPowers->max(), 2),
        ];
    }

    protected function buildPhaseDistribution(): array
    {
        $total = SccData::count();
        $counts = SccData::selectRaw('fase, count(*) as total')
            ->groupBy('fase')
            ->orderByRaw("case fase when 'Bulk' then 1 when 'Absorption' then 2 when 'Float' then 3 when 'Standby' then 4 else 5 end")
            ->pluck('total', 'fase');

        return collect(['Bulk', 'Absorption', 'Float', 'Standby'])
            ->map(function (string $phase) use ($counts, $total) {
                $count = (int) ($counts[$phase] ?? 0);

                return [
                    'phase' => $phase,
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                ];
            })
            ->all();
    }

    protected function buildMamdaniTimeline(): array
    {
        $rows = SccData::query()
            ->latest()
            ->take(120)
            ->get()
            ->reverse()
            ->values();

        $previousError = null;

        return $rows
            ->map(function ($row) use (&$previousError) {
                $target = $this->targetVoltage($row->fase, (float) $row->vbat);
                $error = $target - (float) $row->vbat;
                $deltaError = $previousError === null ? 0.0 : $error - $previousError;
                $thresholdDuty = $this->thresholdDuty((float) $row->vpv, (float) $row->vbat, (float) $row->soc, (string) $row->fase);

                $previousError = $error;

                return [
                    'time' => $row->created_at?->format('H:i:s'),
                    'phase' => $row->fase,
                    'error' => round($error, 3),
                    'delta_error' => round($deltaError, 3),
                    'mamdani_duty' => round((float) $row->duty_cycle, 2),
                    'threshold_duty' => round($thresholdDuty, 2),
                    'soc' => round((float) $row->soc, 2),
                ];
            })
            ->all();
    }

    protected function buildControlComparison(): array
    {
        $points = collect($this->mamdaniTimeline);

        if ($points->count() < 3) {
            return [
                'avg_abs_error' => null,
                'max_abs_error' => null,
                'mamdani_avg_step' => null,
                'threshold_avg_step' => null,
                'smoothness_gain' => null,
                'phase_transitions' => null,
            ];
        }

        $mamdaniSteps = [];
        $thresholdSteps = [];
        $phaseTransitions = 0;
        $previous = null;

        foreach ($points as $point) {
            if ($previous !== null) {
                $mamdaniSteps[] = abs($point['mamdani_duty'] - $previous['mamdani_duty']);
                $thresholdSteps[] = abs($point['threshold_duty'] - $previous['threshold_duty']);

                if ($point['phase'] !== $previous['phase']) {
                    $phaseTransitions++;
                }
            }

            $previous = $point;
        }

        $mamdaniAvgStep = collect($mamdaniSteps)->avg();
        $thresholdAvgStep = collect($thresholdSteps)->avg();
        $mamdaniActiveStep = collect($mamdaniSteps)
            ->filter(fn (float $step) => $step > 0.0)
            ->avg();
        $thresholdActiveStep = collect($thresholdSteps)
            ->filter(fn (float $step) => $step > 0.0)
            ->avg();

        return [
            'avg_abs_error' => round($points->avg(fn (array $point) => abs($point['error'])), 3),
            'max_abs_error' => round($points->max(fn (array $point) => abs($point['error'])), 3),
            'mamdani_avg_step' => round($mamdaniActiveStep ?? $mamdaniAvgStep, 2),
            'threshold_avg_step' => round($thresholdActiveStep ?? $thresholdAvgStep, 2),
            'smoothness_gain' => $thresholdActiveStep > 0.0 ? round((1 - (($mamdaniActiveStep ?? 0.0) / $thresholdActiveStep)) * 100, 1) : null,
            'phase_transitions' => $phaseTransitions,
        ];
    }

    protected function buildMamdaniExample(): array
    {
        $rows = SccData::query()
            ->latest()
            ->take(80)
            ->get()
            ->reverse()
            ->values();

        if ($rows->isEmpty()) {
            return [];
        }

        $controller = app(FuzzyChargeController::class);
        $previousError = null;
        $selected = null;

        foreach ($rows as $row) {
            $explanation = $controller->explain([
                'vpv' => $row->vpv,
                'ipv' => $row->ipv,
                'vbat' => $row->vbat,
                'ibat' => $row->ibat,
                'soc' => $row->soc,
            ], $previousError);

            $previousError = $explanation['target_voltage'] - (float) $row->vbat;

            if (($explanation['evaluated']['fase'] ?? null) !== 'Standby') {
                $selected = $explanation + [
                    'time' => $row->created_at?->format('H:i:s'),
                    'phase' => $explanation['evaluated']['fase'],
                ];
            }
        }

        return $selected ?? [];
    }

    protected function buildDutySocPoints(): array
    {
        return SccData::query()
            ->latest()
            ->take(120)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($row) => [
                'x' => round($row->soc, 2),
                'y' => round($row->duty_cycle, 2),
                'phase' => $row->fase,
                'time' => $row->created_at?->format('H:i:s'),
            ])
            ->all();
    }

    protected function buildQuickInsights(): array
    {
        if (($this->summary['records'] ?? 0) === 0) {
            return [
                [
                    'title' => 'Data belum tersedia',
                    'body' => 'Belum ada sampel SCC yang bisa dibaca untuk membentuk interpretasi performa.',
                    'tone' => 'unknown',
                ],
                [
                    'title' => 'Tren duty belum terbaca',
                    'body' => 'Grafik duty cycle terhadap SoC akan dianalisis setelah data monitoring masuk.',
                    'tone' => 'unknown',
                ],
                [
                    'title' => 'Energi panel belum terbaca',
                    'body' => 'Rata-rata daya panel dan efisiensi belum bisa dibandingkan karena data masih kosong.',
                    'tone' => 'unknown',
                ],
            ];
        }

        return [
            $this->phaseInsight(),
            $this->dutySocInsight(),
            $this->mamdaniInsight(),
        ];
    }

    protected function phaseInsight(): array
    {
        $dominant = collect($this->phaseDistribution)
            ->sortByDesc('count')
            ->first();

        $phase = $dominant['phase'] ?? '-';
        $percentage = $dominant['percentage'] ?? 0;

        return match ($phase) {
            'Bulk' => [
                'title' => 'Charging aktif dominan',
                'body' => "Fase Bulk paling sering muncul ({$percentage}%), menandakan sistem banyak bekerja untuk mempercepat pengisian baterai.",
                'tone' => 'normal',
            ],
            'Absorption' => [
                'title' => 'Baterai mendekati target',
                'body' => "Fase Absorption dominan ({$percentage}%), sehingga kontrol mulai menahan duty cycle agar tegangan baterai stabil.",
                'tone' => 'info',
            ],
            'Float' => [
                'title' => 'Baterai sering penuh',
                'body' => "Fase Float dominan ({$percentage}%), menandakan baterai sering berada di area aman dan charging dijaga rendah.",
                'tone' => 'normal',
            ],
            'Standby' => [
                'title' => 'Charging sering tertahan',
                'body' => "Fase Standby dominan ({$percentage}%), kemungkinan panel kurang kuat atau sistem sering menahan pengisian.",
                'tone' => 'warning',
            ],
            default => [
                'title' => 'Distribusi fase terbaca',
                'body' => 'Fase charging sudah terbaca, tetapi belum ada fase dominan yang cukup jelas.',
                'tone' => 'unknown',
            ],
        };
    }

    protected function dutySocInsight(): array
    {
        $points = collect($this->dutySocPoints);

        if ($points->count() < 8) {
            return [
                'title' => 'Tren duty belum kuat',
                'body' => 'Jumlah titik pada grafik masih sedikit, jadi hubungan duty cycle terhadap SoC belum cukup stabil untuk disimpulkan.',
                'tone' => 'unknown',
            ];
        }

        $lowSocDuty = $points
            ->filter(fn (array $point) => $point['x'] <= 60)
            ->avg('y');
        $highSocDuty = $points
            ->filter(fn (array $point) => $point['x'] >= 80)
            ->avg('y');

        if ($lowSocDuty !== null && $highSocDuty !== null) {
            $gap = $lowSocDuty - $highSocDuty;

            if ($gap >= 15.0) {
                return [
                    'title' => 'Pola fuzzy sesuai',
                    'body' => 'Duty cycle lebih tinggi saat SoC rendah dan turun saat SoC tinggi, sehingga pola kontrol sudah mengikuti kebutuhan charging.',
                    'tone' => 'normal',
                ];
            }

            if ($highSocDuty > $lowSocDuty + 8.0) {
                return [
                    'title' => 'Duty perlu diperiksa',
                    'body' => 'Duty cycle pada SoC tinggi terlihat lebih besar dari SoC rendah, sehingga rule fuzzy atau data sensor perlu divalidasi.',
                    'tone' => 'warning',
                ];
            }
        }

        return [
            'title' => 'Tren duty relatif datar',
            'body' => 'Duty cycle belum menunjukkan penurunan yang kuat terhadap kenaikan SoC; kondisi ini bisa terjadi saat panel lemah atau data fase bercampur.',
            'tone' => 'info',
        ];
    }

    protected function mamdaniInsight(): array
    {
        $mamdaniStep = $this->controlComparison['mamdani_avg_step'] ?? null;
        $thresholdStep = $this->controlComparison['threshold_avg_step'] ?? null;

        if ($mamdaniStep === null || $thresholdStep === null) {
            return [
                'title' => 'Evaluasi Mamdani menunggu data',
                'body' => 'Perbandingan kehalusan duty cycle membutuhkan beberapa sampel berurutan dari data SCC.',
                'tone' => 'unknown',
            ];
        }

        if ($mamdaniStep <= $thresholdStep) {
            return [
                'title' => 'Mamdani lebih halus',
                'body' => "Rata-rata lonjakan duty Mamdani {$mamdaniStep}% saat berubah, lebih rendah dari kontrol threshold {$thresholdStep}%. Ini mendukung klaim transisi PWM lebih halus.",
                'tone' => 'normal',
            ];
        }

        return [
            'title' => 'Perlu cek skenario data',
            'body' => "Duty Mamdani berubah {$mamdaniStep}% per sampel, lebih besar dari threshold {$thresholdStep}%. Cek apakah data bercampur fase Standby atau transisi ekstrem.",
            'tone' => 'warning',
        ];
    }

    protected function targetVoltage(string $phase, float $vbat): float
    {
        return match ($phase) {
            'Bulk', 'Absorption' => FuzzyChargeController::BULK_TARGET,
            'Float' => FuzzyChargeController::FLOAT_TARGET,
            default => $vbat,
        };
    }

    protected function thresholdDuty(float $vpv, float $vbat, float $soc, string $phase): float
    {
        if ($phase === 'Standby' || $vpv < 15.0 || $vpv < $vbat + 1.0) {
            return 0.0;
        }

        $duty = match (true) {
            $soc < 60.0 => 90.0,
            $soc < 80.0 => 70.0,
            $soc < 95.0 => 42.0,
            default => 15.0,
        };

        if ($phase === 'Absorption') {
            $duty = min($duty, 52.0);
        }

        if ($phase === 'Float') {
            $duty = min($duty, 18.0);
        }

        return max(0.0, min(96.0, $duty));
    }

    public function render()
    {
        return view('livewire.pages.scc-analysis')
            ->title('Analisis Performa');
    }
}
