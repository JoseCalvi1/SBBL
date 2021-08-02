<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Region;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function show(Profile $profile)
    {
        $region = Region::find($profile->region_id);

        return view('profiles.show', compact('profile', 'region'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function edit(Profile $profile)
    {
        $regionT = Region::find($profile->region_id);
        $regions = Region::all();

        return view('profiles.edit', compact('profile', 'regions', 'regionT'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile);

        // Validar
        $data = request()->validate([
            'nombre' => 'required',
            'region_id' => 'required',
        ]);

        // Si el usuario sube una imagen
        if($request['imagen'])
        {
            $ruta_imagen = $request['imagen']->store('upload-profiles', 'public');

            $array_imagen = ['imagen' => $ruta_imagen];
        }

        // Asignar nombre y url
        auth()->user()->name = $data['nombre'];
        auth()->user()->save();

        // Eliminar url y name de $data
        unset($data['nombre']);

        // Asignar biografía e imagen
        auth()->user()->profile()->update( array_merge(
                $data,
                $array_imagen ?? []
            )
        );

        // Guardar información

        return redirect()->action('App\Http\Controllers\InicioController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function destroy(Profile $profile)
    {
        //
    }
}
