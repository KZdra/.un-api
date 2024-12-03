<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;

Route::get('/' ,[CommentController::class,'getComments']);
Route::post('/' ,[CommentController::class,'addComment']);
Route::delete('/{id}',[CommentController::class,'deleteComment']);
