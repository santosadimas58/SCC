<div>
    <x-header title="SCC Monitoring Dashboard" separator />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card shadow>
            <div class="flex items-center gap-3">
                <x-icon name="o-battery-100" class="w-8 h-8 text-success" />
                <div>
                    <div class="text-xs text-gray-400">Tegangan Baterai</div>
                    <div class="text-2xl font-bold text-success">{{ $latest ? number_format($latest->vbat, 1).' V' : '-' }}</div>
                </div>
            </div>
        </x-card>
        <x-card shadow>
            <div class="flex items-center gap-3">
                <x-icon name="o-sun" class="w-8 h-8 text-warning" />
                <div>
                    <div class="text-xs text-gray-400">Tegangan Panel</div>
                    <div class="text-2xl font-bold text-warning">{{ $latest ? number_format($latest->vpv, 1).' V' : '-' }}</div>
                </div>
            </div>
        </x-card>
        <x-card shadow>
            <div class="flex items-center gap-3">
                <x-icon name="o-bolt" class="w-8 h-8 text-info" />
                <div>
                    <div class="text-xs text-gray-400">State of Charge</div>
                    <div class="text-2xl font-bold text-info">{{ $latest ? number_format($latest->soc, 1).' %' : '-' }}</div>
                </div>
            </div>
        </x-card>
        <x-card shadow>
            <div class="flex items-center gap-3">
                <x-icon name="o-cpu-chip" class="w-8 h-8 text-primary" />
                <div>
                    <div class="text-xs text-gray-400">Duty Cycle PWM</div>
                    <div class="text-2xl font-bold text-primary">{{ $latest ? number_format($latest->duty_cycle, 1).' %' : '-' }}</div>
                </div>
            </div>
        </x-card>
    </div>

    @if($latest)
    <x-card title="Status Pengisian" shadow class="mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <x-badge :value="$latest->fase" class="{{ $latest->fase == 'Bulk' ? 'badge-error' : ($latest->fase == 'Absorption' ? 'badge-warning' : 'badge-success') }} badge-lg" />
            <span class="text-sm"><span class="text-gray-400">Label Fuzzy:</span> <b>E = {{ $latest->label_e }}</b> | <b>dE = {{ $latest->label_de }}</b></span>
            <span class="text-sm"><span class="text-gray-400">Arus Baterai:</span> <b>{{ number_format($latest->ibat, 2) }} A</b></span>
            <span class="text-sm"><span class="text-gray-400">Daya Panel:</span> <b>{{ number_format($latest->vpv * $latest->ipv, 1) }} W</b></span>
        </div>
    </x-card>
    @endif

    {{-- Grafik dengan wire:ignore agar tidak dihapus Livewire --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6" wire:ignore>
        <x-card title="Grafik Tegangan" shadow>
            <canvas id="chartVoltage" height="120"></canvas>
        </x-card>
        <x-card title="Grafik SoC and Duty Cycle" shadow>
            <canvas id="chartSoc" height="120"></canvas>
        </x-card>
    </div>

    <x-card title="Histori Data (20 terakhir)" shadow>
        <div class="overflow-x-auto">
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
                        { label: 'Vpv (V)', data: data.map(d => d.vpv), borderColor: '#fbbd23', backgroundColor: '#fbbd2320', tension: 0.4, fill: true }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: false } } }
            });

            csChart = new Chart(document.getElementById('chartSoc'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'SoC (%)', data: data.map(d => d.soc), borderColor: '#38bdf8', backgroundColor: '#38bdf820', tension: 0.4, fill: true },
                        { label: 'Duty (%)', data: data.map(d => d.duty_cycle), borderColor: '#818cf8', backgroundColor: '#818cf820', tension: 0.4, fill: true }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: false } } }
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
