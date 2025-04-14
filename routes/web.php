<?php

use App\Http\Controllers\BeybladeCollectionController;
use App\Http\Controllers\BeybladeDatabaseController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TeamsVersusController;
use App\Http\Controllers\TournamentResultController;
use App\Http\Controllers\TrophyController;
use App\Http\Controllers\VersusController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\InicioController::class, 'index'])->name('inicio.index');

Auth::routes();
Auth::routes(['verify' => true]);


Route::get('/home', [App\Http\Controllers\InicioController::class, 'index'])->name('inicio.index');
Route::get('/all-events', [App\Http\Controllers\InicioController::class, 'events'])->name('inicio.events');
Route::get('/random-combo-generator', [App\Http\Controllers\InicioController::class, 'combo'])->name('inicio.combo');
Route::get('/rules', [App\Http\Controllers\InicioController::class, 'rules'])->name('inicio.rules');
Route::get('/policy-privacy', [App\Http\Controllers\InicioController::class, 'privacy'])->name('inicio.privacy');
Route::get('/contact-us', [App\Http\Controllers\InicioController::class, 'contact'])->name('inicio.contact');
Route::get('/entrevistas', [App\Http\Controllers\InicioController::class, 'entrevistas'])->name('inicio.entrevistas');
Route::get('/beyblade-nacional-sbbl-temporada-uno', [App\Http\Controllers\InicioController::class, 'nacional'])->name('inicio.nacional');

Route::get('/events/create', [App\Http\Controllers\EventController::class, 'create'])->name('events.create');
Route::get('/events/{event}', [App\Http\Controllers\EventController::class, 'show'])->name('events.show');
Route::post('/events', [App\Http\Controllers\EventController::class, 'store'])->name('events.store');
Route::get('/events/{event}/edit', [App\Http\Controllers\EventController::class, 'edit'])->name('events.edit');
Route::put('/events/{event}', [App\Http\Controllers\EventController::class, 'update'])->name('events.update');
Route::delete('/events/{event}', [App\Http\Controllers\EventController::class, 'destroy'])->name('events.destroy');
Route::put('/events/{event}/{estado}/estado', [App\Http\Controllers\EventController::class, 'estadoTorneo'])->name('events.estado');
Route::put('/events/{event}/update-video', [EventController::class, 'updateVideo'])->name('events.updateVideo');

Route::post('/events/{event}', [App\Http\Controllers\EventController::class, 'assist'])->name('events.assist');
Route::delete('/assist/{event}', [App\Http\Controllers\EventController::class, 'noassist'])->name('events.noassist');
Route::put('/events/{event}/actualizar-status', [App\Http\Controllers\EventController::class, 'actualizarStatus'])->name('events.status');
Route::put('/events/{event}/updatePuestos', [App\Http\Controllers\EventController::class, 'updatePuestos'])->name('events.updatePuestos');
Route::put('/events/{event}/{mode}/actualizarPuntuaciones', [App\Http\Controllers\EventController::class, 'actualizarPuntuaciones'])->name('events.actualizarPuntuaciones');

Route::get('/versus/create', [App\Http\Controllers\VersusController::class, 'create'])->name('versus.create');
Route::get('/versus/{duel}', [App\Http\Controllers\VersusController::class, 'show'])->name('versus.show');
Route::post('/versus', [App\Http\Controllers\VersusController::class, 'store'])->name('versus.store');
Route::get('/versus/{duel}/edit', [App\Http\Controllers\VersusController::class, 'edit'])->name('versus.edit');
Route::put('/versus/{duel}', [App\Http\Controllers\VersusController::class, 'update'])->name('versus.update');
Route::get('/all-versus', [App\Http\Controllers\VersusController::class, 'show_all'])->name('versus.all');
Route::put('/versus/{duel}/{mode}/{winner}/puntuarDuelo', [App\Http\Controllers\VersusController::class, 'puntuarDuelo'])->name('versus.puntuarDuelo');
Route::get('/versus/{duel}/deck/{deck}', [App\Http\Controllers\VersusController::class, 'versusdeck'])->name('versus.versusdeck');
Route::put('/versusmass/puntuarDuelos', [App\Http\Controllers\VersusController::class, 'puntuarDuelos'])->name('puntuarDuelos');
Route::put('/versus/{duel}/invalidar', [App\Http\Controllers\VersusController::class, 'invalidar'])->name('versus.invalidar');
Route::put('/versus/{versus}/update-video', [VersusController::class, 'updateVideo'])->name('versus.updateVideo');

