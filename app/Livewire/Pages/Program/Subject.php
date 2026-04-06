<?php
namespace App\Livewire\Pages\Program;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Subject as SubjectModel;
#[Layout('layouts.app')]
class Subject extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $subjectId;
    public $kode_mapel = '';
    public $nama_mapel = '';
    public $kategori = '';
    public $sks = 2;
    public $status = 'Aktif';

    public $search = '';
    public $filterStatus = '';
    public $filterKategori = '';

    public function resetFilter()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterKategori = '';
    }

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->showModal = true;
        $this->kode_mapel = 'MPL-' . str_pad(SubjectModel::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->subjectId = null;
        $this->kode_mapel = '';
        $this->nama_mapel = '';
        $this->kategori = '';
        $this->sks = 2;
        $this->status = 'Aktif';
    }

    public function save()
    {
        $this->validate([
            'nama_mapel' => 'required|min:2',
            'sks' => 'required|integer|min:1',
            'status' => 'required',
        ]);

        if ($this->editMode) {
            SubjectModel::find($this->subjectId)->update([
                'nama_mapel' => $this->nama_mapel,
                'kategori' => $this->kategori,
                'sks' => $this->sks,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Mata pelajaran berhasil diupdate.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        } else {
            SubjectModel::create([
                'kode_mapel' => $this->kode_mapel,
                'nama_mapel' => $this->nama_mapel,
                'kategori' => $this->kategori,
                'sks' => $this->sks,
                'status' => $this->status,
            ]);
            $this->dispatch('mary-toast', toast: ['type' => 'success', 'title' => 'Berhasil!', 'description' => 'Mata pelajaran berhasil ditambahkan.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-success', 'timeout' => 3000, 'noProgress' => false]);
        }
        $this->closeModal();
    }

    public function edit($id)
    {
        $s = SubjectModel::find($id);
        $this->subjectId = $s->id;
        $this->kode_mapel = $s->kode_mapel;
        $this->nama_mapel = $s->nama_mapel;
        $this->kategori = $s->kategori;
        $this->sks = $s->sks;
        $this->status = $s->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        SubjectModel::find($id)->delete();
        $this->dispatch('mary-toast', toast: ['type' => 'error', 'title' => 'Dihapus!', 'description' => 'Mata pelajaran berhasil dihapus.', 'position' => 'toast-top toast-end', 'icon' => '', 'css' => 'alert-error', 'timeout' => 3000, 'noProgress' => false]);
    }

    public function render()
    {
        $subjects = SubjectModel::query()
            ->when($this->search, fn($q) => $q->where('nama_mapel', 'like', '%'.$this->search.'%')
                ->orWhere('kode_mapel', 'like', '%'.$this->search.'%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterKategori, fn($q) => $q->where('kategori', 'like', '%'.$this->filterKategori.'%'))
            ->latest()
            ->get();

        $kategoris = SubjectModel::whereNotNull('kategori')->where('kategori', '!=', '')->distinct()->pluck('kategori')->map(fn($k) => ['id' => $k, 'name' => $k])->values()->toArray();

        return view('livewire.pages.program.subject', compact('subjects', 'kategoris'));
    }
}
