<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Middleware\AuthReq;
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

// Auth routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('activate/account', [AuthController::class, 'activateAccount']);
    Route::post('forgot/password', [AuthController::class, 'forgotPassword']);
    Route::post('reset/password', [AuthController::class, 'resetPassword']);
});

// Authenticated routes
Route::group(['middleware' => AuthReq::class], function () {

    // Profile routes
    Route::group(['prefix' => 'profile'], function () {
        Route::get('me', [ProfileController::class, 'index']);
        Route::put('update', [ProfileController::class, 'update']);
        Route::put('change/password', [ProfileController::class, 'change_password']);
    });

    // Media routes
    Route::group(['prefix' => 'media'], function () {
        Route::post('upload', [MediaController::class, 'upload']);
    });

    // Room routes
    Route::resource('rooms', RoomController::class);

});

