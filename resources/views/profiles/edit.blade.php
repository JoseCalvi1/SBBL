@extends('layouts.app')

@section('styles')
<style>
.optionsContainer {
    display: none;
}

.optionsContainer.show {
    display: block;
}

/* Estilos adicionales para hacer el diseño responsive */
@media (max-width: 768px) {
    .col-options {
        flex: 0 0 33.33%;
        max-width: 33.33%;
    }
}
@media (max-width: 576px) {
    .col-options {
        flex: 0 0 50%;
        max-width: 50%;
    }
}
</style>
@endsection

@section('content')

<a href="{{ route('profiles.show', $profile) }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
    <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
    </svg>
    Volver
</a>

<h1 class="text-center mt-2 text-white">Editar mi perfil</h1>

<div class="row justify-content-center mt-5 p-2">
    <div class="col-lg-8 col-md-10 col-sm-12">
        <form action="{{ route('profiles.update', ['profile' => $profile->id]) }}" method="POST" enctype="multipart/form-data" style="color: white">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text"
                    name="nombre"
                    class="form-control @error('nombre') is-invalid @enderror"
                    id="nombre"
                    placeholder="Tu nombre"
                    value="{{ $profile->user->name }}"
                />
                @error('nombre')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{$message}}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <label for="region_id">Región</label>
                <select name="region_id" id="region_id" class="form-control @error('nombre') is-invalid @enderror">
                    @if ($regionT)
                        <option value="{{ $regionT->id }}">{{ $regionT->name }}</option>
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

            <!-- Avatar -->
            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center mb-2" style="cursor: pointer;" onclick="toggleOptions('avatarOptions')">
                    <label for="default_img">Avatar</label>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div id="avatarOptions" class="optionsContainer">
                    <div class="row">
                        @foreach ($avatarOptions as $key => $avatar)
                            <div class="col-md-2 col-options">
                                <label>
                                    <input type="radio" name="default_img" value="{{ $key }}"
                                           @if ($profile->imagen == $avatar) checked @endif/>
                                    <img src="/storage/{{ $avatar }}" class="img-fluid" />
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('default_img')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Marco -->
            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center mb-2" style="cursor: pointer;" onclick="toggleOptions('marcoOptions')">
                    <label for="marco">Marco de avatar</label>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div id="marcoOptions" class="optionsContainer">
                    <div class="row">
                        @foreach ($marcoOptions as $key => $marco)
                            <div class="col-md-2 col-options">
                                <label>
                                    <input type="radio" name="marco" value="{{ $marco }}"
                                           @if ($profile->marco == $marco) checked @endif/>
                                    <img src="/storage/{{ $marco }}" class="img-fluid" />
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('marco')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Fondo de tarjeta -->
            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center mb-2" style="cursor: pointer;" onclick="toggleOptions('fondoOptions')">
                    <label for="fondo">Fondo de tarjeta</label>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div id="fondoOptions" class="optionsContainer">
                    <div class="row">
                        @foreach ($fondoOptions as $key => $fondo)
                            <div class="col-md-3 col-options">
                                <label>
                                    <input type="radio" name="fondo" value="{{ $fondo }}"
                                           @if ($profile->fondo == $fondo) checked @endif/>
                                    <img src="/storage/{{ $fondo }}" class="img-fluid" alt="{{ $key }}" />
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @error('fondo')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Actualizar perfil">
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        function toggleOptions(containerId) {
            var container = document.getElementById(containerId);
            container.classList.toggle('show');
        }
    </script>
@endsection
