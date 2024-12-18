@extends('layouts.app')

@section('content')
<a href="{{ route('trophies.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>
<h2 class="text-center text-light mb-5">Añadir Nuevo Trofeo</h2>

<div class="row justify-content-center">
    <div class="col-md-8">
        <form method="POST" action="{{ route('trophies.store') }}" class="bg-dark p-4 rounded shadow">
            @csrf

            <!-- Nombre del Trofeo -->
            <div class="form-group">
                <label for="name" class="text-light">Nombre del Trofeo</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                @error('name')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Temporada del Trofeo -->
            <div class="form-group">
                <label for="season" class="text-light">Temporada</label>
                <input type="text" name="season" id="season" class="form-control @error('season') is-invalid @enderror" value="{{ old('season') }}">
                @error('season')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Botón de guardar -->
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-block">Guardar Trofeo</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    body {
        background-color: #121212;
        color: #e0e0e0;
    }

    .form-control {
        background-color: #2c2c2c;
        border-color: #444;
        color: #fff;
    }

    .form-control:focus {
        background-color: #444;
        border-color: #f39c12;
        color: #fff;
    }

    .btn-success {
        background-color: #2ecc71;
        border-color: #2ecc71;
    }

    .btn-success:hover {
        background-color: #27ae60;
        border-color: #27ae60;
    }
</style>
@endsection
