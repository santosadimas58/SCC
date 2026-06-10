<?php

namespace App\Livewire\Pages;

use App\Models\SccData;
use App\Services\Scc\LoadManagementController;
use Livewire\Component;

class SccLoadManagement extends Component
{
    public function render()
    {
        $latest = SccData::latest()->first();
        $load = $this->loadManagement($latest);
        $history = $this->loadHistory();
        $summary = $this->summary($history);

        return view('livewire.pages.scc-load-management', compact('latest', 'load', 'history', 'summary'))
            ->title('Kontrol Beban DC');
    }

    protected function loadManagement(?SccData $latest): array
    {
        if (! $latest) {
            return [
                'load_name' => '-',
                'load_status' => '-',
                'load_power' => null,
                'load_current' => null,
                'net_power' => null,
                'energy_status' => '-',
                'tone' => 'unknown',
                'load_reason' => 'Menunggu data SCC terbaru.',
            ];
        }

        $load = app(LoadManagementController::class)->evaluate($latest->toArray());

        return [
            ...$load,
            'energy_status' => ($load['net_power'] ?? 0.0) >= 0.0 ? 'Surplus energi' : 'Defisit energi',
            'tone' => $this->tone($load['load_status'] ?? null),
        ];
    }

    protected function loadHistory()
    {
        return SccData::latest()
            ->take(50)
            ->get()
            ->map(function (SccData $row) {
                $load = app(LoadManagementController::class)->evaluate($row->toArray());

                return [
                    'time' => $row->created_at,
                    'soc' => $row->soc,
                    'phase' => $row->fase,
                    'panel_power' => round($row->vpv * $row->ipv, 2),
                    'active_count' => collect($load['load_items'] ?? [])->where('load_status', 'ON')->count(),
                    'limited_count' => collect($load['load_items'] ?? [])->where('load_status', 'LIMITED')->count(),
                    'off_count' => collect($load['load_items'] ?? [])->where('load_status', 'OFF')->count(),
                    ...$load,
                    'tone' => $this->tone($load['load_status'] ?? null),
                ];
            });
    }

    protected function summary($history): array
    {
        if ($history->isEmpty()) {
            return [
                'records' => 0,
                'avg_load_power' => null,
                'avg_net_power' => null,
                'on_count' => 0,
                'limited_count' => 0,
                'off_count' => 0,
                'avg_score' => null,
            ];
        }

        return [
            'records' => $history->count(),
            'avg_load_power' => round($history->avg('load_power'), 2),
            'avg_net_power' => round($history->avg('net_power'), 2),
            'on_count' => $history->where('load_status', 'ON')->count(),
            'limited_count' => $history->where('load_status', 'LIMITED')->count(),
            'off_count' => $history->where('load_status', 'OFF')->count(),
            'avg_score' => round($history->avg('load_score'), 1),
        ];
    }

    protected function tone(?string $status): string
    {
        return match ($status) {
            'ON' => 'normal',
            'LIMITED' => 'warning',
            'OFF' => 'critical',
            default => 'unknown',
        };
    }
}
