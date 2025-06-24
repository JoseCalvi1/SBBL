@extends('layouts.app')

@section('title', 'Página del evento')

@section('content')

<article class="contenido-event bg-white p-5 shadow" style="color:white !important;background-color: transparent !important;">
        <div class="row">
            <div class="col-md-12">
            <h1 class="text-center mb-4 w-100">{{ $event->name }}
                @if ($event->status == "OPEN")
                    <span class="btn btn-success">ABIERTO</span>
                @elseif ($event->status == "INSCRIPCION")
                    <span class="btn btn-light">INSCRIPCIÓN CERRADA</span>
                @elseif ($event->status == "PENDING")
                    <span class="btn btn-warning">PENDIENTE CALIFICAR</span>
                @elseif ($event->status == "REVIEW")
                    <span class="btn btn-info">EN REVISIÓN</span>
                @elseif ($event->status == "INVALID")
                <span class="btn btn-dark">INVÁLIDO</span>
            @else
                    <span class="btn btn-danger">CERRADO</span>
                @endif
                @if (($event->status != "CLOSE" && $event->status != "INVALID") && (Auth::user()->is_admin || Auth::user()->is_jury))
                    <form method="POST" action="{{ route('events.actualizarPuntuaciones', ['event' => $event->id, 'mode' => $event->mode]) }}" style="display: contents; text-align: center;">
                        @method('PUT')
                        @csrf
                        <button type="submit" class="btn btn-secondary mb-2 mt-2 d-block" style="width: 100%" onclick="return confirm('¿Estás seguro de que deseas cerrar el evento? Esta acción no se puede deshacer.')">
                            Cerrar evento
                        </button>
                    </form>
                @endif
            </h1>
        </div>
            <div class="col-md-5">
                <div class="imagen-event">
                    @if ($event->image_mod)
                        <img src="data:image/png;base64,{{ $event->image_mod }}" class="w-100 h-25" style="border-radius: 5px;">
                    @else
                        <img src="/storage/{{ $event->imagen }}" class="w-100 h-25" style="border-radius: 5px;">
                    @endif
                </div>
                <div id="app" class="mt-2">
                    <chat-component :event-id="{{$event->id}}" />
                </div>
            </div>
            <div class="col-md-7">
                @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                @if($event->status == "OPEN" && Auth::user() && $event->date > $hoy)
                    @if (!$suscribe)
                        @if($isRegistered && ($event->beys == "ranking" || $event->beys == "rankingplus"))
                            <span class="alert alert-warning d-block mt-3 p-2 text-center font-weight-bold">
                                ⚠️ Ya te has apuntado a otro torneo esta semana. Recuerda que está prohibido participar en dos torneos la misma semana salvo excepción aprobada por los admins o ser un torneo especial.
                            </span>
                        @endif

                        <form method="POST" action="{{ route('events.assist', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate style="text-align: center;">
                            @csrf
                            <div class="form-group py-2">
                                <input
                                    type="submit"
                                    class="btn btn-primary text-uppercase font-weight-bold m-1 flex-right"
                                    value="Inscribirse"
                                    @if(in_array($event->beys, ['ranking', 'rankingplus']) && $rankingTournamentsLeft == 0)
                                        disabled
                                    @endif
                                >
                                @if(in_array($event->beys, ['ranking', 'rankingplus']))
                                    <span class="badge badge-warning ml-2" title="Torneos de ranking restantes este mes" style="font-size: 1rem;">
                                        🎟️ {{ $rankingTournamentsLeft }}
                                    </span>
                                @endif
                            </div>
                        </form>


                    @else
                        <form method="POST" action="{{ route('events.noassist', ['event' => $event->id]) }}" style="display: contents; text-align: center;">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger mr-2 text-uppercase font-weight-bold m-1 flex-right">No asistiré</button>
                        </form>
                    @endif
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="event-meta">
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Modalidad:</span>
                            {{ ($event->mode == 'beybladex') ? 'Beyblade X' : 'Beyblade Burst' }} ({{ $event->beys }})
                        </p>

                        @if(in_array($event->beys, ['ranking', 'rankingplus']))
                            <p class="mb-3">
                                <a href="{{ route('inicio.rules') }}" target="_blank"
                                class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-journal-text" viewBox="0 0 16 16">
                                        <path d="M5 10.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                        <path d="M3 1v14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V1H3zm8 13H5a.5.5 0 0 1 0-1h6a.5.5 0 0 1 0 1z"/>
                                    </svg>
                                    Reglas del torneo
                                </a>
                            </p>
                        @endif

                        <p class="mb-2">
                            <span class="fw-bold text-primary">Configuración:</span>
                            {{ $event->deck }} <span class="fw-bold">({{ $event->configuration }})</span>
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Región:</span>
                            {{ $event->region->name }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Localidad:</span>
                            {{ $event->city }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Lugar:</span>
                            {{ $event->location }}
                        </p>
                        <p class="mb-2">
                            <span class="fw-bold text-primary">Fecha y hora:</span>
                            <event-date fecha="{{ $event->date }}"></event-date> <span class="fw-bold">({{ $event->time }})</span>
                        </p>
                        <p class="mb-3">
                            <span class="fw-bold text-primary">Anotaciones:</span>
                            {!! $event->note !!}
                        </p>

                        @php
                            use Carbon\Carbon;
                            $now = Carbon::now();
                            $eventDate = Carbon::parse($event->date);
                            $diffInHours = $eventDate->diffInHours($now, false); // negativo si está en el futuro
                        @endphp

                        @if (
                            ($event->status == "OPEN") &&
                            (Auth::user()->is_admin || Auth::user()->is_referee) &&
                            ($diffInHours <= 24 && $diffInHours >= 0)
                        )
                            <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'inscripcion']) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-light mb-2 w-100">
                                    Cerrar inscripción
                                </button>
                            </form>
                        @endif


                        @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                            <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-3 d-block">
                                Editar
                            </a>
                        @endif

                        @if ($suscribe && $event->status != "INVALID")
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#formModal">
                                Introducir deck
                            </button>
                        @endif
                    </div>

                    </div>
                    <div class="col-md-6">
                        <h4 style="font-weight: bold">Listado de participantes ({{ $assists->count() }})</h4>
                        <!-- Botón para copiar nombres -->
                        <button id="copyButton" class="btn btn-outline-primary mt-3 mb-3 w-100">Copiar nombres</button>
                        @if($assists->count() < 4 && $event->status == "OPEN")
                            <div class="alert alert-danger">
                                Importante: Si el número de participantes es menor a 4 el torneo no se realizará.
                            </div>
                        @endif
                        @if (count($assists) > 0)
                            <form method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')

                                @foreach ($assists as $assist)
                                    <div class="row mb-2">
                                        <div class="col-md-9">
                                            <p class="mb-0">
                                                {{ $assist->name }}
                                                @if($event->beys == "ranking" || $event->beys == "rankingplus") ({{ DB::table('assist_user_event')
                                                ->join('events', 'assist_user_event.event_id', '=', 'events.id')
                                                ->where('assist_user_event.user_id', $assist->id)
                                                ->whereMonth('events.date', \Carbon\Carbon::parse($event->date)->month)
                                                ->whereYear('events.date', \Carbon\Carbon::parse($event->date)->year)
                                                ->whereIn('events.beys', ['ranking', 'rankingplus']) // Añadir filtro de beys
                                                ->where('assist_user_event.puesto', '<>', 'No presentado') // Filtro por puesto
                                                ->count();

                                                }}
                                                torneos) @endif

                                                @if(!empty($assist->pivot->puesto) && $assist->pivot->puesto !== 'No presentado')
                                                    - {{ $assist->pivot->puesto }}
                                                @endif

                                                @if (Auth::user()->is_admin)
                                                    <b>{{ $assist->email }}</b>
                                                @endif

                                                @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                                    <input type="hidden" name="participantes[{{ $assist->id }}][id]" value="{{ $assist->id }}">
                                                    @php
                                                        $count = $assists->count();
                                                        $options = ['primero', 'segundo']; // Default para <8

                                                        if ($count >= 8 && $count <= 15) {
                                                            $options = ['primero', 'segundo', 'tercero'];
                                                        } elseif ($count >= 16 && $count <= 23) {
                                                            $options = ['primero', 'segundo', 'tercero', 'cuarto'];
                                                        } elseif ($count >= 24 && $count <= 31) {
                                                            $options = ['primero', 'segundo', 'tercero', 'cuarto', 'quinto'];
                                                        } elseif ($count > 31) {
                                                            $options = ['primero', 'segundo', 'tercero', 'cuarto', 'quinto', 'septimo'];
                                                        }

                                                        // Etiquetas para mostrar (puedes modificar si quieres)
                                                        $labels = [
                                                            'primero' => 'Primer puesto',
                                                            'segundo' => 'Segundo puesto',
                                                            'tercero' => 'Tercer puesto',
                                                            'cuarto' => 'Cuarto puesto',
                                                            'quinto' => 'Quinto puesto',
                                                            'septimo' => 'Séptimo puesto',
                                                        ];
                                                    @endphp

                                                    <select class="form-control" name="participantes[{{ $assist->id }}][puesto]">
                                                        <option value="participante" {{ $assist->pivot->puesto == 'participante' ? 'selected' : '' }}>-- Selecciona un puesto --</option>

                                                        @foreach ($options as $opt)
                                                            <option value="{{ $opt }}" {{ $assist->pivot->puesto == $opt ? 'selected' : '' }}>
                                                                {{ $labels[$opt] }}
                                                            </option>
                                                        @endforeach
                                                        <option value="nopresentado" {{ $assist->pivot->puesto == 'nopresentado' ? 'selected' : '' }}>No presentado/a</option>
                                                    </select>

                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            @php
                                                $today = \Carbon\Carbon::now()->format('Y-m-d');  // Obtener la fecha actual en formato 'Y-m-d'
                                                $eventDate = \Carbon\Carbon::parse($event->date)->format('Y-m-d');  // Fecha del evento en formato 'Y-m-d'
                                            @endphp
                                            @if(($today === $eventDate && isset($resultsByParticipant[Auth::user()->id]) && count($resultsByParticipant[Auth::user()->id]) > 0) || $today > $eventDate || Auth::user()->is_admin)
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target="#results-{{ $assist->id }}" aria-expanded="false" aria-controls="results-{{ $assist->id }}" title="Ver resultados">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($today === $eventDate && isset($resultsByParticipant[Auth::user()->id]) && count($resultsByParticipant[Auth::user()->id]) > 0 || $today > $eventDate || Auth::user()->is_admin)
                                        <div class="collapse" id="results-{{ $assist->id }}">
                                            <div class="card card-body mb-3" style="background-color: rgb(2, 0, 97)">
                                                @if(isset($resultsByParticipant[$assist->id]) && count($resultsByParticipant[$assist->id]) > 0)
                                                    @foreach($resultsByParticipant[$assist->id] as $index => $result)
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <p> · {{ $result->blade }} {{ $result->assist_blade }} {{ $result->ratchet }} {{ $result->bit }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <p>No hay resultados registrados para este participante.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="mt-4">
                                    <h4 class="mb-4 text-info border-bottom border-info pb-2">Datos del torneo</h4>

                                    <div class="form-group">
                                        <label for="iframe" class="fw-semibold text-light">Link al video del torneo:</label>
                                        <input type="url" name="iframe" id="iframe" class="form-control mb-2 bg-dark text-light border-secondary"
                                            placeholder="https://www.youtube.com/embed/tu-video"
                                            value="{{ old('iframe', $event->iframe ?? '') }}"
                                            required>
                                    </div>

                                    <div class="form-group">
                                        <label for="challonge" class="fw-semibold text-light">Enlace al torneo en Challonge:</label>
                                        <input type="url" name="challonge" id="challonge" class="form-control mb-2 bg-dark text-light border-secondary"
                                            placeholder="https://challonge.com/es/"
                                            value="{{ old('challonge', $event->challonge ?? '') }}"
                                            required>
                                    </div>
                                </div>

                                @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                    <div class="form-group py-2">
                                        <input type="submit" class="btn btn-outline-success text-uppercase font-weight-bold flex-right" value="Enviar resultados"  style="width: 100%">
                                    </div>
                                @endif
                            </form>

                            @if (($event->status != "CLOSE" && $event->status != "INVALID") && Auth::user()->is_referee || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                @if ($event->iframe)
                                    <div class="m-2">
                                        <a href="{{ $event->iframe }}" target="_blank" class="btn btn-info text-uppercase font-weight-bold"
                                        style="width: 100%">Ver Video</a>
                                    </div>
                                    @if ($event->iframe)
                                    <div class="m-2">
                                        <a href="{{ $event->challonge }}" target="_blank" class="btn btn-info text-uppercase font-weight-bold"
                                        style="width: 100%">Ver Challonge</a>
                                    </div>
                                @endif
                                @endif
                            @endif

                        @else
                            <p>No hay participantes.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </article>



@endsection

@section('scripts')
<!-- Modal para introducir decks - Versión Bootstrap 5 -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="formModalLabel">Resultados del deck en el torneo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario dentro del popup -->
                <form method="POST" action="{{ route('tournament.results.store', ['eventId' => $event->id]) }}">
                    @csrf

                    @if(Auth::user()->is_referee)
                        @foreach($assists as $assist)
                            <div class="mb-4">
                                <h4>{{ $assist->name }} ({{ $assist->email }})</h4>
                                @foreach(range(1, 3) as $index)
                                    <div class="row g-3 mb-3">
                                        <!-- Blade -->
                                        <div class="col-md-2">
                                            <label for="blade_{{ $assist->id }}_{{ $index }}" class="form-label">Blade</label>
                                            <select class="form-select select2" id="blade_{{ $assist->id }}_{{ $index }}" name="blade[{{ $assist->id }}][{{ $index }}]" required>
                                                <option value="">-- Selecciona un blade --</option>
                                                @foreach($bladeOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->blade == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Assist blade -->
                                        <div class="col-md-2">
                                            <label for="assist_blade_{{ $assist->id }}_{{ $index }}" class="form-label">Assist blade (Solo CX)</label>
                                            <select class="form-select select2" id="assist_blade_{{ $assist->id }}_{{ $index }}" name="assist_blade[{{ $assist->id }}][{{ $index }}]">
                                                <option value=""></option>
                                                @foreach($assistBladeOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->assist_blade == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Ratchet -->
                                        <div class="col-md-2">
                                            <label for="ratchet_{{ $assist->id }}_{{ $index }}" class="form-label">Ratchet</label>
                                            <select class="form-select select2" id="ratchet_{{ $assist->id }}_{{ $index }}" name="ratchet[{{ $assist->id }}][{{ $index }}]" required>
                                                <option value="">-- Selecciona un ratchet --</option>
                                                @foreach($ratchetOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->ratchet == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Bit -->
                                        <div class="col-md-2">
                                            <label for="bit_{{ $assist->id }}_{{ $index }}" class="form-label">Bit</label>
                                            <select class="form-select select2" id="bit_{{ $assist->id }}_{{ $index }}" name="bit[{{ $assist->id }}][{{ $index }}]" required>
                                                <option value="">-- Selecciona un bit --</option>
                                                @foreach($bitOptions as $option)
                                                    <option value="{{ $option }}"
                                                        @if(isset($resultsByParticipant[$assist->id][$index-1]) && $resultsByParticipant[$assist->id][$index-1]->bit == $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Victorias -->
                                        <div class="col-md-1">
                                            <label for="victorias_{{ $assist->id }}_{{ $index }}" class="form-label">Victorias</label>
                                            <input type="number" class="form-control" id="victorias_{{ $assist->id }}_{{ $index }}" name="victorias[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->victorias : 0 }}">
                                        </div>

                                        <!-- Derrotas -->
                                        <div class="col-md-1">
                                            <label for="derrotas_{{ $assist->id }}_{{ $index }}" class="form-label">Derrotas</label>
                                            <input type="number" class="form-control" id="derrotas_{{ $assist->id }}_{{ $index }}" name="derrotas[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->derrotas : 0 }}">
                                        </div>

                                        <!-- Puntos Ganados -->
                                        <div class="col-md-1">
                                            <label for="puntos_ganados_{{ $assist->id }}_{{ $index }}" class="form-label">P. Ganados</label>
                                            <input type="number" class="form-control" id="puntos_ganados_{{ $assist->id }}_{{ $index }}" name="puntos_ganados[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->puntos_ganados : 0 }}">
                                        </div>

                                        <!-- Puntos Perdidos -->
                                        <div class="col-md-1">
                                            <label for="puntos_perdidos_{{ $assist->id }}_{{ $index }}" class="form-label">P. Perdidos</label>
                                            <input type="number" class="form-control" id="puntos_perdidos_{{ $assist->id }}_{{ $index }}" name="puntos_perdidos[{{ $assist->id }}][{{ $index }}]" value="{{ isset($resultsByParticipant[$assist->id][$index-1]) ? $resultsByParticipant[$assist->id][$index-1]->puntos_perdidos : 0 }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                    @else
                        <!-- Si no es referee, solo se muestra su propio deck -->
                        <div class="mb-4">
                            <h4>{{ Auth::user()->name }}'s Deck</h4>
                            @foreach(range(1, 3) as $index)
                                <div class="row g-3 mb-3">
                                    <!-- Blade -->
                                    <div class="col-md-2">
                                        <label for="blade_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Blade</label>
                                        <select class="form-select select2" id="blade_{{ Auth::user()->id }}_{{ $index }}" name="blade[{{ Auth::user()->id }}][]" required>
                                            <option value="">-- Selecciona un blade --</option>
                                            @foreach($bladeOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->blade == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Assist blade -->
                                    <div class="col-md-2">
                                        <label for="assist_blade_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Assist blade (Solo CX)</label>
                                        <select class="form-select select2" id="ratchet_{{ Auth::user()->id }}_{{ $index }}" name="assist_blade[{{ Auth::user()->id }}][]">
                                            <option value="">-- Selecciona un assist blade --</option>
                                            @foreach($assistBladeOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->assist_blade == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Ratchet -->
                                    <div class="col-md-2">
                                        <label for="ratchet_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Ratchet</label>
                                        <select class="form-select select2" id="ratchet_{{ Auth::user()->id }}_{{ $index }}" name="ratchet[{{ Auth::user()->id }}][]" required>
                                            <option value="">-- Selecciona un ratchet --</option>
                                            @foreach($ratchetOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->ratchet == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Bit -->
                                    <div class="col-md-2">
                                        <label for="bit_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Bit</label>
                                        <select class="form-select select2" id="bit_{{ Auth::user()->id }}_{{ $index }}" name="bit[{{ Auth::user()->id }}][]" required>
                                            <option value="">-- Selecciona un bit --</option>
                                            @foreach($bitOptions as $option)
                                                <option value="{{ $option }}"
                                                    @if(isset($results[$index-1]) && $results[$index-1]->bit == $option) selected @endif>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Victorias -->
                                    <div class="col-md-1">
                                        <label for="victorias_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Victorias</label>
                                        <input type="number" class="form-control" id="victorias_{{ Auth::user()->id }}_{{ $index }}" name="victorias[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->victorias : 0 }}">
                                    </div>

                                    <!-- Derrotas -->
                                    <div class="col-md-1">
                                        <label for="derrotas_{{ Auth::user()->id }}_{{ $index }}" class="form-label">Derrotas</label>
                                        <input type="number" class="form-control" id="derrotas_{{ Auth::user()->id }}_{{ $index }}" name="derrotas[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->derrotas : 0 }}">
                                    </div>

                                    <!-- Puntos Ganados -->
                                    <div class="col-md-1">
                                        <label for="puntos_ganados_{{ Auth::user()->id }}_{{ $index }}" class="form-label">P. Ganados</label>
                                        <input type="number" class="form-control" id="puntos_ganados_{{ Auth::user()->id }}_{{ $index }}" name="puntos_ganados[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->puntos_ganados : 0 }}">
                                    </div>

                                    <!-- Puntos Perdidos -->
                                    <div class="col-md-1">
                                        <label for="puntos_perdidos_{{ Auth::user()->id }}_{{ $index }}" class="form-label">P. Perdidos</label>
                                        <input type="number" class="form-control" id="puntos_perdidos_{{ Auth::user()->id }}_{{ $index }}" name="puntos_perdidos[{{ Auth::user()->id }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->puntos_perdidos : 0 }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <button type="submit" class="btn btn-primary">Guardar resultados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



@endsection

@section('styles')
    <!-- CDN de Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            z-index: 9999; /* Asegúrate de que el select2 se muestra sobre otros elementos */
        }
    </style>
@endsection

@section('scripts')
    <!-- CDN de jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CDN de Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
            // Pasamos el valor desde Blade a una variable JS
            const isReferee = @json(Auth::user()->is_jury);

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById('copyButton').addEventListener('click', function() {
                // Obtener los nombres de los participantes
                let participants = @json($assists->pluck('name')); // Usar `pluck` para obtener solo los nombres
                let names = participants.join('\n'); // Unir los nombres con salto de línea

                // Crear un elemento temporal de área de texto para copiar el texto al portapapeles
                let tempTextArea = document.createElement('textarea');
                tempTextArea.value = names;
                document.body.appendChild(tempTextArea);

                // Seleccionar y copiar el texto
                tempTextArea.select();
                document.execCommand('copy');

                // Eliminar el elemento temporal
                document.body.removeChild(tempTextArea);

                // Notificar que se copió
                alert('Nombres copiados al portapapeles');
            });



            // Solo ejecutamos si NO es referee
            if (!isReferee) {
                const submitResultados = document.querySelector("input[type='submit'][value='Enviar resultados']");
                if (submitResultados) {
                    submitResultados.addEventListener("click", function (event) {
                        const iframeInput = document.querySelector("input[name='iframe']");
                        if (!iframeInput || !iframeInput.value.trim()) {
                            event.preventDefault();
                            alert("⚠️ Debes introducir un enlace de video y de challonge antes de enviar los resultados.");
                        }
                    });
                }
            }
        });

        jQuery(document).ready(function() {
            // Inicializa Select2 cuando el documento esté listo
            jQuery('.select2').select2({
                dropdownParent: $("#formModal")
            });

            // Inicializa Select2 cuando el modal se muestra
            jQuery('#formModal').on('shown.bs.modal', function () {
                jQuery('.select2').select2(); // Re-inicializa Select2
            });
        });

    </script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection
