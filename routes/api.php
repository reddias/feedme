<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RecipeController;
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
Route::post('comments', [CommentController::class, 'create']);

Route::middleware(['auth', 'check.user.is_active'])->group(function () {
    // admin, user, guest
    Route::group(['prefix' => 'auth'], function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::group(['prefix' => 'users'], function () {
        // admin
        Route::middleware(['auth', 'check.user.is_admin'])->group(function () {
            Route::get('/{id}', [UserController::class, 'show']);
            Route::get('/', [UserController::class, 'index']);
            Route::patch('/{id}/updateStatus', [UserController::class, 'updateStatus']);
        });

        // user, admin
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/me', [UserController::class, 'updateMe']);
        Route::delete('/me', [UserController::class, 'destroyMe']);
        Route::post('/me/updatePhoto', [UserController::class, 'updatePhoto']);
        Route::delete('/me/deletePhoto', [UserController::class, 'deletePhoto']);
        Route::patch('/me/updatePassword', [UserController::class, 'changePassword']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // user, admin
    Route::group(['prefix' => 'recipes'], function () {
        Route::get('/mostPopular', [RecipeController::class, 'popularRecipes']);
        Route::post('/', [RecipeController::class, 'create']);
        Route::post('/{id}/clone', [RecipeController::class, 'cloneRecipe']);
        Route::put('/{id}', [RecipeController::class, 'update']);
        Route::post('/{id}/updatePhoto', [RecipeController::class, 'updatePhoto']);
        Route::delete('/{id}/deletePhoto', [RecipeController::class, 'deletePhoto']);
        Route::delete('/{id}', [RecipeController::class, 'destroy']);
    });

    // user, admin
    Route::group(['prefix' => 'categories'], function () {
        Route::post('/', [CategoryController::class, 'create']);
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware('check.user.is_admin');
    });

    // user
    Route::post('like', [LikeController::class, 'like']);

    // user, admin
    Route::group(['prefix' => 'comments'], function () {
        Route::get('/{id}', [CommentController::class, 'show']);
        Route::get('/', [CommentController::class, 'index']);
        Route::delete('/{id}', [CommentController::class, 'destroy']);
    });

});


// admin, user
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
});

// guest
Route::group(['prefix' => 'users'], function () {
    Route::post('/', [UserController::class, 'create']);
});

// admin, user, guest
Route::group(['prefix' => 'recipes'], function () {
    Route::get('/{id}', [RecipeController::class, 'show']);
    Route::get('/', [RecipeController::class, 'index']);
});

// admin, user, guest
Route::group(['prefix' => 'categories'], function () {
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/', [CategoryController::class, 'index']);
});
