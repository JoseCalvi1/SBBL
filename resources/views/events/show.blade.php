@extends('layouts.app')

@section('content')

    <article class="contenido-event bg-white p-5 shadow">
        <div class="row">
            <div class="col-md-5">
                <div class="imagen-event">
                    <img src="/storage/{{ $event->imagen }}" class="w-100 h-25" style="border-radius: 5px;">
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
                        <span class="font-weight-bold text-primary">Región:</span>
                            {{ $event->region->name }}
                    </p>

                    <p>
                        <span class="font-weight-bold text-primary">Lugar:</span>
                            {{ $event->location }}
                    </p>

                    <p>
                        <span class="font-weight-bold text-primary">Fecha:</span>

                        <event-date fecha="{{ $event->date }}"></event-date>
                    </p>
                    @if ($event->status != "CLOSE" && Auth::user()->is_admin || ($event->status == "OPEN" && $event->created_by == Auth::user()->id))
                    <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                @endif
                </div>
            </div>
            <div class="col-md-6">
                <h4 style="font-weight: bold">Listado de participantes</h4>
                @if (count($assists) > 0)
                    <form method="POST" action="{{ route('events.updatePuestos', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        @foreach ($assists as $assist)
                        <p>
                            {{ $assist->name }} @if (Auth::user()->is_admin) <b>{{ $assist->email }}</b> @endif
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

        <div class="row my-4 pl-3">
            <h2 class="my-4">Emparejamientos</h2>
            {!! $event->iframe !!}
        </div>

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
    </article>

@endsection
