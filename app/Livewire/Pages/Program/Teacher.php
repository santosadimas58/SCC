<?php
namespace App\Livewire\Pages\Program;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Teacher as TeacherModel;
#[Layout('layouts.app')]
class Teacher extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $teacherId;
    public $kode_guru = '';
    public $nama = '';
    public $email = '';
    public $no_hp = '';
    public $mata_pelajaran = '';
    public $status = 'Aktif';

    public $search = '';
    public $filterStatus = '';

    public function resetFilter()
    {
        $this->search = '';
        $this->filterStatus = '';
    }

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->showModal = true;
        $this->kode_guru = 'GRU-' . str_pad(TeacherModel::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->teacherId = null;
        $this->kode_guru = '';
        $this->nama = '';
        $this->email = '';
        $this->no_hp = '';
        $this->mata_pelajaran = '';
        $this->status = 'Aktif';
    }

    public function save()
    {
        $this->validate([
            'nama' => 'required|min:3',
            'email' => 'required|email|unique:teachers,email' . ($this->editMode ? ',' . $this->teacherId : ''),
            'status' => 'required',
        ]);

        if ($this->editMode) {
            TeacherModel::find($this->teacherId)->update([
                'nama' => $this->nama,
                'email' => $this->email,
                'no_hp' => $this->no_hp,
                'mata_pelajaran' => $this->mata_pelajaran,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Data guru berhasil diupdate.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        } else {
            TeacherModel::create([
                'kode_guru' => $this->kode_guru,
                'nama' => $this->nama,
                'email' => $this->email,
                'no_hp' => $this->no_hp,
                'mata_pelajaran' => $this->mata_pelajaran,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Data guru berhasil ditambahkan.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        }
        $this->closeModal();
    }

    public function edit($id)
    {
        $teacher = TeacherModel::find($id);
        $this->teacherId = $teacher->id;
        $this->kode_guru = $teacher->kode_guru;
        $this->nama = $teacher->nama;
        $this->email = $teacher->email;
        $this->no_hp = $teacher->no_hp;
        $this->mata_pelajaran = $teacher->mata_pelajaran;
        $this->status = $teacher->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        TeacherModel::find($id)->delete();
        $this->dispatch('mary-toast', toast: ['type' => 'error', 'title' => 'Dihapus!', 'description' => 'Data guru berhasil dihapus.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-error', 'timeout' => 3000, 'noProgress' => false]);
    }

    public function render()
    {
        $teachers = TeacherModel::query()
            ->when($this->search, fn($q) => $q->where('nama', 'like', '%'.$this->search.'%')
                ->orWhere('kode_guru', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')
                ->orWhere('mata_pelajaran', 'like', '%'.$this->search.'%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->get();

        return view('livewire.pages.program.teacher', compact('teachers'));
    }
}
