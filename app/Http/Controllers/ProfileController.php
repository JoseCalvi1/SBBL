<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        $bladers = Profile::select('profiles.*')
            ->leftJoin('profilestrophies', 'profiles.id', '=', 'profilestrophies.profiles_id')
            ->leftJoin('trophies', 'profilestrophies.trophies_id', '=', 'trophies.id')
            ->addSelect([
                'trophies_count' => DB::table('assist_user_event')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('assist_user_event.user_id', 'profiles.user_id')
                    ->where('assist_user_event.puesto', 'primero')
                    ->where('assist_user_event.event_id', '>=', 190)
            ])
            ->where(function ($query) {
                $query->where('profiles.points_x1', '<>', 0)
                    ->orWhere('profiles.points_s3', '<>', 0)
                    ->orWhereNotNull('profiles.imagen');
            })
            ->orderByRaw("
                CASE
                    WHEN trophies.name = 'SUSCRIPCIÓN NIVEL 3' THEN 1
                    WHEN trophies.name = 'SUSCRIPCIÓN NIVEL 2' THEN 2
                    WHEN trophies.name = 'SUSCRIPCIÓN NIVEL 1' THEN 3
                    ELSE 4
                END, profiles.id ASC
            ")
            ->get();

        // Asignar clase CSS según la suscripción
        foreach ($bladers as $blader) {
            $firstTrophyName = $blader->trophies->first()->name ?? '';
            switch ($firstTrophyName) {
                case 'SUSCRIPCIÓN NIVEL 3':
                    $blader->subscription_class = 'suscripcion-nivel-3';
                    break;
                case 'SUSCRIPCIÓN NIVEL 2':
                    $blader->subscription_class = 'suscripcion-nivel-2';
                    break;
                case 'SUSCRIPCIÓN NIVEL 1':
                    $blader->subscription_class = 'suscripcion-nivel-1';
                    break;
                default:
                    $blader->subscription_class = '';
                    break;
            }
        }

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
    public function show(Profile $profile, Request $request)
    {
        $invitacionesPendientes = Invitation::where('user_id', auth()->id())->get();

        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);

        // Obtener los duelos del usuario en el mes seleccionado
        $versus = Versus::orderBy('id', 'DESC')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where(function($query) use ($profile) {
                return $query->where('user_id_1', '=' , $profile->user_id)
                            ->orWhere('user_id_2', '=' , $profile->user_id);
            })
            ->get();

        // Obtener los eventos en los que ha participado el usuario en el mes seleccionado
        $eventos = Event::join('assist_user_event', 'events.id', '=', 'assist_user_event.event_id')
            ->where('assist_user_event.user_id', $profile->user_id)
            ->whereMonth('events.date', $currentMonth)
            ->whereYear('events.date', $currentYear)
            ->orderBy('events.date', 'DESC')
            ->get(['events.*']); // Seleccionamos todas las columnas de la tabla `events`

        return view('profiles.show', compact('profile', 'versus', 'eventos', 'invitacionesPendientes', 'currentMonth', 'currentYear'));
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
            'Base/AeroPegasus_1.webp' => 'upload-profiles/Base/AeroPegasus_1.webp',
            'Base/BiteCroc_1.webp' => 'upload-profiles/Base/BiteCroc_1.webp',
            'Base/BlackShell.webp' => 'upload-profiles/Base/BlackShell.webp',
            'Base/BurnFugiwara_2.webp' => 'upload-profiles/Base/BurnFugiwara_2.webp',
            'Base/ChoPan_2.webp' => 'upload-profiles/Base/ChoPan_2.webp',
            'Base/ChromeRyugu.webp' => 'upload-profiles/Base/ChromeRyugu.webp',
            'Base/CobaltDragoon.webp' => 'upload-profiles/Base/CobaltDragoon.webp',
            'Base/CrimsonGaruda.webp' => 'upload-profiles/Base/CrimsonGaruda.webp',
            'Base/DranBuster.webp' => 'upload-profiles/Base/DranBuster.webp',
            'Base/DranDagger.webp' => 'upload-profiles/Base/DranDagger.webp',
            'Base/DranSword.webp' => 'upload-profiles/Base/DranSword.webp',
            'Base/EkusuKurosu_2.webp' => 'upload-profiles/Base/EkusuKurosu_2.webp',
            'Base/GhostCircle.webp' => 'upload-profiles/Base/GhostCircle.webp',
            'Base/HellChain.webp' => 'upload-profiles/Base/HellChain.webp',
            'Base/HellHammer.webp' => 'upload-profiles/Base/HellHammer.webp',
            'Base/HellScythe.webp' => 'upload-profiles/Base/HellScythe.webp',
            'Base/ImpactDrake.webp' => 'upload-profiles/Base/ImpactDrake.webp',
            'Base/Kadovar_2.webp' => 'upload-profiles/Base/Kadovar_2.webp',
            'Base/KamenX_2.webp' => 'upload-profiles/Base/KamenX_2.webp',
            'Base/KaminariShieru.webp' => 'upload-profiles/Base/KaminariShieru.webp',
            'Base/KaruraKonjiki.webp' => 'upload-profiles/Base/KaruraKonjiki.webp',
            'Base/KazamiBird_2.webp' => 'upload-profiles/Base/KazamiBird_2.webp',
            'Base/KazamiBird2_2.webp' => 'upload-profiles/Base/KazamiBird2_2.webp',
            'Base/KingManju_2.webp' => 'upload-profiles/Base/KingManju_2.webp',
            'Base/KnightLance.webp' => 'upload-profiles/Base/KnightLance.webp',
            'Base/KnightMail.webp' => 'upload-profiles/Base/KnightMail.webp',
            'Base/KnightShield.webp' => 'upload-profiles/Base/KnightShield.webp',
            'Base/LeonCrest.webp' => 'upload-profiles/Base/LeonCrest.webp',
            'Base/MeikoMaiden_2.webp' => 'upload-profiles/Base/MeikoMaiden_2.webp',
            'Base/MultiNanario_2.webp' => 'upload-profiles/Base/MultiNanario_2.webp',
            'Base/MultiNanario2_2.webp' => 'upload-profiles/Base/MultiNanario2_2.webp',
            'Base/MultiNanario3_2.webp' => 'upload-profiles/Base/MultiNanario3_2.webp',
            'Base/PhoenixFeather.webp' => 'upload-profiles/Base/PhoenixFeather.webp',
            'Base/PhoenixRudder.webp' => 'upload-profiles/Base/PhoenixRudder.webp',
            'Base/PhoenixWing.webp' => 'upload-profiles/Base/PhoenixWing.webp',
            'Base/PteraSwing.webp' => 'upload-profiles/Base/PteraSwing.webp',
            'Base/QueenManju.webp' => 'upload-profiles/Base/QueenManju.webp',
            'Base/RexJura.webp' => 'upload-profiles/Base/RexJura.webp',
            'Base/RhinoHorn.webp' => 'upload-profiles/Base/RhinoHorn.webp',
            'Base/RoarTyranno_1.webp' => 'upload-profiles/Base/RoarTyranno_1.webp',
            'Base/SamuraiSaber.webp' => 'upload-profiles/Base/SamuraiSaber.webp',
            'Base/SavageBear.webp' => 'upload-profiles/Base/SavageBear.webp',
            'Base/SharkEdge.webp' => 'upload-profiles/Base/SharkEdge.webp',
            'Base/ShiguruNanairo.webp' => 'upload-profiles/Base/ShiguruNanairo.webp',
            'Base/ShinobiShadow_1.webp' => 'upload-profiles/Base/ShinobiShadow_1.webp',
            'Base/SilverWolf_1.webp' => 'upload-profiles/Base/SilverWolf_1.webp',
            'Base/SphinxCowl.webp' => 'upload-profiles/Base/SphinxCowl.webp',
            'Base/TalonPtera_1.webp' => 'upload-profiles/Base/TalonPtera_1.webp',
            'Base/TenkaShiroboshi.webp' => 'upload-profiles/Base/TenkaShiroboshi.webp',
            'Base/ToguroOkunaga_2.webp' => 'upload-profiles/Base/ToguroOkunaga_2.webp',
            'Base/TuskMammoth_1.webp' => 'upload-profiles/Base/TuskMammoth_1.webp',
            'Base/TyrannoBeat.webp' => 'upload-profiles/Base/TyrannoBeat.webp',
            'Base/UnicornSting.webp' => 'upload-profiles/Base/UnicornSting.webp',
            'Base/WeissTiger.webp' => 'upload-profiles/Base/WeissTiger.webp',
            'Base/WhaleWave.webp' => 'upload-profiles/Base/WhaleWave.webp',
            'Base/WizardArrow.webp' => 'upload-profiles/Base/WizardArrow.webp',
            'Base/WizardRod.webp' => 'upload-profiles/Base/WizardRod.webp',
            'Base/WyvernGale.webp' => 'upload-profiles/Base/WyvernGale.webp',
            'Base/YellKong_1.webp' => 'upload-profiles/Base/YellKong_1.webp',
            'Base/YuniNamba_2.webp' => 'upload-profiles/Base/YuniNamba_2.webp',
            'Base/ZonamosNekoya.webp' => 'upload-profiles/Base/ZonamosNekoya.webp',
            'Base/HellsReaper.webp' => 'upload-profiles/Base/HellsReaper.webp',
            'Base/RhinoReaper.webp' => 'upload-profiles/Base/RhinoReaper.webp',
            'Base/ScorpioSpear.webp' => 'upload-profiles/Base/ScorpioSpear.webp',
            'Base/TriceraPress.webp' => 'upload-profiles/Base/TriceraPress.webp',
        ];

        $bronzeAvatars = [
            'BRONCE/BearScratch.webp' => 'upload-profiles/BRONCE/BearScratch.webp',
            'BRONCE/BlackKnightLance.webp' => 'upload-profiles/BRONCE/BlackKnightLance.webp',
            'BRONCE/BlackSharkEdge.webp' => 'upload-profiles/BRONCE/BlackSharkEdge.webp',
            'BRONCE/BlackShinobiShadow.webp' => 'upload-profiles/BRONCE/BlackShinobiShadow.webp',
            'BRONCE/BlackSphinxCowl.webp' => 'upload-profiles/BRONCE/BlackSphinxCowl.webp',
            'BRONCE/BlackViperTail.webp' => 'upload-profiles/BRONCE/BlackViperTail.webp',
            'BRONCE/BlackWhaleWave.webp' => 'upload-profiles/BRONCE/BlackWhaleWave.webp',
            'BRONCE/BlueDranSword.webp' => 'upload-profiles/BRONCE/BlueDranSword.webp',
            'BRONCE/BlueHellChain.webp' => 'upload-profiles/BRONCE/BlueHellChain.webp',
            'BRONCE/BlueHellHammer.webp' => 'upload-profiles/BRONCE/BlueHellHammer.webp',
            'BRONCE/BlueKnightShield.webp' => 'upload-profiles/BRONCE/BlueKnightShield.webp',
            'BRONCE/BluePhoenixWing.webp' => 'upload-profiles/BRONCE/BluePhoenixWing.webp',
            'BRONCE/BlueShinobiShadow.webp' => 'upload-profiles/BRONCE/BlueShinobiShadow.webp',
            'BRONCE/BlueSphinxCowl.webp' => 'upload-profiles/BRONCE/BlueSphinxCowl.webp',
            'BRONCE/BlueViperTail.webp' => 'upload-profiles/BRONCE/BlueViperTail.webp',
            'BRONCE/BlueWizardArrow.webp' => 'upload-profiles/BRONCE/BlueWizardArrow.webp',
            'BRONCE/BrownWizardArrow.webp' => 'upload-profiles/BRONCE/BrownWizardArrow.webp',
            'BRONCE/CyanKnightShield.webp' => 'upload-profiles/BRONCE/CyanKnightShield.webp',
            'BRONCE/BronzeDranSword.webp' => 'upload-profiles/BRONCE/BronzeDranSword.webp',
            'BRONCE/GreenGhostCircle.webp' => 'upload-profiles/BRONCE/GreenGhostCircle.webp',
            'BRONCE/GreenHellScythe.webp' => 'upload-profiles/BRONCE/GreenHellScythe.webp',
            'BRONCE/GreenSharkEdge.webp' => 'upload-profiles/BRONCE/GreenSharkEdge.webp',
            'BRONCE/GreenTyrannoBeat.webp' => 'upload-profiles/BRONCE/GreenTyrannoBeat.webp',
            'BRONCE/GreenWizardArrow.webp' => 'upload-profiles/BRONCE/GreenWizardArrow.webp',
            'BRONCE/GreenWizardRod.webp' => 'upload-profiles/BRONCE/GreenWizardRod.webp',
            'BRONCE/GreyDranSword.webp' => 'upload-profiles/BRONCE/GreyDranSword.webp',
            'BRONCE/GreyPhoenixFeather.webp' => 'upload-profiles/BRONCE/GreyPhoenixFeather.webp',
            'BRONCE/GreyWyvernGale.webp' => 'upload-profiles/BRONCE/GreyWyvernGale.webp',
            'BRONCE/OrangeDranDagger.webp' => 'upload-profiles/BRONCE/OrangeDranDagger.webp',
            'BRONCE/OrangeWizardArrow.webp' => 'upload-profiles/BRONCE/OrangeWizardArrow.webp',
            'BRONCE/OrangeWizardRod.webp' => 'upload-profiles/BRONCE/OrangeWizardRod.webp',
            'BRONCE/PurpleKnightShield.webp' => 'upload-profiles/BRONCE/PurpleKnightShield.webp',
            'BRONCE/PurpleRhinoHorn.webp' => 'upload-profiles/BRONCE/PurpleRhinoHorn.webp',
            'BRONCE/PurpleViperTail.webp' => 'upload-profiles/BRONCE/PurpleViperTail.webp',
            'BRONCE/PurpleWizardArrow.webp' => 'upload-profiles/BRONCE/PurpleWizardArrow.webp',
            'BRONCE/RedDranSword.webp' => 'upload-profiles/BRONCE/RedDranSword.webp',
            'BRONCE/RedKnightShield.webp' => 'upload-profiles/BRONCE/RedKnightShield.webp',
            'BRONCE/RedLeonCLaw.webp' => 'upload-profiles/BRONCE/RedLeonCLaw.webp',
            'BRONCE/RedTyrannoBeat.webp' => 'upload-profiles/BRONCE/RedTyrannoBeat.webp',
            'BRONCE/RedUnicorn.webp' => 'upload-profiles/BRONCE/RedUnicorn.webp',
            'BRONCE/RedWizardArrow.webp' => 'upload-profiles/BRONCE/RedWizardArrow.webp',
            'BRONCE/RedWyvernGale.webp' => 'upload-profiles/BRONCE/RedWyvernGale.webp',
            'BRONCE/WhiteLeonClaw.webp' => 'upload-profiles/BRONCE/WhiteLeonClaw.webp',
            'BRONCE/WhiteSphinxCowl.webp' => 'upload-profiles/BRONCE/WhiteSphinxCowl.webp',
            'BRONCE/WhiteWhaleWave.webp' => 'upload-profiles/BRONCE/WhiteWhaleWave.webp',
            'BRONCE/YellowBlackShell.webp' => 'upload-profiles/BRONCE/YellowBlackShell.webp',
            'BRONCE/YellowHellScythe.webp' => 'upload-profiles/BRONCE/YellowHellScythe.webp',
            'BRONCE/YellowKnightLance.webp' => 'upload-profiles/BRONCE/YellowKnightLance.webp',
            'BRONCE/YellowSharkEdge.webp' => 'upload-profiles/BRONCE/YellowSharkEdge.webp',
            'BRONCE/YellowShinobiShadow_1.webp' => 'upload-profiles/BRONCE/YellowShinobiShadow_1.webp',
            'BRONCE/YellowViperTail.webp' => 'upload-profiles/BRONCE/YellowViperTail.webp',
            'BRONCE/YellowWyvernGale.webp' => 'upload-profiles/BRONCE/YellowWyvernGale.webp',
            'BRONCE/BlackPhoenixRudder.webp' => 'upload-profiles/BRONCE/BlackPhoenixRudder.webp',
            'BRONCE/HellsArc.webp' => 'upload-profiles/BRONCE/HellsArc.webp',
            'BRONCE/WhiteLeonCrest.webp' => 'upload-profiles/BRONCE/WhiteLeonCrest.webp',
            'BRONCE/YellowWhaleWave.webp' => 'upload-profiles/BRONCE/YellowWhaleWave.webp',
        ];

        $silverAvatars = [
            'PLATA/AeroPegasus_2.webp' => 'upload-profiles/PLATA/AeroPegasus_2.webp',
            'PLATA/BlackDragoon.webp' => 'upload-profiles/PLATA/BlackDragoon.webp',
            'PLATA/BlackHellsHammer.webp' => 'upload-profiles/PLATA/BlackHellsHammer.webp',
            'PLATA/BluenYellowPhoenixWing.webp' => 'upload-profiles/PLATA/BluenYellowPhoenixWing.webp',
            'PLATA/BlueSharkEdge.webp' => 'upload-profiles/PLATA/BlueSharkEdge.webp',
            'PLATA/CobaltDrake.webp' => 'upload-profiles/PLATA/CobaltDrake.webp',
            'PLATA/CobaltDran.webp' => 'upload-profiles/PLATA/CobaltDran.webp',
            'PLATA/DranzerS.webp' => 'upload-profiles/PLATA/DranzerS.webp',
            'PLATA/DrigerS.webp' => 'upload-profiles/PLATA/DrigerS.webp',
            'PLATA/GoldHellScythe.webp' => 'upload-profiles/PLATA/GoldHellScythe.webp',
            'PLATA/GoldKnightShield.webp' => 'upload-profiles/PLATA/GoldKnightShield.webp',
            'PLATA/GoldLeonClaw.webp' => 'upload-profiles/PLATA/GoldLeonClaw.webp',
            'PLATA/GoldWizardRod.webp' => 'upload-profiles/PLATA/GoldWizardRod.webp',
            'PLATA/PinkTuskMammoth.webp' => 'upload-profiles/PLATA/PinkTuskMammoth.webp',
            'PLATA/RedDranBuster.webp' => 'upload-profiles/PLATA/RedDranBuster.webp',
            'PLATA/SilverDrake.webp' => 'upload-profiles/PLATA/SilverDrake.webp',
            'PLATA/SilverDranSword.webp' => 'upload-profiles/PLATA/SilverDranSword.webp',
            'PLATA/SushiroDranSword.webp' => 'upload-profiles/PLATA/SushiroDranSword.webp',
            'PLATA/LeonFang.webp' => 'upload-profiles/PLATA/LeonFang.webp',
        ];

        $goldAvatars = [
            'ORO/GoldDranSword.webp' => 'upload-profiles/ORO/GoldDranSword.webp',
            'ORO/BronceTest120.gif' => 'upload-profiles/ORO/BronceTest120.gif',
            'ORO/SilverTest120.gif' => 'upload-profiles/ORO/SilverTest120.gif',
            'ORO/GoldTest120.gif' => 'upload-profiles/ORO/GoldTest120.gif',
            'ORO/JinniusWave.webp' => 'upload-profiles/ORO/JinniusWave.webp',
            'ORO/Extintor.webp' => 'upload-profiles/ORO/Extintor.webp',
            'ORO/AsefronBeelze.webp' => 'upload-profiles/ORO/AsefronBeelze.webp',
            'ORO/ElPibeCalavera.webp' => 'upload-profiles/ORO/ElPibeCalavera.webp',
            'ORO/Arlen.webp' => 'upload-profiles/ORO/Arlen.webp',
            'ORO/Erubita.webp' => 'upload-profiles/ORO/Erubita.webp',
            'ORO/Hyuga.webp' => 'upload-profiles/ORO/Hyuga.webp',
            'ORO/Ritsu.webp' => 'upload-profiles/ORO/Ritsu.webp',
            /*'ORO/Leon.gif' => 'upload-profiles/ORO/Leon.gif',
            'ORO/dragoon.gif' => 'upload-profiles/ORO/dragoon.gif',
            'ORO/beyblade-tyson.gif' => 'upload-profiles/ORO/beyblade-tyson.gif',
            'ORO/aiga.gif' => 'upload-profiles/ORO/aiga.gif',*/
        ];

        $copaLloros = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::user()->id)
            ->where('events.name', 'LIKE', '%lloro%')
            ->exists(); // Devuelve true si hay algún resultado

        $copaLlorosAvatar = [
                'EXC/SWLLOROS.webp' => 'upload-profiles/EXC/SWLLOROS.webp',
                'EXC/WRLLOROS.webp' => 'upload-profiles/EXC/WRLLOROS.webp',
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

        $marcoBronce = [
            'upload-profiles/Marcos/BRONCE/BlackMARCO.webp' => 'upload-profiles/Marcos/BRONCE/BlackMARCO.webp',
            'upload-profiles/Marcos/BRONCE/CBlueMARCO.webp' => 'upload-profiles/Marcos/BRONCE/CBlueMARCO.webp',
            'upload-profiles/Marcos/BRONCE/DBlueMARCO.webp' => 'upload-profiles/Marcos/BRONCE/DBlueMARCO.webp',
            'upload-profiles/Marcos/BRONCE/GreenMARCO.webp' => 'upload-profiles/Marcos/BRONCE/GreenMARCO.webp',
            'upload-profiles/Marcos/BRONCE/LimeMARCO.webp' => 'upload-profiles/Marcos/BRONCE/LimeMARCO.webp',
            'upload-profiles/Marcos/BRONCE/OrangeMARCO.webp' => 'upload-profiles/Marcos/BRONCE/OrangeMARCO.webp',
            'upload-profiles/Marcos/BRONCE/PinkMARCO.webp' => 'upload-profiles/Marcos/BRONCE/PinkMARCO.webp',
            'upload-profiles/Marcos/BRONCE/PurpleMARCO.webp' => 'upload-profiles/Marcos/BRONCE/PurpleMARCO.webp',
            'upload-profiles/Marcos/BRONCE/RedMARCO.webp' => 'upload-profiles/Marcos/BRONCE/RedMARCO.webp',
            'upload-profiles/Marcos/BRONCE/TurquoseMARCO.webp' => 'upload-profiles/Marcos/BRONCE/TurquoseMARCO.webp',
            'upload-profiles/Marcos/BRONCE/WhiteMARCO.webp' => 'upload-profiles/Marcos/BRONCE/WhiteMARCO.webp',
            'upload-profiles/Marcos/BRONCE/YellowMARCO.webp' => 'upload-profiles/Marcos/BRONCE/YellowMARCO.webp',
        ];

        $marcoPlata = [
            'upload-profiles/Marcos/PLATA/Beast1MARCO.webp' => 'upload-profiles/Marcos/PLATA/Beast1MARCO.webp',
            'upload-profiles/Marcos/PLATA/Beast2MARCO.webp' => 'upload-profiles/Marcos/PLATA/Beast2MARCO.webp',
            'upload-profiles/Marcos/PLATA/Pendragon1MARCO.webp' => 'upload-profiles/Marcos/PLATA/Pendragon1MARCO.webp',
            'upload-profiles/Marcos/PLATA/Pendragon2MARCO.webp' => 'upload-profiles/Marcos/PLATA/Pendragon2MARCO.webp',
            'upload-profiles/Marcos/PLATA/Persona1MARCO.webp' => 'upload-profiles/Marcos/PLATA/Persona1MARCO.webp',
            'upload-profiles/Marcos/PLATA/Persona2MARCO.webp' => 'upload-profiles/Marcos/PLATA/Persona2MARCO.webp',
            'upload-profiles/Marcos/PLATA/Yggdrasil1MARCO.webp' => 'upload-profiles/Marcos/PLATA/Yggdrasil1MARCO.webp',
            'upload-profiles/Marcos/PLATA/Yggdrasil2MARCO.webp' => 'upload-profiles/Marcos/PLATA/Yggdrasil2MARCO.webp',
        ];

        $marcoOro = [
            'upload-profiles/Marcos/ORO/BusterMARCO.webp' => 'upload-profiles/Marcos/ORO/BusterMARCO.webp',
            'upload-profiles/Marcos/ORO/HammerMARCO.webp' => 'upload-profiles/Marcos/ORO/HammerMARCO.webp',
            'upload-profiles/Marcos/ORO/MailMARCO.webp' => 'upload-profiles/Marcos/ORO/MailMARCO.webp',
            'upload-profiles/Marcos/ORO/RodMARCO.webp' => 'upload-profiles/Marcos/ORO/RodMARCO.webp',
            'upload-profiles/Marcos/ORO/SaberMARCO.webp' => 'upload-profiles/Marcos/ORO/SaberMARCO.webp',
            'upload-profiles/Marcos/ORO/WaveMARCO.webp' => 'upload-profiles/Marcos/ORO/WaveMARCO.webp',
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

        // Verificar el nivel de suscripción del usuario
        $subscriptionLevel = optional(Auth::user()->profile->trophies->first())->name;

        return view('profiles.edit', compact('profile', 'regions', 'regionT', 'avatarOptions', 'marcoOptions', 'fondoOptions', 'subscriptionLevel', 'bronzeAvatars', 'silverAvatars', 'goldAvatars', 'marcoBronce', 'marcoPlata', 'marcoOro', 'copaLloros', 'copaLlorosAvatar'));
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

        // Validar los datos, asegurándote de que 'custom_option' no sea obligatorio
        $data = request()->validate([
            'nombre' => 'required',
            'region_id' => 'required',
            'fondo' => 'required',
            'marco' => 'required',
            'subtitulo' => 'nullable|string',  // No es obligatorio
        ]);

        // Si el usuario sube una imagen
        if($request['imagen']) {
            $ruta_imagen = $request['imagen']->store('upload-profiles', 'public');
            $array_imagen = ['imagen' => $ruta_imagen];
        } elseif ($request['default_img']) {
            $array_imagen = ['imagen' => 'upload-profiles/'.$request['default_img']];
        }

        // Asignar nombre
        auth()->user()->name = $data['nombre'];
        auth()->user()->save();

        // Eliminar url y name de $data
        unset($data['nombre']);

        // Asignar biografía, imagen y la opción personalizada
        auth()->user()->profile()->update(array_merge(
            $data,
            $array_imagen ?? []
        ));

        // Redirigir al perfil actualizado
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

    public function updateAllPointsX(Request $request)
    {
        // Validar que todos los puntos sean numéricos y opcionales
        $data = $request->validate([
            'points_x1.*' => 'nullable|numeric',
        ]);

        // Iterar sobre los perfiles y actualizar los puntos
        foreach ($request->input('points_x1', []) as $profileId => $points) {
            $profile = Profile::find($profileId);
            if ($profile) {
                $profile->points_x1 = $points;
                $profile->save();
            }
        }

        // Redirigir con mensaje de éxito
        return redirect()->route('profiles.indexAdminX')
            ->with('success', 'Puntos actualizados correctamente');
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

        public function ranking(Request $request)
        {
            // Valores de filtros
            $limit = $request->input('limit', 25); // Valor por defecto: 25
            $region = $request->input('region', null);

            // Consulta para bladers_s3
            $bladers_s3 = Profile::with(['user.teams', 'region'])
                ->when($region, function ($query) use ($region) {
                    return $query->whereHas('region', function ($q) use ($region) {
                        $q->where('name', $region);
                    });
                })
                ->orderBy('points_s3', 'DESC')
                ->where('points_s3', '!=', 0)
                ->take($limit)
                ->get();

            // Consulta para bladers_x1
            $bladers_x1 = Profile::with(['user.teams', 'region'])
                ->when($region, function ($query) use ($region) {
                    return $query->whereHas('region', function ($q) use ($region) {
                        $q->where('name', $region);
                    });
                })
                ->orderBy('points_x1', 'DESC')
                ->where('points_x1', '!=', 0)
                ->take($limit)
                ->get()
                ->map(function ($blader) {
                    // Incluye el logo si existe
                    if ($blader->user->teams->isNotEmpty() && $blader->user->teams->first()->logo) {
                        $blader->team_logo = 'data:image/png;base64,' . $blader->user->teams->first()->logo;
                    } else {
                        $blader->team_logo = null;
                    }
                    return $blader;
                });

            // Obtener lista de regiones para el filtro
            $regions = Region::all()->pluck('name');

            return view('profiles.ranking', compact('bladers_s3', 'bladers_x1', 'regions', 'limit', 'region'));
        }


    public function wrapped(Profile $profile)
    {
        $primerTorneo = DB::table('assist_user_event')
            ->where('user_id', $profile->id)
            ->where('event_id', '>=', 190)
            ->orderBy('id', 'asc')
            ->first();

        if ($primerTorneo) {
            $datosTorneo = Event::with('region')
                ->find($primerTorneo->event_id);
        } else {
            $datosTorneo = null;
        }

        $numeroTorneos = DB::table('assist_user_event')
            ->where('user_id', $profile->id)
            ->whereNotNull('puesto') // Asegura que 'puesto' no sea null
            ->where('puesto', '!=', 'nopresentado')
            ->where('event_id', '>=', 190)
            ->orderBy('id', 'asc')
            ->count();

        $torneosGanados = DB::table('assist_user_event')
            ->where('user_id', $profile->id)
            ->where('puesto', 'primero')
            ->where('event_id', '>=', 190)
            ->orderBy('id', 'asc')
            ->count();

        $mejorCombo = TournamentResult::where('user_id', $profile->id)
            ->select(DB::raw('blade, ratchet, bit, SUM(puntos_ganados) as total_puntos_ganados, SUM(puntos_perdidos) as total_puntos_perdidos'))
            ->groupBy('blade', 'ratchet', 'bit')
            ->orderBy(DB::raw('SUM(victorias)'), 'desc')
            ->first();

        $peorCombo = TournamentResult::where('user_id', $profile->id)
            ->select(DB::raw('blade, ratchet, bit, SUM(puntos_ganados) as total_puntos_ganados, SUM(puntos_perdidos) as total_puntos_perdidos'))
            ->groupBy('blade', 'ratchet', 'bit')
            ->orderBy(DB::raw('SUM(derrotas)'), 'desc')
            ->first();

        return view('profiles.wrapped', compact('profile', 'datosTorneo', 'numeroTorneos', 'torneosGanados', 'mejorCombo', 'peorCombo'));
    }



}
