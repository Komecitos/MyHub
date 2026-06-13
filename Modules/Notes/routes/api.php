<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('notes', NotesController::class)->names('notes');
});
