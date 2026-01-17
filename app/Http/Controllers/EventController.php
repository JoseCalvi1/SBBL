<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventJudgeReview;
use App\Models\EventReview;
use App\Models\Region;
use App\Models\TournamentResult;
use App\Models\User;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    // --- CONFIGURACIÓN Y CONSTANTES ---
    // Centralizamos los IDs y rutas para no tenerlos dispersos por el código

    const DISCORD_ROLES = [
        'Andalucía' => '1206704489990459452', 'Aragón' => '1209155125654978663',
        'Asturias' => '1209155336033017876', 'Baleares' => '1209155169917476874',
        'Canarias' => '1209154917449859143', 'Cantabria' => '1209155399920386109',
        'Castilla La Mancha' => '1209154971229093978', 'Castilla y León' => '1209154789003239504',
        'Catalunya' => '1209154633227046983', 'Extremadura' => '1209155220450582618',
        'Galicia' => '1209154737220354048', 'Rioja' => '1209155480342106132',
        'Madrid' => '1209154530890219520', 'Murcia' => '1209155058562895944',
        'Navarra' => '1209155367578370059', 'País Vasco' => '1209154853872345138',
        'Valencia' => '1209154705327132712', 'Melilla' => '1209155513967845438',
        'Ceuta' => '1209155549468434432',
    ];

    const REGION_IMAGES = [
        1 => 'upload-events/andalucias2.webp', 2 => 'upload-events/madrids2.webp',
        4 => 'upload-events/valencias2.webp', 5 => 'upload-events/galicias2.webp',
        8 => 'upload-events/canariass2.webp', 11 => 'upload-events/aragons2.webp',
        14 => 'upload-events/asturiass2.webp',
    ];

    const EVENT_TYPES = [
        'quedada' => 'Quedada', 'ranking' => 'Ranking', 'rankingplus' => 'Ranking Plus',
        'grancopa' => 'Gran Copa', 'hasbro' => 'Hasbro', 'copalloros' => 'Copa Lloros',
        'copaligera' => 'Copa Ligera', 'copapaypal' => 'Copa Conqueror',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Listado de eventos con filtros.
     */
    public function index(Request $request)
    {
        $estado = $request->input('estado');
        $beys = $request->input('beys');

        // Optimización: "with" carga las relaciones para evitar muchas consultas SQL
        $query = Event::with(['region', 'reviews.referee'])
            ->where('date', '>=', '2025-09-01')
            ->orderBy('date', 'DESC');

        if ($estado) {
            $statuses = ($estado === 'PENDING_REVIEW') ? ['PENDING', 'REVIEW'] : [$estado];
            $query->whereIn('status', $statuses);
        }

        if ($beys === 'ranking') {
            $query->whereIn('beys', ['ranking', 'rankingplus']);
        }

        $events = $query->get();

        return view('events.index', compact('events', 'estado', 'beys'));
    }

    public function create()
    {
        $regions = Region::all();
        return view('events.create', compact('regions'));
    }

    /**
     * Crear evento optimizado.
     */
    public function store(Request $request)
    {
        // 1. Generar nombre por defecto si no viene
        if (empty($request->name)) {
            $request->merge(['name' => $this->generateDefaultName($request)]);
        }

        // 2. Validación
        $data = $request->validate([
            'name' => 'required|min:6',
            'mode' => 'required',
            'city' => 'required',
            'location' => 'required',
            'region_id' => 'required',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'deck' => 'nullable',
            'configuration' => 'nullable',
            'note' => 'nullable',
            'stadiums' => 'required|integer|min:1',
            'has_stadium_limit' => 'nullable|boolean',
        ]);

        // 3. Procesar imagen subida
        $imageData = $request->hasFile('image_mod')
            ? base64_encode(file_get_contents($request->file('image_mod')))
            : null;

        // 4. Determinar imagen base
        $rutaImagen = $this->resolveImage($request->region_id, $request->imagen);

        // 5. Crear evento usando Eloquent (más limpio)
        $event = Event::create([
            'name' => $data['name'],
            'mode' => $data['mode'],
            'city' => $data['city'],
            'location' => $data['location'],
            'created_by' => Auth::id(),
            'status' => 'OPEN',
            'region_id' => $data['region_id'],
            'date' => $data['event_date'],
            'time' => $data['event_time'],
            'imagen' => $rutaImagen,
            'beys' => $request->imagen,
            'image_mod' => $imageData,
            'deck' => $request->deck,
            'configuration' => $request->configuration,
            'note' => $request->note,
            'has_stadium_limit' => $request->has('has_stadium_limit'),
        ]);

        // 6. Notificación a Discord
        $this->sendDiscordNotification($event, false);

        // Retorno a la vista
        $events = Event::with('region')->get();
        $createEvent = Event::where('created_by', Auth::id())->where('date', '>', Carbon::now())->get();
        $countEvents = count($createEvent);

        return view('inicio.events', compact('events', 'countEvents'));
    }

    public function show(Event $event)
    {
        // Optimización: Carga ansiosa para evitar consultas extra
        $event->load(['users', 'judgeReview', 'reviews.referee']);

        $videos = DB::select('select * from videos where event_id = ?', [$event->id]);
        $assists = $event->users()->withPivot('puesto')->get();
        $hoy = Carbon::now()->subDay()->format('Y-m-d');

        // Verificar suscripción de forma eficiente
        $suscribe = Auth::check() && $event->users->contains(Auth::id())
            ? DB::table('assist_user_event')->where('user_id', Auth::id())->where('event_id', $event->id)->first()
            : [0];

        // Obtener resultados y rellenar líneas vacías
        $results = TournamentResult::where('user_id', Auth::id())
            ->where('event_id', $event->id)
            ->get();

        // Rellenamos hasta 3 líneas si faltan
        $extraLines = max(3 - $results->count(), 0);
        for ($i = 0; $i < $extraLines; $i++) {
            $results->push(new TournamentResult());
        }

        // Resultados por participante
        $resultsByParticipant = [];
        foreach ($assists as $assist) {
            $resultsByParticipant[$assist->id] = TournamentResult::where('user_id', $assist->id)
                ->where('event_id', $event->id)
                ->where('blade', 'NOT LIKE', '%Selecciona%')
                ->get();
        }

        // Opciones para desplegables
        $bladeOptions = DB::table('blades')->orderBy('nombre_takara')->pluck('nombre_takara')->toArray();
        $assistBladeOptions = DB::table('assist_blades')->orderBy('nombre')->pluck('nombre')->toArray();
        $ratchetOptions = DB::table('ratchets')->orderBy('nombre')->pluck('nombre')->toArray();
        $bitOptions = DB::table('bits')->orderBy('nombre')->pluck('nombre')->toArray();

        // Lógica de límites de torneo (extraída a helper para limpieza)
        $limits = $this->calculateRankingLimits($event);

        $participantes = User::all();

        return view('events.show', array_merge(compact(
            'event', 'videos', 'assists', 'suscribe', 'hoy',
            'bladeOptions', 'assistBladeOptions', 'ratchetOptions', 'bitOptions',
            'results', 'extraLines', 'resultsByParticipant', 'participantes'
        ), $limits));
    }

    public function edit(Event $event)
    {
        $regions = Region::all();
        return view('events.edit', compact('event', 'regions'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => 'required|min:6',
            'mode' => 'required',
            'city' => 'required',
            'location' => 'required',
            'region_id' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
            'stadiums' => 'required|integer|min:1',
            'has_stadium_limit' => 'nullable|boolean',
        ]);

        // Imagen personalizada
        if ($request->hasFile('image_mod')) {
            $event->image_mod = base64_encode(file_get_contents($request->file('image_mod')));
        }

        // Imagen base según región/tipo
        $rutaImagen = $this->resolveImage($request->region_id, $request->imagen);

        // Asignación manual para mantener lógica original de campos que no están en $data
        $event->fill($data);
        $event->imagen = $rutaImagen;
        $event->beys = $request->imagen;
        $event->deck = $request['deck'];
        $event->configuration = $request['configuration'];
        $event->iframe = $request['iframe'];
        $event->note = $request['note'];
        $event->has_stadium_limit = $request->has('has_stadium_limit');

        $event->save();

        $this->sendDiscordNotification($event, true);

        return redirect()->action('App\Http\Controllers\EventController@show', ['event' => $event->id])->with('event', $event);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->action('App\Http\Controllers\EventController@index');
    }

    // --- GESTIÓN DE ASISTENCIA ---

    public function assist(Request $request, Event $event)
    {
        $userId = Auth::id();

        // syncWithoutDetaching previene duplicados y es más limpio que DB::insert con comprobación
        $attached = $event->users()->syncWithoutDetaching([$userId]);

        if ($request->wantsJson()) {
            // Si ya estaba inscrito (no se adjuntó nada nuevo)
            $msg = (count($attached['attached']) > 0) ? 'Inscripción exitosa' : 'Ya estabas inscrito';
            return response()->json(['message' => $msg], 200);
        }
        return redirect()->back();
    }

    public function addAssist(Request $request, Event $event)
    {
        $event->users()->syncWithoutDetaching([$request->participante_id]);
        return redirect()->back();
    }

    public function noassist(Event $event)
    {
        $event->users()->detach(Auth::id());
        return redirect()->back();
    }

    // --- GESTIÓN DE RESULTADOS Y STATUS ---

    public function actualizarStatus($id, $status)
    {
        Event::where('id', $id)->update(['status' => $status]);
    }

    public function updatePuestos(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if (!Auth::user()->is_jury && (empty($request->input('iframe')) || empty($request->input('challonge')))) {
            return redirect()->back()->with('error', 'Primero tienes que enviar los datos del torneo.');
        }

        $request->validate([
            'participantes' => 'required|array',
            'participantes.*.id' => 'required|exists:assist_user_event,user_id',
            'participantes.*.puesto' => 'required|string',
        ]);

        if ($request->has('iframe')) $event->iframe = $request->input('iframe');
        if ($request->has('challonge')) $event->challonge = $request->input('challonge');
        $event->status = 'PENDING';
        $event->save();

        // Usamos transacción para seguridad de datos
        DB::transaction(function () use ($request, $id, $event) {
            foreach ($request->input('participantes') as $participante) {
                DB::table('assist_user_event')
                    ->where('user_id', $participante['id'])
                    ->where('event_id', $id)
                    ->update(['puesto' => $participante['puesto']]);
            }
            // Procesar trofeos si corresponde
            $this->processTrophies($event, $request->input('participantes'));
        });

        return redirect()->back()->with('success', 'Resultados actualizados correctamente.');
    }

    public function actualizarPuntuaciones(Request $request, $id, $mode)
    {
        $evento = Event::findOrFail($id);

        // Si no es ranking, cerrar y salir
        if (!in_array($evento->beys, ['ranking', 'rankingplus'])) {
            $this->actualizarStatus($id, 'CLOSE');
            return redirect()->back()->with('success', 'Evento cerrado sin puntuaciones');
        }

        // Comentario de juez
        if ($request->comment) {
            EventJudgeReview::create([
                'event_id' => $id, 'judge_id' => Auth::id(),
                'final_status' => 'approved', 'comment' => $request->comment
            ]);
        }

        // Validar puestos vacíos
        $faltanPuestos = DB::table('assist_user_event')
            ->where('event_id', $id)
            ->where(fn($q) => $q->whereNull('puesto')->orWhere('puesto', ''))
            ->exists();

        if ($faltanPuestos) {
            return redirect()->back()->with('error', 'No se han enviado los resultados del torneo.');
        }

        // Asignar puntos
        $participantes = DB::table('assist_user_event')
            ->where('event_id', $id)
            ->where('puesto', '!=', 'nopresentado')
            ->get();

        $pointsTable = $this->getPointsTable($participantes->count());
        $puestoMap = ['primero' => 0, 'segundo' => 1, 'tercero' => 2, 'cuarto' => 3, 'quinto' => 4, 'septimo' => 5];
        $eventMode = 'points_x2';

        DB::transaction(function () use ($participantes, $pointsTable, $puestoMap, $eventMode, $id) {
            foreach ($participantes as $p) {
                $puesto = strtolower($p->puesto);
                $index = $puestoMap[$puesto] ?? null;
                $puntos = ($index !== null) ? $pointsTable[$index] : 1;

                DB::table('profiles')->where('user_id', $p->user_id)->increment($eventMode, $puntos);

                DB::table('points_log')->insert([
                    'user_id' => $p->user_id, 'event_id' => $id, 'modo' => $eventMode,
                    'puntos' => $puntos, 'puesto' => $puesto, 'created_at' => now(), 'updated_at' => now()
                ]);
            }
        });

        $this->actualizarStatus($id, 'CLOSE');
        return redirect()->back()->with('success', 'Puntuaciones actualizadas correctamente');
    }

    public function estadoTorneo(Request $request, $id, $estado)
    {
        $statusMap = ['invalidar' => 'INVALID', 'revisar' => 'REVIEW', 'inscripcion' => 'INSCRIPCION'];

        if ($estado == "invalidar" && $request->comment) {
            EventJudgeReview::create([
                'event_id' => $id, 'judge_id' => Auth::id(),
                'final_status' => 'rejected', 'comment' => $request->comment
            ]);
        }

        if (isset($statusMap[$estado])) {
            $this->actualizarStatus($id, $statusMap[$estado]);
            $msg = ($estado == 'invalidar') ? 'Evento invalidado' : (($estado == 'revisar') ? 'Evento en revisión' : 'Inscripción cerrada');
            return redirect()->back()->with('success', $msg);
        }
    }

    // --- REVIEWS Y SEGUIMIENTO ---

    public function startReview(Event $event)
    {
        if ($event->reviews()->where('referee_id', Auth::id())->exists()) {
            return back()->with('error', 'Ya has iniciado la revisión de este evento.');
        }

        $event->reviews()->create(['referee_id' => Auth::id(), 'status' => 'pending', 'comment' => '']);
        $this->actualizarStatus($event->id, 'REVIEW');

        return back()->with('success', 'Revisión iniciada. Ahora puedes completarla.');
    }

    public function submitReview(Request $request, $eventId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);

        if ($user->is_referee) {
            $review = $event->reviews()->where('referee_id', $user->id)->first();
            if (!$review) return back()->with('error', 'No has iniciado una revisión.');

            $review->update(['status' => $request->status, 'comment' => $request->comment]);

            // Lógica de 3 revisiones
            if ($event->reviews->count() == 3) {
                if ($event->reviews->every(fn($r) => $r->status === 'approved')) {
                    $event->update(['status' => 'approved']);
                } elseif ($event->reviews->contains(fn($r) => $r->status === 'rejected')) {
                    $event->update(['status' => 'requires_judge']);
                }
            }
        }

        if ($user->is_jury && $event->status === 'requires_judge') {
            EventJudgeReview::create([
                'event_id' => $event->id, 'judge_id' => $user->id,
                'final_status' => $request->final_status, 'comment' => $request->comment
            ]);
            $event->update(['status' => ($request->final_status === 'approved' ? 'approved' : 'rejected')]);
        }

        return back()->with('success', 'Revisión registrada.');
    }

    public function destroyReview($eventId, $userId)
    {
        $review = EventReview::where('event_id', $eventId)->where('referee_id', $userId)->first();
        if (!$review) return back()->with('error', 'Revisión no encontrada.');

        if (Auth::id() !== (int)$userId && !Auth::user()->is_admin) abort(403);

        $review->delete();
        return back()->with('success', 'Revisión eliminada correctamente.');
    }

    public function reviews_seguimiento(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $userId = $request->get('user_id');

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        $users = User::where('is_referee', true)->orWhere('is_jury', true)->orderBy('name')->get();

        // Helpers de consulta
        $dateFilter = fn($q) => $q->whereBetween('created_at', [$start, $end]);
        $userFilter = fn($q, $field) => $userId ? $q->where($field, $userId) : $q;

        // Referees
        $refereeReviews = EventReview::with(['event', 'referee'])
            ->tap($dateFilter)
            ->whereHas('referee', fn($q) => $q->where('is_referee', true)->where('is_jury', false))
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->get();

        // Jueces (combinado)
        $juryReviewsFromEventReviews = EventReview::with(['event', 'referee'])
            ->tap($dateFilter)
            ->whereHas('referee', fn($q) => $q->where('is_jury', true))
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->get();

        $juryReviewsFromJudgeTable = EventJudgeReview::with(['event', 'judge'])
            ->tap($dateFilter)
            ->whereHas('judge', fn($q) => $q->where('is_jury', true))
            ->when($userId, fn($q) => $q->where('judge_id', $userId))
            ->get();

        // Estadísticas Mensuales
        $refereeMonthlyStats = EventReview::selectRaw('referee_id as user_id, COUNT(*) as total_reviews, COUNT(DISTINCT event_id) as total_events, SUM(status = "approved") as approved, SUM(status = "rejected") as rejected, SUM(status = "pending") as pending')
            ->tap($dateFilter)
            ->whereHas('referee', fn($q) => $q->where('is_referee', true)->where('is_jury', false))
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->groupBy('referee_id')
            ->get();

        // Estadísticas Jueces (Unificación)
        $s1 = EventReview::selectRaw('referee_id as user_id, COUNT(*) as total_reviews, COUNT(DISTINCT event_id) as total_events, SUM(status = "approved") as approved, SUM(status = "rejected") as rejected')
            ->tap($dateFilter)
            ->whereHas('referee', fn($q) => $q->where('is_jury', true))
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->groupBy('referee_id')->get();

        $s2 = EventJudgeReview::selectRaw('judge_id as user_id, COUNT(*) as total_reviews, COUNT(DISTINCT event_id) as total_events, SUM(final_status = "approved") as approved, SUM(final_status = "rejected") as rejected')
            ->tap($dateFilter)
            ->whereHas('judge', fn($q) => $q->where('is_jury', true))
            ->when($userId, fn($q) => $q->where('judge_id', $userId))
            ->groupBy('judge_id')->get();

        $juryMonthlyStats = $s1->concat($s2)->groupBy('user_id')->map(function ($items) {
            return (object) [
                'user_id' => $items->first()->user_id,
                'total_reviews' => $items->sum('total_reviews'),
                'total_events' => $items->sum('total_events'),
                'approved' => $items->sum('approved'),
                'rejected' => $items->sum('rejected'),
            ];
        });

        $stats = [
            'referee_total' => $refereeReviews->count(),
            'approved' => $refereeReviews->where('status', 'approved')->count(),
            'rejected' => $refereeReviews->where('status', 'rejected')->count(),
            'pending' => $refereeReviews->where('status', 'pending')->count(),
            'jury_total' => $juryMonthlyStats->sum('total_reviews'),
        ];

        return view('admin.dashboard.reviews', compact(
            'refereeReviews', 'juryReviewsFromEventReviews', 'juryReviewsFromJudgeTable',
            'refereeMonthlyStats', 'juryMonthlyStats', 'stats', 'month', 'year', 'users', 'userId'
        ));
    }

    public function updateVideo(Request $request, Event $event)
    {
        $request->validate(['iframe' => 'required|url', 'challonge' => 'required|url']);
        $event->update(['iframe' => $request->iframe, 'challonge' => $request->challonge]);
        return redirect()->back()->with('success', 'Datos añadidos correctamente.');
    }

    public function getParticipantResults(Request $request, Event $event)
    {
        return response()->json($event->results->where('participant_id', $request->query('id')));
    }

    // =========================================================================
    // FUNCIONES AUXILIARES PRIVADAS (Para limpiar los métodos principales)
    // =========================================================================

    private function generateDefaultName(Request $request): string
    {
        $typeName = self::EVENT_TYPES[$request->imagen] ?? 'Copa';
        $mes = date('m', strtotime($request->event_date));
        $year = date('Y', strtotime($request->event_date));
        $modeName = ($request->mode == "beybladex") ? "X" : "Burst";

        // Usamos count() directo de Eloquent
        $numeroEventos = Event::whereYear('date', $year)
            ->whereMonth('date', $mes)
            ->where('mode', $request->mode)
            ->count() + 1;

        $regionName = Region::find($request->region_id)->name ?? '';
        $monthAbbr = strtoupper(date('M', strtotime($request->event_date)));

        return "{$typeName} {$numeroEventos} {$regionName} {$modeName} " . substr($monthAbbr, 0, 3);
    }

    private function resolveImage($regionId, $beysType): string
    {
        if (isset(self::REGION_IMAGES[$regionId])) {
            return self::REGION_IMAGES[$regionId];
        }
        if (in_array($beysType, ['ranking', 'rankingplus'])) {
            return 'upload-events/rankingx.png';
        }
        return 'upload-events/quedada.jpg';
    }

    private function sendDiscordNotification(Event $event, bool $isEdit = false)
    {
        // 1. Si NO es producción, no hace nada y sale de la función.
        if (config('app.env') !== 'production') {
            return;
        }

        if (!env('DISCORD_WEBHOOK_URL')) return;

        $regionName = $event->region->name ?? 'Global';
        $rolId = self::DISCORD_ROLES[$regionName] ?? '';
        $fecha = Carbon::parse($event->date)->translatedFormat('d \d\e F \d\e\l Y');

        if ($isEdit) {
            $content = "⚠️ ¡El torneo de **{$event->city}** ({$regionName}) ha sido **modificado**!\n<@&{$rolId}>";
            $embed = [
                'title' => "{$event->name} ({$event->mode})",
                'description' => "📅 Nueva fecha: **{$fecha}** a las **{$event->time}**.\n📍 Ubicación: {$event->location}\n🔗 Más info: https://sbbl.es/events/{$event->id}",
                'color' => 16753920
            ];
        } else {
            $content = "¡Hay un nuevo torneo disponible para {$event->city} ({$regionName})!\n<@&{$rolId}>";
            $embed = [
                'title' => "{$event->name} ({$event->mode})",
                'description' => "El día {$fecha} a las {$event->time}. Inscríbete en: https://sbbl.es/events/{$event->id}",
                'color' => 7506394
            ];
        }

        Http::post(env('DISCORD_WEBHOOK_URL'), [
            'content' => $content,
            'embeds' => [$embed],
        ]);
    }

    private function calculateRankingLimits(Event $event): array
    {
        $date = Carbon::parse($event->date);

        $isRegistered = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::id())
            ->whereBetween('events.date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()])
            ->whereIn('events.beys', ['ranking', 'rankingplus'])
            ->exists();

        $participated = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::id())
            ->whereBetween('events.date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->whereIn('events.beys', ['ranking', 'rankingplus'])
            ->where(function ($q) {
                $q->where('assist_user_event.puesto', '<>', 'nopresentado')->orWhereNull('assist_user_event.puesto')->orWhere('assist_user_event.puesto', '');
            })
            ->count();

        return [
            'isRegistered' => $isRegistered,
            'rankingTournamentsLeft' => max(0, 2 - $participated)
        ];
    }

    private function processTrophies(Event $event, array $participantes)
    {
        if (!in_array($event->beys, ['copapaypal', 'grancopa'])) return;

        $trophyId = DB::table('trophies')->where('name', 'SBBL Coin')->value('id');
        if (!$trophyId) return;

        $totalUsers = DB::table('assist_user_event')->where('event_id', $event->id)->count();
        $coinsBase = ($event->beys === 'copapaypal' ? 200 : 500) * $totalUsers;
        $porcentajes = ['primero' => 0.5, 'segundo' => 0.3, 'tercero' => 0.2];

        foreach ($participantes as $p) {
            $puesto = strtolower($p['puesto']);
            if (isset($porcentajes[$puesto])) {
                $coins = intval($coinsBase * $porcentajes[$puesto]);

                $exists = DB::table('profilestrophies')
                    ->where('profiles_id', $p['id'])
                    ->where('trophies_id', $trophyId)->first();

                if ($exists) {
                    DB::table('profilestrophies')->where('id', $exists->id)->increment('count', $coins);
                } else {
                    DB::table('profilestrophies')->insert([
                        'profiles_id' => $p['id'], 'trophies_id' => $trophyId, 'count' => $coins, 'created_at' => now(), 'updated_at' => now()
                    ]);
                }
            }
        }
        $this->actualizarStatus($event->id, 'CLOSED');
    }

    private function getPointsTable(int $count): array
    {
        if ($count >= 33) return [7, 6, 5, 4, 3, 2, 1];
        if ($count >= 25) return [6, 5, 4, 3, 2, 1, 1];
        if ($count >= 17) return [5, 4, 3, 2, 1, 1, 1];
        if ($count >= 9) return [4, 3, 2, 1, 1, 1, 1];
        if ($count >= 6) return [3, 2, 1, 1, 1, 1, 1];
        return [2, 1, 1, 1, 1, 1, 1];
    }
}
