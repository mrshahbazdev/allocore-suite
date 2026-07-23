<?php

use Illuminate\Support\Facades\Route;
use Modules\SweetSpot\Http\Controllers\CustomerController;
use Modules\SweetSpot\Http\Controllers\DashboardController;
use Modules\SweetSpot\Http\Controllers\SettingsWeightController;
use Modules\SweetSpot\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:sweet-spot', EnsureCurrentTeam::class])
    ->prefix('app/sweetspot')
    ->name('sweetspot.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/recalculate', [DashboardController::class, 'recalculate'])->name('recalculate');

        Route::resource('customers', CustomerController::class);

        Route::get('settings', [SettingsWeightController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsWeightController::class, 'update'])->name('settings.update');
    });
