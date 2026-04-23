<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Realtime Overview</div>
        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-white">SCC Monitoring Dashboard</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-300">Pantau kesehatan charging system, fase pengisian, dan performa kontrol fuzzy dalam satu dashboard yang rapi dan mudah dibaca.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
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
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Mode Charging</div>
                <div class="mt-2 text-2xl font-semibold text-white">{{ $status['charging_mode'] }}</div>
                <div class="mt-1 text-sm text-slate-400">Mode aktif berdasarkan fase pengisian terakhir.</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Freshness Data</div>
                <div class="mt-2 text-2xl font-semibold text-white">
                    {{ $status['seconds_since_update'] !== null ? $status['seconds_since_update'].' dtk' : '-' }}
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

    <div class="scc-grid-stats">
        <x-card shadow class="scc-stat-card">
            <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10">
                    <x-icon name="o-battery-100" class="w-8 h-8 text-success" />
                </div>
                <div>
                    <div class="text-xs text-gray-400">Tegangan Baterai</div>
                    <div class="scc-stat-value text-success">{{ $latest ? number_format($latest->vbat, 1).' V' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 11.8 - 14.4 V</div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-500/10">
                    <x-icon name="o-sun" class="w-8 h-8 text-warning" />
                </div>
                <div>
                    <div class="text-xs text-gray-400">Tegangan Panel</div>
                    <div class="scc-stat-value text-warning">{{ $latest ? number_format($latest->vpv, 1).' V' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 17.0 - 22.0 V</div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-500/10">
                    <x-icon name="o-bolt" class="w-8 h-8 text-info" />
                </div>
                <div>
                    <div class="text-xs text-gray-400">State of Charge</div>
                    <div class="scc-stat-value text-info">{{ $latest ? number_format($latest->soc, 1).' %' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 50 - 100 %</div>
                </div>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-500/10">
                    <x-icon name="o-cpu-chip" class="w-8 h-8 text-primary" />
                </div>
                <div>
                    <div class="text-xs text-gray-400">Duty Cycle PWM</div>
                    <div class="scc-stat-value text-primary">{{ $latest ? number_format($latest->duty_cycle, 1).' %' : '-' }}</div>
                    <div class="mt-1 text-xs text-slate-500">Ideal 10 - 95 %</div>
                </div>
            </div>
        </x-card>
    </div>

    @if($latest)
    <x-card title="Status Pengisian" shadow>
        <div class="flex flex-wrap items-center gap-4">
            <x-badge :value="$latest->fase" class="{{ $latest->fase == 'Bulk' ? 'badge-error' : ($latest->fase == 'Absorption' ? 'badge-warning' : 'badge-success') }} badge-lg" />
            <span class="text-sm"><span class="text-gray-400">Label Fuzzy:</span> <b>E = {{ $latest->label_e }}</b> | <b>dE = {{ $latest->label_de }}</b></span>
            <span class="text-sm"><span class="text-gray-400">Arus Baterai:</span> <b>{{ number_format($latest->ibat, 2) }} A</b></span>
            <span class="text-sm"><span class="text-gray-400">Daya Panel:</span> <b>{{ number_format($latest->vpv * $latest->ipv, 1) }} W</b></span>
        </div>
    </x-card>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <x-card title="Monitoring Real-Time" shadow>
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <div class="scc-status-pill {{ $status['online'] ? 'scc-status-live' : 'scc-status-offline' }}">
                    <span class="scc-status-dot"></span>
                    {{ $status['online'] ? 'Live update aktif' : 'Data tidak diperbarui' }}
                </div>
                <div class="text-sm text-slate-400">Timestamp data terakhir: <span class="font-semibold text-white">{{ $status['last_update'] ?? '-' }}</span></div>
            </div>

            @unless($status['online'])
                <div class="mb-4 rounded-2xl border border-red-400/15 bg-red-500/10 p-4 text-sm text-red-200">
                    Data SCC tidak berubah dalam {{ $status['seconds_since_update'] ?? '-' }} detik. Periksa koneksi mikrokontroler, sensor, atau endpoint data.
                </div>
            @endunless

            <div class="scc-mini-grid">
                @foreach($groupedMetrics as $group)
                    <div class="scc-metric-panel">
                        <div class="text-sm font-semibold text-white">{{ $group['title'] }}</div>
                        @foreach($group['items'] as $item)
                            <div class="scc-metric-item">
                                <div class="scc-metric-label">{{ $item['label'] }}</div>
                                <div class="scc-metric-value">{{ $item['value'] }}</div>
                                <div class="scc-metric-range">{{ $item['range'] }}</div>
                                <div class="scc-metric-state scc-metric-{{ $item['status'] }}">
                                    {{ $item['status'] === 'normal' ? 'Normal' : ($item['status'] === 'warning' ? 'Warning' : ($item['status'] === 'critical' ? 'Critical' : 'Informasi')) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Ringkasan Harian" shadow>
            <div class="scc-info-list text-sm">
                <div><span class="text-gray-400">Jumlah record hari ini</span><span class="font-semibold text-white">{{ $dailySummary['records'] }}</span></div>
                <div><span class="text-gray-400">Rata-rata tegangan baterai</span><span class="font-semibold text-white">{{ $dailySummary['avg_vbat'] ? number_format($dailySummary['avg_vbat'], 2).' V' : '-' }}</span></div>
                <div><span class="text-gray-400">Rata-rata SoC</span><span class="font-semibold text-white">{{ $dailySummary['avg_soc'] ? number_format($dailySummary['avg_soc'], 2).' %' : '-' }}</span></div>
                <div><span class="text-gray-400">Daya panel maksimum</span><span class="font-semibold text-white">{{ $dailySummary['max_power'] ? number_format($dailySummary['max_power'], 2).' W' : '-' }}</span></div>
                <div><span class="text-gray-400">Fase dominan</span><span class="font-semibold text-white">{{ $dailySummary['dominant_phase'] }}</span></div>
            </div>
        </x-card>
    </div>

    {{-- Grafik dengan wire:ignore agar tidak dihapus Livewire --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2" wire:ignore>
        <x-card title="Grafik Tegangan" shadow>
            <canvas id="chartVoltage" height="120"></canvas>
        </x-card>
        <x-card title="Grafik SoC and Duty Cycle" shadow>
            <canvas id="chartSoc" height="120"></canvas>
        </x-card>
    </div>

    <x-card title="Histori Data (20 terakhir)" shadow>
        <div class="scc-table-wrap overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Vbat (V)</th>
                        <th>Vpv (V)</th>
                        <th>Arus (A)</th>
                        <th>SoC (%)</th>
                        <th>Duty (%)</th>
                        <th>Fase</th>
                        <th>Fuzzy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                    <tr>
                        <td class="text-xs">{{ $row->created_at->format('H:i:s') }}</td>
                        <td>{{ number_format($row->vbat, 1) }}</td>
                        <td>{{ number_format($row->vpv, 1) }}</td>
                        <td>{{ number_format($row->ibat, 2) }}</td>
                        <td>{{ number_format($row->soc, 1) }}</td>
                        <td>{{ number_format($row->duty_cycle, 1) }}</td>
                        <td><span class="badge badge-sm {{ $row->fase == 'Bulk' ? 'badge-error' : ($row->fase == 'Absorption' ? 'badge-warning' : 'badge-success') }}">{{ $row->fase }}</span></td>
                        <td class="text-xs">{{ $row->label_e }}/{{ $row->label_de }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-gray-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <div wire:poll.5000ms="refreshData"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        let cvChart = null;
        let csChart = null;
        const chartTheme = window.SCCTheme?.chart ?? {};

        function initCharts(data) {
            const labels = data.map(d => new Date(d.created_at).toLocaleTimeString());

            if (cvChart) { cvChart.destroy(); cvChart = null; }
            if (csChart) { csChart.destroy(); csChart = null; }

            cvChart = new Chart(document.getElementById('chartVoltage'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'Vbat (V)', data: data.map(d => d.vbat), borderColor: '#36d399', backgroundColor: '#36d39920', tension: 0.4, fill: true },
                        { label: 'Vpv (V)', data: data.map(d => d.vpv), borderColor: chartTheme.panel || '#a78bfa', backgroundColor: '#a78bfa20', tension: 0.4, fill: true }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { color: chartTheme.labelColor || '#cbd5f5' }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: chartTheme.titleColor || '#f8fafc',
                            bodyColor: chartTheme.labelColor || '#cbd5f5',
                            borderColor: 'rgba(96, 165, 250, 0.22)',
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Waktu', color: chartTheme.labelColor || '#cbd5f5' },
                            ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                            grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' }
                        },
                        y: {
                            beginAtZero: false,
                            title: { display: true, text: 'Tegangan (Volt)', color: chartTheme.labelColor || '#cbd5f5' },
                            ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                            grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' }
                        }
                    }
                }
            });

            csChart = new Chart(document.getElementById('chartSoc'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'SoC (%)', data: data.map(d => d.soc), borderColor: chartTheme.soc || '#60a5fa', backgroundColor: '#60a5fa20', tension: 0.4, fill: true },
                        { label: 'Duty (%)', data: data.map(d => d.duty_cycle), borderColor: chartTheme.duty || '#8b5cf6', backgroundColor: '#8b5cf620', tension: 0.4, fill: true }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { color: chartTheme.labelColor || '#cbd5f5' }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: chartTheme.titleColor || '#f8fafc',
                            bodyColor: chartTheme.labelColor || '#cbd5f5',
                            borderColor: 'rgba(96, 165, 250, 0.22)',
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Waktu', color: chartTheme.labelColor || '#cbd5f5' },
                            ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                            grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' }
                        },
                        y: {
                            beginAtZero: false,
                            title: { display: true, text: 'Persentase (%)', color: chartTheme.labelColor || '#cbd5f5' },
                            ticks: { color: chartTheme.labelColor || '#cbd5f5' },
                            grid: { color: chartTheme.gridColor || 'rgba(148, 163, 184, 0.14)' }
                        }
                    }
                }
            });
        }

        function fetchAndUpdate() {
            fetch('/api/scc/history')
                .then(r => r.json())
                .then(res => {
                    if (res.data && res.data.length) {
                        initCharts(res.data.reverse());
                    }
                });
        }

        fetchAndUpdate();
        setInterval(fetchAndUpdate, 5000);
    </script>
</div>
