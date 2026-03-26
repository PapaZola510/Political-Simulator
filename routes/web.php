<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\PresidentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index'])->name('home');
Route::post('/game/advance', [GameController::class, 'advanceMonth'])->name('game.advance');
Route::post('/game/decision', [GameController::class, 'makeDecision'])->name('game.decision');
Route::post('/game/custom-decision', [GameController::class, 'makeCustomDecision'])->name('game.customDecision');
Route::post('/game/state-outlook', [GameController::class, 'goToStateOutlook'])->name('game.stateOutlook');
Route::post('/game/voter-reactions', [GameController::class, 'goToVoterReactions'])->name('game.voterReactions');
Route::match(['get', 'post'], '/game/dashboard', [GameController::class, 'returnToDashboard'])->name('game.dashboard');
Route::post('/game/reset', [GameController::class, 'reset'])->name('game.reset');
Route::post('/game/toggle-ai-content', [GameController::class, 'toggleAiContent'])->name('game.toggleAiContent');
Route::post('/game/set-scenario', [GameController::class, 'setTestScenario'])->name('game.setScenario');
Route::get('/game/scenarios', [GameController::class, 'getScenarios'])->name('game.getScenarios');
Route::post('/game/force-consequence', [GameController::class, 'forceConsequence'])->name('game.forceConsequence');
Route::post('/game/clear-data', [GameController::class, 'clearData'])->name('game.clearData');
Route::post('/game/save', [GameController::class, 'saveGame'])->name('game.save');
Route::get('/game/saves', [GameController::class, 'getSaves'])->name('game.getSaves');
Route::delete('/game/saves/{id}', [GameController::class, 'deleteSave'])->name('game.deleteSave');
Route::post('/game/load', [GameController::class, 'loadGame'])->name('game.load');

Route::get('/president', [PresidentController::class, 'index'])->name('president');
Route::post('/president/select', [PresidentController::class, 'select'])->name('president.select');
Route::post('/president/reset', [PresidentController::class, 'reset'])->name('president.reset');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
