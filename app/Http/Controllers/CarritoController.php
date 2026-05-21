<?php

namespace App\Http\Controllers;

use App\Mail\PedidoTiendaMail;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CarritoController extends Controller
{
    public function index()
    {
        $productos = Producto::all();

        $productos->transform(function($producto) {
            $atributos = $producto->atributos ?? [];
            $producto->colores = $atributos['colores'] ?? [];
            $producto->tallas  = $atributos['tallas'] ?? [];
            return $producto;
        });

        return view('carrito.index', compact('productos'));
    }

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

        $colorSeleccionado = null;
        $colorInput = $request->input('color');

        if ($colorInput) {
            foreach ($coloresProducto as $color) {
                if ($color['foto'] === $colorInput) {
                    $colorSeleccionado = $color;
                    break;
                }
            }
        }

        $tallaSeleccionada = $request->input('talla') ?? null;

        $atributosCarrito = [
            'color' => $colorSeleccionado,
            'talla' => $tallaSeleccionada
        ];

        $hash = md5(json_encode($atributosCarrito));

        $carrito->productos()->attach($producto->id, [
            'cantidad' => $request->input('cantidad', 1),
            'precio_unitario' => $producto->precio,
            'atributos' => json_encode($atributosCarrito),
            'hash' => $hash,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('carrito.show')->with('success','Producto añadido al pedido.');
    }

    public function update(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)->firstOrFail();

        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $hash = $request->input('hash');

        DB::table('carrito_productos')
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $productoId)
            ->where('hash', $hash)
            ->update([
                'cantidad' => $cantidad,
                'updated_at' => now(),
            ]);

        return redirect()->route('carrito.show')->with('success','Cantidades actualizadas.');
    }

    public function remove(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)->firstOrFail();

        $hash = $request->input('hash');
        $carrito->productos()
            ->wherePivot('hash', $hash)
            ->detach($productoId);

        return redirect()->route('carrito.show')->with('success','Producto eliminado del pedido.');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        $sessionId = $request->session()->getId();
        $carrito = Carrito::with('productos')->where('session_id', $sessionId)->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return back()->with('error', 'Tu pedido está vacío.');
        }

        $totalEuros = $carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario);
        $referencia = 'NAC-' . strtoupper(Str::random(6));

        $carrito->update([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'direccion' => 'Recogida Presencial - BeyCon (Nacional)',
            'referencia' => $referencia,
            'metodo_pago' => 'paypal', // Forzamos PayPal
            'enviado' => false,
            'estado_pago' => 'pendiente',
            'estado_envio' => 'preparando',
            'total' => $totalEuros,
            'total_lagartos' => 0, // Anulamos los lagartos
            'solicitado' => true
        ]);

        return $this->pagoConPayPal($carrito, $totalEuros, $request, $referencia);
    }

    private function pagoConPayPal($carrito, $totalEuros, Request $request, $referencia)
    {
        $totalPago = $totalEuros;

        $usuario = Auth::check() ? Auth::user()->name . ' #' . str_pad(Auth::id(), 4, '0', STR_PAD_LEFT) : $request->nombre;

        $nombresProductos = [];
        foreach ($carrito->productos as $prod) {
            $attr = json_decode($prod->pivot->atributos, true);
            $tallaStr = isset($attr['talla']) ? " [Talla {$attr['talla']}]" : "";
            $nombresProductos[] = $prod->pivot->cantidad . "x " . $prod->nombre . $tallaStr;
        }
        $detalleProductos = implode(", ", $nombresProductos);

        $descripcion = urlencode(substr("SBBL Nacional - {$detalleProductos} - {$usuario}", 0, 120));

        $paypalUrl = "https://www.paypal.com/donate?business=info%40sbbl.es"
                . "&item_name={$descripcion}"
                . "&currency_code=EUR"
                . "&amount=" . number_format($totalPago, 2, '.', '');

        // --- NUEVO: REGISTRO EN LA TESORERÍA ---
        // Calculamos una comisión aproximada de PayPal (1.2% + 0.35€) para donaciones,
        // aunque podrás editarla en el panel de tesorería cuando veas el extracto real.
        $feeEstimado = round(($totalPago * 0.012) + 0.35, 2);

        \App\Models\TreasuryLog::create([
            'type' => 'ingreso',
            'category' => 'Tienda',
            'gross_amount' => $totalPago,
            'fee' => $feeEstimado,
            'net_amount' => $totalPago - $feeEstimado,
            'status' => 'pendiente', // Queda pendiente hasta que confirmes que ha llegado a PayPal
            'description' => "Precompra Nacional: {$referencia} - {$usuario}",
            'reference_id' => $referencia,
            'user_id' => Auth::check() ? Auth::id() : null,
        ]);
        // ----------------------------------------

        try {
            Mail::to('tienda@sbbl.es')->send(new PedidoTiendaMail(
                $request->nombre, $request->email, 'Recogida Presencial', 'PayPal', $carrito, $referencia, $totalEuros
            ));
        } catch (\Exception $e) {
            Log::error("Error enviando correo: ".$e->getMessage());
        }

        $carrito->session_id = null;
        $carrito->save();

        session()->invalidate();
        session()->regenerateToken();
        session()->regenerate();

        return redirect()->away($paypalUrl);
    }

    public function paypalSuccess(Request $request)
    {
        return redirect()->route('carrito.index')->with('success', '¡Precompra completada! Nos vemos en el Nacional.');
    }

    public function paypalCancel()
    {
        return redirect()->route('carrito.index')->with('error', 'El pago fue cancelado. No hemos reservado tu pedido.');
    }
}
