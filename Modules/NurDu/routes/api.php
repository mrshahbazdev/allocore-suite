<?php

use Illuminate\Support\Facades\Route;
use Modules\NurDu\Http\Controllers\NurDuController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('nurdus', NurDuController::class)->names('nurdu');
});
