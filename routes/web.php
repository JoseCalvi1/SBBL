<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\TournamentEngineController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. CRON Y UTILIDADES DE SISTEMA
|--------------------------------------------------------------------------
*/
Route::get('/cron/ejecutar-turno/conquista-modo-manual-SBBLS2', function (\Illuminate\Http\Request $request) {
    $expectedToken = (string) config('sbbl.cron_token');
    $providedToken = (string) ($request->header('X-Cron-Token') ?: $request->query('token', ''));

    if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
        abort(403, 'Acceso no autorizado.');
    }

    set_time_limit(0);
    try {
        Artisan::call('game:resolve');
        return '✅ TURNO EJECUTADO CORRECTAMENTE. Log: <br><pre>' . Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return '❌ ERROR: ' . $e->getMessage();
    }
});

Route::get('/limpiar-cache', function (\Illuminate\Http\Request $request) {
    $expectedToken = (string) config('sbbl.cron_token');
    $providedToken = (string) ($request->header('X-Cron-Token') ?: $request->query('token', ''));

    if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
        abort(403, 'Acceso no autorizado.');
    }

    Artisan::call('cache:clear');
    return '¡Caché borrada correctamente!';
});

/*
|--------------------------------------------------------------------------
| 2. API Y MOTOR DE TORNEOS COMPARTIDO (Accesible desde Web y Hub)
|--------------------------------------------------------------------------
*/
// Visor público del Cuadro Eliminatorio y Clasificación en directo
Route::get('/torneos/{eventId}/cuadro', [TournamentEngineController::class, 'showBracket'])->name('torneo.bracket');
Route::get('/api/torneos/{eventId}/clasificacion', [TournamentEngineController::class, 'getStandings']);
Route::get('/api/torneos/{eventId}/cuadro/html', [TournamentEngineController::class, 'renderBracketHtml']);
Route::get('/torneos/{eventId}/tv', [TournamentEngineController::class, 'showStadiumTv'])->name('torneo.tv');

// Endpoints protegidos compartidos entre el Hub y la Web Principal
Route::middleware('auth')->prefix('api')->group(function () {
    // Datos generales del Hub y Decks
    Route::get('/torneos', [HubController::class, 'getTorneos']);
    Route::get('/piezas', [HubController::class, 'getPiezas']);
    Route::get('/regiones', [HubController::class, 'getRegiones']);
    Route::get('/meta', [HubController::class, 'getMeta']);
    Route::get('/ultimo-deck', [HubController::class, 'getUltimoDeck']);
    Route::get('/ranking', [HubController::class, 'getRanking']);
    Route::get('/ranking-equipos', [HubController::class, 'getTeamRanking']);
    Route::get('/duelos', [HubController::class, 'getTeamDuels']);
    Route::post('/duelos', [HubController::class, 'storeTeamDuel']);
    Route::get('/perfil/{id}', [HubController::class, 'getPlayerProfile']);
    Route::get('/equipos', [HubController::class, 'getAllTeams']);
    Route::post('/lab/analizar', [HubController::class, 'analizarDeckLab']);

    // Inscripción, Decks y Arbitraje de Torneos
    Route::post('/torneos/{eventId}/deck', [HubController::class, 'guardarDeck']);
    Route::post('/torneos/{event}/paypal/order', [EventController::class, 'createEventPayPalOrder'])->name('api.events.paypal.order');
    Route::post('/torneos/{event}/paypal/capture', [EventController::class, 'captureEventPayPalOrder'])->name('api.events.paypal.capture');
    Route::get('/torneos/{id}/mi-deck', [TournamentEngineController::class, 'getMiDeck']);
    Route::delete('/torneos/{eventId}/asistencia', [HubController::class, 'cancelarAsistencia']);
    Route::post('/torneos/{eventId}/juez', [HubController::class, 'toggleJudge']);
    Route::post('/torneos/{eventId}/comenzar', [HubController::class, 'comenzarTorneo']);
    Route::get('/torneos/{eventId}/jugadores', [TournamentEngineController::class, 'getJugadoresTorneo']);
    Route::post('/torneos/{eventId}/jugador/add', [TournamentEngineController::class, 'addJugador']);
    Route::post('/torneos/{eventId}/jugador/{userId}/drop', [TournamentEngineController::class, 'dropJugador']);
    Route::post('/torneos/{eventId}/staff/add', [HubController::class, 'addEventHelper']);
    Route::delete('/torneos/{eventId}/staff/{userId}', [HubController::class, 'removeEventHelper']);

    // Motor de Arena en Vivo y Partidas
    Route::get('/torneos/{eventId}/arena', [TournamentEngineController::class, 'getArenaDashboard']);
    Route::post('/torneos/{eventId}/arena/ronda', [TournamentEngineController::class, 'generateRound']);
    Route::post('/torneos/{eventId}/arena/top', [TournamentEngineController::class, 'generateTop']);
    Route::post('/torneos/{eventId}/arena/realeatorizar', [TournamentEngineController::class, 'rerandomizeBracket']);
    Route::post('/torneos/{eventId}/arena/realeatorizar-suizo', [TournamentEngineController::class, 'rerandomizeSwiss']);
    Route::post('/torneos/{eventId}/arena/swap-grupos', [TournamentEngineController::class, 'swapSwissPlayers']);
    Route::post('/torneos/{eventId}/finalizar', [TournamentEngineController::class, 'finalizeTournament']);
    Route::post('/torneos/duelo-rapido', [TournamentEngineController::class, 'createFastDuel']);

    Route::get('/torneos/partida/{matchId}', [TournamentEngineController::class, 'getMatchDetails']);
    Route::post('/torneos/partida/{matchId}/batalla', [TournamentEngineController::class, 'storeBattle']);
    Route::delete('/torneos/partida/{id}/undo', [TournamentEngineController::class, 'deshacerUltimoRegistro']);
    Route::post('/torneos/partida/{id}/warning', [TournamentEngineController::class, 'asignarWarning']);
    Route::post('/torneos/partida/{matchId}/lock', [TournamentEngineController::class, 'lockMatch']);
    Route::post('/torneos/partida/{matchId}/unlock', [TournamentEngineController::class, 'unlockMatch']);
    Route::post('/torneos/partida/{matchId}/avanzar', [TournamentEngineController::class, 'avanzarPartidaBracket']);
    Route::post('/torneos/partida/{matchId}/victoria-rapida', [TournamentEngineController::class, 'fastWinMatch']);
    Route::post('/torneos/partida/{matchId}/marcador-manual', [TournamentEngineController::class, 'setManualScore']);

    // CRUD de Eventos desde el Hub
    Route::post('/eventos', [EventController::class, 'storeAPI']);
    Route::get('/eventos/{event}', [EventController::class, 'showAPI']);
    Route::put('/eventos/{event}', [EventController::class, 'updateAPI']);
    Route::delete('/eventos/{event}', [EventController::class, 'destroyAPI']);
});
