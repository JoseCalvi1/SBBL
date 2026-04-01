@extends('layouts.app')

@section('title', 'Ranking equipos SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: RANKING DE EQUIPOS (Hereda de layout)
       ==================================================================== */

    /* ── MENÚ SUPERIOR TÁCTICO ── */
    .top-nav-equipos {
        background: #000;
        border-bottom: 4px solid var(--sbbl-gold);
        padding: 15px 0;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Bangers', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    /* ── CONTENEDOR DE RANKING ── */
    .ranking-container {
        background: var(--sbbl-blue-2);
        border: 4px solid #000;
        border-radius: 0;
        padding: 30px;
        box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.8);
        max-width: 800px;
        margin: 0 auto;
    }

    /* ── TARJETAS DE EQUIPO (Grid Shonen) ── */
    .team-card {
        display: flex;
        align-items: center;
        background-size: cover;
        background-position: center;
        position: relative;
        border: 3px solid #000;
        border-radius: 0;
        margin-bottom: 15px;
        padding: 15px 20px;
        overflow: hidden;
        box-shadow: 5px 5px 0 #000;
        transform: skewX(-2deg);
        transition: 0.2s;
    }
    .team-card > * { transform: skewX(2deg); }

    .team-card:hover {
        transform: translate(-3px, -3px) skewX(-2deg);
        box-shadow: 8px 8px 0 var(--sbbl-gold);
        border-color: var(--sbbl-gold);
    }

    .team-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(0, 0, 0, 0.9) 30%, rgba(0, 0, 0, 0.6) 100%);
        z-index: 1;
        pointer-events: none;
    }

    .team-entry {
        display: flex;
        align-items: center;
        width: 100%;
        z-index: 2;
    }

    .team-rank {
        font-family: 'Bangers', cursive;
        font-size: 2.5rem;
        color: #fff;
        margin-right: 20px;
        width: 60px;
        text-align: center;
        text-shadow: 2px 2px 0 #000;
    }

    .team-logo {
        width: 70px;
        height: 70px;
        object-fit: contain;
        margin-right: 20px;
        background: #000;
        border: 2px solid var(--sbbl-gold);
        padding: 5px;
        box-shadow: 3px 3px 0 #000;
    }

    .team-info {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .team-name {
        font-family: 'Bangers', cursive;
        font-size: 1.8rem;
        color: #fff;
        letter-spacing: 1px;
        line-height: 1.1;
        text-shadow: 2px 2px 0 #000;
    }

    .team-points {
        font-family: 'Bangers', cursive;
        font-size: 1.5rem;
        color: var(--sbbl-gold);
        text-shadow: 1px 1px 0 #000;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ===== COLORES POR DIVISIÓN (Shonen Style) ===== */
    /* Resaltamos el borde y la sombra al hacer hover según la división,
       y añadimos una etiqueta de color. */

    .div-badge {
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 0.7rem;
        padding: 2px 8px;
        color: #000;
        border: 1px solid #000;
        text-transform: uppercase;
        letter-spacing: 0;
        text-shadow: none;
    }

    .division-xtreme { border-left: 8px solid #b400ff; }
    .division-xtreme:hover { border-color: #b400ff; box-shadow: 8px 8px 0 #6800a5; }
    .division-xtreme .div-badge { background: #e4b3ff; }

    .division-maestro { border-left: 8px solid #dd0800; }
    .division-maestro:hover { border-color: #dd0800; box-shadow: 8px 8px 0 #8a0500; }
    .division-maestro .div-badge { background: #ff4d4d; }

    .division-platino { border-left: 8px solid #00ffcc; }
    .division-platino:hover { border-color: #00ffcc; box-shadow: 8px 8px 0 #00997f; }
    .division-platino .div-badge { background: #00ffcc; }

    .division-oro { border-left: 8px solid var(--sbbl-gold); }
    .division-oro:hover { border-color: var(--sbbl-gold); box-shadow: 8px 8px 0 #b38600; }
    .division-oro .div-badge { background: var(--sbbl-gold); }

    .division-plata { border-left: 8px solid #e2e8f0; }
    .division-plata:hover { border-color: #e2e8f0; box-shadow: 8px 8px 0 #94a3b8; }
    .division-plata .div-badge { background: #e2e8f0; }

    .division-bronce { border-left: 8px solid #ff9d47; }
    .division-bronce:hover { border-color: #ff9d47; box-shadow: 8px 8px 0 #c26a10; }
    .division-bronce .div-badge { background: #ff9d47; }

    /* Top 3 Resaltado */
    .rank-1 .team-rank { color: var(--sbbl-gold); font-size: 3.5rem; }
    .rank-2 .team-rank { color: #e2e8f0; font-size: 3rem; }
    .rank-3 .team-rank { color: #ff9d47; font-size: 2.8rem; }

    @media (max-width: 576px) {
        .ranking-container { padding: 15px; border-width: 2px; }
        .team-card { padding: 10px; }
        .team-rank { font-size: 1.8rem; margin-right: 10px; width: 40px; }
        .team-logo { width: 50px; height: 50px; margin-right: 10px; }
        .team-name { font-size: 1.3rem; }
        .team-points { font-size: 1.2rem; }
        .rank-1 .team-rank { font-size: 2.5rem; }
        .rank-2 .team-rank { font-size: 2.2rem; }
        .rank-3 .team-rank { font-size: 2rem; }
    }
</style>
@endsection

@section('content')

@php
    // Divisiones Equipos Beyblade X S2
    function divisionTeamBX2($points) {
        if ($points >= 35) return 'Xtreme';
        if ($points >= 28) return 'Maestro';
        if ($points >= 21) return 'Platino';
        if ($points >= 14) return 'Oro';
        if ($points >= 7)  return 'Plata';
        return 'Bronce';
    }
@endphp

{{-- NAVEGACIÓN SUPERIOR DE EQUIPOS --}}
<div class="top-nav-equipos">
    <div class="container">
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a class="btn-shonen btn-shonen-info px-4 py-2" style="background: var(--sbbl-blue-3);" href="{{ route('equipos.index') }}">
                <span><i class="fas fa-users me-1"></i> EQUIPOS</span>
            </a>
            <a class="btn-shonen btn-shonen-info px-4 py-2" style="background: var(--sbbl-blue-3);" href="{{ route('teams_versus.all') }}">
                <span><i class="fas fa-fist-raised me-1"></i> DUELOS</span>
            </a>
            <a class="btn-shonen btn-shonen-warning px-4 py-2" href="{{ route('equipos.ranking') }}">
                <span><i class="fas fa-chart-line me-1"></i> RANKING</span>
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="text-center mb-4">
        <h2 class="page-title"><i class="fas fa-trophy text-white me-2" style="text-shadow: none;"></i> RANKING DE EQUIPOS</h2>
    </div>

    <div class="ranking-container">
        @forelse($teams as $key => $team)

            @php
                $division = divisionTeamBX2($team->points_x2);
                $bgImage = $team->image ? "data:image/png;base64,{$team->image}" : asset('images/FONDO_BX.webp');
            @endphp

            <div class="team-card division-{{ strtolower($division) }} rank-{{ $key + 1 }}"
                 style="background-image: url('{{ $bgImage }}');">

                <div class="team-overlay"></div>

                <div class="team-entry">
                    <div class="team-rank">#{{ $key + 1 }}</div>

                    <img class="team-logo"
                         src="@if($team->logo) data:image/png;base64,{{ $team->logo }} @else /images/logo_new.png @endif"
                         alt="Logo de {{ $team->name }}">

                    <div class="team-info">
                        <div class="team-name text-truncate" title="{{ $team->name }}">{{ $team->name }}</div>
                        <div class="team-points">
                            {{ $team->points_x2 }} PTS
                            <span class="div-badge">{{ $division }}</span>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="text-center py-5 bg-black border border-secondary" style="border-width: 3px !important; box-shadow: 6px 6px 0 #000; transform: skewX(-2deg);">
                <div style="transform: skewX(2deg);">
                    <i class="fas fa-chart-bar mb-3 text-secondary" style="font-size: 4rem;"></i>
                    <h3 class="font-bangers text-white fs-2 mb-2">NO HAY DATOS EN EL RADAR</h3>
                    <p class="text-white fw-bold mb-0">Aún no hay equipos clasificados en esta temporada.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection
