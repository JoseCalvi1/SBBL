@extends('layouts.app')


@section('content')

<a href="{{ route('events.index') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5">Crear nuevo evento</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
            <div class="form-group">
                <label for="name">Título evento</label>

                <input type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    placeholder="Título evento"
                    value="{{old('name')}}"
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
                        <option disabled selected>- Selecciona -</option>

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
                    value="{{old('location')}}"
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
                    value="{{old('event_date')}}"
                    />

                    @error('event_date')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>


            <!-- <div class="form-group mt-4">
                <label for="imagen">Selecciona una imagen</label>
                <input
                    id="imagen"
                    type="file"
                    class="form-control @error('imagen') is-invalid @enderror"
                    name="imagen" />
                @error('imagen')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                @enderror
            </div> -->

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
                <input type="submit" class="btn btn-primary" value="Agregar Evento">
            </div>
        </form>
    </div>
</div>

@endsection

