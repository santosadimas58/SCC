<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Inventory</h1>
        <x-button label="+ Tambah Barang" wire:click="openModal" class="btn-primary" />
    </div>

    <div class="overflow-x-auto rounded-lg shadow">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Kondisi</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventories as $item)
                <tr>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->kategori }}</td>
                    <td>{{ $item->jumlah }}</td>
                    <td>
                        <span class="badge {{ $item->kondisi === 'Baik' ? 'badge-success' : ($item->kondisi === 'Rusak Ringan' ? 'badge-warning' : 'badge-error') }}">
                            {{ $item->kondisi }}
                        </span>
                    </td>
                    <td>{{ $item->lokasi ?? '-' }}</td>
                    <td class="flex gap-2">
                        <x-button label="Edit" wire:click="edit({{ $item->id }})" class="btn-sm btn-info" />
                        <x-button label="Hapus" wire:click="delete({{ $item->id }})" wire:confirm="Yakin ingin menghapus?" class="btn-sm btn-error" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Belum ada data inventory.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-modal wire:model="showModal" title="{{ $editMode ? 'Edit Barang' : 'Tambah Barang Baru' }}">
        <x-input label="Kode Barang" wire:model="kode_barang" disabled />

        <x-input label="Nama Barang" wire:model="nama_barang" placeholder="Masukkan nama barang" class="mt-3" />
        @error('nama_barang') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-input label="Kategori" wire:model="kategori" placeholder="Masukkan kategori" class="mt-3" />
        @error('kategori') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-input label="Jumlah" wire:model="jumlah" type="number" min="0" class="mt-3" />
        @error('jumlah') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-select label="Kondisi" wire:model="kondisi" class="mt-3" :options="[
            ['id' => 'Baik', 'name' => 'Baik'],
            ['id' => 'Rusak Ringan', 'name' => 'Rusak Ringan'],
            ['id' => 'Rusak Berat', 'name' => 'Rusak Berat'],
        ]" />
        @error('kondisi') <span class="text-error text-xs">{{ $message }}</span> @enderror

        <x-input label="Lokasi" wire:model="lokasi" placeholder="Masukkan lokasi barang" class="mt-3" />

        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" />
            <x-button label="Simpan" wire:click="save" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
