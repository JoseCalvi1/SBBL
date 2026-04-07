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
        7 => 'upload-events/paisvasco2_1.webp', 8 => 'upload-events/canariass2.webp',
        11 => 'upload-events/aragons2.webp', 14 => 'upload-events/asturiass2.webp',
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
        // 1. GENERAR NOMBRE POR DEFECTO SI ES NECESARIO
        // Es mejor hacerlo antes de validar para que 'name' tenga valor
        if (empty($request->name)) {
            $request->merge(['name' => $this->generateDefaultName($request)]);
        }

        // 2. VALIDACIÓN ROBUSTA (Incluyendo imagen)
        $data = $request->validate([
            'name'            => 'required|min:6|max:255',
            'mode'            => 'required|string',
            'city'            => 'required|string|max:100',
            'location'        => 'required|string|max:255',
            'region_id'       => 'required|integer',
            'event_date'      => 'required|date',
            'event_time'      => 'required',
            'imagen'          => 'required|string', // Categoría (ranking, quedada...)
            'deck'            => 'required|string',
            'configuration'   => 'required|string',
            'note'            => 'nullable|string',
            'stadiums'        => 'required|integer|min:1',
            'has_stadium_limit' => 'nullable',
            // CRÍTICO: Limitar la imagen a 5MB (5120 KB) para evitar error 500
            'image_mod'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            // 3. PROCESAMIENTO DE IMAGEN (Redimensión para ahorrar memoria)
            $imageData = null;
            if ($request->hasFile('image_mod')) {
                $file = $request->file('image_mod');
                // Usamos una función auxiliar para redimensionar y no guardar 10MB en la BD
                $imageData = $this->resizeAndEncodeImage($file);
            }

            // 4. DETERMINAR RUTA DE IMAGEN BASE
            $rutaImagen = $this->resolveImage($data['region_id'], $request->imagen);

            // 5. CREAR EVENTO
            $event = Event::create([
                'name'              => $data['name'],
                'mode'              => $data['mode'],
                'city'              => $data['city'],
                'location'          => $data['location'],
                'region_id'         => $data['region_id'],
                'date'              => $data['event_date'],
                'time'              => $data['event_time'],
                'imagen'            => $rutaImagen,
                'beys'              => $request->imagen, // 'beys' guarda la categoría
                'image_mod'         => $imageData,       // Imagen en Base64 optimizada
                'deck'              => $data['deck'],
                'configuration'     => $data['configuration'],
                'note'              => $data['note'],
                'stadiums'          => $data['stadiums'],
                'has_stadium_limit' => $request->has('has_stadium_limit'), // Checkbox devuelve true/false
                'created_by'        => Auth::id(),
                'status'            => 'OPEN',
            ]);

            // 6. NOTIFICACIÓN A DISCORD (Envuelto en try/catch para no romper si falla discord)
            try {
                $this->sendDiscordNotification($event, false);
            } catch (\Exception $e) {
                Log::error("Error enviando a Discord: " . $e->getMessage());
            }

            // 7. REDIRECCIÓN (Patrón PRG - Post/Redirect/Get)
            // No devuelvas la vista directamente, redirige al índice o al evento creado
            return redirect()->route('inicio.events')->with('success', 'Evento creado correctamente.');

        } catch (\Exception $e) {
            // Si hay error, liberamos memoria y volvemos atrás con los datos
            Log::error("Error creando evento: " . $e->getMessage());
            return back()->withInput()->with('error', 'Ocurrió un error al guardar el evento. Inténtalo de nuevo.');
        }
    }

    /**
     * Función auxiliar para redimensionar imágenes usando PHP nativo (GD).
     * Esto evita que la BD explote con strings Base64 gigantes.
     */
    private function resizeAndEncodeImage($file)
    {
        // Cargar imagen en memoria
        $sourceImage = imagecreatefromstring(file_get_contents($file));
        if (!$sourceImage) return null;

        // Obtener dimensiones originales
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Definir nuevo ancho máximo (ej. 800px)
        $maxWidth = 800;

        // Calcular nuevas dimensiones manteniendo aspecto
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));

            // Crear lienzo nuevo y redimensionar
            $thumb = imagecreatetruecolor($newWidth, $newHeight);

            // Mantener transparencia si es PNG/WEBP
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $finalImage = $thumb;
        } else {
            $finalImage = $sourceImage;
        }

        // Capturar salida en buffer
        ob_start();
        // Guardar como JPEG con calidad 75 para reducir peso (o PNG si prefieres)
        imagejpeg($finalImage, null, 75);
        $imageData = ob_get_contents();
        ob_end_clean();

        // Liberar memoria
        imagedestroy($sourceImage);
        if (isset($thumb)) imagedestroy($thumb);

        return base64_encode($imageData);
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

        // 1. Cargar asistencias con los equipos
        $assists = $event->users()->with(['teams'])->withPivot('puesto')->get();

        // 2. Calcular los equipos asistentes
        $equiposAsistentes = $assists->filter(function ($user) {
            return $user->active_team;
        })->groupBy(function ($user) {
            return $user->active_team->name;
        })->map(function ($teamMembers, $teamName) {
            return [
                'name' => $teamName,
                'count' => $teamMembers->count(),
                'color' => $teamMembers->first()->active_team->color ?? '#38bdf8',
                'members' => $teamMembers->pluck('name')->toArray()
            ];
        })->sortByDesc('count');

        // 3. AQUÍ ESTÁ LA SOLUCIÓN: Definir la variable que daba el error
        $totalParticipantes = $assists->count();

        // 4. Retornar la vista con todas las variables en el compact
        return view('events.show', array_merge(compact(
            'event', 'videos', 'assists', 'suscribe', 'hoy',
            'bladeOptions', 'assistBladeOptions', 'ratchetOptions', 'bitOptions',
            'results', 'extraLines', 'resultsByParticipant', 'participantes',
            'totalParticipantes', 'equiposAsistentes'
        ), $limits));
    }

    public function edit(Event $event)
    {
        $regions = Region::all();
        return view('events.edit', compact('event', 'regions'));
    }

    public function update(Request $request, Event $event)
    {
        // 1. VALIDACIÓN (Asegúrate de incluir max:5120 para la imagen)
        $data = $request->validate([
            'name' => 'required|min:6|max:255',
            'mode' => 'required',
            'city' => 'required|max:255',
            'location' => 'required|max:255',
            'region_id' => 'required|exists:regions,id',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'stadiums' => 'required|integer|min:1',
            'has_stadium_limit' => 'nullable', // Puede venir como "on", "1" o null
            'image_mod' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Límite 5MB
            // Campos opcionales que no estaban en tu validate original pero se usan
            'imagen' => 'required', // Categoría
            'deck' => 'required',
            'configuration' => 'required',
            'note' => 'nullable|string',
            'iframe' => 'nullable|url',
        ]);

        try {
            // 2. PROCESAMIENTO DE IMAGEN (Redimensión)
            if ($request->hasFile('image_mod')) {
                $file = $request->file('image_mod');
                // Reutilizamos la función auxiliar resizeAndEncodeImage que te pasé antes
                // Si no la tienes en este controlador, cópiala del método store
                $event->image_mod = $this->resizeAndEncodeImage($file);
            }

            // 3. IMAGEN BASE
            // Si cambian la categoría, actualizamos la imagen base
            $rutaImagen = $this->resolveImage($request->region_id, $request->imagen);

            // 4. ACTUALIZACIÓN MASIVA Y MANUAL
            // Actualizamos los campos directos validados
            $event->fill([
                'name' => $data['name'],
                'mode' => $data['mode'],
                'city' => $data['city'],
                'location' => $data['location'],
                'region_id' => $data['region_id'],
                'date' => $data['event_date'],
                'time' => $data['event_time'],
                'stadiums' => $data['stadiums'],
                // El checkbox solo envía valor si está marcado
                'has_stadium_limit' => $request->has('has_stadium_limit'),
                'imagen' => $rutaImagen,
                'beys' => $request->imagen, // Categoría
                'deck' => $request->deck,
                'configuration' => $request->configuration,
                'note' => $request->note,
                'iframe' => $request->iframe,
            ]);

            $event->save();

            // 5. NOTIFICACIÓN (Opcional, solo si quieres notificar ediciones)
            // $this->sendDiscordNotification($event, true);

            return redirect()->route('events.show', ['event' => $event->id])
                             ->with('success', 'Evento actualizado correctamente.');

        } catch (\Exception $e) {
            Log::error("Error actualizando evento: " . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar el evento.');
        }
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

    // 1. Iniciamos una transacción de Base de Datos.
    // Si algo falla dentro de este bloque, NADA se guarda (Rollback).
    return DB::transaction(function () use ($request, $event, $userId) {

        $date = \Carbon\Carbon::parse($event->date);
        $hoy = \Carbon\Carbon::now()->format('Y-m-d');

        // --- VALIDACIONES PREVIAS ---

        // A. Verificar si el evento sigue abierto y no ha pasado la fecha
        if ($event->status !== 'OPEN' || $event->date < $hoy) {
            return response()->json(['error' => 'El evento no acepta más inscripciones.'], 422);
        }

        // B. Lógica de Torneos Ranking (Semanal y Mensual)
        if (in_array($event->beys, ['ranking', 'rankingplus'])) {

            // Bloqueo de lectura para evitar el truco de "múltiples pestañas"
            DB::table('assist_user_event')->where('user_id', $userId)->lockForUpdate()->get();

            // Límite Semanal
            $registeredThisWeek = DB::table('assist_user_event')
                ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                ->where('assist_user_event.user_id', $userId)
                ->whereBetween('events.date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()])
                ->whereIn('events.beys', ['ranking', 'rankingplus'])
                ->where('events.id', '!=', $event->id)
                ->exists();

            if ($registeredThisWeek) {
                return response()->json(['error' => 'Ya estás inscrito en un torneo de ranking esta semana.'], 422);
            }

            // Límite Mensual (Máximo 2)
            $monthlyCount = DB::table('assist_user_event')
                ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                ->where('assist_user_event.user_id', $userId)
                ->whereBetween('events.date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->whereIn('events.beys', ['ranking', 'rankingplus'])
                ->count();

            if ($monthlyCount >= 2) {
                return response()->json(['error' => 'Límite mensual de 2 torneos ranking alcanzado.'], 422);
            }
        }

        // --- VALIDACIÓN DE PAGO (PAYPAL) ---
            if (in_array($event->beys, ["grancopa", "copapaypal"])) {
                if (!$request->has('paypal_order_id')) {
                    return response()->json(['error' => 'Se requiere confirmación de pago para este evento.'], 402);
                }
                // ¡ELIMINAMOS el $pagoInfo de aquí para que no rompa la base de datos!
            }

            // --- REGISTRO FINAL ---
            // Lo dejamos exactamente como lo tenías tú originalmente:
            $attached = $event->users()->syncWithoutDetaching([$userId]);

            // Comprobamos si realmente se añadió (attached) o si ya existía
            if (count($attached['attached']) > 0) {
                $message = 'Inscripción realizada con éxito.';
                $status = 200;
            } else {
                $message = 'Ya te encuentras inscrito en este evento.';
                $status = 200;
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], $status);
            }

            return redirect()->back()->with('success', $message);
        });
    }


    // Función auxiliar para manejar la respuesta JSON o Redirect limpiamente
    private function assistResponse($request, $message, $status)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], $status);
        }

        if ($status !== 200) {
            return redirect()->back()->with('error', $message);
        }

        return redirect()->back()->with('success', $message);
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

        if (!Auth::user()->hasRole('juez') && (empty($request->input('iframe')) || empty($request->input('challonge')))) {
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
    // 1. Validación previa para evitar errores 500 por datos nulos
    $request->validate([
        'status' => 'required|in:approved,rejected',
        'comment' => 'required|string|max:10000', // max:10000 ayuda a prevenir ataques de payload excesivo
    ]);

    try {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);

        if ($user->hasRole('arbitro')) {
            $review = $event->reviews()->where('referee_id', $user->id)->first();

            // Retornamos error JSON si es petición AJAX, sino redirigimos
            if (!$review) {
                return $this->sendResponse('error', 'No has iniciado una revisión.', $request);
            }

            $review->update(['status' => $request->status, 'comment' => $request->comment]);

            // Lógica de 3 revisiones (MANTENIDA INTACATA)
            if ($event->reviews->count() == 3) {
                if ($event->reviews->every(fn($r) => $r->status === 'approved')) {
                    $event->update(['status' => 'approved']);
                } elseif ($event->reviews->contains(fn($r) => $r->status === 'rejected')) {
                    $event->update(['status' => 'requires_judge']);
                }
            }
        }

        if ($user->hasRole('juez') && $event->status === 'requires_judge') {
            EventJudgeReview::create([
                'event_id' => $event->id, 'judge_id' => $user->id,
                'final_status' => $request->final_status, 'comment' => $request->comment
            ]);
            $event->update(['status' => ($request->final_status === 'approved' ? 'approved' : 'rejected')]);
        }

        return $this->sendResponse('success', 'Revisión registrada correctamente.', $request);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // Error 404 controlado
        return $this->sendResponse('error', 'El evento no existe.', $request, 404);
    } catch (\Exception $e) {
        // Error 500 controlado: Logueamos el error real y mostramos mensaje genérico al usuario
        Log::error("Error en submitReview: " . $e->getMessage());
        return $this->sendResponse('error', 'Ocurrió un error inesperado en el servidor. Intenta de nuevo.', $request, 500);
    }
}

