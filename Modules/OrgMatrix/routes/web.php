<?php

use Illuminate\Support\Facades\Route;
use Modules\OrgMatrix\Http\Controllers\DashboardController;
use Modules\OrgMatrix\Http\Controllers\ImportExportController;
use Modules\OrgMatrix\Http\Controllers\OrganizationController;
use Modules\OrgMatrix\Http\Controllers\OrgChartController;
use Modules\OrgMatrix\Http\Controllers\PersonController;
use Modules\OrgMatrix\Http\Controllers\RoleAssignmentController;
use Modules\OrgMatrix\Http\Controllers\RoleController;
use Modules\OrgMatrix\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:org-matrix', EnsureCurrentTeam::class])
    ->prefix('app/orgmatrix')
    ->name('orgmatrix.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('organizations', OrganizationController::class);

        Route::prefix('organizations/{organization}')->name('organizations.')->group(function (): void {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::resource('people', PersonController::class)->except(['show']);

            Route::get('chart', [OrgChartController::class, 'index'])->name('chart');

            Route::get('roles/{role}/assign', [RoleAssignmentController::class, 'create'])->name('roles.assignments.create');
            Route::post('roles/{role}/assign', [RoleAssignmentController::class, 'store'])->name('roles.assignments.store');
            Route::delete('roles/{role}/assign/{assignment}', [RoleAssignmentController::class, 'destroy'])->name('roles.assignments.destroy');

            Route::get('export/roles', [ImportExportController::class, 'exportRoles'])->name('export.roles');
            Route::get('export/people', [ImportExportController::class, 'exportPeople'])->name('export.people');
            Route::post('import/roles', [ImportExportController::class, 'importRoles'])->name('import.roles');
            Route::post('import/people', [ImportExportController::class, 'importPeople'])->name('import.people');
        });
    });
