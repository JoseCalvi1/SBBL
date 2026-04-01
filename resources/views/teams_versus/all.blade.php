@extends('layouts.app')

@section('title', 'Duelos de Sindicatos SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: DUELOS DE EQUIPOS (Hereda de layout)
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
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    /* ── TARJETAS DE DUELO (ARENA BATTLE) ── */
    .duel-card {
        position: relative;
        border: 4px solid #000;
        border-radius: 0;
        padding: 15px;
        height: 300px;
        color: white;
        background-size: cover;
        background-position: center;
        overflow: hidden;
        transition: 0.2s;
        box-shadow: 8px 8px 0 #000;
        transform: skewX(-2deg);
    }

    .duel-card:hover {
        transform: translate(-3px, -3px) skewX(-2deg);
        box-shadow: 12px 12px 0 var(--shonen-red);
        border-color: var(--sbbl-gold);
    }

    .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.6) 50%, rgba(0, 0, 0, 0.9) 100%);
        z-index: 1;
        pointer-events: none;
    }

    .duel-mode {
        background-color: #000;
        border: 2px solid var(--shonen-red);
        color: #fff;
        padding: 5px 20px;
        position: absolute;
        top: -2px; /* Solapa ligeramente el borde */
        left: 50%;
        transform: translateX(-50%) skewX(-10deg);
        font-family: 'Bangers', cursive;
        font-size: 1.2rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        box-shadow: 3px 3px 0 var(--shonen-red);
        z-index: 3;
    }
    .duel-mode > span { display: block; transform: skewX(10deg); }

    .duel-info {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        text-align: center;
        padding-top: 15px;
    }

    .duel-player {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        width: 100%;
    }

    .player-name {
        font-family: 'Bangers', cursive;
        font-size: 1.8rem;
        letter-spacing: 1px;
        text-shadow: 2px 2px 0 #000;
        color: #fff;
        line-height: 1;
        flex: 1;
    }

    /* El ganador resplandece en oro */
    .is-winner .player-name, .is-winner .player-score {
        color: var(--sbbl-gold);
    }

    .player-logo {
        width: 65px;
        height: 65px;
        object-fit: contain;
        background: #000;
        border: 2px solid var(--sbbl-gold);
        padding: 4px;
        box-shadow: 3px 3px 0 #000;
    }

    .vs {
        font-family: 'Bangers', cursive;
        font-size: 3rem;
        color: var(--shonen-red);
        text-shadow: 3px 3px 0 #000;
        margin: 5px 0;
        line-height: 0.8;
        transform: rotate(-5deg);
        animation: battlePulse 2s infinite;
    }

    .player-score {
        font-family: 'Bangers', cursive;
        font-size: 2.5rem;
        color: white;
        text-shadow: 3px 3px 0 #000;
        background: rgba(0,0,0,0.5);
        padding: 0 10px;
        border: 2px solid #333;
    }

    /* ── FOOTER DE LA CARTA (FECHA Y VÍDEO) ── */
    .duel-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: #000;
        border-top: 2px solid #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 3;
        padding: 5px 15px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 0.8rem;
        color: #aaa;
    }

    .duel-date {
        letter-spacing: 1px;
    }

    .duel-video-btn {
        color: #fff;
        background-color: var(--shonen-red);
        padding: 4px 15px;
        text-decoration: none;
        text-transform: uppercase;
        transition: 0.2s;
        border: 2px solid #000;
        transform: skewX(-10deg);
    }
    .duel-video-btn > span { display: block; transform: skewX(10deg); }
    .duel-video-btn:hover {
        background-color: #fff;
        color: #000;
        border-color: var(--shonen-red);
    }

    @keyframes battlePulse {
        0%, 100% { transform: scale(1) rotate(-5deg); text-shadow: 3px 3px 0 #000; }
        50% { transform: scale(1.15) rotate(-5deg); text-shadow: 0 0 15px var(--shonen-red), 3px 3px 0 #000; }
    }

    @media (max-width: 576px) {
        .player-name { font-size: 1.4rem; }
        .player-score { font-size: 2rem; }
        .player-logo { width: 50px; height: 50px; }
        .vs { font-size: 2.2rem; }
    }
</style>
@endsection

@section('content')

{{-- NAVEGACIÓN SUPERIOR DE EQUIPOS --}}
<div class="top-nav-equipos">
    <div class="container">
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a class="btn-shonen btn-shonen-info px-4 py-2" style="background: var(--sbbl-blue-3);" href="{{ route('equipos.index') }}">
                <span><i class="fas fa-users me-1"></i> SINDICATOS</span>
            </a>
            <a class="btn-shonen btn-shonen-warning px-4 py-2" href="{{ route('teams_versus.all') }}">
                <span><i class="fas fa-fist-raised me-1"></i> DUELOS</span>
            </a>
            <a class="btn-shonen btn-shonen-info px-4 py-2" style="background: var(--sbbl-blue-3);" href="{{ route('equipos.ranking') }}">
                <span><i class="fas fa-chart-line me-1"></i> RANKING</span>
            </a>
        </div>
    </div>
</div>


<div class="container mb-5">

    <div class="text-center mb-5">
        <h2 class="page-title"><i class="fas fa-fist-raised text-white me-2" style="text-shadow: none;"></i> REGISTRO DE BATALLAS</h2>

        {{-- BOTÓN CREAR DUELO --}}
        @if (Auth::check() && (Auth::user()->is_referee || (Auth::user()->teams && count(Auth::user()->teams) > 0 && Auth::user()->teams[0]->captain_id == Auth::user()->id && Auth::user()->teams[0]->members()->count() >= 3)))
            <div class="mt-4">
                <a href="{{ route('teams_versus.create') }}" class="btn-shonen btn-shonen-warning d-inline-block px-4 py-2" style="font-size: 1.5rem;">
                    <span><i class="fas fa-bolt me-2"></i> DECLARAR DUELO</span>
                </a>
            </div>
        @endif
    </div>

    {{-- GRID DE DUELOS --}}
    <div class="row g-4">
        @forelse ($versus as $duelo)
            @php
                $bgImage = $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->image : $duelo->versus_2->image;
                $bgUrl = $bgImage ? "data:image/png;base64,{$bgImage}" : asset('images/FONDO_BX.webp');

                $logo1 = $duelo->versus_1->logo ? "data:image/png;base64,{$duelo->versus_1->logo}" : asset('images/logo_new.png');
                $logo2 = $duelo->versus_2->logo ? "data:image/png;base64,{$duelo->versus_2->logo}" : asset('images/logo_new.png');

                $win1 = $duelo->result_1 > $duelo->result_2 ? 'is-winner' : '';
                $win2 = $duelo->result_2 > $duelo->result_1 ? 'is-winner' : '';
            @endphp

            <div class="col-md-6 col-xl-4">
                <div class="duel-card" style="background-image: url('{{ $bgUrl }}');">
                    <div class="overlay"></div>

                    <div class="duel-mode">
                        <span>{{ $duelo->matchup == "beybladex" ? "BEYBLADE X" : "BURST" }}</span>
                    </div>

                    <div class="duel-info">
                        {{-- Equipo 1 --}}
                        <div class="duel-player {{ $win1 }}">
                            <img src="{{ $logo1 }}" alt="Logo 1" class="player-logo">
                            <span class="player-name text-truncate text-start">{{ $duelo->versus_1->name }}</span>
                            <span class="player-score">{{ $duelo->result_1 }}</span>
                        </div>

                        <div class="vs">VS</div>

                        {{-- Equipo 2 --}}
                        <div class="duel-player {{ $win2 }}">
                            <span class="player-score">{{ $duelo->result_2 }}</span>
                            <span class="player-name text-truncate text-end">{{ $duelo->versus_2->name }}</span>
                            <img src="{{ $logo2 }}" alt="Logo 2" class="player-logo">
                        </div>
                    </div>

                    {{-- Footer Carta --}}
                    <div class="duel-footer">
                        <div class="duel-date">
                            <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($duelo->created_at)->format('d/m/Y') }}
                        </div>
                        @if(!empty($duelo->url))
                            <a href="{{ $duelo->url }}" target="_blank" class="duel-video-btn">
                                <span><i class="fab fa-youtube"></i> VÍDEO</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="text-center py-5 bg-black border border-secondary" style="border-width: 3px !important; box-shadow: 6px 6px 0 #000; transform: skewX(-2deg);">
                    <div style="transform: skewX(2deg);">
                        <i class="fas fa-satellite-dish mb-3 text-secondary" style="font-size: 4rem;"></i>
                        <h3 class="font-bangers text-white fs-2 mb-2">SIN REGISTROS DE COMBATE</h3>
                        <p class="text-white fw-bold mb-0">La arena de sindicatos está en silencio.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
