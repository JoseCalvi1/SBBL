<?php

namespace App\Services\Tournament;

use App\Models\Event;
use App\Models\TournamentMatch;

class SwissEngine
{
    /**
     * Determina qué ronda toca y la genera
     */
    public function generateNextRound(Event $event)
    {
        $currentRound = TournamentMatch::where('event_id', $event->id)->max('round_number') ?? 0;
        $nextRound = $currentRound + 1;

        // SI ES LA PRIMERA RONDA DEL TORNEO
        if ($nextRound === 1) {
            // Leemos el formato del evento. Por defecto asumimos DoubleElimination si estuviera vacío.
            $format = $event->configuration ?? 'DoubleElimination'; 

            if ($format === 'DoubleElimination') {
                $engine = new \App\Services\Tournament\DoubleEliminationEngine();
                return $engine->generateInitialBracket($event);
            } 
            elseif ($format === 'Swiss' || empty($format)) {
                return $this->generateFirstRound($event);
            } 
            else {
                // AQUÍ CAPTURAMOS CUALQUIER OTRO FORMATO NO SOPORTADO
                return response()->json([
                    'success' => false, 
                    'message' => "⚠️ El formato de torneo '{$format}' todavía no está soportado por la plataforma."
                ], 422);
            }
        }

        // Si ya vamos por la ronda 2, 3, etc., seguimos con la lógica pura del Suizo
        return $this->generateSubsequentRound($event, $nextRound);
    }

    /**
     * Genera la Ronda 1 (Emparejamiento Aleatorio) EXCLUSIVO PARA SUIZO
     */
    private function generateFirstRound(Event $event)
    {
        // Cogemos a todos los inscritos al azar
        $players = $event->users()->inRandomOrder()->get();
        $matchesCreated = 0;

        if ($players->isEmpty()) {
            return ['success' => false, 'message' => 'No hay jugadores inscritos en el torneo.'];
        }

        // Emparejamos de 2 en 2
        for ($i = 0; $i < $players->count(); $i += 2) {
            $player1 = $players[$i];
            // Si el total es impar, el último jugador no tendrá rival (Bye)
            $player2 = $players[$i + 1] ?? null;

            TournamentMatch::create([
                'event_id' => $event->id,
                'round_number' => 1,
                'bracket_type' => 'swiss',
                'player1_id' => $player1->id,
                'player2_id' => $player2 ? $player2->id : null,
                'status' => 'pending'
            ]);
            $matchesCreated++;
        }

        return ['success' => true, 'message' => "Ronda 1 lista. $matchesCreated partidas creadas."];
    }

    /**
     * Genera Rondas > 1 (Lógica de Sistema Suizo)
     */
    private function generateSubsequentRound(Event $event, $roundNumber)
    {
        // 1. Validar que la ronda anterior ha terminado por completo
        $previousRound = $roundNumber - 1;
        $unfinishedMatches = TournamentMatch::where('event_id', $event->id)
            ->where('round_number', $previousRound)
            ->where('status', '!=', 'completed')
            ->count();

        if ($unfinishedMatches > 0) {
            return ['success' => false, 'message' => "Aún hay $unfinishedMatches partida(s) en curso. Termínalas antes de avanzar."];
        }

        // 2. Obtener jugadores y todo el historial de emparejamientos
        $players = $event->users()->get();
        $matches = TournamentMatch::where('event_id', $event->id)->where('round_number', '<', $roundNumber)->get();

        $standings = [];
        $playedOpponents = [];
        $hadBye = [];

        // Inicializar datos para cada jugador
        foreach ($players as $player) {
            $standings[$player->id] = 0; // 3 pts por victoria
            $playedOpponents[$player->id] = [];
            $hadBye[$player->id] = false;
        }

        // Calcular puntuaciones e historial de cruces
        foreach ($matches as $match) {
            // Si fue un Bye (Descanso)
            if ($match->player2_id === null) {
                $hadBye[$match->player1_id] = true;
                $standings[$match->player1_id] += 3; 
                continue;
            }

            // Registrar quién ha jugado contra quién
            $playedOpponents[$match->player1_id][] = $match->player2_id;
            $playedOpponents[$match->player2_id][] = $match->player1_id;

            // Asignar puntos (El ganador se lleva 3 puntos en el suizo)
            if ($match->winner_id == $match->player1_id) {
                $standings[$match->player1_id] += 3;
            } elseif ($match->winner_id == $match->player2_id) {
                $standings[$match->player2_id] += 3;
            }
        }

        // Ordenar a los jugadores de mayor a menor puntuación
        arsort($standings);
        $rankedPlayerIds = array_keys($standings);

        // 3. Motor de Emparejamiento
        $pairings = [];
        $matched = [];

        // Si son impares, buscar al peor posicionado que no haya tenido un Bye
        if (count($rankedPlayerIds) % 2 !== 0) {
            for ($i = count($rankedPlayerIds) - 1; $i >= 0; $i--) {
                $pid = $rankedPlayerIds[$i];
                if (!$hadBye[$pid]) {
                    $pairings[] = ['p1' => $pid, 'p2' => null];
                    $matched[] = $pid;
                    break;
                }
            }
            // Salvaguarda: si todos hubieran tenido un bye (casi imposible), se lo damos al último
            if (count($matched) == 0) {
                $pid = end($rankedPlayerIds);
                $pairings[] = ['p1' => $pid, 'p2' => null];
                $matched[] = $pid;
            }
        }

        // Emparejar a los restantes (de arriba hacia abajo)
        foreach ($rankedPlayerIds as $pid) {
            if (in_array($pid, $matched)) continue;

            $opponentFound = false;
            foreach ($rankedPlayerIds as $potentialOpponent) {
                if ($pid == $potentialOpponent) continue;
                if (in_array($potentialOpponent, $matched)) continue;
                
                // Condición Suizo: No pueden haber jugado antes
                if (!in_array($potentialOpponent, $playedOpponents[$pid])) {
                    $pairings[] = ['p1' => $pid, 'p2' => $potentialOpponent];
                    $matched[] = $pid;
                    $matched[] = $potentialOpponent;
                    $opponentFound = true;
                    break;
                }
            }

            // Fallback de seguridad extrema: Si es matemáticamente imposible cruzar sin repetir, 
            // rompe la regla y empareja con el siguiente disponible para no bloquear el torneo.
            if (!$opponentFound) {
                foreach ($rankedPlayerIds as $potentialOpponent) {
                    if ($pid != $potentialOpponent && !in_array($potentialOpponent, $matched)) {
                        $pairings[] = ['p1' => $pid, 'p2' => $potentialOpponent];
                        $matched[] = $pid;
                        $matched[] = $potentialOpponent;
                        break;
                    }
                }
            }
        }

        // 4. Guardar los nuevos emparejamientos en la Base de Datos
        $matchesCreated = 0;
        foreach ($pairings as $pair) {
            $isBye = ($pair['p2'] === null);
            TournamentMatch::create([
                'event_id' => $event->id,
                'round_number' => $roundNumber,
                'bracket_type' => 'swiss',
                'player1_id' => $pair['p1'],
                'player2_id' => $pair['p2'],
                // Si es un Bye, la partida nace ya completada a favor del P1
                'status' => $isBye ? 'completed' : 'pending',
                'winner_id' => $isBye ? $pair['p1'] : null,
                'player1_score' => $isBye ? 4 : 0, // Le damos los 4 puntos virtuales
            ]);
            $matchesCreated++;
        }

        return ['success' => true, 'message' => "Ronda $roundNumber generada. $matchesCreated cruces calculados."];
    }
}