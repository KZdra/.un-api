<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;
Route::post('/', [LikeController::class, 'addLike']);
Route::delete('/{id}', [LikeController::class, 'deleteLike']);