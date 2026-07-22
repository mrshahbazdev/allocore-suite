<?php

use Illuminate\Support\Facades\Route;
use Modules\DentalTrack\Http\Controllers\DentalTrackController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('dentaltracks', DentalTrackController::class)->names('dentaltrack');
});
