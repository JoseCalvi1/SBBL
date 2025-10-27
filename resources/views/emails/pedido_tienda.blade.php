<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido {{ $referencia }}</title>
</head>
<body>
    <h2>Nuevo pedido: {{ $referencia }}</h2>
    <p><strong>Cliente:</strong> {{ $nombre }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Dirección:</strong> {{ $direccion }}</p>
    <p><strong>Método de pago:</strong> {{ $metodoPago }}</p>
    <hr>
    <h3>Productos:</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Color</th>
                <th>Talla</th>
                <th>Cantidad</th>
                <th>Precio unitario (€)</th>
                <th>Subtotal (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($carrito->productos as $producto)
                @php
                    $atributos = is_string($producto->pivot->atributos)
                        ? json_decode($producto->pivot->atributos, true)
                        : ($producto->pivot->atributos ?? []);

                    $colorSeleccionado = $atributos['color']['nombre'] ?? 'Original';
                    $talla = $atributos['talla'] ?? '-';
                @endphp
                <tr>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $colorSeleccionado }}</td>
                    <td>{{ $talla }}</td>
                    <td>{{ $producto->pivot->cantidad }}</td>
                    <td>{{ $producto->pivot->precio_unitario }}</td>
                    <td>{{ $producto->pivot->cantidad * $producto->pivot->precio_unitario }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Total:</strong> {{ $total }} {{ $metodoPago === 'SBBL Coins' ? '🦎' : '€' }}</p>
</body>
</html>
