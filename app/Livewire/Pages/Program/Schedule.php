<?php
namespace App\Livewire\Pages\Program;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Schedule as ScheduleModel;
#[Layout('layouts.app')]
class Schedule extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $scheduleId;
    public $hari = '';
    public $jam_mulai = '';
    public $jam_selesai = '';
    public $mata_pelajaran = '';
    public $guru = '';
    public $ruangan = '';
    public $status = 'Aktif';

    // Search & Filter
    public $search = '';
    public $filterHari = '';
    public $filterStatus = '';

    public function updatedSearch() { }
    public function updatedFilterHari() { }
    public function updatedFilterStatus() { }

    public function resetFilter()
    {
        $this->search = '';
        $this->filterHari = '';
        $this->filterStatus = '';
    }

    public function openModal() { $this->resetFields(); $this->editMode = false; $this->showModal = true; }
    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->scheduleId = null;
        $this->hari = '';
        $this->jam_mulai = '';
        $this->jam_selesai = '';
        $this->mata_pelajaran = '';
        $this->guru = '';
        $this->ruangan = '';
        $this->status = 'Aktif';
    }

    public function save()
    {
        $this->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'mata_pelajaran' => 'required',
            'guru' => 'required',
        ]);

        if ($this->editMode) {
            ScheduleModel::find($this->scheduleId)->update([
                'hari' => $this->hari, 'jam_mulai' => $this->jam_mulai,
                'jam_selesai' => $this->jam_selesai, 'mata_pelajaran' => $this->mata_pelajaran,
                'guru' => $this->guru, 'ruangan' => $this->ruangan, 'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Jadwal berhasil diupdate.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        } else {
            ScheduleModel::create([
                'hari' => $this->hari, 'jam_mulai' => $this->jam_mulai,
                'jam_selesai' => $this->jam_selesai, 'mata_pelajaran' => $this->mata_pelajaran,
                'guru' => $this->guru, 'ruangan' => $this->ruangan, 'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Jadwal berhasil ditambahkan.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        }
        $this->closeModal();
    }

    public function edit($id)
    {
        $s = ScheduleModel::find($id);
        $this->scheduleId = $s->id;
        $this->hari = $s->hari;
        $this->jam_mulai = $s->jam_mulai;
        $this->jam_selesai = $s->jam_selesai;
        $this->mata_pelajaran = $s->mata_pelajaran;
        $this->guru = $s->guru;
        $this->ruangan = $s->ruangan;
        $this->status = $s->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        ScheduleModel::find($id)->delete();
        $this->dispatch('mary-toast', toast: ['type' => 'error', 'title' => 'Dihapus!', 'description' => 'Jadwal berhasil dihapus.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-error', 'timeout' => 3000, 'noProgress' => false]);
    }

    public function render()
    {
        $schedules = ScheduleModel::query()
            ->when($this->search, fn($q) => $q->where('mata_pelajaran', 'like', '%'.$this->search.'%')
                ->orWhere('guru', 'like', '%'.$this->search.'%')
                ->orWhere('ruangan', 'like', '%'.$this->search.'%'))
            ->when($this->filterHari, fn($q) => $q->where('hari', $this->filterHari))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->get();

        return view('livewire.pages.program.schedule', compact('schedules'));
    }
}
