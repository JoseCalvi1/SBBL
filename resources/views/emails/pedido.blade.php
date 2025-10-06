@component('mail::message')
# Nuevo pedido recibido

**Nombre:** {{ $carrito->nombre }}
**Email:** {{ $carrito->email }}
**Dirección:** {{ $carrito->direccion }}

**Productos:**

@component('mail::table')
| Producto | Atributos | Cantidad | Precio | Subtotal |
|----------|-----------|----------|--------|---------|
@foreach($carrito->productos as $producto)
@php
    $atributos = is_string($producto->pivot->atributos)
        ? json_decode($producto->pivot->atributos, true)
        : ($producto->pivot->atributos ?? []);

    $color = $atributos['color']['nombre'] ?? null;
    $talla = $atributos['talla'] ?? null;

    $atributosTexto = [];
    if ($color) $atributosTexto[] = "Color: $color";
    if ($talla) $atributosTexto[] = "Talla: $talla";
@endphp
| {{ $producto->nombre }} | {{ implode(', ', $atributosTexto) }} | {{ $producto->pivot->cantidad }} | {{ $producto->pivot->precio_unitario }} € | {{ $producto->pivot->cantidad * $producto->pivot->precio_unitario }} € |
@endforeach
@endcomponent

**Total:** {{ $carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario) }} €

Gracias por tu pedido.
@endcomponent
