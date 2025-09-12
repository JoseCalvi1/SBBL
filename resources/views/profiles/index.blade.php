@extends('layouts.app')

@section('title', 'Bladers SBBL')

@section('content')

<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color: white">Bladers ({{ count($bladers) }})</h2>
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

            @if ($bladers->lastPage() > 1)
                <nav class="d-flex justify-content-center my-3">
                    <ul class="pagination pagination-sm">
                        {{-- Previous --}}
                        <li class="page-item {{ $bladers->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $bladers->previousPageUrl() }}" aria-label="Anterior">&laquo;</a>
                        </li>

                        {{-- Páginas --}}
                        @for ($i = 1; $i <= $bladers->lastPage(); $i++)
                            <li class="page-item {{ $bladers->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $bladers->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        {{-- Next --}}
                        <li class="page-item {{ $bladers->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $bladers->nextPageUrl() }}" aria-label="Siguiente">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            @endif


            @foreach ($bladers->unique('id') as $blader)
                @php
                    // 1️⃣ Prioridad: suscripción activa
                    $subscriptionClass = '';
                    if ($blader->user->activeSubscription) {
                        $level = $blader->user->activeSubscription->plan->slug; // asumiendo que los slugs son '1', '2', '3'
                        switch ($level) {
                            case 'oro':
                                $subscriptionClass = 'suscripcion-nivel-3';
                                break;
                            case 'plata':
                                $subscriptionClass = 'suscripcion-nivel-2';
                                break;
                            case 'bronce':
                                $subscriptionClass = 'suscripcion-nivel-1';
                                break;
                        }
                    }

                    // 2️⃣ Si no hay suscripción activa, recurrir al trofeo de suscripción (lógica antigua)
                    if (!$subscriptionClass) {
                        $subscriptionTrophy = $blader->trophies->first(function ($trophy) {
                            return stripos($trophy->name, 'SUSCRIPCIÓN') !== false;
                        });

                        if ($subscriptionTrophy) {
                            if (stripos($subscriptionTrophy->name, 'NIVEL 3') !== false) {
                                $subscriptionClass = 'suscripcion-nivel-3';
                            } elseif (stripos($subscriptionTrophy->name, 'NIVEL 2') !== false) {
                                $subscriptionClass = 'suscripcion-nivel-2';
                            } elseif (stripos($subscriptionTrophy->name, 'NIVEL 1') !== false) {
                                $subscriptionClass = 'suscripcion-nivel-1';
                            }
                        }
                    }

                    // Copas especiales
                    $hasGranCopaHalloween = $blader->trophies->contains('name', 'Gran Copa Let It R.I.P.');
                    $hasGranCopaSantaKlaw = $blader->trophies->contains('name', 'Gran Copa Santa Klaw'); // Ejemplo
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
                            @if (1==2 && in_array($subscriptionClass, ['suscripcion-nivel-1', 'suscripcion-nivel-2', 'suscripcion-nivel-3']) && $blader->trophies_count > 0)
                                <span style="font-size: 16px;">
                                    {{ $blader->trophies_count }}x
                                    <i class="fas fa-trophy" style="color: gold;"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    {{-- 🏆 Iconos de copas especiales abajo a la derecha --}}
                    <div style="position: absolute; bottom: 5px; right: 15px; font-size: 20px;">
                        @if ($hasGranCopaHalloween)
                            <i class="fas fa-ghost" style="color: orange; margin-left: 4px;"
                            title="Gran Copa Let It R.I.P."></i>
                        @endif

                        @if ($hasGranCopaSantaKlaw)
                            <i class="fas fa-snowflake" style="color: lightblue; margin-left: 4px;"
                            title="Gran Copa Santa Klaw"></i>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($bladers->lastPage() > 1)
                <nav class="d-flex justify-content-center my-3">
                    <ul class="pagination pagination-sm">
                        {{-- Previous --}}
                        <li class="page-item {{ $bladers->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $bladers->previousPageUrl() }}" aria-label="Anterior">&laquo;</a>
                        </li>

                        {{-- Páginas --}}
                        @for ($i = 1; $i <= $bladers->lastPage(); $i++)
                            <li class="page-item {{ $bladers->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $bladers->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        {{-- Next --}}
                        <li class="page-item {{ $bladers->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $bladers->nextPageUrl() }}" aria-label="Siguiente">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            @endif

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
