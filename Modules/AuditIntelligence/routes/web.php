<?php

use Illuminate\Support\Facades\Route;
use Modules\AuditIntelligence\Http\Controllers\DashboardController;
use Modules\AuditIntelligence\Http\Controllers\FindingController;
use Modules\AuditIntelligence\Http\Controllers\RecommendationController;
use Modules\AuditIntelligence\Http\Controllers\UpsellController;
use Modules\AuditIntelligence\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:audit-intelligence', EnsureCurrentTeam::class])
    ->prefix('app/audit-intelligence')
    ->name('auditintelligence.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('findings', FindingController::class);

        Route::get('/findings/{finding}/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
        Route::get('/findings/{finding}/recommendations/create', [RecommendationController::class, 'create'])->name('recommendations.create');
        Route::post('/findings/{finding}/recommendations', [RecommendationController::class, 'store'])->name('recommendations.store');
        Route::get('/findings/{finding}/recommendations/{recommendation}', [RecommendationController::class, 'show'])->name('recommendations.show');
        Route::get('/findings/{finding}/recommendations/{recommendation}/edit', [RecommendationController::class, 'edit'])->name('recommendations.edit');
        Route::put('/findings/{finding}/recommendations/{recommendation}', [RecommendationController::class, 'update'])->name('recommendations.update');
        Route::delete('/findings/{finding}/recommendations/{recommendation}', [RecommendationController::class, 'destroy'])->name('recommendations.destroy');

        Route::get('/findings/{finding}/upsells/create', [UpsellController::class, 'create'])->name('upsells.create');
        Route::post('/findings/{finding}/upsells', [UpsellController::class, 'store'])->name('upsells.store');
        Route::get('/findings/{finding}/upsells/{upsell}/edit', [UpsellController::class, 'edit'])->name('upsells.edit');
        Route::put('/findings/{finding}/upsells/{upsell}', [UpsellController::class, 'update'])->name('upsells.update');
        Route::delete('/findings/{finding}/upsells/{upsell}', [UpsellController::class, 'destroy'])->name('upsells.destroy');
    });
