<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\UserController;
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

Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register')->name('register');
});

Route::controller(PasswordController::class)->prefix('auth')->name('auth.')->group(function () {
    Route::post('/forgot-password', 'forgot')->name('forgot');
    Route::post('/reset-password', 'reset')->name('reset');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(PasswordController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::put('/change-password', 'change')->name('change');
    });
    Route::controller(VerificationController::class)->prefix('verification')->name('verification.')->group(function () {
        Route::get('/email/verify/{id}/{hash}', 'verify')->name('verify');
        Route::post('/email/resend', 'resend')->name('resend');
    });
});

Route::middleware(['auth:sanctum','role:'.UserRole::Admin])->group(function () {
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});

Route::middleware(['auth:sanctum','verified'])->group(function () {
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/{user}', 'show')->name('show');
        Route::put('/{user}', 'update')->name('update');
    });
});
