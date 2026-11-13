<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TournamentResult;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TournamentResultController extends Controller
{
    public function store(Request $request, $eventId)
    {
        $userId = Auth::id();

        if ($request->has('is_tentative')) {
            TournamentResult::where('user_id', $userId)
                ->where('event_id', $eventId)
                ->delete();
            return redirect()->back()->with('success', 'Inscripción marcada como tentativa.');
        }

        $request->validate([
            "blade.{$userId}"   => 'required|array|min:3',
            "blade.{$userId}.*" => 'required|string',
            "ratchet.{$userId}"   => 'required|array|min:3',
            "ratchet.{$userId}.*" => 'required|string',
            "bit.{$userId}"   => 'required|array|min:3',
            "bit.{$userId}.*" => 'required|string',
        ], [
            "blade.{$userId}.*.required"   => 'Debes rellenar los 3 Blades de tu combo.',
            "ratchet.{$userId}.*.required" => 'Debes rellenar los 3 Ratchets de tu combo.',
            "bit.{$userId}.*.required"     => 'Debes rellenar los 3 Bits de tu combo.',
        ]);

        TournamentResult::where('user_id', $userId)
            ->where('event_id', $eventId)
            ->delete();

        $blades         = $request->input("blade.{$userId}", []);
        $ratchets       = $request->input("ratchet.{$userId}", []);
        $bits           = $request->input("bit.{$userId}", []);
        $overBlades     = $request->input("over_blade.{$userId}", []);
        $assistBlades   = $request->input("assist_blade.{$userId}", []);
        $victorias      = $request->input("victorias.{$userId}", []);
        $derrotas       = $request->input("derrotas.{$userId}", []);
        $puntosGanados  = $request->input("puntos_ganados.{$userId}", []);
        $puntosPerdidos = $request->input("puntos_perdidos.{$userId}", []);

        foreach ($blades as $index => $blade) {
            if (empty($blade)) continue;

            TournamentResult::create([
                'user_id'         => $userId,
                'event_id'        => $eventId,
                'blade'           => $blade,
                'over_blade'      => $overBlades[$index] ?? null,
                'assist_blade'    => $assistBlades[$index] ?? null,
                'ratchet'         => $ratchets[$index] ?? null,
                'bit'             => $bits[$index] ?? null,
                'victorias'       => $victorias[$index] ?? 0,
                'derrotas'        => $derrotas[$index] ?? 0,
                'puntos_ganados'  => $puntosGanados[$index] ?? 0,
                'puntos_perdidos' => $puntosPerdidos[$index] ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Deck de combate guardado y registrado correctamente.');
    }

    public function storeduel(Request $request, $versusId)
    {
        $user = Auth::user();
        TournamentResult::where('user_id', $user->id)
            ->where('versus_id', $versusId)
            ->delete();

        foreach ($request->blade as $index => $blade) {
            TournamentResult::create([
                'user_id'         => $user->id,
                'versus_id'       => $versusId,
                'blade'           => $blade,
                'over_blade'      => $request->over_blade[$index],
                'assist_blade'    => $request->assist_blade[$index],
                'ratchet'         => $request->ratchet[$index],
                'bit'             => $request->bit[$index],
                'victorias'       => $request->victorias[$index],
                'derrotas'        => $request->derrotas[$index],
                'puntos_ganados'  => $request->puntos_ganados[$index],
                'puntos_perdidos' => $request->puntos_perdidos[$index],
            ]);
        }

        return redirect()->back()->with('success', 'Resultados guardados correctamente.');
    }

    /**
     * RADAR META PRO + TIER LIST DE 7 NIVELES (SS..D) + BEY-LAB + TORNEOS + H2H
     */
    public function beybladeStats(Request $request)
    {
        $sort        = $request->get('sort', 'meta_score');
        $order       = strtolower($request->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        $minPartidas = (int) $request->get('min_partidas', 5);
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin    = $request->get('fecha_fin');
        $tipoEvento  = $request->get('tipo_evento');
        $regionId    = $request->get('region_id');
        $onlyUser    = $request->has('only_user_parts') && Auth::check();

        // 1. Catálogo oficial de piezas y usuarios
        $blades       = DB::table('blades')->orderBy('nombre_takara')->pluck('nombre_takara');
        $overBlades   = DB::table('over_blades')->orderBy('nombre')->pluck('nombre');
        $assistBlades = DB::table('assist_blades')->orderBy('nombre')->pluck('nombre');
        $ratchets     = DB::table('ratchets')->orderBy('nombre')->pluck('nombre');
        $bits         = DB::table('bits')->orderBy('nombre')->pluck('nombre');
        $regions      = Region::orderBy('name')->get(['id', 'name']);
        $playersList  = DB::table('users')->orderBy('name')->get(['id', 'name']);

        // 2. Condiciones dinámicas
        $bindings = [];
        $whereSql = " WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme') AND c.blade IS NOT NULL AND c.blade NOT LIKE '%Selecciona%' AND c.blade != 'DESCONOCIDO' ";

        if ($request->filled('blade')) {
            $whereSql .= " AND c.blade = ? ";
            $bindings[] = $request->blade;
        }
        if ($request->filled('over_blade')) {
            $whereSql .= " AND c.over_blade = ? ";
            $bindings[] = $request->over_blade;
        }
        if ($request->filled('assist_blade')) {
            $whereSql .= " AND c.assist_blade = ? ";
            $bindings[] = $request->assist_blade;
        }
        if ($request->filled('ratchet')) {
            $whereSql .= " AND c.ratchet = ? ";
            $bindings[] = $request->ratchet;
        }
        if ($request->filled('bit')) {
            $whereSql .= " AND c.bit = ? ";
            $bindings[] = $request->bit;
        }
        if ($request->filled('fecha_inicio')) {
            $whereSql .= " AND e.date >= ? ";
            $bindings[] = $fechaInicio;
        }
        if ($request->filled('fecha_fin')) {
            $whereSql .= " AND e.date <= ? ";
            $bindings[] = $fechaFin;
        }
        if ($request->filled('tipo_evento')) {
            if ($tipoEvento === 'ranking') {
                $whereSql .= " AND e.beys IN ('ranking', 'rankingplus') ";
            } elseif ($tipoEvento === 'crew_battle') {
                $whereSql .= " AND e.beys IN ('crew_battle', 'equipos') ";
            } else {
                $whereSql .= " AND e.beys = ? ";
                $bindings[] = $tipoEvento;
            }
        }
        if ($request->filled('region_id')) {
            $whereSql .= " AND e.region_id = ? ";
            $bindings[] = (int) $regionId;
        }
        if ($onlyUser) {
            $whereSql .= " AND c.user_id = ? ";
            $bindings[] = Auth::id();
        }

        $unionBindings = array_merge($bindings, $bindings, [$minPartidas]);

        $allowedSorts = ['meta_score', 'blade', 'percentage_victories', 'total_partidas', 'eficiencia', 'puntos_ganados_por_combate', 'xtreme_wins', 'burst_wins'];
        $sortCol = in_array($sort, $allowedSorts) ? $sort : 'meta_score';

        // 3. Consulta principal con META_SCORE BAYESIANO
        $beybladeStats = collect(DB::select("
            SELECT 
                blade, ratchet, bit,
                CASE WHEN over_blade IS NULL OR over_blade = '' OR over_blade LIKE '%Selecciona%' THEN '-' ELSE over_blade END as over_blade,
                CASE WHEN assist_blade IS NULL OR assist_blade = '' OR assist_blade LIKE '%Selecciona%' THEN '-' ELSE assist_blade END as assist_blade,
                COUNT(*) as total_partidas,
                SUM(is_winner) as total_victorias,
                SUM(is_loser) as total_derrotas,
                (SUM(is_winner) / GREATEST(COUNT(*), 1)) * 100 as percentage_victories,
                ROUND(((SUM(is_winner) + 5) / (COUNT(*) + 10)) * 100 + (LOG(COUNT(*) + 1) * 3.5), 2) as meta_score,
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
                $whereSql
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
                $whereSql
            ) as combates_unificados
            GROUP BY blade, ratchet, bit, over_blade, assist_blade
            HAVING total_partidas >= ?
            ORDER BY $sortCol $order, total_partidas DESC, percentage_victories DESC
            LIMIT 100
        ", $unionBindings));

        // 4. Red de Enfrentamientos Directos (Presas y Némesis)
        $matchupsRaw = DB::select("
            SELECT
                CONCAT(winner_c.blade, ' ', winner_c.ratchet, ' ', winner_c.bit) as winner_combo,
                CONCAT(loser_c.blade, ' ', loser_c.ratchet, ' ', loser_c.bit) as loser_combo,
                COUNT(*) as times_won
            FROM match_battles mb
            JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
            JOIN tournament_results winner_c ON winner_c.id = CASE WHEN mb.winner_id = tm.player1_id THEN mb.player1_combo_id ELSE mb.player2_combo_id END
            JOIN tournament_results loser_c ON loser_c.id = CASE WHEN mb.winner_id = tm.player1_id THEN mb.player2_combo_id ELSE mb.player1_combo_id END
            WHERE mb.winner_id IS NOT NULL AND mb.player1_combo_id IS NOT NULL AND mb.player2_combo_id IS NOT NULL
            GROUP BY winner_combo, loser_combo
        ");

        $matchupsMap = [];
        foreach ($matchupsRaw as $m) {
            $matchupsMap[$m->winner_combo]['wins'][$m->loser_combo]   = $m->times_won;
            $matchupsMap[$m->loser_combo]['losses'][$m->winner_combo] = $m->times_won;
        }

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

        // 5. Estadísticas por Piezas Aisladas con 7 Tiers (SS, S, A+, A, B, C, D)
        $buildPartStats = function (string $colName) use ($whereSql, $bindings, $minPartidas) {
            $partBindings = array_merge($bindings, $bindings, [max(1, $minPartidas)]);
            $rows = DB::select("
                SELECT 
                    part_name,
                    COUNT(*) as total_partidas,
                    SUM(is_winner) as total_victorias,
                    SUM(is_loser) as total_derrotas,
                    ROUND((SUM(is_winner) / GREATEST(COUNT(*), 1)) * 100, 1) as winrate,
                    ROUND(((SUM(is_winner) + 5) / (COUNT(*) + 10)) * 100 + (LOG(COUNT(*) + 1) * 3.5), 2) as meta_score,
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
                    $whereSql AND c.{$colName} IS NOT NULL AND c.{$colName} != '' AND c.{$colName} != '-' AND c.{$colName} NOT LIKE '%Selecciona%'
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
                    $whereSql AND c.{$colName} IS NOT NULL AND c.{$colName} != '' AND c.{$colName} != '-' AND c.{$colName} NOT LIKE '%Selecciona%'
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
                    if ($pct <= 0.06)      $r->tier = 'SS';
                    elseif ($pct <= 0.16)  $r->tier = 'S';
                    elseif ($pct <= 0.30)  $r->tier = 'A+';
                    elseif ($pct <= 0.50)  $r->tier = 'A';
                    elseif ($pct <= 0.72)  $r->tier = 'B';
                    elseif ($pct <= 0.88)  $r->tier = 'C';
                    else                   $r->tier = 'D';
                }
            }
            unset($r);
            return $rows;
        };

        $partStats = [
            'blades'        => $buildPartStats('blade'),
            'ratchets'      => $buildPartStats('ratchet'),
            'bits'          => $buildPartStats('bit'),
            'over_blades'   => $buildPartStats('over_blade'),
            'assist_blades' => $buildPartStats('assist_blade'),
        ];

        $globalFinishes = [
            'spin'   => $beybladeStats->sum('spin_wins'),
            'over'   => $beybladeStats->sum('over_wins'),
            'burst'  => $beybladeStats->sum('burst_wins'),
            'xtreme' => $beybladeStats->sum('xtreme_wins'),
        ];

        // 6. TOP 5 SEMANAL
        $inicioSemanaPasada   = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $finSemanaPasada      = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');
        $inicioHaceDosSemanas = Carbon::now()->subWeeks(2)->startOfWeek()->format('Y-m-d');
        $finHaceDosSemanas    = Carbon::now()->subWeeks(2)->endOfWeek()->format('Y-m-d');

        $getTopCombos = function($inicio, $fin) {
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
                if ($item->over_blade !== '-') $extras[] = $item->over_blade;
                if ($item->assist_blade !== '-') $extras[] = $item->assist_blade;
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
                    $claseTendencia = 'text-success'; $iconoTendencia = '▲';
                } elseif ($posicionActual > $posicionPasada) {
                    $claseTendencia = 'text-danger'; $iconoTendencia = '▼';
                } else {
                    $claseTendencia = 'text-secondary'; $iconoTendencia = '=';
                }
            }

            $topSemanal[] = (object)[
                'posicion'        => $posicionActual,
                'nombre'          => $nombre,
                'usos'            => $usos,
                'tendencia_clase' => $claseTendencia,
                'tendencia_icono' => $iconoTendencia
            ];
            $posicionActual++;
        }

        // 7. LISTADO DE TORNEOS PARA EL MÓDULO 05 (10 últimos jugados o filtrados)
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

        if ($request->filled('tipo_evento')) {
            if ($tipoEvento === 'ranking') {
                $torneosQuery->whereIn('e.beys', ['ranking', 'rankingplus']);
            } elseif ($tipoEvento === 'crew_battle') {
                $torneosQuery->whereIn('e.beys', ['crew_battle', 'equipos']);
            } else {
                $torneosQuery->where('e.beys', $tipoEvento);
            }
        }
        if ($request->filled('region_id')) {
            $torneosQuery->where('e.region_id', (int) $regionId);
        }
        if ($request->filled('fecha_inicio')) {
            $torneosQuery->whereDate('e.date', '>=', $fechaInicio);
        }
        if ($request->filled('fecha_fin')) {
            $torneosQuery->whereDate('e.date', '<=', $fechaFin);
        }

        $torneosStatsList = $torneosQuery
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

        return view('inicio.stats', compact(
            'beybladeStats', 'partStats', 'globalFinishes', 'sort', 'order', 'minPartidas',
            'blades', 'overBlades', 'assistBlades', 'ratchets', 'bits', 'regions', 'playersList',
            'fechaInicio', 'fechaFin', 'tipoEvento', 'regionId', 'topSemanal', 'torneosStatsList'
        ));
    }

    /**
     * GRÁFICO DE EVOLUCIÓN MENSUAL DE UN COMBO (WINRATE % Y VOLUMEN POR MES)
     */
    public function getComboTrend(Request $request)
    {
        $blade   = $request->query('blade');
        $ratchet = $request->query('ratchet');
        $bit     = $request->query('bit');

        if (empty($blade)) {
            return response()->json(['success' => false, 'timeline' => []]);
        }

        $condExtra = "";
        $bindings = [$blade];
        if (!empty($ratchet)) {
            $condExtra .= " AND c.ratchet = ? ";
            $bindings[] = $ratchet;
        }
        if (!empty($bit)) {
            $condExtra .= " AND c.bit = ? ";
            $bindings[] = $bit;
        }

        $allBindings = array_merge($bindings, $bindings);

        $rows = DB::select("
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
                  AND c.blade = ? $condExtra
                UNION ALL
                SELECT 
                    DATE_FORMAT(e.date, '%Y-%m') as ym,
                    (mb.winner_id = c.user_id) as is_win
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN events e ON tm.event_id = e.id
                JOIN tournament_results c ON mb.player2_combo_id = c.id
                WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                  AND c.blade = ? $condExtra
            ) as t_hist
            WHERE ym IS NOT NULL
            GROUP BY ym
            ORDER BY ym ASC
            LIMIT 12
        ", $allBindings);

        return response()->json([
            'success'  => true,
            'timeline' => $rows,
        ]);
    }

    /**
     * MÓDULO CARA A CARA ENTRE JUGADORES (PLAYER VS PLAYER H2H)
     */
    public function getPlayerH2H(Request $request)
    {
        $p1Id = (int) $request->query('p1');
        $p2Id = (int) $request->query('p2');

        if (!$p1Id || !$p2Id || $p1Id === $p2Id) {
            return response()->json(['success' => false, 'message' => 'Selecciona dos jugadores distintos.'], 422);
        }

        $u1 = DB::table('users')->where('id', $p1Id)->first(['id', 'name']);
        $u2 = DB::table('users')->where('id', $p2Id)->first(['id', 'name']);

        if (!$u1 || !$u2) {
            return response()->json(['success' => false, 'message' => 'Jugador no encontrado.'], 404);
        }

        // Partidas directas entre ambos
        $matches = \App\Models\TournamentMatch::with('event:id,name,date,city')
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

        $p1MatchWins = 0;
        $p2MatchWins = 0;
        $p1TotalPts  = 0;
        $p2TotalPts  = 0;
        $matchHistory = [];

        $matchIds = $matches->pluck('id')->toArray();
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
                ->toArray();

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

            if ($m->winner_id == $p1Id) $p1MatchWins++;
            elseif ($m->winner_id == $p2Id) $p2MatchWins++;

            $rawBouts = $battlesByMatch->get($m->id, collect());
            $timeline = [];
            $runP1 = 0;
            $runP2 = 0;
            $step = 0;

            foreach ($rawBouts as $b) {
                if (in_array($b->finish_type, ['warning_p1', 'warning_p2'])) {
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
                    'bout_num'   => $step,
                    'p1_combo'   => $c1Obj ? trim("{$c1Obj->blade} {$c1Obj->ratchet} {$c1Obj->bit}") : 'Bey P1',
                    'p2_combo'   => $c2Obj ? trim("{$c2Obj->blade} {$c2Obj->ratchet} {$c2Obj->bit}") : 'Bey P2',
                    'winner_name'=> $bWinnerIsP1 ? $u1->name : $u2->name,
                    'winner_p1'  => $bWinnerIsP1,
                    'finish'     => strtoupper($b->finish_type ?? 'PT'),
                    'points'     => (int) $b->points_awarded,
                    'running'    => "{$runP1} - {$runP2}",
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

        // Top 3 combos favoritos de cada jugador en la liga
        $getTopPlayerCombos = function ($uid) {
            return DB::select("
                SELECT 
                    CONCAT(c.blade, ' ', c.ratchet, ' ', c.bit) as combo,
                    COUNT(*) as wins
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN tournament_results c ON c.id = CASE WHEN mb.winner_id = tm.player1_id THEN mb.player1_combo_id ELSE mb.player2_combo_id END
                WHERE mb.winner_id = ? AND mb.finish_type IN ('spin','over','burst','xtreme')
                GROUP BY combo
                ORDER BY wins DESC
                LIMIT 3
            ", [$uid]);
        };

        return response()->json([
            'success'       => true,
            'p1'            => ['id' => $u1->id, 'name' => $u1->name, 'match_wins' => $p1MatchWins, 'total_pts' => $p1TotalPts, 'bouts_won' => $p1BoutsWon, 'top_combos' => $getTopPlayerCombos($p1Id)],
            'p2'            => ['id' => $u2->id, 'name' => $u2->name, 'match_wins' => $p2MatchWins, 'total_pts' => $p2TotalPts, 'bouts_won' => $p2BoutsWon, 'top_combos' => $getTopPlayerCombos($p2Id)],
            'total_matches' => count($matchHistory),
            'matches'       => $matchHistory,
        ]);
    }

    /**
     * TELEMETRÍA COMPLETA DE UN TORNEO (CON PIEZAS CRUDAS PARA BEY-LAB Y TIMELINE REPLAY DE CADA PARTIDA)
     */
    public function getTournamentStatsDetail($eventId)
    {
        $event = \App\Models\Event::with('region')->findOrFail($eventId);

        $engineController = app(\App\Http\Controllers\TournamentEngineController::class);
        $standingsData    = $engineController->getStandings($event->id)->getData(true);
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
                CONCAT(c.blade, ' ', IFNULL(c.ratchet, ''), ' ', IFNULL(c.bit, '')) as combo_name,
                u.name as owner_name,
                COUNT(*) as total_bouts,
                SUM(CASE WHEN mb.winner_id = c.user_id THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END) as pts_generated
            FROM match_battles mb
            JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
            JOIN tournament_results c ON (c.id = mb.player1_combo_id OR c.id = mb.player2_combo_id)
            JOIN users u ON c.user_id = u.id
            WHERE tm.event_id = ?
              AND mb.winner_id IS NOT NULL
              AND mb.finish_type IN ('spin', 'over', 'burst', 'xtreme')
            GROUP BY c.id, combo_name, u.name
            HAVING wins > 0
            ORDER BY wins DESC, pts_generated DESC
            LIMIT 5
        ", [$event->id]);

        $allMatches = \App\Models\TournamentMatch::with(['player1:id,name', 'player2:id,name'])
            ->where('event_id', $event->id)
            ->where('status', 'completed')
            ->orderBy('round_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $allDecksRaw = \App\Models\TournamentResult::where('event_id', $event->id)
            ->whereNotNull('blade')
            ->where('blade', 'NOT LIKE', '%Selecciona%')
            ->orderBy('id', 'asc')
            ->get();

        $combosById  = $allDecksRaw->keyBy('id');
        $allDecks    = $allDecksRaw->groupBy('user_id');

        // Pre-cargar todas las batallas individuales del torneo para construir el Replay Visual de cada partida
        $matchIds = $allMatches->pluck('id')->toArray();
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
                    if (in_array($b->finish_type, ['warning_p1', 'warning_p2'])) {
                        continue;
                    }
                    $boutStep++;
                    $iWonBout = ($b->winner_id == $uid);
                    if ($iWonBout) $myRun += (int) $b->points_awarded;
                    else $rivRun += (int) $b->points_awarded;

                    $c1 = $combosById->get($b->player1_combo_id);
                    $c2 = $combosById->get($b->player2_combo_id);
                    $myComboObj  = $amP1 ? $c1 : $c2;
                    $rivComboObj = $amP1 ? $c2 : $c1;

                    $timeline[] = [
                        'bout_num'  => $boutStep,
                        'my_combo'  => $myComboObj ? trim("{$myComboObj->blade} {$myComboObj->ratchet} {$myComboObj->bit}") : 'Tu Bey',
                        'riv_combo' => $rivComboObj ? trim("{$rivComboObj->blade} {$rivComboObj->ratchet} {$rivComboObj->bit}") : 'Bey Rival',
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
                'id'          => $uid,
                'name'        => $pl['name'],
                'shared_rank' => $pl['shared_rank'] ?? 1,
                'puesto'      => strtoupper($pl['puesto_oficial'] ?? $pl['puesto'] ?? 'PART.'),
                'wins'        => $pl['wins'] ?? 0,
                'losses'      => $pl['losses'] ?? 0,
                'points'      => $pl['points'] ?? 0,
                'buchholz'    => $pl['buchholz'] ?? 0,
                'point_diff'  => $pl['point_diff'] ?? 0,
                'deck'        => $deckFormatted,
                'matches'     => $duelosJugador,
            ];
        }

        return response()->json([
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
        ]);
    }

    /**
     * ANALIZADOR BEY-LAB PRO WEB
     */
    public function analyzeWebLab(Request $request)
    {
        $miDeck      = $request->input('mi_deck', []);
        $rivalDeck   = $request->input('rival_deck', []);
        $mode        = $request->input('mode', 'global_vs_global');
        $tipoEvento  = $request->input('tipo_evento');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');
        $userId      = Auth::id();

        $eventFilterSql = "";
        if (!empty($tipoEvento)) {
            if ($tipoEvento === 'ranking') {
                $eventFilterSql .= " AND e.beys IN ('ranking', 'rankingplus') ";
            } elseif ($tipoEvento === 'crew_battle') {
                $eventFilterSql .= " AND e.beys IN ('crew_battle', 'equipos') ";
            } else {
                $eventFilterSql .= " AND e.beys = " . DB::getPdo()->quote($tipoEvento) . " ";
            }
        }
        if (!empty($fechaInicio)) {
            $eventFilterSql .= " AND e.date >= " . DB::getPdo()->quote($fechaInicio) . " ";
        }
        if (!empty($fechaFin)) {
            $eventFilterSql .= " AND e.date <= " . DB::getPdo()->quote($fechaFin) . " ";
        }

        if (empty($rivalDeck)) {
            $fechaCorte = Carbon::now()->subDays(60)->toDateString();
            $topMetaRaw = DB::select("
                SELECT 
                    tr.blade, tr.ratchet, tr.bit,
                    CASE WHEN tr.over_blade IS NULL OR tr.over_blade = '' OR tr.over_blade = '-' OR tr.over_blade LIKE '%Selecciona%' THEN '-' ELSE tr.over_blade END as over_blade,
                    CASE WHEN tr.assist_blade IS NULL OR tr.assist_blade = '' OR tr.assist_blade = '-' OR tr.assist_blade LIKE '%Selecciona%' THEN '-' ELSE tr.assist_blade END as assist_blade,
                    COUNT(*) as usos
                FROM tournament_results tr
                JOIN events e ON tr.event_id = e.id
                WHERE tr.blade IS NOT NULL AND tr.blade NOT LIKE '%Selecciona%' AND tr.blade != 'DESCONOCIDO'
                  AND e.date >= ? $eventFilterSql
                GROUP BY tr.blade, tr.ratchet, tr.bit, over_blade, assist_blade
                ORDER BY usos DESC
                LIMIT 25
            ", [$fechaCorte]);

            $usedB = []; $usedR = []; $usedBt = []; $usedO = []; $usedA = [];
            foreach ($topMetaRaw as $boss) {
                if (count($rivalDeck) >= 3) break;
                if (in_array($boss->blade, $usedB)) continue;
                if ($boss->ratchet !== 'UX Expanded' && in_array($boss->ratchet, $usedR)) continue;
                if ($boss->bit !== 'Without Bit' && in_array($boss->bit, $usedBt)) continue;
                if ($boss->over_blade !== '-' && in_array($boss->over_blade, $usedO)) continue;
                if ($boss->assist_blade !== '-' && in_array($boss->assist_blade, $usedA)) continue;

                $rivalDeck[] = [
                    'blade'        => $boss->blade,
                    'over_blade'   => $boss->over_blade !== '-' ? $boss->over_blade : '',
                    'assist_blade' => $boss->assist_blade !== '-' ? $boss->assist_blade : '',
                    'ratchet'      => $boss->ratchet,
                    'bit'          => $boss->bit,
                ];
                $usedB[] = $boss->blade;
                if ($boss->ratchet !== 'UX Expanded') $usedR[] = $boss->ratchet;
                if ($boss->bit !== 'Without Bit') $usedBt[] = $boss->bit;
                if ($boss->over_blade !== '-') $usedO[] = $boss->over_blade;
                if ($boss->assist_blade !== '-') $usedA[] = $boss->assist_blade;
            }
        }

        $formatComboName = function ($b) {
            $extras = array_filter([$b['over_blade'] ?? '', $b['assist_blade'] ?? ''], fn($v) => !empty($v) && $v !== '-');
            $extraStr = !empty($extras) ? ' ' . implode(' ', $extras) : '';
            return trim(($b['blade'] ?? '') . $extraStr . ' ' . ($b['ratchet'] ?? '') . ' ' . ($b['bit'] ?? ''));
        };

        $queryStatsForCombo = function ($b, $filterUserId = null) use ($formatComboName, $eventFilterSql) {
            $blade   = $b['blade'] ?? '';
            $ratchet = $b['ratchet'] ?? '';
            $bit     = $b['bit'] ?? '';
            $over    = (!empty($b['over_blade']) && $b['over_blade'] !== '-') ? $b['over_blade'] : null;
            $assist  = (!empty($b['assist_blade']) && $b['assist_blade'] !== '-') ? $b['assist_blade'] : null;

            if (empty($blade)) return null;

            $overCond   = $over ? "AND c.over_blade = " . DB::getPdo()->quote($over) : "";
            $assistCond = $assist ? "AND c.assist_blade = " . DB::getPdo()->quote($assist) : "";
            $userCond   = $filterUserId ? "AND c.user_id = " . (int) $filterUserId : "";

            $runQuery = function ($useFullCombo) use ($blade, $ratchet, $bit, $overCond, $assistCond, $userCond, $eventFilterSql) {
                $comboCond = $useFullCombo
                    ? "AND c.blade = " . DB::getPdo()->quote($blade) . " AND c.ratchet = " . DB::getPdo()->quote($ratchet) . " AND c.bit = " . DB::getPdo()->quote($bit) . " $overCond $assistCond"
                    : "AND c.blade = " . DB::getPdo()->quote($blade);

                return DB::selectOne("
                    SELECT
                        COUNT(*) as total,
                        SUM(is_win) as wins,
                        SUM(is_loss) as losses,
                        ROUND(IFNULL(SUM(pts_won) / NULLIF(SUM(is_win), 0), 0), 2) as avg_dmg_dealt,
                        ROUND(IFNULL(SUM(pts_lost) / NULLIF(SUM(is_loss), 0), 0), 2) as avg_dmg_taken,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'xtreme' THEN 1 ELSE 0 END) as xtreme_w,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as burst_w,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'over' THEN 1 ELSE 0 END) as over_w,
                        SUM(CASE WHEN is_win = 1 AND finish_type = 'spin' THEN 1 ELSE 0 END) as spin_w,
                        SUM(CASE WHEN is_loss = 1 AND finish_type = 'burst' THEN 1 ELSE 0 END) as burst_suffered
                    FROM (
                        SELECT
                            (mb.winner_id = c.user_id) as is_win,
                            (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                            CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                            CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                            mb.finish_type
                        FROM match_battles mb
                        JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                        JOIN events e ON tm.event_id = e.id
                        JOIN tournament_results c ON mb.player1_combo_id = c.id
                        WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                          $comboCond $userCond $eventFilterSql
                        UNION ALL
                        SELECT
                            (mb.winner_id = c.user_id) as is_win,
                            (mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL) as is_loss,
                            CASE WHEN mb.winner_id = c.user_id THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_won,
                            CASE WHEN mb.winner_id != c.user_id AND mb.winner_id IS NOT NULL THEN LEAST(3, GREATEST(1, mb.points_awarded)) ELSE 0 END as pts_lost,
                            mb.finish_type
                        FROM match_battles mb
                        JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                        JOIN events e ON tm.event_id = e.id
                        JOIN tournament_results c ON mb.player2_combo_id = c.id
                        WHERE mb.winner_id IS NOT NULL AND mb.finish_type IN ('spin','over','burst','xtreme')
                          $comboCond $userCond $eventFilterSql
                    ) as hist
                ");
            };

            $exact = $runQuery(true);
            $minThreshold = $filterUserId ? 1 : 3;

            if (!$exact || (int) $exact->total < $minThreshold) {
                $fallback = $runQuery(false);
                if ($fallback && (int) $fallback->total > (int) ($exact->total ?? 0)) {
                    $data      = $fallback;
                    $precision = $filterUserId ? 'Tus datos (Por Blade)' : 'Global (Por Blade)';
                } else {
                    $data      = $exact;
                    $precision = $filterUserId ? 'Tus datos (Combo Exacto)' : 'Global (Combo Exacto)';
                }
            } else {
                $data      = $exact;
                $precision = $filterUserId ? 'Tus datos (Combo Exacto)' : 'Global (Combo Exacto)';
            }

            $totalMatches = (int) ($data->total ?? 0);
            $wins         = (int) ($data->wins ?? 0);
            $losses       = (int) ($data->losses ?? 0);
            $wr           = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 50.0;
            $burstRisk    = $totalMatches > 0 ? round(((int) ($data->burst_suffered ?? 0) / $totalMatches) * 100, 1) : 10.0;

            return [
                'combo'         => $formatComboName($b),
                'short_name'    => $blade,
                'parts_label'   => trim(($over ? "$over · " : '') . ($assist ? "$assist · " : '') . "$ratchet · $bit"),
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
                'avg_dmg_dealt' => (float) ($data->avg_dmg_dealt ?? 0),
                'avg_dmg_taken' => (float) ($data->avg_dmg_taken ?? 0),
                'xtreme_pct'    => $wins > 0 ? round(($data->xtreme_w / $wins) * 100) : 0,
                'burst_pct'     => $wins > 0 ? round(($data->burst_w / $wins) * 100) : 0,
                'over_pct'      => $wins > 0 ? round(($data->over_w / $wins) * 100) : 0,
                'spin_pct'      => $wins > 0 ? round(($data->spin_w / $wins) * 100) : 0,
                'burst_risk'    => $burstRisk,
            ];
        };

        $miDeckStats = [];
        $personalMatchesFound = 0;

        foreach ($miDeck as $b) {
            if (empty($b['blade'])) continue;
            $globalStat = $queryStatsForCombo($b, null);

            if ($mode === 'personal_vs_global' && $userId) {
                $personalStat = $queryStatsForCombo($b, $userId);
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
        foreach ($rivalDeck as $b) {
            if (!empty($b['blade'])) {
                $rivalDeckStats[] = $queryStatsForCombo($b, null);
            }
        }

        $getHeadToHead = function ($cA, $cB) use ($eventFilterSql, $mode) {
            $personalBonus = ($mode === 'personal_vs_global' && !empty($cA['using_personal']))
                ? (($cA['delta_vs_global'] ?? 0) * 0.35)
                : 0;

            $h2hExact = DB::selectOne("
                SELECT COUNT(*) as total, SUM(a_wins) as wins
                FROM (
                    SELECT (mb.winner_id = c1.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL $eventFilterSql
                      AND c1.blade = ? AND c1.ratchet = ? AND c1.bit = ?
                      AND c2.blade = ? AND c2.ratchet = ? AND c2.bit = ?
                    UNION ALL
                    SELECT (mb.winner_id = c2.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL $eventFilterSql
                      AND c2.blade = ? AND c2.ratchet = ? AND c2.bit = ?
                      AND c1.blade = ? AND c1.ratchet = ? AND c1.bit = ?
                ) as duelos
            ", [
                $cA['blade'], $cA['ratchet'], $cA['bit'], $cB['blade'], $cB['ratchet'], $cB['bit'],
                $cA['blade'], $cA['ratchet'], $cA['bit'], $cB['blade'], $cB['ratchet'], $cB['bit']
            ]);

            if ($h2hExact && $h2hExact->total >= 3) {
                $baseWr = ($h2hExact->wins / $h2hExact->total) * 100;
                $finalWr = round(max(5, min(95, $baseWr + $personalBonus)), 1);
                return [
                    'winrate' => $finalWr,
                    'matches' => (int) $h2hExact->total,
                    'source'  => 'Exacto (' . $h2hExact->total . ')'
                ];
            }

            $h2hBlade = DB::selectOne("
                SELECT COUNT(*) as total, SUM(a_wins) as wins
                FROM (
                    SELECT (mb.winner_id = c1.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL $eventFilterSql AND c1.blade = ? AND c2.blade = ?
                    UNION ALL
                    SELECT (mb.winner_id = c2.user_id) as a_wins
                    FROM match_battles mb
                    JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                    JOIN events e ON tm.event_id = e.id
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL $eventFilterSql AND c2.blade = ? AND c1.blade = ?
                ) as duelos_blade
            ", [$cA['blade'], $cB['blade'], $cA['blade'], $cB['blade']]);

            if ($h2hBlade && $h2hBlade->total >= 3) {
                $bladeWr    = ($h2hBlade->wins / $h2hBlade->total) * 100;
                $adjustedWr = round(($bladeWr * 0.75) + ((50 + ($cA['winrate'] - $cB['winrate']) * 0.5) * 0.25) + $personalBonus, 1);
                return [
                    'winrate' => max(5, min(95, $adjustedWr)),
                    'matches' => (int) $h2hBlade->total,
                    'source'  => 'Por Blade (' . $h2hBlade->total . ')'
                ];
            }

            $est = round(50 + (($cA['winrate'] - $cB['winrate']) * 0.8), 1);
            return [
                'winrate' => max(15, min(85, $est)),
                'matches' => 0,
                'source'  => 'Estimado'
            ];
        };

        $matrix          = [];
        $totalWinProbSum = 0;
        $pairCount       = 0;

        foreach ($miDeckStats as $rIdx => $myStat) {
            $row = [];
            $rowSum = 0;
            foreach ($rivalDeckStats as $rivStat) {
                $duel = $getHeadToHead($myStat, $rivStat);
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
            $miDeckStats[$rIdx]['avg_vs_rival_deck'] = count($rivalDeckStats) > 0 ? round($rowSum / count($rivalDeckStats), 1) : $myStat['winrate'];
            $matrix[] = $row;
        }

        $overallVsRival = $pairCount > 0 ? round($totalWinProbSum / $pairCount, 1) : 50.0;

        $ordenSalida = $miDeckStats;
        usort($ordenSalida, function ($a, $b) {
            $scoreA = ($a['avg_vs_rival_deck'] ?? 50) - ($a['burst_risk'] * 0.4);
            $scoreB = ($b['avg_vs_rival_deck'] ?? 50) - ($b['burst_risk'] * 0.4);
            return $scoreB <=> $scoreA;
        });

        $weakIndex = 0;
        $minAvgWr = 999;
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
            if ($idx === $weakIndex) continue;
            if (!empty($st['blade'])) $lockedBlades[] = $st['blade'];
            if (!empty($st['ratchet']) && $st['ratchet'] !== 'UX Expanded') $lockedRatchets[] = $st['ratchet'];
            if (!empty($st['bit']) && $st['bit'] !== 'Without Bit') $lockedBits[] = $st['bit'];
        }

        $rivalBlades = collect($rivalDeckStats)->pluck('blade')->filter()->values()->toArray();
        $recommendedCounters = [];

        if (!empty($rivalBlades)) {
            $placeholders = implode(',', array_fill(0, count($rivalBlades), '?'));
            $bindingsCounter = array_merge($rivalBlades, $rivalBlades);

            $candidateRows = DB::select("
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
                      AND c2.blade IN ($placeholders)
                      AND c1.blade IS NOT NULL AND c1.blade != 'DESCONOCIDO' AND c1.blade NOT LIKE '%Selecciona%'
                    UNION ALL
                    SELECT 
                        c2.blade, c2.ratchet, c2.bit,
                        (mb.winner_id = c2.user_id) as is_win
                    FROM match_battles mb
                    JOIN tournament_results c1 ON mb.player1_combo_id = c1.id
                    JOIN tournament_results c2 ON mb.player2_combo_id = c2.id
                    WHERE mb.winner_id IS NOT NULL
                      AND c1.blade IN ($placeholders)
                      AND c2.blade IS NOT NULL AND c2.blade != 'DESCONOCIDO' AND c2.blade NOT LIKE '%Selecciona%'
                ) as duelos_directos
                GROUP BY blade, ratchet, bit
                HAVING total_vs_rival >= 3 AND wins_vs_rival >= 2
                ORDER BY counter_score DESC, wr_vs_rival DESC, wins_vs_rival DESC
                LIMIT 35
            ", $bindingsCounter);

            $seenCounterBlades = [];
            foreach ($candidateRows as $row) {
                if (count($recommendedCounters) >= 3) break;

                if (in_array($row->blade, $lockedBlades)) continue;
                if (in_array($row->blade, $seenCounterBlades)) continue;
                if ($row->ratchet !== 'UX Expanded' && in_array($row->ratchet, $lockedRatchets)) continue;
                if ($row->bit !== 'Without Bit' && in_array($row->bit, $lockedBits)) continue;

                if ($eslabonDebil && $row->blade === $eslabonDebil['blade'] && $row->ratchet === $eslabonDebil['ratchet'] && $row->bit === $eslabonDebil['bit']) {
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
        }

        $calcRadarProfile = function (array $deckStats) {
            if (empty($deckStats)) return [50, 50, 50, 50, 50];
            $avgWr      = collect($deckStats)->avg('winrate');
            $avgXtreme  = min(100, collect($deckStats)->avg('xtreme_pct') * 1.6);
            $avgBurst   = min(100, collect($deckStats)->avg('burst_pct') * 1.6);
            $avgControl = min(100, collect($deckStats)->avg(fn($s) => ($s['spin_pct'] + $s['over_pct'])) * 1.2);
            $avgDefense = max(10, min(100, 100 - (collect($deckStats)->avg('burst_risk') * 2.2)));

            return [
                round($avgWr, 1),
                round($avgXtreme, 1),
                round($avgBurst, 1),
                round($avgControl, 1),
                round($avgDefense, 1),
            ];
        };

        return response()->json([
            'success'                => true,
            'mode'                   => $mode,
            'personal_matches_found' => $personalMatchesFound,
            'my_deck_stats'          => $miDeckStats,
            'rival_deck_stats'       => $rivalDeckStats,
            'matrix'                 => $matrix,
            'overall_winrate'        => $overallVsRival,
            'radar_my_deck'          => $calcRadarProfile($miDeckStats),
            'radar_rival_deck'       => $calcRadarProfile($rivalDeckStats),
            'recommended_order'      => array_map(fn($x) => $x['combo'], $ordenSalida),
            'weak_link'              => $eslabonDebil ? $eslabonDebil['combo'] : null,
            'weak_slot'              => $weakIndex + 1,
            'recommended_counters'   => $recommendedCounters,
        ]);
    }

    public function separateStats(Request $request)
    {
        $sort  = $request->input('sort', 'blade');
        $order = $request->input('order', 'asc');

        $bladeSortableColumns   = ['blade', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];
        $ratchetSortableColumns = ['ratchet', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];
        $bitSortableColumns     = ['bit', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];

        $bladeSort   = in_array($sort, $bladeSortableColumns) ? $sort : 'blade';
        $ratchetSort = in_array($sort, $ratchetSortableColumns) ? $sort : 'ratchet';
        $bitSort     = in_array($sort, $bitSortableColumns) ? $sort : 'bit';

        $bladeStats = DB::table('tournament_results')
            ->select(
                'blade',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100 ELSE 0 END AS percentage_victories')
            )
            ->where('blade', 'NOT LIKE', '%Selecciona%')
            ->groupBy('blade')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($bladeSort, $order)
            ->get();

        $ratchetStats = DB::table('tournament_results')
            ->select(
                'ratchet',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100 ELSE 0 END AS percentage_victories')
            )
            ->where('ratchet', 'NOT LIKE', '%Selecciona%')
            ->groupBy('ratchet')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($ratchetSort, $order)
            ->get();

        $bitStats = DB::table('tournament_results')
            ->select(
                'bit',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100 ELSE 0 END AS percentage_victories')
            )
            ->where('bit', 'NOT LIKE', '%Selecciona%')
            ->groupBy('bit')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($bitSort, $order)
            ->get();

        return view('inicio.separate_stats', [
            'bladeStats'   => $bladeStats,
            'ratchetStats' => $ratchetStats,
            'bitStats'     => $bitStats,
            'order'        => $order,
        ]);
    }

    public function showRanking()
    {
        $fecha_actual      = now();
        $inicioMesAnterior = $fecha_actual->copy()->subMonth()->startOfMonth()->toDateString();
        $finMesAnterior    = $fecha_actual->copy()->subMonth()->endOfMonth()->toDateString();

        $subquery = DB::table('tournament_results')
            ->leftJoin('events', 'tournament_results.event_id', '=', 'events.id')
            ->leftJoin('versus', 'tournament_results.versus_id', '=', 'versus.id')
            ->join('users', 'tournament_results.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name',
                'tournament_results.puntos_ganados',
                'tournament_results.puntos_perdidos',
                DB::raw("
                    CASE
                        WHEN tournament_results.event_id IS NOT NULL THEN events.date
                        WHEN tournament_results.versus_id IS NOT NULL THEN versus.created_at
                        ELSE NULL
                    END as real_date
                ")
            )
            ->where(function($query) use ($inicioMesAnterior, $finMesAnterior) {
                $query->where(function($q) use ($inicioMesAnterior, $finMesAnterior) {
                    $q->whereNotNull('tournament_results.event_id')
                      ->whereBetween('events.date', [$inicioMesAnterior, $finMesAnterior]);
                })->orWhere(function($q) use ($inicioMesAnterior, $finMesAnterior) {
                    $q->whereNotNull('tournament_results.versus_id')
                      ->whereBetween('versus.created_at', [$inicioMesAnterior, $finMesAnterior]);
                });
            });

        $ranking = DB::table(DB::raw("({$subquery->toSql()}) as sub"))
            ->mergeBindings($subquery)
            ->select(
                'sub.name',
                DB::raw('SUM(sub.puntos_ganados) as total_puntos_ganados'),
                DB::raw('SUM(sub.puntos_perdidos) as total_puntos_perdidos'),
                DB::raw('SUM(sub.puntos_ganados + sub.puntos_perdidos) as total_participacion'),
                DB::raw('ROUND((SUM(sub.puntos_ganados) / NULLIF(SUM(sub.puntos_ganados + sub.puntos_perdidos), 0)) * 100, 2) as porcentaje_ganados')
            )
            ->groupBy('sub.user_id', 'sub.name')
            ->orderByDesc('total_participacion')
            ->limit(15)
            ->get();

        return view('inicio.rankingstats', ['ranking' => $ranking]);
    }

    public function getBeybladeStats(Request $request)
    {
        $sort          = $request->get('sort', 'blade');
        $order         = $request->get('order', 'asc');
        $bladeFilter   = $request->get('blade');
        $ratchetFilter = $request->get('ratchet');
        $bitFilter     = $request->get('bit');

        $beybladeStats = DB::table('tournament_results')
            ->select(
                'blade',
                'ratchet',
                'bit',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('CASE WHEN SUM(victorias) > 0 THEN SUM(puntos_ganados) / GREATEST(SUM(victorias), 1) ELSE 0 END AS puntos_ganados_por_combate'),
                DB::raw('CASE WHEN SUM(derrotas) > 0 THEN SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1) ELSE 0 END AS puntos_perdidos_por_combate'),
                DB::raw('SUM(victorias) + SUM(derrotas) as total_partidas'),
                DB::raw('CASE WHEN SUM(victorias) + SUM(derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias) + SUM(derrotas), 1)) * 100 ELSE 0 END AS percentage_victories'),
                DB::raw('CASE WHEN (SUM(victorias) + SUM(derrotas)) > 0 THEN (((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) / ((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) + (SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1)))) * ((SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100)) * LOG(SUM(victorias) + SUM(derrotas) + 1) ELSE 0 END AS eficiencia')
            )
            ->where('blade', 'NOT LIKE', '%Selecciona%')
            ->when($bladeFilter, fn($q) => $q->where('blade', $bladeFilter))
            ->when($ratchetFilter, fn($q) => $q->where('ratchet', $ratchetFilter))
            ->when($bitFilter, fn($q) => $q->where('bit', $bitFilter))
            ->groupBy('blade', 'over_blade', 'assist_blade', 'ratchet', 'bit')
            ->havingRaw('SUM(victorias) + SUM(derrotas) >= 10')
            ->orderBy($sort, $order)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $beybladeStats,
        ], 200);
    }
}