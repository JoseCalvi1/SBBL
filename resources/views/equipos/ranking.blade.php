@extends('layouts.app')

@section('title', 'Ranking Equipos Beyblade X')

@section('styles')
<style>
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
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 12px;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1;
    }

    .team-logo {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        z-index: 2;
        margin-right: 20px;
    }

    .team-info {
        z-index: 2;
        color: white;
        display: flex;
        flex-direction: column;
    }

    .team-name {
        font-size: 1.4em;
        font-weight: bold;
    }

    .team-points {
        font-size: 1.2em;
        color: #ffc107;
    }

    .team-rank {
        z-index: 2;
        font-size: 2em;
        font-weight: bold;
        color: white;
        margin-right: 20px;
        width: 50px;
        text-align: center;
    }

    .team-entry {
        display: flex;
        align-items: center;
        width: 100%;
    }

</style>
@endsection

@section('content')
<div class="container-fluid" style="background: #283b63">
    <div class="row">
        <ul class="navbar-nav m-auto" style="flex-direction: row;">
            <li class="nav-item">
                <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('equipos.index') }}">INICIO</a>
            </li>
            <li class="nav-item">
                <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('teams_versus.all') }}">DUELOS</a>
            </li>
            <li class="nav-item">
                <a class="nav-link ml-2 mr-2" style="color: white; font-weight: bold; font-size:1.2em;" href="{{ route('equipos.ranking') }}">RANKING</a>
            </li>
        </ul>
    </div>
</div>

<div>
    <div class="container ranking-container">
        <h2 class="ranking-title">Ranking de Equipos</h2>

        @foreach($teams as $key => $team)
        <div class="team-card" style="background-image: url(data:image/png;base64,{{ $team->image }});">
            <div class="team-overlay"></div>
            <div class="team-entry">
                <div class="team-rank">#{{ $key + 1 }}</div>
                <img class="team-logo" src="@if($team->logo) data:image/png;base64,{{ $team->logo }} @else /images/logo_new.png @endif" alt="Logo de {{ $team->name }}">
                <div class="team-info">
                    <span class="team-name">{{ $team->name }}</span>
                    <span class="team-points">{{ $team->points_x1 }} pts</span>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection
