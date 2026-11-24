<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\QualifierController;
use App\Http\Controllers\Admin\RaffleController;
use App\Http\Controllers\Admin\TreasuryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BeybladeCollectionController;
use App\Http\Controllers\BeybladeDatabaseController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamsVersusController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TournamentResultController;
use App\Http\Controllers\TrophyController;
use App\Http\Controllers\VersusController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

// Dominio base dinámico (compatible con entorno local y producción)
$dominio = env('APP_DOMAIN', 'sbbl.es');

/*
|--------------------------------------------------------------------------
| SUBDOMINIO: NACIONAL (nacional.sbbl.es)
|--------------------------------------------------------------------------
*/
Route::domain('nacional.' . $dominio)->group(function () {
    Route::get('/', fn() => view('nacional.index'))->name('nacional.inicio');

    Route::get('/{year}', function ($year) {
        if (!in_array($year, ['2025', '2026'])) {
            abort(404);
        }

        $pathServer = "/home/sbbleso/www/nacional/fotos/{$year}";
        $pathLocal = public_path("nacional/fotos/{$year}");
        $pathFinal = File::exists($pathServer) ? $pathServer : $pathLocal;

        $fotos = [];
        if (File::exists($pathFinal) && File::isDirectory($pathFinal)) {
            foreach (File::files($pathFinal) as $file) {
                if (in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $fotos[] = asset("nacional/fotos/{$year}/" . $file->getFilename());
                }
            }
        }

        $fotos = collect($fotos)->sort()->values();
        return view("nacional.show_{$year}", compact('fotos'));
    })->name('nacional.show_year');
});

