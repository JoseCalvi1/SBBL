@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-white" style="background-color: unset">
                <div class="card-header">Editar Equipo</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('equipos.update', $equipo) }}" class="text-white" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nombre del Equipo:</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $equipo->name }}" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción:</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required>{{ $equipo->description }}</textarea>
                        </div>

                        <div class="form-group" style="color: white;">
                            <label for="logo">Logo del equipo:</label>
                            @if($equipo->logo)
                                <label>Logo actual:</label>
                                <img src="data:image/png;base64,{{ $equipo->logo }}" width="100">
                            @else
                                <p>No hay imagen actual</p>
                            @endif
                            <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*">
                        </div>

                        <div class="form-group" style="color: white;">
                            <label for="image">Imagen de equipo:</label>
                            @if($equipo->image)
                                <label>Imagen actual:</label>
                                <img src="data:image/png;base64,{{ $equipo->image }}" width="100">
                            @else
                                <p>No hay imagen actual</p>
                            @endif
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                        </div>

                        <!-- Agrega aquí más campos si es necesario -->

                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
