<?php

use App\Http\Controllers\Api\SalaController;
use App\Http\Controllers\WaController;
use App\Http\Controllers\Webhooks\EvolutionWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'sala');

Route::get('/wa', [WaController::class, 'page']);
Route::get('/wa/qr.json', [WaController::class, 'qr']);

Route::post('/webhooks/evolution', EvolutionWebhookController::class);

Route::prefix('api')->group(function () {
    Route::get('/program', [SalaController::class, 'program']);
    Route::get('/programs/{program}/messages', [SalaController::class, 'messages']);
    Route::get('/programs/{program}/topics', [SalaController::class, 'topics']);
    Route::get('/programs/{program}/highlights', [SalaController::class, 'highlights']);
    Route::post('/messages/{message}/highlight', [SalaController::class, 'highlight']);
    Route::post('/messages/{message}/feedback', [SalaController::class, 'feedback']);
    Route::patch('/highlights/{highlight}', [SalaController::class, 'updateHighlight']);
});

Route::get('/media/{message}', [SalaController::class, 'media'])->name('media');
