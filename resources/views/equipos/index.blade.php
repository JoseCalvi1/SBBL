@extends('layouts.app')

@section('title', 'Equipos Beyblade X')

@section('content')

<div class="container-fluid bg-dark shadow-sm py-2">
    <div class="d-flex justify-content-center gap-4">
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill" href="{{ route('equipos.index') }}">
            Inicio
        </a>
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill" href="{{ route('teams_versus.all') }}">
            Duelos
        </a>
        <a class="btn btn-outline-light fw-bold text-uppercase px-4 rounded-pill" href="{{ route('equipos.ranking') }}">
            Ranking
        </a>
    </div>
</div>

<div class="container my-5">
    <div class="d-flex align-items-center justify-content-center text-white mb-4">
        <h2 class="text-center mb-0 text-uppercase fw-bold">Listado de Equipos</h2>
    </div>

@if (Auth::user() && Auth::user()->teams->isEmpty())
    <div class="text-center mb-3">
        <a href="{{ route('equipos.create') }}" class="btn btn-warning text-uppercase fw-bold shadow-sm rounded-pill px-4">
            Crear equipo
        </a>
    </div>
@endif

<div class="mb-4 d-flex justify-content-center gap-3">
    <form method="GET" action="{{ route('equipos.index') }}" class="d-flex align-items-center gap-2">
        <label for="region" class="text-white fw-bold text-uppercase">Filtrar región:</label>
        <select name="region" id="region" class="form-select" onchange="this.form.submit()">
            <option value="all" {{ $regionFilter === 'all' ? 'selected' : '' }}>Todas</option>
            @foreach ($regiones as $region)
                <option value="{{ $region->id }}" {{ $regionFilter == $region->id ? 'selected' : '' }}>
                    {{ $region->name }}
                </option>
            @endforeach
        </select>
    </form>
</div>


<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4">
    @forelse($equipos as $equipo)
        <div class="col">
            <div class="d-flex align-items-center justify-content-between bg-dark text-white rounded shadow-sm px-3 py-2 h-100">
                <div class="d-flex align-items-center">
                    <img src="{{ !empty($equipo['logo']) ? 'data:image/png;base64,' . $equipo['logo'] : '/images/logo_new.png' }}"
                         alt="Logo del equipo"
                         class="me-3"
                         style="width: 64px; height: 64px; object-fit: contain;">
                    <div>
                        <h6 class="mb-1 text-warning fw-bold text-uppercase">{{ $equipo['name'] }}</h6>
                        <span class="mb-1 text-uppercase" style="color: #c7c9cc; opacity: 0.7;">{{ $equipo['region_name'] }}</span>
                    </div>
                </div>
                <a href="{{ route('equipos.show', $equipo['id']) }}" class="btn btn-sm btn-outline-warning fw-bold text-nowrap">Ver</a>
            </div>
        </div>
    @empty
        <div class="col">
            <p class="text-center text-muted">No hay equipos disponibles.</p>
        </div>
    @endforelse
</div>


</div>
@endsection

@section('styles')

<style>
    img.me-3 {
        transition: transform 0.3s ease;
    }
    img.me-3:hover {
        transform: scale(1.1);
    }
</style>

@endsection
