<?php

use Illuminate\Support\Facades\Route;
use Modules\PlanHive\Http\Controllers\CalendarEventController;
use Modules\PlanHive\Http\Controllers\ContactController;
use Modules\PlanHive\Http\Controllers\DashboardController;
use Modules\PlanHive\Http\Controllers\DocumentController;
use Modules\PlanHive\Http\Controllers\GoalController;
use Modules\PlanHive\Http\Controllers\NoteController;
use Modules\PlanHive\Http\Controllers\ProjectController;
use Modules\PlanHive\Http\Controllers\ReminderController;
use Modules\PlanHive\Http\Controllers\ReportController;
use Modules\PlanHive\Http\Controllers\SearchController;
use Modules\PlanHive\Http\Controllers\TaskController;
use Modules\PlanHive\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:plan-hive', EnsureCurrentTeam::class])
    ->prefix('app/planhive')
    ->name('planhive.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        Route::resource('projects', ProjectController::class);
        Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');
        Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');

        Route::resource('projects.tasks', TaskController::class)->shallow();
        Route::resource('projects.goals', GoalController::class)->shallow();
        Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
        Route::resource('projects.calendar-events', CalendarEventController::class)->shallow();
        Route::resource('projects.contacts', ContactController::class)->shallow();
        Route::resource('projects.notes', NoteController::class)->shallow();
        Route::resource('projects.documents', DocumentController::class)->shallow();
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::resource('projects.reminders', ReminderController::class)->shallow();
        Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
