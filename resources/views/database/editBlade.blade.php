@extends('layouts.app')

@section('styles')
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
    <h2>Editar Blade</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('blades.update', $blade->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nombre_takara">Nombre Takara</label>
                    <input type="text" name="nombre_takara" id="nombre_takara" class="form-control" value="{{ old('nombre_takara', $blade->nombre_takara) }}">
                </div>
                <div class="form-group">
                    <label for="nombre_hasbro">Nombre Hasbro</label>
                    <input type="text" name="nombre_hasbro" id="nombre_hasbro" class="form-control" value="{{ old('nombre_hasbro', $blade->nombre_hasbro) }}">
                </div>
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select name="tipo" id="tipo" class="form-control">
                        <option value="Ataque" {{ $blade->tipo == 'Ataque' ? 'selected' : '' }}>Ataque</option>
                        <option value="Balance" {{ $blade->tipo == 'Balance' ? 'selected' : '' }}>Balance</option>
                        <option value="Defensa" {{ $blade->tipo == 'Defensa' ? 'selected' : '' }}>Defensa</option>
                        <option value="Energía" {{ $blade->tipo == 'Energía' ? 'selected' : '' }}>Energía</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="giro">Giro</label>
                    <select name="giro" id="giro" class="form-control">
                        <option value="Ataque" {{ $blade->giro == 'Ataque' ? 'selected' : '' }}>Derecho</option>
                        <option value="Balance" {{ $blade->giro == 'Balance' ? 'selected' : '' }}>Izquierdo</option>
                        <option value="Defensa" {{ $blade->giro == 'Defensa' ? 'selected' : '' }}>Ambos</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" name="color" id="color" class="form-control" value="{{ old('color', $blade->color) }}">
                </div>
                <div class="form-group">
                    <label for="sistema">Sistema</label>
                    <select name="sistema" id="sistema" class="form-control">
                        <option value="BX" {{ $blade->sistema == 'BX' ? 'selected' : '' }}>BX</option>
                        <option value="UX" {{ $blade->sistema == 'UX' ? 'selected' : '' }}>UX</option>
                        <option value="CX" {{ $blade->sistema == 'CX' ? 'selected' : '' }}>CX</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="wave_hasbro">Wave Hasbro</label>
                    <input type="text" name="wave_hasbro" id="wave_hasbro" class="form-control" value="{{ old('wave_hasbro', $blade->wave_hasbro) }}">
                </div>
                <div class="form-group">
                    <label for="fecha_takara">Fecha de lanzamiento Takara</label>
                    <input type="date" name="fecha_takara" id="fecha_takara" class="form-control" value="{{ old('fecha_takara', $blade->fecha_takara) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_takara" id="marca_takara" class="form-check-input" {{ $blade->marca_takara ? 'checked' : '' }}>
                    <label class="form-check-label" for="marca_takara">Es de Takara Tomy</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_hasbro" id="marca_hasbro" class="form-check-input" {{ $blade->marca_hasbro ? 'checked' : '' }}>
                    <label class="form-check-label" for="marca_hasbro">Es de Hasbro</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="recolor" id="recolor" class="form-check-input" {{ $blade->recolor ? 'checked' : '' }}>
                    <label class="form-check-label" for="recolor">Recolor</label>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion', $blade->descripcion) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="analisis">Analisis</label>
                    <textarea name="analisis" id="analisis" class="form-control" rows="3">{{ old('analisis', $blade->analisis) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen</label>
                    <input type="file" name="imagen" id="imagen" class="form-control-file">
                    @if ($blade->imagen)
                    <img src="{{ $blade->imagen }}" alt="Blade Image" width="250" class="mt-2">
                    @endif
                </div>

                <div class="form-group">
                    <label for="tarjeta">Tarjeta</label>
                    <input type="file" name="tarjeta" id="tarjeta" class="form-control-file">
                    @if ($blade->tarjeta)
                    <img src="{{ $blade->tarjeta }}" alt="Blade Image" width="250" class="mt-2">
                    @endif
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="{{ route('database.indexPartes') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.1/tinymce.min.js"></script>

<script>
    tinymce.init({
        selector: 'textarea#descripcion, textarea#analisis',
        plugins: 'advlist autolink lists link charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        height: 300
    });
</script>
@endsection
