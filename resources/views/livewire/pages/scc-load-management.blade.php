<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Load Management</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Kontrol Beban DC</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Pantau prioritas Lampu, Kipas, dan Pompa DC berdasarkan skor fuzzy energi, status proteksi, konsumsi daya, arus beban, dan surplus atau defisit energi dari data SCC terbaru.</p>
    </section>

    <div class="scc-grid-stats">
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Beban Diizinkan</div>
            <div class="scc-stat-value mt-2">{{ $load['load_name'] }}</div>
            <div class="mt-2">
                <span class="scc-metric-state scc-metric-{{ $load['tone'] }}">{{ $load['load_status'] }}</span>
            </div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Daya Beban</div>
            <div class="scc-stat-value mt-2">{{ $load['load_power'] !== null ? number_format($load['load_power'], 1).' W' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Konsumsi beban saat ini</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Arus Beban</div>
            <div class="scc-stat-value mt-2">{{ $load['load_current'] !== null ? number_format($load['load_current'], 3).' A' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">Daya beban / Vbat</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Net Power</div>
            <div class="scc-stat-value mt-2">{{ $load['net_power'] !== null ? number_format($load['net_power'], 1).' W' : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">{{ $load['energy_status'] }}</div>
        </x-card>
        <x-card shadow class="scc-stat-card">
            <div class="text-xs text-gray-400">Skor Fuzzy Beban</div>
            <div class="scc-stat-value mt-2">{{ isset($load['load_score']) ? number_format($load['load_score'], 1) : '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">SoC + daya panel + fase charging</div>
        </x-card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <x-card title="Keputusan Beban Saat Ini" shadow>
            <div class="scc-info-list text-sm">
                <div>
                    <span class="text-gray-400">SoC baterai</span>
                    <span class="font-semibold text-white">{{ $latest ? number_format($latest->soc, 1).' %' : '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Fase charging</span>
                    <span class="font-semibold text-white">{{ $latest?->fase ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Daya panel</span>
                    <span class="font-semibold text-white">{{ $latest ? number_format($latest->vpv * $latest->ipv, 1).' W' : '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Estimasi energi</span>
                    <span class="font-semibold text-white">{{ $load['energy_status'] }}</span>
                </div>
            </div>
            <div class="mt-4 rounded-2xl border border-white/10 bg-slate-950/35 px-4 py-3 text-sm text-slate-300">
                {{ $load['load_reason'] }}
            </div>
        </x-card>

        <x-card title="Prioritas Fuzzy Tiap Beban" shadow>
            <div class="grid gap-3">
                @foreach(($load['load_items'] ?? []) as $item)
                    <div class="rounded-2xl border border-white/10 bg-slate-950/35 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-white">{{ $item['name'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">Prioritas {{ $item['priority'] }} · Alokasi {{ $item['allocation'] }}%</div>
                            </div>
                            <span class="scc-metric-state scc-metric-{{ ['ON' => 'normal', 'LIMITED' => 'warning', 'OFF' => 'critical'][$item['load_status']] ?? 'unknown' }}">{{ $item['load_status'] }}</span>
                        </div>
                        <div class="mt-3 grid gap-3 text-sm md:grid-cols-3">
                            <div>
                                <div class="text-xs text-slate-500">Daya</div>
                                <div class="font-semibold text-white">{{ number_format($item['load_power'], 1) }} W</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Arus</div>
                                <div class="font-semibold text-white">{{ number_format($item['load_current'], 3) }} A</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500">Daya penuh</div>
                                <div class="font-semibold text-white">{{ number_format($item['base_power'], 1) }} W</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-400">{{ $item['reason'] }}</div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <x-card title="Ringkasan 50 Data Terbaru" shadow>
            <div class="grid gap-3 md:grid-cols-3">
                <div class="scc-note">
                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">ON</div>
                    <div class="mt-2 text-2xl font-semibold text-white">{{ $summary['on_count'] }}</div>
                </div>
                <div class="scc-note">
                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">LIMITED</div>
                    <div class="mt-2 text-2xl font-semibold text-white">{{ $summary['limited_count'] }}</div>
                </div>
                <div class="scc-note">
                    <div class="text-xs uppercase tracking-[0.18em] text-slate-500">OFF</div>
                    <div class="mt-2 text-2xl font-semibold text-white">{{ $summary['off_count'] }}</div>
                </div>
            </div>
            <div class="mt-4 scc-info-list text-sm">
                <div>
                    <span class="text-gray-400">Rata-rata daya beban</span>
                    <span class="font-semibold text-white">{{ $summary['avg_load_power'] !== null ? number_format($summary['avg_load_power'], 1).' W' : '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Rata-rata net power</span>
                    <span class="font-semibold text-white">{{ $summary['avg_net_power'] !== null ? number_format($summary['avg_net_power'], 1).' W' : '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Rata-rata skor fuzzy</span>
                    <span class="font-semibold text-white">{{ $summary['avg_score'] !== null ? number_format($summary['avg_score'], 1) : '-' }}</span>
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Riwayat Beban DC" shadow>
        <div class="scc-table-wrap overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Beban</th>
                        <th>Status</th>
                        <th>SoC</th>
                        <th>Fase</th>
                        <th>Daya Beban</th>
                        <th>Daya Panel</th>
                        <th>Net Power</th>
                        <th>Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                        <tr>
                            <td>{{ $row['time']?->setTimezone('Asia/Jakarta')->format('d M H:i:s') }} WIB</td>
                            <td>{{ $row['load_name'] }}</td>
                            <td><span class="scc-metric-state scc-metric-{{ $row['tone'] }}">{{ $row['load_status'] }}</span></td>
                            <td>{{ number_format($row['soc'], 1) }} %</td>
                            <td>{{ $row['phase'] }}</td>
                            <td>{{ number_format($row['load_power'], 1) }} W</td>
                            <td>{{ number_format($row['panel_power'], 1) }} W</td>
                            <td>{{ number_format($row['net_power'], 1) }} W</td>
                            <td>{{ number_format($row['load_score'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="scc-empty">Belum ada data beban DC.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
