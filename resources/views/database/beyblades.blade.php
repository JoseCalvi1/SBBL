@extends('layouts.app')

@section('styles')
@section('styles')
<style>
    .fondo-database {
        background-image: linear-gradient(to right, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('/../images/webTile2.png') !important;
    }
    /* Contenedor Principal */
    .blade-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: center;
    }

    /* Filtros */
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
        background: #222;
        border-radius: 10px;
        justify-content: center;
        text-align: center;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .filter-group span {
        font-weight: bold;
        color: #fff;
    }

    /* Estilo Moderno para Checkboxes */
    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: #ddd;
    }

    .custom-checkbox input {
        display: none;
    }

    .custom-checkbox .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid #ffcc00;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.2s;
    }

    .custom-checkbox input:checked + .checkmark {
        background-color: #ffcc00;
    }

    .custom-checkbox .checkmark::after {
        content: '✓';
        font-size: 16px;
        color: black;
        display: none;
    }

    .custom-checkbox input:checked + .checkmark::after {
        display: block;
    }

    /* Botón de Filtro */
    .filter-container button {
        background: #ffcc00;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        color: #222;
        font-weight: bold;
    }

    /* Diseño de los Blades */
    .system-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        padding: 15px;
        border-bottom: 2px solid #444;
    }
    @media (max-width: 768px) {
    .system-row {
        grid-template-columns: repeat(1, 1fr);
    }
}


    .blade-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 2px 2px 15px rgba(255, 255, 255, 0.1);
    }

    .blade-item img {
        width: 100%;
        height: auto;
        border-radius: 5px;
    }
</style>
@include('database.partials.mainmenu-styles')
@endsection

@section('content')
<div class="container-fluid blade-container">
    <div class="row" style="justify-content: center;">
        <div class="menu-container">
            @include('database.partials.mainmenu')
        </div>
    </div>

    <form method="GET" action="{{ route('database.beyblades') }}" class="filter-container">
        <div class="filter-group">
            <span>Marca:</span>
            <label class="custom-checkbox">
                <input type="checkbox" name="marca[]" value="takara"
                       {{ in_array('takara', request('marca', [])) ? 'checked' : '' }}>
                <span class="checkmark"></span> Takara
            </label>
            <label class="custom-checkbox">
                <input type="checkbox" name="marca[]" value="hasbro"
                       {{ in_array('hasbro', request('marca', [])) ? 'checked' : '' }}>
                <span class="checkmark"></span> Hasbro
            </label>
        </div>

        <div class="filter-group">
            <span>Tipo:</span>
            @foreach(['Ataque', 'Balance', 'Defensa', 'Energía'] as $tipo)
                <label class="custom-checkbox">
                    <input type="checkbox" name="tipo[]" value="{{ $tipo }}"
                           {{ in_array($tipo, request('tipo', [])) ? 'checked' : '' }}>
                    <span class="checkmark"></span> {{ $tipo }}
                </label>
            @endforeach
        </div>

        <div class="filter-group">
            <span>Sistema:</span>
            @foreach($sistemas as $sistema)
                <label class="custom-checkbox">
                    <input type="checkbox" name="sistema[]" value="{{ $sistema }}"
                           {{ in_array($sistema, request('sistema', [])) ? 'checked' : '' }}>
                    <span class="checkmark"></span> {{ $sistema }}
                </label>
            @endforeach
        </div>

        <button type="submit">Aplicar Filtro</button>
    </form>

    <div class="row" style="text-align: center;">
        <h2 style="color: white;">BEYBLADES</h2>
    </div>
    <div class="system-row">
        @foreach ($beyblades as $beyblade)
            <div class="blade-item">
                <a href="{{ route('database.showBey', $beyblade->id) }}">
                    <img src="{{ $beyblade->tarjeta }}" alt="{{ $beyblade->blade_nombre }}">
                </a>
            </div>
        @endforeach
    </div>

</div>
@endsection
