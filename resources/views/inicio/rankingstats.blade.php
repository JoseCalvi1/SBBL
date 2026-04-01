@extends('layouts.app')

@section('title', 'Ranking Blader del Mes')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: RANKING DEL MES SHONEN (Hereda de layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Bangers', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 2rem;
        text-align: center;
    }

    /* ── ESTRUCTURA DE TABLA (GRID SHONEN) ── */
    .ranking-container {
        background: rgba(0,0,0,0.5);
        border: 4px solid #000;
        box-shadow: 8px 8px 0 rgba(0,0,0,0.8);
        padding: 20px;
        max-width: 900px;
        margin: 0 auto 3rem auto;
    }

    .item {
        display: grid;
        grid-template-columns: 60px 1.5fr 1fr 1fr 100px;
        align-items: center;
        gap: 15px;
        padding: 12px 20px;
        margin-bottom: 10px;
        background: var(--sbbl-blue-2);
        border: 2px solid #000;
        transform: skewX(-2deg);
        transition: 0.2s;
    }
    .item > * { transform: skewX(2deg); } /* Des-inclinar contenido */

    .item:hover:not(.encabezado) {
        transform: translate(-3px, -3px) skewX(-2deg);
        box-shadow: 6px 6px 0 var(--sbbl-blue-3);
        border-color: var(--sbbl-gold);
        background: #000;
    }

    .encabezado {
        background: var(--sbbl-blue-1) !important;
        border: 3px solid #000 !important;
        color: var(--sbbl-gold) !important;
        font-family: 'Bangers', cursive;
        font-size: 1.3rem;
        letter-spacing: 2px;
        box-shadow: 4px 4px 0 #000;
        margin-bottom: 20px !important;
    }
    .encabezado:hover { transform: skewX(-2deg) !important; box-shadow: 4px 4px 0 #000 !important; }

    /* Textos internos */
    .posicion {
        font-family: 'Bangers', cursive;
        font-size: 1.8rem;
        text-align: center;
        color: #fff;
        text-shadow: 2px 2px 0 #000;
    }
    .resaltado .posicion { color: var(--sbbl-gold); font-size: 2.2rem; }

    .jugador-name {
        font-family: 'Bangers', cursive;
        font-size: 1.5rem;
        color: #fff;
        letter-spacing: 1px;
        text-transform: uppercase;
        text-shadow: 1px 1px 0 #000;
    }
    .resaltado .jugador-name { color: var(--sbbl-gold); }

    .stat-label {
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 0.7rem;
        color: #aaa;
        display: block;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .stat-value {
        font-family: 'Bangers', cursive;
        font-size: 1.4rem;
        letter-spacing: 1px;
        text-shadow: 1px 1px 0 #000;
    }

    .puntos-ganados { color: #00ffcc; }
    .puntos-perdidos { color: var(--shonen-red); }

    .win-rate {
        font-family: 'Bangers', cursive;
        font-size: 1.8rem;
        text-align: right;
        text-shadow: 2px 2px 0 #000;
    }

    /* Colores según % */
    .wr-s { color: #00ff00; }
    .wr-a { color: var(--shonen-cyan); }
    .wr-b { color: var(--sbbl-gold); }
    .wr-c { color: var(--shonen-red); }

    @media (max-width: 768px) {
        .item { grid-template-columns: 50px 1fr 100px; padding: 10px; gap: 10px; }
        .item > div:nth-child(3), .encabezado > div:nth-child(3),
        .item > div:nth-child(4), .encabezado > div:nth-child(4) { display: none; } /* Ocultar Daño en móvil */
        .jugador-name { font-size: 1.2rem; }
        .win-rate { font-size: 1.5rem; }
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    {{-- Navegación / Volver --}}
    <div class="mb-4">
        <a href="{{ route('inicio.stats') }}" class="btn-shonen btn-shonen-info d-inline-block px-4 py-2">
            <span><i class="fas fa-arrow-left me-2"></i> VOLVER AL RADAR</span>
        </a>
    </div>

    {{-- Título --}}
    <h1 class="page-title">
        <i class="fas fa-fire text-white me-2" style="text-shadow: none;"></i> RANKING DEL MES PASADO
    </h1>

    <div class="ranking-container">

        {{-- Encabezado de la Grid --}}
        <div class="item encabezado">
            <span class="text-center">POS</span>
            <span>BLADER</span>
            <div class="text-center">DAÑO INFLIGIDO</div>
            <div class="text-center">DAÑO RECIBIDO</div>
            <span class="text-end">EFICIENCIA</span>
        </div>

        {{-- Lista de Jugadores --}}
        @forelse($ranking as $index => $user)
            @php
                $wr = $user->porcentaje_ganados;
                if ($wr >= 60) { $cText = 'wr-s'; }
                elseif ($wr >= 50) { $cText = 'wr-a'; }
                elseif ($wr >= 40) { $cText = 'wr-b'; }
                else { $cText = 'wr-c'; }
            @endphp

            <div class="item {{ $index < 3 ? 'resaltado' : '' }}">
                <span class="posicion">#{{ $index + 1 }}</span>
                <span class="jugador-name text-truncate">{{ $user->name }}</span>

                <div class="text-center">
                    <span class="stat-label d-md-none">INFLIGIDO</span>
                    <span class="stat-value puntos-ganados">+{{ $user->total_puntos_ganados }}</span>
                </div>

                <div class="text-center">
                    <span class="stat-label d-md-none">RECIBIDO</span>
                    <span class="stat-value puntos-perdidos">-{{ $user->total_puntos_perdidos }}</span>
                </div>

                <span class="win-rate {{ $cText }}">{{ number_format($wr, 1) }}%</span>
            </div>
        @empty
            {{-- Estado Vacío --}}
            <div class="text-center py-5 bg-black border border-secondary mt-3" style="transform: skewX(-2deg);">
                <div style="transform: skewX(2deg);">
                    <i class="fas fa-ghost fa-3x mb-3 text-secondary"></i>
                    <h4 class="font-bangers text-white" style="font-size: 2rem; letter-spacing: 1px;">DATOS NO DISPONIBLES</h4>
                    <p class="text-white fw-bold mb-0">No hay suficientes registros para generar el ranking del mes pasado.</p>
                </div>
            </div>
        @endforelse

    </div>

</div>
@endsection
