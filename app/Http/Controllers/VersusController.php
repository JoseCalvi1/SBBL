<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profile;
use App\Models\User;
use App\Models\Versus;

class VersusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $versus = Versus::all();

        return view('versus.index', compact('versus'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_all()
    {
        $versus = Versus::all();

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
        // Validación
        $data = $request->validate([
            'user_id_1' => 'required',
            'user_id_2' => 'required',
            'winner' => 'required',
            'event_id' => 'required',
        ]);

        // Almacenar datos en la BD (sin modelos)
        DB::table('versus')->insert([
            'user_id_1' => $data['user_id_1'],
            'user_id_2' => $data['user_id_2'],
            'winner' => $data['winner'],
            'event_id' => $data['event_id'],
            'url' => $request['url'],
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
}
