@extends('layouts.app')

@section('title', 'Ranking Beyblade X')

@section('styles')
<style>
    /* Filtros Superiores */
    .filtros-box { background: var(--sbbl-blue-2); border: 3px solid #000; padding: 20px; box-shadow: 6px 6px 0px #000; transform: skewX(-2deg); border-radius: 0; margin-bottom: 2rem; }
    .filtros-box > div { transform: skewX(2deg); }
    .filtros-box label { color: var(--sbbl-gold) !important; font-family: 'Oswald', cursive; font-size: 1.1rem; letter-spacing: 1px;}
    .filtros-box select { border: 2px solid #000; border-radius: 0; font-weight: 900; background: #111 !important; color: #fff !important; cursor: pointer; }
    .filtros-box select:focus { border-color: var(--sbbl-gold); box-shadow: none; }
    .filtros-box select:disabled { opacity: 0.5; cursor: not-allowed; }

    /* Tabs de Temporadas */
    .nav-tabs { border-bottom: 4px solid #000; margin-bottom: 30px !important; gap: 5px; }
    .nav-tabs .nav-item { margin-bottom: -4px; }
    .nav-tabs .nav-link { background: #000; color: #fff; border: 3px solid #000; font-family: 'Oswald', cursive; font-size: 1.2rem; letter-spacing: 1px; border-radius: 0; transform: skewX(-5deg); transition: 0.2s; padding: 10px 20px; }
    .nav-tabs .nav-link:hover { background: var(--sbbl-blue-3); color: var(--sbbl-gold); }
    .nav-tabs .nav-link.active { background: var(--sbbl-gold); color: #000; border-bottom-color: var(--sbbl-gold); box-shadow: 4px -4px 0 var(--shonen-red); }

    /* --- ESTRUCTURA DE TABLA OPTIMIZADA SIN IMÁGENES --- */
    .item {
        display: grid;
        grid-template-columns: 50px 1.5fr 1fr 100px; /* Reajustado: 4 columnas */
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
    .item:hover:not(.encabezado) { transform: translate(-3px, -3px) skewX(-2deg); box-shadow: 6px 6px 0 rgba(0,0,0,0.8); border-color: var(--sbbl-gold); background: #000; }

    @media (max-width: 576px) {
        /* En móvil ocultamos la ubicación (columna 3) para dar más espacio al nombre */
        .item { grid-template-columns: 40px 1fr 85px; font-size: 0.9rem; gap: 10px; padding: 10px; }
        .item > span:nth-child(3), .encabezado > span:nth-child(3) { display: none; }
    }

    .encabezado { background: var(--sbbl-blue-1) !important; border: 3px solid #000 !important; color: var(--sbbl-gold) !important; font-family: 'Oswald', cursive; font-size: 1.2rem; letter-spacing: 2px; box-shadow: 4px 4px 0 #000; margin-bottom: 20px !important; }
    .posicion { font-family: 'Oswald', cursive; font-size: 1.6rem; text-align: center; color: #fff; text-shadow: 2px 2px 0 #000; }
    .resaltado .posicion { color: var(--sbbl-gold); font-size: 2rem; }
    .text-dim { color: #fff; font-weight: 800; font-size: 0.85em; text-transform: uppercase; opacity: 0.8; }
    .item .text-end { font-family: 'Oswald', cursive; font-size: 1.5rem; letter-spacing: 1px; color: #fff; text-shadow: 1px 1px 0 #000; }

    /* --- DIVISIONES --- */
    .division-block { margin-bottom: 60px; padding: 25px; border: 4px solid #000; box-shadow: 10px 10px 0 rgba(0,0,0,0.5); position: relative; }
    .division-title { font-family: 'Oswald', cursive; font-size: 2.8rem; text-align: center; letter-spacing: 2px; margin-bottom: 30px; text-shadow: 3px 3px 0 #000; background: #000; display: inline-block; padding: 5px 30px; border: 3px solid currentColor; transform: translateX(-50%) skewX(-10deg); position: relative; left: 50%; box-shadow: 5px 5px 0 #000; }
    .division-title span { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 0.6em; margin-left: 10px; color: #fff !important; text-shadow: none; vertical-align: middle; }

    .division-xtreme { border-color: #b400ff; background: rgba(180, 0, 255, 0.05); } .division-xtreme .division-title { color: #e4b3ff; border-color: #b400ff; }
    .division-maestro { border-color: #dd0800; background: rgba(221, 8, 0, 0.05); } .division-maestro .division-title { color: #ff4d4d; border-color: #dd0800; }
    .division-platino { border-color: #00ffcc; background: rgba(0, 255, 204, 0.05); } .division-platino .division-title { color: #00ffcc; border-color: #00ffcc; }
    .division-oro { border-color: var(--sbbl-gold); background: rgba(255, 193, 7, 0.05); } .division-oro .division-title { color: var(--sbbl-gold); border-color: var(--sbbl-gold); }
    .division-plata { border-color: #e2e8f0; background: rgba(226, 232, 240, 0.05); } .division-plata .division-title { color: #fff; border-color: #e2e8f0; }
    .division-bronce { border-color: #ff9d47; background: rgba(255, 157, 71, 0.05); } .division-bronce .division-title { color: #ff9d47; border-color: #ff9d47; }
    .resaltado { border-width: 3px !important; background: rgba(255,255,255,0.1) !important; box-shadow: inset 0 0 15px rgba(255,255,255,0.1); }
</style>
@endsection

@section('content')
@php
    function divisionBX2($points) {
        if ($points >= 75) return 'Xtreme';
        if ($points >= 66) return 'Maestro';
        if ($points >= 52) return 'Platino';
        if ($points >= 35) return 'Oro';
        if ($points >= 21) return 'Plata';
        return 'Bronce';
    }

    function rangoPuntos($division) {$rangos = [
            'Bronce'  => '0 - 20',
            'Plata'   => '21 - 34',
            'Oro'     => '35 - 51',
            'Platino' => '52 - 65',
            'Maestro' => '66 - 74',
            'Xtreme'  => '≥75',
        ];
        return $rangos[$division] ?? '';
    }

    $divisionOrder = ['Xtreme', 'Maestro', 'Platino', 'Oro', 'Plata', 'Bronce'];
@endphp

<div class="container py-4">
    {{-- Filtros Superiores Tácticos --}}
    <div class="filtros-box mx-auto" style="max-width: 900px;">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="mb-1"><i class="fas fa-list-ol me-1"></i> MOSTRAR RESULTADOS:</label>
                <select id="limitSelect" class="form-select form-select-lg" onchange="applyFilters()">
                    @foreach([25,50,200,500,1000,'all'] as $val)
                        <option value="{{ $val }}" {{ $limit ==$val ? 'selected' : '' }}>
                            {{ $val === 'all' ? 'TODOS LOS BLADERS' : 'TOP ' .$val }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="mb-1"><i class="fas fa-globe-europe me-1"></i> REGIÓN:</label>
                <select id="regionSelect" class="form-select form-select-lg" onchange="updateProvincesAndFilter()">
                    <option value="">TODAS LAS REGIONES</option>
                    @foreach ($regionsWithProvinces as$regionOption)
                        <option value="{{ $regionOption->name }}" data-provinces='{{ json_encode($regionOption->provinces) }}' {{ $region ==$regionOption->name ? 'selected' : '' }}>
                            {{ mb_strtoupper($regionOption->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="mb-1"><i class="fas fa-map-marker-alt me-1"></i> PROVINCIA:</label>
                <select id="provinceSelect" class="form-select form-select-lg" onchange="applyFilters()" {{ empty($region) ? 'disabled' : '' }}>
                    <option value="">TODAS LAS PROVINCIAS</option>
                    <input type="hidden" id="oldProvince" value="{{ $province }}">
                </select>
            </div>
        </div>
    </div>

    {{-- Pestañas de Temporada --}}
    <ul class="nav nav-tabs justify-content-center">
        @php
            $tabs = [
                'points' => 'Burst S1', 'points_s2' => 'Burst S2', 'points_s3' => 'Burst S3',
                'points_x1' => 'B.X Season 1', 'points_x2' => 'B.X Season 2', 'points_x3' => 'B.X Season 3'
            ];
        @endphp
        @foreach ($tabs as $key =>$label)
            <li class="nav-item">
                <button class="nav-link {{ $loop->last ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#{{ $key }}">
                    {{ mb_strtoupper($label) }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content pt-4">
        @foreach ($tabs as $key =>$label)
            <div class="tab-pane fade {{ $loop->last ? 'show active' : '' }}" id="{{ $key }}">

            @if ($key === 'points_x3')
                @foreach ($divisionOrder as$divisionName)
                    @php
                        $filtered = collect($bladers_points_x3 ?? [])
                            ->filter(fn($b) => divisionBX2($b->points_x3) ===$divisionName)
                            ->values();

                        if ($limit !== 'all') {$filtered = $filtered->take((int)$limit); }
                    @endphp

                    @if($filtered->count() > 0)
                        <div class="division-block division-{{ strtolower($divisionName) }}">
                            <h4 class="division-title">
                                {{ $divisionName }} <span>({{ rangoPuntos($divisionName) }} PTS)</span>
                            </h4>

                            <div class="item encabezado">
                                <span class="posicion text-white">#</span>
                                <span>BLADER</span>
                                <span>UBICACIÓN</span>
                                <span class="text-end">PUNTOS</span>
                            </div>

                            @foreach ($filtered as $index =>$blader)
                                <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                                    <span class="posicion">{{ $index + 1 }}</span>
                                    <span class="fw-bold text-white text-truncate fs-5">{{ optional($blader->user)->name ?? 'Usuario Eliminado' }}</span>
                                    <span class="text-dim text-truncate">
                                        {{ optional($blader->region)->name ?? '---' }}
                                        @if(optional($blader->province)->name)
                                            <br><small style="color:var(--shonen-cyan);">{{ $blader->province->name }}</small>
                                        @endif
                                    </span>
                                    <span class="text-end" style="color: var(--sbbl-gold);">{{ $blader->points_x3 }} <small>PTS</small></span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach

            @else
                <div class="division-block border-secondary" style="background: rgba(0,0,0,0.3);">
                    <div class="item encabezado">
                        <span class="posicion text-white">#</span>
                        <span>BLADER</span>
                        <span>UBICACIÓN</span>
                        <span class="text-end">PUNTOS</span>
                    </div>

                    @foreach (${'bladers_' .$key} ?? [] as $index =>$blader)
                        @if($limit === 'all' || $index < (int)$limit)
                        <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span class="fw-bold text-white text-truncate fs-5">{{ optional($blader->user)->name ?? 'Usuario Eliminado' }}</span>
                            <span class="text-dim text-truncate">
                                {{ optional($blader->region)->name ?? '---' }}
                                @if(optional($blader->province)->name)
                                    <br><small style="color:var(--shonen-cyan);">{{ $blader->province->name }}</small>
                                @endif
                            </span>
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
    document.addEventListener("DOMContentLoaded", function() {
        if(document.getElementById('regionSelect').value !== "") {
            loadProvinces();
        }
    });

    function loadProvinces() {
        const regionSelect = document.getElementById('regionSelect');
        const provinceSelect = document.getElementById('provinceSelect');
        const oldProvince = document.getElementById('oldProvince').value;
        const selectedOption = regionSelect.options[regionSelect.selectedIndex];

        provinceSelect.innerHTML = '<option value="">TODAS LAS PROVINCIAS</option>';

        if (selectedOption.value) {
            provinceSelect.disabled = false;
            try {
                const provinces = JSON.parse(selectedOption.dataset.provinces);
                provinces.forEach(p => {
                    const isSelected = (oldProvince == p.id) ? 'selected' : '';
                    provinceSelect.innerHTML += `<option value="${p.id}" ${isSelected}>${p.name.toUpperCase()}</option>`;
                });
            } catch(e) { console.error("Error leyendo JSON de provincias"); }
        } else {
            provinceSelect.disabled = true;
        }
    }

    function updateProvincesAndFilter() {
        document.getElementById('oldProvince').value = '';
        loadProvinces();
        applyFilters();
    }

    function applyFilters() {
        const limit = document.getElementById('limitSelect').value;
        const region = document.getElementById('regionSelect').value;
        const province = document.getElementById('provinceSelect').value;

        let url = `?limit=${limit}`;
        if (region) url += `&region=${encodeURIComponent(region)}`;
        if (province) url += `&province=${province}`;

        window.location.href = url;
    }
</script>
@endsection
