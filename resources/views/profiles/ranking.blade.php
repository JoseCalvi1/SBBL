@extends('layouts.app')

@section('content')

    <div class="container">
        <!-- Agregar un campo de filtro -->
        <div class="row justify-content-center">
            <div class="col-md-12 pl-5 pr-5">
                <h2 class="titulo-categoria text-uppercase mt-2" style="color:white">Filtrar por región:</h2>
                <input type="text" id="filtroRegion" class="form-control" placeholder="Filtrar por región...">
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="titulo-categoria text-uppercase m-4" style="color:white">Beyblade X</h2>

                <div class="clasificacion" id="clasificacion_x1">
                    <div class="item">
                        <span class="posicion">#</span>
                        <span>Blader</span>
                        <span>Región</span>
                        <span>Puntos</span>
                    </div>
                    @foreach ($bladers_x1 as $index => $blader)
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span>{{ $blader->user->name }}</span>
                            <span>{{ ($blader->region) ? $blader->region->name : 'Región desconocida' }}</span>
                            <span>{{ $blader->points_x1 }} puntos</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-md-6">
                <h2 class="titulo-categoria text-uppercase m-4" style="color:white">Beyblade Burst</h2>

                <div class="clasificacion" id="clasificacion_s3">
                    <div class="item">
                        <span class="posicion">#</span>
                        <span>Blader</span>
                        <span>Región</span>
                        <span>Puntos</span>
                    </div>
                    @foreach ($bladers_s3 as $index => $blader)
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span>{{ $blader->user->name }}</span>
                            <span>{{ ($blader->region) ? $blader->region->name : 'Región desconocida' }}</span>
                            <span>{{ $blader->points_s3 }} puntos</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filtroRegion').on('input', function() {
                var filtro = $(this).val().toLowerCase().trim();

                // Filtrar Beyblade X S1
                $('#clasificacion_x1 .item').each(function() {
                    var region = $(this).find('span:nth-child(3)').text().toLowerCase().trim();
                    if (region.includes(filtro)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                // Filtrar Beyblade Burst S3
                $('#clasificacion_s3 .item').each(function() {
                    var region = $(this).find('span:nth-child(3)').text().toLowerCase().trim();
                    if (region.includes(filtro)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        });
    </script>
@endsection

@section('styles')
    <style>
        .clasificacion {
            border-radius: 10px;
            padding: 20px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .posicion {
            font-weight: bold;
            font-size: 20px;
        }

        .resaltado {
            background-color: #ffd700;
        }

        body {
            background-color: #f0f0f0;
        }
    </style>
@endsection
