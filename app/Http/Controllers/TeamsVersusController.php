<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Models\Team;
use App\Models\TeamsVersus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TeamsVersusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $versus = TeamsVersus::whereDate('created_at', '>=', '2025-09-01')
            ->orderBy('id', 'DESC')
            ->get();

        return view('teams_versus.index', compact('versus'));

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $versus = TeamsVersus::orderBy('id', 'DESC')
            ->where('status', 'CLOSED')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->get();

        return view('teams_versus.all', compact('versus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $teams = Team::orderBy('name')->get();

        return view('teams_versus.create', compact('teams'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function store(Request $request)
{
    $team_id_1 = $request->input('team_id_1');
    $team_id_2 = $request->input('team_id_2');

    $primer_dia_mes_actual = Carbon::now()->startOfMonth();
    $ultimo_dia_mes_actual = Carbon::now()->endOfMonth();

    $se_enfrentaron = TeamsVersus::where(function($query) use ($team_id_1, $team_id_2) {
        $query->where(function($q) use ($team_id_1, $team_id_2) {
            $q->where('team_id_1', $team_id_1)
              ->where('team_id_2', $team_id_2);
        })
        ->orWhere(function($q) use ($team_id_1, $team_id_2) {
            $q->where('team_id_1', $team_id_2)
              ->where('team_id_2', $team_id_1);
        });
    })
    ->whereBetween('created_at', [$primer_dia_mes_actual, $ultimo_dia_mes_actual])
    ->where('matchup', $request->input('modalidad'))
    ->exists();

    if ($se_enfrentaron) {
        return redirect()->back()->with('error', 'Estos equipos ya se han enfrentado una vez este mes');
    }

    $data = $request->validate([
        'team_id_1' => 'required',
        'team_id_2' => 'required',
        'result_1' => 'required',
        'result_2' => 'required',
        'modalidad' => 'required',
    ]);

    DB::table('teams_versus')->insert([
        'team_id_1' => $data['team_id_1'],
        'team_id_2' => $data['team_id_2'],
        'result_1' => $data['result_1'],
        'result_2' => $data['result_2'],
        'matchup' => $data['modalidad'],
        'status' => 'OPEN',
        'created_at' => Carbon::now(),
    ]);

    // 🔁 Redirige en lugar de renderizar
    return redirect()->route('teams_versus.all')
        ->with('success', 'Duelo añadido correctamente');
}


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(TeamsVersus $duel)
    {
        $teams = Team::all();

        return view('teams_versus.edit', compact('duel', 'teams'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TeamsVersus $duel)
    {
        // Validación
        $data = $request->validate([
            'team_id_1' => 'required',
            'team_id_2' => 'required',
            'result_1' => 'required|numeric',
            'result_2' => 'required|numeric'
        ]);

        // Asignar los valores
        $duel->team_id_1 = $data['team_id_1'];
        $duel->team_id_2 = $data['team_id_2'];
        $duel->result_1 = $data['result_1'];
        $duel->result_2 = $data['result_2'];

        $duel->save();

        // Redireccionar
        return redirect()->action('App\Http\Controllers\TeamsVersusController@index');
    }


    public function versus()
    {
        $teamsVersus = TeamsVersus::where('status', '!=' , null)->orderBy('created_at', 'DESC')->get();

        $create = TeamsVersus::where('status', '!=' , null)
                        ->where('team_id_1', '=', Auth::user()->team_id)
                        ->orWhere('team_id_2', '=', Auth::user()->team_id)
                        ->orderBy('created_at', 'DESC')
                        ->first();

        $diasDiferencia = 6;
        if($create) {
            $hoy = Carbon::parse(Carbon::now());
            $duel = Carbon::parse($create->created_at);
            $diasDiferencia = $duel->diffInDays($hoy);
        }

        return view('generations.versus', compact('teamsVersus', 'diasDiferencia'));
    }


    public function puntuarDuelo(Request $request, $id, $mode, $winner)
    {
        $duel = TeamsVersus::findOrFail($id);
        $duel->status = 'CLOSED';
        $duel->save();

        // Determinar el equipo ganador basado en los resultados del duelo
        $winnerId = ($duel->result_1 > $duel->result_2) ? $duel->team_id_1 : $duel->team_id_2;

        // Modificar el modo según la condición
        $mode = 'points_x2';

        // Incrementar los puntos al equipo ganador
        DB::table('teams')
            ->where('id', $winnerId)
            ->increment($mode, 1);

        return redirect()->back()->with('success', 'Puntuaciones actualizadas correctamente');
    }

}
