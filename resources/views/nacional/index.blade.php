@extends('layouts.app')

@section('title', 'Nacional SBBL')

@section('styles')
<style>
    .portal-bg {
        padding: 50px 20px;
        min-height: 80vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .main-title {
        font-size: clamp(2.5rem, 8vw, 4.5rem);
        color: #fff;
        text-align: center;
        letter-spacing: 3px;
        margin-bottom: 50px;
        text-shadow: 3px 3px 0px var(--shonen-red), 6px 6px 0px #000;
    }

    /* Ajustes extra para móviles */
    @media (max-width: 768px) {
        .main-title {
            margin-bottom: 30px;
            letter-spacing: 2px;
            text-shadow: 2px 2px 0px var(--shonen-red), 4px 4px 0px #000;
        }
        .year-title {
            font-size: 3rem; /* Las cajas de años también un poco más pequeñas en móvil */
        }
        .mission-card {
            padding: 30px 15px; /* Menos relleno para que quepan mejor */
        }
    }

    /* Estructura base para ambas tarjetas */
    .mission-card {
        background: rgba(0, 0, 0, 0.7);
        border: 4px solid #444;
        padding: 50px 20px;
        transform: skewX(-2deg);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
    }
    .mission-card:hover {
        transform: translateY(-5px) skewX(-2deg);
    }

    .year-title {
        font-family: 'Oswald', sans-serif;
        font-size: 4rem;
        color: #fff;
        margin-bottom: 10px;
        line-height: 1;
    }

    /* Énfasis Tarjeta 2026 (Actual) */
    .mission-active {
        border-color: var(--shonen-cyan);
        box-shadow: 0 0 20px rgba(0, 255, 204, 0.15);
        background: linear-gradient(180deg, rgba(0, 255, 204, 0.05) 0%, rgba(0,0,0,0.8) 100%);
    }
    .mission-active:hover {
        box-shadow: 0 0 30px rgba(0, 255, 204, 0.4);
    }
    .badge-active {
        position: absolute;
        top: 15px; right: 15px;
        background: var(--shonen-red);
        color: #fff;
        font-family: 'Oswald', sans-serif;
        padding: 5px 15px;
        font-size: 1rem;
        letter-spacing: 1px;
        transform: skewX(5deg);
        box-shadow: 2px 2px 0 #000;
    }

    /* Estilo Tarjeta 2025 (Archivo) */
    .mission-archive {
        border-color: var(--sbbl-gold);
        filter: grayscale(0.4);
    }
    .mission-archive:hover {
        filter: grayscale(0);
        box-shadow: 0 0 25px rgba(255, 193, 7, 0.3);
    }
</style>
@endsection

@section('content')
<div class="portal-bg">
    <div class="container">
        <h1 class="main-title">PORTAL DEL NACIONAL</h1>

        <div class="row g-4 justify-content-center align-items-stretch">

            {{-- HISTÓRICO: 2025 --}}
            <div class="col-md-6 col-lg-5">
                <div class="mission-card mission-archive">
                    <h2 class="year-title">2025</h2>
                    <p class="text-white-50 fw-bold mb-4 fs-5">EL GRAN NACIONAL</p>
                    <a href="{{ route('nacional.show_2025') }}" class="btn-shonen btn-shonen-warning w-75 text-center">
                        <span><i class="fas fa-folder-open me-2"></i> VER EVENTO</span>
                    </a>
                </div>
            </div>

            {{-- MISIÓN ACTUAL: 2026 --}}
            <div class="col-md-6 col-lg-5">
                <div class="mission-card mission-active">
                    <div class="badge-active">AÑO EN CURSO</div>
                    <h2 class="year-title">2026</h2>
                    <p class="text-info fw-bold mb-4 fs-5">LA BEYCON</p>
                    <a href="{{ route('nacional.show_2026') }}" class="btn-shonen w-75 text-center" style="background: var(--shonen-cyan); color: #000; border-color:#000;">
                        <span><i class="fas fa-bolt me-2"></i> ENTRAR</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
