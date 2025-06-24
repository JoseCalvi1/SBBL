@extends('layouts.app')

@section('styles')
<style>
    .duel-card {
            position: relative;
            border: 2px solid #343a40;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            background-size: cover;
            background-position: center;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 250px;
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
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="200" style="top: 0; left: 0;">
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
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">Región: @if ($profile->region)
                {{ $profile->region->name }}
            @else
                Por definir
            @endif</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">Puntos BURST: {{ $profile->points_s3 }}</h3>
            <h3 class="text-center mb-2 mt-5 mt-md-0 text-white">Puntos X: {{ $profile->points_x2 }}</h3>
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
        @if(!$invitacionesPendientes->isEmpty())
        <div class="card bg-dark text-white">
            <div class="card-body">
                <h1 class="card-title">Invitaciones Pendientes</h1>
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
            </div>
        </div>
        @endif
    </div>



    {{-- Controles de navegación de meses --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <a href="{{ route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 1 ? 12 : $currentMonth - 1, 'year' => $currentMonth == 1 ? $currentYear - 1 : $currentYear]) }}" class="btn btn-secondary">
            ← Mes Anterior
        </a>
        <h3 class="text-white">{{ \Carbon\Carbon::create($currentYear, $currentMonth)->translatedFormat('F Y') }}</h3>
        <a href="{{ route('profiles.show', ['profile' => $profile->id, 'month' => $currentMonth == 12 ? 1 : $currentMonth + 1, 'year' => $currentMonth == 12 ? $currentYear + 1 : $currentYear]) }}" class="btn btn-secondary">
            Mes Siguiente →
        </a>
    </div>

    {{-- Duelos Mensuales --}}
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4 text-white">Duelos mensuales</h2>
    <div class="row mt-2">
        @if($versus->isEmpty())
         <p class="col-md-12 text-white">No has participado en ningún duelo este mes</p>
        @endif
        @foreach ($versus as $duelo)
            <div class="col-md-3 mb-3">
                <div class="duel-card" style="background-image: url('/storage/{{ $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->profile->fondo : $duelo->versus_2->profile->fondo }}');">
                    <div class="overlay"></div>
                    <div class="duel-mode">
                        <span class="mode">{{ $duelo->matchup == "beybladex" ? "Beyblade X" : "Beyblade Burst" }}
                            @if ($duelo->status == "CLOSED")
                                - Válido
                            @elseif ($duelo->status == "INVALID")
                                - Inválido
                            @elseif ($duelo->status == "OPEN" && $duelo->url)
                                - Pendiente
                            @else
                                - Enviado
                            @endif
                        </span>
                    </div>
                    <div class="duel-info">
                        <div class="duel-player">
                            <span class="player-name">{{ $duelo->versus_1->name }}</span>
                        </div>
                        <div class="vs"><span class="player-score">{{ $duelo->result_1 }}</span> VS <span class="player-score">{{ $duelo->result_2 }}</span></div>
                        <div class="duel-player">
                            <span class="player-name">{{ $duelo->versus_2->name }}</span>
                        </div>
                        @if ($duelo->user_id_1 == Auth::user()->id || $duelo->user_id_2 == Auth::user()->id)
                                <a href="{{ route('versus.versusdeck', ['duel' => $duelo->id, 'deck' => Auth::user()->id]) }}" type="button" class="btn btn-warning w-100">Introducir deck</a>
                                <button type="button" class="btn btn-outline-light w-100 mt-2" data-toggle="modal" data-target="#modalDeck{{ $duelo->id }}">
                                    Vídeo
                                </button>
                            @endif
                    </div>
                </div>
            </div>
            <!-- Modal por duelo -->
         @auth
         <div class="modal fade" id="modalDeck{{ $duelo->id }}" tabindex="-1" role="dialog" aria-labelledby="modalDeckLabel{{ $duelo->id }}" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered" role="document">
                 <div class="modal-content bg-dark text-white">
                     <div class="modal-header">
                         <h5 class="modal-title" id="modalDeckLabel{{ $duelo->id }}">Actualizar video</h5>
                         <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                             <span aria-hidden="true">&times;</span>
                         </button>
                     </div>
                     <form method="POST" action="{{ route('versus.updateVideo', ['versus' => $duelo->id]) }}" class="p-2">
                         @csrf
                         @method('PUT')
                         <div class="form-group">
                             <label for="url{{ $duelo->id }}">Link al video del duelo:</label>
                             <input type="url" name="url" id="url{{ $duelo->id }}" class="form-control mb-1"
                                    placeholder="https://www.youtube.com/embed/tu-video"
                                    value="{{ old('url', $duelo->url ?? '') }}" required>
                         </div>
                         <div class="form-group">
                             <input type="submit" class="btn btn-outline-success text-uppercase font-weight-bold" value="Enviar datos" style="width: 100%">
                         </div>
                     </form>
                 </div>
             </div>
         </div>
         @endauth
        @endforeach
    </div>

    {{-- Eventos Mensuales --}}
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4 text-white">Eventos mensuales</h2>
    <div class="row mt-2">
        @if($eventos->isEmpty())
         <p class="col-md-12 text-white">No has participado en ningún evento este mes</p>
        @endif
        @foreach ($eventos as $evento)
        <div class="col-md-4 pb-2">
            <div class="card d-flex flex-column text-center" style="background-color: #283b63; color: white; border: 2px solid #ffffff;">
                @if ($evento->image_mod)
                    <span style="width: 100%; min-height: 200px; background: url('data:image/png;base64,{{ $evento->image_mod }}') bottom center no-repeat; background-size: cover;"></span>
                @else
                    <span style="width: 100%; min-height: 200px; background: url('/storage/{{ $evento->imagen }}') bottom center no-repeat; background-size: cover;"></span>
                @endif
                <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                    <h3 style="font-weight: bold;">{{ $evento->name }}</h3>
                    <h3>{{ $evento->region->name }}</h3>
                    <p><event-date fecha="{{ $evento->date }}"></event-date></p>
                </div>
                <a href="{{ route('events.show', ['event' => $evento->id]) }}" class="d-block font-weight-bold text-uppercase pt-2 pb-2 text-center" style="text-decoration: none; color: white; width: 100%; background-color: #1e2a47; border-color: #ffffff;">
                    Ver evento
                </a>
            </div>
        </div>
        @endforeach
    </div>



</div>
@endif
@endsection
