<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/',[PostController::class,'getPosts']);
Route::post('/', [PostController::class,'addPost']);

//Comments Sections
Route::get('/comments',[PostController::class,'getComments']);
Route::post('/comments',[PostController::class,'addComment']);