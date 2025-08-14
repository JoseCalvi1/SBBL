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
/* Resaltar avatares exclusivos */
.exclusive-section-bronze {
    margin-top: 30px;
    margin-bottom: 30px;
    border: 3px solid #CD7F32;  /* Borde dorado */
    box-shadow: 0 0 15px #CD7F32; /* Sombra dorada */
    transform: scale(1.05); /* Aumentar tamaño ligeramente */
    transition: all 0.3s ease; /* Transición suave */
}
.exclusive-section-silver {
    margin-top: 30px;
    margin-bottom: 30px;
    border: 3px solid #c0e5fb;  /* Borde dorado */
    box-shadow: 0 0 15px #c0e5fb; /* Sombra dorada */
    transform: scale(1.05); /* Aumentar tamaño ligeramente */
    transition: all 0.3s ease; /* Transición suave */
}
.exclusive-section-gold {
    margin-top: 30px;
    margin-bottom: 30px;
    border: 3px solid gold;  /* Borde dorado */
    box-shadow: 0 0 15px rgba(255, 223, 0, 0.7); /* Sombra dorada */
    transform: scale(1.05); /* Aumentar tamaño ligeramente */
    transition: all 0.3s ease; /* Transición suave */
}
h4 {
    font-size: 1.25rem;
    font-weight: bold;
    color: #FFD700;  /* Dorado */
    text-align: center;
    margin-top: 20px;
}
</style>
@endsection

