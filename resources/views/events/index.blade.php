@extends('layouts.app')

@section('styles')
<style>
    .admin-filter-box {
        background: #111827;
        border: 2px solid #374151;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.5);
    }
    .event-admin-card {
        background: #1f2937;
        border: 1px solid #374151;
        border-left: 6px solid #6b7280;
        border-radius: 10px;
        margin-bottom: 12px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        color: #f3f4f6;
    }
    .event-admin-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.4);
    }
    /* Bordes laterales según estado/aprobación */
    .event-admin-card.card-approved { border-left: 6px solid #10b981 !important; background: #182725; }
    .event-admin-card.card-rejected { border-left: 6px solid #ef4444 !important; background: #291a1e; }
    .event-admin-card.card-past { opacity: 0.85; background: #111827; }

    /* Colores distintivos por Tipo de Evento (beys) */
    .badge-type-ranking { background-color: #dc2626; color: #fff; }
    .badge-type-simulacro { background-color: #9333ea; color: #fff; }
    .badge-type-quedada { background-color: #0284c7; color: #fff; }
    .badge-type-grancopa { background-color: #d97706; color: #000; font-weight: 800; }
    .badge-type-crew { background-color: #059669; color: #fff; }
    .badge-type-other { background-color: #4b5563; color: #fff; }
</style>
@endsection

@section('content')
@if (Auth::user()->hasAnyRole(['juez', 'admin', 'arbitro', 'revisor']))
<div class="container py-4">
    
    {{-- CABECERA --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="text-white fw-bold mb-0"><i class="fas fa-tasks text-info me-2"></i>Panel de Administración de Eventos</h2>
            <p class="text-muted small mb-0">Filtra, revisa y valida torneos oficiales, simulacros y quedadas.</p>
        </div>
        <a href="{{ route('events.create') }}" class="btn btn-primary fw-bold text-uppercase shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Crear evento
        </a>
    </div>

    {{-- PANEL DE FILTROS INTUITIVO --}}
    <div class="admin-filter-box mb-4">
        <form method="GET" action="{{ route('events.indexAdmin') }}">
            <div class="row g-3">
                {{-- Buscador por texto --}}
                <div class="col-12 col-md-3">
                    <label for="buscar" class="form-label text-light small fw-bold mb-1">
                        <i class="fas fa-search text-info me-1"></i> Buscar (Nombre / Ciudad):
                    </label>
                    <input type="text" name="buscar" id="buscar" value="{{ request('buscar') }}" class="form-control bg-dark text-white border-secondary" placeholder="Ej: Madrid, Copa, Torneo...">
                </div>

                {{-- Filtro por Tipo de Evento --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="beys" class="form-label text-light small fw-bold mb-1">
                        <i class="fas fa-trophy text-warning me-1"></i> Tipo de Evento:
                    </label>
                    <select name="beys" id="beys" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <option value="ranking_all" {{ request('beys') == 'ranking_all' ? 'selected' : '' }}>🔥 Todos los Ranking (Normal + Plus)</option>
                        <option value="ranking" {{ request('beys') == 'ranking' ? 'selected' : '' }}>🎯 Solo Ranking</option>
                        <option value="rankingplus" {{ request('beys') == 'rankingplus' ? 'selected' : '' }}>⚡ Solo Ranking Plus</option>
                        <option value="simulacro" {{ request('beys') == 'simulacro' ? 'selected' : '' }}>🧪 Simulacro</option>
                        <option value="quedada" {{ request('beys') == 'quedada' ? 'selected' : '' }}>🤝 Quedada</option>
                        <option value="grancopa" {{ request('beys') == 'grancopa' ? 'selected' : '' }}>👑 Gran Copa</option>
                        <option value="crew_battle" {{ request('beys') == 'crew_battle' ? 'selected' : '' }}>🛡️ Torneo por Equipos</option>
                        <option value="hasbro" {{ request('beys') == 'hasbro' ? 'selected' : '' }}>📦 Hasbro</option>
                        <option value="copapaypal" {{ request('beys') == 'copapaypal' ? 'selected' : '' }}>💎 Copa Conqueror</option>
                    </select>
                </div>

                {{-- Filtro por Estado --}}
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="estado" class="form-label text-light small fw-bold mb-1">
                        <i class="fas fa-traffic-light text-success me-1"></i> Estado:
                    </label>
                    <select name="estado" id="estado" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="OPEN" {{ request('estado') == 'OPEN' ? 'selected' : '' }}>🟢 Abierto</option>
                        <option value="LIVE" {{ request('estado') == 'LIVE' ? 'selected' : '' }}>🔴 En Curso (LIVE)</option>
                        <option value="PENDING_REVIEW" {{ request('estado') == 'PENDING_REVIEW' ? 'selected' : '' }}>🟡 Pendiente / En revisión</option>
                        <option value="CLOSE" {{ request('estado') == 'CLOSE' ? 'selected' : '' }}>🔒 Cerrado</option>
                        <option value="INVALID" {{ request('estado') == 'INVALID' ? 'selected' : '' }}>❌ Inválido</option>
                    </select>
                </div>

                {{-- Filtro por Región --}}
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="region" class="form-label text-light small fw-bold mb-1">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i> Región:
                    </label>
                    <select name="region" id="region" class="form-select bg-dark text-white border-secondary" onchange="this.form.submit()">
                        <option value="all">Todas</option>
                        @if(isset($regions))
                            @foreach($regions as $reg)
                                <option value="{{ $reg->id }}" {{ request('region') == $reg->id ? 'selected' : '' }}>
                                    {{ $reg->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Botones de Acción --}}
                <div class="col-12 col-sm-6 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-info fw-bold w-100 text-dark">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('events.indexAdmin') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- LISTADO DE EVENTOS --}}
    <div class="list-group">
        @forelse ($events as $event)
            @php
                $reviewStatuses = $event->reviews->pluck('status');
                $allApproved = $reviewStatuses->count() === 3 && $reviewStatuses->every(fn($s) => $s === 'approved');
                $hasRejected = $reviewStatuses->contains(fn($s) => $s === 'rejected');
                $isPast = $event->date < \Carbon\Carbon::today();

                // Diccionario visual para distinguir rápidamente el tipo de evento
                $beysType = strtolower($event->beys ?? '');
                $typeLabels = [
                    'ranking'     => ['label' => 'RANKING', 'class' => 'badge-type-ranking', 'icon' => 'fa-fire'],
                    'rankingplus' => ['label' => 'RANKING PLUS', 'class' => 'badge-type-ranking', 'icon' => 'fa-bolt'],
                    'simulacro'   => ['label' => 'SIMULACRO', 'class' => 'badge-type-simulacro', 'icon' => 'fa-vial'],
                    'quedada'     => ['label' => 'QUEDADA', 'class' => 'badge-type-quedada', 'icon' => 'fa-users'],
                    'grancopa'    => ['label' => 'GRAN COPA', 'class' => 'badge-type-grancopa', 'icon' => 'fa-crown'],
                    'crew_battle' => ['label' => 'EQUIPOS', 'class' => 'badge-type-crew', 'icon' => 'fa-shield-alt'],
                    'hasbro'      => ['label' => 'HASBRO', 'class' => 'badge-type-other', 'icon' => 'fa-box'],
                    'copapaypal'  => ['label' => 'CONQUEROR', 'class' => 'badge-type-grancopa', 'icon' => 'fa-gem'],
                ];
                $currentType = $typeLabels[$beysType] ?? ['label' => strtoupper($event->beys ?: 'EVENTO'), 'class' => 'badge-type-other', 'icon' => 'fa-calendar'];
            @endphp

            <div class="event-admin-card p-3 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between
                {{ $isPast ? 'card-past' : '' }}
                {{ $allApproved && !in_array($event->status, ['INVALID', 'CLOSE']) ? 'card-approved' : '' }}
                {{ $hasRejected && !in_array($event->status, ['INVALID', 'CLOSE']) ? 'card-rejected' : '' }}">

                <div class="flex-grow-1 w-100 pe-md-3">
                    {{-- Fila 1: Badges de Tipo, Estado y Modo --}}
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        {{-- Badge de Tipo de Evento (NUEVO Y DESTACADO) --}}
                        <span class="badge {{ $currentType['class'] }} px-2 py-1">
                            <i class="fas {{ $currentType['icon'] }} me-1"></i> {{ $currentType['label'] }}
                        </span>

                        {{-- Badge de Estado --}}
                        @switch($event->status)
                            @case('OPEN')
                                <span class="badge bg-success">ABIERTO</span>
                                @break
                            @case('LIVE')
                                <span class="badge bg-danger animate-pulse">EN CURSO (LIVE)</span>
                                @break
                            @case('PENDING')
                                <span class="badge bg-warning text-dark">PENDIENTE CALIFICAR</span>
                                @break
                            @case('REVIEW')
                                <span class="badge bg-info text-dark">EN REVISIÓN</span>
                                @break
                            @case('INVALID')
                                <span class="badge bg-dark border border-secondary">INVÁLIDO</span>
                                @break
                            @case('INSCRIPCION')
                                <span class="badge bg-secondary">INSCRIPCIÓN CERRADA</span>
                                @break
                            @default
                                <span class="badge bg-secondary">CERRADO</span>
                        @endswitch

                        <span class="badge bg-dark border border-secondary text-light">
                            {{ strtoupper($event->mode) }}
                        </span>
                    </div>

                    {{-- Fila 2: Título del Evento --}}
                    <h5 class="mb-1 fw-bold text-white">{{ $event->name }}</h5>

                    {{-- Fila 3: Ubicación, Región y Fecha --}}
                    <div class="d-flex flex-wrap gap-3 text-light small mb-2 opacity-75">
                        <span><i class="fas fa-map-marker-alt text-danger me-1"></i> <strong>{{ $event->region->name ?? 'Sin región' }}</strong> ({{ $event->city }})</span>
                        <span><i class="fas fa-calendar-day text-info me-1"></i> <event-date fecha="{{ $event->date }}"></event-date> {{ $event->time ? '• ' . substr($event->time, 0, 5) : '' }}</span>
                    </div>

                    {{-- Fila 4: Enlaces a Vídeos y Challonge --}}
                    <div class="mb-2 d-flex gap-2 flex-wrap">
                        @if ($event->iframe)
                            @foreach (explode(';', $event->iframe) as $index => $link)
                                @php $link = trim($link); @endphp
                                @if (!empty($link))
                                    <a href="{{ $link }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fab fa-youtube me-1"></i> Ver Video {{ $loop->count > 1 ? $index + 1 : '' }}
                                    </a>
                                @endif
                            @endforeach
                        @endif

                        @if ($event->challonge)
                            <a href="{{ $event->challonge }}" target="_blank" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-sitemap me-1"></i> Ver Challonge
                            </a>
                        @endif
                    </div>

                    {{-- Lógica de Permisos de Revisión (Intacta) --}}
                    @php
                        $user = auth()->user();
                        $userId = $user->id;

                        $isReferee = $user->hasRole('arbitro');
                        $isJury = $user->hasRole('juez');
                        $isReviewer = $user->is_reviewer;

                        $hasJuryReview = $event->reviews->contains(function ($review) {
                            return optional($review->referee)->hasRole('juez');
                        });

                        $hasReviewerReview = $event->reviews->contains(function ($review) {
                            return optional($review->referee)->hasRole('revisor');
                        });

                        $maxReviews = $hasJuryReview ? 1 : 3;
                        $reviewsCount = $event->reviews->count();
                        $userReview = $event->reviews->firstWhere('referee_id', $userId);

                        $canReview = false;

                        if ($hasJuryReview) {
                            $canReview = $isJury && !$userReview && $reviewsCount === 0;
                        } else {
                            if ($isJury) {
                                $canReview = !$userReview;
                            } elseif ($isReferee) {
                                $canReview = !$userReview && $reviewsCount < 3;
                            } elseif ($isReviewer) {
                                $canReview = !$userReview && !$hasReviewerReview && $reviewsCount < 3;
                            }
                        }
                    @endphp

                    @if (!in_array($event->status, ['INVALID', 'CLOSE', 'OPEN']) && in_array($event->beys, ['ranking', 'rankingplus']))
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2 mt-2 pt-2 border-top border-secondary" id="review-buttons-{{ $event->id }}">
                            <span class="badge bg-info text-black">
                                <i class="fas fa-user-check me-1"></i> Revisiones: {{ $reviewsCount }}/{{ $maxReviews }}
                            </span>

                            @if ($canReview)
                                <form action="{{ route('event.review.start', $event) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info fw-bold">
                                        Iniciar Revisión
                                    </button>
                                </form>
                            @endif

                            @if ($userReview && $userReview->status === 'pending')
                                <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#reviewModal{{ $event->id }}">
                                    Enviar Veredicto
                                </button>

                                <form action="{{ route('event.destroyReview', ['event' => $event->id, 'user' => $userId]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar tu revisión?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        Dejar Revisión
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @if ((Auth::user()->hasAnyRole(['juez', 'admin'])) && !in_array($event->status, ['INVALID', 'CLOSE']) && in_array($event->beys, ['ranking', 'rankingplus']))
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#validarModal{{ $event->id }}">
                                ✅ Validar evento
                            </button>
                            <button type="button" class="btn btn-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#invalidarModal{{ $event->id }}">
                                ❌ Invalidar evento
                            </button>
                        </div>
                    @endif

                    @if (in_array($event->beys, ['ranking', 'rankingplus']) && (in_array($event->status, ['INVALID', 'CLOSE']) || Auth::user()->hasRole('admin')))
                        <button class="btn btn-outline-light btn-sm mb-1 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#reviewCollapse{{ $event->id }}" aria-expanded="false" aria-controls="reviewCollapse{{ $event->id }}">
                            <i class="fas fa-comments me-1"></i> Mostrar validadores y comentarios ({{ $event->reviews->count() }})
                        </button>

                        <div class="collapse mt-2" id="reviewCollapse{{ $event->id }}">
                            <div class="card card-body bg-dark border-secondary text-light p-2" style="font-size: 0.85rem;">
                                <strong>Validadores y comentarios:</strong>
                                @if($event->reviews->isEmpty())
                                    <em class="text-muted">No hay revisiones aún.</em>
                                @else
                                    <ul class="mb-0 ps-3 mt-1" style="max-height: 150px; overflow-y: auto;">
                                        @foreach($event->reviews as $review)
                                            <li class="mb-1">
                                                <strong>
                                                    @if(Auth::user()->hasRole('admin') || Auth::user()->name == optional($review->referee)->name)
                                                        {{ optional($review->referee)->name ?? 'Árbitro desconocido' }}
                                                    @else
                                                        Árbitro {{ $loop->iteration }}
                                                    @endif
                                                </strong>:
                                                <span class="badge @if($review->status == 'approved') bg-success @elseif($review->status == 'rejected') bg-danger @else bg-secondary @endif">
                                                    {{ strtoupper($review->status) }}
                                                </span><br>
                                                <em class="text-light opacity-75">{{ $review->comment }}</em>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Columna Derecha: Botones de Acción Rápida --}}
                <div class="d-flex flex-row flex-md-column gap-2 flex-shrink-0 mt-3 mt-md-0 w-100 w-md-auto" style="max-width: 150px;">
                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-info btn-sm fw-bold w-100 text-dark">
                        <i class="fas fa-eye me-1"></i> Ver
                    </a>
                    @if(Auth::user()->hasAnyRole(['admin', 'juez']))
                        <a href="{{ route('events.edit', $event->id) }}" class="btn btn-outline-light btn-sm w-100">
                            <i class="fas fa-edit me-1"></i> Editar
                        </a>
                        <div class="w-100">
                            <event-delete event-id="{{ $event->id }}"></event-delete>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div class="text-center py-5 bg-dark border border-secondary rounded-3 text-light">
                <i class="fas fa-calendar-times fa-3x text-secondary mb-3"></i>
                <h5>No se encontraron eventos con esos filtros</h5>
                <p class="text-muted small mb-0">Prueba a limpiar los filtros o seleccionar otra región/categoría.</p>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $events->links('pagination::bootstrap-4') }}
    </div>

</div>
@else
<script>
    window.location = "/";
</script>
@endif
@endsection

@section('scripts')
    @foreach ($events as $event)
        @if (!in_array($event->status, ['INVALID', 'CLOSE', 'OPEN']))
            <div class="modal fade" id="validarModal{{ $event->id }}" tabindex="-1" aria-labelledby="validarModalLabel{{ $event->id }}" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="validarModalLabel{{ $event->id }}">Confirmar validación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>

                  <div class="modal-body">
                    <p>¿Cómo deseas validar el evento <strong>{{ $event->name }}</strong>?</p>

                    <form method="POST" action="{{ route('events.actualizarPuntuaciones', ['event' => $event->id, 'mode' => $event->mode]) }}"
                          id="form-validar-comentario-{{ $event->id }}" style="display: none;">
                        @method('PUT')
                        @csrf
                        <div class="mb-3">
                            <label for="comment{{ $event->id }}" class="form-label">Comentario</label>
                            <textarea name="comment" id="comment{{ $event->id }}" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Validar con comentario</button>
                    </form>
                  </div>

                  <div class="modal-footer d-flex flex-column gap-2">
                    <form method="POST" action="{{ route('events.actualizarPuntuaciones', ['event' => $event->id, 'mode' => $event->mode]) }}" class="w-100">
                      @method('PUT')
                      @csrf
                      <button type="submit" class="btn btn-success w-100">Validar sin comentario</button>
                    </form>

                    <button type="button" class="btn btn-primary w-100"
                        onclick="document.getElementById('form-validar-comentario-{{ $event->id }}').style.display='block'; this.style.display='none';">
                        Validar con comentario
                    </button>

                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                  </div>
                </div>
              </div>
            </div>
        @endif

        <div class="modal fade" id="invalidarModal{{ $event->id }}" tabindex="-1" aria-labelledby="invalidarModalLabel{{ $event->id }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="invalidarModalLabel{{ $event->id }}">Invalidar evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>

              <div class="modal-body">
                <p>¿Deseas invalidar el evento <strong>{{ $event->name }}</strong>?</p>

                <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'invalidar']) }}" id="form-invalidar-comentario-{{ $event->id }}" style="display: none;">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="commentInvalidar{{ $event->id }}" class="form-label">Comentario</label>
                        <textarea name="comment" id="commentInvalidar{{ $event->id }}" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Invalidar con comentario</button>
                </form>
              </div>

              <div class="modal-footer d-flex flex-column gap-2">
                <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'invalidar']) }}" class="w-100">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-warning w-100">Invalidar sin comentario</button>
                </form>

                <button type="button" class="btn btn-secondary w-100"
                    onclick="document.getElementById('form-invalidar-comentario-{{ $event->id }}').style.display='block'; this.style.display='none';">
                    Invalidar con comentario
                </button>

                <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </div>
          </div>
        </div>
    @endforeach

    @foreach ($events as $event)
        @if (!in_array($event->status, ['INVALID', 'CLOSE']))
            <div class="modal fade" id="reviewModal{{ $event->id }}" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
                <div class="modal-dialog">
                    <form class="ajax-review-form" action="{{ route('event.review', $event->id) }}" method="POST" data-event-id="{{ $event->id }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Revisión - {{ $event->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div id="msg-container-{{ $event->id }}" class="alert d-none"></div>

                                <div class="mb-3">
                                    <label class="form-label">Resultado</label>
                                    <select name="status" class="form-select" required>
                                        <option value="">Selecciona una opción</option>
                                        <option value="approved">Aprobar</option>
                                        <option value="rejected">Rechazar</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Comentario</label>
                                    <textarea name="comment" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success btn-submit">Enviar revisión</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('.ajax-review-form');

        forms.forEach(form => {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const eventId = this.dataset.eventId;
                const msgContainer = document.getElementById(`msg-container-${eventId}`);
                const btnSubmit = this.querySelector('.btn-submit');
                const originalBtnText = btnSubmit.innerHTML;

                msgContainer.classList.add('d-none');
                msgContainer.className = 'alert d-none';
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';

                try {
                    const formData = new FormData(this);

                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    if (response.status === 413) {
                        throw new Error('El comentario es demasiado largo o contiene demasiados datos (Error 413). Por favor reduce el contenido.');
                    }

                    if (response.status === 500) {
                        const errorData = await response.json().catch(() => ({}));
                        throw new Error(errorData.message || 'Error interno del servidor (500). Contacta al administrador.');
                    }

                    if (response.status === 422) {
                        const data = await response.json();
                        throw new Error(data.message || 'Datos inválidos. Revisa los campos.');
                    }

                    if (!response.ok) {
                        throw new Error(`Error inesperado: ${response.status}`);
                    }

                    const data = await response.json();

                    msgContainer.textContent = data.message || 'Revisión enviada con éxito.';
                    msgContainer.classList.remove('d-none', 'alert-danger');
                    msgContainer.classList.add('alert', 'alert-success');

                    setTimeout(() => {
                        location.reload();
                    }, 1500);

                } catch (error) {
                    console.error(error);
                    msgContainer.textContent = error.message;
                    msgContainer.classList.remove('d-none', 'alert-success');
                    msgContainer.classList.add('alert', 'alert-danger');

                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalBtnText;
                }
            });
        });

        document.querySelectorAll('.review-button').forEach(button => {
            button.addEventListener('click', async function () {
                const eventId = this.dataset.eventId;
                const status = this.dataset.status;
                const comment = prompt('Comentario sobre la revisión:');

                if (comment === null) return;

                try {
                    const response = await fetch(`/events/${eventId}/review`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status, comment })
                    });

                    if (response.status === 413) throw new Error('Texto demasiado largo (413).');
                    if (!response.ok) throw new Error('Error al procesar la solicitud.');

                    alert('Revisión enviada correctamente.');
                    location.reload();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    });
</script>
@endsection