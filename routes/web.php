<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\DataCollector;
use App\Livewire\CompareCouncils;
use App\Livewire\SectorDashboard;
use App\Livewire\CouncilDetail;
use App\Livewire\LCPDashboard;
use App\Livewire\MapView;
use App\Http\Controllers\ExportController;


Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', LCPDashboard::class)->name('dashboard');
    Route::get('/collect', DataCollector::class)->name('data.collect');
    Route::get('/compare', CompareCouncils::class)->name('compare.councils');
    Route::get('/sector/{code}', SectorDashboard::class)->name('sector.dashboard');
    Route::get('/councils/{council}', CouncilDetail::class)->name('councils.detail');
    Route::get('/map', MapView::class)->name('map.view');

   Route::prefix('exports')->group(function () {
    // Optional landing page with buttons/filters
    Route::get('/', [ExportController::class, 'index'])->name('exports.index');

    // CSV endpoints (GET): pass ?from=YYYY-MM-DD&to=YYYY-MM-DD etc.
    Route::get('/finance.csv',    [ExportController::class, 'financeCsv'])->name('exports.finance');
    Route::get('/indicators.csv', [ExportController::class, 'indicatorsCsv'])->name('exports.indicators');
    Route::get('/projects.csv',   [ExportController::class, 'projectsCsv'])->name('exports.projects');
    Route::get('/issues.csv',     [ExportController::class, 'issuesCsv'])->name('exports.issues');
});
    // GeoJSON FeatureCollection for councils (used by Leaflet map)
    Route::get('/geo/councils', [CouncilGeoController::class, 'featureCollection'])->name('geo.councils');
});

