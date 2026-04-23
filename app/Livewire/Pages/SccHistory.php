<?php
namespace App\Livewire\Pages;
use App\Models\SccData;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SccHistory extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'fase')]
    public string $phase = '';

    #[Url(as: 'from')]
    public string $startDate = '';

    #[Url(as: 'to')]
    public string $endDate = '';

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    public array $sortableFields = [
        'id', 'created_at', 'vbat', 'vpv', 'ibat', 'soc', 'duty_cycle', 'fase',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPhase(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortableFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        if ($field === 'created_at') {
            $this->sortDirection = $this->sortField === 'created_at' && $this->sortDirection === 'asc'
                ? 'asc'
                : $this->sortDirection;
        }

        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'phase', 'startDate', 'endDate', 'sortField', 'sortDirection']);
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
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
            'sort' => $this->sortField,
            'direction' => $this->sortDirection,
        ], fn ($value) => $value !== ''));
    }

    public function render()
    {
        $history = $this->query()
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);

        $summary = [
            'total' => (clone $this->query())->count(),
            'latest' => (clone $this->query())->latest()->first(),
        ];

        return view('livewire.pages.scc-history', compact('history', 'summary'));
    }
}
