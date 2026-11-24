<?php

namespace App\Http\Controllers;

use App\Mail\PedidoTiendaMail;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\ShopSetting;
use App\Services\Payment\PayPalService;
use App\Services\Payment\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CarritoController extends Controller
{
    protected $paypalService;
    protected $treasuryService;

    public function __construct(PayPalService $paypalService, TreasuryService $treasuryService)
    {
        $this->paypalService = $paypalService;
        $this->treasuryService = $treasuryService;
    }

    public function index()
    {
        $productos = Producto::orderByDesc('id')->get();
        $shopSettings = ShopSetting::current();

        $productos->transform(function ($producto) {
            $atributos = is_string($producto->atributos)
                ? json_decode($producto->atributos, true)
                : ($producto->atributos ?? []);
            $producto->colores = $atributos['colores'] ?? [];
            $producto->tallas  = $atributos['tallas'] ?? [];
            return $producto;
        });

        return view('carrito.index', compact('productos', 'shopSettings'));
    }

    public function show(Request $request)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::with('productos')
            ->where('session_id', $sessionId)
            ->where('estado_pago', 'pendiente')
            ->first();

        if (!$carrito) {
            $carrito = Carrito::create([
                'session_id'  => $sessionId,
                'user_id'     => Auth::check() ? Auth::id() : null,
                'estado_pago' => 'pendiente',
            ]);
            $carrito->load('productos');
        }

        $shopSettings = ShopSetting::current();
        $paypalClientId = $this->paypalService->getClientId();
        $paypalCurrency = $this->paypalService->getCurrency();

        return view('carrito.show', compact('carrito', 'shopSettings', 'paypalClientId', 'paypalCurrency'));
    }

    public function add(Request $request, $productoId)
    {
        $producto = Producto::findOrFail($productoId);
        $cantidadNueva = max(1, (int) $request->input('cantidad', 1));

        if ($producto->stock <= 0) {
            return back()->with('error', "El artículo «{$producto->nombre}» está agotado.");
        }

        $sessionId = $request->session()->getId();
        $carrito = Carrito::firstOrCreate(
            ['session_id' => $sessionId, 'estado_pago' => 'pendiente'],
            ['user_id' => Auth::check() ? Auth::id() : null]
        );

        // Comprobar cuántas unidades de este producto tiene ya en el carrito (sumando variantes)
        $enCarritoTotalProducto = (int) DB::table('carrito_productos')
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $producto->id)
            ->sum('cantidad');

        if (($enCarritoTotalProducto + $cantidadNueva) > $producto->stock) {
            return back()->with('error', "Stock insuficiente para «{$producto->nombre}». Disponibles: {$producto->stock}.");
        }

        $atributosProducto = is_string($producto->atributos)
            ? json_decode($producto->atributos, true)
            : ($producto->atributos ?? []);
        $coloresProducto = $atributosProducto['colores'] ?? [];

        $colorSeleccionado = null;
        $colorInput = $request->input('color');

        if ($colorInput) {
            foreach ($coloresProducto as $color) {
                if (($color['foto'] ?? null) === $colorInput) {
                    $colorSeleccionado = $color;
                    break;
                }
            }
        }

        $tallaSeleccionada = $request->input('talla') ?? null;

        $atributosCarrito = [
            'color' => $colorSeleccionado,
            'talla' => $tallaSeleccionada,
        ];

        $hash = md5(json_encode($atributosCarrito));

        $existingPivot = DB::table('carrito_productos')
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $producto->id)
            ->where('hash', $hash)
            ->first();

        if ($existingPivot) {
            DB::table('carrito_productos')
                ->where('id', $existingPivot->id)
                ->update([
                    'cantidad'        => $existingPivot->cantidad + $cantidadNueva,
                    'precio_unitario' => $producto->precio,
                    'updated_at'      => now(),
                ]);
        } else {
            $carrito->productos()->attach($producto->id, [
                'cantidad'        => $cantidadNueva,
                'precio_unitario' => $producto->precio,
                'atributos'       => json_encode($atributosCarrito),
                'hash'            => $hash,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return redirect()->route('carrito.show')->with('success', 'Artículo añadido al carrito.');
    }

    public function update(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)
            ->where('estado_pago', 'pendiente')
            ->firstOrFail();

        $producto = Producto::findOrFail($productoId);
        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $hash = $request->input('hash');

        $otrasVariantes = (int) DB::table('carrito_productos')
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $productoId)
            ->where('hash', '!=', $hash)
            ->sum('cantidad');

        if (($otrasVariantes + $cantidad) > $producto->stock) {
            return redirect()->route('carrito.show')
                ->with('error', "Solo quedan {$producto->stock} unidad(es) en stock de «{$producto->nombre}».");
        }

        DB::table('carrito_productos')
            ->where('carrito_id', $carrito->id)
            ->where('producto_id', $productoId)
            ->where('hash', $hash)
            ->update([
                'cantidad'        => $cantidad,
                'precio_unitario' => $producto->precio,
                'updated_at'      => now(),
            ]);

        return redirect()->route('carrito.show')->with('success', 'Cantidades actualizadas.');
    }

    public function remove(Request $request, $productoId)
    {
        $sessionId = $request->session()->getId();
        $carrito = Carrito::where('session_id', $sessionId)
            ->where('estado_pago', 'pendiente')
            ->firstOrFail();

        $hash = $request->input('hash');
        $carrito->productos()
            ->wherePivot('hash', $hash)
            ->detach($productoId);

        return redirect()->route('carrito.show')->with('success', 'Artículo eliminado del carrito.');
    }

    /**
     * Crea la orden PayPal Orders API v2 para la compra de Merch (soporta invitados y usuarios registrados).
     */
    public function createPaypalOrder(Request $request)
    {
        $settings = ShopSetting::current();

        $data = $request->validate([
            'nombre'              => 'required|string|max:255',
            'email'               => 'required|email|max:255',
            'telefono'            => 'nullable|string|max:30',
            'tipo_entrega'        => 'required|in:recogida,envio',
            'direccion_calle'     => 'nullable|string|max:255',
            'direccion_cp'        => 'nullable|string|max:15',
            'direccion_ciudad'    => 'nullable|string|max:100',
            'direccion_provincia' => 'nullable|string|max:100',
        ]);

        $tipoEntrega = $data['tipo_entrega'];

        if ($tipoEntrega === 'envio' && !$settings->shipping_enabled) {
            return response()->json(['error' => 'El envío a domicilio no está disponible actualmente.'], 422);
        }

        if ($tipoEntrega === 'recogida' && !$settings->pickup_enabled) {
            return response()->json(['error' => 'La recogida presencial no está disponible actualmente.'], 422);
        }

        if ($tipoEntrega === 'envio') {
            if (
                empty($data['direccion_calle']) ||
                empty($data['direccion_cp']) ||
                empty($data['direccion_ciudad']) ||
                empty($data['direccion_provincia']) ||
                empty($data['telefono'])
            ) {
                return response()->json([
                    'error' => 'Completa la dirección postal completa y teléfono de contacto para el envío a domicilio.',
                ], 422);
            }
        }

        $sessionId = $request->session()->getId();
        $carrito = Carrito::with('productos')
            ->where('session_id', $sessionId)
            ->where('estado_pago', 'pendiente')
            ->first();

        if (!$carrito || $carrito->productos->isEmpty()) {
            return response()->json(['error' => 'Tu carrito está vacío.'], 422);
        }

        // Comprobar stock real por producto (agrupando variantes del mismo producto)
        $cantidadesPorProducto = $carrito->productos->groupBy('id')->map(function ($items) {
            return $items->sum(fn($i) => (int) $i->pivot->cantidad);
        });

        foreach ($cantidadesPorProducto as $prodId => $qtyTotal) {
            $prodBd = Producto::find($prodId);
            if (!$prodBd || $prodBd->stock < $qtyTotal) {
                $disp = $prodBd ? $prodBd->stock : 0;
                $nom = $prodBd ? $prodBd->nombre : 'Artículo';
                return response()->json([
                    'error' => "Stock insuficiente para «{$nom}» (disponible: {$disp}). Ajusta las unidades de tu carrito.",
                ], 422);
            }
        }

        // Sincronizar precios vigentes en servidor y calcular subtotal
        $subtotal = 0.0;
        foreach ($carrito->productos as $prod) {
            $precioOficial = round((float) $prod->precio, 2);
            if ((float) $prod->pivot->precio_unitario !== $precioOficial) {
                DB::table('carrito_productos')
                    ->where('carrito_id', $carrito->id)
                    ->where('producto_id', $prod->id)
                    ->where('hash', $prod->pivot->hash)
                    ->update(['precio_unitario' => $precioOficial]);
            }
            $subtotal += $precioOficial * ((int) $prod->pivot->cantidad);
        }
        $subtotal = round($subtotal, 2);

        $gastosEnvio = $settings->calculateShipping($subtotal, $tipoEntrega);
        $totalFinal = round($subtotal + $gastosEnvio, 2);

        $direccionTexto = $tipoEntrega === 'envio'
            ? trim("{$data['direccion_calle']}, CP {$data['direccion_cp']}, {$data['direccion_ciudad']} ({$data['direccion_provincia']})")
            : $settings->pickup_label;

        $referencia = $carrito->referencia ?: ('SBBL-' . strtoupper(Str::random(6)));

        $carrito->update([
            'user_id'        => Auth::check() ? Auth::id() : null,
            'nombre'         => $data['nombre'],
            'email'          => $data['email'],
            'telefono'       => $data['telefono'] ?? null,
            'tipo_entrega'   => $tipoEntrega,
            'direccion'      => $direccionTexto,
            'referencia'     => $referencia,
            'metodo_pago'    => 'paypal',
            'gastos_envio'   => $gastosEnvio,
            'total'          => $totalFinal,
            'total_lagartos' => 0,
        ]);

        try {
            $customId = "SHOP:{$carrito->id}:REF:{$referencia}";
            $desc = "Pedido Merch {$referencia} - {$data['nombre']}";
            $order = $this->paypalService->createOrder($totalFinal, $desc, $customId, $referencia);

            $carrito->update(['paypal_order_id' => $order['id']]);

            return response()->json([
                'id'         => $order['id'],
                'referencia' => $referencia,
            ]);
        } catch (\Throwable $e) {
            Log::error('CarritoController: Error creando orden PayPal de tienda', [
                'carrito_id' => $carrito->id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Captura y verifica en servidor el pago de Merch, descuenta stock atómicamente
     * y registra el ingreso en Tesorería.
     */
    public function capturePaypalOrder(Request $request)
    {
        $request->validate([
            'orderID' => 'required|string|max:128',
        ]);

        $orderId = $request->orderID;

        // Idempotencia si ya estaba pagado
        $alreadyPaid = Carrito::where('paypal_order_id', $orderId)
            ->where('estado_pago', 'pagado')
            ->first();

        if ($alreadyPaid) {
            return response()->json([
                'status'       => 'success',
                'referencia'   => $alreadyPaid->referencia,
                'tipo_entrega' => $alreadyPaid->tipo_entrega,
                'direccion'    => $alreadyPaid->direccion,
            ]);
        }

        try {
            $orderData = $this->paypalService->captureOrder($orderId);
            $verified = $this->paypalService->extractVerifiedCapture($orderData);

            if (!preg_match('/^SHOP:(\d+):REF:([A-Z0-9\-]+)$/', $verified['custom_id'], $matches)) {
                throw new \Exception('Metadatos de pedido inválidos en la orden de PayPal.');
            }

            $carritoId = (int) $matches[1];

            $pedidoCompletado = DB::transaction(function () use ($carritoId, $verified) {
                $carrito = Carrito::with('productos')->where('id', $carritoId)->lockForUpdate()->firstOrFail();

                if ($carrito->estado_pago === 'pagado') {
                    return $carrito;
                }

                if (abs($verified['gross_amount'] - round((float) $carrito->total, 2)) > 0.01) {
                    throw new \Exception('El importe cobrado no coincide con el total del pedido.');
                }

                // Bloquear y descontar stock por producto
                $cantidadesPorProducto = $carrito->productos->groupBy('id')->map(function ($items) {
                    return $items->sum(fn($i) => (int) $i->pivot->cantidad);
                });

                foreach ($cantidadesPorProducto as $prodId => $qtyTotal) {
                    $prodLocked = Producto::where('id', $prodId)->lockForUpdate()->first();
                    if (!$prodLocked || $prodLocked->stock < $qtyTotal) {
                        // El pago se ha capturado en PayPal pero se agotó el stock: registramos en Tesorería con alerta de reembolso
                        $this->treasuryService->recordPayPalIncome([
                            'category'          => 'Tienda / Merch',
                            'gross_amount'      => $verified['gross_amount'],
                            'fee'               => $verified['fee'],
                            'net_amount'        => $verified['net_amount'],
                            'description'       => "SIN STOCK Pedido {$carrito->referencia} - {$carrito->nombre} (Reembolsar)",
                            'notes'             => 'Stock agotado justo durante el pago. Reembolsar manualmente.',
                            'reference_id'      => $verified['capture_id'],
                            'paypal_order_id'   => $verified['order_id'],
                            'paypal_capture_id' => $verified['capture_id'],
                            'user_id'           => $carrito->user_id ?? (Auth::check() ? Auth::id() : null),
                            'carrito_id'        => $carrito->id,
                        ])->update(['refund_requested' => true]);

                        throw new \Exception("El artículo «{$prodLocked->nombre}» se acaba de agotar mientras realizabas el pago. Tu cobro (Ref: {$carrito->referencia}) ha quedado registrado para reembolso inmediato.");
                    }

                    $prodLocked->decrement('stock', $qtyTotal);
                }

                $userId = $carrito->user_id ?: (Auth::check() ? Auth::id() : null);

                $carrito->update([
                    'user_id'           => $userId,
                    'session_id'        => null, // Limpiamos carrito de sesión sin cerrar la sesión del usuario
                    'metodo_pago'       => 'paypal',
                    'estado_pago'       => 'pagado',
                    'estado_envio'      => 'preparando',
                    'solicitado'        => true,
                    'paypal_order_id'   => $verified['order_id'],
                    'paypal_capture_id' => $verified['capture_id'],
                ]);

                $entregaTag = $carrito->tipo_entrega === 'envio' ? 'Envío' : 'Recogida';

                $this->treasuryService->recordPayPalIncome([
                    'category'          => 'Tienda / Merch',
                    'gross_amount'      => $verified['gross_amount'],
                    'fee'               => $verified['fee'],
                    'net_amount'        => $verified['net_amount'],
                    'description'       => "Pedido Merch {$carrito->referencia} ({$entregaTag}) - {$carrito->nombre}",
                    'reference_id'      => $verified['capture_id'],
                    'paypal_order_id'   => $verified['order_id'],
                    'paypal_capture_id' => $verified['capture_id'],
                    'user_id'           => $userId,
                    'carrito_id'        => $carrito->id,
                ]);

                return $carrito;
            });

            // Enviar email de confirmación sin bloquear si falla SMTP
            try {
                Mail::to('tienda@sbbl.es')
                    ->cc($pedidoCompletado->email)
                    ->send(new PedidoTiendaMail(
                        $pedidoCompletado->nombre,
                        $pedidoCompletado->email,
                        $pedidoCompletado->direccion,
                        'PayPal Business',
                        $pedidoCompletado,
                        $pedidoCompletado->referencia,
                        $pedidoCompletado->total
                    ));
            } catch (\Throwable $mailErr) {
                Log::warning('CarritoController: No se pudo enviar email de pedido: ' . $mailErr->getMessage());
            }

            return response()->json([
                'status'       => 'success',
                'referencia'   => $pedidoCompletado->referencia,
                'tipo_entrega' => $pedidoCompletado->tipo_entrega,
                'direccion'    => $pedidoCompletado->direccion,
            ]);
        } catch (\Throwable $e) {
            Log::error('CarritoController: Error capturando orden de tienda', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function paypalSuccess()
    {
        return redirect()->route('carrito.index')->with('success', '¡Pedido confirmado correctamente!');
    }

    public function paypalCancel()
    {
        return redirect()->route('carrito.show')->with('error', 'El pago fue cancelado. Tu carrito sigue guardado.');
    }
}
