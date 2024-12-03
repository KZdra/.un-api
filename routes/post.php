<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'getPosts']);
Route::post('/', [PostController::class, 'addPost']);
Route::delete('/{id}', [PostController::class, 'deletePost']);


