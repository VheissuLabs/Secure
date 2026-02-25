<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MessageController::class, 'index'])->name('home');
Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
Route::post('/messages/show', [MessageController::class, 'show'])->name('messages.show');
