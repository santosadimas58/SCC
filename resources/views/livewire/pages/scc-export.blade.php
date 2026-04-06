<div>
    <x-header title="Export Data" subtitle="Unduh data monitoring SCC" separator />
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Informasi Data" shadow>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Total data tersimpan</span>
                    <span class="font-bold">{{ $total }} record</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Data terakhir masuk</span>
                    <span class="font-bold">{{ $latest ? $latest->created_at->format('d/m/Y H:i:s') : '-' }}</span>
                </div>
            </div>
        </x-card>
        <x-card title="Download" shadow>
            <div class="space-y-3">
                <a href="/scc/export/csv" class="btn btn-success w-full gap-2">
                    <x-icon name="o-arrow-down-tray" class="w-4 h-4" />
                    Export CSV
                </a>
                <p class="text-xs text-gray-400 text-center">Format CSV bisa dibuka di Excel / LibreOffice Calc</p>
            </div>
        </x-card>
    </div>
</div>
