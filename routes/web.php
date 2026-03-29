<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index'])->name('game.index');
Route::get('/president/custom', [GameController::class, 'customPresident'])->name('game.custom_president');
Route::view('/login', 'auth.login')->name('login');
Route::post('/games', [GameController::class, 'store'])->name('game.store');
Route::post('/games/{game}/load', [GameController::class, 'load'])->name('game.load');
Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('game.destroy');
Route::get('/continue', [GameController::class, 'continue'])->name('game.continue');
Route::get('/games/{game}', [GameController::class, 'dashboard'])->name('game.dashboard');
Route::post('/games/{game}/save', [GameController::class, 'save'])->name('game.save');
Route::get('/games/{game}/situation', [GameController::class, 'situation'])->name('game.situation');
Route::post('/games/{game}/decision', [GameController::class, 'decide'])->name('game.decision');
Route::get('/games/{game}/news', [GameController::class, 'news'])->name('game.news');
Route::get('/games/{game}/state-outlook', [GameController::class, 'stateOutlook'])->name('game.state_outlook');
Route::get('/games/{game}/voter-reaction', [GameController::class, 'voterReaction'])->name('game.voter_reaction');
Route::get('/games/{game}/midterm', [GameController::class, 'midterm'])->name('game.midterm');
Route::get('/games/{game}/ended', [GameController::class, 'ended'])->name('game.ended');
Route::get('/games/{game}/score', [GameController::class, 'score'])->name('game.score');
