<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();
        return view('productos.index', compact('productos'));
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'seccion' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'fotos' => 'nullable|image|max:2048',
            'colores.*.nombre' => 'nullable|string|max:50',
            'colores.*.foto' => 'nullable|image|max:2048',
            'tallas.*' => 'nullable|string|max:50',
        ]);

        if ($request->hasFile('fotos')) {
            $image = $request->file('fotos');
            $imageData = 'data:' . $image->getClientMimeType() . ';base64,' . base64_encode(file_get_contents($image->getRealPath()));
            $validated['fotos'] = $imageData;
        }

        // Procesamos atributos
        $atributos = [
            'colores' => [],
            'tallas' => $request->input('tallas', [])
        ];

        if ($request->has('colores')) {
            foreach ($request->colores as $index => $color) {
                $colorData = ['nombre' => $color['nombre'] ?? null, 'foto' => null];
                if (isset($color['foto']) && $request->file("colores.$index.foto")) {
                    $img = $request->file("colores.$index.foto");
                    $colorData['foto'] = 'data:' . $img->getClientMimeType() . ';base64,' . base64_encode(file_get_contents($img->getRealPath()));
                }
                $atributos['colores'][] = $colorData;
            }
        }

        $validated['atributos'] = $atributos;

        Producto::create($validated);

        return redirect()->route('productos.index')->with('success','Producto creado correctamente');
    }


public function edit(Producto $producto)
{
    // Decodificar JSON solo si es string
    $atributos = is_string($producto->atributos) ? json_decode($producto->atributos, true) : $producto->atributos;

    // Extraer colores y tallas
    $producto->colores = $atributos['colores'] ?? [];
    $producto->tallas  = $atributos['tallas'] ?? [];

    return view('productos.edit', compact('producto'));
}



public function update(Request $request, Producto $producto)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'seccion' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'fotos' => 'nullable|image|max:2048'
    ]);

    // Foto principal
    if ($request->hasFile('fotos')) {
        $image = $request->file('fotos');
        $mime = $image->getClientMimeType();
        $validated['fotos'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($image->getRealPath()));
    } else {
        $validated['fotos'] = $producto->fotos;
    }

    // Colores
    $colores = collect($request->input('colores', []))->map(function($c, $i) use ($request) {
        // Si han marcado eliminar, se omite
        if(isset($c['_eliminar']) && $c['_eliminar'] == 1) return null;

        // Foto nueva
        if($request->hasFile("colores.$i.foto")) {
            $file = $request->file("colores.$i.foto");
            $c['foto'] = 'data:' . $file->getClientMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        } elseif(isset($c['foto_actual'])) {
            // Mantener la foto existente si no hay nueva
            $c['foto'] = $c['foto_actual'];
        }

        unset($c['foto_actual'], $c['_eliminar']);
        return $c;
    })->filter()->values()->toArray();

    // Tallas
    $tallas = collect($request->input('tallas', []))
        ->reject(fn($t) => is_array($t) && isset($t['_eliminar']) && $t['_eliminar'] == 1)
        ->map(fn($t) => is_array($t) ? $t[0] : $t)
        ->values()
        ->toArray();

    // Guardar todo dentro de atributos
    $atributos = [
        'colores' => $colores,
        'tallas'  => $tallas,
    ];

    // Actualizar producto
    $producto->update(array_merge($validated, [
        'atributos' => json_encode($atributos),
    ]));

    return redirect()->route('productos.index')->with('success','Producto actualizado correctamente');
}

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')->with('success','Producto eliminado');
    }
}
