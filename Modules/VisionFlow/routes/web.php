<?php

use Illuminate\Support\Facades\Route;
use Modules\VisionFlow\Http\Controllers\DashboardController;
use Modules\VisionFlow\Http\Controllers\DecisionLogController;
use Modules\VisionFlow\Http\Controllers\MissionController;
use Modules\VisionFlow\Http\Controllers\OrganizationController;
use Modules\VisionFlow\Http\Controllers\PrincipleController;
use Modules\VisionFlow\Http\Controllers\ProjectController;
use Modules\VisionFlow\Http\Controllers\StrategicGoalController;
use Modules\VisionFlow\Http\Controllers\ValueController;
use Modules\VisionFlow\Http\Controllers\VisionController;
use Modules\VisionFlow\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:vision-flow', EnsureCurrentTeam::class])
    ->prefix('app/visionflow')
    ->name('visionflow.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('organizations', OrganizationController::class);

        Route::prefix('organizations/{organization}')->name('organizations.')->group(function (): void {
            Route::resource('values', ValueController::class)->except(['show']);
            Route::post('values/{value}/approve', [ValueController::class, 'approve'])->name('values.approve');
            Route::post('values/{value}/archive', [ValueController::class, 'archive'])->name('values.archive');

            Route::resource('principles', PrincipleController::class)->except(['show']);
            Route::resource('strategic-goals', StrategicGoalController::class)->except(['show']);
            Route::resource('visions', VisionController::class);
            Route::post('visions/{vision}/approve', [VisionController::class, 'approve'])->name('visions.approve');
            Route::post('visions/{vision}/current', [VisionController::class, 'setCurrent'])->name('visions.current');

            Route::resource('missions', MissionController::class)->except(['show']);
            Route::resource('projects', ProjectController::class)->except(['show']);
            Route::resource('decision-logs', DecisionLogController::class)->except(['show']);
        });
    });
