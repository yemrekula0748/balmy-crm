<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| IT Envanter Agent
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\AgentReportController;
use App\Http\Controllers\Api\FileEventController;

Route::middleware(['agent.key', 'throttle:60,1'])->group(function () {
    Route::post('/agent/report',       [AgentReportController::class, 'store']);
    Route::post('/agent/file-events',  [FileEventController::class,   'store']);
});
