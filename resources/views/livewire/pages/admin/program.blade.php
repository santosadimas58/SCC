<div class="scc-page">
    <section class="scc-page-hero">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="scc-eyebrow">Program Catalog</div>
                <h1 class="mt-3 text-3xl font-semibold text-white">Program</h1>
                <p class="mt-2 text-sm text-slate-300">Katalog program akademik dibawa ke struktur visual yang sama dengan dashboard, inventory, dan monitoring.</p>
            </div>
            <x-button label="+ Tambah Program Baru" wire:click="openModal" class="btn-primary border-0" />
        </div>
    </section>

    <x-card>
        <div class="scc-table-wrap overflow-x-auto">
            <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Program</th>
                    <th>Jalur</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr>
                    <td>{{ $program->kode_program }}</td>
                    <td>{{ $program->nama_program }}</td>
                    <td>{{ $program->jalur }}</td>
                    <td>
                        <span class="badge {{ $program->status === 'Aktif' ? 'badge-success' : ($program->status === 'Nonaktif' ? 'badge-error' : 'badge-warning') }}">
                            {{ $program->status }}
                        </span>
                    </td>
                    <td class="flex gap-2">
                        <x-button label="Edit" wire:click="edit({{ $program->id }})" class="btn-sm btn-info" />
                        <x-button label="Hapus" wire:click="delete({{ $program->id }})" wire:confirm="Yakin ingin menghapus?" class="btn-sm btn-error" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6">
                        <div class="scc-empty">Belum ada data program.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </x-card>

    <x-modal wire:model="showModal" title="{{ $editMode ? 'Edit Program' : 'Tambah Program Baru' }}">
        <x-input label="Kode Program" wire:model="kode_program" disabled />
        <x-input label="Nama Program" wire:model="nama_program" placeholder="Masukkan nama program" class="mt-3" />
        @error('nama_program') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-textarea label="Deskripsi" wire:model="deskripsi" placeholder="Masukkan deskripsi program" class="mt-3" />

        <x-select label="Jalur" wire:model="jalur" class="mt-3" :options="[
            ['id' => 'Jalur A', 'name' => 'Jalur A'],
            ['id' => 'Jalur B', 'name' => 'Jalur B'],
            ['id' => 'Jalur C', 'name' => 'Jalur C'],
        ]" placeholder="Pilih jalur" />
        @error('jalur') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-select label="Status" wire:model="status" class="mt-3" :options="[
            ['id' => 'Aktif', 'name' => 'Aktif'],
            ['id' => 'Nonaktif', 'name' => 'Nonaktif'],
            ['id' => 'Pending', 'name' => 'Pending'],
        ]" placeholder="Pilih status" />
        @error('status') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" />
            <x-button label="Simpan" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
