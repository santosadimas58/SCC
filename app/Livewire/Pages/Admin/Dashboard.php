<?php
namespace App\Livewire\Pages\Admin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Program as ProgramModel;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\Inventory;
use Spatie\Permission\Models\Role;
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.dashboard', [
            'totalUsers'      => User::count(),
            'totalRoles'      => Role::count(),
            'totalPrograms'   => ProgramModel::count(),
            'totalTeachers'   => Teacher::where('status', 'Aktif')->count(),
            'totalSubjects'   => Subject::where('status', 'Aktif')->count(),
            'totalSchedules'  => Schedule::where('status', 'Aktif')->count(),
            'totalAssignments'=> Assignment::where('status', 'Aktif')->count(),
            'totalInventory'  => Inventory::count(),
            'recentUsers'     => User::latest()->take(5)->get(),
            'recentPrograms'  => ProgramModel::latest()->take(5)->get(),
        ]);
    }
}
