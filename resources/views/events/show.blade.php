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
                <h1 class="text-center mb-4">{{ $event->name }}</h1>
             @if(Auth::user() && $event->date > $hoy)
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

                </div>
            </div>
            <div class="col-md-6">
                <h4 style="font-weight: bold">Listado de participantes</h4>
                @if (count($assists) > 0)
                    @foreach ($assists as $assist)
                        {{ $assist->name.' ('.$assist->profile->points_s2.' puntos)' }} @if (Auth::user()->is_admin) <b>{{ $assist->email }}</b> @endif <br>
                    @endforeach
                @else
                    Aún no hay participantes inscritos
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
	