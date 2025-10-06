@extends('layouts.app')

@section('content')
<div class="container text-white">
    <h1>Nuevo producto</h1>
    <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Seccion</label>
            <input type="text" name="seccion" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control"></textarea>
        </div>
        <div id="colores-container">
            <label>Colores</label>
            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="addColor()">+ Añadir color</button>
        </div>

        <div id="tallas-container">
            <label>Tallas</label>
            <button type="button" class="btn btn-sm btn-primary mb-2" onclick="addTalla()">+ Añadir talla</button>
        </div>
        <div class="mb-3">
            <label>Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Foto</label>
            <input type="file" name="fotos" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
function addColor() {
    let container = document.getElementById('colores-container');
    let index = container.querySelectorAll('.color-item').length;
    let div = document.createElement('div');
    div.classList.add('color-item', 'mb-2');
    div.innerHTML = `
        <input type="text" name="colores[${index}][nombre]" placeholder="Nombre del color" class="form-control mb-1">
        <input type="file" name="colores[${index}][foto]" class="form-control">
    `;
    container.appendChild(div);
}

function addTalla() {
    let container = document.getElementById('tallas-container');
    let index = container.querySelectorAll('input').length;
    let input = document.createElement('input');
    input.type = "text";
    input.name = "tallas[" + index + "]";
    input.placeholder = "Talla (ej: S, M, L)";
    input.classList.add('form-control','mb-2');
    container.appendChild(input);
}
</script>
@endsection
