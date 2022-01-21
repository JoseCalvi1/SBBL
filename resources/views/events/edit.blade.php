@extends('layouts.app')


@section('content')

<a href="{{ route('events.index') }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
    Volver
</a>

<h2 class="text-center mb-5">Editar evento: {{ $event->titulo }}</h2>

<div class="row justify-content-center mt-5" style="margin-right: 0px !important;">
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
                <label for="region_id">Región</label>

                <select name="region_id" id="region_id" class="form-control @error('nombre') is-invalid @enderror">
                    @if ($event->region)
                        <option value="{{ $event->region->id }}">{{ $event->region->name }}</option>
                    @else
                        <option disabled selected>- Selecciona -</option>
                    @endif

                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>

                    @error('region_id')
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

            <div class="form-group">
                <label for="event_date">Fecha del evento</label>

                <input type="date"
                    name="event_date"
                    class="form-control @error('event_date') is-invalid @enderror"
                    id="event_date"
                    placeholder="Título evento"
                    value="{{ $event->date }}"
                    />

                    @error('event_date')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>


            <div class="form-group">
                <label for="imagen">Imagen por defecto</label>

                <select name="imagen" id="imagen" class="form-control @error('imagen') is-invalid @enderror">
                    <option disabled selected>- Selecciona una imagen -</option>
                    <option value="quedada">Quedada</option>
                    <option value="ranking">Ranking</option>
                    <option value="duelo">Duelo</option>
                </select>

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

        <form method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="form-group">
                <label for="url">Id del video</label>

                <input type="hidden" name="event_id" id="event_id" value="{{ $event->id }}">

                <input type="text"
                    name="url"
                    class="form-control @error('url') is-invalid @enderror"
                    id="url"
                    placeholder="Id del video"
                    value="{{ $event->url }}"
                    />

                    @error('url')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Añadir video">
            </div>
        </form>
    </div>
</div>

@endsection
