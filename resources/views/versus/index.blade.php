@extends('layouts.app')


@section('content')
<div class="py-4">
    <h2 class="text-center mb-2">Administra los duelos</h2>

    <div class="col-md-10 mx-auto bg-white p-3">
        <a href="{{ route('versus.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear duelo
        </a>

        <table class="table">
            <thead class="bg-primary text-light">
                <tr>
                    <th scole="col">Jugador 1</th>
                    <th scole="col">Jugador 2</th>
                    <th scole="col">Ganador</th>
                    <th scole="col">Evento</th>
                    <th scole="col">URL</th>
                    <th scole="col">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($versus as $duel)
                    <tr>
                        <td>{{ $duel->versus_1->name }}</td>
                        <td>{{ $duel->versus_2->name }}</td>
                        <td>{{ ($duel->winner == $duel->user_id_1) ? $duel->versus_1->name : $duel->versus_2->name }}</td>
                        <td><a href="{{ route('events.show', ['event' => $duel->event->id]) }}">{{ $duel->event->name }}</a></td>
                        <td>{{ $duel->url }}</td>
                        <td><a href="{{ route('versus.edit', ['duel' => $duel->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>

@endsection
