<?php

use Illuminate\Support\Facades\Route;
use Modules\FocusMatrix\Http\Controllers\FocusMatrixController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('focusmatrices', FocusMatrixController::class)->names('focusmatrix');
});
