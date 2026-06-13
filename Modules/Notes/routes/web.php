<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('notes', NotesController::class)->names('notes');
});
