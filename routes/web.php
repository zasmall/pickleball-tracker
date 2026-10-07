<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('players', PlayerController::class)->except(['create', 'edit']);
    Route::resource('games', GameController::class)->except(['show']);
});

require __DIR__.'/settings.php';
