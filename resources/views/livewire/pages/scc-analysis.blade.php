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

    <x-card title="Interpretasi Cepat" shadow>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="scc-note">Duty cycle cenderung tinggi saat SoC masih rendah karena sistem mendorong fase Bulk untuk mempercepat pengisian.</div>
            <div class="scc-note">Saat SoC naik, duty cycle idealnya mulai turun karena sistem masuk Absorption atau Float.</div>
            <div class="scc-note">Distribusi fase membantu melihat apakah sistem lebih sering charging aktif, menjaga baterai, atau berada pada kondisi Standby.</div>
        </div>
    </x-card>
</div>
