<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Region;
use App\Models\User;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function index()
    {
       $events = Event::orderBy('date', 'DESC')->get();
       return view('events.index', compact('events'));
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
        // Validación
        $data = $request->validate([
            'name' => 'required|min:6',
            'mode' => 'required',
            'location' => 'required',
            'region_id' => 'required',
            'event_date' => 'required',
        ]);

        // Obtener ruta de la imagen
        /*$ruta_imagen = "No hay imagen";
        if($request['imagen']) {
            $ruta_imagen = $request['imagen']->store('upload-events', 'public');
        }*/

        // Si el usuario sube una imagen
        if($request['imagen'])
        {
            $ruta_imagen = 'upload-events/'.$request['imagen'].'.jpg';
        }

        // Almacenar datos en la BD (sin modelos)
        DB::table('events')->insert([
            'name' => $data['name'],
            'mode' => $data['mode'],
            'location' => $data['location'],
            'created_by' => Auth::user()->id,
            'status' => 'OPEN',
            'region_id' => $data['region_id'],
            'date' => $data['event_date'],
            'imagen' => $ruta_imagen,
        ]);

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

        return view('events.show', compact('event', 'videos', 'assists', 'suscribe', 'hoy'));
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
            'location' => 'required',
            'region_id' => 'required',
            'event_date' => 'required',
            'iframe' => 'min:6',
        ]);

        // Si el usuario sube una imagen
        if($request['imagen'])
        {
            $event->imagen = 'upload-events/'.$request['imagen'].'.jpg';
        }

        // Asignar los valores
        $event->name = $data['name'];
        $event->location = $data['location'];
        $event->region_id = $data['region_id'];
        $event->date = $data['event_date'];
        $event->iframe = $data['iframe'];

        $event->save();

        // Redireccionar
        return redirect()->action('App\Http\Controllers\EventController@index');
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
        DB::table('assist_user_event')->insert([
            'user_id' => Auth::user()->id,
            'event_id' => $event->id,
        ]);

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

        $request->validate([
            'participantes' => 'required|array', // Debe ser un array
            'participantes.*.id' => 'exists:assist_user_event,id', // Cada ID de participante debe existir en la tabla assists
            'participantes.*.puesto' => 'required|in:participante,primero,segundo,tercero', // Validación del puesto para cada participante
        ]);

        // Iterar sobre los participantes y actualizar sus puestos
        foreach ($request->input('participantes') as $participante) {
            $exist = DB::table('assist_user_event')->where('id', $participante['id'])->exists();

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
        self::actualizarStatus($id, 'CLOSE');

        // Obtener los IDs de los participantes que están entre los tres primeros puestos
        $participantes = DB::table('assist_user_event')
            ->where('event_id', $id)
            ->where('puesto', '!=', 'nopresentado')
            ->get();

        $eventMode = ($mode == "beybladex") ? 'points_x1' : 'points_s3';

        $totalParticipantes = DB::table('assist_user_event')
        ->where('event_id', $id)
        ->count();

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

        return redirect()->back()->with('success', 'Puntuaciones actualizadas correctamente');
    }
}
