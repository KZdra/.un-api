<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LikeController;

Route::post('/', [LikeController::class, 'addLike']);