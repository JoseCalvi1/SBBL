@extends('layouts.app')


@section('content')
@if (Auth::user()->is_admin)
<div class="py-4">
    <h2 class="text-center mb-2">Administra los eventos</h2>

    <div class="col-md-10 mx-auto bg-white p-3">
        <a href="{{ route('events.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear evento
        </a>

        <table class="table">
            <thead class="bg-primary text-light">
                <tr>
                    <th scole="col">Título</th>
                    <th scole="col">Location</th>
                    <th scole="col">Fecha</th>
                    <th scole="col">Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>{{ $event->name }}</td>
                        <td>{{ $event->location }}</td>
                        <td><event-date fecha="{{ $event->date }}"></event-date></td>
                        <td>
                            <a href="{{ route('events.show', ['event' => $event->id]) }}" class="btn btn-success mb-2 d-block">Ver</a>
                            <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                            <event-delete event-id={{ $event->id }}></event-delete>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@else
header("Location: /");
die();
@endif
@endsection
	