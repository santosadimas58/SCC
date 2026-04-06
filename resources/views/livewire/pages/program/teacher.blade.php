<div>
    <x-header title="Data Guru" subtitle="Manajemen data guru" separator>
        <x-slot:actions>
            <x-button label="+ Tambah Guru" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    {{-- Search & Filter --}}
    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <x-input
            placeholder="Cari nama, kode, email, mata pelajaran..."
            wire:model.live.debounce.300ms="search"
            icon="o-magnifying-glass"
            class="flex-1 min-w-[200px]"
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
        @if($search || $filterStatus)
        <x-button label="Reset" wire:click="resetFilter" class="btn-ghost btn-sm" icon="o-x-mark" />
        @endif
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Mata Pelajaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $i => $teacher)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="font-mono text-sm">{{ $teacher->kode_guru }}</span></td>
                        <td class="font-medium">{{ $teacher->nama }}</td>
                        <td class="text-sm">{{ $teacher->email }}</td>
                        <td>{{ $teacher->no_hp ?? '-' }}</td>
                        <td>
                            @if($teacher->mata_pelajaran)
                                <x-badge :value="$teacher->mata_pelajaran" class="badge-outline" />
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            <x-badge
                                :value="$teacher->status"
                                class="{{ $teacher->status === 'Aktif' ? 'badge-success' : 'badge-error' }}"
                            />
                        </td>
                        <td class="flex gap-2">
                            <x-button label="Edit" wire:click="edit({{ $teacher->id }})" class="btn-sm btn-info" icon="o-pencil" />
                            <x-button label="Hapus" wire:click="delete({{ $teacher->id }})" wire:confirm="Yakin ingin menghapus data guru ini?" class="btn-sm btn-error" icon="o-trash" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8">
                            <x-icon name="o-academic-cap" class="w-12 h-12 mx-auto opacity-30 mb-2" />
                            <p class="opacity-50">{{ $search || $filterStatus ? 'Tidak ada hasil yang cocok.' : 'Belum ada data guru.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->count() > 0)
        <div class="text-sm text-gray-400 mt-3">
            Menampilkan {{ $teachers->count() }} guru
        </div>
        @endif
    </x-card>

    {{-- Modal --}}
    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Guru' : 'Tambah Guru Baru'" separator>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input
                label="Kode Guru"
                wire:model="kode_guru"
                disabled
                class="bg-base-200"
            />
            <x-select
                label="Status"
                wire:model="status"
                :options="[
                    ['id' => 'Aktif',    'name' => 'Aktif'],
                    ['id' => 'Nonaktif', 'name' => 'Nonaktif'],
                ]"
            />
            <div class="md:col-span-2">
                <x-input label="Nama Guru" wire:model="nama" placeholder="Masukkan nama lengkap guru" />
                @error('nama') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <x-input label="Email" wire:model="email" type="email" placeholder="Masukkan email guru" />
                @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <x-input label="No. HP" wire:model="no_hp" placeholder="Contoh: 08123456789" />
            <x-input label="Mata Pelajaran" wire:model="mata_pelajaran" placeholder="Contoh: Matematika" />
        </div>
        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" icon="o-x-mark" />
            <x-button :label="$editMode ? 'Update' : 'Simpan'" wire:click="save" class="btn-primary" icon="o-check" />
        </x-slot:actions>
    </x-modal>
</div>
