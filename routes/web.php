<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\Admin\GameweekController;
use App\Http\Controllers\Admin\GameMatchController;
use App\Http\Controllers\Admin\TournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::resource('admin/gameweeks', GameweekController::class)->names('admin.gameweeks');
    Route::resource('admin/tournaments', TournamentController::class)->names('admin.tournaments');
    Route::post('admin/tournaments/{tournament}/sync-users', [TournamentController::class, 'syncAllUsers'])->name('admin.tournaments.sync-users');
    Route::get('admin/import', [App\Http\Controllers\Admin\ImportController::class, 'create'])->name('admin.import.create');
    Route::post('admin/import', [App\Http\Controllers\Admin\ImportController::class, 'store'])->name('admin.import.store');
    Route::post('admin/gameweeks/adjust-points', [GameweekController::class, 'adjustPoints'])->name('admin.gameweeks.adjust-points');
    Route::post('admin/gameweeks/{gameweek}/recalculate', [GameweekController::class, 'recalculateScores'])->name('admin.gameweeks.recalculate');
    Route::post('admin/gameweeks/{gameweek}/generate-punditry', [GameweekController::class, 'generatePunditry'])->name('admin.gameweeks.generate-punditry');

    // User Management
    Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users');
    Route::post('admin/users/{user}/reset-password', [\App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('admin/users/{user}/remove-passkeys', [\App\Http\Controllers\Admin\UserController::class, 'removePasskeys'])->name('admin.users.remove-passkeys');

    // Game Match Routes (Nested under Gameweeks for creation)
    Route::get('admin/gameweeks/{gameweek}/matches/create', [App\Http\Controllers\Admin\GameMatchController::class, 'create'])->name('admin.matches.create');
    Route::post('admin/gameweeks/{gameweek}/matches', [App\Http\Controllers\Admin\GameMatchController::class, 'store'])->name('admin.matches.store');
    Route::get('admin/matches/{match}/edit', [App\Http\Controllers\Admin\GameMatchController::class, 'edit'])->name('admin.matches.edit');
    Route::put('admin/matches/{match}', [App\Http\Controllers\Admin\GameMatchController::class, 'update'])->name('admin.matches.update');

    // Prediction Routes
    Route::get('/predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::post('/predictions', [PredictionController::class, 'store'])->name('predictions.store');
    Route::post('/predictions/{match}/cashout', [PredictionController::class, 'cashout'])->name('predictions.cashout');




    // Leaderboard
    Route::get('/leaderboard', [App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('/leaderboard/round/{gameweek}', [App\Http\Controllers\LeaderboardController::class, 'showRound'])->name('leaderboard.round');

    // Pundit's Corner
    Route::get('/pundit', [App\Http\Controllers\PunditController::class, 'index'])->name('pundit.index');
    Route::get('/pundit/{gameweek}', [App\Http\Controllers\PunditController::class, 'show'])->name('pundit.show');
    Route::get('/pundit/match/{match}', [App\Http\Controllers\PunditController::class, 'matchCommentary'])->name('pundit.match');

    // Passkey Management
    Route::get('/passkeys/register-options', [App\Http\Controllers\PasskeyController::class, 'registerOptions'])->name('passkeys.register_options');
    Route::post('/passkeys', [App\Http\Controllers\PasskeyController::class, 'store'])->name('passkeys.store');
    Route::delete('/passkeys/{passkey}', [App\Http\Controllers\PasskeyController::class, 'destroy'])->name('passkeys.destroy');
});

Route::passkeys();



require __DIR__ . '/auth.php';
