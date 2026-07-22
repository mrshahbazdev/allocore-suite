<?php

use Illuminate\Support\Facades\Route;
use Modules\NurDu\Http\Controllers\DashboardController;
use Modules\NurDu\Http\Controllers\DecisionController;
use Modules\NurDu\Http\Controllers\QuarterlyFocusController;
use Modules\NurDu\Http\Controllers\VisionCheckController;
use Modules\NurDu\Http\Controllers\VisionController;
use Modules\NurDu\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:nur-du', EnsureCurrentTeam::class])
    ->prefix('app/nurdu')
    ->name('nurdu.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('vision', [VisionController::class, 'index'])->name('vision.index');
        Route::post('vision', [VisionController::class, 'store'])->name('vision.store');
        Route::post('vision/principles', [VisionController::class, 'storePrinciple'])->name('vision.principles.store');
        Route::patch('vision/principles/{principle}', [VisionController::class, 'updatePrinciple'])->name('vision.principles.update');
        Route::delete('vision/principles/{principle}', [VisionController::class, 'destroyPrinciple'])->name('vision.principles.destroy');

        Route::get('quarterly', [QuarterlyFocusController::class, 'index'])->name('quarterly.index');
        Route::post('quarterly', [QuarterlyFocusController::class, 'store'])->name('quarterly.store');
        Route::get('quarterly/{quarterlyFocus}', [QuarterlyFocusController::class, 'show'])->name('quarterly.show');
        Route::delete('quarterly/{quarterlyFocus}', [QuarterlyFocusController::class, 'destroy'])->name('quarterly.destroy');
        Route::post('quarterly/{quarterlyFocus}/priorities', [QuarterlyFocusController::class, 'storePriority'])->name('quarterly.priorities.store');
        Route::patch('priorities/{priority}', [QuarterlyFocusController::class, 'updatePriority'])->name('priorities.update');
        Route::delete('priorities/{priority}', [QuarterlyFocusController::class, 'destroyPriority'])->name('priorities.destroy');

        Route::resource('decisions', DecisionController::class)->except(['show']);

        Route::get('checks', [VisionCheckController::class, 'index'])->name('checks.index');
        Route::get('checks/create', [VisionCheckController::class, 'create'])->name('checks.create');
        Route::post('checks', [VisionCheckController::class, 'store'])->name('checks.store');
        Route::get('checks/{check}', [VisionCheckController::class, 'show'])->name('checks.show');
        Route::delete('checks/{check}', [VisionCheckController::class, 'destroy'])->name('checks.destroy');
        Route::patch('checks/action-items/{actionItem}', [VisionCheckController::class, 'toggleActionItem'])->name('checks.action-items.toggle');
    });
