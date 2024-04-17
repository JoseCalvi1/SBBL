@extends('layouts.app')


@section('content')

<a href="{{ route('inicio.events') }}" class="btn btn-outline-primary mr-2 text-uppercase font-weight-bold m-4">
    Volver
</a>

<h2 class="text-center mb-5" style="color: white">Crear nuevo evento</h2>

<div class="row justify-content-center mt-5">
    <div class="col-md-8">
    <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" novalidate style="color: white">
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
                <label for="mode">Modalidad</label>

                <select name="mode" id="mode" class="form-control @error('mode') is-invalid @enderror">
                    <option disabled selected>- Selecciona un modo -</option>
                    <option value="beybladex">Beyblade X</option>
                    <option value="beybladeburst">Beyblade Burst</option>
                </select>

                    @error('mode')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="imagen">Categoría</label>

                <select name="imagen" id="imagen" class="form-control @error('imagen') is-invalid @enderror">
                    <option disabled selected>- Selecciona una imagen -</option>
                    <option value="quedada">Quedada</option>
                    <option value="ranking">Ranking</option>
                </select>

                    @error('imagen')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="deck">Deck</label>

                <select name="deck" id="deck" class="form-control @error('deck') is-invalid @enderror">
                    <option disabled selected>- Selecciona un tamaño de deck -</option>
                    <option value="3on3">3on3</option>
                    <option value="5g">5G</option>
                </select>

                    @error('deck')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="configuration">Tipo torneo</label>

                <select name="configuration" id="configuration" class="form-control @error('configuration') is-invalid @enderror">
                    <option disabled selected>- Selecciona la configuración del torneo -</option>
                    <option value="SingleElimination">Single elimination</option>
                    <option value="DoubleElimination">Double elimination</option>
                    <option value="RoundRobin">Round Robin</option>
                    <option value="Swiss">Swiss</option>
                    <option value="FreeForAll">Free for all</option>
                    <option value="Leaderboard">Leaderboard</option>
                </select>

                    @error('configuration')
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
                    placeholder="Fecha del evento"
                    value="{{old('event_date')}}"
                    />

                    @error('event_date')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="event_time">Hora del evento</label>

                <input type="time"
                    name="event_time"
                    class="form-control @error('event_time') is-invalid @enderror"
                    id="event_time"
                    placeholder="Fecha del evento"
                    value="{{old('event_time')}}"
                    />

                    @error('event_time')
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
                <input type="submit" class="btn btn-primary" value="Agregar Evento">
            </div>
        </form>
    </div>
</div>

@endsection

