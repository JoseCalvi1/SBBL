<?php

use App\Http\Controllers\Game\ConquestController;
use App\Http\Controllers\Game\MarketController;
use Illuminate\Support\Facades\Route;

// Dominio base dinámico (compatible con entorno local y producción)
$dominio = env('APP_DOMAIN', 'sbbl.es');

/*
|--------------------------------------------------------------------------
| SUBDOMINIO: JUEGO DE CONQUISTA (conquista.sbbl.es)
|--------------------------------------------------------------------------
*/
Route::domain('conquista.' . $dominio)->group(function () {
    Route::get('/', [ConquestController::class, 'welcome'])->name('conquest.index');
    Route::get('/home', [ConquestController::class, 'welcome'])->name('conquest.index');
    Route::get('/mapa', [ConquestController::class, 'map'])->middleware('auth')->name('conquest.map');
    Route::post('/votar', [ConquestController::class, 'vote'])->name('conquest.vote');

    // Chat y Noticias de Conquista
    Route::get('/chat/fetch', [ConquestController::class, 'getChatMessages'])->name('chat.fetch');
    Route::post('/chat/send', [ConquestController::class, 'sendChatMessage'])->name('chat.send');
    Route::post('/chat/motd', [ConquestController::class, 'updatePinnedMessage'])->name('chat.update_motd');
    Route::get('/noticias', [ConquestController::class, 'news'])->name('conquest.news');
    Route::get('/generar-noticias', [ConquestController::class, 'generateTestNews']);

    // Facciones
    Route::post('/faction/choose', [ConquestController::class, 'choose'])->name('faction.choose');
    Route::get('/faction/stats', [ConquestController::class, 'stats'])->name('faction.stats');

    // Mercado Negro, Ruleta y Wordle
    Route::get('/market', [MarketController::class, 'index'])->name('market.index');
    Route::post('/market/buy', [MarketController::class, 'buy'])->name('market.buy');
    Route::post('/roulette/spin', [MarketController::class, 'spinRoulette'])->name('market.spin');
    Route::post('/market/wordle/store', [MarketController::class, 'storeWordle'])->name('market.wordle.store');
    Route::post('/market/wordle/win', [MarketController::class, 'winWordle'])->name('market.wordle.win');
    Route::delete('/market/wordle/{id}', [MarketController::class, 'deleteWordle'])->name('market.wordle.delete');
    Route::post('/market/use-radar', [MarketController::class, 'useRadar'])->name('market.use_radar');
    Route::post('/market/activate', [MarketController::class, 'activate'])->name('market.activate');

    // Administración de Conquista
    Route::post('/admin/force-turn', [ConquestController::class, 'forceResolve'])
        ->middleware('auth')
        ->name('admin.force_resolve');
});
