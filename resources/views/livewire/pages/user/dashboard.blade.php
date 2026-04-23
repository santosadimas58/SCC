<div class="scc-page">
    <section class="scc-page-hero">
        <div class="scc-eyebrow">Personal Workspace</div>
        <h1 class="mt-3 text-3xl font-semibold text-white">Dashboard</h1>
        <p class="mt-2 text-sm text-slate-300">Ringkasan aktivitas akademik pribadi dengan kartu statistik, akses cepat, dan panel informasi yang lebih profesional.</p>
    </section>

    {{-- Stats Cards --}}
    <div class="scc-grid-stats">
        <x-card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-primary/10 rounded-lg">
                    <x-icon name="o-academic-cap" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $stats['teacher'] }}</p>
                    <p class="text-xs opacity-50">Guru Aktif</p>
                </div>
            </div>
        </x-card>
        <x-card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-success/10 rounded-lg">
                    <x-icon name="o-book-open" class="w-6 h-6 text-success" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $stats['subject'] }}</p>
                    <p class="text-xs opacity-50">Mata Pelajaran</p>
                </div>
            </div>
        </x-card>
        <x-card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-info/10 rounded-lg">
                    <x-icon name="o-calendar" class="w-6 h-6 text-info" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $stats['schedule'] }}</p>
                    <p class="text-xs opacity-50">Jadwal Aktif</p>
                </div>
            </div>
        </x-card>
        <x-card>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-warning/10 rounded-lg">
                    <x-icon name="o-clipboard-document" class="w-6 h-6 text-warning" />
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $stats['assignment'] }}</p>
                    <p class="text-xs opacity-50">Tugas Aktif</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Profil --}}
        <x-card title="Profil Saya" icon="o-user">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-16">
                        <span class="text-2xl">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                </div>
                <div>
                    <p class="font-bold text-lg">{{ auth()->user()->name }}</p>
                    <p class="opacity-50 text-sm">{{ auth()->user()->email }}</p>
                    <x-badge value="Program" class="badge-secondary mt-1" />
                </div>
            </div>
        </x-card>

        {{-- Menu --}}
        <x-card title="Menu" icon="o-squares-2x2">
            <div class="grid grid-cols-2 gap-3">
                <a href="/program/teacher">
                    <x-button label="Teacher" icon="o-academic-cap" class="btn-outline w-full" />
                </a>
                <a href="/program/subject">
                    <x-button label="Subject" icon="o-book-open" class="btn-outline w-full" />
                </a>
                <a href="/program/schedule">
                    <x-button label="Schedule" icon="o-calendar" class="btn-outline w-full" />
                </a>
                <a href="/program/assignment">
                    <x-button label="Assignment" icon="o-clipboard-document" class="btn-outline w-full" />
                </a>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Jadwal Hari Ini --}}
        <x-card title="Jadwal Hari Ini" icon="o-clock">
            @forelse($todaySchedules as $schedule)
            <div class="scc-list-row">
                <div>
                    <p class="font-medium text-sm">{{ $schedule->mata_pelajaran }}</p>
                    <p class="text-xs opacity-50">{{ $schedule->guru }} · {{ $schedule->ruangan ?? '-' }}</p>
                </div>
                <span class="text-xs font-mono bg-base-200 px-2 py-1 rounded-xl">
                    {{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }}
                </span>
            </div>
            @empty
            <div class="scc-empty">
                <x-icon name="o-calendar" class="w-8 h-8 mx-auto mb-1" />
                <p class="text-sm">Tidak ada jadwal hari ini</p>
            </div>
            @endforelse
        </x-card>

        {{-- Tugas Mendatang --}}
        <x-card title="Tugas Mendatang" icon="o-clipboard-document-list">
            @forelse($upcomingAssignments as $assignment)
            <div class="scc-list-row">
                <div>
                    <p class="font-medium text-sm">{{ $assignment->judul }}</p>
                    <p class="text-xs opacity-50">{{ $assignment->mata_pelajaran }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold {{ \Carbon\Carbon::parse($assignment->deadline)->diffInDays() <= 2 ? 'text-error' : 'text-warning' }}">
                        {{ \Carbon\Carbon::parse($assignment->deadline)->format('d M') }}
                    </p>
                    <p class="text-xs opacity-40">{{ \Carbon\Carbon::parse($assignment->deadline)->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="scc-empty">
                <x-icon name="o-clipboard-document" class="w-8 h-8 mx-auto mb-1" />
                <p class="text-sm">Tidak ada tugas mendatang</p>
            </div>
            @endforelse
        </x-card>
    </div>
</div>
