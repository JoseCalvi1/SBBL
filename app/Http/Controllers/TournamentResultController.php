<?php

namespace App\Http\Controllers;

use App\Models\TournamentResult;
use App\Services\Analytics\BeyLabService;
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
            "blade.{$userId}"     => 'required|array|min:3',
            "blade.{$userId}.*"   => 'required|string',
            "ratchet.{$userId}"   => 'required|array|min:3',
            "ratchet.{$userId}.*" => 'required|string',
            "bit.{$userId}"       => 'required|array|min:3',
            "bit.{$userId}.*"     => 'required|string',
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
            if (empty($blade)) {
                continue;
            }

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
    public function beybladeStats(Request $request, BeyLabService $beyLabService)
    {
        $filters = [
            'sort'            => $request->get('sort', 'meta_score'),
            'order'           => $request->get('order', 'desc'),
            'min_partidas'    => (int) $request->get('min_partidas', 5),
            'blade'           => $request->get('blade'),
            'over_blade'      => $request->get('over_blade'),
            'assist_blade'    => $request->get('assist_blade'),
            'ratchet'         => $request->get('ratchet'),
            'bit'             => $request->get('bit'),
            'fecha_inicio'    => $request->get('fecha_inicio'),
            'fecha_fin'       => $request->get('fecha_fin'),
            'tipo_evento'     => $request->get('tipo_evento'),
            'region_id'       => $request->get('region_id'),
            'only_user_parts' => $request->has('only_user_parts') && Auth::check(),
        ];

        $viewData = $beyLabService->getBeybladeStatsDashboard(
            $filters,
            Auth::check() ? (int) Auth::id() : null
        );

        return view('inicio.stats', $viewData);
    }

    /**
     * GRÁFICO DE EVOLUCIÓN MENSUAL DE UN COMBO (WINRATE % Y VOLUMEN POR MES)
     */
    public function getComboTrend(Request $request, BeyLabService $beyLabService)
    {
        $result = $beyLabService->getComboTrend(
            $request->query('blade'),
            $request->query('ratchet'),
            $request->query('bit')
        );

        return response()->json($result);
    }

    /**
     * MÓDULO CARA A CARA ENTRE JUGADORES (PLAYER VS PLAYER H2H)
     */
    public function getPlayerH2H(Request $request, BeyLabService $beyLabService)
    {
        $p1Id = (int) $request->query('p1');
        $p2Id = (int) $request->query('p2');

        $result = $beyLabService->getPlayerH2H($p1Id, $p2Id);

        return response()->json($result['data'], $result['status']);
    }

    /**
     * TELEMETRÍA COMPLETA DE UN TORNEO (CON PIEZAS CRUDAS PARA BEY-LAB Y TIMELINE REPLAY DE CADA PARTIDA)
     */
    public function getTournamentStatsDetail($eventId, BeyLabService $beyLabService)
    {
        return response()->json($beyLabService->getTournamentStatsDetail($eventId));
    }

    /**
     * ANALIZADOR BEY-LAB PRO WEB
     */
    public function analyzeWebLab(Request $request, BeyLabService $beyLabService)
    {
        $payload = [
            'mi_deck'      => $request->input('mi_deck', []),
            'rival_deck'   => $request->input('rival_deck', []),
            'mode'         => $request->input('mode', 'global_vs_global'),
            'tipo_evento'  => $request->input('tipo_evento'),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin'    => $request->input('fecha_fin'),
        ];

        return response()->json(
            $beyLabService->analyzeWebDeck($payload, Auth::check() ? (int) Auth::id() : null)
        );
    }

    public function separateStats(Request $request)
    {
        $sort  = $request->input('sort', 'blade');
        $order = strtolower((string) $request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';

        $bladeSortableColumns   = ['blade', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];
        $ratchetSortableColumns = ['ratchet', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];
        $bitSortableColumns     = ['bit', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];

        $bladeSort   = in_array($sort, $bladeSortableColumns, true) ? $sort : 'blade';
        $ratchetSort = in_array($sort, $ratchetSortableColumns, true) ? $sort : 'ratchet';
        $bitSort     = in_array($sort, $bitSortableColumns, true) ? $sort : 'bit';

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
            ->where(function ($query) use ($inicioMesAnterior, $finMesAnterior) {
                $query->where(function ($q) use ($inicioMesAnterior, $finMesAnterior) {
                    $q->whereNotNull('tournament_results.event_id')
                      ->whereBetween('events.date', [$inicioMesAnterior, $finMesAnterior]);
                })->orWhere(function ($q) use ($inicioMesAnterior, $finMesAnterior) {
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
        $allowedSorts = [
            'blade',
            'ratchet',
            'bit',
            'total_victorias',
            'total_derrotas',
            'puntos_ganados_por_combate',
            'puntos_perdidos_por_combate',
            'total_partidas',
            'percentage_victories',
            'eficiencia',
        ];
        $sort          = in_array($request->get('sort'), $allowedSorts, true) ? $request->get('sort') : 'blade';
        $order         = strtolower((string) $request->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
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
