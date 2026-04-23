<?php

namespace App\Livewire\Pages;

use App\Models\SccData;
use Livewire\Component;

class SccDashboard extends Component
{
    public $latest;
    public $history;
    public $status = [];
    public $dailySummary = [];
    public $groupedMetrics = [];

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->latest  = SccData::latest()->first();
        $this->history = SccData::latest()->take(20)->get();
        $this->status = $this->buildStatus();
        $this->dailySummary = $this->buildDailySummary();
        $this->groupedMetrics = $this->buildGroupedMetrics();
    }

    protected function buildStatus(): array
    {
        if (! $this->latest) {
            return [
                'online' => false,
                'label' => 'Offline',
                'message' => 'Belum ada data masuk dari alat.',
                'seconds_since_update' => null,
                'last_update' => null,
                'charging_mode' => '-',
            ];
        }

        $seconds = abs((int) $this->latest->created_at->diffInSeconds(now()));
        $online = $seconds <= 15;

        return [
            'online' => $online,
            'label' => $online ? 'Online' : 'Offline',
            'message' => $online
                ? 'Perangkat masih mengirim pembaruan data secara berkala.'
                : 'Pembaruan data terlambat. Periksa koneksi alat atau sensor.',
            'seconds_since_update' => $seconds,
            'last_update' => $this->latest->created_at->format('d M Y H:i:s'),
            'charging_mode' => $this->latest->fase,
        ];
    }

    protected function buildDailySummary(): array
    {
        $today = SccData::query()
            ->whereDate('created_at', today())
            ->get();

        if ($today->isEmpty()) {
            return [
                'records' => 0,
                'avg_vbat' => null,
                'avg_soc' => null,
                'max_power' => null,
                'dominant_phase' => '-',
            ];
        }

        $phaseCounts = $today->groupBy('fase')->map->count()->sortDesc();

        return [
            'records' => $today->count(),
            'avg_vbat' => round($today->avg('vbat'), 2),
            'avg_soc' => round($today->avg('soc'), 2),
            'max_power' => round($today->map(fn ($row) => $row->vpv * $row->ipv)->max(), 2),
            'dominant_phase' => $phaseCounts->keys()->first(),
        ];
    }

    protected function metricStatus(?float $value, array $normal, array $warning): string
    {
        if ($value === null) {
            return 'unknown';
        }

        if ($value >= $normal[0] && $value <= $normal[1]) {
            return 'normal';
        }

        if ($value >= $warning[0] && $value <= $warning[1]) {
            return 'warning';
        }

        return 'critical';
    }

    protected function buildGroupedMetrics(): array
    {
        $latest = $this->latest;

        return [
            [
                'title' => 'Sumber Energi',
                'items' => [
                    [
                        'label' => 'Tegangan Panel',
                        'value' => $latest ? number_format($latest->vpv, 1) . ' V' : '-',
                        'range' => 'Ideal 17.0 - 22.0 V',
                        'status' => $this->metricStatus($latest?->vpv, [17.0, 22.0], [14.0, 24.0]),
                    ],
                    [
                        'label' => 'Arus Panel',
                        'value' => $latest ? number_format($latest->ipv, 2) . ' A' : '-',
                        'range' => 'Ideal 0.5 - 5.0 A',
                        'status' => $this->metricStatus($latest?->ipv, [0.5, 5.0], [0.1, 6.0]),
                    ],
                ],
            ],
            [
                'title' => 'Baterai',
                'items' => [
                    [
                        'label' => 'Tegangan Baterai',
                        'value' => $latest ? number_format($latest->vbat, 1) . ' V' : '-',
                        'range' => 'Ideal 11.8 - 14.4 V',
                        'status' => $this->metricStatus($latest?->vbat, [11.8, 14.4], [11.0, 14.8]),
                    ],
                    [
                        'label' => 'State of Charge',
                        'value' => $latest ? number_format($latest->soc, 1) . ' %' : '-',
                        'range' => 'Ideal 50 - 100 %',
                        'status' => $this->metricStatus($latest?->soc, [50.0, 100.0], [30.0, 100.0]),
                    ],
                    [
                        'label' => 'Arus Baterai',
                        'value' => $latest ? number_format($latest->ibat, 2) . ' A' : '-',
                        'range' => 'Ideal 0.2 - 5.0 A',
                        'status' => $this->metricStatus($latest?->ibat, [0.2, 5.0], [0.0, 6.0]),
                    ],
                ],
            ],
            [
                'title' => 'Kendali Fuzzy',
                'items' => [
                    [
                        'label' => 'Duty Cycle PWM',
                        'value' => $latest ? number_format($latest->duty_cycle, 1) . ' %' : '-',
                        'range' => 'Ideal 10 - 95 %',
                        'status' => $this->metricStatus($latest?->duty_cycle, [10.0, 95.0], [5.0, 100.0]),
                    ],
                    [
                        'label' => 'Label Error',
                        'value' => $latest ? $latest->label_e : '-',
                        'range' => 'Kategori NB, NS, ZO, PS, PB',
                        'status' => 'info',
                    ],
                    [
                        'label' => 'Label Delta Error',
                        'value' => $latest ? $latest->label_de : '-',
                        'range' => 'Perubahan error antar sampel',
                        'status' => 'info',
                    ],
                ],
            ],
        ];
    }

    public function render()
    {
        return view('livewire.pages.scc-dashboard')
            ->title('SCC Monitoring');
    }
}
