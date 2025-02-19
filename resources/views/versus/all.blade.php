@extends('layouts.app')

@section('styles')
    <style>
        /* Estilos personalizados para los selects */
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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

        <!-- Filtro de duelos -->
        <form method="GET" action="{{ route('versus.all') }}" class="w-100">
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="userFilter" class="text-light">Filtrar por Usuario</label>
                        <select name="user" id="userFilter" class="form-control select2 bg-dark text-white border-secondary">
                            <option value="">Seleccione un usuario</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="statusFilter" class="text-light">Filtrar por Estado</label>
                        <select name="status" id="statusFilter" class="form-control select2 bg-dark text-white border-secondary">
                            <option value="">Seleccione un estado</option>
                            <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>Abierto</option>
                            <option value="CLOSED" {{ request('status') == 'CLOSED' || is_null(request('status')) ? 'selected' : '' }}>Cerrado</option>
                        </select>
                    </div>
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
                            <span class="mode">{{ ($duelo->matchup == "beybladex") ? "Beyblade X" : "Beyblade Burst"  }}{{ $duelo->status == "CLOSED" ? " - Válido" : ($duelo->status == "INVALID" ? " - Inválido" : " - Enviado") }}</span>
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
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('scripts')
    <!-- CDN de jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CDN de Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        jQuery(document).ready(function() {
            jQuery('.select2').select2();
        });
    </script>
@endsection
