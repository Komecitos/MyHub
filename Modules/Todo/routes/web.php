<?php

use Illuminate\Support\Facades\Route;
use Modules\Todo\Http\Controllers\TodoController;

Route::get('todos/history', [TodoController::class, 'history'])->name('todo.history');
Route::resource('todos', TodoController::class)->names('todo');