<?php

use Illuminate\Support\Facades\Route;
use Modules\OrgMatrix\Http\Controllers\OrgMatrixController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('orgmatrices', OrgMatrixController::class)->names('orgmatrix');
});
