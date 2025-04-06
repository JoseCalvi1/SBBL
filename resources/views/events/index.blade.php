@extends('layouts.app')

@section('content')
@if (Auth::user()->is_referee || Auth::user()->is_admin)
<div class="py-4">
    <h2 class="text-center mb-2 text-white">Administra los eventos</h2>

    <div class="col-md-10 mx-auto bg-white p-3" style="background-color:transparent !important;">
        <a href="{{ route('events.create') }}" class="btn btn-outline-primary mr-2 mb-4 text-uppercase font-weight-bold">
            Crear evento
        </a>

        <!-- Formulario de filtros -->
        <form method="GET" action="{{ route('events.indexAdmin') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="estado" class="text-white">Filtrar por Estado:</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="OPEN" {{ request('estado') == 'OPEN' ? 'selected' : '' }}>Abierto</option>
                        <option value="PENDING" {{ request('estado') == 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                        <option value="REVIEW" {{ request('estado') == 'REVIEW' ? 'selected' : '' }}>En revisión</option>
                        <option value="INVALID" {{ request('estado') == 'INVALID' ? 'selected' : '' }}>Inválido</option>
                        <option value="CLOSE" {{ request('estado') == 'CLOSE' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="beys" class="text-white">Filtrar por Tipo de Evento:</label>
                    <select name="beys" id="beys" class="form-control">
                        <option value="">Todos</option>
                        <option value="ranking" {{ request('beys') == 'ranking' ? 'selected' : '' }}>Ranking / Ranking Plus</option>
                    </select>
                </div>

                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </div>
        </form>


        <div class="table-responsive">
            <table class="table" style="color:white !important;">
                <thead class="bg-primary text-light">
                    <tr>
                        <th scope="col">Título</th>
                        <th scope="col">Location</th>
                        <th scope="col">Modalidad</th>
                        <th scope="col">Region</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
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
                            <td>
                                @if ($event->status == "OPEN")
                                    <span class="btn btn-success" style="width: 100%">ABIERTO</span>
                                @elseif ($event->status == "PENDING")
                                    <span class="btn btn-warning" style="width: 100%">PENDIENTE CALIFICAR</span>
                                @elseif ($event->status == "REVIEW")
                                    <span class="btn btn-info" style="width: 100%">EN REVISIÓN</span>
                                @elseif ($event->status == "INVALID")
                                <span class="btn btn-dark" style="width: 100%">INVÁLIDO</span>
                            @else
                                    <span class="btn btn-danger" style="width: 100%">CERRADO</span>
                                @endif
                                @if ($event->iframe)
                                    <div>
                                        <a href="{{ $event->iframe }}" target="_blank" class="btn btn-info text-uppercase font-weight-bold mt-1" style="width: 100%">Ver Video</a>
                                    </div>
                                @endif
                                @if ($event->challonge)
                                    <div>
                                        <a href="{{ $event->challonge }}" target="_blank" class="btn btn-info text-uppercase font-weight-bold mt-1" style="width: 100%">Ver Challonge</a>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('events.show', ['event' => $event->id]) }}" class="btn btn-success mb-2 d-block">Ver</a>
                                <a href="{{ route('events.edit', ['event' => $event->id]) }}" class="btn btn-dark mb-2 d-block">Editar</a>
                                @if ($event->status != "REVIEW")
                                <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'revisar']) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-info mb-2" style="width: 100%">
                                        Revisar
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('events.estado', ['event' => $event->id, 'estado' => 'invalidar']) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-warning mb-2" style="width: 100%">
                                        Invalidar
                                    </button>
                                </form>
                                <event-delete event-id="{{ $event->id }}"></event-delete>
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
