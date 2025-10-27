@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-white mb-4">📦 Gestor de Pedidos</h1>

    <!-- 🔍 FILTROS -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="text" name="busqueda" value="{{ request('busqueda') }}" class="form-control" placeholder="Buscar cliente, correo o ref.">
        </div>
        <div class="col-md-2">
            <select name="metodo_pago" class="form-select">
                <option value="">Método (todos)</option>
                <option value="paypal" {{ request('metodo_pago') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                <option value="coins" {{ request('metodo_pago') === 'coins' ? 'selected' : '' }}>🦎 Coins</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado_pago" class="form-select">
                <option value="">Pago (todos)</option>
                <option value="pendiente" {{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="pagado" {{ request('estado_pago') === 'pagado' ? 'selected' : '' }}>Pagado</option>
                <option value="cancelado" {{ request('estado_pago') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado_envio" class="form-select">
                <option value="">Envío (todos)</option>
                <option value="pendiente" {{ request('estado_envio') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="preparando" {{ request('estado_envio') === 'preparando' ? 'selected' : '' }}>Preparando</option>
                <option value="enviado" {{ request('estado_envio') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                <option value="entregado" {{ request('estado_envio') === 'entregado' ? 'selected' : '' }}>Entregado</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="solicitado" class="form-select">
                <option value="">Solicitado (todos)</option>
                <option value="1" {{ request('solicitado') === '1' ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ request('solicitado') === '0' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-warning">Filtrar</button>
        </div>
    </form>

    <!-- 📋 TABLA DE PEDIDOS -->
    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Cliente</th>
                    <th>Correo</th>
                    <th>Método</th>
                    <th>Pago</th>
                    <th>Envío</th>
                    <th>🦎</th>
                    <th>€</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->referencia }}</td>
                        <td>{{ $pedido->nombre }}</td>
                        <td>{{ $pedido->email }}</td>
                        <td>
                            @if($pedido->metodo_pago === 'paypal')
                                <span class="badge bg-primary">PayPal</span>
                            @elseif($pedido->metodo_pago === 'coins')
                                <span class="badge bg-warning text-dark">🦎 Coins</span>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $pedido->estado_pago === 'pagado' ? 'success' : ($pedido->estado_pago === 'pendiente' ? 'secondary' : 'danger') }}">
                                {{ ucfirst($pedido->estado_pago) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $colores = [
                                    'pendiente' => 'secondary',
                                    'preparando' => 'info',
                                    'enviado' => 'primary',
                                    'entregado' => 'success',
                                ];
                            @endphp
                            <span class="badge bg-{{ $colores[$pedido->estado_envio] ?? 'secondary' }}">
                                {{ ucfirst($pedido->estado_envio) }}
                            </span>
                        </td>
                        <td>{{ $pedido->total_lagartos }}</td>
                        <td>{{ number_format($pedido->total, 2) }} €</td>
                        <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('admin.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-light">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No se encontraron pedidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pedidos->links() }}
</div>
@endsection
