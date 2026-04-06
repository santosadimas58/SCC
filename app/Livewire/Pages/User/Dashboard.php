<?php
namespace App\Livewire\Pages\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Assignment;
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'teacher'    => Teacher::where('status', 'Aktif')->count(),
            'subject'    => Subject::where('status', 'Aktif')->count(),
            'schedule'   => Schedule::where('status', 'Aktif')->count(),
            'assignment' => Assignment::where('status', 'Aktif')->count(),
        ];

        $upcomingAssignments = Assignment::where('status', 'Aktif')
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->take(5)
            ->get();

        $todaySchedules = Schedule::where('status', 'Aktif')
            ->where('hari', now()->locale('id')->isoFormat('dddd'))
            ->orderBy('jam_mulai')
            ->get();

        return view('livewire.pages.user.dashboard', compact('stats', 'upcomingAssignments', 'todaySchedules'));
    }
}
