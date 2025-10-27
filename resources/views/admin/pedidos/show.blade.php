@extends('layouts.app')

@section('content')
<div class="container text-white">
<h1>Pedido {{ $pedido->referencia }}</h1>

<p><strong>Cliente:</strong> {{ $pedido->nombre }} ({{ $pedido->email }})</p>
<p><strong>Dirección:</strong> {{ $pedido->direccion }}</p>

<form action="{{ route('admin.pedidos.update', $pedido) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row mb-3">
        <div class="col-md-4">
            <label>Estado de pago</label>
            <select name="estado_pago" class="form-select">
                <option value="pendiente" {{ $pedido->estado_pago === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="pagado" {{ $pedido->estado_pago === 'pagado' ? 'selected' : '' }}>Pagado</option>
                <option value="cancelado" {{ $pedido->estado_pago === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>
        <div class="col-md-4">
            <label>Estado de envío</label>
            <select name="estado_envio" class="form-select">
                <option value="pendiente" {{ $pedido->estado_envio === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="preparando" {{ $pedido->estado_envio === 'preparando' ? 'selected' : '' }}>Preparando</option>
                <option value="enviado" {{ $pedido->estado_envio === 'enviado' ? 'selected' : '' }}>Enviado</option>
                <option value="entregado" {{ $pedido->estado_envio === 'entregado' ? 'selected' : '' }}>Entregado</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-success">Guardar cambios</button>
</form>

<h3 class="mt-4">Productos</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Color</th>
            <th>Talla</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedido->productos as $producto)
                @php
                    $atributos = is_string($producto->pivot->atributos)
                        ? json_decode($producto->pivot->atributos, true)
                        : ($producto->pivot->atributos ?? []);

                    $colorSeleccionado = $atributos['color']['nombre'] ?? 'Original';
                    $foto = $colorSeleccionado['foto'] ?? $producto->fotos;
                    $talla = $atributos['talla'] ?? '-';
                @endphp
                <tr>
                    <td><img src="{{ $foto }}" width="60" class="me-2" alt="{{ $producto->nombre }}">{{ $producto->nombre }}</td>
                    <td>{{ $colorSeleccionado }}</td>
                    <td>{{ $talla }}</td>
                    <td>{{ $producto->pivot->cantidad }}</td>
                    <td>{{ $producto->pivot->precio_unitario }}</td>
                    <td>{{ $producto->pivot->cantidad * $producto->pivot->precio_unitario }}</td>
                </tr>
            @endforeach
    </tbody>
</table>
</div>
@endsection
