<?php

use Illuminate\Support\Facades\Route;
use Modules\CashCore\Http\Controllers\BehaviorController;
use Modules\CashCore\Http\Controllers\CashLeakController;
use Modules\CashCore\Http\Controllers\CashTransactionController;
use Modules\CashCore\Http\Controllers\CashUnlockerController;
use Modules\CashCore\Http\Controllers\DashboardController;
use Modules\CashCore\Http\Controllers\ExpenseScoringController;
use Modules\CashCore\Http\Controllers\ProfitAllocationController;
use Modules\CashCore\Http\Controllers\ScenarioController;
use Modules\CashCore\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:cash-core', EnsureCurrentTeam::class])
    ->prefix('app/cashcore')
    ->name('cashcore.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('transactions', [CashTransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/create', [CashTransactionController::class, 'create'])->name('transactions.create');
        Route::post('transactions', [CashTransactionController::class, 'store'])->name('transactions.store');
        Route::get('transactions/{transaction}/edit', [CashTransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('transactions/{transaction}', [CashTransactionController::class, 'update'])->name('transactions.update');
        Route::delete('transactions/{transaction}', [CashTransactionController::class, 'destroy'])->name('transactions.destroy');
        Route::get('transactions/import', [CashTransactionController::class, 'importForm'])->name('transactions.import');
        Route::post('transactions/import', [CashTransactionController::class, 'import'])->name('transactions.import.store');

        Route::get('leaks', [CashLeakController::class, 'index'])->name('leaks.index');
        Route::post('leaks/detect', [CashLeakController::class, 'runDetection'])->name('leaks.detect');
        Route::put('leaks/{leak}/status', [CashLeakController::class, 'updateStatus'])->name('leaks.status');

        Route::get('scoring', [ExpenseScoringController::class, 'index'])->name('scoring.index');
        Route::get('scoring/{transaction}', [ExpenseScoringController::class, 'score'])->name('scoring.score');
        Route::post('scoring/{transaction}', [ExpenseScoringController::class, 'storeScore'])->name('scoring.store');

        Route::get('unlocker', [CashUnlockerController::class, 'index'])->name('unlocker.index');
        Route::get('unlocker/create', [CashUnlockerController::class, 'create'])->name('unlocker.create');
        Route::post('unlocker', [CashUnlockerController::class, 'store'])->name('unlocker.store');
        Route::get('unlocker/{blocker}/edit', [CashUnlockerController::class, 'edit'])->name('unlocker.edit');
        Route::put('unlocker/{blocker}', [CashUnlockerController::class, 'update'])->name('unlocker.update');
        Route::delete('unlocker/{blocker}', [CashUnlockerController::class, 'destroy'])->name('unlocker.destroy');
        Route::put('unlocker/{blocker}/status', [CashUnlockerController::class, 'updateStatus'])->name('unlocker.status');

        Route::get('behavior', [BehaviorController::class, 'index'])->name('behavior.index');
        Route::post('behavior/review', [BehaviorController::class, 'scheduleReview'])->name('behavior.schedule');
        Route::get('behavior/review/{review}', [BehaviorController::class, 'startReview'])->name('behavior.review');
        Route::post('behavior/review/{review}/complete', [BehaviorController::class, 'completeReview'])->name('behavior.complete');
        Route::put('behavior/alert/{alert}/read', [BehaviorController::class, 'markAlertRead'])->name('behavior.alert.read');
        Route::put('behavior/alert/{alert}/dismiss', [BehaviorController::class, 'dismissAlert'])->name('behavior.alert.dismiss');

        Route::get('scenarios', [ScenarioController::class, 'index'])->name('scenarios.index');
        Route::get('scenarios/create', [ScenarioController::class, 'create'])->name('scenarios.create');
        Route::post('scenarios', [ScenarioController::class, 'store'])->name('scenarios.store');
        Route::delete('scenarios/{scenario}', [ScenarioController::class, 'destroy'])->name('scenarios.destroy');

        Route::get('allocation', [ProfitAllocationController::class, 'index'])->name('allocation.index');
        Route::post('allocation', [ProfitAllocationController::class, 'update'])->name('allocation.update');
    });
