@extends('layouts.app')

@section('title', 'Nacional 2026 - Procesando...')

@section('styles')
<style>
    .memory-container {
        min-height: 70vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .memory-title {
        font-family: 'Oswald', cursive;
        font-size: 3.5rem;
        color: #fff;
        letter-spacing: 3px;
        text-transform: uppercase;
        text-shadow: 2px 2px 0 #000;
        margin-top: 20px;
        text-align: center;
    }

    .spinner-icon {
        color: var(--shonen-cyan);
        font-size: 5rem;
        animation: spin-slow 3s infinite linear;
        filter: drop-shadow(0 0 10px rgba(0, 255, 204, 0.5));
    }

    @keyframes spin-slow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container memory-container">

    <i class="fas fa-crosshairs spinner-icon"></i>

    <h1 class="memory-title">CARGANDO RECUERDOS...</h1>

    <p class="text-white-50 fw-bold fs-5 text-center mt-3" style="max-width: 600px;">
        La misión 2026 sigue en curso. El archivo multimedia, los resúmenes y la información clasificada del torneo se revelarán en cuanto la batalla concluya.
    </p>

    <a href="{{ route('nacional.inicio') }}" class="btn-shonen btn-shonen-warning mt-5 px-4">
        <span><i class="fas fa-arrow-left me-2"></i> VOLVER AL PORTAL</span>
    </a>

</div>
@endsection
