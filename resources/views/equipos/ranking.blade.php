@extends('layouts.app')

@section('title', 'Ranking Equipos Beyblade X')

@section('styles')
<style>
    body {
        background: #121212;
        color: #e0e0e0;
    }

    .ranking-container {
        background: #1d2a3a;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    .ranking-title {
        font-size: 2rem;
        font-weight: bold;
        text-transform: uppercase;
        color: #ffc107;
        text-align: center;
        margin-bottom: 30px;
        letter-spacing: 2px;
    }

    .team-card {
        display: flex;
        align-items: center;
        background-size: cover;
        background-position: center;
        position: relative;
        border-radius: 12px;
        margin-bottom: 20px;
        padding: 15px 20px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }

    .team-overlay {
        position: absolute;
        inset: 0;
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.65);
        z-index: 1;
    }

    .team-entry {
        display: flex;
        align-items: center;
        width: 100%;
        z-index: 2;
    }

    .team-rank {
        font-size: 2em;
        font-weight: bold;
        color: white;
        margin-right: 20px;
        width: 60px;
        text-align: center;
    }

    .team-logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 20px;
        background: #000;
    }

    .team-info {
        display: flex;
        flex-direction: column;
    }

    .team-name {
        font-size: 1.4em;
        font-weight: bold;
        color: white;
    }

    .team-points {
        font-size: 1.1em;
        color: #ffc107;
        opacity: .9;
    }

    /* ===== Divisiones ===== */

    .division-xtreme {
        border: 2px solid rgba(104, 0, 165, .9);
        box-shadow: 0 0 25px rgba(104, 0, 165, .6);
    }

    .division-maestro {
        border: 2px solid rgba(221, 8, 0, .8);
    }

    .division-platino {
        border: 2px solid rgba(0, 153, 127, .8);
    }

    .division-oro {
        border: 2px solid rgba(221, 179, 0, .8);
    }

    .division-plata {
        border: 2px solid rgba(162, 168, 192, .8);
    }

    .division-bronce {
        border: 2px solid rgba(177, 86, 15, .8);
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

<div class="container-fluid bg-dark shadow-sm py-2 mb-4">
    <div class="d-flex justify-content-center gap-4">
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill"
           href="{{ route('equipos.index') }}">
            Inicio
        </a>

        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill"
           href="{{ route('teams_versus.all') }}">
            Duelos
        </a>

        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill"
           href="{{ route('equipos.ranking') }}">
            Ranking
        </a>
    </div>
</div>

<div class="container ranking-container">
    <h2 class="ranking-title">Ranking de Equipos</h2>

    @foreach($teams as $key => $team)

        @php
            $division = divisionTeamBX2($team->points_x2);
        @endphp

        <div class="team-card division-{{ strtolower($division) }}"
             style="background-image: url(data:image/png;base64,{{ $team->image }});">

            <div class="team-overlay"></div>

            <div class="team-entry">
                <div class="team-rank">#{{ $key + 1 }}</div>

                <img class="team-logo"
                     src="@if($team->logo)
                              data:image/png;base64,{{ $team->logo }}
                          @else
                              /images/logo_new.png
                          @endif"
                     alt="Logo de {{ $team->name }}">

                <div class="team-info">
                    <span class="team-name">{{ $team->name }}</span>
                    <span class="team-points">
                        {{ $team->points_x2 }} pts · {{ $division }}
                    </span>
                </div>
            </div>
        </div>

    @endforeach
</div>

@endsection
