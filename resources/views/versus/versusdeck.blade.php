@extends('layouts.app')

@section('content')
<!-- Formulario normal sin modal -->
<div class="container-fluid text-white pt-4">
    <a href="{{ url()->previous() }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
        </svg>
        Volver
    </a>
    <div class="header">
        <h5 class="title">Resultados del deck en el duelo</h5>
    </div>
    <div class="body">
        @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
        <!-- Formulario dentro de la página -->
        <form method="POST" action="{{ route('versus.results.store', ['versusId' => $versus->id]) }}">
            @csrf

            @foreach($results as $index => $result)
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="blade_{{ $index + 1 }}">Blade</label>
                            <select class="form-control select2" id="blade_{{ $index + 1 }}" name="blade[]" required style="width: 100%">
                                <option>-- Selecciona un blade --</option>
                                @foreach($bladeOptions as $option)
                                    <option value="{{ $option }}" {{ $result->blade == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="assist_blade_{{ $index + 1 }}">Assist blade (Solo CX)</label>
                            <select class="form-control select2" id="assist_blade_{{ $index + 1 }}" name="assist_blade[]" required style="width: 100%">
                                <option>-- Selecciona un ratchet --</option>
                                @foreach($assistBladeOptions as $option)
                                    <option value="{{ $option }}" {{ $result->assist_blade == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="ratchet_{{ $index + 1 }}">Ratchet</label>
                            <select class="form-control select2" id="ratchet_{{ $index + 1 }}" name="ratchet[]" required style="width: 100%">
                                <option>-- Selecciona un ratchet --</option>
                                @foreach($ratchetOptions as $option)
                                    <option value="{{ $option }}" {{ $result->ratchet == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="bit_{{ $index + 1 }}">Bit</label>
                            <select class="form-control select2" id="bit_{{ $index + 1 }}" name="bit[]" required style="width: 100%">
                                <option>-- Selecciona un bit --</option>
                                @foreach($bitOptions as $option)
                                    <option value="{{ $option }}" {{ $result->bit == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="victorias_{{ $index + 1 }}">Victorias</label>
                            <input type="number" class="form-control" id="victorias_{{ $index + 1 }}" name="victorias[]" value="{{ $result->victorias }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="derrotas_{{ $index + 1 }}">Derrotas</label>
                            <input type="number" class="form-control" id="derrotas_{{ $index + 1 }}" name="derrotas[]" value="{{ $result->derrotas }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="puntos_ganados_{{ $index + 1 }}">P.Ganados</label>
                            <input type="number" class="form-control" id="puntos_ganados_{{ $index + 1 }}" name="puntos_ganados[]" value="{{ $result->puntos_ganados }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="puntos_perdidos_{{ $index + 1 }}">P.Perdidos</label>
                            <input type="number" class="form-control" id="puntos_perdidos_{{ $index + 1 }}" name="puntos_perdidos[]" value="{{ $result->puntos_perdidos }}">
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('styles')
    <!-- CDN de Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            z-index: 9999; /* Asegúrate de que el select2 se muestra sobre otros elementos */
        }
    </style>
@endsection

@section('scripts')
    <!-- CDN de jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CDN de Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        jQuery(document).ready(function() {
            // Inicializa Select2 cuando el documento esté listo
            jQuery('.select2').select2({
                dropdownParent: $("#formModal")
            });

            // Inicializa Select2 cuando el modal se muestra
            jQuery('#formModal').on('shown.bs.modal', function () {
                jQuery('.select2').select2(); // Re-inicializa Select2
            });
        });
    </script>
    <!-- Incluye el script para inicializar los tooltips y otros componentes de Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection
