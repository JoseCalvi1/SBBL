<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
public function index(Request $request)
{
    $query = Carrito::with('productos')
        ->whereNotNull('referencia');

    // 🔍 Filtros
    if ($request->filled('metodo_pago')) {
        $query->where('metodo_pago', $request->metodo_pago);
    }

    if ($request->filled('estado_pago')) {
        $query->where('estado_pago', $request->estado_pago);
    }

    if ($request->filled('estado_envio')) {
        $query->where('estado_envio', $request->estado_envio);
    }

    if ($request->filled('solicitado')) {
        $query->where('solicitado', $request->solicitado);
    }

    if ($request->filled('busqueda')) {
        $query->where(function($q) use ($request) {
            $q->where('nombre', 'like', '%'.$request->busqueda.'%')
              ->orWhere('email', 'like', '%'.$request->busqueda.'%')
              ->orWhere('referencia', 'like', '%'.$request->busqueda.'%');
        });
    }

    $pedidos = $query->orderByDesc('created_at')->paginate(20)->appends($request->all());

    return view('admin.pedidos.index', compact('pedidos'));
}



    public function show(Carrito $pedido)
    {
        return view('admin.pedidos.show', compact('pedido'));
    }

    public function update(Request $request, Carrito $pedido)
    {
        $pedido->update($request->only([
            'estado_pago', 'estado_envio', 'solicitado'
        ]));

        return back()->with('success', "Pedido {$pedido->referencia} actualizado correctamente.");
    }

    public function destroy(Carrito $pedido)
    {
        $pedido->delete();
        return back()->with('success', 'Pedido eliminado correctamente.');
    }
}
