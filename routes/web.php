<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\VoteController;

Route::get('/', [GameController::class, 'index']);
Route::post('/games', [GameController::class, 'store']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::post('/games/{game}/join', [GameController::class, 'join']);
Route::post('/statements', [StatementController::class, 'store']);
Route::post('/votes', [VoteController::class, 'store']);
Route::get('/games/{game}/results', [GameController::class, 'results']);