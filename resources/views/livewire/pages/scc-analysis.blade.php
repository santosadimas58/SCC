<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Performance Analysis</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Analisis Performa SCC</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Evaluasi ringkas performa charging, distribusi fase, dan hubungan duty cycle terhadap state of charge dari data monitoring terbaru.</p>
    </section>

    <div class="scc-grid-stats">
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Sampel Analisis</div>
            <div class="scc-stat-value mt-2">{{ $summary['records'] }} data</div>
            <div class="mt-1 text-xs text-slate-500">Maksimal 200 data terbaru</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Rata-rata SoC</div>
            <div class="scc-stat-value mt-2">{{ $summary['avg_soc'] !== null ? number_format($summary['avg_soc'], 1).' %' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Kondisi kapasitas baterai</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Rata-rata Duty</div>
            <div class="scc-stat-value mt-2">{{ $summary['avg_duty'] !== null ? number_format($summary['avg_duty'], 1).' %' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Aksi kendali PWM</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Rata-rata Power Panel</div>
            <div class="scc-stat-value mt-2">{{ $summary['avg_panel_power'] !== null ? number_format($summary['avg_panel_power'], 1).' W' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Vpv x Ipv</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Efisiensi Rata-rata</div>
            <div class="scc-stat-value mt-2">{{ $summary['avg_efficiency'] !== null ? number_format($summary['avg_efficiency'], 1).' %' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Pbat / Ppanel</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Power Panel Maksimum</div>
            <div class="scc-stat-value mt-2">{{ $summary['max_panel_power'] !== null ? number_format($summary['max_panel_power'], 1).' W' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Puncak input panel</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Rata-rata |Error|</div>
            <div class="scc-stat-value mt-2">{{ $controlComparison['avg_abs_error'] !== null ? number_format($controlComparison['avg_abs_error'], 2).' V' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Jarak Vbat ke target fase</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Step Duty Mamdani</div>
            <div class="scc-stat-value mt-2">{{ $controlComparison['mamdani_avg_step'] !== null ? number_format($controlComparison['mamdani_avg_step'], 1).' %' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Lonjakan saat PWM berubah</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Lebih Halus dari Threshold</div>
            <div class="scc-stat-value mt-2">{{ $controlComparison['smoothness_gain'] !== null ? number_format($controlComparison['smoothness_gain'], 1).' %' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Pembanding kontrol bertingkat</div>
        </x-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <x-card title="Distribusi Fase Charging" shadow>
            <div class="scc-phase-distribution">
                @foreach($phaseDistribution as $item)
                    <div class="scc-phase-bar-row">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-white">{{ $item['phase'] }}</span>
                            <span class="text-sm text-slate-400">{{ $item['count'] }} data · {{ number_format($item['percentage'], 1) }}%</span>
                        </div>
                        <div class="scc-phase-bar-track">
                            <div class="scc-phase-bar-fill scc-phase-fill-{{ strtolower($item['phase']) }}" style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Tren Duty Cycle terhadap SoC" shadow>
            <script type="application/json" id="scc-duty-soc-data">
                @json($dutySocPoints)
            </script>
            <div class="scc-chart-frame">
                <canvas id="chartDutySoc" height="145"></canvas>
            </div>
        </x-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <x-card title="Respons Error Mamdani" shadow>
            <script type="application/json" id="scc-mamdani-timeline-data">
                @json($mamdaniTimeline)
            </script>
            <div class="scc-chart-frame">
                <canvas id="chartMamdaniError" height="145"></canvas>
            </div>
            <div class="mt-3 text-xs text-slate-500">
                Error dihitung dari Vref fase charging dikurangi Vbat. Delta error menunjukkan perubahan error antar sampel.
            </div>
        </x-card>

        <x-card title="Mamdani vs Kontrol Threshold" shadow>
            <div class="scc-chart-frame">
                <canvas id="chartMamdaniComparison" height="145"></canvas>
            </div>
            <div class="mt-3 text-xs text-slate-500">
                Threshold dipakai sebagai baseline sederhana. Nilai Mamdani berasal dari inferensi min-max dan defuzzifikasi centroid.
            </div>
        </x-card>
    </div>

    <x-card title="Bukti Inferensi Mamdani pada Sampel Terbaru" shadow>
        @if($mamdaniExample)
            <div class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="scc-fuzzy-output">
                    <div>
                        <div class="scc-fuzzy-rule-label">Sampel {{ $mamdaniExample['time'] ?? '-' }} · {{ $mamdaniExample['phase'] ?? '-' }}</div>
                        <div class="scc-fuzzy-rule-text">
                            e = {{ number_format($mamdaniExample['error'], 3) }} V,
                            de = {{ number_format($mamdaniExample['delta_error'], 3) }}
                        </div>
                    </div>
                    <div class="scc-fuzzy-output-base">
                        <span>Centroid Mamdani</span>
                        <b>{{ number_format($mamdaniExample['mamdani_centroid'], 2) }} %</b>
                    </div>
                    <div class="scc-fuzzy-output-base">
                        <span>Duty akhir setelah batas fase</span>
                        <b>{{ number_format($mamdaniExample['final_duty'], 2) }} %</b>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="scc-note">
                        <div class="font-semibold text-white">Membership Error</div>
                        <div class="mt-3 grid gap-2">
                            @foreach($mamdaniExample['error_memberships'] as $label => $value)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="scc-membership-badge scc-tone-info">{{ $label }}</span>
                                    <span>{{ number_format($value, 3) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="scc-note">
                        <div class="font-semibold text-white">Membership Delta Error</div>
                        <div class="mt-3 grid gap-2">
                            @foreach($mamdaniExample['delta_memberships'] as $label => $value)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="scc-membership-badge scc-tone-warning">{{ $label }}</span>
                                    <span>{{ number_format($value, 3) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="scc-note">
                        <div class="font-semibold text-white">Agregasi Output</div>
                        <div class="mt-3 grid gap-2">
                            @foreach($mamdaniExample['rule_strengths'] as $label => $value)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="scc-membership-badge scc-tone-success">{{ $label }}</span>
                                    <span>{{ number_format($value, 3) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="scc-note">Belum ada sampel aktif untuk menjelaskan inferensi Mamdani.</div>
        @endif
    </x-card>

    <x-card title="Interpretasi Cepat" shadow>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach($quickInsights as $insight)
                <div class="scc-note">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="font-semibold text-white">{{ $insight['title'] }}</span>
                        <span class="scc-metric-state scc-metric-{{ $insight['tone'] }}">{{ ucfirst($insight['tone']) }}</span>
                    </div>
                    <div>{{ $insight['body'] }}</div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>
