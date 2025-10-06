@extends('layouts.app')

@section('content')
<div class="container text-white">
    <a href="{{ route('carrito.index') }}" class="btn btn-outline-warning m-2">Ver productos</a>
    <h1>Tu carrito</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($carrito->productos->count() > 0)
<table class="table table-striped text-white align-middle">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Atributos</th>
            <th>Cantidad</th>
            <th>Precio unitario</th>
            <th>Subtotal</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
@foreach($carrito->productos as $producto)
@php
$atributos = is_string($producto->pivot->atributos)
    ? json_decode($producto->pivot->atributos, true)
    : ($producto->pivot->atributos ?? []);

$colorSeleccionado = $atributos['color'] ?? null;
$foto = $colorSeleccionado['foto'] ?? $producto->fotos;
$nombreColor = $colorSeleccionado['nombre'] ?? null;
$talla = $atributos['talla'] ?? null;
@endphp



<tr>
    <td>
        <img src="{{ $foto }}" width="60" class="me-2" alt="{{ $producto->nombre }}">
        {{ $producto->nombre }}
    </td>
    <td>
        @if($nombreColor)
            Color: {{ $nombreColor }}
        @else
            Original
        @endif
        @if($talla)
            Talla: {{ $talla }}
        @endif
    </td>
    <td>
        <form action="{{ route('carrito.update', $producto->id) }}" method="POST" class="d-flex">
            @csrf
            <input type="hidden" name="hash" value="{{ $producto->pivot->hash }}">
            <input type="number" name="cantidad" value="{{ $producto->pivot->cantidad }}" min="1" class="form-control w-50 me-2">
            <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
        </form>
    </td>
    <td>{{ $producto->pivot->precio_unitario }} €</td>
    <td>{{ $producto->pivot->cantidad * $producto->pivot->precio_unitario }} €</td>
    <td>
        <form action="{{ route('carrito.remove', $producto->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <input type="hidden" name="hash" value="{{ $producto->pivot->hash }}">
            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
        </form>

    </td>
</tr>
@endforeach


    </tbody>
</table>
<h4>Total: {{ $carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario) }} €</h4>
<h3>Finalizar pedido</h3>
        <form action="{{ route('carrito.checkout') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $carrito->nombre) }}">
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email', $carrito->email) }}">
            </div>
            <div class="mb-3">
                <label>Dirección postal</label>
                <textarea name="direccion" class="form-control" required>{{ old('direccion', $carrito->direccion) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar pedido</button>
        </form>
@endif

</div>
@endsection

