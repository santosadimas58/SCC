<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Realtime Overview</div>
        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="scc-fuzzy-chip">Demo Mode</span>
                    <span class="text-xs text-slate-400">Data panel dan cuaca disimulasikan untuk kebutuhan presentasi.</span>
                </div>
                <h1 class="text-3xl font-semibold text-white">SCC Monitoring Dashboard</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">Pantau kesehatan charging system, fase pengisian, dan performa kontrol fuzzy dalam satu dashboard yang rapi dan mudah dibaca.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-button
                    label="Reset Demo"
                    icon="o-arrow-path"
                    class="btn-outline"
                    wire:click="resetDemoData"
                    wire:confirm="Reset demo akan menghapus seluruh data SCC dan mengisi ulang dataset presentasi. Lanjutkan?"
                />
                <div class="scc-status-pill {{ $status['online'] ? 'scc-status-online' : 'scc-status-offline' }}">
                    <span class="scc-status-dot"></span>
                    Status alat {{ $status['label'] }}
                </div>
                <div class="scc-soft-surface px-4 py-3 text-sm">
                    <div class="text-slate-400">Update terakhir</div>
                    <div class="mt-1 font-semibold text-white">{{ $status['last_update'] ?? 'Belum ada data' }}</div>
                </div>
            </div>
        </div>
        @if($demoResetMessage)
            <div class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                {{ $demoResetMessage }}
            </div>
        @endif
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Mode Charging</div>
                <div class="mt-2 text-2xl font-semibold text-white">{{ $status['charging_mode'] }}</div>
                <div class="mt-1 text-sm text-slate-400">Mode aktif berdasarkan fase pengisian terakhir.</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Freshness Data</div>
                <div class="mt-2 text-2xl font-semibold text-white">
                    {{ $status['freshness_label'] ?? '-' }}
                </div>
                <div class="mt-1 text-sm text-slate-400">{{ $status['message'] }}</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Ringkasan Harian</div>
                <div class="mt-2 text-2xl font-semibold text-white">{{ $dailySummary['records'] }} record</div>
                <div class="mt-1 text-sm text-slate-400">Dominan fase {{ $dailySummary['dominant_phase'] }} hari ini.</div>
            </div>
        </div>
    </section>

    @php
        $statusLabels = [
            'normal' => 'Normal',
            'warning' => 'Warning',
            'critical' => 'Critical',
            'info' => 'Info',
            'unknown' => 'Unknown',
        ];
        $socValue = $latest ? min(100, max(0, $latest->soc)) : 0;
    @endphp

    <div class="scc-dashboard-focus">
        <x-card title="Kondisi Baterai" shadow>
            <div class="scc-soc-panel">
                <div class="scc-soc-gauge scc-soc-{{ $performance['soc_status'] ?? 'unknown' }}" style="--soc: {{ $socValue }}%;">
                    <div class="scc-soc-gauge-inner">
                        <div class="text-3xl font-semibold text-white">{{ $latest ? number_format($latest->soc, 1) : '-' }}%</div>
                        <div class="text-xs uppercase tracking-[0.22em] text-slate-500">SoC</div>
                    </div>
                </div>
                <div class="scc-soc-meta">
                    <div>
                        <span class="text-slate-400">Status baterai</span>
                        <span class="scc-metric-state scc-metric-{{ $performance['soc_status'] ?? 'unknown' }}">
                            {{ $statusLabels[$performance['soc_status'] ?? 'unknown'] }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400">Daya baterai</span>
                        <span class="font-semibold text-white">{{ $performance['battery_power'] !== null ? number_format($performance['battery_power'], 1).' W' : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Fase aktif</span>
                        <span class="font-semibold text-white">{{ $status['charging_mode'] }}</span>
                    </div>
                </div>
            </div>
        </x-card>

        <div class="scc-control-stack">
            <x-card title="Kontrol Charging" shadow>
                <div class="scc-control-stack">
                    <div class="scc-phase-timeline scc-phase-timeline-compact">
                        @foreach($phaseTimeline as $phase)
                            <div class="scc-phase-step scc-phase-{{ $phase['state'] }}">
                                <div class="scc-phase-marker">
                                    @if($phase['state'] === 'completed')
                                        <x-icon name="o-check" class="h-4 w-4" />
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="scc-phase-title">{{ $phase['name'] }}</div>
                                    <div class="scc-phase-state">{{ $phase['state'] === 'active' ? 'Aktif' : ($phase['state'] === 'completed' ? 'Terlewati' : 'Menunggu') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="scc-fuzzy-decision scc-fuzzy-decision-compact">
                        <div class="scc-fuzzy-rule">
                            <div class="scc-fuzzy-rule-label">Keputusan pengisian saat ini</div>
                            <div class="scc-fuzzy-rule-text">{{ $fuzzyDecision['rule_text'] ?? 'Belum ada keputusan pengisian aktif.' }}</div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="scc-fuzzy-chip">Kondisi: {{ $fuzzyDecision['condition_text'] ?? '-' }}</span>
                                <span class="scc-fuzzy-chip">Perubahan: {{ $fuzzyDecision['change_text'] ?? '-' }}</span>
                                <span class="scc-fuzzy-chip">Aksi: {{ $fuzzyDecision['action_text'] ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="scc-fuzzy-output">
                            <div>
                                <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Daya Pengisian</div>
                                <div class="mt-2 text-4xl font-semibold text-white">
                                    {{ $fuzzyDecision['final_duty'] !== null ? number_format($fuzzyDecision['final_duty'], 1).' %' : '-' }}
                                </div>
                                <div class="mt-2 text-sm text-slate-400">{{ $fuzzyDecision['phase_note'] ?? '-' }}</div>
                            </div>
                            <div class="scc-fuzzy-output-base">
                                <span>Acuan awal</span>
                                <b>{{ $fuzzyDecision['base_output'] !== null ? number_format($fuzzyDecision['base_output'], 1).' %' : '-' }}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card title="Kontrol Beban DC" shadow>
                <div class="grid gap-4 md:grid-cols-[0.85fr_1.15fr]">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-5">
                        <div class="text-xs uppercase tracking-[0.22em] text-slate-500">Beban Aktif</div>
                        <div class="mt-2 text-2xl font-semibold text-white">{{ $loadManagement['load_name'] ?? '-' }}</div>
                        <div class="scc-metric-state scc-metric-{{ $loadManagement['tone'] ?? 'unknown' }}">
                            {{ $loadManagement['load_status'] ?? '-' }}
                        </div>
                    </div>
                    <div class="scc-info-list text-sm">
                        <div>
                            <span class="text-gray-400">Daya beban</span>
                            <span class="font-semibold text-white">{{ ($loadManagement['load_power'] ?? null) !== null ? number_format($loadManagement['load_power'], 1).' W' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Arus beban</span>
                            <span class="font-semibold text-white">{{ ($loadManagement['load_current'] ?? null) !== null ? number_format($loadManagement['load_current'], 3).' A' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Net power</span>
                            <span class="font-semibold text-white">{{ ($loadManagement['net_power'] ?? null) !== null ? number_format($loadManagement['net_power'], 1).' W' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400">Estimasi</span>
                            <span class="font-semibold text-white">{{ $loadManagement['energy_status'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-300">
                    {{ $loadManagement['load_reason'] ?? 'Menunggu data SCC terbaru.' }}
                </div>
            </x-card>
        </div>
    </div>

    <x-card title="Cuaca Setiabudhi" shadow>
        <div class="scc-weather-panel">
            <div class="scc-weather-current scc-weather-{{ $weather['current']['tone'] ?? 'unknown' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-[0.24em] text-slate-500">BMKG</div>
                        <div class="mt-2 text-2xl font-semibold text-white">{{ $weather['location_label'] ?? 'Setiabudhi, Bandung' }}</div>
                        <div class="mt-1 text-sm text-slate-400">Sumber wilayah: {{ $weather['source_location'] ?? 'Ledeng, Cidadap, Kota Bandung' }}</div>
                    </div>
                    <div class="scc-weather-icon">
                        <x-icon name="o-cloud" class="h-7 w-7" />
                    </div>
                </div>

                @if($weather['available'] ?? false)
                    <div class="mt-5 grid gap-4 md:grid-cols-[0.8fr_1.2fr]">
                        <div>
                            <div class="text-5xl font-semibold text-white">
                                {{ $weather['current']['temperature'] !== null ? number_format($weather['current']['temperature'], 0).'°C' : '-' }}
                            </div>
                            <div class="mt-2 text-xl font-semibold text-white">{{ $weather['current']['weather'] }}</div>
                            <div class="mt-1 text-sm text-slate-400">{{ $weather['current']['date'] }} pukul {{ $weather['current']['time'] }} WIB</div>
                        </div>
                        <div class="scc-weather-metrics">
                            <div><span>Kelembapan</span><b>{{ $weather['current']['humidity'] !== null ? number_format($weather['current']['humidity'], 0).' %' : '-' }}</b></div>
                            <div><span>Tutupan awan</span><b>{{ $weather['current']['cloud_cover'] !== null ? number_format($weather['current']['cloud_cover'], 0).' %' : '-' }}</b></div>
                            <div><span>Angin</span><b>{{ $weather['current']['wind_speed'] !== null ? number_format($weather['current']['wind_speed'], 1).' km/jam' : '-' }}</b></div>
                            <div><span>Arah angin</span><b>{{ $weather['current']['wind_direction'] ?? '-' }}</b></div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-300">
                        {{ $weather['solar_note'] }}
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                        <span class="scc-weather-sync scc-weather-sync-active">
                            Simulasi BMKG
                        </span>
                        <span class="scc-weather-sync {{ ($simulation['generated'] ?? false) ? 'scc-weather-sync-active' : '' }}">
                            {{ $simulation['message'] ?? 'Simulasi cuaca otomatis aktif.' }}
                        </span>
                        <span>Demo Mode: data panel dan cuaca disimulasikan untuk kebutuhan presentasi.</span>
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                        {{ $weather['message'] ?? 'Data cuaca BMKG belum tersedia.' }}
                    </div>
                @endif
            </div>

            <div class="scc-weather-forecast">
                <div class="text-sm font-semibold text-white">Konteks simulasi</div>
                <div class="mt-2 rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-300">
                    BMKG memberi konteks cuaca untuk simulasi panel. Data aktual yang dipakai fuzzy tetap berupa Vpv, Ipv, Vbat, SoC, error, dan delta error.
                </div>
                <div class="mt-3 text-xs text-slate-500">Update: {{ $weather['fetched_at'] ?? '-' }} · Kode wilayah: {{ $weather['adm4'] ?? '32.73.08.1003' }}</div>
            </div>
        </div>
    </x-card>

    <x-card title="Cuaca → Panel → Fuzzy → Baterai" shadow>
        <div class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="scc-fuzzy-chip">BMKG: {{ $weatherControlFlow['weather_label'] ?? '-' }}</span>
                    <span class="scc-fuzzy-chip">Fase: {{ $weatherControlFlow['phase'] ?? '-' }}</span>
                    <span class="scc-fuzzy-chip">Duty: {{ $weatherControlFlow['duty_cycle'] ?? '-' }}</span>
                </div>

                <div class="mt-5 text-xs uppercase tracking-[0.22em] text-slate-500">Interpretasi alur kontrol</div>
                <div class="mt-2 text-lg font-semibold leading-relaxed text-white">
                    {{ $weatherControlFlow['summary'] ?? 'Menunggu data cuaca dan data SCC terbaru.' }}
                </div>

                <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                    {{ $weatherControlFlow['reason'] ?? 'Fuzzy rule-based dengan output duty cycle diskrit akan menyesuaikan PWM berdasarkan kondisi panel dan baterai.' }}
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Cuaca Saat Ini</div>
                        <div class="mt-2 text-sm font-semibold text-white">{{ $weatherControlFlow['weather_label'] ?? '-' }}</div>
                        <div class="mt-1 text-xs text-slate-400">{{ $weatherControlFlow['weather_time'] ?? '-' }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Dampak Panel</div>
                        <div class="mt-2 text-sm font-semibold text-white">{{ $weatherControlFlow['panel_impact'] ?? '-' }}</div>
                        <div class="mt-1 text-xs text-slate-400">{{ $weatherControlFlow['panel_impact_detail'] ?? '-' }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                        <div class="text-xs uppercase tracking-[0.18em] text-slate-500">Keputusan Fuzzy</div>
                        <div class="mt-2 text-sm font-semibold text-white">{{ $weatherControlFlow['decision'] ?? '-' }}</div>
                        <div class="mt-1 text-xs text-slate-400">Fuzzy rule-based dengan output duty cycle diskrit.</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-5">
                <div class="text-sm font-semibold text-white">Data yang dibaca sistem</div>
                <div class="mt-4 scc-info-list text-sm">
                    @foreach(($weatherControlFlow['metrics'] ?? []) as $metric)
                        <div>
                            <span class="text-gray-400">{{ $metric['label'] }}</span>
                            <span class="font-semibold text-white">{{ $metric['value'] }}</span>
                        </div>
                    @endforeach
                    <div>
                        <span class="text-gray-400">Duty cycle PWM</span>
                        <span class="font-semibold text-white">{{ $weatherControlFlow['duty_cycle'] ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Fase charging</span>
                        <span class="font-semibold text-white">{{ $weatherControlFlow['phase'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <div class="scc-grid-stats">
        <x-card shadow class="scc-stat-card">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10">
                    <x-icon name="o-battery-100" class="w-8 h-8 text-success" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-gray-400">Tegangan Baterai</div>
                    <div class="scc-stat-value text-success">{{ $latest ? number_format($latest->vbat, 1).' V' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 11.8 - 14.4 V</div>
                    <div class="scc-metric-state scc-metric-{{ $groupedMetrics[1]['items'][0]['status'] ?? 'unknown' }}">
                        {{ $statusLabels[$groupedMetrics[1]['items'][0]['status'] ?? 'unknown'] }}
                    </div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-500/10">
                    <x-icon name="o-sun" class="w-8 h-8 text-warning" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-gray-400">Tegangan Panel</div>
                    <div class="scc-stat-value text-warning">{{ $latest ? number_format($latest->vpv, 1).' V' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 17.0 - 22.0 V</div>
                    <div class="scc-metric-state scc-metric-{{ $groupedMetrics[0]['items'][0]['status'] ?? 'unknown' }}">
                        {{ $statusLabels[$groupedMetrics[0]['items'][0]['status'] ?? 'unknown'] }}
                    </div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-500/10">
                    <x-icon name="o-bolt" class="w-8 h-8 text-info" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-gray-400">State of Charge</div>
                    <div class="scc-stat-value text-info">{{ $latest ? number_format($latest->soc, 1).' %' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 50 - 100 %</div>
                    <div class="scc-metric-state scc-metric-{{ $performance['soc_status'] ?? 'unknown' }}">
                        {{ $statusLabels[$performance['soc_status'] ?? 'unknown'] }}
                    </div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-500/10">
                    <x-icon name="o-cpu-chip" class="w-8 h-8 text-primary" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-gray-400">Duty Cycle PWM</div>
                    <div class="scc-stat-value text-primary">{{ $latest ? number_format($latest->duty_cycle, 1).' %' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 10 - 95 %</div>
                    <div class="scc-metric-state scc-metric-{{ $groupedMetrics[2]['items'][0]['status'] ?? 'unknown' }}">
                        {{ $statusLabels[$groupedMetrics[2]['items'][0]['status'] ?? 'unknown'] }}
                    </div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/10">
                    <x-icon name="o-bolt" class="w-8 h-8 text-warning" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-gray-400">Power Panel</div>
                    <div class="scc-stat-value text-warning">{{ $performance['panel_power'] !== null ? number_format($performance['panel_power'], 1).' W' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Vpv x Ipv</div>
                    <div class="scc-metric-state scc-metric-{{ $performance['panel_power_status'] ?? 'unknown' }}">
                        {{ $statusLabels[$performance['panel_power_status'] ?? 'unknown'] }}
                    </div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-start gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-500/10">
                    <x-icon name="o-arrow-trending-up" class="w-8 h-8 text-success" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-gray-400">Estimasi Efisiensi</div>
                    <div class="scc-stat-value text-success">{{ $performance['efficiency'] !== null ? number_format($performance['efficiency'], 1).' %' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Pbat / Ppanel</div>
                    <div class="scc-metric-state scc-metric-{{ $performance['efficiency_status'] ?? 'unknown' }}">
                        {{ $statusLabels[$performance['efficiency_status'] ?? 'unknown'] }}
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Navigasi Detail" shadow>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-4">
                <div class="text-sm font-semibold text-white">Real-time Data</div>
                <div class="mt-2 text-sm text-slate-400">Pantau pembacaan terbaru dan status alat.</div>
                <x-button label="Lihat Real-time Data" icon="o-signal" class="btn-outline btn-sm mt-4" link="/scc" />
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-4">
                <div class="text-sm font-semibold text-white">Riwayat Data</div>
                <div class="mt-2 text-sm text-slate-400">Buka tabel lengkap, filter, dan data historis.</div>
                <x-button label="Lihat Riwayat Data" icon="o-clock" class="btn-outline btn-sm mt-4" link="/scc/history" />
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-4">
                <div class="text-sm font-semibold text-white">Analisis Performa</div>
                <div class="mt-2 text-sm text-slate-400">Lihat distribusi fase dan tren duty cycle.</div>
                <x-button label="Lihat Analisis Performa" icon="o-presentation-chart-line" class="btn-outline btn-sm mt-4" link="/scc/analysis" />
            </div>
            <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-4">
                <div class="text-sm font-semibold text-white">Fuzzy Logic</div>
                <div class="mt-2 text-sm text-slate-400">Pelajari input, output, dan rule fuzzy.</div>
                <x-button label="Lihat Fuzzy Logic" icon="o-cpu-chip" class="btn-outline btn-sm mt-4" link="/scc/fuzzy" />
            </div>
        </div>
    </x-card>

    <div wire:poll.visible.5000ms="refreshData"></div>

</div>
