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

    // 1. REGISTRAR CHOQUE NORMAL (CON AVANCE AUTOMÁTICO Y PROTECCIÓN DE EDICIÓN)
    public function storeBattle(Request $request, $matchId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $match = TournamentMatch::findOrFail($matchId);
        $wasCompletedBefore = ($match->status === 'completed');

        // Si la partida ya estaba terminada y la están editando, limpiamos su avance previo por seguridad
        if ($wasCompletedBefore && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }
        
        $points = 0;
        $finishType = $request->finish_type; // 'spin', 'over', 'burst', 'xtreme'
        
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

        if ($isTeamMatch) {
            $winnerTeam = \Illuminate\Support\Facades\DB::table('team_user')->where('user_id', $request->winner_id)->value('team_id');
            if ($winnerTeam == $match->team1_id) $match->player1_score += $points;
            if ($winnerTeam == $match->team2_id) $match->player2_score += $points;
        } else {
            // Normal 1vs1 (CAPADO A 4 PUNTOS)
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
                $match->winner_id = $match->player1_score > $match->player2_score ? $match->team1_id : ($match->player2_score > $match->player1_score ? $match->team2_id : null);
                $match->status = 'completed';
                $matchCompleted = true;
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

        // AVANCE AUTOMÁTICO EN EL CUADRO AL TERMINAR LA PARTIDA
        if ($matchCompleted && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Resultado registrado', 
            'match_completed' => $matchCompleted,
            'p1_score' => $match->player1_score,
            'p2_score' => $match->player2_score
        ]);
    }

    // 2. DESHACER ÚLTIMO REGISTRO (CON REVERSIÓN AUTOMÁTICA DEL CUADRO SI ESTABA COMPLETADA)
    public function deshacerUltimoRegistro($matchId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $match = TournamentMatch::findOrFail($matchId);

        // Si la partida había finalizado y avanzado jugadores en el bracket, deshacemos ese avance primero
        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }

        $lastBattle = \App\Models\MatchBattle::where('tournament_match_id', $matchId)
            ->orderBy('id', 'desc')
            ->first();

        // Si no había batallas con stats (porque fue "No presentado" o "Marcador Manual"), reseteamos el marcador a 0-0
        if (!$lastBattle) {
            if ($match->player1_score > 0 || $match->player2_score > 0 || $match->status === 'completed') {
                $match->player1_score = 0;
                $match->player2_score = 0;
                $match->winner_id = null;
                $match->status = 'playing';
                $match->save();
                return response()->json(['success' => true, 'message' => '↩️ Resultado manual/rápido anulado y avance revertido.']);
            }
            return response()->json(['success' => false, 'message' => 'No hay acciones para deshacer en esta partida.']);
        }

        $lastBattle->delete();

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
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

        // Si tras deshacer sigue estando completada (ej. tenía puntos extra), volvemos a asegurar el avance
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
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $match = TournamentMatch::findOrFail($matchId);

        // Si ya estaba completada, revertimos primero su avance anterior para no duplicar
        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }

        $winnerSide = $request->winner_side; // 'p1' o 'p2'
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

        if (!in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true,
            'message' => '✅ Incomparecencia registrada. El rival avanza automáticamente.',
            'match_completed' => true
        ]);
    }

    // 4. NUEVO: MARCADOR MANUAL SIN STATS (EJ: 4-2 DIRECTO CUANDO HAY PRISAS)
    public function setManualScore(Request $request, $matchId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $match = TournamentMatch::findOrFail($matchId);
        $s1 = max(0, min(4, (int) $request->p1_score));
        $s2 = max(0, min(4, (int) $request->p2_score));

        if ($s1 < 4 && $s2 < 4) {
            return response()->json(['success' => false, 'message' => '⚠️ Para finalizar manualmente, uno de los jugadores debe llegar a 4 puntos.'], 422);
        }
        if ($s1 === 4 && $s2 === 4) {
            return response()->json(['success' => false, 'message' => '⚠️ No puede haber un empate 4 a 4.'], 422);
        }

        // Si ya estaba completada antes, revertimos su avance previo antes de poner al nuevo ganador
        if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->revertMatchProgression($match);
        }

        // Limpiamos batallas sueltas para que no choquen con el marcador manual
        \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $match->id)->delete();

        $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
        $match->player1_score = $s1;
        $match->player2_score = $s2;
        $match->winner_id = ($s1 > $s2) ? ($isTeamMatch ? $match->team1_id : $match->player1_id) : ($isTeamMatch ? $match->team2_id : $match->player2_id);
        $match->status = 'completed';
        $match->locked_by = null;
        $match->save();

        if (!in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            $this->safeProgressMatch($match);
        }

        return response()->json([
            'success' => true,
            'message' => "✅ Marcador manual ($s1 - $s2) guardado y cuadro actualizado.",
            'match_completed' => true
        ]);
    }

    // 5. MOTOR DE AVANCE SEGURO (EVITA DUPLICADOS SI YA ESTABA EN LA SIGUIENTE RONDA)
    private function safeProgressMatch(TournamentMatch $match)
    {
        if (!$match->winner_id) return;

        $alreadyAdvanced = TournamentMatch::where('event_id', $match->event_id)
            ->where('id', '!=', $match->id)
            ->where('round_number', '>=', $match->round_number)
            ->whereIn('bracket_type', ['winners', 'losers', 'grand_final', 'grand_final_reset'])
            ->where(function($q) use ($match) {
                $q->where('player1_id', $match->winner_id)
                  ->orWhere('player2_id', $match->winner_id);
            })
            ->where(function($q) use ($match) {
                // Solo contar rondas posteriores (o caída a losers de la misma ronda base)
                $q->where('round_number', '>', $match->round_number)
                  ->orWhere('bracket_type', '!=', $match->bracket_type);
            })
            ->exists();

        if (!$alreadyAdvanced) {
            $engine = new \App\Services\Tournament\DoubleEliminationEngine();
            $engine->progressMatch($match);
        }
    }

    // 6. ROLLBACK INTELIGENTE: RETIRA AL GANADOR/PERDEDOR DE LA RONDA SIGUIENTE SI SE EDITA O HACE UNDO
    private function revertMatchProgression(TournamentMatch $match)
    {
        $p1 = $match->player1_id;
        $p2 = $match->player2_id;
        $participants = array_filter([$p1, $p2]);
        if (empty($participants)) return;

        // Si era la Gran Final y se había generado el Reset, borramos el Reset si está pendiente
        if ($match->bracket_type === 'grand_final') {
            TournamentMatch::where('event_id', $match->event_id)
                ->where('bracket_type', 'grand_final_reset')
                ->where('status', '!=', 'completed')
                ->delete();
            return;
        }

        // Buscamos partidas posteriores donde hayan caído estos jugadores y que aún NO se hayan jugado
        $futureMatches = TournamentMatch::where('event_id', $match->event_id)
            ->where('id', '!=', $match->id)
            ->where('status', '!=', 'completed')
            ->where(function($q) use ($match) {
                $q->where('round_number', '>', $match->round_number)
                  ->orWhere(function($q2) use ($match) {
                      // En Ronda 1 de Winners, el perdedor cae a Ronda 1 de Losers (mismo round_number absoluto)
                      $q2->where('round_number', '=', $match->round_number)
                         ->where('bracket_type', 'losers');
                  });
            })
            ->where(function($q) use ($participants) {
                $q->whereIn('player1_id', $participants)
                  ->orWhereIn('player2_id', $participants);
            })
            ->get();

        foreach ($futureMatches as $fm) {
            if (in_array($fm->player1_id, $participants)) {
                $fm->player1_id = null;
                $fm->player1_score = 0;
            }
            if (in_array($fm->player2_id, $participants)) {
                $fm->player2_id = null;
                $fm->player2_score = 0;
            }

            // Si la partida futura se ha quedado completamente vacía, la eliminamos para limpiar el cuadro
            $s1Empty = is_null($fm->player1_id) && $fm->player1_score != 99;
            $s2Empty = is_null($fm->player2_id) && $fm->player2_score != 99;

            if ($s1Empty && $s2Empty) {
                $fm->delete();
            } else {
                $fm->status = 'pending';
                $fm->winner_id = null;
                $fm->save();
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
                  ->orWhere('assist_user_event.puesto', '!=', 'dropped'); // Ocultar retirados
            })
            ->select('users.id', 'users.name', 'assist_user_event.puesto')
            ->get();
            
        // 1. Inicializar Estadísticas
        $playerStats = [];
        foreach ($users as $u) {
            $playerStats[$u->id] = ['id' => $u->id, 'name' => $u->name, 'points' => 0, 'buchholz' => 0, 'bracket_score' => 0, 'puesto' => $u->puesto];
        }
        
        // 2. Calcular Puntos Fase Suiza (IGNORANDO BYES)
        $swissMatches = $matches->whereIn('bracket_type', ['swiss', 'round_robin']);
        foreach ($swissMatches as $m) {
            // Solo puntuamos si hay 2 jugadores reales (sin Byes)
            if ($m->player1_id && $m->player2_id) { 
                if ($m->winner_id == $m->player1_id) $playerStats[$m->player1_id]['points'] += 3;
                if ($m->winner_id == $m->player2_id) $playerStats[$m->player2_id]['points'] += 3;
                // Empates
                if (is_null($m->winner_id) || $m->winner_id == 0) {
                    $playerStats[$m->player1_id]['points'] += 1;
                    $playerStats[$m->player2_id]['points'] += 1;
                }
            }
        }
        
        // 3. Calcular Buchholz Real
        foreach ($swissMatches as $m) {
            if ($m->player1_id && $m->player2_id) {
                $playerStats[$m->player1_id]['buchholz'] += $playerStats[$m->player2_id]['points'];
                $playerStats[$m->player2_id]['buchholz'] += $playerStats[$m->player1_id]['points'];
            }
        }
        
        // 4. LÓGICA AVANZADA DE ELIMINATORIA (TOP CUT)
        if ($hasBracket) {
            $hasReset = $matches->where('bracket_type', 'grand_final_reset')->count() > 0;
            
            foreach ($playerStats as $uid => &$stats) {
                $bMatches = $matches->filter(fn($m) => in_array($m->bracket_type, ['winners', 'losers', 'grand_final', 'grand_final_reset', 'single_elimination']) && ($m->player1_id == $uid || $m->player2_id == $uid));
                
                if ($bMatches->count() > 0) {
                    $wonGFReset = $bMatches->where('bracket_type', 'grand_final_reset')->where('winner_id', $uid)->count() > 0;
                    $lostGFReset = $bMatches->where('bracket_type', 'grand_final_reset')->where('winner_id', '!=', $uid)->count() > 0;
                    $wonGF = $bMatches->where('bracket_type', 'grand_final')->where('winner_id', $uid)->count() > 0;
                    $lostGF = $bMatches->where('bracket_type', 'grand_final')->where('winner_id', '!=', $uid)->count() > 0;
                    
                    // Otorgamos puntuación artificial brutal según hasta dónde sobrevivieron
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
                                $stats['bracket_score'] = 1000 + $maxWinRound; // Está aún jugando en Winners
                            }
                        }
                    }
                }
            }
        }
        
        $standings = array_values($playerStats);
        
        // 5. Motor de Ordenación
        usort($standings, function($a, $b) {
            // Prioridad 1: Si el juez puso "1º Lugar" a mano en la Lista
            $puestos = ['primero' => 1, 'segundo' => 2, 'tercero' => 3, 'cuarto' => 4];
            $posA = $puestos[$a['puesto']] ?? 999;
            $posB = $puestos[$b['puesto']] ?? 999;
            
            if ($posA != $posB) return $posA <=> $posB;
            
            // Prioridad 2: Quién llegó más lejos en la Eliminatoria
            if ($a['bracket_score'] != $b['bracket_score']) return $b['bracket_score'] <=> $a['bracket_score'];
            
            // Prioridad 3: Puntos y Buchholz del Suizo
            if ($a['points'] != $b['points']) return $b['points'] <=> $a['points'];
            return $b['buchholz'] <=> $a['buchholz'];
        });
        
        return response()->json([
            'success' => true,
            'is_bracket' => $hasBracket, // Le avisamos al frontend
            'clasificacion' => $standings
        ]);
    }

    public function generateTop(Request $request, $eventId, \App\Services\Tournament\DoubleEliminationEngine $engine)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) return response()->json(['success' => false], 403);
        $event = \App\Models\Event::findOrFail($eventId);
        return response()->json($engine->generateTopCut($event, $request->top_size));
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

    public function avanzarPartidaBracket($matchId)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }
        
        $match = TournamentMatch::findOrFail($matchId);
        
        if ($match->status !== 'completed' || in_array($match->bracket_type, ['swiss', 'round_robin'])) {
            return response()->json(['success' => false, 'message' => 'Esta partida aún no se puede avanzar.'], 400);
        }

        // PROTECCIÓN ANTI-SPAM: Comprobamos si el ganador ya fue colocado en una ronda superior del bracket
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

    // GENERADOR MATEMÁTICO DEL ÁRBOL COMPLETO (OCULTA TODOS LOS BYES Y RELLENA HUECOS FUTUROS)
    private function buildFullBracketTree($eventId)
    {
        // 1. Cargamos todas las partidas del bracket (incluyendo equipos y jugadores)
        $allBracketMatches = \App\Models\TournamentMatch::with(['player1', 'player2', 'team1', 'team2'])
            ->where('event_id', $eventId)
            ->whereNotIn('bracket_type', ['swiss', 'round_robin'])
            ->orderBy('round_number')
            ->orderBy('id')
            ->get();

        if ($allBracketMatches->isEmpty()) {
            return [collect(), collect(), collect()];
        }

        // 2. Calculamos el tamaño real del cuadro ANTES de ocultar los Byes
        $winnersAll = $allBracketMatches->where('bracket_type', 'winners')->groupBy('round_number');
        if ($winnersAll->isEmpty()) {
            return [collect(), collect(), collect()];
        }

        $firstRound = $winnersAll->keys()->min();
        $r1Count = $winnersAll->get($firstRound)->count(); 
        $bracketSize = max(2, pow(2, ceil(log($r1Count * 2, 2))));
        $totalWinnersRounds = (int) log($bracketSize, 2);
        $totalLosersRounds = ($totalWinnersRounds <= 1) ? 1 : ($totalWinnersRounds - 1) * 2;

        // 3. FILTRO ANTI-BYES: Ocultamos tanto los dobles fantasmas (99 vs 99) como los Byes individuales de R1
        $visibleMatches = $allBracketMatches->filter(function ($m) {
            $isTeam = !is_null($m->team1_id) || !is_null($m->team2_id);
            $p1Id = $isTeam ? $m->team1_id : $m->player1_id;
            $p2Id = $isTeam ? $m->team2_id : $m->player2_id;

            $p1Ghost = is_null($p1Id) && $m->player1_score == 99;
            $p2Ghost = is_null($p2Id) && $m->player2_score == 99;

            // Si ambos son fantasmas (99 vs 99), ocultar
            if ($p1Ghost && $p2Ghost) return false;

            // Si la partida se autocompletó por Bye (falta un rival o uno de los dos es fantasma 99), ocultar del árbol
            if ($m->status === 'completed' && ($p1Ghost || $p2Ghost || is_null($p1Id) || is_null($p2Id))) {
                return false;
            }

            return true;
        });

        $winnersRaw = $visibleMatches->where('bracket_type', 'winners')->groupBy('round_number');
        $losersRaw = $visibleMatches->where('bracket_type', 'losers')->groupBy('round_number');
        $finalsRaw = $visibleMatches->whereIn('bracket_type', ['grand_final', 'grand_final_reset'])->values();

        $winnersBracket = collect();
        $losersBracket = collect();

        // 4. Construir todas las rondas de Winners (reales jugables + cajas futuras pendientes)
        for ($r = 1; $r <= $totalWinnersRounds; $r++) {
            $absRound = $firstRound + $r - 1;
            $expectedMatches = (int) ($bracketSize / pow(2, $r));
            
            $allInThisRound = $winnersAll->get($absRound, collect());
            $visibleInThisRound = $winnersRaw->get($absRound, collect())->values();
            
            $roundCol = collect();

            if ($allInThisRound->isEmpty()) {
                for ($m = 0; $m < $expectedMatches; $m++) {
                    $roundCol->push($this->makePlaceholderMatch("placeholder-w-{$r}-{$m}", $absRound, 'winners'));
                }
            } else {
                foreach ($visibleInThisRound as $match) {
                    $roundCol->push($match);
                }
                // Calculamos huecos pendientes usando el total en BD (para no rellenar con placeholders los Byes ocultos)
                $missingSlots = max(0, $expectedMatches - $allInThisRound->count());
                for ($m = 0; $m < $missingSlots; $m++) {
                    $roundCol->push($this->makePlaceholderMatch("placeholder-w-{$r}-extra-{$m}", $absRound, 'winners'));
                }
            }

            if ($roundCol->isNotEmpty()) {
                $winnersBracket->put($r, $roundCol);
            }
        }

        // 5. Construir todas las rondas de Losers (reales jugables + cajas futuras pendientes)
        $losersAll = $allBracketMatches->where('bracket_type', 'losers')->groupBy('round_number');
        
        for ($lr = 1; $lr <= $totalLosersRounds; $lr++) {
            $absRound = $firstRound + $lr - 1;
            $power = ceil($lr / 2) + 1;
            $expectedMatches = (int) max(1, $bracketSize / pow(2, $power));
            
            $allInThisRound = $losersAll->get($absRound, collect());
            $visibleInThisRound = $losersRaw->get($absRound, collect())->values();
            
            $roundCol = collect();

            if ($allInThisRound->isEmpty()) {
                for ($m = 0; $m < $expectedMatches; $m++) {
                    $roundCol->push($this->makePlaceholderMatch("placeholder-l-{$lr}-{$m}", $absRound, 'losers'));
                }
            } else {
                foreach ($visibleInThisRound as $match) {
                    $roundCol->push($match);
                }
                $missingSlots = max(0, $expectedMatches - $allInThisRound->count());
                for ($m = 0; $m < $missingSlots; $m++) {
                    $roundCol->push($this->makePlaceholderMatch("placeholder-l-{$lr}-extra-{$m}", $absRound, 'losers'));
                }
            }

            if ($roundCol->isNotEmpty()) {
                $losersBracket->put($lr, $roundCol);
            }
        }

        // 6. Construir Gran Final
        $finals = $finalsRaw;
        if ($finals->isEmpty()) {
            $finals = collect([
                $this->makePlaceholderMatch('placeholder-gf-1', $firstRound + $totalLosersRounds, 'grand_final')
            ]);
        }

        return [$winnersBracket, $losersBracket, $finals];
    }

    // Helper para generar las cajas vacías de rondas futuras
    private function makePlaceholderMatch($id, $roundNumber, $bracketType)
    {
        return (object)[
            'id' => $id,
            'match_number' => null,
            'round_number' => $roundNumber,
            'bracket_type' => $bracketType,
            'player1_id' => null,
            'player2_id' => null,
            'team1_id' => null,
            'team2_id' => null,
            'player1' => null,
            'player2' => null,
            'team1' => null,
            'team2' => null,
            'player1_score' => 0,
            'player2_score' => 0,
            'winner_id' => null,
            'status' => 'placeholder',
            'locked_by' => null,
            'is_placeholder' => true
        ];
    }

    public function showBracket($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        [$winnersBracket, $losersBracket, $finals] = $this->buildFullBracketTree($eventId);

        return view('hub.bracket', compact('event', 'winnersBracket', 'losersBracket', 'finals'));
    }

    public function renderBracketHtml($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        [$winnersBracket, $losersBracket, $finals] = $this->buildFullBracketTree($eventId);

        return view('hub.partials.bracket_content', compact('event', 'winnersBracket', 'losersBracket', 'finals'));
    }
}