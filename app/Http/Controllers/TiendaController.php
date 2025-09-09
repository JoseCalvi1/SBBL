<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productos = Tienda::all();

        return view('tienda.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tienda.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fotos.*' => 'nullable|image|mimes:jpg,jpeg,png,webp',
            'enlaces' => 'nullable|string',
        ]);

        $fotos = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $imagen) {
                $fotos[] = base64_encode(file_get_contents($imagen->getRealPath()));
            }
        }

        $enlaces = array_filter(array_map('trim', explode("\n", $request->input('enlaces'))));

        Tienda::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'fotos' => $fotos,
            'enlaces' => $enlaces,
        ]);

        return redirect()->route('tienda.index')->with('success', 'Producto creado con éxito.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function show(Tienda $tienda)
    {
        $producto = Tienda::findOrFail($tienda->id);

        return view('tienda.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function edit(Tienda $tienda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tienda $tienda)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tienda  $tienda
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tienda $tienda)
    {
        //
    }
}
