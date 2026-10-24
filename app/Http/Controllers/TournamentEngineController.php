<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TournamentMatch;
use App\Services\Tournament\SwissEngine; // Importamos el motor
use Illuminate\Http\Request;

class TournamentEngineController extends Controller
{
    // Carga los datos principales para el panel del juez
    public function getArenaDashboard(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $allMatches = TournamentMatch::where('event_id', $eventId)->get();
        $maxRound = $allMatches->max('round_number') ?? 0;

        // NUEVO: Detectamos si hay partidas de eliminatoria de forma global
        $isBracket = $allMatches->where('bracket_type', '!=', 'swiss')->isNotEmpty();

        $roundsStatus = [];
        $firstPendingRound = $maxRound; 
        
        for ($i = 1; $i <= $maxRound; $i++) {
            $matchesInRound = $allMatches->where('round_number', $i);
            $pendingCount = $matchesInRound->where('status', '!=', 'completed')->count();
            
            if ($pendingCount > 0) {
                $roundsStatus[$i] = 'pending';
                if ($firstPendingRound === $maxRound || $i < $firstPendingRound) {
                    $firstPendingRound = $i;
                }
            } else {
                $roundsStatus[$i] = 'completed';
            }
        }

        $requestedRound = $request->query('round');
        if (!$requestedRound || $requestedRound == 'null') {
            $requestedRound = $firstPendingRound ?: 1;
        }

        // Filtro limpio: Oculta Fantasmas y Byes (partidas completadas sin rival), 
        // pero MUESTRA las partidas futuras que están esperando a que lleguen los rivales.
        $matches = TournamentMatch::with(['player1.profile', 'player2.profile', 'team1', 'team2', 'lockedByUser'])
            ->where('event_id', $eventId)
            ->where('round_number', $requestedRound)
            ->where('player1_score', '!=', 99) 
            ->where('player2_score', '!=', 99)
            ->where(function ($query) {
                // 1. Partidas 1vs1 listas para jugar (ambos rivales presentes)
                $query->where(function ($q) {
                    $q->whereNotNull('player1_id')->whereNotNull('player2_id');
                })
                // 2. Partidas 1vs1 en espera de rivales (status pending)
                ->orWhere(function ($q) {
                    $q->where('bracket_type', '!=', 'swiss')
                      ->where('status', 'pending');
                })
                // 3. MAGIA: Las partidas de Equipos SIEMPRE se muestran (pendientes o completadas)
                ->orWhereNotNull('team1_id'); 
            })
            ->orderBy('bracket_type', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'event_name' => $event->name,
            'current_round' => (int)$requestedRound,
            'max_round' => (int)$maxRound,
            'rounds_status' => $roundsStatus,
            'is_bracket' => $isBracket, // Enviamos el chivato al frontend
            'matches' => $matches
        ]);
    }

    public function generateRound($eventId, \App\Services\Tournament\SwissEngine $engine)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $event = Event::findOrFail($eventId);
        
        // MAGIA: Si el evento es de equipos, lanzamos el Round Robin
        if (in_array($event->beys, ['equipos', 'crew_battle'])) {
            
            // 1. Sacamos los IDs de los equipos de los jugadores que están apuntados y NO están dropeados
            $validTeams = \Illuminate\Support\Facades\DB::table('assist_user_event')
                ->join('team_user', 'assist_user_event.user_id', '=', 'team_user.user_id')
                ->where('assist_user_event.event_id', $event->id)
                ->where(function($q) {
                    $q->whereNull('assist_user_event.puesto')
                      ->orWhere('assist_user_event.puesto', '!=', 'dropped');
                })
                ->pluck('team_user.team_id')
                ->unique()  // Quitamos duplicados (varios jugadores del mismo equipo)
                ->filter()  // Quitamos si hay algún nulo (gente sin equipo)
                ->values()  // Reindexamos el array
                ->toArray();

            // 2. Comprobación de seguridad: ¿Hay al menos 2 clanes para pelear?
            if (count($validTeams) < 2) {
                return response()->json(['success' => false, 'message' => '⛔ No hay suficientes equipos activos inscritos para generar cruces.'], 400);
            }

            // 3. Le pasamos el evento y la lista limpia al motor de equipos
            return $this->generateTeamRoundRobin($event, $validTeams);
        }
        
        // Si es un torneo normal, sigue usando el motor que tenías
        $result = $engine->generateNextRound($event);
        return response()->json($result);
    }

    private function generateTeamRoundRobin($event, $validTeams) // <-- Añadido $validTeams
    {
        // 1. PRECARGAR DATOS DEL MES ANTES DEL BUCLE PARA NO SATURAR LA BASE DE DATOS
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth();

        $teamMatchCounts = [];
        $playedMatchups = [];
        $matchesCreated = 0; // <-- Añadido contador de duelos válidos

        foreach ($validTeams as $teamId) {
            // Contar cuántos duelos lleva cada equipo este mes
            $teamMatchCounts[$teamId] = \Illuminate\Support\Facades\DB::table('teams_versus')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function($q) use ($teamId) {
                    $q->where('team_id_1', $teamId)->orWhere('team_id_2', $teamId);
                })->count();
        }

        // Buscar qué cruces exactos se han dado ya este mes
        $allVersusThisMonth = \Illuminate\Support\Facades\DB::table('teams_versus')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('matchup', 'crew_battle')
            ->get();

        foreach ($allVersusThisMonth as $v) {
            $playedMatchups[$v->team_id_1 . '_' . $v->team_id_2] = true;
            $playedMatchups[$v->team_id_2 . '_' . $v->team_id_1] = true;
        }

        // 2. EL BUCLE DE GENERACIÓN DE CRUCES
        for ($i = 0; $i < count($validTeams) - 1; $i++) {
            for ($j = $i + 1; $j < count($validTeams); $j++) {
                
                $t1 = $validTeams[$i];
                $t2 = $validTeams[$j];

                // Comprobamos los límites en tiempo real
                $alreadyPlayed = isset($playedMatchups[$t1 . '_' . $t2]);
                $t1LimitReached = $teamMatchCounts[$t1] >= 5;
                $t2LimitReached = $teamMatchCounts[$t2] >= 5;

                $status = 'pending';
                
                // Si incumplen normas, nacen cancelados
                if ($alreadyPlayed || $t1LimitReached || $t2LimitReached) {
                    $status = 'cancelled';
                }

                \App\Models\TournamentMatch::create([
                    'event_id' => $event->id,
                    'round_number' => 1, 
                    'bracket_type' => 'round_robin',
                    'team1_id' => $t1,
                    'team2_id' => $t2,
                    'player1_id' => null, 
                    'player2_id' => null, 
                    'status' => $status, // <--- Estado dinámico (pending o cancelled)
                    'player1_score' => 0,
                    'player2_score' => 0,
                ]);

                // 3. ACTUALIZAR CONTADORES EN VIVO
                if ($status === 'pending') {
                    $teamMatchCounts[$t1]++;
                    $teamMatchCounts[$t2]++;
                    $playedMatchups[$t1 . '_' . $t2] = true;
                    $playedMatchups[$t2 . '_' . $t1] = true;
                    $matchesCreated++; // <-- Sumamos +1 al contador
                }
            }
        }

        return response()->json(['success' => true, 'message' => "¡Liguilla generada! $matchesCreated duelos de clanes válidos listos."]);
    }

    // Obtiene los detalles de una partida y los Decks de los jugadores (o de los Equipos)
    public function getMatchDetails($matchId)
    {
        $match = \App\Models\TournamentMatch::with(['player1', 'player2', 'team1', 'team2'])->findOrFail($matchId);

        $battles = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->orderBy('id', 'asc')
            ->get();

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

        if ($isTeamMatch) {
            // LÓGICA DE EQUIPOS: Sacamos todos los beys de los jugadores ACTIVOS del equipo
            $team1Users = $this->getActiveTeamMembers($match->event_id, $match->team1_id);
            $p1Deck = \App\Models\TournamentResult::where('event_id', $match->event_id)
                ->whereIn('user_id', $team1Users)
                ->whereNotNull('blade')
                ->orderBy('user_id') 
                ->get();

            $team2Users = $this->getActiveTeamMembers($match->event_id, $match->team2_id);
            $p2Deck = \App\Models\TournamentResult::where('event_id', $match->event_id)
                ->whereIn('user_id', $team2Users)
                ->whereNotNull('blade')
                ->orderBy('user_id')
                ->get();
            
            // Calculamos matemáticamente cuántas partidas máximas hay que jugar (Ej: 3 vs 5 = 5 partidas)
            $matchesToPlay = max($team1Users->count(), $team2Users->count());
            $matchesPlayed = $battles->where('finish_type', '!=', 'warning_p1')->where('finish_type', '!=', 'warning_p2')->where('finish_type', '!=', 'penalty')->count();
            
            // Enviamos un flag extra para avisar de que es un torneo por equipos
            return response()->json([
                'match' => $match,
                'p1_deck' => $p1Deck,
                'p2_deck' => $p2Deck,
                'battles' => $battles,
                'is_team_match' => true,
                'matches_played' => $matchesPlayed,
                'matches_to_play' => $matchesToPlay,
                'team1_size' => $team1Users->count(),
                'team2_size' => $team2Users->count()
            ]);

        } else {
            // LÓGICA NORMAL (1vs1)
            $p1Deck = \App\Models\TournamentResult::where('event_id', $match->event_id)
                ->where('user_id', $match->player1_id)
                ->whereNotNull('blade')
                ->orderBy('id', 'asc')
                ->get();

            $p2Deck = \App\Models\TournamentResult::where('event_id', $match->event_id)
                ->where('user_id', $match->player2_id)
                ->whereNotNull('blade')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'match' => $match,
                'p1_deck' => $p1Deck,
                'p2_deck' => $p2Deck,
                'battles' => $battles,
                'is_team_match' => false
            ]);
        }
    }

    // Registra un choque (MatchBattle) y actualiza el marcador
    public function storeBattle(Request $request, $matchId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $match = TournamentMatch::findOrFail($matchId);
        
        // Puntos según la regla oficial
        $points = 0;
        $finishType = $request->finish_type; // 'spin', 'over', 'burst', 'xtreme'
        
        if ($finishType === 'spin') $points = 1;
        if ($finishType === 'over' || $finishType === 'burst') $points = 2;
        if ($finishType === 'xtreme') $points = 3;

        // Registrar el choque en la tabla de metadatos
        \App\Models\MatchBattle::create([
            'tournament_match_id' => $match->id,
            'player1_combo_id' => $request->player1_combo_id,
            'player2_combo_id' => $request->player2_combo_id,
            'winner_id' => $request->winner_id,
            'finish_type' => $finishType,
            'points_awarded' => $points,
        ]);

        // Actualizar marcador general de la partida
        if (!is_null($match->team1_id) && !is_null($match->team2_id)) {
            // En equipos, el Frontend nos mandará 'team1' o 'team2' en un campo oculto, o lo sacamos del team del jugador ganador
            $winnerTeam = \Illuminate\Support\Facades\DB::table('team_user')->where('user_id', $request->winner_id)->value('team_id');
            if ($winnerTeam == $match->team1_id) $match->player1_score += $points;
            if ($winnerTeam == $match->team2_id) $match->player2_score += $points;
        } else {
            // Normal (1vs1)
            if ($request->winner_id == $match->player1_id) $match->player1_score += $points;
            elseif ($request->winner_id == $match->player2_id) $match->player2_score += $points;
        }
        
        // MAGIA: Comprobar condición de victoria según el tipo de torneo
        $matchCompleted = false;
        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

        if ($isTeamMatch) {
            // REGLAS EQUIPOS: Calculamos límite con los usuarios activos
            $allBattles = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->get();
            $matchesPlayed = $allBattles->where('finish_type', '!=', 'warning_p1')->where('finish_type', '!=', 'warning_p2')->where('finish_type', '!=', 'penalty')->count();
            
            $team1UsersCount = $this->getActiveTeamMembers($match->event_id, $match->team1_id)->count();
            $team2UsersCount = $this->getActiveTeamMembers($match->event_id, $match->team2_id)->count();
            
            $limit = max($team1UsersCount, $team2UsersCount);

            if ($matchesPlayed >= $limit) {
                // Quien tenga más puntos totales, gana el duelo
                $match->winner_id = $match->player1_score > $match->player2_score ? $match->team1_id : ($match->player2_score > $match->player1_score ? $match->team2_id : null);
                $match->status = 'completed';
                $matchCompleted = true;
            } else {
                $match->status = 'playing';
            }
            
        } else {
            // REGLAS NORMALES: El primero que llega a 4
            $limit = 4;
            if ($match->player1_score >= $limit || $match->player2_score >= $limit) {
                $match->winner_id = $match->player1_score >= $limit ? $match->player1_id : $match->player2_id;
                $match->status = 'completed';
                $matchCompleted = true;
            } else {
                $match->status = 'playing';
            }
        }

        $match->save();

        // Si la partida terminó y estamos en fase eliminatoria, avanzamos a los jugadores
        if ($matchCompleted && $match->bracket_type !== 'swiss' && $match->bracket_type !== 'round_robin') {
            $engine = new \App\Services\Tournament\DoubleEliminationEngine();
            $engine->progressMatch($match);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Resultado registrado', 
            'match_completed' => $matchCompleted,
            'p1_score' => $match->player1_score,
            'p2_score' => $match->player2_score
        ]);
    }

    // Calcula la clasificación en vivo (Puntos y Buchholz)
    public function getStandings($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        
        $matches = \App\Models\TournamentMatch::where('event_id', $eventId)
            ->where('status', 'completed')
            ->get();
            
        $isTeamMatch = in_array($event->beys, ['equipos', 'crew_battle']);
        $stats = [];

        if ($isTeamMatch) {
            // --- LÓGICA DE CLASIFICACIÓN DE EQUIPOS ---
            // Sacamos los IDs de los equipos que están jugando
            $teamIds = $matches->pluck('team1_id')->merge($matches->pluck('team2_id'))->unique()->filter();
            $teams = \App\Models\Team::whereIn('id', $teamIds)->get();

            // 1. Inicializamos los marcadores a 0
            foreach ($teams as $t) {
                $stats[$t->id] = [
                    'id' => $t->id,
                    'name' => $t->name,
                    'avatar' => $t->logo ?? '/images/default.png', // Ajusta si tienes logo de equipo
                    'points' => 0,
                    'buchholz' => 0,
                    'opponents' => []
                ];
            }

            // 2. Sumamos los puntos de equipos
            foreach ($matches as $m) {
                if ($m->winner_id !== null && isset($stats[$m->winner_id])) {
                    $stats[$m->winner_id]['points'] += 3; // 3 Puntos por ganar el Duelo
                }
                
                if ($m->team1_id && $m->team2_id) { 
                    if (isset($stats[$m->team1_id]) && isset($stats[$m->team2_id])) {
                        $stats[$m->team1_id]['opponents'][] = $m->team2_id;
                        $stats[$m->team2_id]['opponents'][] = $m->team1_id;
                    }
                }
            }

        } else {
            // --- LÓGICA ORIGINAL PARA 1VS1 ---
            $players = $event->users()->with('profile')->get();

            foreach ($players as $p) {
                $stats[$p->id] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'avatar' => $p->profile->avatar_url ?? '/images/default.png',
                    'points' => 0,
                    'buchholz' => 0,
                    'opponents' => []
                ];
            }

            foreach ($matches as $m) {
                if ($m->winner_id !== null) {
                    if ($m->player1_id && isset($stats[$m->player1_id]) && $m->winner_id == $m->player1_id) {
                        $stats[$m->player1_id]['points'] += 3;
                    } 
                    elseif ($m->player2_id && isset($stats[$m->player2_id]) && $m->winner_id == $m->player2_id) {
                        $stats[$m->player2_id]['points'] += 3;
                    }
                }
                
                if ($m->player1_id && $m->player2_id) { 
                    if (isset($stats[$m->player1_id]) && isset($stats[$m->player2_id])) {
                        $stats[$m->player1_id]['opponents'][] = $m->player2_id;
                        $stats[$m->player2_id]['opponents'][] = $m->player1_id;
                    }
                }
            }
        }

        // --- CÁLCULO COMÚN DE BUCHHOLZ Y ORDENACIÓN ---
        foreach ($stats as $id => &$data) {
            foreach ($data['opponents'] as $oppId) {
                $data['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
        }

        usort($stats, function($a, $b) {
            if ($a['points'] === $b['points']) {
                return $b['buchholz'] <=> $a['buchholz']; // Desempate
            }
            return $b['points'] <=> $a['points'];
        });

        // Reindexar array para que el frontend no tenga problemas
        $stats = array_values($stats);

        return response()->json([
            'success' => true,
            'standings' => $stats
        ]);
    }

    public function generateTop(Request $request, $eventId, \App\Services\Tournament\DoubleEliminationEngine $engine)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) return response()->json(['success' => false], 403);
        $event = \App\Models\Event::findOrFail($eventId);
        return response()->json($engine->generateTopCut($event, $request->top_size));
    }

    public function showBracket($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        
        // Cargamos las partidas con los datos de los perfiles de los jugadores
        $matches = \App\Models\TournamentMatch::with(['player1', 'player2'])
            ->where('event_id', $eventId)
            ->where('bracket_type', '!=', 'swiss')
            ->where(function($query) {
                // Excluimos las partidas donde AMBOS son fantasmas
                $query->where('player1_score', '!=', 99)
                      ->orWhere('player2_score', '!=', 99);
            })
            ->orderBy('round_number')
            ->orderBy('id')
            ->get();

        // Agrupamos por tipo y ronda
        $winnersBracket = $matches->where('bracket_type', 'winners')->groupBy('round_number');
        $losersBracket = $matches->where('bracket_type', 'losers')->groupBy('round_number');
        $finals = $matches->whereIn('bracket_type', ['grand_final', 'grand_final_reset']);

        return view('hub.bracket', compact('event', 'winnersBracket', 'losersBracket', 'finals'));
    }

    public function renderBracketHtml($eventId)
    {
        $matches = \App\Models\TournamentMatch::with(['player1', 'player2'])
            ->where('event_id', $eventId)
            ->where('bracket_type', '!=', 'swiss')
            ->where(function($query) {
                // Excluimos las partidas donde AMBOS son fantasmas
                $query->where('player1_score', '!=', 99)
                      ->orWhere('player2_score', '!=', 99);
            })
            ->orderBy('round_number')
            ->orderBy('id')
            ->get();

        $winnersBracket = $matches->where('bracket_type', 'winners')->groupBy('round_number');
        $losersBracket = $matches->where('bracket_type', 'losers')->groupBy('round_number');
        $finals = $matches->whereIn('bracket_type', ['grand_final', 'grand_final_reset']);

        return view('hub.partials.bracket_content', compact('winnersBracket', 'losersBracket', 'finals'));
    }

    public function getMiDeck(Request $request, $eventId)
    {
        $userId = auth()->id();
        
        // Si un Juez pide ver el deck de otro jugador concreto
        if ($request->filled('user_id') && auth()->check() && auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            $userId = $request->query('user_id');
        }

        $deck = \App\Models\TournamentResult::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->whereNotNull('blade')
            ->orderBy('id', 'asc') // <-- CRÍTICO: Evita que el 9-60 se cruce con otro bey
            ->get();
            
        return response()->json($deck);
    }

    public function deshacerUltimoRegistro($matchId)
    {
        $match = \App\Models\TournamentMatch::findOrFail($matchId);
        
        $lastBattle = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$lastBattle) {
            return response()->json(['success' => false, 'message' => 'No hay registros para deshacer.']);
        }
        
        \Illuminate\Support\Facades\DB::table('match_battles')->where('id', $lastBattle->id)->delete();
        
        // MAGIA: Recalcular puntos detectando si es Equipo o 1vs1
        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
        
        if ($isTeamMatch) {
            $team1Users = $this->getActiveTeamMembers($match->event_id, $match->team1_id);
            $team2Users = $this->getActiveTeamMembers($match->event_id, $match->team2_id);
            
            $p1_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->whereIn('winner_id', $team1Users)->sum('points_awarded') ?? 0;
            $p2_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->whereIn('winner_id', $team2Users)->sum('points_awarded') ?? 0;
        } else {
            $p1_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player1_id)->sum('points_awarded') ?? 0;
            $p2_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player2_id)->sum('points_awarded') ?? 0;
        }
            
        $match->player1_score = $p1_score;
        $match->player2_score = $p2_score;
        
        if ($match->status === 'completed') {
            $match->status = 'playing';
        }
        $match->save();
        
        $w1 = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('finish_type', 'warning_p1')->exists();
        $w2 = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('finish_type', 'warning_p2')->exists();

        return response()->json([
            'success' => true,
            'message' => 'Última acción eliminada.',
            'p1_score' => $p1_score,
            'p2_score' => $p2_score,
            'w1_active' => $w1,
            'w2_active' => $w2
        ]);
    }

    public function asignarWarning($matchId, \Illuminate\Http\Request $request)
    {
        $match = \App\Models\TournamentMatch::findOrFail($matchId);
        $playerKey = $request->player; // 'p1' o 'p2'
        
        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

        // Si es de equipos, le regalamos el punto a un jugador ACTIVO del equipo rival
        if ($isTeamMatch) {
            $beneficiaryId = ($playerKey === 'p1') ? 
                $this->getActiveTeamMembers($match->event_id, $match->team2_id)->first() : 
                $this->getActiveTeamMembers($match->event_id, $match->team1_id)->first();
        } else {
            $beneficiaryId = ($playerKey === 'p1') ? $match->player2_id : $match->player1_id;
        }

        $warningType = 'warning_' . $playerKey;

        $existingWarning = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->where('finish_type', $warningType)
            ->first();

        if ($existingWarning) {
            \Illuminate\Support\Facades\DB::table('match_battles')->where('id', $existingWarning->id)->delete();
            
            \Illuminate\Support\Facades\DB::table('match_battles')->insert([
                'tournament_match_id' => $matchId,
                'winner_id' => $beneficiaryId, 
                'player1_combo_id' => null,
                'player2_combo_id' => null,
                'finish_type' => 'penalty', 
                'points_awarded' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $msg = "2º Warning: ¡1 Punto para el rival!";
            $isActive = false; 
        } else {
            \Illuminate\Support\Facades\DB::table('match_battles')->insert([
                'tournament_match_id' => $matchId,
                'winner_id' => $beneficiaryId, 
                'player1_combo_id' => null,
                'player2_combo_id' => null,
                'finish_type' => $warningType,
                'points_awarded' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $msg = "1º Warning anotado.";
            $isActive = true; 
        }

        // Recalcular puntos
        if ($isTeamMatch) {
            $team1Users = $this->getActiveTeamMembers($match->event_id, $match->team1_id);
            $team2Users = $this->getActiveTeamMembers($match->event_id, $match->team2_id);
            
            $p1_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->whereIn('winner_id', $team1Users)->sum('points_awarded') ?? 0;
            $p2_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->whereIn('winner_id', $team2Users)->sum('points_awarded') ?? 0;
        } else {
            $p1_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player1_id)->sum('points_awarded') ?? 0;
            $p2_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player2_id)->sum('points_awarded') ?? 0;
        }
        
        $match->player1_score = $p1_score;
        $match->player2_score = $p2_score;

        // Comprobar límite de victoria (Batallas máximas vs 4 puntos normales)
        $matchCompleted = false;
        
        if ($isTeamMatch) {
            $limit = max(count($team1Users), count($team2Users));
            $allBattles = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->get();
            $matchesPlayed = $allBattles->where('finish_type', '!=', 'warning_p1')->where('finish_type', '!=', 'warning_p2')->where('finish_type', '!=', 'penalty')->count();
            
            if ($matchesPlayed >= $limit) {
                $match->status = 'completed';
                $match->winner_id = $p1_score > $p2_score ? $match->team1_id : ($p2_score > $p1_score ? $match->team2_id : null);
                $matchCompleted = true;
                $msg .= " ¡Duelo Finalizado!";
            }
        } else {
            if ($p1_score >= 4 || $p2_score >= 4) {
                $match->status = 'completed';
                $match->winner_id = $p1_score >= 4 ? $match->player1_id : $match->player2_id;
                $matchCompleted = true;
                $msg .= " ¡Partida finalizada!";
            }
        }

        $match->save();

        if ($matchCompleted && $match->bracket_type !== 'swiss' && $match->bracket_type !== 'round_robin') {
            $engine = new \App\Services\Tournament\DoubleEliminationEngine();
            $engine->progressMatch($match);
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'p1_score' => $p1_score,
            'p2_score' => $p2_score,
            'warning_active' => $isActive,
            'match_completed' => $matchCompleted
        ]);
    }

    public function getJugadoresTorneo($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        
        $jugadoresRaw = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->join('users', 'assist_user_event.user_id', '=', 'users.id')
            ->where('assist_user_event.event_id', $eventId)
            ->select('users.id', 'users.name', 'assist_user_event.puesto')
            ->orderBy('assist_user_event.created_at', 'asc')
            ->get();

        // Contamos cuántas piezas tiene registradas cada jugador
        $decksCount = \App\Models\TournamentResult::where('event_id', $eventId)
            ->whereNotNull('blade')
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $isStaff = auth()->check() && auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro']);
        
        $jugadores = [];
        $nombresTxt = "";
        $inscribedIds = [];
        
        // MAGIA: ¿Cuántas piezas exige este evento?
        $requiredBeys = in_array($event->beys, ['equipos', 'crew_battle']) ? 1 : 3;
        
        foreach ($jugadoresRaw as $user) {
            $isDropped = $user->puesto === 'dropped';
            $piezasRegistradas = $decksCount[$user->id] ?? 0;
            
            $jugadores[] = [
                'id' => $user->id,
                'name' => $user->name,
                'puesto' => $isDropped ? 'dropped' : 'active',
                'has_deck' => $piezasRegistradas >= $requiredBeys // <-- AHORA SE ADAPTA
            ];
            
            $inscribedIds[] = $user->id;

            if (!$isDropped) {
                $nombresTxt .= $user->name . "\n";
            }
        }

        $availableUsers = [];
        if ($isStaff) {
            // Buscamos todos los usuarios del sistema que NO estén ya en la lista de inscritos
            $availableUsers = \App\Models\User::whereNotIn('id', $inscribedIds)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
        }

        return response()->json([
            'success' => true, 
            'jugadores' => $jugadores,
            'nombres' => $nombresTxt,
            'is_staff' => $isStaff,
            'event_status' => $event->status,
            'event_type' => $event->beys,
            'available_users' => $availableUsers
        ]);
    }

    // NUEVO: Función para añadir jugadores a mano
    public function addJugador(Request $request, $eventId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado'], 403);
        }

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Selecciona un jugador']);
        }

        // Evitar duplicados por si acaso
        $exists = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            \Illuminate\Support\Facades\DB::table('assist_user_event')->insert([
                'event_id' => $eventId,
                'user_id' => $userId,
                'puesto' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['success' => true, 'message' => '✅ Jugador añadido a la arena']);
    }

    public function dropJugador($eventId, $userId)
    {
        // En lugar de adivinar el nombre de la tabla, buscamos el registro directamente en la tabla pivote de Laravel
        $event = \App\Models\Event::findOrFail($eventId);
        
        // Asumiendo que la tabla intermedia estándar es event_user, actualizamos su estado
        \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->update(['puesto' => 'dropped']); // Si tu columna de estado se llama diferente, cámbialo aquí (ej: 'status')

        return response()->json([
            'success' => true,
            'message' => 'Jugador retirado del torneo exitosamente.'
        ]);
    }

    // El Juez entra en la batalla
    public function lockMatch($matchId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) return response()->json(['success' => false]);
        
        $match = TournamentMatch::findOrFail($matchId);
        
        // Si ya está bloqueada por otro, avisamos
        if ($match->locked_by !== null && $match->locked_by !== auth()->id()) {
            return response()->json([
                'success' => false, 
                'message' => 'Mesa ocupada por otro juez.'
            ]);
        }
        
        $match->locked_by = auth()->id();
        $match->save();
        
        return response()->json(['success' => true]);
    }

    // El Juez sale de la batalla
    public function unlockMatch($matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        
        // Solo la desbloquea si era él quien la tenía bloqueada (o es un admin forzando)
        if ($match->locked_by === auth()->id() || auth()->user()->hasRole('admin')) {
            $match->locked_by = null;
            $match->save();
        }
        
        return response()->json(['success' => true]);
    }

    public function finalizeTournament($eventId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $event = Event::findOrFail($eventId);
        
        // VOLCADO AUTOMÁTICO AL RANKING DE EQUIPOS
        if (in_array($event->beys, ['equipos', 'crew_battle'])) {
            $teamMatches = TournamentMatch::where('event_id', $eventId)
                ->whereNotNull('team1_id')
                ->where('status', 'completed')
                ->get();

            $primer_dia_mes_actual = \Carbon\Carbon::now()->startOfMonth();
            $ultimo_dia_mes_actual = \Carbon\Carbon::now()->endOfMonth();
                
            foreach ($teamMatches as $tm) {
                // 1. REGLA: ¿Se han enfrentado ya este mes en cualquier modalidad de Crew Battle?
                $se_enfrentaron = \Illuminate\Support\Facades\DB::table('teams_versus')
                    ->where(function($query) use ($tm) {
                        $query->where(function($q) use ($tm) {
                            $q->where('team_id_1', $tm->team1_id)
                              ->where('team_id_2', $tm->team2_id);
                        })
                        ->orWhere(function($q) use ($tm) {
                            $q->where('team_id_1', $tm->team2_id)
                              ->where('team_id_2', $tm->team1_id);
                        });
                    })
                    ->whereBetween('created_at', [$primer_dia_mes_actual, $ultimo_dia_mes_actual])
                    ->where('matchup', 'crew_battle')
                    ->exists();

                // 2. Comprobar si ya se insertó exactamente ESTE combate hoy (evitar dobles clics)
                $ya_volcado_hoy = \Illuminate\Support\Facades\DB::table('teams_versus')
                    ->where('team_id_1', $tm->team1_id)
                    ->where('team_id_2', $tm->team2_id)
                    ->whereDate('created_at', now()->toDateString()) 
                    ->where('url', url("/eventos/{$eventId}"))
                    ->exists();
                    
                if (!$se_enfrentaron && !$ya_volcado_hoy) {
                    \Illuminate\Support\Facades\DB::table('teams_versus')->insert([
                        'team_id_1'  => $tm->team1_id,
                        'team_id_2'  => $tm->team2_id,
                        'result_1'   => $tm->player1_score,
                        'result_2'   => $tm->player2_score,
                        'matchup'    => 'crew_battle', 
                        'url'        => url("/eventos/{$eventId}"), // Enlace al acta del evento
                        'status'     => 'OPEN', // <--- ¡A LA COLA DE MODERACIÓN!
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);
                }
            }
        }

        // Cerramos el torneo
        $event->status = 'COMPLETED'; 
        $event->save();

        return response()->json(['success' => true, 'message' => '🏆 Torneo finalizado. ¡Duelos enviados a moderación con éxito!']);
    }

    // NUEVO: Crea un evento express, inscribe equipos y genera la partida en 1 segundo
    public function createFastDuel(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        $request->validate([
            'team1_id' => 'required',
            'team2_id' => 'required|different:team1_id'
        ]);

        $t1 = \App\Models\Team::findOrFail($request->team1_id);
        $t2 = \App\Models\Team::findOrFail($request->team2_id);

        // 1. Crear evento oculto express (Entra directo a LIVE)
        $event = \App\Models\Event::create([
            'name' => "Duelo Express: {$t1->name} vs {$t2->name}",
            'mode' => 'beybladex',
            'city' => 'Arena Local',
            'location' => 'Arena Express',
            'region_id' => 1, 
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'imagen' => 'upload-events/crew_battle.png',
            'beys' => 'crew_battle',
            'deck' => '1on1',
            'configuration' => 'RoundRobin',
            'stadiums' => 1,
            'status' => 'LIVE', 
            'created_by' => auth()->id()
        ]);

        // 2. Inscribir a todos los jugadores de ambos equipos
        $usersT1 = \Illuminate\Support\Facades\DB::table('team_user')->where('team_id', $t1->id)->pluck('user_id');
        $usersT2 = \Illuminate\Support\Facades\DB::table('team_user')->where('team_id', $t2->id)->pluck('user_id');
        $allUsers = $usersT1->merge($usersT2);

        foreach ($allUsers as $uid) {
            \Illuminate\Support\Facades\DB::table('assist_user_event')->insert([
                'event_id' => $event->id,
                'user_id' => $uid,
                'puesto' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 3. MAGIA: Creamos un slot de deck vacío para que el juez pueda editarlo luego
            \App\Models\TournamentResult::create([
                'user_id' => $uid,
                'event_id' => $event->id,
                'blade' => 'DESCONOCIDO', // Lo llamamos así para que salga en pantalla
                'ratchet' => '-',
                'bit' => '-',
                'victorias' => 0, 'derrotas' => 0, 'puntos_ganados' => 0, 'puntos_perdidos' => 0
            ]);
        }

        // 4. Crear el enfrentamiento
        \App\Models\TournamentMatch::create([
            'event_id' => $event->id,
            'round_number' => 1,
            'bracket_type' => 'round_robin',
            'team1_id' => $t1->id,
            'team2_id' => $t2->id,
            'player1_id' => null,
            'player2_id' => null,
            'status' => 'pending',
            'player1_score' => 0,
            'player2_score' => 0,
        ]);

        // Devolvemos el ID del evento para que el frontend lo abra automáticamente
        return response()->json(['success' => true, 'event_id' => $event->id]);
    }

    // Busca solo a los miembros del equipo que están ACTIVOS en este evento concreto
    private function getActiveTeamMembers($eventId, $teamId) {
        return \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->join('team_user', 'assist_user_event.user_id', '=', 'team_user.user_id')
            ->where('assist_user_event.event_id', $eventId)
            ->where('team_user.team_id', $teamId)
            ->where(function($q) {
                $q->whereNull('assist_user_event.puesto')
                  ->orWhere('assist_user_event.puesto', '!=', 'dropped');
            })
            ->pluck('assist_user_event.user_id');
    }
}