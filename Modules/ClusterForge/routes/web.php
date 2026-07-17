<?php

use Illuminate\Support\Facades\Route;
use Modules\ClusterForge\Http\Controllers\ClusterForgeController;
use Modules\ClusterForge\Http\Middleware\EnsureCurrentTeam;

Route::prefix('app/clusters')
    ->name('clusterforge.')
    ->middleware(['auth', 'verified', 'module:keyword-cluster', EnsureCurrentTeam::class])
    ->group(function () {
        Route::get('/', [ClusterForgeController::class, 'index'])->name('index');
        Route::post('/', [ClusterForgeController::class, 'store'])->name('store');
        Route::get('/{cluster}', [ClusterForgeController::class, 'show'])->name('show');
        Route::delete('/{cluster}', [ClusterForgeController::class, 'destroy'])->name('destroy');
    });
