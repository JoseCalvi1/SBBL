<?php

namespace App\Services\Analytics;

use App\Models\Event;
use App\Models\Region;
use App\Models\TournamentMatch;
use App\Models\TournamentResult;
use App\Services\Tournament\StandingsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BeyLabService
{
    /**
     * TTL de caché para agregaciones globales de BeyLab (10 minutos).
     */
    private const CACHE_TTL_SECONDS = 600;

    /**
     * Columnas permitidas para estadísticas por pieza.
     */
    private const ALLOWED_PART_COLUMNS = [
        'blade',
        'ratchet',
        'bit',
        'over_blade',
        'assist_blade',
    ];

    /**
     * Columnas permitidas para ordenación en el radar de combos.
     */
    private const ALLOWED_COMBO_SORTS = [
        'meta_score',
        'blade',
        'percentage_victories',
        'total_partidas',
        'eficiencia',
        'puntos_ganados_por_combate',
        'xtreme_wins',
        'burst_wins',
    ];

    /**
     * Analiza un enfrentamiento 3on3 para el BeyLab del Hub (POST /api/lab/analizar).
     *
     * @param  array<int, array<string, mixed>>  $miDeck
     * @param  array<int, array<string, mixed>>  $rivalDeck
     * @return array<string, mixed>
     */
    public function analyzeHubDeck(array $miDeck, array $rivalDeck): array
    {
        $topMetaBoss = $this->getTopMetaBoss([], 20);

        if (empty($rivalDeck)) {
            foreach ($topMetaBoss as $boss) {
                $rivalDeck[] = [
                    'blade'        => $boss->blade,
                    'over_blade'   => $boss->over_blade !== '-' ? $boss->over_blade : '',
                    'assist_blade' => $boss->assist_blade !== '-' ? $boss->assist_blade : '',
                    'ratchet'      => $boss->ratchet,
                    'bit'          => $boss->bit,
                ];
            }
        }

        $miDeckValid = array_values(array_filter($miDeck, fn($b) => !empty($b['blade'])));
        $rivalDeckValid = array_values(array_filter($rivalDeck, fn($b) => !empty($b['blade'])));

        $allBlades = collect(array_merge($miDeckValid, $rivalDeckValid))
            ->pluck('blade')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Consolidación: 1 sola consulta agrupada para estadísticas de todos los combos implicados
        $globalComboRows = $this->fetchGroupedStatsByBlades($allBlades, null, '', []);

        $miDeckStats = [];
        foreach ($miDeckValid as $b) {
            $stat = $this->computeComboStatFromGrouped($b, $globalComboRows, 'hub', null);
            if ($stat) {
                $miDeckStats[] = $stat;
            }
        }

        $rivalDeckStats = [];
        foreach ($rivalDeckValid as $b) {
            $stat = $this->computeComboStatFromGrouped($b, $globalComboRows, 'hub', null);
            if ($stat) {
                $rivalDeckStats[] = $stat;
            }
        }

        // Consolidación H2H: 1 sola consulta agrupada para toda la matriz 3x3
        $myBlades = collect($miDeckStats)->pluck('blade')->filter()->unique()->values()->all();
        $rivBlades = collect($rivalDeckStats)->pluck('blade')->filter()->unique()->values()->all();
        $h2hRows = $this->fetchGroupedH2HRows($myBlades, $rivBlades, '', []);

        $matrix = [];
        $totalWinProbSum = 0.0;
        $pairCount = 0;

        foreach ($miDeckStats as $myStat) {
            $row = [];
            foreach ($rivalDeckStats as $rivStat) {
                $duel = $this->resolveHeadToHeadFromRows($myStat, $rivStat, $h2hRows, 'hub', 'global_vs_global');
                $row[] = [
                    'my_combo'    => $myStat['combo'],
                    'rival_combo' => $rivStat['combo'],
                    'winrate'     => $duel['winrate'],
                    'matches'     => $duel['matches'],
                    'source'      => $duel['source'],
                ];
                $totalWinProbSum += $duel['winrate'];
                $pairCount++;
            }
            $matrix[] = $row;
        }

        $overallVsRival = $pairCount > 0 ? round($totalWinProbSum / $pairCount, 1) : 50.0;

        return [
            'success'          => true,
            'my_deck_stats'    => $miDeckStats,
            'rival_deck_stats' => $rivalDeckStats,
            'matrix'           => $matrix,
            'overall_winrate'  => $overallVsRival,
            'top_meta_boss'    => array_slice($topMetaBoss, 0, 3),
        ];
    }

    /**
     * Analiza un enfrentamiento 3on3 para el BeyLab Pro Web (POST /beyblade-database/stats/lab-analyze).
     *
     * @param  array<string, mixed>  $params
     * @param  int|null  $userId
     * @return array<string, mixed>
     */
    public function analyzeWebDeck(array $params, ?int $userId = null): array
    {
        $miDeck      = is_array($params['mi_deck'] ?? null) ? $params['mi_deck'] : [];
        $rivalDeck   = is_array($params['rival_deck'] ?? null) ? $params['rival_deck'] : [];
        $mode        = (string) ($params['mode'] ?? 'global_vs_global');
        $tipoEvento  = $params['tipo_evento'] ?? null;
        $fechaInicio = $params['fecha_inicio'] ?? null;
        $fechaFin    = $params['fecha_fin'] ?? null;

        [$eventFilterSql, $eventBindings] = $this->buildEventFilterSqlAndBindings([
            'tipo_evento'  => $tipoEvento,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
        ]);

        if (empty($rivalDeck)) {
            $topMetaBoss = $this->getTopMetaBoss([
                'tipo_evento'  => $tipoEvento,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin'    => $fechaFin,
            ], 25);

            foreach ($topMetaBoss as $boss) {
                $rivalDeck[] = [
                    'blade'        => $boss->blade,
                    'over_blade'   => $boss->over_blade !== '-' ? $boss->over_blade : '',
                    'assist_blade' => $boss->assist_blade !== '-' ? $boss->assist_blade : '',
                    'ratchet'      => $boss->ratchet,
                    'bit'          => $boss->bit,
                ];
            }
        }

        $miDeckValid = array_values(array_filter($miDeck, fn($b) => !empty($b['blade'])));
        $rivalDeckValid = array_values(array_filter($rivalDeck, fn($b) => !empty($b['blade'])));

        $allBlades = collect(array_merge($miDeckValid, $rivalDeckValid))
            ->pluck('blade')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Consolidación: 1 consulta agrupada global + 1 consulta agrupada personal (solo si aplica)
        $globalComboRows = $this->fetchGroupedStatsByBlades($allBlades, null, $eventFilterSql, $eventBindings);
        $personalComboRows = ($mode === 'personal_vs_global' && $userId)
            ? $this->fetchGroupedStatsByBlades(
                collect($miDeckValid)->pluck('blade')->filter()->unique()->values()->all(),
                $userId,
                $eventFilterSql,
                $eventBindings
            )
            : collect();

        $miDeckStats = [];
        $personalMatchesFound = 0;

        foreach ($miDeckValid as $b) {
            $globalStat = $this->computeComboStatFromGrouped($b, $globalComboRows, 'web', null);
            if (!$globalStat) {
                continue;
            }

            if ($mode === 'personal_vs_global' && $userId) {
                $personalStat = $this->computeComboStatFromGrouped($b, $personalComboRows, 'web', $userId);
                if ($personalStat && $personalStat['sample_size'] > 0) {
                    $personalMatchesFound += $personalStat['sample_size'];
                    $personalStat['global_winrate']  = $globalStat['winrate'];
                    $personalStat['global_sample']   = $globalStat['sample_size'];
                    $personalStat['delta_vs_global'] = round($personalStat['winrate'] - $globalStat['winrate'], 1);
                    $personalStat['using_personal']  = true;
                    $miDeckStats[] = $personalStat;
                } else {
                    $globalStat['global_winrate']  = $globalStat['winrate'];
                    $globalStat['global_sample']   = $globalStat['sample_size'];
                    $globalStat['delta_vs_global'] = 0;
                    $globalStat['using_personal']  = false;
                    $globalStat['precision']       = 'Sin registros tuyos (Usando Global)';
                    $miDeckStats[] = $globalStat;
                }
            } else {
                $globalStat['global_winrate']  = $globalStat['winrate'];
                $globalStat['global_sample']   = $globalStat['sample_size'];
                $globalStat['delta_vs_global'] = null;
                $globalStat['using_personal']  = false;
                $miDeckStats[] = $globalStat;
            }
        }

        $rivalDeckStats = [];
        foreach ($rivalDeckValid as $b) {
            $stat = $this->computeComboStatFromGrouped($b, $globalComboRows, 'web', null);
            if ($stat) {
                $rivalDeckStats[] = $stat;
            }
        }

        // Consolidación H2H: 1 sola consulta agrupada para toda la matriz 3x3
        $myBlades = collect($miDeckStats)->pluck('blade')->filter()->unique()->values()->all();
        $rivBlades = collect($rivalDeckStats)->pluck('blade')->filter()->unique()->values()->all();
        $h2hRows = $this->fetchGroupedH2HRows($myBlades, $rivBlades, $eventFilterSql, $eventBindings);

        $matrix          = [];
        $totalWinProbSum = 0.0;
        $pairCount       = 0;

        foreach ($miDeckStats as $rIdx => $myStat) {
            $row = [];
            $rowSum = 0.0;
            foreach ($rivalDeckStats as $rivStat) {
                $duel = $this->resolveHeadToHeadFromRows($myStat, $rivStat, $h2hRows, 'web', $mode);
                $row[] = [
                    'my_combo'    => $myStat['combo'],
                    'rival_combo' => $rivStat['combo'],
                    'winrate'     => $duel['winrate'],
                    'matches'     => $duel['matches'],
                    'source'      => $duel['source'],
                ];
                $rowSum += $duel['winrate'];
                $totalWinProbSum += $duel['winrate'];
                $pairCount++;
            }
            $miDeckStats[$rIdx]['avg_vs_rival_deck'] = count($rivalDeckStats) > 0
                ? round($rowSum / count($rivalDeckStats), 1)
                : $myStat['winrate'];
            $matrix[] = $row;
        }

        $overallVsRival = $pairCount > 0 ? round($totalWinProbSum / $pairCount, 1) : 50.0;

        $ordenSalida = $miDeckStats;
        usort($ordenSalida, function (array $a, array $b) {
            $scoreA = ($a['avg_vs_rival_deck'] ?? 50) - (($a['burst_risk'] ?? 10) * 0.4);
            $scoreB = ($b['avg_vs_rival_deck'] ?? 50) - (($b['burst_risk'] ?? 10) * 0.4);
            return $scoreB <=> $scoreA;
        });

        $weakIndex = 0;
        $minAvgWr = 999.0;
        foreach ($miDeckStats as $idx => $st) {
            if (($st['avg_vs_rival_deck'] ?? 50) < $minAvgWr) {
                $minAvgWr = $st['avg_vs_rival_deck'] ?? 50;
                $weakIndex = $idx;
            }
        }
        $eslabonDebil = $miDeckStats[$weakIndex] ?? null;

        $lockedBlades   = [];
        $lockedRatchets = [];
        $lockedBits     = [];
        foreach ($miDeckStats as $idx => $st) {
            if ($idx === $weakIndex) {
                continue;
            }
            if (!empty($st['blade'])) {
                $lockedBlades[] = $st['blade'];
            }
            if (!empty($st['ratchet']) && $st['ratchet'] !== 'UX Expanded') {
                $lockedRatchets[] = $st['ratchet'];
            }
            if (!empty($st['bit']) && $st['bit'] !== 'Without Bit') {
                $lockedBits[] = $st['bit'];
            }
        }

        $rivalBlades = collect($rivalDeckStats)->pluck('blade')->filter()->values()->all();
        $recommendedCounters = $this->getRecommendedCounters(
            $rivalBlades,
            $lockedBlades,
            $lockedRatchets,
            $lockedBits,
            $eslabonDebil,
            $weakIndex
        );

        return [
            'success'                => true,
            'mode'                   => $mode,
            'personal_matches_found' => $personalMatchesFound,
            'my_deck_stats'          => $miDeckStats,
            'rival_deck_stats'       => $rivalDeckStats,
            'matrix'                 => $matrix,
            'overall_winrate'        => $overallVsRival,
            'radar_my_deck'          => $this->calcRadarProfile($miDeckStats),
            'radar_rival_deck'       => $this->calcRadarProfile($rivalDeckStats),
            'recommended_order'      => array_map(fn($x) => $x['combo'], $ordenSalida),
            'weak_link'              => $eslabonDebil ? $eslabonDebil['combo'] : null,
            'weak_slot'              => $weakIndex + 1,
            'recommended_counters'   => $recommendedCounters,
        ];
    }

    private function metaScoreSql(): string
    {
        return 'ROUND(((SUM(is_winner) + 10) / (COUNT(*) + 20)) * 100 + LEAST(18, LOG10(COUNT(*) + 1) * 6.5), 2)';
    }

    /**
     * Obtiene el ranking de combos del Meta para el Hub (GET /api/meta).
     *
     * @param  int  $minPartidas
     * @param  string  $dias
     * @return array<int, object>
     */
    public function getHubMeta(int $minPartidas = 5, string $dias = '30'): array
    {
        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $minPartidas = max(1, $minPartidas);
        $cacheKey = sprintf('beylab:hub_meta:v2:%s:dias_%s:min_%d', $seasonStart, $dias, $minPartidas);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($minPartidas, $dias, $seasonStart) {
            $fechaCorte = $seasonStart;
            if ($dias !== 'all') {
                $diasInt = max(1, (int) $dias);
                $diasCorte = Carbon::now()->subDays($diasInt)->toDateString();
                if ($diasCorte > $seasonStart) {
                    $fechaCorte = $diasCorte;
                }
            }

            $dateSql = ' AND e.date >= ? ';
            $dateBindings = [$fechaCorte];
            $comboBindings = array_merge($dateBindings, $dateBindings, [$minPartidas]);
            $metaScoreSql = $this->metaScoreSql();

            $combos = DB::select("
                SELECT
                    blade, ratchet, bit,
                    CASE WHEN over_blade IS NULL OR over_blade = '' OR over_blade LIKE '%Selecciona%' THEN '-' ELSE over_blade END as over_blade,
                    CASE WHEN assist_blade IS NULL OR assist_blade = '' OR assist_blade LIKE '%Selecciona%' THEN '-' ELSE assist_blade END as assist_blade,
                    COUNT(*) as total_partidas,
                    SUM(is_winner) as total_victorias,
                    SUM(is_loser) as total_derrotas,
                    (SUM(is_winner) / GREATEST(COUNT(*), 1)) * 100 as percentage_victories,
                    {$metaScoreSql} as meta_score,
                    SUM(points_won) as eficiencia,
                    ROUND(IFNULL(SUM(points_won) / NULLIF(SUM(is_winner), 0), 0), 2) as puntos_ganados_por_combate,
                    ROUND(IFNULL(SUM(points_lost) / NULLIF(SUM(is_loser), 0), 0), 2) as puntos_perdidos_por_combate,
                    SUM(spin_wins) as spin_wins,
                    SUM(over_wins) as over_wins,
                    SUM(burst_wins) as burst_wins,
                    SUM(xtreme_wins) as xtreme_wins
                FROM (
                    SELECT
                        c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                        (mb.winner_id = c.user_id) as is_winner,
                        (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                        CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_won,
                        CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_lost,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c ON mb.player1_combo_id = c.id
                    WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND c.blade IS NOT NULL AND c.blade NOT LIKE '%Selecciona%' AND c.blade != 'DESCONOCIDO' {$dateSql}

                    UNION ALL

                    SELECT
                        c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                        (mb.winner_id = c.user_id) as is_winner,
                        (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                        CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_won,
                        CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_lost,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c ON mb.player2_combo_id = c.id
                    WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND c.blade IS NOT NULL AND c.blade NOT LIKE '%Selecciona%' AND c.blade != 'DESCONOCIDO' {$dateSql}
                ) as combates_unificados
                GROUP BY blade, ratchet, bit,
                    CASE WHEN over_blade IS NULL OR over_blade = '' OR over_blade LIKE '%Selecciona%' THEN '-' ELSE over_blade END,
                    CASE WHEN assist_blade IS NULL OR assist_blade = '' OR assist_blade LIKE '%Selecciona%' THEN '-' ELSE assist_blade END
                HAVING total_partidas >= ?
                ORDER BY meta_score DESC, total_partidas DESC, percentage_victories DESC
                LIMIT 50
            ", $comboBindings);

            $matchupsMap = $this->buildGlobalMatchupsMap($dateSql, $dateBindings);

            foreach ($combos as &$combo) {
                $comboKey = $combo->blade . ' ' . $combo->ratchet . ' ' . $combo->bit;

                $bestMatchups = [];
                if (!empty($matchupsMap[$comboKey]['wins'])) {
                    $wins = $matchupsMap[$comboKey]['wins'];
                    arsort($wins);
                    $filteredWins = array_filter($wins, fn($k) => $k !== $comboKey, ARRAY_FILTER_USE_KEY);
                    foreach (array_slice($filteredWins, 0, 3, true) as $enemy => $w) {
                        $bestMatchups[] = ['combo' => $enemy, 'victorias' => $w];
                    }
                }
                $combo->mejores_emparejamientos = $bestMatchups;

                $worstMatchups = [];
                if (!empty($matchupsMap[$comboKey]['losses'])) {
                    $losses = $matchupsMap[$comboKey]['losses'];
                    arsort($losses);
                    $filteredLosses = array_filter($losses, fn($k) => $k !== $comboKey, ARRAY_FILTER_USE_KEY);
                    foreach (array_slice($filteredLosses, 0, 3, true) as $enemy => $l) {
                        $worstMatchups[] = ['combo' => $enemy, 'derrotas' => $l];
                    }
                }
                $combo->peores_emparejamientos = $worstMatchups;

                $styles = [
                    'SPIN FINISHER'   => (int) $combo->spin_wins,
                    'OVER FINISHER'   => (int) $combo->over_wins,
                    'BURST ATTACKER'  => (int) $combo->burst_wins,
                    'XTREME ASSASSIN' => (int) $combo->xtreme_wins,
                ];
                arsort($styles);
                $signatureStyle = array_key_first($styles);
                $combo->estilo_combate = $styles[$signatureStyle] > 0 ? $signatureStyle : 'BALANCEADO';
            }
            unset($combo);

            return $combos;
        });
    }

    /**
     * Obtiene todos los datos del panel Radar Meta Pro + Tier List + BeyLab Web (GET /beyblade-database/stats).
     *
     * @param  array<string, mixed>  $filters
     * @param  int|null  $userId
     * @return array<string, mixed>
     */
    public function getBeybladeStatsDashboard(array $filters, ?int $userId = null): array
    {
        $sort        = (string) ($filters['sort'] ?? 'meta_score');
        $order       = strtolower((string) ($filters['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $minPartidas = (int) ($filters['min_partidas'] ?? 5);
        $fechaInicio = $filters['fecha_inicio'] ?? null;
        $fechaFin    = $filters['fecha_fin'] ?? null;
        $tipoEvento  = $filters['tipo_evento'] ?? null;
        $regionId    = $filters['region_id'] ?? null;
        $onlyUser    = !empty($filters['only_user_parts']) && $userId !== null;

        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');

        // 1. Catálogos cacheados (10 min)
        $catalogs = Cache::remember("beylab:catalogs:{$seasonStart}", self::CACHE_TTL_SECONDS, function () {
            return [
                'blades'       => DB::table('blades')->orderBy('nombre_takara')->pluck('nombre_takara'),
                'overBlades'   => DB::table('over_blades')->orderBy('nombre')->pluck('nombre'),
                'assistBlades' => DB::table('assist_blades')->orderBy('nombre')->pluck('nombre'),
                'ratchets'     => DB::table('ratchets')->orderBy('nombre')->pluck('nombre'),
                'bits'         => DB::table('bits')->orderBy('nombre')->pluck('nombre'),
                'regions'      => Region::orderBy('name')->get(['id', 'name']),
                'playersList'  => DB::table('users')->orderBy('name')->get(['id', 'name']),
            ];
        });

        // 2. Construcción de filtros parametrizados
        $bindings = [];
        $whereSql = " WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme') AND c.blade IS NOT NULL AND c.blade NOT LIKE '%Selecciona%' AND c.blade != 'DESCONOCIDO' ";

        if (!empty($filters['blade'])) {
            $whereSql .= ' AND c.blade = ? ';
            $bindings[] = $filters['blade'];
        }
        if (!empty($filters['over_blade'])) {
            $whereSql .= ' AND c.over_blade = ? ';
            $bindings[] = $filters['over_blade'];
        }
        if (!empty($filters['assist_blade'])) {
            $whereSql .= ' AND c.assist_blade = ? ';
            $bindings[] = $filters['assist_blade'];
        }
        if (!empty($filters['ratchet'])) {
            $whereSql .= ' AND c.ratchet = ? ';
            $bindings[] = $filters['ratchet'];
        }
        if (!empty($filters['bit'])) {
            $whereSql .= ' AND c.bit = ? ';
            $bindings[] = $filters['bit'];
        }

        [$eventFilterSql, $eventBindings] = $this->buildEventFilterSqlAndBindings([
            'tipo_evento'  => $tipoEvento,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
            'region_id'    => $regionId,
        ]);

        $whereSql .= $eventFilterSql;
        $bindings = array_merge($bindings, $eventBindings);

        if ($onlyUser && $userId) {
            $whereSql .= ' AND c.user_id = ? ';
            $bindings[] = $userId;
        }

        $sortCol = in_array($sort, self::ALLOWED_COMBO_SORTS, true) ? $sort : 'meta_score';

        $statsCacheKey = 'beylab:dashboard_stats:v2:' . $seasonStart . ':' . md5(json_encode([
            'sort'         => $sortCol,
            'order'        => $order,
            'min_partidas' => $minPartidas,
            'blade'        => $filters['blade'] ?? '',
            'over_blade'   => $filters['over_blade'] ?? '',
            'assist_blade' => $filters['assist_blade'] ?? '',
            'ratchet'      => $filters['ratchet'] ?? '',
            'bit'          => $filters['bit'] ?? '',
            'fecha_inicio' => $fechaInicio ?? '',
            'fecha_fin'    => $fechaFin ?? '',
            'tipo_evento'  => $tipoEvento ?? '',
            'region_id'    => $regionId ?? '',
            'user_id'      => $onlyUser ? $userId : 0,
        ]));

        $aggregated = Cache::remember($statsCacheKey, self::CACHE_TTL_SECONDS, function () use (
            $whereSql,
            $bindings,
            $minPartidas,
            $sortCol,
            $order,
            $eventFilterSql,
            $eventBindings
        ) {
            $unionBindings = array_merge($bindings, $bindings, [$minPartidas]);
            $metaScoreSql  = $this->metaScoreSql();

            $beybladeStats = collect(DB::select("
                SELECT 
                    blade, ratchet, bit,
                    CASE WHEN over_blade IS NULL OR over_blade = '' OR over_blade LIKE '%Selecciona%' THEN '-' ELSE over_blade END as over_blade,
                    CASE WHEN assist_blade IS NULL OR assist_blade = '' OR assist_blade LIKE '%Selecciona%' THEN '-' ELSE assist_blade END as assist_blade,
                    COUNT(*) as total_partidas,
                    SUM(is_winner) as total_victorias,
                    SUM(is_loser) as total_derrotas,
                    (SUM(is_winner) / GREATEST(COUNT(*), 1)) * 100 as percentage_victories,
                    {$metaScoreSql} as meta_score,
                    SUM(points_won) as eficiencia,
                    ROUND(IFNULL(SUM(points_won) / NULLIF(SUM(is_winner), 0), 0), 2) as puntos_ganados_por_combate,
                    ROUND(IFNULL(SUM(points_lost) / NULLIF(SUM(is_loser), 0), 0), 2) as puntos_perdidos_por_combate,
                    SUM(spin_wins) as spin_wins,
                    SUM(over_wins) as over_wins,
                    SUM(burst_wins) as burst_wins,
                    SUM(xtreme_wins) as xtreme_wins,
                    SUM(spin_losses) as spin_losses,
                    SUM(over_losses) as over_losses,
                    SUM(burst_losses) as burst_losses,
                    SUM(xtreme_losses) as xtreme_losses
                FROM (
                    SELECT 
                        c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                        (mb.winner_id = c.user_id) as is_winner,
                        (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                        CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_won,
                        CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_lost,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_losses,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_losses,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_losses,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_losses
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c ON mb.player1_combo_id = c.id
                    {$whereSql}
                    UNION ALL
                    SELECT 
                        c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                        (mb.winner_id = c.user_id) as is_winner,
                        (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                        CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_won,
                        CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_lost,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                        CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_losses,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_losses,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_losses,
                        CASE WHEN mb.winner_id != c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_losses
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c ON mb.player2_combo_id = c.id
                    {$whereSql}
                ) as combates_unificados
                GROUP BY blade, ratchet, bit, over_blade, assist_blade
                HAVING total_partidas >= ?
                ORDER BY {$sortCol} {$order}, total_partidas DESC, percentage_victories DESC
                LIMIT 100
            ", $unionBindings));

            $matchupsMap = $this->buildGlobalMatchupsMap($eventFilterSql, $eventBindings);

            foreach ($beybladeStats as &$combo) {
                $comboKey = $combo->blade . ' ' . $combo->ratchet . ' ' . $combo->bit;

                $best = [];
                if (!empty($matchupsMap[$comboKey]['wins'])) {
                    $wList = $matchupsMap[$comboKey]['wins'];
                    arsort($wList);
                    foreach (array_slice(array_filter($wList, fn($k) => $k !== $comboKey, ARRAY_FILTER_USE_KEY), 0, 5, true) as $en => $cnt) {
                        $best[] = ['combo' => $en, 'count' => $cnt];
                    }
                }
                $combo->presas = $best;

                $worst = [];
                if (!empty($matchupsMap[$comboKey]['losses'])) {
                    $lList = $matchupsMap[$comboKey]['losses'];
                    arsort($lList);
                    foreach (array_slice(array_filter($lList, fn($k) => $k !== $comboKey, ARRAY_FILTER_USE_KEY), 0, 5, true) as $en => $cnt) {
                        $worst[] = ['combo' => $en, 'count' => $cnt];
                    }
                }
                $combo->nemesis = $worst;

                $styles = [
                    'XTREME ASSASSIN' => (int) $combo->xtreme_wins,
                    'BURST ATTACKER'  => (int) $combo->burst_wins,
                    'OVER FINISHER'   => (int) $combo->over_wins,
                    'SPIN FINISHER'   => (int) $combo->spin_wins,
                ];
                arsort($styles);
                $topStyle = array_key_first($styles);
                $combo->estilo_combate = $styles[$topStyle] > 0 ? $topStyle : 'BALANCEADO';
            }
            unset($combo);

            $partStats = [
                'blades'        => $this->buildPartStatsForColumn('blade', $whereSql, $bindings, $minPartidas),
                'ratchets'      => $this->buildPartStatsForColumn('ratchet', $whereSql, $bindings, $minPartidas),
                'bits'          => $this->buildPartStatsForColumn('bit', $whereSql, $bindings, $minPartidas),
                'over_blades'   => $this->buildPartStatsForColumn('over_blade', $whereSql, $bindings, $minPartidas),
                'assist_blades' => $this->buildPartStatsForColumn('assist_blade', $whereSql, $bindings, $minPartidas),
            ];

            $globalFinishes = [
                'spin'   => $beybladeStats->sum('spin_wins'),
                'over'   => $beybladeStats->sum('over_wins'),
                'burst'  => $beybladeStats->sum('burst_wins'),
                'xtreme' => $beybladeStats->sum('xtreme_wins'),
            ];

            return [
                'beybladeStats'  => $beybladeStats,
                'partStats'      => $partStats,
                'globalFinishes' => $globalFinishes,
            ];
        });

        $topSemanal = $this->getWeeklyTopCombos();
        $torneosStatsList = $this->getRecentTournamentsList([
            'tipo_evento'  => $tipoEvento,
            'region_id'    => $regionId,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
        ]);

        return [
            'beybladeStats'    => $aggregated['beybladeStats'],
            'partStats'        => $aggregated['partStats'],
            'globalFinishes'   => $aggregated['globalFinishes'],
            'sort'             => $sort,
            'order'            => $order,
            'minPartidas'      => $minPartidas,
            'blades'           => $catalogs['blades'],
            'overBlades'       => $catalogs['overBlades'],
            'assistBlades'     => $catalogs['assistBlades'],
            'ratchets'         => $catalogs['ratchets'],
            'bits'             => $catalogs['bits'],
            'regions'          => $catalogs['regions'],
            'playersList'      => $catalogs['playersList'],
            'fechaInicio'      => $fechaInicio,
            'fechaFin'         => $fechaFin,
            'tipoEvento'       => $tipoEvento,
            'regionId'         => $regionId,
            'topSemanal'       => $topSemanal,
            'torneosStatsList' => $torneosStatsList,
        ];
    }

    /**
     * Evolución mensual de un combo (GET /beyblade-database/stats/combo-trend).
     *
     * @param  string|null  $blade
     * @param  string|null  $ratchet
     * @param  string|null  $bit
     * @return array{success: bool, timeline: array<int, object>}
     */
    public function getComboTrend(?string $blade, ?string $ratchet, ?string $bit): array
    {
        if (empty($blade)) {
            return ['success' => false, 'timeline' => []];
        }

        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $cacheKey = 'beylab:combo_trend:' . $seasonStart . ':' . md5(json_encode([$blade, $ratchet, $bit]));

        $rows = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($blade, $ratchet, $bit) {
            $condExtra = '';
            $bindings = [$blade];

            if (!empty($ratchet)) {
                $condExtra .= ' AND c.ratchet = ? ';
                $bindings[] = $ratchet;
            }
            if (!empty($bit)) {
                $condExtra .= ' AND c.bit = ? ';
                $bindings[] = $bit;
            }

            $allBindings = array_merge($bindings, $bindings);

            return DB::select("
                SELECT 
                    ym,
                    COUNT(*) as total_matches,
                    SUM(is_win) as wins,
                    ROUND((SUM(is_win) / GREATEST(COUNT(*), 1)) * 100, 1) as winrate
                FROM (
                    SELECT 
                        DATE_FORMAT(e.date, '%Y-%m') as ym,
                        (mb.winner_id = c.user_id) as is_win
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c ON mb.player1_combo_id = c.id
                    WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND c.blade = ? {$condExtra}
                    UNION ALL
                    SELECT 
                        DATE_FORMAT(e.date, '%Y-%m') as ym,
                        (mb.winner_id = c.user_id) as is_win
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c ON mb.player2_combo_id = c.id
                    WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                      AND c.blade = ? {$condExtra}
                ) as t_hist
                WHERE ym IS NOT NULL
                GROUP BY ym
                ORDER BY ym ASC
                LIMIT 12
            ", $allBindings);
        });

        return [
            'success'  => true,
            'timeline' => $rows,
        ];
    }

    /**
     * Cara a cara histórico entre dos jugadores (GET /beyblade-database/stats/player-h2h).
     *
     * @param  int  $p1Id
     * @param  int  $p2Id
     * @return array{status: int, data: array<string, mixed>}
     */
    public function getPlayerH2H(int $p1Id, int $p2Id): array
    {
        if (!$p1Id || !$p2Id || $p1Id === $p2Id) {
            return [
                'status' => 422,
                'data'   => ['success' => false, 'message' => 'Selecciona dos jugadores distintos.'],
            ];
        }

        $users = DB::table('users')
            ->whereIn('id', [$p1Id, $p2Id])
            ->get(['id', 'name'])
            ->keyBy('id');

        $u1 = $users->get($p1Id);
        $u2 = $users->get($p2Id);

        if (!$u1 || !$u2) {
            return [
                'status' => 404,
                'data'   => ['success' => false, 'message' => 'Jugador no encontrado.'],
            ];
        }

        $matches = TournamentMatch::with('event:id,name,date,city')
            ->where('status', 'completed')
            ->where(function ($q) use ($p1Id, $p2Id) {
                $q->where(function ($sq) use ($p1Id, $p2Id) {
                    $sq->where('player1_id', $p1Id)->where('player2_id', $p2Id);
                })->orWhere(function ($sq) use ($p1Id, $p2Id) {
                    $sq->where('player1_id', $p2Id)->where('player2_id', $p1Id);
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        $p1MatchWins  = 0;
        $p2MatchWins  = 0;
        $p1TotalPts   = 0;
        $p2TotalPts   = 0;
        $matchHistory = [];

        $matchIds = $matches->pluck('id')->all();
        $battlesByMatch = collect();
        $combosMap = collect();

        if (!empty($matchIds)) {
            $allBattles = DB::table('match_battles')
                ->whereIn('tournament_match_id', $matchIds)
                ->orderBy('id', 'asc')
                ->get();
            $battlesByMatch = $allBattles->groupBy('tournament_match_id');

            $comboIds = $allBattles->pluck('player1_combo_id')
                ->merge($allBattles->pluck('player2_combo_id'))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($comboIds)) {
                $combosMap = DB::table('tournament_results')
                    ->whereIn('id', $comboIds)
                    ->get()
                    ->keyBy('id');
            }
        }

        $p1BoutsWon = 0;
        $p2BoutsWon = 0;

        foreach ($matches as $m) {
            $p1IsSlot1 = ($m->player1_id == $p1Id);
            $s1 = $p1IsSlot1 ? (int) $m->player1_score : (int) $m->player2_score;
            $s2 = $p1IsSlot1 ? (int) $m->player2_score : (int) $m->player1_score;

            $p1TotalPts += $s1;
            $p2TotalPts += $s2;

            if ($m->winner_id == $p1Id) {
                $p1MatchWins++;
            } elseif ($m->winner_id == $p2Id) {
                $p2MatchWins++;
            }

            $rawBouts = $battlesByMatch->get($m->id, collect());
            $timeline = [];
            $runP1 = 0;
            $runP2 = 0;
            $step = 0;

            foreach ($rawBouts as $b) {
                if (in_array($b->finish_type, ['warning_p1', 'warning_p2'], true)) {
                    continue;
                }
                $step++;
                $bWinnerIsP1 = ($b->winner_id == $p1Id);
                if ($bWinnerIsP1) {
                    $runP1 += (int) $b->points_awarded;
                    $p1BoutsWon++;
                } else {
                    $runP2 += (int) $b->points_awarded;
                    $p2BoutsWon++;
                }

                $cSlot1 = $combosMap->get($b->player1_combo_id);
                $cSlot2 = $combosMap->get($b->player2_combo_id);
                $c1Obj  = $p1IsSlot1 ? $cSlot1 : $cSlot2;
                $c2Obj  = $p1IsSlot1 ? $cSlot2 : $cSlot1;

                $timeline[] = [
                    'bout_num'    => $step,
                    'p1_combo'    => $c1Obj ? trim("{$c1Obj->blade} {$c1Obj->ratchet} {$c1Obj->bit}") : 'Bey P1',
                    'p2_combo'    => $c2Obj ? trim("{$c2Obj->blade} {$c2Obj->ratchet} {$c2Obj->bit}") : 'Bey P2',
                    'winner_name' => $bWinnerIsP1 ? $u1->name : $u2->name,
                    'winner_p1'   => $bWinnerIsP1,
                    'finish'      => strtoupper($b->finish_type ?? 'PT'),
                    'points'      => (int) $b->points_awarded,
                    'running'     => "{$runP1} - {$runP2}",
                ];
            }

            $matchHistory[] = [
                'match_id'   => $m->id,
                'event_name' => $m->event->name ?? 'Torneo SBBL',
                'date'       => $m->event && $m->event->date ? Carbon::parse($m->event->date)->format('d/m/Y') : '---',
                'round'      => 'R' . $m->round_number . ' · ' . ($m->bracket_type === 'swiss' ? 'SUIZO' : strtoupper(str_replace('_', ' ', $m->bracket_type))),
                'p1_score'   => $s1,
                'p2_score'   => $s2,
                'p1_won'     => ($m->winner_id == $p1Id),
                'timeline'   => $timeline,
            ];
        }

        // Consolidación: 1 sola consulta agrupada para el Top 3 combos de ambos jugadores usando PK joins
        $topCombosByUser = $this->fetchTopCombosForPlayers([$p1Id, $p2Id]);

        return [
            'status' => 200,
            'data'   => [
                'success'       => true,
                'p1'            => [
                    'id'         => $u1->id,
                    'name'       => $u1->name,
                    'match_wins' => $p1MatchWins,
                    'total_pts'  => $p1TotalPts,
                    'bouts_won'  => $p1BoutsWon,
                    'top_combos' => $topCombosByUser[$p1Id] ?? [],
                ],
                'p2'            => [
                    'id'         => $u2->id,
                    'name'       => $u2->name,
                    'match_wins' => $p2MatchWins,
                    'total_pts'  => $p2TotalPts,
                    'bouts_won'  => $p2BoutsWon,
                    'top_combos' => $topCombosByUser[$p2Id] ?? [],
                ],
                'total_matches' => count($matchHistory),
                'matches'       => $matchHistory,
            ],
        ];
    }

    /**
     * Telemetría detallada de un torneo (GET /beyblade-database/stats/tournament/{eventId}).
     *
     * @param  int|string  $eventId
     * @return array<string, mixed>
     */
    public function getTournamentStatsDetail($eventId): array
    {
        $event = Event::with('region')->findOrFail($eventId);

        $standingsService = app(StandingsService::class);
        $standingsData    = $standingsService->getStandings($event->id);
        $clasificacion    = $standingsData['clasificacion'] ?? [];

        $battleStats = DB::table('match_battles as mb')
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

        $topCombosTorneo = DB::select("
            SELECT 
                TRIM(CONCAT(
                    c.blade,
                    CASE WHEN c.over_blade IS NOT NULL AND c.over_blade != '' AND c.over_blade != '-' THEN CONCAT(' ', c.over_blade) ELSE '' END,
                    CASE WHEN c.assist_blade IS NOT NULL AND c.assist_blade != '' AND c.assist_blade != '-' THEN CONCAT(' ', c.assist_blade) ELSE '' END,
                    ' · ', IFNULL(c.ratchet, ''), ' · ', IFNULL(c.bit, '')
                )) as combo_name,
                u.name as owner_name,
                COUNT(*) as total_bouts,
                SUM(is_win) as wins,
                SUM(pts_won) as pts_generated
            FROM (
                SELECT
                    mb.player1_combo_id as combo_id,
                    (mb.winner_id = tm.player1_id) as is_win,
                    CASE WHEN mb.winner_id = tm.player1_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                WHERE tm.event_id = ?
                  AND mb.winner_id IS NOT NULL
                  AND mb.player1_combo_id IS NOT NULL
                  AND mb.finish_type IN ('spin', 'over', 'burst', 'xtreme')
                UNION ALL
                SELECT
                    mb.player2_combo_id as combo_id,
                    (mb.winner_id = tm.player2_id) as is_win,
                    CASE WHEN mb.winner_id = tm.player2_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                WHERE tm.event_id = ?
                  AND mb.winner_id IS NOT NULL
                  AND mb.player2_combo_id IS NOT NULL
                  AND mb.finish_type IN ('spin', 'over', 'burst', 'xtreme')
            ) as b_uni
            JOIN tournament_results c ON c.id = b_uni.combo_id
            JOIN users u ON c.user_id = u.id
            GROUP BY c.id, combo_name, u.name
            HAVING wins > 0
            ORDER BY wins DESC, pts_generated DESC
            LIMIT 5
        ", [$event->id, $event->id]);

        $allMatches = TournamentMatch::with(['player1:id,name', 'player2:id,name'])
            ->where('event_id', $event->id)
            ->where('status', 'completed')
            ->orderBy('round_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $allDecksRaw = TournamentResult::where('event_id', $event->id)
            ->whereNotNull('blade')
            ->where('blade', 'NOT LIKE', '%Selecciona%')
            ->orderBy('id', 'asc')
            ->get();

        $combosById = $allDecksRaw->keyBy('id');
        $allDecks   = $allDecksRaw->groupBy('user_id');

        $matchIds = $allMatches->pluck('id')->all();
        $battlesByMatch = collect();
        if (!empty($matchIds)) {
            $battlesByMatch = DB::table('match_battles')
                ->whereIn('tournament_match_id', $matchIds)
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('tournament_match_id');
        }

        $comboBattleStatsRaw = DB::select("
            SELECT 
                combo_id,
                COUNT(*) as total,
                SUM(is_win) as wins,
                SUM(pts_won) as pts_for,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtremes,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as bursts,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'over' THEN 1 ELSE 0 END) as overs,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'spin' THEN 1 ELSE 0 END) as spins
            FROM (
                SELECT 
                    mb.player1_combo_id as combo_id,
                    (mb.winner_id = tm.player1_id) as is_win,
                    CASE WHEN mb.winner_id = tm.player1_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                    mb.finish_type
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                WHERE tm.event_id = ? AND mb.winner_id IS NOT NULL AND mb.player1_combo_id IS NOT NULL
                  AND mb.finish_type IN ('spin','over','burst','xtreme')
                UNION ALL
                SELECT 
                    mb.player2_combo_id as combo_id,
                    (mb.winner_id = tm.player2_id) as is_win,
                    CASE WHEN mb.winner_id = tm.player2_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                    mb.finish_type
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                WHERE tm.event_id = ? AND mb.winner_id IS NOT NULL AND mb.player2_combo_id IS NOT NULL
                  AND mb.finish_type IN ('spin','over','burst','xtreme')
            ) as c_bouts
            GROUP BY combo_id
        ", [$event->id, $event->id]);

        $comboStatsMap = collect($comboBattleStatsRaw)->keyBy('combo_id');

        $playersTelemetry = [];
        foreach ($clasificacion as $pl) {
            $uid        = $pl['id'];
            $userCombos = $allDecks->get($uid, collect());

            $deckFormatted = [];
            foreach ($userCombos as $c) {
                $st  = $comboStatsMap->get($c->id);
                $tot = (int) ($st->total ?? 0);
                $w   = (int) ($st->wins ?? 0);
                $extras   = array_filter([$c->over_blade, $c->assist_blade], fn($v) => !empty($v) && $v !== '-');
                $extraStr = !empty($extras) ? ' ' . implode(' ', $extras) : '';

                $deckFormatted[] = [
                    'combo'        => trim("{$c->blade}{$extraStr} · {$c->ratchet} · {$c->bit}"),
                    'blade'        => $c->blade,
                    'over_blade'   => ($c->over_blade && $c->over_blade !== '-') ? $c->over_blade : '',
                    'assist_blade' => ($c->assist_blade && $c->assist_blade !== '-') ? $c->assist_blade : '',
                    'ratchet'      => $c->ratchet,
                    'bit'          => $c->bit,
                    'wins'         => $w,
                    'losses'       => max(0, $tot - $w),
                    'total'        => $tot,
                    'winrate'      => $tot > 0 ? round(($w / $tot) * 100, 1) : 0,
                    'pts_for'      => (int) ($st->pts_for ?? 0),
                    'xtremes'      => (int) ($st->xtremes ?? 0),
                    'bursts'       => (int) ($st->bursts ?? 0),
                    'overs'        => (int) ($st->overs ?? 0),
                    'spins'        => (int) ($st->spins ?? 0),
                ];
            }

            $userMatches   = $allMatches->filter(fn($m) => $m->player1_id == $uid || $m->player2_id == $uid);
            $duelosJugador = [];

            foreach ($userMatches as $m) {
                $isBye = is_null($m->player2_id) || $m->player1_score == 99 || $m->player2_score == 99;
                if ($isBye) {
                    $duelosJugador[] = [
                        'match_id'  => $m->id,
                        'round'     => 'R' . $m->round_number,
                        'phase'     => $m->bracket_type === 'swiss' ? 'SUIZO' : strtoupper($m->bracket_type),
                        'rival'     => 'BYE (PASE DIRECTO)',
                        'my_score'  => 4,
                        'riv_score' => 0,
                        'is_win'    => true,
                        'timeline'  => [],
                    ];
                    continue;
                }

                $amP1 = ($m->player1_id == $uid);
                $rawBouts = $battlesByMatch->get($m->id, collect());
                $timeline = [];
                $myRun    = 0;
                $rivRun   = 0;
                $boutStep = 0;

                foreach ($rawBouts as $b) {
                    if (in_array($b->finish_type, ['warning_p1', 'warning_p2'], true)) {
                        continue;
                    }
                    $boutStep++;
                    $iWonBout = ($b->winner_id == $uid);
                    if ($iWonBout) {
                        $myRun += (int) $b->points_awarded;
                    } else {
                        $rivRun += (int) $b->points_awarded;
                    }

                    $c1 = $combosById->get($b->player1_combo_id);
                    $c2 = $combosById->get($b->player2_combo_id);
                    $myComboObj  = $amP1 ? $c1 : $c2;
                    $rivComboObj = $amP1 ? $c2 : $c1;

                    $formatComboLabel = static function ($comboObj, string $fallback): string {
                        if (!$comboObj) {
                            return $fallback;
                        }
                        $extras = array_filter([$comboObj->over_blade, $comboObj->assist_blade], fn($v) => !empty($v) && $v !== '-');
                        $extraStr = !empty($extras) ? ' ' . implode(' ', $extras) : '';
                        return trim("{$comboObj->blade}{$extraStr} · {$comboObj->ratchet} · {$comboObj->bit}");
                    };

                    $timeline[] = [
                        'bout_num'  => $boutStep,
                        'my_combo'  => $formatComboLabel($myComboObj, 'Tu Bey'),
                        'riv_combo' => $formatComboLabel($rivComboObj, 'Bey Rival'),
                        'i_won'     => $iWonBout,
                        'finish'    => strtoupper($b->finish_type ?? 'PT'),
                        'points'    => (int) $b->points_awarded,
                        'running'   => "{$myRun} - {$rivRun}",
                    ];
                }

                $duelosJugador[] = [
                    'match_id'  => $m->id,
                    'round'     => 'R' . $m->round_number,
                    'phase'     => $m->bracket_type === 'swiss' ? 'SUIZO' : strtoupper(str_replace('_', ' ', $m->bracket_type)),
                    'rival'     => $amP1 ? ($m->player2->name ?? 'Rival') : ($m->player1->name ?? 'Rival'),
                    'my_score'  => $amP1 ? $m->player1_score : $m->player2_score,
                    'riv_score' => $amP1 ? $m->player2_score : $m->player1_score,
                    'is_win'    => ($m->winner_id == $uid),
                    'timeline'  => $timeline,
                ];
            }

            $playersTelemetry[$uid] = [
                'id'            => $uid,
                'name'          => $pl['name'],
                'shared_rank'   => $pl['shared_rank'] ?? 1,
                'puesto'        => strtoupper($pl['puesto_oficial'] ?? $pl['puesto'] ?? 'PART.'),
                'bracket_stage' => $pl['bracket_stage'] ?? '',
                'wins'        => $pl['wins'] ?? 0,
                'losses'      => $pl['losses'] ?? 0,
                'points'      => $pl['points'] ?? 0,
                'buchholz'    => $pl['buchholz'] ?? 0,
                'point_diff'  => $pl['point_diff'] ?? 0,
                'deck'        => $deckFormatted,
                'matches'     => $duelosJugador,
            ];
        }

        return [
            'success'      => true,
            'event'        => [
                'id'          => $event->id,
                'name'        => $event->name,
                'date'        => Carbon::parse($event->date)->format('d/m/Y'),
                'city'        => $event->city,
                'region'      => $event->region->name ?? 'España',
                'type'        => strtoupper($event->beys ?? 'EVENTO'),
                'bracket_url' => route('torneo.bracket', $event->id),
            ],
            'battle_stats' => [
                'total_bouts' => (int) ($battleStats->total_bouts ?? 0),
                'xtremes'     => (int) ($battleStats->xtremes ?? 0),
                'bursts'      => (int) ($battleStats->bursts ?? 0),
                'overs'       => (int) ($battleStats->overs ?? 0),
                'spins'       => (int) ($battleStats->spins ?? 0),
            ],
            'top_combos'   => $topCombosTorneo,
            'standings'    => array_values($playersTelemetry),
        ];
    }

    // =========================================================================
    // MÉTODOS INTERNOS COMPARTIDOS (TOP META, STATS CONSOLIDADAS Y H2H)
    // =========================================================================

    /**
     * Obtiene el Top 3 Meta Boss legal bajo reglas 3on3 (con caché de 10 min).
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $limit
     * @return array<int, object>
     */
    private function getTopMetaBoss(array $filters = [], int $limit = 20): array
    {
        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $fechaCorte = Carbon::now()->subDays(60)->toDateString();

        $cacheKey = 'beylab:top_meta_boss:' . $seasonStart . ':' . md5(json_encode([
            'fecha_corte'  => $fechaCorte,
            'limit'        => $limit,
            'tipo_evento'  => $filters['tipo_evento'] ?? '',
            'fecha_inicio' => $filters['fecha_inicio'] ?? '',
            'fecha_fin'    => $filters['fecha_fin'] ?? '',
        ]));

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($filters, $fechaCorte, $limit) {
            [$eventFilterSql, $eventBindings] = $this->buildEventFilterSqlAndBindings($filters);
            $bindings = array_merge([$fechaCorte], $eventBindings, [$limit]);

            $topMetaRaw = DB::select("
                SELECT
                    tr.blade, tr.ratchet, tr.bit,
                    CASE WHEN tr.over_blade IS NULL OR tr.over_blade = '' OR tr.over_blade = '-' OR tr.over_blade LIKE '%Selecciona%' THEN '-' ELSE tr.over_blade END as over_blade,
                    CASE WHEN tr.assist_blade IS NULL OR tr.assist_blade = '' OR tr.assist_blade = '-' OR tr.assist_blade LIKE '%Selecciona%' THEN '-' ELSE tr.assist_blade END as assist_blade,
                    COUNT(*) as usos
                FROM tournament_results tr
                JOIN events e ON tr.event_id = e.id
                WHERE tr.blade IS NOT NULL
                  AND tr.blade NOT LIKE '%Selecciona%'
                  AND tr.blade != 'DESCONOCIDO'
                  AND e.date >= ? {$eventFilterSql}
                GROUP BY tr.blade, tr.ratchet, tr.bit, over_blade, assist_blade
                ORDER BY usos DESC
                LIMIT ?
            ", $bindings);

            $topMetaBoss = [];
            $usedBlades = [];
            $usedRatchets = [];
            $usedBits = [];
            $usedOvers = [];
            $usedAssists = [];

            foreach ($topMetaRaw as $boss) {
                if (count($topMetaBoss) >= 3) {
                    break;
                }

                if (in_array($boss->blade, $usedBlades, true)) {
                    continue;
                }
                if ($boss->ratchet !== 'UX Expanded' && in_array($boss->ratchet, $usedRatchets, true)) {
                    continue;
                }
                if ($boss->bit !== 'Without Bit' && in_array($boss->bit, $usedBits, true)) {
                    continue;
                }
                if ($boss->over_blade !== '-' && in_array($boss->over_blade, $usedOvers, true)) {
                    continue;
                }
                if ($boss->assist_blade !== '-' && in_array($boss->assist_blade, $usedAssists, true)) {
                    continue;
                }

                $topMetaBoss[] = $boss;
                $usedBlades[] = $boss->blade;
                if ($boss->ratchet !== 'UX Expanded') {
                    $usedRatchets[] = $boss->ratchet;
                }
                if ($boss->bit !== 'Without Bit') {
                    $usedBits[] = $boss->bit;
                }
                if ($boss->over_blade !== '-') {
                    $usedOvers[] = $boss->over_blade;
                }
                if ($boss->assist_blade !== '-') {
                    $usedAssists[] = $boss->assist_blade;
                }
            }

            return $topMetaBoss;
        });
    }

    /**
     * Construye condiciones SQL parametrizadas sobre la tabla `events e`.
     *
     * @param  array<string, mixed>  $filters
     * @return array{0: string, 1: array<int, mixed>}
     */
    private function buildEventFilterSqlAndBindings(array $filters): array
    {
        $sql = '';
        $bindings = [];

        $tipoEvento  = $filters['tipo_evento'] ?? null;
        $fechaInicio = $filters['fecha_inicio'] ?? null;
        $fechaFin    = $filters['fecha_fin'] ?? null;
        $regionId    = $filters['region_id'] ?? null;

        if (!empty($tipoEvento)) {
            if ($tipoEvento === 'ranking') {
                $sql .= " AND e.beys IN ('ranking', 'rankingplus') ";
            } elseif ($tipoEvento === 'crew_battle') {
                $sql .= " AND e.beys IN ('crew_battle', 'equipos') ";
            } else {
                $sql .= ' AND e.beys = ? ';
                $bindings[] = (string) $tipoEvento;
            }
        }
        if (!empty($fechaInicio)) {
            $sql .= ' AND e.date >= ? ';
            $bindings[] = (string) $fechaInicio;
        }
        if (!empty($fechaFin)) {
            $sql .= ' AND e.date <= ? ';
            $bindings[] = (string) $fechaFin;
        }
        if (!empty($regionId)) {
            $sql .= ' AND e.region_id = ? ';
            $bindings[] = (int) $regionId;
        }

        return [$sql, $bindings];
    }

    /**
     * Ejecuta una única consulta agrupada por `(blade, ratchet, bit, over_blade, assist_blade)`
     * para todas las Blades de ambos decks, permitiendo resolver en memoria tanto el Combo Exacto
     * como el fallback Por Blade sin repetir consultas por slot.
     *
     * @param  array<int, string>  $blades
     * @param  int|null  $filterUserId
     * @param  string  $eventFilterSql
     * @param  array<int, mixed>  $eventBindings
     * @return Collection<int, object>
     */
    private function fetchGroupedStatsByBlades(
        array $blades,
        ?int $filterUserId,
        string $eventFilterSql,
        array $eventBindings
    ): Collection {
        if (empty($blades)) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, count($blades), '?'));
        $branchSql = " AND c.blade IN ({$placeholders}) ";
        $branchBindings = $blades;

        if ($filterUserId !== null) {
            $branchSql .= ' AND c.user_id = ? ';
            $branchBindings[] = $filterUserId;
        }

        $joinEvents = $eventFilterSql !== ''
            ? 'JOIN tournament_matches tm ON mb.tournament_match_id = tm.id JOIN events e ON tm.event_id = e.id'
            : '';

        if ($eventFilterSql !== '') {
            $branchSql .= $eventFilterSql;
            $branchBindings = array_merge($branchBindings, $eventBindings);
        }

        $allBindings = array_merge($branchBindings, $branchBindings);

        $rows = DB::select("
            SELECT
                blade,
                ratchet,
                bit,
                IFNULL(over_blade, '') as over_blade,
                IFNULL(assist_blade, '') as assist_blade,
                COUNT(*) as total,
                SUM(is_win) as wins,
                SUM(is_loss) as losses,
                SUM(pts_won) as sum_pts_won,
                SUM(pts_lost) as sum_pts_lost,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtreme_w,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as burst_w,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'over' THEN 1 ELSE 0 END) as over_w,
                SUM(CASE WHEN is_win = 1 AND finish_type = 'spin' THEN 1 ELSE 0 END) as spin_w,
                SUM(CASE WHEN is_loss = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as burst_suffered
            FROM (
                SELECT
                    c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                    (mb.winner_id = c.user_id) as is_win,
                    (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                    CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                    CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                    mb.finish_type
                FROM match_battles mb
                {$joinEvents}
                JOIN tournament_results c ON mb.player1_combo_id = c.id
                WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                  {$branchSql}
                UNION ALL
                SELECT
                    c.blade, c.ratchet, c.bit, c.over_blade, c.assist_blade,
                    (mb.winner_id = c.user_id) as is_win,
                    (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                    CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                    CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                    mb.finish_type
                FROM match_battles mb
                {$joinEvents}
                JOIN tournament_results c ON mb.player2_combo_id = c.id
                WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                  {$branchSql}
            ) as hist
            GROUP BY blade, ratchet, bit, over_blade, assist_blade
        ", $allBindings);

        return collect($rows);
    }

    /**
     * Calcula las métricas de un combo específico (exacto o fallback por Blade) a partir
     * de las filas agrupadas en memoria.
     *
     * @param  array<string, mixed>  $b
     * @param  Collection<int, object>  $groupedRows
     * @param  string  $context  'hub' | 'web'
     * @param  int|null  $filterUserId
     * @return array<string, mixed>|null
     */
    private function computeComboStatFromGrouped(
        array $b,
        Collection $groupedRows,
        string $context,
        ?int $filterUserId = null
    ): ?array {
        $blade   = (string) ($b['blade'] ?? '');
        $ratchet = (string) ($b['ratchet'] ?? '');
        $bit     = (string) ($b['bit'] ?? '');
        $over    = (!empty($b['over_blade']) && $b['over_blade'] !== '-') ? (string) $b['over_blade'] : null;
        $assist  = (!empty($b['assist_blade']) && $b['assist_blade'] !== '-') ? (string) $b['assist_blade'] : null;

        if ($blade === '') {
            return null;
        }

        $bladeRows = $groupedRows->filter(fn($r) => (string) $r->blade === $blade);

        $exactRows = $bladeRows->filter(function ($r) use ($ratchet, $bit, $over, $assist) {
            if ((string) $r->ratchet !== $ratchet || (string) $r->bit !== $bit) {
                return false;
            }
            if ($over !== null && (string) $r->over_blade !== $over) {
                return false;
            }
            if ($assist !== null && (string) $r->assist_blade !== $assist) {
                return false;
            }
            return true;
        });

        $aggregateCollection = function (Collection $rows): object {
            $total         = (int) $rows->sum('total');
            $wins          = (int) $rows->sum('wins');
            $losses        = (int) $rows->sum('losses');
            $sumPtsWon     = (float) $rows->sum('sum_pts_won');
            $sumPtsLost    = (float) $rows->sum('sum_pts_lost');
            $xtremeW       = (int) $rows->sum('xtreme_w');
            $burstW        = (int) $rows->sum('burst_w');
            $overW         = (int) $rows->sum('over_w');
            $spinW         = (int) $rows->sum('spin_w');
            $burstSuffered = (int) $rows->sum('burst_suffered');

            return (object) [
                'total'          => $total,
                'wins'           => $wins,
                'losses'         => $losses,
                'avg_dmg_dealt'  => $wins > 0 ? round($sumPtsWon / $wins, 2) : 0.0,
                'avg_dmg_taken'  => $losses > 0 ? round($sumPtsLost / $losses, 2) : 0.0,
                'xtreme_w'       => $xtremeW,
                'burst_w'        => $burstW,
                'over_w'         => $overW,
                'spin_w'         => $spinW,
                'burst_suffered' => $burstSuffered,
            ];
        };

        $exact = $aggregateCollection($exactRows);
        $fallback = $aggregateCollection($bladeRows);

        if ($context === 'hub') {
            if ($exact->total < 3) {
                $data = $fallback;
                $precision = 'Por Blade';
            } else {
                $data = $exact;
                $precision = 'Combo Exacto';
            }
        } else {
            $minThreshold = $filterUserId ? 1 : 3;
            if ($exact->total < $minThreshold) {
                if ($fallback->total > $exact->total) {
                    $data = $fallback;
                    $precision = $filterUserId ? 'Tus datos (Por Blade)' : 'Global (Por Blade)';
                } else {
                    $data = $exact;
                    $precision = $filterUserId ? 'Tus datos (Combo Exacto)' : 'Global (Combo Exacto)';
                }
            } else {
                $data = $exact;
                $precision = $filterUserId ? 'Tus datos (Combo Exacto)' : 'Global (Combo Exacto)';
            }
        }

        $totalMatches = (int) $data->total;
        $wins         = (int) $data->wins;
        $losses       = (int) $data->losses;
        $wr           = $totalMatches > 0 ? round(($wins / max(1, $totalMatches)) * 100, 1) : 50.0;

        $extras = array_filter([$over ?? '', $assist ?? ''], fn($v) => $v !== '' && $v !== '-');
        if ($context === 'hub') {
            $extraStr = !empty($extras) ? ' [' . implode('/', $extras) . ']' : '';
            $comboName = trim($blade . $extraStr . ' ' . $ratchet . ' ' . $bit);
            $partsLabel = trim(($over ? "{$over} " : '') . ($assist ? "{$assist} " : '') . "{$ratchet} {$bit}");
        } else {
            $extraStr = !empty($extras) ? ' ' . implode(' ', $extras) : '';
            $comboName = trim($blade . $extraStr . ' ' . $ratchet . ' ' . $bit);
            $partsLabel = trim(($over ? "{$over} · " : '') . ($assist ? "{$assist} · " : '') . "{$ratchet} · {$bit}");
        }

        $result = [
            'combo'         => $comboName,
            'short_name'    => $blade,
            'parts_label'   => $partsLabel,
            'blade'         => $blade,
            'over_blade'    => $over ?? '',
            'assist_blade'  => $assist ?? '',
            'ratchet'       => $ratchet,
            'bit'           => $bit,
            'sample_size'   => $totalMatches,
            'wins'          => $wins,
            'losses'        => $losses,
            'precision'     => $precision,
            'winrate'       => $wr,
            'avg_dmg_dealt' => (float) $data->avg_dmg_dealt,
            'avg_dmg_taken' => (float) $data->avg_dmg_taken,
            'xtreme_pct'    => $wins > 0 ? round(($data->xtreme_w / $wins) * 100) : 0,
            'burst_pct'     => $wins > 0 ? round(($data->burst_w / $wins) * 100) : 0,
            'over_pct'      => $wins > 0 ? round(($data->over_w / $wins) * 100) : 0,
            'spin_pct'      => $wins > 0 ? round(($data->spin_w / $wins) * 100) : 0,
        ];

        if ($context === 'web') {
            $result['burst_risk'] = $totalMatches > 0
                ? round(($data->burst_suffered / $totalMatches) * 100, 1)
                : 10.0;
        }

        return $result;
    }

    /**
     * Ejecuta una única consulta agrupada para todos los enfrentamientos directos (H2H)
     * entre las Blades del jugador y las del rival, usando los índices compuestos de `tournament_results`
     * y `match_battles`.
     *
     * @param  array<int, string>  $myBlades
     * @param  array<int, string>  $rivalBlades
     * @param  string  $eventFilterSql
     * @param  array<int, mixed>  $eventBindings
     * @return Collection<int, object>
     */
    private function fetchGroupedH2HRows(
        array $myBlades,
        array $rivalBlades,
        string $eventFilterSql,
        array $eventBindings
    ): Collection {
        if (empty($myBlades) || empty($rivalBlades)) {
            return collect();
        }

        $myPlaceholders  = implode(',', array_fill(0, count($myBlades), '?'));
        $rivPlaceholders = implode(',', array_fill(0, count($rivalBlades), '?'));

        $joinEvents = $eventFilterSql !== ''
            ? 'JOIN tournament_matches tm ON mb.tournament_match_id = tm.id JOIN events e ON tm.event_id = e.id'
            : '';

        $bindings = array_merge($myBlades, $rivalBlades, $myBlades, $rivalBlades, $eventBindings);

        $rows = DB::select("
            SELECT
                c1.blade as b1,
                c1.ratchet as r1,
                c1.bit as bt1,
                c2.blade as b2,
                c2.ratchet as r2,
                c2.bit as bt2,
                COUNT(*) as total,
                SUM(CASE WHEN mb.winner_id = c1.user_id THEN 1 ELSE 0 END) as c1_wins,
                SUM(CASE WHEN mb.winner_id = c2.user_id THEN 1 ELSE 0 END) as c2_wins
            FROM match_battles mb
            {$joinEvents}
            JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
            JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
            WHERE mb.winner_id IS NOT NULL
              AND (
                  (c1.blade IN ({$myPlaceholders}) AND c2.blade IN ({$rivPlaceholders}))
                  OR (c2.blade IN ({$myPlaceholders}) AND c1.blade IN ({$rivPlaceholders}))
              )
              {$eventFilterSql}
            GROUP BY c1.blade, c1.ratchet, c1.bit, c2.blade, c2.ratchet, c2.bit
        ", $bindings);

        return collect($rows);
    }

    /**
     * Resuelve el cruce H2H (Exacto -> Por Blade -> Estimado) entre dos combos desde las filas agrupadas.
     *
     * @param  array<string, mixed>  $cA
     * @param  array<string, mixed>  $cB
     * @param  Collection<int, object>  $h2hRows
     * @param  string  $context  'hub' | 'web'
     * @param  string  $mode
     * @return array{winrate: float, matches: int, source: string}
     */
    private function resolveHeadToHeadFromRows(
        array $cA,
        array $cB,
        Collection $h2hRows,
        string $context,
        string $mode
    ): array {
        $personalBonus = ($context === 'web' && $mode === 'personal_vs_global' && !empty($cA['using_personal']))
            ? (($cA['delta_vs_global'] ?? 0) * 0.35)
            : 0.0;

        $exactTotal = 0;
        $exactWins  = 0;
        $bladeTotal = 0;
        $bladeWins  = 0;

        $aBlade   = (string) ($cA['blade'] ?? '');
        $aRatchet = (string) ($cA['ratchet'] ?? '');
        $aBit     = (string) ($cA['bit'] ?? '');

        $bBlade   = (string) ($cB['blade'] ?? '');
        $bRatchet = (string) ($cB['ratchet'] ?? '');
        $bBit     = (string) ($cB['bit'] ?? '');

        foreach ($h2hRows as $row) {
            // Dirección 1: c1 es A y c2 es B
            if ((string) $row->b1 === $aBlade && (string) $row->b2 === $bBlade) {
                $bladeTotal += (int) $row->total;
                $bladeWins  += (int) $row->c1_wins;

                if (
                    (string) $row->r1 === $aRatchet && (string) $row->bt1 === $aBit
                    && (string) $row->r2 === $bRatchet && (string) $row->bt2 === $bBit
                ) {
                    $exactTotal += (int) $row->total;
                    $exactWins  += (int) $row->c1_wins;
                }
            }

            // Dirección 2: c2 es A y c1 es B
            if ((string) $row->b2 === $aBlade && (string) $row->b1 === $bBlade) {
                $bladeTotal += (int) $row->total;
                $bladeWins  += (int) $row->c2_wins;

                if (
                    (string) $row->r2 === $aRatchet && (string) $row->bt2 === $aBit
                    && (string) $row->r1 === $bRatchet && (string) $row->bt1 === $bBit
                ) {
                    $exactTotal += (int) $row->total;
                    $exactWins  += (int) $row->c2_wins;
                }
            }
        }

        if ($exactTotal >= 3) {
            $baseWr = ($exactWins / $exactTotal) * 100;
            if ($context === 'web') {
                $finalWr = round(max(5, min(95, $baseWr + $personalBonus)), 1);
                return [
                    'winrate' => $finalWr,
                    'matches' => $exactTotal,
                    'source'  => 'Exacto (' . $exactTotal . ')',
                ];
            }

            return [
                'winrate' => round($baseWr, 1),
                'matches' => $exactTotal,
                'source'  => 'Exacto',
            ];
        }

        if ($bladeTotal >= 3) {
            $bladeWr = ($bladeWins / $bladeTotal) * 100;
            $adjustedWr = round(($bladeWr * 0.75) + ((50 + (($cA['winrate'] - $cB['winrate']) * 0.5)) * 0.25) + $personalBonus, 1);

            return [
                'winrate' => (float) max(5, min(95, $adjustedWr)),
                'matches' => $bladeTotal,
                'source'  => $context === 'web' ? 'Por Blade (' . $bladeTotal . ')' : 'Blade',
            ];
        }

        $est = round(50 + (($cA['winrate'] - $cB['winrate']) * 0.8), 1);

        return [
            'winrate' => (float) max(15, min(85, $est)),
            'matches' => 0,
            'source'  => 'Estimado',
        ];
    }

    /**
     * Construye el mapa global de enfrentamientos directos (Presas y Némesis) usando
     * JOINs directos por clave primaria sobre `tournament_results` y caché de 10 minutos.
     *
     * @param  string  $eventFilterSql
     * @param  array<int, mixed>  $eventBindings
     * @return array<string, array{wins?: array<string, int>, losses?: array<string, int>}>
     */
    private function buildGlobalMatchupsMap(string $eventFilterSql = '', array $eventBindings = []): array
    {
        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $cacheKey = 'beylab:matchups_map:' . $seasonStart . ':' . md5(json_encode([$eventFilterSql, $eventBindings]));

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($eventFilterSql, $eventBindings) {
            $joinEvents = $eventFilterSql !== ''
                ? 'JOIN events e ON tm.event_id = e.id'
                : '';

            $matchupsRaw = DB::select("
                SELECT
                    CASE
                        WHEN mb.winner_id = tm.player1_id THEN CONCAT(c1.blade, ' ', c1.ratchet, ' ', c1.bit)
                        ELSE CONCAT(c2.blade, ' ', c2.ratchet, ' ', c2.bit)
                    END as winner_combo,
                    CASE
                        WHEN mb.winner_id = tm.player1_id THEN CONCAT(c2.blade, ' ', c2.ratchet, ' ', c2.bit)
                        ELSE CONCAT(c1.blade, ' ', c1.ratchet, ' ', c1.bit)
                    END as loser_combo,
                    COUNT(*) as times_won
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                {$joinEvents}
                JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                WHERE mb.winner_id IS NOT NULL
                  AND mb.player1_combo_id IS NOT NULL
                  AND mb.player2_combo_id IS NOT NULL
                  {$eventFilterSql}
                GROUP BY winner_combo, loser_combo
            ", $eventBindings);

            $matchupsMap = [];
            foreach ($matchupsRaw as $m) {
                $matchupsMap[$m->winner_combo]['wins'][$m->loser_combo]   = (int) $m->times_won;
                $matchupsMap[$m->loser_combo]['losses'][$m->winner_combo] = (int) $m->times_won;
            }

            return $matchupsMap;
        });
    }

    /**
     * Calcula estadísticas y Tier List (7 niveles: SS..D) para una categoría de pieza validada.
     *
     * @param  string  $colName
     * @param  string  $whereSql
     * @param  array<int, mixed>  $bindings
     * @param  int  $minPartidas
     * @return array<int, object>
     */
    private function buildPartStatsForColumn(
        string $colName,
        string $whereSql,
        array $bindings,
        int $minPartidas
    ): array {
        if (!in_array($colName, self::ALLOWED_PART_COLUMNS, true)) {
            return [];
        }

        $partBindings = array_merge($bindings, $bindings, [max(1, $minPartidas)]);
        $metaScoreSql = $this->metaScoreSql();
        $rows = DB::select("
            SELECT 
                part_name,
                COUNT(*) as total_partidas,
                SUM(is_winner) as total_victorias,
                SUM(is_loser) as total_derrotas,
                ROUND((SUM(is_winner) / GREATEST(COUNT(*), 1)) * 100, 1) as winrate,
                {$metaScoreSql} as meta_score,
                ROUND(IFNULL(SUM(points_won) / NULLIF(SUM(is_winner), 0), 0), 2) as avg_dmg,
                SUM(xtreme_wins) as xtremes,
                SUM(burst_wins) as bursts,
                SUM(over_wins) as overs,
                SUM(spin_wins) as spins
            FROM (
                SELECT 
                    c.{$colName} as part_name,
                    (mb.winner_id = c.user_id) as is_winner,
                    (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                    CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_won,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN events e ON tm.event_id = e.id
                JOIN tournament_results c ON mb.player1_combo_id = c.id
                {$whereSql} AND c.{$colName} IS NOT NULL AND c.{$colName} != '' AND c.{$colName} != '-' AND c.{$colName} NOT LIKE '%Selecciona%'
                UNION ALL
                SELECT 
                    c.{$colName} as part_name,
                    (mb.winner_id = c.user_id) as is_winner,
                    (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loser,
                    CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as points_won,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'xtreme' THEN 1 ELSE 0 END as xtreme_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'burst' THEN 1 ELSE 0 END as burst_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'over' THEN 1 ELSE 0 END as over_wins,
                    CASE WHEN mb.winner_id = c.user_id AND mb.finish_type = 'spin' THEN 1 ELSE 0 END as spin_wins
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN events e ON tm.event_id = e.id
                JOIN tournament_results c ON mb.player2_combo_id = c.id
                {$whereSql} AND c.{$colName} IS NOT NULL AND c.{$colName} != '' AND c.{$colName} != '-' AND c.{$colName} NOT LIKE '%Selecciona%'
            ) as p_uni
            GROUP BY part_name
            HAVING total_partidas >= ?
            ORDER BY meta_score DESC, total_partidas DESC
            LIMIT 60
        ", $partBindings);

        $totalRows = count($rows);
        foreach ($rows as $idx => &$r) {
            if ($totalRows <= 7) {
                $map = ['SS', 'S', 'A+', 'A', 'B', 'C', 'D'];
                $r->tier = $map[$idx] ?? 'D';
            } else {
                $pct = ($idx + 1) / $totalRows;
                if ($pct <= 0.06) {
                    $r->tier = 'SS';
                } elseif ($pct <= 0.16) {
                    $r->tier = 'S';
                } elseif ($pct <= 0.30) {
                    $r->tier = 'A+';
                } elseif ($pct <= 0.50) {
                    $r->tier = 'A';
                } elseif ($pct <= 0.72) {
                    $r->tier = 'B';
                } elseif ($pct <= 0.88) {
                    $r->tier = 'C';
                } else {
                    $r->tier = 'D';
                }
            }
        }
        unset($r);

        return $rows;
    }

    /**
     * Obtiene hasta 3 counters legales recomendados frente a las Blades rivales.
     *
     * @param  array<int, string>  $rivalBlades
     * @param  array<int, string>  $lockedBlades
     * @param  array<int, string>  $lockedRatchets
     * @param  array<int, string>  $lockedBits
     * @param  array<string, mixed>|null  $eslabonDebil
     * @param  int  $weakIndex
     * @return array<int, array<string, mixed>>
     */
    private function getRecommendedCounters(
        array $rivalBlades,
        array $lockedBlades,
        array $lockedRatchets,
        array $lockedBits,
        ?array $eslabonDebil,
        int $weakIndex
    ): array {
        if (empty($rivalBlades)) {
            return [];
        }

        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $sortedBlades = $rivalBlades;
        sort($sortedBlades);
        $cacheKey = 'beylab:counters:' . $seasonStart . ':' . md5(json_encode($sortedBlades));

        $candidateRows = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($rivalBlades) {
            $placeholders = implode(',', array_fill(0, count($rivalBlades), '?'));
            $bindingsCounter = array_merge($rivalBlades, $rivalBlades);

            return DB::select("
                SELECT 
                    blade, ratchet, bit,
                    SUM(is_win) as wins_vs_rival,
                    COUNT(*) as total_vs_rival,
                    ROUND((SUM(is_win) / COUNT(*)) * 100, 1) as wr_vs_rival,
                    ROUND(((SUM(is_win) + 1.5) / (COUNT(*) + 3.0)) * 100, 2) as counter_score
                FROM (
                    SELECT 
                        c1.blade, c1.ratchet, c1.bit,
                        (mb.winner_id = c1.user_id) as is_win
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL
                      AND c2.blade IN ({$placeholders})
                      AND c1.blade IS NOT NULL AND c1.blade != 'DESCONOCIDO' AND c1.blade NOT LIKE '%Selecciona%'
                    UNION ALL
                    SELECT 
                        c2.blade, c2.ratchet, c2.bit,
                        (mb.winner_id = c2.user_id) as is_win
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL
                      AND c1.blade IN ({$placeholders})
                      AND c2.blade IS NOT NULL AND c2.blade != 'DESCONOCIDO' AND c2.blade NOT LIKE '%Selecciona%'
                ) as duelos_directos
                GROUP BY blade, ratchet, bit
                HAVING total_vs_rival >= 3 AND wins_vs_rival >= 2
                ORDER BY counter_score DESC, wr_vs_rival DESC, wins_vs_rival DESC
                LIMIT 35
            ", $bindingsCounter);
        });

        $recommendedCounters = [];
        $seenCounterBlades = [];

        foreach ($candidateRows as $row) {
            if (count($recommendedCounters) >= 3) {
                break;
            }

            if (in_array($row->blade, $lockedBlades, true)) {
                continue;
            }
            if (in_array($row->blade, $seenCounterBlades, true)) {
                continue;
            }
            if ($row->ratchet !== 'UX Expanded' && in_array($row->ratchet, $lockedRatchets, true)) {
                continue;
            }
            if ($row->bit !== 'Without Bit' && in_array($row->bit, $lockedBits, true)) {
                continue;
            }

            if (
                $eslabonDebil
                && $row->blade === ($eslabonDebil['blade'] ?? '')
                && $row->ratchet === ($eslabonDebil['ratchet'] ?? '')
                && $row->bit === ($eslabonDebil['bit'] ?? '')
            ) {
                continue;
            }

            $seenCounterBlades[] = $row->blade;
            $recommendedCounters[] = [
                'combo'        => "{$row->blade} {$row->ratchet} {$row->bit}",
                'blade'        => $row->blade,
                'ratchet'      => $row->ratchet,
                'bit'          => $row->bit,
                'wins'         => (int) $row->wins_vs_rival,
                'total'        => (int) $row->total_vs_rival,
                'winrate'      => (float) $row->wr_vs_rival,
                'replace_slot' => $weakIndex + 1,
            ];
        }

        return $recommendedCounters;
    }

    /**
     * Calcula el perfil pentagonal para el gráfico radar de BeyLab Web.
     *
     * @param  array<int, array<string, mixed>>  $deckStats
     * @return array<int, float>
     */
    private function calcRadarProfile(array $deckStats): array
    {
        if (empty($deckStats)) {
            return [50, 50, 50, 50, 50];
        }

        $col        = collect($deckStats);
        $avgWr      = (float) $col->avg('winrate');
        $avgXtreme  = min(100, (float) $col->avg('xtreme_pct') * 1.6);
        $avgBurst   = min(100, (float) $col->avg('burst_pct') * 1.6);
        $avgControl = min(100, (float) $col->avg(fn($s) => ($s['spin_pct'] + $s['over_pct'])) * 1.2);
        $avgDefense = max(10, min(100, 100 - ((float) $col->avg('burst_risk') * 2.2)));

        return [
            round($avgWr, 1),
            round($avgXtreme, 1),
            round($avgBurst, 1),
            round($avgControl, 1),
            round($avgDefense, 1),
        ];
    }

    /**
     * Obtiene el Top 5 de combos semanales con comparativa de tendencia (cacheado 10 min).
     *
     * @return array<int, object>
     */
    private function getWeeklyTopCombos(): array
    {
        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $inicioSemanaPasada   = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $finSemanaPasada      = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');
        $inicioHaceDosSemanas = Carbon::now()->subWeeks(2)->startOfWeek()->format('Y-m-d');
        $finHaceDosSemanas    = Carbon::now()->subWeeks(2)->endOfWeek()->format('Y-m-d');

        $cacheKey = "beylab:weekly_top:{$seasonStart}:{$inicioSemanaPasada}_{$finSemanaPasada}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use (
            $inicioSemanaPasada,
            $finSemanaPasada,
            $inicioHaceDosSemanas,
            $finHaceDosSemanas
        ) {
            $getTopCombos = function (string $inicio, string $fin): array {
                $data = DB::table('tournament_results')
                    ->select(
                        'blade',
                        DB::raw("CASE WHEN over_blade LIKE '%Selecciona%' OR over_blade IS NULL OR over_blade = '' THEN '-' ELSE over_blade END as over_blade"),
                        DB::raw("CASE WHEN assist_blade LIKE '%Selecciona%' OR assist_blade IS NULL OR assist_blade = '' THEN '-' ELSE assist_blade END as assist_blade"),
                        'ratchet',
                        'bit',
                        DB::raw('COUNT(*) as usos')
                    )
                    ->whereDate('created_at', '>=', $inicio)
                    ->whereDate('created_at', '<=', $fin)
                    ->where('blade', '!=', '')
                    ->where('blade', 'NOT LIKE', '%Selecciona%')
                    ->where('blade', '!=', 'DESCONOCIDO')
                    ->groupBy('blade', 'over_blade', 'assist_blade', 'ratchet', 'bit')
                    ->orderByDesc('usos')
                    ->get();

                $formateado = [];
                foreach ($data as $item) {
                    $extras = [];
                    if ($item->over_blade !== '-') {
                        $extras[] = $item->over_blade;
                    }
                    if ($item->assist_blade !== '-') {
                        $extras[] = $item->assist_blade;
                    }
                    $extraStr = !empty($extras) ? ' ' . implode(' ', $extras) : '';
                    $nombreCombo = $item->blade . $extraStr . ' | ' . $item->ratchet . ' · ' . $item->bit;
                    $formateado[$nombreCombo] = $item->usos;
                }
                return $formateado;
            };

            $combosSemanaPasada    = $getTopCombos($inicioSemanaPasada, $finSemanaPasada);
            $combosHaceDosSemanas  = $getTopCombos($inicioHaceDosSemanas, $finHaceDosSemanas);
            $topCombosSemanaPasada = array_slice($combosSemanaPasada, 0, 5, true);
            $topSemanal            = [];
            $posicionActual        = 1;

            foreach ($topCombosSemanaPasada as $nombre => $usos) {
                $claseTendencia = 'text-info';
                $iconoTendencia = 'NEW';
                $posicionPasada = 1;
                $encontrado     = false;

                foreach ($combosHaceDosSemanas as $nombrePasado => $usosPasado) {
                    if ($nombre === $nombrePasado) {
                        $encontrado = true;
                        break;
                    }
                    $posicionPasada++;
                }

                if ($encontrado) {
                    if ($posicionActual < $posicionPasada) {
                        $claseTendencia = 'text-success';
                        $iconoTendencia = '▲';
                    } elseif ($posicionActual > $posicionPasada) {
                        $claseTendencia = 'text-danger';
                        $iconoTendencia = '▼';
                    } else {
                        $claseTendencia = 'text-secondary';
                        $iconoTendencia = '=';
                    }
                }

                $topSemanal[] = (object) [
                    'posicion'        => $posicionActual,
                    'nombre'          => $nombre,
                    'usos'            => $usos,
                    'tendencia_clase' => $claseTendencia,
                    'tendencia_icono' => $iconoTendencia,
                ];
                $posicionActual++;
            }

            return $topSemanal;
        });
    }

    /**
     * Obtiene los 10 últimos torneos jugados o filtrados para el Módulo 05 de Telemetría.
     *
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object>
     */
    private function getRecentTournamentsList(array $filters): Collection
    {
        $seasonStart = (string) config('sbbl.season_start', '2026-09-01');
        $cacheKey = 'beylab:recent_tourneys:' . $seasonStart . ':' . md5(json_encode($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($filters) {
            $tipoEvento  = $filters['tipo_evento'] ?? null;
            $regionId    = $filters['region_id'] ?? null;
            $fechaInicio = $filters['fecha_inicio'] ?? null;
            $fechaFin    = $filters['fecha_fin'] ?? null;

            $torneosQuery = DB::table('events as e')
                ->leftJoin('regions as r', 'e.region_id', '=', 'r.id')
                ->where(function ($q) {
                    $q->whereIn('e.status', ['COMPLETED', 'CLOSED', 'PENDING', 'REVIEW', 'LIVE'])
                      ->orWhereExists(function ($sq) {
                          $sq->select(DB::raw(1))
                             ->from('tournament_matches as tm')
                             ->whereColumn('tm.event_id', 'e.id');
                      });
                });

            if (!empty($tipoEvento)) {
                if ($tipoEvento === 'ranking') {
                    $torneosQuery->whereIn('e.beys', ['ranking', 'rankingplus']);
                } elseif ($tipoEvento === 'crew_battle') {
                    $torneosQuery->whereIn('e.beys', ['crew_battle', 'equipos']);
                } else {
                    $torneosQuery->where('e.beys', $tipoEvento);
                }
            }
            if (!empty($regionId)) {
                $torneosQuery->where('e.region_id', (int) $regionId);
            }
            if (!empty($fechaInicio)) {
                $torneosQuery->whereDate('e.date', '>=', $fechaInicio);
            }
            if (!empty($fechaFin)) {
                $torneosQuery->whereDate('e.date', '<=', $fechaFin);
            }

            return $torneosQuery
                ->select(
                    'e.id',
                    'e.name',
                    'e.date',
                    'e.city',
                    'e.beys',
                    'r.name as region_name',
                    DB::raw('(SELECT COUNT(*) FROM assist_user_event aue WHERE aue.event_id = e.id AND (aue.puesto IS NULL OR aue.puesto != "dropped")) as total_players')
                )
                ->orderBy('e.date', 'desc')
                ->orderBy('e.id', 'desc')
                ->limit(10)
                ->get();
        });
    }

    /**
     * Obtiene en una sola consulta el Top 3 de combos ganadores para múltiples jugadores,
     * utilizando JOINs directos por PK en lugar de `JOIN ... ON CASE WHEN`.
     *
     * @param  array<int, int>  $userIds
     * @return array<int, array<int, object>>
     */
    private function fetchTopCombosForPlayers(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $bindings = array_merge($userIds, $userIds);

        $rows = DB::select("
            SELECT
                winner_id,
                combo,
                COUNT(*) as wins
            FROM (
                SELECT
                    mb.winner_id,
                    CONCAT(c1.blade, ' ', c1.ratchet, ' ', c1.bit) as combo
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                WHERE mb.winner_id IN ({$placeholders})
                  AND mb.winner_id = tm.player1_id
                  AND mb.finish_type IN ('spin','over','burst','xtreme')
                UNION ALL
                SELECT
                    mb.winner_id,
                    CONCAT(c2.blade, ' ', c2.ratchet, ' ', c2.bit) as combo
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                WHERE mb.winner_id IN ({$placeholders})
                  AND mb.winner_id = tm.player2_id
                  AND mb.finish_type IN ('spin','over','burst','xtreme')
            ) as player_wins
            GROUP BY winner_id, combo
            ORDER BY wins DESC
        ", $bindings);

        $grouped = [];
        foreach ($userIds as $uid) {
            $grouped[$uid] = [];
        }

        foreach ($rows as $r) {
            $uid = (int) $r->winner_id;
            if (isset($grouped[$uid]) && count($grouped[$uid]) < 3) {
                $grouped[$uid][] = (object) [
                    'combo' => $r->combo,
                    'wins'  => (int) $r->wins,
                ];
            }
        }

        return $grouped;
    }
}
