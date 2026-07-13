<?php

use Illuminate\Support\Facades\Route;
use Modules\LeadQuality\Http\Controllers\ActivityController;
use Modules\LeadQuality\Http\Controllers\AnalyticsController;
use Modules\LeadQuality\Http\Controllers\ContactController;
use Modules\LeadQuality\Http\Controllers\DashboardController;
use Modules\LeadQuality\Http\Controllers\DiagnosticController;
use Modules\LeadQuality\Http\Controllers\EmailAccountController;
use Modules\LeadQuality\Http\Controllers\IcpProfileController;
use Modules\LeadQuality\Http\Controllers\LanguageController;
use Modules\LeadQuality\Http\Controllers\PipelineController;
use Modules\LeadQuality\Http\Controllers\SequenceController;
use Modules\LeadQuality\Http\Middleware\EnsureCurrentTeam;

Route::get('lang/{locale}', LanguageController::class)->name('leadquality.lang.switch');

Route::prefix('app/leads')
    ->name('leadquality.')
    ->middleware(['auth', 'verified', 'module:lead-quality', EnsureCurrentTeam::class])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('contacts', ContactController::class);
        Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');
        Route::post('contacts/{contact}/activities', [ActivityController::class, 'store'])->name('contacts.activities.store');
        Route::post('contacts/{contact}/analyze', [ContactController::class, 'analyzeAi'])->name('contacts.analyze');

        Route::get('pipeline', PipelineController::class)->name('pipeline');
        Route::post('pipeline/update-stage', [PipelineController::class, 'updateStage'])->name('pipeline.update-stage');

        Route::get('icp', [IcpProfileController::class, 'index'])->name('icp.index');
        Route::post('icp', [IcpProfileController::class, 'store'])->name('icp.store');

        Route::get('analytics', AnalyticsController::class)->name('analytics');
        Route::get('diagnostic', DiagnosticController::class)->name('diagnostic.index');
        Route::post('diagnostic', [DiagnosticController::class, 'store'])->name('diagnostic.store');

        Route::get('email-accounts', [EmailAccountController::class, 'index'])->name('email-accounts.index');
        Route::post('email-accounts', [EmailAccountController::class, 'store'])->name('email-accounts.store');
        Route::delete('email-accounts/{emailAccount}', [EmailAccountController::class, 'destroy'])->name('email-accounts.destroy');

        Route::resource('sequences', SequenceController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::post('sequences/{sequence}/steps', [SequenceController::class, 'storeStep'])->name('sequences.steps.store');
        Route::post('sequences/enroll', [SequenceController::class, 'enroll'])->name('sequences.enroll');
    });
