<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all = Event::all();
        $hoy = Carbon::today();

        $bladers = Profile::orderBy('points', 'DESC')->paginate(10);
        $antiguos = $all->where("date", "<", Carbon::now());
        $nuevos = $all->where("date", ">=", Carbon::now());

        return view('inicio.index', compact('bladers', 'nuevos', 'antiguos'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function events()
    {
        $all = Event::all();
        $hoy = Carbon::today();

        $antiguos = $all->where("date", "<", Carbon::now());
        $nuevos = $all->where("date", ">=", Carbon::now());

        return view('inicio.events', compact('nuevos', 'antiguos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function show(c $c)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function edit(c $c)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, c $c)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\c  $c
     * @return \Illuminate\Http\Response
     */
    public function destroy(c $c)
    {
        //
    }
}
