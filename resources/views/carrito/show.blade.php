@extends('layouts.app')

@section('title', 'Carrito: Precompras Nacional')

@section('styles')
<style>
    /* Estilo Shonen Táctico para el Carrito */
    .cart-container { background: var(--sbbl-blue-2); border: 3px solid #000; box-shadow: 8px 8px 0 #000; padding: 30px; margin-top: 40px; margin-bottom: 60px; }
    .cart-title { font-family: 'Oswald', cursive; font-size: 3rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000, 4px 4px 0 var(--shonen-red); text-transform: uppercase; letter-spacing: 2px; border-bottom: 4px solid var(--shonen-red); padding-bottom: 15px; margin-bottom: 30px; }

    .table-cart { border: 2px solid #000; background: #111; }
    .table-cart thead { background: var(--sbbl-blue-1); font-family: 'Oswald', cursive; font-size: 1.2rem; color: var(--shonen-cyan); letter-spacing: 1px; }
    .table-cart th, .table-cart td { border-bottom: 1px dashed #444; color: #fff; vertical-align: middle; padding: 15px; }
    .table-cart tbody tr:hover { background: rgba(255, 255, 255, 0.05); }

    .checkout-panel { background: #000; border: 3px solid var(--sbbl-gold); padding: 25px; margin-top: 30px; }
    .checkout-title { font-family: 'Oswald', cursive; color: #fff; border-bottom: 2px solid var(--sbbl-gold); padding-bottom: 10px; margin-bottom: 20px; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="cart-container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="cart-title m-0"><i class="fas fa-box-open me-3 text-white"></i> TU RESERVA</h1>
            <a href="{{ route('carrito.index') }}" class="btn-shonen btn-shonen-warning fs-5">
                <span><i class="fas fa-store me-2"></i> VOLVER A LA TIENDA</span>
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-shonen alert-shonen-success text-center mb-4"><div><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="alert alert-shonen alert-shonen-danger text-center mb-4"><div><i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}</div></div>
        @endif

        @if($carrito->productos->count() > 0)
            <div class="table-responsive mb-4">
                <table class="table table-cart text-center">
                    <thead>
                        <tr>
                            <th class="text-start">ARTÍCULO</th>
                            <th>ESPECIFICACIONES</th>
                            <th>CANTIDAD</th>
                            <th>COSTE U.</th>
                            <th>SUBTOTAL</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carrito->productos as $producto)
                            @php
                                $atributos = is_string($producto->pivot->atributos) ? json_decode($producto->pivot->atributos, true) : ($producto->pivot->atributos ?? []);
                                $colorSeleccionado = $atributos['color'] ?? null;
                                $foto = $colorSeleccionado['foto'] ?? $producto->fotos;
                                $nombreColor = $colorSeleccionado['nombre'] ?? null;
                                $talla = $atributos['talla'] ?? null;
                            @endphp

                            <tr>
                                <td class="text-start fw-bold fs-5 d-flex align-items-center">
                                    <img src="{{ $foto }}" width="70" class="me-3 border border-secondary" style="background:#000;" alt="{{ $producto->nombre }}">
                                    {{ mb_strtoupper($producto->nombre) }}
                                </td>
                                <td>
                                    @if($nombreColor) <span class="badge bg-secondary border border-white me-1">{{ mb_strtoupper($nombreColor) }}</span> @endif
                                    @if($talla) <span class="badge bg-info text-dark fw-bold border border-white">TALLA {{ mb_strtoupper($talla) }}</span> @endif
                                </td>
                                <td>
                                    <form action="{{ route('carrito.update', $producto->id) }}" method="POST" class="d-inline-flex justify-content-center">
                                        @csrf
                                        <input type="hidden" name="hash" value="{{ $producto->pivot->hash }}">
                                        <input type="number" name="cantidad" value="{{ $producto->pivot->cantidad }}" min="1" class="form-control form-control-sm bg-dark text-white border-secondary text-center me-2 fw-bold" style="width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-success border border-dark" title="Actualizar"><i class="fas fa-sync-alt"></i></button>
                                    </form>
                                </td>
                                <td class="fw-bold text-info">
                                    {{ $producto->pivot->precio_unitario }}€
                                </td>
                                <td class="font-Oswald fs-4" style="color: var(--sbbl-gold);">
                                    {{ number_format($producto->pivot->cantidad * $producto->pivot->precio_unitario, 2) }}€
                                </td>
                                <td>
                                    <form action="{{ route('carrito.remove', $producto->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="hash" value="{{ $producto->pivot->hash }}">
                                        <button type="submit" class="btn btn-sm btn-danger border border-dark" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="alert alert-shonen alert-shonen-info mb-0 h-100 d-flex flex-column justify-content-center">
                        <div class="text-start text-white">
                            <h4 class="font-Oswald text-white mb-3"><i class="fas fa-truck-pickup me-2"></i> RECOGIDA EN LA BEYCON</h4>
                            <p class="mb-2 fw-bold">1. Este pedido es una precompra exclusiva.</p>
                            <p class="mb-2 fw-bold">2. El stock se fabricará bajo demanda con estas reservas.</p>
                            <p class="mb-0 fw-bold">3. Se te entregará en mano el día del Torneo Nacional en el stand de registro.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="checkout-panel">
                        <h3 class="checkout-title">CONFIRMAR RESERVA</h3>

                        <div class="d-flex justify-content-between font-Oswald mb-4">
                            <span class="fs-4 text-white">TOTAL A PAGAR:</span>
                            <div class="text-end">
                                <span class="fs-2" style="color: var(--sbbl-gold);">{{ number_format($carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario), 2) }} €</span>
                            </div>
                        </div>

                        <form action="{{ route('carrito.checkout') }}" method="POST">
                            @csrf
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="text-white fw-bold small">Nombre y Apellidos</label>
                                    <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" required value="{{ old('nombre', Auth::check() ? Auth::user()->name : '') }}" placeholder="Identificación para recogida">
                                </div>
                                <div class="col-md-6">
                                    <label class="text-white fw-bold small">Correo Electrónico</label>
                                    <input type="email" name="email" class="form-control bg-dark text-white border-secondary" required value="{{ old('email', Auth::check() ? Auth::user()->email : '') }}">
                                </div>
                            </div>

                            <div class="alert bg-dark text-white border border-secondary p-3 mb-4">
                                <i class="fab fa-paypal text-primary me-2 fs-4 align-middle"></i>
                                <span class="fw-bold">El pago se procesará de forma segura a través de PayPal o Tarjeta de Crédito.</span>
                            </div>

                            <button type="submit" class="btn-shonen w-100 text-center" style="background: var(--shonen-cyan); color: #000; border-color: #000; padding: 15px; font-size: 1.5rem;">
                                <span><i class="fas fa-check-double me-2"></i> PAGAR RESERVA</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-secondary mb-4"></i>
                <h3 class="font-Oswald text-white fs-2 mb-3">EL CARRITO ESTÁ VACÍO</h3>
                <p class="text-white-50 fw-bold mb-4">No has añadido ningún artículo para tu reserva del Nacional.</p>
                <a href="{{ route('carrito.index') }}" class="btn-shonen btn-shonen-warning fs-4 px-5 py-2">
                    <span>IR AL CATÁLOGO</span>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
