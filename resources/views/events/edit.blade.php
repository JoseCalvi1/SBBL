@extends('layouts.app')


@section('content')

<a href="{{ route('events.index') }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
    Volver
</a>

<h2 class="text-center mb-5">Editar evento: {{ $event->titulo }}</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
    <form method="POST" action="{{ route('events.update', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate>
        @csrf

        @method('PUT')
            <div class="form-group">
                <label for="name">Título evento</label>

                <input type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    placeholder="Título evento"
                    value="{{ $event->name }}"
                    />

                    @error('name')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="location">Lugar del evento</label>

                <input type="text"
                    name="location"
                    class="form-control @error('location') is-invalid @enderror"
                    id="location"
                    placeholder="Lugar del evento"
                    value="{{ $event->location }}"
                    />

                    @error('location')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>


            <div class="form-group mt-4">
                <label for="imagen">Selecciona una imagen</label>
                <input
                    id="imagen"
                    type="file"
                    class="form-control @error('imagen') is-invalid @enderror"
                    name="imagen" />

                    <div class="mt-4">
                        <p>Imagen Actual:</p>
                        <img src="/storage/{{ $event->imagen }}" style="width: 300px;">
                    </div>
                @error('imagen')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                @enderror
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Actualizar evento">
            </div>
        </form>
    </div>
</div>

@endsection
