<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TournamentMatch;
use App\Services\Tournament\SwissEngine;
use Illuminate\Http\Request;

class TournamentEngineController extends Controller
{
    /**
     * VERIFICADOR CENTRALIZADO DE PERMISOS DE ARENA:
     * - En torneos de Ranking ('ranking', 'rankingplus'): Admin, Juez, Árbitro o Juez asignado.
     * - En el resto de torneos (Quedadas, Simulacros, Hasbro, Copas, Equipos): también Revisores y el Creador del evento.
     */
    private function canManageEvent(Event $event): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return true;
        }

        $isAssignedStaff = $event->judges()->where('user_id', $user->id)->exists();
        $isRanking = in_array($event->beys, ['ranking', 'rankingplus']);

        if (!$isRanking) {
            if ($user->hasRole('revisor') || $event->created_by == $user->id || $isAssignedStaff) {
                return true;
            }
        }

        return $isAssignedStaff && !$user->hasRole('revisor');
    }

    // Carga los datos principales para el panel del juez (OPTIMIZADO SIN LOGOS PESADOS)
    public function getArenaDashboard(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        // Si es un torneo de equipos, sincronizamos en vivo el límite de 5 duelos mensuales
        if (in_array($event->beys, ['equipos', 'crew_battle'])) {
            $this->syncTeamMonthlyLimits($eventId);
        }

        $allMatches = TournamentMatch::where('event_id', $eventId)
            ->select('id', 'round_number', 'bracket_type', 'status')
            ->get();
            
        $maxRound = $allMatches->max('round_number') ?? 0;

        // Excluimos tanto 'swiss' como 'round_robin' para que los duelos de equipos no disparen el motor de eliminatorias
        $isBracket = $allMatches->whereNotIn('bracket_type', ['swiss', 'round_robin'])->isNotEmpty();

        $roundsStatus = [];
        $firstPendingRound = $maxRound; 
        
        for ($i = 1; $i <= $maxRound; $i++) {
            $matchesInRound = $allMatches->where('round_number', $i);
            $pendingCount = $matchesInRound->whereNotIn('status', ['completed', 'cancelled'])->count();
            
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

        // CRÍTICO: Cargamos solo id y name de player1, player2, team1 y team2 (evita cargar los logos BLOB/Base64 de teams)
        $matches = TournamentMatch::with([
                'player1:id,name',
                'player2:id,name',
                'team1:id,name',
                'team2:id,name',
                'lockedByUser:id,name'
            ])
            ->where('event_id', $eventId)
            ->where('round_number', $requestedRound)
            ->where('player1_score', '!=', 99) 
            ->where('player2_score', '!=', 99)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('player1_id')->whereNotNull('player2_id');
                })
                ->orWhere(function ($q) {
                    $q->whereNotIn('bracket_type', ['swiss', 'round_robin'])
                      ->where('status', 'pending');
                })
                ->orWhereNotNull('team1_id'); 
            })
            ->orderBy('bracket_type', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        if ($isBracket) {
            $engine = new \App\Services\Tournament\DoubleEliminationEngine();
            $topology = $engine->buildBracketTopology($eventId);

            $matches = $matches->filter(function ($m) use ($topology) {
                return empty($topology['hidden'][$m->id]);
            })->values();

            foreach ($matches as $m) {
                $m->match_number = $topology['numbers'][$m->id] ?? $m->id;
                $m->p1_placeholder = $topology['sources'][$m->id][1] ?? 'ESPERANDO RIVAL';
                $m->p2_placeholder = $topology['sources'][$m->id][2] ?? 'ESPERANDO RIVAL';
            }
        }

        return response()->json([
            'success'       => true,
            'event_name'    => $event->name,
            'current_round' => (int) $requestedRound,
            'max_round'     => (int) $maxRound,
            'rounds_status' => $roundsStatus,
            'is_bracket'    => $isBracket,
            'is_team_event' => in_array($event->beys, ['equipos', 'crew_battle']),
            'matches'       => $matches
        ]);
    }

    public function generateRound($eventId, \App\Services\Tournament\SwissEngine $engine)
    {
        $event = Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }
        
        // Si el evento es de equipos, lanzamos el Round Robin intercalado
        if (in_array($event->beys, ['equipos', 'crew_battle'])) {
            $validTeams = \Illuminate\Support\Facades\DB::table('assist_user_event')
                ->join('team_user', 'assist_user_event.user_id', '=', 'team_user.user_id')
                ->where('assist_user_event.event_id', $event->id)
                ->where(function($q) {
                    $q->whereNull('assist_user_event.puesto')
                      ->orWhere('assist_user_event.puesto', '!=', 'dropped');
                })
                ->pluck('team_user.team_id')
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            if (count($validTeams) < 2) {
                return response()->json(['success' => false, 'message' => '⛔ No hay suficientes equipos activos inscritos para generar cruces.'], 400);
            }

            return $this->generateTeamRoundRobin($event, $validTeams);
        }
        
        $result = $engine->generateNextRound($event);
        return response()->json($result);
    }

    private function generateTeamRoundRobin($event, $validTeams)
    {
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth();

        $teamMatchCounts = [];
        $playedMatchups = [];
        $matchesCreated = 0;

        // Barajamos el orden inicial de los equipos para que el calendario sea aleatorio en cada torneo
        shuffle($validTeams);

        foreach ($validTeams as $teamId) {
            // Solo contamos los duelos que YA se jugaron en el mes (fuera de este torneo)
            $teamMatchCounts[$teamId] = \Illuminate\Support\Facades\DB::table('teams_versus')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function ($q) use ($teamId) {
                    $q->where('team_id_1', $teamId)->orWhere('team_id_2', $teamId);
                })->count();
        }

        // Cruces exactos ya jugados este mes en la liga
        $allVersusThisMonth = \Illuminate\Support\Facades\DB::table('teams_versus')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('matchup', 'crew_battle')
            ->get();

        foreach ($allVersusThisMonth as $v) {
            $playedMatchups[$v->team_id_1 . '_' . $v->team_id_2] = true;
            $playedMatchups[$v->team_id_2 . '_' . $v->team_id_1] = true;
        }

        // 1. ALGORITMO DEL POLÍGONO (BERGER): Empareja por tandas donde todos rotan a la vez
        $teamsPool = array_values($validTeams);
        if (count($teamsPool) % 2 !== 0) {
            $teamsPool[] = null; // Equipo fantasma para cuadrar rotaciones impares
        }

        $totalTeams = count($teamsPool);
        $numRounds = $totalTeams - 1;
        $half = $totalTeams / 2;
        $rawPairings = [];

        for ($r = 0; $r < $numRounds; $r++) {
            for ($i = 0; $i < $half; $i++) {
                $t1 = $teamsPool[$i];
                $t2 = $teamsPool[$totalTeams - 1 - $i];

                if (!is_null($t1) && !is_null($t2)) {
                    $rawPairings[] = ($r % 2 === 0) ? [$t1, $t2] : [$t2, $t1];
                }
            }
            // Rotamos todos los equipos menos el primero (pivote fijo)
            $last = array_pop($teamsPool);
            array_splice($teamsPool, 1, 0, [$last]);
        }

        // 2. FILTRO ANTI-CONSECUTIVOS: Reordena la cola para que ningún equipo repita en dos partidos seguidos
        $orderedPairings = [];
        $lastMatchTeams = [];

        while (!empty($rawPairings)) {
            $pickedIndex = null;

            // Buscamos el primer cruce cuyos 2 equipos NO hayan jugado en el duelo inmediatamente anterior
            foreach ($rawPairings as $idx => $pair) {
                if (!in_array($pair[0], $lastMatchTeams) && !in_array($pair[1], $lastMatchTeams)) {
                    $pickedIndex = $idx;
                    break;
                }
            }

            // Si quedan pocos equipos (ej. 3 equipos) y uno tiene que repetir por matemática pura, buscamos que al menos 1 descanse
            if (is_null($pickedIndex)) {
                foreach ($rawPairings as $idx => $pair) {
                    if (!in_array($pair[0], $lastMatchTeams) || !in_array($pair[1], $lastMatchTeams)) {
                        $pickedIndex = $idx;
                        break;
                    }
                }
            }

            if (is_null($pickedIndex)) {
                $pickedIndex = array_key_first($rawPairings);
            }

            $chosenPair = $rawPairings[$pickedIndex];
            $orderedPairings[] = $chosenPair;
            $lastMatchTeams = [$chosenPair[0], $chosenPair[1]];
            unset($rawPairings[$pickedIndex]);
        }

        // 3. CREACIÓN EN BASE DE DATOS EN EL ORDEN INTERCALADO
        foreach ($orderedPairings as $pair) {
            $t1 = $pair[0];
            $t2 = $pair[1];

            $alreadyPlayed = isset($playedMatchups[$t1 . '_' . $t2]);
            $t1AlreadyMaxed = ($teamMatchCounts[$t1] ?? 0) >= 5;
            $t2AlreadyMaxed = ($teamMatchCounts[$t2] ?? 0) >= 5;

            // Todos nacen abiertos ('pending') salvo que ya hubieran jugado este mes o vinieran con 5 duelos previos
            $status = ($alreadyPlayed || $t1AlreadyMaxed || $t2AlreadyMaxed) ? 'cancelled' : 'pending';

            \App\Models\TournamentMatch::create([
                'event_id'      => $event->id,
                'round_number'  => 1,
                'bracket_type'  => 'round_robin',
                'team1_id'      => $t1,
                'team2_id'      => $t2,
                'player1_id'    => null,
                'player2_id'    => null,
                'status'        => $status,
                'player1_score' => 0,
                'player2_score' => 0,
            ]);

            if ($status === 'pending') {
                $matchesCreated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "¡Liguilla intercalada! $matchesCreated duelos disponibles. Se bloquearán al completar 5 duelos por clan."
        ]);
    }

    /**
     * SINCRONIZADOR EN VIVO DEL LÍMITE DE 5 DUELOS MENSUALES:
     * Cuando un equipo alcanza su 5º duelo completado, cancela el resto de sus duelos pendientes.
     * Si el juez hace Undo y baja de 5, se los vuelve a abrir automáticamente.
     */
    private function syncTeamMonthlyLimits(int $eventId)
    {
        $eventMatches = \App\Models\TournamentMatch::where('event_id', $eventId)
            ->whereNotNull('team1_id')
            ->whereNotNull('team2_id')
            ->get();

        if ($eventMatches->isEmpty()) return;

        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth();

        $teamIds = $eventMatches->pluck('team1_id')
            ->merge($eventMatches->pluck('team2_id'))
            ->unique()
            ->filter()
            ->values();

        // 1. Duelos previos registrados en teams_versus este mes
        $monthlyCounts = [];
        foreach ($teamIds as $tid) {
            $monthlyCounts[$tid] = \Illuminate\Support\Facades\DB::table('teams_versus')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function ($q) use ($tid) {
                    $q->where('team_id_1', $tid)->orWhere('team_id_2', $tid);
                })->count();
        }

        // Cruces ya registrados en teams_versus este mes
        $playedMatchups = [];
        $allVersusThisMonth = \Illuminate\Support\Facades\DB::table('teams_versus')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('matchup', 'crew_battle')
            ->get();

        foreach ($allVersusThisMonth as $v) {
            $playedMatchups[$v->team_id_1 . '_' . $v->team_id_2] = true;
            $playedMatchups[$v->team_id_2 . '_' . $v->team_id_1] = true;
        }

        // 2. Sumamos únicamente los duelos que YA se han COMPLETADO en este torneo
        foreach ($eventMatches->where('status', 'completed') as $compMatch) {
            $monthlyCounts[$compMatch->team1_id] = ($monthlyCounts[$compMatch->team1_id] ?? 0) + 1;
            $monthlyCounts[$compMatch->team2_id] = ($monthlyCounts[$compMatch->team2_id] ?? 0) + 1;
        }

        // 3. Actualizamos el estado de las partidas pendientes (pending <-> cancelled)
        foreach ($eventMatches as $m) {
            if ($m->status === 'completed' || $m->status === 'playing') {
                continue;
            }

            $t1 = $m->team1_id;
            $t2 = $m->team2_id;

            $alreadyPlayed = isset($playedMatchups[$t1 . '_' . $t2]);
            $limitReached = (($monthlyCounts[$t1] ?? 0) >= 5) || (($monthlyCounts[$t2] ?? 0) >= 5);

            $shouldCancel = $alreadyPlayed || $limitReached;

            if ($shouldCancel && $m->status !== 'cancelled') {
                $m->status = 'cancelled';
                $m->save();
            } elseif (!$shouldCancel && $m->status === 'cancelled') {
                $m->status = 'pending';
                $m->save();
            }
        }
    }

    // Obtiene los detalles de una partida y los Decks (OPTIMIZADO EN 1 SOLA CONSULTA SIN LOGOS)
    public function getMatchDetails($matchId)
    {
        $match = \App\Models\TournamentMatch::with([
            'player1:id,name',
            'player2:id,name',
            'team1:id,name',
            'team2:id,name'
        ])->findOrFail($matchId);

        $battles = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->orderBy('id', 'asc')
            ->get();

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

        if ($isTeamMatch) {
            $buildTeamDeck = function ($teamId) use ($match) {
                $memberIds = $this->getActiveTeamMembers($match->event_id, $teamId);
                $members = \App\Models\User::whereIn('id', $memberIds)->get(['id', 'name']);
                
                // Pre-cargamos todos los combos del equipo de golpe para evitar N+1 consultas
                $existingCombos = \App\Models\TournamentResult::where('event_id', $match->event_id)
                    ->whereIn('user_id', $memberIds)
                    ->whereNotNull('blade')
                    ->get()
                    ->keyBy('user_id');

                $deckList = [];

                foreach ($members as $member) {
                    $combo = $existingCombos->get($member->id);

                    if (!$combo) {
                        $combo = \App\Models\TournamentResult::create([
                            'event_id'        => $match->event_id,
                            'user_id'         => $member->id,
                            'blade'           => 'DESCONOCIDO',
                            'ratchet'         => '-',
                            'bit'             => '-',
                            'victorias'       => 0,
                            'derrotas'        => 0,
                            'puntos_ganados'  => 0,
                            'puntos_perdidos' => 0,
                        ]);
                    }

                    $deckList[] = [
                        'id'           => $combo->id,
                        'user_id'      => $member->id,
                        'user_name'    => $member->name,
                        'blade'        => $combo->blade ?? 'DESCONOCIDO',
                        'over_blade'   => $combo->over_blade,
                        'assist_blade' => $combo->assist_blade,
                        'ratchet'      => $combo->ratchet ?? '-',
                        'bit'          => $combo->bit ?? '-',
                    ];
                }
                return $deckList;
            };

            $p1Deck = $buildTeamDeck($match->team1_id);
            $p2Deck = $buildTeamDeck($match->team2_id);

            $team1Size = count($p1Deck);
            $team2Size = count($p2Deck);
            $matchesToPlay = max($team1Size, $team2Size);
            $matchesPlayed = $battles->whereNotIn('finish_type', ['warning_p1', 'warning_p2', 'penalty'])->count();

            return response()->json([
                'match'           => $match,
                'p1_deck'         => $p1Deck,
                'p2_deck'         => $p2Deck,
                'battles'         => $battles,
                'is_team_match'   => true,
                'team1_size'      => $team1Size,
                'team2_size'      => $team2Size,
                'matches_played'  => $matchesPlayed,
                'matches_to_play' => $matchesToPlay,
            ]);
        }

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
            'match'         => $match,
            'p1_deck'       => $p1Deck,
            'p2_deck'       => $p2Deck,
            'battles'       => $battles,
            'is_team_match' => false
        ]);
    }

    // 1. REGISTRAR CHOQUE NORMAL (CON AVANCE AUTOMÁTICO Y PROTECCIÓN DE EDICIÓN)
    public function storeBattle(Request $request, $matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $wasCompletedBefore = ($match->status === 'completed');

        if ($wasCompletedBefore && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }
        
        $points = 0;
        $finishType = $request->finish_type;
        
        if ($finishType === 'spin') $points = 1;
        if ($finishType === 'over' || $finishType === 'burst') $points = 2;
        if ($finishType === 'xtreme') $points = 3;

        \App\Models\MatchBattle::create([
            'tournament_match_id' => $match->id,
            'player1_combo_id' => $request->player1_combo_id,
            'player2_combo_id' => $request->player2_combo_id,
            'winner_id' => $request->winner_id,
            'finish_type' => $finishType,
            'points_awarded' => $points,
        ]);

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
        $isTiebreakerActive = false;

        if ($isTeamMatch) {
            $winnerTeam = \Illuminate\Support\Facades\DB::table('team_user')->where('user_id', $request->winner_id)->value('team_id');
            if ($winnerTeam == $match->team1_id) $match->player1_score += $points;
            if ($winnerTeam == $match->team2_id) $match->player2_score += $points;
        } else {
            if ($request->winner_id == $match->player1_id) {
                $match->player1_score = min(4, $match->player1_score + $points);
            } elseif ($request->winner_id == $match->player2_id) {
                $match->player2_score = min(4, $match->player2_score + $points);
            }
        }
        
        $matchCompleted = false;

        if ($isTeamMatch) {
            $allBattles = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->get();
            $matchesPlayed = $allBattles->whereNotIn('finish_type', ['warning_p1', 'warning_p2', 'penalty'])->count();
            
            $team1UsersCount = $this->getActiveTeamMembers($match->event_id, $match->team1_id)->count();
            $team2UsersCount = $this->getActiveTeamMembers($match->event_id, $match->team2_id)->count();
            $limit = max($team1UsersCount, $team2UsersCount);

            if ($matchesPlayed >= $limit) {
                if ($match->player1_score !== $match->player2_score) {
                    $match->winner_id = $match->player1_score > $match->player2_score ? $match->team1_id : $match->team2_id;
                    $match->status = 'completed';
                    $matchCompleted = true;
                } else {
                    $match->status = 'playing';
                    $matchCompleted = false;
                    $isTiebreakerActive = true;
                }
            } else {
                $match->status = 'playing';
            }
        } else {
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

        if ($isTeamMatch && ($matchCompleted || $wasCompletedBefore)) {
            $this->syncTeamMonthlyLimits($match->event_id);
        }

        if ($matchCompleted && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true, 
            'message' => $isTiebreakerActive ? '⚡ ¡EMPATE! Todos los jugadores habilitados para el duelo de desempate.' : 'Resultado registrado', 
            'match_completed' => $matchCompleted,
            'is_tiebreaker' => $isTiebreakerActive,
            'p1_score' => $match->player1_score,
            'p2_score' => $match->player2_score
        ]);
    }

    // 2. DESHACER ÚLTIMO REGISTRO (CON REVERSIÓN AUTOMÁTICA DEL CUADRO Y DE LÍMITES DE EQUIPO)
    public function deshacerUltimoRegistro($matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }

        $lastBattle = \App\Models\MatchBattle::where('tournament_match_id', $matchId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastBattle) {
            if ($match->player1_score > 0 || $match->player2_score > 0 || $match->status === 'completed') {
                $match->player1_score = 0;
                $match->player2_score = 0;
                $match->winner_id = null;
                $match->status = 'playing';
                $match->save();

                if ($isTeamMatch) {
                    $this->syncTeamMonthlyLimits($match->event_id);
                }

                return response()->json(['success' => true, 'message' => '↩️ Resultado manual/rápido anulado y avance revertido.']);
            }
            return response()->json(['success' => false, 'message' => 'No hay acciones para deshacer en esta partida.']);
        }

        $lastBattle->delete();

        $remainingBattles = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->get();

        if ($isTeamMatch) {
            $p1_score = 0;
            $p2_score = 0;
            foreach ($remainingBattles as $b) {
                $wTeam = \Illuminate\Support\Facades\DB::table('team_user')->where('user_id', $b->winner_id)->value('team_id');
                if ($wTeam == $match->team1_id) $p1_score += $b->points_awarded;
                if ($wTeam == $match->team2_id) $p2_score += $b->points_awarded;
            }
            $match->player1_score = $p1_score;
            $match->player2_score = $p2_score;
            $match->winner_id = null;
            $match->status = $remainingBattles->count() > 0 ? 'playing' : 'pending';
        } else {
            $p1_score = $remainingBattles->where('winner_id', $match->player1_id)->sum('points_awarded') ?? 0;
            $p2_score = $remainingBattles->where('winner_id', $match->player2_id)->sum('points_awarded') ?? 0;
            
            $match->player1_score = min(4, $p1_score);
            $match->player2_score = min(4, $p2_score);

            if ($match->player1_score >= 4 || $match->player2_score >= 4) {
                $match->winner_id = $match->player1_score >= 4 ? $match->player1_id : $match->player2_id;
                $match->status = 'completed';
            } else {
                $match->winner_id = null;
                $match->status = $remainingBattles->count() > 0 ? 'playing' : 'pending';
            }
        }

        $match->save();

        if ($isTeamMatch) {
            $this->syncTeamMonthlyLimits($match->event_id);
        }

        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true,
            'message' => '↩️ Última acción deshecha correctamente.',
            'p1_score' => $match->player1_score,
            'p2_score' => $match->player2_score
        ]);
    }

    // 3. NO PRESENTADO (DA LA VICTORIA 4-0 AL OTRO JUGADOR Y AVANZA AUTOMÁTICAMENTE)
    public function fastWinMatch(Request $request, $matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }

        $winnerSide = $request->winner_side;
        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

        \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $match->id)->delete();

        if ($winnerSide === 'p1') {
            $match->player1_score = 4;
            $match->player2_score = 0;
            $match->winner_id = $isTeamMatch ? $match->team1_id : $match->player1_id;
        } else {
            $match->player1_score = 0;
            $match->player2_score = 4;
            $match->winner_id = $isTeamMatch ? $match->team2_id : $match->player2_id;
        }

        $match->status = 'completed';
        $match->locked_by = null;
        $match->save();

        if ($isTeamMatch) {
            $this->syncTeamMonthlyLimits($match->event_id);
        }

        if (!in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true,
            'message' => '✅ Incomparecencia registrada. El rival avanza automáticamente.',
            'match_completed' => true
        ]);
    }

    // 4. MARCADOR MANUAL SIN STATS
    public function setManualScore(Request $request, $matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $s1 = max(0, min(4, (int) $request->p1_score));
        $s2 = max(0, min(4, (int) $request->p2_score));

        if ($s1 < 4 && $s2 < 4) {
            return response()->json(['success' => false, 'message' => '⚠️ Para finalizar manualmente, uno de los jugadores debe llegar a 4 puntos.'], 422);
        }
        if ($s1 === 4 && $s2 === 4) {
            return response()->json(['success' => false, 'message' => '⚠️ No puede haber un empate 4 a 4.'], 422);
        }

        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }

        \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $match->id)->delete();

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
        $match->player1_score = $s1;
        $match->player2_score = $s2;
        $match->winner_id = ($s1 > $s2) ? ($isTeamMatch ? $match->team1_id : $match->player1_id) : ($isTeamMatch ? $match->team2_id : $match->player2_id);
        $match->status = 'completed';
        $match->locked_by = null;
        $match->save();

        if ($isTeamMatch) {
            $this->syncTeamMonthlyLimits($match->event_id);
        }

        if (!in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true,
            'message' => "✅ Marcador manual ($s1 - $s2) guardado y cuadro actualizado.",
            'match_completed' => true
        ]);
    }

    // 5. MOTOR DE AVANCE SEGURO
    private function safeProgressMatch(TournamentMatch $match)
    {
        if (!$match->winner_id) return;
        $engine = new \App\Services\Tournament\DoubleEliminationEngine();
        $engine->progressMatch($match);
    }

    // 6. ROLLBACK EXACTO USANDO LA TOPOLOGÍA PRE-GENERADA
    private function revertMatchProgression(TournamentMatch $match)
    {
        if (in_array($match->bracket_type, ['swiss', 'round_robin'])) return;

        if ($match->bracket_type === 'grand_final') {
            TournamentMatch::where('event_id', $match->event_id)
                ->where('bracket_type', 'grand_final_reset')
                ->delete();
            return;
        }

        $engine = new \App\Services\Tournament\DoubleEliminationEngine();
        $topology = $engine->buildBracketTopology($match->event_id);
        $route = $topology['targets'][$match->id] ?? null;

        if (!$route) return;

        foreach (['win', 'lose'] as $branch) {
            if (!empty($route[$branch])) {
                $targetId = $route[$branch]['match_id'];
                $slot = $route[$branch]['slot'];
                $targetMatch = TournamentMatch::find($targetId);

                if ($targetMatch) {
                    if ($targetMatch->status === 'completed') {
                        $this->revertMatchProgression($targetMatch);
                    }
                    if ($slot === 1) {
                        $targetMatch->player1_id = null;
                        $targetMatch->player1_score = 0;
                    } else {
                        $targetMatch->player2_id = null;
                        $targetMatch->player2_score = 0;
                    }
                    $targetMatch->winner_id = null;
                    $targetMatch->status = 'pending';
                    $targetMatch->save();
                }
            }
        }
    }

    // Calcula la clasificación en vivo (Puntos y Buchholz)
    public function getStandings($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        $matches = \App\Models\TournamentMatch::where('event_id', $eventId)->where('status', 'completed')->get();
        $hasBracket = $matches->whereIn('bracket_type', ['winners', 'losers', 'grand_final', 'grand_final_reset', 'single_elimination'])->count() > 0;
        
        $users = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->join('users', 'assist_user_event.user_id', '=', 'users.id')
            ->where('assist_user_event.event_id', $eventId)
            ->where(function($q) {
                $q->whereNull('assist_user_event.puesto')
                  ->orWhere('assist_user_event.puesto', '!=', 'dropped');
            })
            ->select('users.id', 'users.name', 'assist_user_event.puesto')
            ->get();
            
        $playerStats = [];
        foreach ($users as $u) {
            $playerStats[$u->id] = ['id' => $u->id, 'name' => $u->name, 'points' => 0, 'buchholz' => 0, 'bracket_score' => 0, 'puesto' => $u->puesto];
        }
        
        $swissMatches = $matches->whereIn('bracket_type', ['swiss', 'round_robin']);
        foreach ($swissMatches as $m) {
            if ($m->player1_id && $m->player2_id) { 
                if ($m->winner_id == $m->player1_id) $playerStats[$m->player1_id]['points'] += 3;
                if ($m->winner_id == $m->player2_id) $playerStats[$m->player2_id]['points'] += 3;
                if (is_null($m->winner_id) || $m->winner_id == 0) {
                    $playerStats[$m->player1_id]['points'] += 1;
                    $playerStats[$m->player2_id]['points'] += 1;
                }
            }
        }
        
        foreach ($swissMatches as $m) {
            if ($m->player1_id && $m->player2_id) {
                $playerStats[$m->player1_id]['buchholz'] += $playerStats[$m->player2_id]['points'];
                $playerStats[$m->player2_id]['buchholz'] += $playerStats[$m->player1_id]['points'];
            }
        }
        
        if ($hasBracket) {
            $hasReset = $matches->where('bracket_type', 'grand_final_reset')->count() > 0;
            
            foreach ($playerStats as $uid => &$stats) {
                $bMatches = $matches->filter(fn($m) => in_array($m->bracket_type, ['winners', 'losers', 'grand_final', 'grand_final_reset', 'single_elimination']) && ($m->player1_id == $uid || $m->player2_id == $uid));
                
                if ($bMatches->count() > 0) {
                    $wonGFReset = $bMatches->where('bracket_type', 'grand_final_reset')->where('winner_id', $uid)->count() > 0;
                    $lostGFReset = $bMatches->where('bracket_type', 'grand_final_reset')->where('winner_id', '!=', $uid)->count() > 0;
                    $wonGF = $bMatches->where('bracket_type', 'grand_final')->where('winner_id', $uid)->count() > 0;
                    $lostGF = $bMatches->where('bracket_type', 'grand_final')->where('winner_id', '!=', $uid)->count() > 0;
                    
                    if ($hasReset && $wonGFReset) $stats['bracket_score'] = 10000;
                    elseif ($hasReset && $lostGFReset) $stats['bracket_score'] = 9000;
                    elseif (!$hasReset && $wonGF) $stats['bracket_score'] = 10000;
                    elseif (!$hasReset && $lostGF) $stats['bracket_score'] = 9000;
                    else {
                        $losers = $bMatches->where('bracket_type', 'losers');
                        if ($losers->count() > 0) {
                            $maxRoundLost = 0;
                            foreach($losers as $lm) {
                                if ($lm->winner_id != $uid && $lm->round_number > $maxRoundLost) $maxRoundLost = $lm->round_number;
                            }
                            if ($maxRoundLost > 0) $stats['bracket_score'] = 5000 + $maxRoundLost;
                        } else {
                            $se = $bMatches->where('bracket_type', 'single_elimination');
                            if ($se->count() > 0) {
                                $maxRoundLost = 0;
                                foreach($se as $sm) {
                                    if ($sm->winner_id != $uid && $sm->round_number > $maxRoundLost) $maxRoundLost = $sm->round_number;
                                }
                                $stats['bracket_score'] = 5000 + $maxRoundLost;
                            } else {
                                $maxWinRound = $bMatches->where('bracket_type', 'winners')->max('round_number');
                                $stats['bracket_score'] = 1000 + $maxWinRound;
                            }
                        }
                    }
                }
            }
        }
        
        $standings = array_values($playerStats);
        
        usort($standings, function($a, $b) {
            $puestos = ['primero' => 1, 'segundo' => 2, 'tercero' => 3, 'cuarto' => 4];
            $posA = $puestos[$a['puesto']] ?? 999;
            $posB = $puestos[$b['puesto']] ?? 999;
            
            if ($posA != $posB) return $posA <=> $posB;
            if ($a['bracket_score'] != $b['bracket_score']) return $b['bracket_score'] <=> $a['bracket_score'];
            if ($a['points'] != $b['points']) return $b['points'] <=> $a['points'];
            return $b['buchholz'] <=> $a['buchholz'];
        });
        
        return response()->json([
            'success' => true,
            'is_bracket' => $hasBracket,
            'clasificacion' => $standings
        ]);
    }

    public function generateTop(Request $request, $eventId, \App\Services\Tournament\DoubleEliminationEngine $engine)
    {
        $event = \App\Models\Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }
        
        $result = $engine->generateTopCut($event, $request->top_size);

        if ($result['success']) {
            try {
                \App\Http\Controllers\EventController::sendDiscordBracketNotificationHUB($event);
            } catch (\Exception $e) {}
        }

        return response()->json($result);
    }

    public function getMiDeck(Request $request, $eventId)
    {
        $userId = auth()->id();
        $event = Event::find($eventId);
        
        if ($request->filled('user_id') && auth()->check() && $event && $this->canManageEvent($event)) {
            $userId = $request->query('user_id');
        }

        $deck = \App\Models\TournamentResult::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->whereNotNull('blade')
            ->orderBy('id', 'asc')
            ->get();
            
        return response()->json($deck);
    }

    public function asignarWarning($matchId, \Illuminate\Http\Request $request)
    {
        $match = \App\Models\TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $playerKey = $request->player;
        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

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

        $matchCompleted = false;
        
        if ($isTeamMatch) {
            $limit = max(count($team1Users), count($team2Users));
            $allBattles = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->get();
            $matchesPlayed = $allBattles->where('finish_type', '!=', 'warning_p1')->where('finish_type', '!=', 'warning_p2')->where('finish_type', '!=', 'penalty')->count();
            
            if ($matchesPlayed >= $limit && $p1_score !== $p2_score) {
                $match->status = 'completed';
                $match->winner_id = $p1_score > $p2_score ? $match->team1_id : $match->team2_id;
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

        if ($isTeamMatch && $matchCompleted) {
            $this->syncTeamMonthlyLimits($match->event_id);
        }

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
        
        $limite = 9999;
        if ($event->has_stadium_limit || !empty($event->max_players)) {
            if ($event->stadiums == 1) $limite = 20;
            elseif ($event->stadiums == 2) $limite = 35;
            elseif ($event->stadiums == 3) $limite = 45;
            elseif ($event->stadiums == 4) $limite = 55;
            elseif ($event->stadiums >= 5) $limite = 64;
            if (!empty($event->max_players)) $limite = $event->max_players;
        }

        $jugadoresRaw = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->join('users', 'assist_user_event.user_id', '=', 'users.id')
            ->leftJoin('team_user', 'users.id', '=', 'team_user.user_id')
            ->leftJoin('teams', 'team_user.team_id', '=', 'teams.id')
            ->where('assist_user_event.event_id', $eventId)
            ->select(
                'users.id',
                'users.name',
                'assist_user_event.puesto',
                'teams.id as team_id',
                'teams.name as team_name'
            )
            ->orderBy('assist_user_event.created_at', 'asc')
            ->get();

        $decksCount = \App\Models\TournamentResult::where('event_id', $eventId)
            ->whereNotNull('blade')
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $isStaff = $this->canManageEvent($event);
        $isTeamEvent = in_array($event->beys, ['equipos', 'crew_battle']) || str_starts_with($event->name, 'Duelo Express:');
        $requiredBeys = $isTeamEvent ? 1 : 3;
        
        $jugadores = [];
        $nombresTxt = "";
        $inscribedIds = [];
        $activeCount = 0;
        
        foreach ($jugadoresRaw as $user) {
            $isDropped = ($user->puesto === 'dropped');
            $piezasRegistradas = $decksCount[$user->id] ?? 0;
            
            $bracketStatus = 'dropped';
            $activePosition = null;

            if (!$isDropped) {
                $activeCount++;
                $activePosition = $activeCount;
                if ($activeCount <= $limite) {
                    $bracketStatus = 'entra';
                    $nombresTxt .= $user->name . "\n";
                } else {
                    $bracketStatus = 'reserva';
                }
            }

            $jugadores[] = [
                'id' => $user->id,
                'name' => $user->name,
                'puesto' => $isDropped ? 'dropped' : 'active',
                'bracket_status' => $bracketStatus,
                'active_pos' => $activePosition,
                'reserva_num' => ($bracketStatus === 'reserva') ? ($activeCount - $limite) : null,
                'has_deck' => $piezasRegistradas >= $requiredBeys,
                'team_id' => $user->team_id,
                'team_name' => $user->team_name ?? 'SIN EQUIPO / AGENTE LIBRE'
            ];
            
            $inscribedIds[] = $user->id;
        }

        $availableUsers = [];
        if ($isStaff) {
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
            'is_team_event' => $isTeamEvent,
            'event_type' => $event->beys,
            'event_status' => $event->status,
            'aforo_maximo' => $limite,
            'available_users' => $availableUsers
        ]);
    }

    public function addJugador(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado'], 403);
        }

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Selecciona un jugador']);
        }

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
        $event = \App\Models\Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado'], 403);
        }
        
        \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->update(['puesto' => 'dropped']);

        return response()->json([
            'success' => true,
            'message' => 'Jugador retirado del torneo exitosamente.'
        ]);
    }

    public function lockMatch($matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false]);
        }
        
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

    public function unlockMatch($matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        
        if ($match->locked_by === auth()->id() || (auth()->check() && auth()->user()->hasRole('admin'))) {
            $match->locked_by = null;
            $match->save();
        }
        
        return response()->json(['success' => true]);
    }

    public function finalizeTournament($eventId)
    {
        $event = Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }
        
        // Detectamos todas las partidas de equipos completadas en este evento
        $teamMatches = TournamentMatch::where('event_id', $eventId)
            ->whereNotNull('team1_id')
            ->whereNotNull('team2_id')
            ->where('status', 'completed')
            ->get();

        $volcados = 0;

        if ($teamMatches->isNotEmpty() || in_array($event->beys, ['equipos', 'crew_battle'])) {
            $primer_dia_mes_actual = \Carbon\Carbon::now()->startOfMonth();
            $ultimo_dia_mes_actual = \Carbon\Carbon::now()->endOfMonth();
                
            foreach ($teamMatches as $tm) {
                $ya_existe_este_mes = \Illuminate\Support\Facades\DB::table('teams_versus')
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
                    ->exists();
                    
                if (!$ya_existe_este_mes) {
                    \Illuminate\Support\Facades\DB::table('teams_versus')->insert([
                        'team_id_1'  => $tm->team1_id,
                        'team_id_2'  => $tm->team2_id,
                        'result_1'   => $tm->player1_score,
                        'result_2'   => $tm->player2_score,
                        'matchup'    => $event->mode ?? 'beybladex', 
                        'url'        => url("/events/{$eventId}"),
                        'status'     => 'OPEN',
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ]);
                    $volcados++;
                }
            }
        }

        // Guardamos automáticamente el link del Cuadro Oficial en challonge si estaba vacío
        $hasBracketMatches = TournamentMatch::where('event_id', $eventId)
            ->whereNotIn('bracket_type', ['swiss', 'round_robin'])
            ->exists();

        if ($hasBracketMatches && empty($event->challonge)) {
            $event->challonge = route('torneo.bracket', $event->id);
        }

        // Cerramos el torneo (En Ranking pasa a PENDING para que pueda validarse/repartir puntos en la web, o COMPLETED)
        $event->status = in_array($event->beys, ['ranking', 'rankingplus']) ? 'PENDING' : 'COMPLETED';
        $event->save();

        // Si es un torneo individual, asignamos automáticamente los puestos hasta el 17º en assist_user_event
        if ($teamMatches->isEmpty() && !in_array($event->beys, ['equipos', 'crew_battle'])) {
            try {
                $standingsData = $this->getStandings($event->id)->getData(true);
                $clasificacion = $standingsData['clasificacion'] ?? [];
                
                $mapaPuestos = [
                    0  => 'primero', 
                    1  => 'segundo', 
                    2  => 'tercero', 
                    3  => 'cuarto',
                    4  => 'quinto',
                    5  => 'sexto',
                    6  => 'septimo',
                    7  => 'octavo',
                    8  => 'noveno',
                    9  => 'decimo',
                    10 => 'undecimo',
                    11 => 'duodecimo',
                    12 => 'decimotercero',
                    13 => 'decimocuarto',
                    14 => 'decimoquinto',
                    15 => 'decimosexto',
                    16 => 'decimoseptimo'
                ];

                foreach ($mapaPuestos as $idx => $nombrePuesto) {
                    if (isset($clasificacion[$idx])) {
                        \Illuminate\Support\Facades\DB::table('assist_user_event')
                            ->where('event_id', $event->id)
                            ->where('user_id', $clasificacion[$idx]['id'])
                            ->where(function ($q) {
                                $q->whereNull('puesto')
                                  ->orWhere('puesto', '')
                                  ->orWhere('puesto', 'active')
                                  ->orWhere('puesto', 'participante');
                            })
                            ->update(['puesto' => $nombrePuesto]);
                    }
                }

                // 🚀 ENVIAR REPORTE CON PODIO, DECKS Y STATS A DISCORD
                $this->sendDiscordTournamentReport($event);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Error post-cierre torneo: " . $e->getMessage());
            }
        }

        $msg = $teamMatches->isNotEmpty()
            ? "🏆 Torneo finalizado. ¡{$volcados} duelo(s) de equipos generados en estado OPEN para validación!"
            : "🏆 Acta cerrada, posiciones guardadas y enlace del cuadro vinculado.";

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function createFastDuel(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado'], 403);
        }
        
        $request->validate([
            'team1_id' => 'required',
            'team2_id' => 'required|different:team1_id'
        ]);

        $t1 = \App\Models\Team::select('id', 'name')->findOrFail($request->team1_id);
        $t2 = \App\Models\Team::select('id', 'name')->findOrFail($request->team2_id);

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
            
            \App\Models\TournamentResult::create([
                'user_id' => $uid,
                'event_id' => $event->id,
                'blade' => 'DESCONOCIDO',
                'ratchet' => '-',
                'bit' => '-',
                'victorias' => 0, 'derrotas' => 0, 'puntos_ganados' => 0, 'puntos_perdidos' => 0
            ]);
        }

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

        return response()->json(['success' => true, 'event_id' => $event->id]);
    }

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

    public function avanzarPartidaBracket($matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }
        
        if ($match->status !== 'completed' || in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            return response()->json(['success' => false, 'message' => 'Esta partida aún no se puede avanzar.'], 400);
        }

        $alreadyAdvanced = TournamentMatch::where('event_id', $match->event_id)
            ->where('round_number', '>', $match->round_number)
            ->whereIn('bracket_type', ['winners', 'losers', 'grand_final', 'grand_final_reset'])
            ->where(function($q) use ($match) {
                $q->where('player1_id', $match->winner_id)
                  ->orWhere('player2_id', $match->winner_id);
            })
            ->exists();

        if ($alreadyAdvanced) {
            return response()->json([
                'success' => false, 
                'already_advanced' => true,
                'message' => '⚠️ Este jugador ya había sido avanzado anteriormente en el cuadro.'
            ]);
        }
        
        $engine = new \App\Services\Tournament\DoubleEliminationEngine();
        $engine->progressMatch($match);
        
        return response()->json(['success' => true, 'message' => '¡Jugador avanzado en el cuadro!']);
    }

    // CONSTRUCCIÓN DEL ÁRBOL CON ETIQUETAS ("GANADOR #X" / "PERDEDOR #Y") Y DESTINOS FIJOS
    private function buildFullBracketTree($eventId)
    {
        $allBracketMatches = \App\Models\TournamentMatch::with(['player1:id,name', 'player2:id,name', 'team1:id,name', 'team2:id,name'])
            ->where('event_id', $eventId)
            ->whereNotIn('bracket_type', ['swiss', 'round_robin'])
            ->orderBy('round_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($allBracketMatches->isEmpty()) {
            return [collect(), collect(), collect()];
        }

        $engine = new \App\Services\Tournament\DoubleEliminationEngine();
        $topology = $engine->buildBracketTopology($eventId);

        $winnersAll = $allBracketMatches->where('bracket_type', 'winners')->groupBy('round_number');
        if ($winnersAll->isEmpty()) {
            return [collect(), collect(), collect()];
        }

        $firstRound = $winnersAll->keys()->min();

        $visibleMatches = $allBracketMatches->filter(function ($m) use ($topology) {
            return empty($topology['hidden'][$m->id]);
        });

        foreach ($visibleMatches as $m) {
            $m->match_number = $topology['numbers'][$m->id] ?? $m->id;
            $m->p1_placeholder = $topology['sources'][$m->id][1] ?? 'ESPERANDO...';
            $m->p2_placeholder = $topology['sources'][$m->id][2] ?? 'ESPERANDO...';
            $m->next_win_match_id = $topology['targets'][$m->id]['win']['match_id'] ?? null;
            $m->next_lose_match_id = $topology['targets'][$m->id]['lose']['match_id'] ?? null;
        }

        $winnersRaw = $visibleMatches->where('bracket_type', 'winners')->groupBy('round_number');
        $losersRaw = $visibleMatches->where('bracket_type', 'losers')->groupBy('round_number');
        $finals = $visibleMatches->whereIn('bracket_type', ['grand_final', 'grand_final_reset'])->values();

        $winnersBracket = collect();
        foreach ($winnersRaw as $absRound => $matches) {
            $relRound = $absRound - $firstRound + 1;
            $winnersBracket->put($relRound, $matches->values());
        }

        $losersBracket = collect();
        foreach ($losersRaw as $absRound => $matches) {
            $relRound = $absRound - $firstRound + 1;
            $losersBracket->put($relRound, $matches->values());
        }

        return [$winnersBracket, $losersBracket, $finals];
    }

    public function showBracket($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        [$winnersBracket, $losersBracket, $finals] = $this->buildFullBracketTree($eventId);

        return view('hub.bracket', compact('event', 'winnersBracket', 'losersBracket', 'finals'));
    }

    public function renderBracketHtml($eventId)
    {
        [$winnersBracket, $losersBracket, $finals] = $this->buildFullBracketTree($eventId);

        return view('hub.partials.bracket_content', compact('winnersBracket', 'losersBracket', 'finals'));
    }

    private function sendDiscordTournamentReport(Event $event)
    {
        // Solo enviamos reporte para torneos individuales de Ranking (o quita este if si lo quieres en todos)
        if (!in_array($event->beys, ['ranking', 'rankingplus', 'copaespecial1'])) {
            return;
        }

        if (config('app.env') !== 'production' || !env('DISCORD_WEBHOOK_URL')) {
            // return; // <-- Comenta esta línea si quieres probarlo en local
        }

        $event->loadMissing('region');
        $regionName = $event->region->name ?? 'Global';
        $rolId = \App\Http\Controllers\EventController::DISCORD_ROLES[$regionName] ?? '';
        $mention = $rolId ? "<@&{$rolId}>" : '';

        // 1. OBTENER EL PODIO (TOP 3) USANDO EL MOTOR DE CLASIFICACIÓN
        $standingsResponse = $this->getStandings($event->id)->getData(true);
        $clasificacion = $standingsResponse['clasificacion'] ?? [];
        $top3 = array_slice($clasificacion, 0, 3);

        if (empty($top3)) return;

        // Función auxiliar para formatear el Deck de un jugador
        $formatPlayerDeck = function ($userId) use ($event) {
            $combos = \App\Models\TournamentResult::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->whereNotNull('blade')
                ->orderBy('id', 'asc')
                ->limit(3)
                ->get();

            if ($combos->isEmpty()) return "_Sin deck registrado_";

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
                'inline' => false
            ];
        }

        // 2. ESTADÍSTICAS GLOBALES DE COMBATE DEL TORNEO
        $battleStats = \Illuminate\Support\Facades\DB::table('match_battles as mb')
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

        // 3. BEYBLADE MVP (EL COMBO CON MÁS VICTORIAS EN EL TORNEO)
        $mvpCombo = \Illuminate\Support\Facades\DB::selectOne("
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

        // 4. FRANCOTIRADOR XTREME (JUGADOR CON MÁS XTREME FINISHES)
        $xtremeKing = \Illuminate\Support\Facades\DB::table('match_battles as mb')
            ->join('tournament_matches as tm', 'mb.tournament_match_id', '=', 'tm.id')
            ->join('users as u', 'mb.winner_id', '=', 'u.id')
            ->where('tm.event_id', $event->id)
            ->where('mb.finish_type', 'xtreme')
            ->select('u.name', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_xtremes'))
            ->groupBy('u.id', 'u.name')
            ->orderByDesc('total_xtremes')
            ->first();

        // Añadimos los bloques de estadísticas al Embed
        if ($mvpCombo) {
            $podiumFields[] = [
                'name'   => '🔥 BEYBLADE MVP DEL TORNEO',
                'value'  => "**{$mvpCombo->combo_name}** (de *{$mvpCombo->owner_name}*)\n`{$mvpCombo->wins} victorias` · `{$mvpCombo->pts} pts generados`",
                'inline' => true
            ];
        }

        if ($xtremeKing && $xtremeKing->total_xtremes > 0) {
            $podiumFields[] = [
                'name'   => '⚡ REY DEL XTREME',
                'value'  => "**{$xtremeKing->name}**\n`{$xtremeKing->total_xtremes} Xtreme Finishes`",
                'inline' => true
            ];
        }

        if ($battleStats && $battleStats->total_bouts > 0) {
            $podiumFields[] = [
                'name'   => "📊 TELEMETRÍA DE LA ARENA ({$battleStats->total_bouts} lanzamientos)",
                'value'  => "💥 **Xtreme:** `{$battleStats->xtremes}`  |  💣 **Burst:** `{$battleStats->bursts}`  |  🚀 **Over:** `{$battleStats->overs}`  |  🌀 **Spin:** `{$battleStats->spins}`",
                'inline' => false
            ];
        }

        $totalPlayers = count($clasificacion);
        $bracketUrl = route('torneo.bracket', $event->id);

        $embed = [
            'title'       => "🏆 CRÓNICA OFICIAL: {$event->name}",
            'url'         => $bracketUrl,
            'description' => "¡Torneo finalizado en **{$event->city}** ({$regionName}) con **{$totalPlayers} Bladers** participantes!\nAquí tenéis el podio oficial, los decks campeones y el resumen estadístico de la jornada:",
            'color'       => 16766720, // Dorado (#FFD700)
            'fields'      => $podiumFields,
            'footer'      => [
                'text' => "SBBL Hub • Temporada 3 • Pulsa en el título para ver el cuadro completo"
            ],
            'timestamp'   => now()->toIso8601String()
        ];

        try {
            \Illuminate\Support\Facades\Http::post(env('DISCORD_WEBHOOK_URL'), [
                'content' => "📢 **¡ACTA CERRADA Y RESULTADOS OFICIALES!** {$mention}",
                'embeds'  => [$embed],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error enviando reporte final a Discord: " . $e->getMessage());
        }
    }
}