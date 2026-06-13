<?php

use Illuminate\Support\Facades\Route;
use Modules\Todo\Http\Controllers\TodoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('todos', TodoController::class)->names('todo');
});
