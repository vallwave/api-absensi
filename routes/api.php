<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route::apiResource('absensi', AbsenController::class)->middleware('auth:api');

Route::group(['prefix' => 'absensi', 'middleware' => 'auth:api'], function () {
    Route::post('/clock-in', [AbsenController::class, 'clockIn']);
    Route::post('/clock-in-wfh', [AbsenController::class, 'clockInWfh']);
    Route::post('/clock-out', [AbsenController::class, 'clockOut']);
    Route::post('/clock-out-wfh', [AbsenController::class, 'clockOutWfh']);
});
