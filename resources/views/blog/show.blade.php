@extends('layouts.app')

@section('title', $article->title)

@section('styles')
    <style>
        .article-content img {
            max-width: 100%;
            height: auto;
            margin: 1rem auto;
        }
    </style>
@endsection

@section('content')
<div class="container my-4 text-white">

    {{-- Navegación superior responsive --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <a href="{{ route('blog.index') }}" class="text-decoration-none text-info">
            ← Volver al listado de posts
        </a>

        @if (Auth::user() && (Auth::user()->is_referee || Auth::user()->id == $article->user_id || in_array(Auth::user()->id, [301, 513])))
            <a href="{{ route('blog.edit', $article->id) }}" class="btn btn-primary mt-2 mt-md-0">
                Editar post
            </a>
        @endif
    </div>

    {{-- Título --}}
    <h1 class="mb-3 text-center text-md-left">{{ $article->title }}</h1>

    {{-- Imagen del artículo --}}
    @if ($article->image)
        <div class="text-center">
            <img src="data:image/png;base64,{{ $article->image }}" alt="Imagen del artículo" class="img-fluid rounded mb-4">
        </div>
    @endif

    {{-- Información del artículo --}}
    <div class="mb-3 small text-muted">
        <p class="mb-1"><strong class="text-white">Tipo:</strong> {{ $article->article_type }}</p>
        <p class="mb-0"><strong class="text-white">Publicado:</strong> {{ $article->created_at->format('d/m/Y') }}</p>
    </div>

    {{-- Contenido --}}
    <div class="article-content">
        {!! $article->description !!}
    </div>
</div>
@endsection

@section('script')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
