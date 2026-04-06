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
    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
            <x-app-brand class="px-5 pt-4" />

            <x-menu activate-by-route>
                @if($user = auth()->user())
                    <x-menu-separator />
                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                        </x-slot:actions>
                    </x-list-item>
                    <x-menu-separator />
                @endif

                {{-- Home --}}
                @if(auth()->check() && auth()->user()->hasRole('admin'))
                    <x-menu-item title="Home" icon="o-home" link="/admin/dashboard" />
                @else
                    <x-menu-item title="Home" icon="o-home" link="/user/dashboard" />
                @endif

                {{-- Admin Menu --}}
                @role('admin')
                    <x-menu-item title="Users" icon="o-users" link="/admin/users" />
                    <x-menu-item title="Program" icon="o-archive-box" link="/admin/program" />
                    <x-menu-item title="Inventory" icon="o-cube" link="/admin/inventory" />
                @endrole

                {{-- Program Menu --}}
                @role('program')
                    <x-menu-separator />
                    <x-menu-item title="Teacher" icon="o-academic-cap" link="/program/teacher" />
                    <x-menu-item title="Subject" icon="o-book-open" link="/program/subject" />
                    <x-menu-item title="Schedule" icon="o-calendar" link="/program/schedule" />
                    <x-menu-item title="Assignment" icon="o-clipboard-document" link="/program/assignment" />
                    <x-menu-separator />
                    <x-menu-item title="Profile" icon="o-user-circle" link="/user/profile" />
                @endrole
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast />
</body>
</html>
