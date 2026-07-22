<?php

use Illuminate\Support\Facades\Route;
use Modules\PlanHive\Http\Controllers\PlanHiveController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('planhives', PlanHiveController::class)->names('planhive');
});
