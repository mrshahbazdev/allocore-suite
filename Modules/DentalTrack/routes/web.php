<?php

use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;
use Modules\DentalTrack\Http\Controllers\Admin\CompanyController;
use Modules\DentalTrack\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Modules\DentalTrack\Http\Controllers\Admin\EmployeePerformanceController;
use Modules\DentalTrack\Http\Controllers\Admin\LabController;
use Modules\DentalTrack\Http\Controllers\Admin\OrderController;
use Modules\DentalTrack\Http\Controllers\Admin\PredictionController;
use Modules\DentalTrack\Http\Controllers\Admin\ProcessTemplateController;
use Modules\DentalTrack\Http\Controllers\Admin\ProductTypeController;
use Modules\DentalTrack\Http\Controllers\Admin\QualityController;
use Modules\DentalTrack\Http\Controllers\Admin\ReportController;
use Modules\DentalTrack\Http\Controllers\Admin\ReworkEventController;
use Modules\DentalTrack\Http\Controllers\Admin\ScanEventController;
use Modules\DentalTrack\Http\Controllers\Admin\StationMonitoringController;
use Modules\DentalTrack\Http\Controllers\Admin\WorkstationController;
use Modules\DentalTrack\Http\Controllers\DashboardController;
use Modules\DentalTrack\Http\Controllers\ScanController;
use Modules\DentalTrack\Http\Controllers\TrackController;
use Modules\DentalTrack\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:dental-track', EnsureCurrentTeam::class])
    ->prefix('app/dentaltrack')
    ->name('dentaltrack.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('scan/{uuid?}', [ScanController::class, 'index'])->name('scan.index');
        Route::post('scan', [ScanController::class, 'process'])->name('scan.process');
    });

Route::get('app/dentaltrack/track', TrackController::class)->name('dentaltrack.track');

Route::middleware(['auth', 'verified', 'module:dental-track', EnsureCurrentTeam::class, EnsureAdmin::class])
    ->prefix('app/dentaltrack/admin')
    ->name('dentaltrack.admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('station-monitoring', [StationMonitoringController::class, 'index'])->name('station-monitoring.index');
        Route::get('employee-performance', [EmployeePerformanceController::class, 'index'])->name('employee-performance.index');
        Route::get('quality-control', [QualityController::class, 'index'])->name('quality.index');
        Route::get('predictions', [PredictionController::class, 'index'])->name('predictions.index');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export-orders', [ReportController::class, 'exportOrders'])->name('reports.export-orders');
        Route::get('reports/export-scans', [ReportController::class, 'exportScans'])->name('reports.export-scans');

        Route::resource('companies', CompanyController::class);
        Route::resource('labs', LabController::class);
        Route::resource('product-types', ProductTypeController::class);
        Route::resource('process-templates', ProcessTemplateController::class);
        Route::resource('workstations', WorkstationController::class);
        Route::get('workstations/{workstation}/sticker', [WorkstationController::class, 'sticker'])->name('workstations.sticker');

        Route::resource('orders', OrderController::class);
        Route::get('orders/{order}/sticker', [OrderController::class, 'sticker'])->name('orders.sticker');
        Route::post('orders/print-stickers', [OrderController::class, 'printStickers'])->name('orders.print-stick');

        Route::get('scan-events', [ScanEventController::class, 'index'])->name('scan-events.index');

        Route::get('rework-events', [ReworkEventController::class, 'index'])->name('rework-events.index');
        Route::get('rework-events/create', [ReworkEventController::class, 'create'])->name('rework-events.create');
        Route::post('rework-events', [ReworkEventController::class, 'store'])->name('rework-events.store');
        Route::post('rework-events/{reworkEvent}/resolve', [ReworkEventController::class, 'resolve'])->name('rework-events.resolve');
    });
