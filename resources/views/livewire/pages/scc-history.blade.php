<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Monitoring Archive</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Riwayat Data SCC</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Seluruh data yang diterima dari perangkat disajikan dalam tabel yang lebih bersih, mudah dipindai, dan konsisten dengan dashboard utama.</p>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Total Hasil Filter</div>
                <div class="mt-2 text-2xl font-semibold text-white">{{ $summary['total'] }}</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Data Terbaru</div>
                <div class="mt-2 text-lg font-semibold text-white">{{ $summary['latest']?->created_at?->format('d M Y H:i:s') ?? '-' }}</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Fase Aktif Filter</div>
                <div class="mt-2 text-2xl font-semibold text-white">{{ $phase !== '' ? $phase : 'Semua' }}</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Export</div>
                <a href="{{ $this->exportUrl }}" class="mt-2 inline-flex text-sm font-semibold text-blue-300 hover:text-blue-200">Unduh CSV sesuai filter aktif</a>
            </div>
        </div>
    </section>

    <x-card shadow>
        <div class="scc-filters">
            <x-input
                label="Pencarian"
                placeholder="Cari ID, fase, label fuzzy..."
                wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass"
            />
            <x-select
                label="Fase Charging"
                wire:model.live="phase"
                :options="[
                    ['id' => '', 'name' => 'Semua Fase'],
                    ['id' => 'Bulk', 'name' => 'Bulk'],
                    ['id' => 'Absorption', 'name' => 'Absorption'],
                    ['id' => 'Float', 'name' => 'Float'],
                ]"
            />
            <x-input label="Tanggal Mulai" wire:model.live="startDate" type="date" />
            <x-input label="Tanggal Akhir" wire:model.live="endDate" type="date" />
            <div class="flex items-end gap-3">
                <x-button label="Reset Filter" wire:click="resetFilter" class="btn-ghost w-full" icon="o-arrow-path" />
            </div>
        </div>
    </x-card>

    <x-card shadow>
        <div class="scc-table-wrap overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th><button type="button" wire:click="sortBy('id')">#</button></th>
                        <th><button type="button" wire:click="sortBy('created_at')">Waktu</button></th>
                        <th><button type="button" wire:click="sortBy('vbat')">Vbat (V)</button></th>
                        <th><button type="button" wire:click="sortBy('vpv')">Vpv (V)</button></th>
                        <th><button type="button" wire:click="sortBy('ibat')">Ibat (A)</button></th>
                        <th><button type="button" wire:click="sortBy('soc')">SoC (%)</button></th>
                        <th><button type="button" wire:click="sortBy('duty_cycle')">Duty (%)</button></th>
                        <th><button type="button" wire:click="sortBy('fase')">Fase</button></th>
                        <th>Fuzzy E / dE</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                    <tr>
                        <td class="text-xs text-gray-400">{{ $row->id }}</td>
                        <td class="text-xs">{{ $row->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ number_format($row->vbat, 1) }}</td>
                        <td>{{ number_format($row->vpv, 1) }}</td>
                        <td>{{ number_format($row->ibat, 2) }}</td>
                        <td>{{ number_format($row->soc, 1) }}</td>
                        <td>{{ number_format($row->duty_cycle, 1) }}</td>
                        <td>
                            <span class="badge badge-sm {{ $row->fase == 'Bulk' ? 'badge-error' : ($row->fase == 'Absorption' ? 'badge-warning' : 'badge-success') }}">
                                {{ $row->fase }}
                            </span>
                        </td>
                        <td class="text-xs">{{ $row->label_e }} / {{ $row->label_de }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-gray-400">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $history->links() }}</div>
    </x-card>
</div>
