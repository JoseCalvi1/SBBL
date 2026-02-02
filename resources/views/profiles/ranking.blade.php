@extends('layouts.app')

@section('title', 'Ranking Beyblade X')

@section('styles')
<style>
    /* --- AJUSTES GENERALES --- */
    body { background:#121212; color:#e0e0e0 !important; }

    /* Tabs */
    .nav-tabs { border-bottom: 1px solid #444; }
    .nav-tabs .nav-link { background:rgba(255,255,255,0.05); color:#aaa; border:none; margin-right: 2px; }
    .nav-tabs .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .nav-tabs .nav-link.active { background:#ffca28; color:#121212; font-weight:bold; }

    /* --- ESTRUCTURA DE TABLA (SOLUCIÓN AL TEXTO JUNTO) --- */
    .clasificacion {
        padding: 15px;
        border-radius: 10px;
    }

    /* Convertimos las filas en una GRID para que no se monten */
    .item {
        display: grid;
        /* Definición de columnas:
           1. Ranking (pequeño)
           2. Avatar (fijo)
           3. Nombre (flexible)
           4. Región (flexible)
           5. Puntos (fijo derecha)
        */
        grid-template-columns: 50px 70px 1.5fr 1fr 80px;
        align-items: center;
        gap: 15px;
        padding: 12px;
        margin-bottom: 8px;
        border-radius: 6px;
    }

    /* Ajuste para móvil: Ocultamos región y ajustamos tamaños */
    @media (max-width: 576px) {
        .item {
            grid-template-columns: 40px 60px 1fr 70px;
            font-size: 0.9rem;
            gap: 10px;
        }
        /* Ocultar la columna de región (la 4ª columna) en móvil */
        .item > span:nth-child(4) { display: none; }
        /* En el encabezado también */
        .encabezado > span:nth-child(4) { display: none; }
    }

    /* Estilos de texto */
    .encabezado {
        font-weight: bold;
        color: #ffca28; /* Color dorado para títulos */
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 1px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 15px !important;
    }

    .posicion { text-align: center; font-weight: bold; color: #888; }
    .resaltado .posicion { color: #fff; text-shadow: 0 0 5px rgba(255,255,255,0.5); }

    /* Avatares */
    .profile-container { width: 50px; height: 50px; position: relative; margin: 0 auto; }
    .blader-image, .blader-frame { width: 100%; height: 100%; position: absolute; top: 0; left: 0; border-radius: 50%; object-fit: cover; }
    .blader-frame { z-index: 2; transform: scale(1.1); } /* El marco un poco más grande */

    /* Textos más legibles (Reemplaza al text-muted) */
    .text-dim { color: #bbb; font-size: 0.9em; }

    /* --- TUS COLORES DE DIVISIÓN ORIGINALES --- */
    .division-block { margin-bottom: 40px; border-radius: 12px; padding: 15px; }
    .division-title { text-align: center; font-weight: bold; letter-spacing: 2px; margin-bottom: 15px; }

    /* XTREME (Tu código original) */
    .division-xtreme {
        position: relative;
        background: linear-gradient(135deg, rgba(104, 0, 165, .35), rgba(180, 0, 255, .18));
        border-radius: 14px;
        box-shadow: 0 0 25px rgba(104, 0, 165, .45), inset 0 0 30px rgba(180, 0, 255, .15);
        overflow: hidden;
    }
    .division-xtreme::before {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(120deg, transparent 30%, rgba(195, 0, 255, .35), transparent 70%);
        animation: xtremeGlow 6s linear infinite; pointer-events: none;
    }
    .division-xtreme .division-title {
        color: #e4b3ff; text-shadow: 0 0 8px rgba(195, 0, 255, .8), 0 0 18px rgba(104, 0, 165, .9); letter-spacing: 4px;
    }
    .division-xtreme .item {
        background: linear-gradient(90deg, rgba(104, 0, 165, .35), rgba(180, 0, 255, .18));
        border: 1px solid rgba(195, 0, 255, .55); box-shadow: 0 0 12px rgba(104, 0, 165, .4);
    }
    .division-xtreme .item.resaltado {
        box-shadow: 0 0 18px rgba(195, 0, 255, .85), inset 0 0 12px rgba(255, 255, 255, .15);
    }
    @keyframes xtremeGlow { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    /* RESTO DE DIVISIONES (Tus colores originales) */
    .division-maestro { background: rgba(221, 8, 0, .18); }
    .division-maestro .division-title { color: #dd0800; }
    .division-maestro .item { background: rgba(221, 8, 0, .12); border: 1px solid rgba(221, 8, 0, .35); }

    .division-platino { background: rgba(0, 153, 127, .18); }
    .division-platino .division-title { color: #00997f; }
    .division-platino .item { background: rgba(0, 153, 127, .12); border: 1px solid rgba(0, 153, 127, .35); }

    .division-oro { background: rgba(221, 179, 0, .18); }
    .division-oro .division-title { color: #ddb300; }
    .division-oro .item { background: rgba(221, 179, 0, .12); border: 1px solid rgba(221, 179, 0, .35); }

    .division-plata { background: rgba(162, 168, 192, .18); }
    .division-plata .division-title { color: #a2a8c0; }
    .division-plata .item { background: rgba(162, 168, 192, .12); border: 1px solid rgba(162, 168, 192, .35); }

    .division-bronce { background: rgba(177, 86, 15, .18); }
    .division-bronce .division-title { color: #b1560f; }
    .division-bronce .item { background: rgba(177, 86, 15, .12); border: 1px solid rgba(177, 86, 15, .35); }

    /* Fallback para items sin división específica */
    .item-default { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
</style>
@endsection

@section('content')
@php
    function divisionBX2($points) {
        if ($points >= 95) return 'Xtreme';
        if ($points >= 79) return 'Maestro';
        if ($points >= 59) return 'Platino';
        if ($points >= 39) return 'Oro';
        if ($points >= 23) return 'Plata';
        return 'Bronce';
    }

    function rangoPuntos($division) {
        $rangos = [
            'Bronce'  => '0 - 22',
            'Plata'   => '23 - 38',
            'Oro'     => '39 - 58',
            'Platino' => '59 - 78',
            'Maestro' => '79 - 94',
            'Xtreme'  => '≥95',
        ];
        return $rangos[$division] ?? '';
    }

    $divisionOrder = ['Xtreme', 'Maestro', 'Platino', 'Oro', 'Plata', 'Bronce'];
    $filterRegion = request('region', '');
    $limit = request('limit', 'all');
@endphp

<div class="container py-4">

    <div class="row justify-content-center mb-5">
        <div class="col-md-5 mb-3 mb-md-0">
            <label class="text-white small text-uppercase mb-1 fw-bold">Mostrar:</label>
            <select id="limitSelect" class="form-control bg-dark text-white border-secondary" onchange="applyFilters()">
                @foreach([25,50,200,500,1000,'all'] as $val)
                    <option value="{{ $val }}" {{ $limit == $val ? 'selected' : '' }}>
                        {{ $val == 'all' ? 'Todos' : $val }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <label class="text-white small text-uppercase mb-1 fw-bold">Filtrar por región:</label>
            <select id="regionSelect" class="form-control bg-dark text-white border-secondary" onchange="applyFilters()">
                <option value="">Todas las regiones</option>
                @foreach ($regions as $regionOption)
                    <option value="{{ $regionOption }}" {{ $filterRegion == $regionOption ? 'selected' : '' }}>
                        {{ $regionOption }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <ul class="nav nav-tabs justify-content-center border-0 mb-4">
        @php
            $tabs = [
                'points' => 'Burst S1',
                'points_s2' => 'Burst S2',
                'points_s3' => 'Burst S3',
                'points_x1' => 'Beyblade X S1',
                'points_x2' => 'Beyblade X S2'
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <button class="nav-link px-4 py-2 {{ $loop->last ? 'active' : '' }}"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $key }}">
                    {{ $label }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach ($tabs as $key => $label)
            <div class="tab-pane fade {{ $loop->last ? 'show active' : '' }}" id="{{ $key }}">

            {{-- BEYBLADE X SEASON 2 --}}
            @if ($key === 'points_x2')

                @foreach ($divisionOrder as $divisionName)
                    @php
                        $filtered = collect($bladers_points_x2 ?? [])
                            ->filter(fn($b) => divisionBX2($b->points_x2) === $divisionName)
                            ->when($filterRegion, fn($c) => $c->filter(fn($b) => optional($b->region)->name === $filterRegion))
                            ->values();

                        if ($limit != 'all') {
                            $filtered = $filtered->take((int) $limit);
                        }
                    @endphp

                    <div class="division-block division-{{ strtolower($divisionName) }}">
                        <h4 class="division-title">
                            {{ $divisionName }} <span style="opacity: 0.6; font-size: 0.8em">({{ rangoPuntos($divisionName) }} pts)</span>
                        </h4>

                        <div class="clasificacion">
                            {{-- HEADER (Grid) --}}
                            <div class="item encabezado">
                                <span class="posicion">#</span>
                                <span>Avatar</span>
                                <span>Blader</span>
                                <span>Región</span>
                                <span class="text-end">Puntos</span>
                            </div>

                            @forelse ($filtered as $index => $blader)
                                {{-- ROW (Grid) --}}
                                <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                                    <span class="posicion">{{ $index + 1 }}</span>

                                    <div class="profile-container">
                                         @if($blader->avatar_url)
                                            <img src="{{ $blader->avatar_url }}" class="blader-image" loading="lazy">
                                            <img src="{{ $blader->marco_url }}" class="blader-frame" loading="lazy">
                                         @endif
                                    </div>

                                    <span class="fw-bold text-white text-truncate">{{ $blader->user->name }}</span>

                                    {{-- Usamos clase text-dim en vez de text-muted --}}
                                    <span class="text-dim text-truncate">{{ optional($blader->region)->name ?? '---' }}</span>

                                    <span class="text-end fw-bold text-white">{{ $blader->points_x2 }} pts</span>
                                </div>
                            @empty
                                <div class="text-center py-4 text-white-50">
                                    No hay jugadores en esta división
                                </div>
                            @endforelse
                        </div>
                    </div>

                @endforeach

            {{-- RESTO DE TEMPORADAS --}}
            @else
                <div class="clasificacion mb-4">
                    <div class="item encabezado">
                        <span class="posicion">#</span>
                        <span>Avatar</span>
                        <span>Blader</span>
                        <span>Región</span>
                        <span class="text-end">Puntos</span>
                    </div>

                    @foreach (${'bladers_' . $key} ?? [] as $index => $blader)
                         @if($limit == 'all' || $index < (int)$limit)
                        <div class="item item-default {{ $index < 3 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                             <div class="profile-container">
                                 @if(isset($blader->avatar_url))
                                    <img src="{{ $blader->avatar_url }}" class="blader-image" loading="lazy">
                                    <img src="{{ $blader->marco_url }}" class="blader-frame" loading="lazy">
                                 @endif
                            </div>
                            <span class="fw-bold text-white text-truncate">{{ $blader->user->name }}</span>
                            <span class="text-dim text-truncate">{{ $blader->region->name ?? '---' }}</span>
                            <span class="text-end fw-bold text-white">{{ $blader->{$key} }} pts</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            @endif

            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
function applyFilters() {
    const limit = document.getElementById('limitSelect').value;
    const region = document.getElementById('regionSelect').value;
    window.location.href = `?limit=${limit}&region=${region}`;
}
</script>
@endsection