// Helper privado para responder según si es AJAX o Normal
private function sendResponse($type, $message, $request, $code = 200)
{
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'status' => $type,
            'message' => $message
        ], $type === 'success' ? 200 : $code);
    }
    return back()->with($type, $message);
}

    public function destroyReview($eventId, $userId)
    {
        $review = EventReview::where('event_id', $eventId)->where('referee_id', $userId)->first();
        if (!$review) return back()->with('error', 'Revisión no encontrada.');

        if (Auth::id() !== (int)$userId && !Auth::user()->hasRole('admin')) abort(403);

        $review->delete();
        return back()->with('success', 'Revisión eliminada correctamente.');
    }

    public function reviews_seguimiento(\Illuminate\Http\Request $request)
    {
        // Cogemos el mes y año, o 'all' si queremos el histórico
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $userId = $request->get('user_id');

        // Traemos solo a los usuarios que tengan rol de árbitro o juez
        $users = \App\Models\User::whereHas('roles', function($query) {
            $query->whereIn('name', ['arbitro', 'juez']);
        })->orderBy('name')->get();

        // Helper inteligente de consulta para las fechas
        $dateFilter = function($q) use ($month, $year) {
            if ($month === 'all' && $year === 'all') {
                return $q; // SIN FILTRO: Histórico Total
            }
            if ($year !== 'all' && $month !== 'all') {
                // Mes y Año exactos
                $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();
                return $q->whereBetween('created_at', [$start, $end]);
            }
            if ($year !== 'all') {
                // Todo un año completo
                return $q->whereYear('created_at', $year);
            }
            if ($month !== 'all') {
                // Un mes específico en todos los años (raro, pero posible)
                return $q->whereMonth('created_at', $month);
            }
            return $q;
        };

        // ==========================================
        // 1. ÁRBITROS (Excluimos a los que también son jueces)
        // ==========================================
        $refereeReviews = \App\Models\EventReview::with(['event', 'referee'])
            ->tap($dateFilter)
            ->whereHas('referee', function($q) {
                $q->whereHas('roles', fn($q2) => $q2->where('name', 'arbitro'))
                  ->whereDoesntHave('roles', fn($q2) => $q2->where('name', 'juez'));
            })
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->get();

        $refereeMonthlyStats = \App\Models\EventReview::selectRaw('referee_id as user_id, COUNT(*) as total_reviews, COUNT(DISTINCT event_id) as total_events, SUM(status = "approved") as approved, SUM(status = "rejected") as rejected, SUM(status = "pending") as pending')
            ->tap($dateFilter)
            ->whereHas('referee', function($q) {
                $q->whereHas('roles', fn($q2) => $q2->where('name', 'arbitro'))
                  ->whereDoesntHave('roles', fn($q2) => $q2->where('name', 'juez'));
            })
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->groupBy('referee_id')
            ->get();

        // ==========================================
        // 2. JUECES (Combinamos sus revisiones normales y sus veredictos finales)
        // ==========================================
        $juryReviewsFromEventReviews = \App\Models\EventReview::with(['event', 'referee'])
            ->tap($dateFilter)
            ->whereHas('referee', fn($q) => $q->whereHas('roles', fn($q2) => $q2->where('name', 'juez')))
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->get();

        $juryReviewsFromJudgeTable = \App\Models\EventJudgeReview::with(['event', 'judge'])
            ->tap($dateFilter)
            ->whereHas('judge', fn($q) => $q->whereHas('roles', fn($q2) => $q2->where('name', 'juez')))
            ->when($userId, fn($q) => $q->where('judge_id', $userId))
            ->get();

        $s1 = \App\Models\EventReview::selectRaw('referee_id as user_id, COUNT(*) as total_reviews, COUNT(DISTINCT event_id) as total_events, SUM(status = "approved") as approved, SUM(status = "rejected") as rejected')
            ->tap($dateFilter)
            ->whereHas('referee', fn($q) => $q->whereHas('roles', fn($q2) => $q2->where('name', 'juez')))
            ->when($userId, fn($q) => $q->where('referee_id', $userId))
            ->groupBy('referee_id')->get();

        $s2 = \App\Models\EventJudgeReview::selectRaw('judge_id as user_id, COUNT(*) as total_reviews, COUNT(DISTINCT event_id) as total_events, SUM(final_status = "approved") as approved, SUM(final_status = "rejected") as rejected')
            ->tap($dateFilter)
            ->whereHas('judge', fn($q) => $q->whereHas('roles', fn($q2) => $q2->where('name', 'juez')))
            ->when($userId, fn($q) => $q->where('judge_id', $userId))
            ->groupBy('judge_id')->get();

        // Unificamos las estadísticas de los jueces
        $juryMonthlyStats = $s1->concat($s2)->groupBy('user_id')->map(function ($items) {
            return (object) [
                'user_id' => $items->first()->user_id,
                'total_reviews' => $items->sum('total_reviews'),
                'total_events' => $items->sum('total_events'),
                'approved' => $items->sum('approved'),
                'rejected' => $items->sum('rejected'),
            ];
        });

        // ==========================================
        // 3. ESTADÍSTICAS GLOBALES
        // ==========================================
        $globalStats = (object) [
            'total_reviews' => $refereeReviews->count() + $juryReviewsFromEventReviews->count() + $juryReviewsFromJudgeTable->count(),
            'approved' => $refereeReviews->where('status', 'approved')->count() + $juryReviewsFromEventReviews->where('status', 'approved')->count() + $juryReviewsFromJudgeTable->where('final_status', 'approved')->count(),
            'rejected' => $refereeReviews->where('status', 'rejected')->count() + $juryReviewsFromEventReviews->where('status', 'rejected')->count() + $juryReviewsFromJudgeTable->where('final_status', 'rejected')->count(),
            'pending' => $refereeReviews->where('status', 'pending')->count() + $juryReviewsFromEventReviews->where('status', 'pending')->count()
        ];

        return view('admin.dashboard.reviews', compact(
            'refereeReviews', 'juryReviewsFromEventReviews', 'juryReviewsFromJudgeTable',
            'refereeMonthlyStats', 'juryMonthlyStats', 'globalStats', 'month', 'year', 'users', 'userId'
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
