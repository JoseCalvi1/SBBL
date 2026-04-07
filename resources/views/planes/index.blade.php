@extends('layouts.app')

@section('title', 'Suscripciones SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: AURA / SUSCRIPCIONES (Hereda de layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Oswald', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 2rem;
    }

    /* ── TARJETAS DE PLANES (AURA) ── */
    .aura-card {
        background: var(--sbbl-blue-2);
        border: 4px solid #000;
        border-radius: 0 20px 0 20px;
        box-shadow: 8px 8px 0px #000;
        transition: 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Efecto Hover Diferenciado por Nivel */
    .card-bronce:hover { border-color: #cd7f32; box-shadow: 10px 10px 0 #cd7f32; transform: translate(-2px, -2px); }
    .card-plata:hover { border-color: #c0e5fb; box-shadow: 10px 10px 0 #c0e5fb; transform: translate(-2px, -2px); }
    .card-oro:hover { border-color: var(--sbbl-gold); box-shadow: 10px 10px 0 var(--sbbl-gold); transform: translate(-2px, -2px); }

    /* Cabecera de Tarjeta */
    .aura-header {
        background: #000;
        padding: 20px;
        border-bottom: 4px solid #333;
        text-align: center;
    }
    .aura-title {
        font-family: 'Oswald', cursive;
        font-size: 2.5rem;
        margin: 0;
        letter-spacing: 2px;
        text-shadow: 2px 2px 0 #000;
    }

    /* Colores por Nivel */
    .title-bronce { color: #cd7f32; border-bottom-color: #cd7f32; }
    .title-plata { color: #c0e5fb; border-bottom-color: #c0e5fb; }
    .title-oro { color: var(--sbbl-gold); border-bottom-color: var(--sbbl-gold); }

    /* Lista de Beneficios */
    .aura-features {
        padding: 20px;
        flex-grow: 1;
        background-image: radial-gradient(rgba(255, 255, 255, 0.05) 2px, transparent 2px);
        background-size: 15px 15px;
    }
    .aura-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .aura-features li {
        color: #fff;
        font-weight: bold;
        margin-bottom: 15px;
        padding-left: 25px;
        position: relative;
        font-size: 0.95rem;
    }
    .aura-features li::before {
        content: '\f0da'; /* Icono de flecha de FontAwesome */
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 0;
        color: var(--shonen-cyan);
    }

    /* Caja de Precios */
    .price-box {
        background: #000;
        border: 2px solid #333;
        padding: 15px;
        margin-bottom: 15px;
        transform: skewX(-5deg);
    }
    .price-box > * { transform: skewX(5deg); }

    .price-label {
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        margin-bottom: 5px;
        letter-spacing: 1px;
    }
    .price-amount {
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 1.8rem;
        color: #fff;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="page-title"><i class="fas fa-fire me-2 text-white" style="text-shadow: none;"></i> DESBLOQUEA TU AURA</h1>
        <p class="text-white fw-bold bg-dark d-inline-block px-4 py-2 border border-secondary" style="transform: skewX(-5deg);">
            <span style="display:block; transform: skewX(5deg);">Obtén acceso a estadísticas avanzadas, herramientas tácticas y apoya a la liga.</span>
        </p>
    </div>

    {{-- Mensajes de éxito/error --}}
    <div id="subscription-message" class="mb-4"></div>

    <div class="row justify-content-center g-4">
        @foreach($plans as $plan)
            @php
                $nivel = strtolower($plan->slug);
                $claseTarjeta = 'card-oro';
                $claseTitulo = 'title-oro';

                if ($nivel === 'bronce') {
                    $claseTarjeta = 'card-bronce';
                    $claseTitulo = 'title-bronce';
                } elseif ($nivel === 'plata') {
                    $claseTarjeta = 'card-plata';
                    $claseTitulo = 'title-plata';
                }
            @endphp

            <div class="col-md-6 col-lg-4">
                <div class="aura-card {{ $claseTarjeta }}">

                    {{-- Cabecera --}}
                    <div class="aura-header {{ $claseTitulo }}">
                        <h2 class="aura-title">{{ $plan->name }}</h2>
                    </div>

                    {{-- Características --}}
                    <div class="aura-features">
                        @if(!empty($plan->features))
                            <ul>
                                @foreach($plan->features as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-white opacity-50 text-center fw-bold mt-4">Sin datos de configuración.</p>
                        @endif
                    </div>

                    {{-- Pago / Suscripción --}}
                    <div class="p-3" style="background: var(--sbbl-blue-1); border-top: 3px solid #000;">
                        @auth
                            {{-- Mensual --}}
                            <div class="price-box">
                                <div class="price-label text-info">PAQUETE MENSUAL</div>
                                <div class="price-amount">{{ number_format($plan->monthly_price,2) }}€ <small class="text-white fs-6">/mes</small></div>
                                <div class="paypal-button-container"
                                     data-plan-id="{{ $plan->paypal_plan_monthly_id }}"
                                     data-plan-slug="{{ $plan->slug }}"
                                     data-period="monthly"></div>
                            </div>

                            {{-- Anual --}}
                            <div class="price-box">
                                <div class="price-label" style="color: var(--sbbl-gold);">PAQUETE ANUAL (AHORRO)</div>
                                <div class="price-amount">{{ number_format($plan->annual_price,2) }}€ <small class="text-white fs-6">/año</small></div>
                                <div class="paypal-button-container"
                                     data-plan-id="{{ $plan->paypal_plan_annual_id }}"
                                     data-plan-slug="{{ $plan->slug }}"
                                     data-period="annual"></div>
                            </div>
                        @else
                            <div class="alert alert-dark border-secondary text-center mb-0 bg-black">
                                <p class="text-white font-Oswald fs-4 mb-0">INICIA SESIÓN PARA ACTIVAR</p>
                            </div>
                        @endauth
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
@php
$paypalClientId = config('paypal.mode') === 'sandbox'
    ? config('paypal.sandbox.client_id')
    : config('paypal.live.client_id');
@endphp

<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&vault=true&intent=subscription"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @auth
    const messageDiv = document.getElementById('subscription-message');

    document.querySelectorAll('.paypal-button-container').forEach(container => {
        const planId = container.dataset.planId;
        const slug = container.dataset.planSlug;
        const period = container.dataset.period;

        if (!planId) {
            container.innerHTML = '<div class="alert alert-danger p-2 text-center small fw-bold">Plan inactivo en pasarela</div>';
            return;
        }

        paypal.Buttons({
            style: { shape: 'rect', color: 'gold', layout: 'vertical', label: 'subscribe' },
            createSubscription: (data, actions) => {
                return actions.subscription.create({ plan_id: planId });
            },
            onApprove: (data) => {
                // Interfaz de carga visual
                messageDiv.innerHTML = '<div class="alert alert-info border border-info bg-black text-white font-Oswald fs-4 text-center"><i class="fas fa-spinner fa-spin me-2"></i> VERIFICANDO FIRMA DE AURA...</div>';

                fetch("{{ route('paypal.confirm') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        subscription_id: data.subscriptionID,
                        plan_slug: slug,
                        period: period
                    })
                })
                .then(r => r.json())
                .then(resp => {
                    if (resp.success) {
                        messageDiv.innerHTML = '<div class="alert alert-success border border-success bg-black text-success font-Oswald fs-4 text-center"><i class="fas fa-check-circle me-2"></i> ¡AURA DESBLOQUEADA CORRECTAMENTE! REINICIANDO SISTEMAS...</div>';
                        setTimeout(() => window.location.href = resp.redirect ?? '/', 2000);
                    } else {
                        messageDiv.innerHTML = '<div class="alert alert-danger border border-danger bg-black text-danger font-Oswald fs-4 text-center"><i class="fas fa-exclamation-triangle me-2"></i> ERROR: ' + (resp.message || 'FALLO EN LA VALIDACIÓN.') + '</div>';
                    }
                })
                .catch(err => {
                    console.error("❌ Error de transmisión:", err);
                    messageDiv.innerHTML = '<div class="alert alert-danger border border-danger bg-black text-danger font-Oswald fs-4 text-center"><i class="fas fa-exclamation-triangle me-2"></i> ERROR AL PROCESAR EL PAGO EN EL SERVIDOR CENTRAL.</div>';
                });
            },
            onError: (err) => {
                console.error("❌ Error en Pasarela:", err);
                messageDiv.innerHTML = '<div class="alert alert-danger border border-danger bg-black text-danger font-Oswald fs-4 text-center"><i class="fas fa-exclamation-triangle me-2"></i> LA CONEXIÓN CON PAYPAL HA SIDO INTERRUMPIDA.</div>';
            }
        }).render(container);
    });
    @endauth
});
</script>
@endsection
