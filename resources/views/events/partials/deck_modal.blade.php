@php
    $isUserInscribed = Auth::check() && $assists->contains('id', Auth::id());
    // Decidir adónde envía el formulario según si ya está inscrito o no
    $formAction = $isUserInscribed
        ? route('tournament.results.store', ['eventId' => $event->id]) // Ruta actual para editar
        : route('events.assist', ['event' => $event->id]);             // Nueva ruta para apuntarse por 1ra vez
@endphp

<div class="modal fade" id="formModal" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content" style="background-color: #0f172a;">

            <div class="modal-header border-bottom border-secondary" style="background-color: #1e293b;">
                <h5 class="modal-title fw-bold text-white font-Oswald fs-3" id="formModalLabel">
                    <i class="fas fa-layer-group me-2" style="color: var(--sbbl-gold);"></i>
                    {{ $isUserInscribed ? 'ACTUALIZAR MI DECK' : 'REGISTRO OFICIAL DE TORNEO' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="background-color: #0f172a;">
                <div class="container py-3" style="max-width: 1200px;">

                    @if(!$isUserInscribed)
                        <div class="alert alert-warning border-warning bg-warning bg-opacity-10 text-white p-4 shadow-sm mb-4">
                            <h5 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2 text-warning"></i> PROTOCOLO DE INSCRIPCIÓN</h5>
                            <p class="mb-0">Para participar en este evento oficial, debes registrar el Deck que vas a utilizar. Si aún no estás seguro de tu combo, marca la opción "Inscripción Tentativa".</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ $formAction }}" id="deckForm">
                        @csrf

                        {{-- SECCIÓN TENTATIVO --}}
                        <div class="card bg-dark border-secondary mb-4 shadow">
                            <div class="card-body p-4 text-center">
                                <div class="form-check form-switch d-inline-block p-0">
                                    <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" role="switch" id="tentativeCheck" name="is_tentative" style="width: 3.5em; height: 1.75em; cursor: pointer;">
                                    <label class="form-check-label text-white fw-bold fs-5 text-uppercase align-middle" for="tentativeCheck" style="cursor: pointer;">
                                        Aún no tengo mi Deck decidido (Inscripción Tentativa)
                                    </label>
                                </div>
                                <div class="text-danger fw-bold mt-3 small" id="tentativeWarning" style="display: none;">
                                    ⚠️ AVISO DE JUEZ: Si marcas esta casilla guardarás tu plaza, pero ES OBLIGATORIO volver a este menú y rellenar tu Deck definitivo antes de que comience el torneo o se te prohibirá la entrada.
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN DE BEBYS --}}
                        <div class="row" id="deckContainer">
                            @foreach(range(1, 3) as $index)
                                <div class="col-12 col-xl-4 mb-4">
                                    <div class="card h-100 border-secondary shadow-lg" style="background-color: #1e293b;">
                                        <div class="card-header border-secondary bg-black text-white fw-bold font-Oswald fs-4 d-flex justify-content-between align-items-center">
                                            <span>#{{ $index }} BEYBLADE</span>
                                            <i class="fas fa-gamepad text-muted"></i>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Blade</label>
                                                    <select class="form-select select2-modal deck-input" id="blade_{{ $index }}" name="blade[{{ Auth::id() }}][]" required>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($bladeOptions as $option)
                                                            <option value="{{ $option }}" {{ (isset($results[$index-1]) && $results[$index-1]->blade == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Ratchet</label>
                                                    <select class="form-select select2-modal deck-input" id="ratchet_{{ $index }}" name="ratchet[{{ Auth::id() }}][]" required>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($ratchetOptions as $option)
                                                            <option value="{{ $option }}" {{ (isset($results[$index-1]) && $results[$index-1]->ratchet == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Bit</label>
                                                    <select class="form-select select2-modal deck-input" id="bit_{{ $index }}" name="bit[{{ Auth::id() }}][]" required>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($bitOptions as $option)
                                                            <option value="{{ $option }}" {{ (isset($results[$index-1]) && $results[$index-1]->bit == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Assist (Opcional)</label>
                                                    <select class="form-select select2-modal deck-input" id="assist_{{ $index }}" name="assist_blade[{{ Auth::id() }}][]">
                                                        <option value="">-</option>
                                                        @foreach($assistBladeOptions as $option)
                                                            <option value="{{ $option }}" {{ (isset($results[$index-1]) && $results[$index-1]->assist_blade == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Solo mostramos estadísticas si YA está inscrito y estamos en fase de resultados --}}
                                                @if($isUserInscribed && $event->status != 'OPEN')
                                                <div class="col-12 mt-4 pt-3 border-top border-secondary">
                                                    <h6 class="small text-uppercase text-muted fw-bold mb-3"><i class="fas fa-chart-bar me-1"></i> Estadísticas Post-Torneo</h6>
                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label small text-white mb-0">Victorias</label>
                                                            <input type="number" class="form-control form-control-sm bg-dark text-white border-secondary" name="victorias[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->victorias : 0 }}" min="0">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small text-white mb-0">Derrotas</label>
                                                            <input type="number" class="form-control form-control-sm bg-dark text-white border-secondary" name="derrotas[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->derrotas : 0 }}" min="0">
                                                        </div>
                                                    </div>
                                                </div>
                                                @else
                                                    {{-- Campos ocultos a 0 para que la base de datos no dé error al apuntarse por 1ra vez --}}
                                                    <input type="hidden" name="victorias[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->victorias : 0 }}">
                                                    <input type="hidden" name="derrotas[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->derrotas : 0 }}">
                                                    <input type="hidden" name="puntos_ganados[{{ Auth::id() }}][]" value="0">
                                                    <input type="hidden" name="puntos_perdidos[{{ Auth::id() }}][]" value="0">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer border-top border-secondary justify-content-start" style="background-color: #1e293b;">
                <button type="button" class="btn fw-bold bg-dark text-white border-secondary px-4" data-bs-dismiss="modal">CANCELAR</button>
                <button type="submit" form="deckForm" class="btn btn-success fw-bold px-5 font-Oswald fs-5" id="submitDeckBtn">
                    <i class="fas fa-save me-2"></i> {{ $isUserInscribed ? 'GUARDAR DECK' : 'CONFIRMAR INSCRIPCIÓN' }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tentativeCheck = document.getElementById('tentativeCheck');
        const deckContainer = document.getElementById('deckContainer');
        const inputs = document.querySelectorAll('.deck-input');
        const warning = document.getElementById('tentativeWarning');
        const submitBtn = document.getElementById('submitDeckBtn');

        function toggleTentative() {
            if(tentativeCheck.checked) {
                deckContainer.style.opacity = '0.3';
                deckContainer.style.pointerEvents = 'none';
                warning.style.display = 'block';
                submitBtn.classList.replace('btn-success', 'btn-warning');
                submitBtn.classList.replace('text-white', 'text-dark');
                inputs.forEach(input => input.removeAttribute('required'));
            } else {
                deckContainer.style.opacity = '1';
                deckContainer.style.pointerEvents = 'auto';
                warning.style.display = 'none';
                submitBtn.classList.replace('btn-warning', 'btn-success');
                submitBtn.classList.replace('text-dark', 'text-white');
                inputs.forEach(input => {
                    if(!input.id.includes('assist')) input.setAttribute('required', 'required');
                });
            }
        }

        if(tentativeCheck) {
            tentativeCheck.addEventListener('change', toggleTentative);
            toggleTentative(); // Se ejecuta al cargar
        }
    });
</script>

