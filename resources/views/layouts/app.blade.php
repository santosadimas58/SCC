<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased">
    <x-nav sticky class="lg:hidden m-3 scc-mobile-nav border-0 bg-transparent">
        <x-slot:brand>
            <div class="flex items-center gap-3 px-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-400 to-violet-600 text-white shadow-lg shadow-violet-900/30">
                    <x-icon name="o-bolt" class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-sm font-semibold text-white">SCC Monitoring</div>
                    <div class="text-[11px] uppercase tracking-[0.28em] text-slate-400">Control Center</div>
                </div>
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer text-slate-200" />
            </label>
        </x-slot:actions>
    </x-nav>

    <x-main>
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-transparent lg:bg-inherit">

            <div class="scc-sidebar-shell">
                <div class="scc-sidebar-brand">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-400 to-violet-600 text-white shadow-lg shadow-violet-900/30">
                            <x-icon name="o-bolt" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="font-semibold text-lg text-white">SCC Monitor</div>
                            <div class="text-xs uppercase tracking-[0.28em] text-slate-400">Solar Charge Controller</div>
                        </div>
                    </div>
                </div>

                @if($user = auth()->user())
                    <div class="mt-4 rounded-2xl border border-white/8 bg-slate-950/40 p-3">
                        <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="!bg-transparent !px-1 !py-1 rounded-xl">
                            <x-slot:actions>
                                <x-button icon="o-power" class="btn-circle btn-ghost btn-xs border border-white/10 bg-white/5 text-slate-200" tooltip-left="Logout" no-wire-navigate link="/logout" />
                            </x-slot:actions>
                        </x-list-item>
                    </div>
                @endif

                <div class="mt-4 text-[11px] font-semibold uppercase tracking-[0.28em] text-slate-500 px-3">Navigation</div>
                <x-menu activate-by-route class="mt-2">
                    <x-menu-item title="Dashboard" icon="o-chart-bar" link="/scc" />
                    <x-menu-sub title="Monitoring" icon="o-eye">
                        <x-menu-item title="Real-time Data" icon="o-signal" link="/scc" />
                        <x-menu-item title="Riwayat Data" icon="o-clock" link="/scc/history" />
                    </x-menu-sub>
                    <x-menu-sub title="Fuzzy Logic" icon="o-cpu-chip">
                        <x-menu-item title="Membership Function" icon="o-adjustments-horizontal" link="/scc/fuzzy" />
                        <x-menu-item title="Rule Base" icon="o-table-cells" link="/scc/rules" />
                    </x-menu-sub>
                    <x-menu-item title="Export Data" icon="o-arrow-down-tray" link="/scc/export" />
                    <x-menu-item title="Tentang Project" icon="o-information-circle" link="/scc/about" />
                </x-menu>
            </div>

        </x-slot:sidebar>

        <x-slot:content>
            <div class="scc-content-shell">
                <div class="scc-content-inner">
                    {{ $slot }}
                </div>
            </div>
        </x-slot:content>
    </x-main>

    <x-toast />
    @stack('scripts')
</body>
</html>
