<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Home')->name('home');
Route::inertia('/lobby', 'Lobby')->name('lobby');
Route::inertia('/leaderboard', 'Leaderboard')->name('leaderboard');
Route::inertia('/profile', 'Profile')->name('profile');

Route::get('/room/{code}', fn (string $code) => Inertia::render('Room', ['code' => $code]))->name('room');
Route::get('/game/{code}', fn (string $code) => Inertia::render('Game', ['code' => $code]))->name('game');
Route::get('/scoreboard/{code}', fn (string $code) => Inertia::render('ScoreBoard', ['code' => $code]))->name('scoreboard');
