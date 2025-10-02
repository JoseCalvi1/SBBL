@extends('layouts.app')

@section('content')
@if (Auth::user()->is_jury || Auth::user()->is_admin || Auth::user()->is_referee)
<div class="py-4">
    <h2 class="text-center mb-2 text-white">Administra los eventos</h2>

    <div class="col-md-12 mx-auto p-3" style="background-color:transparent !important;">
        <a href="{{ route('events.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear evento
        </a>

        <!-- Formulario de filtros -->
        <form method="GET" action="{{ route('events.indexAdmin') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4 mt-2">
                    <label for="estado" class="text-white">Filtrar por Estado:</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="OPEN" {{ request('estado') == 'OPEN' ? 'selected' : '' }}>Abierto</option>
                        <option value="PENDING_REVIEW" {{ request('estado') == 'PENDING_REVIEW' ? 'selected' : '' }}>Pendiente / En revisión</option>
                        <option value="INVALID" {{ request('estado') == 'INVALID' ? 'selected' : '' }}>Inválido</option>
                        <option value="CLOSE" {{ request('estado') == 'CLOSE' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>

                <div class="col-md-4 mt-2">
                    <label for="beys" class="text-white">Filtrar por Tipo de Evento:</label>
                    <select name="beys" id="beys" class="form-control">
                        <option value="">Todos</option>
                        <option value="ranking" {{ request('beys') == 'ranking' ? 'selected' : '' }}>Ranking / Ranking Plus</option>
                    </select>
                </div>

                <div class="col-md-4 align-self-end mt-2">
                    <button type="submit" class="btn btn-primary w-100">Aplicar Filtros</button>
                </div>
            </div>
        </form>


<div class="list-group">
  @foreach ($events as $event)

        @php
            $reviewStatuses = $event->reviews->pluck('status');
            $allApproved = $reviewStatuses->count() === 3 && $reviewStatuses->every(fn($s) => $s === 'approved');
            $hasRejected = $reviewStatuses->contains(fn($s) => $s === 'rejected');
        @endphp

    <div class="list-group-item d-flex flex-column flex-md-row align-items-start align-items-md-center
        {{ $event->date < \Carbon\Carbon::today() ? 'bg-secondary text-white' : '' }}
        {{ $allApproved && !in_array($event->status, ['INVALID', 'CLOSE']) ? 'border border-success border-5' : '' }}
        {{ $hasRejected && !in_array($event->status, ['INVALID', 'CLOSE']) ? 'border border-danger border-5' : '' }}">


      <!-- INFORMACIÓN DEL EVENTO -->
      <div class="flex-grow-1" style="width: 100%">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <h5 class="mb-1 me-2">{{ $event->name }}</h5>
        </div>

        <p class="mb-1 small text-truncate">
          {{ $event->city }} • {{ $event->region->name }}
        </p>
        <p class="mb-1 small text-truncate">
          <event-date fecha="{{ $event->date }}"></event-date> • {{ $event->mode }}
        </p>

        <!-- ESTADO -->
        <div class="mb-1">
          @switch($event->status)
            @case('OPEN')
              <span class="badge bg-success">ABIERTO</span>
              @break
            @case('PENDING')
              <span class="badge bg-warning text-dark">PENDIENTE CALIFICAR</span>
              @break
            @case('REVIEW')
              <span class="badge bg-info text-dark">EN REVISIÓN</span>
              @break
            @case('INVALID')
              <span class="badge bg-dark">INVÁLIDO</span>
              @break
            @case('INSCRIPCION')
              <span class="badge bg-light">INSCRIPCION CERRADA</span>
              @break
            @default
              <span class="badge bg-danger">CERRADO</span>
          @endswitch
        </div>

        <!-- ENLACES OPCIONALES -->
        <div class="mb-1 d-flex gap-2 flex-wrap">
          @if ($event->iframe)
            @foreach (explode(';', $event->iframe) as $index => $link)
                @php
                    $link = trim($link);
                @endphp
                @if (!empty($link))
                    <a href="{{ $link }}" target="_blank" class="btn btn-sm btn-info me-1">
                        Ver Video {{ $loop->count > 1 ? $index + 1 : '' }}
                    </a>
                @endif
            @endforeach
        @endif

          @if ($event->challonge)
            <a href="{{ $event->challonge }}" target="_blank" class="btn btn-sm btn-info">Ver Challonge</a>
          @endif
        </div>

@php
    $maxReviews = 3;
    $userId = auth()->id();
    $reviewsCount = $event->reviews->count();
    $userReview = $event->reviews->firstWhere('referee_id', $userId);
    $canReview = $reviewsCount < $maxReviews && !$userReview;
@endphp

@if(!in_array($event->status, ['INVALID', 'CLOSE', 'OPEN']) && in_array($event->beys, ['ranking', 'rankingplus']))
<div class="d-flex flex-wrap gap-2 mb-2" id="review-buttons-{{ $event->id }}">

    {{-- Mostrar contador de revisiones --}}
    <span class="badge bg-info text-black">Revisiones: {{ $reviewsCount }}/{{ $maxReviews }}</span>

    @if($canReview)
        {{-- Usuario aún no ha empezado revisión y hay sitio --}}
        <form action="{{ route('event.review.start', $event) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-info me-1 review-button">
                Revisar
            </button>
        </form>

    @elseif($userReview && $userReview->status === 'pending')
        {{-- Usuario está revisando en estado 'pending' --}}
        <button type="button" class="btn btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#reviewModal{{ $event->id }}"
                id="revisar-evento-btn-{{ $event->id }}">
            Revisar Evento
        </button>
        {{-- Botón para eliminar la revisión --}}
        <form action="{{ route('event.destroyReview', ['event' => $event->id, 'user' => Auth::id()]) }}"
            method="POST" class="d-inline"
            onsubmit="return confirm('¿Seguro que deseas eliminar tu revisión?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                Dejar Revisión
            </button>
        </form>
    @endif

    @auth
    @if((auth()->user()->is_jury || auth()->user()->is_admin) && in_array($event->beys, ['ranking', 'rankingplus']))
        {{-- Botón invalidar visible para árbitros --}}
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn btn-success btn-sm"
                data-bs-toggle="modal" data-bs-target="#validarModal{{ $event->id }}">
                Validar
            </button>

            <!-- Botón Invalidar en la tabla o card del evento -->
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#invalidarModal{{ $event->id }}">
                Invalidar
            </button>

        </div>
    @endif
@endauth

</div>
@endif




        @if (in_array($event->beys, ['ranking', 'rankingplus']) && (in_array($event->status, ['INVALID', 'CLOSE']) || auth()->user()->is_admin))
<!-- BOTÓN PARA MOSTRAR VALIDADORES -->
        <button class="btn btn-danger btn-sm mb-1 mt-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#reviewCollapse{{ $event->id }}"
                aria-expanded="false" aria-controls="reviewCollapse{{ $event->id }}">
          Mostrar validadores y comentarios
        </button>

        <!-- SECCIÓN DESPLEGABLE DE VALIDADORES -->
        <div class="collapse mt-2" id="reviewCollapse{{ $event->id }}">
        <div class="card card-body bg-light text-dark p-2" style="font-size: 0.85rem;">
            <strong>Validadores y comentarios:</strong>
            @if($event->reviews->isEmpty())
            <em>No hay revisiones aún.</em>
            @else
            <ul class="mb-0 ps-3" style="max-height: 150px; overflow-y: auto;">
                @foreach($event->reviews as $review)
                <li class="mb-1">
                    <strong>
                    @if(auth()->user()->is_admin || Auth::user()->name == $review->referee->name)
                        {{ $review->referee->name ?? 'Árbitro desconocido' }}
                    @else
                        Árbitro {{ $loop->iteration }}
                    @endif
                    </strong>:
                    <span class="badge
                    @if($review->status == 'approved') bg-success
                    @elseif($review->status == 'rejected') bg-danger
                    @else bg-secondary
                    @endif">
                    {{ strtoupper($review->status) }}
                    </span><br>
                    <em>{{ $review->comment }}</em>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
        </div>

@endif

      </div>
      <!-- ACCIONES: Ver, Editar, Eliminar -->
      <div class="d-flex flex-column flex-shrink-0 me-md-3 mt-3 mb-3 mb-md-0" style="min-width: 140px;">
        <a href="{{ route('events.show', $event->id) }}" class="btn btn-success btn-sm mb-1">Ver</a>
        @if (auth()->user()->is_admin || auth()->user()->is_jury)
            <a href="{{ route('events.edit', $event->id) }}" class="btn btn-dark btn-sm mb-1">Editar</a>
            <event-delete event-id="{{ $event->id }}"></event-delete>
        @endif
      </div>

    </div>
  @endforeach
</div>


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
            <!-- Modal Validar -->
            <div class="modal fade" id="validarModal{{ $event->id }}" tabindex="-1" aria-labelledby="validarModalLabel{{ $event->id }}" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="validarModalLabel{{ $event->id }}">Confirmar validación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                  </div>

                  <div class="modal-body">
                    <p>¿Cómo deseas validar el evento <strong>{{ $event->name }}</strong>?</p>

                    <!-- Formulario con comentario (oculto por defecto, se muestra al pulsar botón correspondiente) -->
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
                    <!-- Validar sin comentario -->
                    <form method="POST" action="{{ route('events.actualizarPuntuaciones', ['event' => $event->id, 'mode' => $event->mode]) }}" class="w-100">
                      @method('PUT')
                      @csrf
                      <button type="submit" class="btn btn-success w-100">Validar sin comentario</button>
                    </form>

                    <!-- Botón que activa el textarea -->
                    <button type="button" class="btn btn-primary w-100"
                        onclick="document.getElementById('form-validar-comentario-{{ $event->id }}').style.display='block'; this.style.display='none';">
                        Validar con comentario
                    </button>

                    <!-- Cancelar -->
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                  </div>
                </div>
              </div>
            </div>
        @endif

                <!-- Modal Invalidar -->
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
            <!-- Modal de revisión para cada evento -->
            <div class="modal fade" id="reviewModal{{ $event->id }}" tabindex="-1" aria-labelledby="reviewModalLabel{{ $event->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('event.review', $event->id) }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="reviewModalLabel{{ $event->id }}">Revisión del Árbitro - {{ $event->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="status{{ $event->id }}" class="form-label">Resultado</label>
                                    <select name="status" id="status{{ $event->id }}" class="form-select" required>
                                        <option value="">Selecciona una opción</option>
                                        <option value="approved">Aprobar</option>
                                        <option value="rejected">Rechazar</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="comment{{ $event->id }}" class="form-label">Comentario</label>
                                    <textarea name="comment" id="comment{{ $event->id }}" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Enviar revisión</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        document.querySelectorAll('.review-button').forEach(button => {
        button.addEventListener('click', async function () {
            const eventId = this.dataset.eventId;
            const status = this.dataset.status;
            const comment = prompt('Comentario sobre la revisión:');

            if (comment === null) return; // Usuario canceló

            try {
            const response = await fetch(`/events/${eventId}/review`, {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status, comment })
            });

            if (response.ok) {
                alert('Revisión enviada correctamente.');
                location.reload(); // O actualiza solo esa parte
            } else {
                alert('Error al enviar la revisión.');
            }
            } catch (error) {
            console.error(error);
            alert('Error de red o del servidor.');
            }
        });
        });

    </script>
@endsection

