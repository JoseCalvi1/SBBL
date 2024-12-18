@extends('layouts.app') <!-- Cambia esto según tu layout -->

@section('content')
<div class="container my-5" style="border-radius: 8px; padding: 20px; color: #ffffff;">
    <h1 class="text-center mb-4" style="color: #f8f9fa;">Suscripciones</h1>
    <div class="row text-center">
        <!-- Nivel 1 -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-light h-100 shadow-sm" style="border-radius: 10px;">
                <div class="card-body">
                    <h2 class="card-title" style="color: #cd7f32;">Nivel 1</h2>
                    <h4>15€/año</h4>
                    <ul class="list-unstyled mt-3">
                        <li>Nombre destacado en color bronce</li>
                        <li>Subtítulo personal (10 opciones)</li>
                        <li>Copas de torneos ganados</li>
                        <li>Rol exclusivo en Discord</li>
                        <li>Recolor de avatares</li>
                        <li>Marcos y fondos exclusivos</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Nivel 2 -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-light h-100 shadow-sm" style="border-radius: 10px;">
                <div class="card-body">
                    <h2 class="card-title" style="color: #c0e5fb;">Nivel 2</h2>
                    <h4>25€/año</h4>
                    <ul class="list-unstyled mt-3">
                        <li>Todo lo del Nivel 1</li>
                        <li>Nombre destacado en color plata azulado</li>
                        <li>Subtítulo personal (15 opciones)</li>
                        <li>Copas de torneos ganados y especiales</li>
                        <li>Prioridad en la revisión de un torneo</li>
                        <li>Chat privado en Discord</li>
                        <li>Emote para Discord/Twitch</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Nivel 3 -->
        <div class="col-md-4 mb-4">
            <div class="card bg-dark text-light h-100 shadow-sm" style="border-radius: 10px;">
                <div class="card-body">
                    <h2 class="card-title" style="color: gold;">Nivel 3</h2>
                    <h4>50€/año</h4>
                    <ul class="list-unstyled mt-3">
                        <li>Todo lo del Nivel 2</li>
                        <li>Nombre destacado en color oro</li>
                        <li>Subtítulo personal abierto</li>
                        <li>Prioridad en la revisión de dos torneos</li>
                        <li>Invitación a una BeyTalk</li>
                        <li>Avatar, marco y perfil personalizado</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <p>La suscripciones estarán disponibles a partir del día 1 de enero</p>
        <!--<p>Haz el envío poniendo tu nombre en la web y el nivel de suscripción que solicitas (Ej: JoseCalvi1 Nivel 3)</p>
        <a href="https://www.paypal.com/paypalme/sbbloficial" target="_blank" class="btn btn-primary btn-lg" style="background-color: #1e1e2f; border: none;">
            Suscribirse ahora
        </a>-->
    </div>
</div>
@endsection
