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
        @if (Auth::user()->is_admin || Auth::user()->is_referee)
            <div class="form-group">
                <label for="name">Título evento</label>

                <input type="text"
                    name="name"
                    class="form-control bg-dark text-white @error('name') is-invalid @enderror"
                    id="name"
                    placeholder="Título evento"
                    value="{{ old('name', $event->name) }}"
                    />

                    @error('name')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group" style="color: white;">
                <label for="image_mod">Imagen personalizada:</label>
                @if($event->image_mod)
                    <label>Imagen actual:</label>
                    <img src="data:image/png;base64,{{ $event->image_mod }}" width="100">
                @else
                    <p>No hay imagen actual</p>
                @endif
                <input type="file" class="form-control-file" id="image_mod" name="image_mod" accept="image/*">
            </div>
        @endif

            <div class="form-group">
                <label for="mode">Modalidad</label>
                <select name="mode" id="mode" class="form-control bg-dark text-white @error('mode') is-invalid @enderror">
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
                <select name="imagen" id="imagen" class="form-control bg-dark text-white @error('imagen') is-invalid @enderror">
                    <option disabled selected>- Selecciona una imagen -</option>
                    <option value="quedada" {{ $event->imagen == 'upload-events/quedada.jpg' ? 'selected' : '' }}>Quedada</option>
                    <option value="ranking" {{ ($event->imagen == 'upload-events/ranking.jpg' || $event->imagen == 'upload-events/rankingx.jpg') ? 'selected' : '' }}>Ranking</option>
                    @if (Auth::user()->is_admin || Auth::user()->is_referee)
                    <option value="rankingplus" {{ $event->imagen == 'upload-events/rankingplus.jpg' ? 'selected' : '' }}>Ranking Plus</option>
                    <option value="grancopa" {{ $event->imagen == 'upload-events/grancopa.jpg' ? 'selected' : '' }}>Gran Copa</option>
                    @endif
                    <option value="hasbro" {{ $event->imagen == 'upload-events/hasbro.jpg' ? 'selected' : '' }}>Hasbro</option>
                </select>

                    @error('imagen')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="deck">Deck</label>
                <select name="deck" id="deck" class="form-control bg-dark text-white @error('deck') is-invalid @enderror">
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
                <select name="configuration" id="configuration" class="form-control bg-dark text-white @error('configuration') is-invalid @enderror">
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
                <select name="region_id" id="region_id" class="form-control bg-dark text-white @error('region_id') is-invalid @enderror">
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
                <label for="city">Localidad del evento</label>
                <input type="text"
                    name="city"
                    class="form-control bg-dark text-white @error('city') is-invalid @enderror"
                    id="city"
                    placeholder="Localidad del evento"
                    value="{{ old('city', $event->city) }}"
                    />

                    @error('city')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

            <div class="form-group">
                <label for="location">Lugar del evento</label>
                <input type="text"
                    name="location"
                    class="form-control bg-dark text-white @error('location') is-invalid @enderror"
                    id="location"
                    placeholder="Lugar del evento"
                    value="{{ old('location', $event->location) }}"
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
                    class="form-control bg-dark text-white @error('note') is-invalid @enderror"
                    id="note"
                    placeholder="Anotaciones importantes"
                    value="{{ old('note', $event->note) }}"
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
                    class="form-control bg-dark text-white @error('event_date') is-invalid @enderror"
                    id="event_date"
                    value="{{ old('event_date', $event->date) }}"
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
                    class="form-control bg-dark text-white @error('event_time') is-invalid @enderror"
                    id="event_time"
                    value="{{ old('event_time', $event->time) }}"
                    />

                    @error('event_time')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{$message}}</strong>
                        </span>
                    @enderror
            </div>

        <!-- Advertencia para no admins y árbitros -->
        @if(!Auth::user()->is_admin && !Auth::user()->is_referee)
            <div class="alert alert-warning text-center text-dark" role="alert">
                Al editar este evento, afirmo que he leído y comprendido <a href="sbbl.es/rules" target="_blank">las normas</a> para el desarrollo del torneo y que soy el encargado de que haya material suficiente para ello (Estadio y material de grabación como trípode y móvil/cámara).
            </div>
        @endif

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Actualizar evento">
        </div>
    </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modeSelect = document.getElementById("mode");
        const deckSelect = document.getElementById("deck");

        function updateDeckOptions() {
            if (modeSelect.value === "beybladex") {
                deckSelect.value = "3on3";
                deckSelect.querySelectorAll("option").forEach(option => {
                    if (option.value !== "3on3") option.disabled = true;
                });
            } else {
                deckSelect.querySelectorAll("option").forEach(option => option.disabled = false);
            }
        }

        modeSelect.addEventListener("change", updateDeckOptions);
        updateDeckOptions();
    });
</script>
@endsection
