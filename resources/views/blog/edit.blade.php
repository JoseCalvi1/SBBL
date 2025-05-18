@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/tinymce@5.10.2/themes/silver/theme.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container pt-2 pb-2">
        <a href="{{ route('blog.show', ['custom_url' => $article->custom_url]) }}" class="btn btn-outline-primary mt-2 mb-2 ml-0 text-uppercase font-weight-bold">
            Volver
        </a>

        <h1 style="color: white;">Editar post</h1>

        <form action="{{ route('blog.update', $article->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="title" style="color: white;">Título:</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $article->title }}">
            </div>

            <div class="form-group" style="color: white;">
                <label for="image">Imagen de cabecera:</label>
                @if($article->image)
                    <label>Imagen actual:</label>
                    <img src="data:image/png;base64,{{ $article->image }}" width="100">
                @else
                    <p>No hay imagen actual</p>
                @endif
                <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
            </div>


            <div class="form-group">
                <label for="description" style="color: white;">Descripción:</label>
                <!-- Utiliza un textarea simple para que TinyMCE pueda actuar sobre él -->
                <textarea class="form-control" id="description" name="description">{{ $article->description }}</textarea>
            </div>

            <div class="form-group">
                <label for="article_type" style="color: white;">Tipo de post:</label>
                <input type="text" class="form-control" id="article_type" name="article_type" value="{{ $article->article_type }}">
            </div>

            <div class="form-group">
                <label for="custom_url" style="color: white;">URL Personalizada:</label>
                <input type="text" class="form-control" id="custom_url" name="custom_url" value="{{ isset($article) ? $article->custom_url : '' }}">
            </div>

            <button type="submit" class="btn btn-primary">Actualizar post</button>
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
                // Convierte la imagen a base64 y la inserta directamente
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
</script>
@endsection
