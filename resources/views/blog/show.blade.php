@extends('layouts.app')


@section('content')
<div class="m-4" style="color: white">
    <a href="{{ route('blog.index') }}">Volver al listado de artículos</a>
    @if ((Auth::user() && Auth::user()->is_admin))
        <a href="{{ route('blog.edit', $article->id) }}" class="btn btn-primary">Editar Artículo</a>
    @endif
    <h1>{{ $article->title }}</h1>
    <p><strong>Tipo:</strong> {{ $article->article_type }}</p>
    <p><strong>Publicado:</strong> {{ $article->created_at->format('d/m/Y') }}</p>
    <p>{!! $article->description !!}</p>
</div>
@endsection
