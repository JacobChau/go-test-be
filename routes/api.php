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


/* AUTHENTICATION ROUTES */
Route::prefix('auth')->name('auth.')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::post('/register', 'register')->name('register');
        Route::post('/google', 'loginWithGoogle')->name('google');
    });

    Route::controller(PasswordController::class)->group(function () {
        Route::post('/forgot-password', 'forgot')->name('forgot');
        Route::post('/reset-password', 'reset')->name('reset');
    });

    Route::middleware(['api'])->group(function () {
        Route::controller(PasswordController::class)->group(function () {
            Route::put('/change-password', 'change')->name('change');
        });
        Route::controller(VerificationController::class)->group(function () {
            Route::get('/email/verify/{id}/{hash}', 'verify')->name('verify');
            Route::post('/email/resend', 'resend')->name('resend');
        });
    });
});

/* USER ROUTES */
Route::prefix('users')->name('users.')->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{user}', 'show')->name('show');
    });
});
Route::middleware(['api','role:'.UserRole::Admin])->group(function () {
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
