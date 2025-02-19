@extends('layouts.app')

@section('styles')
<style>
.fondo-database {
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

/* Responsividad para tablets y móviles */
@media (max-width: 768px) {
    .blade-container {
        flex-direction: column; /* Apila los elementos */
        text-align: center;
    }

    .blade-description {
        text-align: center;
    }

    .blade-info-box {
        text-align: center;
        padding: 15px;
    }

    .blade-info-box img {
        width: 200px; /* Reduce tamaño de imagen */
    }

    /* Ajusta la disposición de los bloques de Blade, Ratchet y Bit */
    .d-flex.align-items-center {
        flex-direction: column;
        text-align: center;
    }

    .flex-grow-1 {
        text-align: center !important;
    }

    .text-center {
        text-align: center !important;
    }

    .badge {
        display: block;
        margin: auto;
    }

    .text-right {
        text-align: center !important;
    }

    .mt-2 img {
        margin: auto;
    }
}

/* Para móviles más pequeños */
@media (max-width: 480px) {
    .blade-container {
        padding: 10px;
    }

    .blade-info-box img {
        width: 180px;
    }

    .badge {
        font-size: 0.9rem; /* Reduce tamaño del badge */
    }

    h2 {
        font-size: 1.5rem; /* Reduce tamaño de los títulos */
    }

    p {
        font-size: 0.9rem;
    }

    .mt-2 img {
        width: 60px; /* Reduce el tamaño del botón de info */
    }
}


</style>
@include('database.partials.mainmenu-styles') <!-- Estilos del menú -->
@endsection

@section('content')
<div class="container text-white">
    <!-- Fila con el menú a anchura completa y centrado -->
    <div class="row">
        <div class="menu-container">
            @include('database.partials.mainmenu') <!-- Aquí se incluye el partial del menú -->
        </div>
    </div>

    <!-- Fila con el contenido del blade -->
    <div class="row m-4">
        <div class="col-md-8">
            <p><h1>{{ $beyblade->blade_nombre }}</h1></p>
            <p><h1>{{ $beyblade->ratchet_nombre }} {{ $beyblade->bit_nombre }}</h1></p>
            <p><strong>Descripción:</strong> {!! $beyblade->descripcion !!}</p>
        </div>
        <div class="col-md-4">
            @if ($beyblade->imagen)
                <img width="350" src="{{ $beyblade->imagen }}" alt="Imagen de la Beyblade">
            @endif
        </div>
    </div>
    <div class="row m-4" style="padding:0 3em 0 3em;">

        <div class="col-md-12 p-5" style="background: url('/../images/GRID.webp') no-repeat center center; background-size: 100% 100%;">
            <div class="d-flex align-items-center mb-2">
                <div class="text-center mr-3">
                    <img width="150" src="{{ $beyblade->sistema == 'BX' ? url('/images/sbbl_bx_emoji.png') : url ('/images/sbbl_ux_emoji.png') }}" alt="Imagen del blade" class="ms-3">
                </div>
                <div class="flex-grow-1 text-end">
                    <span class="badge px-3 py-1 text-white" style="background-color: @if($beyblade->sistema == 'BX') #71bce9 @else #ee7800 @endif; font-size: 1rem;">BLADE</span>
                    <h2 class="mt-2">{{ $beyblade->blade_nombre }}</h2>
                    <hr style="border: 2px solid @if($beyblade->sistema == 'BX') #71bce9 @else #ee7800 @endif">
                    <span style="font-size: large">{!! $beyblade->blade_descripcion !!}</span>
                </div>
                <div class="text-center">
                    <img width="250" src="{{ $beyblade->blade_imagen }}" alt="Imagen del blade" class="ms-3">
                    <div class="mt-2 text-right">
                        <a href="{{ route('database.showBlade', $beyblade->blade_id) }}" target="_blank">
                            <img src="{{ url('/../images/info.png') }}" alt="Info" style="height: 30px; width: 70px;">
                        </a>

                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-2">
                <div class="flex-grow-1 text-end">
                    <span class="badge px-3 py-1 text-white" style="background-color: @if($beyblade->sistema == 'BX') #71bce9 @else #ee7800 @endif; font-size: 1rem;">RATCHET</span>
                    <h2 class="mt-2">{{ $beyblade->ratchet_nombre }}</h2>
                    <hr style="border: 2px solid @if($beyblade->sistema == 'BX') #71bce9 @else #ee7800 @endif">
                    <span style="font-size: large">{!! $beyblade->ratchet_descripcion !!}</span>
                </div>
                <div class="text-center">
                    <img width="250" src="{{ $beyblade->ratchet_imagen }}" alt="Imagen del ratchet" class="ms-3">
                    <div class="mt-2 text-right">
                        <a href="{{ route('database.showRatchet', $beyblade->ratchet_id) }}" target="_blank">
                            <img src="{{ url('/../images/info.png') }}" alt="Info" style="height: 30px; width: 70px;">
                        </a>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center mb-2">
                <div class="flex-grow-1 text-end">
                    <span class="badge px-3 py-1 text-white" style="background-color: @if($beyblade->sistema == 'BX') #71bce9 @else #ee7800 @endif; font-size: 1rem;">BIT</span>
                    <h2 class="mt-2">{{ $beyblade->bit_nombre }}</h2>
                    <hr style="border: 2px solid @if($beyblade->sistema == 'BX') #71bce9 @else #ee7800 @endif">
                    <span style="font-size: large">{!! $beyblade->bit_descripcion !!}</span>
                </div>
                <div class="text-center">
                    <img width="250" src="{{ $beyblade->bit_imagen }}" alt="Imagen del bit" class="ms-3">
                    <div class="mt-2 text-right">
                        <a href="{{ route('database.showBit', $beyblade->bit_id) }}" target="_blank">
                            <img src="{{ url('/../images/info.png') }}" alt="Info" style="height: 30px; width: 70px;">
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-md-12 m-4">
            <p><strong>Análisis:</strong> {!! $beyblade->analisis !!}</p>
        </div>

    </div>
</div>
@endsection
