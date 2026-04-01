@extends('layouts.app')

@section('title', 'Informe Semanal - SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: INFORME TÁCTICO (El resto hereda del layout)
       ==================================================================== */

    /* --- CABECERA --- */
    .report-header {
        border-bottom: 4px solid #000;
        padding-bottom: 1.5rem;
        margin-bottom: 2.5rem;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 15px;
    }
    .report-title {
        font-family: 'Bangers', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0; line-height: 1;
    }
    .report-subtitle {
        font-weight: 900; color: #fff; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;
    }

    .report-date {
        font-family: 'Bangers', cursive;
        color: #fff; font-size: 1.5rem;
        background: #000; padding: 8px 20px;
        border: 3px solid var(--sbbl-gold);
        transform: skewX(-10deg);
        box-shadow: 5px 5px 0 var(--sbbl-blue-3);
        display: inline-block; letter-spacing: 1px;
    }
    .report-date > span { display: block; transform: skewX(10deg); }

    /* Títulos de Sección */
    .section-heading {
        font-family: 'Bangers', cursive;
        font-size: 2.2rem;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 4px dashed #000;
        padding-bottom: 10px;
        margin-bottom: 30px;
        display: inline-block;
        text-shadow: 2px 2px 0 #000;
    }

    /* --- TARJETAS DE TORNEO (EVENTOS) --- */
    .shonen-event-card {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        border-radius: 0 15px 0 15px;
        box-shadow: 6px 6px 0 #000;
        overflow: hidden;
        transition: 0.2s;
        height: 100%;
        display: flex; flex-direction: column;
    }
    .shonen-event-card:hover {
        transform: translate(-2px, -2px);
        box-shadow: 8px 8px 0 var(--sbbl-gold);
        border-color: var(--sbbl-gold);
    }

    .event-img {
        height: 130px; background-size: cover; background-position: center;
        border-bottom: 3px solid #000; position: relative;
    }
    .event-overlay {
        position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0.1));
    }
    .shonen-event-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--sbbl-gold); color: #000;
        font-family: 'Bangers', cursive; padding: 5px 10px;
        border: 2px solid #000; transform: skewX(-5deg);
        box-shadow: 3px 3px 0 var(--shonen-red);
        font-size: 1.1rem; letter-spacing: 1px;
    }
    .shonen-event-badge > span { display: block; transform: skewX(5deg); }

    .event-body { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
    .event-title { font-family: 'Bangers', cursive; font-size: 1.6rem; color: #fff; text-shadow: 1px 1px 0 #000; letter-spacing: 1px; margin-bottom: 5px;}
    .event-meta { font-size: 0.9rem; color: #fff; font-weight: 900; margin-bottom: 15px; border-bottom: 2px dashed #000; padding-bottom: 10px; text-transform: uppercase; }

    /* Podio dentro de la tarjeta */
    .winner-list { list-style: none; padding: 0; margin: 0; font-size: 1rem; }
    .winner-item {
        background: #000; padding: 6px 10px; margin-bottom: 6px;
        border: 2px solid #333; transform: skewX(-5deg);
        display: flex; align-items: center; color: #fff; font-weight: bold;
    }
    .winner-item > * { transform: skewX(5deg); }
    .winner-item.first { border-color: var(--sbbl-gold); color: var(--sbbl-gold); box-shadow: 2px 2px 0 var(--shonen-red); }
    .winner-item.second { border-color: #fff; color: #fff; box-shadow: 2px 2px 0 #000; }
    .winner-item.third { border-color: #ff9d47; color: #ff9d47; box-shadow: 2px 2px 0 #000; }
    .medal-icon { margin-right: 10px; font-size: 1.1rem; }

    /* --- LOGS DE DUELOS DE EQUIPO --- */
    .battle-log {
        background: var(--sbbl-blue-3);
        border: 3px solid #000;
        padding: 15px 20px; margin-bottom: 15px;
        transform: skewX(-2deg);
        box-shadow: 5px 5px 0 #000;
        transition: 0.2s;
        display: flex; justify-content: space-between; align-items: center;
    }
    .battle-log > * { transform: skewX(2deg); }
    .battle-log:hover {
        border-color: var(--sbbl-gold);
        box-shadow: 5px 5px 0 var(--shonen-cyan);
        transform: translate(-2px, -2px) skewX(-2deg);
    }

    .team-name { font-family: 'Bangers', cursive; font-size: 1.8rem; letter-spacing: 1px; color: #fff; text-shadow: 2px 2px 0 #000; }
    .team-name.winner { color: var(--sbbl-gold); text-shadow: 2px 2px 0 var(--shonen-red); }

    .vs-badge { font-family: 'Bangers', cursive; font-size: 1.4rem; color: var(--shonen-red); margin: 0 15px; text-shadow: 1px 1px 0 #000; }

    .score-badge {
        background: #000; padding: 5px 15px;
        font-family: 'Bangers', cursive; font-size: 2rem; color: #fff;
        border: 3px solid var(--sbbl-gold); box-shadow: 4px 4px 0 var(--shonen-red);
        line-height: 1;
    }

    @media (max-width: 768px) {
        .battle-log { flex-direction: column; text-align: center; gap: 15px; padding: 20px; }
        .team-name { font-size: 1.5rem; }
        .score-badge { width: 100%; text-align: center; font-size: 2.2rem; }
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    {{-- CABECERA DEL INFORME --}}
    <div class="report-header">
        <div>
            <h2 class="report-title"><i class="fas fa-clipboard-list me-2" style="color: var(--text-main);"></i>Informe Semanal</h2>
            <div class="report-subtitle mt-2">RESUMEN DE OPERACIONES (Últimos 7 días)</div>
        </div>
        <div class="report-date">
            <span>{{ now()->subDays(7)->format('d/m') }} - {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- SECCIÓN 1: TORNEOS --}}
    <div class="section-heading">
        <i class="fas fa-trophy me-2"></i> Torneos Finalizados
    </div>

    <div class="row g-4 mb-5">
        @forelse($eventos as $evento)
            <div class="col-md-6 col-lg-3">
                <div class="shonen-event-card">
                    {{-- Imagen --}}
                    <div class="event-img" style="background-image: url(/storage/{{ $evento->imagen }});">
                        <div class="event-overlay"></div>
                        <div class="shonen-event-badge"><span>{{ $evento->region->name }}</span></div>
                    </div>

                    {{-- Contenido --}}
                    <div class="event-body">
                        <div class="event-title text-truncate" title="{{ $evento->name }}">{{ $evento->name }}</div>
                        <div class="event-meta">
                            <i class="far fa-calendar-alt me-1 text-white"></i> {{ \Carbon\Carbon::parse($evento->date)->format('d/m/Y') }}
                            <span class="mx-2" style="color: var(--sbbl-gold);">|</span>
                            <i class="fas fa-users me-1 text-white"></i> {{ $evento->beys }} Beys
                        </div>

                        {{-- Lista de Ganadores --}}
                        <ul class="winner-list mt-auto">
                            @php
                                $puestosOrden = ['primero', 'segundo', 'tercero'];
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
                                    $icono = '';
                                    $clase = '';

                                    if ($p->puesto == 'primero') {
                                        $icono = '🥇';
                                        $clase = 'first';
                                    } elseif ($p->puesto == 'segundo') {
                                        $icono = '🥈';
                                        $clase = 'second';
                                    } elseif ($p->puesto == 'tercero') {
                                        $icono = '🥉';
                                        $clase = 'third';
                                    }
                                @endphp

                                <li class="winner-item {{ $clase }}">
                                    <span><span class="medal-icon">{{ $icono }}</span> <span class="text-truncate">{{ $p->user_name }}</span></span>
                                </li>
                            @empty
                                <li class="text-white small fw-bold text-center mt-3 border border-dark p-2 bg-dark" style="border-width: 2px !important;">Pendiente de procesar.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-dark border-secondary text-center" style="background: rgba(0,0,0,0.5) !important; border: 3px solid #000 !important; border-radius: 0; box-shadow: 4px 4px 0 #000;">
                    <span class="font-bangers fs-3 text-white">NO SE HAN REGISTRADO TORNEOS EN ESTE PERIODO.</span>
                </div>
            </div>
        @endforelse
    </div>


    {{-- SECCIÓN 2: DUELOS DE EQUIPO --}}
    <div class="section-heading" style="border-bottom-color: var(--shonen-cyan);">
        <i class="fas fa-shield-alt me-2" style="color: var(--shonen-cyan);"></i> Conflictos de Equipos
    </div>

    <div class="row pb-5">
        <div class="col-12">
            @forelse($duelosEquipo as $duelo)
                <div class="battle-log">
                    <div class="d-flex flex-column flex-sm-row align-items-center flex-grow-1 w-100">
                        {{-- Fecha --}}
                        <span class="text-white fw-bold me-sm-4 mb-2 mb-sm-0" style="font-size: 1rem; border-right: 3px solid #000; padding-right: 15px;">
                            <i class="far fa-clock me-1 text-white"></i> {{ \Carbon\Carbon::parse($duelo->created_at)->format('d/m') }}
                        </span>

                        <div class="d-flex align-items-center justify-content-center flex-wrap flex-grow-1">
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
                    <div class="score-badge mt-3 mt-sm-0 ms-sm-3">
                        {{ $duelo->result_1 }} <span style="color: var(--shonen-red);">-</span> {{ $duelo->result_2 }}
                    </div>
                </div>
            @empty
                <div class="alert alert-dark border-secondary text-center" style="background: rgba(0,0,0,0.5) !important; border: 3px solid #000 !important; border-radius: 0; box-shadow: 4px 4px 0 #000;">
                    <span class="font-bangers fs-3 text-white">SIN ACTIVIDAD DE EQUIPOS REGISTRADA ESTA SEMANA.</span>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
