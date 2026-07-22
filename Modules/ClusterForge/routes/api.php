<?php

use Illuminate\Support\Facades\Route;
use Modules\ClusterForge\Http\Controllers\ClusterForgeController;

Route::middleware(['api-token'])->prefix('v1')->group(function () {
    Route::get('clusterforges', [ClusterForgeController::class, 'index'])->name('clusterforge.index');
    Route::post('clusterforges', [ClusterForgeController::class, 'store'])->name('clusterforge.store');
    Route::get('clusterforges/{cluster}', [ClusterForgeController::class, 'show'])->name('clusterforge.show');
    Route::put('clusterforges/{cluster}', [ClusterForgeController::class, 'update'])->name('clusterforge.update');
    Route::delete('clusterforges/{cluster}', [ClusterForgeController::class, 'destroy'])->name('clusterforge.destroy');
    Route::get('clusterforges/{cluster}/export', [ClusterForgeController::class, 'export'])->name('clusterforge.export');
});
