<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController as APIAuthController;
use App\Http\Controllers\API\TaskAssignmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->controller(APIAuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', 'user');
        Route::get('/logout', 'logout');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('task')->controller(TaskAssignmentController::class)->group(function () {
        Route::get('/get', 'get');
        Route::put('/skip/{taskId}', 'skip')->whereNumber('taskId');
        Route::put('/solve/{taskId}', 'solve')->whereNumber('taskId');
    });
});
