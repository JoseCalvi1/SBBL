<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TournamentResult;
use App\Models\Profile;
use App\Models\Region;
use App\Models\Versus;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                   ->orWhere('imagen', '<>', null)
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
        $invitacionesPendientes = Invitation::where('user_id', auth()->id())->get();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $versus = Versus::orderBy('id', 'DESC')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where(
           function($query) use ($profile) {
             return $query->where('user_id_1', '=' , $profile->user_id)->orWhere('user_id_2', '=' , $profile->user_id);
            })
            ->get();
    
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
        ->where('user_id', auth()->id())
        ->groupBy('blade', 'ratchet', 'bit')
        ->get();

        return view('profiles.show', compact('profile','versus','invitacionesPendientes', 'beybladeStats'));
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
        $avatarOptions = [
            'DranSwordBase' => 'upload-profiles/DranSwordBase.png',
            'DranDaggerBase' => 'upload-profiles/DranDaggerBase.png',
            'DranBusterBase' => 'upload-profiles/DranBusterBase.png',
            'HellScytheBase' => 'upload-profiles/HellScytheBase.png',
            'HellsChainBase' => 'upload-profiles/HellsChainBase.png',
            'HellsHammerBase' => 'upload-profiles/HellsHammerBase.png',
            'KnightShieldBase' => 'upload-profiles/KnightShieldBase.png',
            'KnightLanceBase' => 'upload-profiles/KnightLanceBase.png',
            'LeonClawBase' => 'upload-profiles/LeonClawBase.png',
            'PhoenixFeatherBase' => 'upload-profiles/PhoenixFeatherBase.png',
            'PhoenixWingBase' => 'upload-profiles/PhoenixWingBase.png',
            'RhinoShieldBase' => 'upload-profiles/RhinoShieldBase.png',
            'SharkEdgeBase' => 'upload-profiles/SharkEdgeBase.png',
            'SphinxCowlBase' => 'upload-profiles/SphinxCowlBase.png',
            'TuskMammoth' => 'upload-profiles/TuskMammoth.png',
            'TyrannoBeatBase' => 'upload-profiles/TyrannoBeatBase.png',
            'UnicornStingBase' => 'upload-profiles/UnicornStingBase.png',
            'ViperTailBase' => 'upload-profiles/ViperTailBase.png',
            'WizardArrowBase' => 'upload-profiles/WizardArrowBase.png',
            'WizardRodBase' => 'upload-profiles/WizardRodBase.png',
            'WyvernGaleBase' => 'upload-profiles/WyvernGaleBase.png',
            'AeroPegasus' => 'upload-profiles/AeroPegasus.png',
            'BlackShell' => 'upload-profiles/BlackShell.png',
            'CobaltDragoon' => 'upload-profiles/CobaltDragoon.png',
            'ShinobiShadow' => 'upload-profiles/ShinobiShadow.png',
            'WeissTiger' => 'upload-profiles/WeissTiger.png',

            'BurnFugiwara' => 'upload-profiles/BurnFugiwara.png',
            'ChoPan' => 'upload-profiles/ChoPan.png',
            'EkusuKurosu' => 'upload-profiles/EkusuKurosu.png',
            'Kadovar' => 'upload-profiles/Kadovar.png',
            'KamenX' => 'upload-profiles/KamenX.png',
            'KazamiBird' => 'upload-profiles/KazamiBird.png',
            'KazamiBird2' => 'upload-profiles/KazamiBird2.png',
            'KingManju' => 'upload-profiles/KingManju.png',
            'MeikoMaiden' => 'upload-profiles/MeikoMaiden.png',
            'MultiNanario' => 'upload-profiles/MultiNanario.png',
            'MultiNanario2' => 'upload-profiles/MultiNanario2.png',
            'MultiNanario3' => 'upload-profiles/MultiNanario3.png',
            'ToguroOkunaga' => 'upload-profiles/ToguroOkunaga.png',
            'YuniNamba' => 'upload-profiles/YuniNamba.png',
            'ZonamosNekoyama' => 'upload-profiles/ZonamosNekoyama.png',
        ];
        $marcoOptions = [
            'BaseBlack.png' => 'upload-profiles/Marcos/BaseBlack.png',
            'BaseBlue.png' => 'upload-profiles/Marcos/BaseBlue.png',
            'BaseDBlue.png' => 'upload-profiles/Marcos/BaseDBlue.png',
            'BaseDGreen.png' => 'upload-profiles/Marcos/BaseDGreen.png',
            'BaseGreen.png' => 'upload-profiles/Marcos/BaseGreen.png',
            'BaseOrange.png' => 'upload-profiles/Marcos/BaseOrange.png',
            'BasePink.png' => 'upload-profiles/Marcos/BasePink.png',
            'BasePurple.png' => 'upload-profiles/Marcos/BasePurple.png',
            'BaseRed.png' => 'upload-profiles/Marcos/BaseRed.png',
            'BaseTeal.png' => 'upload-profiles/Marcos/BaseTeal.png',
            'BaseWhite.png' => 'upload-profiles/Marcos/BaseWhite.png',
            'BaseYellow.png' => 'upload-profiles/Marcos/BaseYellow.png',
            'BaseAttack.png' => 'upload-profiles/Marcos/BaseAttack.png',
            'BaseBalance.png' => 'upload-profiles/Marcos/BaseBalance.png',
            'BaseDefense.png' => 'upload-profiles/Marcos/BaseDefense.png',
            'BaseStamina.png' => 'upload-profiles/Marcos/BaseStamina.png',
        ];
        $fondoOptions = [
            'FondoBaseBlue.png' => 'upload-profiles/Fondos/FondoBaseBlue.png',
            'FondoBaseGreen.png' => 'upload-profiles/Fondos/FondoBaseGreen.png',
            'FondoBaseRed.png' => 'upload-profiles/Fondos/FondoBaseRed.png',
            'FondoBaseYellow.png' => 'upload-profiles/Fondos/FondoBaseYellow.png',
            'FondoATK.png' => 'upload-profiles/Fondos/FondoATK.png',
            'FondoDEF.png' => 'upload-profiles/Fondos/FondoDEF.png',
            'FondoBAL.png' => 'upload-profiles/Fondos/FondoBAL.png',
            'FondoSTA.png' => 'upload-profiles/Fondos/FondoSTA.png',
            // Add more options as needed
        ];

        return view('profiles.edit', compact('profile', 'regions', 'regionT', 'avatarOptions', 'marcoOptions', 'fondoOptions'));
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
