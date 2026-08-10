<?php

use Illuminate\Support\Facades\Route;
use Modules\ClusterForge\Http\Controllers\ClusterForgeController;
use Modules\ClusterForge\Http\Middleware\EnsureCurrentTeam;

Route::prefix('app/clusters')
    ->name('clusterforge.')
    ->middleware(['auth', 'verified', 'module:keyword-cluster', EnsureCurrentTeam::class])
    ->group(function () {
        Route::get('/', [ClusterForgeController::class, 'index'])->name('index');
        Route::get('/create', [ClusterForgeController::class, 'create'])->name('create');
        Route::post('/', [ClusterForgeController::class, 'store'])->name('store');
        Route::get('/{project}', [ClusterForgeController::class, 'show'])->name('show');
        Route::get('/{project}/status', [ClusterForgeController::class, 'status'])->name('status');
        Route::post('/{project}/retry', [ClusterForgeController::class, 'retry'])->name('retry');
        Route::delete('/{project}', [ClusterForgeController::class, 'destroy'])->name('destroy');
        Route::get('/{project}/export/pillar', [ClusterForgeController::class, 'exportPillar'])->name('export.pillar');
        Route::get('/{project}/export/cluster/{subtopic}', [ClusterForgeController::class, 'exportCluster'])->name('export.cluster');
    });
