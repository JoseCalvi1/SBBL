<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TournamentResult;
use App\Models\Profile;
use App\Models\Team;
use App\Models\TeamsVersus;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HubController extends Controller
{
    public function index()
    {
        return view('hub.app');
    }

    public function getTorneos(Request $request)
    {
        $hoy = \Carbon\Carbon::now()->format('Y-m-d');
        $userId = Auth::id();
        $user = Auth::user();

        $isAdmin = $user ? $user->hasRole('admin') : false;
        $isJuez = $user ? $user->hasRole('juez') : false;
        $isRevisor = $user ? $user->hasRole('revisor') : false;

        $query = Event::with(['region', 'users', 'judges.profile', 'judges.roles']);
        $query->whereDate('date', '>=', config('sbbl.season_start'));

        $isPersonal = $request->filled('personal');
        if ($isPersonal) {
            if ($request->personal === 'inscrito') {
                $query->whereHas('users', fn($q) => $q->where('users.id', $userId));
            } elseif ($request->personal === 'juez') {
                $query->where(function ($q) use ($userId) {
                    $q->whereHas('judges', fn($sq) => $sq->where('users.id', $userId))
                      ->orWhere('created_by', $userId);
                });
            }
        }

        if ($request->filled('tipo') && $request->tipo === 'historial') {
            $query->whereIn('status', ['COMPLETED', 'CLOSED', 'PENDING', 'REVIEW', 'INVALID'])
                  ->orderBy('date', 'desc')->orderBy('time', 'desc');
        } else {
            if ($isPersonal) {
                $query->orderBy('date', 'desc')->orderBy('time', 'desc');
            } else {
                $query->whereIn('status', ['OPEN', 'LIVE'])
                      ->whereDate('date', '>=', $hoy)
                      ->orderBy('date', 'asc')->orderBy('time', 'asc');
            }
        }

        if ($request->filled('tipo') && $request->tipo !== 'historial') {
            if ($request->tipo === 'express') {
                $query->where('name', 'LIKE', 'Duelo Express:%');
            } elseif ($request->tipo === 'crew_battle') {
                $query->whereIn('beys', ['crew_battle', 'equipos'])->where('name', 'NOT LIKE', 'Duelo Express:%');
            } else {
                $query->where('beys', $request->tipo)->where('name', 'NOT LIKE', 'Duelo Express:%');
            }
        } elseif (!$request->filled('tipo') || $request->tipo !== 'historial') {
            $query->where('name', 'NOT LIKE', 'Duelo Express:%');
        }

        if ($request->filled('region')) $query->where('region_id', $request->region);

        $torneos = $query->get();

        $torneos->map(function ($torneo) use ($userId, $isAdmin, $isJuez, $isRevisor) {
            $torneo->is_inscribed = $torneo->users->contains('id', $userId);
            $torneo->total_inscritos = $torneo->users->count();

            // Diferenciar Jueces, Revisores y Ayudantes locales del evento
            $torneo->jueces_asignados = $torneo->judges->map(function ($juez) {
                $roles = $juez->roles->pluck('name');
                $isMainJudge = $roles->contains('admin') || $roles->contains('juez');
                $isOnlyRevisor = !$isMainJudge && $roles->contains('revisor');
                $isHelper = !$isMainJudge && !$isOnlyRevisor;

                return [
                    'id'         => $juez->id,
                    'name'       => $juez->name,
                    'avatar'     => $juez->profile->avatar_url ?? '/images/default.png',
                    'is_revisor' => $isOnlyRevisor,
                    'is_helper'  => $isHelper,
                    'role_rank'  => $isMainJudge ? 1 : ($isOnlyRevisor ? 2 : 3)
                ];
            });

            $torneo->is_judge_assigned = $torneo->judges->contains('id', $userId);
            $isTeamEvent = in_array($torneo->beys, ['equipos', 'crew_battle']) || str_starts_with($torneo->name, 'Duelo Express:');

            // 1. Auto-apuntarse como Staff: Admin, Juez y Revisor pueden apuntarse a TODOS los torneos
            $torneo->can_be_staff = ($isAdmin || $isJuez || $isRevisor);

            // 2. Iniciar y Arbitrar la Arena:
            $canArbitrate = false;
            if ($isAdmin || $isJuez || $isRevisor) {
                // Admin, Jueces y Revisores pueden iniciar y arbitrar cualquier torneo
                $canArbitrate = true;
            } elseif ($torneo->is_judge_assigned) {
                // Cualquier ayudante asignado específicamente a este torneo puede arbitrarlo
                $canArbitrate = true;
            } elseif (!$isTeamEvent && $torneo->created_by == $userId) {
                // El creador del evento puede gestionar su propio torneo (salvo duelos de equipos)
                $canArbitrate = true;
            }
            $torneo->can_arbitrate = $canArbitrate;

            // 3. Añadir ayudantes manuales al torneo: Staff oficial siempre, y el Creador en su propio torneo (salvo equipos)
            $torneo->can_manage_helpers = ($isAdmin || $isJuez || $isRevisor) || (!$isTeamEvent && $torneo->created_by == $userId);

            $limite = 9999;
            if ($torneo->has_stadium_limit || !empty($torneo->max_players)) {
                if ($torneo->stadiums == 1) $limite = 20;
                elseif ($torneo->stadiums == 2) $limite = 35;
                elseif ($torneo->stadiums == 3) $limite = 45;
                elseif ($torneo->stadiums == 4) $limite = 55;
                elseif ($torneo->stadiums >= 5) $limite = 64;
                if (!empty($torneo->max_players)) $limite = $torneo->max_players;
            }
            $torneo->aforo_maximo = $limite;

            unset($torneo->users);
            unset($torneo->judges);

            return $torneo;
        });

        return response()->json($torneos);
    }

    public function getRegiones()
    {
        $regiones = Region::with('provinces')->orderBy('name')->get();
        return response()->json($regiones);
    }

    public function getPiezas()
    {
        return response()->json([
            'blades' => DB::table('blades')->orderBy('nombre_takara')->pluck('nombre_takara'),
            'ratchets' => DB::table('ratchets')->orderBy('nombre')->pluck('nombre'),
            'bits' => DB::table('bits')->orderBy('nombre')->pluck('nombre'),
            'over_blades' => DB::table('over_blades')->orderBy('nombre')->pluck('nombre'),
            'assist_blades' => DB::table('assist_blades')->orderBy('nombre')->pluck('nombre'),
        ]);
    }

    public function guardarDeck(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $authUser = Auth::user();
        $userId = $authUser->id;

        // Comprobamos si un miembro del Staff o Creador está editando el deck de otro jugador
        $isEditingOther = false;
        if ($request->filled('target_user_id') && (int) $request->input('target_user_id') !== (int) $authUser->id) {
            $isTeamEvent = in_array($event->beys, ['equipos', 'crew_battle']) || str_starts_with($event->name, 'Duelo Express:');
            $isAssignedStaff = $event->judges()->where('user_id', $authUser->id)->exists();

            $canEditOthers = $authUser->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])
                || $isAssignedStaff
                || (!$isTeamEvent && $event->created_by == $authUser->id);

            if (!$canEditOthers) {
                return response()->json(['success' => false, 'message' => '⛔ No tienes permisos para editar el deck de otro jugador.'], 403);
            }

            $userId = (int) $request->input('target_user_id');
            $isEditingOther = true;
        }

        $date = Carbon::parse($event->date);
        $isAlreadyInscribed = $event->users()->where('user_id', $userId)->exists();

        // Solo comprobamos límites semanales/mensuales si es el propio jugador inscribiéndose por primera vez
        if (!$isEditingOther && !$isAlreadyInscribed && in_array($event->beys, ['ranking', 'rankingplus'])) {
            $registeredThisWeek = DB::table('assist_user_event')
                ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                ->where('assist_user_event.user_id', $userId)
                ->whereBetween('events.date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()])
                ->whereIn('events.beys', ['ranking', 'rankingplus'])
                ->exists();

            if ($registeredThisWeek) {
                return response()->json(['success' => false, 'message' => '⛔ LÍMITE: Ya tienes un torneo ranking esta semana.'], 422);
            }

            $monthlyCount = DB::table('assist_user_event')
                ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                ->where('assist_user_event.user_id', $userId)
                ->whereBetween('events.date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->whereIn('events.beys', ['ranking', 'rankingplus'])
                ->count();

            if ($monthlyCount >= 2) {
                return response()->json(['success' => false, 'message' => '⛔ LÍMITE: Máximo 2 torneos ranking al mes.'], 422);
            }
        }

        // Aseguramos que el jugador objetivo esté en la lista del torneo (sin inscribir al juez)
        $event->users()->syncWithoutDetaching([$userId]);

        // 2x1 Juez + Jugador (Solo aplica si el juez se está inscribiendo a sí mismo)
        if (!$isEditingOther && $request->has('also_judge') && $request->also_judge == '1' && $authUser->hasAnyRole(['admin', 'juez'])) {
            $event->judges()->syncWithoutDetaching([$authUser->id]);
        }

        if ($request->has('is_tentative') && $request->is_tentative == 'true') {
            TournamentResult::where('user_id', $userId)->where('event_id', $eventId)->delete();
            return response()->json(['success' => true, 'message' => '✅ Plaza asegurada (Inscripción Tentativa).']);
        }

        TournamentResult::where('user_id', $userId)->where('event_id', $eventId)->delete();

        $blades = $request->input("blade", []);
        $ratchets = $request->input("ratchet", []);
        $bits = $request->input("bit", []);
        $overBlades = $request->input("over_blade", []);
        $assistBlades = $request->input("assist_blade", []);

        foreach ($blades as $index => $blade) {
            if (empty($blade)) continue;

            TournamentResult::create([
                'user_id'         => $userId,
                'event_id'        => $eventId,
                'blade'           => $blade,
                'over_blade'      => $overBlades[$index] ?? null,
                'assist_blade'    => $assistBlades[$index] ?? null,
                'ratchet'         => $ratchets[$index] ?? null,
                'bit'             => $bits[$index] ?? null,
                'victorias'       => 0,
                'derrotas'        => 0,
                'puntos_ganados'  => 0,
                'puntos_perdidos' => 0,
            ]);
        }

        $msg = $isEditingOther ? '✅ Deck del jugador actualizado correctamente.' : '✅ ¡Deck de combate registrado!';
        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function cancelarAsistencia($eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->status !== 'OPEN') {
            return response()->json(['success' => false, 'message' => '⛔ No puedes retirarte de un torneo en curso.'], 422);
        }

        $event->users()->detach(Auth::id());

        return response()->json(['success' => true, 'message' => 'Asistencia cancelada correctamente.']);
    }

    public function comenzarTorneo($eventId)
    {
        $event = Event::findOrFail($eventId);
        $user = Auth::user();
        $isTeamEvent = in_array($event->beys, ['equipos', 'crew_battle']) || str_starts_with($event->name, 'Duelo Express:');
        $isAssigned = $event->judges()->where('user_id', $user->id)->exists();

        $canStart = false;
        if ($user->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            $canStart = true;
        } elseif ($isAssigned) {
            $canStart = true;
        } elseif (!$isTeamEvent && $event->created_by == $user->id) {
            $canStart = true;
        }

        if (!$canStart) {
            return response()->json(['message' => '⛔ No tienes permisos para iniciar este torneo.'], 403);
        }

        $event->status = 'LIVE';
        $event->save();

        return response()->json(['success' => true, 'message' => '✅ ¡Torneo en curso! Inscripciones bloqueadas.']);
    }

    public function getNombresTorneo($eventId)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['message' => '⛔ Acceso denegado'], 403);
        }

        $event = Event::with('users')->findOrFail($eventId);

        $names = $event->users->map(function($user) {
            return $user->name . '#' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
        })->implode("\n");

        return response()->json(['success' => true, 'nombres' => $names]);
    }

    public function getMeta(Request $request)
    {
        $minPartidas = (int) $request->query('min_partidas', 5);
        $dias = $request->query('dias', '30'); // Por defecto 30 días

        // Construimos el filtro de fecha dinámico
        $dateFilter = "";
        if ($dias !== 'all') {
            $diasInt = (int) $dias;
            $dateFilter = " AND e.date >= DATE_SUB(CURDATE(), INTERVAL {$diasInt} DAY) ";
        }

        // 1. TOP COMBOS
        $combos = \Illuminate\Support\Facades\DB::select("
            SELECT
                blade, ratchet, bit,
                IFNULL(over_blade, '-') as over_blade,
                IFNULL(assist_blade, '-') as assist_blade,
                COUNT(*) as total_partidas,
                SUM(is_winner) as total_victorias,
                SUM(is_loser) as total_derrotas,
                (SUM(is_winner) / COUNT(*)) * 100 as percentage_victories,
                SUM(points_won) as eficiencia,
                ROUND(IFNULL(SUM(points_won) / NULLIF(SUM(is_winner), 0), 0), 2) as puntos_ganados_por_combate,
                ROUND(IFNULL(SUM(points_lost) / NULLIF(SUM(is_loser), 0), 0), 2) as puntos_perdidos_por_combate,
                SUM(spin_wins) as spin_wins,
                SUM(over_wins) as over_wins,
                SUM(burst_wins) as burst_wins,
                SUM(xtreme_wins) as xtreme_wins
            FROM (
                SELECT
                    c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                    (mb.winner_id = c.user_id) as is_winner,
                    (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                    CASE WHEN mb.winner_id = c.user_id THEN mb.points_awarded ELSE 0 END as points_won,
                    CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN mb.points_awarded ELSE 0 END as points_lost,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN events e ON tm.event_id = e.id
                JOIN tournament_results c ON mb.player1_combo_id = c.id
                WHERE mb.winner_id IS NOT NULL $dateFilter

                UNION ALL

                SELECT
                    c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                    (mb.winner_id = c.user_id) as is_winner,
                    (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                    CASE WHEN mb.winner_id = c.user_id THEN mb.points_awarded ELSE 0 END as points_won,
                    CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN mb.points_awarded ELSE 0 END as points_lost,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN events e ON tm.event_id = e.id
                JOIN tournament_results c ON mb.player2_combo_id = c.id
                WHERE mb.winner_id IS NOT NULL $dateFilter
            ) as combates_unificados
            GROUP BY blade, ratchet, bit, over_blade, assist_blade
            HAVING total_partidas >= ?
            ORDER BY percentage_victories DESC, total_partidas DESC, eficiencia DESC
            LIMIT 50
        ", [$minPartidas]);

        // 2. RED DE ENFRENTAMIENTOS: ¿Quién gana a quién?
        $matchupsRaw = \Illuminate\Support\Facades\DB::select("
            SELECT
                CONCAT(winner_c.blade, ' ', winner_c.ratchet, ' ', winner_c.bit) as winner_combo,
                CONCAT(loser_c.blade, ' ', loser_c.ratchet, ' ', loser_c.bit) as loser_combo,
                COUNT(*) as times_won
            FROM match_battles mb
            JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
            JOIN tournament_results winner_c ON winner_c.id = CASE WHEN mb.winner_id = tm.player1_id THEN mb.player1_combo_id ELSE mb.player2_combo_id END
            JOIN tournament_results loser_c ON loser_c.id = CASE WHEN mb.winner_id = tm.player1_id THEN mb.player2_combo_id ELSE mb.player1_combo_id END
            WHERE mb.winner_id IS NOT NULL AND mb.player1_combo_id IS NOT NULL AND mb.player2_combo_id IS NOT NULL
            GROUP BY winner_combo, loser_combo
        ");

        $matchupsMap = [];
        foreach ($matchupsRaw as $m) {
            $matchupsMap[$m->winner_combo]['wins_against'][$m->loser_combo] = $m->times_won;
            $matchupsMap[$m->loser_combo]['losses_against'][$m->winner_combo] = $m->times_won;
        }

        // 3. Cruzar los datos y construir el perfil táctico avanzado
        foreach ($combos as &$combo) {
            $comboKey = $combo->blade . ' ' . $combo->ratchet . ' ' . $combo->bit;

            // TOP 3: Fuerte Contra (Presas)
            $bestMatchups = [];
            if (isset($matchupsMap[$comboKey]['wins_against'])) {
                $wins = $matchupsMap[$comboKey]['wins_against'];
                arsort($wins); // Ordenamos de más victorias a menos
                // Quitamos las victorias contra el mismo combo (espejo) y cogemos los 3 primeros
                $filteredWins = array_filter($wins, fn($k) => $k !== $comboKey, ARRAY_FILTER_USE_KEY);
                $top3Wins = array_slice($filteredWins, 0, 3, true);

                foreach($top3Wins as $enemy => $w) {
                    $bestMatchups[] = ['combo' => $enemy, 'victorias' => $w];
                }
            }
            $combo->mejores_emparejamientos = $bestMatchups;

            // TOP 3: Débil Contra (Némesis)
            $worstMatchups = [];
            if (isset($matchupsMap[$comboKey]['losses_against'])) {
                $losses = $matchupsMap[$comboKey]['losses_against'];
                arsort($losses); // Ordenamos de más derrotas a menos
                $filteredLosses = array_filter($losses, fn($k) => $k !== $comboKey, ARRAY_FILTER_USE_KEY);
                $top3Losses = array_slice($filteredLosses, 0, 3, true);

                foreach($top3Losses as $enemy => $l) {
                    $worstMatchups[] = ['combo' => $enemy, 'derrotas' => $l];
                }
            }
            $combo->peores_emparejamientos = $worstMatchups;

            // Identidad del combo (¿Qué tipo de final hace más?)
            $styles = [
                'SPIN FINISHER' => $combo->spin_wins,
                'OVER FINISHER' => $combo->over_wins,
                'BURST ATTACKER' => $combo->burst_wins,
                'XTREME ASSASSIN' => $combo->xtreme_wins,
            ];
            arsort($styles);
            $signatureStyle = array_key_first($styles);
            $combo->estilo_combate = $styles[$signatureStyle] > 0 ? $signatureStyle : 'BALANCEADO';
        }

        return response()->json($combos);
    }

    public function getUltimoDeck()
    {
        $userId = Auth::id();

        $latestEventId = TournamentResult::where('user_id', $userId)
            ->whereNotNull('blade')
            ->orderBy('id', 'desc')
            ->value('event_id');

        if (!$latestEventId) {
            return response()->json([]);
        }

        $ultimoDeck = TournamentResult::where('user_id', $userId)
            ->where('event_id', $latestEventId)
            ->orderBy('id', 'asc') // <-- CRÍTICO: Mantiene el orden exacto 1, 2, 3
            ->limit(3)
            ->get(['blade', 'ratchet', 'bit', 'over_blade', 'assist_blade']);

        return response()->json($ultimoDeck);
    }

    // EQUIPOS: Ahora SIN la columna de imagen 'logo' para evitar memory limits
    public function getTeamRanking(Request $request)
    {
        $limit = $request->get('limit', 50);

        $ranking = Team::select('id', 'name', 'points_x3')
            ->where('points_x3', '>', 0)
            ->orderBy('points_x3', 'DESC')
            ->limit($limit)
            ->get();

        return response()->json($ranking);
    }

    public function getTeamDuels()
    {
        $duels = TeamsVersus::with(['versus_1', 'versus_2'])
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->orderBy('created_at', 'DESC')
            ->limit(15)
            ->get();

        return response()->json($duels);
    }

    public function storeTeamDuel(Request $request)
    {
        $request->validate([
            'team_id_1' => 'required|exists:teams,id',
            'team_id_2' => 'required|exists:teams,id|different:team_id_1',
            'result_1'  => 'required|integer|min:0',
            'result_2'  => 'required|integer|min:0',
            'url'       => 'nullable|url',
        ]);

        TeamsVersus::create([
            'team_id_1' => $request->team_id_1,
            'team_id_2' => $request->team_id_2,
            'result_1'  => $request->result_1,
            'result_2'  => $request->result_2,
            'matchup'   => $request->modalidad ?? 'beybladex',
            'status'    => 'OPEN',
            'url'       => $request->url,
        ]);

        return response()->json(['message' => '⚔️ Duelo registrado y pendiente de revisión.']);
    }

    public function getPlayerProfile($id)
    {
        $s3StartDate = config('sbbl.season_start');

        $profile = \App\Models\Profile::with(['user', 'region'])->where('user_id', $id)->first();
        if (!$profile) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $team = \Illuminate\Support\Facades\DB::table('team_user')
            ->join('teams', 'team_user.team_id', '=', 'teams.id')
            ->where('team_user.user_id', $id)
            ->select('teams.name', 'teams.points_x3')
            ->first();

        $stats = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $id)
            ->where('events.date', '>=', $s3StartDate)
            ->selectRaw('
                SUM(CASE WHEN assist_user_event.puesto != "nopresentado" AND assist_user_event.puesto != "dropped" AND assist_user_event.puesto IS NOT NULL AND assist_user_event.puesto != "" THEN 1 ELSE 0 END) as torneos_jugados,
                SUM(CASE WHEN assist_user_event.puesto = "primero" THEN 1 ELSE 0 END) as primeros,
                SUM(CASE WHEN assist_user_event.puesto = "segundo" THEN 1 ELSE 0 END) as segundos,
                SUM(CASE WHEN assist_user_event.puesto = "tercero" THEN 1 ELSE 0 END) as terceros
            ')
            ->first();

        $finish_stats = \Illuminate\Support\Facades\DB::table('match_battles')
            ->join('tournament_matches as tm', 'match_battles.tournament_match_id', '=', 'tm.id')
            ->join('events as e', 'tm.event_id', '=', 'e.id')
            ->where('match_battles.winner_id', $id)
            ->where('e.date', '>=', $s3StartDate)
            ->selectRaw('
                SUM(CASE WHEN match_battles.finish_type = "xtreme" THEN 1 ELSE 0 END) as xtremes,
                SUM(CASE WHEN match_battles.finish_type = "burst" THEN 1 ELSE 0 END) as bursts
            ')
            ->first();

        $puntos = $profile->points_x3 ?? 0;
        $primeros = $stats->primeros ?? 0;
        $torneos_jugados = $stats->torneos_jugados ?? 0;

        $xtremes = $finish_stats->xtremes ?? 0;
        $bursts = $finish_stats->bursts ?? 0;

        $titulos = [
            ['nombre' => 'LEYENDA VIVA', 'rareza' => 'Mítico', 'color' => 'red', 'desc' => 'Gana 5 torneos', 'unlocked' => $primeros >= 5],
            ['nombre' => 'CAMPEÓN REGIONAL', 'rareza' => 'Legendario', 'color' => 'yellow', 'desc' => 'Gana 1 torneo', 'unlocked' => $primeros >= 1],
            ['nombre' => 'ASESINO XTREME', 'rareza' => 'Épico', 'color' => 'purple', 'desc' => '15 Xtreme Finish', 'unlocked' => $xtremes >= 15],
            ['nombre' => 'TRITURADOR', 'rareza' => 'Épico', 'color' => 'purple', 'desc' => '15 Burst Finish', 'unlocked' => $bursts >= 15],
            ['nombre' => 'VETERANO S3', 'rareza' => 'Raro', 'color' => 'cyan', 'desc' => 'Juega 10 torneos', 'unlocked' => $torneos_jugados >= 10],
            ['nombre' => 'DESTRUCTOR', 'rareza' => 'Raro', 'color' => 'cyan', 'desc' => 'Consigue 50 Puntos', 'unlocked' => $puntos >= 50],
            ['nombre' => 'RANDOM', 'rareza' => 'Común', 'color' => 'gray', 'desc' => 'Inicia tu camino', 'unlocked' => true]
        ];

        $mejores_combos = \Illuminate\Support\Facades\DB::select("
            SELECT CONCAT(c.blade, ' ', c.ratchet, ' ', c.bit) as nombre, COUNT(*) as victorias
            FROM match_battles mb
            JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
            JOIN events e ON tm.event_id = e.id
            JOIN tournament_results c ON c.id = CASE WHEN mb.winner_id = tm.player1_id THEN mb.player1_combo_id ELSE mb.player2_combo_id END
            WHERE mb.winner_id = ? AND e.date >= ?
            GROUP BY nombre
            ORDER BY victorias DESC
            LIMIT 3
        ", [$id, $s3StartDate]);

        // 7. HISTORIAL DETALLADO DE TORNEOS JUGADOS (DECK + ESTADÍSTICAS + ENFRENTAMIENTOS)
        $eventosInscritos = \Illuminate\Support\Facades\DB::table('assist_user_event as aue')
            ->join('events as e', 'aue.event_id', '=', 'e.id')
            ->where('aue.user_id', $id)
            ->where('e.date', '>=', $s3StartDate)
            ->select('e.id', 'e.name', 'e.date', 'e.city', 'e.beys', 'aue.puesto')
            ->orderBy('e.date', 'desc')
            ->limit(15)
            ->get();

        $historial_torneos = [];
        foreach ($eventosInscritos as $ev) {
            // Deck registrado en ese torneo con su rendimiento real en match_battles
            $deckRows = \App\Models\TournamentResult::where('event_id', $ev->id)
                ->where('user_id', $id)
                ->whereNotNull('blade')
                ->orderBy('id', 'asc')
                ->get();

            $deckStats = [];
            foreach ($deckRows as $c) {
                $bStats = \Illuminate\Support\Facades\DB::selectOne("
                    SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN mb.winner_id = ? THEN 1 ELSE 0 END) as wins,
                        SUM(CASE WHEN mb.winner_id = ? AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtremes,
                        SUM(CASE WHEN mb.winner_id = ? AND mb.finish_type = 'burst' THEN 1 ELSE 0 END) as bursts,
                        SUM(CASE WHEN mb.winner_id = ? AND mb.finish_type = 'over' THEN 1 ELSE 0 END) as overs,
                        SUM(CASE WHEN mb.winner_id = ? AND mb.finish_type = 'spin' THEN 1 ELSE 0 END) as spins
                    FROM match_battles mb
                    WHERE mb.winner_id IS NOT NULL
                      AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND (mb.player1_combo_id = ? OR mb.player2_combo_id = ?)
                ", [$id, $id, $id, $id, $id, $c->id, $c->id]);

                $tot = (int) ($bStats->total ?? 0);
                $w = (int) ($bStats->wins ?? 0);
                $extras = array_filter([$c->over_blade, $c->assist_blade], fn($v) => !empty($v) && $v !== '-');
                $extraStr = !empty($extras) ? ' [' . implode('/', $extras) . ']' : '';

                $deckStats[] = [
                    'combo'   => trim("{$c->blade}{$extraStr} {$c->ratchet} {$c->bit}"),
                    'wins'    => $w,
                    'losses'  => max(0, $tot - $w),
                    'winrate' => $tot > 0 ? round(($w / $tot) * 100) : 0,
                    'xtremes' => (int) ($bStats->xtremes ?? 0),
                    'bursts'  => (int) ($bStats->bursts ?? 0),
                    'overs'   => (int) ($bStats->overs ?? 0),
                    'spins'   => (int) ($bStats->spins ?? 0),
                ];
            }

            // Partidas jugadas en ese torneo
            $matchesRows = \App\Models\TournamentMatch::with(['player1:id,name', 'player2:id,name'])
                ->where('event_id', $ev->id)
                ->where('status', 'completed')
                ->where(function ($q) use ($id) {
                    $q->where('player1_id', $id)->orWhere('player2_id', $id);
                })
                ->orderBy('round_number', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $duelos = [];
            $winsTorneo = 0;
            $lossesTorneo = 0;

            foreach ($matchesRows as $m) {
                $isBye = is_null($m->player2_id) || $m->player1_score == 99 || $m->player2_score == 99;
                if ($isBye) {
                    $winsTorneo++;
                    $duelos[] = [
                        'round'      => 'R' . $m->round_number,
                        'phase'      => strtoupper($m->bracket_type),
                        'rival'      => 'BYE (PASE DIRECTO)',
                        'my_score'   => 4,
                        'riv_score'  => 0,
                        'is_win'     => true,
                    ];
                    continue;
                }

                $amP1 = ($m->player1_id == $id);
                $myScore = $amP1 ? $m->player1_score : $m->player2_score;
                $rivScore = $amP1 ? $m->player2_score : $m->player1_score;
                $rivalName = $amP1 ? ($m->player2->name ?? 'Rival') : ($m->player1->name ?? 'Rival');
                $isWin = ($m->winner_id == $id);

                if ($isWin) $winsTorneo++;
                else $lossesTorneo++;

                $duelos[] = [
                    'round'     => 'R' . $m->round_number,
                    'phase'     => $m->bracket_type === 'swiss' ? 'SUIZO' : strtoupper(str_replace('_', ' ', $m->bracket_type)),
                    'rival'     => $rivalName,
                    'my_score'  => $myScore,
                    'riv_score' => $rivScore,
                    'is_win'    => $isWin,
                ];
            }

            $historial_torneos[] = [
                'event_id' => $ev->id,
                'name'     => $ev->name,
                'date'     => \Carbon\Carbon::parse($ev->date)->format('d/m/Y'),
                'city'     => $ev->city,
                'puesto'   => $ev->puesto ?: 'EN CURSO / PART.',
                'wins'     => $winsTorneo,
                'losses'   => $lossesTorneo,
                'deck'     => $deckStats,
                'matches'  => $duelos,
            ];
        }

        return response()->json([
            'id'                => $id,
            'nombre'            => $profile->user ? $profile->user->name : 'Desconocido',
            'region'            => $profile->region ? $profile->region->name : '---',
            'puntos_x3'         => $puntos,
            'equipo_nombre'     => $team ? $team->name : 'Ninguno',
            'equipo_puntos'     => $team ? $team->points_x3 : 0,
            'torneos_jugados'   => $torneos_jugados,
            'primeros'          => $primeros,
            'segundos'          => $stats->segundos ?? 0,
            'terceros'          => $stats->terceros ?? 0,
            'avatar_url'        => $profile->avatar_url ?? '/images/default.png',
            'marco_url'         => $profile->marco_url ?? null,
            'subtitulo'         => $profile->subtitulo ?? null,
            'titulos'           => $titulos,
            'mejores_combos'    => $mejores_combos,
            'historial_torneos' => $historial_torneos
        ]);
    }

    public function toggleJudge($eventId)
    {
        // Añadido 'revisor' a los permisos permitidos
        if (!Auth::user()->hasAnyRole(['admin', 'juez', 'revisor'])) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos de Staff.'], 403);
        }

        $event = Event::findOrFail($eventId);
        $userId = Auth::id();

        if ($event->judges()->where('user_id', $userId)->exists()) {
            $event->judges()->detach($userId);
            $msg = "Has salido del Staff de este torneo.";
        } else {
            $event->judges()->attach($userId);
            $msg = "Te has apuntado como Staff del torneo.";
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    // NUEVO: Obtener TODOS los equipos para el buscador de duelos
    public function getAllTeams()
    {
        $teams = Team::select('id', 'name')->orderBy('name', 'ASC')->get();
        return response()->json($teams);
    }
    // Obtiene el ranking individual de los Bladers
    public function getRanking(Request $request)
    {
        $limit = $request->get('limit', 100);

        // Buscamos los perfiles que tengan puntos, ordenados de mayor a menor
        $ranking = \App\Models\Profile::with(['user', 'region'])
            ->where('points_x3', '>', 0)
            ->orderBy('points_x3', 'DESC')
            ->limit($limit)
            ->get();

        return response()->json($ranking);
    }

    public function analizarDeckLab(Request $request)
    {
        $miDeck = $request->input('mi_deck', []);
        $rivalDeck = $request->input('rival_deck', []);

        $fechaCorte = \Carbon\Carbon::now()->subDays(60)->toDateString();

        // Top de combos más jugados ampliando el límite para poder filtrar los ilegales
        $topMetaRaw = DB::select("
            SELECT
                blade, ratchet, bit,
                CASE WHEN over_blade IS NULL OR over_blade = '' OR over_blade = '-' OR over_blade LIKE '%Selecciona%' THEN '-' ELSE over_blade END as over_blade,
                CASE WHEN assist_blade IS NULL OR assist_blade = '' OR assist_blade = '-' OR assist_blade LIKE '%Selecciona%' THEN '-' ELSE assist_blade END as assist_blade,
                COUNT(*) as usos
            FROM tournament_results tr
            JOIN events e ON tr.event_id = e.id
            WHERE tr.blade IS NOT NULL
              AND tr.blade NOT LIKE '%Selecciona%'
              AND tr.blade != 'DESCONOCIDO'
              AND e.date >= ?
            GROUP BY blade, ratchet, bit, over_blade, assist_blade
            ORDER BY usos DESC
            LIMIT 20
        ", [$fechaCorte]);

        // FILTRO DE LEGALIDAD 3ON3
        $topMetaBoss = [];
        $usedBlades = [];
        $usedRatchets = [];
        $usedBits = [];
        $usedOvers = [];
        $usedAssists = [];

        foreach ($topMetaRaw as $boss) {
            if (count($topMetaBoss) >= 3) break;

            $isLegal = true;
            if (in_array($boss->blade, $usedBlades)) $isLegal = false;
            if ($boss->ratchet !== 'UX Expanded' && in_array($boss->ratchet, $usedRatchets)) $isLegal = false;
            if ($boss->bit !== 'Without Bit' && in_array($boss->bit, $usedBits)) $isLegal = false;
            if ($boss->over_blade !== '-' && in_array($boss->over_blade, $usedOvers)) $isLegal = false;
            if ($boss->assist_blade !== '-' && in_array($boss->assist_blade, $usedAssists)) $isLegal = false;

            if ($isLegal) {
                $topMetaBoss[] = $boss;
                $usedBlades[] = $boss->blade;
                if ($boss->ratchet !== 'UX Expanded') $usedRatchets[] = $boss->ratchet;
                if ($boss->bit !== 'Without Bit') $usedBits[] = $boss->bit;
                if ($boss->over_blade !== '-') $usedOvers[] = $boss->over_blade;
                if ($boss->assist_blade !== '-') $usedAssists[] = $boss->assist_blade;
            }
        }

        if (empty($rivalDeck)) {
            foreach ($topMetaBoss as $boss) {
                $rivalDeck[] = [
                    'blade'        => $boss->blade,
                    'over_blade'   => $boss->over_blade !== '-' ? $boss->over_blade : '',
                    'assist_blade' => $boss->assist_blade !== '-' ? $boss->assist_blade : '',
                    'ratchet'      => $boss->ratchet,
                    'bit'          => $boss->bit,
                ];
            }
        }

        $formatComboName = function ($b) {
            $extras = array_filter([$b['over_blade'] ?? '', $b['assist_blade'] ?? ''], fn($v) => !empty($v) && $v !== '-');
            $extraStr = !empty($extras) ? ' [' . implode('/', $extras) . ']' : '';
            return trim(($b['blade'] ?? '') . $extraStr . ' ' . ($b['ratchet'] ?? '') . ' ' . ($b['bit'] ?? ''));
        };

        $getComboStats = function ($b) use ($formatComboName) {
            $blade = $b['blade'] ?? '';
            $ratchet = $b['ratchet'] ?? '';
            $bit = $b['bit'] ?? '';
            $over = (!empty($b['over_blade']) && $b['over_blade'] !== '-') ? $b['over_blade'] : null;
            $assist = (!empty($b['assist_blade']) && $b['assist_blade'] !== '-') ? $b['assist_blade'] : null;

            if (empty($blade)) return null;

            $overCond = $over ? "AND c.over_blade = " . DB::getPdo()->quote($over) : "";
            $assistCond = $assist ? "AND c.assist_blade = " . DB::getPdo()->quote($assist) : "";

            $exact = DB::selectOne("
                SELECT
                    COUNT(*) as total,
                    SUM(is_win) as wins,
                    SUM(is_loss) as losses,
                    ROUND(IFNULL(SUM(pts_won) / NULLIF(SUM(is_win), 0), 0), 2) as avg_dmg_dealt,
                    ROUND(IFNULL(SUM(pts_lost) / NULLIF(SUM(is_loss), 0), 0), 2) as avg_dmg_taken,
                    SUM(CASE WHEN is_win = 1 AND finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtreme_w,
                    SUM(CASE WHEN is_win = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as burst_w,
                    SUM(CASE WHEN is_win = 1 AND finish_type = 'over' THEN 1 ELSE 0 END) as over_w,
                    SUM(CASE WHEN is_win = 1 AND finish_type = 'spin' THEN 1 ELSE 0 END) as spin_w
                FROM (
                    SELECT
                        (mb.winner_id = c.user_id) as is_win,
                        (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                        CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                        CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                        mb.finish_type
                    FROM match_battles mb
                    JOIN tournament_results c ON mb.player1_combo_id = c.id
                    WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND c.blade = ? AND c.ratchet = ? AND c.bit = ? $overCond $assistCond
                    UNION ALL
                    SELECT
                        (mb.winner_id = c.user_id) as is_win,
                        (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                        CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                        CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                        mb.finish_type
                    FROM match_battles mb
                    JOIN tournament_results c ON mb.player2_combo_id = c.id
                    WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND c.blade = ? AND c.ratchet = ? AND c.bit = ? $overCond $assistCond
                ) as hist
            ", [$blade, $ratchet, $bit, $blade, $ratchet, $bit]);

            if (!$exact || $exact->total < 3) {
                $bladeOnly = DB::selectOne("
                    SELECT
                        COUNT(*) as total,
                        SUM(is_win) as wins,
                        SUM(is_loss) as losses,
                        ROUND(IFNULL(SUM(pts_won) / NULLIF(SUM(is_win), 0), 0), 2) as avg_dmg_dealt,
                        ROUND(IFNULL(SUM(pts_lost) / NULLIF(SUM(is_loss), 0), 0), 2) as avg_dmg_taken,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtreme_w,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as burst_w,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'over' THEN 1 ELSE 0 END) as over_w,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'spin' THEN 1 ELSE 0 END) as spin_w
                    FROM (
                        SELECT
                            (mb.winner_id = c.user_id) as is_win,
                            (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                            CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                            CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                            mb.finish_type
                        FROM match_battles mb
                        JOIN tournament_results c ON mb.player1_combo_id = c.id
                        WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme') AND c.blade = ?
                        UNION ALL
                        SELECT
                            (mb.winner_id = c.user_id) as is_win,
                            (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                            CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                            CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                            mb.finish_type
                        FROM match_battles mb
                        JOIN tournament_results c ON mb.player2_combo_id = c.id
                        WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme') AND c.blade = ?
                    ) as hist
                ", [$blade, $blade]);

                $data = $bladeOnly;
                $precision = 'Por Blade';
            } else {
                $data = $exact;
                $precision = 'Combo Exacto';
            }

            $total = max(1, (int) ($data->total ?? 0));
            $wins = (int) ($data->wins ?? 0);
            $wr = ($data->total > 0) ? round(($wins / $total) * 100, 1) : 50.0;

            return [
                'combo'         => $formatComboName($b),
                'short_name'    => $blade,
                'parts_label'   => trim(($over ? "$over " : '') . ($assist ? "$assist " : '') . "$ratchet $bit"),
                'blade'         => $blade,
                'over_blade'    => $over ?? '',
                'assist_blade'  => $assist ?? '',
                'ratchet'       => $ratchet,
                'bit'           => $bit,
                'sample_size'   => (int) ($data->total ?? 0),
                'wins'          => $wins,
                'losses'        => (int) ($data->losses ?? 0),
                'precision'     => $precision,
                'winrate'       => $wr,
                'avg_dmg_dealt' => (float) ($data->avg_dmg_dealt ?? 0),
                'avg_dmg_taken' => (float) ($data->avg_dmg_taken ?? 0),
                'xtreme_pct'    => $wins > 0 ? round(($data->xtreme_w / $wins) * 100) : 0,
                'burst_pct'     => $wins > 0 ? round(($data->burst_w / $wins) * 100) : 0,
                'over_pct'      => $wins > 0 ? round(($data->over_w / $wins) * 100) : 0,
                'spin_pct'      => $wins > 0 ? round(($data->spin_w / $wins) * 100) : 0,
            ];
        };

        $getHeadToHead = function ($cA, $cB) {
            $h2hExact = DB::selectOne("
                SELECT COUNT(*) as total, SUM(a_wins) as wins
                FROM (
                    SELECT (mb.winner_id = c1.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL
                      AND c1.blade = ? AND c1.ratchet = ? AND c1.bit = ?
                      AND c2.blade = ? AND c2.ratchet = ? AND c2.bit = ?
                    UNION ALL
                    SELECT (mb.winner_id = c2.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL
                      AND c2.blade = ? AND c2.ratchet = ? AND c2.bit = ?
                      AND c1.blade = ? AND c1.ratchet = ? AND c1.bit = ?
                ) as duelos
            ", [
                $cA['blade'], $cA['ratchet'], $cA['bit'], $cB['blade'], $cB['ratchet'], $cB['bit'],
                $cA['blade'], $cA['ratchet'], $cA['bit'], $cB['blade'], $cB['ratchet'], $cB['bit']
            ]);

            if ($h2hExact && $h2hExact->total >= 3) {
                return [
                    'winrate' => round(($h2hExact->wins / $h2hExact->total) * 100, 1),
                    'matches' => (int) $h2hExact->total,
                    'source'  => 'Exacto'
                ];
            }

            $h2hBlade = DB::selectOne("
                SELECT COUNT(*) as total, SUM(a_wins) as wins
                FROM (
                    SELECT (mb.winner_id = c1.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL AND c1.blade = ? AND c2.blade = ?
                    UNION ALL
                    SELECT (mb.winner_id = c2.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL AND c2.blade = ? AND c1.blade = ?
                ) as duelos_blade
            ", [$cA['blade'], $cB['blade'], $cA['blade'], $cB['blade']]);

            if ($h2hBlade && $h2hBlade->total >= 3) {
                $bladeWr = ($h2hBlade->wins / $h2hBlade->total) * 100;
                $adjustedWr = round(($bladeWr * 0.75) + ((50 + ($cA['winrate'] - $cB['winrate']) * 0.5) * 0.25), 1);
                return [
                    'winrate' => max(5, min(95, $adjustedWr)),
                    'matches' => (int) $h2hBlade->total,
                    'source'  => 'Blade'
                ];
            }

            $est = round(50 + (($cA['winrate'] - $cB['winrate']) * 0.8), 1);
            return [
                'winrate' => max(15, min(85, $est)),
                'matches' => 0,
                'source'  => 'Estimado'
            ];
        };

        $miDeckStats = [];
        foreach ($miDeck as $b) {
            if (!empty($b['blade'])) {
                $miDeckStats[] = $getComboStats($b);
            }
        }

        $rivalDeckStats = [];
        foreach ($rivalDeck as $b) {
            if (!empty($b['blade'])) {
                $rivalDeckStats[] = $getComboStats($b);
            }
        }

        $matrix = [];
        $totalWinProbSum = 0;
        $pairCount = 0;

        foreach ($miDeckStats as $myStat) {
            $row = [];
            foreach ($rivalDeckStats as $rivStat) {
                $duel = $getHeadToHead($myStat, $rivStat);
                $row[] = [
                    'my_combo'    => $myStat['combo'],
                    'rival_combo' => $rivStat['combo'],
                    'winrate'     => $duel['winrate'],
                    'matches'     => $duel['matches'],
                    'source'      => $duel['source'],
                ];
                $totalWinProbSum += $duel['winrate'];
                $pairCount++;
            }
            $matrix[] = $row;
        }

        $overallVsRival = $pairCount > 0 ? round($totalWinProbSum / $pairCount, 1) : 50.0;

        return response()->json([
            'success'          => true,
            'my_deck_stats'    => $miDeckStats,
            'rival_deck_stats' => $rivalDeckStats,
            'matrix'           => $matrix,
            'overall_winrate'  => $overallVsRival,
            'top_meta_boss'    => array_slice($topMetaBoss, 0, 3), // Devolvemos estrictamente los 3 filtrados
        ]);
    }

    public function addEventHelper(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $user = Auth::user();
        $isTeamEvent = in_array($event->beys, ['equipos', 'crew_battle']) || str_starts_with($event->name, 'Duelo Express:');

        $canManageStaff = $user->hasAnyRole(['admin', 'juez']) || (!$isTeamEvent && $event->created_by == $user->id);
        if (!$canManageStaff) {
            return response()->json(['success' => false, 'message' => '⛔ Solo los Jueces o el creador del evento pueden añadir ayudantes.'], 403);
        }

        $helperId = $request->input('user_id');
        if (!$helperId) {
            return response()->json(['success' => false, 'message' => 'Selecciona un usuario válido.'], 422);
        }

        $event->judges()->syncWithoutDetaching([$helperId]);

        return response()->json(['success' => true, 'message' => '🛠️ Ayudante asignado al Staff de este torneo.']);
    }

    public function removeEventHelper($eventId, $targetUserId)
    {
        $event = Event::findOrFail($eventId);
        $user = Auth::user();
        $isTeamEvent = in_array($event->beys, ['equipos', 'crew_battle']) || str_starts_with($event->name, 'Duelo Express:');

        $canManageStaff = $user->hasAnyRole(['admin', 'juez']) || (!$isTeamEvent && $event->created_by == $user->id);
        if (!$canManageStaff) {
            return response()->json(['success' => false, 'message' => '⛔ No tienes permisos para gestionar el Staff de este torneo.'], 403);
        }

        // Un creador normal no puede expulsar a un Juez Oficial o Admin
        $targetUser = \App\Models\User::findOrFail($targetUserId);
        if (!$user->hasAnyRole(['admin', 'juez']) && $targetUser->hasAnyRole(['admin', 'juez'])) {
            return response()->json(['success' => false, 'message' => '⛔ No puedes retirar a un Juez Oficial.'], 403);
        }

        $event->judges()->detach($targetUserId);

        return response()->json(['success' => true, 'message' => 'Usuario retirado del Staff del torneo.']);
    }
}
