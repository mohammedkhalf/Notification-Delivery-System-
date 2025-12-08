<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationStatusController;
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

// Event ingestion
Route::post('/events', [EventController::class, 'store']);

// Notification status endpoints
Route::prefix('notifications')->group(function () {
    Route::get('/{id}', [NotificationStatusController::class, 'show']);
    Route::get('/event/{eventId}', [NotificationStatusController::class, 'byEvent']);
    Route::get('/failed', [NotificationStatusController::class, 'failed']);
    Route::get('/dead-letter', [NotificationStatusController::class, 'deadLetter']);
});
