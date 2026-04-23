<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Data Delivery</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Export Data</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-300">Unduh data monitoring SCC dengan ringkasan status penyimpanan yang konsisten dengan tampilan dashboard dan halaman analisis.</p>
    </section>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-card title="Informasi Data" shadow>
            <div class="scc-info-list text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Total data tersimpan</span>
                    <span class="font-bold">{{ $total }} record</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Data terakhir masuk</span>
                    <span class="font-bold">{{ $latest ? $latest->created_at->format('d/m/Y H:i:s') : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Record hari ini</span>
                    <span class="font-bold">{{ $todayCount }}</span>
                </div>
            </div>
        </x-card>
        <x-card title="Ringkasan Dataset" shadow>
            <div class="space-y-3 text-sm">
                @forelse($phaseCounts as $phase => $count)
                    <div class="flex items-center justify-between rounded-2xl border border-white/8 bg-slate-950/35 px-4 py-3">
                        <span class="text-slate-300">{{ $phase }}</span>
                        <span class="font-semibold text-white">{{ $count }} record</span>
                    </div>
                @empty
                    <div class="scc-empty">Belum ada distribusi data.</div>
                @endforelse
            </div>
        </x-card>
        <x-card title="Opsi Export" shadow>
            <div class="space-y-3">
                <div id="export-feedback" class="scc-note hidden"></div>
                <div class="scc-export-actions">
                    <button type="button" class="btn btn-primary flex-1 border-0" data-export-url="/scc/export/csv">
                        <x-icon name="o-arrow-down-tray" class="w-4 h-4" />
                        Export seluruh data
                    </button>
                    <a href="/scc/history" class="btn btn-outline flex-1">
                        <x-icon name="o-funnel" class="w-4 h-4" />
                        Buka filter riwayat
                    </a>
                </div>
                <p class="text-xs text-gray-400 text-center">Format CSV dapat dibuka di Microsoft Excel, LibreOffice Calc, atau perangkat analisis data lainnya.</p>
            </div>
        </x-card>
    </div>

    <x-card title="Preview Data Terbaru" shadow>
        <div class="scc-table-wrap overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Vbat</th>
                        <th>Vpv</th>
                        <th>SoC</th>
                        <th>Duty</th>
                        <th>Fase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preview as $row)
                        <tr>
                            <td>{{ $row->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ number_format($row->vbat, 1) }} V</td>
                            <td>{{ number_format($row->vpv, 1) }} V</td>
                            <td>{{ number_format($row->soc, 1) }} %</td>
                            <td>{{ number_format($row->duty_cycle, 1) }} %</td>
                            <td><span class="badge {{ $row->fase === 'Bulk' ? 'badge-error' : ($row->fase === 'Absorption' ? 'badge-warning' : 'badge-success') }}">{{ $row->fase }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6"><div class="scc-empty">Belum ada data untuk ditampilkan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-export-url]').forEach((button) => {
        button.addEventListener('click', async () => {
            const feedback = document.getElementById('export-feedback');
            const url = button.dataset.exportUrl;
            const original = button.innerHTML;

            feedback.classList.remove('hidden');
            feedback.textContent = 'Menyiapkan file export...';
            button.disabled = true;

            try {
                const response = await fetch(url, { credentials: 'same-origin' });
                if (!response.ok) {
                    throw new Error('Gagal menyiapkan file export.');
                }

                const blob = await response.blob();
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = objectUrl;
                link.download = 'scc-data-export.csv';
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(objectUrl);

                feedback.textContent = 'Export berhasil dimulai. File CSV sedang diunduh.';
            } catch (error) {
                feedback.textContent = 'Export gagal diproses. Periksa koneksi atau coba lagi.';
            } finally {
                button.disabled = false;
                button.innerHTML = original;
            }
        });
    });
</script>
@endpush
