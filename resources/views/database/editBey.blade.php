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
    <h2>Editar Beyblade</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('beyblades.update', $beyblade->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select name="tipo" id="tipo" class="form-control">
                        <option value="Ataque" {{ $beyblade->tipo == 'Ataque' ? 'selected' : '' }}>Ataque</option>
                        <option value="Balance" {{ $beyblade->tipo == 'Balance' ? 'selected' : '' }}>Balance</option>
                        <option value="Defensa" {{ $beyblade->tipo == 'Defensa' ? 'selected' : '' }}>Defensa</option>
                        <option value="Energía" {{ $beyblade->tipo == 'Energía' ? 'selected' : '' }}>Energía</option>
                    </select>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_takara" id="marca_takara" class="form-check-input" {{ $beyblade->marca_takara ? 'checked' : '' }}>
                    <label class="form-check-label" for="marca_takara">Es de Takara Tomy</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_hasbro" id="marca_hasbro" class="form-check-input" {{ $beyblade->marca_hasbro ? 'checked' : '' }}>
                    <label class="form-check-label" for="marca_hasbro">Es de Hasbro</label>
                </div>
                <div class="form-group">
                    <label for="blade_id">Blade</label>
                    <select name="blade_id" id="blade_id" class="form-control">
                        @foreach($blades as $blade)
                            <option value="{{ $blade->id }}" {{ $beyblade->blade_id == $blade->id ? 'selected' : '' }}>{{ $blade->nombre_takara }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="ratchet_id">Ratchet</label>
                    <select name="ratchet_id" id="ratchet_id" class="form-control">
                        @foreach($ratchets as $ratchet)
                            <option value="{{ $ratchet->id }}" {{ $beyblade->ratchet_id == $ratchet->id ? 'selected' : '' }}>{{ $ratchet->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="bit_id">Bit</label>
                    <select name="bit_id" id="bit_id" class="form-control">
                        @foreach($bits as $bit)
                            <option value="{{ $bit->id }}" {{ $beyblade->bit_id == $bit->id ? 'selected' : '' }}>{{ $bit->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion', $beyblade->descripcion) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="analisis">Análisis</label>
                    <textarea name="analisis" id="analisis" class="form-control" rows="3">{{ old('analisis', $beyblade->analisis) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen</label>
                    <input type="file" name="imagen" id="imagen" class="form-control-file">
                    @if ($beyblade->imagen)
                    <img src="{{ $beyblade->imagen }}" alt="Beyblade imagen" width="250" class="mt-2">
                    @endif
                </div>
                <div class="form-group">
                    <label for="tarjeta">Tarjeta</label>
                    <input type="file" name="tarjeta" id="tarjeta" class="form-control-file">
                    @if ($beyblade->tarjeta)
                    <img src="{{ $beyblade->tarjeta }}" alt="Beyblade tarjeta" width="250" class="mt-2">
                    @endif
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="{{ route('database.indexBeys') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
