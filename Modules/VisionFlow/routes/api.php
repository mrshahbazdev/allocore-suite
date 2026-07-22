<?php

use Illuminate\Support\Facades\Route;
use Modules\VisionFlow\Http\Controllers\VisionFlowController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('visionflows', VisionFlowController::class)->names('visionflow');
});
