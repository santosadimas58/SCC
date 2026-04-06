<?php
namespace App\Livewire\Pages\Admin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Program as ProgramModel;
#[Layout('layouts.app')]
class Program extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $programId;
    public $kode_program = '';
    public $nama_program = '';
    public $deskripsi = '';
    public $jalur = '';
    public $status = 'Pending';
    public $perPage = 10;
    public $search = '';
    public $filterStatus = '';
    public $filterJalur = '';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedFilterJalur() { $this->resetPage(); }

    public function resetFilter()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterJalur = '';
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->showModal = true;
        $this->kode_program = 'PRG-' . str_pad(ProgramModel::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->programId = null;
        $this->kode_program = '';
        $this->nama_program = '';
        $this->deskripsi = '';
        $this->jalur = '';
        $this->status = 'Pending';
    }

    public function save()
    {
        $this->validate([
            'nama_program' => 'required|min:3',
            'jalur' => 'required',
            'status' => 'required',
        ]);

        if ($this->editMode) {
            ProgramModel::find($this->programId)->update([
                'nama_program' => $this->nama_program,
                'deskripsi' => $this->deskripsi,
                'jalur' => $this->jalur,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Program berhasil diupdate.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        } else {
            ProgramModel::create([
                'kode_program' => $this->kode_program,
                'nama_program' => $this->nama_program,
                'deskripsi' => $this->deskripsi,
                'jalur' => $this->jalur,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Program berhasil ditambahkan.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        }
        $this->closeModal();
        $this->resetPage();
    }

    public function edit($id)
    {
        $program = ProgramModel::find($id);
        $this->programId = $program->id;
        $this->kode_program = $program->kode_program;
        $this->nama_program = $program->nama_program;
        $this->deskripsi = $program->deskripsi;
        $this->jalur = $program->jalur;
        $this->status = $program->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        ProgramModel::find($id)->delete();
        $this->dispatch('mary-toast', toast: ['type' => 'error', 'title' => 'Dihapus!', 'description' => 'Program berhasil dihapus.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-error', 'timeout' => 3000, 'noProgress' => false]);
        $this->resetPage();
    }

    public function render()
    {
        $programs = ProgramModel::query()
            ->when($this->search, fn($q) => $q->where('nama_program', 'like', '%'.$this->search.'%')
                ->orWhere('kode_program', 'like', '%'.$this->search.'%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterJalur, fn($q) => $q->where('jalur', $this->filterJalur))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.pages.admin.program', compact('programs'));
    }
}
