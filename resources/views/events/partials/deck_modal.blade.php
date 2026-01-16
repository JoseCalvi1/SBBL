<div class="modal fade" id="formModal" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content" style="background-color: #0f172a;">

            <div class="modal-header border-bottom border-secondary" style="background-color: #1e293b;">
                <h5 class="modal-title fw-bold text-white" id="formModalLabel">
                    <i class="fas fa-layer-group me-2 text-info"></i>Registro de Deck
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="background-color: #0f172a;">

                <div class="container py-3">
                    <div class="alert alert-info bg-dark border-info text-info small mb-4 shadow-sm">
                        <i class="fas fa-info-circle me-1"></i> Configura los 3 Beyblades de tu deck.
                    </div>

                    <form method="POST" action="{{ route('tournament.results.store', ['eventId' => $event->id]) }}" id="deckForm">
                        @csrf

                        <div class="row">
                            @foreach(range(1, 3) as $index)
                                <div class="col-12 col-xl-4 mb-4">
                                    <div class="card h-100 border-secondary shadow-lg" style="background-color: #1e293b;">
                                        <div class="card-header border-secondary bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
                                            <span>#{{ $index }} Beyblade</span>
                                            <i class="fas fa-gamepad text-muted"></i>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Blade</label>
                                                    <select class="form-select select2-modal" id="blade_{{ $index }}" name="blade[{{ Auth::id() }}][]" required>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($bladeOptions as $option)
                                                            <option value="{{ $option }}"
                                                                {{ (isset($results[$index-1]) && $results[$index-1]->blade == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Ratchet</label>
                                                    <select class="form-select select2-modal" id="ratchet_{{ $index }}" name="ratchet[{{ Auth::id() }}][]" required>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($ratchetOptions as $option)
                                                            <option value="{{ $option }}"
                                                                {{ (isset($results[$index-1]) && $results[$index-1]->ratchet == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Bit</label>
                                                    <select class="form-select select2-modal" id="bit_{{ $index }}" name="bit[{{ Auth::id() }}][]" required>
                                                        <option value="">Seleccionar...</option>
                                                        @foreach($bitOptions as $option)
                                                            <option value="{{ $option }}"
                                                                {{ (isset($results[$index-1]) && $results[$index-1]->bit == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase fw-bold mb-1">Assist (Opcional)</label>
                                                    <select class="form-select select2-modal" id="assist_{{ $index }}" name="assist_blade[{{ Auth::id() }}][]">
                                                        <option value="">-</option>
                                                        @foreach($assistBladeOptions as $option)
                                                            <option value="{{ $option }}"
                                                                {{ (isset($results[$index-1]) && $results[$index-1]->assist_blade == $option) ? 'selected' : '' }}>
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <input type="hidden" name="victorias[{{ Auth::id() }}][]" value="0">
                                                <input type="hidden" name="derrotas[{{ Auth::id() }}][]" value="0">
                                                <input type="hidden" name="puntos_ganados[{{ Auth::id() }}][]" value="0">
                                                <input type="hidden" name="puntos_perdidos[{{ Auth::id() }}][]" value="0">
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="deckForm" class="btn btn-success fw-bold px-5">
                    <i class="fas fa-save me-2"></i> GUARDAR DECK
                </button>
            </div>
        </div>
    </div>
</div>
