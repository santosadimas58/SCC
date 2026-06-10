<?php
namespace App\Livewire\Pages;
use App\Models\SccData;
use Livewire\Attributes\Url;
use Livewire\Component;
class SccExport extends Component
{
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'fase')]
    public string $phase = '';

    #[Url(as: 'from')]
    public string $startDate = '';

    #[Url(as: 'to')]
    public string $endDate = '';

    public function resetFilter(): void
    {
        $this->reset(['search', 'phase', 'startDate', 'endDate']);
    }

    protected function query()
    {
        return SccData::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('fase', 'like', '%' . $this->search . '%')
                        ->orWhere('label_e', 'like', '%' . $this->search . '%')
                        ->orWhere('label_de', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->phase !== '', fn ($query) => $query->where('fase', $this->phase))
            ->when($this->startDate !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->endDate));
    }

    public function getExportUrlProperty(): string
    {
        return route('scc.export.csv', array_filter([
            'search' => $this->search,
            'phase' => $this->phase,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ], fn ($value) => $value !== ''));
    }

    public function render()
    {
        $total = SccData::count();
        $latest = SccData::latest()->first();
        $filteredTotal = (clone $this->query())->count();
        $preview = $this->query()->latest()->take(8)->get();
        $todayCount = SccData::whereDate('created_at', today())->count();
        $phaseCounts = (clone $this->query())->selectRaw('fase, count(*) as total')
            ->groupBy('fase')
            ->pluck('total', 'fase');

        return view('livewire.pages.scc-export', compact('total', 'filteredTotal', 'latest', 'preview', 'todayCount', 'phaseCounts'));
    }
}
