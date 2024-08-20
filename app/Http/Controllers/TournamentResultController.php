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

    public function beybladeStats()
    {
        $beybladeStats = DB::table('tournament_results')
            ->select(
                'blade',
                'ratchet',
                'bit',
                DB::raw('SUM(victorias) as total_victorias'),
                DB::raw('SUM(derrotas) as total_derrotas'),
                DB::raw('SUM(puntos_ganados) as total_puntos_ganados'),
                DB::raw('SUM(puntos_perdidos) as total_puntos_perdidos'),
                // Calcular el número total de veces utilizado
                DB::raw('SUM(victorias + derrotas) as total_partidas'),
                // Calcular el porcentaje de victorias sobre el total de partidas
                DB::raw('CASE
                    WHEN SUM(victorias + derrotas) > 0 THEN (SUM(victorias) / SUM(victorias + derrotas)) * 100
                    ELSE 0
                END AS percentage_victories')
            )
            ->where('blade', 'NOT LIKE', '%Selecciona%')
            ->groupBy('blade', 'ratchet', 'bit')
            ->get();

        return view('inicio.stats', compact('beybladeStats'));
    }


}
