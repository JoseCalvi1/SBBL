<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Models\User;
use App\Models\Versus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VersusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $versus = Versus::orderBy('id', 'DESC')->get();

        return view('versus.index', compact('versus'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all()
    {
        $versus = Versus::orderBy('id', 'DESC')->where('status', 'CLOSED')->get();

        return view('versus.all', compact('versus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $events = Event::orderBy('id', 'DESC')->get();

        return view('versus.create', compact('users', 'events'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_id_1 = $request->input('user_id_1');
        $user_id_2 = $request->input('user_id_2');

        $primer_dia_mes_actual = Carbon::now()->startOfMonth(); // Obtener el primer día del mes actual
        $ultimo_dia_mes_actual = Carbon::now()->endOfMonth();   // Obtener el último día del mes actual

        $se_enfrentaron = Versus::where(function($query) use ($user_id_1, $user_id_2) {
            $query->where(function($q) use ($user_id_1, $user_id_2) {
                $q->where('user_id_1', $user_id_1)
                ->where('user_id_2', $user_id_2);
            })
            ->orWhere(function($q) use ($user_id_1, $user_id_2) {
                $q->where('user_id_1', $user_id_2)
                ->where('user_id_2', $user_id_1);
            });
        })
        ->whereBetween('created_at', [$primer_dia_mes_actual, $ultimo_dia_mes_actual])
        ->where('matchup', $request->input('modalidad'))
        ->exists();

        if ($se_enfrentaron) {
            return redirect()->back()->with('error', 'Estos jugadores ya se han enfrentado una vez este mes');
        }

        // Validación
        $data = $request->validate([
            'user_id_1' => 'required',
            'user_id_2' => 'required',
            'result_1' => 'required',
            'result_2' => 'required',
            'modalidad' => 'required',
        ]);

        // Almacenar datos en la BD (sin modelos)
        DB::table('versus')->insert([
            'user_id_1' => $data['user_id_1'],
            'user_id_2' => $data['user_id_2'],
            'result_1' => $data['result_1'],
            'result_2' => $data['result_2'],
            'matchup' => $request['modalidad'],
            'status' => 'OPEN',
            'created_at' => Carbon::now(),
        ]);

        return redirect()->action('App\Http\Controllers\VersusController@index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Versus $duel)
    {
        $users = User::all();

        return view('versus.edit', compact('duel', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Versus $duel)
    {
        // Validación
        $data = $request->validate([
            'user_id_1' => 'required',
            'user_id_2' => 'required',
            'winner' => 'required',
        ]);

        // Asignar los valores
        $duel->user_id_1 = $data['user_id_1'];
        $duel->user_id_2 = $data['user_id_2'];
        $duel->winner = $data['winner'];
        $duel->url = $request['url'];

        $duel->save();

        // Redireccionar
        return redirect()->action('App\Http\Controllers\VersusController@index');
    }

// SBBL Generations

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generation()
    {
        $bladers = Profile::orderBy('points_g1', 'DESC')->paginate(20);

        return view('generations.index', compact('bladers'));
    }

    public function versus()
    {
        $versus = Versus::where('status', '!=' , null)->orderBy('created_at', 'DESC')->get();

        $create = Versus::where('status', '!=' , null)
                        ->where('user_id_1', '=', Auth::user()->id)
                        ->orWhere('user_id_2', '=', Auth::user()->id)
                        ->orderBy('created_at', 'DESC')
                        ->first();

        $diasDiferencia = 6;
        if($create) {
            $hoy = Carbon::parse(Carbon::now());
            $duelo = Carbon::parse($create->created_at);
            $diasDiferencia = $duelo->diffInDays($hoy);
        }

        return view('generations.versus', compact('versus', 'diasDiferencia'));
    }

    public function gcreate()
    {
        $create = Versus::where('status', '!=' , null)
                        ->where('user_id_1', '=', Auth::user()->id)
                        ->orWhere('user_id_2', '=', Auth::user()->id)
                        ->orderBy('created_at', 'DESC')
                        ->first();

        $users = User::where('id', '!=', Auth::user()->id)->get();

        return view('generations.create', compact('users'));
    }

    public function gstore(Request $request)
    {
        // Validación
        $data = $request->validate([
            'user_id_1' => 'required',
            'user_id_2' => 'required',
        ]);

        $input = array("Single/Dual/God", "Remake/Cho Z", "GT", "Sparking", "DB/BU");
        $input2 = array("Single/Dual/God", "Remake/Cho Z", "GT", "Sparking", "DB/BU");
        shuffle($input);
        shuffle($input2);
        $rand_keys = array_rand($input, 5);
        $rand_keys2 = array_rand($input2, 5);
        $matchup = $input[$rand_keys[0]] . " vs " . $input2[$rand_keys2[0]]. "<br>".
        $input[$rand_keys[1]] . " vs " . $input2[$rand_keys2[1]]. "<br>".
        $input[$rand_keys[2]] . " vs " . $input2[$rand_keys2[2]]. "<br>".
        $input[$rand_keys[3]] . " vs " . $input2[$rand_keys2[3]]. "<br>".
        $input[$rand_keys[4]] . " vs " . $input2[$rand_keys2[4]]. "<br>";

        // Almacenar datos en la BD (sin modelos)
        DB::table('versus')->insert([
            'user_id_1' => $data['user_id_1'],
            'user_id_2' => $data['user_id_2'],
            'matchup' => $matchup,
            'status' => 'Abierto',
            'created_at' => Carbon::now(),
        ]);

        return redirect()->action('App\Http\Controllers\VersusController@versus');
    }

    public function gedit(Versus $versus)
    {
        $users = User::where('id', '=', $versus->versus_1->id)->orWhere('id', '=', $versus->versus_2->id)->get();

        return view('generations.edit', compact('versus', 'users'));
    }

    public function gupdate(Request $request, Versus $versus)
    {
        if($request['complete'] == 'complete') {
            $versus->status = 'Cerrado';
            $user = Profile::where('user_id', '=', $versus->winner)->first();
            $user->points_g1 = $user->points_g1+1;

            $versus->save();
            $user->save();
        } else {
            // Validación
            $data = $request->validate([
                'winner' => 'required',
            ]);

            // Asignar los valores
            $versus->winner = $data['winner'];
            $versus->result = $request['result'];
            $versus->status = 'Pendiente';

            $versus->save();
        }

        // Redireccionar
        return redirect()->action('App\Http\Controllers\VersusController@versus');
    }

    public function puntuarDuelo(Request $request, $id, $mode, $winner)
    {
        $duel = Versus::findOrFail($id);
        $duel->status = 'CLOSED';
        $duel->save();

        $mode = ($mode == "beybladex") ? 'points_x1' : 'points_s3';

        DB::table('profiles')
            ->where('user_id', $winner)
            ->increment($mode, 1);

        return redirect()->back()->with('success', 'Puntuaciones actualizadas correctamente');
    }
}
