@extends('layouts.app')

@section('title', 'Suscripciones SBBL')

@section('content')
<div class="container my-5" style="border-radius: 8px; padding: 20px; color: #ffffff;">
    <h1 class="text-center mb-4" style="color: #f8f9fa;">Suscripciones</h1>
    <div class="row text-center">
        <!-- Nivel 1 -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-light h-100 shadow-sm mb-2" style="border-radius: 10px;">
                <div class="card-body">
                    <h2 class="card-title" style="color: #cd7f32;">Nivel 1</h2>
                    <ul class="list-unstyled mt-3">
                        <li>Nombre destacado en color bronce</li>
                        <li>Subtítulo personal (10 opciones)</li>
                        <li>Copas de torneos ganados</li>
                        <li>Rol exclusivo en Discord</li>
                        <li>Recolor de avatares</li>
                        <li>Marcos y fondos exclusivos</li>
                    </ul>
                    @auth
                        <div class="mb-3 p-2 rounded" style="background-color: #3e3e3e;">
                            <h5 class="text-info">Mensual</h5>
                            <h4>2€/mes</h4>
                            <div id="paypal-button-container-nivel1-mensual"></div>
                        </div>
                        <div class="mb-3 p-2 rounded" style="background-color: #2a2a2a;">
                            <h5 class="text-warning">Anual</h5>
                            <h4>22€/año</h4>
                            <div id="paypal-button-container-nivel1-anual"></div>
                        </div>
                    @else
                        <p class="text-warning">Inicia sesión para suscribirte</p>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Nivel 2 -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-light h-100 shadow-sm" style="border-radius: 10px;">
                <div class="card-body">
                    <h2 class="card-title" style="color: #c0e5fb;">Nivel 2</h2>
                    <ul class="list-unstyled mt-3">
                        <li>Todo lo del Nivel 1</li>
                        <li>Nombre destacado en color plata azulado</li>
                        <li>Subtítulo personal (15 opciones)</li>
                        <li>Copas de torneos ganados y especiales</li>
                        <li>Prioridad en la revisión de un torneo</li>
                        <li>Chat privado en Discord</li>
                        <li>Emote para Discord/Twitch</li>
                    </ul>
                    @auth
                        <div class="mb-3 p-2 rounded" style="background-color: #3e3e3e;">
                            <h5 class="text-info">Mensual</h5>
                            <h4>3.5€/mes</h4>
                            <div id="paypal-button-container-nivel2-mensual"></div>
                        </div>
                        <div class="mb-3 p-2 rounded" style="background-color: #2a2a2a;">
                            <h5 class="text-warning">Anual</h5>
                            <h4>36.5€/año</h4>
                            <div id="paypal-button-container-nivel2-anual"></div>
                        </div>
                    @else
                        <p class="text-warning">Inicia sesión para suscribirte</p>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Nivel 3 -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-light h-100 shadow-sm" style="border-radius: 10px;">
                <div class="card-body">
                    <h2 class="card-title" style="color: gold;">Nivel 3</h2>
                    <ul class="list-unstyled mt-3">
                        <li>Todo lo del Nivel 2</li>
                        <li>Nombre destacado en color oro</li>
                        <li>Subtítulo personal abierto</li>
                        <li>Prioridad en la revisión de dos torneos</li>
                        <li>Invitación a una BeyTalk</li>
                        <li>Avatar, marco y perfil personalizado</li>
                    </ul>
                    @auth
                        <div class="mb-3 p-2 rounded" style="background-color: #3e3e3e;">
                            <h5 class="text-info">Mensual</h5>
                            <h4>5€/mes</h4>
                            <div id="paypal-button-container-nivel3-mensual"></div>
                        </div>
                        <div class="mb-3 p-2 rounded" style="background-color: #2a2a2a;">
                            <h5 class="text-warning">Anual</h5>
                            <h4>50€/año</h4>
                            <div id="paypal-button-container-nivel3-anual"></div>
                        </div>
                    @else
                        <p class="text-warning">Inicia sesión para suscribirte</p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://www.paypal.com/sdk/js?client-id=AXFtujy1wQ4rl9kZOgurijAaKuzWylXGlxOYzB0L0Rm7o6JEGvqiEJvceYwoYI5sufboxE0f96CFVAb0&vault=true&intent=subscription"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    @auth
    const buttons = [
        { id: 'nivel1-mensual', plan: 'P-1X657411GU0088618NC77KEI', label: 'Nivel 1 mensual' },
        { id: 'nivel1-anual', plan: 'P-2RB08490DN179502MNC77BIY', label: 'Nivel 1 anual' },
        { id: 'nivel2-mensual', plan: 'P-2S41183369023920GNDAAAZY', label: 'Nivel 2 mensual' },
        { id: 'nivel2-anual', plan: 'P-6K592089KN690864BNDAAANA', label: 'Nivel 2 anual' },
        { id: 'nivel3-mensual', plan: 'P-389338823B569620NNDAABQA', label: 'Nivel 3 mensual' },
        { id: 'nivel3-anual', plan: 'P-44B29505HU436204HNDAABIA', label: 'Nivel 3 anual' },
    ];

    buttons.forEach(btn => {
        paypal.Buttons({
            style: { shape: 'pill', color: 'gold', layout: 'vertical', label: 'subscribe' },
            createSubscription: (data, actions) => actions.subscription.create({ plan_id: btn.plan }),
            onApprove: (data) => alert(btn.label + " suscrito: " + data.subscriptionID)
        }).render('#paypal-button-container-' + btn.id);
    });
    @endauth
});
</script>
@endsection
