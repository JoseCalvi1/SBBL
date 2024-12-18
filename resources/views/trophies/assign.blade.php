@extends('layouts.app')

@section('content')
<a href="{{ route('trophies.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>
<h2 class="text-center text-light mb-5">Asignar Trofeo a Usuario</h2>

<div class="row justify-content-center">
    <div class="col-md-8">
        <form method="POST" action="{{ route('trophies.assign') }}" class="bg-dark p-4 rounded shadow">
            @csrf

            <!-- Seleccionar Usuario -->
            <div class="form-group">
                <label for="user_id" class="text-light">Seleccionar Usuario</label>
                <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
                    <option disabled selected>- Selecciona -</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Seleccionar Trofeo -->
            <div class="form-group">
                <label for="trophy_id" class="text-light">Seleccionar Trofeo</label>
                <select name="trophy_id" id="trophy_id" class="form-control @error('trophy_id') is-invalid @enderror">
                    <option disabled selected>- Selecciona -</option>
                    @foreach($trophies as $trophy)
                        <option value="{{ $trophy->id }}">{{ $trophy->name }}</option>
                    @endforeach
                </select>
                @error('trophy_id')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Botón de asignar -->
            <div class="form-group">
                <button type="submit" class="btn btn-info btn-block">Asignar Trofeo</button>
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

    .btn-info {
        background-color: #3498db;
        border-color: #3498db;
    }

    .btn-info:hover {
        background-color: #2980b9;
        border-color: #2980b9;
    }
</style>
@endsection
