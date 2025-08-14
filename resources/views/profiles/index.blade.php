@extends('layouts.app')

@section('title', 'Bladers SBBL')

@section('content')

<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color: white">Bladers</h2>
    <div>
        <div class="row">
            <form method="GET" class="mb-4" style="display: flex; gap: 1rem; flex-wrap: wrap;">
    <div>
        <label for="region" style="color: white;">Región:</label>
        <select name="region" id="region" class="form-control">
            <option value="">Todas</option>
            @foreach ($regiones as $region)
                <option value="{{ $region->id }}" {{ request('region') == $region->id ? 'selected' : '' }}>
                    {{ $region->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="free_agent" style="color: white;">Buscando equipo:</label>
        <select name="free_agent" id="free_agent" class="form-control">
            <option value="">Todos</option>
            <option value="1" {{ request('free_agent') === '1' ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ request('free_agent') === '0' ? 'selected' : '' }}>No</option>
        </select>
    </div>

    <div style="align-self: end;">
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>

            @foreach ($bladers as $blader)
                @php
                    // Determinar la clase CSS según el nivel de suscripción
                    $firstTrophyName = $blader->trophies->first()->name ?? '';
                    switch ($firstTrophyName) {
                        case 'SUSCRIPCIÓN NIVEL 3':
                            $subscriptionClass = 'suscripcion-nivel-3';
                            break;
                        case 'SUSCRIPCIÓN NIVEL 2':
                            $subscriptionClass = 'suscripcion-nivel-2';
                            break;
                        case 'SUSCRIPCIÓN NIVEL 1':
                            $subscriptionClass = 'suscripcion-nivel-1';
                            break;
                        default:
                            $subscriptionClass = '';
                            break;
                    }
                @endphp

                <div class="tarjeta box-{{ $subscriptionClass }} {{ $blader->free_agent ? 'box-free-agent' : '' }}"
                    style="background-image: url('/storage/{{ ($blader->fondo) ? $blader->fondo : "upload-profiles/Fondos/SBBLFondo.png" }}');
                           background-size: cover;
                           background-repeat: repeat;
                           background-position: center;
                           color: white;">

                    @if ($blader->free_agent && Auth::user() && $equipo && Auth::user()->id != $blader->user_id)
                        <form method="POST" action="{{ route('equipos.sendInvitation', $equipo->id) }}" class="invitation-form">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $blader->id }}">
                            <div class="open-to-work-label hover-invitar" onclick="this.closest('form').submit();">Open to work</div>
                        </form>
                    @elseif ($blader->free_agent)
                        <div class="open-to-work-label">Open to work</div>
                    @endif

                    <div style="position: relative;" class="mr-3">
                        @if ($blader->imagen)
                            <img src="/storage/{{ $blader->imagen }}" class="rounded-circle" width="100" style="top: 0; left: 0; {{ strpos($blader->imagen, '.gif') !== false ? 'padding: 20px;' : '' }}">
                        @else
                            <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" style="top: 0; left: 0;">
                        @endif

                        @if ($blader->marco)
                            <img src="/storage/{{ $blader->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 0;">
                        @else
                            <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 0;">
                        @endif
                    </div>

                    <div class="info">
                        <div class="nombre {{ $subscriptionClass }}">{{ $blader->user->name }}</div>
                        <div class="subtitulo">{{ $blader->subtitulo }}</div>
                        <div class="region">
                            {{ ($blader->region) ? $blader->region->name : 'No definida' }}
                            @if (in_array($subscriptionClass, ['suscripcion-nivel-1', 'suscripcion-nivel-2', 'suscripcion-nivel-3']) && $blader->trophies_count > 0)
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
        position: relative;
        overflow: hidden;
    }

    .tarjeta::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.2);
        z-index: 0;
    }

    .tarjeta .info,
    .tarjeta .nombre,
    .tarjeta .region,
    .tarjeta .subtitulo {
        position: relative;
        z-index: 1;
    }

    .nombre {
        font-size: 24px;
        font-weight: bold;
    }

    .region {
        font-size: 16px;
    }

    .suscripcion-nivel-3 {
        color: gold;
    }

    .suscripcion-nivel-2 {
        color: #c0e5fb;
    }

    .suscripcion-nivel-1 {
        color: #CD7F32;
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
        font-size: 1rem;
        font-weight: bolder;
        font-style: italic;
        text-transform: uppercase;
        padding-bottom: 5px;
    }

    .region span {
        display: flex;
        align-items: center;
        font-weight: bold;
    }

    .box-free-agent {
        border: 3px solid #28a745;
        position: relative;
    }

    .open-to-work-label {
        position: absolute;
        bottom: 0px;
        right: 0px;
        background-color: #28a745;
        color: white;
        padding: 4px 10px;
        border-radius: 5px 0;
        font-weight: bold;
        font-size: 0.85rem;
        z-index: 2;
        text-transform: uppercase;
        cursor: default;
        transition: all 0.3s ease-in-out;
    }

    .hover-invitar {
        cursor: pointer;
    }

    .hover-invitar:hover {
        background-color: #ffc107 !important; /* amarillo */
        color: black;
    }

    .hover-invitar:hover {
        content: "Invitar";
    }

    .hover-invitar:hover {
        animation: none;
    }

    .hover-invitar:hover {
        /* Reemplazamos el texto usando JavaScript */
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = document.querySelectorAll('.hover-invitar');

        labels.forEach(label => {
            const originalText = label.innerText;

            label.addEventListener('mouseenter', function () {
                label.innerText = 'Invitar a equipo';
            });

            label.addEventListener('mouseleave', function () {
                label.innerText = originalText;
            });
        });
    });
</script>

@endsection
