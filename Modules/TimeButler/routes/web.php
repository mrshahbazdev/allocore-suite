<?php

use Illuminate\Support\Facades\Route;
use Modules\TimeButler\Http\Controllers\AbsenceController;
use Modules\TimeButler\Http\Controllers\AbsenceTypeController;
use Modules\TimeButler\Http\Controllers\DashboardController;
use Modules\TimeButler\Http\Controllers\HolidayController;
use Modules\TimeButler\Http\Controllers\ReportController;
use Modules\TimeButler\Http\Controllers\TeamCalendarController;
use Modules\TimeButler\Http\Controllers\TimeTrackingController;
use Modules\TimeButler\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:time-butler', EnsureCurrentTeam::class])
    ->prefix('app/timebutler')
    ->name('timebutler.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/absences', [AbsenceController::class, 'index'])->name('absences.index');
        Route::get('/absences/create', [AbsenceController::class, 'create'])->name('absences.create');
        Route::post('/absences', [AbsenceController::class, 'store'])->name('absences.store');
        Route::get('/absences/{absence}', [AbsenceController::class, 'show'])->name('absences.show');
        Route::post('/absences/{absence}/approve', [AbsenceController::class, 'approve'])->name('absences.approve');
        Route::post('/absences/{absence}/reject', [AbsenceController::class, 'reject'])->name('absences.reject');
        Route::post('/absences/{absence}/cancel', [AbsenceController::class, 'cancel'])->name('absences.cancel');
        Route::delete('/absences/{absence}', [AbsenceController::class, 'destroy'])->name('absences.destroy');

        Route::get('/absence-types', [AbsenceTypeController::class, 'index'])->name('absence-types.index');
        Route::post('/absence-types', [AbsenceTypeController::class, 'store'])->name('absence-types.store');
        Route::post('/absence-types/{absenceType}', [AbsenceTypeController::class, 'update'])->name('absence-types.update');
        Route::delete('/absence-types/{absenceType}', [AbsenceTypeController::class, 'destroy'])->name('absence-types.destroy');

        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays/import', [HolidayController::class, 'import'])->name('holidays.import');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

        Route::get('/calendar', [TeamCalendarController::class, 'index'])->name('calendar.index');

        Route::get('/time-tracking', [TimeTrackingController::class, 'index'])->name('time-tracking.index');
        Route::post('/time-tracking/clock-in', [TimeTrackingController::class, 'clockIn'])->name('time-tracking.clock-in');
        Route::post('/time-tracking/clock-out', [TimeTrackingController::class, 'clockOut'])->name('time-tracking.clock-out');
        Route::post('/time-tracking', [TimeTrackingController::class, 'store'])->name('time-tracking.store');
        Route::delete('/time-tracking/{timeEntry}', [TimeTrackingController::class, 'destroy'])->name('time-tracking.destroy');

        Route::get('/reports/absences', [ReportController::class, 'absence'])->name('reports.absences');
        Route::get('/reports/time', [ReportController::class, 'time'])->name('reports.time');
    });
