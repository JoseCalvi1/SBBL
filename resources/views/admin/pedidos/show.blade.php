@extends('layouts.app')

@section('title', 'Pedido ' . $pedido->referencia)

@section('content')
<div class="container py-4 text-white">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-Oswald text-warning m-0">PEDIDO {{ $pedido->referencia }}</h2>
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-1"></i> Volver a Pedidos
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="p-4 border border-secondary bg-dark h-100">
                <h5 class="font-Oswald text-info mb-3">DATOS DEL CLIENTE Y ENTREGA</h5>
                <p class="mb-1"><strong>Comprador:</strong> {{ $pedido->nombre }}
                    @if($pedido->user)
                        <span class="badge bg-info text-dark ms-1">Usuario #{{ $pedido->user->id }}</span>
                    @else
                        <span class="badge bg-secondary ms-1">Invitado</span>
                    @endif
                </p>
                <p class="mb-1"><strong>Correo:</strong> {{ $pedido->email }}</p>
                <p class="mb-1"><strong>Teléfono:</strong> {{ $pedido->telefono ?: 'No indicado' }}</p>
                <p class="mb-1"><strong>Modalidad:</strong> {{ $pedido->tipo_entrega === 'envio' ? 'Envío Postal a Domicilio' : 'Recogida Presencial' }}</p>
                <p class="mb-0"><strong>Destino / Punto de entrega:</strong> {{ $pedido->direccion }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-4 border border-secondary bg-dark h-100">
                <h5 class="font-Oswald text-warning mb-3">TRAZABILIDAD PAYPAL Y TESORERÍA</h5>
                <p class="mb-1"><strong>ID Orden PayPal:</strong> <code>{{ $pedido->paypal_order_id ?: '-' }}</code></p>
                <p class="mb-1"><strong>ID Captura PayPal:</strong> <code>{{ $pedido->paypal_capture_id ?: '-' }}</code></p>
                <p class="mb-1"><strong>Gastos de envío:</strong> {{ number_format($pedido->gastos_envio, 2, ',', '.') }} €</p>
                <p class="mb-2"><strong>Total abonado:</strong> <span class="text-warning fw-bold fs-5">{{ number_format($pedido->total, 2, ',', '.') }} €</span></p>
                @if($pedido->treasuryLog)
                    <a href="{{ route('admin.treasury.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-shield-alt me-1"></i> Registrado en Tesorería (#{{ $pedido->treasuryLog->id }} — {{ strtoupper($pedido->treasuryLog->status) }})
                    </a>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('admin.pedidos.update', $pedido) }}" method="POST" class="p-4 border border-secondary bg-black mb-4">
        @csrf
        @method('PUT')
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="fw-bold mb-1">Estado de pago</label>
                <select name="estado_pago" class="form-select bg-dark text-white border-secondary">
                    <option value="pendiente" {{ $pedido->estado_pago === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="pagado" {{ $pedido->estado_pago === 'pagado' ? 'selected' : '' }}>Pagado</option>
                    <option value="reembolsado" {{ $pedido->estado_pago === 'reembolsado' ? 'selected' : '' }}>Reembolsado (Sincroniza Tesorería)</option>
                    <option value="cancelado" {{ $pedido->estado_pago === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="fw-bold mb-1">Estado logístico / entrega</label>
                <select name="estado_envio" class="form-select bg-dark text-white border-secondary">
                    <option value="pendiente" {{ $pedido->estado_envio === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="preparando" {{ $pedido->estado_envio === 'preparando' ? 'selected' : '' }}>Preparando</option>
                    <option value="enviado" {{ $pedido->estado_envio === 'enviado' ? 'selected' : '' }}>Enviado</option>
                    <option value="entregado" {{ $pedido->estado_envio === 'entregado' ? 'selected' : '' }}>Entregado</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success fw-bold w-100">Guardar cambios</button>
            </div>
        </div>
    </form>

    <h4 class="font-Oswald mb-3">ARTÍCULOS DEL PEDIDO</h4>
    <div class="table-responsive">
        <table class="table table-dark table-bordered align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Color</th>
                    <th>Talla</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio U.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->productos as $producto)
                    @php
                        $atributos = is_string($producto->pivot->atributos)
                            ? json_decode($producto->pivot->atributos, true)
                            : ($producto->pivot->atributos ?? []);
                        $colorObj = $atributos['color'] ?? null;
                        $colorNombre = is_array($colorObj) ? ($colorObj['nombre'] ?? 'Original') : 'Original';
                        $foto = (is_array($colorObj) && !empty($colorObj['foto'])) ? $colorObj['foto'] : $producto->fotos;
                        $talla = $atributos['talla'] ?? '-';
                    @endphp
                    <tr>
                        <td><img src="{{ $foto }}" width="55" class="me-2 border border-secondary" alt="{{ $producto->nombre }}">{{ $producto->nombre }}</td>
                        <td>{{ $colorNombre }}</td>
                        <td>{{ $talla }}</td>
                        <td class="text-center fw-bold">{{ $producto->pivot->cantidad }}</td>
                        <td class="text-end">{{ number_format($producto->pivot->precio_unitario, 2, ',', '.') }} €</td>
                        <td class="text-end fw-bold text-warning">{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_unitario, 2, ',', '.') }} €</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
