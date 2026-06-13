<?php

use Illuminate\Support\Facades\Route;
use Modules\Aquarium\Http\Controllers\AquariumController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('aquaria', AquariumController::class)->names('aquarium');
});
