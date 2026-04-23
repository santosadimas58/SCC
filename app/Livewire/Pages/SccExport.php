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
        $preview = SccData::latest()->take(5)->get();
        $todayCount = SccData::whereDate('created_at', today())->count();
        $phaseCounts = SccData::selectRaw('fase, count(*) as total')
            ->groupBy('fase')
            ->pluck('total', 'fase');

        return view('livewire.pages.scc-export', compact('total', 'latest', 'preview', 'todayCount', 'phaseCounts'));
    }
}
