@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    h2 {
        color: #ffffff;
    }
    label {
        font-weight: bold;
        color: #ffffff;
    }
    .form-control, .form-control-file, .form-check-input {
        background-color: #2a2a2a;
        color: #ffffff;
        border: 1px solid #ffffff;
    }
    .form-control:focus {
        background-color: #333333;
        color: #ffffff;
        border-color: #ffffff;
        box-shadow: 0 0 5px rgba(255, 204, 0, 0.5);
    }
    .btn-primary {
        background-color: #ffffff;
        border-color: #ffffff;
        color: #000;
    }
    .btn-primary:hover {
        background-color: #ffffff;
        border-color: #ffffff;
    }
    .btn-secondary {
        background-color: #444;
        border-color: #444;
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #666;
        border-color: #666;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <h2>Editar Ratchet</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ratchets.update', $ratchet->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $ratchet->nombre) }}">
                </div>
                <div class="form-group">
                    <label for="altura">Altura</label>
                    <input type="number" name="altura" id="altura" class="form-control" value="{{ old('altura', $ratchet->altura) }}">
                </div>
                <div class="form-group">
                    <label for="num_salientes">Número de salientes</label>
                    <input type="number" name="num_salientes" id="num_salientes" class="form-control" value="{{ old('num_salientes', $ratchet->num_salientes) }}">
                </div>
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" name="color" id="color" class="form-control" value="{{ old('color', $ratchet->color) }}">
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion', $ratchet->descripcion) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="analisis">Analisis</label>
                    <textarea name="analisis" id="analisis" class="form-control" rows="3">{{ old('analisis', $ratchet->analisis) }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_takara" id="marca_takara" class="form-check-input" {{ $ratchet->marca_takara ? 'checked' : '' }}>
                    <label class="form-check-label" for="marca_takara">Es de Takara Tomy</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_hasbro" id="marca_hasbro" class="form-check-input" {{ $ratchet->marca_hasbro ? 'checked' : '' }}>
                    <label class="form-check-label" for="marca_hasbro">Es de Hasbro</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="recolor" id="recolor" class="form-check-input" {{ $ratchet->recolor ? 'checked' : '' }}>
                    <label class="form-check-label" for="recolor">Recolor</label>
                </div>
                <div class="form-group">
                    <label for="wave_hasbro">Wave Hasbro</label>
                    <input type="text" name="wave_hasbro" id="wave_hasbro" class="form-control" value="{{ old('wave_hasbro', $ratchet->wave_hasbro) }}">
                </div>
                <div class="form-group">
                    <label for="fecha_takara">Fecha de lanzamiento Takara</label>
                    <input type="date" name="fecha_takara" id="fecha_takara" class="form-control" value="{{ old('fecha_takara', $ratchet->fecha_takara) }}">
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen</label>
                    <input type="file" name="imagen" id="imagen" class="form-control-file">
                    @if ($ratchet->imagen)
                    <img src="{{ $ratchet->imagen }}" alt="Ratchet Image" width="250" class="mt-2">
                    @endif
                </div>
                <div class="form-group">
                    <label for="tarjeta">Tarjeta</label>
                    <input type="file" name="tarjeta" id="tarjeta" class="form-control-file">
                    @if ($ratchet->tarjeta)
                    <img src="{{ $ratchet->tarjeta }}" alt="Rachet Image" width="250" class="mt-2">
                    @endif
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="{{ route('database.indexPartes') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
