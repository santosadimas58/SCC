<div class="min-h-screen flex items-center justify-center">
    <x-card class="w-full max-w-md shadow-xl">
        <div class="text-center mb-6">
            <x-icon name="o-lock-closed" class="w-12 h-12 mx-auto text-primary mb-2" />
            <h1 class="text-2xl font-bold">Login</h1>
            <p class="text-sm opacity-50">Masukkan kredensial Anda</p>
        </div>

        <x-input
            label="Email"
            wire:model="email"
            type="email"
            placeholder="Masukkan email"
            icon="o-envelope"
            class="mb-3"
        />
        @error('email')
            <x-alert title="{{ $message }}" class="alert-error mb-3" />
        @enderror

        <x-input
            label="Password"
            wire:model="password"
            type="password"
            placeholder="Masukkan password"
            icon="o-key"
            class="mb-6"
        />

        <x-button
            label="Login"
            wire:click="authenticate"
            class="btn-primary w-full"
            icon="o-arrow-right-on-rectangle"
        />
    </x-card>
</div>
