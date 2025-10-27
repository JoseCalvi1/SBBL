<?php

namespace App\Http\Controllers;

use App\Mail\PedidoMail;
use App\Mail\PedidoTiendaMail;
use App\Models\Carrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

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

    public function checkout(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email',
            'direccion' => 'required|string|max:500',
            'metodo_pago' => 'required|in:paypal,coins',
        ]);

        // 🛒 Obtener carrito por session_id
        $sessionId = $request->session()->getId();
        $carrito = Carrito::with('productos')->where('session_id', $sessionId)->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío.');
        }

        // Calcular totales
        $totalEuros = $carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario);
        $totalLagartos = $totalEuros * 150;
        $referencia = 'PED-' . strtoupper(Str::random(8));

        // Guardar datos del comprador
        $carrito->update([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'direccion' => $request->direccion,
            'referencia' => $referencia,
            'metodo_pago' => $request->metodo_pago,
            'direccion' => $request->direccion,
            'enviado' => true,
            'estado_pago' => 'pendiente',
            'estado_envio' => 'pendiente',
            'total' => $totalEuros,
            'total_lagartos' => $totalLagartos,
            'solicitado' => true
        ]);

        // 🔹 Elegir método de pago
        if ($request->metodo_pago === 'coins') {
            return $this->pagoConCoins($carrito, $totalLagartos, $request, $referencia);
        }

        if ($request->metodo_pago === 'paypal') {
            return $this->pagoConPayPal($carrito, $totalEuros, $request, $referencia);
        }

        return back()->with('error', 'Método de pago inválido.');
    }

    private function pagoConCoins($carrito, $totalLagartos, Request $request, $referencia)
    {
        if (!Auth::check()) {
            return back()->with('error', 'Debes iniciar sesión para pagar con 🦎.');
        }

        $userId = Auth::user()->id;

        // 🦎 Obtener ID del trofeo SBBL Coin
        $trophyId = DB::table('trophies')->where('name', 'SBBL Coin')->value('id');
        if (!$trophyId) {
            return back()->with('error', 'No se encontró el trofeo SBBL Coin.');
        }

        // 🦎 Consultar saldo actual
        $coinCount = DB::table('profilestrophies')
            ->where('trophies_id', $trophyId)
            ->where('profiles_id', $userId)
            ->value('count') ?? 0;

        if ($coinCount < $totalLagartos) {
            return back()->with('error', 'No tienes suficientes 🦎 para completar la compra.');
        }

        // 🧮 Restar coins
        DB::table('profilestrophies')
            ->where('trophies_id', $trophyId)
            ->where('profiles_id', $userId)
            ->update(['count' => $coinCount - $totalLagartos]);

        // 📧 Enviar correo con el pedido (solo a tienda)
        try {
            Log::info("Enviando correo de pedido con coins a tienda...");
            Mail::to('tienda@sbbl.es')->send(new PedidoTiendaMail(
                $request->nombre,
                $request->email,
                $request->direccion,
                'SBBL Coins',
                $carrito,
                $referencia,
                $totalLagartos
            ));
            Log::info("Correo enviado correctamente a tienda.");
        } catch (\Exception $e) {
            Log::error("Error enviando correo de pedido: ".$e->getMessage());
        }

        // 🛒 Vaciar carrito
        // 3️⃣ Desasociar la sesión actual del carrito
        $carrito->session_id = null;
        $carrito->save();

        // 4️⃣ Regenerar la sesión (nuevo carrito vacío)
        session()->invalidate();
        session()->regenerateToken();
        session()->regenerate();

        return redirect()->route('carrito.index')->with('success', 'Pedido realizado con 🦎 correctamente.');
    }

    private function pagoConPayPal($carrito, $totalEuros, Request $request, $referencia)
    {
        // Solo pagamos el 50% en esta web
        $totalPago = $totalEuros * 0.5;

        // Nombre del usuario
        $usuario = Auth::check() ? Auth::user()->name : $request->nombre;

        // Descripción para PayPal con referencia
        $descripcion = urlencode("Pedido {$referencia} de {$usuario}");

        // Generar enlace de PayPal (donación)
        $paypalUrl = "https://www.paypal.com/donate?business=info%40sbbl.es"
                . "&item_name={$descripcion}"
                . "&currency_code=EUR"
                . "&amount=" . number_format($totalPago, 2, '.', '');

        // 📧 Enviar correo a tienda con pedido pendiente
        try {
            Log::info("Enviando correo de pedido PayPal a tienda...");
            Mail::to('tienda@sbbl.es')->send(new PedidoTiendaMail(
                $request->nombre,
                $request->email,
                $request->direccion,
                'PayPal (50%)',
                $carrito,
                $referencia,
                $totalEuros
            ));
            Log::info("Correo enviado correctamente a tienda.");
        } catch (\Exception $e) {
            Log::error("Error enviando correo de pedido PayPal: ".$e->getMessage());
            return back()->with('error', 'Error enviando correo del pedido. Por favor, intenta de nuevo.');
        }

        // 🛒 Vaciar carrito
        // 3️⃣ Desasociar la sesión actual del carrito
        $carrito->session_id = null;
        $carrito->save();

        // 4️⃣ Regenerar la sesión (nuevo carrito vacío)
        session()->invalidate();
        session()->regenerateToken();
        session()->regenerate();

        // Redirigir al enlace de PayPal
        return redirect()->away($paypalUrl);
    }


    public function paypalSuccess(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $provider->capturePaymentOrder($request->token);
        return redirect()->route('carrito.index')->with('success', 'Pago completado correctamente.');
    }

    public function paypalCancel()
    {
        return redirect()->route('carrito.index')->with('error', 'Pago cancelado.');
    }

}

