<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoxController;
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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user(); // test
    });

    Route::get('/info', [BoxController::class, 'getInfo']);
    Route::get('/schedule', [BoxController::class, 'getSchedule']);
    Route::get('/download-media', [BoxController::class, 'downloadMedia']);
    Route::post('/log-media', [BoxController::class, 'updateLogMedia']);
    Route::get('/server-time', [BoxController::class, 'getServerTime']);
    Route::post('/check-status', [BoxController::class, 'checkStatus']);
    Route::get('/download-media/{id}', [BoxController::class, 'downloadMedia']);
});
