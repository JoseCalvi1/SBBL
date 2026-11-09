<?php

namespace App\Services\Tournament;

use App\Models\Event;
use App\Models\TournamentMatch;
use Illuminate\Support\Facades\DB;

class DoubleEliminationEngine
{
    /**
     * Obtiene únicamente los jugadores TITULARES y ACTIVOS (No Dropped y dentro del límite de aforo)
     */
    private function getActiveSeatedPlayers(Event $event)
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

        $activeUserIds = DB::table('assist_user_event')
            ->where('event_id', $event->id)
            ->where(function ($q) {
                $q->whereNull('puesto')->orWhere('puesto', '!=', 'dropped');
            })
            ->orderBy('created_at', 'asc')
            ->limit($limite)
            ->pluck('user_id');

        return \App\Models\User::whereIn('id', $activeUserIds)->get();
    }

    /**
     * GENERA EL CUADRO INICIAL COMPLETO (TODAS LAS RONDAS PRE-CREADAS)
     */
    public function generateInitialBracket(Event $event)
    {
        $players = $this->getActiveSeatedPlayers($event)->shuffle()->values();
        $numPlayers = $players->count();

        if ($numPlayers < 4) {
            return ['success' => false, 'message' => 'Se necesitan al menos 4 jugadores activos (no retirados) para Doble Eliminación.'];
        }

        $bracketSize = (int) pow(2, ceil(log($numPlayers, 2)));
        $numByes = $bracketSize - $numPlayers;
        $matchesR1 = (int) ($bracketSize / 2);

        // Distribuimos los Byes de forma alterna
        $byeSlots = array_fill(0, $matchesR1, false);
        $assignedByes = 0;

        for ($i = 0; $i < $matchesR1 && $assignedByes < $numByes; $i += 2) {
            $byeSlots[$i] = true;
            $assignedByes++;
        }
        for ($i = 1; $i < $matchesR1 && $assignedByes < $numByes; $i += 2) {
            $byeSlots[$i] = true;
            $assignedByes++;
        }

        $pairings = [];
        $playerIndex = 0;
        for ($i = 0; $i < $matchesR1; $i++) {
            $p1 = $players[$playerIndex++]->id;
            $isBye = $byeSlots[$i];
            $p2 = $isBye ? null : $players[$playerIndex++]->id;
            $pairings[] = ['p1' => $p1, 'p2' => $p2, 'is_bye' => $isBye];
        }

        $this->createFullPrebuiltBracket($event->id, $bracketSize, 1, $pairings);

        return ['success' => true, 'message' => "Cuadro completo de $bracketSize pre-generado ($numPlayers jugadores activos)."];
    }

    /**
     * GENERA EL TOP CUT DESDE SUIZO USANDO LA CLASIFICACIÓN Y DESEMPATES OFICIALES
     */
    public function generateTopCut(Event $event, int $topSize)
    {
        $engineController = app(\App\Http\Controllers\TournamentEngineController::class);
        $standingsData = $engineController->getStandings($event->id)->getData(true);
        $clasificacion = $standingsData['clasificacion'] ?? [];

        $topPlayers = array_slice($clasificacion, 0, $topSize);

        if (count($topPlayers) < $topSize) {
            return ['success' => false, 'message' => "No hay suficientes jugadores activos sin retirar para un Top $topSize."];
        }

        $seeds = [
            4  => [ [1,4], [2,3] ],
            8  => [ [1,8], [4,5], [2,7], [3,6] ],
            16 => [ [1,16], [8,9], [4,13], [5,12], [2,15], [7,10], [3,14], [6,11] ],
            32 => [ [1,32], [16,17], [8,25], [9,24], [4,29], [13,20], [5,28], [12,21], [2,31], [15,18], [7,26], [10,23], [3,30], [14,19], [6,27], [11,22] ]
        ];

        if (!isset($seeds[$topSize])) {
            return ['success' => false, 'message' => 'Tamaño de Top no soportado.'];
        }

        $startRound = (TournamentMatch::where('event_id', $event->id)->max('round_number') ?? 0) + 1;

        $pairings = [];
        foreach ($seeds[$topSize] as $matchup) {
            $pairings[] = [
                'p1'     => $topPlayers[$matchup[0] - 1]['id'],
                'p2'     => $topPlayers[$matchup[1] - 1]['id'],
                'is_bye' => false
            ];
        }

        $this->createFullPrebuiltBracket($event->id, $topSize, $startRound, $pairings);

        return ['success' => true, 'message' => "Top $topSize pre-generado al completo según clasificación oficial."];
    }

    /**
     * CONSTRUYE TODAS LAS PARTIDAS DE WINNERS, LOSERS Y GRAN FINAL DE GOLPE EN LA BD
     */
    private function createFullPrebuiltBracket(int $eventId, int $bracketSize, int $startRound, array $r1Pairings)
    {
        $totalWinnersRounds = (int) log($bracketSize, 2);
        $totalLosersRounds = ($totalWinnersRounds <= 1) ? 1 : (2 * $totalWinnersRounds) - 2;

        $r1MatchesToProgress = [];

        // 1. Pre-crear TODAS las rondas de WINNERS
        for ($r = 1; $r <= $totalWinnersRounds; $r++) {
            $absRound = $startRound + $r - 1;
            $matchesInRound = (int) ($bracketSize / pow(2, $r));

            for ($m = 0; $m < $matchesInRound; $m++) {
                $match = new TournamentMatch();
                $match->event_id = $eventId;
                $match->round_number = $absRound;
                $match->bracket_type = 'winners';

                if ($r === 1) {
                    $pair = $r1Pairings[$m];
                    $isBye = $pair['is_bye'];
                    $match->player1_id = $pair['p1'];
                    $match->player2_id = $pair['p2'];
                    $match->player1_score = $isBye ? 4 : 0;
                    $match->player2_score = $isBye ? 99 : 0;
                    $match->status = $isBye ? 'completed' : 'pending';
                    $match->winner_id = $isBye ? $pair['p1'] : null;
                } else {
                    $match->player1_id = null;
                    $match->player2_id = null;
                    $match->player1_score = 0;
                    $match->player2_score = 0;
                    $match->status = 'pending';
                    $match->winner_id = null;
                }

                $match->save();

                if ($r === 1 && $pair['is_bye']) {
                    $r1MatchesToProgress[] = $match;
                }
            }
        }

        // 2. Pre-crear TODAS las rondas de LOSERS
        for ($lr = 1; $lr <= $totalLosersRounds; $lr++) {
            $absRound = $startRound + $lr - 1;
            $power = ceil($lr / 2) + 1;
            $matchesInRound = (int) max(1, $bracketSize / pow(2, $power));

            for ($m = 0; $m < $matchesInRound; $m++) {
                $match = new TournamentMatch();
                $match->event_id = $eventId;
                $match->round_number = $absRound;
                $match->bracket_type = 'losers';
                $match->player1_id = null;
                $match->player2_id = null;
                $match->player1_score = 0;
                $match->player2_score = 0;
                $match->status = 'pending';
                $match->winner_id = null;
                $match->save();
            }
        }

        // 3. Pre-crear la GRAN FINAL
        $gfRound = $startRound + max($totalWinnersRounds, $totalLosersRounds);
        $gf = new TournamentMatch();
        $gf->event_id = $eventId;
        $gf->round_number = $gfRound;
        $gf->bracket_type = 'grand_final';
        $gf->player1_id = null;
        $gf->player2_id = null;
        $gf->player1_score = 0;
        $gf->player2_score = 0;
        $gf->status = 'pending';
        $gf->winner_id = null;
        $gf->save();

        // 4. Propagar automáticamente todos los Byes iniciales por el árbol
        foreach ($r1MatchesToProgress as $byeMatch) {
            $this->progressMatch($byeMatch);
        }
    }

    /**
     * MAPA TOPOLÓGICO DETERMINISTA CON CROSS-SEEDING
     */
    public function buildBracketTopology(int $eventId): array
    {
        $allMatches = TournamentMatch::where('event_id', $eventId)
            ->whereNotIn('bracket_type', ['swiss', 'round_robin'])
            ->orderBy('round_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if ($allMatches->isEmpty()) return ['targets' => [], 'sources' => [], 'numbers' => [], 'hidden' => []];

        $winnersByRound = $allMatches->where('bracket_type', 'winners')->groupBy('round_number');
        if ($winnersByRound->isEmpty()) return ['targets' => [], 'sources' => [], 'numbers' => [], 'hidden' => []];

        $firstRound = $winnersByRound->keys()->min();
        $r1Count = $winnersByRound->get($firstRound)->count();
        $bracketSize = (int) max(2, pow(2, ceil(log($r1Count * 2, 2))));
        $totalWinnersRounds = (int) log($bracketSize, 2);
        $totalLosersRounds = ($totalWinnersRounds <= 1) ? 1 : (2 * $totalWinnersRounds) - 2;

        $losersByRound = $allMatches->where('bracket_type', 'losers')->groupBy('round_number');
        $grandFinal = $allMatches->where('bracket_type', 'grand_final')->first();

        // 1. Identificar Byes ocultos y numerar partidas
        $hidden = [];
        $numbers = [];
        $seq = 1;

        foreach ($allMatches as $m) {
            $isTeam = !is_null($m->team1_id) || !is_null($m->team2_id);
            $p1Id = $isTeam ? $m->team1_id : $m->player1_id;
            $p2Id = $isTeam ? $m->team2_id : $m->player2_id;
            $p1Ghost = is_null($p1Id) && $m->player1_score == 99;
            $p2Ghost = is_null($p2Id) && $m->player2_score == 99;

            if ($p1Ghost && $p2Ghost) {
                $hidden[$m->id] = true;
            } elseif ($m->round_number == $firstRound && $m->status === 'completed' && ($p1Ghost || $p2Ghost || is_null($p1Id) || is_null($p2Id))) {
                $hidden[$m->id] = true;
            } else {
                $numbers[$m->id] = $seq++;
            }
        }

        $targets = [];
        $sources = [];

        // 2. Enlazar TODAS LAS RONDAS DE WINNERS Y APLICAR CROSS-SEEDING A LOSERS
        for ($r = 1; $r <= $totalWinnersRounds; $r++) {
            $absRound = $firstRound + $r - 1;
            $currMatches = $winnersByRound->get($absRound, collect())->values();

            foreach ($currMatches as $idx => $m) {
                $targets[$m->id] = ['win' => null, 'lose' => null];
                $mNum = $numbers[$m->id] ?? null;

                // Destino del GANADOR
                if ($r < $totalWinnersRounds) {
                    $nextMatches = $winnersByRound->get($absRound + 1, collect())->values();
                    $targetMatch = $nextMatches->get((int) floor($idx / 2));
                    $targetSlot = ($idx % 2 === 0) ? 1 : 2;
                    if ($targetMatch) {
                        $targets[$m->id]['win'] = ['match_id' => $targetMatch->id, 'slot' => $targetSlot];
                        if ($mNum) $sources[$targetMatch->id][$targetSlot] = "GANADOR #{$mNum}";
                    }
                } elseif ($grandFinal) {
                    $targets[$m->id]['win'] = ['match_id' => $grandFinal->id, 'slot' => 1];
                    $sources[$grandFinal->id][1] = $mNum ? "GANADOR #{$mNum}" : "CAMPEÓN WINNERS";
                }

                // Destino del PERDEDOR (Con CROSS-SEEDING para evitar rematches)
                $relDrop = ($r === 1) ? 1 : (($r - 1) * 2);
                $losersAbsRound = $firstRound + $relDrop - 1;
                $loserMatches = $losersByRound->get($losersAbsRound, collect())->values();
                $totalLoserMatches = $loserMatches->count();

                if ($r === 1) {
                    $targetMatch = $loserMatches->get((int) floor($idx / 2));
                    $targetSlot = ($idx % 2 === 0) ? 1 : 2;
                } else {
                    if ($r % 2 === 0) {
                        $mappedIdx = $totalLoserMatches - 1 - $idx;
                    } else {
                        $mappedIdx = $idx;
                    }
                    $targetMatch = $loserMatches->get($mappedIdx);
                    $targetSlot = 2;
                }

                if ($targetMatch) {
                    $targets[$m->id]['lose'] = ['match_id' => $targetMatch->id, 'slot' => $targetSlot];
                    if ($mNum) $sources[$targetMatch->id][$targetSlot] = "PERDEDOR #{$mNum}";
                }
            }
        }

        // 3. Enlazar TODAS LAS RONDAS DE LOSERS
        for ($lr = 1; $lr <= $totalLosersRounds; $lr++) {
            $absRound = $firstRound + $lr - 1;
            $currMatches = $losersByRound->get($absRound, collect())->values();

            foreach ($currMatches as $idx => $m) {
                if (!isset($targets[$m->id])) $targets[$m->id] = ['win' => null, 'lose' => null];
                $mNum = $numbers[$m->id] ?? null;

                if ($lr < $totalLosersRounds) {
                    $nextMatches = $losersByRound->get($absRound + 1, collect())->values();
                    $nextLr = $lr + 1;

                    if ($nextLr % 2 === 0) {
                        $targetMatch = $nextMatches->get($idx);
                        $targetSlot = 1;
                    } else {
                        $targetMatch = $nextMatches->get((int) floor($idx / 2));
                        $targetSlot = ($idx % 2 === 0) ? 1 : 2;
                    }

                    if ($targetMatch) {
                        $targets[$m->id]['win'] = ['match_id' => $targetMatch->id, 'slot' => $targetSlot];
                        if ($mNum) $sources[$targetMatch->id][$targetSlot] = "GANADOR #{$mNum}";
                    }
                } elseif ($grandFinal) {
                    $targets[$m->id]['win'] = ['match_id' => $grandFinal->id, 'slot' => 2];
                    $sources[$grandFinal->id][2] = $mNum ? "GANADOR #{$mNum}" : "CAMPEÓN LOSERS";
                }
            }
        }

        return [
            'targets' => $targets,
            'sources' => $sources,
            'numbers' => $numbers,
            'hidden'  => $hidden
        ];
    }

    /**
     * AVANZA AL GANADOR Y AL PERDEDOR A SU PARTIDA Y SLOT PRE-CREADO EXACTO
     */
    public function progressMatch(TournamentMatch $match)
    {
        if ($match->bracket_type === 'grand_final') {
            // Si gana el jugador que venía de Losers (player2), se crea el Bracket Reset
            if ($match->winner_id != $match->player1_id) {
                $existsReset = TournamentMatch::where('event_id', $match->event_id)
                    ->where('bracket_type', 'grand_final_reset')
                    ->exists();
                if (!$existsReset) {
                    $resetMatch = new TournamentMatch();
                    $resetMatch->event_id = $match->event_id;
                    $resetMatch->round_number = $match->round_number + 1;
                    $resetMatch->bracket_type = 'grand_final_reset';
                    $resetMatch->player1_id = $match->player1_id;
                    $resetMatch->player2_id = $match->player2_id;
                    $resetMatch->player1_score = 0;
                    $resetMatch->player2_score = 0;
                    $resetMatch->status = 'pending';
                    $resetMatch->save();
                }
            }
            return;
        }

        if ($match->bracket_type === 'grand_final_reset') {
            return;
        }

        $loser_id = ($match->winner_id == $match->player1_id) ? $match->player2_id : $match->player1_id;
        $loserIsGhost = is_null($loser_id);
        $winnerIsGhost = is_null($match->winner_id);

        $topology = $this->buildBracketTopology($match->event_id);
        $route = $topology['targets'][$match->id] ?? null;

        if (!$route) return;

        // 1. Colocar al GANADOR en su partida y slot exacto
        if (!empty($route['win'])) {
            $this->assignPlayerToPrebuiltSlot(
                $route['win']['match_id'],
                $route['win']['slot'],
                $match->winner_id,
                $winnerIsGhost
            );
        }

        // 2. Colocar al PERDEDOR en su partida y slot exacto
        if (!empty($route['lose'])) {
            $this->assignPlayerToPrebuiltSlot(
                $route['lose']['match_id'],
                $route['lose']['slot'],
                $loser_id,
                $loserIsGhost
            );
        }
    }

    private function assignPlayerToPrebuiltSlot(int $targetMatchId, int $slot, $playerId, bool $isGhost)
    {
        $targetMatch = TournamentMatch::find($targetMatchId);
        if (!$targetMatch) return;

        if ($slot === 1) {
            $targetMatch->player1_id = $isGhost ? null : $playerId;
            $targetMatch->player1_score = $isGhost ? 99 : 0;
        } else {
            $targetMatch->player2_id = $isGhost ? null : $playerId;
            $targetMatch->player2_score = $isGhost ? 99 : 0;
        }

        $targetMatch->save();
        $this->checkAndAutoResolveGhostMatch($targetMatch);
    }

    private function checkAndAutoResolveGhostMatch(TournamentMatch $nextMatch)
    {
        $slot1Filled = !is_null($nextMatch->player1_id) || $nextMatch->player1_score == 99;
        $slot2Filled = !is_null($nextMatch->player2_id) || $nextMatch->player2_score == 99;

        if ($slot1Filled && $slot2Filled && $nextMatch->status !== 'completed') {
            $p1Ghost = is_null($nextMatch->player1_id) && $nextMatch->player1_score == 99;
            $p2Ghost = is_null($nextMatch->player2_id) && $nextMatch->player2_score == 99;

            if ($p1Ghost && $p2Ghost) {
                $nextMatch->status = 'completed';
                $nextMatch->winner_id = null;
                $nextMatch->player1_score = 99;
                $nextMatch->player2_score = 99;
                $nextMatch->save();
                $this->progressMatch($nextMatch);
            } elseif ($p1Ghost) {
                $nextMatch->status = 'completed';
                $nextMatch->winner_id = $nextMatch->player2_id;
                $nextMatch->player1_score = 99;
                $nextMatch->player2_score = 4;
                $nextMatch->save();
                $this->progressMatch($nextMatch);
            } elseif ($p2Ghost) {
                $nextMatch->status = 'completed';
                $nextMatch->winner_id = $nextMatch->player1_id;
                $nextMatch->player1_score = 4;
                $nextMatch->player2_score = 99;
                $nextMatch->save();
                $this->progressMatch($nextMatch);
            }
        }
    }
}
