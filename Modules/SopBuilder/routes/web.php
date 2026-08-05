<?php

use Illuminate\Support\Facades\Route;
use Modules\SopBuilder\Http\Controllers\CategoryController;
use Modules\SopBuilder\Http\Controllers\DashboardController;
use Modules\SopBuilder\Http\Controllers\ExecutionController;
use Modules\SopBuilder\Http\Controllers\SopController;
use Modules\SopBuilder\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:sop-builder', EnsureCurrentTeam::class])
    ->prefix('app/sopbuilder')
    ->name('sopbuilder.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/sops', [SopController::class, 'index'])->name('sops.index');
        Route::get('/sops/create', [SopController::class, 'create'])->name('sops.create');
        Route::post('/sops', [SopController::class, 'store'])->name('sops.store');
        Route::get('/sops/{sop}', [SopController::class, 'show'])->name('sops.show');
        Route::get('/sops/{sop}/edit', [SopController::class, 'edit'])->name('sops.edit');
        Route::put('/sops/{sop}', [SopController::class, 'update'])->name('sops.update');
        Route::delete('/sops/{sop}', [SopController::class, 'destroy'])->name('sops.destroy');
        Route::post('/sops/{sop}/publish', [SopController::class, 'publish'])->name('sops.publish');
        Route::post('/sops/{sop}/duplicate', [SopController::class, 'duplicate'])->name('sops.duplicate');

        Route::get('/sops/{sop}/execute', [ExecutionController::class, 'show'])->name('execute.show');
        Route::post('/sops/{sop}/execute', [ExecutionController::class, 'store'])->name('execute.store');
        Route::get('/sops/{sop}/evidence/create', [ExecutionController::class, 'createEvidence'])->name('evidence.create');
        Route::post('/sops/{sop}/evidence', [ExecutionController::class, 'storeEvidence'])->name('evidence.store');
    });
