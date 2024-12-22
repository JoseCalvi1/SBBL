@extends('layouts.app')

@section('content')
@if (Auth::user()->is_referee)
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
                            <td>{{ $event->city }}</td>
                            <td>{{ $event->mode }}</td>
                            <td>{{ $event->region->name }}</td>
                            <td><event-date fecha="{{ $event->date }}"></event-date></td>
                            <td>@if ($event->status == "OPEN")
                        <span class="btn btn-success" style="width: 100%">ABIERTO</span>
                    @elseif ($event->status == "PENDING")
                        <span class="btn btn-warning" style="width: 100%">PENDIENTE CALIFICAR</span>
                    @elseif ($event->status == "INVALID")
                        <span class="btn btn-dark" style="width: 100%">INVÁLIDO</span>
                    @else
                        <span class="btn btn-danger" style="width: 100%">CERRADO</span>
                    @endif
                    @if ($event->iframe)
                    <div>
                        <a href="{{ $event->iframe }}" target="_blank" class="btn btn-info text-uppercase font-weight-bold mt-1"
                        style="width: 100%">Ver Video</a>
                    </div>
                @endif</td>
                            <td>
                                <a href="{{ route('events.show', ['event' => $event->id]) }}" class="btn btn-success mb-2 d-block">Ver</a>
                                <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                                <form method="POST" action="{{ route('events.invalidar', ['event' => $event->id]) }}">
                                    @csrf
                                    @method('PUT') <!-- O POST según tu configuración -->
                                    <button type="submit" class="btn btn-warning mb-2" style="width: 100%">
                                        <i class="fas fa-times-circle"></i> Invalidar
                                    </button>
                                </form>
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
