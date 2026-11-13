<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TournamentMatch;
use App\Services\Discord\DiscordNotifier;
use App\Services\Tournament\StandingsService;
use App\Services\Tournament\SwissEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentEngineController extends Controller
{
    private StandingsService $standingsService;
    private DiscordNotifier $discordNotifier;

    public function __construct(?StandingsService $standingsService = null, ?DiscordNotifier $discordNotifier = null)
    {
        $this->standingsService = $standingsService ?? app(StandingsService::class);
        $this->discordNotifier = $discordNotifier ?? app(DiscordNotifier::class);
    }

    /**
     * VERIFICADOR CENTRALIZADO DE PERMISOS DE ARENA
     */
    private function canManageEvent(Event $event): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'juez', 'arbitro', 'revisor'])) {
            return true;
        }

        if ($event->judges()->where('user_id', $user->id)->exists()) {
            return true;
        }

        $isTeamEvent = in_array($event->beys, ['equipos', 'crew_battle']) || str_starts_with($event->name, 'Duelo Express:');
        if (!$isTeamEvent && $event->created_by == $user->id) {
            return true;
        }

        return false;
    }

    public function getArenaDashboard(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        if (in_array($event->beys, ['equipos', 'crew_battle'])) {
            $this->syncTeamMonthlyLimits($eventId);
        }

        // AUTO-SANACIÓN DE BYES EN SUIZO
        TournamentMatch::where('event_id', $eventId)
            ->whereIn('bracket_type', ['swiss', 'round_robin'])
            ->where('status', '!=', 'completed')
            ->where(function ($q) {
                $q->whereNull('player2_id')
                  ->orWhere('player1_score', 99)
                  ->orWhere('player2_score', 99);
            })
            ->update([
                'status'    => 'completed',
                'winner_id' => \Illuminate\Support\Facades\DB::raw('COALESCE(winner_id, player1_id)')
            ]);

        $allMatches = TournamentMatch::where('event_id', $eventId)
            ->select('id', 'round_number', 'bracket_type', 'status', 'player1_id', 'player2_id', 'team1_id', 'player1_score', 'player2_score')
            ->get();

        $maxRound = $allMatches->max('round_number') ?? 0;
        $isBracket = $allMatches->whereNotIn('bracket_type', ['swiss', 'round_robin'])->isNotEmpty();

        $roundsStatus = [];
        $firstPendingRound = $maxRound;

        for ($i = 1; $i <= $maxRound; $i++) {
            $matchesInRound = $allMatches->where('round_number', $i)->filter(function ($m) {
                if (in_array($m->bracket_type, ['swiss', 'round_robin'])) {
                    if (!is_null($m->team1_id)) return true;
                    return !is_null($m->player1_id) && !is_null($m->player2_id) && $m->player1_score != 99 && $m->player2_score != 99;
                }
                return true;
            });

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

        // Eliminamos 'lockedByUser' del with() para evitar Error 500 por relación no encontrada
        $matches = TournamentMatch::with([
                'player1:id,name',
                'player2:id,name',
                'team1:id,name',
                'team2:id,name'
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

        // Buscamos los nombres de los jueces bloqueadores manualmente para no romper la app
        $lockedByIds = $matches->pluck('locked_by')->filter()->unique();
        $lockedUsers = \App\Models\User::whereIn('id', $lockedByIds)->pluck('name', 'id');

        foreach ($matches as $m) {
            if ($m->locked_by && isset($lockedUsers[$m->locked_by])) {
                $m->setAttribute('locked_by_user', ['id' => $m->locked_by, 'name' => $lockedUsers[$m->locked_by]]);
            }
        }

        $byeMatch = TournamentMatch::with('player1:id,name')
            ->where('event_id', $eventId)
            ->where('round_number', $requestedRound)
            ->whereIn('bracket_type', ['swiss', 'round_robin'])
            ->where(function ($q) {
                $q->whereNull('player2_id')->orWhere('player1_score', 99)->orWhere('player2_score', 99);
            })
            ->first();

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
            'bye_player'    => $byeMatch && $byeMatch->player1 ? $byeMatch->player1->name : null,
            'matches'       => $matches
        ]);
    }

    public function generateRound($eventId, \App\Services\Tournament\SwissEngine $engine)
    {
        $event = Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        TournamentMatch::where('event_id', $eventId)
            ->whereIn('bracket_type', ['swiss', 'round_robin'])
            ->where('status', '!=', 'completed')
            ->where(function ($q) {
                $q->whereNull('player2_id')
                  ->orWhere('player1_score', 99)
                  ->orWhere('player2_score', 99);
            })
            ->update([
                'status'    => 'completed',
                'winner_id' => \Illuminate\Support\Facades\DB::raw('COALESCE(winner_id, player1_id)')
            ]);

        $hadMatchesBefore = TournamentMatch::where('event_id', $eventId)->exists();

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

        TournamentMatch::where('event_id', $eventId)
            ->whereIn('bracket_type', ['swiss', 'round_robin'])
            ->where('status', '!=', 'completed')
            ->where(function ($q) {
                $q->whereNull('player2_id')
                  ->orWhere('player1_score', 99)
                  ->orWhere('player2_score', 99);
            })
            ->update([
                'status'    => 'completed',
                'winner_id' => \Illuminate\Support\Facades\DB::raw('COALESCE(winner_id, player1_id)')
            ]);

        if (!empty($result['success']) && !$hadMatchesBefore) {
            if (empty($event->challonge)) {
                $event->challonge = route('torneo.bracket', $event->id);
                $event->save();
            }
            try {
                $this->discordNotifier->sendBracketNotification($event, true);
            } catch (\Exception $e) {}
        }

        return response()->json($result);
    }

    private function generateTeamRoundRobin($event, $validTeams)
    {
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth();

        $teamMatchCounts = [];
        $playedMatchups = [];
        $matchesCreated = 0;

        shuffle($validTeams);

        foreach ($validTeams as $teamId) {
            $teamMatchCounts[$teamId] = \Illuminate\Support\Facades\DB::table('teams_versus')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function ($q) use ($teamId) {
                    $q->where('team_id_1', $teamId)->orWhere('team_id_2', $teamId);
                })->count();
        }

        $allVersusThisMonth = \Illuminate\Support\Facades\DB::table('teams_versus')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('matchup', 'crew_battle')
            ->get();

        foreach ($allVersusThisMonth as $v) {
            $playedMatchups[$v->team_id_1 . '_' . $v->team_id_2] = true;
            $playedMatchups[$v->team_id_2 . '_' . $v->team_id_1] = true;
        }

        $teamsPool = array_values($validTeams);
        if (count($teamsPool) % 2 !== 0) {
            $teamsPool[] = null;
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
            $last = array_pop($teamsPool);
            array_splice($teamsPool, 1, 0, [$last]);
        }

        $orderedPairings = [];
        $lastMatchTeams = [];

        while (!empty($rawPairings)) {
            $pickedIndex = null;

            foreach ($rawPairings as $idx => $pair) {
                if (!in_array($pair[0], $lastMatchTeams) && !in_array($pair[1], $lastMatchTeams)) {
                    $pickedIndex = $idx;
                    break;
                }
            }

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

        foreach ($orderedPairings as $pair) {
            $t1 = $pair[0];
            $t2 = $pair[1];

            $alreadyPlayed = isset($playedMatchups[$t1 . '_' . $t2]);
            $t1AlreadyMaxed = ($teamMatchCounts[$t1] ?? 0) >= 5;
            $t2AlreadyMaxed = ($teamMatchCounts[$t2] ?? 0) >= 5;

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

        $monthlyCounts = [];
        foreach ($teamIds as $tid) {
            $monthlyCounts[$tid] = \Illuminate\Support\Facades\DB::table('teams_versus')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function ($q) use ($tid) {
                    $q->where('team_id_1', $tid)->orWhere('team_id_2', $tid);
                })->count();
        }

        $playedMatchups = [];
        $allVersusThisMonth = \Illuminate\Support\Facades\DB::table('teams_versus')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('matchup', 'crew_battle')
            ->get();

        foreach ($allVersusThisMonth as $v) {
            $playedMatchups[$v->team_id_1 . '_' . $v->team_id_2] = true;
            $playedMatchups[$v->team_id_2 . '_' . $v->team_id_1] = true;
        }

        foreach ($eventMatches->where('status', 'completed') as $compMatch) {
            $monthlyCounts[$compMatch->team1_id] = ($monthlyCounts[$compMatch->team1_id] ?? 0) + 1;
            $monthlyCounts[$compMatch->team2_id] = ($monthlyCounts[$compMatch->team2_id] ?? 0) + 1;
        }

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

    public function storeBattle(Request $request, $matchId)
    {
        $finishType = $request->finish_type;
        if (!in_array($finishType, ['spin', 'over', 'burst', 'xtreme'], true)) {
            return response()->json(['success' => false, 'message' => '⚠️ Tipo de finalización no válido.'], 422);
        }

        return DB::transaction(function () use ($request, $matchId, $finishType) {
            $match = TournamentMatch::where('id', $matchId)->lockForUpdate()->firstOrFail();
            $event = Event::findOrFail($match->event_id);

            if (!$this->canManageEvent($event)) {
                return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
            }

            if ($match->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Esta partida ya figura como finalizada. Deshaz la última acción si necesitas editarla.',
                    'match_completed' => true,
                    'p1_score' => $match->player1_score,
                    'p2_score' => $match->player2_score
                ], 409);
            }

            $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
            $winnerId = $request->winner_id;
            $winnerTeam = null;

            if ($isTeamMatch) {
                $winnerTeam = DB::table('team_user')->where('user_id', $winnerId)->value('team_id');
                if (!$winnerId || !in_array((int) $winnerTeam, [(int) $match->team1_id, (int) $match->team2_id], true)) {
                    return response()->json(['success' => false, 'message' => '⚠️ El ganador seleccionado no pertenece a los equipos enfrentados.'], 422);
                }
            } else {
                if (!$winnerId || !in_array((int) $winnerId, [(int) $match->player1_id, (int) $match->player2_id], true)) {
                    return response()->json(['success' => false, 'message' => '⚠️ El ganador seleccionado no corresponde a ninguno de los jugadores del combate.'], 422);
                }
            }

            $points = 0;
            if ($finishType === 'spin') $points = 1;
            if ($finishType === 'over' || $finishType === 'burst') $points = 2;
            if ($finishType === 'xtreme') $points = 3;

            \App\Models\MatchBattle::create([
                'tournament_match_id' => $match->id,
                'player1_combo_id' => $request->player1_combo_id,
                'player2_combo_id' => $request->player2_combo_id,
                'winner_id' => $winnerId,
                'finish_type' => $finishType,
                'points_awarded' => $points,
            ]);

            $isTiebreakerActive = false;

            if ($isTeamMatch) {
                if ($winnerTeam == $match->team1_id) $match->player1_score += $points;
                if ($winnerTeam == $match->team2_id) $match->player2_score += $points;
            } else {
                if ($winnerId == $match->player1_id) {
                    $match->player1_score = min(4, $match->player1_score + $points);
                } elseif ($winnerId == $match->player2_id) {
                    $match->player2_score = min(4, $match->player2_score + $points);
                }
            }

            $matchCompleted = false;

            if ($isTeamMatch) {
                $allBattles = DB::table('match_battles')->where('tournament_match_id', $matchId)->get();
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

            if ($isTeamMatch && $matchCompleted) {
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
        });
    }

    public function deshacerUltimoRegistro($matchId)
    {
        return DB::transaction(function () use ($matchId) {
            $match = TournamentMatch::where('id', $matchId)->lockForUpdate()->firstOrFail();
            $event = Event::findOrFail($match->event_id);

            if (!$this->canManageEvent($event)) {
                return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
            }

            $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);

            if ($match->status === 'completed' && !in_array($match->bracket_type, ['swiss', 'round_robin'])) {
                $this->revertMatchProgression($match);
            }

            $lastBattle = \App\Models\MatchBattle::where('tournament_match_id', $matchId)
                ->lockForUpdate()
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

            $remainingBattles = DB::table('match_battles')->where('tournament_match_id', $matchId)->get();

            if ($isTeamMatch) {
                $p1_score = 0;
                $p2_score = 0;
                foreach ($remainingBattles as $b) {
                    $wTeam = DB::table('team_user')->where('user_id', $b->winner_id)->value('team_id');
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
        });
    }

    public function fastWinMatch(Request $request, $matchId)
    {
        $winnerSide = $request->winner_side;
        if (!in_array($winnerSide, ['p1', 'p2'], true)) {
            return response()->json(['success' => false, 'message' => '⚠️ Lado ganador no válido.'], 422);
        }

        return DB::transaction(function () use ($matchId, $winnerSide) {
            $match = TournamentMatch::where('id', $matchId)->lockForUpdate()->firstOrFail();
            $event = Event::findOrFail($match->event_id);

            if (!$this->canManageEvent($event)) {
                return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
            }

            if ($match->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Esta partida ya figura como finalizada. Deshaz el resultado actual si necesitas modificarlo.',
                    'match_completed' => true
                ], 409);
            }

            $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
            $resolvedWinnerId = ($winnerSide === 'p1')
                ? ($isTeamMatch ? $match->team1_id : $match->player1_id)
                : ($isTeamMatch ? $match->team2_id : $match->player2_id);

            if (empty($resolvedWinnerId)) {
                return response()->json(['success' => false, 'message' => '⚠️ El participante ganador no está asignado en esta partida.'], 422);
            }

            DB::table('match_battles')->where('tournament_match_id', $match->id)->delete();

            if ($winnerSide === 'p1') {
                $match->player1_score = 4;
                $match->player2_score = 0;
            } else {
                $match->player1_score = 0;
                $match->player2_score = 4;
            }

            $match->winner_id = $resolvedWinnerId;
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
        });
    }

    public function setManualScore(Request $request, $matchId)
    {
        $s1 = max(0, min(4, (int) $request->p1_score));
        $s2 = max(0, min(4, (int) $request->p2_score));

        if ($s1 < 4 && $s2 < 4) {
            return response()->json(['success' => false, 'message' => '⚠️ Para finalizar manualmente, uno de los jugadores debe llegar a 4 puntos.'], 422);
        }
        if ($s1 === 4 && $s2 === 4) {
            return response()->json(['success' => false, 'message' => '⚠️ No puede haber un empate 4 a 4.'], 422);
        }

        return DB::transaction(function () use ($matchId, $s1, $s2) {
            $match = TournamentMatch::where('id', $matchId)->lockForUpdate()->firstOrFail();
            $event = Event::findOrFail($match->event_id);

            if (!$this->canManageEvent($event)) {
                return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
            }

            if ($match->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Esta partida ya figura como finalizada. Deshaz el resultado actual si necesitas modificarlo.',
                    'match_completed' => true
                ], 409);
            }

            $isTeamMatch = !is_null($match->team1_id) && !is_null($match->team2_id);
            $resolvedWinnerId = ($s1 > $s2)
                ? ($isTeamMatch ? $match->team1_id : $match->player1_id)
                : ($isTeamMatch ? $match->team2_id : $match->player2_id);

            if (empty($resolvedWinnerId)) {
                return response()->json(['success' => false, 'message' => '⚠️ El participante ganador no está asignado en esta partida.'], 422);
            }

            DB::table('match_battles')->where('tournament_match_id', $match->id)->delete();

            $match->player1_score = $s1;
            $match->player2_score = $s2;
            $match->winner_id = $resolvedWinnerId;
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
        });
    }

    private function safeProgressMatch(TournamentMatch $match)
    {
        if (!$match->winner_id) return;
        $engine = new \App\Services\Tournament\DoubleEliminationEngine();
        $engine->progressMatch($match);
    }

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

    /**
     * CLASIFICACIÓN UNIFICADA (SUIZO + DOBLE ELIMINACIÓN) CON POSICIONES COMPARTIDAS EXACTAS
     */
    public function getStandings($eventId)
    {
        return response()->json($this->standingsService->getStandings($eventId));
    }

    public function generateTop(Request $request, $eventId, \App\Services\Tournament\DoubleEliminationEngine $engine)
    {
        $event = \App\Models\Event::findOrFail($eventId);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => '⛔ Acceso denegado.'], 403);
        }

        $result = $engine->generateTopCut($event, (int) $request->top_size);

        if ($result['success']) {
            try {
                $this->discordNotifier->sendBracketNotification($event, false);
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

        $user = auth()->user();
        $canManageHelpers = $user && (
            $user->hasAnyRole(['admin', 'juez', 'revisor']) ||
            (!$isTeamEvent && $event->created_by == $user->id)
        );

        $availableUsers = [];
        if ($isStaff) {
            $availableUsers = \App\Models\User::whereNotIn('id', $inscribedIds)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
        }

        $staffAsignado = $event->judges()->with('roles')->get()->map(function ($st) {
            $roles = $st->roles->pluck('name');
            $isMainJudge = $roles->contains('admin') || $roles->contains('juez');
            $isOnlyRevisor = !$isMainJudge && $roles->contains('revisor');
            return [
                'id'         => $st->id,
                'name'       => $st->name,
                'is_judge'   => $isMainJudge,
                'is_revisor' => $isOnlyRevisor,
                'is_helper'  => !$isMainJudge && !$isOnlyRevisor,
            ];
        })->values();

        $staffIds = $staffAsignado->pluck('id')->toArray();
        $availableStaffUsers = [];
        if ($canManageHelpers) {
            $availableStaffUsers = \App\Models\User::whereNotIn('id', $staffIds)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();
        }

        return response()->json([
            'success'               => true,
            'jugadores'             => $jugadores,
            'nombres'               => $nombresTxt,
            'is_staff'              => $isStaff,
            'can_manage_helpers'    => $canManageHelpers,
            'staff_asignado'        => $staffAsignado,
            'available_staff_users' => $availableStaffUsers,
            'is_team_event'         => $isTeamEvent,
            'event_type'            => $event->beys,
            'event_status'          => $event->status,
            'aforo_maximo'          => $limite,
            'available_users'       => $availableUsers
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
        $match = TournamentMatch::with('lockedByUser:id,name')->findOrFail($matchId);
        $event = Event::findOrFail($match->event_id);

        if (!$this->canManageEvent($event)) {
            return response()->json(['success' => false, 'message' => 'No tienes permisos de arbitraje.']);
        }

        $previousJudge = null;
        if ($match->locked_by !== null && $match->locked_by !== auth()->id()) {
            $previousJudge = $match->lockedByUser ? $match->lockedByUser->name : 'Otro árbitro';
        }

        $match->locked_by = auth()->id();
        $match->save();

        return response()->json([
            'success'        => true,
            'taken_over'     => !is_null($previousJudge),
            'previous_judge' => $previousJudge
        ]);
    }

    public function unlockMatch($matchId)
    {
        $match = TournamentMatch::findOrFail($matchId);

        if ($match->locked_by === auth()->id() || (auth()->check() && auth()->user()->hasAnyRole(['admin', 'juez', 'revisor']))) {
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

        $hasEngineMatches = TournamentMatch::where('event_id', $eventId)->exists();
        if ($hasEngineMatches && empty($event->challonge)) {
            $event->challonge = route('torneo.bracket', $event->id);
        }

        $event->status = in_array($event->beys, ['ranking', 'rankingplus']) ? 'PENDING' : 'COMPLETED';
        $event->save();

        if ($teamMatches->isEmpty() && !in_array($event->beys, ['equipos', 'crew_battle'])) {
            try {
                $standingsData = $this->standingsService->getStandings($event->id);
                $clasificacion = $standingsData['clasificacion'] ?? [];

                foreach ($clasificacion as $player) {
                    $nombrePuesto = $player['puesto_oficial'] ?? 'decimoseptimo';

                    \Illuminate\Support\Facades\DB::table('assist_user_event')
                        ->where('event_id', $event->id)
                        ->where('user_id', $player['id'])
                        ->where(function ($q) {
                            $q->whereNull('puesto')
                              ->orWhere('puesto', '')
                              ->orWhere('puesto', 'active')
                              ->orWhere('puesto', 'participante')
                              ->orWhere('puesto', '!=', 'dropped');
                        })
                        ->update(['puesto' => $nombrePuesto]);
                }

                $this->discordNotifier->sendTournamentReport($event, $clasificacion);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Error post-cierre torneo: " . $e->getMessage());
            }
        }

        $msg = $teamMatches->isNotEmpty()
            ? "🏆 Torneo finalizado. ¡{$volcados} duelo(s) de equipos generados en estado OPEN para validación!"
            : "🏆 Acta cerrada, posiciones compartidas guardadas y enlace del cuadro vinculado.";

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

        $swissRounds = \App\Models\TournamentMatch::with(['player1:id,name', 'player2:id,name', 'team1:id,name', 'team2:id,name'])
            ->where('event_id', $eventId)
            ->whereIn('bracket_type', ['swiss', 'round_robin'])
            ->orderBy('round_number', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('round_number');

        return view('hub.bracket', compact('event', 'winnersBracket', 'losersBracket', 'finals', 'swissRounds'));
    }

    public function renderBracketHtml($eventId)
    {
        [$winnersBracket, $losersBracket, $finals] = $this->buildFullBracketTree($eventId);

        return view('hub.partials.bracket_content', compact('winnersBracket', 'losersBracket', 'finals'));
    }

    public function showStadiumTv($eventId)
    {
        $event = \App\Models\Event::with('region')->findOrFail($eventId);
        return view('hub.tv', compact('event'));
    }
}
