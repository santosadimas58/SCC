<?php
namespace App\Livewire\Pages;
use App\Models\SccData;
use Livewire\Component;
class SccHistory extends Component
{
    public function render()
    {
        $history = SccData::latest()->paginate(50);
        return view('livewire.pages.scc-history', compact('history'));
    }
}
