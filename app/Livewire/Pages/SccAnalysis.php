<?php

namespace App\Livewire\Pages;

use App\Models\SccData;
use Livewire\Component;

class SccAnalysis extends Component
{
    public array $summary = [];
    public array $phaseDistribution = [];
    public array $dutySocPoints = [];

    public function mount(): void
    {
        $this->summary = $this->buildSummary();
        $this->phaseDistribution = $this->buildPhaseDistribution();
        $this->dutySocPoints = $this->buildDutySocPoints();
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

    public function render()
    {
        return view('livewire.pages.scc-analysis')
            ->title('Analisis Performa');
    }
}
