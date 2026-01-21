@extends('layouts.app')

@section('title', 'Estadísticas Beyblade X')

@section('styles')
<style>
    /* Estilos Generales */
    .table-responsive {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        max-height: 80vh;
        overflow-y: auto;
    }

    thead th {
        position: sticky;
        top: 0;
        background-color: #212529 !important;
        z-index: 2;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
        vertical-align: middle;
    }

    /* Estilos para los Headers Ordenables */
    thead th a {
        color: #adb5bd; /* Gris claro por defecto */
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: color 0.2s;
    }
    thead th a:hover {
        color: white;
    }
    thead th a.active-sort {
        color: #ffc107; /* Amarillo para la columna activa */
        font-weight: bold;
    }

    /* Resto de estilos (Filtros, badges, etc) */
    tbody tr:hover { background-color: rgba(255,255,255,0.05) !important; }
    .filter-card { background-color: #212529 !important; border: 1px solid #495057; color: white; }
    .form-select, .form-control { background-color: #2c3034 !important; border-color: #495057 !important; color: #fff !important; }
    .text-tier-s { color: #d63384; font-weight: bold; }
    .text-tier-a { color: #0dcaf0; font-weight: bold; }
    .text-tier-b { color: #198754; }
    .text-tier-c { color: #ffc107; }
    .text-tier-d { color: #dc3545; }
    .collapse-row { background-color: #2c3034; box-shadow: inset 0 0 10px rgba(0,0,0,0.5); }
    .btn-detail[aria-expanded="true"] { background-color: #0d6efd; color: white; transform: rotate(180deg); }
    .btn-detail { transition: transform 0.3s ease; }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">

    <div class="d-flex justify-content-between align-items-center py-3">
        <h2 class="text-white m-0"><i class="bi bi-graph-up-arrow text-primary"></i> Meta Reporte</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('stats.separate') }}" class="btn btn-outline-secondary btn-sm">Partes Individuales</a>
            <a href="{{ route('stats.rankingstats') }}" class="btn btn-warning btn-sm fw-bold text-dark">Ranking</a>
        </div>
    </div>

    <div class="card filter-card mb-4">
        <div class="card-header border-secondary p-0" id="headingFilters">
            <button class="btn btn-link text-decoration-none text-white w-100 text-start p-3 d-flex justify-content-between align-items-center"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                <span class="fw-bold"><i class="bi bi-funnel-fill text-warning"></i> FILTRAR COMBINACIONES</span>
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>

        <div id="collapseFilters" class="collapse show">
            <div class="card-body pt-0">
                <form method="GET" action="{{ route('stats.index') }}" id="filterForm">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="order" value="{{ $order }}">

                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-6 col-md-3">
                            <label class="text-secondary small mb-1">Blade</label>
                            <select name="blade" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($blades as $b) <option value="{{ $b }}" {{ $b == request('blade') ? 'selected' : '' }}>{{ $b }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="text-secondary small mb-1">Ratchet</label>
                            <select name="ratchet" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($ratchets as $r) <option value="{{ $r }}" {{ $r == request('ratchet') ? 'selected' : '' }}>{{ $r }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="text-secondary small mb-1">Bit</label>
                            <select name="bit" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($bits as $bit) <option value="{{ $bit }}" {{ $bit == request('bit') ? 'selected' : '' }}>{{ $bit }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="text-secondary small mb-1">Min. Partidas</label>
                            <select name="min_partidas" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="10" {{ $minPartidas == 10 ? 'selected' : '' }}>10+</option>
                                <option value="30" {{ $minPartidas == 30 ? 'selected' : '' }}>30+</option>
                                <option value="50" {{ $minPartidas == 50 ? 'selected' : '' }}>50+</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end border-top border-secondary pt-3">
                        <div class="col-6 col-md-3">
                            <label class="text-secondary small mb-1">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ $fechaInicio }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="text-secondary small mb-1">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm" value="{{ $fechaFin }}" onchange="this.form.submit()">
                        </div>

                        <div class="col-12 col-md-6 d-flex justify-content-end align-items-center">
                            @if(Auth::check())
                            <div class="form-check form-switch me-3">
                                <input class="form-check-input" type="checkbox" id="userParts" name="only_user_parts" value="on" {{ request('only_user_parts') ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label text-white small" for="userParts">Mis Datos</label>
                            </div>
                            @endif

                            <a href="{{ route('stats.index') }}" class="btn btn-outline-danger btn-sm px-3">
                                <i class="bi bi-x-circle"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead>
                <tr class="text-uppercase small text-secondary">

                    <th style="min-width: 250px;">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'blade', 'order' => ($sort == 'blade' && $order == 'asc') ? 'desc' : 'asc'])) }}"
                           class="{{ $sort == 'blade' ? 'active-sort' : '' }} justify-content-start ps-2">
                            Combo
                            @if($sort == 'blade')
                                <i class="bi bi-caret-{{ $order == 'asc' ? 'up' : 'down' }}-fill"></i>
                            @endif
                        </a>
                    </th>

                    <th class="text-center">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'percentage_victories', 'order' => ($sort == 'percentage_victories' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                           class="{{ $sort == 'percentage_victories' ? 'active-sort' : '' }}">
                            Win Rate
                            @if($sort == 'percentage_victories')
                                <i class="bi bi-caret-{{ $order == 'asc' ? 'up' : 'down' }}-fill"></i>
                            @endif
                        </a>
                    </th>

                    <th class="text-center">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'total_partidas', 'order' => ($sort == 'total_partidas' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                           class="{{ $sort == 'total_partidas' ? 'active-sort' : '' }}">
                            Partidas
                            @if($sort == 'total_partidas')
                                <i class="bi bi-caret-{{ $order == 'asc' ? 'up' : 'down' }}-fill"></i>
                            @endif
                        </a>
                    </th>

                    <th class="text-center">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'eficiencia', 'order' => ($sort == 'eficiencia' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                           class="{{ $sort == 'eficiencia' ? 'active-sort' : '' }}">
                            Eficiencia
                            @if($sort == 'eficiencia')
                                <i class="bi bi-caret-{{ $order == 'asc' ? 'up' : 'down' }}-fill"></i>
                            @endif
                        </a>
                    </th>

                    <th class="text-end pe-3">Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beybladeStats as $index => $stat)
                    @php
                        $wr = $stat->percentage_victories;
                        $colorClass = $wr >= 60 ? 'bg-success' : ($wr >= 45 ? 'bg-warning' : 'bg-danger');
                        $textClass = $wr >= 60 ? 'text-success' : ($wr >= 45 ? 'text-warning' : 'text-danger');
                        $collapseId = "details-" . $index;
                    @endphp

                    <tr>
                        <td class="py-3 ps-3">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-white fs-5">{{ $stat->blade }}</span>
                                @if($stat->assist_blade && $stat->assist_blade != '-')
                                    <div class="text-info fw-bold small my-1">
                                        <i class="bi bi-plus-lg" style="font-size: 0.7em;"></i> {{ $stat->assist_blade }}
                                    </div>
                                @endif
                                <div class="small text-secondary mt-1">
                                    {{ $stat->ratchet }} <span class="mx-1 text-white">•</span> {{ $stat->bit }}
                                </div>
                            </div>
                        </td>

                        <td style="width: 25%;">
                            <div class="d-flex justify-content-between mb-1 px-2">
                                <span class="{{ $textClass }} fw-bold">{{ number_format($wr, 1) }}%</span>
                                <span class="small text-white">{{ $stat->total_victorias }}W - {{ $stat->total_derrotas }}L</span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #444;">
                                <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $wr }}%"></div>
                            </div>
                        </td>

                        <td class="text-center text-white fw-bold fs-5">
                            {{ $stat->total_partidas }}
                        </td>

                        <td class="text-center">
                            <span class="badge bg-dark border border-secondary text-white p-2">
                                {{ number_format($stat->eficiencia, 0) }} pts
                            </span>
                        </td>

                        <td class="text-end pe-3">
                            <button class="btn btn-sm btn-outline-secondary btn-detail" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="false">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="5" class="p-0 border-0">
                            <div class="collapse" id="{{ $collapseId }}">
                                <div class="collapse-row p-4 d-flex justify-content-around align-items-center text-white">
                                    <div class="text-center">
                                        <div class="text-secondary small text-uppercase mb-1">Pts. Ganados / Combate</div>
                                        <div class="fs-4 fw-bold text-success">
                                            <i class="bi bi-arrow-up-circle"></i> {{ number_format($stat->puntos_ganados_por_combate, 2) }}
                                        </div>
                                    </div>
                                    <div style="border-left: 1px solid #555; height: 40px;"></div>
                                    <div class="text-center">
                                        <div class="text-secondary small text-uppercase mb-1">Pts. Cedidos / Combate</div>
                                        <div class="fs-4 fw-bold text-danger">
                                            <i class="bi bi-arrow-down-circle"></i> {{ number_format($stat->puntos_perdidos_por_combate, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white">
                            No hay datos con estos filtros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
