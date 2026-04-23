<?php
namespace App\Livewire\Pages\Admin;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Inventory as InventoryModel;
#[Layout('layouts.app')]
class Inventory extends Component
{
    public $inventories;
    public $showModal = false;
    public $editMode = false;
    public $inventoryId;
    public $kode_barang = '';
    public $nama_barang = '';
    public $kategori = '';
    public $jumlah = 0;
    public $kondisi = 'Baik';
    public $lokasi = '';

    public function mount()
    {
        $this->loadInventories();
    }

    public function loadInventories()
    {
        $this->inventories = InventoryModel::latest()->get();
    }

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->showModal = true;
        $this->kode_barang = 'INV-' . str_pad(InventoryModel::count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->inventoryId = null;
        $this->kode_barang = '';
        $this->nama_barang = '';
        $this->kategori = '';
        $this->jumlah = 0;
        $this->kondisi = 'Baik';
        $this->lokasi = '';
    }

    public function save()
    {
        $this->validate([
            'nama_barang' => 'required|min:2',
            'kategori' => 'required',
            'jumlah' => 'required|integer|min:0',
            'kondisi' => 'required',
        ]);

        if ($this->editMode) {
            InventoryModel::find($this->inventoryId)->update([
                'nama_barang' => $this->nama_barang,
                'kategori' => $this->kategori,
                'jumlah' => $this->jumlah,
                'kondisi' => $this->kondisi,
                'lokasi' => $this->lokasi,
            ]);
        } else {
            InventoryModel::create([
                'kode_barang' => $this->kode_barang,
                'nama_barang' => $this->nama_barang,
                'kategori' => $this->kategori,
                'jumlah' => $this->jumlah,
                'kondisi' => $this->kondisi,
                'lokasi' => $this->lokasi,
            ]);
        }

        $this->closeModal();
        $this->loadInventories();
    }

    public function edit($id)
    {
        $item = InventoryModel::find($id);
        $this->inventoryId = $item->id;
        $this->kode_barang = $item->kode_barang;
        $this->nama_barang = $item->nama_barang;
        $this->kategori = $item->kategori;
        $this->jumlah = $item->jumlah;
        $this->kondisi = $item->kondisi;
        $this->lokasi = $item->lokasi;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        InventoryModel::find($id)->delete();
        $this->loadInventories();
    }

    public function render()
    {
        return view('livewire.pages.admin.inventory');
    }
}
