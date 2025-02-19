@extends('layouts.app')

@section('styles')
<style>
.fondo-database {
    background-image: linear-gradient(to right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/../images/FONDO_BX.webp') !important;
    width: 100vw;
    background-size: cover !important;
    background-position: center right !important;
    background-repeat: no-repeat;
}

.blade-container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
    display: flex;
    gap: 20px;
    align-items: center; /* Alinea verticalmente */
    flex-wrap: wrap; /* Permite que los elementos se acomoden en diferentes líneas si es necesario */
}

.blade-description {
    flex: 1;
    padding: 20px;
    color: #fff;
    text-align: left; /* Mantiene el texto alineado a la izquierda */
}

.blade-info-box {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: url('/../images/GRID.webp') no-repeat center center;
    background-size: 100% 100%;
    padding: 20px;
    color: #fff;
    min-height: auto;
    text-align: left; /* Mantiene el texto alineado a la izquierda */
}

.blade-info-box img {
    width: 250px;
    height: auto;
    border-radius: 8px;
    margin: 0 auto 15px auto; /* Centra la imagen horizontalmente */
    display: block;
}

.blade-info-box p {
    text-align: left; /* Asegura que los párrafos estén alineados a la izquierda */
    width: 100%; /* Ocupa todo el ancho disponible */
}

/* Responsividad para pantallas menores a 768px (tablets y móviles) */
@media (max-width: 768px) {
    .blade-container {
        flex-direction: column; /* Cambia a diseño en columna */
        align-items: center; /* Centra los elementos */
        text-align: center; /* Centra el texto en móviles */
    }

    .blade-description {
        text-align: center; /* Centra el texto en móviles */
    }

    .blade-info-box {
        text-align: center; /* Centra los textos en el cuadro de la derecha */
    }

    .blade-info-box p {
        text-align: center; /* Asegura que los párrafos estén centrados */
    }
}

/* Responsividad para pantallas menores a 480px (móviles pequeños) */
@media (max-width: 480px) {
    .blade-container {
        padding: 10px;
    }

    .blade-info-box img {
        width: 200px; /* Reduce el tamaño de la imagen */
    }
}

</style>
@include('database.partials.mainmenu-styles') <!-- Estilos del menú -->
@endsection

@section('content')
<div class="container">
    <!-- Fila con el menú a anchura completa y centrado -->
    <div class="row">
        <div class="menu-container">
            @include('database.partials.mainmenu') <!-- Aquí se incluye el partial del menú -->
        </div>
    </div>

    <!-- Fila con el contenido del blade -->
    <div class="row m-4">
        <div class="blade-description">
            <h2>{{ $bit->nombre ?? 'Nombre no disponible' }} ({{ $bit->abreviatura ?? 'No especificado' }})</h2>
            <p><strong>Descripción:</strong></p>
            <p>{!! $bit->descripcion ?? 'No hay descripción disponible.' !!}</p>
            <p><strong>Análisis:</strong></p>
            <p>{!! $bit->analisis ?? 'No hay análisis disponible.' !!}</p>
        </div>
        <div class="blade-info-box">
            <img src="{{ $bit->imagen }}" alt="{{ $bit->nombre }}">
            <p>
                {{ $bit->marca_takara ? '✔️ Takara Tomy' : '❌ Takara Tomy' }}
                {{ $bit->marca_hasbro ? '✔️ Hasbro' : '❌ Hasbro' }}
            </p>
            <p><strong>Altura:</strong> {{ $bit->altura ?? 'No especificado' }}</p>
            <p><strong>Color:</strong> {{ $bit->color ?? 'No especificado' }}</p>
            <p><strong>Tipo:</strong> {{ $bit->tipo ?? 'No especificado' }}</p>
            <p><strong>Wave Hasbro:</strong> {{ $bit->wave_hasbro ?? 'No disponible' }}</p>
            <p><strong>Fecha de lanzamiento (Takara Tomy):</strong>
                {{ \Carbon\Carbon::parse($bit->fecha_takara)->format('d/m/Y') ?? 'No disponible' }}
            </p>
        </div>
    </div>
</div>
@endsection
