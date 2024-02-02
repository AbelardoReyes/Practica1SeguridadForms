<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailAndPhoneController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\UserController;

Route::controller(AuthController::class)->prefix('/auth')->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register')->name('register');
    Route::get('/logout', 'logout')->middleware('auth')->name('logout');
})->middleware('throttle:6,1');

Route::controller(AuthController::class)->prefix('/auth')->group(function () {
    Route::post('/activeUser', 'activeUser')->name('activeUser');
});


Route::controller(VerifyEmailAndPhoneController::class)->prefix('/verifyEmailAndPhone')->group(function () {
    Route::get('/verifyEmail', 'verifyEmail')->name('verifyEmail')->middleware('signed');
    Route::post('/sendCodeVerifyEmailAndPhone', 'sendCodeVerifyEmailAndPhone')->name('sendCodeVerifyEmailAndPhone')->middleware('signed');
})->middleware('throttle:6,1');

Route::controller(TwoFactorAuthController::class)->prefix('/twoFactorAuth')->group(function () {
    Route::get('/twoFactorAuth', 'twoFactorAuth')->name('twoFactorAuth')->middleware('signed');
    Route::post('/verifyTwoFactorAuth', 'verifyTwoFactorAuth')->name('verifyTwoFactorAuth')->middleware('signed');
})->middleware('throttle:6,1');

// Route::controller(UserController::class)->prefix('/admin')->group(function () {
//     Route::get('/users', 'show')->name('users')->middleware('auth');
// })->middleware('throttle:6,1');
