@extends('layouts.app')


@section('content')

<a href="{{ route('events.show', ['event' => $event->id]) }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
    Volver
</a>

<h2 class="text-center mb-5 text-white">Editar evento: {{ $event->name }}</h2>

<div class="row justify-content-center mt-5 text-white" style="margin-right: 0px !important;">
    <div class="col-md-8">
    <form method="POST" action="{{ route('events.update', ['event' => $event->id]) }}" enctype="multipart/form-data" novalidate style="color:white;">
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
                <label for="mode">Modalidad</label>

                <select name="mode" id="mode" class="form-control @error('mode') is-invalid @enderror">
                    <option disabled selected>- Selecciona un modo -</option>
                    <option value="beybladex" {{ $event->mode == 'beybladex' ? 'selected' : '' }}>Beyblade X</option>
                    <option value="beybladeburst" {{ $event->mode == 'beybladeburst' ? 'selected' : '' }}>Beyblade Burst</option>
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
                    <option value="quedada" {{ $event->imagen == 'upload-events/quedada.jpg' ? 'selected' : '' }}>Quedada</option>
                    <option value="ranking" {{ ($event->imagen == 'upload-events/ranking.jpg' || $event->imagen == 'upload-events/rankingx.jpg') ? 'selected' : '' }}>Ranking</option>
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
                    <option value="3on3" {{ $event->deck == '3on3' ? 'selected' : '' }}>3on3</option>
                    <option value="5g" {{ $event->deck == '5g' ? 'selected' : '' }}>5G</option>
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
                    <option value="SingleElimination" {{ $event->configuration == 'SingleElimination' ? 'selected' : '' }}>Single elimination</option>
                    <option value="DoubleElimination" {{ $event->configuration == 'DoubleElimination' ? 'selected' : '' }}>Double elimination</option>
                    <option value="RoundRobin" {{ $event->configuration == 'RoundRobin' ? 'selected' : '' }}>Round Robin</option>
                    <option value="Swiss" {{ $event->configuration == 'Swiss' ? 'selected' : '' }}>Swiss</option>
                    <option value="FreeForAll" {{ $event->configuration == 'FreeForAll' ? 'selected' : '' }}>Free for all</option>
                    <option value="Leaderboard" {{ $event->configuration == 'Leaderboard' ? 'selected' : '' }}>Leaderboard</option>
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
                <label for="note">Anotaciones</label>

                <input type="text"
                    name="note"
                    class="form-control @error('note') is-invalid @enderror"
                    id="note"
                    placeholder="Anotaciones importantes"
                    value="{{ $event->note }}"
                    />

                    @error('note')
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
                <label for="event_time">Hora del evento</label>

                <input type="time"
                    name="event_time"
                    class="form-control @error('event_time') is-invalid @enderror"
                    id="event_time"
                    placeholder="Fecha del evento"
                    value="{{ $event->time }}"
                    />

                    @error('event_time')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>


            <div class="form-group">
                <label for="iframe">Iframe</label>

                <input type="text"
                    name="iframe"
                    class="form-control @error('iframe') is-invalid @enderror"
                    id="iframe"
                    placeholder="Iframe"
                    value="{{ $event->iframe }}"
                    />

                    @error('iframe')
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