Route::get('/profiles', [App\Http\Controllers\ProfileController::class, 'index'])->name('profiles.index');
Route::get('/profiles/{profile}', [App\Http\Controllers\ProfileController::class, 'show'])->name('profiles.show');
Route::get('/profiles/{profile}/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profiles.edit');
Route::put('/profiles/{profile}', [App\Http\Controllers\ProfileController::class, 'update'])->name('profiles.update');
Route::put('/profiles-admin/{profile}', [App\Http\Controllers\ProfileController::class, 'updatePoints'])->name('profiles.updatePoints');
Route::put('/profiles-admin-x/{profile}', [App\Http\Controllers\ProfileController::class, 'updatePointsX'])->name('profiles.updatePointsX');
Route::put('/update-admin-x/update-all', [App\Http\Controllers\ProfileController::class, 'updateAllPointsX'])->name('profiles.updateAllPointsX');
Route::get('/wrapped/{profile}', [App\Http\Controllers\ProfileController::class, 'wrapped'])->name('profiles.wrapped');

Route::get('/rankings', [App\Http\Controllers\ProfileController::class, 'ranking'])->name('profiles.ranking');

Route::get('/challenges', [App\Http\Controllers\ChallengeController::class, 'index'])->name('challenges.index');
Route::post('/challenges', [App\Http\Controllers\ChallengeController::class, 'store'])->name('challenges.store');
Route::delete('/challenges/{challenges_profiles_id}', [App\Http\Controllers\ChallengeController::class, 'destroy'])->name('challenges.destroy');

Route::get('/generations', [App\Http\Controllers\VersusController::class, 'generation'])->name('generations.index');
Route::get('/gversus', [App\Http\Controllers\VersusController::class, 'versus'])->name('generations.versus');
Route::get('/generation/create', [App\Http\Controllers\VersusController::class, 'gcreate'])->name('generations.create');
Route::post('/generations', [App\Http\Controllers\VersusController::class, 'gstore'])->name('generations.gstore');
Route::get('/generations/{versus}/edit', [App\Http\Controllers\VersusController::class, 'gedit'])->name('generations.edit');
Route::put('/generations/{versus}', [App\Http\Controllers\VersusController::class, 'gupdate'])->name('generations.update');

Route::post('/videos', [App\Http\Controllers\VideoController::class, 'store'])->name('videos.store');

Route::get('/mercado', [App\Http\Controllers\ArticleController::class, 'index'])->name('mercado.index');
Route::get('/mercado/{custom_url}', [App\Http\Controllers\ArticleController::class, 'show'])->name('mercado.show');
Route::get('/article/create', [App\Http\Controllers\ArticleController::class, 'create'])->name('mercado.create');
Route::post('/article/store', [App\Http\Controllers\ArticleController::class, 'store'])->name('mercado.store');
Route::get('/mercado/{article}/edit', [App\Http\Controllers\ArticleController::class, 'edit'])->name('mercado.edit');
Route::put('/mercado/{article}', [App\Http\Controllers\ArticleController::class, 'update'])->name('mercado.update');

