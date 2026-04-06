<?php
namespace App\Livewire\Pages\Admin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Program as ProgramModel;
#[Layout('layouts.app')]
class Program extends Component
{
    public $programs;
    public $showModal = false;
    public $editMode = false;
    public $programId;
    public $kode_program = '';
    public $nama_program = '';
    public $deskripsi = '';
    public $jalur = '';
    public $status = 'Pending';

    public function mount()
    {
        $this->loadPrograms();
    }

    public function loadPrograms()
    {
        $this->programs = ProgramModel::latest()->get();
    }

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->showModal = true;
        $this->kode_program = 'PRG-' . str_pad(ProgramModel::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetFields();
    }

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
        } else {
            ProgramModel::create([
                'kode_program' => $this->kode_program,
                'nama_program' => $this->nama_program,
                'deskripsi' => $this->deskripsi,
                'jalur' => $this->jalur,
                'status' => $this->status,
            ]);
        }

        $this->closeModal();
        $this->loadPrograms();
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
        $this->loadPrograms();
    }

    public function render()
    {
        return view('livewire.pages.admin.program');
    }
}
