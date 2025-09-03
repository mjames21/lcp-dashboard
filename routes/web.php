<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\DataCollector;
use App\Livewire\CompareCouncils;
use App\Livewire\SectorDashboard;
use App\Livewire\CouncilDetail;
use App\Livewire\MapView;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/collect', DataCollector::class)->name('data.collect');
    Route::get('/compare', CompareCouncils::class)->name('compare.councils');
    Route::get('/sector/{code}', SectorDashboard::class)->name('sector.dashboard');
    Route::get('/councils/{council}', CouncilDetail::class)->name('councils.detail');
    Route::get('/map', MapView::class)->name('map.view');

    // Exports (CSV + XLSX)
    Route::get('/exports/finance.csv', [ExportController::class, 'financeCsv'])->name('exports.finance.csv');
    Route::get('/exports/indicator.csv', [ExportController::class, 'indicatorCsv'])->name('exports.indicator.csv');
    Route::get('/exports/finance.xlsx', [ExportController::class, 'financeXlsx'])->name('exports.finance.xlsx');
    Route::get('/exports/indicator.xlsx',[ExportController::class, 'indicatorXlsx'])->name('exports.indicator.xlsx');
    // Queue exports (JSON response with URL; requires queue worker)
    Route::post('/exports/finance.queue', [ExportController::class, 'queueFinanceXlsx'])->name('exports.finance.queue');
    Route::post('/exports/indicator.queue', [ExportController::class, 'queueIndicatorXlsx'])->name('exports.indicator.queue');
    // GeoJSON FeatureCollection for councils (used by Leaflet map)
    Route::get('/geo/councils', [CouncilGeoController::class, 'featureCollection'])->name('geo.councils');
});

