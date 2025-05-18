@extends('layouts.app')


@section('content')
<div class="m-4" style="color: white">
    <a href="{{ route('blog.index') }}">Volver al listado de anuncios</a>
    @if ((Auth::user() && Auth::user()->is_admin))
        <a href="{{ route('blog.edit', $article->id) }}" class="btn btn-primary">Editar anuncio</a>
    @endif
    <h1>{{ $article->title }}</h1>
    <img src="data:image/png;base64,{{ $article->image }}" width="100%" class="pb-2">
    <p><strong>Tipo:</strong> {{ $article->article_type }}</p>
    <p><strong>Publicado:</strong> {{ $article->created_at->format('d/m/Y') }}</p>
    <p>{!! $article->description !!}</p>
</div>
@endsection

@section('script')
    <script src="{{ mix('js/app.js') }}"></script>
@endsection
