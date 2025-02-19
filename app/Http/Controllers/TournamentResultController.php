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
    $sort = $request->get('sort', 'blade');
    $order = $request->get('order', 'asc');

    // Filtros
    $bladeFilter = $request->get('blade');
    $ratchetFilter = $request->get('ratchet');
    $bitFilter = $request->get('bit');
    $userPartsFilter = $request->get('only_user_parts') == 'on';

    // Obtener opciones únicas y ordenarlas alfabéticamente
    $blades = DB::table('tournament_results')->distinct()->pluck('blade')->sort();
    $ratchets = DB::table('tournament_results')->distinct()->pluck('ratchet')->sort();
    $bits = DB::table('tournament_results')->distinct()->pluck('bit')->sort();

    $beybladeStats = DB::table('tournament_results')
        ->select(
            'blade',
            'ratchet',
            'bit',
            DB::raw('SUM(victorias) as total_victorias'),
            DB::raw('SUM(derrotas) as total_derrotas'),
            DB::raw('CASE WHEN SUM(victorias) > 0 THEN SUM(puntos_ganados) / GREATEST(SUM(victorias), 1) ELSE 0 END AS puntos_ganados_por_combate'),
            DB::raw('CASE WHEN SUM(derrotas) > 0 THEN SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1) ELSE 0 END AS puntos_perdidos_por_combate'),
            DB::raw('SUM(victorias + derrotas) as total_partidas'),
            DB::raw('CASE WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100 ELSE 0 END AS percentage_victories'),
            DB::raw('CASE WHEN (SUM(victorias) + SUM(derrotas)) > 0 THEN (((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) / ((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) + (SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1)))) * ((SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100)) * LOG(SUM(victorias + derrotas) + 1) ELSE 0 END AS eficiencia')
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
        ->when($userPartsFilter, function ($query) {
            return $query->where('user_id', Auth::user()->id);
        })
        ->groupBy('blade', 'ratchet', 'bit')
        ->havingRaw('SUM(victorias + derrotas) >= 10')
        ->orderBy($sort, $order)
        ->get();

    return view('inicio.stats', compact('beybladeStats', 'sort', 'order', 'bladeFilter', 'ratchetFilter', 'bitFilter', 'blades', 'ratchets', 'bits', 'userPartsFilter'));
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
    // Obtener la fecha actual
    $fecha_actual = now();

    // Calcular el mes y el año del mes anterior
    $mes_anterior = $fecha_actual->month - 1; // Restar un mes

    // Ajustar el año si el mes es 0 (es decir, si estamos en enero y necesitamos diciembre del año anterior)
    if ($mes_anterior === 0) {
        $mes_anterior = 12; // Diciembre
        $anio_anterior = $fecha_actual->year - 1; // Año anterior
    } else {
        $anio_anterior = $fecha_actual->year; // Mismo año
    }

    // Consultar el ranking
    $ranking = DB::table('tournament_results')
        ->join('users', 'tournament_results.user_id', '=', 'users.id')
        ->select(
            'users.name',
            DB::raw('SUM(tournament_results.puntos_ganados) as total_puntos_ganados'),
            DB::raw('SUM(tournament_results.puntos_perdidos) as total_puntos_perdidos'),
            DB::raw('SUM(tournament_results.puntos_ganados + tournament_results.puntos_perdidos) as total_participacion'),
            DB::raw('(SUM(tournament_results.puntos_ganados) / (SUM(tournament_results.puntos_ganados) + SUM(tournament_results.puntos_perdidos))) * 100 as porcentaje_ganados')
        )
        ->whereMonth('tournament_results.created_at', $mes_anterior) // Filtrar por mes anterior
        ->whereYear('tournament_results.created_at', $anio_anterior) // Filtrar por año
        ->groupBy('users.id', 'users.name')
        ->orderByDesc('total_participacion') // Ordenar por la suma de puntos_ganados + puntos_perdidos
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
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                DB::raw('CASE WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100 ELSE 0 END AS percentage_victories'),
                DB::raw('CASE WHEN (SUM(victorias) + SUM(derrotas)) > 0 THEN (((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) / ((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) + (SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1)))) * ((SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100)) * LOG(SUM(victorias + derrotas) + 1) ELSE 0 END AS eficiencia')
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
            ->groupBy('blade', 'ratchet', 'bit')
            ->havingRaw('SUM(victorias + derrotas) >= 10')
            ->orderBy($sort, $order)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $beybladeStats,
        ], 200);
    }
}
