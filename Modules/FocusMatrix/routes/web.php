<?php

use Illuminate\Support\Facades\Route;
use Modules\FocusMatrix\Http\Controllers\AiController;
use Modules\FocusMatrix\Http\Controllers\AnalyticsController;
use Modules\FocusMatrix\Http\Controllers\CalendarController;
use Modules\FocusMatrix\Http\Controllers\DashboardController;
use Modules\FocusMatrix\Http\Controllers\DelegationController;
use Modules\FocusMatrix\Http\Controllers\IntegrationController;
use Modules\FocusMatrix\Http\Controllers\KillListController;
use Modules\FocusMatrix\Http\Controllers\OrgCheckController;
use Modules\FocusMatrix\Http\Controllers\SelfCheckController;
use Modules\FocusMatrix\Http\Controllers\TaskController;
use Modules\FocusMatrix\Http\Controllers\TriageController;
use Modules\FocusMatrix\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:focus-matrix', EnsureCurrentTeam::class])
    ->prefix('app/focusmatrix')
    ->name('focusmatrix.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('tasks', TaskController::class)->except(['create', 'edit']);
        Route::get('tasks/{task}/triage', [TriageController::class, 'show'])->name('tasks.triage');
        Route::post('tasks/{task}/triage', [TriageController::class, 'decide'])->name('tasks.triage.decide');
        Route::post('tasks/{task}/ai-suggest', [TriageController::class, 'aiSuggest'])->name('tasks.ai-suggest');

        Route::get('delegations/assigned', [DelegationController::class, 'assignedIndex'])->name('delegations.assigned');
        Route::post('delegations/{delegation}/accept', [DelegationController::class, 'accept'])->name('delegations.accept');
        Route::post('delegations/{delegation}/decline', [DelegationController::class, 'decline'])->name('delegations.decline');
        Route::post('delegations/draft', [DelegationController::class, 'aiDraft'])->name('delegations.ai-draft');
        Route::resource('delegations', DelegationController::class)->except(['edit']);

        Route::get('kill-list', [KillListController::class, 'index'])->name('kill-list.index');
        Route::post('kill-list', [KillListController::class, 'store'])->name('kill-list.store');
        Route::delete('kill-list/{item}', [KillListController::class, 'destroy'])->name('kill-list.destroy');

        Route::get('self-check', [SelfCheckController::class, 'index'])->name('self-check.index');
        Route::post('self-check', [SelfCheckController::class, 'store'])->name('self-check.store');
        Route::post('self-check/insights', [SelfCheckController::class, 'aiInsights'])->name('self-check.ai-insights');

        Route::get('org-check', [OrgCheckController::class, 'index'])->name('org-check.index');
        Route::post('org-check', [OrgCheckController::class, 'store'])->name('org-check.store');

        Route::get('integrations', [IntegrationController::class, 'index'])->name('integrations.index');
        Route::get('integrations/google/connect', [IntegrationController::class, 'connectGoogle'])->name('integrations.google.connect');
        Route::get('integrations/google/callback', [IntegrationController::class, 'callbackGoogle'])->name('integrations.google.callback');
        Route::delete('integrations/google', [IntegrationController::class, 'disconnectGoogle'])->name('integrations.google.disconnect');
        Route::post('integrations/webhook/{provider}', [IntegrationController::class, 'connectWebhook'])->name('integrations.webhook.connect');
        Route::post('integrations/webhook/{provider}/test', [IntegrationController::class, 'testWebhook'])->name('integrations.webhook.test');
        Route::delete('integrations/webhook/{provider}', [IntegrationController::class, 'disconnectWebhook'])->name('integrations.webhook.disconnect');
        Route::post('integrations/ics/regenerate', [IntegrationController::class, 'regenerateIcsToken'])->name('integrations.ics.regenerate');

        Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::post('calendar/events', [CalendarController::class, 'storeEvent'])->name('calendar.events.store');
        Route::put('calendar/events/{event}', [CalendarController::class, 'updateEvent'])->name('calendar.events.update');
        Route::delete('calendar/events/{event}', [CalendarController::class, 'destroyEvent'])->name('calendar.events.destroy');
        Route::post('calendar/focus-block/{task}', [CalendarController::class, 'focusBlock'])->name('calendar.focus-block');
        Route::post('calendar/import-weak', [CalendarController::class, 'importWeakToInbox'])->name('calendar.import-weak');

        Route::get('settings/ai', [AiController::class, 'index'])->name('ai.index');
        Route::put('settings/ai', [AiController::class, 'update'])->name('ai.update');
        Route::post('settings/ai/test', [AiController::class, 'test'])->name('ai.test');
        Route::delete('settings/ai', [AiController::class, 'destroy'])->name('ai.destroy');

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });

Route::get('app/focusmatrix/calendar/ics/{token}', [CalendarController::class, 'feed'])
    ->name('focusmatrix.calendar.ics');

Route::get('app/focusmatrix/assigned/{token}/accept', [DelegationController::class, 'acceptByToken'])
    ->name('focusmatrix.delegations.accept.token');
Route::get('app/focusmatrix/assigned/{token}/decline', [DelegationController::class, 'declineByToken'])
    ->name('focusmatrix.delegations.decline.token');
