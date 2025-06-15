@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/tinymce@5.10.2/themes/silver/theme.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container pt-2 pb-2">
        <a href="{{ route('blog.index') }}" class="btn btn-outline-primary mt-2 mb-2 ml-0 text-uppercase font-weight-bold">
            Volver
        </a>

        <h1 style="color: white;">Crear Nuevo Post</h1>

        {{-- Mostrar errores de validación --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <h5 class="mb-2">Se encontraron los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <form action="{{ route('blog.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title" style="color: white;">Título:</label>
                <input type="text" class="form-control" id="title" name="title" oninput="formatearParaURL()">
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
                <label for="article_type" style="color: white;">Tipo de post:</label>
                <select name="article_type" id="article_type" class="form-control">
                    <option value="">-- Todos --</option>
                    <option value="Borrador" {{ request('article_type') == 'Borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="Competitivo" {{ request('article_type') == 'Competitivo' ? 'selected' : '' }}>Competitivo</option>
                    <option value="Conoce a nuestros bladers" {{ request('article_type') == 'Conoce a nuestros bladers' ? 'selected' : '' }}>Conoce a nuestros bladers</option>
                    <option value="Curiosidades" {{ request('article_type') == 'Curiosidades' ? 'selected' : '' }}>Curiosidades</option>
                    <option value="Guía" {{ request('article_type') == 'Guía' ? 'selected' : '' }}>Guía</option>
                    <option value="El Giro Semanal" {{ request('article_type') == 'El Giro Semanal' ? 'selected' : '' }}>El Giro Semanal</option>
                    <option value="Eventos especiales" {{ request('article_type') == 'Eventos especiales' ? 'selected' : '' }}>Eventos especiales</option>
                    <option value="Guía de compra" {{ request('article_type') == 'Guía de compra' ? 'selected' : '' }}>Guía de compra</option>
                    <option value="Noticias" {{ request('article_type') == 'Noticias' ? 'selected' : '' }}>Noticias</option>
                </select>
            </div>

            <div class="form-group">
                <label for="custom_url" style="color: white;">URL Personalizada:</label>
                <input type="text" class="form-control" id="custom_url" name="custom_url" value="{{ isset($article) ? $article->custom_url : '' }}">
            </div>

            <button type="submit" class="btn btn-primary">Crear post</button>
        </form>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@5.10.2/tinymce.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#description',
            height: 300,
            plugins: 'lists link image code',
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link image | code',
            automatic_uploads: true,
            images_upload_handler: function (blobInfo, success, failure) {
                // Convierte la imagen en base64 e inserta directamente
                success("data:" + blobInfo.blob().type + ";base64," + blobInfo.base64());
            },
            file_picker_types: 'image',
            file_picker_callback: function (cb, value, meta) {
                if (meta.filetype === 'image') {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.onchange = function () {
                        const file = this.files[0];
                        const reader = new FileReader();

                        reader.onload = function () {
                            cb(reader.result, { title: file.name });
                        };

                        reader.readAsDataURL(file);
                    };

                    input.click();
                }
            }
        });
    });

        function formatearParaURL() {
            const inputText = document.getElementById('title').value;
            const formattedText = inputText
                .normalize("NFD") // Elimina tildes
                .replace(/[\u0300-\u036f]/g, "") // Elimina los caracteres diacríticos
                .replace(/[^a-zA-Z0-9\s]/g, "") // Elimina caracteres especiales
                .trim() // Elimina espacios al principio y al final
                .replace(/\s+/g, '-') // Reemplaza espacios por guiones
                .toLowerCase(); // Convierte a minúsculas
            document.getElementById('custom_url').value = formattedText;
        }

    </script>
@endsection
