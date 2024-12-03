<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::get('/', [ProfileController::class, 'getProfile']);
Route::get('/list', [ProfileController::class, 'getProfileList']);
Route::post('/edit', [ProfileController::class, 'updateProfile']);
