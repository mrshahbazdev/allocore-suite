<?php

use Illuminate\Support\Facades\Route;
use Modules\KpiTool\Http\Controllers\KpiToolController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('kpitools', KpiToolController::class)->names('kpitool');
});
