@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 beycon-bg">

    <div class="container py-5">
        <div class="main-card mx-auto p-4 p-md-5">
            <div class="row g-5">

                {{-- COLUMNA IZQUIERDA: INFO BEYCON --}}
                <div class="col-md-7">
                    <div class="d-inline-block bg-black text-white px-3 py-1 mb-3 font-Oswald fs-4" style="border: 2px solid var(--shonen-red); box-shadow: 4px 4px 0 #000; transform: skewX(-10deg);">
                        <span style="display:block; transform: skewX(10deg);"><i class="fas fa-ticket-alt me-2 text-warning"></i> TICKETS OFICIALES</span>
                    </div>

                    <h2 class="title mb-4 font-Oswald" style="font-size: 3.5rem; text-shadow: 3px 3px 0 var(--shonen-red);">
                        BEYCON '26
                    </h2>

                    <p class="lead fw-bold text-white mb-4">
                        Asegura tu asistencia en el mayor evento nacional de Beyblade de España. Adquiere tus entradas para competir, participar en side events y vivir la experiencia SBBL.
                    </p>

                    <div class="bg-black p-4 border border-secondary mb-4" style="transform: skewX(-2deg); box-shadow: 5px 5px 0 #000;">
                        <div style="transform: skewX(2deg);">
                            <h4 class="font-Oswald" style="color: var(--sbbl-gold);">INFORMACIÓN DE LA ENTRADA</h4>
                            <ul class="list-unstyled mb-0 mt-3 text-white">
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Precio:</strong> 5,00€ Entrada + 0,50€ Gastos de gestión (Total: 5,50€).</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> <strong>Límite:</strong> Máximo 5 entradas por cuenta.</li>
                                <li><i class="fas fa-exclamation-circle text-warning me-2"></i> <strong>Reembolsos:</strong> Aplicable contactando con la organización hasta 2 semanas antes del evento.</li>
                            </ul>
                        </div>
                    </div>

                    <h5 class="mt-4 font-Oswald fs-4 text-white"><i class="fas fa-map-marker-alt text-danger me-2"></i> DATOS DEL EVENTO</h5>
                    <p class="mb-1 text-white-50">🗓️ <strong>Fecha:</strong> 18 de Julio de 2026</p>
                    <p class="text-white-50">📍 <strong>Lugar:</strong> Sevilla (Hotel Vértice Aljarafe)</p>

                </div>

                {{-- COLUMNA DERECHA: COMPRA --}}
                <div class="col-md-5">
                    <div class="ticket-card h-100 d-flex flex-column justify-content-center">
                        <h3 class="text-center font-Oswald mb-2" style="font-size: 2rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000;">PASARELA DE PAGO</h3>
                        <p class="text-center text-white-50 mb-4 fw-bold">1 Ticket = 5,50€</p>

                        @if($soldOut)
                            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger text-center p-4 mt-3">
                                <h4 class="fw-bold mb-0 text-white"><i class="fas fa-times-circle me-2"></i> ¡ENTRADAS AGOTADAS (SOLD OUT)!</h4>
                                <p class="text-white-50 mt-2 mb-0">Se ha alcanzado el aforo máximo para la BeyCon.</p>
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="mb-2 text-white font-Oswald fs-5">CANTIDAD (Máx 5):</label>
                                <input id="ticketQty" type="number" min="1" max="5" value="1" class="form-control form-control-lg text-center fw-bold bg-dark text-white border-secondary" style="font-size: 1.5rem;">
                            </div>

                            <div id="paypal-buttons" class="my-3"></div>
                            <div id="buyMsg" class="mt-3 text-center font-Oswald fs-5"></div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- BOTÓN DE LA MAGIA --}}
        <div class="text-center mt-5">
            <button id="toggleIconBtn" class="btn btn-magic font-Oswald" style="background: aliceblue;box-shadow: 4px 4px 0 #f9f9f9; transform: skewX(-5deg);">
                <div style="transform: skewX(5deg);">
                    <span id="magicIcon">✨</span> HAZ LA MAGIA
                </div>
            </button>
        </div>

    </div>
</div>
@endsection

