<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        $query = Event::where('date', '>=', '2024-06-31')
                    ->orderBy('date', 'DESC');

        // Aplicar filtro por estado si se selecciona uno
        if ($estado) {
            $query->where('status', $estado);
        }

        // Aplicar filtro por beys si se selecciona uno
        if ($beys == 'ranking') {
            $query->whereIn('beys', ['ranking', 'rankingplus']);
        }

        // Obtener los eventos filtrados
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
            $ruta_imagen = 'upload-events/CartelAndalucia.webp';
        }   elseif($request['region_id'] == 2)  {
            $ruta_imagen = 'upload-events/CartelMadrid.webp';
        }   elseif($request['region_id'] == 4)  {
            $ruta_imagen = 'upload-events/ValenciaCartel.webp';
        }   elseif($request['region_id'] == 8)  {
            $ruta_imagen = 'upload-events/CanariasBase.png';
        }   else {
            $ruta_imagen = 'upload-events/rankingx.jpg';
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
        //Self::notification(Event::find($eventId));

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
        $assists = $event->users()->get();
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


        $currentDate = Carbon::now();
        $startOfWeek = $currentDate->startOfWeek()->format('Y-m-d');
        $endOfWeek = $currentDate->endOfWeek()->format('Y-m-d');

        // Consulta para verificar si el usuario está apuntado a un evento de la semana actual
        $isRegistered = DB::table('assist_user_event')
            ->join('events', 'assist_user_event.event_id', '=', 'events.id')
            ->where('assist_user_event.user_id', Auth::user()->id)
            ->whereBetween('events.date', [$startOfWeek, $endOfWeek])
            ->exists();

        return view('events.show', compact('event', 'videos', 'assists', 'suscribe', 'hoy', 'bladeOptions', 'assistBladeOptions', 'ratchetOptions', 'bitOptions', 'results', 'extraLines', 'resultsByParticipant', 'isRegistered'));
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
            $ruta_imagen = 'upload-events/CartelAndalucia.webp';
        }   elseif($request['region_id'] == 2)  {
            $ruta_imagen = 'upload-events/CartelMadrid.webp';
        }   elseif($request['region_id'] == 4)  {
            $ruta_imagen = 'upload-events/ValenciaCartel.webp';
        }   elseif($request['region_id'] == 8)  {
            $ruta_imagen = 'upload-events/CanariasBase.png';
        }elseif($request['beys'] == 'ranking' || $request['beys'] == 'rankingplus')  {
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

        self::actualizarStatus($id, 'PENDING');

        /*$request->validate([
            'participantes' => 'required|array', // Debe ser un array
            'participantes.*.id' => 'exists:assist_user_event,id', // Cada ID de participante debe existir en la tabla assists
            'participantes.*.puesto' => 'required|in:participante,primero,segundo,tercero', // Validación del puesto para cada participante
        ]);*/

        // Iterar sobre los participantes y actualizar sus puestos
        foreach ($request->input('participantes') as $participante) {
            $exist = DB::table('assist_user_event')->where('user_id', $participante['id'])->exists();

            if ($exist) {
                DB::table('assist_user_event')
                    ->where('user_id', $participante['id'])
                    ->where('event_id', $id)
                    ->update(['puesto' => $participante['puesto']]);
            }
        }

        return redirect()->back()->with('success', 'Puestos actualizados correctamente');
    }

    public function actualizarPuntuaciones(Request $request, $id, $mode)
    {

        $evento = Event::findOrFail($id);

        if($evento->beys == 'ranking' || $evento->beys == 'rankingplus') {

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

            // Obtener los IDs de los participantes que están entre los tres primeros puestos
            $participantes = DB::table('assist_user_event')
                ->where('event_id', $id)
                ->where('puesto', '!=', 'nopresentado')
                ->get();

            $eventMode = ($mode == "beybladex") ? 'points_x1' : 'points_s3';

            $totalParticipantes = DB::table('assist_user_event')
                ->where('event_id', $id)
                ->count();

            // Limitar el número máximo de participantes a 12
            $totalParticipantes = min($totalParticipantes, 12);

            // Calcular el número de participantes que quedarían en los tres primeros puestos
            $participantesTresPrimeros = floor($totalParticipantes / 4);

            // Actualizar las puntuaciones de los perfiles de los participantes
            foreach ($participantes as $participante) {
                // Obtener el ID del usuario asociado a este participante
                $usuarioId = $participante->user_id;

                // Actualizar la puntuación del usuario en la tabla de perfiles
                if ($participante->puesto === 'primero') {
                    DB::table('profiles')
                        ->where('user_id', $usuarioId)
                        ->increment($eventMode, 1 + $participantesTresPrimeros + 2); // Añadir puntos al primero
                } elseif ($participante->puesto === 'segundo') {
                    DB::table('profiles')
                        ->where('user_id', $usuarioId)
                        ->increment($eventMode, 1 + $participantesTresPrimeros + 1); // Añadir puntos al segundo
                } elseif ($participante->puesto === 'tercero') {
                    DB::table('profiles')
                        ->where('user_id', $usuarioId)
                        ->increment($eventMode, 1 + $participantesTresPrimeros); // Añadir puntos al tercero
                } else {
                    DB::table('profiles')
                        ->where('user_id', $usuarioId)
                        ->increment($eventMode, 1); // Añadir 1 punto al perfil
                }
            }
            self::actualizarStatus($id, 'CLOSE');
            return redirect()->back()->with('success', 'Puntuaciones actualizadas correctamente');
        }
        self::actualizarStatus($id, 'CLOSE');
        return redirect()->back()->with('success', 'Evento cerrado sin puntuaciones');
    }

    public function invalidarTorneo(Request $request, $id) {
        self::actualizarStatus($id, 'INVALID');

        return redirect()->back()->with('success', 'Evento invalidado');
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
        $message = "¡Hay un nuevo torneo disponible para $regionName!";

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


}
