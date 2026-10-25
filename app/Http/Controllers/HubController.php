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
        $hoy = Carbon::now()->format('Y-m-d');
        $userId = Auth::id();
        $user = Auth::user();
        
        // El revisor y el admin tienen llaves maestras silenciosas
        $isJuez = $user ? $user->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor']) : false;
        $isGlobalStaff = $user ? $user->hasAnyRole(['admin', 'revisor']) : false;
        
        $query = Event::with(['region', 'users', 'judges.profile']);

        if ($request->filled('tipo') && $request->tipo === 'historial') {
            $query->whereIn('status', ['COMPLETED', 'CLOSED', 'PENDING', 'REVIEW'])
                  ->orderBy('date', 'desc')
                  ->limit(30);
        } else {
            $query->whereIn('status', ['OPEN', 'LIVE'])
                  ->whereDate('date', '>=', $hoy)
                  ->orderBy('date', 'asc')
                  ->orderBy('time', 'asc');
        }

        if ($request->filled('tipo') && $request->tipo !== 'historial') {
            if ($request->tipo === 'express') {
                $query->where('name', 'LIKE', 'Duelo Express:%');
            } elseif ($request->tipo === 'crew_battle') {
                $query->where('beys', 'crew_battle')->where('name', 'NOT LIKE', 'Duelo Express:%');
            } else {
                $query->where('beys', $request->tipo)->where('name', 'NOT LIKE', 'Duelo Express:%');
            }
        } else {
            $query->where('name', 'NOT LIKE', 'Duelo Express:%');
        }

        if ($request->filled('region')) {
            $query->where('region_id', $request->region);
        }

        if ($request->filled('personal')) {
            if ($request->personal === 'inscrito') {
                $query->whereHas('users', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
            } elseif ($request->personal === 'juez' && $isJuez) {
                $query->whereHas('judges', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
            }
        }

        $torneos = $query->get();

        $torneos->map(function ($torneo) use ($userId, $isJuez, $isGlobalStaff) {
            $torneo->is_inscribed = $torneo->users->contains('id', $userId);
            $torneo->user_is_judge = $isJuez;
            $torneo->user_is_revisor = $isGlobalStaff;
            $torneo->total_inscritos = $torneo->users->count();

            $torneo->jueces_asignados = $torneo->judges->map(function ($juez) {
                return [
                    'id' => $juez->id,
                    'name' => $juez->name,
                    'avatar' => $juez->profile->avatar_url ?? '/images/default.png'
                ];
            });

            // MAGIA REVISOR: Si está asignado como juez O si tiene el rol 'revisor'/'admin', puede entrar a arbitrar cualquier evento sin salir en la lista de staff
            $torneo->is_judge_assigned = $torneo->judges->contains('id', $userId) || $isGlobalStaff;

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
        $regiones = Region::orderBy('name')->get(['id', 'name']);
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
        $userId = Auth::id();
        $isStaff = Auth::check() && Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro']);

        // NUEVO: Si es Juez/Admin y está editando el deck de otro jugador
        if ($isStaff && $request->filled('target_user_id')) {
            $userId = $request->input('target_user_id');
        }

        $date = Carbon::parse($event->date);
        $isAlreadyInscribed = $event->users()->where('user_id', $userId)->exists();

        // Solo aplicamos límites semanales/mensuales si no está inscrito y NO lo está forzando un Juez
        if (!$isAlreadyInscribed && !$request->filled('target_user_id') && in_array($event->beys, ['ranking', 'rankingplus'])) {
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

        $event->users()->syncWithoutDetaching([$userId]);

        if ($request->has('is_tentative') && $request->is_tentative == 'true') {
            TournamentResult::where('user_id', $userId)->where('event_id', $eventId)->delete();
            return response()->json(['success' => true, 'message' => '✅ Plaza asegurada (Inscripción Tentativa).']);
        }

        // Borramos el deck anterior de este evento para no duplicar ni mezclar piezas
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
                'blade'           => trim($blade),
                'over_blade'      => !empty($overBlades[$index]) ? trim($overBlades[$index]) : null,
                'assist_blade'    => !empty($assistBlades[$index]) ? trim($assistBlades[$index]) : null,
                'ratchet'         => isset($ratchets[$index]) ? trim($ratchets[$index]) : null,
                'bit'             => isset($bits[$index]) ? trim($bits[$index]) : null,
                'victorias'       => 0,
                'derrotas'        => 0,
                'puntos_ganados'  => 0,
                'puntos_perdidos' => 0,
            ]);
        }

        return response()->json(['success' => true, 'message' => '✅ ¡Deck de combate registrado!']);
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
        if (!Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['message' => '⛔ Acceso denegado'], 403);
        }

        $event = Event::findOrFail($eventId);
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
                AVG(points_won) as puntos_ganados_por_combate,
                AVG(points_lost) as puntos_perdidos_por_combate,
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
        // Definimos la fecha de inicio de la S3
        $s3StartDate = '2026-09-01';

        // 1. Obtener datos básicos
        $profile = \App\Models\Profile::with(['user', 'region'])->where('user_id', $id)->first();
        if (!$profile) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        // 2. Buscar equipo
        $team = \Illuminate\Support\Facades\DB::table('team_user')
            ->join('teams', 'team_user.team_id', '=', 'teams.id')
            ->where('team_user.user_id', $id)
            ->select('teams.name', 'teams.points_x3')
            ->first();

        // 3. Calcular estadísticas de torneos (FILTRADO POR FECHA S3)
        $stats = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', $id)
            ->where('events.date', '>=', $s3StartDate)
            ->selectRaw('
                SUM(CASE WHEN assist_user_event.puesto != "nopresentado" AND assist_user_event.puesto IS NOT NULL AND assist_user_event.puesto != "" THEN 1 ELSE 0 END) as torneos_jugados,
                SUM(CASE WHEN assist_user_event.puesto = "primero" THEN 1 ELSE 0 END) as primeros,
                SUM(CASE WHEN assist_user_event.puesto = "segundo" THEN 1 ELSE 0 END) as segundos,
                SUM(CASE WHEN assist_user_event.puesto = "tercero" THEN 1 ELSE 0 END) as terceros
            ')
            ->first();
            
        // 4. Calcular estadísticas de combate (Estilo de juego) (FILTRADO POR FECHA S3)
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

        // 5. SISTEMA DE LOGROS AMPLIADO
        $titulos = [
            [
                'nombre' => 'LEYENDA VIVA',
                'rareza' => 'Mítico',
                'color' => 'red',
                'desc' => 'Gana 5 torneos',
                'unlocked' => $primeros >= 5
            ],
            [
                'nombre' => 'CAMPEÓN REGIONAL',
                'rareza' => 'Legendario',
                'color' => 'yellow',
                'desc' => 'Gana 1 torneo',
                'unlocked' => $primeros >= 1
            ],
            [
                'nombre' => 'ASESINO XTREME',
                'rareza' => 'Épico',
                'color' => 'purple',
                'desc' => '15 Xtreme Finish',
                'unlocked' => $xtremes >= 15
            ],
            [
                'nombre' => 'TRITURADOR',
                'rareza' => 'Épico',
                'color' => 'purple',
                'desc' => '15 Burst Finish',
                'unlocked' => $bursts >= 15
            ],
            [
                'nombre' => 'VETERANO S3',
                'rareza' => 'Raro',
                'color' => 'cyan',
                'desc' => 'Juega 10 torneos',
                'unlocked' => $torneos_jugados >= 10
            ],
            [
                'nombre' => 'DESTRUCTOR',
                'rareza' => 'Raro',
                'color' => 'cyan',
                'desc' => 'Consigue 50 Puntos',
                'unlocked' => $puntos >= 50
            ],
            [
                'nombre' => 'RANDOM',
                'rareza' => 'Común',
                'color' => 'gray',
                'desc' => 'Inicia tu camino',
                'unlocked' => true // Siempre desbloqueado por defecto
            ]
        ];

        // 6. TOP 3 COMBOS ESTRELLA (FILTRADO POR FECHA S3)
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

        return response()->json([
            'id' => $id,
            'nombre' => $profile->user ? $profile->user->name : 'Desconocido',
            'region' => $profile->region ? $profile->region->name : '---',
            'puntos_x3' => $puntos,
            'equipo_nombre' => $team ? $team->name : 'Ninguno',
            'equipo_puntos' => $team ? $team->points_x3 : 0,
            'torneos_jugados' => $torneos_jugados,
            'primeros' => $primeros,
            'segundos' => $stats->segundos ?? 0,
            'terceros' => $stats->terceros ?? 0,
            'avatar_url' => $profile->avatar_url ?? '/images/default.png',
            'marco_url' => $profile->marco_url ?? null,
            'subtitulo' => $profile->subtitulo ?? null,
            'titulos' => $titulos,
            'mejores_combos' => $mejores_combos
        ]);
    }

    // NUEVO: Permite a los jueces auto-asignarse o retirarse de un torneo
        public function toggleJudge($eventId)
        {
            if (!Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
                return response()->json(['message' => '⛔ Acceso denegado'], 403);
            }

            $event = Event::findOrFail($eventId);
            $event->judges()->toggle(Auth::id()); // Si está lo quita, si no está lo pone

            $isAssigned = $event->judges()->where('user_id', Auth::id())->exists();
            $msg = $isAssigned ? '✅ Te has asignado como Juez.' : '❌ Te has retirado del arbitraje.';

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
}