<?php

use Illuminate\Support\Facades\Route;
use Modules\SmartKpi\Http\Controllers\ActionController;
use Modules\SmartKpi\Http\Controllers\CompanyController;
use Modules\SmartKpi\Http\Controllers\DashboardController;
use Modules\SmartKpi\Http\Controllers\DepartmentController;
use Modules\SmartKpi\Http\Controllers\EmployeeController;
use Modules\SmartKpi\Http\Controllers\ForecastController;
use Modules\SmartKpi\Http\Controllers\GoalController;
use Modules\SmartKpi\Http\Controllers\KpiDefinitionController;
use Modules\SmartKpi\Http\Controllers\KpiRelationshipController;
use Modules\SmartKpi\Http\Controllers\KpiValueController;
use Modules\SmartKpi\Http\Controllers\ProblemController;
use Modules\SmartKpi\Http\Controllers\ReportController;
use Modules\SmartKpi\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:smart-kpi', EnsureCurrentTeam::class])
    ->prefix('app/smartkpi')
    ->name('smartkpi.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('companies', CompanyController::class);
        Route::resource('companies.departments', DepartmentController::class)->only(['create', 'store']);
        Route::resource('departments', DepartmentController::class)->except(['create', 'store']);
        Route::resource('departments.employees', EmployeeController::class)->only(['create', 'store']);
        Route::resource('employees', EmployeeController::class)->except(['create', 'store']);

        Route::resource('kpi-definitions', KpiDefinitionController::class);
        Route::post('kpi-definitions/{kpi_definition}/duplicate', [KpiDefinitionController::class, 'duplicate'])->name('kpi-definitions.duplicate');
        Route::resource('kpi-definitions.kpi-values', KpiValueController::class)->only(['create', 'store']);
        Route::resource('kpi-values', KpiValueController::class)->except(['create', 'store']);

        Route::resource('problems', ProblemController::class);
        Route::resource('problems.actions', ActionController::class)->only(['create', 'store']);
        Route::resource('actions', ActionController::class)->except(['create', 'store']);

        Route::resource('relationships', KpiRelationshipController::class);
        Route::resource('goals', GoalController::class);
        Route::post('kpi-definitions/{kpi_definition}/forecasts', [ForecastController::class, 'store'])->name('kpi-definitions.forecasts.store');
        Route::get('forecasts/{forecast}', [ForecastController::class, 'show'])->name('forecasts.show');

        Route::get('reports/kpi-values', [ReportController::class, 'kpiValuesCsv'])->name('reports.kpi-values');
        Route::get('reports/problems', [ReportController::class, 'problemsCsv'])->name('reports.problems');
    });
