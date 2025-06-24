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
    <h2>Crear Beyblade</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('beyblades.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control">
                            <option>-- Selecciona --</option>
                            <option value="Ataque">Ataque</option>
                            <option value="Balance">Balance</option>
                            <option value="Defensa">Defensa</option>
                            <option value="Energía">Energía</option>
                        </select>
                    </div>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_takara" id="marca_takara" class="form-check-input">
                    <label class="form-check-label" for="marca_takara">Es de Takara Tomy</label>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" name="marca_hasbro" id="marca_hasbro" class="form-check-input">
                    <label class="form-check-label" for="marca_hasbro">Es de Hasbro</label>
                </div>
                <div class="form-group">
                    <label for="blade_id">Blade</label>
                    <select name="blade_id" id="blade_id" class="form-control">
                        <option>-- Selecciona --</option>
                        @foreach ($blades as $blade)
                            <option value="{{ $blade->id }}">{{ ($blade->nombre_takara) ? $blade->nombre_takara : $blade->nombre_hasbro }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="ratchet_id">Ratchet</label>
                    <select name="ratchet_id" id="ratchet_id" class="form-control">
                        <option>-- Selecciona --</option>
                        @foreach ($ratchets as $ratchet)
                            <option value="{{ $ratchet->id }}">{{ $ratchet->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="bit_id">Bit</label>
                    <select name="bit_id" id="bit_id" class="form-control">
                        <option>-- Selecciona --</option>
                        @foreach ($bits as $bit)
                            <option value="{{ $bit->id }}">{{ $bit->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="analisis">Análisis</label>
                    <textarea name="analisis" id="analisis" class="form-control" rows="3">{{ old('analisis') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="imagen">Imagen</label>
                    <input type="file" name="imagen" id="imagen" class="form-control-file">
                </div>
                <div class="form-group">
                    <label for="tarjeta">Tarjeta</label>
                    <input type="file" name="tarjeta" id="tarjeta" class="form-control-file">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Crear Beyblade</button>
        <a href="{{ route('database.index') }}" class="btn btn-secondary">Cancelar</a>
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
