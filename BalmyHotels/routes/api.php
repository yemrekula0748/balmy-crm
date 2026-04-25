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
use App\Http\Controllers\Api\BrowserHistoryController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\AgentScreenshotController;
use App\Http\Controllers\Api\VncController;
use App\Http\Controllers\Api\DeletionController;
use App\Http\Controllers\Api\AgentAutoScreenshotController;

Route::middleware(['agent.key', 'throttle:60,1'])->group(function () {
    Route::post('/agent/report',           [AgentReportController::class,         'store']);
    Route::post('/agent/file-events',      [FileEventController::class,           'store']);
    Route::post('/agent/browser-history',  [BrowserHistoryController::class,      'store']);
    Route::post('/agent/screenshot',       [AgentScreenshotController::class,     'store']);
    Route::post('/agent/deletions',        [DeletionController::class,            'store']);
    Route::post('/agent/auto-screenshot',  [AgentAutoScreenshotController::class, 'store']);
});

Route::middleware(['agent.key'])->group(function () {
    Route::get('/agent/commands/pending', [CommandController::class, 'pending']);
    Route::post('/agent/commands/result', [CommandController::class, 'result']);
    // VNC
    Route::post('/agent/vnc/frame',  [VncController::class, 'storeFrame']);
    Route::get('/agent/vnc/input',   [VncController::class, 'getInput']);
});
