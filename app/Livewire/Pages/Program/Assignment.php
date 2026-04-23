<?php
namespace App\Livewire\Pages\Program;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Assignment as AssignmentModel;
#[Layout('layouts.app')]
class Assignment extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $assignmentId;
    public $judul = '';
    public $deskripsi = '';
    public $mata_pelajaran = '';
    public $deadline = '';
    public $status = 'Aktif';

    public $search = '';
    public $filterStatus = '';

    public function resetFilter()
    {
        $this->search = '';
        $this->filterStatus = '';
    }

    public function openModal() { $this->resetFields(); $this->editMode = false; $this->showModal = true; }
    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->assignmentId = null;
        $this->judul = '';
        $this->deskripsi = '';
        $this->mata_pelajaran = '';
        $this->deadline = '';
        $this->status = 'Aktif';
    }

    public function save()
    {
        $this->validate([
            'judul' => 'required|min:3',
            'mata_pelajaran' => 'required',
            'deadline' => 'required|date',
            'status' => 'required',
        ]);

        if ($this->editMode) {
            AssignmentModel::find($this->assignmentId)->update([
                'judul' => $this->judul, 'deskripsi' => $this->deskripsi,
                'mata_pelajaran' => $this->mata_pelajaran, 'deadline' => $this->deadline,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Tugas berhasil diupdate.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        } else {
            AssignmentModel::create([
                'judul' => $this->judul, 'deskripsi' => $this->deskripsi,
                'mata_pelajaran' => $this->mata_pelajaran, 'deadline' => $this->deadline,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Tugas berhasil ditambahkan.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        }
        $this->closeModal();
    }

    public function edit($id)
    {
        $a = AssignmentModel::find($id);
        $this->assignmentId = $a->id;
        $this->judul = $a->judul;
        $this->deskripsi = $a->deskripsi;
        $this->mata_pelajaran = $a->mata_pelajaran;
        $this->deadline = $a->deadline;
        $this->status = $a->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        AssignmentModel::find($id)->delete();
        $this->dispatch('mary-toast', toast: ['type' => 'error', 'title' => 'Dihapus!', 'description' => 'Tugas berhasil dihapus.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-error', 'timeout' => 3000, 'noProgress' => false]);
    }

    public function render()
    {
        $assignments = AssignmentModel::query()
            ->when($this->search, fn($q) => $q->where('judul', 'like', '%'.$this->search.'%')
                ->orWhere('mata_pelajaran', 'like', '%'.$this->search.'%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->get();

        return view('livewire.pages.program.assignment', compact('assignments'));
    }
}
