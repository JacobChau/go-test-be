<?php

use App\Enums\UserRole;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\PassageController;
use App\Http\Controllers\Question\QuestionCategoryController;
use App\Http\Controllers\Question\QuestionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UploadController;
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

// Group all routes that don't require specific role middleware
Route::middleware('api')->group(function () {
    // AUTHENTICATION ROUTES
    Route::prefix('auth')->name('auth.')->group(function () {
        // AuthController routes
        Route::controller(AuthController::class)->group(function () {
            Route::post('/login', 'login')->name('login');
            Route::post('/register', 'register')->name('register');
            Route::post('/google', 'loginWithGoogle')->name('google');
            Route::post('/refresh', 'refresh')->name('refresh');
        });

        // PasswordController routes
        Route::controller(PasswordController::class)->group(function () {
            Route::post('/forgot-password', 'forgot')->name('forgot');
            Route::post('/reset-password', 'reset')->name('reset');
            Route::put('/change-password', 'change')->name('change');
        });

    });
    // VerificationController routes
    Route::prefix('auth')->name('verification.')->group(function () {
        Route::controller(VerificationController::class)->group(function () {
            Route::get('/email/verify/{id}/{hash}', 'verify')->name('verify');
            Route::post('/email/resend', 'resend')->name('resend');
        });
    });
});

// Group all routes that require specific role middleware
Route::middleware(['api', 'role:'.UserRole::Admin.'|'.UserRole::Teacher])->group(function () {
    // USER ROUTES
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    // SUBJECT ROUTES
    Route::prefix('subjects')->name('subjects.')->controller(SubjectController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{subject}', 'update')->name('update');
        Route::delete('/{subject}', 'destroy')->name('destroy');
    });

    // PASSAGE ROUTES
    Route::prefix('passages')->name('passages.')->controller(PassageController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{passage}', 'update')->name('update');
        Route::delete('/{passage}', 'destroy')->name('destroy');
    });

    // QUESTION ROUTES
    Route::prefix('questions')->name('questions.')->controller(QuestionController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{question}', 'update')->name('update');
        Route::delete('/{question}', 'destroy')->name('destroy');
    });

    // QUESTION CATEGORY ROUTES
    Route::prefix('categories')->name('categories.')->controller(QuestionCategoryController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
    });

    // UPLOAD ROUTES
    Route::prefix('upload')->name('upload.')->controller(UploadController::class)->group(function () {
        Route::post('/', 'upload')->name('upload');
    });

    // ASSESSMENT ROUTES
    Route::prefix('assessments')->name('assessments.')->controller(AssessmentController::class)->group(function () {
        Route::get('/management', 'management')->name('management');
        Route::put('/{assessment}/results/{attempt}/publish', 'publishResult')->name('publishResult');
        Route::put('/{assessment}/results/{attempt}/answers/{id}', 'updateAnswerAttempt')->name('updateAnswerAttempt');
        Route::get('/{assessment}/results', 'getResultsByAssessment')->name('getResultsByAssessment');
        Route::post('/', 'store')->name('store');
        Route::put('/{assessment}', 'update')->name('update');
        Route::delete('/{assessment}', 'destroy')->name('destroy');
    });

    // GROUP ROUTES
    Route::prefix('groups')->name('groups.')->controller(GroupController::class)->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{group}', 'update')->name('update');
        Route::delete('/{group}', 'destroy')->name('destroy');
    });
});

Route::middleware(['api', 'auth'])->group(function () {
    // AUTHENTICATION ROUTES
    Route::prefix('auth')->name('auth.')->group(function () {
        // AuthController routes
        Route::controller(AuthController::class)->group(function () {
            Route::get('/me', 'me')->name('me');
            Route::post('/logout', 'logout')->name('logout');
        });
    });

    // USER ROUTES
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/groups/{group}/not-in-group', 'getNotInGroup')->name('getNotInGroup');
        Route::get('/me', 'me')->name('me');
        Route::get('/{user}', 'show')->name('show');
    });

    // SUBJECT ROUTES
    Route::prefix('subjects')->name('subjects.')->controller(SubjectController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{subject}', 'show')->name('show');
    });

    // PASSAGE ROUTES
    Route::prefix('passages')->name('passages.')->controller(PassageController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{passage}', 'show')->name('show');
    });

    // QUESTION ROUTES
    Route::prefix('questions')->name('questions.')->controller(QuestionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{question}', 'show')->name('show');
    });

    // QUESTION CATEGORY ROUTES
    Route::prefix('categories')->name('categories.')->controller(QuestionCategoryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{category}', 'show')->name('show');
    });

    // ASSESSMENT ROUTES
    Route::prefix('assessments')->name('assessments.')->controller(AssessmentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/results', 'results')->name('results');
        Route::get('/{assessment}/questions', 'questions')->name('questions');
        Route::post('/{assessment}/attempt', 'attempt')->name('attempt');
        Route::post('/{assessment}/submit', 'submit')->name('submit');
        Route::get('/{assessment}/results/{attempt}', 'resultDetail')->name('resultDetail');
        Route::get('/{assessment}', 'show')->name('show');
    });

    // GROUP ROUTES
    Route::prefix('groups')->name('groups.')->controller(GroupController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{group}/members', 'getMembers')->name('getMembers');
        Route::post('/{group}/members', 'addMembers')->name('addMembers');
        Route::delete('/{group}/members/{user}', 'removeMember')->name('removeMember');
        Route::get('/{group}', 'show')->name('show');
    });
});
