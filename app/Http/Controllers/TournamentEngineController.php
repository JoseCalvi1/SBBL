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
        $matches = TournamentMatch::with(['player1.profile', 'player2.profile', 'lockedByUser'])
            ->where('event_id', $eventId)
            ->where('round_number', $requestedRound)
            ->where('player1_score', '!=', 99) 
            ->where('player2_score', '!=', 99)
            ->where(function ($query) {
                // 1. Partidas listas para jugar (ambos rivales presentes)
                $query->where(function ($q) {
                    $q->whereNotNull('player1_id')->whereNotNull('player2_id');
                })
                // 2. Partidas de Doble Eliminación en espera de rivales (status pending)
                ->orWhere(function ($q) {
                    $q->where('bracket_type', '!=', 'swiss')
                      ->where('status', 'pending');
                });
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

    // NUEVO: Ejecuta la creación de la ronda
    public function generateRound($eventId, SwissEngine $engine)
    {
        // Seguridad: Solo Staff
        if (!auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro'])) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $event = Event::findOrFail($eventId);
        
        // El motor hace toda la magia y nos devuelve el resultado
        $result = $engine->generateNextRound($event);

        return response()->json($result);
    }

    // Obtiene los detalles de una partida y los Decks de los jugadores
    public function getMatchDetails($matchId)
    {
        $match = \App\Models\TournamentMatch::with(['player1', 'player2'])->findOrFail($matchId);

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

        // NUEVO: Obtenemos los choques jugados para saber qué beys ya se han usado en el 3on3
        $battles = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'match' => $match,
            'p1_deck' => $p1Deck,
            'p2_deck' => $p2Deck,
            'battles' => $battles // <-- Lo enviamos al frontend
        ]);
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
        if ($request->winner_id == $match->player1_id) {
            $match->player1_score += $points;
        } elseif ($request->winner_id == $match->player2_id) {
            $match->player2_score += $points;
        }
        
        // Comprobar si alguien ha ganado la partida (ej: llega a 4 o 7 puntos)
        $limit = 4;
        $matchCompleted = false;

        if ($match->player1_score >= $limit || $match->player2_score >= $limit) {
            $match->winner_id = $match->player1_score >= $limit ? $match->player1_id : $match->player2_id;
            $match->status = 'completed';
            $matchCompleted = true;
        } else {
            $match->status = 'playing';
        }

        $match->save();

        // MAGIA DOBLE ELIMINACIÓN: Si la partida terminó y estamos en fase eliminatoria, avanzamos a los jugadores
        if ($matchCompleted && $match->bracket_type !== 'swiss') {
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
    // Calcula la clasificación en vivo (Puntos y Buchholz)
    public function getStandings($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        
        // Solo contamos las partidas terminadas
        $matches = \App\Models\TournamentMatch::where('event_id', $eventId)
            ->where('status', 'completed')
            ->get();
            
        // Cargamos todos los jugadores inscritos (con su perfil para el avatar)
        $players = $event->users()->with('profile')->get();

        $stats = [];
        // 1. Inicializamos los marcadores a 0
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

        // 2. Sumamos los puntos y registramos contra quién ha jugado cada uno
        foreach ($matches as $m) {
            // Validación ESTRICTA para evitar el error null == null y jugadores Dropeados
            if ($m->winner_id !== null) {
                if ($m->player1_id && isset($stats[$m->player1_id]) && $m->winner_id == $m->player1_id) {
                    $stats[$m->player1_id]['points'] += 3;
                } 
                elseif ($m->player2_id && isset($stats[$m->player2_id]) && $m->winner_id == $m->player2_id) {
                    $stats[$m->player2_id]['points'] += 3;
                }
            }
            
            // Si no fue un Bye y ambos jugadores existen en los stats (no han sido borrados)
            if ($m->player1_id && $m->player2_id) { 
                if (isset($stats[$m->player1_id]) && isset($stats[$m->player2_id])) {
                    $stats[$m->player1_id]['opponents'][] = $m->player2_id;
                    $stats[$m->player2_id]['opponents'][] = $m->player1_id;
                }
            }
        }

        // 3. Calculamos el Buchholz (Resistencia): Sumamos los puntos de todos sus rivales
        foreach ($stats as $id => &$data) {
            foreach ($data['opponents'] as $oppId) {
                $data['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
        }

        // 4. Ordenamos: 1º por Puntos (mayor a menor), 2º por Buchholz (mayor a menor)
        usort($stats, function($a, $b) {
            if ($a['points'] === $b['points']) {
                return $b['buchholz'] <=> $a['buchholz']; // Desempate Buchholz
            }
            return $b['points'] <=> $a['points']; // Puntos
        });

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
        
        // Buscar el último registro de batalla de este combate exacto
        $lastBattle = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->orderBy('id', 'desc')
            ->first();
            
        if (!$lastBattle) {
            return response()->json(['success' => false, 'message' => 'No hay registros para deshacer.']);
        }
        
        // Lo eliminamos (sea un punto, un penalty o un warning vacío)
        \Illuminate\Support\Facades\DB::table('match_battles')->where('id', $lastBattle->id)->delete();
        
        // Recalcular puntos
        $p1_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player1_id)->sum('points_awarded') ?? 0;
        $p2_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player2_id)->sum('points_awarded') ?? 0;
            
        $match->player1_score = $p1_score;
        $match->player2_score = $p2_score;
        
        // Si el combate se había cerrado accidentalmente, lo reabrimos
        if ($match->status === 'completed') {
            $match->status = 'playing';
        }
        $match->save();
        
        // Comprobar warnings actuales para devolver la luz al frontend
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
        
        $beneficiaryId = ($playerKey === 'p1') ? $match->player2_id : $match->player1_id;
        $warningType = 'warning_' . $playerKey;

        // Comprobamos si ya tiene tarjeta amarilla
        $existingWarning = \Illuminate\Support\Facades\DB::table('match_battles')
            ->where('tournament_match_id', $matchId)
            ->where('finish_type', $warningType)
            ->first();

        if ($existingWarning) {
            // SEGUNDO WARNING: Borramos el aviso e insertamos penalización
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
            // PRIMER WARNING: Aviso sin puntos
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
        $p1_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player1_id)->sum('points_awarded') ?? 0;
        $p2_score = \Illuminate\Support\Facades\DB::table('match_battles')->where('tournament_match_id', $matchId)->where('winner_id', $match->player2_id)->sum('points_awarded') ?? 0;
        
        $match->player1_score = $p1_score;
        $match->player2_score = $p2_score;

        // COMPROBAR SI LA PARTIDA SE HA ACABADO POR EL WARNING
        $matchCompleted = false;
        if ($p1_score >= 4 || $p2_score >= 4) {
            $match->status = 'completed';
            $match->winner_id = $p1_score >= 4 ? $match->player1_id : $match->player2_id;
            $matchCompleted = true;
            $msg .= " ¡Partida finalizada por penalización!";
        }

        $match->save();

        // Si la partida terminó por penalización en fase eliminatoria, avanzamos a los jugadores
        if ($matchCompleted && $match->bracket_type !== 'swiss') {
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
        $users = $event->users()->get();
        
        // Contamos cuántas piezas tiene registradas cada jugador en este torneo
        $decksCount = \App\Models\TournamentResult::where('event_id', $eventId)
            ->whereNotNull('blade')
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $isStaff = auth()->check() && auth()->user()->hasAnyRole(['admin', 'juez', 'arbitro']);
        
        $jugadores = [];
        $nombresTxt = "";
        
        foreach ($users as $user) {
            $isDropped = (isset($user->pivot->puesto) && $user->pivot->puesto === 'dropped') || 
                         (isset($user->pivot->status) && $user->pivot->status === 'dropped');
            
            $piezasRegistradas = $decksCount[$user->id] ?? 0;

            $jugadores[] = [
                'id' => $user->id,
                'name' => $user->name,
                'puesto' => $isDropped ? 'dropped' : 'active',
                'has_deck' => $piezasRegistradas >= 3 // True si tiene los 3 beys listos
            ];
            
            if (!$isDropped) {
                $nombresTxt .= $user->name . "\n";
            }
        }

        return response()->json([
            'success' => true, 
            'jugadores' => $jugadores,
            'nombres' => $nombresTxt,
            'is_staff' => $isStaff,
            'event_status' => $event->status
        ]);
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
        $event->status = 'COMPLETED'; 
        $event->save();

        return response()->json(['success' => true, 'message' => '🏆 Torneo finalizado. ¡Acta cerrada con éxito!']);
    }
}