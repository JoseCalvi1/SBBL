@extends('layouts.app')

@section('styles')
<style>
    .duel-card {
        position: relative;
        border: 2px solid #343a40;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        height: 200px;
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
@if (Auth::user()->profile->id == $profile->id || Auth::user()->is_admin)
<div class="container pb-4">
    <div class="row">
        <div class="col-12">
            @if ($profile->fondo)
                <div style="background-image: url('/storage/{{ $profile->fondo }}'); background-size: cover; background-repeat: no-repeat; background-position: center; padding: 80px;"></div>
            @else
                <div style="background-image: url('/storage/upload-profiles/SBBLFondo.png'); background-size: cover; background-repeat: repeat; background-position: center; padding: 80px;"></div>
            @endif
        </div>
        <div class="col-md-4" style="margin-top: -20px;">
            <div style="position: relative;">
                @if ($profile->imagen)
                                <img src="/storage/{{ $profile->imagen }}" class="rounded-circle" width="200" style="top: 0; left: 0; {{ strpos($profile->imagen, '.gif') !== false ? 'padding: 20px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/BASE/DranDagger.webp" class="rounded-circle" width="200" style="top: 0; left: 0;">
                            @endif
                            @if ($profile->marco)
                                <img src="/storage/{{ $profile->marco }}" class="rounded-circle" width="200" style="position: absolute; top: 0; left: 0;">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="200" style="position: absolute; top: 0; left: 0;">
                            @endif
            </div>

        </div>
        <div class="col-md-8">
            <h2 class="text-center mb-2 mt-5 mt-md-0 text-white">{{ $profile->user->name }}</h2>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">{{ $profile->user->email }}</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">Región: @if ($profile->region)
                {{ $profile->region->name }}
            @else
                Por definir
            @endif</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">Puntos BURST: {{ $profile->points_s3 }}</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">Puntos X: {{ $profile->points_x1 }}</h3>
            <a href="{{ route('profiles.edit', ['profile' => Auth::user()->id]) }}" class="btn btn-outline-info mr-2 text-uppercase font-weight-bold w-100">
                Editar perfil
            </a>

            <!--
            <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Palmarés</h3>
            <div class="row">
            @if (count($profile->trophies) != 0)
                @foreach ($profile->trophies as $trophy)
                    <div class="col-md-6">
                        <p class="font-weight-bold">{{ $trophy->pivot->count }}x<svg style="@if($trophy->id == 1 || $trophy->id == 4) fill:gold; @elseif($trophy->id == 2 || $trophy->id == 5) fill:silver; @else fill:rgba(205, 127, 50); @endif" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 576 512"><path d="M572.1 82.38C569.5 71.59 559.8 64 548.7 64h-100.8c.2422-12.45 .1078-23.7-.1559-33.02C447.3 13.63 433.2 0 415.8 0H160.2C142.8 0 128.7 13.63 128.2 30.98C127.1 40.3 127.8 51.55 128.1 64H27.26C16.16 64 6.537 71.59 3.912 82.38C3.1 85.78-15.71 167.2 37.07 245.9c37.44 55.82 100.6 95.03 187.5 117.4c18.7 4.805 31.41 22.06 31.41 41.37C256 428.5 236.5 448 212.6 448H208c-26.51 0-47.99 21.49-47.99 48c0 8.836 7.163 16 15.1 16h223.1c8.836 0 15.1-7.164 15.1-16c0-26.51-21.48-48-47.99-48h-4.644c-23.86 0-43.36-19.5-43.36-43.35c0-19.31 12.71-36.57 31.41-41.37c86.96-22.34 150.1-61.55 187.5-117.4C591.7 167.2 572.9 85.78 572.1 82.38zM77.41 219.8C49.47 178.6 47.01 135.7 48.38 112h80.39c5.359 59.62 20.35 131.1 57.67 189.1C137.4 281.6 100.9 254.4 77.41 219.8zM498.6 219.8c-23.44 34.6-59.94 61.75-109 81.22C426.9 243.1 441.9 171.6 447.2 112h80.39C528.1 135.7 526.5 178.7 498.6 219.8z"/></svg> {{ $trophy->name.' Season '.$trophy->season }}</p>
                    </div>
                @endforeach
            @else
                <div class="col-md-6">
                    <p>No hay registros</p>
                </div>
            @endif
            </div>
        -->
        </div>
    </div>

    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        <div class="card bg-dark text-white">
            <div class="card-body">
                <h1 class="card-title">Invitaciones Pendientes</h1>
                @if ($invitacionesPendientes->isEmpty())
                    <p class="card-text">No tienes invitaciones pendientes en este momento.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($invitacionesPendientes as $invitacion)
                            <li class="list-group-item bg-dark border-bottom border-white mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Equipo: {{ $invitacion->team->name }}</span>
                                    <div class="btn-group" role="group">
                                        <form action="{{ route('invitations.accept', $invitacion) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Aceptar</button>
                                        </form>
                                        <form action="{{ route('invitations.reject', $invitacion) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Rechazar</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>




    <h2 class="titulo-categoria text-uppercase mb-4 mt-4 text-white">Duelos mensuales</h2>
    <div class="row mt-2">
        @foreach ($versus as $duelo)
        <div class="col-md-3 mb-3"> <!-- Cada tarjeta ocupará 3 columnas en una fila y tendrá un margen inferior -->
            <div class="duel-card" style="background-image: url('/storage/{{ $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->profile->fondo : $duelo->versus_2->profile->fondo }}');">
                <div class="overlay"></div>
                <div class="duel-mode">
                    <span class="mode">{{ ($duelo->matchup == "beybladex") ? "Beyblade X" : "Beyblade Burst"  }}{{ $duelo->status == "CLOSED" ? " - Válido" : ($duelo->status == "invalid" ? " - Inválido" : " - Enviado") }}</span>
                </div>
                <div class="duel-info">
                    <div class="duel-player">
                        <span class="player-name">{{ $duelo->versus_1->name }}</span>
                    </div>
                    <div class="vs"><span class="player-score">{{ $duelo->result_1 }}</span> VS <span class="player-score">{{ $duelo->result_2 }}</span></div>
                    <div class="duel-player">
                        <span class="player-name">{{ $duelo->versus_2->name }}</span>
                    </div>
                </div>

            </div>
        </div>
        @endforeach
    </div>

</div>
<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4 text-white">Estadísticas de Beyblades</h2>
    <div class="table-responsive">
        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th>Blade</th>
                    <th>Ratchet</th>
                    <th>Bit</th>
                    <th>Victorias</th>
                    <th>Derrotas</th>
                    <th>Total Partidas</th>
                    <th>Porcentaje Victorias/Derrotas</th>
                    <th>Puntos Ganados por Combate</th>
                    <th>Puntos Perdidos por Combate</th>
                    <th>Puntos OTH</th>
                </tr>
            </thead>
            <tbody>
                @foreach($beybladeStats as $stat)
                    <tr>
                        <td>{{ $stat->blade }}</td>
                        <td>{{ $stat->ratchet }}</td>
                        <td>{{ $stat->bit }}</td>
                        <td>{{ $stat->total_victorias }}</td>
                        <td>{{ $stat->total_derrotas }}</td>
                        <td>{{ $stat->total_partidas }}</td>
                        <td>{{ number_format($stat->percentage_victories, 2) }}%</td>
                        <td>{{ number_format($stat->puntos_ganados_por_combate, 2) }}</td>
                        <td>{{ number_format($stat->puntos_perdidos_por_combate, 2) }}</td>
                        <td>{{ number_format($stat->eficiencia, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
