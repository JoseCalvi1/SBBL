@extends('layouts.app')

@section('title', 'SBBL Wrapped')

@section('styles')
<style>
    /* General */
    body {
        color: white;
        text-align: center;
        margin: 0;
        padding: 0;
        background-color: #121212;
    }

    /* Sección principal */
    .wrapped {
        width: 100%;
        max-width: 800px;
        margin: auto;
        padding: 3rem 2rem;
        border-radius: 12px;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.7);
        background: linear-gradient(135deg, #6a11cb, #2575fc); /* Gradiente moderno de colores */
    }

    /* Fondo Oscurecido */
    .background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Filtro oscuro */
        filter: brightness(0.2);
        opacity: 0.8;
    }

    /* Contenido principal */
    .content {
        position: relative;
        z-index: 1;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    }

    /* Títulos y Párrafos */
    h1 {
        font-size: 2.5rem;
        font-weight: bold;
        color: #ffc107;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }

    p, .stat {
        font-size: 1.1rem;
        line-height: 1.8rem;
        margin: 1.5rem 0;
    }

    /* Botón de descarga */
    .btn-download {
        display: inline-block;
        width: 100%;
        margin-top: 2rem;
        padding: 1rem 2rem;
        background: linear-gradient(90deg, #ffc107, #ff8800);
        color: #121212;
        font-size: 1.2rem;
        font-weight: bold;
        text-decoration: none;
        text-transform: uppercase;
        transition: background 0.3s ease, transform 0.2s ease;
        text-align: center;
    }

    .btn-download:hover {
        background: linear-gradient(90deg, #ff8800, #ffc107);
    }

    /* Responsividad */
    @media (max-width: 768px) {
        .wrapped {
            padding: 2rem 1rem;
        }

        h1 {
            font-size: 2rem;
        }

        p, .stat {
            font-size: 1rem;
        }

        .btn-download {
            padding: 1rem 1.5rem;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .wrapped {
            padding: 1.5rem;
        }

        h1 {
            font-size: 1.5rem;
        }

        p, .stat {
            font-size: 0.9rem;
        }

        .btn-download {
            padding: 0.8rem 1.2rem;
            font-size: 0.9rem;
        }
    }
</style>
@endsection

@section('content')
<div class="wrapped mt-4 mb-4" id="wrapped">
    <div class="background"></div>
    <div class="content">
        <h1>¡Hola, {{ strtoupper($profile->user->name) }}!</h1>
        <p>¡Gracias por formar parte de nuestra liga! Aquí te traemos tu <b>SBBL Wrapped</b> de media temporada. Ha sido una aventura increíble hasta ahora y hemos vivido muchas cosas en solo medio año.</p>

        @if($datosTorneo)
            <div class="stat">🌟 <b>Primer torneo:</b> {{ $datosTorneo->name }} ({{ $datosTorneo->region->name }})</div>
        @else
            <div class="stat">🌟 <b>Primer torneo:</b> No disponible</div>
        @endif

        <div class="stat">🏆 <b>Torneos jugados:</b> {{ $numeroTorneos }} | <b>Ganados:</b> {{ $torneosGanados }}</div>

        @if($mejorCombo)
            <div class="stat">💥 <b>Combo estrella:</b> {{ $mejorCombo->blade }} {{ $mejorCombo->ratchet }} {{ $mejorCombo->bit }} <br>
                🔥 <b>Puntos ganados:</b> {{ $mejorCombo->total_puntos_ganados }}
            </div>
        @else
            <div class="stat">💥 <b>Combo estrella:</b> No disponible</div>
        @endif

        @if($peorCombo)
            <div class="stat">😓 <b>Combo desafortunado:</b> {{ $peorCombo->blade }} {{ $peorCombo->ratchet }} {{ $peorCombo->bit }} <br>
                ❌ <b>Puntos perdidos:</b> {{ $peorCombo->total_puntos_perdidos }}
            </div>
        @else
            <div class="stat">😓 <b>Combo desafortunado:</b> No disponible</div>
        @endif

        <p>Gracias por acompañarnos en esta aventura. ¡Felices fiestas y nos vemos en la próxima batalla!</p>
    </div>
</div>

<a href="#" class="btn-download" onclick="downloadImage()">Descargar Imagen</a>
@endsection

@section('scripts')

@endsection
