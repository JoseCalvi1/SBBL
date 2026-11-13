<?php

use App\Http\Controllers\Api\FantasyController;
use Illuminate\Support\Facades\Route;

// Dominio base dinámico (compatible con entorno local y producción)
$dominio = env('APP_DOMAIN', 'sbbl.es');

/*
|--------------------------------------------------------------------------
| SUBDOMINIO: FANTASY (fantasy.sbbl.es)
|--------------------------------------------------------------------------
*/
Route::domain('fantasy.' . $dominio)->group(function () {
    Route::get('/', fn() => view('fantasy.app'))->name('fantasy.index');
    Route::get('/home', fn() => redirect('/'));

    Route::middleware('auth')->prefix('api')->group(function () {
        Route::get('/my-team', [FantasyController::class, 'getMyTeam']);
        Route::post('/save-lineup', [FantasyController::class, 'saveLineup']);
        Route::get('/market-players', [FantasyController::class, 'getMarketPlayers']);
        Route::post('/buy-player', [FantasyController::class, 'buyPlayer']);
        Route::post('/sell-player', [FantasyController::class, 'sellPlayer']);
        Route::post('/bulk-sell', [FantasyController::class, 'bulkSellPlayers']);
        Route::get('/fantasy-ranking', [FantasyController::class, 'getRanking']);
        Route::get('/fantasy-history', [FantasyController::class, 'getHistory']);
        Route::post('/open-pack', [FantasyController::class, 'openGachaPack']);
        Route::post('/claim-daily-reward', [FantasyController::class, 'claimDailyReward']);
        Route::post('/fuse-players', [FantasyController::class, 'fusePlayers']);
        Route::post('/open-item-pack', [FantasyController::class, 'openItemPack']);
        Route::post('/equip-item', [FantasyController::class, 'equipItem']);
    });
});
