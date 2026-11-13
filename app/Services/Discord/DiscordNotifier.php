<?php

namespace App\Services\Discord;

use App\Models\Article;
use App\Models\Event;
use App\Models\TournamentResult;
use App\Models\User;
use App\Services\Tournament\StandingsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordNotifier
{
    public const DISCORD_ROLES = [
        'Andalucía'          => '1206704489990459452',
        'Aragón'             => '1209155125654978663',
        'Asturias'           => '1209155336033017876',
        'Baleares'           => '1209155169917476874',
        'Canarias'           => '1209154917449859143',
        'Cantabria'          => '1209155399920386109',
        'Castilla La Mancha' => '1209154971229093978',
        'Castilla y León'    => '1209154789003239504',
        'Catalunya'          => '1209154633227046983',
        'Extremadura'        => '1209155220450582618',
        'Galicia'            => '1209154737220354048',
        'Rioja'              => '1209155480342106132',
        'Madrid'             => '1209154530890219520',
        'Murcia'             => '1209155058562895944',
        'Navarra'            => '1209155367578370059',
        'País Vasco'         => '1209154853872345138',
        'Valencia'           => '1209154705327132712',
        'Melilla'            => '1209155513967845438',
        'Ceuta'              => '1209155549468434432',
    ];

    private StandingsService $standingsService;

    public function __construct(?StandingsService $standingsService = null)
    {
        $this->standingsService = $standingsService ?? app(StandingsService::class);
    }

    /**
     * Obtiene el ID de rol de Discord para una región/comunidad.
     */
    public function getRoleIdForRegion(?string $regionName): string
    {
        if (!$regionName) {
            return '';
        }

        return self::DISCORD_ROLES[$regionName] ?? '';
    }

    /**
     * Construye el string de mención <@&ROLE_ID> para la región del evento.
     */
    public function getRegionMention(Event $event, string $prefix = ''): string
    {
        $event->loadMissing('region');
        $regionName = $event->region->name ?? 'Global';
        $roleId = $this->getRoleIdForRegion($regionName);

        return $roleId ? "{$prefix}<@&{$roleId}>" : '';
    }

    /**
     * Notificación de creación/edición de evento desde la web principal (sbbl.es).
     */
    public function sendEventNotification(Event $event, bool $isEdit = false): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $webhookUrl = config('services.discord.webhook_url');
        if (!$webhookUrl) {
            return;
        }

        $event->loadMissing('region');
        $regionName = $event->region->name ?? 'Global';
        $rolId = $this->getRoleIdForRegion($regionName);
        $fecha = Carbon::parse($event->date)->translatedFormat('d \d\e F \d\e\l Y');

        if ($isEdit) {
            $content = "⚠️ ¡El torneo de **{$event->city}** ({$regionName}) ha sido **modificado**!\n<@&{$rolId}>";
            $embed = [
                'title'       => "{$event->name} ({$event->mode})",
                'description' => "📅 Nueva fecha: **{$fecha}** a las **{$event->time}**.\n📍 Ubicación: {$event->location}\n🔗 Más info: https://sbbl.es/events/{$event->id}",
                'color'       => 16753920,
            ];
        } else {
            $content = "¡Hay un nuevo torneo disponible para {$event->city} ({$regionName})!\n<@&{$rolId}>";
            $embed = [
                'title'       => "{$event->name} ({$event->mode})",
                'description' => "El día {$fecha} a las {$event->time}. Inscríbete en: https://sbbl.es/events/{$event->id}",
                'color'       => 7506394,
            ];
        }

        $this->dispatchWebhook($webhookUrl, [
            'content' => $content,
            'embeds'  => [$embed],
        ], 'notificación web de evento a Discord');
    }

    /**
     * Notificación de creación/edición de evento desde el Hub (PWA).
     */
    public function sendHubEventNotification(Event $event, bool $isEdit = false): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $webhookUrl = config('services.discord.webhook_url');
        if (!$webhookUrl) {
            return;
        }

        $event->loadMissing('region');
        $regionName = $event->region->name ?? 'Global';
        $mention = $this->getRegionMention($event, "\n");

        $fecha = Carbon::parse($event->date)->translatedFormat('d \d\e F \d\e\l Y');
        $hora = substr($event->time, 0, 5);
        $hubUrl = "https://hub.sbbl.es/?evento={$event->id}";

        if ($isEdit) {
            $content = "⚠️ ¡El torneo de **{$event->city}** ({$regionName}) ha sido **modificado**!{$mention}";
            $embed = [
                'title'       => "🏆 {$event->name} (" . strtoupper($event->mode) . ")",
                'url'         => $hubUrl,
                'description' => "📅 **Nueva fecha:** {$fecha} a las **{$hora}**\n📍 **Ubicación:** {$event->location}\n⚔️ **Formato:** " . strtoupper($event->beys) . "\n\n📲 **[Abrir e inscribirse en el Hub]({$hubUrl})**",
                'color'       => 16753920,
            ];
        } else {
            $content = "🔥 ¡Nuevo torneo disponible en **{$event->city}** ({$regionName})!{$mention}";
            $embed = [
                'title'       => "🏆 {$event->name} (" . strtoupper($event->mode) . ")",
                'url'         => $hubUrl,
                'description' => "📅 **Fecha:** {$fecha} a las **{$hora}**\n📍 **Ubicación:** {$event->location}\n⚔️ **Formato:** " . strtoupper($event->beys) . "\n\n📲 **[Inscríbete y registra tu Deck en el Hub]({$hubUrl})**",
                'color'       => 65521,
            ];
        }

        $this->dispatchWebhook($webhookUrl, [
            'content' => $content,
            'embeds'  => [$embed],
        ], 'webhook Hub Discord');
    }

    /**
     * Notificación al iniciar la Ronda 1 o generar el Cuadro Eliminatorio (Top Cut).
     */
    public function sendBracketNotification(Event $event, bool $isSwissStart = false): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $webhookUrl = config('services.discord.hub_webhook') ?: config('services.discord.webhook_url');
        if (!$webhookUrl) {
            return;
        }

        $event->loadMissing('region');
        $mention = $this->getRegionMention($event, "\n");
        $bracketUrl = route('torneo.bracket', $event->id);

        if ($isSwissStart) {
            $content = "⚔️ ¡Ha comenzado **{$event->name}**! La **Ronda 1** ya está en marcha en directo.{$mention}";
            $embed = [
                'title'       => "📡 Seguir Rondas, Clasificación y Cuadro en Vivo - {$event->name}",
                'url'         => $bracketUrl,
                'description' => "¡El torneo ha comenzado! Consulta los emparejamientos de cada ronda, la tabla de clasificación con desempates y el cuadro eliminatorio en tiempo real:\n\n📲 **[Abrir Seguimiento en Vivo]({$bracketUrl})**",
                'color'       => 65521,
                'footer'      => ['text' => 'SBBL Hub • Spanish Beybattle League'],
            ];
        } else {
            $content = "🏆 ¡El **Cuadro Eliminatorio (Top Cut)** de **{$event->name}** ya está disponible en directo!{$mention}";
            $embed = [
                'title'       => "🔗 Sigue el Árbol Eliminatorio en Vivo - {$event->name}",
                'url'         => $bracketUrl,
                'description' => "¡La fase eliminatoria ha comenzado!\n\n📲 **[Ver el Cuadro Interactivo en tiempo real]({$bracketUrl})**",
                'color'       => 14177041,
                'footer'      => ['text' => 'SBBL Hub • Spanish Beybattle League'],
            ];
        }

        $this->dispatchWebhook($webhookUrl, [
            'content' => $content,
            'embeds'  => [$embed],
        ], 'webhook Bracket Discord');
    }

    /**
     * Reporte oficial de cierre de acta con podio, decks, MVP y telemetría de arena.
     */
    public function sendTournamentReport(Event $event, ?array $clasificacion = null): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        if (!in_array($event->beys, ['ranking', 'rankingplus', 'copaespecial1'])) {
            return;
        }

        $webhookUrl = config('services.discord.webhook_url');
        if (!$webhookUrl) {
            return;
        }

        $event->loadMissing('region');
        $regionName = $event->region->name ?? 'Global';
        $mention = $this->getRegionMention($event);

        if (is_null($clasificacion)) {
            $standingsData = $this->standingsService->getStandings($event->id);
            $clasificacion = $standingsData['clasificacion'] ?? [];
        }

        $top3 = array_slice($clasificacion, 0, 3);
        if (empty($top3)) {
            return;
        }

        $formatPlayerDeck = function ($userId) use ($event) {
            $combos = TournamentResult::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->whereNotNull('blade')
                ->orderBy('id', 'asc')
                ->limit(3)
                ->get();

            if ($combos->isEmpty()) {
                return '_Sin deck registrado_';
            }

            return $combos->map(function ($c) {
                $extras = array_filter([$c->over_blade, $c->assist_blade], fn($v) => !empty($v) && $v !== '-');
                $extraStr = !empty($extras) ? ' (' . implode('/', $extras) . ')' : '';
                return "• `{$c->blade}{$extraStr} {$c->ratchet} {$c->bit}`";
            })->implode("\n");
        };

        $medals = ['🥇 1º PUESTO', '🥈 2º PUESTO', '🥉 3º PUESTO'];
        $podiumFields = [];

        foreach ($top3 as $idx => $player) {
            $podiumFields[] = [
                'name'   => "{$medals[$idx]} — {$player['name']}",
                'value'  => $formatPlayerDeck($player['id']),
                'inline' => false,
            ];
        }

        $battleStats = DB::table('match_battles as mb')
            ->join('tournament_matches as tm', 'mb.tournament_match_id', '=', 'tm.id')
            ->where('tm.event_id', $event->id)
            ->whereIn('mb.finish_type', ['spin', 'over', 'burst', 'xtreme'])
            ->selectRaw("
                COUNT(*) as total_bouts,
                SUM(CASE WHEN mb.finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtremes,
                SUM(CASE WHEN mb.finish_type = 'burst' THEN 1 ELSE 0 END) as bursts,
                SUM(CASE WHEN mb.finish_type = 'over' THEN 1 ELSE 0 END) as overs,
                SUM(CASE WHEN mb.finish_type = 'spin' THEN 1 ELSE 0 END) as spins
            ")
            ->first();

        $mvpCombo = DB::selectOne("
            SELECT
                CONCAT(c.blade, ' ', IFNULL(c.ratchet, ''), ' ', IFNULL(c.bit, '')) as combo_name,
                u.name as owner_name,
                COUNT(*) as wins,
                SUM(mb.points_awarded) as pts
            FROM match_battles mb
            JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
            JOIN tournament_results c ON c.id = CASE
                WHEN mb.winner_id = tm.player1_id THEN mb.player1_combo_id
                ELSE mb.player2_combo_id
            END
            JOIN users u ON c.user_id = u.id
            WHERE tm.event_id = ?
              AND mb.winner_id IS NOT NULL
              AND mb.finish_type IN ('spin', 'over', 'burst', 'xtreme')
            GROUP BY combo_name, u.name
            ORDER BY wins DESC, pts DESC
            LIMIT 1
        ", [$event->id]);

        $xtremeKing = DB::table('match_battles as mb')
            ->join('tournament_matches as tm', 'mb.tournament_match_id', '=', 'tm.id')
            ->join('users as u', 'mb.winner_id', '=', 'u.id')
            ->where('tm.event_id', $event->id)
            ->where('mb.finish_type', 'xtreme')
            ->select('u.name', DB::raw('COUNT(*) as total_xtremes'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total_xtremes')
            ->first();

        if ($mvpCombo) {
            $podiumFields[] = [
                'name'   => '🔥 BEYBLADE MVP DEL TORNEO',
                'value'  => "**{$mvpCombo->combo_name}** (de *{$mvpCombo->owner_name}*)\n`{$mvpCombo->wins} victorias` · `{$mvpCombo->pts} pts generados`",
                'inline' => true,
            ];
        }

        if ($xtremeKing && $xtremeKing->total_xtremes > 0) {
            $podiumFields[] = [
                'name'   => '⚡ REY DEL XTREME',
                'value'  => "**{$xtremeKing->name}**\n`{$xtremeKing->total_xtremes} Xtreme Finishes`",
                'inline' => true,
            ];
        }

        if ($battleStats && $battleStats->total_bouts > 0) {
            $podiumFields[] = [
                'name'   => "📊 TELEMETRÍA DE LA ARENA ({$battleStats->total_bouts} lanzamientos)",
                'value'  => "💥 **Xtreme:** `{$battleStats->xtremes}`  |  💣 **Burst:** `{$battleStats->bursts}`  |  🚀 **Over:** `{$battleStats->overs}`  |  🌀 **Spin:** `{$battleStats->spins}`",
                'inline' => false,
            ];
        }

        $totalPlayers = count($clasificacion);
        $bracketUrl = route('torneo.bracket', $event->id);

        $embed = [
            'title'       => "🏆 CRÓNICA OFICIAL: {$event->name}",
            'url'         => $bracketUrl,
            'description' => "¡Torneo finalizado en **{$event->city}** ({$regionName}) con **{$totalPlayers} Bladers** participantes!\nAquí tenéis el podio oficial, los decks campeones y el resumen estadístico de la jornada:",
            'color'       => 16766720,
            'fields'      => $podiumFields,
            'footer'      => [
                'text' => 'SBBL Hub • Temporada 3 • Pulsa en el título para ver el cuadro completo',
            ],
            'timestamp'   => now()->toIso8601String(),
        ];

        $this->dispatchWebhook($webhookUrl, [
            'content' => "📢 **¡ACTA CERRADA Y RESULTADOS OFICIALES!** {$mention}",
            'embeds'  => [$embed],
        ], 'reporte final a Discord');
    }

    /**
     * Notificación de nuevo artículo o anuncio en el blog/mercado.
     *
     * @param  Article|Collection|array|int  $article
     * @param  User|null  $author
     */
    public function sendArticleNotification($article, ?User $author = null): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        $webhookUrl = config('services.discord.articles_webhook');
        if (!$webhookUrl) {
            return;
        }

        if ($article instanceof Collection || is_array($article)) {
            $article = $article[0] ?? null;
        } elseif (is_numeric($article)) {
            $article = Article::find($article);
        }

        if (!$article instanceof Article) {
            return;
        }

        $author = $author ?? (Auth::id() ? User::find(Auth::id()) : User::find($article->user_id));
        $authorName = $author ? $author->name : 'Un usuario';

        $this->dispatchWebhook($webhookUrl, [
            'content' => '¡Hay un nuevo anuncio de compra/venta!',
            'embeds'  => [
                [
                    'title'       => "{$authorName} ha publicado un nuevo anuncio de {$article->article_type}",
                    'description' => "{$article->title}. Accede en: https://sbbl.es/mercado/{$article->custom_url}",
                    'color'       => '7506394',
                ],
            ],
        ], 'notificación de artículo a Discord');
    }

    /**
     * Despacha el envío HTTP POST tras enviar la respuesta al cliente (sin bloquear la petición HTTP).
     */
    private function dispatchWebhook(string $webhookUrl, array $payload, string $context): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        dispatch(function () use ($webhookUrl, $payload, $context) {
            try {
                Http::post($webhookUrl, $payload);
            } catch (\Throwable $e) {
                Log::warning("Error enviando {$context}: " . $e->getMessage());
            }
        })->afterResponse();
    }
}
