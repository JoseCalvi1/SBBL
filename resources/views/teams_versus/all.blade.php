@extends('layouts.app')

@section('title', 'Duelos de equipos Beyblade X')

@section('styles')
<style>
    .duel-card {
        position: relative;
        border: 2px solid #343a40;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        height: 230px;
        color: white;
        background-size: cover;
        background-position: center;
        overflow: hidden;
        transition: transform 0.3s ease-in-out;
    }

    .duel-card:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 30px rgba(255, 0, 0, 0.4);
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom right, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.9));
        z-index: 1;
    }

    .duel-info, .duel-mode {
        position: relative;
        z-index: 2;
    }

    .duel-mode {
        background-color: #e53935;
        color: #ffffff;
        padding: 5px 15px;
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.9rem;
        box-shadow: 0 0 10px #ff0000;
    }

    .duel-info {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
        text-align: center;
    }

    .duel-player {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .player-name {
        font-size: 1.1rem;
        font-weight: bold;
        text-shadow: 0 0 6px rgba(255,255,255,0.4);
    }

    .player-logo {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 50%;
    }

    .vs {
        font-size: 28px;
        font-weight: bold;
        color: #ffc107;
        animation: pulseGlow 1.5s infinite;
        text-shadow: 0 0 10px #fff700;
    }

    .player-score {
        font-size: 22px;
        font-weight: bold;
        color: white;
        text-shadow: 0 0 5px black;
    }

    @keyframes pulseGlow {
        0%, 100% { transform: scale(1); text-shadow: 0 0 5px #ffc107; }
        50% { transform: scale(1.2); text-shadow: 0 0 15px #fff700; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid bg-dark shadow-sm py-2">
    <div class="d-flex justify-content-center gap-4">
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill" href="{{ route('equipos.index') }}">
            Inicio
        </a>
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill" href="{{ route('teams_versus.all') }}">
            Duelos
        </a>
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill" href="{{ route('equipos.ranking') }}">
            Ranking
        </a>
    </div>
</div>

<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Duelos de equipo
        @if (
            Auth::check() &&
            (
                Auth::user()->is_referee ||
                (
                    Auth::user()->teams &&
                    count(Auth::user()->teams) > 0 &&
                    Auth::user()->teams[0]->captain_id == Auth::user()->id &&
                    Auth::user()->teams[0]->members()->count() >= 3
                )
            )
        )
        <a href="{{ route('teams_versus.create') }}" class="btn btn-outline-warning mb-2 text-uppercase font-weight-bold">
            Crear duelo
        </a>
        @endif
    </h2>

    <div class="row mt-2">
        @foreach ($versus as $duelo)
        <div class="col-md-4 mb-3">
            <div class="duel-card" style="background-image: url(data:image/png;base64,{{ $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->image : $duelo->versus_2->image }});">
                <div class="overlay"></div>
                <div class="duel-mode">
                    {{ $duelo->matchup == "beybladex" ? "Beyblade X" : "Beyblade Burst" }}
                </div>
                <div class="duel-info mt-3">
                    <div class="duel-player">
                        @if ($duelo->versus_1->logo)
                            <img src="data:image/png;base64,{{ $duelo->versus_1->logo }}" alt="Logo 1" class="player-logo">
                        @else
                            <img src="/images/logo_new.png" alt="Logo 1" class="player-logo">
                        @endif
                        <span class="player-name">{{ $duelo->versus_1->name }}</span>
                        <span class="player-score">{{ $duelo->result_1 }}</span>
                    </div>
                    <div class="vs">VS</div>
                    <div class="duel-player">
                        <span class="player-score">{{ $duelo->result_2 }}</span>
                        <span class="player-name">{{ $duelo->versus_2->name }}</span>
                        @if ($duelo->versus_2->logo)
                            <img src="data:image/png;base64,{{ $duelo->versus_2->logo }}" alt="Logo 1" class="player-logo">
                        @else
                            <img src="/images/logo_new.png" alt="Logo 1" class="player-logo">
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
