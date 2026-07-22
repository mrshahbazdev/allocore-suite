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

        Route::resource('projects.tasks', TaskController::class)->shallow()->names([
            'index' => 'tasks.index', 'create' => 'tasks.create', 'store' => 'tasks.store',
        ]);
        Route::resource('projects.goals', GoalController::class)->shallow()->names([
            'index' => 'goals.index', 'create' => 'goals.create', 'store' => 'goals.store',
        ]);
        Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');
        Route::resource('projects.calendar-events', CalendarEventController::class)->shallow()->names([
            'index' => 'calendar-events.index', 'create' => 'calendar-events.create', 'store' => 'calendar-events.store',
        ]);
        Route::resource('projects.contacts', ContactController::class)->shallow()->names([
            'index' => 'contacts.index', 'create' => 'contacts.create', 'store' => 'contacts.store',
        ]);
        Route::resource('projects.notes', NoteController::class)->shallow()->names([
            'index' => 'notes.index', 'create' => 'notes.create', 'store' => 'notes.store',
        ]);
        Route::resource('projects.documents', DocumentController::class)->shallow()->names([
            'index' => 'documents.index', 'create' => 'documents.create', 'store' => 'documents.store',
        ]);
        Route::resource('projects.reminders', ReminderController::class)->shallow()->names([
            'index' => 'reminders.index', 'create' => 'reminders.create', 'store' => 'reminders.store',
        ]);
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
