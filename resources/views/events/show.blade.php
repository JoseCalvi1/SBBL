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

            <p>
                <assist-button></assist-button>
            </p>

        </div>
            </div>
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
