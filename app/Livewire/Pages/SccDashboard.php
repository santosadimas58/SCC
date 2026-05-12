<?php

namespace App\Livewire\Pages;

use App\Models\SccData;
use App\Services\Scc\SccDemoData;
use App\Services\Scc\BmkgWeatherService;
use App\Services\Scc\FuzzyChargeController;
use App\Services\Scc\LoadManagementController;
use App\Services\Scc\WeatherAwareSccSimulator;
use Livewire\Component;

class SccDashboard extends Component
{
    public $latest;
    public $status = [];
    public $dailySummary = [];
    public $groupedMetrics = [];
    public $performance = [];
    public $phaseTimeline = [];
    public $fuzzyDecision = [];
    public $weather = [];
    public $simulation = [];
    public $weatherControlFlow = [];
    public $loadManagement = [];
    public ?string $demoResetMessage = null;

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->weather = app(BmkgWeatherService::class)->forecast();
        $this->simulation = $this->simulateFromWeatherIfNeeded($this->weather);
        $this->latest  = SccData::latest()->first();
        $this->status = $this->buildStatus();
        $this->dailySummary = $this->buildDailySummary();
        $this->groupedMetrics = $this->buildGroupedMetrics();
        $this->performance = $this->buildPerformance();
        $this->phaseTimeline = $this->buildPhaseTimeline();
        $this->fuzzyDecision = $this->buildFuzzyDecision();
        $this->loadManagement = $this->buildLoadManagement();
        $this->weatherControlFlow = $this->buildWeatherControlFlow();
    }

    public function resetDemoData(): void
    {
        $count = app(SccDemoData::class)->reset();

        $this->demoResetMessage = "Demo data berhasil di-reset dengan {$count} record baru.";
        $this->refreshData();
    }

    protected function buildStatus(): array
    {
        if (! $this->latest) {
            return [
                'online' => false,
                'label' => 'Offline',
                'message' => 'Belum ada data masuk dari alat.',
                'seconds_since_update' => null,
                'freshness_label' => '-',
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
            'freshness_label' => $this->formatFreshness($seconds),
            'last_update' => $this->latest->created_at->format('d M Y H:i:s'),
            'charging_mode' => $this->latest->fase,
        ];
    }

    protected function simulateFromWeatherIfNeeded(array $weather): array
    {
        if (! config('services.bmkg_weather.auto_simulation', true)) {
            return [
                'enabled' => false,
                'generated' => false,
                'message' => 'Simulasi cuaca otomatis nonaktif.',
            ];
        }

        $latest = SccData::latest()->first();
        $interval = (int) config('services.bmkg_weather.simulation_interval_seconds', 60);

        if ($latest && $latest->created_at->diffInSeconds(now()) < $interval) {
            return [
                'enabled' => true,
                'generated' => false,
                'message' => 'Data simulasi masih baru.',
            ];
        }

        $created = app(WeatherAwareSccSimulator::class)->createFromWeather($weather);

        return [
            'enabled' => true,
            'generated' => $created !== null,
            'message' => $created
                ? 'Data SCC disimulasikan dari cuaca BMKG terbaru.'
                : 'Simulasi menunggu data BMKG tersedia.',
        ];
    }

    protected function formatFreshness(int $seconds): string
    {
        if ($seconds < 60) {
            return 'Baru saja';
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return trim($days.' hari '.($hours > 0 ? $hours.' jam' : ''));
        }

        if ($hours > 0) {
            return trim($hours.' jam '.($minutes > 0 ? $minutes.' menit' : ''));
        }

        return $minutes.' menit';
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

    protected function buildPerformance(): array
    {
        $latest = $this->latest;
        $panelPower = $latest ? round($latest->vpv * $latest->ipv, 2) : null;
        $batteryPower = $latest ? round($latest->vbat * $latest->ibat, 2) : null;
        $efficiency = ($panelPower && $panelPower > 0.0 && $batteryPower !== null)
            ? round(min(100.0, max(0.0, ($batteryPower / $panelPower) * 100.0)), 1)
            : null;

        return [
            'panel_power' => $panelPower,
            'battery_power' => $batteryPower,
            'efficiency' => $efficiency,
            'soc_status' => $this->metricStatus($latest?->soc, [50.0, 100.0], [30.0, 100.0]),
            'panel_power_status' => $this->metricStatus($panelPower, [10.0, 95.0], [2.0, 120.0]),
            'efficiency_status' => $efficiency === null
                ? 'unknown'
                : $this->metricStatus($efficiency, [70.0, 100.0], [45.0, 100.0]),
        ];
    }

    protected function buildPhaseTimeline(): array
    {
        $phases = [
            'Bulk' => 'Pengisian cepat saat baterai masih rendah.',
            'Absorption' => 'Tegangan dijaga saat baterai mendekati penuh.',
            'Float' => 'Baterai dipertahankan aman setelah penuh.',
            'Standby' => 'Panel belum cukup atau charging dihentikan.',
        ];

        $phaseNames = array_keys($phases);
        $activePhase = $this->latest?->fase;
        $activeIndex = array_search($activePhase, $phaseNames, true);

        return collect($phases)
            ->map(function (string $description, string $phase) use ($activePhase, $activeIndex, $phaseNames) {
                $phaseIndex = array_search($phase, $phaseNames, true);

                return [
                    'name' => $phase,
                    'description' => $description,
                    'state' => $phase === $activePhase
                        ? 'active'
                        : ($activeIndex !== false && $phaseIndex < $activeIndex ? 'completed' : 'pending'),
                ];
            })
            ->values()
            ->all();
    }

    protected function buildFuzzyDecision(): array
    {
        $latest = $this->latest;

        if (! $latest) {
            return [
                'available' => false,
                'output_label' => '-',
                'base_output' => null,
                'final_duty' => null,
                'rule_text' => 'Belum ada keputusan pengisian aktif.',
                'condition_text' => '-',
                'change_text' => '-',
                'action_text' => '-',
                'phase_note' => 'Menunggu data SCC.',
            ];
        }

        $rules = FuzzyChargeController::rules();
        $outputValues = FuzzyChargeController::outputValues();
        $labelE = $latest->label_e;
        $labelDe = $latest->label_de;
        $outputLabel = $rules[$labelE][$labelDe] ?? '-';
        $baseOutput = $outputValues[$outputLabel] ?? null;

        return [
            'available' => $outputLabel !== '-',
            'label_e' => $labelE,
            'label_de' => $labelDe,
            'output_label' => $outputLabel,
            'base_output' => $baseOutput,
            'final_duty' => $latest->duty_cycle,
            'phase' => $latest->fase,
            'rule_text' => $this->friendlyRuleText($labelE, $labelDe, $outputLabel),
            'condition_text' => $this->friendlyErrorLabel($labelE),
            'change_text' => $this->friendlyDeltaErrorLabel($labelDe),
            'action_text' => $this->friendlyOutputLabel($outputLabel),
            'phase_note' => $this->fuzzyPhaseNote($latest->fase),
        ];
    }

    protected function buildWeatherControlFlow(): array
    {
        $latest = $this->latest;
        $currentWeather = is_array($this->weather['current'] ?? null) ? $this->weather['current'] : [];
        $weatherLabel = (string) ($currentWeather['weather'] ?? ($this->weather['available'] ?? false ? '-' : 'Belum tersedia'));
        $cloudCover = $currentWeather['cloud_cover'] ?? null;
        $impact = $this->weatherPanelImpact($weatherLabel, is_numeric($cloudCover) ? (float) $cloudCover : null);
        $phase = $latest?->fase ?? '-';
        $duty = $latest?->duty_cycle;
        $panelPower = $latest ? round($latest->vpv * $latest->ipv, 2) : null;

        return [
            'weather_label' => $weatherLabel,
            'weather_time' => isset($currentWeather['date'], $currentWeather['time'])
                ? $currentWeather['date'].' '.$currentWeather['time'].' WIB'
                : '-',
            'panel_impact' => $impact['label'],
            'panel_impact_detail' => $impact['detail'],
            'decision' => $this->fuzzyDecision['action_text'] ?? 'Keputusan fuzzy belum tersedia',
            'duty_cycle' => $duty !== null ? number_format($duty, 1).' %' : '-',
            'phase' => $phase,
            'reason' => $this->weatherControlReason($latest, $impact['category']),
            'summary' => $this->weatherControlSummary($latest, $weatherLabel, $impact['category']),
            'metrics' => [
                ['label' => 'Vpv', 'value' => $latest ? number_format($latest->vpv, 1).' V' : '-'],
                ['label' => 'Ipv', 'value' => $latest ? number_format($latest->ipv, 2).' A' : '-'],
                ['label' => 'Vbat', 'value' => $latest ? number_format($latest->vbat, 1).' V' : '-'],
                ['label' => 'SoC', 'value' => $latest ? number_format($latest->soc, 1).' %' : '-'],
                ['label' => 'Daya panel', 'value' => $panelPower !== null ? number_format($panelPower, 1).' W' : '-'],
                ['label' => 'Fuzzy', 'value' => $latest ? $latest->label_e.'/'.$latest->label_de : '-'],
            ],
        ];
    }

    protected function buildLoadManagement(): array
    {
        $latest = $this->latest;

        if (! $latest) {
            return [
                'available' => false,
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

        $load = $latest->load_status
            ? [
                'load_name' => $latest->load_name,
                'load_status' => $latest->load_status,
                'load_power' => $latest->load_power,
                'load_current' => $latest->load_current,
                'net_power' => $latest->net_power,
                'load_reason' => $latest->load_reason,
            ]
            : app(LoadManagementController::class)->evaluate($latest->toArray());

        $netPower = $load['net_power'];
        $status = $load['load_status'];

        return [
            'available' => true,
            ...$load,
            'energy_status' => $netPower >= 0.0 ? 'Surplus energi' : 'Defisit energi',
            'tone' => match ($status) {
                'ON' => 'normal',
                'LIMITED' => 'warning',
                'OFF' => 'critical',
                default => 'unknown',
            },
        ];
    }

    protected function weatherPanelImpact(string $weather, ?float $cloudCover): array
    {
        $weather = mb_strtolower($weather);

        if (str_contains($weather, 'hujan') || str_contains($weather, 'petir') || str_contains($weather, 'lebat') || str_contains($weather, 'mendung')) {
            return [
                'category' => 'weak',
                'label' => 'Charging melemah atau standby',
                'detail' => 'Hujan atau mendung membuat potensi energi panel turun, sehingga duty cycle perlu dibatasi bila Vpv tidak cukup.',
            ];
        }

        if (str_contains($weather, 'berawan') || ($cloudCover !== null && $cloudCover >= 70.0)) {
            return [
                'category' => 'reduced',
                'label' => 'Daya panel menurun',
                'detail' => 'Kondisi berawan menurunkan potensi Vpv dan Ipv, sehingga fuzzy tetap membaca batas kemampuan panel.',
            ];
        }

        if (str_contains($weather, 'cerah')) {
            return [
                'category' => 'strong',
                'label' => 'Potensi Vpv/Ipv lebih tinggi',
                'detail' => 'Cuaca cerah mendukung input panel lebih kuat, sehingga charging dapat didorong selama baterai masih membutuhkan energi.',
            ];
        }

        return [
            'category' => 'normal',
            'label' => 'Daya panel relatif stabil',
            'detail' => 'Kondisi cuaca tidak menunjukkan gangguan besar, sehingga keputusan fuzzy terutama mengikuti baterai dan daya panel aktual.',
        ];
    }

    protected function weatherControlReason(?SccData $latest, string $impactCategory): string
    {
        if (! $latest) {
            return 'Menunggu data SCC agar hubungan cuaca, panel, fuzzy, dan baterai dapat dianalisis.';
        }

        if ($latest->fase === 'Standby') {
            return 'Panel belum cukup kuat untuk charging, sehingga fuzzy rule-based dengan output duty cycle diskrit menahan PWM di 0%.';
        }

        if ($latest->fase === 'Float') {
            return 'Baterai sudah mendekati penuh, sehingga duty cycle diturunkan agar output charging tetap aman.';
        }

        if ($latest->fase === 'Absorption') {
            return 'Baterai mendekati target tegangan, sehingga duty cycle ditahan atau dikurangi agar tegangan tetap stabil.';
        }

        if ($impactCategory === 'reduced') {
            return 'Cuaca berawan membuat daya panel turun. Sistem membaca kebutuhan baterai lalu menaikkan duty cycle, tetapi tetap dibatasi kemampuan panel.';
        }

        if ($impactCategory === 'weak') {
            return 'Cuaca hujan atau mendung menurunkan potensi panel, sehingga duty cycle dibatasi agar charging tidak dipaksakan.';
        }

        return 'Baterai masih membutuhkan energi dan panel cukup kuat, sehingga fuzzy memilih duty cycle tinggi untuk fase Bulk.';
    }

    protected function weatherControlSummary(?SccData $latest, string $weatherLabel, string $impactCategory): string
    {
        if (! $latest) {
            return 'Data BMKG tersedia sebagai konteks cuaca, tetapi data SCC terbaru belum tersedia.';
        }

        $weather = ! in_array($weatherLabel, ['-', 'Belum tersedia'], true) ? "Cuaca {$weatherLabel}" : 'Kondisi cuaca';
        $duty = number_format($latest->duty_cycle, 1);

        return match ($impactCategory) {
            'strong' => "{$weather} mendukung potensi panel lebih tinggi. Fuzzy membaca fase {$latest->fase} dan memilih duty cycle {$duty}% sesuai kebutuhan baterai.",
            'reduced' => "{$weather} membuat daya panel cenderung turun. Fuzzy membaca fase {$latest->fase}, lalu menyesuaikan duty cycle {$duty}% agar charging tetap terkendali.",
            'weak' => "{$weather} dapat melemahkan charging. Fuzzy membaca fase {$latest->fase} dan membatasi duty cycle {$duty}% sesuai kemampuan panel.",
            default => "{$weather} menjadi konteks simulasi panel. Fuzzy membaca fase {$latest->fase} dan memilih duty cycle {$duty}% dari rule diskrit.",
        };
    }

    protected function fuzzyPhaseNote(string $phase): string
    {
        return match ($phase) {
            'Absorption' => 'Daya pengisian dijaga agar tegangan tetap stabil saat baterai mendekati penuh.',
            'Float' => 'Daya pengisian diturunkan agar baterai tetap aman setelah penuh.',
            'Standby' => 'Pengisian dihentikan sementara karena daya panel belum cukup.',
            default => 'Daya pengisian mengikuti kebutuhan baterai dan batas aman converter.',
        };
    }

    protected function friendlyRuleText(string $labelE, string $labelDe, string $outputLabel): string
    {
        return sprintf(
            '%s dan %s, sehingga sistem memilih %s.',
            $this->friendlyErrorSentence($labelE),
            $this->friendlyDeltaErrorSentence($labelDe),
            lcfirst($this->friendlyOutputLabel($outputLabel))
        );
    }

    protected function friendlyErrorLabel(string $label): string
    {
        return match ($label) {
            'NB' => 'Tegangan terlalu tinggi',
            'NS' => 'Sedikit di atas target',
            'ZO' => 'Sesuai target',
            'PS' => 'Sedikit di bawah target',
            'PB' => 'Jauh di bawah target',
            default => '-',
        };
    }

    protected function friendlyErrorSentence(string $label): string
    {
        return match ($label) {
            'NB' => 'tegangan baterai terlalu tinggi',
            'NS' => 'tegangan baterai sedikit di atas target',
            'ZO' => 'tegangan baterai sudah sesuai target',
            'PS' => 'tegangan baterai sedikit di bawah target',
            'PB' => 'tegangan baterai masih jauh di bawah target',
            default => 'kondisi baterai belum terbaca',
        };
    }

    protected function friendlyDeltaErrorLabel(string $label): string
    {
        return match ($label) {
            'NB' => 'Turun cepat',
            'NS' => 'Mulai turun',
            'ZO' => 'Stabil',
            'PS' => 'Mulai naik',
            'PB' => 'Naik cepat',
            default => '-',
        };
    }

    protected function friendlyDeltaErrorSentence(string $label): string
    {
        return match ($label) {
            'NB' => 'perubahannya turun cepat',
            'NS' => 'perubahannya mulai turun',
            'ZO' => 'perubahannya stabil',
            'PS' => 'perubahannya mulai naik',
            'PB' => 'perubahannya naik cepat',
            default => 'perubahannya belum terbaca',
        };
    }

    protected function friendlyOutputLabel(string $label): string
    {
        return match ($label) {
            'NB' => 'Pengisian sangat rendah',
            'NS' => 'Pengisian rendah',
            'ZO' => 'Pengisian sedang',
            'PS' => 'Pengisian tinggi',
            'PB' => 'Pengisian sangat tinggi',
            default => 'Aksi pengisian belum tersedia',
        };
    }

    public function render()
    {
        return view('livewire.pages.scc-dashboard')
            ->title('SCC Monitoring');
    }
}
