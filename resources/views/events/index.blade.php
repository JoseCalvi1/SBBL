@extends('layouts.app')

@section('content')
@if (Auth::user()->is_admin)
<div class="py-4">
    <h2 class="text-center mb-2 text-white">Administra los eventos</h2>

    <div class="col-md-10 mx-auto bg-white p-3" style="background-color:transparent !important;">
        <a href="{{ route('events.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear evento
        </a>

        <div class="table-responsive">
            <table class="table" style="color:white !important;">
                <thead class="bg-primary text-light">
                    <tr>
                        <th scole="col">Título</th>
                        <th scole="col">Location</th>
                        <th scole="col">Modalidad</th>
                        <th scole="col">Region</th>
                        <th scole="col">Fecha</th>
                        <th scole="col">Estado</th>
                        <th scole="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($events as $event)
                        <tr class="{{ ($event->date < \Carbon\Carbon::today()) ? 'bg-secondary' : '' }}">
                            <td>{{ $event->name }}</td>
                            <td>{{ $event->location }}</td>
                            <td>{{ $event->mode }}</td>
                            <td>{{ $event->region->name }}</td>
                            <td><event-date fecha="{{ $event->date }}"></event-date></td>
                            <td>@if ($event->status == "OPEN")
                        <span class="btn btn-success">ABIERTO</span>
                    @elseif ($event->status == "PENDING")
                        <span class="btn btn-warning">PENDIENTE CALIFICAR</span>
                    @else
                        <span class="btn btn-danger">CERRADO</span>
                    @endif</td>
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
</div>
@else
<script type="text/javascript">
    window.location = "/";
</script>
@endif
@endsection
