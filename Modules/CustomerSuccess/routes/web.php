<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerSuccess\Http\Controllers\DashboardController;
use Modules\CustomerSuccess\Http\Controllers\InquiryController;
use Modules\CustomerSuccess\Http\Middleware\EnsureCurrentTeam;

Route::middleware(['auth', 'verified', 'module:customer-success', EnsureCurrentTeam::class])
    ->prefix('app/customer-success')
    ->name('customersuccess.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('inquiries', InquiryController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    });
