<?php

use App\Http\Controllers\Api\GameRoomController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PlayerAuthController;
use App\Http\Controllers\Api\PlayerProfileController;
use App\Http\Controllers\Api\RoundController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Middleware\AuthenticatePlayer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| BacAttack API Routes
|--------------------------------------------------------------------------
*/

// Public routes — no auth required
Route::group([], function (): void {
    Route::post('/auth/login', [PlayerAuthController::class, 'login']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/rooms', [GameRoomController::class, 'index']);
    Route::get('/rooms/{code}', [GameRoomController::class, 'show']);
});

// Authenticated routes
Route::middleware(AuthenticatePlayer::class)->group(function (): void {
    // Auth
    Route::get('/auth/me', [PlayerAuthController::class, 'me']);
    Route::post('/auth/logout', [PlayerAuthController::class, 'logout']);

    // Profile
    Route::get('/profile', [PlayerProfileController::class, 'show']);
    Route::get('/profile/history', [PlayerProfileController::class, 'gameHistory']);

    // Game rooms
    Route::post('/rooms', [GameRoomController::class, 'store']);
    Route::post('/rooms/{code}/join', [GameRoomController::class, 'join']);
    Route::post('/rooms/{code}/leave', [GameRoomController::class, 'leave']);
    Route::post('/rooms/{code}/start', [GameRoomController::class, 'start']);

    // Rounds
    Route::post('/rooms/{code}/submit', [RoundController::class, 'submit']);
    Route::post('/rooms/{code}/stop', [RoundController::class, 'stop']);

    // Voting
    Route::post('/rooms/{code}/vote', [VoteController::class, 'submit']);
    Route::post('/rooms/{code}/vote/force-close', [VoteController::class, 'forceClose']);
});
