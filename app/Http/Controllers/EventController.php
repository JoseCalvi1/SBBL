<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::all();

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('events.create');
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
            'location' => 'required',
            'event_date' => 'required',
        ]);

        // Obtener ruta de la imagen
        $ruta_imagen = "No hay imagen";
        if($request['imagen']) {
            $ruta_imagen = $request['imagen']->store('upload-events', 'public');
        }

        // Almacenar datos en la BD (sin modelos)
        DB::table('events')->insert([
            'name' => $data['name'],
            'location' => $data['location'],
            'date' => $data['event_date'],
            'imagen' => $ruta_imagen,
        ]);

        return redirect()->action('App\Http\Controllers\EventController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
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
        ]);

        // Si el usuario sube nueva imagen
        if($request['imagen'])
        {
            $ruta_imagen = $request['imagen']->store('upload-events', 'public');
            $event->imagen = $ruta_imagen;
        }

        // Asignar los valores
        $event->name = $data['name'];
        $event->location = $data['location'];

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
        //
    }
}
