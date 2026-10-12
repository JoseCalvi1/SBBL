@extends('layouts.app')

@section('title', 'Splits Season 3')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: SPLITS TEMÁTICOS (Hereda del layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Oswald', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* ── TABS (PESTAÑAS DE SPLIT) ── */
    .nav-tabs {
        border-bottom: 4px solid #000;
        margin-bottom: 30px !important;
        gap: 5px;
        justify-content: center;
    }
    .nav-tabs .nav-item { margin-bottom: -4px; }

    .nav-tabs .nav-link {
        background: #000;
        color: #fff;
        border: 3px solid #000;
        font-family: 'Oswald', cursive;
        font-size: 1.3rem;
        letter-spacing: 1px;
        border-radius: 0;
        transform: skewX(-5deg);
        transition: 0.2s;
        padding: 10px 20px;
    }

    /* Hover y Active usan la variable dinámica --theme-main y --theme-shadow */
    .nav-tabs .nav-link:hover {
        background: rgba(255,255,255,0.05);
        color: var(--theme-main);
        border-color: var(--theme-main);
    }
    .nav-tabs .nav-link.active {
        background: var(--theme-main);
        color: #000 !important;
        border-bottom-color: var(--theme-main);
        box-shadow: 4px -4px 0 var(--theme-shadow);
    }
    /* Iconos dentro de la pestaña activa se vuelven negros para contrastar */
    .nav-tabs .nav-link.active i { color: #000 !important; }

    /* ── ESTRUCTURA DE TABLA (GRID SHONEN) ── */
    .split-container {
        background: rgba(0,0,0,0.4);
        border: 4px solid #333;
        box-shadow: 8px 8px 0 rgba(0,0,0,0.8);
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        transition: border-color 0.3s;
    }

    .item {
        display: grid;
        grid-template-columns: 80px 1fr 120px;
        align-items: center;
        gap: 15px;
        padding: 15px 20px;
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
        border-color: var(--theme-main);
        background: #000;
    }

    .encabezado {
        background: var(--sbbl-blue-1) !important;
        border: 3px solid #000 !important;
        color: var(--theme-main) !important;
        font-family: 'Oswald', cursive;
        font-size: 1.3rem;
        letter-spacing: 2px;
        box-shadow: 4px 4px 0 #000;
        margin-bottom: 20px !important;
    }
    .encabezado:hover { transform: skewX(-2deg) !important; box-shadow: 4px 4px 0 #000 !important; }

    /* Textos internos adaptables al tema */
    .posicion {
        font-family: 'Oswald', cursive;
        font-size: 1.8rem;
        text-align: center;
        color: #fff;
        text-shadow: 2px 2px 0 #000;
    }
    .resaltado .posicion { color: var(--theme-main); font-size: 2.2rem; }

    .jugador-name {
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        color: #fff;
        font-size: 1.3rem;
        text-transform: uppercase;
        text-shadow: 1px 1px 0 #000;
    }

    .puntos {
        font-family: 'Oswald', cursive;
        font-size: 1.8rem;
        letter-spacing: 1px;
        color: var(--theme-main);
        text-shadow: 2px 2px 0 #000;
        text-align: right;
    }
    .puntos small { font-size: 0.5em; color: #fff; text-shadow: none; margin-left: 3px;}

    @media (max-width: 576px) {
        .item { grid-template-columns: 50px 1fr 80px; padding: 10px; gap: 10px; }
        .jugador-name { font-size: 1rem; }
        .posicion, .puntos { font-size: 1.4rem; }
        .nav-tabs .nav-link { font-size: 1rem; padding: 8px 10px; }
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    <div class="text-center mb-5 mt-2">
        <h1 class="page-title">
            <i class="fas fa-code-branch me-2 text-white" style="text-shadow: none;"></i> RANKING POR SPLITS - SEASON 3
        </h1>
    </div>

    {{-- DICCIONARIO DE TEMÁTICAS BASADO EN EL HISTÓRICO DE LA LIGA --}}
    @php
        $tematicas = [
            0 => ['color' => '#00ffcc', 'shadow' => '#008080', 'icon' => 'fas fa-wrench'],       // Pretemporada (Cian / Ajustes)
            1 => ['color' => '#ffd700', 'shadow' => '#cc0000', 'icon' => 'fas fa-bolt'],         // Inicial (Dorado / Rayo de inicio)
            2 => ['color' => '#b400ff', 'shadow' => '#4a0080', 'icon' => 'fas fa-ghost'],        // Let it R.I.P (Morado / Fantasma - Halloween)
            3 => ['color' => '#ff3333', 'shadow' => '#800000', 'icon' => 'fas fa-gift'],         // XMAS (Rojo / Regalo - Navidad)
            4 => ['color' => '#e2e8f0', 'shadow' => '#718096', 'icon' => 'fas fa-sync-alt'],     // Ligera Revival (Plateado / Reinicio)
            5 => ['color' => '#ff5500', 'shadow' => '#8b0000', 'icon' => 'fas fa-fire-alt'],     // Attack Only (Naranja-Fuego / Ataque)
        ];
    @endphp

    {{-- NAVEGACIÓN DE TABS (SPLITS) --}}
    <ul class="nav nav-tabs" id="splitTabs" role="tablist">
        @foreach($splits as $nombre => $rango)
            @php $tema = $tematicas[$loop->index] ?? $tematicas[1]; @endphp
            <li class="nav-item" role="presentation">
                {{-- Inyectamos las variables CSS dinámicas en el style inline del botón --}}
                <button class="nav-link @if ($loop->index === $activeSplitIndex) active @endif"
                        id="tab-{{ $loop->index }}"
                        data-bs-toggle="tab"
                        data-bs-target="#content-{{ $loop->index }}"
                        type="button"
                        role="tab"
                        style="--theme-main: {{ $tema['color'] }}; --theme-shadow: {{ $tema['shadow'] }};">
                    <span><i class="{{ $tema['icon'] }} me-2" style="color: {{ $tema['color'] }};"></i>{{ mb_strtoupper($nombre) }}</span>
                </button>
            </li>
        @endforeach
    </ul>

    {{-- CONTENIDO DE LOS SPLITS --}}
    <div class="tab-content mt-4" id="splitTabsContent">
        @foreach($splits as $nombre => $rango)
            @php $tema = $tematicas[$loop->index] ?? $tematicas[1]; @endphp

            {{-- Inyectamos las variables CSS dinámicas en el contenedor principal del panel --}}
            <div class="tab-pane fade @if ($loop->index === $activeSplitIndex) show active @endif"
                 id="content-{{ $loop->index }}"
                 role="tabpanel"
                 style="--theme-main: {{ $tema['color'] }}; --theme-shadow: {{ $tema['shadow'] }};">

                <div class="split-container" style="border-color: rgba( {{ hexdec(substr($tema['color'],1,2)) }}, {{ hexdec(substr($tema['color'],3,2)) }}, {{ hexdec(substr($tema['color'],5,2)) }}, 0.3 );">

                    {{-- Encabezado de la Grid --}}
                    <div class="item encabezado">
                        <span class="text-center">POS</span>
                        <span>JUGADOR</span>
                        <span class="text-end">PUNTOS</span>
                    </div>

                    {{-- Lista de Jugadores --}}
                    @forelse($data[$nombre] as $index => $jugador)
                        <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                            <span class="posicion">#{{ $index + 1 }}</span>
                            <span class="jugador-name text-truncate">{{ $jugador->name }}</span>
                            <span class="puntos">{{ $jugador->total_puntos }}<small>PTS</small></span>
                        </div>
                    @empty
                        {{-- Estado Vacío --}}
                        <div class="text-center py-5 bg-black border border-secondary mt-3" style="transform: skewX(-2deg);">
                            <div style="transform: skewX(2deg);">
                                <i class="{{ $tema['icon'] }} fa-3x mb-3" style="color: var(--theme-main); opacity: 0.5;"></i>
                                <h4 class="font-Oswald text-white" style="font-size: 2rem; letter-spacing: 1px;">SIN DATOS EN ESTE SPLIT</h4>
                                <p class="text-white fw-bold mb-0">Aún no hay batallas registradas en este periodo.</p>
                            </div>
                        </div>
                    @endforelse

                </div>

            </div>
        @endforeach
    </div>
</div>
@endsection
