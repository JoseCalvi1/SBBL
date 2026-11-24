@extends('layouts.app')

@section('title', 'Gestor de Pedidos y Envíos')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h1 class="text-white font-Oswald m-0">📦 GESTOR DE PEDIDOS DE MERCH</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-info text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#shopSettingsCollapse">
                <i class="fas fa-truck me-1"></i> Configurar Recogida / Envíos
            </button>
            <a href="{{ route('productos.index') }}" class="btn btn-outline-light fw-bold">
                <i class="fas fa-tags me-1"></i> Catálogo y Stock
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- PANEL CONFIGURACIÓN DE ENTREGA Y ENVÍOS --}}
    <div class="collapse mb-4" id="shopSettingsCollapse">
        <div class="p-4 border border-info text-white" style="background: #0f172a;">
            <h4 class="font-Oswald text-info mb-3"><i class="fas fa-sliders-h me-2"></i> CONFIGURACIÓN DE ENTREGA Y ENVÍO A DOMICILIO</h4>
            <form method="POST" action="{{ route('admin.pedidos.settings') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="pickup_enabled" value="1" id="swPickup" {{ $shopSettings->pickup_enabled ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-warning" for="swPickup">Habilitar Recogida Presencial (Sin gastos de envío)</label>
                        </div>
                        <label class="small text-white-50 fw-bold mb-1">Etiqueta de Recogida *</label>
                        <input type="text" name="pickup_label" class="form-control bg-dark text-white border-secondary mb-2" value="{{ $shopSettings->pickup_label }}" required>
                        <label class="small text-white-50 fw-bold mb-1">Instrucciones de Recogida</label>
                        <textarea name="pickup_instructions" rows="2" class="form-control bg-dark text-white border-secondary">{{ $shopSettings->pickup_instructions }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="shipping_enabled" value="1" id="swShipping" {{ $shopSettings->shipping_enabled ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-info" for="swShipping">Habilitar Envío Postal a Domicilio</label>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small text-white-50 fw-bold mb-1">Coste de Envío (€) *</label>
                                <input type="number" step="0.01" min="0" name="shipping_cost" class="form-control bg-dark text-white border-secondary" value="{{ $shopSettings->shipping_cost }}" required>
                            </div>
                            <div class="col-6">
                                <label class="small text-white-50 fw-bold mb-1">Envío Gratis desde (€)</label>
                                <input type="number" step="0.01" min="0" name="free_shipping_min" class="form-control bg-dark text-white border-secondary" value="{{ $shopSettings->free_shipping_min }}" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="small text-white-50 mt-2">
                            Actualmente:
                            <strong>Recogida {{ $shopSettings->pickup_enabled ? 'ACTIVA' : 'INACTIVA' }}</strong> ·
                            <strong>Envío {{ $shopSettings->shipping_enabled ? 'ACTIVO' : 'INACTIVO' }}</strong>.
                        </div>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-info text-dark fw-bold px-4">Guardar Configuración</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- FILTROS --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="text" name="busqueda" value="{{ request('busqueda') }}" class="form-control bg-dark text-white border-secondary" placeholder="Cliente, email, ref. o captura PayPal">
        </div>
        <div class="col-md-2">
            <select name="tipo_entrega" class="form-select bg-dark text-white border-secondary">
                <option value="">Entrega (todas)</option>
                <option value="recogida" {{ request('tipo_entrega') === 'recogida' ? 'selected' : '' }}>Recogida</option>
                <option value="envio" {{ request('tipo_entrega') === 'envio' ? 'selected' : '' }}>Envío</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado_pago" class="form-select bg-dark text-white border-secondary">
                <option value="">Pago (todos)</option>
                <option value="pagado" {{ request('estado_pago') === 'pagado' ? 'selected' : '' }}>Pagado</option>
                <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="reembolsado" {{ request('estado_pago') === 'reembolsado' ? 'selected' : '' }}>Reembolsado</option>
                <option value="cancelado" {{ request('estado_pago') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="estado_envio" class="form-select bg-dark text-white border-secondary">
                <option value="">Estado Entrega (todos)</option>
                <option value="preparando" {{ request('estado_envio') === 'preparando' ? 'selected' : '' }}>Preparando</option>
                <option value="enviado" {{ request('estado_envio') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                <option value="entregado" {{ request('estado_envio') === 'entregado' ? 'selected' : '' }}>Entregado</option>
                <option value="pendiente" {{ request('estado_envio') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-warning fw-bold">Filtrar</button>
        </div>
    </form>

    {{-- TABLA DE PEDIDOS --}}
    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Comprador</th>
                    <th>Entrega</th>
                    <th>Pago</th>
                    <th>Logística</th>
                    <th class="text-end">Total</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td class="fw-bold text-warning">
                            <div>{{ $pedido->referencia }}</div>
                            @if($pedido->paypal_capture_id)
                                <small class="text-white-50"><code>{{ $pedido->paypal_capture_id }}</code></small>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $pedido->nombre }}</div>
                            <div class="small text-white-50">{{ $pedido->email }}</div>
                            @if($pedido->user)
                                <span class="badge bg-info text-dark" style="font-size:0.7rem;">Cuenta: {{ $pedido->user->name }}</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:0.7rem;">Invitado</span>
                            @endif
                        </td>
                        <td>
                            @if($pedido->tipo_entrega === 'envio')
                                <span class="badge bg-primary"><i class="fas fa-truck me-1"></i>Envío</span>
                            @else
                                <span class="badge bg-dark border border-secondary"><i class="fas fa-hand-holding-box me-1"></i>Recogida</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $pagoColors = [
                                    'pagado' => 'success',
                                    'pendiente' => 'warning text-dark',
                                    'reembolsado' => 'secondary',
                                    'cancelado' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $pagoColors[$pedido->estado_pago] ?? 'secondary' }}">
                                {{ ucfirst($pedido->estado_pago) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $colores = [
                                    'pendiente' => 'secondary',
                                    'preparando' => 'info text-dark',
                                    'enviado' => 'primary',
                                    'entregado' => 'success',
                                ];
                            @endphp
                            <span class="badge bg-{{ $colores[$pedido->estado_envio] ?? 'secondary' }}">
                                {{ ucfirst($pedido->estado_envio) }}
                            </span>
                        </td>
                        <td class="text-end fw-bold">{{ number_format($pedido->total, 2, ',', '.') }} €</td>
                        <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-light">Gestionar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No se encontraron pedidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pedidos->links() }}
</div>
@endsection
