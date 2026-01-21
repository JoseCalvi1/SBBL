<?php

namespace App\Http\Controllers;

use App\Models\TournamentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TournamentResultController extends Controller
{
    public function store(Request $request, $eventId)
    {
        // Eliminar resultados existentes del usuario para este evento
        // Cambiar el 'user_id' por el 'assistId' para borrar los resultados del dueño correcto del deck
        foreach ($request->blade as $assistId => $blades) {
            TournamentResult::where('user_id', $assistId)
                            ->where('event_id', $eventId)
                            ->delete();
        }

        // Guardar los nuevos resultados
        foreach ($request->blade as $assistId => $blades) {
            foreach ($blades as $index => $blade) {
                TournamentResult::create([
                    'user_id' => $assistId,  // Cambiar por el 'assistId' para guardar con el propietario del deck
                    'event_id' => $eventId,
                    'blade' => $blade,
                    'assist_blade' => $request->assist_blade[$assistId][$index],
                    'ratchet' => $request->ratchet[$assistId][$index],
                    'bit' => $request->bit[$assistId][$index],
                    'victorias' => $request->victorias[$assistId][$index],
                    'derrotas' => $request->derrotas[$assistId][$index],
                    'puntos_ganados' => $request->puntos_ganados[$assistId][$index],
                    'puntos_perdidos' => $request->puntos_perdidos[$assistId][$index],
                ]);
            }
        }

        return redirect()->back()->with('success', 'Resultados guardados correctamente.');
    }


    public function storeduel(Request $request, $versusId)
    {
        $user = Auth::user();
    // Eliminar resultados existentes del usuario para este evento
    TournamentResult::where('user_id', $user->id)
                    ->where('versus_id', $versusId)
                    ->delete();

                    // Guardar los nuevos resultados
    foreach ($request->blade as $index => $blade) {
        TournamentResult::create([
            'user_id' => $user->id,
            'versus_id' => $versusId,
            'blade' => $blade,
            'assist_blade' => $request->assist_blade[$index],
            'ratchet' => $request->ratchet[$index],
            'bit' => $request->bit[$index],
            'victorias' => $request->victorias[$index],
            'derrotas' => $request->derrotas[$index],
            'puntos_ganados' => $request->puntos_ganados[$index],
            'puntos_perdidos' => $request->puntos_perdidos[$index],
        ]);
    }

    return redirect()->back()->with('success', 'Resultados guardados correctamente.');
    }

public function beybladeStats(Request $request)
{
    // 1. Valores por defecto y Filtros Simples
    $sort = $request->get('sort', 'percentage_victories');
    $order = $request->get('order', 'desc');
    $minPartidas = $request->get('min_partidas', 10);
    $fechaInicio = $request->get('fecha_inicio'); // Nuevo: Capturar fecha
    $fechaFin = $request->get('fecha_fin');       // Nuevo: Capturar fecha

    // 2. Listas para los selects
    $parts = DB::table('tournament_results')
        ->select('blade', 'assist_blade', 'ratchet', 'bit')
        ->distinct()
        ->get();

    $blades = $parts->pluck('blade')->unique()->sort()->values();
    $assistBlades = $parts->pluck('assist_blade')->unique()
        ->filter(fn($v) => $v !== '-- Selecciona un assist blade --')
        ->sort()->values();
    $ratchets = $parts->pluck('ratchet')->unique()->sort()->values();
    $bits = $parts->pluck('bit')->unique()->sort()->values();

    // 3. Query Principal
    $query = DB::table('tournament_results')
        ->select(
            'blade',
            DB::raw("CASE WHEN assist_blade LIKE '%Selecciona%' OR assist_blade IS NULL THEN '-' ELSE assist_blade END as assist_blade"),
            'ratchet',
            'bit',
            DB::raw('SUM(victorias) as total_victorias'),
            DB::raw('SUM(derrotas) as total_derrotas'),
            DB::raw('SUM(victorias) + SUM(derrotas) as total_partidas'),

            // Win Rate
            DB::raw('CASE WHEN SUM(victorias) + SUM(derrotas) > 0 THEN (SUM(victorias) / (SUM(victorias) + SUM(derrotas))) * 100 ELSE 0 END AS percentage_victories'),

            // Puntos Promedio (Necesarios para el detalle)
            DB::raw('CASE WHEN SUM(victorias) > 0 THEN SUM(puntos_ganados) / GREATEST(SUM(victorias), 1) ELSE 0 END AS puntos_ganados_por_combate'),
            DB::raw('CASE WHEN SUM(derrotas) > 0 THEN SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1) ELSE 0 END AS puntos_perdidos_por_combate'),

            // Eficiencia
            DB::raw('CASE WHEN (SUM(victorias) + SUM(derrotas)) > 0 THEN (((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) / ((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) + (SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1)))) * ((SUM(victorias) / GREATEST(SUM(victorias) + SUM(derrotas), 1)) * 100)) * LOG(SUM(victorias) + SUM(derrotas) + 1) ELSE 0 END AS eficiencia')
        )
        ->where('blade', 'NOT LIKE', '%Selecciona%');

    // 4. Aplicar Filtros
    if ($request->filled('blade')) $query->where('blade', $request->blade);
    if ($request->filled('assist_blade')) $query->where('assist_blade', $request->assist_blade); // Filtro opcional si quisieras usarlo
    if ($request->filled('ratchet')) $query->where('ratchet', $request->ratchet);
    if ($request->filled('bit')) $query->where('bit', $request->bit);

    // Filtros de fecha añadidos
    if ($request->filled('fecha_inicio')) $query->whereDate('created_at', '>=', $fechaInicio);
    if ($request->filled('fecha_fin')) $query->whereDate('created_at', '<=', $fechaFin);

    if ($request->has('only_user_parts') && Auth::check()) $query->where('user_id', Auth::id());

    // 5. Agrupación y Ejecución
    $beybladeStats = $query
        ->groupBy('blade', 'assist_blade', 'ratchet', 'bit')
        ->havingRaw('(SUM(victorias) + SUM(derrotas)) >= ?', [$minPartidas])
        ->orderBy($sort, $order)
        ->get();

    return view('inicio.stats', compact(
        'beybladeStats', 'sort', 'order', 'minPartidas',
        'blades', 'assistBlades', 'ratchets', 'bits',
        'fechaInicio', 'fechaFin' // Pasamos las fechas a la vista
    ));
}

    public function separateStats(Request $request)
    {
        // Obtener los parámetros de ordenación de la URL
        $sort = $request->input('sort', 'blade'); // Campo por defecto 'blade'
        $order = $request->input('order', 'asc'); // Orden por defecto 'asc'

        // Definir columnas ordenables para cada parte
        $bladeSortableColumns = ['blade', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];
        $ratchetSortableColumns = ['ratchet', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];
        $bitSortableColumns = ['bit', 'total_victorias', 'total_derrotas', 'total_partidas', 'percentage_victories'];

        // Ajustar el sort para cada tipo
        $bladeSort = in_array($sort, $bladeSortableColumns) ? $sort : 'blade';
        $ratchetSort = in_array($sort, $ratchetSortableColumns) ? $sort : 'ratchet';
        $bitSort = in_array($sort, $bitSortableColumns) ? $sort : 'bit';

        // Estadísticas de Blades
        $bladeStats = DB::table('tournament_results')
            ->select(
                'blade',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE
                    WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100
                    ELSE 0
                END AS percentage_victories')
            )
            ->where('blade', 'NOT LIKE', '%Selecciona%')
            ->groupBy('blade')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($bladeSort, $order) // Usar el campo de ordenación correcto
            ->get();

        // Estadísticas de Ratchets
        $ratchetStats = DB::table('tournament_results')
            ->select(
                'ratchet',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE
                    WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100
                    ELSE 0
                END AS percentage_victories')
            )
            ->where('ratchet', 'NOT LIKE', '%Selecciona%')
            ->groupBy('ratchet')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($ratchetSort, $order) // Usar el campo de ordenación correcto
            ->get();

        // Estadísticas de Bits
        $bitStats = DB::table('tournament_results')
            ->select(
                'bit',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE
                    WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100
                    ELSE 0
                END AS percentage_victories')
            )
            ->where('bit', 'NOT LIKE', '%Selecciona%')
            ->groupBy('bit')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($bitSort, $order) // Usar el campo de ordenación correcto
            ->get();

        return view('inicio.separate_stats', [
            'bladeStats' => $bladeStats,
            'ratchetStats' => $ratchetStats,
            'bitStats' => $bitStats,
            'order' => $order, // Pasar el orden actual a la vista
        ]);
    }


    public function showRanking()
    {
        // Obtener el rango del mes anterior
        $fecha_actual = now();
        $inicioMesAnterior = $fecha_actual->copy()->subMonth()->startOfMonth()->toDateString();
        $finMesAnterior = $fecha_actual->copy()->subMonth()->endOfMonth()->toDateString();

        // Subconsulta base con fecha real
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

        // Agrupar y calcular el ranking
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

        // Retornar la vista con el ranking
        return view('inicio.rankingstats', ['ranking' => $ranking]);
    }


    public function getBeybladeStats(Request $request)
    {
        $sort = $request->get('sort', 'blade');
        $order = $request->get('order', 'asc');

        // Filtros
        $bladeFilter = $request->get('blade');
        $ratchetFilter = $request->get('ratchet');
        $bitFilter = $request->get('bit');

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
            ->when($bladeFilter, function ($query) use ($bladeFilter) {
                return $query->where('blade', $bladeFilter);
            })
            ->when($ratchetFilter, function ($query) use ($ratchetFilter) {
                return $query->where('ratchet', $ratchetFilter);
            })
            ->when($bitFilter, function ($query) use ($bitFilter) {
                return $query->where('bit', $bitFilter);
            })
            ->groupBy('blade', 'assist_blade', 'ratchet', 'bit')
            ->havingRaw('SUM(victorias) + SUM(derrotas) >= 10')
            ->orderBy($sort, $order)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $beybladeStats,
        ], 200);
    }
}
