<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = Plan::all()->map(function($plan) {
            switch($plan->slug) {
                case 'bronce':
                    $plan->features = [
                        'Nombre destacado en color bronce',
                        'Subtítulo personal (10 opciones)',
                        'Copas de torneos ganados',
                        'Rol exclusivo en Discord',
                        'Recolor de avatares',
                        'Marcos y fondos exclusivos'
                    ];
                    break;
                case 'plata':
                    $plan->features = [
                        'Todo lo del Nivel 1',
                        'Nombre destacado en color plata azulado',
                        'Subtítulo personal (15 opciones)',
                        'Copas de torneos ganados y especiales',
                        'Prioridad en la revisión de un torneo',
                        'Chat privado en Discord',
                        'Emote para Discord/Twitch'
                    ];
                    break;
                case 'oro':
                    $plan->features = [
                        'Todo lo del Nivel 2',
                        'Nombre destacado en color oro',
                        'Subtítulo personal abierto',
                        'Prioridad en la revisión de dos torneos',
                        'Invitación a una BeyTalk',
                        'Avatar, marco y perfil personalizado'
                    ];
                    break;
            }
            return $plan;
        });

        return view('planes.index', compact('plans'));
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
