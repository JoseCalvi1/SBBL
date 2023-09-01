@extends('layouts.app')

@section('content')

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h2 class="titulo-categoria text-uppercase m-4">Beyblade X S1</h2>

                    <div class="clasificacion">
                        <div class="item">
                            <span class="posicion">#</span>
                            <span>Blader</span>
                            <span>Puntos</span>
                        </div>
                        @foreach ($bladers_x1 as $index => $blader)
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span>{{ $blader->user->name }}</span>
                            <span>{{ $blader->points_x1 }} puntos</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-md-6">
                    <h2 class="titulo-categoria text-uppercase m-4">Beyblade Burst S3</h2>

                    <div class="clasificacion">
                        <div class="item">
                            <span class="posicion">#</span>
                            <span>Blader</span>
                            <span>Puntos</span>
                        </div>
                        @foreach ($bladers_s3 as $index => $blader)
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>
                            <span>{{ $blader->user->name }}</span>
                            <span>{{ $blader->points_s3 }} puntos</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
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
