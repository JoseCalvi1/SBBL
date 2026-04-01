@extends('layouts.app')

@section('title', 'Equipos SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: LISTADO DE EQUIPOS (Hereda de layout)
       ==================================================================== */

    /* ── MENÚ SUPERIOR TÁCTICO ── */
    .top-nav-equipos {
        background: #000;
        border-bottom: 4px solid var(--sbbl-gold);
        padding: 15px 0;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    }

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Bangers', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    /* ── FILTROS ── */
    .filtros-box {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 6px 6px 0px #000;
        border-radius: 0;
        transform: skewX(-2deg);
        padding: 15px 25px;
    }
    .filtros-box > * { transform: skewX(2deg); }
    .filtros-box select {
        border: 2px solid #000;
        border-radius: 0;
        font-weight: 900;
        background: #111 !important;
        color: #fff !important;
        cursor: pointer;
    }
    .filtros-box select:focus { box-shadow: 0 0 0 3px var(--sbbl-gold); border-color: var(--sbbl-gold); }

    /* ── TARJETAS DE EQUIPOS ── */
    .equipo-card {
        background: var(--sbbl-blue-3);
        border: 3px solid #000;
        box-shadow: 5px 5px 0 #000;
        transform: skewX(-2deg);
        transition: 0.2s;
        padding: 15px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .equipo-card > * { transform: skewX(2deg); }
    .equipo-card:hover {
        border-color: var(--sbbl-gold);
        box-shadow: 7px 7px 0 var(--shonen-red);
        transform: translate(-2px, -2px) skewX(-2deg);
        background: var(--sbbl-blue-2);
    }

    .equipo-logo {
        width: 75px;
        height: 75px;
        object-fit: contain;
        background: #000;
        border: 2px solid var(--sbbl-gold);
        padding: 5px;
        box-shadow: 3px 3px 0 #000;
        transition: 0.3s ease;
    }
    .equipo-card:hover .equipo-logo {
        transform: scale(1.1) rotate(5deg);
        border-color: var(--shonen-cyan);
    }

    .equipo-name {
        font-family: 'Bangers', cursive;
        font-size: 1.6rem;
        color: #fff;
        text-shadow: 2px 2px 0 #000;
        letter-spacing: 1px;
        line-height: 1.1;
        margin-bottom: 5px;
    }

    .equipo-region {
        font-weight: 900;
        font-size: 0.8rem;
        color: #fff;
        background: #000;
        padding: 2px 8px;
        border: 1px solid #444;
        display: inline-block;
        text-transform: uppercase;
    }

    /* ── BOTONES ── */
    .btn-equipo {
        font-family: 'Bangers', cursive;
        font-size: 1.1rem;
        padding: 5px 15px;
        border: 2px solid #000;
        border-radius: 0;
        background: var(--shonen-cyan);
        color: #000;
        text-transform: uppercase;
        box-shadow: 3px 3px 0 #000;
        transition: 0.2s;
        text-decoration: none;
        display: inline-block;
        letter-spacing: 1px;
    }
    .btn-equipo:hover {
        background: #fff;
        transform: translate(-2px, -2px);
        box-shadow: 5px 5px 0 var(--shonen-red);
        color: #000;
    }
</style>
@endsection

@section('content')

{{-- NAVEGACIÓN SUPERIOR DE EQUIPOS --}}
<div class="top-nav-equipos">
    <div class="container">
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a class="btn-shonen btn-shonen-warning px-4 py-2" href="{{ route('equipos.index') }}">
                <span><i class="fas fa-users me-1"></i> EQUIPOS</span>
            </a>
            <a class="btn-shonen btn-shonen-info px-4 py-2" style="background: var(--sbbl-blue-3);" href="{{ route('teams_versus.all') }}">
                <span><i class="fas fa-fist-raised me-1"></i> DUELOS</span>
            </a>
            <a class="btn-shonen btn-shonen-info px-4 py-2" style="background: var(--sbbl-blue-3);" href="{{ route('equipos.ranking') }}">
                <span><i class="fas fa-chart-line me-1"></i> RANKING</span>
            </a>
        </div>
    </div>
</div>

<div class="container py-4 mb-5">

    <div class="text-center mb-4">
        <h2 class="page-title"><i class="fas fa-shield-alt text-white me-2" style="text-shadow: none;"></i> REGISTRO DE EQUIPOS</h2>
    </div>

    {{-- BOTÓN CREAR EQUIPO --}}
    @if (Auth::user() && Auth::user()->teams->isEmpty())
        <div class="text-center mb-5">
            <a href="{{ route('equipos.create') }}" class="btn-shonen btn-shonen-warning" style="font-size: 1.5rem; padding: 10px 30px;">
                <span><i class="fas fa-plus-circle me-2"></i> FUNDAR EQUIPO</span>
            </a>
        </div>
    @endif

    {{-- FILTROS --}}
    <div class="d-flex justify-content-center mb-5">
        <form method="GET" action="{{ route('equipos.index') }}" class="filtros-box d-flex flex-column flex-sm-row align-items-sm-center gap-3">
            <label for="region" class="font-bangers text-white mb-0 fs-4" style="letter-spacing: 1px;">ZONA DE OPERACIONES:</label>
            <select name="region" id="region" class="form-select w-auto" onchange="this.form.submit()">
                <option value="all" {{ $regionFilter === 'all' ? 'selected' : '' }}>TODAS LAS REGIONES</option>
                @foreach ($regiones as $region)
                    <option value="{{ $region->id }}" {{ $regionFilter == $region->id ? 'selected' : '' }}>
                        {{ mb_strtoupper($region->name) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- GRID DE EQUIPOS --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        @forelse($equipos as $equipo)
            <div class="col">
                <div class="equipo-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ !empty($equipo['logo']) ? 'data:image/png;base64,' . $equipo['logo'] : '/images/logo_new.png' }}"
                             alt="Logo del equipo"
                             class="equipo-logo me-3">
                        <div>
                            <div class="equipo-name text-truncate" style="max-width: 180px;" title="{{ $equipo['name'] }}">
                                {{ $equipo['name'] }}
                            </div>
                            <div class="equipo-region">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> {{ $equipo['region_name'] }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-auto">
                        <a href="{{ route('equipos.show', $equipo['id']) }}" class="btn-equipo w-100 text-center">
                            INSPECCIONAR EQUIPO <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="text-center py-5 bg-black border border-secondary" style="border-width: 3px !important; box-shadow: 6px 6px 0 #000; transform: skewX(-2deg);">
                    <div style="transform: skewX(2deg);">
                        <i class="fas fa-users-slash mb-3 text-secondary" style="font-size: 4rem;"></i>
                        <h3 class="font-bangers text-white fs-2 mb-2">NO HAY EQUIPOS REGISTRADOS</h3>
                        <p class="text-white fw-bold mb-0">Modifica los filtros o funda tu propio equipo.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection
