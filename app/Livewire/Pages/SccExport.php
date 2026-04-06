<?php
namespace App\Livewire\Pages;
use App\Models\SccData;
use Livewire\Component;
class SccExport extends Component
{
    public function render()
    {
        $total = SccData::count();
        $latest = SccData::latest()->first();
        return view('livewire.pages.scc-export', compact('total', 'latest'));
    }
}
