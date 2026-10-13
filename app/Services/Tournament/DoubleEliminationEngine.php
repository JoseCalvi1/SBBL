<?php

namespace App\Services\Tournament;

use App\Models\Event;
use App\Models\TournamentMatch;

class DoubleEliminationEngine
{
    public function generateTopCut(Event $event, int $topSize)
    {
        // 1. Obtenemos las posiciones finales (misma lógica que getStandings)
        $matches = TournamentMatch::where('event_id', $event->id)->where('status', 'completed')->get();
        $players = $event->users()->get();

        $stats = [];
        foreach ($players as $p) {
            $stats[$p->id] = ['id' => $p->id, 'points' => 0, 'buchholz' => 0, 'opponents' => []];
        }

        foreach ($matches as $m) {
            if ($m->winner_id == $m->player1_id) $stats[$m->player1_id]['points'] += 3;
            elseif ($m->winner_id == $m->player2_id) $stats[$m->player2_id]['points'] += 3;
            if ($m->player2_id && $m->player2_id != 0) { // Omitimos fantasmas si los hubiera
                $stats[$m->player1_id]['opponents'][] = $m->player2_id;
                $stats[$m->player2_id]['opponents'][] = $m->player1_id;
            }
        }

        foreach ($stats as $id => &$data) {
            foreach ($data['opponents'] as $oppId) {
                $data['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
        }

        usort($stats, function($a, $b) {
            if ($a['points'] === $b['points']) return $b['buchholz'] <=> $a['buchholz'];
            return $b['points'] <=> $a['points'];
        });

        // 2. Extraemos el Top X
        $topPlayers = array_slice($stats, 0, $topSize);

        // 3. Plantillas de cruce oficial (Seeding)
        $seeds = [
            4 => [ [1,4], [2,3] ],
            8 => [ [1,8], [4,5], [2,7], [3,6] ],
            16 => [ [1,16], [8,9], [4,13], [5,12], [2,15], [7,10], [3,14], [6,11] ],
            32 => [ [1,32], [16,17], [8,25], [9,24], [4,29], [13,20], [5,28], [12,21], [2,31], [15,18], [7,26], [10,23], [3,30], [14,19], [6,27], [11,22] ]
        ];

        if (!isset($seeds[$topSize])) return ['success' => false, 'message' => 'Tamaño de Top no soportado.'];

        $roundNumber = TournamentMatch::where('event_id', $event->id)->max('round_number') + 1;
        
        foreach ($seeds[$topSize] as $matchup) {
            // BLINDAJE: Usamos el modelo directo y aseguramos el score inicial a 0
            $match = new TournamentMatch();
            $match->event_id = $event->id;
            $match->round_number = $roundNumber;
            $match->bracket_type = 'winners';
            $match->player1_id = $topPlayers[$matchup[0] - 1]['id'];
            $match->player2_id = $topPlayers[$matchup[1] - 1]['id'];
            $match->player1_score = 0;
            $match->player2_score = 0;
            $match->status = 'pending';
            $match->save();
        }

        return ['success' => true, 'message' => "Top $topSize generado correctamente."];
    }

    public function generateInitialBracket(\App\Models\Event $event)
    {
        $players = $event->users()->get()->shuffle();
        $numPlayers = $players->count();
        
        if ($numPlayers < 4) return ['success' => false, 'message' => 'Se necesitan al menos 4 jugadores.'];

        $bracketSize = pow(2, ceil(log($numPlayers, 2)));
        $numByes = $bracketSize - $numPlayers;
        $matchesToCreate = $bracketSize / 2;

        $playerIndex = 0;
        $matchesToProgress = [];

        for ($i = 0; $i < $matchesToCreate; $i++) {
            $p1 = $players[$playerIndex++]->id;
            
            if ($i < $numByes) {
                $p2 = null; // AHORA SÍ PODEMOS USAR NULL
                $isBye = true;
            } else {
                $p2 = $players[$playerIndex++]->id;
                $isBye = false;
            }

            $match = new TournamentMatch();
            $match->event_id = $event->id;
            $match->round_number = 1;
            $match->bracket_type = 'winners';
            $match->player1_id = $p1;
            $match->player2_id = $p2;
            $match->status = $isBye ? 'completed' : 'pending';
            $match->winner_id = $isBye ? $p1 : null;
            $match->player1_score = $isBye ? 4 : 0;
            $match->player2_score = $isBye ? 99 : 0; // 99 para fantasmas
            $match->save();

            if ($isBye) $matchesToProgress[] = $match;
        }

        foreach ($matchesToProgress as $m) {
            $this->progressMatch($m);
        }

        return ['success' => true, 'message' => "Cuadro de $bracketSize generado ($numByes Byes)."];
    }

    public function progressMatch(TournamentMatch $match)
    {
        $loser_id = ($match->winner_id == $match->player1_id) ? $match->player2_id : $match->player1_id;
        $event = \App\Models\Event::findOrFail($match->event_id);

        $firstRound = TournamentMatch::where('event_id', $match->event_id)
            ->where('bracket_type', 'winners')
            ->min('round_number');

        if (!$firstRound) return;

        $wr1Count = TournamentMatch::where('event_id', $match->event_id)
            ->where('bracket_type', 'winners')
            ->where('round_number', $firstRound)
            ->count();
            
        if ($wr1Count == 0) return;
        
        $totalWinnersRounds = (int) log($wr1Count * 2, 2);
        $totalLosersRounds = ($totalWinnersRounds == 1) ? 1 : (2 * $totalWinnersRounds) - 2;
        $relRound = $match->round_number - $firstRound + 1; 

        $loserIsGhost = is_null($loser_id);
        $winnerIsGhost = is_null($match->winner_id);

        if ($match->bracket_type === 'winners') {
            if ($relRound == $totalWinnersRounds) {
                $this->placeInGrandFinal($match->event_id, $match->winner_id, 'player1_id', $winnerIsGhost);
            } else {
                $this->placeInNextAvailableMatch($match->event_id, $match->winner_id, 'winners', $match->round_number + 1, $winnerIsGhost);
            }
            
            $relDrop = ($relRound == 1) ? 1 : (($relRound - 1) * 2);
            $losersAbsoluteRound = $firstRound + $relDrop - 1;
            
            $this->placeInNextAvailableMatch($match->event_id, $loser_id, 'losers', $losersAbsoluteRound, $loserIsGhost);
            
        } elseif ($match->bracket_type === 'losers') {
            if ($relRound == $totalLosersRounds) {
                $this->placeInGrandFinal($match->event_id, $match->winner_id, 'player2_id', $winnerIsGhost);
            } else {
                $this->placeInNextAvailableMatch($match->event_id, $match->winner_id, 'losers', $match->round_number + 1, $winnerIsGhost);
            }
        } elseif ($match->bracket_type === 'grand_final') {
            if ($match->winner_id == $match->player1_id) {
                $event->update(['status' => 'completed']);
            } else {
                $resetMatch = new TournamentMatch();
                $resetMatch->event_id = $match->event_id;
                $resetMatch->round_number = $match->round_number + 1;
                $resetMatch->bracket_type = 'grand_final_reset';
                $resetMatch->player1_id = $match->player1_id;
                $resetMatch->player2_id = $match->player2_id;
                $resetMatch->status = 'pending';
                $resetMatch->save();
            }
        } elseif ($match->bracket_type === 'grand_final_reset') {
            $event->update(['status' => 'completed']);
        }
    }

    private function placeInNextAvailableMatch($eventId, $playerId, $bracketType, $roundNumber, $isGhost = false)
    {
        // 1. Buscamos de forma sencilla (Evita Error 500)
        $matches = TournamentMatch::where('event_id', $eventId)
            ->where('bracket_type', $bracketType)
            ->where('round_number', $roundNumber)
            ->orderBy('id', 'asc')
            ->get();

        $nextMatch = null;

        // 2. Buscamos el hueco en PHP
        foreach ($matches as $m) {
            $slot1Empty = is_null($m->player1_id) && $m->player1_score != 99;
            $slot2Empty = is_null($m->player2_id) && $m->player2_score != 99;

            if ($slot1Empty || $slot2Empty) {
                $nextMatch = $m;
                break;
            }
        }

        // 3. Rellenamos el hueco
        if ($nextMatch) {
            $slot1Empty = is_null($nextMatch->player1_id) && $nextMatch->player1_score != 99;
            
            if ($slot1Empty) {
                if ($isGhost) $nextMatch->player1_score = 99;
                else $nextMatch->player1_id = $playerId;
            } else {
                if ($isGhost) $nextMatch->player2_score = 99;
                else $nextMatch->player2_id = $playerId;
            }
            $nextMatch->save();
        } else {
            // Creamos nuevo partido si no hay huecos
            $nextMatch = new TournamentMatch();
            $nextMatch->event_id = $eventId;
            $nextMatch->round_number = $roundNumber;
            $nextMatch->bracket_type = $bracketType;
            $nextMatch->player1_id = $isGhost ? null : $playerId;
            $nextMatch->player1_score = $isGhost ? 99 : 0;
            $nextMatch->player2_id = null;
            $nextMatch->player2_score = 0;
            $nextMatch->status = 'pending';
            $nextMatch->save();
        }

        // 4. Auto-Resolver batallas de fantasmas
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

    private function placeInGrandFinal($eventId, $playerId, $position, $isGhost = false)
    {
        $grandFinal = TournamentMatch::where('event_id', $eventId)
            ->where('bracket_type', 'grand_final')
            ->first();

        if ($grandFinal) {
            if ($isGhost) {
                if ($position == 'player1_id') $grandFinal->player1_score = 99;
                else $grandFinal->player2_score = 99;
            } else {
                $grandFinal->$position = $playerId;
            }
            $grandFinal->save();
        } else {
            $maxRound = TournamentMatch::where('event_id', $eventId)->max('round_number') + 1;
            
            $grandFinal = new TournamentMatch();
            $grandFinal->event_id = $eventId;
            $grandFinal->round_number = $maxRound;
            $grandFinal->bracket_type = 'grand_final';
            $grandFinal->player1_id = null;
            $grandFinal->player1_score = 0;
            $grandFinal->player2_id = null;
            $grandFinal->player2_score = 0;
            $grandFinal->status = 'pending';
            
            if ($isGhost) {
                if ($position == 'player1_id') $grandFinal->player1_score = 99;
                else $grandFinal->player2_score = 99;
            } else {
                $grandFinal->$position = $playerId;
            }
            $grandFinal->save();
        }
    }
}