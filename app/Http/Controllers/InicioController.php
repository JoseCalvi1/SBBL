<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Profile;
use App\Models\TournamentResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $all = Event::orderBy('date', 'DESC')->get();
        $hoy = Carbon::today();

        $bladers = Profile::orderBy('points_x1', 'DESC')->paginate(5);
        $stamina = Profile::where('user_id', 1)->first();
        $antiguos = $all->where("date", "<", Carbon::now())->take(10);
        $nuevos = $all->where("date", ">=", Carbon::now()->subDays(1))->sortBy('date')->take(3);

        // Obtener el user_id con la media más alta de puntos_ganados / puntos_perdidos
        // Obtener el user_id con la media más alta del mes anterior
        // Obtener el mes y año del mes anterior
        $lastMonth = Carbon::now()->month - 1;
        $lastYear = Carbon::now()->subMonth()->year;
        // Obtener el mes anterior en español
        $lastMonthName = strtoupper(Carbon::now()->subMonth()->translatedFormat('F'));

        // Obtener el user_id con la mayor cantidad de puntos ganados en total
        $bestUser = TournamentResult::select('user_id', DB::raw('SUM(puntos_ganados) as total_puntos'))
            ->whereMonth('updated_at', $lastMonth)
            ->whereYear('updated_at', $lastYear)
            ->groupBy('user_id')
            ->orderByDesc('total_puntos')
            ->first();



        $bestUserProfile = User::find($bestUser->user_id ?? 1);

        if ($bestUser) {
            $bestUserRecord = TournamentResult::where('user_id', $bestUser->user_id)
                ->whereMonth('updated_at', $lastMonth)
                ->whereYear('updated_at', $lastYear)
                ->orderBy(DB::raw('puntos_ganados / puntos_perdidos'), 'desc')
                ->first();
        } else {
            $bestUserRecord = null;
        }

        return view('inicio.index', compact('bladers', 'stamina', 'nuevos', 'antiguos', 'bestUserProfile', 'bestUserRecord', 'bestUser', 'lastMonthName', 'lastYear'));
    }

    public function entrevistas()
    {
        return view('inicio.entrevistas');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function events()
    {
        $events = Event::with('region')->get();
        if(Auth::user()) {
            $createEvent = Event::where('created_by', Auth::user()->id)->where('date', '>', Carbon::now())->get();
            $countEvents = count($createEvent);
        } else {
            $countEvents = 2;
        }

        return view('inicio.events', compact('events', 'countEvents'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function contact()
    {
        return view('inicio.contact');
    }

    public function sendMail(Request $request) {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rules()
    {
        return view('inicio.rules');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        return view('inicio.privacy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function combo()
    {
        return view('inicio.combo');
    }
}
