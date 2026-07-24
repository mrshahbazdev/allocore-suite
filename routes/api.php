<?php

use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ModuleResourceController;
use App\Http\Controllers\Api\V1\ModuleStatsController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api-token')->group(function () {
    Route::get('/user', UserController::class)->name('api.user');

    Route::get('/dashboard', DashboardController::class)->name('api.dashboard');
    Route::get('/modules', [ModuleStatsController::class, 'index'])->name('api.modules.index');
    Route::get('/modules/{module}', [ModuleStatsController::class, 'show'])->name('api.modules.show');
    Route::get('/modules/{module}/records', [ModuleResourceController::class, 'index'])->name('api.modules.records.index');
    Route::get('/modules/{module}/records/{id}', [ModuleResourceController::class, 'show'])->name('api.modules.records.show');
});
