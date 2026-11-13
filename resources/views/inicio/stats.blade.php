@extends('layouts.app')

@section('title', 'Radar Táctico, Tier List & Bey-Lab PRO - SBBL')

@section('styles')
<style>
    .page-title {
        font-family: 'Oswald', sans-serif;
        font-size: 2rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    /* ── SIDEBAR IZQUIERDO LIMPIO Y OCULTABLE ── */
    .stats-sidebar {
        background-color: #0b0f19;
        border: 2px solid #1e293b;
        box-shadow: 4px 4px 0 #000;
        padding: 18px;
    }
    @media (min-width: 992px) {
        .stats-sidebar-sticky {
            position: sticky;
            top: 85px;
            max-height: calc(100vh - 105px);
            overflow-y: auto;
        }
        .stats-sidebar-sticky::-webkit-scrollbar { width: 5px; }
        .stats-sidebar-sticky::-webkit-scrollbar-thumb { background: #334155; }
    }

    .sidebar-section-title {
        font-family: 'Oswald', sans-serif;
        font-size: 0.9rem;
        color: var(--sbbl-gold);
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 1px solid #1e293b;
        padding-bottom: 6px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .module-switcher-box {
        background: #111827;
        border: 2px solid var(--sbbl-gold);
        padding: 12px;
        margin-bottom: 20px;
    }
    .module-select {
        background-color: #05070c !important;
        border: 1px solid #475569 !important;
        color: #fff !important;
        font-family: 'Oswald', sans-serif;
        font-size: 1.05rem;
        letter-spacing: 0.6px;
        padding: 8px 12px;
        border-radius: 0;
        cursor: pointer;
    }

    .filter-label {
        font-family: 'Oswald', sans-serif;
        font-size: 0.8rem;
        color: #94a3b8;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        margin-bottom: 4px;
        display: block;
    }
    .stats-sidebar .form-select,
    .stats-sidebar .form-control {
        background-color: #05070c !important;
        border: 1px solid #334155 !important;
        color: #fff !important;
        border-radius: 0;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 6px 10px;
    }

    .help-badge-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        border-radius: 50%;
        background: #1e293b;
        color: var(--shonen-cyan);
        border: 1px solid var(--shonen-cyan);
        font-family: sans-serif;
        font-size: 0.7rem;
        font-weight: 800;
        cursor: pointer;
        margin-left: 5px;
        vertical-align: middle;
    }
    .help-badge-btn:hover {
        background: var(--shonen-cyan);
        color: #000;
    }

    .kpi-box {
        background: #0b0f19;
        border: 2px solid #1e293b;
        border-left: 4px solid var(--sbbl-gold);
        padding: 12px 16px;
    }

    /* ── TABLAS MÁS LEGIBLES Y CON MAYOR AIRE VISUAL ── */
    .table-responsive-arena {
        border: 2px solid #1e293b;
        box-shadow: 6px 6px 0px #000;
        max-height: 80vh;
        overflow-y: auto;
        background: #080b12;
    }
    .table-responsive-arena thead th {
        position: sticky;
        top: 0;
        background-color: #05070c !important;
        border-bottom: 2px solid var(--sbbl-gold) !important;
        z-index: 2;
        padding: 14px 12px !important;
        font-family: 'Oswald', sans-serif;
        letter-spacing: 0.8px;
        font-weight: 600;
    }
    .table-responsive-arena thead th a {
        font-family: 'Oswald', sans-serif;
        font-size: 0.98rem;
        color: #e2e8f0;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .table-responsive-arena thead th a.active-sort { color: var(--sbbl-gold); }

    .combo-name {
        font-family: 'Oswald', sans-serif;
        font-size: 1.35rem;
        color: var(--sbbl-gold);
        line-height: 1.15;
    }
    .combo-parts-line {
        font-family: 'Oswald', sans-serif;
        font-size: 0.98rem;
        color: #f8fafc;
        letter-spacing: 0.4px;
        margin-top: 2px;
    }

    .energy-bar-bg { background-color: #000; border: 1px solid #334155; height: 9px; margin-top: 5px; overflow: hidden; }
    .energy-bar-fill { height: 100%; transition: width 0.4s ease; }
    .win-rate-text { font-family: 'Oswald', sans-serif; font-size: 1.25rem; line-height: 1; }

    .wr-s { color: #10b981; } .bg-wr-s { background: #10b981; }
    .wr-a { color: var(--shonen-cyan); } .bg-wr-a { background: var(--shonen-cyan); }
    .wr-b { color: var(--sbbl-gold); } .bg-wr-b { background: var(--sbbl-gold); }
    .wr-c { color: var(--shonen-red); } .bg-wr-c { background: var(--shonen-red); }

    .eficiencia-badge {
        background: #0f172a;
        border: 1px solid #475569;
        color: var(--sbbl-gold);
        font-family: 'Oswald', sans-serif;
        font-size: 1.05rem;
        padding: 3px 12px;
        display: inline-block;
    }

    .collapse-row {
        background-color: #05070c;
        border-bottom: 2px solid var(--sbbl-gold);
    }

    /* ── TIER LIST DE 7 NIVELES ── */
    .tier-row {
        display: flex;
        border: 2px solid #1e293b;
        background: #080b12;
        margin-bottom: 10px;
        min-height: 82px;
    }
    .tier-badge-col {
        width: 92px;
        min-width: 92px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-family: 'Oswald', sans-serif;
        border-right: 2px solid #000;
        font-weight: 800;
    }
    .tier-ss-bg { background: linear-gradient(135deg, #d946ef, #9333ea); color: #fff; }
    .tier-s-bg  { background: linear-gradient(135deg, #ff2a2a, #ea580c); color: #fff; }
    .tier-ap-bg { background: linear-gradient(135deg, #f97316, #facc15); color: #000; }
    .tier-a-bg  { background: linear-gradient(135deg, #ffc107, #fde047); color: #000; }
    .tier-b-bg  { background: linear-gradient(135deg, #00ffcc, #38bdf8); color: #000; }
    .tier-c-bg  { background: linear-gradient(135deg, #3b82f6, #6366f1); color: #fff; }
    .tier-d-bg  { background: linear-gradient(135deg, #475569, #64748b); color: #fff; }

    .tier-items-col {
        flex: 1;
        padding: 10px 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .tier-part-card {
        background: #111827;
        border: 1px solid #334155;
        padding: 6px 12px;
        min-width: 135px;
    }

    /* ── TARJETAS DE MÓDULOS Y TIMELINE DE REPLAY ── */
    .lab-card {
        background: #0b0f19;
        border: 2px solid #1e293b;
        box-shadow: 5px 5px 0 #000;
    }
    .lab-slot {
        background: #111827;
        border: 1px solid #1f2937;
        border-left: 4px solid var(--shonen-cyan);
        padding: 10px 12px;
    }
    .lab-slot-rival { border-left-color: var(--shonen-red); }

    .mode-switch-btn {
        font-family: 'Oswald', sans-serif;
        border: 1px solid #334155;
        background: #05070c;
        color: #94a3b8;
        padding: 7px 14px;
        cursor: pointer;
        transition: 0.2s;
    }
    .mode-switch-btn.active {
        background: var(--sbbl-gold);
        color: #000;
        border-color: #000;
        font-weight: 700;
    }

    .tourney-pill-btn {
        background: #05070c;
        border: 1px solid #334155;
        color: #cbd5e1;
        padding: 10px 14px;
        min-width: 215px;
        text-align: left;
        transition: 0.15s;
        cursor: pointer;
    }
    .tourney-pill-btn:hover { border-color: var(--shonen-cyan); color: #fff; }
    .tourney-pill-btn.active {
        background: #172554;
        border-color: var(--sbbl-gold);
        color: #fff;
    }

    /* Línea de tiempo (Replay de Combate) */
    .timeline-step {
        position: relative;
        padding-left: 18px;
        border-left: 2px solid #334155;
        margin-bottom: 8px;
    }
    .timeline-step::before {
        content: '';
        position: absolute;
        left: -5px;
        top: 6px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--sbbl-gold);
    }

    #topSemanalModal { z-index: 1250 !important; }
    .modal-backdrop { z-index: 1240 !important; }
    .modal-content-export {
        background: #080b12 !important;
        border: 4px solid var(--sbbl-gold);
        border-radius: 0;
        box-shadow: 10px 10px 0 var(--shonen-red);
        color: #fff;
    }
    .export-body {
        background-color: #0b0f19;
        background-image: radial-gradient(rgba(255, 255, 255, 0.06) 2px, transparent 2px);
        background-size: 24px 24px;
        position: relative;
    }
</style>
@endsection

@section('content')
@php
    $activeTabInitial = request('vista', 'combos');
@endphp

<div class="container-fluid px-3 px-xl-4 py-3 mb-5">

    {{-- CABECERA COMPACTA CON BOTÓN DE OCULTAR/MOSTRAR FILTROS --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pb-3 mb-3 border-bottom border-dark gap-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <button type="button" id="btnToggleSidebarDesktop" onclick="toggleStatsSidebar()" class="btn btn-dark border border-secondary text-warning font-Oswald px-3 py-1.5">
                <span id="lblToggleSidebar">OCULTAR FILTROS</span>
            </button>
            <h2 class="page-title m-0">CENTRO DE TELEMETRÍA & BEY-LAB PRO</h2>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" class="btn-shonen btn-shonen-warning" style="font-size: 0.9rem; padding: 5px 14px;" data-bs-toggle="modal" data-bs-target="#topSemanalModal">
                <span>TOP 5 SEMANAL</span>
            </button>
        </div>
    </div>

    <div class="row g-4 align-items-start">

        {{-- ====================================================================
             COLUMNA IZQUIERDA: SELECTOR LIMPIO DE MÓDULO + FILTROS VERTICALES
             ==================================================================== --}}
        <div class="col-12 col-lg-3" id="statsSidebarCol">
            <aside class="stats-sidebar stats-sidebar-sticky">

                {{-- 1. DESPLEGABLE LIMPIO SIN ICONOS PARA CAMBIAR DE MÓDULO (6 MÓDULOS) --}}
                <div class="module-switcher-box">
                    <label class="font-Oswald text-warning small d-block mb-1">SELECCIONAR VISTA</label>
                    <select id="sidebarModuleSelect" class="form-select module-select" onchange="switchStatsMainTab(this.value)">
                        <option value="combos" {{ $activeTabInitial === 'combos' ? 'selected' : '' }}>1. Radar de Combos</option>
                        <option value="tierlist" {{ $activeTabInitial === 'tierlist' ? 'selected' : '' }}>2. Tier List de Piezas (SS - D)</option>
                        <option value="partes" {{ $activeTabInitial === 'partes' ? 'selected' : '' }}>3. Tabla por Piezas Aisladas</option>
                        <option value="lab" {{ $activeTabInitial === 'lab' ? 'selected' : '' }}>4. Simulador Bey-Lab PRO</option>
                        <option value="torneos" {{ $activeTabInitial === 'torneos' ? 'selected' : '' }}>5. Telemetría de Torneos</option>
                        <option value="h2h" {{ $activeTabInitial === 'h2h' ? 'selected' : '' }}>6. Cara a Cara (Jugador vs Jugador)</option>
                    </select>
                </div>

                {{-- 2. FORMULARIO DE FILTROS LATERALES --}}
                <form method="GET" action="{{ route('stats.index') }}" id="filterForm">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="order" value="{{ $order }}">
                    <input type="hidden" name="vista" id="hidden_vista_input" value="{{ $activeTabInitial }}">

                    <div class="sidebar-section-title">
                        <span>FILTRAR POR PIEZAS</span>
                        <a href="{{ route('stats.index') }}" class="text-info text-decoration-none small">Limpiar</a>
                    </div>

                    <div class="mb-2">
                        <label class="filter-label">Blade Principal</label>
                        <select name="blade" class="form-select mb-2" onchange="this.form.submit()">
                            <option value="">Todas las Blades</option>
                            @foreach($blades as $b)
                                <option value="{{ $b }}" {{ $b == request('blade') ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="filter-label">Over Blade</label>
                            <select name="over_blade" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($overBlades as $ob)
                                    <option value="{{ $ob }}" {{ $ob == request('over_blade') ? 'selected' : '' }}>{{ $ob }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="filter-label">Assist Blade</label>
                            <select name="assist_blade" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($assistBlades as $ab)
                                    <option value="{{ $ab }}" {{ $ab == request('assist_blade') ? 'selected' : '' }}>{{ $ab }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="filter-label">Ratchet</label>
                            <select name="ratchet" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($ratchets as $r)
                                    <option value="{{ $r }}" {{ $r == request('ratchet') ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="filter-label">Bit</label>
                            <select name="bit" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                @foreach($bits as $bit)
                                    <option value="{{ $bit }}" {{ $bit == request('bit') ? 'selected' : '' }}>{{ $bit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="sidebar-section-title mt-3">
                        <span>CONTEXTO DE TORNEO</span>
                    </div>

                    <div class="mb-2">
                        <label class="filter-label">Tipo de Evento</label>
                        <select name="tipo_evento" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los Eventos</option>
                            <option value="ranking" {{ $tipoEvento == 'ranking' ? 'selected' : '' }}>Ranking Oficial</option>
                            <option value="quedada" {{ $tipoEvento == 'quedada' ? 'selected' : '' }}>Quedadas</option>
                            <option value="copaespecial1" {{ $tipoEvento == 'copaespecial1' ? 'selected' : '' }}>Copa Solo CX</option>
                            <option value="crew_battle" {{ $tipoEvento == 'crew_battle' ? 'selected' : '' }}>Torneos de Equipos</option>
                            <option value="hasbro" {{ $tipoEvento == 'hasbro' ? 'selected' : '' }}>Hasbro</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="filter-label">Región</label>
                            <select name="region_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Toda España</option>
                                @foreach($regions as $reg)
                                    <option value="{{ $reg->id }}" {{ $regionId == $reg->id ? 'selected' : '' }}>{{ strtoupper($reg->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="filter-label">Mín. Combates</label>
                            <select name="min_partidas" class="form-select" onchange="this.form.submit()">
                                <option value="1" {{ $minPartidas == 1 ? 'selected' : '' }}>1+ Combates</option>
                                <option value="3" {{ $minPartidas == 3 ? 'selected' : '' }}>3+ Combates</option>
                                <option value="5" {{ $minPartidas == 5 ? 'selected' : '' }}>5+ Combates</option>
                                <option value="10" {{ $minPartidas == 10 ? 'selected' : '' }}>10+ Combates</option>
                                <option value="25" {{ $minPartidas == 25 ? 'selected' : '' }}>25+ Combates</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="filter-label">Desde Fecha</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="{{ $fechaInicio }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-6">
                            <label class="filter-label">Hasta Fecha</label>
                            <input type="date" name="fecha_fin" class="form-control" value="{{ $fechaFin }}" onchange="this.form.submit()">
                        </div>
                    </div>

                    @if(Auth::check())
                        <div class="bg-black border border-secondary p-2.5 mb-3">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="userParts" name="only_user_parts" value="on" {{ request('only_user_parts') ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label font-Oswald ms-1 text-info small" for="userParts">
                                    SOLO MIS COMBATES
                                </label>
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('stats.index') }}" class="btn btn-outline-secondary text-light w-100 font-Oswald rounded-0">
                        RESTABLECER FILTROS
                    </a>
                </form>

            </aside>
        </div>

        {{-- ====================================================================
             COLUMNA DERECHA: CONTENIDO PRINCIPAL DE LOS 6 MÓDULOS
             ==================================================================== --}}
        <div class="col-12 col-lg-9" id="statsMainContentCol">

            {{-- RESUMEN KPI SUPERIOR --}}
            @php
                $totalCombosListados = $beybladeStats->count();
                $totalLanzamientos   = $beybladeStats->sum('total_partidas');
                $mejorCombo          = $beybladeStats->first();
                $totalXtremesGlobal  = $globalFinishes['xtreme'] ?? 0;
            @endphp
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="kpi-box">
                        <div class="text-secondary font-Oswald small">COMBOS EN RADAR</div>
                        <div class="font-Oswald fs-3 text-white lh-1 mt-1">{{ $totalCombosListados }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-box" style="border-left-color: var(--shonen-cyan);">
                        <div class="text-secondary font-Oswald small">LANZAMIENTOS</div>
                        <div class="font-Oswald fs-3 text-info lh-1 mt-1">{{ number_format($totalLanzamientos) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-box" style="border-left-color: #10b981;">
                        <div class="text-secondary font-Oswald small">LÍDER PONDERADO (#1)</div>
                        <div class="font-Oswald fs-6 text-warning text-truncate lh-1 mt-2">
                            {{ $mejorCombo ? $mejorCombo->blade . ' ' . $mejorCombo->ratchet . ' ' . $mejorCombo->bit : '---' }}
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="kpi-box" style="border-left-color: #d946ef;">
                        <div class="text-secondary font-Oswald small">XTREME FINISHES</div>
                        <div class="font-Oswald fs-3 lh-1 mt-1" style="color: #e879f9;">{{ number_format($totalXtremesGlobal) }}</div>
                    </div>
                </div>
            </div>

            {{-- ====================================================================
                 MÓDULO 01: RADAR DE COMBOS (CON GRÁFICO DE EVOLUCIÓN MENSUAL)
                 ==================================================================== --}}
            <div id="main-tab-combos" class="{{ $activeTabInitial === 'combos' ? '' : 'd-none' }}">
                <div class="table-responsive-arena">
                    <table class="table table-dark table-hover mb-0 align-middle border-0">
                        <thead>
                            <tr>
                                <th style="min-width: 230px;" class="ps-3">
                                    <a href="{{ route('stats.index', array_merge(request()->query(), ['vista' => 'combos', 'sort' => 'blade', 'order' => ($sort == 'blade' && $order == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="{{ $sort == 'blade' ? 'active-sort' : '' }}">
                                        # / CONFIGURACIÓN DEL COMBO
                                    </a>
                                </th>
                                <th class="text-center" style="min-width: 140px;">
                                    <a href="{{ route('stats.index', array_merge(request()->query(), ['vista' => 'combos', 'sort' => 'meta_score', 'order' => ($sort == 'meta_score' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                                       class="{{ $sort == 'meta_score' ? 'active-sort' : '' }}">
                                        META SCORE
                                    </a>
                                    <button type="button" class="help-badge-btn" onclick="abrirInfoModal('META SCORE (RANKING PONDERADO)', 'Combina el porcentaje de victorias con el volumen de partidas jugadas para que los combos más probados y consistentes en torneos se sitúen por delante de combos con pocas partidas.')">?</button>
                                </th>
                                <th class="text-center" style="min-width: 170px;">
                                    <a href="{{ route('stats.index', array_merge(request()->query(), ['vista' => 'combos', 'sort' => 'percentage_victories', 'order' => ($sort == 'percentage_victories' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                                       class="{{ $sort == 'percentage_victories' ? 'active-sort' : '' }}">
                                        WINRATE REAL
                                    </a>
                                </th>
                                <th class="text-center" style="min-width: 180px;">
                                    <span class="text-info">TIPOS DE FINAL</span>
                                    <button type="button" class="help-badge-btn" onclick="abrirInfoModal('DISTRIBUCIÓN DE FINALES', 'Desglose de victorias por tipo de final: Spin (1 pt, verde), Over (2 pts, azul), Burst (2 pts, amarillo) y Xtreme (3 pts, fucsia).')">?</button>
                                </th>
                                <th class="text-center">
                                    <a href="{{ route('stats.index', array_merge(request()->query(), ['vista' => 'combos', 'sort' => 'total_partidas', 'order' => ($sort == 'total_partidas' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                                       class="{{ $sort == 'total_partidas' ? 'active-sort' : '' }}">
                                        BATALLAS
                                    </a>
                                </th>
                                <th class="text-center">
                                    <a href="{{ route('stats.index', array_merge(request()->query(), ['vista' => 'combos', 'sort' => 'puntos_ganados_por_combate', 'order' => ($sort == 'puntos_ganados_por_combate' && $order == 'desc') ? 'asc' : 'desc'])) }}"
                                       class="{{ $sort == 'puntos_ganados_por_combate' ? 'active-sort' : '' }}">
                                        DAÑO (1-3)
                                    </a>
                                </th>
                                <th class="text-end pe-3">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($beybladeStats as $index => $stat)
                                @php
                                    $wr = (float) $stat->percentage_victories;
                                    if ($wr >= 60) { $cText = 'wr-s'; $cBg = 'bg-wr-s'; }
                                    elseif ($wr >= 50) { $cText = 'wr-a'; $cBg = 'bg-wr-a'; }
                                    elseif ($wr >= 40) { $cText = 'wr-b'; $cBg = 'bg-wr-b'; }
                                    else { $cText = 'wr-c'; $cBg = 'bg-wr-c'; }

                                    $winsTot = max(1, (int) $stat->total_victorias);
                                    $pSpin   = round(($stat->spin_wins / $winsTot) * 100);
                                    $pOver   = round(($stat->over_wins / $winsTot) * 100);
                                    $pBurst  = round(($stat->burst_wins / $winsTot) * 100);
                                    $pXtreme = round(($stat->xtreme_wins / $winsTot) * 100);

                                    $partesSecundarias = [];
                                    if (!empty($stat->over_blade) && $stat->over_blade !== '-') $partesSecundarias[] = $stat->over_blade;
                                    if (!empty($stat->assist_blade) && $stat->assist_blade !== '-') $partesSecundarias[] = $stat->assist_blade;
                                    $partesSecundarias[] = $stat->ratchet;
                                    $partesSecundarias[] = $stat->bit;

                                    $collapseId = "details-combo-" . $index;
                                @endphp

                                <tr>
                                    <td class="py-3 ps-3 border-0">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <span class="font-Oswald fs-4 {{ $index < 3 ? 'text-warning' : 'text-secondary' }}" style="min-width: 34px;">#{{ $index + 1 }}</span>
                                            <div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="combo-name">{{ $stat->blade }}</span>
                                                    <span class="badge bg-black border border-secondary text-secondary" style="font-size: 0.62rem;">{{ $stat->estilo_combate }}</span>
                                                </div>
                                                <div class="combo-parts-line">
                                                    {!! implode(' <span class="text-warning mx-1">·</span> ', array_map('e', $partesSecundarias)) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center border-0">
                                        <span class="eficiencia-badge">{{ number_format($stat->meta_score, 1) }}</span>
                                    </td>

                                    <td class="border-0 px-2">
                                        <div class="d-flex justify-content-between align-items-end mb-1">
                                            <span class="win-rate-text {{ $cText }}">{{ number_format($wr, 1) }}%</span>
                                            <span class="fw-bold text-light opacity-75 small">{{ $stat->total_victorias }}W - {{ $stat->total_derrotas }}L</span>
                                        </div>
                                        <div class="energy-bar-bg">
                                            <div class="energy-bar-fill {{ $cBg }}" style="width: {{ min(100, $wr) }}%"></div>
                                        </div>
                                    </td>

                                    <td class="border-0 px-2">
                                        <div class="d-flex justify-content-between text-uppercase fw-bold" style="font-size: 0.66rem;">
                                            <span class="text-success">SPN:{{ $stat->spin_wins }}</span>
                                            <span class="text-info">OVR:{{ $stat->over_wins }}</span>
                                            <span class="text-warning">BRS:{{ $stat->burst_wins }}</span>
                                            <span style="color:#e879f9;">XTR:{{ $stat->xtreme_wins }}</span>
                                        </div>
                                        <div class="d-flex w-100 bg-black border border-secondary mt-1" style="height: 7px; overflow: hidden;">
                                            <div style="width: {{ $pSpin }}%; background: #10b981;"></div>
                                            <div style="width: {{ $pOver }}%; background: #0ea5e9;"></div>
                                            <div style="width: {{ $pBurst }}%; background: #f59e0b;"></div>
                                            <div style="width: {{ $pXtreme }}%; background: #d946ef;"></div>
                                        </div>
                                    </td>

                                    <td class="text-center border-0 font-Oswald fs-4 text-white">
                                        {{ $stat->total_partidas }}
                                    </td>

                                    <td class="text-center border-0">
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success font-Oswald me-1">
                                            +{{ number_format($stat->puntos_ganados_por_combate, 2) }}
                                        </span>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger font-Oswald">
                                            -{{ number_format($stat->puntos_perdidos_por_combate, 2) }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-3 border-0">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    onclick="enviarComboAlLab('{{ addslashes($stat->blade) }}', '{{ addslashes($stat->over_blade) }}', '{{ addslashes($stat->assist_blade) }}', '{{ addslashes($stat->ratchet) }}', '{{ addslashes($stat->bit) }}')"
                                                    class="btn btn-sm btn-outline-info font-Oswald px-2 py-1"
                                                    title="Enviar a Tu Deck en el Bey-Lab">
                                                A LAB
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning font-Oswald px-2.5 py-1" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $collapseId }}">
                                                DETALLE
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- FILA DESPLEGABLE CON ENFRENTAMIENTOS Y EVOLUCIÓN TEMPORAL --}}
                                <tr>
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="collapse" id="{{ $collapseId }}">
                                            <div class="collapse-row p-4">
                                                <div class="row g-4">
                                                    <div class="col-md-4">
                                                        <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                                                            <h6 class="font-Oswald text-warning m-0">BALANCE POR FINAL (W / L)</h6>
                                                            <button type="button"
                                                                    onclick="abrirModalEvolucionCombo('{{ addslashes($stat->blade) }}', '{{ addslashes($stat->ratchet) }}', '{{ addslashes($stat->bit) }}')"
                                                                    class="btn btn-xs btn-outline-info font-Oswald py-0.5 px-2"
                                                                    style="font-size: 0.75rem;">
                                                                VER EVOLUCIÓN MENSUAL
                                                            </button>
                                                        </div>
                                                        <div class="row g-2 text-center font-Oswald">
                                                            <div class="col-6">
                                                                <div class="bg-dark border border-secondary p-2">
                                                                    <div class="small text-secondary">SPIN (1 PT)</div>
                                                                    <div class="fs-5"><span class="text-success">{{ $stat->spin_wins }}W</span> / <span class="text-danger">{{ $stat->spin_losses }}L</span></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="bg-dark border border-secondary p-2">
                                                                    <div class="small text-secondary">OVER (2 PTS)</div>
                                                                    <div class="fs-5"><span class="text-success">{{ $stat->over_wins }}W</span> / <span class="text-danger">{{ $stat->over_losses }}L</span></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="bg-dark border border-secondary p-2">
                                                                    <div class="small text-secondary">BURST (2 PTS)</div>
                                                                    <div class="fs-5"><span class="text-success">{{ $stat->burst_wins }}W</span> / <span class="text-danger">{{ $stat->burst_losses }}L</span></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="bg-dark border border-secondary p-2">
                                                                    <div class="small text-secondary">XTREME (3 PTS)</div>
                                                                    <div class="fs-5"><span class="text-success">{{ $stat->xtreme_wins }}W</span> / <span class="text-danger">{{ $stat->xtreme_losses }}L</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <h6 class="font-Oswald text-success border-bottom border-secondary pb-2 mb-3">
                                                            FUERTE CONTRA (PRESAS FRECUENTES)
                                                        </h6>
                                                        @forelse($stat->presas as $presa)
                                                            <div class="d-flex justify-content-between align-items-center bg-dark border border-secondary px-3 py-1.5 mb-1">
                                                                <span class="small fw-bold text-white text-uppercase">{{ $presa['combo'] }}</span>
                                                                <span class="badge bg-success text-dark fw-bold">{{ $presa['count'] }} WINS</span>
                                                            </div>
                                                        @empty
                                                            <div class="text-secondary small fst-italic">Sin suficientes duelos directos registrados.</div>
                                                        @endforelse
                                                    </div>

                                                    <div class="col-md-4">
                                                        <h6 class="font-Oswald text-danger border-bottom border-secondary pb-2 mb-3">
                                                            DÉBIL CONTRA (COUNTERS / NÉMESIS)
                                                        </h6>
                                                        @forelse($stat->nemesis as $nem)
                                                            <div class="d-flex justify-content-between align-items-center bg-dark border border-secondary px-3 py-1.5 mb-1">
                                                                <span class="small fw-bold text-white text-uppercase">{{ $nem['combo'] }}</span>
                                                                <span class="badge bg-danger text-white fw-bold">{{ $nem['count'] }} LOSSES</span>
                                                            </div>
                                                        @empty
                                                            <div class="text-secondary small fst-italic">Sin suficientes duelos directos registrados.</div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-white border-0">
                                        <span class="font-Oswald fs-4 text-secondary">NO SE ENCONTRARON COMBOS CON LOS FILTROS SELECCIONADOS.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ====================================================================
                 MÓDULO 02: TIER LIST DE PIEZAS EN 7 NIVELES (SS / S / A+ / A / B / C / D)
                 ==================================================================== --}}
            <div id="main-tab-tierlist" class="{{ $activeTabInitial === 'tierlist' ? '' : 'd-none' }}">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-black border border-secondary p-3 mb-3 gap-2">
                    <div>
                        <h4 class="font-Oswald text-warning m-0">
                            TIER LIST DE PIEZAS EN EL META (7 NIVELES)
                            <button type="button" class="help-badge-btn" onclick="abrirInfoModal('¿CÓMO SE CALCULA LA TIER LIST DE 7 NIVELES?', 'Cada pieza se clasifica según su Meta Score (Winrate ponderado por volumen de uso en torneos oficiales):\n\n• TIER SS (Top 6%): Dominante absoluto\n• TIER S (Top 16%): Meta Staple\n• TIER A+ (Top 30%): Alto Rendimiento\n• TIER A (Top 50%): Competitivo Sólido\n• TIER B (Top 72%): Viable / Situacional\n• TIER C (Top 88%): Nicho / Rogue\n• TIER D: Experimental.')">?</button>
                        </h4>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5">
                        <button type="button" onclick="switchTierCategory('blades')" id="btn-tier-blades" class="btn btn-sm btn-warning fw-bold font-Oswald">BLADES</button>
                        <button type="button" onclick="switchTierCategory('ratchets')" id="btn-tier-ratchets" class="btn btn-sm btn-outline-light fw-bold font-Oswald">RATCHETS</button>
                        <button type="button" onclick="switchTierCategory('bits')" id="btn-tier-bits" class="btn btn-sm btn-outline-light fw-bold font-Oswald">BITS</button>
                        <button type="button" onclick="switchTierCategory('over_blades')" id="btn-tier-over_blades" class="btn btn-sm btn-outline-light fw-bold font-Oswald">OVER BLADES</button>
                        <button type="button" onclick="switchTierCategory('assist_blades')" id="btn-tier-assist_blades" class="btn btn-sm btn-outline-light fw-bold font-Oswald">ASSIST BLADES</button>
                    </div>
                </div>

                @foreach(['blades', 'ratchets', 'bits', 'over_blades', 'assist_blades'] as $catKey)
                    <div id="tier-container-{{ $catKey }}" class="{{ $catKey === 'blades' ? '' : 'd-none' }}">
                        @php
                            $itemsCat = collect($partStats[$catKey] ?? []);
                            $tiersMap = [
                                'SS' => ['label' => 'TIER SS', 'sub' => 'DOMINANTE',    'class' => 'tier-ss-bg'],
                                'S'  => ['label' => 'TIER S',  'sub' => 'META STAPLE',  'class' => 'tier-s-bg'],
                                'A+' => ['label' => 'TIER A+', 'sub' => 'ALTO NIVEL',   'class' => 'tier-ap-bg'],
                                'A'  => ['label' => 'TIER A',  'sub' => 'COMPETITIVO',  'class' => 'tier-a-bg'],
                                'B'  => ['label' => 'TIER B',  'sub' => 'VIABLE',       'class' => 'tier-b-bg'],
                                'C'  => ['label' => 'TIER C',  'sub' => 'SITUACIONAL',  'class' => 'tier-c-bg'],
                                'D'  => ['label' => 'TIER D',  'sub' => 'EXPERIMENTAL', 'class' => 'tier-d-bg'],
                            ];
                        @endphp

                        @foreach($tiersMap as $tCode => $tMeta)
                            @php $tierItems = $itemsCat->where('tier', $tCode); @endphp
                            <div class="tier-row">
                                <div class="tier-badge-col {{ $tMeta['class'] }}">
                                    <span class="fs-3 lh-1">{{ $tMeta['label'] }}</span>
                                    <span style="font-size: 0.62rem; letter-spacing: 0.5px;">{{ $tMeta['sub'] }}</span>
                                </div>
                                <div class="tier-items-col">
                                    @forelse($tierItems as $pItem)
                                        <div class="tier-part-card">
                                            <div class="font-Oswald fs-5 text-white">{{ $pItem->part_name }}</div>
                                            <div class="d-flex justify-content-between align-items-center gap-3 mt-1">
                                                <span class="badge bg-black border border-secondary text-warning font-Oswald">{{ $pItem->winrate }}% WR</span>
                                                <span class="small text-secondary fw-bold">{{ $pItem->total_partidas }} comb.</span>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-secondary small fst-italic">Sin piezas en este rango con los filtros actuales.</span>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            {{-- ====================================================================
                 MÓDULO 03: TABLA DETALLADA POR PIEZAS AISLADAS
                 ==================================================================== --}}
            <div id="main-tab-partes" class="{{ $activeTabInitial === 'partes' ? '' : 'd-none' }}">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" onclick="switchPartSubTab('blades')" id="btn-part-blades" class="btn btn-sm btn-warning fw-bold font-Oswald">BLADES ({{ count($partStats['blades']) }})</button>
                    <button type="button" onclick="switchPartSubTab('over_blades')" id="btn-part-over_blades" class="btn btn-sm btn-outline-light fw-bold font-Oswald">OVER BLADES ({{ count($partStats['over_blades']) }})</button>
                    <button type="button" onclick="switchPartSubTab('assist_blades')" id="btn-part-assist_blades" class="btn btn-sm btn-outline-light fw-bold font-Oswald">ASSIST BLADES ({{ count($partStats['assist_blades']) }})</button>
                    <button type="button" onclick="switchPartSubTab('ratchets')" id="btn-part-ratchets" class="btn btn-sm btn-outline-light fw-bold font-Oswald">RATCHETS ({{ count($partStats['ratchets']) }})</button>
                    <button type="button" onclick="switchPartSubTab('bits')" id="btn-part-bits" class="btn btn-sm btn-outline-light fw-bold font-Oswald">BITS ({{ count($partStats['bits']) }})</button>
                </div>

                @foreach(['blades' => 'BLADE', 'over_blades' => 'OVER BLADE', 'assist_blades' => 'ASSIST BLADE', 'ratchets' => 'RATCHET', 'bits' => 'BIT'] as $key => $label)
                    <div id="subtab-part-{{ $key }}" class="{{ $key === 'blades' ? '' : 'd-none' }}">
                        <div class="table-responsive-arena">
                            <table class="table table-dark table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-4">TIER / #</th>
                                        <th>{{ $label }}</th>
                                        <th class="text-center">META SCORE</th>
                                        <th class="text-center">WINRATE</th>
                                        <th class="text-center">COMBATES (V - D)</th>
                                        <th class="text-center">DAÑO PROM. (1-3)</th>
                                        <th class="text-center">FINALES (SPN / OVR / BRS / XTR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($partStats[$key] as $pIdx => $p)
                                        @php
                                            $tBadge = match($p->tier) {
                                                'SS' => 'text-white border border-light',
                                                'S'  => 'bg-danger text-white',
                                                'A+' => 'bg-warning text-dark',
                                                'A'  => 'bg-warning bg-opacity-75 text-dark',
                                                'B'  => 'bg-info text-dark',
                                                'C'  => 'bg-primary text-white',
                                                default => 'bg-secondary text-white'
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-4 font-Oswald fs-5">
                                                <span class="badge {{ $tBadge }} me-2" style="{{ $p->tier === 'SS' ? 'background:#a21caf;' : '' }}">TIER {{ $p->tier }}</span>
                                                <span class="text-secondary">#{{ $pIdx + 1 }}</span>
                                            </td>
                                            <td class="font-Oswald fs-4 text-white">{{ $p->part_name }}</td>
                                            <td class="text-center font-Oswald fs-5 text-warning">{{ number_format($p->meta_score, 1) }}</td>
                                            <td class="text-center" style="width: 200px;">
                                                <span class="font-Oswald fs-4 {{ $p->winrate >= 55 ? 'text-success' : ($p->winrate >= 48 ? 'text-info' : 'text-warning') }}">{{ $p->winrate }}%</span>
                                                <div class="energy-bar-bg">
                                                    <div class="energy-bar-fill {{ $p->winrate >= 55 ? 'bg-wr-s' : 'bg-wr-a' }}" style="width: {{ min(100, $p->winrate) }}%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center font-Oswald fs-5">
                                                {{ $p->total_partidas }} <span class="small text-secondary">({{ $p->total_victorias }}W - {{ $p->total_derrotas }}L)</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark font-Oswald fs-6">{{ number_format($p->avg_dmg, 2) }} PTS</span>
                                            </td>
                                            <td class="text-center fw-bold small">
                                                <span class="text-success me-2">SPN:{{ $p->spins }}</span>
                                                <span class="text-info me-2">OVR:{{ $p->overs }}</span>
                                                <span class="text-warning me-2">BRS:{{ $p->bursts }}</span>
                                                <span style="color:#e879f9;">XTR:{{ $p->xtremes }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center py-4 text-secondary font-Oswald">SIN DATOS PARA ESTA CATEGORÍA</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ====================================================================
                 MÓDULO 04: SBBL BEY-LAB PRO (CON EXPORTACIÓN DE INFORME A IMAGEN PNG)
                 ==================================================================== --}}
            <div id="main-tab-lab" class="{{ $activeTabInitial === 'lab' ? '' : 'd-none' }}">
                <div class="lab-card p-3 p-md-4 mb-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center border-bottom border-secondary pb-3 mb-4 gap-3">
                        <div>
                            <h3 class="font-Oswald text-info m-0">
                                SIMULADOR TÁCTICO BEY-LAB PRO
                                <button type="button" class="help-badge-btn" onclick="abrirInfoModal('¿CÓMO FUNCIONA EL BEY-LAB PRO?', 'Configura tus 3 Beyblades y compáralos contra cualquier Deck rival o contra el Top 3 Meta actual. Calcula la matriz 3x3, recomienda el orden de salida, detecta el eslabón débil y sugiere 3 Counters legales.')">?</button>
                            </h3>
                            <p class="text-secondary small mb-0">Compara alineaciones 3on3, descubre el orden óptimo de lanzamiento y exporta tu informe táctico.</p>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <input type="hidden" id="web_lab_mode" value="global_vs_global">
                            <button type="button" id="btn-mode-global" onclick="setWebLabMode('global_vs_global')" class="mode-switch-btn active">
                                STATS GLOBALES VS GLOBAL
                            </button>
                            @if(Auth::check())
                                <button type="button" id="btn-mode-personal" onclick="setWebLabMode('personal_vs_global')" class="mode-switch-btn">
                                    MIS STATS PERSONALES VS GLOBAL
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="font-Oswald text-info m-0">BANCO 01: TU DECK 3ON3</h5>
                                @if(Auth::check())
                                    <button type="button" onclick="importarMiUltimoDeckWeb()" class="btn btn-sm btn-outline-info font-Oswald">
                                        Importar mi último Deck
                                    </button>
                                @endif
                            </div>

                            <div class="d-flex flex-column gap-2">
                                @for($i = 1; $i <= 3; $i++)
                                    <div class="lab-slot">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="font-Oswald text-info small">BEYBLADE #0{{ $i }}</span>
                                            <button type="button" onclick="limpiarSlotWebLab('my', {{ $i }})" class="btn btn-link btn-sm text-secondary p-0 text-decoration-none">Limpiar</button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4">
                                                <select id="wlab_my_blade_{{ $i }}" onchange="validarLegalidadWebLab()" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Blade...</option>
                                                    @foreach($blades as $b) <option value="{{ $b }}">{{ $b }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_my_over_{{ $i }}" onchange="validarLegalidadWebLab()" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Over</option>
                                                    @foreach($overBlades as $ob) <option value="{{ $ob }}">{{ $ob }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_my_assist_{{ $i }}" onchange="validarLegalidadWebLab()" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Assist</option>
                                                    @foreach($assistBlades as $ab) <option value="{{ $ab }}">{{ $ab }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_my_ratchet_{{ $i }}" onchange="validarLegalidadWebLab()" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Ratchet</option>
                                                    @foreach($ratchets as $r) <option value="{{ $r }}">{{ $r }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_my_bit_{{ $i }}" onchange="validarLegalidadWebLab()" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Bit</option>
                                                    @foreach($bits as $bt) <option value="{{ $bt }}">{{ $bt }}</option> @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            <div id="wlab-legality-warning" class="alert alert-danger mt-2 py-2 px-3 small fw-bold d-none"></div>
                        </div>

                        <div class="col-xl-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="font-Oswald text-danger m-0">BANCO 02: DECK RIVAL / META</h5>
                                <button type="button" onclick="cargarMetaBossWebLab()" class="btn btn-sm btn-outline-warning font-Oswald">
                                    Cargar Top 3 Meta Actual
                                </button>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                @for($i = 1; $i <= 3; $i++)
                                    <div class="lab-slot lab-slot-rival">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="font-Oswald text-danger small">RIVAL #0{{ $i }}</span>
                                            <button type="button" onclick="limpiarSlotWebLab('riv', {{ $i }})" class="btn btn-link btn-sm text-secondary p-0 text-decoration-none">Limpiar</button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-4">
                                                <select id="wlab_riv_blade_{{ $i }}" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">(Auto Top Meta)</option>
                                                    @foreach($blades as $b) <option value="{{ $b }}">{{ $b }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_riv_over_{{ $i }}" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Over</option>
                                                    @foreach($overBlades as $ob) <option value="{{ $ob }}">{{ $ob }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_riv_assist_{{ $i }}" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Assist</option>
                                                    @foreach($assistBlades as $ab) <option value="{{ $ab }}">{{ $ab }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_riv_ratchet_{{ $i }}" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Ratchet</option>
                                                    @foreach($ratchets as $r) <option value="{{ $r }}">{{ $r }}</option> @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-2">
                                                <select id="wlab_riv_bit_{{ $i }}" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="">Bit</option>
                                                    @foreach($bits as $bt) <option value="{{ $bt }}">{{ $bt }}</option> @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4 pt-3 border-top border-secondary">
                        <button type="button" onclick="ejecutarAnalisisWebLab()" id="btnRunWebLab" class="btn-shonen btn-shonen-warning px-5 py-2">
                            <span>ANALIZAR ENFRENTAMIENTO 3X3</span>
                        </button>
                    </div>
                </div>

                {{-- CONTENEDOR EXPORTABLE DE RESULTADOS DEL BEY-LAB PRO --}}
                <div id="web-lab-results" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div id="wlab-mode-banner" class="bg-black border border-info text-info font-Oswald py-1.5 px-3"></div>
                        <button type="button" onclick="exportarInformeLabPNG(this)" class="btn btn-sm btn-outline-warning font-Oswald px-3 py-1.5">
                            DESCARGAR INFORME TÁCTICO (PNG)
                        </button>
                    </div>

                    <div id="wlab-export-capture-area" class="p-2" style="background:#060911;">
                        <div class="row g-3 mb-4" id="wlab-tactical-advisor"></div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-7">
                                <div class="lab-card p-4 h-100">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <div>
                                            <div class="text-secondary font-Oswald small">
                                                EXPECTATIVA GLOBAL DE VICTORIA
                                                <button type="button" class="help-badge-btn" onclick="abrirInfoModal('EXPECTATIVA GLOBAL DE VICTORIA', 'Media de probabilidad de victoria en los 9 cruces posibles entre tus 3 Beyblades y los 3 Beyblades del rival.')">?</button>
                                            </div>
                                            <div id="wlab-overall-wr" class="font-Oswald display-4 text-info lh-1">50.0%</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-secondary font-Oswald small">DECK RIVAL / META</div>
                                            <div id="wlab-rival-wr" class="font-Oswald fs-2 text-danger lh-1">50.0%</div>
                                        </div>
                                    </div>
                                    <div class="energy-bar-bg mb-4" style="height: 14px;">
                                        <div id="wlab-overall-bar" class="energy-bar-fill bg-wr-a" style="width: 50%"></div>
                                    </div>

                                    <h5 class="font-Oswald text-warning mb-3">
                                        MATRIZ TÁCTICA 3X3 (CARA A CARA)
                                        <button type="button" class="help-badge-btn" onclick="abrirInfoModal('¿QUÉ SIGNIFICAN EXACTO, POR BLADE Y ESTIMADO?', '• EXACTO: Ambos combos completos se han enfrentado cara a cara al menos 3 veces en torneos.\n• POR BLADE: Basado en duelos reales entre ambas Blades.\n• ESTIMADO: Calculado según el diferencial de Winrate.')">?</button>
                                    </h5>
                                    <div class="table-responsive" id="wlab-matrix-container"></div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="lab-card p-4 h-100 d-flex flex-column justify-content-between">
                                    <h5 class="font-Oswald text-info border-bottom border-secondary pb-2 mb-3">
                                        PERFIL PENTAGONAL: TU DECK VS RIVAL
                                    </h5>
                                    <div style="height: 310px;" class="d-flex align-items-center justify-content-center">
                                        <canvas id="chartLabRadar"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lab-card p-4">
                            <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">
                                RENDIMIENTO INDIVIDUAL DE TU ALINEACIÓN
                            </h5>
                            <div class="row g-3" id="wlab-my-cards-container"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================================================================
                 MÓDULO 05: TELEMETRÍA DE TORNEOS (CON BOTÓN CARGAR EN BEY-LAB Y REPLAY VISUAL)
                 ==================================================================== --}}
            <div id="main-tab-torneos" class="{{ $activeTabInitial === 'torneos' ? '' : 'd-none' }}">
                <div class="lab-card p-3 p-md-4 mb-4">
                    <div class="border-bottom border-secondary pb-2 mb-3">
                        <h4 class="font-Oswald text-warning m-0">TELEMETRÍA Y REPLAYS DE TORNEOS</h4>
                        <p class="text-secondary small mb-0">
                            Selecciona uno de los últimos torneos para ver su resumen global, inspeccionar el Deck de cualquier jugador, cargarlo en el Bey-Lab o ver el Replay lance por lance.
                        </p>
                    </div>

                    <div class="d-flex gap-2 overflow-x-auto pb-2" id="tourneySelectorList">
                        @forelse($torneosStatsList as $tIdx => $torneoItem)
                            <button type="button"
                                    onclick="cargarTelemetriaTorneo({{ $torneoItem->id }}, this)"
                                    id="btn-tourney-item-{{ $torneoItem->id }}"
                                    class="tourney-pill-btn shrink-0 {{ $tIdx === 0 ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-warning text-dark font-Oswald">{{ strtoupper($torneoItem->beys ?? 'EVENTO') }}</span>
                                    <span class="small text-info font-Oswald">{{ \Carbon\Carbon::parse($torneoItem->date)->format('d/m/Y') }}</span>
                                </div>
                                <div class="font-Oswald text-white text-truncate" style="max-width: 210px; font-size: 1.02rem;">{{ $torneoItem->name }}</div>
                                <div class="small text-secondary mt-1">
                                    {{ $torneoItem->city }} · <b class="text-light">{{ $torneoItem->total_players }} Bladers</b>
                                </div>
                            </button>
                        @empty
                            <div class="text-secondary font-Oswald py-3">No se encontraron torneos con los filtros seleccionados.</div>
                        @endforelse
                    </div>
                </div>

                <div id="tourneyTelemetryContainer" class="lab-card p-4">
                    <div class="text-center py-5">
                        <div class="font-Oswald text-secondary fs-5">SELECCIONA UN TORNEO PARA VER SU TELEMETRÍA...</div>
                    </div>
                </div>
            </div>

            {{-- ====================================================================
                 MÓDULO 06: CARA A CARA ENTRE JUGADORES (PLAYER VS PLAYER H2H)
                 ==================================================================== --}}
            <div id="main-tab-h2h" class="{{ $activeTabInitial === 'h2h' ? '' : 'd-none' }}">
                <div class="lab-card p-3 p-md-4 mb-4">
                    <div class="border-bottom border-secondary pb-2 mb-3">
                        <h4 class="font-Oswald text-warning m-0">CARA A CARA HISTÓRICO (JUGADOR VS JUGADOR)</h4>
                        <p class="text-secondary small mb-0">
                            Compara el historial directo entre dos Bladers en torneos oficiales, sus marcadores, lances ganados y el Replay de sus enfrentamientos.
                        </p>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="filter-label text-info">BLADER 01</label>
                            <select id="h2h_player_1" class="form-select bg-black text-white border-secondary font-Oswald fs-6">
                                <option value="">Selecciona Blader 1...</option>
                                @foreach($playersList as $pl)
                                    <option value="{{ $pl->id }}" {{ Auth::id() == $pl->id ? 'selected' : '' }}>{{ $pl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="filter-label text-danger">BLADER 02 (RIVAL)</label>
                            <select id="h2h_player_2" class="form-select bg-black text-white border-secondary font-Oswald fs-6">
                                <option value="">Selecciona Blader 2...</option>
                                @foreach($playersList as $pl)
                                    <option value="{{ $pl->id }}">{{ $pl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" onclick="ejecutarComparativaH2H()" class="btn btn-warning w-100 font-Oswald fw-bold rounded-0 py-2">
                                COMPARAR H2H
                            </button>
                        </div>
                    </div>
                </div>

                <div id="h2hResultsContainer" class="d-none"></div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- MODAL DE EVOLUCIÓN MENSUAL DEL COMBO --}}
<div class="modal fade" id="comboTrendModal" tabindex="-1" aria-hidden="true" style="z-index: 1300;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-black border-2 border-warning text-white rounded-0 shadow-lg">
            <div class="modal-header border-bottom border-secondary py-2.5">
                <h5 class="modal-title font-Oswald text-warning" id="comboTrendModalTitle">EVOLUCIÓN DEL COMBO EN EL META</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div style="height: 300px;">
                    <canvas id="chartComboTrendCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INFORMATIVO DE TOOLTIPS (?) --}}
<div class="modal fade" id="infoExplainModal" tabindex="-1" aria-hidden="true" style="z-index: 1300;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-black border-2 border-info text-white rounded-0 shadow-lg">
            <div class="modal-header border-bottom border-secondary py-2.5">
                <h5 class="modal-title font-Oswald text-info" id="infoExplainTitle">INFORMACIÓN TÁCTICA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="infoExplainBody" class="mb-0 text-light small" style="white-space: pre-line; line-height: 1.6;"></p>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TOP 5 SEMANAL --}}
<div class="modal fade" id="topSemanalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-export">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 pt-0 export-body" id="export-top-node">
                <div class="text-center mb-4 mt-2">
                    <h2 class="font-Oswald m-0" style="font-size: 3.2rem; color: var(--sbbl-gold); text-shadow: 3px 3px 0 var(--shonen-red);">META RADAR</h2>
                    <div class="d-inline-block bg-white text-dark px-3 py-1 mt-1 font-Oswald fs-5" style="border: 2px solid #000;">
                        TOP 5 COMBOS DE LA SEMANA
                    </div>
                </div>

                <div class="d-flex flex-column gap-3 px-md-3">
                    @if(isset($topSemanal) && count($topSemanal) > 0)
                        @foreach($topSemanal as $combo)
                            <div class="d-flex align-items-center justify-content-between p-3" style="background: rgba(0,0,0,0.92); border: 2px solid {{ $combo->posicion == 1 ? 'var(--sbbl-gold)' : '#333' }};">
                                <div class="font-Oswald pe-2" style="font-size: 2.2rem; color: {{ $combo->posicion == 1 ? 'var(--sbbl-gold)' : '#ff2a2a' }}; min-width: 50px;">
                                    #{{ $combo->posicion }}
                                </div>
                                <div class="font-Oswald text-white flex-grow-1 px-2 text-uppercase" style="font-size: 1.3rem; letter-spacing: 1px; line-height: 1.25; word-break: break-word;">
                                    @php $nombreFormateado = str_replace('|', '<span style="color:var(--sbbl-gold);" class="mx-1">·</span>', e($combo->nombre)); @endphp
                                    {!! $nombreFormateado !!}
                                </div>
                                <div class="d-flex align-items-center gap-3 ps-2 shrink-0">
                                    <div class="text-end text-white">
                                        <span class="d-block font-Oswald" style="font-size: 1.5rem; line-height: 1;">{{ $combo->usos }}</span>
                                        <span style="font-weight: 700; font-size: 0.7rem; opacity: 0.65;">USOS</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center bg-black border {{ $combo->posicion == 1 ? 'border-warning' : 'border-secondary' }}" style="width: 40px; height: 40px; font-size: 1rem; font-family:'Oswald',sans-serif;">
                                        <span class="{{ $combo->tendencia_clase }}">{{ $combo->tendencia_icono }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-secondary font-Oswald py-4">SIN REGISTROS ESTA SEMANA</div>
                    @endif
                </div>

                <div class="text-center mt-4 mb-1">
                    <span class="font-Oswald" style="font-size: 1.1rem; letter-spacing: 2px; color: #fff; opacity: 0.5;">SBBL OFICIAL - ESPAÑA</span>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center pb-4 pt-0 mt-2">
                <button type="button" class="btn-shonen btn-shonen-info" onclick="downloadImage()">
                    <span>DESCARGAR IMAGEN</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    let labRadarChartInstance = null;
    let comboTrendChartInstance = null;
    let sidebarVisible = true;
    let currentTourneyDataCache = null;
    let firstTourneyLoaded = false;

    function toggleStatsSidebar() {
        const sidebarCol = document.getElementById('statsSidebarCol');
        const mainCol    = document.getElementById('statsMainContentCol');
        const lbl        = document.getElementById('lblToggleSidebar');

        sidebarVisible = !sidebarVisible;
        if (sidebarVisible) {
            sidebarCol.classList.remove('d-none');
            mainCol.className = 'col-12 col-lg-9';
            if (lbl) lbl.innerText = 'OCULTAR FILTROS';
        } else {
            sidebarCol.classList.add('d-none');
            mainCol.className = 'col-12';
            if (lbl) lbl.innerText = 'MOSTRAR FILTROS Y MENÚ';
        }
    }

    function abrirInfoModal(titulo, texto) {
        document.getElementById('infoExplainTitle').innerText = titulo;
        document.getElementById('infoExplainBody').innerText  = texto;
        const m = new bootstrap.Modal(document.getElementById('infoExplainModal'));
        m.show();
    }

    function switchStatsMainTab(tab) {
        ['combos', 'tierlist', 'partes', 'lab', 'torneos', 'h2h'].forEach(t => {
            const el = document.getElementById(`main-tab-${t}`);
            if (el) el.classList.toggle('d-none', t !== tab);
        });

        const selectEl = document.getElementById('sidebarModuleSelect');
        if (selectEl && selectEl.value !== tab) {
            selectEl.value = tab;
        }

        const hiddenVista = document.getElementById('hidden_vista_input');
        if (hiddenVista) {
            hiddenVista.value = tab;
        }

        if (tab === 'torneos' && !firstTourneyLoaded) {
            const firstBtn = document.querySelector('#tourneySelectorList .tourney-pill-btn');
            if (firstBtn) firstBtn.click();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if ('{{ $activeTabInitial }}' === 'torneos') {
            const firstBtn = document.querySelector('#tourneySelectorList .tourney-pill-btn');
            if (firstBtn) firstBtn.click();
        }
    });

    function switchTierCategory(catKey) {
        ['blades', 'ratchets', 'bits', 'over_blades', 'assist_blades'].forEach(k => {
            document.getElementById(`tier-container-${k}`).classList.toggle('d-none', k !== catKey);
            const btn = document.getElementById(`btn-tier-${k}`);
            if (btn) {
                btn.className = (k === catKey) ? 'btn btn-sm btn-warning fw-bold font-Oswald' : 'btn btn-sm btn-outline-light fw-bold font-Oswald';
            }
        });
    }

    function switchPartSubTab(partKey) {
        ['blades', 'over_blades', 'assist_blades', 'ratchets', 'bits'].forEach(k => {
            document.getElementById(`subtab-part-${k}`).classList.toggle('d-none', k !== partKey);
            const btn = document.getElementById(`btn-part-${k}`);
            if (btn) {
                btn.className = (k === partKey) ? 'btn btn-sm btn-warning fw-bold font-Oswald' : 'btn btn-sm btn-outline-light fw-bold font-Oswald';
            }
        });
    }

    // =========================================================================
    // GRÁFICO DE EVOLUCIÓN MENSUAL DEL COMBO (/beyblade-database/stats/combo-trend)
    // =========================================================================
    async function abrirModalEvolucionCombo(blade, ratchet, bit) {
        document.getElementById('comboTrendModalTitle').innerText = `EVOLUCIÓN MENSUAL: ${blade} ${ratchet} ${bit}`;
        const modal = new bootstrap.Modal(document.getElementById('comboTrendModal'));
        modal.show();

        try {
            const res = await fetch(`/beyblade-database/stats/combo-trend?blade=${encodeURIComponent(blade)}&ratchet=${encodeURIComponent(ratchet)}&bit=${encodeURIComponent(bit)}`);
            const data = await res.json();
            const timeline = data.timeline || [];

            const ctx = document.getElementById('chartComboTrendCanvas');
            if (comboTrendChartInstance) comboTrendChartInstance.destroy();

            comboTrendChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: timeline.map(r => r.ym),
                    datasets: [
                        {
                            label: 'Winrate (%)',
                            data: timeline.map(r => r.winrate),
                            borderColor: '#00ffcc',
                            backgroundColor: 'rgba(0, 255, 204, 0.15)',
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Combates Jugados',
                            type: 'bar',
                            data: timeline.map(r => r.total_matches),
                            backgroundColor: 'rgba(255, 193, 7, 0.5)',
                            borderColor: '#ffc107',
                            borderWidth: 1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { color: '#cbd5e1', font: { family: 'Oswald' } } },
                        y: { position: 'left', min: 0, max: 100, ticks: { color: '#00ffcc' }, title: { display: true, text: 'Winrate %', color: '#00ffcc' } },
                        y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#ffc107' }, title: { display: true, text: 'Combates', color: '#ffc107' } }
                    },
                    plugins: { legend: { labels: { color: '#fff', font: { family: 'Oswald' } } } }
                }
            });
        } catch (e) {
            alert('Error al cargar la evolución histórica del combo.');
        }
    }

    // =========================================================================
    // MÓDULO 05: TELEMETRÍA DE TORNEOS + CARGAR DECK EN BEY-LAB + REPLAY VISUAL
    // =========================================================================
    async function cargarTelemetriaTorneo(eventId, btnEl = null) {
        firstTourneyLoaded = true;
        document.querySelectorAll('#tourneySelectorList .tourney-pill-btn').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');

        const container = document.getElementById('tourneyTelemetryContainer');
        container.innerHTML = `<div class="text-center py-5 font-Oswald text-secondary fs-5">CARGANDO DATOS DEL TORNEO...</div>`;

        try {
            const res = await fetch(`/beyblade-database/stats/tournament/${eventId}`);
            const data = await res.json();
            currentTourneyDataCache = data;
            renderVistaTorneo('general');
        } catch (e) {
            container.innerHTML = `<div class="text-center text-danger font-Oswald py-4">Error al cargar las estadísticas del torneo.</div>`;
        }
    }

    function cargarDeckJugadorEnLab(playerId, side = 'my') {
        if (!currentTourneyDataCache) return;
        const player = (currentTourneyDataCache.standings || []).find(p => String(p.id) === String(playerId));
        if (!player || !player.deck || player.deck.length === 0) {
            alert('Este jugador no tiene un Deck registrado en este torneo.');
            return;
        }

        for (let i = 1; i <= 3; i++) limpiarSlotWebLab(side, i);

        player.deck.slice(0, 3).forEach((d, idx) => {
            const s = idx + 1;
            document.getElementById(`wlab_${side}_blade_${s}`).value   = d.blade || '';
            document.getElementById(`wlab_${side}_over_${s}`).value    = d.over_blade || '';
            document.getElementById(`wlab_${side}_assist_${s}`).value  = d.assist_blade || '';
            document.getElementById(`wlab_${side}_ratchet_${s}`).value = d.ratchet || '';
            document.getElementById(`wlab_${side}_bit_${s}`).value     = d.bit || '';
        });

        if (side === 'my') validarLegalidadWebLab();
        switchStatsMainTab('lab');
        document.getElementById('main-tab-lab').scrollIntoView({ behavior: 'smooth' });
    }

    function renderVistaTorneo(selectedPlayerId = 'general') {
        if (!currentTourneyDataCache) return;
        const data = currentTourneyDataCache;
        const ev   = data.event;
        const bs   = data.battle_stats;
        const standings = data.standings || [];
        const container = document.getElementById('tourneyTelemetryContainer');

        const totBouts = Math.max(1, bs.total_bouts);
        const pSpin    = Math.round((bs.spins / totBouts) * 100);
        const pOver    = Math.round((bs.overs / totBouts) * 100);
        const pBurst   = Math.round((bs.bursts / totBouts) * 100);
        const pXtreme  = Math.round((bs.xtremes / totBouts) * 100);

        let html = `
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center bg-black border border-secondary p-3 mb-4 gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark font-Oswald">${ev.type}</span>
                        <span class="text-info font-Oswald small">${ev.date} · ${ev.city} (${ev.region})</span>
                    </div>
                    <h3 class="font-Oswald text-white m-0 mt-1">${ev.name}</h3>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <label class="font-Oswald text-warning small me-1">INSPECCIONAR:</label>
                    <select id="tourneyPlayerSelect" onchange="renderVistaTorneo(this.value)" class="form-select form-select-sm bg-dark text-white border-warning font-Oswald fs-6" style="min-width: 240px;">
                        <option value="general" ${selectedPlayerId === 'general' ? 'selected' : ''}>VISTA GENERAL DEL TORNEO (${standings.length} BLADERS)</option>
                        ${standings.map(pl => `
                            <option value="${pl.id}" ${String(selectedPlayerId) === String(pl.id) ? 'selected' : ''}>
                                #${pl.shared_rank}º — ${pl.name} (${pl.wins}V - ${pl.losses}D)
                            </option>
                        `).join('')}
                    </select>
                    <a href="${ev.bracket_url}" target="_blank" class="btn btn-sm btn-outline-info font-Oswald">
                        VER CUADRO
                    </a>
                </div>
            </div>
        `;

        if (selectedPlayerId === 'general') {
            const top3 = standings.slice(0, 3);
            const medals = ['1º CAMPEÓN', '2º FINALISTA', '3º PUESTO'];

            html += `
                <div class="bg-black border border-secondary p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <span class="font-Oswald text-info">TELEMETRÍA DE LA ARENA (${bs.total_bouts} LANZAMIENTOS REGISTRADOS)</span>
                        <div class="d-flex gap-3 fw-bold small">
                            <span class="text-success">SPIN: ${bs.spins} (${pSpin}%)</span>
                            <span class="text-info">OVER: ${bs.overs} (${pOver}%)</span>
                            <span class="text-warning">BURST: ${bs.bursts} (${pBurst}%)</span>
                            <span style="color:#e879f9;">XTREME: ${bs.xtremes} (${pXtreme}%)</span>
                        </div>
                    </div>
                    <div class="d-flex w-100 bg-dark border border-secondary" style="height: 10px;">
                        <div style="width:${pSpin}%; background:#10b981;"></div>
                        <div style="width:${pOver}%; background:#0ea5e9;"></div>
                        <div style="width:${pBurst}%; background:#f59e0b;"></div>
                        <div style="width:${pXtreme}%; background:#d946ef;"></div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    ${top3.map((pl, idx) => `
                        <div class="col-md-4">
                            <div class="bg-black border ${idx === 0 ? 'border-warning' : 'border-secondary'} p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-2">
                                        <span class="font-Oswald ${idx === 0 ? 'text-warning' : 'text-info'}">${medals[idx]}</span>
                                        <button type="button" onclick="renderVistaTorneo('${pl.id}')" class="btn btn-xs btn-outline-warning font-Oswald py-0 px-2" style="font-size:0.75rem;">VER FICHA</button>
                                    </div>
                                    <div class="font-Oswald fs-4 text-white text-uppercase mb-2">${pl.name} <small class="text-secondary fs-6">(${pl.wins}V - ${pl.losses}D)</small></div>
                                    <div>
                                        ${pl.deck && pl.deck.length > 0 ? pl.deck.map(d => `
                                            <div class="d-flex justify-content-between align-items-center bg-dark px-2 py-1 mb-1 border border-secondary">
                                                <span class="small fw-bold text-white text-truncate pe-2">${d.combo}</span>
                                                <span class="badge bg-black text-warning font-Oswald shrink-0">${d.wins}W-${d.losses}L</span>
                                            </div>
                                        `).join('') : '<div class="text-secondary small fst-italic">Sin deck registrado</div>'}
                                    </div>
                                </div>
                                ${pl.deck && pl.deck.length > 0 ? `
                                    <div class="d-flex gap-1 mt-3 pt-2 border-top border-secondary">
                                        <button type="button" onclick="cargarDeckJugadorEnLab('${pl.id}', 'my')" class="btn btn-xs btn-outline-info font-Oswald flex-fill py-1" style="font-size:0.72rem;">CARGAR EN MI LAB</button>
                                        <button type="button" onclick="cargarDeckJugadorEnLab('${pl.id}', 'riv')" class="btn btn-xs btn-outline-danger font-Oswald flex-fill py-1" style="font-size:0.72rem;">USAR COMO RIVAL</button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">BEYBLADES MVP DEL TORNEO</h5>
                        ${data.top_combos && data.top_combos.length > 0 ? data.top_combos.map((tc, cIdx) => `
                            <div class="d-flex justify-content-between align-items-center bg-black border border-secondary p-2.5 mb-2">
                                <div>
                                    <span class="badge bg-warning text-dark font-Oswald me-1">#${cIdx + 1}</span>
                                    <span class="font-Oswald text-white fs-5">${tc.combo_name}</span>
                                    <div class="small text-secondary">Blader: <b class="text-info">${tc.owner_name}</b></div>
                                </div>
                                <div class="text-end font-Oswald">
                                    <div class="fs-5 text-success">${tc.wins} WINS</div>
                                    <div class="small text-warning">${tc.pts_generated} PTS GENERADOS</div>
                                </div>
                            </div>
                        `).join('') : '<div class="text-secondary small fst-italic">Sin combates individuales registrados en este torneo.</div>'}
                    </div>

                    <div class="col-lg-7">
                        <h5 class="font-Oswald text-info border-bottom border-secondary pb-2 mb-3">CLASIFICACIÓN GENERAL (TOCA UN BLADER PARA VER SU FICHA Y REPLAYS)</h5>
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr class="font-Oswald small text-secondary">
                                        <th>POS</th>
                                        <th>BLADER</th>
                                        <th class="text-center">V - D</th>
                                        <th class="text-center text-warning">PTS</th>
                                        <th class="text-center text-info">BCH</th>
                                        <th class="text-center">DIF</th>
                                        <th class="text-end">FICHA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${standings.map(pl => `
                                        <tr style="cursor:pointer;" onclick="renderVistaTorneo('${pl.id}')">
                                            <td class="font-Oswald fs-5 ${pl.shared_rank <= 4 ? 'text-warning' : 'text-secondary'}">#${pl.shared_rank}º</td>
                                            <td class="font-Oswald text-white text-uppercase">${pl.name}</td>
                                            <td class="text-center font-Oswald"><span class="text-success">${pl.wins}V</span> - <span class="text-danger">${pl.losses}D</span></td>
                                            <td class="text-center font-Oswald text-warning fs-5">${pl.points}</td>
                                            <td class="text-center font-Oswald text-info">${pl.buchholz}</td>
                                            <td class="text-center font-Oswald ${pl.point_diff >= 0 ? 'text-success' : 'text-danger'}">${pl.point_diff > 0 ? '+' + pl.point_diff : pl.point_diff}</td>
                                            <td class="text-end"><button type="button" class="btn btn-xs btn-outline-info font-Oswald py-0 px-2">VER DECK</button></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } else {
            const player = standings.find(p => String(p.id) === String(selectedPlayerId));
            if (!player) return renderVistaTorneo('general');

            html += `
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <button type="button" onclick="renderVistaTorneo('general')" class="btn btn-sm btn-outline-warning font-Oswald">
                        VOLVER A LA VISTA GENERAL DEL TORNEO
                    </button>
                    ${player.deck && player.deck.length > 0 ? `
                        <div class="d-flex gap-2">
                            <button type="button" onclick="cargarDeckJugadorEnLab('${player.id}', 'my')" class="btn btn-sm btn-outline-info font-Oswald">
                                CARGAR ESTE DECK EN MI BEY-LAB
                            </button>
                            <button type="button" onclick="cargarDeckJugadorEnLab('${player.id}', 'riv')" class="btn btn-sm btn-outline-danger font-Oswald">
                                CARGAR COMO DECK RIVAL EN BEY-LAB
                            </button>
                        </div>
                    ` : ''}
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="kpi-box">
                            <div class="text-secondary font-Oswald small">POSICIÓN FINAL</div>
                            <div class="font-Oswald fs-2 text-warning lh-1 mt-1">#${player.shared_rank}º (${player.puesto})</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="kpi-box" style="border-left-color:#10b981;">
                            <div class="text-secondary font-Oswald small">BALANCE DE RONDAS</div>
                            <div class="font-Oswald fs-2 text-white lh-1 mt-1"><span class="text-success">${player.wins}V</span> - <span class="text-danger">${player.losses}D</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="kpi-box" style="border-left-color:var(--shonen-cyan);">
                            <div class="text-secondary font-Oswald small">PUNTOS / BUCHHOLZ</div>
                            <div class="font-Oswald fs-2 text-info lh-1 mt-1">${player.points} PTS <small class="fs-6 text-secondary">(${player.buchholz} BCH)</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="kpi-box" style="border-left-color:#d946ef;">
                            <div class="text-secondary font-Oswald small">DIFERENCIA DE PUNTOS (+/-)</div>
                            <div class="font-Oswald fs-2 ${player.point_diff >= 0 ? 'text-success' : 'text-danger'} lh-1 mt-1">${player.point_diff > 0 ? '+' + player.point_diff : player.point_diff}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">
                            RENDIMIENTO DEL DECK DE ${player.name.toUpperCase()}
                        </h5>
                        ${player.deck && player.deck.length > 0 ? player.deck.map((d, idx) => `
                            <div class="bg-black border border-secondary p-3 mb-2.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-info text-dark font-Oswald me-1">BEY #0${idx + 1}</span>
                                        <span class="font-Oswald fs-4 text-warning">${d.combo}</span>
                                    </div>
                                    <div class="text-end font-Oswald">
                                        <span class="fs-4 ${d.winrate >= 50 ? 'text-success' : 'text-warning'}">${d.winrate}% WR</span>
                                        <div class="small text-secondary">${d.wins}W - ${d.losses}L (${d.pts_for} pts anotados)</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small fw-bold bg-dark px-3 py-1.5 border border-secondary">
                                    <span class="text-success">SPIN: ${d.spins}</span>
                                    <span class="text-info">OVER: ${d.overs}</span>
                                    <span class="text-warning">BURST: ${d.bursts}</span>
                                    <span style="color:#e879f9;">XTREME: ${d.xtremes}</span>
                                </div>
                            </div>
                        `).join('') : '<div class="text-secondary fst-italic">Este jugador no registró piezas o no tuvo combates individuales registrados.</div>'}
                    </div>

                    <div class="col-lg-7">
                        <h5 class="font-Oswald text-info border-bottom border-secondary pb-2 mb-3">
                            HOJA DE RUTA Y REPLAY VISUAL LANCE POR LANCE (${player.matches.length})
                        </h5>
                        <div class="d-flex flex-column gap-2">
                            ${player.matches && player.matches.length > 0 ? player.matches.map((m, mIdx) => {
                                const hasReplay = m.timeline && m.timeline.length > 0;
                                return `
                                    <div class="bg-black border ${m.is_win ? 'border-success' : 'border-danger'} p-2.5">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-dark border border-secondary text-info font-Oswald me-2">${m.round} · ${m.phase}</span>
                                                <span class="font-Oswald text-white fs-5 text-uppercase">vs ${m.rival}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="font-Oswald fs-4 ${m.is_win ? 'text-success' : 'text-danger'}">
                                                    ${m.my_score} - ${m.riv_score}
                                                </span>
                                                <span class="badge ${m.is_win ? 'bg-success text-dark' : 'bg-danger text-white'} font-Oswald">${m.is_win ? 'WIN' : 'LOSS'}</span>
                                                ${hasReplay ? `<button type="button" onclick="document.getElementById('replay-box-${mIdx}').classList.toggle('d-none')" class="btn btn-xs btn-outline-light font-Oswald py-0.5 px-2" style="font-size:0.72rem;">VER REPLAY (${m.timeline.length})</button>` : ''}
                                            </div>
                                        </div>
                                        ${hasReplay ? `
                                            <div id="replay-box-${mIdx}" class="d-none mt-3 pt-2 border-top border-secondary">
                                                <div class="small font-Oswald text-warning mb-2">LÍNEA DE TIEMPO DEL ENFRENTAMIENTO:</div>
                                                ${m.timeline.map(b => `
                                                    <div class="timeline-step">
                                                        <div class="d-flex justify-content-between align-items-center bg-dark px-2.5 py-1.5 border border-secondary">
                                                            <div class="small">
                                                                <span class="text-secondary fw-bold me-1">LANCE #${b.bout_num}:</span>
                                                                <span class="text-info fw-bold">${b.my_combo}</span>
                                                                <span class="text-secondary mx-1">vs</span>
                                                                <span class="text-danger fw-bold">${b.riv_combo}</span>
                                                            </div>
                                                            <div class="font-Oswald small">
                                                                <span class="badge ${b.i_won ? 'bg-success text-dark' : 'bg-danger text-white'} me-1">${b.finish} (+${b.points})</span>
                                                                <span class="text-white">Marcador: ${b.running}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                `;
                            }).join('') : '<div class="text-secondary fst-italic">Sin rondas registradas.</div>'}
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    // =========================================================================
    // MÓDULO 06: CARA A CARA ENTRE JUGADORES (/beyblade-database/stats/player-h2h)
    // =========================================================================
    async function ejecutarComparativaH2H() {
        const p1 = document.getElementById('h2h_player_1')?.value;
        const p2 = document.getElementById('h2h_player_2')?.value;

        if (!p1 || !p2 || p1 === p2) {
            alert('Selecciona dos jugadores distintos para comparar su historial.');
            return;
        }

        const container = document.getElementById('h2hResultsContainer');
        container.classList.remove('d-none');
        container.innerHTML = `<div class="lab-card p-5 text-center font-Oswald text-secondary fs-5">BUSCANDO HISTORIAL DE ENFRENTAMIENTOS...</div>`;

        try {
            const res = await fetch(`/beyblade-database/stats/player-h2h?p1=${p1}&p2=${p2}`);
            const data = await res.json();

            if (!data.success) {
                container.innerHTML = `<div class="lab-card p-4 text-center text-danger font-Oswald">${data.message || 'Error al comparar jugadores.'}</div>`;
                return;
            }

            const tot = Math.max(1, data.total_matches);
            const p1Pct = data.total_matches > 0 ? Math.round((data.p1.match_wins / tot) * 100) : 50;

            let html = `
                <div class="lab-card p-4 mb-4">
                    <div class="row align-items-center text-center g-3 mb-3">
                        <div class="col-md-4 text-md-start">
                            <div class="font-Oswald fs-3 text-info text-uppercase">${data.p1.name}</div>
                            <div class="small text-secondary">${data.p1.bouts_won} lances ganados · ${data.p1.total_pts} pts totales</div>
                            <div class="mt-2">
                                ${(data.p1.top_combos || []).map(c => `<span class="badge bg-dark border border-secondary text-light me-1">${c.combo} (${c.wins}W)</span>`).join('')}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small font-Oswald text-secondary">HISTORIAL DIRECTO (${data.total_matches} PARTIDAS)</div>
                            <div class="font-Oswald display-4 text-white lh-1 my-1">
                                <span class="text-info">${data.p1.match_wins}</span>
                                <span class="text-secondary mx-2">-</span>
                                <span class="text-danger">${data.p2.match_wins}</span>
                            </div>
                        </div>

                        <div class="col-md-4 text-md-end">
                            <div class="font-Oswald fs-3 text-danger text-uppercase">${data.p2.name}</div>
                            <div class="small text-secondary">${data.p2.bouts_won} lances ganados · ${data.p2.total_pts} pts totales</div>
                            <div class="mt-2">
                                ${(data.p2.top_combos || []).map(c => `<span class="badge bg-dark border border-secondary text-light ms-1">${c.combo} (${c.wins}W)</span>`).join('')}
                            </div>
                        </div>
                    </div>

                    <div class="energy-bar-bg" style="height: 14px; background:#dc2626;">
                        <div class="energy-bar-fill" style="width: ${p1Pct}%; background:#00ffcc;"></div>
                    </div>
                </div>

                <div class="lab-card p-4">
                    <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">
                        REGISTRO DE PARTIDAS ENTRE ${data.p1.name.toUpperCase()} Y ${data.p2.name.toUpperCase()}
                    </h5>
                    ${data.matches && data.matches.length > 0 ? data.matches.map((m, idx) => {
                        const hasTl = m.timeline && m.timeline.length > 0;
                        return `
                            <div class="bg-black border ${m.p1_won ? 'border-info' : 'border-danger'} p-3 mb-2.5">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <span class="badge bg-warning text-dark font-Oswald me-2">${m.date}</span>
                                        <span class="font-Oswald text-white fs-5">${m.event_name}</span>
                                        <span class="text-secondary small ms-2">(${m.round})</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="font-Oswald fs-4">
                                            <span class="${m.p1_won ? 'text-info' : 'text-secondary'}">${data.p1.name} ${m.p1_score}</span>
                                            <span class="text-secondary mx-1">-</span>
                                            <span class="${!m.p1_won ? 'text-danger' : 'text-secondary'}">${m.p2_score} ${data.p2.name}</span>
                                        </span>
                                        ${hasTl ? `<button type="button" onclick="document.getElementById('h2h-tl-${idx}').classList.toggle('d-none')" class="btn btn-xs btn-outline-light font-Oswald py-0.5 px-2" style="font-size:0.75rem;">VER REPLAY (${m.timeline.length})</button>` : ''}
                                    </div>
                                </div>
                                ${hasTl ? `
                                    <div id="h2h-tl-${idx}" class="d-none mt-3 pt-2 border-top border-secondary">
                                        ${m.timeline.map(b => `
                                            <div class="timeline-step">
                                                <div class="d-flex justify-content-between align-items-center bg-dark px-2.5 py-1.5 border border-secondary">
                                                    <div class="small">
                                                        <span class="text-secondary fw-bold me-1">LANCE #${b.bout_num}:</span>
                                                        <span class="text-info fw-bold">${b.p1_combo}</span>
                                                        <span class="text-secondary mx-1">vs</span>
                                                        <span class="text-danger fw-bold">${b.p2_combo}</span>
                                                    </div>
                                                    <div class="font-Oswald small">
                                                        <span class="badge ${b.winner_p1 ? 'bg-info text-dark' : 'bg-danger text-white'} me-1">GANA ${b.winner_name.toUpperCase()} · ${b.finish} (+${b.points})</span>
                                                        <span class="text-white">Marcador: ${b.running}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('') : '<div class="text-secondary font-Oswald py-3 text-center">Estos dos Bladers aún no se han enfrentado directamente en ningún torneo registrado.</div>'}
                </div>
            `;

            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = `<div class="lab-card p-4 text-center text-danger font-Oswald">Error de red al consultar el Cara a Cara.</div>`;
        }
    }

    // =========================================================================
    // MÓDULO 04: BEY-LAB PRO Y EXPORTACIÓN DE INFORME TÁCTICO A PNG
    // =========================================================================
    function setWebLabMode(mode) {
        document.getElementById('web_lab_mode').value = mode;
        const bGlobal   = document.getElementById('btn-mode-global');
        const bPersonal = document.getElementById('btn-mode-personal');
        if (bGlobal) bGlobal.classList.toggle('active', mode === 'global_vs_global');
        if (bPersonal) bPersonal.classList.toggle('active', mode === 'personal_vs_global');

        if (!document.getElementById('web-lab-results').classList.contains('d-none')) {
            ejecutarAnalisisWebLab();
        }
    }

    function enviarComboAlLab(blade, over, assist, ratchet, bit) {
        switchStatsMainTab('lab');
        let targetSlot = 1;
        for (let i = 1; i <= 3; i++) {
            if (!document.getElementById(`wlab_my_blade_${i}`).value) {
                targetSlot = i;
                break;
            }
        }
        document.getElementById(`wlab_my_blade_${targetSlot}`).value   = blade || '';
        document.getElementById(`wlab_my_over_${targetSlot}`).value    = (over && over !== '-') ? over : '';
        document.getElementById(`wlab_my_assist_${targetSlot}`).value  = (assist && assist !== '-') ? assist : '';
        document.getElementById(`wlab_my_ratchet_${targetSlot}`).value = ratchet || '';
        document.getElementById(`wlab_my_bit_${targetSlot}`).value     = bit || '';
        validarLegalidadWebLab();
        document.getElementById('main-tab-lab').scrollIntoView({ behavior: 'smooth' });
    }

    function limpiarSlotWebLab(side, idx) {
        ['blade', 'over', 'assist', 'ratchet', 'bit'].forEach(p => {
            const el = document.getElementById(`wlab_${side}_${p}_${idx}`);
            if (el) el.value = '';
        });
        if (side === 'my') validarLegalidadWebLab();
    }

    function validarLegalidadWebLab() {
        const warn = document.getElementById('wlab-legality-warning');
        const blades = [], overs = [], assists = [], ratchets = [], bits = [];
        for (let i = 1; i <= 3; i++) {
            const b  = document.getElementById(`wlab_my_blade_${i}`)?.value;
            const ob = document.getElementById(`wlab_my_over_${i}`)?.value;
            const ab = document.getElementById(`wlab_my_assist_${i}`)?.value;
            const r  = document.getElementById(`wlab_my_ratchet_${i}`)?.value;
            const bt = document.getElementById(`wlab_my_bit_${i}`)?.value;
            if (b) blades.push(b);
            if (ob) overs.push(ob);
            if (ab) assists.push(ab);
            if (r && r !== 'UX Expanded') ratchets.push(r);
            if (bt && bt !== 'Without Bit') bits.push(bt);
        }
        const dup = arr => arr.filter((v, idx, a) => a.indexOf(v) !== idx);
        const errs = [];
        if (dup(blades).length) errs.push(`Blade (${dup(blades)[0]})`);
        if (dup(overs).length) errs.push(`Over (${dup(overs)[0]})`);
        if (dup(assists).length) errs.push(`Assist (${dup(assists)[0]})`);
        if (dup(ratchets).length) errs.push(`Ratchet (${dup(ratchets)[0]})`);
        if (dup(bits).length) errs.push(`Bit (${dup(bits)[0]})`);

        if (errs.length > 0) {
            warn.innerText = `Piezas repetidas en formato 3on3: ${errs.join(' · ')}`;
            warn.classList.remove('d-none');
        } else {
            warn.classList.add('d-none');
        }
    }

    async function importarMiUltimoDeckWeb() {
        try {
            const res = await fetch('/api/ultimo-deck');
            const deck = await res.json();
            if (!deck || deck.length === 0) {
                alert('No tienes ningún Deck registrado recientemente.');
                return;
            }
            deck.forEach((bey, idx) => {
                const i = idx + 1;
                if (i > 3) return;
                document.getElementById(`wlab_my_blade_${i}`).value   = bey.blade || '';
                document.getElementById(`wlab_my_over_${i}`).value    = (bey.over_blade && bey.over_blade !== '-') ? bey.over_blade : '';
                document.getElementById(`wlab_my_assist_${i}`).value  = (bey.assist_blade && bey.assist_blade !== '-') ? bey.assist_blade : '';
                document.getElementById(`wlab_my_ratchet_${i}`).value = bey.ratchet || '';
                document.getElementById(`wlab_my_bit_${i}`).value     = bey.bit || '';
            });
            validarLegalidadWebLab();
        } catch (e) {
            alert('Error al importar tu último Deck.');
        }
    }

    function cargarMetaBossWebLab() {
        for (let i = 1; i <= 3; i++) limpiarSlotWebLab('riv', i);
        ejecutarAnalisisWebLab();
    }

    function usarCounterRecomendado(blade, ratchet, bit, targetSlot = 1) {
        const slot = targetSlot || 1;
        document.getElementById(`wlab_my_blade_${slot}`).value   = blade || '';
        document.getElementById(`wlab_my_over_${slot}`).value    = '';
        document.getElementById(`wlab_my_assist_${slot}`).value  = '';
        document.getElementById(`wlab_my_ratchet_${slot}`).value = ratchet || '';
        document.getElementById(`wlab_my_bit_${slot}`).value     = bit || '';
        validarLegalidadWebLab();
        ejecutarAnalisisWebLab();
    }

    async function ejecutarAnalisisWebLab() {
        const miDeck = [];
        const rivalDeck = [];

        for (let i = 1; i <= 3; i++) {
            const mb = document.getElementById(`wlab_my_blade_${i}`)?.value;
            if (mb) {
                miDeck.push({
                    blade: mb,
                    over_blade: document.getElementById(`wlab_my_over_${i}`)?.value || '',
                    assist_blade: document.getElementById(`wlab_my_assist_${i}`)?.value || '',
                    ratchet: document.getElementById(`wlab_my_ratchet_${i}`)?.value || '',
                    bit: document.getElementById(`wlab_my_bit_${i}`)?.value || ''
                });
            }
            const rb = document.getElementById(`wlab_riv_blade_${i}`)?.value;
            if (rb) {
                rivalDeck.push({
                    blade: rb,
                    over_blade: document.getElementById(`wlab_riv_over_${i}`)?.value || '',
                    assist_blade: document.getElementById(`wlab_riv_assist_${i}`)?.value || '',
                    ratchet: document.getElementById(`wlab_riv_ratchet_${i}`)?.value || '',
                    bit: document.getElementById(`wlab_riv_bit_${i}`)?.value || ''
                });
            }
        }

        if (miDeck.length === 0) {
            alert('Selecciona al menos 1 Beyblade en tu Deck (Banco 01).');
            return;
        }

        const btn = document.getElementById('btnRunWebLab');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<span>PROCESANDO SIMULACIÓN...</span>';

        try {
            const activeMode = document.getElementById('web_lab_mode')?.value || 'global_vs_global';
            const res = await fetch('{{ route("stats.lab.analyze") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mi_deck: miDeck,
                    rival_deck: rivalDeck,
                    mode: activeMode,
                    tipo_evento: document.querySelector('select[name="tipo_evento"]')?.value || '',
                    fecha_inicio: document.querySelector('input[name="fecha_inicio"]')?.value || '',
                    fecha_fin: document.querySelector('input[name="fecha_fin"]')?.value || ''
                })
            });
            const data = await res.json();

            if (rivalDeck.length === 0 && data.rival_deck_stats) {
                data.rival_deck_stats.forEach((r, idx) => {
                    const i = idx + 1;
                    if (i <= 3) {
                        document.getElementById(`wlab_riv_blade_${i}`).value   = r.blade || '';
                        document.getElementById(`wlab_riv_over_${i}`).value    = r.over_blade || '';
                        document.getElementById(`wlab_riv_assist_${i}`).value  = r.assist_blade || '';
                        document.getElementById(`wlab_riv_ratchet_${i}`).value = r.ratchet || '';
                        document.getElementById(`wlab_riv_bit_${i}`).value     = r.bit || '';
                    }
                });
            }

            document.getElementById('web-lab-results').classList.remove('d-none');

            const modeBanner = document.getElementById('wlab-mode-banner');
            if (data.mode === 'personal_vs_global') {
                modeBanner.innerHTML = `MODO ACTIVO: <b>TUS ESTADÍSTICAS PERSONALES VS META GLOBAL</b> (${data.personal_matches_found} combates tuyos encontrados)`;
            } else {
                modeBanner.innerHTML = `MODO ACTIVO: <b>ESTADÍSTICAS GLOBALES DE LA LIGA VS GLOBAL</b>`;
            }

            const orderStr = (data.recommended_order || []).map((c, idx) => `<span class="badge bg-dark border border-info text-white me-1">${idx + 1}º ${c}</span>`).join(' ');

            const countersList = data.recommended_counters || [];
            const counterHtml = countersList.length > 0
                ? `<div class="d-flex flex-column gap-1.5 mt-2">
                    ${countersList.map((c, cIdx) => `
                        <div class="d-flex justify-content-between align-items-center bg-dark border border-secondary px-2 py-1">
                            <div class="text-truncate pe-2">
                                <span class="badge bg-warning text-dark font-Oswald me-1">#${cIdx + 1}</span>
                                <span class="text-white fw-bold small">${c.combo}</span>
                                <span class="text-success fw-bold ms-1" style="font-size:0.75rem;">(${c.winrate}% WR · ${c.wins}W/${c.total}G)</span>
                            </div>
                            <button type="button"
                                    onclick="usarCounterRecomendado('${c.blade}','${c.ratchet}','${c.bit}', ${c.replace_slot || 1})"
                                    class="btn btn-xs btn-warning fw-bold py-0 px-2 shrink-0"
                                    style="font-size:0.72rem;">
                                CAMBIAR POR #0${c.replace_slot || 1}
                            </button>
                        </div>
                    `).join('')}
                   </div>`
                : `<div class="text-secondary small mt-1">Sin datos de counter directo adicionales</div>`;

            document.getElementById('wlab-tactical-advisor').innerHTML = `
                <div class="col-md-4">
                    <div class="kpi-box h-100" style="border-left-color: #00ffcc;">
                        <div class="text-info font-Oswald small">ORDEN ÓPTIMO DE LANZAMIENTO</div>
                        <div class="mt-2">${orderStr || '---'}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-box h-100" style="border-left-color: #ff2a2a;">
                        <div class="text-danger font-Oswald small">ESLABÓN MÁS EXPUESTO (#0${data.weak_slot || 1})</div>
                        <div class="font-Oswald fs-5 text-white mt-1">${data.weak_link || '---'}</div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="kpi-box h-100" style="border-left-color: #ffc107;">
                        <div class="text-warning font-Oswald small">TOP 3 COUNTERS LEGALES POR % DE VICTORIA</div>
                        ${counterHtml}
                    </div>
                </div>
            `;

            const wr = data.overall_winrate;
            document.getElementById('wlab-overall-wr').innerText = `${wr}%`;
            document.getElementById('wlab-rival-wr').innerText   = `${(100 - wr).toFixed(1)}%`;
            document.getElementById('wlab-overall-bar').style.width = `${wr}%`;

            let matrixHtml = `<table class="table table-dark table-bordered text-center align-middle mb-0"><thead><tr><th class="text-start text-info font-Oswald">TU BEY \\ RIVAL</th>`;
            data.rival_deck_stats.forEach(riv => {
                matrixHtml += `<th class="text-danger font-Oswald">${riv.short_name}<div class="small text-light opacity-75 fw-normal">${riv.parts_label}</div></th>`;
            });
            matrixHtml += `</tr></thead><tbody>`;

            data.matrix.forEach((row, rIdx) => {
                const mySt = data.my_deck_stats[rIdx];
                matrixHtml += `<tr><td class="text-start font-Oswald text-info">${mySt.short_name}<div class="small text-light opacity-75 fw-normal">${mySt.parts_label}</div></td>`;
                row.forEach(cell => {
                    const cWr = cell.winrate;
                    const badgeCol = cWr >= 55 ? 'bg-success bg-opacity-25 text-success border-success' : (cWr >= 45 ? 'bg-secondary bg-opacity-25 text-light border-secondary' : 'bg-danger bg-opacity-25 text-danger border-danger');
                    matrixHtml += `<td><div class="border ${badgeCol} p-2"><div class="font-Oswald fs-4 lh-1">${cWr}%</div><div class="small opacity-75">${cell.source}</div></div></td>`;
                });
                matrixHtml += `</tr>`;
            });
            matrixHtml += `</tbody></table>`;
            document.getElementById('wlab-matrix-container').innerHTML = matrixHtml;

            let cardsHtml = '';
            data.my_deck_stats.forEach(st => {
                let deltaBadge = '';
                if (st.delta_vs_global !== null && st.delta_vs_global !== undefined) {
                    const sign = st.delta_vs_global >= 0 ? '+' : '';
                    const dCol = st.delta_vs_global >= 0 ? 'bg-success text-dark' : 'bg-danger text-white';
                    deltaBadge = `<span class="badge ${dCol} mt-1 d-inline-block">${sign}${st.delta_vs_global}% vs Media Global (${st.global_winrate}%)</span>`;
                }

                cardsHtml += `
                    <div class="col-md-4">
                        <div class="bg-black border border-secondary p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="font-Oswald fs-4 text-warning">${st.short_name}</div>
                                    <div class="text-white font-Oswald small">${st.parts_label}</div>
                                    <div class="small text-secondary mt-1">${st.wins}W - ${st.losses}L (${st.sample_size} comb. · ${st.precision})</div>
                                </div>
                                <div class="text-end">
                                    <span class="font-Oswald fs-3 text-info">${st.winrate}% WR</span>
                                    <div>${deltaBadge}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small fw-bold mt-2 mb-1">
                                <span class="text-success">SPN ${st.spin_pct}%</span>
                                <span class="text-info">OVR ${st.over_pct}%</span>
                                <span class="text-warning">BRS ${st.burst_pct}%</span>
                                <span style="color:#e879f9;">XTR ${st.xtreme_pct}%</span>
                            </div>
                            <div class="d-flex w-100 bg-dark" style="height: 8px;">
                                <div style="width:${st.spin_pct}%; background:#10b981;"></div>
                                <div style="width:${st.over_pct}%; background:#0ea5e9;"></div>
                                <div style="width:${st.burst_pct}%; background:#f59e0b;"></div>
                                <div style="width:${st.xtreme_pct}%; background:#d946ef;"></div>
                            </div>
                            <div class="row g-2 mt-2 text-center font-Oswald">
                                <div class="col-4"><div class="bg-dark p-1 border border-secondary"><div class="small text-secondary">PTS/WIN</div><div class="text-success">${st.avg_dmg_dealt.toFixed(2)}</div></div></div>
                                <div class="col-4"><div class="bg-dark p-1 border border-secondary"><div class="small text-secondary">PTS/LOSS</div><div class="text-danger">${st.avg_dmg_taken.toFixed(2)}</div></div></div>
                                <div class="col-4"><div class="bg-dark p-1 border border-secondary"><div class="small text-secondary">RIESGO BURST</div><div class="text-warning">${st.burst_risk}%</div></div></div>
                            </div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('wlab-my-cards-container').innerHTML = cardsHtml;

            const ctxRadar = document.getElementById('chartLabRadar');
            if (labRadarChartInstance) labRadarChartInstance.destroy();
            labRadarChartInstance = new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: ['Winrate Global', 'Explosividad Xtreme', 'Poder Burst', 'Control Spin/Over', 'Defensa Anti-Burst'],
                    datasets: [
                        {
                            label: 'Tu Deck',
                            data: data.radar_my_deck,
                            backgroundColor: 'rgba(0, 255, 204, 0.25)',
                            borderColor: '#00ffcc',
                            pointBackgroundColor: '#00ffcc',
                            borderWidth: 2
                        },
                        {
                            label: 'Deck Rival / Meta',
                            data: data.radar_rival_deck,
                            backgroundColor: 'rgba(255, 42, 42, 0.25)',
                            borderColor: '#ff2a2a',
                            pointBackgroundColor: '#ff2a2a',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            angleLines: { color: 'rgba(255,255,255,0.15)' },
                            grid: { color: 'rgba(255,255,255,0.15)' },
                            pointLabels: { color: '#fff', font: { family: 'Oswald', size: 12 } },
                            ticks: { display: false }
                        }
                    },
                    plugins: { legend: { labels: { color: '#fff', font: { family: 'Oswald' } } } }
                }
            });

        } catch (e) {
            alert('Error al ejecutar la simulación en el Bey-Lab.');
        } finally {
            btn.innerHTML = oldHtml;
        }
    }

    function exportarInformeLabPNG(btnEl) {
        const node = document.getElementById('wlab-export-capture-area');
        const oldTxt = btnEl.innerText;
        btnEl.innerText = 'GENERANDO IMAGEN...';

        html2canvas(node, {
            scale: 2,
            backgroundColor: "#060911",
            useCORS: true
        }).then(canvas => {
            btnEl.innerText = oldTxt;
            const link = document.createElement('a');
            link.download = 'sbbl_informe_beylab.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        }).catch(() => {
            btnEl.innerText = oldTxt;
            alert('Error al generar la imagen del informe.');
        });
    }

    function downloadImage() {
        const node = document.getElementById('export-top-node');
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>PROCESANDO...</span>';

        html2canvas(node, {
            scale: 2,
            backgroundColor: "#0b0f19",
            useCORS: true
        }).then(canvas => {
            btn.innerHTML = originalText;
            let link = document.createElement('a');
            link.download = 'sbbl_top_semanal.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        }).catch(() => {
            btn.innerHTML = originalText;
            alert("Error al generar la imagen.");
        });
    }
</script>
@endsection