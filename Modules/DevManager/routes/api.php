<?php

use Illuminate\Support\Facades\Route;
use Modules\DevManager\Http\Controllers\DevManagerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('devmanagers', DevManagerController::class)->names('devmanager');
});
