<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use App\Models\Sport;
use Illuminate\Support\Facades\Route;

// ── Public routes ────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
Route::get('/api/schedule', [ScheduleController::class, 'api'])->name('schedule.api');
// Route::get('/results', [ResultController::class, 'index'])->name('results');
Route::get('/scores', [SportController::class, 'index'])->name('scores.index');
Route::get('/scores/{sport}', [SportController::class, 'show'])->name('scores.show');
Route::get('/scores/{sport}/{category}/match-{match}', [GameController::class, 'showByContext'])->name('games.show');

// Redirect old standings URLs to the sport page with category filter
Route::get('/scores/{sport}/{category}', function (Sport $sport, Category $category) {
    return redirect()->route('scores.show', ['sport' => $sport, 'category' => $category->slug]);
})->name('standings.show');

// ── Public live data API (no auth, for spectator polling) ────────────────────
Route::get('/api/games/{game}/live', [GameController::class, 'liveData'])->name('api.games.live');
Route::get('/api/games/batch', [GameController::class, 'batchLiveData'])->name('api.games.batch');

// ── Auth routes (hidden — no public links) ───────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Authenticated routes (overseers + admin) ─────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/games/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
    Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
    Route::patch('/games/{game}/live', [GameController::class, 'liveUpdate'])->name('games.live-update');
    Route::post('/games/{game}/reset', [GameController::class, 'reset'])->name('games.reset');

    // ── Admin-only routes ────────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});
