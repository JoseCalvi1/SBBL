@extends('layouts.app')

@section('title', 'Ranking Beyblade X')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: RANKING & DIVISIONES (Hereda de layout)
       ==================================================================== */

    /* Filtros Superiores */
    .filtros-box {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        padding: 15px;
        box-shadow: 6px 6px 0px #000;
        transform: skewX(-2deg);
        border-radius: 0;
        margin-bottom: 2rem;
    }
    .filtros-box > div { transform: skewX(2deg); }
    .filtros-box label { color: var(--sbbl-gold) !important; font-family: 'Oswald', cursive; font-size: 1.2rem; letter-spacing: 1px;}
    .filtros-box select { border: 2px solid #000; border-radius: 0; font-weight: 900; background: #111 !important; color: #fff !important; }
    .filtros-box select:focus { border-color: var(--sbbl-gold); box-shadow: none; }

    /* Tabs de Temporadas */
    .nav-tabs { border-bottom: 4px solid #000; margin-bottom: 30px !important; gap: 5px; }
    .nav-tabs .nav-item { margin-bottom: -4px; }
    .nav-tabs .nav-link {
        background: #000; color: #fff; border: 3px solid #000;
        font-family: 'Oswald', cursive; font-size: 1.2rem; letter-spacing: 1px;
        border-radius: 0; transform: skewX(-5deg); transition: 0.2s;
        padding: 10px 20px;
    }
    .nav-tabs .nav-link:hover { background: var(--sbbl-blue-3); color: var(--sbbl-gold); }
    .nav-tabs .nav-link.active {
        background: var(--sbbl-gold); color: #000;
        border-bottom-color: var(--sbbl-gold);
        box-shadow: 4px -4px 0 var(--shonen-red);
    }

    /* --- ESTRUCTURA DE TABLA (GRID SHONEN) --- */
    .item {
        display: grid;
        grid-template-columns: 50px 70px 1.5fr 1fr 100px;
        align-items: center;
        gap: 15px;
        padding: 10px 15px;
        margin-bottom: 10px;
        background: rgba(0,0,0,0.6);
        border: 2px solid #000;
        transform: skewX(-2deg);
        transition: 0.2s;
    }
    .item > * { transform: skewX(2deg); }
    .item:hover:not(.encabezado) {
        transform: translate(-3px, -3px) skewX(-2deg);
        box-shadow: 6px 6px 0 rgba(0,0,0,0.8);
        border-color: var(--sbbl-gold);
        background: #000;
    }

    @media (max-width: 576px) {
        .item { grid-template-columns: 40px 60px 1fr 85px; font-size: 0.9rem; gap: 10px; padding: 10px; }
        .item > span:nth-child(4), .encabezado > span:nth-child(4) { display: none; }
    }

    .encabezado {
        background: var(--sbbl-blue-1) !important;
        border: 3px solid #000 !important;
        color: var(--sbbl-gold) !important;
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        letter-spacing: 2px;
        box-shadow: 4px 4px 0 #000;
        margin-bottom: 20px !important;
    }

    .posicion { font-family: 'Oswald', cursive; font-size: 1.6rem; text-align: center; color: #fff; text-shadow: 2px 2px 0 #000; }
    .resaltado .posicion { color: var(--sbbl-gold); font-size: 2rem; }

    .text-dim { color: #fff; font-weight: 800; font-size: 0.85em; text-transform: uppercase; opacity: 0.8; }
    .item .text-end { font-family: 'Oswald', cursive; font-size: 1.5rem; letter-spacing: 1px; color: #fff; text-shadow: 1px 1px 0 #000; }

    /* Avatares 100% Redondos (Corregido) */
    .profile-container { width: 55px; height: 55px; position: relative; margin: 0 auto; }
    .blader-image, .blader-frame { width: 100%; height: 100%; position: absolute; top: 0; left: 0; object-fit: cover; border-radius: 50%; }
    .blader-image { border: 2px solid var(--sbbl-gold); background: #000; }
    .blader-frame { z-index: 2; transform: scale(1.15); }

    /* --- DIVISIONES --- */
    .division-block { margin-bottom: 60px; padding: 25px; border: 4px solid #000; box-shadow: 10px 10px 0 rgba(0,0,0,0.5); position: relative; }

    .division-title {
        font-family: 'Oswald', cursive;
        font-size: 2.8rem;
        text-align: center;
        letter-spacing: 2px;
        margin-bottom: 30px;
        text-shadow: 3px 3px 0 #000;
        background: #000;
        display: inline-block;
        padding: 5px 30px;
        border: 3px solid currentColor;
        transform: translateX(-50%) skewX(-10deg);
        position: relative;
        left: 50%;
        box-shadow: 5px 5px 0 #000;
    }
    .division-title span { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.6em; margin-left: 10px; color: #fff !important; text-shadow: none; vertical-align: middle; }

    /* Estilos por colores de liga */
    .division-xtreme { border-color: #b400ff; background: rgba(180, 0, 255, 0.05); }
    .division-xtreme .division-title { color: #e4b3ff; border-color: #b400ff; }

    .division-maestro { border-color: #dd0800; background: rgba(221, 8, 0, 0.05); }
    .division-maestro .division-title { color: #ff4d4d; border-color: #dd0800; }

    .division-platino { border-color: #00ffcc; background: rgba(0, 255, 204, 0.05); }
    .division-platino .division-title { color: #00ffcc; border-color: #00ffcc; }

    .division-oro { border-color: var(--sbbl-gold); background: rgba(255, 193, 7, 0.05); }
    .division-oro .division-title { color: var(--sbbl-gold); border-color: var(--sbbl-gold); }

    .division-plata { border-color: #e2e8f0; background: rgba(226, 232, 240, 0.05); }
    .division-plata .division-title { color: #fff; border-color: #e2e8f0; }

    .division-bronce { border-color: #ff9d47; background: rgba(255, 157, 71, 0.05); }
    .division-bronce .division-title { color: #ff9d47; border-color: #ff9d47; }

    .resaltado { border-width: 3px !important; background: rgba(255,255,255,0.1) !important; box-shadow: inset 0 0 15px rgba(255,255,255,0.1); }
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

    {{-- Filtros Superiores --}}
    <div class="filtros-box mx-auto" style="max-width: 800px;">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <label class="mb-1">MOSTRAR RESULTADOS:</label>
                <select id="limitSelect" class="form-select" onchange="applyFilters()">
                    @foreach([25,50,200,500,1000,'all'] as $val)
                        <option value="{{ $val }}" {{ $limit == $val ? 'selected' : '' }}>
                            {{ $val == 'all' ? 'TODOS LOS BLADERS' : 'TOP ' . $val }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="mb-1">ZONA DE COMBATE:</label>
                <select id="regionSelect" class="form-select" onchange="applyFilters()">
                    <option value="">TODAS LAS REGIONES</option>
                    @foreach ($regions as $regionOption)
                        <option value="{{ $regionOption }}" {{ $filterRegion == $regionOption ? 'selected' : '' }}>
                            {{ $regionOption }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Pestañas de Temporada --}}
    <ul class="nav nav-tabs justify-content-center">
        @php
            $tabs = [
                'points' => 'Burst S1',
                'points_s2' => 'Burst S2',
                'points_s3' => 'Burst S3',
                'points_x1' => 'B.X Season 1',
                'points_x2' => 'B.X Season 2'
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <li class="nav-item">
                <button class="nav-link {{ $loop->last ? 'active' : '' }}"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $key }}">
                    {{ mb_strtoupper($label) }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content pt-4">
        @foreach ($tabs as $key => $label)
            <div class="tab-pane fade {{ $loop->last ? 'show active' : '' }}" id="{{ $key }}">

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
                            {{ $divisionName }} <span>({{ rangoPuntos($divisionName) }} PTS)</span>
                        </h4>

                        <div class="item encabezado">
                            <span class="posicion text-white">#</span>
                            <span class="text-center">TERMINAL</span>
                            <span>BLADER</span>
                            <span>REGIÓN</span>
                            <span class="text-end">PUNTOS</span>
                        </div>

                        @forelse ($filtered as $index => $blader)
                            <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                                <span class="posicion">{{ $index + 1 }}</span>
                                <div class="profile-container">
                                    <img src="{{ $blader->avatar_url }}" class="blader-image" loading="lazy">
                                    <img src="{{ $blader->marco_url }}" class="blader-frame" loading="lazy">
                                </div>
                                <span class="fw-bold text-white text-truncate fs-5">{{ $blader->user->name }}</span>
                                <span class="text-dim text-truncate">{{ optional($blader->region)->name ?? '---' }}</span>
                                <span class="text-end" style="color: var(--sbbl-gold);">{{ $blader->points_x2 }} <small>PTS</small></span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-white font-Oswald opacity-50" style="font-size: 1.5rem;">
                                NO SE DETECTAN BLADERS EN ESTA DIVISIÓN
                            </div>
                        @endforelse
                    </div>
                @endforeach

            @else
                <div class="division-block border-secondary" style="background: rgba(0,0,0,0.3);">
                    <div class="item encabezado">
                        <span class="posicion text-white">#</span>
                        <span class="text-center">TERMINAL</span>
                        <span>BLADER</span>
                        <span>REGIÓN</span>
                        <span class="text-end">PUNTOS</span>
                    </div>

                    @foreach (${'bladers_' . $key} ?? [] as $index => $blader)
                         @if($limit == 'all' || $index < (int)$limit)
                        <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                             <div class="profile-container">
                                <img src="{{ $blader->avatar_url }}" class="blader-image" loading="lazy">
                                <img src="{{ $blader->marco_url }}" class="blader-frame" loading="lazy">
                            </div>
                            <span class="fw-bold text-white text-truncate fs-5">{{ $blader->user->name }}</span>
                            <span class="text-dim text-truncate">{{ $blader->region->name ?? '---' }}</span>
                            <span class="text-end">{{ $blader->{$key} }} <small>PTS</small></span>
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
