<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Profile;
use App\Models\Team;
use App\Models\TournamentResult;
use App\Models\User;
use App\Models\Versus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $hoy = Carbon::today();

        $bladers = Profile::orderBy('points_x2', 'DESC')->limit(5)->get();
        $stamina = Profile::where('user_id', 1)->first();
        $antiguos = Event::where('date', '<', now())
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
        $nuevos = Event::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('date', 'asc')
            ->get();


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
        ];

        $usuarios = User::whereIn('id', [1, 3, 4, 182, 13, 215, 310])->get()->map(function ($usuario) use ($subtitulos) {
            // Asignar el subtítulo personalizado desde el arreglo
            $usuario->titulo = $subtitulos[$usuario->id] ?? 'Staff';
            return $usuario;
        });


        $teams = Team::orderBy('points_x2', 'desc')
             ->take(3)
             ->get();

        // Últimos 5 artículos (excepto borradores)
        $articles = Article::where('article_type', '!=', 'Borrador')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();


    return view('inicio.index', compact(
        'usuarios', 'bladers', 'stamina', 'nuevos', 'antiguos', 'bestUserProfile',
        'bestUserRecord', 'bestUser', 'lastMonthName', 'teams', 'articles'
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
        $events = Event::select('id', 'date', 'city', 'region_id', 'mode', 'beys')
            ->with(['region:id,name']) // Solo traer el id y name de la región
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

    public function enviar(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'motivo' => 'required|string',
            'mensaje' => 'required|string',
        ]);

        Mail::send([], [], function ($message) use ($datos) {
            $message->to('info@sbbl.es')
                ->subject($datos['motivo']) // 👉 El motivo como asunto
                ->setBody("
                    <h3>Nuevo mensaje de contacto</h3>
                    <p><strong>Nombre:</strong> {$datos['nombre']}</p>
                    <p><strong>Email:</strong> {$datos['email']}</p>
                    <p><strong>Mensaje:</strong><br>{$datos['mensaje']}</p>
                ", 'text/html');
        });

        return back()->with('success', 'Tu mensaje ha sido enviado correctamente.');
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

    public function halloffame()
    {
        $burstusers = User::whereIn('id', [4, 18])->get();

        $xusers = User::where('id', 215)->get();

        $nacionalusers2025 = User::whereIn('id', [142, 766, 579])->get();

        // Usuario con más asistencias
        $topAttenderData = DB::table('assist_user_event')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        $topAttender = null;
        if ($topAttenderData) {
            $topAttender = User::with('profile')->find($topAttenderData->user_id);
            $topAttender->total_assists = $topAttenderData->total;
        }

        // Usuario con más primeros puestos
        $topWinnerData = DB::table('assist_user_event')
            ->select('user_id', DB::raw('COUNT(*) as wins'))
            ->where('puesto', 'primero')
            ->groupBy('user_id')
            ->orderByDesc('wins')
            ->first();

        $topWinner = null;
        if ($topWinnerData) {
            $topWinner = User::with('profile')->find($topWinnerData->user_id);
            $topWinner->total_wins = $topWinnerData->wins;
        }

        // Usuario con más duelos jugados
        $duelParticipants = DB::table(function ($query) {
            $query->select('user_id_1 as user_id')->from('versus')
                ->unionAll(
                    DB::table('versus')->select('user_id_2 as user_id')
                );
        }, 'all_users')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        $topDueler = null;
        if ($duelParticipants) {
            $topDueler = User::with('profile')->find($duelParticipants->user_id);
            $topDueler->total_duels = $duelParticipants->total;
        }

        // Usuario con más duelos ganados
        $duelWins = DB::table('versus')
            ->selectRaw("
                CASE
                    WHEN result_1 > result_2 THEN user_id_1
                    WHEN result_2 > result_1 THEN user_id_2
                    ELSE NULL
                END as winner_id
            ")
            ->whereNotNull('result_1')
            ->whereNotNull('result_2');

        $topDuelWinnerData = DB::table(DB::raw("({$duelWins->toSql()}) as winners"))
            ->mergeBindings($duelWins)
            ->select('winner_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('winner_id')
            ->groupBy('winner_id')
            ->orderByDesc('total')
            ->first();

        $topDuelWinner = null;
        if ($topDuelWinnerData) {
            $topDuelWinner = User::with('profile')->find($topDuelWinnerData->winner_id);
            $topDuelWinner->total_wins = $topDuelWinnerData->total;
        }

        // Usuario con más registros en tournament_results
        $topRegisterData = DB::table('tournament_results')
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->first();

        $topRegister = null;
        if ($topRegisterData) {
            $topRegister = User::with('profile')->find($topRegisterData->user_id);
            $topRegister->total_registers = $topRegisterData->total;
        }

        // Usuario con más puntos ganados
        $topPointsData = DB::table('tournament_results')
            ->select('user_id', DB::raw('SUM(puntos_ganados) as total_points'))
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->first();

        $topPoints = null;
        if ($topPointsData) {
            $topPoints = User::with('profile')->find($topPointsData->user_id);
            $topPoints->total_points = $topPointsData->total_points;
        }


        $inicio = Carbon::create(2024, 8, 1);
        $fin = now()->startOfMonth()->subSecond(); // Último segundo del mes anterior

        $mejoresPorMes = DB::table('tournament_results')
            ->join('events', 'events.id', '=', 'tournament_results.event_id')
            ->whereBetween('events.date', [$inicio, $fin])
            ->select(
                'tournament_results.user_id',
                DB::raw("DATE_FORMAT(events.date, '%Y-%m') as mes"),
                DB::raw("SUM(tournament_results.puntos_ganados) as total_puntos")
            )
            ->groupBy('tournament_results.user_id', 'mes')
            ->get()
            ->groupBy('mes')
            ->sortKeys() // <- Ordena los meses de forma cronológica descendente
            ->map(function ($grupo) {
                $top = $grupo->sortByDesc('total_puntos')->first();
                $user = \App\Models\User::with('profile')->find($top->user_id);
                $user->total_puntos = $top->total_puntos;
                return $user;
            });


        return view('inicio.halloffame', compact(
            'topAttender',
            'topWinner',
            'topDueler',
            'topDuelWinner',
            'topRegister',
            'topPoints',
            'mejoresPorMes',
            'burstusers',
            'xusers',
            'nacionalusers2025'
        ));
    }

    public function resumen_semanal()
    {
        $hoy = Carbon::now();
        $hace_7_dias = $hoy->copy()->subDays(7);

        // Eventos en los últimos 7 días
        $eventos = Event::with('region')
            ->whereBetween('date', [$hace_7_dias->toDateString(), $hoy->toDateString()])
            ->get();


        // Participantes con puesto 1, 2 o 3 en esos eventos
        $eventos_ids = $eventos->pluck('id');

        $participantes = DB::table('assist_user_event')
            ->whereIn('event_id', $eventos_ids)
            ->whereIn('puesto', ['primero', 'segundo', 'tercero'])
            ->join('users', 'assist_user_event.user_id', '=', 'users.id')
            ->select('assist_user_event.event_id', 'users.name as user_name', 'assist_user_event.puesto')
            ->get()
            ->groupBy('event_id');

        // Duelos en los últimos 7 días
        $duelos = DB::table('versus')
            ->whereBetween('versus.created_at', [$hace_7_dias, $hoy])
            ->join('users as u1', 'versus.user_id_1', '=', 'u1.id')
            ->join('users as u2', 'versus.user_id_2', '=', 'u2.id')
            ->select('versus.*', 'u1.name as user1_name', 'u2.name as user2_name')
            ->get();

        // Duelos en los últimos 7 días
        $duelosEquipo = DB::table('teams_versus')
            ->whereBetween('teams_versus.created_at', [$hace_7_dias, $hoy])
            ->join('teams as t1', 'teams_versus.team_id_1', '=', 't1.id')
            ->join('teams as t2', 'teams_versus.team_id_2', '=', 't2.id')
            ->select('teams_versus.*', 't1.name as team1_name', 't2.name as team2_name')
            ->get();

        return view('inicio.resumen_semanal', compact('eventos', 'participantes', 'duelos', 'duelosEquipo'));
    }


    public function anuncios()
    {
        return view('inicio.anuncios');
    }

    public function sendAnuncio(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'mention' => 'nullable|string',
        ]);

        $mention = $request->mention;
        $mentionText = '';

        // Si el valor del select no es "none", añadimos la mención
        if ($mention !== 'none') {
            // Para @everyone usamos directamente la mención estándar
            if ($mention === '875324662010228746') {
                $mentionText = '@everyone ';
            } else {
                // En caso de que más adelante añadas roles específicos
                $mentionText = "<@&{$mention}> ";
            }
        }

        try {
            Http::post(config('services.discord.announcements'), [
                'content' => $mentionText, // 👈 aquí va la mención
                'embeds' => [[
                    'title' => '📢 Nuevo anuncio de SBBL',
                    'description' => $request->message,
                    'color' => hexdec('ffcc00'),
                    'timestamp' => now()->toIso8601String(),
                ]],
                'allowed_mentions' => [
                    // Si se selecciona none, no se permite ninguna mención
                    'parse' => $mention === 'none' ? [] : ['everyone', 'roles'],
                    'roles' => $mention !== 'none' && $mention !== '875324662010228746' ? [$mention] : [],
                ],
            ]);

            return back()->with('success', 'Anuncio enviado a Discord correctamente ✅');
        } catch (\Exception $e) {
            Log::error('Error al enviar anuncio a Discord: ' . $e->getMessage());
            return back()->with('error', 'No se pudo enviar el anuncio a Discord ❌');
        }
    }


}
