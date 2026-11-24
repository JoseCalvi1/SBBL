@extends('layouts.app')

@section('title', 'Control de Clasificatorios Nacional')

@section('styles')
<style>
    .qual-card { background: var(--sbbl-blue-2); border: 3px solid #000; box-shadow: 6px 6px 0 #000; }
    .stat-box { border: 3px solid #000; box-shadow: 5px 5px 0 #000; padding: 18px; text-align: center; height: 100%; }
    .table-qual { background: #0d1322; border: 2px solid #000; margin-bottom: 0; }
    .table-qual thead { background: #000; border-bottom: 3px solid var(--sbbl-gold); }
    .table-qual th { font-family: 'Oswald', sans-serif; color: var(--shonen-cyan); text-transform: uppercase; padding: 11px; border: none; }
    .table-qual td { color: #fff; border-bottom: 1px solid rgba(255,255,255,0.08); vertical-align: middle; padding: 11px; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5 px-lg-4">

    <div class="text-center mb-4">
        <h2 class="font-bangers" style="font-size: 3.4rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000, 4px 4px 0 var(--shonen-red);">
            <i class="fas fa-crown me-2 text-white" style="text-shadow:none;"></i> CLASIFICATORIOS AL NACIONAL
        </h2>
        <p class="text-white-50 fw-bold mb-0">
            Control de eventos Clasificatorios, Top 5 por sede y listado oficial de Finalistas Clasificados (Top 2).
        </p>
    </div>

    {{-- TARJETAS KPI --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-box" style="background: rgba(56, 189, 248, 0.1); border-color: var(--shonen-cyan);">
                <div class="small text-white-50 font-Oswald">CLASIFICATORIOS</div>
                <div class="font-bangers text-info" style="font-size: 2.8rem;">{{ $qualifiers->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box" style="background: rgba(255, 255, 255, 0.06); border-color: #94a3b8;">
                <div class="small text-white-50 font-Oswald">BLADERS ÚNICOS</div>
                <div class="font-bangers text-white" style="font-size: 2.8rem;">{{ $totalUniqueParticipants }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box" style="background: rgba(234, 179, 8, 0.14); border-color: var(--sbbl-gold);">
                <div class="small text-white-50 font-Oswald">CLASIFICADOS (TOP 2)</div>
                <div class="font-bangers" style="font-size: 2.8rem; color: var(--sbbl-gold);">{{ $totalQualifiedUnique }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-box" style="background: rgba(0, 255, 136, 0.1); border-color: #00ff88;">
                <div class="small text-white-50 font-Oswald">RECAUDACIÓN NETA</div>
                <div class="font-bangers" style="font-size: 2.8rem; color: #00ff88;">{{ number_format($totalNetRevenue, 2, ',', '.') }} €</div>
            </div>
        </div>
    </div>

    {{-- BARRA DE CONTROLES + TOGGLE CLASIFICADOS --}}
    <div class="p-3 mb-4 border border-warning d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" style="background: #090d16; box-shadow: 5px 5px 0 #000;">
        <div class="form-check form-switch m-0 d-flex align-items-center gap-3">
            <input class="form-check-input m-0" type="checkbox" role="switch" id="toggleOnlyQualified" style="width: 3.4em; height: 1.7em; cursor: pointer;">
            <label class="form-check-label font-Oswald fs-4 text-warning" for="toggleOnlyQualified" style="cursor: pointer;">
                🏆 MOSTRAR SOLO CLASIFICADOS AL NACIONAL (FINALISTAS TOP 2)
            </label>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if($showAllSeasons)
                <a href="{{ route('admin.qualifiers.index') }}" class="btn btn-sm btn-outline-info fw-bold">
                    <i class="fas fa-calendar-check me-1"></i> Solo Temporada Actual (desde {{ \Carbon\Carbon::parse($seasonStart)->format('d/m/Y') }})
                </a>
            @else
                <a href="{{ route('admin.qualifiers.index', ['all_seasons' => 1]) }}" class="btn btn-sm btn-outline-secondary fw-bold text-white">
                    <i class="fas fa-history me-1"></i> Ver Todas las Temporadas
                </a>
            @endif
            <a href="{{ route('events.create') }}" class="btn btn-sm btn-success fw-bold border border-dark">
                <i class="fas fa-plus me-1"></i> Nuevo Clasificatorio
            </a>
        </div>
    </div>

    {{-- VISTA 1: LISTADO DE CLASIFICATORIOS Y SU TOP 5 --}}
    <div id="viewAllQualifiers">
        @forelse($qualifiers as $ev)
            <div class="qual-card p-4 mb-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 border-bottom border-secondary pb-3 mb-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark border border-dark fw-bold">CLASIFICATORIO</span>
                            <span class="badge bg-{{ $ev->status === 'OPEN' ? 'success' : ($ev->status === 'LIVE' ? 'danger' : 'secondary') }}">{{ $ev->status }}</span>
                            <span class="text-white-50 small fw-bold">
                                <i class="far fa-calendar-alt me-1"></i>{{ $ev->date ? \Carbon\Carbon::parse($ev->date)->format('d/m/Y') : '-' }}
                                · <i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $ev->city ?: ($ev->region->name ?? '-') }}
                            </span>
                        </div>
                        <h3 class="font-Oswald text-white m-0">{{ $ev->name }}</h3>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="text-end">
                            <div class="small text-white-50">INSCRITOS ACTIVOS</div>
                            <div class="font-Oswald fs-4 text-info">{{ $ev->active_participants_count }}</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-white-50">RECAUDACIÓN NETA</div>
                            <div class="font-Oswald fs-4" style="color: #00ff88;">{{ number_format($ev->net_revenue, 2, ',', '.') }} €</div>
                        </div>
                        <a href="{{ route('events.show', $ev->id) }}" class="btn btn-sm btn-outline-light fw-bold">
                            <i class="fas fa-external-link-alt me-1"></i> Ver Evento
                        </a>
                    </div>
                </div>

                @if($ev->top5->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-qual align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Puesto</th>
                                    <th>Blader</th>
                                    <th>Email</th>
                                    <th class="text-center">Estado Nacional</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ev->top5 as $idx => $player)
                                    @php
                                        $p = strtolower(trim((string) $player->pivot->puesto));
                                        $isFinalist = in_array($p, ['primero', 'segundo'], true);
                                        $medals = [
                                            'primero' => '🥇 1.º',
                                            'segundo' => '🥈 2.º',
                                            'tercero' => '🥉 3.º',
                                            'cuarto'  => '4.º',
                                            'quinto'  => '5.º',
                                        ];
                                    @endphp
                                    <tr style="{{ $isFinalist ? 'background: rgba(234, 179, 8, 0.12);' : '' }}">
                                        <td class="font-Oswald fs-5 {{ $isFinalist ? 'text-warning' : 'text-white' }}">
                                            {{ $medals[$p] ?? strtoupper($p) }}
                                        </td>
                                        <td class="fw-bold">
                                            {{ $player->name }} <span class="text-white-50 small">#{{ str_pad($player->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td class="text-white-50 small">{{ $player->email }}</td>
                                        <td class="text-center">
                                            @if($isFinalist)
                                                <span class="badge bg-warning text-dark border border-dark fw-bold px-2 py-1">
                                                    <i class="fas fa-trophy me-1"></i> CLASIFICADO AL NACIONAL
                                                </span>
                                            @else
                                                <span class="badge bg-dark border border-secondary text-white-50">Top 5</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 bg-black border border-secondary text-center text-white-50 small fw-bold">
                        <i class="fas fa-clock me-1"></i> Aún no se han guardado los resultados / Top 5 de este clasificatorio.
                    </div>
                @endif
            </div>
        @empty
            <div class="qual-card p-5 text-center text-white">
                <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                <h4 class="font-Oswald">NO HAY CLASIFICATORIOS REGISTRADOS TODAVÍA</h4>
                <p class="text-white-50 mb-3">Crea un nuevo evento con categoría <strong>Clasificatorio</strong> desde la Web o desde el Hub para que aparezca aquí.</p>
                <a href="{{ route('events.create') }}" class="btn btn-warning text-dark fw-bold">Crear Primer Clasificatorio</a>
            </div>
        @endforelse
    </div>

    {{-- VISTA 2 (TOGGLE ACTIVO): LISTADO UNIFICADO DE CLASIFICADOS AL NACIONAL --}}
    <div id="viewOnlyQualified" class="d-none">
        <div class="qual-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <h3 class="font-Oswald text-warning m-0">
                    <i class="fas fa-award me-2"></i> PLAZAS CONFIRMADAS PARA EL NACIONAL ({{ $qualifiedList->count() }})
                </h3>
                @if($qualifiedList->isNotEmpty())
                    <button type="button" class="btn btn-sm btn-info text-dark fw-bold" onclick="copyQualifiedList()">
                        <i class="fas fa-copy me-1"></i> Copiar Lista de Clasificados
                    </button>
                @endif
            </div>

            @if($qualifiedList->isEmpty())
                <div class="p-4 bg-black border border-secondary text-center text-white-50 fw-bold">
                    Todavía no hay ningún finalista (Top 2) cerrado en los clasificatorios de esta temporada.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-qual align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Blader Clasificado</th>
                                <th>Email</th>
                                <th class="text-center">Puesto</th>
                                <th>Clasificatorio / Sede</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qualifiedList as $idx => $q)
                                <tr>
                                    <td class="font-Oswald text-info">{{ $idx + 1 }}</td>
                                    <td class="fw-bold text-white">
                                        {{ $q['user_name'] }} <span class="text-white-50 small">#{{ str_pad($q['user_id'], 4, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="text-white-50 small">{{ $q['user_email'] }}</td>
                                    <td class="text-center">
                                        @if($q['puesto'] === 'primero')
                                            <span class="badge bg-warning text-dark fw-bold">🥇 Campeón (1.º)</span>
                                        @else
                                            <span class="badge bg-light text-dark fw-bold">🥈 Subcampeón (2.º)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('events.show', $q['event_id']) }}" class="text-info text-decoration-none fw-bold">
                                            {{ $q['event_name'] }}
                                        </a>
                                        <span class="text-white-50 small">({{ $q['event_city'] }})</span>
                                    </td>
                                    <td class="small">{{ $q['event_date'] ? \Carbon\Carbon::parse($q['event_date'])->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleOnlyQualified');
    const allView = document.getElementById('viewAllQualifiers');
    const qualView = document.getElementById('viewOnlyQualified');

    toggle.addEventListener('change', function () {
        if (this.checked) {
            allView.classList.add('d-none');
            qualView.classList.remove('d-none');
        } else {
            qualView.classList.add('d-none');
            allView.classList.remove('d-none');
        }
    });
});

function copyQualifiedList() {
    const lines = @json($qualifiedList->map(fn($q) => ($q['puesto'] === 'primero' ? '[1º]' : '[2º]') . ' ' . $q['user_name'] . ' #' . str_pad($q['user_id'], 4, '0', STR_PAD_LEFT) . ' (' . $q['event_name'] . ')')->values()->all());
    if (navigator.clipboard) {
        navigator.clipboard.writeText(lines.join('\n')).then(() => alert('Lista de clasificados copiada al portapapeles.'));
    }
}
</script>
@endsection
