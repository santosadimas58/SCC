<?php

use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\SccDashboard;
use App\Livewire\Pages\SccHistory;
use App\Livewire\Pages\SccFuzzy;
use App\Livewire\Pages\SccRules;
use App\Livewire\Pages\SccExport;
use App\Livewire\Pages\SccAbout;

Route::get('/', Login::class)->name('login');

Route::get('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/scc',         SccDashboard::class)->name('scc.dashboard');
    Route::get('/scc/history', SccHistory::class)->name('scc.history');
    Route::get('/scc/fuzzy',   SccFuzzy::class)->name('scc.fuzzy');
    Route::get('/scc/rules',   SccRules::class)->name('scc.rules');
    Route::get('/scc/export',  SccExport::class)->name('scc.export');
    Route::get('/scc/about',   SccAbout::class)->name('scc.about');
});

Route::get('/scc/export/csv', function () {
    $query = \App\Models\SccData::query();

    if ($search = request('search')) {
        $query->where(function ($sub) use ($search) {
            $sub->where('fase', 'like', '%' . $search . '%')
                ->orWhere('label_e', 'like', '%' . $search . '%')
                ->orWhere('label_de', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        });
    }

    if ($phase = request('phase')) {
        $query->where('fase', $phase);
    }

    if ($startDate = request('start_date')) {
        $query->whereDate('created_at', '>=', $startDate);
    }

    if ($endDate = request('end_date')) {
        $query->whereDate('created_at', '<=', $endDate);
    }

    $sort = request('sort', 'created_at');
    $direction = request('direction', 'desc');
    $allowedSorts = ['created_at', 'vbat', 'vpv', 'ibat', 'soc', 'duty_cycle', 'fase'];

    if (! in_array($sort, $allowedSorts, true)) {
        $sort = 'created_at';
    }

    if (! in_array($direction, ['asc', 'desc'], true)) {
        $direction = 'desc';
    }

    $data = $query->orderBy($sort, $direction)->get();
    $csv = "ID,Waktu,Vbat,Vpv,Ibat,Ipv,SoC,Duty Cycle,Fase,Label E,Label dE\n";
    foreach ($data as $row) {
        $csv .= "{$row->id},{$row->created_at},{$row->vbat},{$row->vpv},{$row->ibat},{$row->ipv},{$row->soc},{$row->duty_cycle},{$row->fase},{$row->label_e},{$row->label_de}\n";
    }
    return response($csv, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="scc-data-'.now()->format('Ymd-His').'.csv"',
    ]);
})->middleware('auth')->name('scc.export.csv');
