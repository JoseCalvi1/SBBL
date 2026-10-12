@extends('layouts.app')

@section('title', 'Centro de Equipos SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: DASHBOARD DE EQUIPOS (Hereda de layout)
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
        font-family: 'Oswald', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-blue);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    /* ── TARJETAS DE ESTADÍSTICAS ── */
    .stat-card {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 5px 5px 0 #000;
        padding: 20px;
        text-align: center;
        transform: skewX(-5deg);
        transition: 0.2s;
    }
    .stat-card > * { transform: skewX(5deg); }
    .stat-card:hover { border-color: var(--shonen-cyan); transform: translate(-2px, -2px) skewX(-5deg); box-shadow: 7px 7px 0 var(--shonen-cyan); }
    .stat-value { font-family: 'Oswald', cursive; font-size: 3rem; color: var(--sbbl-gold); line-height: 1; text-shadow: 2px 2px 0 #000; }
    .stat-label { font-weight: 900; color: #fff; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem; }

    /* ── PANEL DE TU EQUIPO ── */
    .my-team-panel {
        background: url('/images/FONDO_BX.webp') center/cover;
        border: 4px solid var(--sbbl-gold);
        box-shadow: 8px 8px 0 rgba(0,0,0,0.8);
        padding: 30px;
        position: relative;
        overflow: hidden;
    }
    .my-team-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 100%); z-index: 1; }
    .my-team-content { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;}

    /* ── TOP 3 PODIO MINI ── */
    .podium-mini {
        background: #000; border: 3px solid #333; padding: 15px; height: 100%; display: flex; flex-direction: column; justify-content: center;
    }
    .podium-item {
        display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #222;
    }
    .podium-item:last-child { border-bottom: none; }
    .podium-rank { font-family: 'Oswald', cursive; font-size: 1.5rem; width: 30px; text-align: center; margin-right: 15px; }
    .rank-1 { color: var(--sbbl-gold); text-shadow: 1px 1px 0 #000; font-size: 2rem;}
    .rank-2 { color: #e2e8f0; }
    .rank-3 { color: #ff9d47; }

    /* ── FILTROS Y GRID ── */
    .filtros-box {
        background: var(--sbbl-blue-2); border: 3px solid #000; box-shadow: 6px 6px 0px #000; border-radius: 0; transform: skewX(-2deg); padding: 15px 25px;
    }
    .filtros-box > * { transform: skewX(2deg); }
    .filtros-box select { border: 2px solid #000; border-radius: 0; font-weight: 900; background: #111 !important; color: #fff !important; cursor: pointer; }
    .filtros-box select:focus { box-shadow: 0 0 0 3px var(--sbbl-gold); border-color: var(--sbbl-gold); }

    .equipo-card {
        background: var(--sbbl-blue-3); border: 3px solid #000; box-shadow: 5px 5px 0 #000; transform: skewX(-2deg); transition: 0.2s; padding: 15px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;
    }
    .equipo-card > * { transform: skewX(2deg); }
    .equipo-card:hover { border-color: var(--sbbl-gold); box-shadow: 7px 7px 0 var(--shonen-red); transform: translate(-2px, -2px) skewX(-2deg); background: var(--sbbl-blue-2); }
    .equipo-logo { width: 75px; height: 75px; object-fit: contain; background: #000; border: 2px solid var(--sbbl-gold); padding: 5px; box-shadow: 3px 3px 0 #000; transition: 0.3s ease; }
    .equipo-card:hover .equipo-logo { transform: scale(1.1) rotate(5deg); border-color: var(--shonen-cyan); }
    .equipo-name { font-family: 'Oswald', cursive; font-size: 1.6rem; color: #fff; text-shadow: 2px 2px 0 #000; letter-spacing: 1px; line-height: 1.1; margin-bottom: 5px; }
    .equipo-region { font-weight: 900; font-size: 0.8rem; color: #fff; background: #000; padding: 2px 8px; border: 1px solid #444; display: inline-block; text-transform: uppercase; }

    /* PAGINACIÓN ESTILO SHONEN */
    .pagination { margin-top: 2rem; justify-content: center; }
    .page-item .page-link { background: #000; border: 2px solid #333; color: #fff; font-family: 'Oswald', cursive; font-size: 1.2rem; margin: 0 5px; transform: skewX(-5deg); box-shadow: 3px 3px 0 #000; }
    .page-item.active .page-link { background: var(--sbbl-gold); color: #000; border-color: #000; font-weight: bold; }
    .page-item .page-link:hover { background: var(--shonen-cyan); color: #000; }
</style>
@endsection

@section('content')

{{-- NAVEGACIÓN SUPERIOR DE EQUIPOS --}}
<div class="top-nav-equipos">
    <div class="container">
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a class="btn-shonen btn-shonen-warning px-4 py-2" href="{{ route('equipos.index') }}">
                <span><i class="fas fa-satellite-dish me-1"></i> INICIO</span>
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

    <div class="text-center mb-5">
        <h2 class="page-title"><i class="fas fa-globe-americas text-white me-2" style="text-shadow: none;"></i> RED GLOBAL DE EQUIPOS</h2>
    </div>

    {{-- DASHBOARD: ESTADÍSTICAS Y TU EQUIPO --}}
    <div class="row g-4 mb-5">
        {{-- Bloque Izquierdo: Tu Equipo / Crear Equipo --}}
        <div class="col-lg-8">
            @if ($myTeam)
                {{-- Si el usuario tiene equipo --}}
                <div class="my-team-panel h-100 d-flex flex-column justify-content-center">
                    <div class="my-team-overlay"></div>
                    <div class="my-team-content">
                        <div class="d-flex align-items-center">
                            <img src="{{ !empty($myTeam->logo) ? 'data:image/png;base64,' . $myTeam->logo : '/images/logo_new.png' }}"
                                 alt="Logo" class="equipo-logo me-4" style="width: 100px; height: 100px;">
                            <div>
                                <h5 class="text-white font-Oswald mb-0 fs-5" style="color: var(--shonen-cyan) !important;">TU FACCIÓN ACTUAL:</h5>
                                <h3 class="font-Oswald text-white m-0" style="font-size: 3rem; text-shadow: 2px 2px 0 #000;">{{ mb_strtoupper($myTeam->name) }}</h3>
                                <div class="mt-2 text-white fw-bold"><i class="fas fa-star me-1 text-warning"></i> {{ $myTeam->points_x3 }} PTS EN LIGA</div>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('equipos.show', $myTeam->id) }}" class="btn-shonen btn-shonen-warning py-3 px-4">
                                <span>ACCEDER AL CUARTEL <i class="fas fa-arrow-right ms-2"></i></span>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                {{-- Si el usuario NO tiene equipo --}}
                <div class="my-team-panel h-100 d-flex flex-column justify-content-center border-secondary" style="background: #111;">
                    <div class="my-team-content flex-column text-center w-100">
                        <i class="fas fa-user-shield text-secondary mb-3" style="font-size: 3rem;"></i>
                        <h3 class="font-Oswald text-white fs-2">ERES UN AGENTE LIBRE</h3>
                        <p class="text-white mb-4">Puedes esperar a recibir una invitación de un equipo existente o fundar el tuyo propio y liderarlo hasta la cima.</p>
                        <a href="{{ route('equipos.create') }}" class="btn-shonen btn-shonen-warning py-3 px-5">
                            <span><i class="fas fa-plus-circle me-2"></i> FUNDAR EQUIPO NUEVO</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Bloque Derecho: Top 3 & Stats --}}
        <div class="col-lg-4">
            <div class="podium-mini mb-3">
                <h5 class="text-white font-Oswald text-center border-bottom border-dark pb-2 mb-3"><i class="fas fa-trophy text-warning me-1"></i> TOP 3 GLOBAL</h5>
                @forelse($topTeams as $index => $team)
                    <div class="podium-item">
                        <div class="podium-rank rank-{{ $index + 1 }}">#{{ $index + 1 }}</div>
                        <img src="{{ !empty($team->logo) ? 'data:image/png;base64,' . $team->logo : '/images/logo_new.png' }}"
                             style="width: 40px; height: 40px; border: 1px solid #555; background: #000; margin-right: 15px; object-fit: contain;">
                        <div class="text-white flex-grow-1 font-Oswald text-truncate" style="font-size: 1.2rem;">{{ $team->name }}</div>
                        <div class="fw-bold text-warning small">{{ $team->points_x3 }} PTS</div>
                    </div>
                @empty
                    <p class="text-center text-secondary m-0">Aún no hay puntos registrados.</p>
                @endforelse
            </div>

            <div class="d-flex gap-2">
                <div class="stat-card flex-grow-1 p-2">
                    <div class="stat-value fs-2">{{ $stats['total_teams'] }}</div>
                    <div class="stat-label" style="font-size: 0.7rem;">Equipos Registrados</div>
                </div>
                <div class="stat-card flex-grow-1 p-2 border-secondary" style="background: #1a1a1a;">
                    <div class="stat-value fs-2 text-white">{{ $stats['total_duels'] }}</div>
                    <div class="stat-label" style="font-size: 0.7rem;">Duelos S3</div>
                </div>
            </div>
        </div>
    </div>

    <hr class="border-secondary mb-5">

    {{-- FILTROS DEL RADAR --}}
    <div class="d-flex justify-content-center mb-5">
        <form method="GET" action="{{ route('equipos.index') }}" class="filtros-box d-flex flex-column flex-sm-row align-items-sm-center gap-3">
            <label for="region" class="font-Oswald text-white mb-0 fs-4" style="letter-spacing: 1px;">BÚSQUEDA:</label>
            <select name="region" id="region" class="form-select w-auto" onchange="this.form.submit()">
                <option value="all" {{ $regionFilter === 'all' ? 'selected' : '' }}>GLOBAL (TODAS LAS REGIONES)</option>
                @foreach ($regiones as $region)
                    <option value="{{ $region->id }}" {{ $regionFilter == $region->id ? 'selected' : '' }}>
                        {{ mb_strtoupper($region->name) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- GRID DE EQUIPOS (LISTADO) --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
        @forelse($equipos as $equipo)
            <div class="col">
                <div class="equipo-card">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ !empty($equipo->logo) ? 'data:image/png;base64,' . $equipo->logo : '/images/logo_new.png' }}"
                             alt="Logo" class="equipo-logo me-3">
                        <div>
                            <div class="equipo-name text-truncate" style="max-width: 180px;" title="{{ $equipo->name }}">
                                {{ $equipo->name }}
                            </div>
                            <div class="equipo-region">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                {{ optional(optional(optional($equipo->captain)->profile)->region)->name ?? 'SIN ASIGNAR' }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-auto">
                        <a href="{{ route('equipos.show', $equipo->id) }}" class="btn-shonen w-100 text-center py-2" style="background: var(--shonen-cyan); color:#000;">
                            <span>INSPECCIONAR DATOS <i class="fas fa-arrow-right ms-1"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="text-center py-5 bg-black border border-secondary" style="border-width: 3px !important; box-shadow: 6px 6px 0 #000; transform: skewX(-2deg);">
                    <div style="transform: skewX(2deg);">
                        <i class="fas fa-satellite mb-3 text-secondary" style="font-size: 4rem;"></i>
                        <h3 class="font-Oswald text-white fs-2 mb-2">NO HAY EQUIPOS</h3>
                        <p class="text-white fw-bold mb-0">No se encontraron facciones en esta región.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN (VITAL PARA LA MEMORIA RAM) --}}
    <div class="d-flex justify-content-center">
        {{ $equipos->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection
