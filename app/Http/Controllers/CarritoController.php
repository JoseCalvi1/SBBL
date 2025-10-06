<?php

namespace App\Http\Controllers;

use App\Mail\PedidoMail;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CarritoController extends Controller
{
    public function index()
    {
        // Traemos todos los productos disponibles
        $productos = Producto::all();

        // Asegurarnos de que colores y tallas siempre existan
        $productos->transform(function($producto) {
            $atributos = $producto->atributos ?? [];
            $producto->colores = $atributos['colores'] ?? [];
            $producto->tallas  = $atributos['tallas'] ?? [];
            return $producto;
        });

        return view('carrito.index', compact('productos'));
    }

    // Mostrar productos del carrito
    public function show(Request $request)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::firstOrCreate(['session_id' => $sessionId]);
        return view('carrito.show', compact('carrito'));
    }

    public function add(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::firstOrCreate(['session_id' => $sessionId]);
        $producto = Producto::findOrFail($productoId);

        $atributosProducto = $producto->atributos ?? [];
        $coloresProducto = $atributosProducto['colores'] ?? [];

        // Procesar color seleccionado
        $colorSeleccionado = null;
        $colorInput = $request->input('color'); // viene como la foto base64

        if ($colorInput) {
            foreach ($coloresProducto as $color) {
                if ($color['foto'] === $colorInput) {
                    $colorSeleccionado = $color; // guardamos nombre y foto
                    break;
                }
            }
        }

        // Procesar talla seleccionada
        $tallaSeleccionada = $request->input('talla') ?? null;

        // Solo guardamos el color/talla seleccionados
        $atributosCarrito = [
            'color' => $colorSeleccionado, // array con nombre y foto o null
            'talla' => $tallaSeleccionada
        ];

        // Al añadir al carrito
        $hash = md5(json_encode($atributosCarrito));

        $carrito->productos()->attach($producto->id, [
            'cantidad' => $request->input('cantidad', 1),
            'precio_unitario' => $producto->precio,
            'atributos' => json_encode($atributosCarrito),
            'hash' => $hash, // agregamos el hash para identificar la combinación
            'created_at' => now(),
            'updated_at' => now()
        ]);


        return redirect()->route('carrito.show')->with('success','Producto añadido al carrito');
    }


    public function checkout(Request $request)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)->firstOrFail();

        // Guardar los datos del usuario
        $carrito->update($request->only('nombre','email','direccion'));
        $carrito->enviado = true;
        $carrito->save();

        // Enviar email a la tienda con los productos y atributos
        Mail::to('tienda@sbbl.es')->send(new \App\Mail\PedidoMail($carrito));

        return redirect()->route('carrito.show')->with('success','Pedido enviado con éxito');
    }

    public function update(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)->firstOrFail();

        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $hash = $request->input('hash');

        // Actualizar solo el pivot que coincida con hash
        DB::table('carrito_productos')
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $productoId)
            ->where('hash', $hash)
            ->update([
                'cantidad' => $cantidad,
                'updated_at' => now(),
            ]);

        return redirect()->route('carrito.show')->with('success','Cantidad actualizada');
    }


    public function remove(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)->firstOrFail();

        $hash = $request->input('hash'); // recibimos el hash desde el formulario de eliminación
        $carrito->productos()
            ->wherePivot('hash', $hash)
            ->detach($productoId);

        return redirect()->route('carrito.show')->with('success','Producto eliminado');
    }

}

