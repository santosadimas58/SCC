<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Account Settings</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Profil Saya</h1>
        <p class="mt-2 text-sm text-slate-300">Kelola identitas akun dan keamanan akses dalam layout yang lebih bersih dan konsisten dengan keseluruhan aplikasi.</p>
    </section>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        {{-- Info Card --}}
        <div class="card flex flex-col items-center p-6 text-center">
            <div class="w-24 h-24 rounded-full bg-primary flex items-center justify-center mb-4">
                <span class="text-4xl font-bold text-primary-content">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
            <p class="font-bold text-xl">{{ auth()->user()->name }}</p>
            <p class="text-sm opacity-50 mt-1">{{ auth()->user()->email }}</p>
            <div class="mt-3">
                @foreach(auth()->user()->getRoleNames() as $role)
                    <x-badge :value="$role" class="{{ $role === 'admin' ? 'badge-primary' : 'badge-secondary' }}" />
                @endforeach
            </div>
            <div class="divider"></div>
            <p class="text-xs opacity-40">Bergabung sejak</p>
            <p class="text-sm font-medium">{{ auth()->user()->created_at->format('d M Y') }}</p>
        </div>

        {{-- Edit Forms --}}
        <div class="md:col-span-2 flex flex-col gap-6">

            {{-- Edit Profil --}}
            <x-card title="Edit Profil" icon="o-user" separator>
                <div class="flex flex-col gap-4 mt-2">
                    <div>
                        <x-input label="Nama Lengkap" wire:model="name" placeholder="Masukkan nama lengkap" />
                        @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-input label="Email" wire:model="email" type="email" placeholder="Masukkan email" />
                        @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end">
                        <x-button label="Simpan Perubahan" wire:click="updateProfile" class="btn-primary" icon="o-check" />
                    </div>
                </div>
            </x-card>

            {{-- Ganti Password --}}
            <x-card title="Ganti Password" icon="o-lock-closed" separator>
                <div class="flex flex-col gap-4 mt-2">
                    <div>
                        <x-input label="Password Saat Ini" wire:model="current_password" type="password" placeholder="Masukkan password saat ini" />
                        @error('current_password') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-input label="Password Baru" wire:model="new_password" type="password" placeholder="Minimal 6 karakter" />
                        @error('new_password') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-input label="Konfirmasi Password Baru" wire:model="new_password_confirmation" type="password" placeholder="Ulangi password baru" />
                    </div>
                    <div class="flex justify-end">
                        <x-button label="Ubah Password" wire:click="updatePassword" class="btn-warning" icon="o-lock-closed" />
                    </div>
                </div>
            </x-card>

        </div>
    </div>
</div>
