@extends('layouts.app')

@section('title', 'Ranking Beyblade X')

@section('content')
@php
    // Divisiones Beyblade X S2
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

    // Obtener filtros actuales
    $filterRegion = request('region', '');
    $limit = request('limit', 'all');
@endphp

<div class="container">

    <!-- Filtros -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <label class="text-white">Mostrar:</label>
            <select id="limitSelect" class="form-control bg-dark text-white" onchange="applyFilters()">
                @foreach([25,50,200,500,1000,'all'] as $val)
                    <option value="{{ $val }}" {{ $limit == $val ? 'selected' : '' }}>
                        {{ $val == 'all' ? 'Todos' : $val }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="text-white">Filtrar por región:</label>
            <select id="regionSelect" class="form-control bg-dark text-white" onchange="applyFilters()">
                <option value="">Todas las regiones</option>
                @foreach ($regions as $regionOption)
                    <option value="{{ $regionOption }}" {{ $filterRegion == $regionOption ? 'selected' : '' }}>
                        {{ $regionOption }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
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
                <button class="nav-link {{ $loop->last ? 'active' : '' }}"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $key }}">
                    {{ $label }}
                </button>
            </li>
        @endforeach
    </ul>

    <!-- Contenido -->
    <div class="tab-content">
        @foreach ($tabs as $key => $label)
            <div class="tab-pane fade {{ $loop->last ? 'show active' : '' }}" id="{{ $key }}">

            {{-- BEYBLADE X SEASON 2 --}}
            @if ($key === 'points_x2')

                @foreach ($divisionOrder as $divisionName)

                    @php
                        $filtered = collect($bladers_points_x2 ?? [])
                            ->filter(function ($b) use ($divisionName) {
                                return divisionBX2($b->points_x2) === $divisionName;
                            })
                            ->when($filterRegion, function ($c) use ($filterRegion) {
                                return $c->filter(function ($b) use ($filterRegion) {
                                    return optional($b->region)->name === $filterRegion;
                                });
                            })
                            ->values();

                        if ($limit != 'all') {
                            $filtered = $filtered->take((int) $limit);
                        }
                    @endphp


                    <div class="division-block division-{{ strtolower($divisionName) }}">
                        <h4 class="division-title">
                            {{ $divisionName }} ({{ rangoPuntos($divisionName) }} pts)
                        </h4>

                        <div class="clasificacion">
                            <div class="item encabezado">
                                <span class="posicion">#</span>
                                <span></span>
                                <span>Blader</span>
                                <span>Región</span>
                                <span>Puntos</span>
                            </div>

                            @forelse ($filtered as $index => $blader)
                                <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                                    <span class="posicion">{{ $index + 1 }}</span>

                                    <span class="profile-container">
                                        <div class="blader-avatar d-none d-sm-block">
                                            <img src="/storage/{{ $blader->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}" class="blader-image">
                                            <img src="/storage/{{ $blader->marco ?? 'upload-profiles/Marcos/BaseBlue.png' }}" class="blader-frame">
                                        </div>
                                    </span>

                                    <span>{{ $blader->user->name }}</span>
                                    <span>{{ optional($blader->region)->name ?? 'Región desconocida' }}</span>
                                    <span>{{ $blader->points_x2 }} pts</span>
                                </div>
                            @empty
                                <div class="item">
                                    <span style="width:100%; text-align:center; opacity:.6">
                                        No hay jugadores en esta división
                                    </span>
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
                        <span>Blader</span>
                        <span>Región</span>
                        <span>Puntos</span>
                    </div>

                    @foreach (${'bladers_' . $key} ?? [] as $index => $blader)
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span>{{ $blader->user->name }}</span>
                            <span>{{ $blader->region->name ?? 'Región desconocida' }}</span>
                            <span>{{ $blader->{$key} }} pts</span>
                        </div>
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

