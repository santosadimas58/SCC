<div class="scc-page scc-dashboard-clean">
    @php
        $statusLabels = [
            'normal' => 'Normal',
            'warning' => 'Warning',
            'critical' => 'Critical',
            'info' => 'Info',
            'unknown' => 'Unknown',
        ];

        $socValue = $latest ? min(100, max(0, $latest->soc)) : 0;
        $metricCards = [
            [
                'label' => 'State of Charge',
                'value' => $latest ? number_format($latest->soc, 1).'%' : '-',
                'hint' => 'Kapasitas baterai',
                'icon' => 'o-battery-100',
                'tone' => $performance['soc_status'] ?? 'unknown',
            ],
            [
                'label' => 'Tegangan Baterai',
                'value' => $latest ? number_format($latest->vbat, 1).' V' : '-',
                'hint' => 'Target fase '.$status['charging_mode'],
                'icon' => 'o-battery-50',
                'tone' => $groupedMetrics[1]['items'][0]['status'] ?? 'unknown',
            ],
            [
                'label' => 'Daya Panel',
                'value' => $performance['panel_power'] !== null ? number_format($performance['panel_power'], 1).' W' : '-',
                'hint' => 'Vpv x Ipv',
                'icon' => 'o-sun',
                'tone' => $performance['panel_power_status'] ?? 'unknown',
            ],
            [
                'label' => 'Duty Cycle PWM',
                'value' => $latest ? number_format($latest->duty_cycle, 1).'%' : '-',
                'hint' => 'Output Mamdani',
                'icon' => 'o-cpu-chip',
                'tone' => $groupedMetrics[2]['items'][0]['status'] ?? 'unknown',
            ],
        ];
    @endphp

    <section class="scc-clean-hero">
        <div>
            <div class="scc-eyebrow">Solar Charge Controller</div>
            <h1 class="mt-2 text-3xl font-semibold text-white md:text-4xl">SCC Monitoring Dashboard</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Ringkasan charging, keputusan fuzzy Mamdani, cuaca simulasi, dan kondisi beban dalam satu layar presentasi.
            </p>
        </div>

        <div class="scc-clean-hero-actions">
            <span class="scc-fuzzy-chip">Demo Mode</span>
            <div class="scc-status-pill {{ $status['online'] ? 'scc-status-online' : 'scc-status-offline' }}">
                <span class="scc-status-dot"></span>
                Status: {{ $status['label'] }}
            </div>
            <x-button
                label="Reset Demo"
                icon="o-arrow-path"
                class="btn-outline btn-sm"
                wire:click="resetDemoData"
                wire:confirm="Reset demo akan menghapus seluruh data SCC dan mengisi ulang dataset presentasi. Lanjutkan?"
            />
        </div>
    </section>

    @if($demoResetMessage)
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            {{ $demoResetMessage }}
        </div>
    @endif

    <div class="scc-clean-metrics">
        @foreach($metricCards as $metric)
            <div class="scc-clean-metric scc-clean-metric-{{ $metric['tone'] }}">
                <div class="scc-clean-metric-icon">
                    <x-icon name="{{ $metric['icon'] }}" class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <div class="text-sm text-slate-400">{{ $metric['label'] }}</div>
                    <div class="mt-2 text-3xl font-semibold text-white">{{ $metric['value'] }}</div>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="scc-metric-state scc-metric-{{ $metric['tone'] }}">{{ $statusLabels[$metric['tone']] ?? ucfirst($metric['tone']) }}</span>
                        <span class="text-xs text-slate-500">{{ $metric['hint'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="scc-clean-main">
        <x-card title="Kondisi Baterai" shadow>
            <div class="scc-clean-battery">
                <div class="scc-soc-gauge scc-soc-{{ $performance['soc_status'] ?? 'unknown' }}" style="--soc: {{ $socValue }}%;">
                    <div class="scc-soc-gauge-inner">
                        <div class="text-4xl font-semibold text-white">{{ $latest ? number_format($latest->soc, 1) : '-' }}%</div>
                        <div class="text-sm text-slate-400">SoC</div>
                    </div>
                </div>

                <div class="scc-clean-list">
                    <div>
                        <span>Status</span>
                        <b class="scc-metric-state scc-metric-{{ $performance['soc_status'] ?? 'unknown' }}">{{ $statusLabels[$performance['soc_status'] ?? 'unknown'] }}</b>
                    </div>
                    <div>
                        <span>Fase Pengisian</span>
                        <b>{{ $status['charging_mode'] }}</b>
                    </div>
                    <div>
                        <span>Daya Baterai</span>
                        <b>{{ $performance['battery_power'] !== null ? number_format($performance['battery_power'], 1).' W' : '-' }}</b>
                    </div>
                    <div>
                        <span>Update Terakhir</span>
                        <b>{{ $status['last_update'] ?? '-' }}</b>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card title="Keputusan Fuzzy Mamdani Saat Ini" shadow>
            <div class="scc-clean-fuzzy">
                <div>
                    <div class="scc-fuzzy-rule-label">Inferensi charging</div>
                    <div class="scc-clean-fuzzy-copy">
                        {{ $fuzzyDecision['rule_text'] ?? 'Belum ada keputusan pengisian aktif.' }}
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-3">
                        <div class="scc-clean-chip-panel">
                            <x-icon name="o-signal" class="h-5 w-5 text-sky-300" />
                            <span>Kondisi</span>
                            <b>{{ $fuzzyDecision['condition_text'] ?? '-' }}</b>
                        </div>
                        <div class="scc-clean-chip-panel">
                            <x-icon name="o-arrow-trending-up" class="h-5 w-5 text-emerald-300" />
                            <span>Perubahan</span>
                            <b>{{ $fuzzyDecision['change_text'] ?? '-' }}</b>
                        </div>
                        <div class="scc-clean-chip-panel">
                            <x-icon name="o-bolt" class="h-5 w-5 text-violet-300" />
                            <span>Aksi</span>
                            <b>{{ $fuzzyDecision['action_text'] ?? '-' }}</b>
                        </div>
                    </div>
                </div>

                <div class="scc-clean-duty">
                    <div class="text-sm text-slate-400">Perintah PWM</div>
                    <div class="mt-2 text-6xl font-semibold text-violet-300">
                        {{ $fuzzyDecision['final_duty'] !== null ? number_format($fuzzyDecision['final_duty'], 1).'%' : '-' }}
                    </div>
                    <div class="scc-clean-duty-track">
                        <div style="width: {{ $fuzzyDecision['final_duty'] !== null ? min(100, max(0, $fuzzyDecision['final_duty'])) : 0 }}%"></div>
                    </div>
                    <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-300">
                        Fase charging aktif: <b class="text-sky-200">{{ $status['charging_mode'] }}</b>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <div class="scc-clean-secondary">
        <x-card title="Konteks Cuaca - Setiabudhi, Bandung" shadow>
            <div class="scc-clean-weather">
                <div class="flex items-center gap-4">
                    <div class="scc-clean-weather-icon">
                        <x-icon name="o-cloud" class="h-8 w-8" />
                    </div>
                    <div>
                        <div class="text-4xl font-semibold text-white">
                            {{ ($weather['available'] ?? false) && $weather['current']['temperature'] !== null ? number_format($weather['current']['temperature'], 0).'°C' : '-' }}
                        </div>
                        <div class="mt-1 text-sm font-semibold text-white">{{ $weather['current']['weather'] ?? 'Data BMKG belum tersedia' }}</div>
                        <div class="mt-1 text-xs text-slate-500">Sumber: BMKG · {{ $weather['fetched_at'] ?? '-' }}</div>
                    </div>
                </div>

                <div class="scc-clean-weather-grid">
                    <div><span>Kelembapan</span><b>{{ ($weather['available'] ?? false) && $weather['current']['humidity'] !== null ? number_format($weather['current']['humidity'], 0).'%' : '-' }}</b></div>
                    <div><span>Tutupan Awan</span><b>{{ ($weather['available'] ?? false) && $weather['current']['cloud_cover'] !== null ? number_format($weather['current']['cloud_cover'], 0).'%' : '-' }}</b></div>
                    <div><span>Angin</span><b>{{ ($weather['available'] ?? false) && $weather['current']['wind_speed'] !== null ? number_format($weather['current']['wind_speed'], 1).' km/jam' : '-' }}</b></div>
                    <div><span>Arah Angin</span><b>{{ $weather['current']['wind_direction'] ?? '-' }}</b></div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-300">
                    {{ $weather['solar_note'] ?? 'Cuaca dipakai sebagai konteks simulasi panel, bukan input langsung fuzzy Mamdani.' }}
                </div>
            </div>
        </x-card>

        <x-card title="Alur Sistem" shadow>
            <div class="scc-clean-flow">
                <div class="scc-clean-flow-steps">
                    <div>
                        <span>1</span>
                        <x-icon name="o-cloud" class="h-7 w-7 text-sky-300" />
                        <b>Cuaca</b>
                        <small>{{ $weatherControlFlow['weather_label'] ?? '-' }}</small>
                    </div>
                    <div>
                        <span>2</span>
                        <x-icon name="o-sun" class="h-7 w-7 text-amber-300" />
                        <b>Panel</b>
                        <small>{{ $performance['panel_power'] !== null ? number_format($performance['panel_power'], 1).' W' : '-' }}</small>
                    </div>
                    <div>
                        <span>3</span>
                        <x-icon name="o-cpu-chip" class="h-7 w-7 text-violet-300" />
                        <b>Fuzzy</b>
                        <small>PWM {{ $latest ? number_format($latest->duty_cycle, 1).'%' : '-' }}</small>
                    </div>
                    <div>
                        <span>4</span>
                        <x-icon name="o-battery-100" class="h-7 w-7 text-emerald-300" />
                        <b>Baterai</b>
                        <small>SoC {{ $latest ? number_format($latest->soc, 1).'%' : '-' }}</small>
                    </div>
                </div>

                <div class="scc-clean-flow-summary">
                    <div><span>Efisiensi Estimasi</span><b class="text-emerald-300">{{ $performance['efficiency'] !== null ? number_format($performance['efficiency'], 1).'%' : '-' }}</b></div>
                    <div><span>Fase Charging</span><b class="text-sky-300">{{ $status['charging_mode'] }}</b></div>
                    <div><span>Beban DC Aktif</span><b class="text-amber-300">{{ isset($loadManagement['load_power']) ? number_format($loadManagement['load_power'], 1).' W' : '-' }}</b></div>
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Load Management DC" shadow>
        <div class="scc-clean-load">
            <div>
                <div class="text-sm text-slate-400">Keputusan beban</div>
                <div class="mt-2 text-2xl font-semibold text-white">{{ $loadManagement['load_name'] ?? '-' }}</div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="scc-metric-state scc-metric-{{ $loadManagement['tone'] ?? 'unknown' }}">{{ $loadManagement['load_status'] ?? '-' }}</span>
                    <span class="scc-fuzzy-chip">Skor {{ isset($loadManagement['load_score']) ? number_format($loadManagement['load_score'], 1) : '-' }}</span>
                    <span class="scc-fuzzy-chip">Net {{ isset($loadManagement['net_power']) ? number_format($loadManagement['net_power'], 1).' W' : '-' }}</span>
                </div>
            </div>

            <div class="scc-clean-load-items">
                @foreach(($loadManagement['load_items'] ?? []) as $item)
                    <div>
                        <span>{{ $item['name'] }}</span>
                        <b>{{ $item['load_status'] }}</b>
                        <small>{{ number_format($item['load_power'], 1) }} W</small>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <div class="scc-clean-links">
        <a href="/scc/analysis" wire:navigate>
            <x-icon name="o-presentation-chart-line" class="h-5 w-5" />
            <span>Analisis Mamdani</span>
        </a>
        <a href="/scc/fuzzy" wire:navigate>
            <x-icon name="o-adjustments-horizontal" class="h-5 w-5" />
            <span>Membership Function</span>
        </a>
        <a href="/scc/rules" wire:navigate>
            <x-icon name="o-table-cells" class="h-5 w-5" />
            <span>Rule Base</span>
        </a>
        <a href="/scc/history" wire:navigate>
            <x-icon name="o-clock" class="h-5 w-5" />
            <span>Riwayat Data</span>
        </a>
    </div>

    <div wire:poll.visible.5000ms="refreshData"></div>
</div>
