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

Route::middleware(['auth:sanctum', 'active.user'])->get('/user', function (Request $request) {
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
use App\Http\Controllers\Api\Mobile\MobileAuthController;
use App\Http\Controllers\Api\Mobile\MobileDoorLogController;
use App\Http\Controllers\Api\Mobile\MobileGuestLogController;
use App\Http\Controllers\Api\Mobile\MobileOrderController;
use App\Http\Controllers\Api\Mobile\MobileRoleController;
use App\Http\Controllers\Api\Mobile\MobileUserController;

Route::prefix('auth')->group(function () {
    Route::post('login', [MobileAuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        Route::get('me', [MobileAuthController::class, 'me']);
        Route::post('logout', [MobileAuthController::class, 'logout']);
    });
});

Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
    Route::apiResource('users', MobileUserController::class);
    Route::get('door-logs', [MobileDoorLogController::class, 'index']);
    Route::get('guest-logs', [MobileGuestLogController::class, 'index']);
    Route::post('guest-logs', [MobileGuestLogController::class, 'store']);
    Route::put('guest-logs/{id}', [MobileGuestLogController::class, 'checkOut']);
    Route::get('roles', [MobileRoleController::class, 'index']);
    Route::post('roles', [MobileRoleController::class, 'store']);
    Route::delete('roles/{name}', [MobileRoleController::class, 'destroy']);
    Route::get('role-permissions', [MobileRoleController::class, 'permissions']);
    Route::put('role-permissions', [MobileRoleController::class, 'updatePermission']);
    Route::get('orders', [MobileOrderController::class, 'index']);
    Route::post('orders', [MobileOrderController::class, 'store']);
    Route::put('orders/{id}', [MobileOrderController::class, 'updateStatus']);
    Route::get('restaurants', [MobileOrderController::class, 'restaurants']);
});

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
