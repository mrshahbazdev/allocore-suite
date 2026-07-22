<?php

use Illuminate\Support\Facades\Route;
use Modules\LoopEngine\Http\Controllers\LoopEngineController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('loopengines', LoopEngineController::class)->names('loopengine');
});
