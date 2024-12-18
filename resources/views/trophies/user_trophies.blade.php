@extends('layouts.app')

@section('content')
<div class="container mt-3">
    <a href="{{ route('trophies.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
        Volver
    </a>
    <h2 class="text-center text-light mb-5">Usuarios con Trofeos Asignados</h2>

    <!-- Mensaje de éxito -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($users->isEmpty())
        <p class="text-center text-light">No hay usuarios con trofeos asignados.</p>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>Nombre de Usuario</th>
                        <th>Trofeos Asignados</th>
                        <th>Meses desde Creación</th> <!-- Nueva columna -->
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>
                            @foreach($user->trophies as $trophy)
                                <span class="badge badge-primary">
                                    {{ $trophy->name }} ({{ $trophy->pivot->count }})
                                    <!-- Formulario para editar el número de trofeos -->
                                    <form action="{{ route('trophies.updateCount', ['userId' => $user->id, 'trophyId' => $trophy->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="count" value="{{ $trophy->pivot->count }}" min="1" style="width: 60px;" class="form-control form-control-sm d-inline-block">
                                        <button type="submit" class="btn btn-sm btn-success">Actualizar</button>
                                    </form>
                                </span>
                            @endforeach
                        </td>
                        <td>
                            <!-- Calcular meses desde la creación -->
                            @php
                                $createdAt = \Carbon\Carbon::parse($user->created_at);
                                $monthsSinceCreation = $createdAt->diffInMonths(\Carbon\Carbon::now());
                            @endphp
                            {{ $monthsSinceCreation }} meses
                        </td>
                        <td>
                            <!-- Formulario para eliminar un trofeo de un usuario -->
                            @foreach($user->trophies as $trophy)
                                <form action="{{ route('trophies.remove', ['userId' => $user->id, 'trophyId' => $trophy->id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@section('styles')
<style>
    /* Estilo para el tema oscuro */
    body {
        background-color: #121212;
        color: #e0e0e0;
    }
    .table-dark {
        background-color: #1d1d1d;
    }
    .table-hover tbody tr:hover {
        background-color: #444444;
    }
    .badge-primary {
        background-color: #007bff;
    }
</style>
@endsection
