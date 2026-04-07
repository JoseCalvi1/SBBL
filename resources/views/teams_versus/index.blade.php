@extends('layouts.app')

@section('title', 'Gestión Duelos de Equipo')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS TÁCTICOS (Heredados del panel Admin)
       ==================================================================== */
    .page-title {
        font-family: 'Oswald', sans-serif;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .table-tactical {
        border: 3px solid #000;
        box-shadow: 6px 6px 0 #000;
        background: var(--sbbl-blue-2);
        margin-bottom: 0;
    }
    .table-tactical thead {
        background: #000;
        border-bottom: 4px solid var(--sbbl-gold);
    }
    .table-tactical th {
        font-family: 'Oswald', sans-serif;
        color: var(--shonen-cyan);
        font-size: 1.1rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        border: none;
        padding: 15px;
    }
    .table-tactical td {
        background: transparent;
        color: #fff;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        vertical-align: middle;
        padding: 15px;
    }
    .table-tactical tbody tr:hover { background: rgba(0, 0, 0, 0.4); }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5">

    <div class="text-center mb-5">
        <h2 class="page-title"><i class="fas fa-users-cog me-2 text-white" style="text-shadow:none;"></i> DUELOS DE EQUIPOS</h2>
        <p class="text-white fw-bold">Administración y resolución de enfrentamientos oficiales.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-shonen alert-shonen-success mb-4 text-center"><div><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div></div>
    @endif

    <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h4 class="font-bangers text-white m-0" style="font-size: 2rem;">
                <i class="fas fa-list-alt me-2" style="color: var(--shonen-cyan);"></i> REGISTRO DE COMBATES
            </h4>
            <a href="{{ route('teams_versus.create') }}" class="btn-shonen btn-shonen-info text-center">
                <span><i class="fas fa-plus-circle me-2"></i> CREAR NUEVO DUELO</span>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-tactical text-center align-middle">
                <thead>
                    <tr>
                        <th>Equipo 1</th>
                        <th>Pts 1</th>
                        <th>Equipo 2</th>
                        <th>Pts 2</th>
                        <th>Modalidad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($versus as $duel)
                        <tr>
                            <td class="fw-bold fs-5 text-info">{{ $duel->versus_1->name ?? 'Desconocido' }}</td>
                            <td class="fw-bold fs-5">{{ $duel->result_1 }}</td>
                            <td class="fw-bold fs-5 text-warning">{{ $duel->versus_2->name ?? 'Desconocido' }}</td>
                            <td class="fw-bold fs-5">{{ $duel->result_2 }}</td>
                            <td><span class="badge bg-dark border border-secondary">{{ strtoupper($duel->matchup) }}</span></td>

                            {{-- ESTADO DEL DUELO --}}
                            <td>
                                @if($duel->status == 'OPEN') <span class="badge bg-success">ABIERTO</span>
                                @elseif($duel->status == 'INVALID') <span class="badge bg-danger">INVÁLIDO</span>
                                @else <span class="badge bg-secondary">{{ $duel->status ?? 'CERRADO' }}</span>
                                @endif
                            </td>

                            <td class="text-white-50"><i class="far fa-calendar-alt me-1"></i> {{ $duel->created_at ? $duel->created_at->format('d/m/Y') : '-' }}</td>

                            {{-- ACCIONES --}}
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">

                                    @if(!empty($duel->url))
                                        <a href="{{ $duel->url }}" target="_blank" class="btn btn-sm btn-danger border border-dark" title="Ver Vídeo">
                                            <i class="fab fa-youtube"></i>
                                        </a>
                                    @endif

                                    @if ($duel->status == 'OPEN')
                                        <form method="POST" action="{{ route('teams_versus.puntuarDuelo', ['duel' => $duel->id, 'mode' => $duel->matchup, 'winner' => $duel->team_id_1]) }}">
                                            @method('PUT') @csrf
                                            <button type="submit" class="btn btn-sm btn-success border border-dark" title="Confirmar Puntuación">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('teams_versus.edit', ['duel' => $duel->id]) }}" class="btn btn-sm btn-light border border-dark" title="Editar Duelo">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if ($duel->status != 'INVALID')
                                        <form method="POST" action="{{ route('teams_versus.invalidate', $duel->id) }}" onsubmit="return confirm('¿Estás seguro de invalidar este duelo? Sus puntos dejarán de contar.');">
                                            @method('PUT') @csrf
                                            <button type="submit" class="btn btn-sm btn-warning border border-dark" title="Invalidar Duelo">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('teams_versus.destroy', $duel->id) }}" onsubmit="return confirm('¿Estás totalmente seguro de eliminar este duelo? Esta acción NO se puede deshacer.');">
                                        @method('DELETE') @csrf
                                        <button type="submit" class="btn btn-sm" style="background-color: var(--shonen-red); color: white; border: 1px solid #000;" title="Eliminar Duelo">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($versus->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center text-white-50 p-4">Aún no se han registrado duelos entre equipos.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
