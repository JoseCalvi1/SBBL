@extends('layouts.app')

@section('title', 'Estadísticas Beyblade X')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: RADAR META (El resto hereda del layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Bangers', cursive;
        font-size: 2.5rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* ── BOTONES SUPERIORES ── */
    .btn-top-action {
        font-family: 'Bangers', cursive;
        font-size: 1.1rem;
        border-radius: 0;
        border: 2px solid #000;
        transform: skewX(-10deg);
        box-shadow: 3px 3px 0 var(--sbbl-blue-3);
        transition: 0.2s;
        text-transform: uppercase;
        padding: 5px 15px;
    }
    .btn-top-action > * { transform: skewX(10deg); display: block; }
    .btn-top-action:hover { transform: translate(-2px, -2px) skewX(-10deg); box-shadow: 5px 5px 0 var(--shonen-red); }

    /* ── PANEL DE FILTROS (Radar Setup) ── */
    .filter-card {
        background-color: var(--sbbl-blue-2) !important;
        border: 3px solid #000;
        color: white;
        border-radius: 0;
        box-shadow: 6px 6px 0 var(--sbbl-blue-3);
        margin-bottom: 2rem !important;
    }
    .filter-card label {
        font-family: 'Bangers', cursive;
        font-size: 1.1rem;
        color: var(--sbbl-gold);
        letter-spacing: 1px;
    }
    .filter-card .form-select, .filter-card .form-control {
        background-color: #000 !important;
        border: 2px solid #333 !important;
        color: #fff !important;
        border-radius: 0;
        font-weight: 900;
    }

    /* ── TABLA ESTILO ARENA ── */
    .table-responsive {
        border: 3px solid #000;
        border-radius: 0 20px 0 20px;
        box-shadow: 8px 8px 0px var(--shonen-red);
        max-height: 80vh;
        overflow-y: auto;
        background: #000;
    }
    thead th {
        position: sticky;
        top: 0;
        background-color: #000 !important;
        border-bottom: 3px solid var(--sbbl-gold) !important;
        z-index: 2;
        padding: 15px 10px !important;
    }
    thead th a {
        font-family: 'Bangers', cursive;
        font-size: 1.2rem;
        color: #fff;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-shadow: 1px 1px 0 #333;
    }
    thead th a.active-sort { color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000; }

    .combo-name { font-family: 'Bangers', cursive; font-size: 1.5rem; color: var(--sbbl-gold); text-shadow: 1px 1px 0 #000; }

    /* Barras de Energía (Win Rate) */
    .energy-bar-bg { background-color: #000; border: 2px solid #333; height: 12px; border-radius: 0; transform: skewX(-15deg); margin-top: 5px; }
    .energy-bar-fill { height: 100%; transition: width 0.5s ease; }
    .win-rate-text { font-family: 'Bangers', cursive; font-size: 1.4rem; line-height: 1; text-shadow: 1px 1px 0 #000; }

    .wr-s { color: #00ff00; } .bg-wr-s { background: #00ff00; box-shadow: 0 0 10px rgba(0,255,0,0.5); }
    .wr-a { color: var(--shonen-cyan); } .bg-wr-a { background: var(--shonen-cyan); box-shadow: 0 0 10px rgba(0,255,204,0.5); }
    .wr-b { color: var(--sbbl-gold); } .bg-wr-b { background: var(--sbbl-gold); box-shadow: 0 0 10px rgba(255,215,0,0.5); }
    .wr-c { color: var(--shonen-red); } .bg-wr-c { background: var(--shonen-red); box-shadow: 0 0 10px rgba(255,42,42,0.5); }

    .eficiencia-badge {
        background: #000; border: 2px solid #fff; color: var(--sbbl-gold);
        font-family: 'Bangers', cursive; font-size: 1.2rem;
        padding: 5px 15px; transform: skewX(-10deg); display: inline-block;
        box-shadow: 3px 3px 0 var(--sbbl-blue-3);
    }
    .eficiencia-badge > * { transform: skewX(10deg); display: block; }

    /* Fila Desplegable (Análisis) */
    .collapse-row {
        background-color: #000;
        border-bottom: 3px solid var(--shonen-red);
        background-image: repeating-linear-gradient(45deg, rgba(255,255,255,0.02) 0px, rgba(255,255,255,0.02) 10px, transparent 10px, transparent 20px);
    }
    .stat-title-detail { font-family: 'Bangers', cursive; font-size: 1.1rem; color: #aaa; letter-spacing: 1px; }
    .stat-value-detail { font-family: 'Bangers', cursive; font-size: 2.5rem; text-shadow: 2px 2px 0 #000; }

    /* ── MODAL TOP 5 (EXPORT) ── */
    .modal-content-export {
        background: #000 !important; border: 4px solid var(--sbbl-gold); border-radius: 0;
        box-shadow: 10px 10px 0 var(--shonen-red); color: #fff;
    }
    .export-body {
        background-color: #111;
        background-image: radial-gradient(rgba(255, 255, 255, 0.1) 3px, transparent 3px);
        background-size: 30px 30px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 mb-4">

    {{-- HEADER SUPERIOR --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-4 gap-3">
        <h2 class="page-title m-0">
            <i class="fas fa-radar text-white" style="text-shadow: none; font-size: 0.8em;"></i> RADAR TÁCTICO (META)
        </h2>
        <div class="d-flex flex-wrap gap-3">
            <button type="button" class="btn-top-action" style="background: var(--shonen-red); color: #fff; border-color: #fff;" data-bs-toggle="modal" data-bs-target="#topSemanalModal">
                <span><i class="fas fa-fire me-1"></i> TOP 5 SEMANAL</span>
            </button>
            <a href="{{ route('stats.separate') }}" class="btn-top-action" style="background: var(--sbbl-blue-3); color:#fff;">
                <span>PARTES AISLADAS</span>
            </a>
            <a href="{{ route('stats.rankingstats') }}" class="btn-top-action" style="background: var(--sbbl-gold); color:#000;">
                <span>RANKING GLOBAL</span>
            </a>
        </div>
    </div>

    {{-- PANEL DE FILTROS --}}
    <div class="card filter-card">
        <div class="card-header p-0">
            <button class="btn btn-link text-decoration-none w-100 text-start p-3 d-flex justify-content-between align-items-center"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                <span class="text-white font-bangers fs-4"><i class="fas fa-filter text-warning me-2"></i> CONFIGURAR ESCÁNER</span>
                <i class="fas fa-chevron-down text-white"></i>
            </button>
        </div>

        <div id="collapseFilters" class="collapse show">
            <div class="card-body">
                <form method="GET" action="{{ route('stats.index') }}" id="filterForm">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="order" value="{{ $order }}">

                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-6 col-md-3">
                            <label>BLADE</label>
                            <select name="blade" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">TODOS</option>
                                @foreach($blades as $b) <option value="{{ $b }}" {{ $b == request('blade') ? 'selected' : '' }}>{{ $b }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label>RATCHET</label>
                            <select name="ratchet" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">TODOS</option>
                                @foreach($ratchets as $r) <option value="{{ $r }}" {{ $r == request('ratchet') ? 'selected' : '' }}>{{ $r }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label>BIT</label>
                            <select name="bit" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">TODOS</option>
                                @foreach($bits as $bit) <option value="{{ $bit }}" {{ $bit == request('bit') ? 'selected' : '' }}>{{ $bit }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label>MIN. BATALLAS</label>
                            <select name="min_partidas" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="10" {{ $minPartidas == 10 ? 'selected' : '' }}>10+ Enfrentamientos</option>
                                <option value="30" {{ $minPartidas == 30 ? 'selected' : '' }}>30+ Enfrentamientos</option>
                                <option value="50" {{ $minPartidas == 50 ? 'selected' : '' }}>50+ Enfrentamientos</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 align-items-end border-top border-dark pt-3">
                        <div class="col-6 col-md-3">
                            <label>FECHA INICIO</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ $fechaInicio }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-6 col-md-3">
                            <label>FECHA FIN</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm" value="{{ $fechaFin }}" onchange="this.form.submit()">
                        </div>

                        <div class="col-12 col-md-6 d-flex justify-content-md-end align-items-center gap-4">
                            @if(Auth::check())
                            <div class="form-check form-switch" style="transform: scale(1.2);">
                                <input class="form-check-input bg-dark border-secondary" type="checkbox" id="userParts" name="only_user_parts" value="on" {{ request('only_user_parts') ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label font-bangers ms-2 text-white" for="userParts">MIS DATOS</label>
                            </div>
                            @endif

                            <a href="{{ route('stats.index') }}" class="btn-shonen btn-shonen-warning" style="font-size: 1rem; padding: 5px 15px;">
                                <span><i class="fas fa-times me-1"></i> RESETEAR RADAR</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle border-0">
            <thead>
                <tr>
                    <th style="min-width: 250px;">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'blade', 'order' => ($sort == 'blade' && $order == 'asc') ? 'desc' : 'asc'])) }}"
                           class="{{ $sort == 'blade' ? 'active-sort' : '' }} justify-content-start ps-2">
                            CONFIGURACIÓN (COMBO)
                            @if($sort == 'blade') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }}"></i> @endif
                        </a>
                    </th>
                    <th class="text-center" style="min-width: 150px;">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'percentage_victories', 'order' => ($sort == 'percentage_victories' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                           class="{{ $sort == 'percentage_victories' ? 'active-sort' : '' }}">
                            PODER DE VICTORIA
                            @if($sort == 'percentage_victories') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }}"></i> @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'total_partidas', 'order' => ($sort == 'total_partidas' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                           class="{{ $sort == 'total_partidas' ? 'active-sort' : '' }}">
                            BATALLAS
                            @if($sort == 'total_partidas') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }}"></i> @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('stats.index', array_merge(request()->query(), ['sort' => 'eficiencia', 'order' => ($sort == 'eficiencia' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                           class="{{ $sort == 'eficiencia' ? 'active-sort' : '' }}">
                            EFICIENCIA
                            @if($sort == 'eficiencia') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }}"></i> @endif
                        </a>
                    </th>
                    <th class="text-end pe-4">INFO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($beybladeStats as $index => $stat)
                    @php
                        $wr = $stat->percentage_victories;
                        if ($wr >= 60) { $cText = 'wr-s'; $cBg = 'bg-wr-s'; }
                        elseif ($wr >= 50) { $cText = 'wr-a'; $cBg = 'bg-wr-a'; }
                        elseif ($wr >= 40) { $cText = 'wr-b'; $cBg = 'bg-wr-b'; }
                        else { $cText = 'wr-c'; $cBg = 'bg-wr-c'; }
                        $collapseId = "details-" . $index;
                    @endphp

                    <tr>
                        <td class="py-3 ps-4 border-0">
                            <div class="d-flex flex-column">
                                <span class="combo-name">{{ $stat->blade }}</span>
                                @if($stat->assist_blade && $stat->assist_blade != '-')
                                    <div class="my-1">
                                        <span class="badge bg-black text-white border border-info" style="font-size: 0.7rem; transform: skewX(-10deg);">
                                            <span style="display:block; transform:skewX(10deg);"><i class="fas fa-link me-1"></i> {{ $stat->assist_blade }}</span>
                                        </span>
                                    </div>
                                @endif
                                <div class="fw-bold mt-1 text-white opacity-75" style="font-size: 0.9rem;">
                                    {{ $stat->ratchet }} <span class="mx-2 text-warning">X</span> {{ $stat->bit }}
                                </div>
                            </div>
                        </td>

                        <td style="width: 25%;" class="border-0">
                            <div class="d-flex justify-content-between align-items-end mb-1 px-1">
                                <span class="win-rate-text {{ $cText }}">{{ number_format($wr, 1) }}%</span>
                                <span class="fw-bold text-white opacity-50" style="font-size:0.8rem;">{{ $stat->total_victorias }}W - {{ $stat->total_derrotas }}L</span>
                            </div>
                            <div class="energy-bar-bg">
                                <div class="energy-bar-fill {{ $cBg }}" style="width: {{ $wr }}%"></div>
                            </div>
                        </td>

                        <td class="text-center border-0">
                            <span class="partidas-count text-white">{{ $stat->total_partidas }}</span>
                        </td>

                        <td class="text-center border-0">
                            <span class="eficiencia-badge">
                                <span>{{ number_format($stat->eficiencia, 0) }} PTS</span>
                            </span>
                        </td>

                        <td class="text-end pe-4 border-0">
                            <button class="btn btn-sm btn-outline-light border-2 px-3 py-1" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="5" class="p-0 border-0">
                            <div class="collapse" id="{{ $collapseId }}">
                                <div class="collapse-row p-4 d-flex justify-content-around align-items-center">
                                    <div class="text-center">
                                        <div class="stat-title-detail">DAÑO CAUSADO (PTS/BAT)</div>
                                        <div class="stat-value-detail text-success">
                                            <i class="fas fa-fist-raised me-1"></i> {{ number_format($stat->puntos_ganados_por_combate, 2) }}
                                        </div>
                                    </div>
                                    <div style="border-left: 3px solid #333; height: 60px; transform: skewX(-15deg);"></div>
                                    <div class="text-center">
                                        <div class="stat-title-detail">DAÑO RECIBIDO (PTS/BAT)</div>
                                        <div class="stat-value-detail text-danger">
                                            <i class="fas fa-shield-alt me-1"></i> {{ number_format($stat->puntos_perdidos_por_combate, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-white border-0">
                            <span class="font-bangers fs-3" style="color: var(--shonen-red);">EL RADAR NO DETECTA COMBOS CON ESTOS PARÁMETROS.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


@endsection

@section('scripts')
{{-- MODAL TOP 5 SEMANAL (SHONEN EXPORT STYLE) --}}
<div class="modal fade" id="topSemanalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-export">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 pt-0" id="export-top-node" class="export-body">
                {{-- Adornos --}}
                <div style="position: absolute; top: 0; left: 0; width: 80px; height: 80px; border-top: 8px solid var(--shonen-red); border-left: 8px solid var(--shonen-red);"></div>
                <div style="position: absolute; bottom: 0; right: 0; width: 80px; height: 80px; border-bottom: 8px solid var(--sbbl-gold); border-right: 8px solid var(--sbbl-gold);"></div>

                <div class="text-center mb-4 mt-3">
                    <h2 class="font-bangers m-0" style="font-size: 4rem; color: var(--sbbl-gold); text-shadow: 3px 3px 0 var(--shonen-red);">META RADAR</h2>
                    <div class="d-inline-block bg-white text-dark px-3 py-1 mt-2 font-bangers fs-4" style="transform: skewX(-10deg); border: 2px solid #000; box-shadow: 4px 4px 0 var(--sbbl-blue-3);">
                        <span style="transform: skewX(10deg); display: block;">TOP 5 COMBOS DE LA SEMANA</span>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3 px-md-4">
                    @if(isset($topSemanal) && count($topSemanal) > 0)
                        @foreach($topSemanal as $combo)
                            <div class="d-flex align-items-center justify-content-between p-3" style="background: rgba(0,0,0,0.9); border: 3px solid {{ $combo->posicion == 1 ? 'var(--sbbl-gold)' : '#333' }}; transform: skewX(-5deg); box-shadow: {{ $combo->posicion == 1 ? '5px 5px 0 var(--shonen-red)' : '5px 5px 0 #000' }};">
                                <div class="font-bangers" style="font-size: 3rem; color: {{ $combo->posicion == 1 ? 'var(--sbbl-gold)' : '#ff2a2a' }}; text-shadow: 2px 2px 0 #fff; min-width: 60px; transform: skewX(5deg);">
                                    #{{ $combo->posicion }}
                                </div>
                                <div class="font-bangers text-white flex-grow-1 px-3 text-truncate text-uppercase" style="font-size: 1.6rem; letter-spacing: 2px; transform: skewX(5deg);">
                                    @php $nombreFormateado = str_replace('|', '<span style="color:var(--sbbl-gold);">|</span>', $combo->nombre); @endphp
                                    {!! $nombreFormateado !!}
                                </div>
                                <div class="d-flex align-items-center gap-3" style="transform: skewX(5deg);">
                                    <div class="text-end d-none d-sm-block text-white">
                                        <span class="d-block font-bangers" style="font-size: 1.8rem; line-height: 1;">{{ $combo->usos }}</span>
                                        <span style="font-weight: 900; font-size: 0.8rem; opacity: 0.6;">USOS</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center bg-black border {{ $combo->posicion == 1 ? 'border-warning' : 'border-secondary' }} rounded-circle" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                        {{ $combo->tendencia_icono }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="text-center mt-5 mb-2">
                    <span class="font-bangers" style="font-size: 1.5rem; letter-spacing: 2px; color: #fff; opacity: 0.5;">SBBL OFICIAL - ESPAÑA</span>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-4 pt-0 mt-3">
                <button type="button" class="btn-shonen btn-shonen-info" onclick="downloadImage()">
                    <span><i class="fas fa-camera me-1"></i> DESCARGAR IMAGEN</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function downloadImage() {
        const node = document.getElementById('export-top-node');
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>PROCESANDO...</span>';

        html2canvas(node, {
            scale: 2,
            backgroundColor: "#111111",
            useCORS: true
        }).then(canvas => {
            btn.innerHTML = originalText;
            let link = document.createElement('a');
            link.download = 'sbbl_top_semanal.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        }).catch(err => {
            btn.innerHTML = originalText;
            alert("Error al generar la imagen.");
        });
    }
</script>
@endsection
