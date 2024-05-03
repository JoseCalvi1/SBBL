<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Region;
use App\Models\Versus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $bladers = Profile::where('points_x1', '<>', 0)
                   ->orWhere('points_s3', '<>', 0)
                   ->orderBy('id', 'ASC')
                   ->get();

        return view('profiles.index', compact('bladers'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAdmin()
    {
        $profiles = Profile::orderBy('points_s3', 'DESC')->get();

        return view('profiles.indexAdmin', compact('profiles'));
    }

    public function indexAdminX()
    {
        $profiles = Profile::orderBy('points_x1', 'DESC')->get();

        return view('profiles.indexAdminX', compact('profiles'));
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
        $versus = Versus::where('status', null)->where(
           function($query) use ($profile) {
             return $query->where('user_id_1', '=' , $profile->user_id)->orWhere('user_id_2', '=' , $profile->user_id);
            })->get();

        return view('profiles.show', compact('profile','versus'));
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
            'fondo' => 'required',
            'marco' => 'required',
        ]);

        // Si el usuario sube una imagen
        if($request['imagen'])
        {
            $ruta_imagen = $request['imagen']->store('upload-profiles', 'public');

            $array_imagen = ['imagen' => $ruta_imagen];

        } elseif ($request['default_img'])
        {
            $array_imagen = ['imagen' => 'upload-profiles/'.$request['default_img'].'.png'];
        }

        // Asignar nombre
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
        return redirect()->action('App\Http\Controllers\ProfileController@show', $profile);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Profile  $profile
     * @return \Illuminate\Http\Response
     */
    public function updatePoints(Request $request, Profile $profile)
    {

        // Validar
        $data = request()->validate([
            'points_s3' => 'required',
        ]);

        // Asignar los valores
        $profile->points_s3 = $data['points_s3'];

        $profile->save();

        $profiles = Profile::orderBy('id', 'ASC')->get();

        return view('profiles.indexAdmin', compact('profiles'));
    }

    public function updatePointsX(Request $request, Profile $profile)
    {

        // Validar
        $data = request()->validate([
            'points_x1' => 'required',
        ]);

        // Asignar los valores
        $profile->points_x1 = $data['points_x1'];

        $profile->save();

        $profiles = Profile::orderBy('id', 'ASC')->get();

        return view('profiles.indexAdminX', compact('profiles'));
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

    public function ranking()
    {
        $bladers_s3 = Profile::orderBy('points_s3', 'DESC')->where('points_s3', '!=', 0)->get();
        $bladers_x1 = Profile::orderBy('points_x1', 'DESC')->where('points_x1', '!=', 0)->get();

        return view('profiles.ranking', compact('bladers_s3', 'bladers_x1'));
    }
}
