<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::middleware(['middleware' => 'jwt.verify'])->group(function () {
    Route::prefix('posts')->group(base_path('routes/post.php'));
    Route::prefix('profile')->group(base_path('routes/profile.php'));
    Route::prefix('comments')->group(base_path('routes/comment.php'));
    Route::prefix('like')->group(base_path('routes/like.php'));
});
