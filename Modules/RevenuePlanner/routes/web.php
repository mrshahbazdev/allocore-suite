<?php

use Illuminate\Support\Facades\Route;
use Modules\RevenuePlanner\Http\Controllers\RevenuePlannerController;

Route::middleware(['auth', 'verified', 'module:revenue-planner'])
    ->prefix('app/revenue-planner')
    ->name('revenueplanner.')
    ->group(function (): void {
        Route::get('/', [RevenuePlannerController::class, 'index'])->name('dashboard');
        Route::get('/wizard/{plan?}', [RevenuePlannerController::class, 'wizard'])->name('wizard');
        Route::post('/wizard', [RevenuePlannerController::class, 'saveWizard'])->name('wizard.save');
        
        Route::get('/plans/{plan}', [RevenuePlannerController::class, 'show'])->name('plans.show');
        Route::post('/plans/{plan}/sync-invoicemaker', [RevenuePlannerController::class, 'syncInvoiceMaker'])->name('plans.sync-invoicemaker');
        Route::post('/plans/{plan}/scenarios', [RevenuePlannerController::class, 'storeScenario'])->name('plans.scenarios.store');
        Route::delete('/plans/{plan}', [RevenuePlannerController::class, 'destroy'])->name('plans.destroy');
    });
