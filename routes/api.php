<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware'=>'api','prefix'=>'auth'],function($router){
    Route::post('/register', [AuthController::class,'register']);
    Route::post('/login', [AuthController::class,'login']);
});

Route::group(['prefix' => 'absensi', 'middleware' => 'api'], function () {
    Route::post('/clock-in', [AbsenController::class, 'clockIn']);
    Route::post('/clock-out', [AbsenController::class, 'clockOut']);
    Route::get('/get-absensi', [AbsenController::class, 'getAbsensi']);
});