@extends('layouts.app')

@section('content')
<div class="container text-white">
    <h1>Editar producto</h1>
    <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Campos básicos -->
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Seccion</label>
            <input type="text" name="seccion" value="{{ old('seccion', $producto->seccion) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control">{{ old('descripcion', $producto->descripcion) }}</textarea>
        </div>

        <!-- Colores -->
        <div id="colores-container">
            <label>Colores</label>
            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="addColor()">+ Añadir color</button>

            @foreach($producto->colores ?? [] as $i => $color)
            <div class="color-item mb-2 border p-2 rounded">
                <input type="text" name="colores[{{ $i }}][nombre]" value="{{ $color['nombre'] ?? '' }}" placeholder="Nombre del color" class="form-control mb-1">
                <div class="mb-1">
                    <label>Imagen actual</label><br>
                    @if(!empty($color['foto']))
                        <img src="{{ $color['foto'] }}" width="100" alt="color foto">
                        <input type="hidden" name="colores[{{ $i }}][foto_actual]" value="{{ $color['foto'] }}">
                    @endif
                </div>

                <input type="file" name="colores[{{ $i }}][foto]" class="form-control mb-1">

                <div class="form-check">
                    <input type="checkbox" name="colores[{{ $i }}][_eliminar]" value="1" class="form-check-input" id="eliminar_color_{{ $i }}">
                    <label class="form-check-label" for="eliminar_color_{{ $i }}">Eliminar color</label>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tallas -->
        <div id="tallas-container">
            <label>Tallas</label>
            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="addTalla()">+ Añadir talla</button>

            @foreach($producto->tallas ?? [] as $i => $talla)
            <div class="talla-item mb-2 d-flex align-items-center">
                <input type="text" name="tallas[{{ $i }}]" value="{{ $talla }}" placeholder="Talla (ej: S, M, L)" class="form-control me-2">
                <div class="form-check">
                    <input type="checkbox" name="tallas[{{ $i }}][_eliminar]" value="1" class="form-check-input" id="eliminar_talla_{{ $i }}">
                    <label class="form-check-label" for="eliminar_talla_{{ $i }}">Eliminar</label>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Precio, stock, foto principal -->
        <div class="mb-3">
            <label>Precio</label>
            <input type="number" step="0.01" name="precio" value="{{ old('precio', $producto->precio) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Stock</label>
            <input type="number" name="stock" value="{{ old('stock', $producto->stock) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Foto principal</label><br>
            @if($producto->fotos)
                <img src="{{ $producto->fotos }}" width="120" alt="foto principal">
            @endif
            <input type="file" name="fotos" class="form-control mt-1">
        </div>

        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@section('scripts')
<script>
function addColor() {
    let container = document.getElementById('colores-container');
    let index = container.querySelectorAll('.color-item').length;
    let div = document.createElement('div');
    div.classList.add('color-item','mb-2','border','p-2','rounded');
    div.innerHTML = `
        <input type="text" name="colores[${index}][nombre]" placeholder="Nombre del color" class="form-control mb-1">
        <input type="file" name="colores[${index}][foto]" class="form-control mb-1">
        <button type="button" class="btn btn-sm btn-danger mt-1" onclick="this.parentElement.remove()">Eliminar</button>
    `;
    container.appendChild(div);
}

function addTalla() {
    let container = document.getElementById('tallas-container');
    let index = container.querySelectorAll('.talla-item').length;
    let div = document.createElement('div');
    div.classList.add('talla-item','mb-2','d-flex','align-items-center');
    div.innerHTML = `
        <input type="text" name="tallas[${index}]" placeholder="Talla (ej: S, M, L)" class="form-control me-2">
        <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">Eliminar</button>
    `;
    container.appendChild(div);
}
</script>
@endsection
