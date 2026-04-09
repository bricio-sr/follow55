<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

// ── Autenticação ──────────────────────────────────────────────
Route::post('/login',  [AuthController::class, 'login']);

Route::middleware('auth:sanctum')
    ->post('/logout', [AuthController::class, 'logout']);

// ── Filmes públicos ───────────────────────────────────────────
Route::get('/movies',       [MovieController::class, 'index']);
Route::get('/movies/{movie}', [MovieController::class, 'show']);

// ── Filmes protegidos ─────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/movies',            [MovieController::class, 'store']);
    Route::put('/movies/{movie}',     [MovieController::class, 'update']);
    Route::delete('/movies/{movie}',  [MovieController::class, 'destroy']);
});
