@extends('layouts.app')

@section('title', 'Resumen semanal')

@section('content')
<div class="container py-5 text-light">
    <h2 class="mb-5 border-bottom pb-2">🗓️ Resumen semanal (últimos 7 días)</h2>

    <h3 class="text-warning">🏆 Torneos</h3>
    <div class="row">
        @forelse($eventos as $evento)
            <div class="col-md-3 mb-4">
                <div class="card text-light h-100 rounded-4 shadow-sm border border-warning border-opacity-25 transition position-relative overflow-hidden"
                    style="transition: transform 0.2s; background-image: url(/storage/{{ $evento->imagen }}); background-size: cover; background-position: center;">

                    <!-- Capa oscura encima de la imagen -->
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0,0,0,0.8); z-index: 0;"></div>

                    <!-- Contenido de la tarjeta -->
                    <div class="card-header bg-transparent border-bottom border-secondary d-flex justify-content-between align-items-center rounded-top-4 position-relative">
                        <span class="fw-bold">{{ $evento->name }} ({{ $evento->region->name }})</span>
                        <div class="text-end small">
                            <span>{{ \Carbon\Carbon::parse($evento->date)->format('d/m/Y') }}</span><br>
                            <span class="text-warning">{{ $evento->beys }}</span>
                        </div>
                    </div>
                    <div class="card-body position-relative">
                        <ul class="list-unstyled mb-0">
                            @php
                                $puestosOrden = ['primero', 'segundo', 'tercero'];
                                $resto = collect($participantes[$evento->id] ?? [])->filter(fn($p) => !in_array($p->puesto, $puestosOrden));
                                $ordenados = collect($participantes[$evento->id] ?? [])
                                    ->filter(fn($p) => in_array($p->puesto, $puestosOrden))
                                    ->sortBy(fn($p) => array_search($p->puesto, $puestosOrden))
                                    ->merge($resto);
                            @endphp
                            @foreach($ordenados as $p)
                                @php
                                    if ($p->puesto == 'primero') {
                                        $puestoIcono = '🥇 1º';
                                    } elseif ($p->puesto == 'segundo') {
                                        $puestoIcono = '🥈 2º';
                                    } elseif ($p->puesto == 'tercero') {
                                        $puestoIcono = '🥉 3º';
                                    } else {
                                        $puestoIcono = $p->puesto . 'º';
                                    }
                                @endphp
                                <li class="mb-1">
                                    <strong>{{ $puestoIcono }}</strong> — {{ $p->user_name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>
        @empty
            <p class="text-muted">No se han jugado torneos esta semana.</p>
        @endforelse
    </div>

    <h3 class="text-success mt-5">⚔️ Duelos</h3>
    <div class="row">
        @forelse($duelos as $duelo)
            <div class="col-md-3 mb-3">
                <div class="card bg-dark text-light h-100 rounded-4 shadow-sm border border-success border-opacity-25 transition" style="transition: transform 0.2s;">
                    <div class="card-body">
                        <div class="mb-1 text-muted small">{{ \Carbon\Carbon::parse($duelo->created_at)->format('d/m/Y') }}</div>
                        @php
                            if ($duelo->result_1 > $duelo->result_2) {
                                $linea = '<strong class="text-success">' . $duelo->user1_name . '</strong> vs ' . $duelo->user2_name;
                            } elseif ($duelo->result_1 < $duelo->result_2) {
                                $linea = $duelo->user1_name . ' vs <strong class="text-success">' . $duelo->user2_name . '</strong>';
                            } else {
                                $linea = $duelo->user1_name . ' vs ' . $duelo->user2_name;
                            }

                            $linea .= ' <span class="text-muted">(' . $duelo->result_1 . ' - ' . $duelo->result_2 . ')</span>';
                        @endphp

                        <div class="fs-6">{!! $linea !!}</div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No se han registrado duelos esta semana.</p>
        @endforelse
    </div>

    <h3 class="text-success mt-5">⚔️ Duelos de equipo</h3>
    <div class="row">
        @forelse($duelosEquipo as $duelo)
            <div class="col-md-3 mb-3">
                <div class="card bg-dark text-light h-100 rounded-4 shadow-sm border border-info border-opacity-25 transition" style="transition: transform 0.2s;">
                    <div class="card-body">
                        <div class="mb-1 text-muted small">{{ \Carbon\Carbon::parse($duelo->created_at)->format('d/m/Y') }}</div>
                        @php
                            if ($duelo->result_1 > $duelo->result_2) {
                                $linea = '<strong class="text-success">' . $duelo->team1_name . '</strong> vs ' . $duelo->team2_name;
                            } elseif ($duelo->result_1 < $duelo->result_2) {
                                $linea = $duelo->team1_name . ' vs <strong class="text-success">' . $duelo->team2_name . '</strong>';
                            } else {
                                $linea = $duelo->team1_name . ' vs ' . $duelo->team2_name;
                            }

                            $linea .= ' <span class="text-muted">(' . $duelo->result_1 . ' - ' . $duelo->result_2 . ')</span>';
                        @endphp
                        <div class="fs-6">{!! $linea !!}</div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No se han registrado duelos de equipos esta semana.</p>
        @endforelse
    </div>

</div>

<style>
    .card:hover {
        transform: scale(1.02);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
    }
</style>
@endsection
