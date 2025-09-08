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

    // Exports (CSV + XLSX)
    Route::get('/exports/finance.csv',    [ExportController::class, 'finance'])->name('export.finance');
    Route::get('/exports/indicators.csv', [ExportController::class, 'indicators'])->name('export.indicators');
    Route::get('/exports/projects.csv',   [ExportController::class, 'projects'])->name('export.projects');
    Route::get('/exports/issues.csv',     [ExportController::class, 'issues'])->name('export.issues');
    // Queue exports (JSON response with URL; requires queue worker)
    Route::post('/exports/finance.queue', [ExportController::class, 'queueFinanceXlsx'])->name('exports.finance.queue');
    Route::post('/exports/indicator.queue', [ExportController::class, 'queueIndicatorXlsx'])->name('exports.indicator.queue');
    // GeoJSON FeatureCollection for councils (used by Leaflet map)
    Route::get('/geo/councils', [CouncilGeoController::class, 'featureCollection'])->name('geo.councils');
});

