@extends('layouts.app')

@section('title', 'Suscripciones SBBL')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center" style="color: #f8f9fa;">Elige tu suscripción</h2>

    {{-- Mensajes de éxito/error --}}
    <div id="subscription-message" class="mb-4"></div>

    <div class="row text-center">
        @foreach($plans as $plan)
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-light h-100 shadow-sm" style="border-radius: 10px;">
                    <div class="card-body">
                        <h2 class="card-title" style="color:
                            @if($plan->slug === 'bronce') #cd7f32
                            @elseif($plan->slug === 'plata') #c0e5fb
                            @else gold
                            @endif
                        ">{{ $plan->name }}</h2>

                        @if(!empty($plan->features))
                            <ul class="list-unstyled mt-3 text-start">
                                @foreach($plan->features as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        @endif

                        @auth
                        {{-- Mensual --}}
                        <div class="mb-3 p-2 rounded" style="background-color: #3e3e3e;">
                            <h5 class="text-info">Mensual</h5>
                            <h4>{{ number_format($plan->monthly_price,2) }}€/mes</h4>
                            <div class="paypal-button-container"
                                 data-plan-id="{{ $plan->paypal_plan_monthly_id }}"
                                 data-plan-slug="{{ $plan->slug }}"
                                 data-period="monthly"></div>
                        </div>

                        {{-- Anual --}}
                        <div class="mb-3 p-2 rounded" style="background-color: #2a2a2a;">
                            <h5 class="text-warning">Anual</h5>
                            <h4>{{ number_format($plan->annual_price,2) }}€/año</h4>
                            <div class="paypal-button-container"
                                 data-plan-id="{{ $plan->paypal_plan_annual_id }}"
                                 data-plan-slug="{{ $plan->slug }}"
                                 data-period="annual"></div>
                        </div>
                        @else
                        <p class="text-warning">Inicia sesión para suscribirte</p>
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

        console.log("🔹 Plan ID:", planId);
        console.log("🔹 Slug:", slug);
        console.log("🔹 Period:", period);

        if (!planId) {
            container.innerHTML = '<small class="text-danger">Plan no configurado en PayPal</small>';
            return;
        }

        paypal.Buttons({
            style: { shape: 'pill', color: 'gold', layout: 'vertical', label: 'subscribe' },
            createSubscription: (data, actions) => {
                console.log("🔹 Creando suscripción con planId:", planId);
                return actions.subscription.create({ plan_id: planId });
            },
            onApprove: (data) => {
                console.log("✅ Suscripción aprobada. subscriptionID:", data.subscriptionID);

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
                    console.log("📦 Respuesta del backend:", resp);

                    if (resp.success) {
                        messageDiv.innerHTML = '<div class="alert alert-success">¡Suscripción creada correctamente! Redirigiendo...</div>';
                        setTimeout(() => window.location.href = resp.redirect ?? '/', 1500);
                    } else {
                        messageDiv.innerHTML = '<div class="alert alert-danger">' + (resp.message || 'Hubo un error validando la suscripción.') + '</div>';
                    }
                })
                .catch(err => {
                    console.error("❌ Error al hacer fetch:", err);
                    messageDiv.innerHTML = '<div class="alert alert-danger">Error al procesar el pago con PayPal.</div>';
                });
            },
            onError: (err) => {
                console.error("❌ Error en PayPal Buttons:", err);
                messageDiv.innerHTML = '<div class="alert alert-danger">Error al procesar el pago con PayPal.</div>';
            }
        }).render(container);
    });
    @endauth
});
</script>

@endsection
