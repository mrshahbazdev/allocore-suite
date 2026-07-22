<?php

use Illuminate\Support\Facades\Route;
use Modules\CashCore\Http\Controllers\CashCoreController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('cashcores', CashCoreController::class)->names('cashcore');
});
