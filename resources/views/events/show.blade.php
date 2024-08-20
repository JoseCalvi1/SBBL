@extends('layouts.app')

@section('content')

    <article class="contenido-event bg-white p-5 shadow" style="color:white !important;background-color: transparent !important;">
        <div class="row">
            <div class="col-md-5">
                <div class="imagen-event">
                    @if ($event->image_mod)
                        <img src="data:image/png;base64,{{ $event->image_mod }}" class="w-100 h-25" style="border-radius: 5px;">
                    @else
                        <img src="/storage/{{ $event->imagen }}" class="w-100 h-25" style="border-radius: 5px;">
                    @endif
                </div>
            </div>
            <div class="col-md-7">
                <h1 class="text-center mb-4">{{ $event->name }}
                    @if ($event->status == "OPEN")
                        <span class="btn btn-success">ABIERTO</span>
                    @elseif ($event->status == "PENDING")
                        <span class="btn btn-warning">PENDIENTE CALIFICAR</span>
                    @else
                        <span class="btn btn-danger">CERRADO</span>
                    @endif
                    @if ($event->status != "CLOSE" && Auth::user()->is_admin)
                        <form method="POST" action="{{ route('events.actualizarPuntuaciones', ['event' => $event->id, 'mode' => $event->mode]) }}" style="display: contents; text-align: center;">
                            @method('PUT')
                            @csrf
                            <button type="submit" class="btn btn-secondary mb-2 mt-2 d-block" style="width: 100%">Cerrar evento</button>
                        </form>
                    @endif
                </h1>
                @if($event->status == "OPEN" && Auth::user() && $event->date > $hoy)
                    @if (!$suscribe)
                        <form method="POST" action="{{ route('events.assist', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate style="text-align: center;">
                            @csrf
                            <div class="form-group py-2">
                                <input type="submit" class="btn btn-primary text-uppercase font-weight-bold m-1 flex-right" value="Inscribirse">
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
                        <div class="event-meta mt-2">
                            <p>
                                <span class="font-weight-bold text-primary">Modalidad:</span>
                                {{ ($event->mode == 'beybladex') ? 'Beyblade X' : 'Beyblade Burst' }}
                            </p>
                            <p>
                                <span class="font-weight-bold text-primary">Configuración:</span>
                                {{ $event->deck }} <span class="font-weight-bold">({{ $event->configuration }})</span>
                            </p>
                            <p>
                                <span class="font-weight-bold text-primary">Región:</span>
                                {{ $event->region->name }}
                            </p>

                            <p>
                                <span class="font-weight-bold text-primary">Lugar:</span>
                                {{ $event->location }}
                            </p>

                            <p>
                                <span class="font-weight-bold text-primary">Anotaciones:</span>
                                {{ $event->note }}
                            </p>

                            <p>
                                <span class="font-weight-bold text-primary">Fecha y hora:</span>
                                <event-date fecha="{{ $event->date }}"></event-date> <span class="font-weight-bold">({{ $event->time }})</span>
                            </p>
                            @if ($event->status != "CLOSE" && Auth::user()->is_admin || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                            @endif
                            @if ($suscribe)
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#formModal">
                                    Introducir deck
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 style="font-weight: bold">Listado de participantes ({{ $assists->count() }})</h4>
                        @if (count($assists) > 0)
                            <form method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method('PUT')

                                @foreach ($assists as $assist)
                                    <div class="row mb-2">
                                        <div class="col-md-9">
                                            <p class="mb-0">
                                                {{ $assist->name }}
                                                @if (Auth::user()->is_admin)
                                                    <b>{{ $assist->email }}</b>
                                                @endif

                                                @if ($event->status != "CLOSE" && Auth::user()->is_admin || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                                    <input type="hidden" name="participantes[{{ $assist->id }}][id]" value="{{ $assist->id }}">
                                                    <select class="form-control" name="participantes[{{ $assist->id }}][puesto]">
                                                        <option value="participante" {{ $assist->pivot->puesto == 'participante' ? 'selected' : '' }}>-- Selecciona un puesto --</option>
                                                        <option value="primero" {{ $assist->pivot->puesto == 'primero' ? 'selected' : '' }}>Primer puesto</option>
                                                        <option value="segundo" {{ $assist->pivot->puesto == 'segundo' ? 'selected' : '' }}>Segundo puesto</option>
                                                        <option value="tercero" {{ $assist->pivot->puesto == 'tercero' ? 'selected' : '' }}>Tercer puesto</option>
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
                                            @if($today === $eventDate && isset($resultsByParticipant[Auth::user()->id]) && count($resultsByParticipant[Auth::user()->id]) > 0)
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target="#results-{{ $assist->id }}" aria-expanded="false" aria-controls="results-{{ $assist->id }}" title="Ver resultados">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($today === $eventDate && isset($resultsByParticipant[Auth::user()->id]) && count($resultsByParticipant[Auth::user()->id]) > 0)
                                        <div class="collapse" id="results-{{ $assist->id }}">
                                            <div class="card card-body mb-3" style="background-color: rgb(2, 0, 97)">
                                                @if(isset($resultsByParticipant[$assist->id]) && count($resultsByParticipant[$assist->id]) > 0)
                                                    @foreach($resultsByParticipant[$assist->id] as $index => $result)
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <p> · {{ $result->blade }} {{ $result->ratchet }} {{ $result->bit }}</p>
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

                                @if ($event->status != "CLOSE" && Auth::user()->is_admin || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                                    <div class="form-group py-2">
                                        <input type="submit" class="btn btn-outline-success text-uppercase font-weight-bold flex-right" value="Enviar resultados">
                                    </div>
                                @endif
                            </form>
                        @else
                            <p>No hay participantes.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($event->iframe)
            <div class="row my-4 pl-3">
                <h2 class="my-4">Emparejamientos</h2>
                {!! $event->iframe !!}
            </div>
        @endif

        @if ($videos)
            <div class="my-4">
                <h2 class="my-4">Vídeos del evento</h2>
                <div class="row">
                    @foreach ($videos as $video)
                        <div class="col-md-4">
                            <iframe id="player" type="text/html" width="100%" height="250"
                            src="https://www.youtube.com/embed/{{ $video->url }}"
                            frameborder="0"></iframe>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </article>

    <!-- Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width: 80%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">Resultados del deck en el torneo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario dentro del popup -->
                    <form method="POST" action="{{ route('tournament.results.store', ['eventId' => $event->id]) }}">
                        @csrf

                        @foreach($results as $index => $result)
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="blade_{{ $index + 1 }}">Blade</label>
                                        <select class="form-control select2" id="blade_{{ $index + 1 }}" name="blade[]" required style="width: 100%">
                                            <option>-- Selecciona un blade --</option>
                                            @foreach($bladeOptions as $option)
                                                <option value="{{ $option }}" {{ $result->blade == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ratchet_{{ $index + 1 }}">Ratchet</label>
                                        <select class="form-control select2" id="ratchet_{{ $index + 1 }}" name="ratchet[]" required style="width: 100%">
                                            <option>-- Selecciona un ratchet --</option>
                                            @foreach($ratchetOptions as $option)
                                                <option value="{{ $option }}" {{ $result->ratchet == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="bit_{{ $index + 1 }}">Bit</label>
                                        <select class="form-control select2" id="bit_{{ $index + 1 }}" name="bit[]" required style="width: 100%">
                                            <option>-- Selecciona un bit --</option>
                                            @foreach($bitOptions as $option)
                                                <option value="{{ $option }}" {{ $result->bit == $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="victorias_{{ $index + 1 }}">Victorias</label>
                                        <input type="number" class="form-control" id="victorias_{{ $index + 1 }}" name="victorias[]" value="{{ $result->victorias }}">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="derrotas_{{ $index + 1 }}">Derrotas</label>
                                        <input type="number" class="form-control" id="derrotas_{{ $index + 1 }}" name="derrotas[]" value="{{ $result->derrotas }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="puntos_ganados_{{ $index + 1 }}">Puntos Ganados</label>
                                        <input type="number" class="form-control" id="puntos_ganados_{{ $index + 1 }}" name="puntos_ganados[]" value="{{ $result->puntos_ganados }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="puntos_perdidos_{{ $index + 1 }}">Puntos Perdidos</label>
                                        <input type="number" class="form-control" id="puntos_perdidos_{{ $index + 1 }}" name="puntos_perdidos[]" value="{{ $result->puntos_perdidos }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Guardar</button>
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
    <!-- Incluye el script para inicializar los tooltips y otros componentes de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection
