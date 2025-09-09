@extends('layouts.app')

@section('content')
<div class="container py-5 text-white">
    <h2 class="mb-4">Crear nuevo producto</h2>

    <form action="{{ route('tienda.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Nombre -->
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del producto</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="3" required></textarea>
        </div>

        <!-- Imágenes -->
        <div class="mb-3">
            <label for="fotos" class="form-label">Imágenes (puedes subir varias)</label>
            <input type="file" class="form-control" name="fotos[]" id="fotos" multiple accept="image/*">
        </div>

        <!-- Enlaces -->
        <div class="mb-3">
            <label for="enlaces" class="form-label">Enlaces (uno por línea)</label>
            <textarea class="form-control" name="enlaces" id="enlaces" rows="3" placeholder="https://ejemplo.com"></textarea>
        </div>

        <!-- Botón -->
        <button type="submit" class="btn btn-primary">Guardar producto</button>
    </form>
</div>
@endsection