/*
|--------------------------------------------------------------------------
| WEB PRINCIPAL: PORTAL PÚBLICO, AUTENTICACIÓN Y ADMINISTRACIÓN (sbbl.es)
|--------------------------------------------------------------------------
*/
Route::domain($dominio)->group(function () {
    /*
    |--------------------------------------------------------------------------
    | 1. PORTAL PÚBLICO Y AUTENTICACIÓN
    |--------------------------------------------------------------------------
    */
    Auth::routes(['verify' => true]);

    Route::get('/', [InicioController::class, 'index'])->name('inicio.index');
    Route::get('/home', [InicioController::class, 'index'])->name('inicio.index');
    Route::get('/all-events', [InicioController::class, 'events'])->name('inicio.events');
    Route::get('/eventos', [InicioController::class, 'events'])->name('events.index');
    Route::post('/eventos/fetch', [InicioController::class, 'fetchEvents'])->name('events.fetch');

    Route::get('/random-combo-generator', [InicioController::class, 'combo'])->name('inicio.combo');
    Route::get('/rules', [InicioController::class, 'rules'])->name('inicio.rules');
    Route::get('/policy-privacy', [InicioController::class, 'privacy'])->name('inicio.privacy');
    Route::get('/politica-cookies', fn() => view('inicio.cookies'))->name('politica.cookies');
    Route::get('/contact-us', [InicioController::class, 'contact'])->name('inicio.contact');
    Route::post('/contacto', [InicioController::class, 'enviar'])->name('contacto.enviar');
    Route::get('/entrevistas', [InicioController::class, 'entrevistas'])->name('inicio.entrevistas');
    Route::get('/beyblade-nacional-españa', [InicioController::class, 'nacional'])->name('inicio.nacional');
    Route::get('/salon-de-la-fama-beyblade', [InicioController::class, 'halloffame'])->name('inicio.halloffame');
    Route::get('/resumen-semanal', [InicioController::class, 'resumen_semanal'])->name('inicio.resumen_semanal');
    Route::get('/combates', [InicioController::class, 'eventstats'])->name('combates');
    Route::get('/subscriptions', [InicioController::class, 'suscriptions'])->name('subscriptions');
    Route::get('/planes', [PlanController::class, 'index'])->name('planes.index');

    // Nacional (Ranking)
    Route::get('/nacional/ranking', [InicioController::class, 'rankingNacional'])->name('nacional.ranking');

    // Blog / Artículos
    Route::get('/blog', [ArticleController::class, 'index'])->name('blog.index');
    Route::get('/blog/{custom_url}', [ArticleController::class, 'show'])->name('blog.show');
    Route::get('/article/create', [ArticleController::class, 'create'])->name('blog.create');
    Route::post('/article/store', [ArticleController::class, 'store'])->name('blog.store');
    Route::get('/blog/{article}/edit', [ArticleController::class, 'edit'])->name('blog.edit');
    Route::put('/blog/{article}', [ArticleController::class, 'update'])->name('blog.update');

    // Vídeos y Chat de Eventos
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::get('/chat/messages/{eventId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'storeMessage'])->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | 2. EVENTOS, TORNEOS Y ESTADÍSTICAS DEL META
    |--------------------------------------------------------------------------
    */
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // Asistencia, Estados, Puntuaciones y Revisiones
    Route::post('/events/{event}', [EventController::class, 'assist'])->name('events.assist');
    Route::post('/events-add/{event}', [EventController::class, 'addAssist'])->name('events.addAssist');
    Route::delete('/assist/{event}', [EventController::class, 'noassist'])->name('events.noassist');
    Route::put('/events/{event}/{estado}/estado', [EventController::class, 'estadoTorneo'])->name('events.estado');
    Route::put('/events/{event}/actualizar-status', [EventController::class, 'actualizarStatus'])->name('events.status');
    Route::put('/events/{event}/update-video', [EventController::class, 'updateVideo'])->name('events.updateVideo');
    Route::put('/events/{event}/updatePuestos', [EventController::class, 'updatePuestos'])->name('events.updatePuestos');
    Route::put('/events/{event}/{mode}/actualizarPuntuaciones', [EventController::class, 'actualizarPuntuaciones'])->name('events.actualizarPuntuaciones');
    Route::post('/events/{event}/paypal/order', [EventController::class, 'createEventPayPalOrder'])->middleware('auth')->name('events.paypal.order');
    Route::post('/events/{event}/paypal/capture', [EventController::class, 'captureEventPayPalOrder'])->middleware('auth')->name('events.paypal.capture');

    Route::post('/event/{event}/review/start', [EventController::class, 'startReview'])->name('event.review.start');
    Route::post('/events/{event}/review', [EventController::class, 'submitReview'])->name('event.review');
    Route::delete('/events/{event}/reviews/{user}', [EventController::class, 'destroyReview'])->name('event.destroyReview');
    Route::get('/events/{event}/participant/results', [EventController::class, 'getParticipantResults'])->name('events.getParticipantResults');

    // Resultados de Decks y Estadísticas Globales
    Route::post('/event/{eventId}/results', [TournamentResultController::class, 'store'])->name('tournament.results.store');
    Route::post('/versus/{versusId}/results', [TournamentResultController::class, 'storeduel'])->name('versus.results.store');

    Route::get('/beyblade-database/beyblade-stats', [TournamentResultController::class, 'beybladeStats'])->name('inicio.stats');
    Route::get('/beyblade-database/stats', [TournamentResultController::class, 'beybladeStats'])->name('stats.index');
    Route::get('/beyblade-database/separate-stats', [TournamentResultController::class, 'separateStats'])->name('stats.separate');
    Route::get('/beyblade-database/rankingstats', [TournamentResultController::class, 'showRanking'])->name('stats.rankingstats');

    // Endpoints AJAX de Telemetría, Evolución, H2H y Bey-Lab PRO
    Route::post('/beyblade-database/stats/lab-analyze', [TournamentResultController::class, 'analyzeWebLab'])->name('stats.lab.analyze');
    Route::get('/beyblade-database/stats/tournament/{eventId}', [TournamentResultController::class, 'getTournamentStatsDetail'])->name('stats.tournament.detail');
    Route::get('/beyblade-database/stats/combo-trend', [TournamentResultController::class, 'getComboTrend'])->name('stats.combo.trend');
    Route::get('/beyblade-database/stats/player-h2h', [TournamentResultController::class, 'getPlayerH2H'])->name('stats.player.h2h');

    /*
    |--------------------------------------------------------------------------
    | 3. PERFILES, RANKINGS, RETOS Y TROFEOS
    |--------------------------------------------------------------------------
    */
    Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
    Route::get('/profiles/{profile}', [ProfileController::class, 'show'])->name('profiles.show');
    Route::get('/profiles/{profile}/edit', [ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/profiles/{profile}', [ProfileController::class, 'update'])->name('profiles.update');
    Route::put('/profiles-admin/{profile}', [ProfileController::class, 'updatePoints'])->name('profiles.updatePoints');
    Route::put('/profiles-admin-x/{profile}', [ProfileController::class, 'updatePointsX'])->name('profiles.updatePointsX');
    Route::put('/update-admin-x/update-all', [ProfileController::class, 'updateAllPointsX'])->name('profiles.updateAllPointsX');

    Route::get('/rankings', [ProfileController::class, 'ranking'])->name('profiles.ranking');
    Route::get('/ranking-splits', [ProfileController::class, 'rankingPorSplits'])->name('profiles.splits');
    Route::get('/wrapped/{profile}', [ProfileController::class, 'wrapped'])->name('profiles.wrapped');
    Route::get('/bladers/{id}/details', [ProfileController::class, 'getDetails'])->name('blader.details');
    Route::get('/blader/{slug}', [ProfileController::class, 'showPublic'])
        ->name('blader.public')
        ->where('slug', '[a-zA-Z0-9._\-]+');

    // Retos (Challenges)
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::post('/challenges', [ChallengeController::class, 'store'])->name('challenges.store');
    Route::delete('/challenges/{challenges_profiles_id}', [ChallengeController::class, 'destroy'])->name('challenges.destroy');

    // Trofeos
    Route::get('/trophy/assign', [TrophyController::class, 'assignForm'])->name('trophies.assignForm');
    Route::post('/trophy/assign', [TrophyController::class, 'assign'])->name('trophies.assign');
    Route::get('/users/trophies', [TrophyController::class, 'usersWithTrophies'])->name('trophies.usersWithTrophies');
    Route::get('/trophies/user/{userId}', [TrophyController::class, 'showUserTrophies'])->name('trophies.userTrophies');
    Route::delete('/trophies/removeAssignment/{assignmentId}', [TrophyController::class, 'removeAssignment'])->name('trophies.removeAssignment');
    Route::put('/trophies/{userId}/updateCount/{trophyId}', [TrophyController::class, 'updateCount'])->name('trophies.updateCount');
    Route::delete('/trophies/{userId}/remove/{trophyId}', [TrophyController::class, 'remove'])->name('trophies.remove');

    /*
    |--------------------------------------------------------------------------
    | 4. DUELOS INDIVIDUALES (VERSUS), GENERATIONS Y EQUIPOS (CLANES)
    |--------------------------------------------------------------------------
    */
    // Duelos Individuales
    Route::get('/all-versus', [VersusController::class, 'show_all'])->name('versus.all');
    Route::get('/versus/create', [VersusController::class, 'create'])->name('versus.create');
    Route::post('/versus', [VersusController::class, 'store'])->name('versus.store');
    Route::get('/versus/{duel}', [VersusController::class, 'show'])->name('versus.show');
    Route::get('/versus/{duel}/edit', [VersusController::class, 'edit'])->name('versus.edit');
    Route::put('/versus/{duel}', [VersusController::class, 'update'])->name('versus.update');
    Route::get('/versus/{duel}/deck/{deck}', [VersusController::class, 'versusdeck'])->name('versus.versusdeck');
    Route::put('/versus/{duel}/{mode}/{winner}/puntuarDuelo', [VersusController::class, 'puntuarDuelo'])->name('versus.puntuarDuelo');
    Route::put('/versusmass/puntuarDuelos', [VersusController::class, 'puntuarDuelos'])->name('puntuarDuelos');
    Route::put('/versus/{duel}/invalidar', [VersusController::class, 'invalidar'])->name('versus.invalidar');
    Route::put('/versus/{versus}/update-video', [VersusController::class, 'updateVideo'])->name('versus.updateVideo');

    // Generations
    Route::get('/generations', [VersusController::class, 'generation'])->name('generations.index');
    Route::get('/gversus', [VersusController::class, 'versus'])->name('generations.versus');
    Route::get('/generation/create', [VersusController::class, 'gcreate'])->name('generations.create');
    Route::post('/generations', [VersusController::class, 'gstore'])->name('generations.gstore');
    Route::get('/generations/{versus}/edit', [VersusController::class, 'gedit'])->name('generations.edit');
    Route::put('/generations/{versus}', [VersusController::class, 'gupdate'])->name('generations.update');

    // Equipos / Clanes
    Route::get('/equipos', [TeamController::class, 'index'])->name('equipos.index');
    Route::get('/equipos-ranking', [TeamController::class, 'ranking_teams'])->name('equipos.ranking');
    Route::get('/equipos/crear', [TeamController::class, 'create'])->name('equipos.create');
    Route::post('/equipos', [TeamController::class, 'store'])->name('equipos.store');
    Route::get('/equipos/{equipo}', [TeamController::class, 'show'])->name('equipos.show');
    Route::get('/equipos/{equipo}/editar', [TeamController::class, 'edit'])->name('equipos.edit');
    Route::put('/equipos/{equipo}', [TeamController::class, 'update'])->name('equipos.update');
    Route::delete('/equipos/{equipo}', [TeamController::class, 'destroy'])->name('equipos.destroy');

    Route::post('/equipos/{equipo}/add-member', [TeamController::class, 'addMember'])->name('equipos.addMember');
    Route::delete('/equipos/{equipo}/remove-member/{user}', [TeamController::class, 'removeMember'])->name('equipos.removeMember');
    Route::patch('/equipos/{equipo}/change-captain/{miembro}', [TeamController::class, 'changeCaptain'])->name('equipos.changeCaptain');
    Route::post('/equipos/{equipo}/leave', [TeamController::class, 'leaveTeam'])->name('equipos.leave');
    Route::post('/equipos/{equipo}/send-invitation', [TeamController::class, 'sendInvitation'])->name('equipos.sendInvitation');
    Route::post('/equipos/{equipo}/accept-invitation', [TeamController::class, 'acceptInvitation'])->name('equipos.acceptInvitation');
    Route::post('/invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('/invitations/{invitation}/reject', [InvitationController::class, 'reject'])->name('invitations.reject');

    // Duelos de Equipos (Teams Versus)
    Route::get('/all-teams-versus', [TeamsVersusController::class, 'show_all'])->name('teams_versus.all');
    Route::get('/teams-versus/create', [TeamsVersusController::class, 'create'])->name('teams_versus.create');
    Route::post('/teams-versus', [TeamsVersusController::class, 'store'])->name('teams_versus.store');
    Route::get('/teams-versus/{duel}', [TeamsVersusController::class, 'show'])->name('teams_versus.show');
    Route::get('/teams-versus/{duel}/edit', [TeamsVersusController::class, 'edit'])->name('teams_versus.edit');
    Route::put('/teams-versus/{duel}', [TeamsVersusController::class, 'update'])->name('teams_versus.update');
    Route::delete('/teams-versus/{duel}', [TeamsVersusController::class, 'destroy'])->name('teams_versus.destroy');
    Route::put('/teams-versus/{duel}/{mode}/{winner}/puntuarDuelo', [TeamsVersusController::class, 'puntuarDuelo'])->name('teams_versus.puntuarDuelo');
    Route::put('/teams-versus/{duel}/invalidate', [TeamsVersusController::class, 'invalidate'])->name('teams_versus.invalidate');

    /*
    |--------------------------------------------------------------------------
    | 5. ENCICLOPEDIA Y COLECCIÓN DE PIEZAS BEYBLADE
    |--------------------------------------------------------------------------
    */
    Route::get('/beyblade-database', [BeybladeDatabaseController::class, 'index'])->name('database.index');
    Route::get('/beyblade-database/beyblades', [BeybladeDatabaseController::class, 'listBeyblades'])->name('database.beyblades');
    Route::get('/beyblade-database/parts', [BeybladeDatabaseController::class, 'listParts'])->name('database.parts');

    // Colección Personal
    Route::get('/beyblade-database/collection', [BeybladeCollectionController::class, 'index'])->name('collection.index');
    Route::post('/beyblade-database/collection/store', [BeybladeCollectionController::class, 'store'])->name('collection.store');
    Route::put('/collection/{id}', [BeybladeCollectionController::class, 'update'])->name('collection.update');
    Route::delete('/collection/{id}', [BeybladeCollectionController::class, 'destroy'])->name('collection.destroy');
    Route::post('/collection/bulk-destroy', [BeybladeCollectionController::class, 'bulkDestroy'])->name('collection.bulk_destroy');

    // Gestión Genérica de Partes
    Route::get('/partes/{type}', [BeybladeDatabaseController::class, 'indexPartes']);
    Route::get('/partes/{type}/{id}', [BeybladeDatabaseController::class, 'show']);
    Route::post('/partes/{type}', [BeybladeDatabaseController::class, 'store']);
    Route::put('/partes/{type}/{id}', [BeybladeDatabaseController::class, 'update']);
    Route::delete('/partes/{type}/{id}', [BeybladeDatabaseController::class, 'destroy']);

    // Beyblades Completos
    Route::get('/beyblades/create', [BeybladeDatabaseController::class, 'createBey'])->name('beyblades.create');
    Route::post('/beyblades', [BeybladeDatabaseController::class, 'storeBey'])->name('beyblades.store');
    Route::get('/beyblades/{id}/edit', [BeybladeDatabaseController::class, 'editBey'])->name('beyblades.edit');
    Route::put('/beyblades/{id}', [BeybladeDatabaseController::class, 'updateBey'])->name('beyblades.update');
    Route::get('/beyblade-database/beyblades/{id}', [BeybladeDatabaseController::class, 'showBey'])->name('database.showBey');

    // Blades, Ratchets, Bits, OverBlades y AssistBlades
    Route::post('/blades', [BeybladeDatabaseController::class, 'store'])->name('blades.store');
    Route::get('/blades/{id}/edit', [BeybladeDatabaseController::class, 'editBlade'])->name('blades.edit');
    Route::put('/blades/{id}', [BeybladeDatabaseController::class, 'update'])->name('blades.update');
    Route::get('/beyblade-database/blades/{id}', [BeybladeDatabaseController::class, 'showBlade'])->name('database.showBlade');

    Route::post('/ratchets', [BeybladeDatabaseController::class, 'store'])->name('ratchets.store');
    Route::get('/ratchets/{id}/edit', [BeybladeDatabaseController::class, 'editRatchet'])->name('ratchets.edit');
    Route::put('/ratchets/{id}', [BeybladeDatabaseController::class, 'updateRatchet'])->name('ratchets.update');
    Route::get('/beyblade-database/ratchets/{id}', [BeybladeDatabaseController::class, 'showRatchet'])->name('database.showRatchet');

    Route::post('/bits', [BeybladeDatabaseController::class, 'store'])->name('bits.store');
    Route::get('/bits/{id}/edit', [BeybladeDatabaseController::class, 'editBit'])->name('bits.edit');
    Route::put('/bits/{id}', [BeybladeDatabaseController::class, 'updateBit'])->name('bits.update');
    Route::get('/beyblade-database/bits/{id}', [BeybladeDatabaseController::class, 'showBit'])->name('database.showBit');

    Route::post('/overBlades', [BeybladeDatabaseController::class, 'store'])->name('overBlade.store');
    Route::get('/overBlades/{id}/edit', [BeybladeDatabaseController::class, 'editoverBlade'])->name('overBlade.edit');
    Route::put('/overBlades/{id}', [BeybladeDatabaseController::class, 'updateoverBlade'])->name('overBlade.update');
    Route::get('/beyblade-database/overBlades/{id}', [BeybladeDatabaseController::class, 'showoverBlade'])->name('overBlade.showBit');

    Route::post('/assistBlades', [BeybladeDatabaseController::class, 'store'])->name('assistBlade.store');
    Route::get('/assistBlades/{id}/edit', [BeybladeDatabaseController::class, 'editAssistBlade'])->name('assistBlade.edit');
    Route::put('/assistBlades/{id}', [BeybladeDatabaseController::class, 'updateAssistBlade'])->name('assistBlade.update');
    Route::get('/beyblade-database/assistBlades/{id}', [BeybladeDatabaseController::class, 'showAssistBlade'])->name('assistBlade.showBit');

    /*
    |--------------------------------------------------------------------------
    | 6. TIENDA, CARRITO, PAYPAL, TICKETS Y DISCORD AUTH
    |--------------------------------------------------------------------------
    */
    Route::get('/tienda', [CarritoController::class, 'index'])->name('carrito.index');
    Route::get('/carrito', [CarritoController::class, 'show'])->name('carrito.show');
    Route::post('/carrito/add/{producto}', [CarritoController::class, 'add'])->name('carrito.add');
    Route::post('/carrito/update/{producto}', [CarritoController::class, 'update'])->name('carrito.update');
    Route::delete('/carrito/remove/{producto}', [CarritoController::class, 'remove'])->name('carrito.remove');
    Route::post('/carrito/paypal/order', [CarritoController::class, 'createPaypalOrder'])->name('carrito.paypal.order');
    Route::post('/carrito/paypal/capture', [CarritoController::class, 'capturePaypalOrder'])->name('carrito.paypal.capture');
    Route::get('/paypal/success', [CarritoController::class, 'paypalSuccess'])->name('paypal.success');
    Route::get('/paypal/cancel', [CarritoController::class, 'paypalCancel'])->name('paypal.cancel');

    Route::post('/paypal/subscription/confirm', [PayPalController::class, 'confirm'])->middleware('auth')->name('paypal.confirm');
    Route::post('/paypal/webhook', [PayPalWebhookController::class, 'handle']);

    Route::middleware('auth')->group(function () {
        // Compra y consulta de Papeletas de Rifa (Requiere login)
        Route::get('/tickets/buy', [TicketController::class, 'showBuyForm'])->name('tickets.buy');
        Route::post('/paypal/order', [TicketController::class, 'createOrder'])->name('paypal.order');
        Route::post('/paypal/capture', [TicketController::class, 'captureOrder'])->name('paypal.capture');
        Route::get('/tickets/my', [TicketController::class, 'myTickets'])->name('tickets.mine');

        // Vinculación Discord
        Route::get('/auth/discord/redirect', [DiscordController::class, 'redirectToDiscord'])->name('discord.login');
        Route::get('/auth/discord/callback', [DiscordController::class, 'handleDiscordCallback']);

        // Seguridad y Cuenta (Correo y Contraseña)
        Route::put('/account/email', [AccountController::class, 'updateEmail'])->name('account.email.update');
        Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    });

    /*
    |--------------------------------------------------------------------------
    | 7. PANEL DE ADMINISTRACIÓN Y STAFF (/dashboard & /admin)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [InicioController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard/events', [EventController::class, 'index'])->name('events.indexAdmin');
    Route::get('/dashboard/versus', [VersusController::class, 'index'])->name('versus.index');
    Route::get('/dashboard/teams-versus', [TeamsVersusController::class, 'index'])->name('teams_versus.index');
    Route::get('/dashboard/teams', [TeamController::class, 'indexAdmin'])->name('equipos.indexAdmin');
    Route::get('/dashboard/beyblade-parts', [BeybladeDatabaseController::class, 'indexPartes'])->name('database.indexPartes');
    Route::get('/dashboard/beyblades', [BeybladeDatabaseController::class, 'indexBeys'])->name('database.indexBeys');
    Route::get('/dashboard/reviews', [EventController::class, 'reviews_seguimiento'])->middleware('auth')->name('admin.dashboard.reviews');

    // Exclusivo Middleware Admin (Auth + Rol Admin)
    Route::middleware(['auth', 'admin'])->group(function () {
        // Inventario, Tesorería, Rifas y Suscripciones
        Route::get('/dashboard/admin/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
        Route::post('/admin/inventory', [InventoryController::class, 'store'])->name('admin.inventory.store');
        Route::put('/admin/inventory/{id}', [InventoryController::class, 'update'])->name('admin.inventory.update');
        Route::delete('/admin/inventory/{id}', [InventoryController::class, 'destroy'])->name('admin.inventory.destroy');

        Route::get('/dashboard/admin/treasury', [TreasuryController::class, 'index'])->name('admin.treasury.index');
        Route::post('/admin/treasury/income', [TreasuryController::class, 'storeIncome'])->name('admin.treasury.income.store');
        Route::post('/admin/treasury/expense', [TreasuryController::class, 'storeExpense'])->name('admin.treasury.expense.store');
        Route::put('/admin/treasury/{log}/refund', [TreasuryController::class, 'markRefunded'])->name('admin.treasury.refund');
        Route::post('/admin/treasury/reset', [TreasuryController::class, 'resetPeriod'])->name('admin.treasury.reset');
        Route::get('/admin/treasury/export', [TreasuryController::class, 'exportCsv'])->name('admin.treasury.export');

        // Gestión de Clasificatorios Nacional (Exclusivo Admin)
        Route::get('/dashboard/admin/clasificatorios', [QualifierController::class, 'index'])->name('admin.qualifiers.index');

        // Gestión de Rifas y Bombo de Sorteo (Exclusivo Admin)
        Route::get('/dashboard/admin/raffles', [RaffleController::class, 'index'])->name('admin.raffles.index');
        Route::post('/admin/raffles', [RaffleController::class, 'store'])->name('admin.raffles.store');
        Route::put('/admin/raffles/{raffle}', [RaffleController::class, 'update'])->name('admin.raffles.update');
        Route::put('/admin/raffles/{raffle}/toggle', [RaffleController::class, 'toggleActive'])->name('admin.raffles.toggle');
        Route::get('/dashboard/admin/raffles/{raffle}/tickets', [RaffleController::class, 'showTickets'])->name('admin.raffles.tickets');
        Route::get('/sorteo', [TicketController::class, 'index'])->name('tickets.sorteo');
        Route::post('/sorteo/ganadores', [TicketController::class, 'draw'])->name('tickets.sorteo.draw');
        Route::delete('/sorteo', [TicketController::class, 'reset'])->name('tickets.sorteo.reset');

        Route::delete('/admin/historial/borrar/{id}', [ProfileController::class, 'destroyHistory'])->name('admin.history.destroy');
        Route::post('/admin/suscripciones', [ProfileController::class, 'storeSubscription'])->name('admin.subscriptions.store');
        Route::put('/admin/suscripciones/{id}', [ProfileController::class, 'updateSubscription'])->name('admin.subscriptions.update');
        Route::delete('/admin/suscripciones/{id}', [ProfileController::class, 'destroySubscription'])->name('admin.subscriptions.destroy');

        Route::put('/profiles/update-roles/{user}', [ProfileController::class, 'updateRoles'])->name('profiles.updateRoles');

        Route::get('/nacional/importar', [InicioController::class, 'mostrarFormulario'])->name('nacional.importar');
        Route::post('/nacional/procesar-csv', [InicioController::class, 'procesarCsv'])->name('nacional.procesar');

        Route::get('/dashboard/profiles', [ProfileController::class, 'indexAdmin'])->name('profiles.indexAdmin');
        Route::get('/dashboard/profiles-x', [ProfileController::class, 'indexAdminX'])->name('profiles.indexAdminX');
        Route::resource('/dashboard/trophies', TrophyController::class);
        Route::resource('/dashboard/productos', ProductoController::class);
        Route::get('/dashboard/admin/announcements', [InicioController::class, 'anuncios'])->name('index.anuncios');
        Route::post('/dashboard/admin/announcements/send', [InicioController::class, 'sendAnuncio'])->name('index.announcements.send');

        Route::put('/dashboard/pedidos-settings', [PedidoController::class, 'updateSettings'])->name('admin.pedidos.settings');
        Route::get('/dashboard/pedidos', [PedidoController::class, 'index'])->name('admin.pedidos.index');
        Route::get('/dashboard/pedidos/{pedido}', [PedidoController::class, 'show'])->name('admin.pedidos.show');
        Route::put('/dashboard/pedidos/{pedido}', [PedidoController::class, 'update'])->name('admin.pedidos.update');
        Route::delete('/dashboard/pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('admin.pedidos.destroy');
    });
});
