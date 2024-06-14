<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);




Route::group(['middleware' => ['auth:api', 'throttle:only_three_time']], function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('profile', [SiteController::class, 'profile']);
    Route::get('send-mail', [SiteController::class, 'sendMail']);
});
