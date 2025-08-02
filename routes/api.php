<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockedController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\FriendshipsController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test-google-client', [GoogleController::class, 'testClientId']);
Route::post('/auth/google', [GoogleController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    // === AUTH & PROFILE ===
    Route::get('/user/profile', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });

    // === USER MANAGEMENT ===
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/search', [UserController::class, 'search']);
        Route::get('/{id}', [UserController::class, 'show']);

        // Admin routes
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/{id}/block', [UserController::class, 'block']);
        Route::post('/{id}/unblock', [UserController::class, 'unblock']);
    });

    // === FRIENDSHIP MANAGEMENT ===
    Route::prefix('friends')->group(function () {
        Route::get('/', [FriendshipsController::class, 'index']);
        Route::post('/add', [FriendshipsController::class, 'store']);
        Route::get('/requests', [FriendshipsController::class, 'getFriendRequests']);
        Route::post('/accept', [FriendshipsController::class, 'accept']);
        Route::post('/reject', [FriendshipsController::class, 'reject']);
        Route::post('/remove', [FriendshipsController::class, 'destroy']);
        Route::post('/cancel', [FriendshipsController::class, 'cancelAddFriend']);
        Route::get('/search', [FriendshipsController::class, 'search']);
    });

    // === BLOCK MANAGEMENT ===
    Route::prefix('blocked')->group(function () {
        Route::get('/', [BlockedController::class, 'getBlockedUsers']);
        Route::post('/', [BlockedController::class, 'block']);
        Route::delete('/', [BlockedController::class, 'unblock']);
    });

    // === CONVERSATION MANAGEMENT ===
    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::get('/search', [ConversationController::class, 'search']);
    });

    // === MESSAGE MANAGEMENT ===
    Route::prefix('messages')->group(function () {
        Route::get('/conversation/{conversationId}', [MessageController::class, 'index']);
        Route::post('/', [MessageController::class, 'store']);
        Route::put('/', [MessageController::class, 'update']);
        Route::delete('/', [MessageController::class, 'destroy']);
        Route::post('/mark-as-read', [MessageController::class, 'markAsRead']);
    });
});
