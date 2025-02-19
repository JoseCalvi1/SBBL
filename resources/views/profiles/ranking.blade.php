@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <!-- Filtros de región y límite de resultados -->
            <div class="col-md-6">
                <label for="limitSelect" class="text-white">Mostrar:</label>
                <select id="limitSelect" class="form-control bg-dark text-white" onchange="applyFilters()">
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                    <option value="200" {{ $limit == 200 ? 'selected' : '' }}>200</option>
                    <option value="500" {{ $limit == 500 ? 'selected' : '' }}>500</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="regionSelect" class="text-white">Filtrar por región:</label>
                <select id="regionSelect" class="form-control bg-dark text-white" onchange="applyFilters()">
                    <option value="">Todas las regiones</option>
                    @foreach ($regions as $regionOption)
                        <option value="{{ $regionOption }}" {{ $region == $regionOption ? 'selected' : '' }}>
                            {{ $regionOption }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Clasificación de Beyblade X -->
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h2 class="titulo-categoria text-uppercase m-4" style="color:white">Beyblade X</h2>
                <div class="clasificacion" id="clasificacion_x1">
                    <div class="item encabezado">
                        <span class="posicion">#</span>
                        <span></span>
                        <span>Blader</span>
                        <span>Región</span>
                        <span>Puntos</span>
                    </div>
                    @foreach ($bladers_x1 as $index => $blader)
                        @php
                            // Determinar la clase CSS según el nivel de suscripción
                            $firstTrophyName = $blader->trophies->first()->name ?? '';
                            switch ($firstTrophyName) {
                                case 'SUSCRIPCIÓN NIVEL 3':
                                    $subscriptionClass = 'suscripcion-nivel-3';
                                    break;
                                case 'SUSCRIPCIÓN NIVEL 2':
                            $subscriptionClass = 'suscripcion-nivel-2';
                            break;
                        case 'SUSCRIPCIÓN NIVEL 1':
                            $subscriptionClass = 'suscripcion-nivel-1';
                            break;
                                default:
                                    $subscriptionClass = '';
                                    break;
                            }
                        @endphp
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span class="profile-container">
                                <div class="blader-avatar d-none d-sm-block">
                                    @if ($blader->imagen)
                                        <img src="/storage/{{ $blader->imagen }}" class="blader-image">
                                    @else
                                        <img src="/storage/upload-profiles/Base/DranDagger.webp" class="blader-image">
                                    @endif
                                    @if ($blader->marco)
                                        <img src="/storage/{{ $blader->marco }}" class="blader-frame">
                                    @else
                                        <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="blader-frame">
                                    @endif
                                </div>
                            </span>

                            <span class="{{ $subscriptionClass }}">{{ $blader->user->name }}</span>
                            <span>{{ $blader->region ? $blader->region->name : 'Región desconocida' }}</span>
                            <span>{{ $blader->points_x1 }} puntos</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Clasificación de Beyblade Burst -->
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h2 class="titulo-categoria text-uppercase m-4" style="color:white">Beyblade Burst</h2>
                <div class="clasificacion" id="clasificacion_s3">
                    <div class="item encabezado">
                        <span class="posicion">#</span>
                        <span>Blader</span>
                        <span>Región</span>
                        <span>Puntos</span>
                    </div>
                    @foreach ($bladers_s3 as $index => $blader)
                        @php
                            // Determinar la clase CSS según el nivel de suscripción
                            $firstTrophyName = $blader->trophies->first()->name ?? '';
                            switch ($firstTrophyName) {
                                case 'SUSCRIPCIÓN NIVEL 3':
                                    $subscriptionClass = 'suscripcion-nivel-3';
                                    break;
                                case 'SUSCRIPCIÓN NIVEL 2':
                                case 'SUSCRIPCIÓN NIVEL 1':
                                    $subscriptionClass = 'suscripcion';
                                    break;
                                default:
                                    $subscriptionClass = '';
                                    break;
                            }
                        @endphp
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span class="{{ $subscriptionClass }}">{{ $blader->user->name }}</span>
                            <span>{{ $blader->region ? $blader->region->name : 'Región desconocida' }}</span>
                            <span>{{ $blader->points_s3 }} puntos</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function applyFilters() {
            const limit = document.getElementById('limitSelect').value;
            const region = document.getElementById('regionSelect').value;
            const url = `?limit=${limit}&region=${region}`;
            window.location.href = url;
        }
    </script>
@endsection

@section('styles')
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }

        .clasificacion {
            border-radius: 10px;
            padding: 20px;
            background-color: #1e1e1e;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #2a2a2a;
            color: #e0e0e0;
        }

        .encabezado {
            font-weight: bold;
            background-color: #333;
            color: #fff;
        }

        .item span.posicion {
            flex: 1;
            text-align: center;
            color: #ffca28;
        }

        .item span:nth-child(2) {
            flex: 1;
            text-align: center;
        }

        .item span:nth-child(3) {
            flex: 4;
        }

        .item span:nth-child(4) {
            flex: 3;
        }

        .item span:nth-child(5) {
            flex: 3;
            text-align: center;
        }

        .resaltado {
            background-color: #424242;
        }

        .titulo-categoria {
            color: #ffca28;
        }

        .form-control {
            background-color: #333;
            color: #e0e0e0;
            border: 1px solid #555;
        }

        .form-control:focus {
            border-color: #ffca28;
            box-shadow: none;
        }

        /* Clases de color según el nivel de suscripción */
        .suscripcion-nivel-3 {
            color: gold;
            font-weight: bold;
        }

        .suscripcion-nivel-2 {
            color: #c0e5fb;
            font-weight: bold;
        }

        .suscripcion-nivel-1 {
            color: #CD7F32; /* Bronce */
            font-weight: bold;
        }
        .profile-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px; /* Tamaño del contenedor */
            height: 50px;
        }

        .blader-avatar {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .blader-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .blader-frame {
            width: 50px;
            height: 50px;
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 50%;
            z-index: 2; /* Asegura que el marco está encima */
        }

    </style>
@endsection
