@extends('layouts.app')

@section('content')
<div class="container-fluid text-white pt-4">
    <a href="{{ route('versus.all') }}" class="btn btn-outline-primary m-4 text-uppercase font-weight-bold">
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

        <form method="POST" action="{{ route('versus.results.store', ['versusId' => $versus->id]) }}">
            @csrf

            @foreach($results as $index => $result)
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="blade_{{ $index + 1 }}">Blade</label>
                            <select class="form-control select2" id="blade_{{ $index + 1 }}" name="blade[]" required>
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
                            <select class="form-control select2" id="assist_blade_{{ $index + 1 }}" name="assist_blade[]">
                                <option></option>
                                @foreach($assistBladeOptions as $option)
                                    <option value="{{ $option }}" {{ $result->assist_blade == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="ratchet_{{ $index + 1 }}">Ratchet</label>
                            <select class="form-control select2" id="ratchet_{{ $index + 1 }}" name="ratchet[]" required>
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
                            <select class="form-control select2" id="bit_{{ $index + 1 }}" name="bit[]" required>
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
                            <small id="ganados_rango_{{ $index + 1 }}" class="form-text text-muted"></small>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="puntos_perdidos_{{ $index + 1 }}">P.Perdidos</label>
                            <input type="number" class="form-control" id="puntos_perdidos_{{ $index + 1 }}" name="puntos_perdidos[]" value="{{ $result->puntos_perdidos }}">
                            <small id="perdidos_rango_{{ $index + 1 }}" class="form-text text-muted"></small>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            z-index: 9999;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        jQuery(function () {
            jQuery('[data-toggle="tooltip"]').tooltip()
        });

        jQuery(document).ready(function () {
            jQuery('.select2').select2();

            @foreach($results as $index => $result)
                (function () {
                    const victoriasInput = jQuery('#victorias_{{ $index + 1 }}');
                    const derrotasInput = jQuery('#derrotas_{{ $index + 1 }}');
                    const puntosGanadosInput = jQuery('#puntos_ganados_{{ $index + 1 }}');
                    const puntosPerdidosInput = jQuery('#puntos_perdidos_{{ $index + 1 }}');
                    const rangoGanados = jQuery('#ganados_rango_{{ $index + 1 }}');
                    const rangoPerdidos = jQuery('#perdidos_rango_{{ $index + 1 }}');

                    function updateGanadosLimits() {
                        const victorias = parseInt(victoriasInput.val()) || 0;
                        const min = victorias;
                        const max = victorias * 3;
                        puntosGanadosInput.attr('min', min);
                        puntosGanadosInput.attr('max', max);
                        rangoGanados.text(`Rango permitido: ${min} - ${max}`);
                        //validateInput(puntosGanadosInput, min, max);
                    }

                    function updatePerdidosLimits() {
                        const derrotas = parseInt(derrotasInput.val()) || 0;
                        const min = derrotas;
                        const max = derrotas * 3;
                        puntosPerdidosInput.attr('min', min);
                        puntosPerdidosInput.attr('max', max);
                        rangoPerdidos.text(`Rango permitido: ${min} - ${max}`);
                        //validateInput(puntosPerdidosInput, min, max);
                    }

                    function validateInput(inputElement, min, max) {
                        const val = parseInt(inputElement.val());
                        if (val < min || val > max) {
                            inputElement.addClass('is-invalid');
                        } else {
                            inputElement.removeClass('is-invalid');
                        }
                    }

                    victoriasInput.on('input change', updateGanadosLimits);
                    derrotasInput.on('input change', updatePerdidosLimits);
                    //puntosGanadosInput.on('input change', () => validateInput(puntosGanadosInput, parseInt(victoriasInput.val()), parseInt(victoriasInput.val()) * 3));
                    //puntosPerdidosInput.on('input change', () => validateInput(puntosPerdidosInput, parseInt(derrotasInput.val()), parseInt(derrotasInput.val()) * 3));

                    updateGanadosLimits();
                    updatePerdidosLimits();
                })();
                @endforeach

                jQuery('form').on('submit', function (e) {
                    let valido = true;

                    @foreach($results as $index => $result)
                    (function () {
                        const victorias = parseInt(jQuery('#victorias_{{ $index + 1 }}').val()) || 0;
                        const derrotas = parseInt($('#derrotas_{{ $index + 1 }}').val()) || 0;

                        const puntosGanados = parseInt(jQuery('#puntos_ganados_{{ $index + 1 }}').val()) || 0;
                        const puntosPerdidos = parseInt(jQuery('#puntos_perdidos_{{ $index + 1 }}').val()) || 0;

                        const minGanados = victorias;
                        const maxGanados = victorias * 3;

                        const minPerdidos = derrotas;
                        const maxPerdidos = derrotas * 3;

                        if (puntosGanados < minGanados || puntosGanados > maxGanados) {
                            valido = false;
                            alert(`Deck {{ $index + 1 }}: los puntos ganados deben estar entre ${minGanados} y ${maxGanados}`);
                        }

                        if (puntosPerdidos < minPerdidos || puntosPerdidos > maxPerdidos) {
                            valido = false;
                            alert(`Deck {{ $index + 1 }}: los puntos perdidos deben estar entre ${minPerdidos} y ${maxPerdidos}`);
                        }
                    })();
                    @endforeach

                    if (!valido) {
                        e.preventDefault(); // Cancela el envío
                    }
                });


        });
    </script>
@endsection
