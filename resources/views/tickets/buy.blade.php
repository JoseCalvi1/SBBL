@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <div class="christmas-bg">

        {{-- Copos de nieve --}}
        @for ($i = 0; $i < 20; $i++)
            <div class="snowflake" style="left: {{ rand(0,100) }}%; animation-delay: {{ rand(0,10) }}s;">
                ❄
            </div>
        @endfor

        <div class="main-card mx-auto my-5 p-4">
            <div class="row g-4">

                {{-- COLUMNA IZQUIERDA --}}
                <div class="col-md-7">
                    <h2 class="title mb-3 text-center">
                        🎄✨ RIFA NAVIDEÑA 2025 ✨🎄
                    </h2>

                    <p class="lead">
                        Gracias a vuestra colaboración a través de las suscripciones y las Copas Let It Rip,
                        hemos preparado una rifa para celebrar estas fiestas.
                    </p>

                    <p>
                        Queremos agradecer a la comunidad por su apoyo y, al mismo tiempo, recaudar fondos para el
                        <strong>Nacional 2026</strong> y futuros eventos.
                    </p>

                    <h5 class="mt-4"><strong>📅 Fecha del sorteo:</strong></h5>
                    <p class="mb-1">🗓️ 19 de diciembre</p>
                    <p>🎥 Se realizará en directo en nuestro canal de YouTube</p>

                    <hr class="divider">

                    <h4 class="mb-3">🏆 Premios:</h4>

                    <ul>
                        <li><strong>1º Premio:</strong> Dran Buster morado, lanzador string morado, 2 botellas, 2 toallas, 2 llaveros, 2 carpetas oficiales</li>
                        <li class="mt-2"><strong>2º Premio:</strong> Cobalt Dragoon metal coat negro, 2 botellas, 2 toallas, 2 llaveros, 2 carpetas oficiales</li>
                        <li class="mt-2"><strong>3º Premio:</strong> 1 botella, 1 toalla, 1 llavero, 1 carpeta oficial</li>
                    </ul>

                    <p class="mt-3 text-center">🌟 ¡Vamos a cerrar el año por todo lo alto! 🌟</p>
                </div>

                {{-- COLUMNA DERECHA
                <div class="col-md-5">
                    <div class="ticket-card">
                        <h3 class="text-center mb-3">🎫 Compra tus tickets</h3>

                        <p class="text-center">Solo <strong>3€</strong> cada uno</p>

                        <div class="mb-3">
                            <label class="mb-2">Cantidad de tickets</label>
                            <input id="ticketQty" type="number" min="1" value="1" class="form-control">
                        </div>

                        <div id="paypal-buttons" class="my-3"></div>
                        <div id="buyMsg" class="mt-3"></div>

                    </div>
                </div>--}}

            </div>
        </div>

        {{-- BOTÓN DE LA MAGIA --}}
        <div class="text-center my-5">
            <button id="toggleIconBtn" class="btn btn-magic">
                <span id="magicIcon">✨</span> VER LA MAGIA
            </button>
        </div>

    </div>

</div>
@endsection

@section('styles')
<style>
/* Fondo general */
.christmas-bg {
    background: radial-gradient(circle at top, #2a2340 0%, #1a1528 60%, #181225 100%);
    padding: 40px 0;
    position: relative;
    overflow: hidden;
}

/* Tarjeta principal */
.main-card {
    max-width: 1100px;
    backdrop-filter: blur(10px);
    color: white;
}

/* Copos de nieve */
@keyframes snow {
    0% { transform: translateY(-10vh); opacity: 1; }
    100% { transform: translateY(100vh); opacity: 0; }
}
.snowflake {
    position: absolute;
    top: -25px;
    color: white;
    font-size: 1.2em;
    animation: snow linear infinite;
    opacity: 0.8;
}
.snowflake:nth-child(odd) { animation-duration: 8s; }
.snowflake:nth-child(even) { animation-duration: 12s; }

/* Títulos */
.title {
    font-size: 2rem;
    font-weight: 900;
    color: #ffccd5;
    text-shadow: 0 0 12px rgba(255, 110, 150, 0.9);
}

/* Divider */
.divider {
    border-top: 1px solid rgba(255,255,255,0.3);
}

/* Tarjeta de compra */
.ticket-card {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 0 18px rgba(255, 80, 80, 0.25);
}

/* Inputs */
.form-control {
    background: rgba(255,255,255,0.25);
    border: none;
    color: white;
}
.form-control:focus {
    background: rgba(255,255,255,0.35);
    box-shadow: 0 0 8px #ff88aa;
}

/* --- ESTILO DEL BOTÓN DE LA MAGIA (NUEVO) --- */
.btn-magic {
    /* Base Navideña */
    background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%); /* Rojo frambuesa */
    color: white;
    font-weight: bold;
    font-size: 1.15rem;
    padding: 10px 30px;
    border-radius: 50px;

    /* Borde de caramelo (candy cane) */
    border: 3px solid #fff;
    box-shadow: 0 0 15px rgba(233, 30, 99, 0.7); /* Brillo rojo */
    transition: all 0.2s ease;

    /* Estilo para que el texto no se mueva */
    line-height: 1;
}