@section('styles')
<style>
/* Fondo general Beycon */
.beycon-bg {
    background: radial-gradient(circle at center, var(--sbbl-blue-1) 0%, #000 100%);
    background-image: repeating-linear-gradient(45deg, rgba(0,0,0,0.1) 0px, rgba(0,0,0,0.1) 10px, transparent 10px, transparent 20px), radial-gradient(circle at center, var(--sbbl-blue-1) 0%, #000 100%);
    min-height: calc(100vh - 100px);
}

/* Tarjeta principal */
.main-card {
    background: var(--sbbl-blue-2);
    border: 4px solid #000;
    box-shadow: 10px 10px 0 var(--sbbl-gold);
    color: white;
}

/* Tarjeta de compra */
.ticket-card {
    background: rgba(0, 0, 0, 0.5);
    border: 3px solid #000;
    padding: 30px;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.8);
}

/* --- ESTILO DEL BOTÓN DE LA MAGIA --- */
.btn-magic {
    background: var(--sbbl-gold);
    color: #000;
    font-size: 1.5rem;
    padding: 10px 40px;
    border: 3px solid #000;
    transition: all 0.2s ease;
}

.btn-magic:hover {
    background: var(--shonen-red);
    color: #fff;
    box-shadow: 4px 4px 0 var(--sbbl-gold) !important;
}

.btn-magic.active-magic {
    background: #ff7bac; /* Color rosado bacon */
    color: #000;
}

/* --- ANIMACIÓN BACON LLOVIENDO --- */
@keyframes bacon-fall {
    0% { transform: translateY(-10vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
}
.bacon-drop {
    position: fixed;
    top: -10%;
    z-index: 9999;
    font-size: 2.5rem;
    animation: bacon-fall linear forwards;
    pointer-events: none;
    user-select: none;
}
</style>
@endsection

@section('scripts')
{{-- Cargar el SDK de PayPal --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_LIVE_CLIENT_ID') }}&currency=EUR"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA DE LA LLUVIA DE BACON ---
    const toggleBtn = document.getElementById('toggleIconBtn');
    const magicIcon = document.getElementById('magicIcon');
    let baconInterval = null;

    toggleBtn.addEventListener('click', () => {
        if (baconInterval) {
            // Detener magia
            clearInterval(baconInterval);
            baconInterval = null;
            magicIcon.textContent = '✨';
            toggleBtn.classList.remove('active-magic');

            // Limpiar bacons de la pantalla
            document.querySelectorAll('.bacon-drop').forEach(b => b.remove());
        } else {
            // Empezar magia
            magicIcon.textContent = '🥓';
            toggleBtn.classList.add('active-magic');

            baconInterval = setInterval(() => {
                const bacon = document.createElement('div');
                bacon.className = 'bacon-drop';
                bacon.textContent = '🥓';
                bacon.style.left = Math.random() * 100 + 'vw';
                bacon.style.animationDuration = (Math.random() * 3 + 2) + 's'; // Entre 2 y 5 segundos en caer
                document.body.appendChild(bacon);

                // Limpiar del DOM cuando termina la animación
                setTimeout(() => {
                    if (bacon.parentNode) bacon.remove();
                }, 5000);
            }, 150); // Caen bacons cada 150ms
        }
    });

    // --- LÓGICA DE PAYPAL ---
    const qtyInput = document.getElementById('ticketQty');
    const msg = document.getElementById('buyMsg');

    // Validación de cantidad en el input
    qtyInput.addEventListener('change', function() {
        let val = parseInt(this.value);
        if (val < 1) this.value = 1;
        if (val > 5) this.value = 5;
    });

    paypal.Buttons({
        style: { color: 'gold', shape: 'rect', label: 'pay', height: 45 },

        createOrder: function(data, actions) {
            const qty = parseInt(qtyInput.value) || 1;

            if(qty < 1 || qty > 5) {
                msg.innerHTML = `<div class="text-danger">❌ Solo puedes comprar entre 1 y 5 tickets.</div>`;
                return;
            }

            return fetch("{{ route('paypal.order') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ quantity: qty })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.id) {
                    msg.innerHTML = `<div class="text-danger">❌ Error creando orden.</div>`;
                    throw new Error("createOrder failed");
                }
                return data.id;
            });
        },

        onApprove: function(data) {
            msg.innerHTML = `<div class="text-warning">⌛ Procesando pago... Por favor, espere.</div>`;

            return fetch("{{ route('paypal.capture') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ orderID: data.orderID })
            })
            .then(async res => {
                const result = await res.json();

                if (result.status === 'success') {
                    msg.innerHTML = `
                        <div class="text-success text-start bg-dark p-3 border border-success">
                            🎉 <strong>¡COMPRA COMPLETADA!</strong><br>
                            Entradas: <strong>${result.created}</strong><br>
                            <small class="text-white-50">${result.tickets.join('<br>')}</small>
                        </div>`;
                } else {
                    const errorMessage = result.paypal_name ? `${result.paypal_name}: ${result.details}` : result.error;
                    msg.innerHTML = `<div class="text-danger">❌ Error: ${errorMessage}</div>`;
                }
            })
            .catch(error => {
                msg.innerHTML = `<div class="text-danger">❌ Error de conexión al capturar el pago.</div>`;
            });
        },

        onError: function(err) {
            msg.innerHTML = `<div class="text-danger">❌ Error en la ventana de PayPal.</div>`;
        }
    }).render('#paypal-buttons');
});
</script>
@endsection
