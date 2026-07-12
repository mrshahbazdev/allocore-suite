<?php

use Illuminate\Support\Facades\Route;
use Modules\AuditPro\Http\Controllers\AuditController;
use Modules\AuditPro\Http\Middleware\EnsureCurrentTeam;
use Modules\AuditPro\Livewire\Assessment;
use Modules\AuditPro\Livewire\AuditComparison;
use Modules\AuditPro\Livewire\AuditList;
use Modules\AuditPro\Livewire\TemplateBuilder;
use Modules\AuditPro\Livewire\TemplateList;

Route::prefix('app/audit')
    ->name('audit.')
    ->middleware(['auth', 'verified', 'module:audit', EnsureCurrentTeam::class])
    ->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::post('/audits', [AuditController::class, 'start'])->name('start');
        Route::get('/audits', AuditList::class)->name('audits');
        Route::get('/audits/{audit}/assessment', Assessment::class)->name('assessment');
        Route::get('/audits/{audit}/results', [AuditController::class, 'results'])->name('results');
        Route::get('/audits/{audit}/report', [AuditController::class, 'report'])->name('report');
        Route::delete('/audits/{audit}', [AuditController::class, 'destroy'])->name('destroy');
        Route::get('/compare', AuditComparison::class)->name('compare');
        Route::get('/templates', TemplateList::class)->name('templates');
        Route::get('/templates/{template}', TemplateBuilder::class)->name('templates.builder');
    });
