<?php

use Illuminate\Support\Facades\Route;
use Modules\LoopEngine\Http\Controllers\DashboardController;
use Modules\LoopEngine\Http\Controllers\ExportController;
use Modules\LoopEngine\Http\Controllers\ProcessController;
use Modules\LoopEngine\Http\Controllers\RunController;
use Modules\LoopEngine\Http\Controllers\TeamController;
use Modules\LoopEngine\Http\Controllers\TemplateController;
use Modules\LoopEngine\Http\Controllers\WebhookController;
use Modules\LoopEngine\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:loop-engine', EnsureCurrentTeam::class])
    ->prefix('app/loopengine')
    ->name('loopengine.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('processes', ProcessController::class);
        Route::post('/processes/{process}/duplicate', [ProcessController::class, 'duplicate'])->name('processes.duplicate');
        Route::post('/processes/{process}/version', [ProcessController::class, 'newVersion'])->name('processes.version');
        Route::post('/processes/{process}/activate', [ProcessController::class, 'activate'])->name('processes.activate');
        Route::post('/processes/{process}/archive', [ProcessController::class, 'archive'])->name('processes.archive');
        Route::post('/processes/{process}/steps/reorder', [ProcessController::class, 'reorderSteps'])->name('processes.steps.reorder');

        Route::post('/processes/{process}/steps', [ProcessController::class, 'storeStep'])->name('steps.store');
        Route::get('/steps/{step}/edit', [ProcessController::class, 'editStep'])->name('steps.edit');
        Route::put('/steps/{step}', [ProcessController::class, 'updateStep'])->name('steps.update');
        Route::delete('/steps/{step}', [ProcessController::class, 'destroyStep'])->name('steps.destroy');

        Route::post('/steps/{step}/options', [ProcessController::class, 'storeOption'])->name('options.store');
        Route::delete('/options/{option}', [ProcessController::class, 'destroyOption'])->name('options.destroy');

        Route::post('/steps/{step}/transitions', [ProcessController::class, 'storeTransition'])->name('transitions.store');
        Route::delete('/transitions/{transition}', [ProcessController::class, 'destroyTransition'])->name('transitions.destroy');

        Route::get('/runs', [RunController::class, 'index'])->name('runs.index');
        Route::post('/processes/{process}/runs', [RunController::class, 'start'])->name('runs.start');
        Route::get('/runs/{run}', [RunController::class, 'show'])->name('runs.show');
        Route::post('/runs/{run}/answer', [RunController::class, 'answer'])->name('runs.answer');
        Route::post('/runs/{run}/pause', [RunController::class, 'pause'])->name('runs.pause');
        Route::post('/runs/{run}/resume', [RunController::class, 'resume'])->name('runs.resume');
        Route::post('/runs/{run}/cancel', [RunController::class, 'cancel'])->name('runs.cancel');
        Route::get('/runs/{run}/summary', [RunController::class, 'summary'])->name('runs.summary');

        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');
        Route::post('/processes/{process}/share', [TemplateController::class, 'share'])->name('templates.share');
        Route::post('/templates/{template}/install', [TemplateController::class, 'install'])->name('templates.install');
        Route::post('/templates/{template}/rate', [TemplateController::class, 'rate'])->name('templates.rate');
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');

        Route::resource('webhooks', WebhookController::class);
        Route::get('/webhooks/{webhook}/logs', [WebhookController::class, 'logs'])->name('webhooks.logs');

        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('/team/assign', [TeamController::class, 'createAssignment'])->name('team.assign.create');
        Route::post('/team/assign', [TeamController::class, 'storeAssignment'])->name('team.assign.store');
        Route::get('/team/assignments', [TeamController::class, 'assignments'])->name('team.assignments');

        Route::get('/export/runs/csv', [ExportController::class, 'runsCsv'])->name('export.runs.csv');
        Route::get('/export/logs/csv', [ExportController::class, 'logsCsv'])->name('export.logs.csv');
    });
