<?php

use Illuminate\Support\Facades\Route;
use Modules\KpiTool\Http\Controllers\DashboardController;
use Modules\KpiTool\Http\Controllers\KpiCatalogController;
use Modules\KpiTool\Http\Controllers\KpiDefinitionController;
use Modules\KpiTool\Http\Controllers\KpiSpreadsheetController;
use Modules\KpiTool\Http\Controllers\KpiTargetController;
use Modules\KpiTool\Http\Controllers\KpiValueController;
use Modules\KpiTool\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:kpi-tool', EnsureCurrentTeam::class])
    ->prefix('app/kpitool')
    ->name('kpitool.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/catalog', [KpiCatalogController::class, 'index'])->name('catalog.index');
        Route::post('/catalog/{kpiDefinition}/clone', [KpiCatalogController::class, 'clone'])->name('catalog.clone');

        Route::resource('definitions', KpiDefinitionController::class);

        Route::get('/definitions/{kpiDefinition}/values', [KpiValueController::class, 'index'])->name('values.index');
        Route::post('/definitions/{kpiDefinition}/values', [KpiValueController::class, 'store'])->name('values.store');
        Route::get('/values/{kpiValue}/edit', [KpiValueController::class, 'edit'])->name('values.edit');
        Route::put('/values/{kpiValue}', [KpiValueController::class, 'update'])->name('values.update');
        Route::delete('/values/{kpiValue}', [KpiValueController::class, 'destroy'])->name('values.destroy');

        Route::get('/targets', [KpiTargetController::class, 'index'])->name('targets.index');
        Route::post('/targets/generate', [KpiTargetController::class, 'generate'])->name('targets.generate');
        Route::get('/definitions/{kpiDefinition}/targets', [KpiTargetController::class, 'show'])->name('targets.show');
        Route::post('/definitions/{kpiDefinition}/targets', [KpiTargetController::class, 'store'])->name('targets.store');
        Route::delete('/targets/{kpiMonthlyTarget}', [KpiTargetController::class, 'destroy'])->name('targets.destroy');

        Route::get('/spreadsheet', [KpiSpreadsheetController::class, 'index'])->name('spreadsheet.index');
        Route::get('/spreadsheet/export', [KpiSpreadsheetController::class, 'export'])->name('spreadsheet.export');
        Route::post('/spreadsheet/import', [KpiSpreadsheetController::class, 'import'])->name('spreadsheet.import');
    });
