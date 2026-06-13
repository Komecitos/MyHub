<?php

use Illuminate\Support\Facades\Route;
use Modules\Aquarium\Http\Controllers\AquariumController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('aquaria', AquariumController::class)->names('aquarium');
});
