@extends('layouts.app')

@section('title', 'Blog - SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: BLOG SHONEN (Hereda de layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .blog-title {
        font-family: 'Oswald', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 2rem;
    }

    /* ── FILTROS (ESTILO COMUNICACIONES) ── */
    .blog-filters {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 6px 6px 0px #000;
        border-radius: 0;
        transform: skewX(-2deg);
        padding: 20px;
        margin-bottom: 3rem;
    }
    .blog-filters > div { transform: skewX(2deg); }

    .blog-filters label {
        color: var(--sbbl-gold) !important;
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .blog-filters .form-control, .blog-filters .form-select {
        background: #000 !important;
        border: 2px solid #000 !important;
        border-radius: 0 !important;
        color: #fff !important;
        font-weight: 900;
    }

    /* ── TARJETAS DE ARTÍCULOS ── */
    .article-card {
        background: var(--sbbl-blue-3);
        border: 3px solid #000;
        border-radius: 0 15px 0 15px;
        box-shadow: 6px 6px 0px #000;
        transition: 0.2s;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .article-card:hover {
        transform: translate(-3px, -3px);
        box-shadow: 8px 8px 0px var(--sbbl-gold);
        border-color: var(--sbbl-gold);
    }

    .article-img-container {
        width: 100%;
        position: relative;
        border-bottom: 3px solid #000;
        background: #000;
    }

    .article-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: var(--shonen-red);
        color: #fff;
        font-family: 'Oswald', cursive;
        padding: 4px 12px;
        border: 2px solid #000;
        transform: skewX(-10deg);
        box-shadow: 2px 2px 0 #000;
        z-index: 5;
    }

    .article-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .article-title {
        font-family: 'Oswald', cursive;
        font-size: 1.8rem;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 15px;
        text-shadow: 1px 1px 0 #000;
    }

    .article-meta {
        font-size: 0.85rem;
        font-weight: 800;
        color: #fff !important;
        text-transform: uppercase;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .article-meta i {
        color: var(--sbbl-gold);
    }

    /* ── BOTÓN CREAR ── */
    .btn-create-post {
        background: var(--shonen-cyan);
        color: #000;
        border: 3px solid #000;
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        padding: 5px 15px;
        box-shadow: 4px 4px 0 #000;
        transition: 0.2s;
        text-decoration: none;
    }
    .btn-create-post:hover {
        background: #fff;
        transform: translate(-2px, -2px);
        box-shadow: 6px 6px 0 var(--sbbl-blue-3);
        color: #000;
    }

</style>
@endsection

@section('content')
<div class="container py-4">

    {{-- CABECERA --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h2 class="blog-title m-0 text-center text-md-start">TABLÓN BLADER</h2>

        @if (Auth::user() && Auth::user()->hasRole('editor'))
            <a href="{{ route('blog.create') }}" class="btn-create-post">
                <i class="fas fa-plus-circle me-1"></i> NUEVO INFORME
            </a>
        @endif
    </div>

    {{-- FILTROS --}}
    <form method="GET" class="blog-filters">
        <div class="row align-items-end">
            <div class="col-md-4 mb-3 mb-md-0">
                <label for="type">TIPO DE INFORME:</label>
                <select name="type" id="type" class="form-select">
                    <option value="">TODOS</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ mb_strtoupper($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-3 mb-md-0">
                <label for="date_from">DESDE EL CICLO:</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>

            <div class="col-md-3 mb-3 mb-md-0">
                <label for="date_to">HASTA EL CICLO:</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn-shonen btn-shonen-warning w-100 text-center">
                    <span>FILTRAR</span>
                </button>
            </div>
        </div>
    </form>

    {{-- GRID DE ARTÍCULOS --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        @forelse($articles as $article)
            <div class="col">
                <div class="article-card">
                    <div class="article-img-container">
                        <div class="article-badge"><span>{{ $article->article_type }}</span></div>
                        @if($article->image)
                            <div style="padding-top: 56.25%; background-image: url(data:image/png;base64,{{ $article->image }}); background-size: cover; background-position: center;"></div>
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-black" style="height: 200px;">
                                <i class="fas fa-file-alt fa-4x text-secondary"></i>
                            </div>
                        @endif
                    </div>

                    <div class="article-body">
                        <div class="article-meta">
                            <i class="far fa-calendar-alt"></i> CICLO: {{ $article->created_at->format('d/m/Y') }}
                        </div>
                        <h4 class="article-title">{{ $article->title }}</h4>

                        <div class="mt-auto pt-3">
                            <a href="{{ route('blog.show', $article->custom_url) }}" class="btn-shonen btn-shonen-info w-100 text-center" style="padding: 10px;">
                                <span>LEER INFORME <i class="fas fa-arrow-right ms-1"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="command-panel p-5 text-center">
                    <i class="fas fa-ghost fa-4x mb-3 text-secondary"></i>
                    <h3 class="font-Oswald text-white">FRECUENCIA VACÍA</h3>
                    <p class="text-white fw-bold">No hay transmisiones registradas en el servidor.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN (Si existe) --}}
    @if (method_exists($articles, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $articles->links() }}
        </div>
    @endif

</div>
@endsection
