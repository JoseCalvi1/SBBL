@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/tinymce@5.10.2/themes/silver/theme.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container pt-2 pb-2">
        <a href="{{ route('blog.index') }}" class="btn btn-outline-primary mt-2 mb-2 ml-0 text-uppercase font-weight-bold">
            Volver
        </a>

        <h1 style="color: white;">Crear Nuevo Artículo</h1>

        <form action="{{ route('blog.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title" style="color: white;">Título:</label>
                <input type="text" class="form-control" id="title" name="title">
            </div>

            <div class="form-group" style="color: white;">
                <label for="image">Imagen de cabecera:</label>
                <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-group">
                <label for="description" style="color: white;">Descripción:</label>
                <!-- Utiliza un textarea simple para que TinyMCE pueda actuar sobre él -->
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>

            <div class="form-group">
                <label for="article_type" style="color: white;">Tipo de Artículo:</label>
                <input type="text" class="form-control" id="article_type" name="article_type">
            </div>

            <div class="form-group">
                <label for="custom_url" style="color: white;">URL Personalizada:</label>
                <input type="text" class="form-control" id="custom_url" name="custom_url" value="{{ isset($article) ? $article->custom_url : '' }}">
            </div>

            <button type="submit" class="btn btn-primary">Crear Artículo</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.2/tinymce.min.js"></script>
    <script>
        // Inicializa TinyMCE en el campo de descripción
        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: '#description',
                height: 300,
                plugins: 'lists link',
                toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link',
            });
        });
    </script>
@endsection
