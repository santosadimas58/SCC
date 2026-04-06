<div>
    <x-header title="Riwayat Data SCC" subtitle="Seluruh data yang diterima dari perangkat" separator />
    <x-card shadow>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Vbat (V)</th>
                        <th>Vpv (V)</th>
                        <th>Ibat (A)</th>
                        <th>SoC (%)</th>
                        <th>Duty (%)</th>
                        <th>Fase</th>
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
