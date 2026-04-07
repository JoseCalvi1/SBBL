@extends('layouts.app')

@section('title', 'Añadir Duelo de Equipos')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    /* --- ARREGLO DE SELECT2 (Modo Oscuro / Shonen) --- */
    .select2-container { width: 100% !important; }
    .select2-dropdown { z-index: 999999 !important; background-color: #000 !important; border: 2px solid var(--shonen-cyan) !important; border-radius: 0 !important; }
    .select2-container .select2-selection--single {
        background-color: #111 !important;
        border: 2px solid #444 !important;
        border-radius: 0 !important;
        height: 45px !important;
        display: flex;
        align-items: center;
    }
    .select2-container .select2-selection__rendered { color: #fff !important; font-weight: bold; font-size: 1.1rem; }
    .select2-container .select2-selection__arrow { height: 45px !important; }
    .select2-search__field { background-color: #222 !important; color: #fff !important; border: 1px solid #555 !important; border-radius: 0 !important; }
    .select2-results__option { color: #fff !important; background-color: #000 !important; font-weight: bold; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: var(--shonen-cyan) !important; color: #000 !important; }

    /* --- INPUTS OSCUROS --- */
    .form-control-shonen {
        background-color: #111 !important;
        border: 2px solid #444 !important;
        color: #fff !important;
        border-radius: 0 !important;
        height: 45px;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .form-control-shonen:focus {
        border-color: var(--shonen-cyan) !important;
        box-shadow: 0 0 0 0.25rem rgba(0, 255, 204, 0.25) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 mb-5">

    <div class="mb-4">
        <a href="{{ route('teams_versus.all') }}" class="btn-shonen btn-shonen-warning">
            <span><i class="fas fa-arrow-left me-2"></i> VOLVER AL REGISTRO</span>
        </a>
    </div>

    <div class="text-center mb-5">
        <h2 class="font-bangers" style="font-size: 3.5rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);">
            <i class="fas fa-khanda me-2 text-white" style="text-shadow:none;"></i> AÑADIR NUEVO DUELO
        </h2>
        <p class="text-white fw-bold fs-5">Registra un nuevo enfrentamiento de equipos en la base de datos.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-10">
            <div class="command-panel p-4 p-md-5" style="background: rgba(0,0,0,0.6); border: 2px solid var(--shonen-cyan);">

                <form method="POST" action="{{ route('teams_versus.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    @if(session('error'))
                        <div class="alert alert-shonen alert-shonen-danger mb-4 text-center">
                            <div><i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}</div>
                        </div>
                    @endif

                    {{-- EQUIPO 1 --}}
                    <div class="row g-3 mb-4 p-3 border border-secondary" style="background: rgba(255,255,255,0.02);">
                        <div class="col-md-9">
                            <label for="team_id_1" class="text-white font-bangers fs-4 mb-2" style="color: var(--shonen-cyan) !important;">1. SELECCIONAR EQUIPO LOCAL</label>
                            <select name="team_id_1" id="team_id_1" class="form-control select2 @error('team_id_1') is-invalid @enderror">
                                <option disabled selected>- Buscar equipo -</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id_1') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                            @error('team_id_1')
                                <span class="text-danger fw-bold mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="result_1" class="text-white font-bangers fs-4 mb-2">PUNTUACIÓN 1</label>
                            <input type="number" name="result_1" id="result_1" class="form-control form-control-shonen text-center @error('result_1') is-invalid @enderror" placeholder="0" value="{{ old('result_1') }}">
                            @error('result_1')
                                <span class="text-danger fw-bold mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- VS ICON --}}
                    <div class="text-center mb-4">
                        <span class="font-bangers" style="font-size: 2.5rem; color: var(--shonen-red); text-shadow: 2px 2px 0 #000;">VS</span>
                    </div>

                    {{-- EQUIPO 2 --}}
                    <div class="row g-3 mb-4 p-3 border border-secondary" style="background: rgba(255,255,255,0.02);">
                        <div class="col-md-9">
                            <label for="team_id_2" class="text-white font-bangers fs-4 mb-2" style="color: var(--sbbl-gold) !important;">2. SELECCIONAR EQUIPO VISITANTE</label>
                            <select name="team_id_2" id="team_id_2" class="form-control select2 @error('team_id_2') is-invalid @enderror">
                                <option disabled selected>- Buscar equipo -</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('team_id_2') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                            @error('team_id_2')
                                <span class="text-danger fw-bold mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="result_2" class="text-white font-bangers fs-4 mb-2">PUNTUACIÓN 2</label>
                            <input type="number" name="result_2" id="result_2" class="form-control form-control-shonen text-center @error('result_2') is-invalid @enderror" placeholder="0" value="{{ old('result_2') }}">
                            @error('result_2')
                                <span class="text-danger fw-bold mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- DATOS EXTRA --}}
                    <div class="row g-3 mb-4 mt-5">
                        <div class="col-md-8">
                            <label for="url" class="text-white font-bangers fs-5 mb-2"><i class="fab fa-youtube text-danger me-2"></i>ENLACE DEL VÍDEO</label>
                            <input type="url" name="url" id="url" class="form-control form-control-shonen @error('url') is-invalid @enderror" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('url') }}">
                            @error('url')
                                <span class="text-danger fw-bold mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="modalidad" class="text-white font-bangers fs-5 mb-2"><i class="fas fa-gamepad text-secondary me-2"></i>MODALIDAD</label>
                            <select name="modalidad" id="modalidad" class="form-control form-control-shonen @error('modalidad') is-invalid @enderror">
                                <option selected value="beybladex">BEYBLADE X</option>
                            </select>
                            @error('modalidad')
                                <span class="text-danger fw-bold mt-2 d-block"><i class="fas fa-exclamation-circle me-1"></i>{{$message}}</span>
                            @enderror
                        </div>
                    </div>

                    <hr class="border-secondary my-5">

                    <div class="text-end">
                        <button type="submit" class="btn-shonen w-100 text-center" style="background: var(--shonen-cyan); color: #000; padding: 15px; font-size: 1.5rem;">
                            <span><i class="fas fa-save me-2"></i> REGISTRAR DUELO DE EQUIPOS</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "- Buscar equipo -",
                width: '100%'
            });
        });
    </script>
@endsection
