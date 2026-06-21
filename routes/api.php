<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoxController;
use App\Http\Controllers\Api\DeviceApiController;
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


Route::post('/devices/register', [DeviceApiController::class, 'register']);
Route::post('/devices/check-pairing', [DeviceApiController::class, 'checkPairing']);
Route::post('/devices/ping', [DeviceApiController::class, 'ping']);
Route::post('/register-device', [BoxController::class, 'registerDevice']);

Route::middleware('device.auth')->group(function () {
    Route::get('/info', [BoxController::class, 'getInfo']);
    Route::get('/schedule', [BoxController::class, 'getSchedule']);
    Route::get('/download-media', [BoxController::class, 'downloadMedia']);
    Route::post('/log-media', [BoxController::class, 'updateLogMedia']);
    Route::get('/server-time', [BoxController::class, 'getServerTime']);
    Route::post('/check-status', [BoxController::class, 'checkStatus']);
    Route::get('/download-media/{id}', [BoxController::class, 'downloadMedia']);
});
