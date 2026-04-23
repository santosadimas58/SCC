<div class="grid w-full max-w-5xl gap-8 lg:grid-cols-[1.1fr_0.9fr]">
    <div class="hidden lg:flex flex-col justify-between rounded-[2rem] border border-white/10 bg-slate-950/45 p-10 shadow-2xl shadow-slate-950/40 backdrop-blur-xl">
        <div>
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-400 to-violet-600 text-white shadow-lg shadow-violet-900/30">
                <x-icon name="o-bolt" class="h-7 w-7" />
            </div>
            <div class="scc-eyebrow">SCC Monitoring</div>
            <h1 class="mt-4 text-4xl font-semibold leading-tight text-white">Panel monitoring modern untuk Solar Charge Controller.</h1>
            <p class="mt-4 max-w-xl text-base text-slate-300">Akses dashboard real-time, riwayat data, fuzzy logic, dan export data dalam satu workspace gelap yang konsisten.</p>
        </div>

        <div class="scc-hero-grid">
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Monitoring</div>
                <div class="mt-2 text-2xl font-semibold text-white">Real-time</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Analisis</div>
                <div class="mt-2 text-2xl font-semibold text-white">Fuzzy Logic</div>
            </div>
            <div class="scc-hero-stat">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-500">Output</div>
                <div class="mt-2 text-2xl font-semibold text-white">CSV Export</div>
            </div>
        </div>
    </div>

    <x-card class="w-full border border-white/10 bg-slate-950/55 p-2 shadow-2xl shadow-slate-950/40">
        <div class="rounded-[1.6rem] bg-gradient-to-br from-blue-500/10 via-transparent to-violet-500/10 p-8 sm:p-10">
            <div class="mb-10 text-center">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-3xl bg-gradient-to-br from-blue-400 to-violet-600 text-white shadow-lg shadow-violet-900/30">
                    <x-icon name="o-lock-closed" class="h-8 w-8" />
                </div>
                <h2 class="text-3xl font-semibold text-white">Masuk ke SCC Monitor</h2>
                <p class="mt-2 text-sm text-slate-400">Gunakan akun Anda untuk mengakses seluruh dashboard monitoring.</p>
            </div>

            <div class="space-y-5">
                <x-input
                    label="Email"
                    wire:model="email"
                    type="email"
                    placeholder="Masukkan email"
                    icon="o-envelope"
                    autocomplete="email"
                />
                @error('email')
                    <x-alert title="{{ $message }}" class="alert-error" />
                @enderror

                <div class="scc-password-wrap">
                    <x-input
                        label="Password"
                        wire:model="password"
                        type="password"
                        placeholder="Masukkan password"
                        icon="o-key"
                        id="login-password"
                        autocomplete="current-password"
                        class="pr-12"
                    />
                    <button type="button" class="scc-password-toggle" aria-label="Tampilkan password" data-password-toggle>
                        <x-icon name="o-eye" class="h-4 w-4" data-password-icon="show" />
                        <x-icon name="o-eye-slash" class="hidden h-4 w-4" data-password-icon="hide" />
                    </button>
                </div>

                @if($errors->any())
                    <div class="scc-note">
                        Pastikan email dan password benar. Jika data masih salah, akun atau kredensial perlu diperiksa.
                    </div>
                @endif

                <x-button
                    label="Login"
                    wire:click="authenticate"
                    wire:loading.attr="disabled"
                    wire:target="authenticate"
                    class="btn-primary h-12 w-full border-0 text-base font-semibold"
                    icon="o-arrow-right-on-rectangle"
                />

                <div wire:loading wire:target="authenticate" class="scc-status-pill scc-status-live w-full justify-center">
                    <span class="scc-status-dot"></span>
                    Memverifikasi kredensial dan membuka dashboard...
                </div>
            </div>
        </div>
    </x-card>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById('login-password');
            const showIcon = button.querySelector('[data-password-icon="show"]');
            const hideIcon = button.querySelector('[data-password-icon="hide"]');
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            showIcon.classList.toggle('hidden', isPassword);
            hideIcon.classList.toggle('hidden', !isPassword);
            button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });
</script>
@endpush
