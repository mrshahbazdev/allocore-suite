<?php

use Illuminate\Support\Facades\Route;
use Modules\TimeButler\Http\Controllers\TimeButlerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('timebutlers', TimeButlerController::class)->names('timebutler');
});
