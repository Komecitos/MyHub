<?php

use Illuminate\Support\Facades\Route;
use Modules\FreeFire\Http\Controllers\FreeFireController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('freefires', FreeFireController::class)->names('freefire');
});
