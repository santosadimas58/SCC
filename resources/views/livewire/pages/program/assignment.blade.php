<div>
    <x-header title="Tugas" subtitle="Manajemen data tugas" separator>
        <x-slot:actions>
            <x-button label="+ Tambah Tugas" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    {{-- Search & Filter --}}
    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <x-input
            placeholder="Cari judul, mata pelajaran..."
            wire:model.live.debounce.300ms="search"
            icon="o-magnifying-glass"
            class="flex-1 min-w-[200px]"
        />
        <x-select
            placeholder="Semua Status"
            wire:model.live="filterStatus"
            :options="[
                ['id' => 'Aktif',   'name' => 'Aktif'],
                ['id' => 'Selesai', 'name' => 'Selesai'],
                ['id' => 'Batal',   'name' => 'Batal'],
            ]"
            class="w-44"
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
                        <th>Judul</th>
                        <th>Mata Pelajaran</th>
                        <th>Deskripsi</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $i => $assignment)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="font-medium">{{ $assignment->judul }}</td>
                        <td>{{ $assignment->mata_pelajaran }}</td>
                        <td class="max-w-xs truncate text-sm text-gray-400">{{ $assignment->deskripsi ?? '-' }}</td>
                        <td>
                            @php $deadline = \Carbon\Carbon::parse($assignment->deadline); @endphp
                            <span class="{{ $deadline->isPast() && $assignment->status === 'Aktif' ? 'text-error font-semibold' : '' }}">
                                {{ $deadline->format('d M Y') }}
                            </span>
                            @if($deadline->isPast() && $assignment->status === 'Aktif')
                                <span class="text-xs text-error block">Lewat deadline!</span>
                            @elseif($assignment->status === 'Aktif')
                                <span class="text-xs text-gray-400 block">{{ $deadline->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td>
                            <x-badge
                                :value="$assignment->status"
                                class="{{
                                    $assignment->status === 'Aktif' ? 'badge-success' :
                                    ($assignment->status === 'Selesai' ? 'badge-info' : 'badge-error')
                                }}"
                            />
                        </td>
                        <td class="flex gap-2">
                            <x-button label="Edit" wire:click="edit({{ $assignment->id }})" class="btn-sm btn-info" icon="o-pencil" />
                            <x-button label="Hapus" wire:click="delete({{ $assignment->id }})" wire:confirm="Yakin ingin menghapus tugas ini?" class="btn-sm btn-error" icon="o-trash" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <x-icon name="o-clipboard-document" class="w-12 h-12 mx-auto opacity-30 mb-2" />
                            <p class="opacity-50">{{ $search || $filterStatus ? 'Tidak ada hasil yang cocok.' : 'Belum ada tugas.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assignments->count() > 0)
        <div class="text-sm text-gray-400 mt-3">
            Menampilkan {{ $assignments->count() }} tugas
        </div>
        @endif
    </x-card>

    {{-- Modal --}}
    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Tugas' : 'Tambah Tugas'" separator>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-input label="Judul Tugas" wire:model="judul" placeholder="Masukkan judul tugas" />
                @error('judul') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <x-input label="Mata Pelajaran" wire:model="mata_pelajaran" placeholder="Contoh: Matematika" />
            <x-input label="Deadline" wire:model="deadline" type="date" />
            <div class="md:col-span-2">
                <x-textarea label="Deskripsi" wire:model="deskripsi" placeholder="Deskripsi tugas (opsional)" rows="3" />
            </div>
            <x-select
                label="Status"
                wire:model="status"
                :options="[
                    ['id' => 'Aktif',   'name' => 'Aktif'],
                    ['id' => 'Selesai', 'name' => 'Selesai'],
                    ['id' => 'Batal',   'name' => 'Batal'],
                ]"
            />
        </div>
        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" icon="o-x-mark" />
            <x-button :label="$editMode ? 'Update' : 'Simpan'" wire:click="save" class="btn-primary" icon="o-check" />
        </x-slot:actions>
    </x-modal>
</div>
