<?php

use Illuminate\Support\Facades\Route;
use Modules\ClusterForge\Http\Controllers\ClusterForgeController;

Route::middleware(['api-token'])->group(function () {
    Route::get('clusterforges', [ClusterForgeController::class, 'index'])->name('clusterforge.index');
    Route::post('clusterforges', [ClusterForgeController::class, 'store'])->name('clusterforge.store');
    Route::get('clusterforges/{project}', [ClusterForgeController::class, 'show'])->name('clusterforge.show');
    Route::delete('clusterforges/{project}', [ClusterForgeController::class, 'destroy'])->name('clusterforge.destroy');
    Route::get('clusterforges/{project}/export/pillar', [ClusterForgeController::class, 'exportPillar'])->name('clusterforge.export.pillar');
});
