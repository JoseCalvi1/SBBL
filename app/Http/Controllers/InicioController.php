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

        $bladers = Profile::orderBy('points_x3', 'DESC')->limit(5)->get();
        $stamina = Profile::where('user_id', 1)->first();
        $antiguos = Event::where('date', '<', now())
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();
        $startOfWindow = Carbon::now()->startOfDay();
        $endOfWindow = Carbon::now()->addDays(7)->endOfDay();


        $nuevos = Event::where('date', '>=', $startOfWindow)
                    ->where('date', '<=', $endOfWindow)
                    ->orderBy('date', 'asc')
                    ->get();


        // Blader del Mes (mes anterior cerrado) según puntos ganados en torneos de ranking (points_log)
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonthNoOverflow();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth()->toDateString();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth()->toDateString();

        // Nombre y año del mes anterior
        $lastMonthName = mb_strtoupper($lastMonth->translatedFormat('F'), 'UTF-8');
        $lastYear = $lastMonth->year;

        $bestUser = DB::table('points_log')
            ->join('events', 'points_log.event_id', '=', 'events.id')
            ->whereIn('events.beys', ['ranking', 'rankingplus'])
            ->where('points_log.modo', '!=', 'fantasy_reward')
            ->whereBetween('events.date', [$startOfLastMonth, $endOfLastMonth])
            ->select(
                'points_log.user_id',
                DB::raw('SUM(points_log.puntos) as total_puntos'),
                DB::raw("SUM(CASE WHEN LOWER(points_log.puesto) = 'primero' THEN 1 ELSE 0 END) as primeros"),
                DB::raw('COUNT(DISTINCT points_log.event_id) as torneos_jugados')
            )
            ->groupBy('points_log.user_id')
            ->orderByDesc('total_puntos')
            ->orderByDesc('primeros')
            ->orderByDesc('torneos_jugados')
            ->orderBy('points_log.user_id')
            ->first();

        // Obtener perfil y resumen de combates/combo del ganador del mes
        $bestUserProfile = User::with('profile.region')->find($bestUser->user_id ?? 1);
        $bestUserRecord = null;

        if ($bestUser) {
            // 1. Estadísticas reales del mes en el nuevo motor (match_battles en torneos de ranking)
            $totalMonthWins = (int) DB::table('match_battles as mb')
                ->join('tournament_matches as tm', 'mb.tournament_match_id', '=', 'tm.id')
                ->join('events as e', 'tm.event_id', '=', 'e.id')
                ->where('mb.winner_id', $bestUser->user_id)
                ->whereIn('mb.finish_type', ['spin', 'over', 'burst', 'xtreme'])
                ->whereIn('e.beys', ['ranking', 'rankingplus'])
                ->whereBetween('e.date', [$startOfLastMonth, $endOfLastMonth])
                ->count();

            $bestCombo = DB::selectOne("
                SELECT c.blade, c.ratchet, c.bit, COUNT(*) as total_victorias_combo
                FROM match_battles mb
                JOIN tournament_matches tm ON mb.tournament_match_id = tm.id
                JOIN events e ON tm.event_id = e.id
                JOIN tournament_results c ON c.id = CASE
                    WHEN mb.winner_id = tm.player1_id THEN mb.player1_combo_id
                    ELSE mb.player2_combo_id
                END
                WHERE mb.winner_id = ?
                  AND mb.finish_type IN ('spin', 'over', 'burst', 'xtreme')
                  AND e.beys IN ('ranking', 'rankingplus')
                  AND e.date BETWEEN ? AND ?
                  AND c.blade IS NOT NULL
                  AND c.blade NOT LIKE '%Selecciona%'
                  AND c.blade != 'DESCONOCIDO'
                GROUP BY c.blade, c.ratchet, c.bit
                ORDER BY total_victorias_combo DESC
                LIMIT 1
            ", [$bestUser->user_id, $startOfLastMonth, $endOfLastMonth]);

            // Fallback a tournament_results si en ese mes no hubo combates registrados en match_battles
            if ($totalMonthWins === 0 && !$bestCombo) {
                $totalMonthWins = (int) TournamentResult::join('events', 'tournament_results.event_id', '=', 'events.id')
                    ->where('tournament_results.user_id', $bestUser->user_id)
                    ->whereIn('events.beys', ['ranking', 'rankingplus'])
                    ->whereBetween('events.date', [$startOfLastMonth, $endOfLastMonth])
                    ->sum('victorias');

                $bestCombo = TournamentResult::select(
                        'blade', 'ratchet', 'bit',
                        DB::raw('SUM(victorias) as total_victorias_combo')
                    )
                    ->join('events', 'tournament_results.event_id', '=', 'events.id')
                    ->where('tournament_results.user_id', $bestUser->user_id)
                    ->whereIn('events.beys', ['ranking', 'rankingplus'])
                    ->whereBetween('events.date', [$startOfLastMonth, $endOfLastMonth])
                    ->whereNotNull('blade')
                    ->where('blade', 'NOT LIKE', '%Selecciona%')
                    ->groupBy('blade', 'ratchet', 'bit')
                    ->orderByDesc('total_victorias_combo')
                    ->first();
            }

            if ($bestCombo) {
                $bestUserRecord = (object) [
                    'blade' => $bestCombo->blade,
                    'ratchet' => $bestCombo->ratchet,
                    'bit' => $bestCombo->bit,
                    'total_victorias_combo' => (int) ($bestCombo->total_victorias_combo ?? 0),
                    'total_victorias_mes' => $totalMonthWins,
                ];
            } elseif ($totalMonthWins > 0) {
                $bestUserRecord = (object) [
                    'blade' => null,
                    'ratchet' => null,
                    'bit' => null,
                    'total_victorias_combo' => 0,
                    'total_victorias_mes' => $totalMonthWins,
                ];
            }
        }


        $subtitulos = [
            1 => 'Co-Fundador',
            3 => 'Co-Fundador',
            4 => 'Co-Fundador',
        ];

        $usuarios = User::whereIn('id', [1, 3, 4])->get()->map(function ($usuario) use ($subtitulos) {
            // Asignar el subtítulo personalizado desde el arreglo
            $usuario->titulo = $subtitulos[$usuario->id] ?? 'Staff';
            return $usuario;
        });


        $teams = Team::orderBy('points_x3', 'desc')
             ->take(5)
             ->get();

        // Últimos 5 artículos (excepto borradores)
        $articles = Article::where('article_type', '!=', 'Borrador')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();


    return view('inicio.index', compact(
        'usuarios', 'bladers', 'stamina', 'nuevos', 'antiguos', 'bestUserProfile',
        'bestUserRecord', 'bestUser', 'lastMonthName', 'lastYear', 'teams', 'articles'
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
            $countEvents = 0;
            if(Auth::check()) {
                $countEvents = Event::where('created_by', Auth::id())
                    ->where('date', '>', now())
                    ->count();
            } else {
                $countEvents = 2; // Valor por defecto si no hay usuario
            }

            return view('inicio.events', compact('countEvents'));
        }

        public function fetchEvents(Request $request)
        {
            $year = $request->input('year');
            $month = $request->input('month');

            // Validar entradas básicas
            if (!$year || !$month) {
                return response()->json([], 400);
            }

            $events = Event::select('id', 'date', 'city', 'region_id', 'mode', 'beys')
                ->with(['region:id,name'])
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                //->where('status', '!=', 'INVALID') // Opcional: Filtrar inválidos
                ->orderBy('date', 'asc')
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
        // 1. VALIDACIÓN BÁSICA
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email',
            'motivo' => 'required|string',
            'mensaje' => 'required|string',
            'g-recaptcha-response' => 'required' // El campo que envía Google se llama así
        ], [
            'g-recaptcha-response.required' => 'Por favor, marca la casilla "No soy un robot".'
        ]);

        // 2. VERIFICACIÓN MANUAL DEL CAPTCHA CON GOOGLE
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => '6LcShF8sAAAAAC7Ey93Nb3FpGHhUiiQmzjYhr6AQ', // <--- TU CLAVE SECRETA (Empieza por 6L...)
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip() // Opcional, pero recomendado
        ]);

        // Convertimos la respuesta de Google a array/json
        $captchaResult = $response->json();

        // Si 'success' es false, es un bot o falló la verificación
        if (!$captchaResult['success']) {
            return back()->withInput()->withErrors(['g-recaptcha-response' => 'Verificación fallida. ¿Eres un robot?']);
        }

        // 3. SI PASA EL CAPTCHA, ENVIAMOS EL CORREO
        $datos = $request->only('nombre', 'email', 'motivo', 'mensaje');

        Mail::send([], [], function ($message) use ($datos) {
            $message->to('info@sbbl.es')
                ->subject($datos['motivo'])
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
        return redirect('https://sbbl.es/blog/reglamento-s3-v1');
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
        return view('admin.dashboard.index');
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


        $inicio = Carbon::create(2024, 8, 1)->startOfDay();
        $fin = now()->startOfMonth()->subSecond(); // Último segundo del mes anterior
        $v2Start = Carbon::parse(config('sbbl.blader_month_v2_start', '2026-11-01'))->startOfMonth();

        // 1. Histórico intacto anterior a la fecha de corte (tournament_results)
        $legacyEnd = $fin->lt($v2Start) ? $fin : $v2Start->copy()->subSecond();
        $legacyWinners = collect();

        if ($legacyEnd->gte($inicio)) {
            $legacyWinners = DB::table('tournament_results')
                ->join('events', 'events.id', '=', 'tournament_results.event_id')
                ->whereBetween('events.date', [$inicio, $legacyEnd])
                ->select(
                    'tournament_results.user_id',
                    DB::raw("DATE_FORMAT(events.date, '%Y-%m') as mes"),
                    DB::raw("SUM(tournament_results.puntos_ganados) as total_puntos")
                )
                ->groupBy('tournament_results.user_id', 'mes')
                ->get()
                ->groupBy('mes')
                ->map(fn($grupo) => $grupo->sortByDesc('total_puntos')->first());
        }

        // 2. Meses cerrados desde la fecha de corte con las nuevas stats (puntos en torneos de ranking)
        $v2Winners = collect();
        if ($fin->gte($v2Start)) {
            $v2Winners = DB::table('points_log')
                ->join('events', 'events.id', '=', 'points_log.event_id')
                ->whereIn('events.beys', ['ranking', 'rankingplus'])
                ->where('points_log.modo', '!=', 'fantasy_reward')
                ->whereBetween('events.date', [$v2Start->toDateString(), $fin->toDateString()])
                ->select(
                    'points_log.user_id',
                    DB::raw("DATE_FORMAT(events.date, '%Y-%m') as mes"),
                    DB::raw('SUM(points_log.puntos) as total_puntos'),
                    DB::raw("SUM(CASE WHEN LOWER(points_log.puesto) = 'primero' THEN 1 ELSE 0 END) as primeros"),
                    DB::raw('COUNT(DISTINCT points_log.event_id) as torneos_jugados')
                )
                ->groupBy('points_log.user_id', 'mes')
                ->get()
                ->groupBy('mes')
                ->map(function ($grupo) {
                    return $grupo->sort(function ($a, $b) {
                        return ($b->total_puntos <=> $a->total_puntos)
                            ?: ($b->primeros <=> $a->primeros)
                            ?: ($b->torneos_jugados <=> $a->torneos_jugados)
                            ?: ($a->user_id <=> $b->user_id);
                    })->first();
                });
        }

        // 3. Combinamos ambos periodos manteniendo orden cronológico y cargando perfiles en una sola consulta
        $winnersByMonth = $legacyWinners->merge($v2Winners)->sortKeys();
        $winnerUsers = User::with('profile')
            ->whereIn('id', $winnersByMonth->pluck('user_id')->filter()->unique()->values())
            ->get()
            ->keyBy('id');

        $mejoresPorMes = $winnersByMonth->map(function ($top) use ($winnerUsers) {
            $user = $winnerUsers->get($top->user_id);
            if (!$user) {
                return null;
            }
            $cloned = clone $user;
            $cloned->total_puntos = $top->total_puntos;
            return $cloned;
        })->filter();


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

    public function eventstats() {
        return view('inicio.event-stats');
    }

    private function getRankingQuery()
    {
        return "
            WITH
            BaseResultados AS (
                SELECT a.user_id,
                    CASE
                        WHEN e.name LIKE 'COPA LET IT R.I.P.%' THEN 'let_it_rip'
                        WHEN e.name LIKE 'COPA X-MAS%' THEN 'xmas'
                        WHEN e.name LIKE 'COPA LIGERA REVIVAL%' THEN 'ligera'
                        WHEN e.name LIKE 'COPA ATTACK%' THEN 'attack'
                        WHEN e.name LIKE 'COPA STOCK%' THEN 'stock'
                        ELSE 'otra'
                    END AS tipo_copa,
                    CASE
                        WHEN a.puesto IN ('primero') THEN 3
                        WHEN a.puesto IN ('segundo') THEN 2
                        WHEN a.puesto IN ('tercero') THEN 1
                        ELSE 0
                    END AS puntos_partida
                FROM assist_user_event a
                JOIN events e ON a.event_id = e.id
                WHERE (e.beys = 'grancopa' OR e.name LIKE 'COPA STOCK%') AND e.date >= ?
            ),
            CupAggregates AS (
                SELECT user_id,
                    COUNT(DISTINCT CASE WHEN tipo_copa NOT IN ('otra', 'stock') THEN tipo_copa END) AS copas_inscritas,
                    SUM(max_puntos_copa) AS puntos_campeon,
                    MAX(CASE WHEN tipo_copa = 'stock' THEN 1 ELSE 0 END) AS jugo_stock
                FROM (
                    SELECT user_id, tipo_copa, MAX(puntos_partida) AS max_puntos_copa
                    FROM BaseResultados WHERE tipo_copa != 'otra'
                    GROUP BY user_id, tipo_copa
                ) mejores_resultados
                GROUP BY user_id
            ),
            RankingPoints AS (
                SELECT user_id, points_x3,
                    CASE
                        WHEN points_x3 >= 95 THEN 6
                        WHEN points_x3 >= 79 THEN 5
                        WHEN points_x3 >= 59 THEN 4
                        WHEN points_x3 >= 39 THEN 3
                        WHEN points_x3 >= 23 THEN 2
                        ELSE 1
                    END AS puntos_ranking
                FROM profiles
            ),
            FidelidadPoints AS (
                SELECT user_id,
                    MAX(CASE
                        WHEN plan_id = 3 THEN 3
                        WHEN plan_id = 2 THEN 2
                        WHEN plan_id = 1 THEN 1
                        ELSE 0
                    END) AS puntos_fidelidad
                FROM subscriptions
                WHERE status = 'active'
                  AND (period = 'annual' OR (period = 'monthly' AND started_at <= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)))
                GROUP BY user_id
            )
            SELECT
                ROW_NUMBER() OVER (
                    ORDER BY
                        COALESCE(ca.copas_inscritas, 0) DESC,
                        -- Si tiene 4 copas o más, el comodín no aplica para desempatar
                        CASE WHEN COALESCE(ca.copas_inscritas, 0) >= 4 THEN 0 ELSE COALESCE(ca.jugo_stock, 0) END DESC,
                        (COALESCE(ca.puntos_campeon, 0) + COALESCE(fp.puntos_fidelidad, 0) + COALESCE(rp.puntos_ranking, 1)) DESC
                ) AS posicion,
                u.id AS raw_id,
                CONCAT('#', LPAD(u.id, 4, '0')) AS id_formateado,
                u.name AS nombre_usuario,
                COALESCE(ca.copas_inscritas, 0) AS copas_inscritas,
                COALESCE(ca.jugo_stock, 0) AS jugo_stock,
                CASE
                    WHEN COALESCE(ca.copas_inscritas, 0) >= 4 THEN 'Grupo A'
                    WHEN COALESCE(ca.copas_inscritas, 0) = 3  THEN 'Grupo B'
                    WHEN COALESCE(ca.copas_inscritas, 0) = 2  THEN 'Grupo C'
                    WHEN COALESCE(ca.copas_inscritas, 0) = 1  THEN 'Grupo D'
                END AS grupo_prioridad,
                (COALESCE(ca.puntos_campeon, 0) + COALESCE(fp.puntos_fidelidad, 0) + COALESCE(rp.puntos_ranking, 1)) AS total_desempate,
                COALESCE(ca.puntos_campeon, 0) AS pt_campeon,
                COALESCE(fp.puntos_fidelidad, 0) AS pt_fidelidad,
                COALESCE(rp.puntos_ranking, 1) AS pt_ranking
            FROM users u
            JOIN CupAggregates ca ON u.id = ca.user_id
            LEFT JOIN RankingPoints rp ON u.id = rp.user_id
            LEFT JOIN FidelidadPoints fp ON u.id = fp.user_id
            ORDER BY
                copas_inscritas DESC,
                -- Replicamos la misma lógica en el ORDER BY final
                CASE WHEN copas_inscritas >= 4 THEN 0 ELSE jugo_stock END DESC,
                total_desempate DESC;
        ";
    }

    public function rankingNacional()
    {
        $participantes = DB::select($this->getRankingQuery(), [config('sbbl.season_start')]);

        // Enviamos el mismo nombre de variables que usa el proceso del CSV
        // para que la vista no falle
        return view('admin.dashboard.rankingNacional', [
            'listaIndividual' => collect($participantes),
            'listaEquipos' => collect([]), // Vacío en vista general
            'esBusquedaCsv' => false
        ]);
    }

    public function mostrarFormulario()
    {
        return view('admin.dashboard.importar');
    }

    public function procesarCsv(Request $request)
    {
        $request->validate(['archivo_csv' => 'required|file|mimes:csv,txt|max:2048']);
        $file = $request->file('archivo_csv');
        $handle = fopen($file->getRealPath(), "r");
        $solicitudesIndividual = []; $solicitudesEquipos = []; $header = true;

        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($header) { $header = false; continue; }
            $identificador = $row[1] ?? ''; $torneos = $row[2] ?? '';
            $partes = explode('#', $identificador);
            $nombreCsv = strtolower(trim($partes[0]));
            $idCsv = isset($partes[1]) ? (int) trim($partes[1]) : null;
            $datosBusqueda = ['id' => $idCsv, 'nombre' => $nombreCsv];

            if (strpos($torneos, "NACIONAL'26 Individual") !== false) $solicitudesIndividual[] = $datosBusqueda;
            if (strpos($torneos, "NACIONAL'26 Equipos") !== false) $solicitudesEquipos[] = $datosBusqueda;
        }
        fclose($handle);

        $todosLosParticipantes = DB::select($this->getRankingQuery(), [config('sbbl.season_start')]);

        $filtrar = function($maestro, $solicitudes) {
            return collect($maestro)->filter(function($jugador) use ($solicitudes) {
                foreach ($solicitudes as $s) {
                    if ($s['id'] !== null && $s['id'] === $jugador->raw_id) return true;
                    if ($s['id'] === null && $s['nombre'] === strtolower(trim($jugador->nombre_usuario))) return true;
                }
                return false;
            })->values();
        };

        return view('admin.dashboard.rankingNacional', [
            'listaIndividual' => $filtrar($todosLosParticipantes, $solicitudesIndividual),
            'listaEquipos' => $filtrar($todosLosParticipantes, $solicitudesEquipos),
            'esBusquedaCsv' => true
        ]);
    }

}
