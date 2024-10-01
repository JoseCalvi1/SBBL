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
        $user = Auth::user();

    // Eliminar resultados existentes del usuario para este evento
    TournamentResult::where('user_id', $user->id)
                    ->where('event_id', $eventId)
                    ->delete();
    // Guardar los nuevos resultados
    foreach ($request->blade as $index => $blade) {
        TournamentResult::create([
            'user_id' => $user->id,
            'event_id' => $eventId,
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

$beybladeStats = DB::table('tournament_results')
    ->select(
        'blade',
        'ratchet',
        'bit',
        DB::raw('SUM(victorias) as total_victorias'),
        DB::raw('SUM(derrotas) as total_derrotas'),
        DB::raw('CASE
            WHEN SUM(victorias) > 0 THEN SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)
            ELSE 0
        END AS puntos_ganados_por_combate'),
        DB::raw('CASE
            WHEN SUM(derrotas) > 0 THEN SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1)
            ELSE 0
        END AS puntos_perdidos_por_combate'),
        DB::raw('SUM(victorias + derrotas) as total_partidas'),
        DB::raw('CASE
            WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100
            ELSE 0
        END AS percentage_victories'),
        DB::raw('CASE
            WHEN (SUM(victorias) + SUM(derrotas)) > 0 THEN
                (
                    (
                        (SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) /
                        ((SUM(puntos_ganados) / GREATEST(SUM(victorias), 1)) + (SUM(puntos_perdidos) / GREATEST(SUM(derrotas), 1)))
                    )
                    *
                    ((SUM(victorias) / GREATEST(SUM(victorias + derrotas), 1)) * 100)
                )
                * LOG(SUM(victorias + derrotas) + 1)
            ELSE 0
        END AS eficiencia')
    )
    ->where('blade', 'NOT LIKE', '%Selecciona%')
    ->groupBy('blade', 'ratchet', 'bit')
    ->orderBy($sort, $order)
    ->get();

return view('inicio.stats', compact('beybladeStats', 'sort', 'order'));

}






}
