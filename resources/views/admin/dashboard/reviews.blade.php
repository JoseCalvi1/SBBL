@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2 class="text-center mb-4 text-white">📊 Seguimiento mensual de revisiones</h2>

    <!-- ================= FILTROS ================= -->
    <form method="GET" class="card card-body mb-4">
        <div class="row g-3 align-items-end">

            <div class="col-md-3">
                <label>Mes</label>
                <select name="month" class="form-control">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-3">
                <label>Año</label>
                <select name="year" class="form-control">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-4">
                <label>Revisor</label>
                <select name="user_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                            {{ $user->is_jury ? '(Jurado)' : '(Árbitro)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">Filtrar</button>
            </div>

        </div>
    </form>



    @if ($userId)
        <div class="alert alert-info py-2">
            Mostrando actividad mensual de:
            <strong>{{ optional($users->firstWhere('id', $userId))->name }}</strong>
        </div>
    @endif

    <!-- ================= ÁRBITROS ================= -->
    <div class="card mb-4">
        <div class="card-header">🧑‍⚖️ Actividad mensual de árbitros</div>

        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Árbitro</th>
                        <th>Eventos revisados</th>
                        <th>Total revisiones</th>
                        <th>Aprobados</th>
                        <th>Rechazados</th>
                        <th>Pendientes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($refereeMonthlyStats as $stat)
                        <tr>
                            <td>{{ optional(\App\Models\User::find($stat->user_id))->name }}</td>
                            <td><strong>{{ $stat->total_events }}</strong></td>
                            <td>{{ $stat->total_reviews }}</td>
                            <td class="text-success">{{ $stat->approved }}</td>
                            <td class="text-danger">{{ $stat->rejected }}</td>
                            <td class="text-warning">{{ $stat->pending }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No hay datos de árbitros
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= JURADO ================= -->
    <div class="card mb-4">
        <div class="card-header">🏁 Actividad mensual de jurado</div>

        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Jurado</th>
                        <th>Eventos</th>
                        <th>Total revisiones</th>
                        <th>Aprobados</th>
                        <th>Rechazados</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($juryMonthlyStats as $stat)
                        <tr>
                            <td>{{ optional(\App\Models\User::find($stat->user_id))->name }}</td>
                            <td><strong>{{ $stat->total_events }}</strong></td>
                            <td>{{ $stat->total_reviews }}</td>
                            <td class="text-success">{{ $stat->approved }}</td>
                            <td class="text-danger">{{ $stat->rejected }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No hay datos de jurado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= DETALLE ================= -->
    <div class="card">
        <div class="card-header">📋 Detalle de revisiones</div>

        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Revisor</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- Árbitros --}}
                    @foreach ($refereeReviews as $review)
                        <tr>
                            <td>{{ $review->event->name }}</td>
                            <td>{{ $review->referee->name }}</td>
                            <td>Árbitro</td>
                            <td>
                                <span class="badge
                                    @if($review->status === 'approved') bg-success
                                    @elseif($review->status === 'rejected') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ strtoupper($review->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>{{ $review->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach

                    {{-- Jurado desde event_reviews --}}
                    @foreach ($juryReviewsFromEventReviews as $review)
                        <tr class="table-warning">
                            <td>{{ $review->event->name }}</td>
                            <td>{{ $review->referee->name }}</td>
                            <td>Jurado</td>
                            <td>
                                <span class="badge
                                    @if($review->status === 'approved') bg-success
                                    @elseif($review->status === 'rejected') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ strtoupper($review->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>{{ $review->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach

                    {{-- Jurado final --}}
                    @foreach ($juryReviewsFromJudgeTable as $review)
                        <tr class="table-info">
                            <td>{{ $review->event->name }}</td>
                            <td>{{ $review->judge->name }}</td>
                            <td>Jurado (final)</td>
                            <td>
                                <span class="badge {{ $review->final_status === 'approved' ? 'bg-success' : 'bg-danger' }}">
                                    {{ strtoupper($review->final_status) }}
                                </span>
                            </td>
                            <td>{{ $review->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach

                    @if (
                        $refereeReviews->isEmpty() &&
                        $juryReviewsFromEventReviews->isEmpty() &&
                        $juryReviewsFromJudgeTable->isEmpty()
                    )
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No hay revisiones para este filtro
                            </td>
                        </tr>
                    @endif

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