/* Efecto Hover: Más brillo y escala */
.btn-magic:hover {
    background: linear-gradient(135deg, #d81b60 0%, #c2185b 100%);
    box-shadow: 0 0 25px rgba(233, 30, 99, 1);
    transform: scale(1.05);
    color: white; /* Aseguramos que el texto siga siendo blanco */
    border-color: #fce4ec; /* Borde más brillante en hover */
}

/* Efecto de Pulsación (Active/Focus): "Hundir" el botón */
.btn-magic:active, .btn-magic:focus {
    transform: scale(0.98);
    box-shadow: 0 0 5px rgba(233, 30, 99, 0.5);
    background: #c2185b; /* Tono más oscuro al pulsar */
}

/* Estilo para el botón cuando la magia está activada (cambio de icono) */
.btn-magic.active-magic {
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%); /* Cambia a Verde */
    box-shadow: 0 0 15px rgba(76, 175, 80, 0.7); /* Brillo verde */
    border-color: #e8f5e9;
}
.btn-magic.active-magic:hover {
    background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
    box-shadow: 0 0 25px rgba(76, 175, 80, 1);
}
</style>
@endsection

@section('scripts')
{{-- PASO 1: Cargar el SDK de PayPal primero usando PAYPAL_CLIENT_ID (estandarizado) --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_LIVE_CLIENT_ID') }}&currency=EUR"></script>

{{-- PASO 2: Ejecutar el código que usa 'paypal' una vez que el DOM esté listo --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleIconBtn');
    const magicIcon = document.getElementById('magicIcon'); // Referencia al icono
    let showingSnow = true;

    const qtyInput = document.getElementById('ticketQty');
    const msg = document.getElementById('buyMsg');

    // LÓGICA DEL BOTÓN DE LA MAGIA
    toggleBtn.addEventListener('click', () => {

        // 1. Cambia el contenido de los copos
        document.querySelectorAll('.snowflake').forEach(el => {
            el.textContent = showingSnow ? '🦎' : '❄';
        });

        // 2. Cambia el icono del botón y su clase (para el estilo verde)
        if (showingSnow) {
            magicIcon.textContent = '🎉';
            toggleBtn.classList.add('active-magic');
        } else {
            magicIcon.textContent = '✨';
            toggleBtn.classList.remove('active-magic');
        }

        showingSnow = !showingSnow;
    });

    // LÓGICA DE PAYPAL
    paypal.Buttons({
        style: { color: 'gold', shape: 'pill', label: 'pay', height: 45 },

        createOrder: function() {
            const qty = parseInt(qtyInput.value) || 1;

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
                    msg.innerHTML = `<div class="alert alert-danger">❌ Error creando orden: ${data.error_details ?? data.details ?? 'Desconocido'}</div>`;
                    throw new Error("createOrder failed");
                }

                return data.id;
            });
        },

        onApprove: function(data) {
            msg.innerHTML = `<div class="alert alert-info">⌛ Procesando pago... Por favor, espere.</div>`;

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
                        <div class="alert alert-success">
                            🎉 <strong>¡Compra completada!</strong><br>
                            Tickets asignados: <strong>${result.created}</strong><br>
                            IDs: <small>${result.tickets.join(', ')}</small>
                        </div>`;
                } else {
                    const errorMessage = result.paypal_name ? `${result.paypal_name}: ${result.details}` : result.error;
                    msg.innerHTML = `<div class="alert alert-danger">❌ Error en la captura: ${errorMessage}</div>`;
                }
            })
            .catch(error => {
                msg.innerHTML = `<div class="alert alert-danger">❌ Error de conexión al servidor al capturar el pago.</div>`;
            });
        },

        onError: function(err) {
            msg.innerHTML = `<div class="alert alert-danger">❌ Error con PayPal (intente de nuevo).</div>`;
        }
    }).render('#paypal-buttons');
});
</script>
@endsection
