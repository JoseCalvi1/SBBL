<?php

namespace App\Services\Tournament;

use App\Models\Event;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StandingsService
{
    /**
     * Calcula la clasificación unificada (Suizo + Doble Eliminación) con posiciones compartidas exactas.
     *
     * @param  int|string  $eventId
     * @return array{success: bool, is_bracket: bool, has_swiss: bool, clasificacion: array<int, array<string, mixed>>}
     */
    public function getStandings($eventId): array
    {
        Event::findOrFail($eventId);
        $allEventMatches = TournamentMatch::where('event_id', $eventId)->get();
        $matches = $allEventMatches->where('status', 'completed');

        $bracketMatchesAll = $allEventMatches->whereIn('bracket_type', [
            'winners',
            'losers',
            'grand_final',
            'grand_final_reset',
            'single_elimination',
        ]);
        $hasBracket = $bracketMatchesAll->isNotEmpty();
        $hasSwiss = $allEventMatches->whereIn('bracket_type', ['swiss', 'round_robin'])->isNotEmpty();

        $users = DB::table('assist_user_event')
            ->join('users', 'assist_user_event.user_id', '=', 'users.id')
            ->where('assist_user_event.event_id', $eventId)
            ->where(function ($q) {
                $q->whereNull('assist_user_event.puesto')
                  ->orWhere('assist_user_event.puesto', '!=', 'dropped');
            })
            ->select('users.id', 'users.name', 'assist_user_event.puesto')
            ->get();

        $playerStats = [];
        $headToHead = [];

        foreach ($users as $u) {
            $playerStats[$u->id] = [
                'id'             => $u->id,
                'name'           => $u->name,
                'points'         => 0,
                'wins'           => 0,
                'losses'         => 0,
                'byes'           => 0,
                'buchholz'       => 0,
                'pts_for'        => 0,
                'pts_against'    => 0,
                'point_diff'     => 0,
                'h2h_wins'       => 0,
                'bracket_score'  => 0,
                'bracket_stage'  => '',
                'shared_rank'    => null,
                'puesto_oficial' => null,
                'puesto'         => $u->puesto,
            ];
        }

        // 1. Procesar partidas de Suizo / Round Robin
        $swissMatches = $matches->whereIn('bracket_type', ['swiss', 'round_robin']);
        $this->processSwissMatches($swissMatches, $playerStats, $headToHead);

        // 2. Procesar partidas de Doble Eliminación (Winners / Losers / Grand Final)
        $completedBracketMatches = $matches->whereIn('bracket_type', [
            'winners',
            'losers',
            'grand_final',
            'grand_final_reset',
            'single_elimination',
        ]);
        $this->processBracketMatches($completedBracketMatches, $playerStats, $headToHead, $hasSwiss, $hasBracket);

        // Mini-liga H2H entre empatados
        $this->calculateHeadToHeadTiebreakers($playerStats, $headToHead);

        // 3. Puntuación de Progreso en el Árbol
        if ($hasBracket) {
            $this->calculateBracketProgress($playerStats, $bracketMatchesAll, $completedBracketMatches, $allEventMatches);
        }

        $standings = array_values($playerStats);

        // Ordenar: 1º bracket_score -> 2º Puntos -> 3º Buchholz -> 4º H2H -> 5º DIF -> 6º Puntos a favor
        usort($standings, fn(array $a, array $b) => $this->comparePlayers($a, $b, $headToHead));

        // 4. Asignación de posiciones compartidas en Doble Eliminación y nombre oficial
        $this->assignSharedRanks($standings, $hasBracket);

        return [
            'success'       => true,
            'is_bracket'    => $hasBracket,
            'has_swiss'     => $hasSwiss,
            'clasificacion' => $standings,
        ];
    }

    private function processSwissMatches(Collection $swissMatches, array &$playerStats, array &$headToHead): void
    {
        foreach ($swissMatches as $m) {
            $isBye = is_null($m->player2_id) || $m->player1_score == 99 || $m->player2_score == 99;

            if ($isBye && $m->player1_id && isset($playerStats[$m->player1_id])) {
                $playerStats[$m->player1_id]['points'] += 3;
                $playerStats[$m->player1_id]['wins'] += 1;
                $playerStats[$m->player1_id]['byes'] += 1;
                $playerStats[$m->player1_id]['pts_for'] += 4;
                $playerStats[$m->player1_id]['point_diff'] += 4;
                continue;
            }

            if ($m->player1_id && $m->player2_id && isset($playerStats[$m->player1_id]) && isset($playerStats[$m->player2_id])) {
                $s1 = (int) $m->player1_score;
                $s2 = (int) $m->player2_score;

                $playerStats[$m->player1_id]['pts_for'] += $s1;
                $playerStats[$m->player1_id]['pts_against'] += $s2;
                $playerStats[$m->player1_id]['point_diff'] += ($s1 - $s2);

                $playerStats[$m->player2_id]['pts_for'] += $s2;
                $playerStats[$m->player2_id]['pts_against'] += $s1;
                $playerStats[$m->player2_id]['point_diff'] += ($s2 - $s1);

                if ($m->winner_id == $m->player1_id) {
                    $playerStats[$m->player1_id]['points'] += 3;
                    $playerStats[$m->player1_id]['wins'] += 1;
                    $playerStats[$m->player2_id]['losses'] += 1;
                    $headToHead[$m->player1_id][$m->player2_id] = true;
                } elseif ($m->winner_id == $m->player2_id) {
                    $playerStats[$m->player2_id]['points'] += 3;
                    $playerStats[$m->player2_id]['wins'] += 1;
                    $playerStats[$m->player1_id]['losses'] += 1;
                    $headToHead[$m->player2_id][$m->player1_id] = true;
                } else {
                    $playerStats[$m->player1_id]['points'] += 1;
                    $playerStats[$m->player2_id]['points'] += 1;
                }
            }
        }

        // Calcular Buchholz en Suizo
        foreach ($swissMatches as $m) {
            $isBye = is_null($m->player2_id) || $m->player1_score == 99 || $m->player2_score == 99;
            if (!$isBye && $m->player1_id && $m->player2_id && isset($playerStats[$m->player1_id]) && isset($playerStats[$m->player2_id])) {
                $playerStats[$m->player1_id]['buchholz'] += $playerStats[$m->player2_id]['points'];
                $playerStats[$m->player2_id]['buchholz'] += $playerStats[$m->player1_id]['points'];
            }
        }
    }

    private function processBracketMatches(
        Collection $completedBracketMatches,
        array &$playerStats,
        array &$headToHead,
        bool $hasSwiss,
        bool $hasBracket
    ): void {
        foreach ($completedBracketMatches as $bm) {
            $isGhostOrBye = is_null($bm->player1_id) || is_null($bm->player2_id) || $bm->player1_score == 99 || $bm->player2_score == 99;
            if ($isGhostOrBye) {
                continue;
            }
            if (isset($playerStats[$bm->player1_id]) && isset($playerStats[$bm->player2_id])) {
                $s1 = (int) $bm->player1_score;
                $s2 = (int) $bm->player2_score;

                $playerStats[$bm->player1_id]['pts_for'] += $s1;
                $playerStats[$bm->player1_id]['pts_against'] += $s2;
                $playerStats[$bm->player1_id]['point_diff'] += ($s1 - $s2);

                $playerStats[$bm->player2_id]['pts_for'] += $s2;
                $playerStats[$bm->player2_id]['pts_against'] += $s1;
                $playerStats[$bm->player2_id]['point_diff'] += ($s2 - $s1);

                if ($bm->winner_id == $bm->player1_id) {
                    $playerStats[$bm->player1_id]['wins'] += 1;
                    $playerStats[$bm->player2_id]['losses'] += 1;
                    if (!$hasSwiss) {
                        $playerStats[$bm->player1_id]['points'] += 3;
                        $headToHead[$bm->player1_id][$bm->player2_id] = true;
                    }
                } elseif ($bm->winner_id == $bm->player2_id) {
                    $playerStats[$bm->player2_id]['wins'] += 1;
                    $playerStats[$bm->player1_id]['losses'] += 1;
                    if (!$hasSwiss) {
                        $playerStats[$bm->player2_id]['points'] += 3;
                        $headToHead[$bm->player2_id][$bm->player1_id] = true;
                    }
                }
            }
        }

        if (!$hasSwiss && $hasBracket) {
            foreach ($completedBracketMatches as $bm) {
                $isGhostOrBye = is_null($bm->player1_id) || is_null($bm->player2_id) || $bm->player1_score == 99 || $bm->player2_score == 99;
                if (!$isGhostOrBye && isset($playerStats[$bm->player1_id]) && isset($playerStats[$bm->player2_id])) {
                    $playerStats[$bm->player1_id]['buchholz'] += $playerStats[$bm->player2_id]['points'];
                    $playerStats[$bm->player2_id]['buchholz'] += $playerStats[$bm->player1_id]['points'];
                }
            }
        }
    }

    private function calculateHeadToHeadTiebreakers(array &$playerStats, array $headToHead): void
    {
        foreach ($playerStats as $uid => &$st) {
            $tiedOpponents = array_filter($playerStats, fn($other) => $other['id'] !== $uid && $other['points'] === $st['points']);
            foreach ($tiedOpponents as $opp) {
                if (!empty($headToHead[$uid][$opp['id']])) {
                    $st['h2h_wins']++;
                }
            }
        }
        unset($st);
    }

    private function calculateBracketProgress(
        array &$playerStats,
        Collection $bracketMatchesAll,
        Collection $completedBracketMatches,
        Collection $allEventMatches
    ): void {
        $hasResetCompleted = $completedBracketMatches->where('bracket_type', 'grand_final_reset')->isNotEmpty();

        foreach ($playerStats as $uid => &$stats) {
            $allPlayerBracketMatches = $bracketMatchesAll->filter(fn($m) => $m->player1_id == $uid || $m->player2_id == $uid);
            $compPlayerBracketMatches = $completedBracketMatches->filter(fn($m) => $m->player1_id == $uid || $m->player2_id == $uid);

            if ($allPlayerBracketMatches->isNotEmpty()) {
                $wonGFReset = $compPlayerBracketMatches->where('bracket_type', 'grand_final_reset')->where('winner_id', $uid)->isNotEmpty();
                $lostGFReset = $compPlayerBracketMatches->where('bracket_type', 'grand_final_reset')->where('winner_id', '!=', $uid)->isNotEmpty();
                $wonGF = $compPlayerBracketMatches->where('bracket_type', 'grand_final')->where('winner_id', $uid)->isNotEmpty();
                $lostGF = $compPlayerBracketMatches->where('bracket_type', 'grand_final')->where('winner_id', '!=', $uid)->isNotEmpty();

                if ($hasResetCompleted && $wonGFReset) {
                    $stats['bracket_score'] = 10000;
                    $stats['bracket_stage'] = 'CAMPEÓN';
                } elseif ($hasResetCompleted && $lostGFReset) {
                    $stats['bracket_score'] = 9000;
                    $stats['bracket_stage'] = 'FINALISTA';
                } elseif (!$hasResetCompleted && $wonGF && !$allEventMatches->where('bracket_type', 'grand_final_reset')->isNotEmpty()) {
                    $stats['bracket_score'] = 10000;
                    $stats['bracket_stage'] = 'CAMPEÓN';
                } elseif (!$hasResetCompleted && $lostGF && !$allEventMatches->where('bracket_type', 'grand_final_reset')->isNotEmpty()) {
                    $stats['bracket_score'] = 9000;
                    $stats['bracket_stage'] = 'FINALISTA';
                } else {
                    $lostInLosers = $compPlayerBracketMatches->where('bracket_type', 'losers')->filter(fn($m) => $m->winner_id && $m->winner_id != $uid);
                    if ($lostInLosers->isNotEmpty()) {
                        $roundEliminated = $lostInLosers->max('round_number');
                        $stats['bracket_score'] = 5000 + $roundEliminated;
                        $stats['bracket_stage'] = 'ELIM. LOSERS R' . $roundEliminated;
                    } else {
                        $maxRoundReached = $allPlayerBracketMatches->max('round_number') ?? 1;
                        $inWinners = $allPlayerBracketMatches->whereIn('bracket_type', ['winners', 'grand_final', 'grand_final_reset'])
                            ->where('status', '!=', 'completed')->isNotEmpty();
                        $stats['bracket_score'] = ($inWinners ? 7500 : 7000) + $maxRoundReached;
                        $stats['bracket_stage'] = $inWinners ? 'EN WINNERS' : 'EN LOSERS';
                    }
                }
            }
        }
        unset($stats);
    }

    private function comparePlayers(array $a, array $b, array $headToHead): int
    {
        if ($a['bracket_score'] != $b['bracket_score']) return $b['bracket_score'] <=> $a['bracket_score'];
        if ($a['points'] != $b['points']) return $b['points'] <=> $a['points'];
        if ($a['buchholz'] != $b['buchholz']) return $b['buchholz'] <=> $a['buchholz'];

        if (!empty($headToHead[$a['id']][$b['id']])) return -1;
        if (!empty($headToHead[$b['id']][$a['id']])) return 1;
        if ($a['h2h_wins'] != $b['h2h_wins']) return $b['h2h_wins'] <=> $a['h2h_wins'];

        if ($a['point_diff'] != $b['point_diff']) return $b['point_diff'] <=> $a['point_diff'];
        return $b['pts_for'] <=> $a['pts_for'];
    }

    private function assignSharedRanks(array &$standings, bool $hasBracket): void
    {
        $currentSharedRank = 1;
        foreach ($standings as $idx => &$player) {
            $actualPos = $idx + 1;
            if ($idx === 0) {
                $currentSharedRank = 1;
            } else {
                $prev = $standings[$idx - 1];
                if ($hasBracket && $player['bracket_score'] > 0 && $prev['bracket_score'] > 0) {
                    if ($player['bracket_score'] !== $prev['bracket_score']) {
                        $currentSharedRank = $actualPos;
                    }
                } else {
                    $sameSwiss = ($player['points'] === $prev['points'])
                        && ($player['buchholz'] === $prev['buchholz'])
                        && ($player['point_diff'] === $prev['point_diff'])
                        && ($player['h2h_wins'] === $prev['h2h_wins']);
                    if (!$sameSwiss) {
                        $currentSharedRank = $actualPos;
                    }
                }
            }

            $player['shared_rank'] = $currentSharedRank;
            $player['puesto_oficial'] = $this->rankToPuestoName($currentSharedRank);
        }
        unset($player);
    }

    private function rankToPuestoName(int $rank): string
    {
        if ($rank === 1) return 'primero';
        if ($rank === 2) return 'segundo';
        if ($rank === 3) return 'tercero';
        if ($rank === 4) return 'cuarto';
        if ($rank <= 6) return 'quinto';         // 5º - 6º
        if ($rank <= 8) return 'septimo';        // 7º - 8º
        if ($rank <= 12) return 'noveno';        // 9º - 12º
        if ($rank <= 16) return 'decimotercero'; // 13º - 16º
        return 'decimoseptimo';                  // 17º+
    }
}
