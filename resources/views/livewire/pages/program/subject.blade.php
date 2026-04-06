<div>
    <x-header title="Mata Pelajaran" subtitle="Manajemen data mata pelajaran" separator>
        <x-slot:actions>
            <x-button label="+ Tambah Mapel" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    {{-- Search & Filter --}}
    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <x-input
            placeholder="Cari kode atau nama mapel..."
            wire:model.live.debounce.300ms="search"
            icon="o-magnifying-glass"
            class="flex-1 min-w-[200px]"
        />
        <x-select
            placeholder="Semua Kategori"
            wire:model.live="filterKategori"
            :options="$kategoris"
            class="w-44"
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
        @if($search || $filterStatus || $filterKategori)
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
                        <th>Nama Mapel</th>
                        <th>Kategori</th>
                        <th>SKS</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $i => $subject)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><span class="font-mono text-sm">{{ $subject->kode_mapel }}</span></td>
                        <td class="font-medium">{{ $subject->nama_mapel }}</td>
                        <td>
                            @if($subject->kategori)
                                <x-badge :value="$subject->kategori" class="badge-outline" />
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-neutral">{{ $subject->sks }} SKS</span>
                        </td>
                        <td>
                            <x-badge
                                :value="$subject->status"
                                class="{{ $subject->status === 'Aktif' ? 'badge-success' : 'badge-error' }}"
                            />
                        </td>
                        <td class="flex gap-2">
                            <x-button label="Edit" wire:click="edit({{ $subject->id }})" class="btn-sm btn-info" icon="o-pencil" />
                            <x-button label="Hapus" wire:click="delete({{ $subject->id }})" wire:confirm="Yakin ingin menghapus mata pelajaran ini?" class="btn-sm btn-error" icon="o-trash" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <x-icon name="o-book-open" class="w-12 h-12 mx-auto opacity-30 mb-2" />
                            <p class="opacity-50">{{ $search || $filterStatus || $filterKategori ? 'Tidak ada hasil yang cocok.' : 'Belum ada data mata pelajaran.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subjects->count() > 0)
        <div class="text-sm text-gray-400 mt-3">
            Menampilkan {{ $subjects->count() }} mata pelajaran
        </div>
        @endif
    </x-card>

    {{-- Modal --}}
    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'" separator>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input
                label="Kode Mapel"
                wire:model="kode_mapel"
                disabled
                class="bg-base-200"
            />
            <x-input
                label="SKS"
                wire:model="sks"
                type="number"
                min="1"
                max="6"
            />
            <div class="md:col-span-2">
                <x-input
                    label="Nama Mata Pelajaran"
                    wire:model="nama_mapel"
                    placeholder="Contoh: Matematika Dasar"
                />
                @error('nama_mapel') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <x-input
                label="Kategori"
                wire:model="kategori"
                placeholder="Contoh: Sains, Bahasa, Teknik..."
            />
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
            <x-button label="Batal" wire:click="closeModal" icon="o-x-mark" />
            <x-button :label="$editMode ? 'Update' : 'Simpan'" wire:click="save" class="btn-primary" icon="o-check" />
        </x-slot:actions>
    </x-modal>
</div>
