<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);




Route::group(['middleware' => ['auth:api']], function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('send-mail', [SiteController::class, 'sendMail']);
    Route::get('profile', [SiteController::class, 'profile']);
    Route::post('update-profile', [SiteController::class, 'updateProfile']);
    Route::post('update-post/{id}', [PostController::class, 'updatePost']);
    Route::get('posts/public', [PostController::class, 'publicPosts']);
    Route::apiResource('posts', (PostController::class));
    Route::post('post/comment', [LikeCommentController::class, 'comment']);
    Route::get('like-unlike/{id}', [LikeCommentController::class, 'likeUnlike']);
});
