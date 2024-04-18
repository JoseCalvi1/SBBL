@extends('layouts.app')

@section('content')
<h2 class="text-center mt-2 mb-2" style="color: white">Artículos del Blog
    @if ((Auth::user() && Auth::user()->is_admin))
        <a href="{{ route('blog.create') }}" class="btn btn-outline-warning text-uppercase font-weight-bold">
            Crear artículo
        </a>
    @endif
</h2>

    @foreach($articles as $article)
        <div class="m-4" style="color: white">
            <h2>{{ $article->title }}</h2>
            <p><strong>Tipo:</strong> {{ $article->article_type }}</p>
            <p><strong>Publicado:</strong> {{ $article->created_at->format('d/m/Y') }}</p>
            <a href="{{ route('blog.show', $article->id) }}">Ver artículo</a>
        </div>
        <hr>
    @endforeach
@endsection
