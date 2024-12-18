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
        @if (Auth::user()->is_admin || Auth::user()->is_referee)
            <div class="form-group">
                <label for="name">Título evento</label>
                <input type="text"
                    name="name"
                    class="form-control bg-dark text-white @error('name') is-invalid @enderror"
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

            <div class="form-group" style="color: white;">
                <label for="image_mod">Imagen personalizada:</label>
                <input type="file" class="form-control-file" id="image_mod" name="image_mod" accept="image/*">
            </div>
        @endif

            <div class="form-group">
                <label for="mode">Modalidad</label>
                <select name="mode" id="mode" class="form-control bg-dark text-white @error('mode') is-invalid @enderror">
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
                <select name="imagen" id="imagen" class="form-control bg-dark text-white @error('imagen') is-invalid @enderror">
                    <option disabled selected>- Selecciona una imagen -</option>
                    <option value="quedada">Quedada</option>
                    <option value="ranking">Ranking</option>
                    @if (Auth::user()->is_admin || Auth::user()->is_referee)
                    <option value="rankingplus">Ranking Plus</option>
                    <option value="grancopa">Gran Copa</option>
                    @endif
                    <option value="hasbro">Hasbro</option>
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
                <select name="configuration" id="configuration" class="form-control bg-dark text-white @error('configuration') is-invalid @enderror">
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
                <select name="region_id" id="region_id" class="form-control bg-dark text-white @error('nombre') is-invalid @enderror">
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
                <label for="city">Localidad del evento</label>
                <input type="text"
                    name="city"
                    class="form-control bg-dark text-white @error('city') is-invalid @enderror"
                    id="city"
                    placeholder="Localidad del evento"
                    value="{{old('city')}}"
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
                    value="{{old('location')}}"
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
                    value="{{old('note')}}"
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
                    class="form-control bg-dark text-white @error('event_time') is-invalid @enderror"
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

            @if(!Auth::user()->is_admin && !Auth::user()->is_referee)
                <div class="alert alert-warning text-center text-dark" role="alert">
                    Al crear este evento, afirmo que he leído y comprendido <a href="sbbl.es/rules" target="_blank">las normas</a> para el desarrollo del torneo y que soy el encargado de que haya material suficiente para ello (Estadio y material de grabación como trípode y móvil/cámara)
                </div>
            @endif

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Agregar Evento" id="submitButton">
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
                // Si el modo es Beyblade X, solo muestra la opción 3on3
                deckSelect.value = "3on3";
                deckSelect.querySelectorAll("option").forEach(option => {
                    option.hidden = option.value !== "3on3";
                });
            } else {
                // Si el modo no es Beyblade X, muestra todas las opciones
                deckSelect.querySelectorAll("option").forEach(option => {
                    option.hidden = false;
                });
                deckSelect.value = "";
            }
        }

        // Escuchar el cambio en el select de modalidad
        modeSelect.addEventListener("change", updateDeckOptions);

        // Llamar a la función al cargar la página en caso de que ya haya un valor seleccionado
        updateDeckOptions();
    });
</script>
@endsection
