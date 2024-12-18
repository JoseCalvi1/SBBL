@extends('layouts.app')

@section('content')
<div class="container mt-2">
    <!-- Botón para añadir trofeo -->
    <a href="{{ route('trophies.create') }}" class="btn btn-outline-light mb-4">
        <i class="fas fa-trophy"></i> Añadir Trofeo
    </a>
    <a href="{{ route('trophies.assignForm') }}" class="btn btn-outline-info ml-2 mb-4">Asignar Trofeo</a>
    <a href="{{ route('trophies.usersWithTrophies') }}" class="btn btn-outline-warning ml-2 mb-4">Manejar Trofeos</a>

    <h2 class="text-center text-light mb-5">Trofeos</h2>

    <!-- Tabla de trofeos -->
    <div class="table-responsive">
        <table class="table table-dark table-hover">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Temporada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trophies as $trophy)
                <tr>
                    <td>{{ $trophy->name }}</td>
                    <td>{{ $trophy->season }}</td>
                    <td>
                        <!-- Botones de acción -->
                        <a href="{{ route('trophies.edit', $trophy->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <form action="{{ route('trophies.destroy', $trophy->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Estilo general para el tema oscuro */
    body {
        background-color: #121212; /* Fondo oscuro */
        color: #e0e0e0; /* Texto claro */
    }

    table th,
    table td {
        color: #e0e0e0;
    }

    .table-dark {
        background-color: #1d1d1d; /* Fondo oscuro para la tabla */
    }

    .table-hover tbody tr:hover {
        background-color: #444444; /* Color de hover de la tabla */
    }

    /* Botones personalizados */
    .btn-outline-light {
        border-color: #ffffff;
        color: #ffffff;
    }

    .btn-outline-light:hover {
        background-color: #ffffff;
        color: #121212;
    }

    .btn-warning {
        background-color: #f39c12;
        border-color: #f39c12;
    }

    .btn-warning:hover {
        background-color: #e67e22;
        border-color: #e67e22;
    }

    .btn-danger {
        background-color: #e74c3c;
        border-color: #e74c3c;
    }

    .btn-danger:hover {
        background-color: #c0392b;
        border-color: #c0392b;
    }
</style>
@endsection
