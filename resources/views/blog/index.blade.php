@extends('layouts.app')

@section('title', 'Blog')

@section('content')
<div class="container">
    <h2 class="text-center mt-2 mb-4" style="color: white">Tablón Blader
        @if (Auth::user() && (Auth::user()->is_referee || in_array(Auth::user()->id, [301, 513])))
            <a href="{{ route('blog.create') }}" class="btn btn-outline-warning text-uppercase font-weight-bold">Crear post</a>
        @endif
    </h2>

    <form method="GET" class="mb-4" style="color: white">
        <div class="row">
            <div class="col-md-4 mb-2">
                <label for="type">Tipo de post:</label>
                <select name="type" id="type" class="form-control">
                    <option value="">Todos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-2">
                <label for="date_from">Publicado desde:</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>

            <div class="col-md-3 mb-2">
                <label for="date_to">Publicado hasta:</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>

            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">Filtrar</button>
            </div>
        </div>
    </form>

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
                        <a href="{{ route('blog.show', $article->custom_url) }}" class="btn btn-primary">Ver post</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="text-center" style="color: white">No hay posts publicados.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
