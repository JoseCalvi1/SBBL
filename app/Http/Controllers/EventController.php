<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\Region;
use App\Models\TournamentResult;
use App\Models\User;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EventController extends Controller
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
    public function index(Request $request)
    {
        // Obtener los filtros de la petición
        $estado = $request->input('estado'); // Filtro por estado
        $beys = $request->input('beys'); // Filtro por tipo de evento (ranking o rankingplus)

        // Consulta base con los eventos a partir de una fecha específica
        $query = Event::where('date', '>=', '2025-09-01')
                    ->orderBy('date', 'DESC');

        // Aplicar filtro por estado si se selecciona uno
        if ($estado) {
            if ($estado === 'PENDING_REVIEW') {
                $query->whereIn('status', ['PENDING', 'REVIEW']);
            } else {
                $query->where('status', $estado);
            }
        }


        // Aplicar filtro por beys si se selecciona uno
        if ($beys == 'ranking') {
            $query->whereIn('beys', ['ranking', 'rankingplus']);
        }

        // Cargamos también las revisiones y los árbitros (validadores)
        $query->with(['region', 'reviews.referee']);

        // Obtener los eventos filtrados con paginación para mejor rendimiento
        $events = $query->get();

        return view('events.index', compact('events', 'estado', 'beys'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $regions = Region::all();

        return view('events.create', compact('regions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (empty($request->name)) {
            $eventDate = $request->event_date;
            $mode = $request->mode;

            $modeName = ($request->mode == "beybladex") ? "X" : "Burst" ;

            // Inicializamos una variable para el texto
            $textoImagen = '';

            // Realizamos el condicional para asignar el texto correspondiente
            if ($request->imagen == 'quedada') {
                $textoImagen = 'Quedada';
            } elseif ($request->imagen == 'ranking') {
                $textoImagen = 'Ranking';
            } elseif ($request->imagen == 'rankingplus') {
                $textoImagen = 'Ranking Plus';
            } elseif ($request->imagen == 'grancopa') {
                $textoImagen = 'Gran Copa';
            } elseif ($request->imagen == 'hasbro') {
                $textoImagen = 'Hasbro';
            } elseif ($request->imagen == 'copalloros') {
                $textoImagen = 'Copa Lloros';
            } elseif ($request->imagen == 'copaligera') {
                $textoImagen = 'Copa Ligera';
            } elseif ($request->imagen == 'copapaypal') {
                $textoImagen = 'Copa Paypal';
            } else {
                $textoImagen = 'Copa'; // Si no coincide con ninguno de los valores
            }

            // Obtener el mes y año de la fecha del evento
            $mes = date('m', strtotime($eventDate));
            $año = date('Y', strtotime($eventDate));

            // Consulta para obtener el número de eventos del mismo tipo realizados en el mismo mes y año
            $numeroEventos = Event::whereYear('date', $año)
                                ->whereMonth('date', $mes)
                                ->where('mode', $mode)
                                ->count()+1;

            $regionId = Region::FindOrFail($request->region_id);
            $monthAbbreviation = date('M', strtotime($request->event_date));
            $defaultName = $textoImagen . ' ' . $numeroEventos . ' ' . $regionId->name.' '.$modeName.' ' . strtoupper(substr($monthAbbreviation, 0, 3));
            $request->merge(['name' => $defaultName]);
        }

        // Validación
        $data = $request->validate([
            'name' => 'required|min:6',
            'mode' => 'required',
            'city' => 'required',
            'location' => 'required',
            'region_id' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
        ]);

        // Procesamiento de la imagen
        if ($request->hasFile('image_mod')) {
            $imageData = base64_encode(file_get_contents($request->file('image_mod')));
        } else {
            $imageData = null;
        }

        // Obtener ruta de la imagen
        /*$ruta_imagen = "No hay imagen";
        if($request['imagen']) {
            $ruta_imagen = $request['imagen']->store('upload-events', 'public');
        }*/

        // Si el usuario sube una imagen
        if($request['region_id'] == 1) {
            $ruta_imagen = 'upload-events/andalucias2.webp';
        }   elseif($request['region_id'] == 2)  {
            $ruta_imagen = 'upload-events/madrids2.webp';
        }   elseif($request['region_id'] == 4)  {
            $ruta_imagen = 'upload-events/valencias2.webp';
        }   elseif($request['region_id'] == 8)  {
            $ruta_imagen = 'upload-events/canariass2.webp';
        }   elseif($request['region_id'] == 11)  {
            $ruta_imagen = 'upload-events/aragons2.webp';
        }    elseif($request['region_id'] == 5)  {
            $ruta_imagen = 'upload-events/galicias2.webp';
        }    elseif($request['region_id'] == 14)  {
            $ruta_imagen = 'upload-events/asturiass2.webp';
        }    elseif($request['beys'] == 'ranking' || $request['beys'] == 'rankingplus')  {
            $ruta_imagen = 'upload-events/rankingx.png';
        } else {
            $ruta_imagen = 'upload-events/quedada.jpg';
        }

        // Almacenar datos en la BD (sin modelos)
        $eventId = DB::table('events')->insertGetId([
            'name' => $data['name'],
            'mode' => $data['mode'],
            'city' => $data['city'],
            'location' => $data['location'],
            'created_by' => Auth::user()->id,
            'status' => 'OPEN',
            'region_id' => $data['region_id'],
            'date' => $data['event_date'],
            'time' => $data['event_time'],
            'imagen' => $ruta_imagen,
            'beys' => $request->imagen,
            'image_mod' => $imageData,
            'deck' => $request['deck'],
            'configuration' => $request['configuration'],
            'note' => $request['note'],
        ]);

        // TODO Comentar para probar en local
        Self::notification(Event::find($eventId));

        $events = Event::with('region')->get();
        $createEvent = Event::where('created_by', Auth::user()->id)->where('date', '>', Carbon::now())->get();
        $countEvents = count($createEvent);


        return view('inicio.events', compact('events', 'countEvents'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
     public function show(Event $event)
    {
        $videos = DB::select('select * from videos where event_id = '.$event->id);
        $assists = $event->users()->withPivot('puesto')->get();
        $hoy = Carbon::now()->subDay()->format('Y-m-d');
        if(Auth::user()) {
           $suscribe = DB::table('assist_user_event')->where('user_id', Auth::user()->id)->where('event_id', $event->id)->first();
        } else {
           $suscribe[] = 0;
        }

        // Recuperar los resultados existentes del usuario para este evento
        $results = TournamentResult::where('user_id', Auth::user()->id)
        ->where('event_id', $event->id)
        ->get();

        // Crear líneas vacías adicionales si hay menos de 3 resultados
        $extraLines = max(3 - $results->count(), 0);
        for ($i = 0; $i < $extraLines; $i++) {
            $results->push(new TournamentResult()); // Añadir un modelo vacío para las líneas faltantes
        }

         // Obtener resultados de cada participante para este evento
        $resultsByParticipant = [];
        foreach ($assists as $assist) {
            $resultsByParticipant[$assist->id] = TournamentResult::where('user_id', $assist->id)
                                                                ->where('event_id', $event->id)
                                                                ->where('blade', 'NOT LIKE', '%Selecciona%')
                                                                ->get();
        }

        $bladeOptions = DB::table('blades')->orderBy('nombre_takara')->pluck('nombre_takara')->toArray();
        $assistBladeOptions = DB::table('assist_blades')->orderBy('nombre')->pluck('nombre')->toArray();
        $ratchetOptions = DB::table('ratchets')->orderBy('nombre')->pluck('nombre')->toArray();
        $bitOptions = DB::table('bits')->orderBy('nombre')->pluck('nombre')->toArray();


        $currentDate = Carbon::parse($event->date);
        $startOfWeek = $currentDate->startOfWeek()->format('Y-m-d');
        $endOfWeek = $currentDate->endOfWeek()->format('Y-m-d');

        // Consulta para verificar si el usuario está apuntado a un evento de la semana actual
        $isRegistered = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::user()->id)
            ->whereBetween('events.date', [$startOfWeek, $endOfWeek])
            ->whereIn('events.beys', ['ranking', 'rankingplus'])
            ->exists();


        // Supongamos que tienes el evento en una variable $event
        $eventDate = \Carbon\Carbon::parse($event->date);

        // Determinar mes y año del evento
        $startOfEventMonth = $eventDate->copy()->startOfMonth();
        $endOfEventMonth   = $eventDate->copy()->endOfMonth();

        // Contar eventos tipo ranking o rankingplus en los que está inscrito
        $rankingTournamentsParticipated = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::user()->id)
            ->whereBetween('events.date', [$startOfEventMonth, $endOfEventMonth])
            ->whereIn('events.beys', ['ranking', 'rankingplus'])
            ->where('assist_user_event.puesto', '!=', 'No presentado')
            ->count();

        // Límite de torneos de ranking al mes
        $maxRankingTournaments = 2;

        // Calcular cuántos le quedan para el mes de ese torneo
        $rankingTournamentsLeft = max(0, $maxRankingTournaments - $rankingTournamentsParticipated);

        $participantes = User::all();

        return view('events.show', compact('event', 'videos', 'assists', 'suscribe', 'hoy', 'bladeOptions', 'assistBladeOptions', 'ratchetOptions', 'bitOptions', 'results', 'extraLines', 'resultsByParticipant', 'isRegistered' , 'rankingTournamentsLeft', 'participantes'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        $regions = Region::all();

        return view('events.edit', compact('event', 'regions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        // Validación
        $data = $request->validate([
            'name' => 'required|min:6',
            'mode' => 'required',
            'city' => 'required',
            'location' => 'required',
            'region_id' => 'required',
            'event_date' => 'required',
            'event_time' => 'required',
        ]);

        // Almacenar las imágenes actuales antes de actualizar el equipo
        $currentImage = $event->image_mod;

        // Procesar la imagen si se proporciona un nuevo archivo
        if ($request->hasFile('image_mod')) {
            $imageData = base64_encode(file_get_contents($request->file('image_mod')));
            $event->image_mod = $imageData;
        }

        // Realizamos el condicional para asignar el texto correspondiente
        if ($request->imagen == 'quedada') {
            $textoImagen = 'Quedada';
        } elseif ($request->imagen == 'ranking') {
            $textoImagen = 'Ranking';
        } elseif ($request->imagen == 'rankingplus') {
            $textoImagen = 'Ranking Plus';
        } elseif ($request->imagen == 'grancopa') {
            $textoImagen = 'Gran Copa';
        } elseif ($request->imagen == 'hasbro') {
            $textoImagen = 'Hasbro';
        } else {
            $textoImagen = 'Copa'; // Si no coincide con ninguno de los valores
        }

        // Restaurar la imagen actual si el formulario se envía sin seleccionar un nuevo archivo de imagen
        if (!$request->hasFile('image_mod') && $currentImage !== null) {
            $event->image_mod = $currentImage;
        }

        // Si el usuario sube una imagen
        if($request['region_id'] == 1) {
            $ruta_imagen = 'upload-events/andalucias2.webp';
        }   elseif($request['region_id'] == 2)  {
            $ruta_imagen = 'upload-events/madrids2.webp';
        }   elseif($request['region_id'] == 4)  {
            $ruta_imagen = 'upload-events/valencias2.webp';
        }   elseif($request['region_id'] == 8)  {
            $ruta_imagen = 'upload-events/canariass2.webp';
        }   elseif($request['region_id'] == 11)  {
            $ruta_imagen = 'upload-events/aragons2.webp';
        }    elseif($request['region_id'] == 5)  {
            $ruta_imagen = 'upload-events/galicias2.webp';
        }    elseif($request['region_id'] == 14)  {
            $ruta_imagen = 'upload-events/asturiass2.webp';
        }    elseif($request['beys'] == 'ranking' || $request['beys'] == 'rankingplus')  {
            $ruta_imagen = 'upload-events/rankingx.png';
        } else {
            $ruta_imagen = 'upload-events/quedada.jpg';
        }

        // Asignar los valores
        $event->name = $data['name'];
        $event->mode = $data['mode'];
        $event->city = $data['city'];
        $event->location = $data['location'];
        $event->status = 'OPEN';
        $event->region_id = $data['region_id'];
        $event->date = $data['event_date'];
        $event->time = $data['event_time'];
        $event->imagen = $ruta_imagen;
        $event->beys = $request->imagen;
        $event->deck = $request['deck'];
        $event->configuration = $request['configuration'];
        $event->iframe = $request['iframe'];
        $event->note = $request['note'];

        $event->save();
        // TODO: Comentar en local
        $this->notificationEdited($event);

        // Redireccionar
        return redirect()->action('App\Http\Controllers\EventController@show', ['event' => $event->id])->with('event', $event);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->action('App\Http\Controllers\EventController@index');
    }

    public function assist(Request $request, Event $event)
    {
        $userId = Auth::id();
        $eventId = $event->id;

        // Verificar si la asistencia ya existe
        $exists = DB::table('assist_user_event')
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->exists();

        if (!$exists) {
            DB::table('assist_user_event')->insert([
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
        }

        return redirect()->back();
    }

    public function addAssist(Request $request, Event $event)
    {
        $userId = $request->participante_id;
        $eventId = $event->id;

        // Verificar si la asistencia ya existe
        $exists = DB::table('assist_user_event')
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->exists();

        if (!$exists) {
            DB::table('assist_user_event')->insert([
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
        }

        return redirect()->back();
    }


    public function noassist(Event $event)
    {
        $sql = DB::table('assist_user_event')
            ->where('user_id', Auth::user()->id)
            ->where('event_id', $event->id)
            ->delete();

        return redirect()->back();
    }

    public function actualizarStatus($id, $status)
    {
        // Buscar el evento por su ID
        $evento = Event::findOrFail($id);

        // Actualizar el nombre del evento con el nuevo nombre proporcionado en la solicitud
        $evento->status = $status;
        $evento->save();
    }

    public function updatePuestos(Request $request, $id)
    {
        // Buscar evento una sola vez
        $event = Event::findOrFail($id);

        // Validar que el usuario sea referee o que el evento tenga challonge e iframe
        if (!Auth::user()->is_jury && (empty($request->input('iframe')) || empty($request->input('challonge')))) {
            return redirect()->back()->with('error', 'Primero tienes que enviar los datos del torneo.');
        }


        // Validar iframe y challonge (requiere URL)
        //if (!Auth::user()->is_jury) {
            $request->validate([
                'iframe' => 'required',
                'challonge' => 'required',
            ]);

            // Actualizar iframe y challonge
            $event->iframe = $request->input('iframe');
            $event->challonge = $request->input('challonge');
            $event->save();
        //}
        // Actualizar status (asumo que este método existe y funciona)
        self::actualizarStatus($event->id, 'PENDING');

        // Validar participantes - que exista array y que cada elemento tenga id y puesto válidos
        $rules = [
            'participantes' => 'required|array',
            'participantes.*.id' => 'required|integer|exists:assist_user_event,user_id',
            'participantes.*.puesto' => 'required|string',
        ];

        $messages = [
            'participantes.required' => 'Debes enviar la lista de participantes.',
            'participantes.array' => 'Participantes debe ser un array.',
            'participantes.*.id.required' => 'Cada participante debe tener un ID válido.',
            'participantes.*.id.exists' => 'Uno de los participantes no está registrado en este evento.',
            'participantes.*.puesto.required' => 'Cada participante debe tener un puesto asignado.',
        ];

        $request->validate($rules, $messages);

        // Actualizar los puestos en la tabla pivot
        foreach ($request->input('participantes') as $participante) {
            $updated = DB::table('assist_user_event')
                ->where('user_id', $participante['id'])
                ->where('event_id', $id)
                ->update(['puesto' => $participante['puesto']]);

            if ($updated === false) {
                return redirect()->back()->with('error', "No se pudo actualizar el puesto para el participante ID {$participante['id']}.");
            }
        }

        if ($event->beys === 'copapaypal' || $event->beys === 'grancopa') {

            // Obtener el ID del trofeo "SBBL Coin"
            $trophyId = DB::table('trophies')->where('name', 'SBBL Coin')->value('id');

            if ($trophyId) {

                // Contar participantes que se presentaron
                $totalParticipantes = DB::table('assist_user_event')
                    ->where('event_id', $id)
                    ->where('puesto', '!=', 'nopresentado')
                    ->count();

                // Coins base: 200/500 * total participantes
                $coinsBase = 0;
                if ($event->beys === 'copapaypal') {
                    $coinsBase = 200 * $totalParticipantes;
                } elseif($event->beys === 'grancopa') {
                    $coinsBase = 500 * $totalParticipantes;
                }

                // Porcentajes por puesto
                $porcentajes = [
                    'primero' => 0.5,
                    'segundo' => 0.3,
                    'tercero' => 0.2,
                ];

                foreach ($request->input('participantes') as $participante) {
                    $puestoTexto = strtolower($participante['puesto']); // aseguramos minúsculas

                    if (isset($porcentajes[$puestoTexto])) {
                        $coins = intval($coinsBase * $porcentajes[$puestoTexto]);

                        $registro = DB::table('profilestrophies')
                            ->where('profiles_id', $participante['id'])
                            ->where('trophies_id', $trophyId)
                            ->first();

                        if ($registro) {
                            DB::table('profilestrophies')
                                ->where('id', $registro->id)
                                ->update(['count' => $registro->count + $coins]);
                        } else {
                            DB::table('profilestrophies')->insert([
                                'profiles_id' => $participante['id'],
                                'trophies_id' => $trophyId,
                                'count' => $coins,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
                self::actualizarStatus($event->id, 'CLOSED');

            }
        }


        return redirect()->back()->with('success', 'Resultados actualizados correctamente.');
    }


    public function actualizarPuntuaciones(Request $request, $id, $mode)
{
    $evento = Event::findOrFail($id);

    if ($evento->beys == 'ranking' || $evento->beys == 'rankingplus') {

        if($request->comment != null) {
            DB::table('event_judge_reviews')->insert([
                'event_id'     => $id,
                'judge_id'     => Auth::user()->id,
                'final_status' => 'approved',
                'comment'      => $request->comment,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Verificar si hay participantes con puesto vacío o null
        $participantesSinPuesto = DB::table('assist_user_event')
            ->where('event_id', $id)
            ->where(function ($query) {
                $query->whereNull('puesto')
                    ->orWhere('puesto', '');
            })
            ->exists();

        if ($participantesSinPuesto) {
            return redirect()->back()->with('error', 'No se han enviado los resultados del torneo.');
        }

        // Obtener todos los participantes válidos
        $participantes = DB::table('assist_user_event')
            ->where('event_id', $id)
            ->where('puesto', '!=', 'nopresentado')
            ->get();

        $eventMode = 'points_x2';

        $totalParticipantes = DB::table('assist_user_event')
            ->where('event_id', $id)
            ->where('puesto', '!=', 'nopresentado')
            ->count();

        // Limitar máximo a 32
        //$totalParticipantes = min($totalParticipantes, 32);

        // Tabla de puntuaciones por rango de jugadores
        if ($totalParticipantes >= 33) {
            $tabla = [7, 6, 5, 4, 3, 2, 1];
        } elseif ($totalParticipantes >= 25) {
            $tabla = [6, 5, 4, 3, 2, 1, 1];
        } elseif ($totalParticipantes >= 17) {
            $tabla = [5, 4, 3, 2, 1, 1, 1];
        } elseif ($totalParticipantes >= 9) {
            $tabla = [4, 3, 2, 1, 1, 1, 1];
        } elseif ($totalParticipantes >= 6) {
            $tabla = [3, 2, 1, 1, 1, 1, 1];
        } else {
            $tabla = [2, 1, 1, 1, 1, 1, 1];
        }

        // Función para convertir puesto a índice en la tabla
        $puestoAIndice = [
            'primero' => 0,
            'segundo' => 1,
            'tercero' => 2,
            'cuarto' => 3,
            'quinto' => 4,
            'septimo' => 5,
        ];

        foreach ($participantes as $participante) {
            $usuarioId = $participante->user_id;
            $puesto = strtolower($participante->puesto);
            $index = $puestoAIndice[$puesto] ?? null;

            $puntos = $index !== null ? $tabla[$index] : 1;

            // Sumar puntos al perfil
            DB::table('profiles')
                ->where('user_id', $usuarioId)
                ->increment($eventMode, $puntos);

            // Guardar en log
            DB::table('points_log')->insert([
                'user_id' => $usuarioId,
                'event_id' => $id,
                'modo' => $eventMode,
                'puntos' => $puntos,
                'puesto' => $puesto,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        self::actualizarStatus($id, 'CLOSE');
        return redirect()->back()->with('success', 'Puntuaciones actualizadas correctamente');
    }

    self::actualizarStatus($id, 'CLOSE');
    return redirect()->back()->with('success', 'Evento cerrado sin puntuaciones');
}



    public function estadoTorneo(Request $request, $id, $estado) {
        if($estado == "invalidar") {

            if($request->comment != null) {
            DB::table('event_judge_reviews')->insert([
                'event_id'     => $id,
                'judge_id'     => Auth::user()->id,
                'final_status' => 'rejected',
                'comment'      => $request->comment,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

            self::actualizarStatus($id, 'INVALID');
            return redirect()->back()->with('success', 'Evento invalidado');

        } elseif($estado == "revisar") {
            self::actualizarStatus($id, 'REVIEW');
            return redirect()->back()->with('success', 'Evento en revisión');
        } elseif($estado == "inscripcion") {
            self::actualizarStatus($id, 'INSCRIPCION');
            return redirect()->back()->with('success', 'Inscripción cerrada');
        }
    }


    public function notification($eventId)
    {
        $regionName = $eventId->region->name;
        $fecha = Carbon::parse($eventId->date);
        $fechaFormateada = $fecha->translatedFormat('d \d\e F \d\e\l Y');

        // Array con los roles de Discord por comunidad autónoma
        $rolesPorComunidad = [
            'Andalucía' => '1206704489990459452',
            'Aragón' => '1209155125654978663',
            'Asturias' => '1209155336033017876',
            'Baleares' => '1209155169917476874',
            'Canarias' => '1209154917449859143',
            'Cantabria' => '1209155399920386109',
            'Castilla La Mancha' => '1209154971229093978',
            'Castilla y León' => '1209154789003239504',
            'Catalunya' => '1209154633227046983',
            'Extremadura' => '1209155220450582618',
            'Galicia' => '1209154737220354048',
            'Rioja' => '1209155480342106132',
            'Madrid' => '1209154530890219520',
            'Murcia' => '1209155058562895944',
            'Navarra' => '1209155367578370059',
            'País Vasco' => '1209154853872345138',
            'Valencia' => '1209154705327132712',
            'Melilla' => '1209155513967845438',
            'Ceuta' => '1209155549468434432',
            // Agrega más comunidades autónomas si es necesario
        ];

        // Obtienes el ID del rol de Discord correspondiente a la región
        $rolId = $rolesPorComunidad[$regionName] ?? '';

        // Construyes el mensaje mencionando la región específica
        $message = "¡Hay un nuevo torneo disponible para " . $eventId->city . "(" . $regionName . ")!";

        // Añades la mención del rol de Discord de la región al mensaje
        $message .= "\n<@&$rolId>";

        // Envías el mensaje al webhook de Discord
        return Http::post(env('DISCORD_WEBHOOK_URL'), [
            'content' => $message,
            'embeds' => [
                [
                    'title' => $eventId->name . " (" . $eventId->mode . ")",
                    'description' => "El día " . $fechaFormateada . " a las " . $eventId->time . ". Inscríbete en: https://sbbl.es/events/" . $eventId->id,
                    'color' => '7506394',
                ]
            ],
        ]);
    }

    public function notificationEdited($event)
    {
        $regionName = $event->region->name;
        $fecha = \Carbon\Carbon::parse($event->date);
        $fechaFormateada = $fecha->translatedFormat('d \d\e F \d\e\l Y');

        // Array con los roles de Discord por comunidad autónoma
        $rolesPorComunidad = [
            'Andalucía' => '1206704489990459452',
            'Aragón' => '1209155125654978663',
            'Asturias' => '1209155336033017876',
            'Baleares' => '1209155169917476874',
            'Canarias' => '1209154917449859143',
            'Cantabria' => '1209155399920386109',
            'Castilla La Mancha' => '1209154971229093978',
            'Castilla y León' => '1209154789003239504',
            'Catalunya' => '1209154633227046983',
            'Extremadura' => '1209155220450582618',
            'Galicia' => '1209154737220354048',
            'Rioja' => '1209155480342106132',
            'Madrid' => '1209154530890219520',
            'Murcia' => '1209155058562895944',
            'Navarra' => '1209155367578370059',
            'País Vasco' => '1209154853872345138',
            'Valencia' => '1209154705327132712',
            'Melilla' => '1209155513967845438',
            'Ceuta' => '1209155549468434432',
        ];

        // ID del rol correspondiente
        $rolId = $rolesPorComunidad[$regionName] ?? '';

        // Mensaje principal
        $message = "⚠️ ¡El torneo de **" . $event->city . "** (" . $regionName . ") ha sido **modificado**!";

        // Añadimos la mención del rol
        $message .= "\n<@&$rolId>";

        // Embed con la info actualizada
        return \Illuminate\Support\Facades\Http::post(env('DISCORD_WEBHOOK_URL'), [
            'content' => $message,
            'embeds' => [
                [
                    'title' => $event->name . " (" . $event->mode . ")",
                    'description' => "📅 Nueva fecha: **" . $fechaFormateada . "** a las **" . $event->time . "**.\n📍 Ubicación: " . $event->location . "\n🔗 Más info: https://sbbl.es/events/" . $event->id,
                    'color' => 16753920, // Naranja para avisos
                ]
            ],
        ]);
    }


    public function getParticipantResults(Request $request, Event $event)
    {
        $participantId = $request->query('id');
        $results = $event->results->where('participant_id', $participantId); // Ajusta según tu relación de modelos

        return response()->json($results);
    }

    public function updateVideo(Request $request, Event $event)
    {
        $request->validate([
            'iframe' => 'required|url',
            'challonge' => 'required|url'
        ]);

        $event->iframe = $request->input('iframe');
        $event->challonge = $request->input('challonge');
        $event->save();

        return redirect()->back()->with('success', 'Datos añadidos correctamente.');
    }

    public function startReview(Event $event)
    {
        $user = auth()->user();

        // Comprobamos si ya existe una revisión del usuario
        if ($event->reviews()->where('referee_id', $user->id)->exists()) {
            return back()->with('error', 'Ya has iniciado la revisión de este evento.');
        }

        // Creamos la revisión vacía en estado 'pending'
        $event->reviews()->create([
            'referee_id' => $user->id,
            'status' => 'pending',
            'comment' => '',
        ]);

        Self::actualizarStatus($event->id, "REVIEW");

        return back()->with('success', 'Revisión iniciada. Ahora puedes completarla.');
    }


    public function submitReview(Request $request, $eventId)
    {
        $user = auth()->user();

        // Verifica que el evento exista
        $event = Event::find($eventId);
        if (!$event) {
            return back()->with('error', 'Evento no encontrado.');
        }

        if ($user->is_referee) {
            // Buscar la revisión pendiente del árbitro
            $review = $event->reviews()->where('referee_id', $user->id)->first();

            if (!$review) {
                return back()->with('error', 'No has iniciado una revisión para este evento.');
            }

            // Actualizar la revisión existente
            $review->update([
                'status'  => $request->status,
                'comment' => $request->comment,
            ]);

            // Verificar si hay 3 revisiones y tomar decisiones
            $reviews = $event->reviews;

            if ($reviews->count() == 3) {
                $allApproved = $reviews->every(fn($r) => $r->status === 'approved');
                $hasRejected = $reviews->contains(fn($r) => $r->status === 'rejected');

                if ($allApproved) {
                    $event->update(['status' => 'approved']);
                } elseif ($hasRejected) {
                    $event->update(['status' => 'requires_judge']);
                }
            }
        }

        if ($user->is_jury && $event->status === 'requires_judge') {
            DB::table('event_judge_reviews')->insert([
                'event_id'     => $event->id,
                'judge_id'     => $user->id,
                'final_status' => $request->final_status,
                'comment'      => $request->comment,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $finalStatus = $request->final_status === 'approved' ? 'approved' : 'rejected';
            $event->update(['status' => $finalStatus]);
        }

        return back()->with('success', 'Revisión registrada.');
    }

    public function destroyReview($eventId, $userId)
    {
        $review = EventReview::where('event_id', $eventId)
                    ->where('referee_id', $userId)
                    ->first();

        if (!$review) {
            return back()->with('error', 'Revisión no encontrada.');
        }

        // Solo el dueño o admin puede borrar
        if (auth()->id() !== (int)$userId && !auth()->user()->is_admin) {
            abort(403, 'No autorizado');
        }

        $review->delete();

        return back()->with('success', 'Revisión eliminada correctamente.');
    }



}
