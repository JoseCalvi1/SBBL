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
            $format = $event->configuration ?? 'DoubleElimination';

            if ($format === 'DoubleElimination') {
                $engine = new \App\Services\Tournament\DoubleEliminationEngine();
                return $engine->generateInitialBracket($event);
            }
            elseif ($format === 'Swiss' || empty($format)) {
                return $this->generateFirstRound($event);
            }
            else {
                return [
                    'success' => false,
                    'message' => "⚠️ El formato de torneo '{$format}' todavía no está soportado por la plataforma."
                ];
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
        $players = $this->getActivePlayers($event)->shuffle()->values();
        $matchesCreated = 0;

        if ($players->isEmpty()) {
            return ['success' => false, 'message' => 'No hay jugadores inscritos en el torneo.'];
        }

        // Emparejamos de 2 en 2
        for ($i = 0; $i < $players->count(); $i += 2) {
            $player1 = $players[$i];
            // Si el total es impar, el último jugador no tendrá rival (Bye)
            $player2 = $players[$i + 1] ?? null;
            $isBye = is_null($player2);

            TournamentMatch::create([
                'event_id'      => $event->id,
                'round_number'  => 1,
                'bracket_type'  => 'swiss',
                'player1_id'    => $player1->id,
                'player2_id'    => $isBye ? null : $player2->id,
                // Si es un Bye en R1, nace ya completado a favor de P1 con 4 puntos
                'status'        => $isBye ? 'completed' : 'pending',
                'winner_id'     => $isBye ? $player1->id : null,
                'player1_score' => $isBye ? 4 : 0,
                'player2_score' => 0,
            ]);
            $matchesCreated++;
        }

        return ['success' => true, 'message' => "Ronda 1 lista. $matchesCreated partidas creadas."];
    }

    /**
     * Genera Rondas > 1 (Lógica de Sistema Suizo con Desempates)
     */
    private function generateSubsequentRound(Event $event, $roundNumber)
    {
        // 1. Validar que todas las partidas reales (no BYEs) de la ronda anterior han terminado
        $previousRound = $roundNumber - 1;
        $unfinishedMatches = TournamentMatch::where('event_id', $event->id)
            ->where('round_number', $previousRound)
            ->whereNotNull('player2_id')
            ->where('player1_score', '!=', 99)
            ->where('player2_score', '!=', 99)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        if ($unfinishedMatches > 0) {
            return ['success' => false, 'message' => "Aún hay $unfinishedMatches partida(s) en curso. Termínalas antes de avanzar."];
        }

        // 2. Obtener jugadores activos y todo el historial de emparejamientos previos
        $players = $this->getActivePlayers($event);
        $matches = TournamentMatch::where('event_id', $event->id)
            ->where('round_number', '<', $roundNumber)
            ->get();

        $stats = [];
        $playedOpponents = [];
        $hadBye = [];

        foreach ($players as $player) {
            $stats[$player->id] = [
                'id'         => $player->id,
                'points'     => 0,
                'buchholz'   => 0,
                'point_diff' => 0,
            ];
            $playedOpponents[$player->id] = [];
            $hadBye[$player->id] = false;
        }

        // Calcular puntuaciones, diferencia de puntos e historial de cruces
        foreach ($matches as $match) {
            $isBye = ($match->player2_id === null || $match->player1_score == 99 || $match->player2_score == 99);

            if ($isBye) {
                if (isset($stats[$match->player1_id])) {
                    $hadBye[$match->player1_id] = true;
                    $stats[$match->player1_id]['points'] += 3;
                    $stats[$match->player1_id]['point_diff'] += 4;
                }
                continue;
            }

            if ($match->player1_id && $match->player2_id) {
                $playedOpponents[$match->player1_id][] = $match->player2_id;
                $playedOpponents[$match->player2_id][] = $match->player1_id;

                $s1 = (int) $match->player1_score;
                $s2 = (int) $match->player2_score;

                if (isset($stats[$match->player1_id])) {
                    $stats[$match->player1_id]['point_diff'] += ($s1 - $s2);
                    if ($match->winner_id == $match->player1_id) {
                        $stats[$match->player1_id]['points'] += 3;
                    }
                }

                if (isset($stats[$match->player2_id])) {
                    $stats[$match->player2_id]['point_diff'] += ($s2 - $s1);
                    if ($match->winner_id == $match->player2_id) {
                        $stats[$match->player2_id]['points'] += 3;
                    }
                }
            }
        }

        // Calcular Buchholz para ordenar con precisión dentro de cada tramo de puntos
        foreach ($playedOpponents as $pid => $opps) {
            if (!isset($stats[$pid])) continue;
            foreach ($opps as $oppId) {
                $stats[$pid]['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
        }

        // Ordenar jugadores por: 1º Puntos -> 2º Buchholz -> 3º Diferencia de puntos (+/-)
        uasort($stats, function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
            if ($a['buchholz'] !== $b['buchholz']) return $b['buchholz'] <=> $a['buchholz'];
            return $b['point_diff'] <=> $a['point_diff'];
        });

        $rankedPlayerIds = array_keys($stats);

        // 3. Motor de Emparejamiento
        $pairings = [];
        $matched = [];

        // Si son impares, buscar al peor posicionado que aún no haya tenido un Bye
        if (count($rankedPlayerIds) % 2 !== 0) {
            for ($i = count($rankedPlayerIds) - 1; $i >= 0; $i--) {
                $pid = $rankedPlayerIds[$i];
                if (empty($hadBye[$pid])) {
                    $pairings[] = ['p1' => $pid, 'p2' => null];
                    $matched[] = $pid;
                    break;
                }
            }
            // Salvaguarda: si todos hubieran tenido un bye, se lo damos al último
            if (count($matched) === 0 && !empty($rankedPlayerIds)) {
                $pid = end($rankedPlayerIds);
                $pairings[] = ['p1' => $pid, 'p2' => null];
                $matched[] = $pid;
            }
        }

        // Emparejar a los restantes (de arriba hacia abajo sin repetir rival)
        foreach ($rankedPlayerIds as $pid) {
            if (in_array($pid, $matched)) continue;

            $opponentFound = false;
            foreach ($rankedPlayerIds as $potentialOpponent) {
                if ($pid == $potentialOpponent) continue;
                if (in_array($potentialOpponent, $matched)) continue;

                if (!in_array($potentialOpponent, $playedOpponents[$pid] ?? [])) {
                    $pairings[] = ['p1' => $pid, 'p2' => $potentialOpponent];
                    $matched[] = $pid;
                    $matched[] = $potentialOpponent;
                    $opponentFound = true;
                    break;
                }
            }

            // Fallback de seguridad extrema si todos se han enfrentado ya
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
                'event_id'      => $event->id,
                'round_number'  => $roundNumber,
                'bracket_type'  => 'swiss',
                'player1_id'    => $pair['p1'],
                'player2_id'    => $pair['p2'],
                'status'        => $isBye ? 'completed' : 'pending',
                'winner_id'     => $isBye ? $pair['p1'] : null,
                'player1_score' => $isBye ? 4 : 0,
                'player2_score' => 0,
            ]);
            $matchesCreated++;
        }

        return ['success' => true, 'message' => "Ronda $roundNumber generada. $matchesCreated cruces calculados."];
    }

    private function getActivePlayers(\App\Models\Event $event)
    {
        $limite = 9999;
        if ($event->has_stadium_limit || !empty($event->max_players)) {
            if ($event->stadiums == 1) $limite = 20;
            elseif ($event->stadiums == 2) $limite = 35;
            elseif ($event->stadiums == 3) $limite = 45;
            elseif ($event->stadiums == 4) $limite = 55;
            elseif ($event->stadiums >= 5) $limite = 64;
            if (!empty($event->max_players)) $limite = $event->max_players;
        }

        $activeIds = \Illuminate\Support\Facades\DB::table('assist_user_event')
            ->where('event_id', $event->id)
            ->where(function ($q) {
                $q->whereNull('puesto')->orWhere('puesto', '!=', 'dropped');
            })
            ->orderBy('created_at', 'asc')
            ->limit($limite)
            ->pluck('user_id');

        return \App\Models\User::whereIn('id', $activeIds)->get();
    }
}
