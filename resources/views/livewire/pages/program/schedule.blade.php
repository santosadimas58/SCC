<div>
    <x-header title="Jadwal" subtitle="Manajemen jadwal pelajaran" separator>
        <x-slot:actions>
            <x-button label="+ Tambah Jadwal" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    {{-- Search & Filter --}}
    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <x-input
            placeholder="Cari mata pelajaran, guru, ruangan..."
            wire:model.live.debounce.300ms="search"
            icon="o-magnifying-glass"
            class="flex-1 min-w-[200px]"
        />
        <x-select
            placeholder="Semua Hari"
            wire:model.live="filterHari"
            :options="[
                ['id' => 'Senin',   'name' => 'Senin'],
                ['id' => 'Selasa',  'name' => 'Selasa'],
                ['id' => 'Rabu',    'name' => 'Rabu'],
                ['id' => 'Kamis',   'name' => 'Kamis'],
                ['id' => 'Jumat',   'name' => 'Jumat'],
                ['id' => 'Sabtu',   'name' => 'Sabtu'],
            ]"
            class="w-40"
        />
        <x-select
            placeholder="Semua Status"
            wire:model.live="filterStatus"
            :options="[
                ['id' => 'Aktif',    'name' => 'Aktif'],
                ['id' => 'Nonaktif', 'name' => 'Nonaktif'],
            ]"
            class="w-40"
        />
        @if($search || $filterHari || $filterStatus)
        <x-button label="Reset" wire:click="resetFilter" class="btn-ghost btn-sm" icon="o-x-mark" />
        @endif
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Ruangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $i => $schedule)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $schedule->hari }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }}</td>
                        <td>{{ $schedule->mata_pelajaran }}</td>
                        <td>{{ $schedule->guru }}</td>
                        <td>{{ $schedule->ruangan ?? '-' }}</td>
                        <td>
                            <x-badge
                                :value="$schedule->status"
                                class="{{ $schedule->status === 'Aktif' ? 'badge-success' : 'badge-error' }}"
                            />
                        </td>
                        <td class="flex gap-2">
                            <x-button label="Edit" wire:click="edit({{ $schedule->id }})" class="btn-sm btn-info" icon="o-pencil" />
                            <x-button label="Hapus" wire:click="delete({{ $schedule->id }})" wire:confirm="Yakin ingin menghapus jadwal ini?" class="btn-sm btn-error" icon="o-trash" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-6">
                            {{ $search || $filterHari || $filterStatus ? 'Tidak ada hasil yang cocok.' : 'Belum ada jadwal.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->count() > 0)
        <div class="text-sm text-gray-400 mt-3">
            Menampilkan {{ $schedules->count() }} jadwal
        </div>
        @endif
    </x-card>

    {{-- Modal --}}
    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Jadwal' : 'Tambah Jadwal'" separator>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-select
                label="Hari"
                wire:model="hari"
                :options="[
                    ['id' => 'Senin',  'name' => 'Senin'],
                    ['id' => 'Selasa', 'name' => 'Selasa'],
                    ['id' => 'Rabu',   'name' => 'Rabu'],
                    ['id' => 'Kamis',  'name' => 'Kamis'],
                    ['id' => 'Jumat',  'name' => 'Jumat'],
                    ['id' => 'Sabtu',  'name' => 'Sabtu'],
                ]"
                placeholder="Pilih hari"
            />
            <x-input label="Mata Pelajaran" wire:model="mata_pelajaran" placeholder="Contoh: Matematika" />
            <x-input label="Jam Mulai" wire:model="jam_mulai" type="time" />
            <x-input label="Jam Selesai" wire:model="jam_selesai" type="time" />
            <x-input label="Guru" wire:model="guru" placeholder="Nama guru" />
            <x-input label="Ruangan" wire:model="ruangan" placeholder="Contoh: Ruang A1 (opsional)" />
            <x-select
                label="Status"
                wire:model="status"
                :options="[
                    ['id' => 'Aktif',    'name' => 'Aktif'],
                    ['id' => 'Nonaktif', 'name' => 'Nonaktif'],
                ]"
            />
        </div>
        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" />
            <x-button :label="$editMode ? 'Update' : 'Simpan'" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
