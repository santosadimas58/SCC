<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <div class="flex items-center gap-2 px-2">
                <x-icon name="o-bolt" class="w-6 h-6 text-warning" />
                <span class="font-bold">SCC Monitor</span>
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    <x-main>
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            <div class="px-5 pt-4 pb-2">
                <div class="flex items-center gap-2">
                    <x-icon name="o-bolt" class="w-7 h-7 text-warning" />
                    <span class="font-bold text-lg">SCC Monitor</span>
                </div>
                <div class="text-xs text-gray-400 mt-1">Solar Charge Controller</div>
            </div>

            @if($user = auth()->user())
                <x-menu-separator />
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                    <x-slot:actions>
                        <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="Logout" no-wire-navigate link="/logout" />
                    </x-slot:actions>
                </x-list-item>
                <x-menu-separator />
            @endif

            <x-menu activate-by-route>
                <x-menu-item title="Dashboard" icon="o-chart-bar" link="/scc" />
                <x-menu-separator />
                <x-menu-sub title="Monitoring" icon="o-eye">
                    <x-menu-item title="Real-time Data" icon="o-signal" link="/scc" />
                    <x-menu-item title="Riwayat Data" icon="o-clock" link="/scc/history" />
                </x-menu-sub>
                <x-menu-separator />
                <x-menu-sub title="Fuzzy Logic" icon="o-cpu-chip">
                    <x-menu-item title="Membership Function" icon="o-adjustments-horizontal" link="/scc/fuzzy" />
                    <x-menu-item title="Rule Base" icon="o-table-cells" link="/scc/rules" />
                </x-menu-sub>
                <x-menu-separator />
                <x-menu-item title="Export Data" icon="o-arrow-down-tray" link="/scc/export" />
                <x-menu-separator />
                <x-menu-item title="Tentang Project" icon="o-information-circle" link="/scc/about" />
            </x-menu>

        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast />
    @stack('scripts')
</body>
</html>
