@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mt-2 mb-2">
                <h2 class="titulo-categoria text-white">Lista de Equipos</h2>

                <div class="mt-3">
                    <a href="{{ route('equipos.create') }}" class="btn btn-primary mb-3">Crear Nuevo Equipo</a>

                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table text-white">
                            <thead>
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipos as $equipo)
                                    <tr>
                                        <td>{{ $equipo->name }}</td>
                                        <td>{{ $equipo->description }}</td>
                                        <td>
                                            <a href="{{ route('equipos.show', $equipo) }}" class="btn btn-info btn-sm mb-1">Ver</a>
                                            <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-primary btn-sm mb-1">Editar</a>
                                            <form action="{{ route('equipos.destroy', $equipo) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('¿Estás seguro de querer eliminar este equipo?')">Eliminar</button>
                                            </form>

                                            @if ($equipo->status == "pending")
                                                <form action="{{ route('equipos.acceptInvitation', $equipo) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm mb-1">Aceptar equipo</button>
                                                </form>
                                            @endif
                                            @if ($equipo->status == "updated")
                                                <form action="{{ route('equipos.acceptInvitation', $equipo) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-info btn-sm mb-1">Aceptar cambios</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