@section('content')
@if (Auth::user()->profile->id == $profile->id || Auth::user()->is_admin)
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

            <div class="form-group mt-2">
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

                @php
                    // Determinar la clase CSS según el nivel de suscripción
                    $firstTrophyName = $profile->trophies->first()->name ?? '';
                    switch ($firstTrophyName) {
                        case 'SUSCRIPCIÓN NIVEL 3':
                            $subscriptionClass = 'suscripcion-nivel-3';
                            break;
                        case 'SUSCRIPCIÓN NIVEL 2':
                        case 'SUSCRIPCIÓN NIVEL 1':
                            $subscriptionClass = 'suscripcion';
                            break;
                        default:
                            $subscriptionClass = '';
                            break;
                    }

                @endphp

                    @if ($subscriptionClass == "suscripcion")
                    <div class="form-group mt-2">
                        <label for="subtitulo">Opción personalizada</label>
                        <select name="subtitulo" id="subtitulo" class="form-control @error('subtitulo') is-invalid @enderror">
                            <option value="" selected>- Selecciona una opción -</option>

                            @php
                                $subtitulos = [
                                    "Burst Timidín", "Custom fanboy", "Maestro del Beyblade", "Lloriquín",
                                    "Wizard Rod Destroyer", "SBBL Fraud", "Liga de Coña de Beyblade",
                                    "SlipGrip fangirl", "Trabajando…", "Beytakl Enjoyer", "Blader solitari@",
                                    "It is what it is", "Otro día más en la oficina", "Tocho", "Blader senil",
                                    "WizardLloros", "Beynito Villamarín", "Ratchet Pizjuan",
                                    "Dinosaurios Chad", "Supersonic Acrobatic Rocket-Powered Battle Beys",
                                    "Colormaxxing", "Brainrot"
                                ];
                                $limit = $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 1' ? 10 : count($subtitulos);
                            @endphp

                            @foreach (array_slice($subtitulos, 0, $limit) as $subtitulo)
                                <option value="{{ $subtitulo }}" @if ($profile->subtitulo == $subtitulo) selected @endif>
                                    {{ $subtitulo }}
                                </option>
                            @endforeach

                        </select>
                        @error('subtitulo')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    @elseif ($subscriptionClass == "suscripcion-nivel-3")
                    <div class="form-group mt-2">
                        <label for="subtitulo">Subtítulo</label>
                        <input type="text"
                            name="subtitulo"
                            class="form-control @error('subtitulo') is-invalid @enderror"
                            id="subtitulo"
                            placeholder="Subtítulo"
                            value="{{ $profile->subtitulo }}"
                        />
                        @error('subtitulo')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{$message}}</strong>
                            </span>
                        @enderror
                    </div>
                    @endif

            <div class="form-group mt-2">
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

            <div class="form-group mt-2 form-check">
                <input
                    type="checkbox"
                    class="form-check-input"
                    id="free_agent"
                    name="free_agent"
                    value="1"
                    {{ old('free_agent', $profile->free_agent) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="free_agent">Estoy buscando equipo</label>
            </div>


            <!-- Avatar -->
            <div class="form-group mt-2">
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
                                    <input type="radio" name="default_img" value="{{ $key }}" @if ($profile->imagen == $avatar) checked @endif/>
                                    <img src="/storage/{{ $avatar }}" class="img-fluid" />
                                </label>
                            </div>
                        @endforeach

                        <!-- Resaltar la sección de avatares exclusivos -->
                        @if ($subscriptionLevel == 'SUSCRIPCIÓN NIVEL 1' || $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 2' || $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 3')
                            <div class="exclusive-section-bronze">
                            <h4>AVATARES EXCLUSIVOS NIVEL 1</h4>
                                <div class="row">
                                    @foreach ($bronzeAvatars as $key => $avatar)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="default_img" value="{{ $key }}" @if ($profile->imagen == $avatar) checked @endif/>
                                                <img src="/storage/{{ $avatar }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($subscriptionLevel == 'SUSCRIPCIÓN NIVEL 2' || $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 3')
                            <div class="exclusive-section-silver">
                            <h4>AVATARES EXCLUSIVOS NIVEL 2</h4>
                                <div class="row">
                                    @foreach ($silverAvatars as $key => $avatar)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="default_img" value="{{ $key }}" @if ($profile->imagen == $avatar) checked @endif/>
                                                <img src="/storage/{{ $avatar }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($subscriptionLevel == 'SUSCRIPCIÓN NIVEL 3')
                            <div class="exclusive-section-gold">
                            <h4>AVATARES EXCLUSIVOS NIVEL 3</h4>
                                <div class="row">
                                    @foreach ($goldAvatars as $key => $avatar)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="default_img" value="{{ $key }}" @if ($profile->imagen == $avatar) checked @endif/>
                                                <img src="/storage/{{ $avatar }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>


                @error('default_img')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Marco -->
            <div class="form-group mt-2">
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
                        <!-- Resaltar la sección de avatares exclusivos -->
                        @if ($subscriptionLevel == 'SUSCRIPCIÓN NIVEL 1' || $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 2' || $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 3')
                            <div class="exclusive-section-bronze">
                            <h4>MARCOS EXCLUSIVOS NIVEL 1</h4>
                                <div class="row">
                                    @foreach ($marcoBronce as $key => $marco)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="marco" value="{{ $key }}" @if ($profile->marco == $marco) checked @endif/>
                                                <img src="/storage/{{ $marco }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($subscriptionLevel == 'SUSCRIPCIÓN NIVEL 2' || $subscriptionLevel == 'SUSCRIPCIÓN NIVEL 3')
                            <div class="exclusive-section-silver">
                            <h4>MARCOS EXCLUSIVOS NIVEL 2</h4>
                                <div class="row">
                                    @foreach ($marcoPlata as $key => $marco)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="marco" value="{{ $key }}" @if ($profile->marco == $marco) checked @endif/>
                                                <img src="/storage/{{ $marco }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($subscriptionLevel == 'SUSCRIPCIÓN NIVEL 3')
                            <div class="exclusive-section-gold">
                            <h4>MARCOS EXCLUSIVOS NIVEL 3</h4>
                                <div class="row">
                                    @foreach ($marcoOro as $key => $marco)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="marco" value="{{ $key }}" @if ($profile->marco == $marco) checked @endif/>
                                                <img src="/storage/{{ $marco }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($copaLloros || Auth::user()->is_admin)
                            <div class="exclusive-section-gold">
                            <h4>AVATARES COPAS EXCLUSIVAS</h4>
                                <div class="row">
                                    @foreach ($copaLlorosAvatar as $key => $avatar)
                                        <div class="col-md-2 col-options">
                                            <label>
                                                <input type="radio" name="default_img" value="{{ $key }}" @if ($profile->imagen == $avatar) checked @endif/>
                                                <img src="/storage/{{ $avatar }}" class="img-fluid" loading="lazy" />
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @error('marco')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Fondo de tarjeta -->
            <div class="form-group mt-2">
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
@endif
@endsection

@section('scripts')
    <script>
        function toggleOptions(containerId) {
            var container = document.getElementById(containerId);
            container.classList.toggle('show');
        }
    </script>
@endsection
