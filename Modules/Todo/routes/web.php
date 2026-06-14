<?php

use Illuminate\Support\Facades\Route;
use Modules\Todo\Http\Controllers\TodoController;

Route::resource('todos', TodoController::class)->names('todo');
