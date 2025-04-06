@extends('layouts.app')

@section('styles')
    <style>
        .select2-container--default .select2-selection--single {
            height: calc(1.6em + 0.75rem + 2px) !important;
            background-color: #343a40 !important;
            color: white !important;
            border: 1px solid #6c757d !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: white !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            border-color: #6c757d !important;
        }

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container">
    <h2 class="titulo-categoria text-uppercase mb-4 mt-4" style="color:white">Duelos
        @auth
            <a href="{{ route('versus.create') }}" class="btn btn-outline-warning mb-2 text-uppercase font-weight-bold">
                Crear duelo
            </a>
        @endauth
    </h2>

    <form method="GET" action="{{ route('versus.all') }}" class="w-100">
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label for="userFilter" class="text-light">Filtrar por Usuario</label>
                <select name="user" id="userFilter" class="form-control select2 bg-dark text-white border-secondary">
                    <option value="">Seleccione un usuario</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-2">
                <label for="statusFilter" class="text-light">Filtrar por Estado</label>
                <select name="status" id="statusFilter" class="form-control select2 bg-dark text-white border-secondary">
                    <option value="">Seleccione un estado</option>
                    <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>Abierto</option>
                    <option value="CLOSED" {{ request('status') == 'CLOSED' || is_null(request('status')) ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>

            <div class="col-md-4 mb-2">
                <button type="submit" style="height: -webkit-fill-available;" class="btn btn-outline-light w-100">Aplicar Filtros</button>
            </div>
        </div>
    </form>

    <div class="row mt-2">
        @foreach ($versus as $duelo)
            <div class="col-md-3 mb-3">
                <div class="duel-card" style="background-image: url('/storage/{{ $duelo->result_1 > $duelo->result_2 ? $duelo->versus_1->profile->fondo : $duelo->versus_2->profile->fondo }}');">
                    <div class="overlay"></div>
                    <div class="duel-mode">
                        <span class="mode">
                            {{ $duelo->matchup == "beybladex" ? "Beyblade X" : "Beyblade Burst" }}
                            @if ($duelo->status == "CLOSED") - Válido
                            @elseif ($duelo->status == "INVALID") - Inválido
                            @elseif ($duelo->status == "OPEN" && $duelo->url) - Pendiente
                            @else - Enviado
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

                        @auth
                            @if ($duelo->user_id_1 == Auth::id() || $duelo->user_id_2 == Auth::id())
                                <a href="{{ route('versus.versusdeck', ['duel' => $duelo->id, 'deck' => Auth::id()]) }}" class="btn btn-warning w-100">Introducir deck</a>
                                <button type="button" class="btn btn-outline-light w-100 mt-2" data-toggle="modal" data-target="#modalDeck{{ $duelo->id }}">
                                    Vídeo
                                </button>
                            @endif
                        @endauth
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
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        jQuery(document).ready(function() {
            jQuery('.select2').select2();
        });
    </script>
@endsection
