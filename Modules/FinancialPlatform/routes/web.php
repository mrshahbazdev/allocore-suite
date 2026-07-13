<?php

use Illuminate\Support\Facades\Route;
use Modules\FinancialPlatform\Http\Controllers\CompanyController;
use Modules\FinancialPlatform\Http\Controllers\DashboardController;
use Modules\FinancialPlatform\Http\Controllers\ExcelImportController;
use Modules\FinancialPlatform\Http\Controllers\GmbhAnalyseController;
use Modules\FinancialPlatform\Http\Controllers\ImmobilienController;
use Modules\FinancialPlatform\Http\Controllers\JahresabschlussController;
use Modules\FinancialPlatform\Http\Controllers\LeadController;
use Modules\FinancialPlatform\Http\Controllers\PaypalController;
use Modules\FinancialPlatform\Http\Middleware\EnsureCurrentTeam;
use Modules\FinancialPlatform\Models\Analysis;

Route::middleware(['auth', 'verified', 'module:financial-platform', EnsureCurrentTeam::class])
    ->prefix('app/finance')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('financial.dashboard');

        Route::resource('companies', CompanyController::class);

        Route::get('/gmbh', [GmbhAnalyseController::class, 'index'])->name('gmbh.index');
        Route::get('/gmbh/create', [GmbhAnalyseController::class, 'create'])->name('gmbh.create');
        Route::post('/gmbh', [GmbhAnalyseController::class, 'store'])->name('gmbh.store');
        Route::get('/gmbh/{gmbh}', [GmbhAnalyseController::class, 'show'])->name('gmbh.show');
        Route::get('/gmbh/{gmbh}/edit', [GmbhAnalyseController::class, 'edit'])->name('gmbh.edit');
        Route::patch('/gmbh/{gmbh}', [GmbhAnalyseController::class, 'update'])->name('gmbh.update');
        Route::delete('/gmbh/{gmbh}', [GmbhAnalyseController::class, 'destroy'])->name('gmbh.destroy');
        Route::get('/gmbh/{gmbh}/pdf', [GmbhAnalyseController::class, 'exportPdf'])->name('gmbh.pdf');

        Route::get('/jahresabschluss', [JahresabschlussController::class, 'index'])->name('jahresabschluss.index');
        Route::get('/jahresabschluss/create', [JahresabschlussController::class, 'create'])->name('jahresabschluss.create');
        Route::post('/jahresabschluss', [JahresabschlussController::class, 'store'])->name('jahresabschluss.store');
        Route::get('/jahresabschluss/{jahresabschluss}', [JahresabschlussController::class, 'show'])->name('jahresabschluss.show');
        Route::delete('/jahresabschluss/{jahresabschluss}', [JahresabschlussController::class, 'destroy'])->name('jahresabschluss.destroy');
        Route::get('/jahresabschluss/{jahresabschluss}/pdf', [JahresabschlussController::class, 'exportPdf'])->name('jahresabschluss.pdf');

        Route::get('/immobilien', [ImmobilienController::class, 'index'])->name('immobilien.index');
        Route::get('/immobilien/create', [ImmobilienController::class, 'create'])->name('immobilien.create');
        Route::post('/immobilien', [ImmobilienController::class, 'store'])->name('immobilien.store');
        Route::get('/immobilien/compare', [ImmobilienController::class, 'compare'])->name('immobilien.compare');
        Route::get('/immobilien/{immobilien}', [ImmobilienController::class, 'show'])->name('immobilien.show');
        Route::delete('/immobilien/{immobilien}', [ImmobilienController::class, 'destroy'])->name('immobilien.destroy');
        Route::get('/immobilien/{immobilien}/pdf', [ImmobilienController::class, 'exportPdf'])->name('immobilien.pdf');

        Route::get('/analyses', function () {
            $analyses = Analysis::query()
                ->with('company')
                ->latest()
                ->paginate(20);

            return view('financialplatform::analyses.index', compact('analyses'));
        })->name('analyses.index');

        Route::get('/import', [ExcelImportController::class, 'show'])->name('import.index');
        Route::post('/import', [ExcelImportController::class, 'import'])->name('import.upload');
        Route::get('/import/template/{type}', [ExcelImportController::class, 'downloadTemplate'])->name('import.template');

        Route::resource('leads', LeadController::class);
        Route::post('/leads-transfer', [LeadController::class, 'transferToLeadOs'])->name('leads.transfer');
        Route::get('/leads-export', [LeadController::class, 'exportCsv'])->name('leads.export');

        Route::get('/paypal', [PaypalController::class, 'index'])->name('paypal.index');
        Route::get('/paypal/settings', [PaypalController::class, 'settings'])->name('paypal.settings');
        Route::post('/paypal/settings', [PaypalController::class, 'saveSettings'])->name('paypal.save-settings');
        Route::post('/paypal/create-payment', [PaypalController::class, 'createPayment'])->name('paypal.create-payment');
        Route::get('/paypal/capture', [PaypalController::class, 'capture'])->name('paypal.capture');
        Route::get('/paypal/cancel', [PaypalController::class, 'cancel'])->name('paypal.cancel');
        Route::get('/paypal/{transaction}', [PaypalController::class, 'show'])->name('paypal.show');
    });
