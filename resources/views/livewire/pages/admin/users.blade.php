<div>
    <x-header title="Manajemen User" subtitle="Kelola user dan role" separator>
        <x-slot:actions>
            <x-button label="+ Tambah User" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    <div class="flex flex-wrap gap-3 mb-4 items-center">
        <x-input
            placeholder="Cari nama atau email..."
            wire:model.live.debounce.300ms="search"
            icon="o-magnifying-glass"
            class="flex-1 min-w-[200px]"
        />
        <x-select
            placeholder="Semua Role"
            wire:model.live="filterRole"
            :options="$roleOptions"
            class="w-40"
        />
        @if($search || $filterRole)
        <x-button label="Reset" wire:click="resetFilter" class="btn-ghost btn-sm" icon="o-x-mark" />
        @endif
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="font-medium {{ $user->id === auth()->id() ? 'text-primary' : '' }}">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="text-xs opacity-50">(Anda)</span>
                                @endif
                            </span>
                        </td>
                        <td class="text-sm">{{ $user->email }}</td>
                        <td>
                            @foreach($user->getRoleNames() as $role)
                                <x-badge :value="$role" class="{{ $role === 'admin' ? 'badge-primary' : 'badge-secondary' }}" />
                            @endforeach
                        </td>
                        <td class="text-xs opacity-50">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="flex gap-2">
                            <x-button label="Edit" wire:click="edit({{ $user->id }})" class="btn-sm btn-info" icon="o-pencil" />
                            <x-button
                                label="Hapus"
                                wire:click="delete({{ $user->id }})"
                                wire:confirm="Yakin ingin menghapus user ini?"
                                class="btn-sm btn-error"
                                icon="o-trash"
                                :disabled="$user->id === auth()->id()"
                            />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <x-icon name="o-users" class="w-12 h-12 mx-auto opacity-30 mb-2" />
                            <p class="opacity-50">{{ $search || $filterRole ? 'Tidak ada hasil yang cocok.' : 'Belum ada data user.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->count() > 0)
        <div class="text-sm text-gray-400 mt-3">
            Menampilkan {{ $users->count() }} user
        </div>
        @endif
    </x-card>

    <x-modal wire:model="showModal" :title="$editMode ? 'Edit User' : 'Tambah User Baru'" separator>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-input label="Nama Lengkap" wire:model="name" placeholder="Masukkan nama lengkap" />
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <x-input label="Email" wire:model="email" type="email" placeholder="Masukkan email" />
                @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <x-input
                    label="{{ $editMode ? 'Password Baru (kosongkan jika tidak diubah)' : 'Password' }}"
                    wire:model="password"
                    type="password"
                    placeholder="Masukkan password"
                />
                @error('password') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="md:col-span-2">
                <x-select label="Role" wire:model="selectedRole" :options="$roleOptions" placeholder="Pilih role" />
                @error('selectedRole') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" icon="o-x-mark" />
            <x-button :label="$editMode ? 'Update' : 'Simpan'" wire:click="save" class="btn-primary" icon="o-check" />
        </x-slot:actions>
    </x-modal>
</div>
