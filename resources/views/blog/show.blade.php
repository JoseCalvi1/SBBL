@extends('layouts.app')

@section('title', $article->title)

@section('content')
<div class="container my-4 text-white">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('blog.index') }}" class="text-decoration-none text-info">
            ← Volver al listado de posts
        </a>
        @if (Auth::user() && Auth::user()->is_admin)
            <a href="{{ route('blog.edit', $article->id) }}" class="btn btn-primary">
                Editar post
            </a>
        @endif
    </div>

    <h1 class="mb-3">{{ $article->title }}</h1>

    @if ($article->image)
        <img src="data:image/png;base64,{{ $article->image }}" alt="Imagen del artículo" class="img-fluid rounded mb-4">
    @endif

    <div class="mb-3 small text-muted">
        <p><strong class="text-white">Tipo:</strong> {{ $article->article_type }}</p>
        <p><strong class="text-white">Publicado:</strong> {{ $article->created_at->format('d/m/Y') }}</p>
    </div>

    <div class="article-content">
        {!! $article->description !!}
    </div>
</div>

@endsection

@section('script')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
