@extends('layouts.app')

@section('content')

<a href="{{ route('teams_versus.all') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5 text-white">Añadir nuevo duelo de equipos</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <form method="POST" action="{{ route('teams_versus.store') }}" enctype="multipart/form-data" novalidate style="color: white">
            @csrf
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="form-group">
                <label for="team_id_1">Equipo 1</label><br>
                <select name="team_id_1" id="team_id_1" class="form-control select2 @error('team_id_1') is-invalid @enderror">
                    <option disabled selected>- Selecciona -</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                @error('team_id_1')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{$message}}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="result_1">Resultado equipo 1</label>
                <input type="number"
                       name="result_1"
                       class="form-control @error('result_1') is-invalid @enderror"
                       id="result_1"
                       placeholder="0"
                       value="{{ old('result_1') }}"
                />
                @error('result_1')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{$message}}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="team_id_2">Equipo 2</label>
                <select name="team_id_2" id="team_id_2" class="form-control select2 @error('team_id_2') is-invalid @enderror">
                    <option disabled selected>- Selecciona -</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                @error('team_id_2')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{$message}}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="result_2">Resultado equipo 2</label>
                <input type="number"
                       name="result_2"
                       class="form-control @error('result_2') is-invalid @enderror"
                       id="result_2"
                       placeholder="0"
                       value="{{ old('result_2') }}"
                />
                @error('result_2')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{$message}}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="modalidad">Modalidad</label>
                <select name="modalidad" id="modalidad" class="form-control @error('modalidad') is-invalid @enderror">
                    <option selected value="beybladex">Beyblade X</option>
                </select>
                @error('modalidad')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{$message}}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Agregar duelo">
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
    <!-- CDN de jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CDN de Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        jQuery(document).ready(function() {
            jQuery('.select2').select2();
        });
    </script>
@endsection
