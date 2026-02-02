@extends('layouts.app')

@section('title', 'Informe Semanal - SBBL')

@section('styles')
<style>
    /* --- ESTILO DE INFORME TÁCTICO --- */
    :root {
        --report-bg: #121212;
        --card-bg: rgba(30, 30, 47, 0.9);
        --neon-green: #20c997;
        --neon-blue: #0dcaf0;
        --neon-gold: #ffc107;
    }

    .report-wrapper {
        background-image: url('../images/webTile2.png');
        background-size: 300px;
        background-repeat: repeat;
        min-height: 100vh;
        padding-bottom: 3rem;
        position: relative;
    }

    .report-wrapper::before {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(10, 10, 15, 0.94); z-index: 0;
    }

    .content-layer { position: relative; z-index: 2; }

    /* --- CABECERA --- */
    .report-header {
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 1rem; margin-bottom: 2rem;
        display: flex; justify-content: space-between; align-items: center;
    }
    .report-title {
        font-family: monospace; text-transform: uppercase; letter-spacing: 1px;
        color: white; margin: 0;
    }
    .report-date {
        font-family: monospace; color: #666; font-size: 0.9rem;
    }

    /* --- TARJETAS DE TORNEO (EVENTOS) --- */
    .event-card {
        background: var(--card-bg);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        overflow: hidden;
        transition: 0.3s;
        height: 100%;
        position: relative;
    }
    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
        border-color: var(--neon-gold);
    }

    .event-img {
        height: 120px; background-size: cover; background-position: center;
        position: relative;
    }
    .event-overlay {
        position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.3));
    }
    .event-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--neon-gold); color: black; font-weight: bold;
        padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;
    }

    .event-body { padding: 15px; }
    .event-title { font-weight: bold; color: white; margin-bottom: 5px; text-transform: uppercase; font-size: 0.9rem; }
    .event-meta { font-size: 0.75rem; color: #aaa; margin-bottom: 15px; font-family: monospace; }

    .winner-list { list-style: none; padding: 0; margin: 0; font-size: 0.85rem; }
    .winner-item { display: flex; align-items: center; margin-bottom: 4px; color: #ddd; }
    .medal-icon { margin-right: 8px; width: 20px; text-align: center; }

    /* --- LOGS DE DUELOS DE EQUIPO --- */
    .battle-log {
        background: rgba(0,0,0,0.3);
        border-left: 3px solid var(--neon-blue);
        padding: 15px; margin-bottom: 10px;
        border-radius: 0 4px 4px 0;
        transition: 0.2s;
        display: flex; justify-content: space-between; align-items: center;
    }
    .battle-log:hover { background: rgba(255,255,255,0.05); }

    .team-name { font-weight: bold; color: white; }
    .team-name.winner { color: var(--neon-green); text-shadow: 0 0 5px rgba(32, 201, 151, 0.3); }
    .vs-badge { color: #666; font-size: 0.8rem; margin: 0 10px; font-style: italic; }
    .score-badge {
        background: #1e1e2f; padding: 4px 8px; border-radius: 4px;
        font-family: monospace; border: 1px solid #444; color: white;
    }
</style>
@endsection

@section('content')
<div class="report-wrapper">
    <div class="container content-layer pt-4">

        {{-- CABECERA DEL INFORME --}}
        <div class="report-header">
            <div>
                <h2 class="report-title"><i class="fas fa-clipboard-list me-2"></i>Informe Semanal</h2>
                <small class="text-white">Resumen de operaciones (Últimos 7 días)</small>
            </div>
            <div class="report-date d-none d-md-block">
                {{ now()->subDays(7)->format('d/m') }} - {{ now()->format('d/m/Y') }}
            </div>
        </div>

        {{-- SECCIÓN 1: TORNEOS --}}
        <h4 class="text-warning mb-4 text-uppercase fw-bold" style="letter-spacing: 1px;">
            <i class="fas fa-trophy me-2"></i>Torneos Finalizados
        </h4>

        <div class="row g-4 mb-5">
            @forelse($eventos as $evento)
                <div class="col-md-6 col-lg-3">
                    <div class="event-card">
                        {{-- Imagen --}}
                        <div class="event-img" style="background-image: url(/storage/{{ $evento->imagen }});">
                            <div class="event-overlay"></div>
                            <div class="event-badge">{{ $evento->region->name }}</div>
                        </div>

                        {{-- Contenido --}}
                        <div class="event-body">
                            <div class="event-title text-truncate" title="{{ $evento->name }}">{{ $evento->name }}</div>
                            <div class="event-meta">
                                <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($evento->date)->format('d/m/Y') }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-users me-1"></i> {{ $evento->beys }} Beys
                            </div>

                            {{-- Lista de Ganadores --}}
                            <ul class="winner-list">
                                @php
                                    $puestosOrden = ['primero', 'segundo', 'tercero'];

                                    // Usamos function() use (...) en lugar de fn() => para compatibilidad
                                    $participantesEvento = $participantes[$evento->id] ?? [];

                                    $top3 = collect($participantesEvento)
                                        ->filter(function($p) use ($puestosOrden) {
                                            return in_array($p->puesto, $puestosOrden);
                                        })
                                        ->sortBy(function($p) use ($puestosOrden) {
                                            return array_search($p->puesto, $puestosOrden);
                                        });
                                @endphp

                                @forelse($top3 as $p)
                                    @php
                                        // Reemplazo de match por if/elseif tradicional
                                        $icono = '';
                                        $clase = '';

                                        if ($p->puesto == 'primero') {
                                            $icono = '🥇';
                                            $clase = 'text-warning fw-bold';
                                        } elseif ($p->puesto == 'segundo') {
                                            $icono = '🥈';
                                        } elseif ($p->puesto == 'tercero') {
                                            $icono = '🥉';
                                        }
                                    @endphp

                                    <li class="winner-item {{ $clase }}">
                                        <span class="medal-icon">{{ $icono }}</span>
                                        <span class="text-truncate">{{ $p->user_name }}</span>
                                    </li>
                                @empty
                                    <li class="text-muted small">Resultados pendientes de procesar.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-dark border-secondary text-center" style="background: none !important;">
                        <small class="text-white">No se han registrado torneos en este periodo.</small>
                    </div>
                </div>
            @endforelse
        </div>


        {{-- SECCIÓN 2: DUELOS DE EQUIPO --}}
        <h4 class="text-info mb-4 text-uppercase fw-bold" style="letter-spacing: 1px;">
            <i class="fas fa-shield-alt me-2"></i>Conflictos de Equipos
        </h4>

        <div class="row">
            <div class="col-12">
                @forelse($duelosEquipo as $duelo)
                    <div class="battle-log">
                        <div class="d-flex align-items-center flex-grow-1">
                            <span class="text-white small me-3 font-monospace d-none d-sm-block">
                                {{ \Carbon\Carbon::parse($duelo->created_at)->format('d/m') }}
                            </span>

                            <div class="d-flex align-items-center flex-wrap">
                                {{-- Equipo 1 --}}
                                <span class="team-name {{ $duelo->result_1 > $duelo->result_2 ? 'winner' : '' }}">
                                    {{ $duelo->team1_name }}
                                </span>

                                <span class="vs-badge">VS</span>

                                {{-- Equipo 2 --}}
                                <span class="team-name {{ $duelo->result_2 > $duelo->result_1 ? 'winner' : '' }}">
                                    {{ $duelo->team2_name }}
                                </span>
                            </div>
                        </div>

                        {{-- Resultado --}}
                        <div class="score-badge">
                            {{ $duelo->result_1 }} - {{ $duelo->result_2 }}
                        </div>
                    </div>
                @empty
                    <div class="alert alert-dark border-secondary text-center" style="background: none !important;">
                        <small class="text-white">Sin actividad de equipos registrada esta semana.</small>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
