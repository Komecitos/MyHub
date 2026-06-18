<?php

use Illuminate\Support\Facades\Route;
use Modules\FreeFire\Http\Controllers\FreeFireController;

Route::get('freefires/calc', [FreeFireController::class, 'calc'])->name('freefire.calc');
Route::get('freefires/session', [FreeFireController::class, 'session'])->name('freefire.session');
Route::post('freefires/session/store', [FreeFireController::class, 'storeSession'])->name('freefire.session.store');
Route::post('freefires/session/{id}/log', [FreeFireController::class, 'addLog'])->name('freefire.session.log');
Route::patch('freefires/session/{id}/complete', [FreeFireController::class, 'completeSession'])->name('freefire.session.complete');
Route::resource('freefires', FreeFireController::class)->names('freefire');