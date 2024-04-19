@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center mt-2 mb-4" style="color: white">Artículos del Blog
        @if (Auth::user()->is_admin)
            <a href="{{ route('blog.create') }}" class="btn btn-outline-warning text-uppercase font-weight-bold">Crear artículo</a>
        @endif
    </h2>

    <div class="row row-cols-1 row-cols-md-3">
        @forelse($articles as $article)
            <div class="col mb-4">
                <div class="card bg-dark text-white">
                    <div class="card-body p-0">
                        @if($article->image)
                        <div class="mb-2" style="padding-top: 56.25%; background-image: url(data:image/png;base64,{{ $article->image }}); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
                        @endif
                        <h5 class="card-title ml-1">{{ $article->title }}</h5>
                        <p class="card-text ml-1"><strong>Tipo:</strong> {{ $article->article_type }}</p>
                        <p class="card-text ml-1"><strong>Publicado:</strong> {{ $article->created_at->format('d/m/Y') }}</p>
                        <a href="{{ route('blog.show', $article->custom_url) }}" class="btn btn-primary">Ver artículo</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="text-center" style="color: white">No hay artículos disponibles.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
