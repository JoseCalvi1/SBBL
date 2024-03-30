@extends('layouts.app')

@section('styles')
<style>
    .duel-card {
        background-color: #f8f9fa;
        border: 2px solid #343a40;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.1);
    }

    .duel-info {
        display: flex;
        align-items: center;
        flex: 1;
        justify-content: space-around;
    }

    .duel-player {
        text-align: center;
    }

    .player-name {
        font-size: 20px;
        font-weight: bold;
        color: #343a40;
    }

    .player-score {
        font-size: 18px;
        color: #343a40;
        margin-top: 5px;
    }

    .vs {
        font-size: 24px;
        color: #343a40;
    }

    .duel-mode {
        background-color: #343a40;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 5px;
        margin-left: 20px;
    }

    .mode {
        font-size: 18px;
    }
</style>
@endsection

@section('content')

<div class="container">
        <h2 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos
            @if (Auth::user())
            <a href="{{ route('versus.create') }}" class="btn btn-outline-primary mb-2 text-uppercase font-weight-bold">
                Crear duelo
            </a>
            @endif
        </h2>
    <div class="row mt-2">
        @foreach ($versus as $duelo)
        <div class="duel-card">
            <div class="duel-info">
                <div class="duel-player">
                    <span class="player-name">{{ $duelo->versus_1->name }}</span>
                    <span class="player-score">{{ $duelo->result_1 }}</span>
                </div>
                <div class="vs">VS</div>
                <div class="duel-player">
                    <span class="player-score">{{ $duelo->result_2 }}</span>
                    <span class="player-name">{{ $duelo->versus_2->name }}</span>
                </div>
            </div>
            <div class="duel-mode">
                <span class="mode">{{ ($duelo->matchup == "beybladex") ? "Beyblade X" : "Beyblade Burst"  }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection

