@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/tinymce@5.10.2/themes/silver/theme.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container pt-2 pb-2">
        <h1 style="color: white;">Editar Artículo</h1>

        <form action="{{ route('blog.update', $article->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="title" style="color: white;">Título:</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $article->title }}">
            </div>

            <div class="form-group">
                <label for="description" style="color: white;">Descripción:</label>
                <!-- Utiliza un textarea simple para que TinyMCE pueda actuar sobre él -->
                <textarea class="form-control" id="description" name="description">{{ $article->description }}</textarea>
            </div>

            <div class="form-group">
                <label for="article_type" style="color: white;">Tipo de Artículo:</label>
                <input type="text" class="form-control" id="article_type" name="article_type" value="{{ $article->article_type }}">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Artículo</button>
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
