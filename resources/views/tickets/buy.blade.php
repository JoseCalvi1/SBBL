@extends('layouts.app')

@section('title', $raffle ? $raffle->title : 'Rifas Oficiales SBBL')

@section('styles')
<style>
    .raffle-bg {
        background: radial-gradient(circle at center, var(--sbbl-blue-1) 0%, #000 100%);
        min-height: calc(100vh - 100px);
    }
    .main-card {
        background: var(--sbbl-blue-2);
        border: 4px solid #000;
        box-shadow: 10px 10px 0 var(--sbbl-gold);
        color: white;
    }
    .ticket-card {
        background: rgba(0, 0, 0, 0.6);
        border: 3px solid #000;
        padding: 30px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0 raffle-bg">
    <div class="container py-5">

        @if(!$raffle)
            <div class="main-card mx-auto p-5 text-center" style="max-width: 750px;">
                <i class="fas fa-ticket-alt fa-4x mb-3" style="color: var(--sbbl-gold);"></i>
                <h2 class="font-Oswald text-white mb-3" style="font-size: 2.8rem;">NO HAY NINGUNA RIFA ACTIVA</h2>
                <p class="text-white-50 fw-bold fs-5 mb-4">
                    En este momento no hay papeletas a la venta. ¡Mantente atento a nuestras redes y anuncios de Discord para la próxima rifa de la SBBL!
                </p>
                <a href="{{ route('tickets.mine') }}" class="btn-shonen btn-shonen-info px-4 py-2 fs-5">
                    <span><i class="fas fa-receipt me-2"></i> CONSULTAR MIS PAPELETAS</span>
                </a>
            </div>
        @else
            <div class="main-card mx-auto p-4 p-md-5">
                <div class="row g-5">
                    {{-- COLUMNA IZQUIERDA: INFORMACIÓN DE LA RIFA --}}
                    <div class="col-md-7">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="d-inline-block bg-black text-white px-3 py-1 font-Oswald fs-5" style="border: 2px solid var(--shonen-red); box-shadow: 4px 4px 0 #000;">
                                <i class="fas fa-ticket-alt me-2 text-warning"></i> RIFA OFICIAL SBBL
                            </div>
                            <a href="{{ route('tickets.mine') }}" class="btn btn-sm btn-outline-info fw-bold">
                                <i class="fas fa-list-ul me-1"></i> Mis Papeletas ({{ $userTicketsCount }})
                            </a>
                        </div>

                        <h2 class="font-Oswald mb-3" style="font-size: 3.2rem; text-shadow: 3px 3px 0 var(--shonen-red);">
                            {{ $raffle->title }}
                        </h2>

                        @if($raffle->banner_b64)
                            <div class="mb-4 border border-2 border-dark bg-black text-center">
                                <img src="{{ $raffle->banner_b64 }}" alt="{{ $raffle->title }}" class="img-fluid" style="max-height: 280px; object-fit: contain;">
                            </div>
                        @endif

                        @if($raffle->description)
                            <p class="lead fw-bold text-white mb-4" style="white-space: pre-line;">{{ $raffle->description }}</p>
                        @endif

                        @if($raffle->prizes_info)
                            <div class="bg-black p-4 border border-warning mb-4" style="box-shadow: 5px 5px 0 #000;">
                                <h4 class="font-Oswald mb-3" style="color: var(--sbbl-gold);">
                                    <i class="fas fa-trophy me-2"></i> PREMIOS DEL SORTEO
                                </h4>
                                <div class="text-white fw-bold" style="white-space: pre-line;">{{ $raffle->prizes_info }}</div>
                            </div>
                        @endif

                        <div class="bg-black p-4 border border-secondary" style="box-shadow: 5px 5px 0 #000;">
                            <h5 class="font-Oswald text-info mb-3">CONDICIONES DE PARTICIPACIÓN</h5>
                            <ul class="list-unstyled mb-0 text-white">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Precio por papeleta:</strong> {{ number_format($raffle->ticket_price, 2, ',', '.') }} €</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Límite por cuenta:</strong> Máximo {{ $raffle->max_per_user }} papeletas por blader (tienes {{ $userTicketsCount }}).</li>
                                @if($availableTotal !== null)
                                    <li class="mb-2"><i class="fas fa-layer-group text-warning me-2"></i> <strong>Disponibilidad:</strong> Quedan {{ $availableTotal }} de {{ $raffle->max_tickets }} papeletas totales.</li>
                                @endif
                                @if($raffle->ends_at)
                                    <li class="mb-2"><i class="far fa-clock text-info me-2"></i> <strong>Cierre de venta:</strong> {{ $raffle->ends_at->format('d/m/Y H:i') }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: PASARELA DE COMPRA --}}
                    <div class="col-md-5">
                        <div class="ticket-card h-100 d-flex flex-column justify-content-center">
                            <h3 class="text-center font-Oswald mb-1" style="font-size: 2rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000;">
                                ADQUIRIR PAPELETAS
                            </h3>
                            <p class="text-center text-white-50 mb-4 fw-bold">
                                1 Papeleta = {{ number_format($raffle->ticket_price, 2, ',', '.') }} €
                            </p>

                            @if($soldOut)
                                <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger text-center p-4">
                                    <h4 class="fw-bold mb-1 text-white"><i class="fas fa-lock me-2"></i> VENTA CERRADA / AGOTADA</h4>
                                    <p class="text-white-50 mb-0">Esta rifa ya no admite la compra de más papeletas.</p>
                                </div>
                            @elseif($maxAllowedNow <= 0)
                                <div class="alert alert-info bg-info bg-opacity-10 border border-info text-center p-4">
                                    <h5 class="fw-bold text-white mb-2"><i class="fas fa-check-double me-2"></i> LÍMITE ALCANZADO</h5>
                                    <p class="text-white-50 mb-3">Ya has adquirido el máximo permitido de {{ $raffle->max_per_user }} papeletas en esta rifa.</p>
                                    <a href="{{ route('tickets.mine') }}" class="btn btn-info text-dark fw-bold">Ver mis papeletas</a>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="mb-2 text-white font-Oswald fs-5">CANTIDAD (Máx. {{ $maxAllowedNow }}):</label>
                                    <input id="ticketQty" type="number" min="1" max="{{ $maxAllowedNow }}" value="1" class="form-control form-control-lg text-center fw-bold bg-dark text-white border-secondary" style="font-size: 1.5rem;">
                                </div>

                                <div class="d-flex justify-content-between align-items-center bg-black p-3 border border-secondary mb-3">
                                    <span class="text-white-50 fw-bold">TOTAL A ABONAR:</span>
                                    <span class="font-Oswald fs-3" style="color: var(--sbbl-gold);" id="totalRafflePrice">
                                        {{ number_format($raffle->ticket_price, 2, ',', '.') }} €
                                    </span>
                                </div>

                                <div id="paypal-buttons" class="my-2"></div>
                                <div id="buyMsg" class="mt-3 text-center font-Oswald fs-6"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
@if($raffle && !$soldOut && $maxAllowedNow > 0 && !empty($paypalClientId))
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $paypalCurrency }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('ticketQty');
    const totalEl = document.getElementById('totalRafflePrice');
    const msg = document.getElementById('buyMsg');
    const unitPrice = {{ (float) $raffle->ticket_price }};
    const maxAllowed = {{ (int) $maxAllowedNow }};

    function syncTotal() {
        let val = parseInt(qtyInput.value) || 1;
        if (val < 1) val = 1;
        if (val > maxAllowed) val = maxAllowed;
        qtyInput.value = val;
        totalEl.textContent = (val * unitPrice).toFixed(2).replace('.', ',') + ' €';
    }

    qtyInput.addEventListener('input', syncTotal);
    qtyInput.addEventListener('change', syncTotal);

    if (typeof window.paypal === 'undefined') {
        msg.innerHTML = '<div class="text-danger">⚠️ Error cargando PayPal SDK. Revisa la configuración de credenciales.</div>';
        return;
    }

    paypal.Buttons({
        style: { color: 'gold', shape: 'rect', label: 'pay', height: 45 },

        createOrder: function() {
            msg.innerHTML = '';
            const qty = parseInt(qtyInput.value) || 1;

            return fetch("{{ route('paypal.order') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ quantity: qty })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.id) {
                    const errText = data.details || data.error_details || 'Error creando orden.';
                    msg.innerHTML = `<div class="text-danger">❌ ${errText}</div>`;
                    throw new Error(errText);
                }
                return data.id;
            });
        },

        onApprove: function(data) {
            msg.innerHTML = `<div class="text-warning"><i class="fas fa-spinner fa-spin me-1"></i> Verificando pago y asignando papeletas...</div>`;

            return fetch("{{ route('paypal.capture') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ orderID: data.orderID })
            })
            .then(async res => {
                const result = await res.json();
                if (res.ok && result.status === 'success') {
                    msg.innerHTML = `
                        <div class="text-success text-start bg-black p-3 border border-success">
                            🎉 <strong>¡PAGO CONFIRMADO!</strong><br>
                            Papeletas asignadas: <strong>${result.created}</strong><br>
                            <code class="text-warning">${result.tickets.join('<br>')}</code>
                            <div class="mt-2"><a href="{{ route('tickets.mine') }}" class="btn btn-sm btn-outline-success w-100 fw-bold">Ver todas mis papeletas</a></div>
                        </div>`;
                } else {
                    msg.innerHTML = `<div class="text-danger">❌ ${result.error || 'Error verificando la captura del pago.'}</div>`;
                }
            })
            .catch(() => {
                msg.innerHTML = `<div class="text-danger">❌ Error de conexión al capturar el pago.</div>`;
            });
        },

        onError: function() {
            if (!msg.innerHTML) {
                msg.innerHTML = `<div class="text-danger">❌ Operación cancelada o con error en la ventana de PayPal.</div>`;
            }
        }
    }).render('#paypal-buttons');
});
</script>
@endif
@endsection
