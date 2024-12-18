@extends('layouts.app')

@section('content')

<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color: white">Bladers</h2>
    <div>
        <div class="row">
            @foreach ($bladers as $blader)
                @php
                    // Determinar la clase CSS según el nivel de suscripción
                    $firstTrophyName = $blader->trophies->first()->name ?? '';
                    switch ($firstTrophyName) {
                        case 'SUSCRIPCIÓN NIVEL 3':
                            $subscriptionClass = 'suscripcion-nivel-3';
                            break;
                        case 'SUSCRIPCIÓN NIVEL 2':
                        case 'SUSCRIPCIÓN NIVEL 1':
                            $subscriptionClass = 'suscripcion';
                            break;
                        default:
                            $subscriptionClass = '';
                            break;
                    }

                @endphp

                <div class="tarjeta box-{{ $subscriptionClass }}"
                     style="background-image: url('/storage/{{ ($blader->fondo) ? $blader->fondo : "upload-profiles/Fondos/SBBLFondo.png" }}');
                            background-size: cover;
                            background-repeat: repeat;
                            background-position: center;
                            color: white;">
                    <div style="position: relative;" class="mr-3">
                        @if ($blader->imagen)
                            <img src="/storage/{{ $blader->imagen }}" class="rounded-circle" width="100" style="top: 0; left: 0; {{ strpos($blader->imagen, '.gif') !== false ? 'padding: 20px;' : '' }}">
                        @else
                            <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="top: 0; left: 0; padding:10x">
                        @endif

                        @if ($blader->marco)
                            <img src="/storage/{{ $blader->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 0;">
                        @else
                            <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 0;">
                        @endif
                    </div>
                    <div class="info">
                        <!-- Aplicar el color según el nivel de suscripción -->
                        <div class="nombre {{ $subscriptionClass }}">{{ $blader->user->name }}</div>
                        <div class="subtitulo">{{ $blader->subtitulo }}</div>
                        <div class="region">
                            {{ ($blader->region) ? $blader->region->name : 'No definida' }}
                            @if (in_array($blader->subscription_class, ['suscripcion-nivel-1', 'suscripcion-nivel-2', 'suscripcion-nivel-3']) && $blader->trophies_count > 0)
                                <span style="font-size: 16px;">
                                    {{ $blader->trophies_count }}x
                                    <i class="fas fa-trophy" style="color: gold;"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<style>
    .tarjeta {
        display: flex;
        align-items: center;
        width: 350px;
        border-radius: 10px;
        padding: 20px;
        margin: 10px auto;
        color: white;
        position: relative; /* Para que el pseudo-elemento se posicione correctamente */
        overflow: hidden; /* Para evitar que el pseudo-elemento sobresalga */
    }

    .tarjeta::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2); /* Color negro semitransparente (opacidad 50%) */
        z-index: 0; /* Asegura que la superposición quede detrás del contenido */
    }

    .tarjeta .info,
    .tarjeta .nombre,
    .tarjeta .region,
    .tarjeta .subtitulo {
        position: relative; /* Para que se mantengan por encima de la superposición */
        z-index: 1;
    }


    .nombre {
        font-size: 24px;
        font-weight: bold;
    }

    .region {
        font-size: 16px;
    }

    /* Clases de color según el nivel de suscripción */
    .suscripcion-nivel-3 {
        color: gold;
    }

    .suscripcion-nivel-2 {
        color: #c0e5fb;
    }

    .suscripcion-nivel-1 {
        color: #CD7F32; /* Bronce */
    }
    .box-suscripcion-nivel-3 {
    border: 3px solid gold;
    box-shadow: 0 0 10px gold;
    }

    .box-suscripcion-nivel-2 {
        border: 3px solid #c0e5fb;
        box-shadow: 0 0 10px #c0e5fb;
    }

    .box-suscripcion-nivel-1 {
        border: 3px solid #CD7F32;
        box-shadow: 0 0 10px #CD7F32;
    }
    .subtitulo {
        font-size: 1rem; /* Tamaño del texto */
        font-weight: bolder; /* Peso medio para un look de subtítulo */
        font-style: italic; /* Estilo cursivo para diferenciarlo */
        text-transform: uppercase; /* Primera letra de cada palabra en mayúscula */
        padding-bottom: 5px; /* Espacio entre texto y línea */
    }
        .region span {
        display: flex;
        align-items: center;
        font-weight: bold;
        /*color: gold;  Color dorado */
    }

</style>
@endsection
