@extends('layouts.app')

@section('title', 'Carrito de Merch SBBL')

@section('styles')
<style>
    .cart-container { background: var(--sbbl-blue-2); border: 3px solid #000; box-shadow: 8px 8px 0 #000; padding: 30px; margin-top: 40px; margin-bottom: 60px; }
    .cart-title { font-family: 'Oswald', cursive; font-size: 2.8rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000, 4px 4px 0 var(--shonen-red); text-transform: uppercase; letter-spacing: 2px; border-bottom: 4px solid var(--shonen-red); padding-bottom: 15px; }
    .table-cart { border: 2px solid #000; background: #111; }
    .table-cart thead { background: var(--sbbl-blue-1); font-family: 'Oswald', cursive; font-size: 1.15rem; color: var(--shonen-cyan); letter-spacing: 1px; }
    .table-cart th, .table-cart td { border-bottom: 1px dashed #444; color: #fff; vertical-align: middle; padding: 14px; }
    .checkout-panel { background: #000; border: 3px solid var(--sbbl-gold); padding: 25px; }
    .checkout-title { font-family: 'Oswald', cursive; color: #fff; border-bottom: 2px solid var(--sbbl-gold); padding-bottom: 10px; margin-bottom: 20px; }
</style>
@endsection

@section('content')
<div class="container">
    <div class="cart-container">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h1 class="cart-title m-0"><i class="fas fa-shopping-bag me-2 text-white"></i> TU PEDIDO DE MERCH</h1>
            <a href="{{ route('carrito.index') }}" class="btn-shonen btn-shonen-warning fs-5">
                <span><i class="fas fa-store me-2"></i> SEGUIR COMPRANDO</span>
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-shonen alert-shonen-success text-center mb-4"><div><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="alert alert-shonen alert-shonen-danger text-center mb-4"><div><i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}</div></div>
        @endif

        <div id="orderCompletedBanner" class="d-none p-5 text-center border border-3 border-success mb-4" style="background: #0a1410;">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h2 class="font-Oswald text-white mb-2">¡PAGO VERIFICADO Y PEDIDO CONFIRMADO!</h2>
            <p class="text-white-50 fw-bold fs-5 mb-3">Guarda tu código de referencia oficial para cualquier consulta o recogida:</p>
            <div class="d-inline-block bg-black px-4 py-2 border border-warning font-Oswald fs-2 text-warning mb-3" id="completedOrderRef"></div>
            <p class="text-white small mb-4" id="completedOrderDelivery"></p>
            <a href="{{ route('carrito.index') }}" class="btn btn-success fw-bold px-4 py-2">Volver a la Tienda</a>
        </div>

        @if($carrito->productos->count() > 0)
            @php
                $subtotalProductos = round($carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario), 2);
                $defaultDelivery = $shopSettings->pickup_enabled ? 'recogida' : 'envio';
            @endphp

            <div id="activeCartContent">
                <div class="table-responsive mb-4">
                    <table class="table table-cart text-center">
                        <thead>
                            <tr>
                                <th class="text-start">ARTÍCULO</th>
                                <th>OPCIONES</th>
                                <th>CANTIDAD</th>
                                <th>PRECIO U.</th>
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
                                        <img src="{{ $foto }}" width="65" class="me-3 border border-secondary" style="background:#000;" alt="{{ $producto->nombre }}">
                                        {{ mb_strtoupper($producto->nombre) }}
                                    </td>
                                    <td>
                                        @if($nombreColor) <span class="badge bg-secondary border border-white me-1">{{ mb_strtoupper($nombreColor) }}</span> @endif
                                        @if($talla) <span class="badge bg-info text-dark fw-bold border border-white">TALLA {{ mb_strtoupper($talla) }}</span> @endif
                                        @if(!$nombreColor && !$talla) <span class="text-secondary small">Estándar</span> @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('carrito.update', $producto->id) }}" method="POST" class="d-inline-flex justify-content-center">
                                            @csrf
                                            <input type="hidden" name="hash" value="{{ $producto->pivot->hash }}">
                                            <input type="number" name="cantidad" value="{{ $producto->pivot->cantidad }}" min="1" max="{{ max(1, $producto->stock) }}" class="form-control form-control-sm bg-dark text-white border-secondary text-center me-2 fw-bold" style="width: 72px;">
                                            <button type="submit" class="btn btn-sm btn-success border border-dark" title="Actualizar"><i class="fas fa-sync-alt"></i></button>
                                        </form>
                                    </td>
                                    <td class="fw-bold text-info">{{ number_format($producto->pivot->precio_unitario, 2, ',', '.') }} €</td>
                                    <td class="font-Oswald fs-4" style="color: var(--sbbl-gold);">
                                        {{ number_format($producto->pivot->cantidad * $producto->pivot->precio_unitario, 2, ',', '.') }} €
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

                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="alert alert-shonen alert-shonen-info mb-0 h-100 d-flex flex-column justify-content-center">
                            <div class="text-start text-white">
                                <h4 class="font-Oswald text-white mb-3"><i class="fas fa-box-check me-2"></i> INFORMACIÓN DE ENTREGA</h4>
                                <p class="fw-bold mb-3">
                                    {{ $shopSettings->pickup_instructions ?: 'Tu pedido quedará registrado a tu nombre y correo electrónico para su entrega oficial.' }}
                                </p>
                                @guest
                                    <div class="p-2 bg-black border border-secondary small text-info">
                                        <i class="fas fa-user-check me-1"></i> Estás comprando como <strong>invitado</strong>. Recibirás tu código de pedido al completar el pago y en tu correo electrónico.
                                    </div>
                                @endguest
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="checkout-panel">
                            <h3 class="checkout-title">DATOS DEL COMPRADOR Y ENTREGA</h3>

                            <form id="shopCheckoutForm" onsubmit="return false;">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="text-white fw-bold small mb-1">Nombre y Apellidos *</label>
                                        <input type="text" id="chk_nombre" class="form-control bg-dark text-white border-secondary" required value="{{ Auth::check() ? Auth::user()->name : '' }}" placeholder="Nombre completo">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-white fw-bold small mb-1">Correo Electrónico *</label>
                                        <input type="email" id="chk_email" class="form-control bg-dark text-white border-secondary" required value="{{ Auth::check() ? Auth::user()->email : '' }}" placeholder="tu@email.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-white fw-bold small mb-1">Teléfono de contacto <span id="phoneReqMark" class="d-none text-warning">*</span></label>
                                        <input type="text" id="chk_telefono" class="form-control bg-dark text-white border-secondary" placeholder="Opcional en recogida">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-white fw-bold small mb-1">Método de Entrega *</label>
                                        <select id="chk_tipo_entrega" class="form-select bg-dark text-white border-secondary fw-bold">
                                            @if($shopSettings->pickup_enabled)
                                                <option value="recogida" {{ $defaultDelivery === 'recogida' ? 'selected' : '' }}>
                                                    {{ $shopSettings->pickup_label }} (0,00 €)
                                                </option>
                                            @endif
                                            @if($shopSettings->shipping_enabled)
                                                @php
                                                    $costEnvio = $shopSettings->calculateShipping($subtotalProductos, 'envio');
                                                @endphp
                                                <option value="envio" {{ $defaultDelivery === 'envio' ? 'selected' : '' }}>
                                                    Envío a Domicilio ({{ $costEnvio > 0 ? '+' . number_format($costEnvio, 2, ',', '.') . ' €' : '¡GRATIS!' }})
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                {{-- BLOQUE DIRECCIÓN POSTAL (Solo visible cuando Envío está habilitado y seleccionado) --}}
                                <div id="shippingAddressFields" class="row g-3 mb-3 p-3 border border-info bg-dark bg-opacity-50 d-none">
                                    <div class="col-12">
                                        <div class="text-info fw-bold small text-uppercase"><i class="fas fa-shipping-fast me-1"></i> Dirección de Envío Postal</div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="text-white small fw-bold mb-1">Calle, número, piso y puerta *</label>
                                        <input type="text" id="chk_calle" class="form-control form-control-sm bg-black text-white border-secondary">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="text-white small fw-bold mb-1">Código Postal *</label>
                                        <input type="text" id="chk_cp" class="form-control form-control-sm bg-black text-white border-secondary">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-white small fw-bold mb-1">Ciudad / Localidad *</label>
                                        <input type="text" id="chk_ciudad" class="form-control form-control-sm bg-black text-white border-secondary">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-white small fw-bold mb-1">Provincia *</label>
                                        <input type="text" id="chk_provincia" class="form-control form-control-sm bg-black text-white border-secondary">
                                    </div>
                                </div>

                                {{-- RESUMEN DE IMPORTE --}}
                                <div class="border-top border-secondary pt-3 mt-3 mb-4">
                                    <div class="d-flex justify-content-between text-white-50 fw-bold mb-1">
                                        <span>Subtotal artículos:</span>
                                        <span>{{ number_format($subtotalProductos, 2, ',', '.') }} €</span>
                                    </div>
                                    <div class="d-flex justify-content-between text-white-50 fw-bold mb-2" id="shippingSummaryRow">
                                        <span>Gastos de entrega:</span>
                                        <span id="shippingAmountLabel">0,00 €</span>
                                    </div>
                                    <div class="d-flex justify-content-between font-Oswald align-items-center">
                                        <span class="fs-4 text-white">TOTAL A PAGAR:</span>
                                        <span class="fs-2" style="color: var(--sbbl-gold);" id="grandTotalLabel">{{ number_format($subtotalProductos, 2, ',', '.') }} €</span>
                                    </div>
                                </div>

                                <div id="shop-paypal-buttons"></div>
                                <div id="shopPaypalMsg" class="mt-3 text-center fw-bold small"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-secondary mb-4"></i>
                <h3 class="font-Oswald text-white fs-2 mb-3">EL CARRITO ESTÁ VACÍO</h3>
                <p class="text-white-50 fw-bold mb-4">Añade tus artículos desde el catálogo para realizar tu pedido.</p>
                <a href="{{ route('carrito.index') }}" class="btn-shonen btn-shonen-warning fs-4 px-5 py-2">
                    <span>IR AL CATÁLOGO</span>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
@if($carrito->productos->count() > 0 && !empty($paypalClientId))
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $paypalCurrency }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subtotal = {{ (float) $carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario) }};
    const shippingCostSetting = {{ (float) $shopSettings->calculateShipping($carrito->productos->sum(fn($p) => $p->pivot->cantidad * $p->pivot->precio_unitario), 'envio') }};

    const selectEntrega = document.getElementById('chk_tipo_entrega');
    const addrBox = document.getElementById('shippingAddressFields');
    const shipLabel = document.getElementById('shippingAmountLabel');
    const totalLabel = document.getElementById('grandTotalLabel');
    const phoneMark = document.getElementById('phoneReqMark');
    const msg = document.getElementById('shopPaypalMsg');

    function syncDeliveryUI() {
        const isEnvio = selectEntrega.value === 'envio';
        if (isEnvio) {
            addrBox.classList.remove('d-none');
            phoneMark.classList.remove('d-none');
            const total = subtotal + shippingCostSetting;
            shipLabel.textContent = shippingCostSetting.toFixed(2).replace('.', ',') + ' €';
            totalLabel.textContent = total.toFixed(2).replace('.', ',') + ' €';
        } else {
            addrBox.classList.add('d-none');
            phoneMark.classList.add('d-none');
            shipLabel.textContent = '0,00 €';
            totalLabel.textContent = subtotal.toFixed(2).replace('.', ',') + ' €';
        }
    }

    selectEntrega.addEventListener('change', syncDeliveryUI);
    syncDeliveryUI();

    if (typeof window.paypal === 'undefined') {
        msg.innerHTML = '<span class="text-danger">⚠️ Pasarela de PayPal no disponible. Revisa las credenciales configuradas.</span>';
        return;
    }

    paypal.Buttons({
        style: { color: 'gold', shape: 'rect', label: 'pay', height: 45 },

        createOrder: function () {
            msg.innerHTML = '';
            const payload = {
                nombre: document.getElementById('chk_nombre').value.trim(),
                email: document.getElementById('chk_email').value.trim(),
                telefono: document.getElementById('chk_telefono').value.trim(),
                tipo_entrega: selectEntrega.value,
                direccion_calle: document.getElementById('chk_calle').value.trim(),
                direccion_cp: document.getElementById('chk_cp').value.trim(),
                direccion_ciudad: document.getElementById('chk_ciudad').value.trim(),
                direccion_provincia: document.getElementById('chk_provincia').value.trim(),
            };

            if (!payload.nombre || !payload.email) {
                msg.innerHTML = '<span class="text-danger">❌ Introduce tu nombre completo y correo electrónico antes de pagar.</span>';
                return Promise.reject(new Error('Datos incompletos'));
            }

            if (payload.tipo_entrega === 'envio' && (!payload.direccion_calle || !payload.direccion_cp || !payload.direccion_ciudad || !payload.direccion_provincia || !payload.telefono)) {
                msg.innerHTML = '<span class="text-danger">❌ Rellena todos los datos de dirección postal y teléfono para el envío.</span>';
                return Promise.reject(new Error('Dirección incompleta'));
            }

            return fetch("{{ route('carrito.paypal.order') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            }).then(async res => {
                const data = await res.json();
                if (!res.ok || !data.id) {
                    const firstValidationErr = data.errors ? Object.values(data.errors)[0][0] : null;
                    const errStr = firstValidationErr || data.error || 'No se pudo crear la orden de pago.';
                    msg.innerHTML = `<span class="text-danger">❌ ${errStr}</span>`;
                    throw new Error(errStr);
                }
                return data.id;
            });
        },

        onApprove: function (data) {
            msg.innerHTML = '<span class="text-warning"><i class="fas fa-spinner fa-spin me-1"></i> Verificando pago y reservando stock...</span>';

            return fetch("{{ route('carrito.paypal.capture') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ orderID: data.orderID })
            }).then(async res => {
                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    document.getElementById('activeCartContent').classList.add('d-none');
                    document.getElementById('completedOrderRef').textContent = result.referencia;
                    document.getElementById('completedOrderDelivery').textContent =
                        (result.tipo_entrega === 'envio' ? 'Modalidad: Envío a domicilio — ' : 'Modalidad: Recogida presencial — ') + (result.direccion || '');
                    document.getElementById('orderCompletedBanner').classList.remove('d-none');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    msg.innerHTML = `<span class="text-danger">❌ ${result.message || 'Error al confirmar el pago.'}</span>`;
                }
            }).catch(() => {
                msg.innerHTML = '<span class="text-danger">❌ Error de red al confirmar el pedido. Si se efectuó el cargo, queda protegido vía Webhook.</span>';
            });
        },

        onError: function () {
            if (!msg.innerHTML) {
                msg.innerHTML = '<span class="text-danger">❌ Operación cancelada o con error en PayPal.</span>';
            }
        }
    }).render('#shop-paypal-buttons');
});
</script>
@endif
@endsection
