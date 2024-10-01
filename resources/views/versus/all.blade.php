@extends('layouts.app')

@section('styles')
<style>
    .duel-card {
        position: relative;
        border: 2px solid #343a40;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        height: 250px;
        color: white;
        background-size: cover;
        background-position: center;
        overflow: hidden;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.9));
        z-index: 1;
    }

    .duel-info, .duel-mode {
        position: relative;
        z-index: 2;
    }

    .duel-info {
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
    }

    .player-score {
        font-size: 18px;
        margin-top: 5px;
    }

    .vs {
        font-size: 24px;
        text-align: center;
    }

    .duel-mode {
        background-color: #343a40;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 5px;
    }

    .mode {
        font-size: 18px;
    }
</style>
@endsection

@section('content')

<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Duelos
        @if (Auth::user())
        <a href="{{ route('versus.create') }}" class="btn btn-outline-warning mb-2 text-uppercase font-weight-bold">
            Crear duelo
        </a>
        @endif
    </h2>
    <div class="row mt-2">
        @foreach ($versus as $duelo)
        <div class="col-md-3 mb-3"> <!-- Cada tarjeta ocupará 3 columnas en una fila y tendrá un margen inferior -->
            <div class="duel-card" style="background-image: url('/storage/{{ $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->profile->fondo : $duelo->versus_2->profile->fondo }}');">
                <div class="overlay"></div>
                <div class="duel-mode">
                    <span class="mode">{{ ($duelo->matchup == "beybladex") ? "Beyblade X" : "Beyblade Burst"  }}</span>
                </div>
                <div class="duel-info">
                    <div class="duel-player">
                        <span class="player-name">{{ $duelo->versus_1->name }}</span>
                    </div>
                    <div class="vs"><span class="player-score">{{ $duelo->result_1 }}</span> VS <span class="player-score">{{ $duelo->result_2 }}</span></div>
                    <div class="duel-player">
                        <span class="player-name">{{ $duelo->versus_2->name }}</span>
                    </div>
                    @if ($duelo->versus_1->name == Auth::user()->name || $duelo->versus_2->name == Auth::user()->name)
                        <a href="{{ route('versus.versusdeck', ['duel' => $duelo->id, 'deck' => Auth::user()->id]) }}" type="button" class="btn btn-warning w-100">Introducir deck</a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
