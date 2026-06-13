<?php

use Illuminate\Support\Facades\Route;
use Modules\FreeFire\Http\Controllers\FreeFireController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('freefires', FreeFireController::class)->names('freefire');
});
