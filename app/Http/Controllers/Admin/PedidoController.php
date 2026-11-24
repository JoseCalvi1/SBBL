<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\ShopSetting;
use App\Services\Payment\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Carrito::with(['productos', 'user'])
            ->whereNotNull('referencia');

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->metodo_pago);
        }

        if ($request->filled('estado_pago')) {
            $query->where('estado_pago', $request->estado_pago);
        }

        if ($request->filled('estado_envio')) {
            $query->where('estado_envio', $request->estado_envio);
        }

        if ($request->filled('tipo_entrega')) {
            $query->where('tipo_entrega', $request->tipo_entrega);
        }

        if ($request->filled('busqueda')) {
            $qStr = $request->busqueda;
            $query->where(function ($q) use ($qStr) {
                $q->where('nombre', 'like', '%' . $qStr . '%')
                  ->orWhere('email', 'like', '%' . $qStr . '%')
                  ->orWhere('referencia', 'like', '%' . $qStr . '%')
                  ->orWhere('paypal_capture_id', 'like', '%' . $qStr . '%');
            });
        }

        $pedidos = $query->orderByDesc('created_at')->paginate(20)->appends($request->all());
        $shopSettings = ShopSetting::current();

        return view('admin.pedidos.index', compact('pedidos', 'shopSettings'));
    }

    public function show(Carrito $pedido)
    {
        $pedido->load(['productos', 'user', 'treasuryLog']);
        return view('admin.pedidos.show', compact('pedido'));
    }

    public function update(Request $request, Carrito $pedido, TreasuryService $treasuryService)
    {
        $data = $request->validate([
            'estado_pago'  => 'required|in:pendiente,pagado,cancelado,reembolsado',
            'estado_envio' => 'required|in:pendiente,preparando,enviado,entregado',
        ]);

        $pedido->update([
            'estado_pago'  => $data['estado_pago'],
            'estado_envio' => $data['estado_envio'],
        ]);

        // Si el admin marca el pedido como reembolsado y tiene movimiento completado en Tesorería, sincronizarlo
        if ($data['estado_pago'] === 'reembolsado' && $pedido->treasuryLog && $pedido->treasuryLog->status !== 'reembolsado') {
            $treasuryService->markRefundedManually(
                $pedido->treasuryLog,
                Auth::id(),
                "Sincronizado desde Gestor de Pedidos ({$pedido->referencia})"
            );
        }

        return back()->with('success', "Pedido {$pedido->referencia} actualizado correctamente.");
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'pickup_enabled'      => 'nullable|boolean',
            'pickup_label'        => 'required|string|max:255',
            'pickup_instructions' => 'nullable|string|max:2000',
            'shipping_enabled'    => 'nullable|boolean',
            'shipping_cost'       => 'required|numeric|min:0|max:200',
            'free_shipping_min'   => 'nullable|numeric|min:0|max:5000',
        ]);

        $pickupEnabled = $request->boolean('pickup_enabled');
        $shippingEnabled = $request->boolean('shipping_enabled');

        if (!$pickupEnabled && !$shippingEnabled) {
            return back()->withErrors(['pickup_enabled' => 'Debe haber al menos un método de entrega habilitado (Recogida o Envío).']);
        }

        $settings = ShopSetting::current();
        $settings->update([
            'pickup_enabled'      => $pickupEnabled,
            'pickup_label'        => $data['pickup_label'],
            'pickup_instructions' => $data['pickup_instructions'] ?? null,
            'shipping_enabled'    => $shippingEnabled,
            'shipping_cost'       => round((float) $data['shipping_cost'], 2),
            'free_shipping_min'   => $data['free_shipping_min'] !== null ? round((float) $data['free_shipping_min'], 2) : null,
        ]);

        return back()->with('success', 'Configuración de entrega y envíos de la tienda actualizada.');
    }

    public function destroy(Carrito $pedido)
    {
        if ($pedido->estado_pago === 'pagado' || $pedido->paypal_capture_id) {
            return back()->withErrors(['pedido' => 'Por integridad contable, no se puede eliminar un pedido ya pagado en PayPal.']);
        }

        $pedido->productos()->detach();
        $pedido->delete();

        return back()->with('success', 'Pedido pendiente eliminado correctamente.');
    }
}
