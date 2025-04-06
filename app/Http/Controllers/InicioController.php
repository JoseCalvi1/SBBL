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

        // Fechas del mes pasado
        $now = Carbon::now();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth()->toDateString();

        // Nombre del mes anterior
        $lastMonthName = strtoupper(Carbon::now()->subMonth()->translatedFormat('F'));

        // Subquery para traer la fecha real dependiendo del tipo de resultado
        $resultsWithRealDate = TournamentResult::select(
                'tournament_results.user_id',
                'tournament_results.puntos_ganados',
                DB::raw("
                    CASE
                        WHEN tournament_results.event_id IS NOT NULL THEN events.date
                        WHEN tournament_results.versus_id IS NOT NULL THEN versus.created_at
                        ELSE NULL
                    END as real_date
                ")
            )
            ->leftJoin('events', 'tournament_results.event_id', '=', 'events.id')
            ->leftJoin('versus', 'tournament_results.versus_id', '=', 'versus.id')
            ->where(function($query) use ($startOfLastMonth, $endOfLastMonth) {
                $query->where(function($q) use ($startOfLastMonth, $endOfLastMonth) {
                    $q->whereNotNull('tournament_results.event_id')
                    ->whereBetween('events.date', [$startOfLastMonth, $endOfLastMonth]);
                })->orWhere(function($q) use ($startOfLastMonth, $endOfLastMonth) {
                    $q->whereNotNull('tournament_results.versus_id')
                    ->whereBetween('versus.created_at', [$startOfLastMonth, $endOfLastMonth]);
                });
            });

        // Ahora usamos este subquery como base para calcular el mejor usuario
        $bestUser = DB::table(DB::raw("({$resultsWithRealDate->toSql()}) as sub"))
            ->mergeBindings($resultsWithRealDate->getQuery())
            ->select('user_id', DB::raw('SUM(puntos_ganados) as total_puntos'))
            ->groupBy('user_id')
            ->orderByDesc('total_puntos')
            ->first();

        // Obtener perfil y mejor resultado
        $bestUserProfile = User::find($bestUser->user_id ?? 1);

        if ($bestUser) {
            $bestUserRecord = TournamentResult::select(
                    'tournament_results.*',
                    DB::raw("
                        CASE
                            WHEN tournament_results.event_id IS NOT NULL THEN events.date
                            WHEN tournament_results.versus_id IS NOT NULL THEN versus.created_at
                            ELSE NULL
                        END as real_date
                    ")
                )
                ->leftJoin('events', 'tournament_results.event_id', '=', 'events.id')
                ->leftJoin('versus', 'tournament_results.versus_id', '=', 'versus.id')
                ->where('tournament_results.user_id', $bestUser->user_id)
                ->where(function($query) use ($startOfLastMonth, $endOfLastMonth) {
                    $query->where(function($q) use ($startOfLastMonth, $endOfLastMonth) {
                        $q->whereNotNull('tournament_results.event_id')
                        ->whereBetween('events.date', [$startOfLastMonth, $endOfLastMonth]);
                    })->orWhere(function($q) use ($startOfLastMonth, $endOfLastMonth) {
                        $q->whereNotNull('tournament_results.versus_id')
                        ->whereBetween('versus.created_at', [$startOfLastMonth, $endOfLastMonth]);
                    });
                })
                ->orderBy(DB::raw('puntos_ganados / puntos_perdidos'), 'desc')
                ->first();
        } else {
            $bestUserRecord = null;
        }


        $subtitulos = [
            1 => 'Co-Fundador',
            3 => 'Co-Fundador',
            4 => 'Co-Fundador',
            301 => 'Community manager',
            182 => 'Relaciones Públicas',
            513 => 'Editor',
            310 => 'Árbitro/Editor',
        ];

        $usuarios = User::whereIn('id', [1, 3, 4, 182, 13, 228, 215, 307, 301, 310, 513])->get()->map(function ($usuario) use ($subtitulos) {
            // Asignar el subtítulo personalizado desde el arreglo
            $usuario->titulo = $subtitulos[$usuario->id] ?? 'Árbitro';
            return $usuario;
        });

        // Obtener cantidad de usuarios por comunidad autónoma
        $usuariosPorComunidad = Profile::with('region')
        ->select('region_id', DB::raw('COUNT(*) as total'))
        //->where('points_x1', '>', 0)
        ->groupBy('region_id')
        ->get()
        ->map(function ($item) {
            return [
                'comunidad_autonoma' => $item->region->name ?? 'Desconocida',
                'total' => $item->total
            ];
        });

    return view('inicio.index', compact(
        'usuarios', 'bladers', 'stamina', 'nuevos', 'antiguos', 'bestUserProfile',
        'bestUserRecord', 'bestUser', 'lastMonthName', 'usuariosPorComunidad'
    ));
    }

    public function entrevistas()
    {
        return view('inicio.entrevistas');
    }

    public function nacional()
    {
        return view('inicio.nacional');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function events()
    {
        if(Auth::user()) {
            $createEvent = Event::where('created_by', Auth::user()->id)->where('date', '>', now())->get();
            $countEvents = count($createEvent);
        } else {
            $countEvents = 2;
        }

        return view('inicio.events', compact('countEvents'));
    }

    public function fetchEvents(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $events = Event::with('region')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return response()->json($events);
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

    public function suscriptions()
    {
        return view('inicio.subscriptions');
    }

    public function dashboard()
    {
        return view('inicio.dashboard');
    }
}