@section('styles')
<style>
body { background:#121212; color:#e0e0e0 !important; }
.nav-tabs .nav-link { background:#333; color:#e0e0e0; border:none; }
.nav-tabs .nav-link.active { background:#ffca28; color:#121212; font-weight:bold; }

.clasificacion { padding:15px; border-radius:10px; }
.item { display:flex; align-items:center; justify-content:space-between; padding:10px; margin-bottom:8px; border-radius:6px; }
.encabezado { font-weight:bold; opacity:.85; }
.posicion { width:40px; text-align:center; font-weight:bold; }
.resaltado { box-shadow: 0 0 10px rgba(255,255,255,.05); }
.profile-container { width:50px; height:50px; }
.blader-avatar { position:relative; width:50px; height:50px; }
.blader-image, .blader-frame { width:50px; height:50px; border-radius:50%; position:absolute; }

/* Bloques por división */
.division-block { margin-bottom:40px; border-radius:12px; padding:15px; }
.division-title { text-align:center; font-weight:bold; letter-spacing:2px; margin-bottom:15px; }

/* ===== XTREME ===== */
.division-xtreme {
    position: relative;
    background:
        linear-gradient(
            135deg,
            rgba(104, 0, 165, .35),
            rgba(180, 0, 255, .18)
        );
    border-radius: 14px;
    box-shadow:
        0 0 25px rgba(104, 0, 165, .45),
        inset 0 0 30px rgba(180, 0, 255, .15);
    overflow: hidden;
}

/* Aura animada */
.division-xtreme::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            120deg,
            transparent 30%,
            rgba(195, 0, 255, .35),
            transparent 70%
        );
    animation: xtremeGlow 6s linear infinite;
    pointer-events: none;
}

/* Título */
.division-xtreme .division-title {
    color: #e4b3ff;
    text-shadow:
        0 0 8px rgba(195, 0, 255, .8),
        0 0 18px rgba(104, 0, 165, .9);
    letter-spacing: 4px;
}

/* Items */
.division-xtreme .item {
    background:
        linear-gradient(
            90deg,
            rgba(104, 0, 165, .35),
            rgba(180, 0, 255, .18)
        );
    border: 1px solid rgba(195, 0, 255, .55);
    box-shadow:
        0 0 12px rgba(104, 0, 165, .4);
}

/* TOP 3 aún más pro */
.division-xtreme .item.resaltado {
    box-shadow:
        0 0 18px rgba(195, 0, 255, .85),
        inset 0 0 12px rgba(255, 255, 255, .15);
}

/* Animación */
@keyframes xtremeGlow {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}


/* Maestro */
.division-maestro {
    background: rgba(221, 8, 0, .18);
}
.division-maestro .division-title {
    color: #dd0800;
}
.division-maestro .item {
    background: rgba(221, 8, 0, .12);
    border: 1px solid rgba(221, 8, 0, .35);
}

/* Platino */
.division-platino {
    background: rgba(0, 153, 127, .18);
}
.division-platino .division-title {
    color: #00997f;
}
.division-platino .item {
    background: rgba(0, 153, 127, .12);
    border: 1px solid rgba(0, 153, 127, .35);
}

/* Oro */
.division-oro {
    background: rgba(221, 179, 0, .18);
}
.division-oro .division-title {
    color: #ddb300;
}
.division-oro .item {
    background: rgba(221, 179, 0, .12);
    border: 1px solid rgba(221, 179, 0, .35);
}

/* Plata */
.division-plata {
    background: rgba(162, 168, 192, .18);
}
.division-plata .division-title {
    color: #a2a8c0;
}
.division-plata .item {
    background: rgba(162, 168, 192, .12);
    border: 1px solid rgba(162, 168, 192, .35);
}

/* Bronce */
.division-bronce {
    background: rgba(177, 86, 15, .18);
}
.division-bronce .division-title {
    color: #b1560f;
}
.division-bronce .item {
    background: rgba(177, 86, 15, .12);
    border: 1px solid rgba(177, 86, 15, .35);
}

</style>
@endsection
