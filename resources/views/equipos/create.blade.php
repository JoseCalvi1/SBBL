@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-2 mb-2 text-white" style="background-color: unset">
                <div class="card-header">Crear Nuevo Equipo</div>

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

                    <form method="POST" action="{{ route('equipos.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="name">Nombre del Equipo:</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Descripción:</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="form-group" style="color: white;">
                            <label for="logo">Logo del equipo:</label>
                            <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*">
                        </div>

                        <div class="form-group" style="color: white;">
                            <label for="image">Imagen de equipo:</label>
                            <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                        </div>

                        <!-- Agrega aquí más campos según sea necesario -->

                        <button type="submit" class="btn btn-primary">Validar Equipo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