Route::get('/equipos-admin', [TeamController::class, 'indexAdmin'])->name('equipos.indexAdmin');
Route::get('/equipos', [TeamController::class, 'index'])->name('equipos.index');
Route::get('/equipos/crear', [TeamController::class, 'create'])->name('equipos.create');
Route::post('/equipos', [TeamController::class, 'store'])->name('equipos.store');
Route::get('/equipos/{equipo}', [TeamController::class, 'show'])->name('equipos.show');
Route::get('/equipos/{equipo}/editar', [TeamController::class, 'edit'])->name('equipos.edit');
Route::put('/equipos/{equipo}', [TeamController::class, 'update'])->name('equipos.update');
Route::delete('/equipos/{equipo}', [TeamController::class, 'destroy'])->name('equipos.destroy');
Route::post('equipos/{equipo}/add-member', [TeamController::class, 'addMember'])->name('equipos.addMember');
Route::delete('equipos/{equipo}/remove-member/{user}', [TeamController::class, 'removeMember'])->name('equipos.removeMember');
Route::patch('/equipos/{equipo}/change-captain/{miembro}', [TeamController::class, 'changeCaptain'])->name('equipos.changeCaptain');
Route::post('equipos/{equipo}/leave', [TeamController::class, 'leaveTeam'])->name('equipos.leave');
Route::post('equipos/{equipo}/send-invitation', [TeamController::class, 'sendInvitation'])->name('equipos.sendInvitation');
Route::post('invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
Route::post('invitations/{invitation}/reject', [InvitationController::class, 'reject'])->name('invitations.reject');
Route::post('/equipos/{equipo}/accept-invitation', [TeamController::class, 'acceptInvitation'])->name('equipos.acceptInvitation');
Route::get('/equipos-ranking', [App\Http\Controllers\TeamController::class, 'ranking_teams'])->name('equipos.ranking');

Route::get('/teams-versus/create', [TeamsVersusController::class, 'create'])->name('teams_versus.create');
Route::get('/teams-versus/{duel}', [TeamsVersusController::class, 'show'])->name('teams_versus.show');
Route::post('/teams-versus', [TeamsVersusController::class, 'store'])->name('teams_versus.store');
Route::get('/teams-versus/{duel}/edit', [TeamsVersusController::class, 'edit'])->name('teams_versus.edit');
Route::put('/teams-versus/{duel}', [TeamsVersusController::class, 'update'])->name('teams_versus.update');
Route::get('/all-teams-versus', [TeamsVersusController::class, 'show_all'])->name('teams_versus.all');
Route::put('/teams-versus/{duel}/{mode}/{winner}/puntuarDuelo', [TeamsVersusController::class, 'puntuarDuelo'])->name('teams_versus.puntuarDuelo');
Route::get('/teams-versus-admin', [TeamsVersusController::class, 'index'])->name('teams_versus.index');

Route::post('/event/{eventId}/results', [TournamentResultController::class, 'store'])->name('tournament.results.store');
Route::post('/versus/{versusId}/results', [TournamentResultController::class, 'storeduel'])->name('versus.results.store');
Route::get('/beyblade-stats', [TournamentResultController::class, 'beybladeStats'])->name('inicio.stats');
Route::get('/events/{event}/participant/results', [EventController::class, 'getParticipantResults'])->name('events.getParticipantResults');
Route::get('/stats', [TournamentResultController::class, 'beybladeStats'])->name('stats.index');
Route::get('/separate-stats', [TournamentResultController::class, 'separateStats'])->name('stats.separate');
Route::get('/rankingstats', [TournamentResultController::class, 'showRanking'])->name('stats.rankingstats');

Route::get('/chat/messages/{articleId}', [ChatController::class, 'getMessages']);
Route::post('/chat/messages', [ChatController::class, 'storeMessage'])->middleware('auth');

Route::resource('trophies', TrophyController::class); // Para los métodos index, create, store, etc.
Route::get('/trophy/assign', [TrophyController::class, 'assignForm'])->name('trophies.assignForm');
Route::post('/trophy/assign', [TrophyController::class, 'assign'])->name('trophies.assign');
Route::get('/users/trophies', [TrophyController::class, 'usersWithTrophies'])->name('trophies.usersWithTrophies');
Route::get('/trophies/user/{userId}', [TrophyController::class, 'showUserTrophies'])->name('trophies.userTrophies');
Route::delete('/trophies/removeAssignment/{assignmentId}', [TrophyController::class, 'removeAssignment'])->name('trophies.removeAssignment');
Route::put('/trophies/{userId}/updateCount/{trophyId}', [TrophyController::class, 'updateCount'])->name('trophies.updateCount');
Route::delete('/trophies/{userId}/remove/{trophyId}', [TrophyController::class, 'remove'])->name('trophies.remove');

Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.indexAdmin');
Route::get('/eventos', [InicioController::class, 'events'])->name('events.index');
Route::post('/eventos/fetch', [InicioController::class, 'fetchEvents'])->name('events.fetch');
Route::get('/versus', [App\Http\Controllers\VersusController::class, 'index'])->name('versus.index');
Route::get('/subscriptions', [InicioController::class, 'suscriptions'])->name('subscriptions');
Route::get('/admin', [InicioController::class, 'dashboard'])->name('admin.dashboard');

Route::get('/beyblade-database', [BeybladeDatabaseController::class, 'index'])->name('database.index');

// Rutas de partes admin (blades, ratchets, bits)
Route::get('partes', [BeybladeDatabaseController::class, 'indexPartes'])->name('database.indexPartes');
Route::get('beys', [BeybladeDatabaseController::class, 'indexBeys'])->name('database.indexBeys');
Route::get('partes/{type}', [BeybladeDatabaseController::class, 'indexPartes']);
Route::get('partes/{type}/{id}', [BeybladeDatabaseController::class, 'show']); // Para editar
Route::post('partes/{type}', [BeybladeDatabaseController::class, 'store']);
Route::put('partes/{type}/{id}', [BeybladeDatabaseController::class, 'update']);
Route::delete('partes/{type}/{id}', [BeybladeDatabaseController::class, 'destroy']);

// Rutas para blades, ratchets y bits
Route::get('beyblade-database/beyblades', [BeybladeDatabaseController::class, 'listBeyblades'])->name('database.beyblades');
Route::get('beyblade-database/parts', [BeybladeDatabaseController::class, 'listParts'])->name('database.parts');

Route::post('beyblades', [BeybladeDatabaseController::class, 'storeBey'])->name('beyblades.store');
Route::get('/beyblades/create', [BeybladeDatabaseController::class, 'createBey'])->name('beyblades.create');
Route::get('/beyblades/{id}/edit', [BeybladeDatabaseController::class, 'editBey'])->name('beyblades.edit');
Route::put('/beyblades/{id}', [BeybladeDatabaseController::class, 'updateBey'])->name('beyblades.update');
Route::get('beyblade-database/beyblades/{id}', [BeybladeDatabaseController::class, 'showBey'])->name('database.showBey');

Route::post('blades', [BeybladeDatabaseController::class, 'store'])->name('blades.store');
Route::get('/blades/{id}/edit', [BeybladeDatabaseController::class, 'editBlade'])->name('blades.edit');
Route::put('/blades/{id}', [BeybladeDatabaseController::class, 'update'])->name('blades.update');
Route::get('beyblade-database/blades/{id}', [BeybladeDatabaseController::class, 'showBlade'])->name('database.showBlade');

Route::post('ratchets', [BeybladeDatabaseController::class, 'store'])->name('ratchets.store');
Route::get('/ratchets/{id}/edit', [BeybladeDatabaseController::class, 'editRatchet'])->name('ratchets.edit');
Route::put('/ratchets/{id}', [BeybladeDatabaseController::class, 'updateRatchet'])->name('ratchets.update');
Route::get('beyblade-database/ratchets/{id}', [BeybladeDatabaseController::class, 'showRatchet'])->name('database.showRatchet');

Route::post('bits', [BeybladeDatabaseController::class, 'store'])->name('bits.store');
Route::get('/bits/{id}/edit', [BeybladeDatabaseController::class, 'editBit'])->name('bits.edit');
Route::put('/bits/{id}', [BeybladeDatabaseController::class, 'updateBit'])->name('bits.update');
Route::get('beyblade-database/bits/{id}', [BeybladeDatabaseController::class, 'showBit'])->name('database.showBit');

Route::get('/beyblade-database/collection', [BeybladeCollectionController::class, 'index'])->name('collection.index');
Route::post('/beyblade-database/collection/store', [BeybladeCollectionController::class, 'store'])->name('collection.store');
Route::put('/collection/{id}', [BeybladeCollectionController::class, 'update'])->name('collection.update');
Route::delete('/collection/{id}', [BeybladeCollectionController::class, 'destroy'])->name('collection.destroy');

Route::group(['middleware' => 'admin'], function () {
   Route::get('/profiles-admin', [App\Http\Controllers\ProfileController::class, 'indexAdmin'])->name('profiles.indexAdmin');
   Route::get('/profiles-admin-x', [App\Http\Controllers\ProfileController::class, 'indexAdminX'])->name('profiles.indexAdminX');
});
