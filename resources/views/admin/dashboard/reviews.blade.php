@extends('layouts.app')

@section('title', 'Seguimiento de Revisiones')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Complementos para la tabla y paneles */
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
    .table-tactical tbody tr:hover {
        background: rgba(0, 0, 0, 0.4);
    }

    .stat-card {
        border: 3px solid #000;
        padding: 20px;
        text-align: center;
        box-shadow: 5px 5px 0 #000;
        transition: 0.2s;
    }
    .stat-card:hover { transform: translate(-2px, -2px); }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5">

    <div class="text-center mb-5">
        <h2 class="font-bangers" style="font-size: 3.5rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);">
            <i class="fas fa-clipboard-check me-2 text-white" style="text-shadow:none;"></i> CENTRO DE SUPERVISIÓN
        </h2>
        <p class="text-white fw-bold fs-5">
            @if($month === 'all' && $year === 'all')
                Rendimiento Histórico Total de Árbitros y Jueces.
            @else
                Seguimiento de Rendimiento de Árbitros y Jueces.
            @endif
        </p>
    </div>

    <div class="command-panel p-4 mb-5" style="background: rgba(0,0,0,0.5); border: 2px solid var(--shonen-cyan);">
        <form method="GET" action="{{ route('admin.dashboard.reviews') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="text-white font-bangers fs-5 mb-2">MES</label>
                    <select name="month" class="form-control bg-dark text-white border-secondary">
                        <option value="all" {{ $month === 'all' ? 'selected' : '' }}>-- TODOS LOS MESES --</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ strtoupper(\Carbon\Carbon::create()->month($m)->translatedFormat('F')) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="text-white font-bangers fs-5 mb-2">AÑO</label>
                    <select name="year" class="form-control bg-dark text-white border-secondary">
                        <option value="all" {{ $year === 'all' ? 'selected' : '' }}>-- HISTÓRICO --</option>
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="text-white font-bangers fs-5 mb-2">FILTRAR POR SUPERVISOR</label>
                    <select name="user_id" class="form-control select2">
                        <option value="">-- Todos los supervisores --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} {{ $user->hasRole('juez') ? '(Juez)' : '(Árbitro)' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-info font-bangers fs-5 w-100 py-2" style="letter-spacing: 1px;">FILTRAR</button>
                </div>
            </div>
        </form>
    </div>

    @if ($userId)
        <div class="alert alert-shonen alert-shonen-info text-center mb-4">
            <div class="text-white">MOSTRANDO ACTIVIDAD EXCLUSIVA DE: <strong class="text-warning">{{ strtoupper(optional($users->firstWhere('id', $userId))->name) }}</strong></div>
        </div>
    @endif

    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="stat-card" style="background: var(--sbbl-blue-2);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">REVISIONES TOTALES</h6>
                <div class="font-bangers text-white" style="font-size: 3rem;">{{ $globalStats->total_reviews }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-success" style="background: rgba(0, 255, 0, 0.1);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">APROBADAS</h6>
                <div class="font-bangers text-success" style="font-size: 3rem;">{{ $globalStats->approved }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-danger" style="background: rgba(255, 42, 42, 0.1);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">RECHAZADAS</h6>
                <div class="font-bangers text-danger" style="font-size: 3rem;">{{ $globalStats->rejected }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-warning" style="background: rgba(255, 193, 7, 0.1);">
                <h6 class="text-white-50 font-bangers fs-5 m-0">PENDIENTES</h6>
                <div class="font-bangers text-warning" style="font-size: 3rem;">{{ $globalStats->pending }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <h4 class="font-bangers text-white mb-3" style="font-size: 2rem;"><i class="fas fa-flag text-secondary me-2"></i> RENDIMIENTO: ÁRBITROS</h4>
            <div class="table-responsive">
                <table class="table table-tactical text-center">
                    <thead>
                        <tr>
                            <th class="text-start">Operativo</th>
                            <th>Torneos</th>
                            <th>Total Rev.</th>
                            <th class="text-success"><i class="fas fa-check"></i></th>
                            <th class="text-danger"><i class="fas fa-times"></i></th>
                            <th class="text-warning"><i class="fas fa-clock"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($refereeMonthlyStats as $stat)
                            <tr>
                                <td class="text-start fw-bold fs-5">{{ optional(\App\Models\User::find($stat->user_id))->name }}</td>
                                <td>{{ $stat->total_events }}</td>
                                <td class="fs-5 text-info fw-bold">{{ $stat->total_reviews }}</td>
                                <td class="text-success fw-bold">{{ $stat->approved }}</td>
                                <td class="text-danger fw-bold">{{ $stat->rejected }}</td>
                                <td class="text-warning fw-bold">{{ $stat->pending }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-white-50 p-4">Sin actividad de árbitros registrada en este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-6">
            <h4 class="font-bangers text-white mb-3" style="font-size: 2rem;"><i class="fas fa-gavel me-2" style="color: var(--sbbl-gold);"></i> RENDIMIENTO: JURADO</h4>
            <div class="table-responsive">
                <table class="table table-tactical text-center">
                    <thead>
                        <tr>
                            <th class="text-start">Magistrado</th>
                            <th>Torneos</th>
                            <th>Total Rev.</th>
                            <th class="text-success"><i class="fas fa-check"></i></th>
                            <th class="text-danger"><i class="fas fa-times"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($juryMonthlyStats as $stat)
                            <tr>
                                <td class="text-start fw-bold fs-5" style="color: var(--sbbl-gold);">{{ optional(\App\Models\User::find($stat->user_id))->name }}</td>
                                <td>{{ $stat->total_events }}</td>
                                <td class="fs-5 text-info fw-bold">{{ $stat->total_reviews }}</td>
                                <td class="text-success fw-bold">{{ $stat->approved }}</td>
                                <td class="text-danger fw-bold">{{ $stat->rejected }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-white-50 p-4">Sin actividad de jueces registrada en este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h4 class="font-bangers text-white mb-3" style="font-size: 2rem;"><i class="fas fa-list-alt text-white me-2"></i> REGISTRO DETALLADO DE ACCIONES</h4>
    <div class="table-responsive">
        <table class="table table-tactical">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Torneo Afectado</th>
                    <th>Supervisor</th>
                    <th>Rango</th>
                    <th class="text-center">Estado del Veredicto</th>
                </tr>
            </thead>
            <tbody>
                {{-- Árbitros --}}
                @foreach ($refereeReviews as $review)
                    <tr>
                        <td class="text-white-50">
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $review->created_at ? $review->created_at->format('d/m/Y - H:i') : 'Sin fecha' }}
                        </td>
                        <td class="fw-bold">{{ $review->event->name ?? 'Evento borrado' }}</td>
                        <td>{{ $review->referee->name ?? 'Desconocido' }}</td>
                        <td><span class="badge bg-dark border border-secondary">Árbitro</span></td>
                        <td class="text-center">
                            @if($review->status === 'approved') <span class="badge bg-success border border-white font-bangers fs-6">APROBADO</span>
                            @elseif($review->status === 'rejected') <span class="badge bg-danger border border-white font-bangers fs-6">RECHAZADO</span>
                            @else <span class="badge bg-warning text-dark border border-dark font-bangers fs-6">PENDIENTE</span>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- Jurado (Revisiones normales) --}}
                @foreach ($juryReviewsFromEventReviews as $review)
                    <tr style="background-color: rgba(255, 193, 7, 0.05);">
                        <td class="text-white-50">
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $review->created_at ? $review->created_at->format('d/m/Y - H:i') : 'Sin fecha' }}
                        </td>
                        <td class="fw-bold">{{ $review->event->name ?? 'Evento borrado' }}</td>
                        <td style="color: var(--sbbl-gold);">{{ $review->referee->name ?? 'Desconocido' }}</td>
                        <td><span class="badge text-dark border border-dark" style="background: var(--sbbl-gold);">Juez (Rev)</span></td>
                        <td class="text-center">
                            @if($review->status === 'approved') <span class="badge bg-success border border-white font-bangers fs-6">APROBADO</span>
                            @elseif($review->status === 'rejected') <span class="badge bg-danger border border-white font-bangers fs-6">RECHAZADO</span>
                            @else <span class="badge bg-warning text-dark border border-dark font-bangers fs-6">PENDIENTE</span>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- Jurado (Veredicto Final) --}}
                @foreach ($juryReviewsFromJudgeTable as $review)
                    <tr style="background-color: rgba(0, 255, 204, 0.05);">
                        <td class="text-white-50">
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $review->created_at ? $review->created_at->format('d/m/Y - H:i') : 'Sin fecha' }}
                        </td>
                        <td class="fw-bold text-info">{{ $review->event->name ?? 'Evento borrado' }}</td>
                        <td style="color: var(--shonen-cyan);">{{ $review->judge->name ?? 'Desconocido' }}</td>
                        <td><span class="badge text-dark border border-dark" style="background: var(--shonen-cyan);">Juez (FINAL)</span></td>
                        <td class="text-center">
                            @if($review->final_status === 'approved') <span class="badge bg-success border border-white font-bangers fs-6">APROBADO</span>
                            @else <span class="badge bg-danger border border-white font-bangers fs-6">RECHAZADO</span>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if ($refereeReviews->isEmpty() && $juryReviewsFromEventReviews->isEmpty() && $juryReviewsFromJudgeTable->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center text-white-50 p-4">No se han encontrado registros de actividad para los filtros seleccionados.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '-- Buscar operativo --',
            width: '100%',
            dropdownCssClass: "bg-black border-warning text-white"
        });
    });
</script>
@endsection
