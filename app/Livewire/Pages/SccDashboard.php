<?php

namespace App\Livewire\Pages;

use App\Models\SccData;
use Livewire\Component;

class SccDashboard extends Component
{
    public $latest;
    public $history;

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->latest  = SccData::latest()->first();
        $this->history = SccData::latest()->take(20)->get();
    }

    public function render()
    {
        return view('livewire.pages.scc-dashboard')
            ->title('SCC Monitoring');
    }
}
