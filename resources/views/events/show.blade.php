@extends('layouts.app')

@section('content')

    <article class="contenido-event bg-white p-5 shadow">
        <h1 class="text-center mb-4">{{ $event->name }}</h1>

        <div class="imagen-event">
            <img src="/storage/{{ $event->imagen }}" class="w-100 h-50">
        </div>

        <div class="event-meta mt-2">
            <p>
                <span class="font-weight-bold text-primary">Lugar:</span>
                    {{ $event->location }}
            </p>

            <p>
                <span class="font-weight-bold text-primary">Fecha:</span>

                <event-date fecha="{{ $event->date }}"></event-date>
            </p>

        </div>
    </article>

@endsection
