<?php

namespace App\Services\Tournament;

use App\Models\Event;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SwissEngine
{
    /**
     * Determina qué ronda toca y la genera
     */
    public function generateNextRound(Event $event, int $groupsCount = 1)
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
                return $this->generateFirstRound($event, $groupsCount);
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
     * Soporta dividir a los jugadores aleatoriamente en N grupos equilibrados.
     */
    private function generateFirstRound(Event $event, int $groupsCount = 1)
    {
        $groupsCount = max(1, $groupsCount);
        $players = $this->getActivePlayers($event)->shuffle()->values();

        if ($players->isEmpty()) {
            return ['success' => false, 'message' => 'No hay jugadores inscritos en el torneo.'];
        }

        if ($groupsCount > 1 && $players->count() < $groupsCount * 2) {
            return [
                'success' => false,
                'message' => "Se necesitan al menos " . ($groupsCount * 2) . " jugadores activos para dividir en $groupsCount grupos (mínimo 2 por grupo)."
            ];
        }

        $total = $players->count();
        $baseSize = intdiv($total, $groupsCount);
        $remainder = $total % $groupsCount;

        $groups = [];
        $offset = 0;
        for ($g = 1; $g <= $groupsCount; $g++) {
            $size = $baseSize + ($g <= $remainder ? 1 : 0);
            $groups[$g] = $players->slice($offset, $size)->values();
            $offset += $size;
        }

        $matchesCreated = 0;

        DB::transaction(function () use ($event, $groups, $groupsCount, &$matchesCreated) {
            foreach ($groups as $groupNum => $groupPlayers) {
                $assignedGroup = $groupsCount > 1 ? $groupNum : null;

                DB::table('assist_user_event')
                    ->where('event_id', $event->id)
                    ->whereIn('user_id', $groupPlayers->pluck('id')->toArray())
                    ->update(['swiss_group' => $assignedGroup]);

                for ($i = 0; $i < $groupPlayers->count(); $i += 2) {
                    $player1 = $groupPlayers[$i];
                    $player2 = $groupPlayers[$i + 1] ?? null;
                    $isBye = is_null($player2);

                    TournamentMatch::create([
                        'event_id'      => $event->id,
                        'round_number'  => 1,
                        'bracket_type'  => 'swiss',
                        'swiss_group'   => $assignedGroup,
                        'player1_id'    => $player1->id,
                        'player2_id'    => $isBye ? null : $player2->id,
                        'status'        => $isBye ? 'completed' : 'pending',
                        'winner_id'     => $isBye ? $player1->id : null,
                        'player1_score' => $isBye ? 4 : 0,
                        'player2_score' => 0,
                    ]);
                    $matchesCreated++;
                }
            }
        });

        $groupSuffix = $groupsCount > 1 ? " en $groupsCount grupos." : ".";
        return ['success' => true, 'message' => "Ronda 1 lista. $matchesCreated partidas creadas$groupSuffix"];
    }

    /**
     * Re-sortea la Ronda 1 (y sus grupos) siempre que no haya empezado ninguna partida real
     */
    public function rerandomizeFirstRound(Event $event, int $groupsCount = 1): array
    {
        $hasNonFirstRound = TournamentMatch::where('event_id', $event->id)
            ->where(function ($q) {
                $q->where('round_number', '>', 1)
                  ->orWhereNotIn('bracket_type', ['swiss', 'round_robin']);
            })
            ->exists();

        if ($hasNonFirstRound) {
            return ['success' => false, 'message' => '⛔ Solo se puede re-sortear cuando el torneo está en la Ronda 1 de Suizo.'];
        }

        if ($this->hasAnyStartedRealMatch($event->id)) {
            return ['success' => false, 'message' => '⛔ No se puede re-sortear la Ronda 1 porque ya hay partidas iniciadas o completadas.'];
        }

        return DB::transaction(function () use ($event, $groupsCount) {
            TournamentMatch::where('event_id', $event->id)->delete();
            DB::table('assist_user_event')
                ->where('event_id', $event->id)
                ->update(['swiss_group' => null]);

            $res = $this->generateFirstRound($event, $groupsCount);
            if (!empty($res['success'])) {
                $res['message'] = '🔀 Ronda 1 y grupos re-sorteados correctamente.';
            }
            return $res;
        });
    }

    /**
     * Intercambia dos jugadores de distinto grupo en Ronda 1 antes de que empiecen las partidas
     */
    public function swapPlayersBetweenGroups(Event $event, int $player1Id, int $player2Id): array
    {
        if ($player1Id === $player2Id) {
            return ['success' => false, 'message' => 'Selecciona dos jugadores distintos para intercambiar.'];
        }

        $hasNonFirstRound = TournamentMatch::where('event_id', $event->id)
            ->where(function ($q) {
                $q->where('round_number', '>', 1)
                  ->orWhereNotIn('bracket_type', ['swiss', 'round_robin']);
            })
            ->exists();

        if ($hasNonFirstRound || $this->hasAnyStartedRealMatch($event->id)) {
            return ['success' => false, 'message' => '⛔ Solo se pueden intercambiar jugadores de grupo antes de iniciar partidas en Ronda 1.'];
        }

        $rows = DB::table('assist_user_event')
            ->where('event_id', $event->id)
            ->whereIn('user_id', [$player1Id, $player2Id])
            ->pluck('swiss_group', 'user_id');

        $g1 = isset($rows[$player1Id]) ? (int) $rows[$player1Id] : null;
        $g2 = isset($rows[$player2Id]) ? (int) $rows[$player2Id] : null;

        if (!$g1 || !$g2) {
            return ['success' => false, 'message' => 'Ambos jugadores deben pertenecer a un grupo activo del torneo.'];
        }

        if ($g1 === $g2) {
            return ['success' => false, 'message' => 'Los dos jugadores ya están en el mismo grupo.'];
        }

        DB::transaction(function () use ($event, $player1Id, $player2Id, $g1, $g2) {
            DB::table('assist_user_event')
                ->where('event_id', $event->id)
                ->where('user_id', $player1Id)
                ->update(['swiss_group' => $g2]);

            DB::table('assist_user_event')
                ->where('event_id', $event->id)
                ->where('user_id', $player2Id)
                ->update(['swiss_group' => $g1]);

            $r1Matches = TournamentMatch::where('event_id', $event->id)
                ->where('round_number', 1)
                ->where('bracket_type', 'swiss')
                ->where(function ($q) use ($player1Id, $player2Id) {
                    $q->whereIn('player1_id', [$player1Id, $player2Id])
                      ->orWhereIn('player2_id', [$player1Id, $player2Id]);
                })
                ->get();

            foreach ($r1Matches as $m) {
                $changed = false;
                if ((int) $m->player1_id === $player1Id) {
                    $m->player1_id = $player2Id;
                    if ((int) $m->winner_id === $player1Id) $m->winner_id = $player2Id;
                    $changed = true;
                } elseif ((int) $m->player1_id === $player2Id) {
                    $m->player1_id = $player1Id;
                    if ((int) $m->winner_id === $player2Id) $m->winner_id = $player1Id;
                    $changed = true;
                }

                if ((int) $m->player2_id === $player1Id) {
                    $m->player2_id = $player2Id;
                    if ((int) $m->winner_id === $player1Id) $m->winner_id = $player2Id;
                    $changed = true;
                } elseif ((int) $m->player2_id === $player2Id) {
                    $m->player2_id = $player1Id;
                    if ((int) $m->winner_id === $player2Id) $m->winner_id = $player1Id;
                    $changed = true;
                }

                if ($changed) {
                    $m->save();
                }
            }
        });

        return ['success' => true, 'message' => "🔄 Jugadores intercambiados entre Grupo $g1 y Grupo $g2."];
    }

    public function countActivePlayers(Event $event): int
    {
        return $this->getActivePlayers($event)->count();
    }

    private function hasAnyStartedRealMatch(int $eventId): bool
    {
        $matchIds = TournamentMatch::where('event_id', $eventId)->pluck('id');
        if ($matchIds->isEmpty()) {
            return false;
        }

        $hasBattles = DB::table('match_battles')
            ->whereIn('tournament_match_id', $matchIds)
            ->exists();

        if ($hasBattles) {
            return true;
        }

        return TournamentMatch::where('event_id', $eventId)
            ->where('player1_score', '!=', 99)
            ->where('player2_score', '!=', 99)
            ->whereNotNull('player2_id')
            ->where(function ($q) {
                $q->where('status', '!=', 'pending')
                  ->orWhere('player1_score', '>', 0)
                  ->orWhere('player2_score', '>', 0)
                  ->orWhereNotNull('winner_id');
            })
            ->exists();
    }

    /**
     * Genera Rondas > 1 (Lógica de Sistema Suizo con Desempates, por grupo si existen grupos)
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

        // 2. Obtener jugadores activos con su grupo asignado y todo el historial previo
        $players = $this->getActivePlayers($event);
        $playerGroupsMap = DB::table('assist_user_event')
            ->where('event_id', $event->id)
            ->whereIn('user_id', $players->pluck('id')->toArray())
            ->pluck('swiss_group', 'user_id')
            ->toArray();

        $distinctGroups = collect($playerGroupsMap)->filter(fn($g) => !is_null($g))->unique()->sort()->values();
        $hasGroups = $distinctGroups->isNotEmpty();

        // Salvaguarda: si se añadió un jugador tarde sin grupo en un torneo con grupos, asignarlo al grupo con menos jugadores
        if ($hasGroups) {
            $groupCounts = [];
            foreach ($distinctGroups as $g) {
                $groupCounts[(int) $g] = 0;
            }
            foreach ($players as $p) {
                $g = $playerGroupsMap[$p->id] ?? null;
                if (!is_null($g) && isset($groupCounts[(int) $g])) {
                    $groupCounts[(int) $g]++;
                }
            }
            foreach ($players as $p) {
                if (!isset($playerGroupsMap[$p->id]) || is_null($playerGroupsMap[$p->id])) {
                    asort($groupCounts);
                    $targetGroup = (int) array_key_first($groupCounts);
                    $playerGroupsMap[$p->id] = $targetGroup;
                    $groupCounts[$targetGroup]++;
                    DB::table('assist_user_event')
                        ->where('event_id', $event->id)
                        ->where('user_id', $p->id)
                        ->update(['swiss_group' => $targetGroup]);
                }
            }
        }

        $matches = TournamentMatch::where('event_id', $event->id)
            ->where('round_number', '<', $roundNumber)
            ->get();

        $groupedPlayers = $players->groupBy(function ($p) use ($playerGroupsMap, $hasGroups) {
            return $hasGroups ? (int) ($playerGroupsMap[$p->id] ?? 1) : 0;
        })->sortKeys();

        $matchesCreated = 0;
        foreach ($groupedPlayers as $groupKey => $groupPlayers) {
            $swissGroup = ($hasGroups && $groupKey > 0) ? (int) $groupKey : null;
            $matchesCreated += $this->pairSwissGroup($groupPlayers, $matches, $roundNumber, $event->id, $swissGroup);
        }

        $groupSuffix = $hasGroups ? " (" . $groupedPlayers->count() . " grupos)." : ".";
        return ['success' => true, 'message' => "Ronda $roundNumber generada. $matchesCreated cruces calculados$groupSuffix"];
    }

    /**
     * Ejecuta el cálculo de clasificación y emparejamiento Suizo para un grupo concreto
     */
    private function pairSwissGroup(Collection $players, Collection $allMatches, int $roundNumber, int $eventId, ?int $swissGroup): int
    {
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

        foreach ($allMatches as $match) {
            if (!is_null($swissGroup) && !is_null($match->swiss_group) && (int) $match->swiss_group !== $swissGroup) {
                continue;
            }

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
                if (isset($playedOpponents[$match->player1_id])) {
                    $playedOpponents[$match->player1_id][] = $match->player2_id;
                }
                if (isset($playedOpponents[$match->player2_id])) {
                    $playedOpponents[$match->player2_id][] = $match->player1_id;
                }

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

        foreach ($playedOpponents as $pid => $opps) {
            if (!isset($stats[$pid])) continue;
            foreach ($opps as $oppId) {
                $stats[$pid]['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
        }

        uasort($stats, function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
            if ($a['buchholz'] !== $b['buchholz']) return $b['buchholz'] <=> $a['buchholz'];
            return $b['point_diff'] <=> $a['point_diff'];
        });

        $rankedPlayerIds = array_keys($stats);
        $pairings = [];
        $matched = [];

        if (count($rankedPlayerIds) % 2 !== 0) {
            for ($i = count($rankedPlayerIds) - 1; $i >= 0; $i--) {
                $pid = $rankedPlayerIds[$i];
                if (empty($hadBye[$pid])) {
                    $pairings[] = ['p1' => $pid, 'p2' => null];
                    $matched[] = $pid;
                    break;
                }
            }
            if (count($matched) === 0 && !empty($rankedPlayerIds)) {
                $pid = end($rankedPlayerIds);
                $pairings[] = ['p1' => $pid, 'p2' => null];
                $matched[] = $pid;
            }
        }

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

        $matchesCreated = 0;
        foreach ($pairings as $pair) {
            $isBye = ($pair['p2'] === null);
            TournamentMatch::create([
                'event_id'      => $eventId,
                'round_number'  => $roundNumber,
                'bracket_type'  => 'swiss',
                'swiss_group'   => $swissGroup,
                'player1_id'    => $pair['p1'],
                'player2_id'    => $pair['p2'],
                'status'        => $isBye ? 'completed' : 'pending',
                'winner_id'     => $isBye ? $pair['p1'] : null,
                'player1_score' => $isBye ? 4 : 0,
                'player2_score' => 0,
            ]);
            $matchesCreated++;
        }

        return $matchesCreated;
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
