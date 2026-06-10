<?php
namespace App\Livewire\Pages;

use App\Services\Scc\FuzzyChargeController;
use Livewire\Component;

class SccRules extends Component
{
    public function render()
    {
        return view('livewire.pages.scc-rules', [
            'rules' => FuzzyChargeController::rules(),
        ]);
    }
}
