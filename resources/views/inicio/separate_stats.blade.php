@extends('layouts.app')

@section('title', 'Ranking de piezas Beyblade X')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: PIEZAS AISLADAS (Hereda de layout)
       ==================================================================== */

    /* ── TÍTULO Y NAVEGACIÓN ── */
    .page-title {
        font-family: 'Oswald', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0;
        line-height: 1;
    }

    .section-subtitle {
        font-family: 'Oswald', cursive;
        font-size: 2.2rem;
        color: #fff;
        letter-spacing: 1px;
        text-shadow: 2px 2px 0 #000;
        margin-top: 3rem;
        margin-bottom: 1.5rem;
        border-bottom: 4px solid var(--sbbl-gold);
        display: inline-block;
        padding-bottom: 5px;
    }

    .btn-top-action {
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        border-radius: 0;
        border: 2px solid #000;
        transform: skewX(-10deg);
        box-shadow: 4px 4px 0 #000;
        transition: 0.2s;
        text-transform: uppercase;
        padding: 8px 20px;
        background: var(--shonen-cyan);
        color: #000;
        text-decoration: none;
        display: inline-block;
    }
    .btn-top-action > * { transform: skewX(10deg); display: block; }
    .btn-top-action:hover { transform: translate(-2px, -2px) skewX(-10deg); box-shadow: 6px 6px 0 var(--shonen-red); color: #000; background: #fff; }

    /* ── TABLA ESTILO ARENA ── */
    .table-responsive {
        border: 4px solid #000;
        box-shadow: 8px 8px 0px #000;
        background: #000;
        border-radius: 0;
        margin-bottom: 2rem;
    }
    thead th {
        position: sticky;
        top: 0;
        background-color: var(--sbbl-blue-1) !important;
        border-bottom: 4px solid var(--sbbl-gold) !important;
        z-index: 2;
        padding: 15px 10px !important;
    }
    thead th a {
        font-family: 'Oswald', cursive;
        font-size: 1.3rem;
        color: #fff;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-shadow: 1px 1px 0 #000;
    }
    thead th a:hover { color: var(--shonen-cyan); }
    thead th a.active-sort { color: var(--sbbl-gold); }

    /* Filas */
    tbody tr {
        border-bottom: 2px solid #333;
        transition: 0.2s;
        background: var(--sbbl-blue-2);
    }
    tbody tr:hover { background-color: var(--sbbl-blue-3) !important; }

    .piece-name {
        font-family: 'Oswald', cursive;
        font-size: 1.8rem;
        letter-spacing: 1px;
        color: #fff;
        text-shadow: 2px 2px 0 #000;
    }

    .stat-number {
        font-family: 'Oswald', cursive;
        font-size: 1.6rem;
        color: #fff;
        text-shadow: 1px 1px 0 #000;
    }

    /* Barras de Energía (Win Rate) */
    .energy-bar-bg { background-color: #000; border: 2px solid #000; height: 14px; transform: skewX(-15deg); margin-top: 5px; }
    .energy-bar-fill { height: 100%; transition: width 0.5s ease; }
    .win-rate-text { font-family: 'Oswald', cursive; font-size: 1.6rem; line-height: 1; text-shadow: 2px 2px 0 #000; }

    .wr-s { color: #00ff00; } .bg-wr-s { background: #00ff00; box-shadow: 0 0 10px rgba(0,255,0,0.5); }
    .wr-a { color: var(--shonen-cyan); } .bg-wr-a { background: var(--shonen-cyan); box-shadow: 0 0 10px rgba(0,255,204,0.5); }
    .wr-b { color: var(--sbbl-gold); } .bg-wr-b { background: var(--sbbl-gold); box-shadow: 0 0 10px rgba(255,215,0,0.5); }
    .wr-c { color: var(--shonen-red); } .bg-wr-c { background: var(--shonen-red); box-shadow: 0 0 10px rgba(255,42,42,0.5); }

</style>
@endsection

@section('content')

{{-- Evitamos el error capturando los parámetros de ordenación desde la URL --}}
@php
    $sort = request('sort', '');
    $order = request('order', 'desc');
@endphp

<div class="container py-4">

    {{-- HEADER SUPERIOR --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 mb-4 gap-3 border-bottom border-dark" style="border-width: 3px !important;">
        <h1 class="page-title">ANÁLISIS DE PIEZAS AISLADAS</h1>
        <a href="{{ route('inicio.stats') }}" class="btn-top-action">
            <span><i class="fas fa-arrow-left me-1"></i> VOLVER AL RADAR</span>
        </a>
    </div>

    {{-- ========================================== --}}
    {{-- 1. ESTADÍSTICAS DE BLADES                  --}}
    {{-- ========================================== --}}
    <h3 class="section-subtitle"><i class="fas fa-compact-disc me-2" style="color: var(--shonen-red);"></i> Estadísticas de Blades</h3>

    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle border-0">
            <thead>
                <tr>
                    <th style="min-width: 200px; padding-left: 20px !important;">
                        <a href="{{ route('stats.separate', ['sort' => 'blade', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="justify-content-start {{ $sort == 'blade' ? 'active-sort' : '' }}">
                            BLADE @if($sort == 'blade') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('stats.separate', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="{{ $sort == 'total_partidas' ? 'active-sort' : '' }}">
                            BATALLAS @if($sort == 'total_partidas') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                    <th class="text-center" style="min-width: 150px;">
                        <a href="{{ route('stats.separate', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="{{ $sort == 'percentage_victories' ? 'active-sort' : '' }}">
                            WIN RATE (%) @if($sort == 'percentage_victories') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($bladeStats as $stat)
                    @php
                        $wr = $stat->percentage_victories;
                        if ($wr >= 60) { $cText = 'wr-s'; $cBg = 'bg-wr-s'; }
                        elseif ($wr >= 50) { $cText = 'wr-a'; $cBg = 'bg-wr-a'; }
                        elseif ($wr >= 40) { $cText = 'wr-b'; $cBg = 'bg-wr-b'; }
                        else { $cText = 'wr-c'; $cBg = 'bg-wr-c'; }
                    @endphp
                    <tr>
                        <td class="ps-4 border-0 py-3"><span class="piece-name">{{ $stat->blade }}</span></td>
                        <td class="text-center border-0"><span class="stat-number">{{ $stat->total_partidas }}</span></td>
                        <td class="pe-4 border-0">
                            <div class="d-flex justify-content-between align-items-end mb-1 px-1">
                                <span class="win-rate-text {{ $cText }}">{{ number_format($wr, 1) }}%</span>
                                <span class="fw-bold text-white opacity-50" style="font-size:0.8rem;">{{ $stat->total_victorias }}W - {{ $stat->total_derrotas }}L</span>
                            </div>
                            <div class="energy-bar-bg">
                                <div class="energy-bar-fill {{ $cBg }}" style="width: {{ $wr }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ========================================== --}}
    {{-- 2. ESTADÍSTICAS DE RATCHETS                --}}
    {{-- ========================================== --}}
    <h3 class="section-subtitle"><i class="fas fa-cog me-2" style="color: var(--shonen-cyan);"></i> Estadísticas de Ratchets</h3>

    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle border-0">
            <thead>
                <tr>
                    <th style="min-width: 200px; padding-left: 20px !important;">
                        <a href="{{ route('stats.separate', ['sort' => 'ratchet', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="justify-content-start {{ $sort == 'ratchet' ? 'active-sort' : '' }}">
                            RATCHET @if($sort == 'ratchet') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('stats.separate', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="{{ $sort == 'total_partidas' ? 'active-sort' : '' }}">
                            BATALLAS @if($sort == 'total_partidas') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                    <th class="text-center" style="min-width: 150px;">
                        <a href="{{ route('stats.separate', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="{{ $sort == 'percentage_victories' ? 'active-sort' : '' }}">
                            WIN RATE (%) @if($sort == 'percentage_victories') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($ratchetStats as $stat)
                    @php
                        $wr = $stat->percentage_victories;
                        if ($wr >= 60) { $cText = 'wr-s'; $cBg = 'bg-wr-s'; }
                        elseif ($wr >= 50) { $cText = 'wr-a'; $cBg = 'bg-wr-a'; }
                        elseif ($wr >= 40) { $cText = 'wr-b'; $cBg = 'bg-wr-b'; }
                        else { $cText = 'wr-c'; $cBg = 'bg-wr-c'; }
                    @endphp
                    <tr>
                        <td class="ps-4 border-0 py-3"><span class="piece-name">{{ $stat->ratchet }}</span></td>
                        <td class="text-center border-0"><span class="stat-number">{{ $stat->total_partidas }}</span></td>
                        <td class="pe-4 border-0">
                            <div class="d-flex justify-content-between align-items-end mb-1 px-1">
                                <span class="win-rate-text {{ $cText }}">{{ number_format($wr, 1) }}%</span>
                                <span class="fw-bold text-white opacity-50" style="font-size:0.8rem;">{{ $stat->total_victorias }}W - {{ $stat->total_derrotas }}L</span>
                            </div>
                            <div class="energy-bar-bg">
                                <div class="energy-bar-fill {{ $cBg }}" style="width: {{ $wr }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ========================================== --}}
    {{-- 3. ESTADÍSTICAS DE BITS                    --}}
    {{-- ========================================== --}}
    <h3 class="section-subtitle"><i class="fas fa-caret-up me-2" style="color: var(--shonen-yellow);"></i> Estadísticas de Bits</h3>

    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle border-0">
            <thead>
                <tr>
                    <th style="min-width: 200px; padding-left: 20px !important;">
                        <a href="{{ route('stats.separate', ['sort' => 'bit', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="justify-content-start {{ $sort == 'bit' ? 'active-sort' : '' }}">
                            BIT @if($sort == 'bit') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('stats.separate', ['sort' => 'total_partidas', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="{{ $sort == 'total_partidas' ? 'active-sort' : '' }}">
                            BATALLAS @if($sort == 'total_partidas') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                    <th class="text-center" style="min-width: 150px;">
                        <a href="{{ route('stats.separate', ['sort' => 'percentage_victories', 'order' => $order == 'asc' ? 'desc' : 'asc']) }}" class="{{ $sort == 'percentage_victories' ? 'active-sort' : '' }}">
                            WIN RATE (%) @if($sort == 'percentage_victories') <i class="fas fa-caret-{{ $order == 'asc' ? 'up' : 'down' }} ms-1"></i> @endif
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($bitStats as $stat)
                    @php
                        $wr = $stat->percentage_victories;
                        if ($wr >= 60) { $cText = 'wr-s'; $cBg = 'bg-wr-s'; }
                        elseif ($wr >= 50) { $cText = 'wr-a'; $cBg = 'bg-wr-a'; }
                        elseif ($wr >= 40) { $cText = 'wr-b'; $cBg = 'bg-wr-b'; }
                        else { $cText = 'wr-c'; $cBg = 'bg-wr-c'; }
                    @endphp
                    <tr>
                        <td class="ps-4 border-0 py-3"><span class="piece-name">{{ $stat->bit }}</span></td>
                        <td class="text-center border-0"><span class="stat-number">{{ $stat->total_partidas }}</span></td>
                        <td class="pe-4 border-0">
                            <div class="d-flex justify-content-between align-items-end mb-1 px-1">
                                <span class="win-rate-text {{ $cText }}">{{ number_format($wr, 1) }}%</span>
                                <span class="fw-bold text-white opacity-50" style="font-size:0.8rem;">{{ $stat->total_victorias }}W - {{ $stat->total_derrotas }}L</span>
                            </div>
                            <div class="energy-bar-bg">
                                <div class="energy-bar-fill {{ $cBg }}" style="width: {{ $wr }}%"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
