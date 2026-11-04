@php
    $isUserInscribed = Auth::check() && $assists->contains('id', Auth::id());
    $numBeysModal = ($event->deck === '1on1' || in_array($event->beys, ['crew_battle', 'equipos'])) ? 1 : 3;
    $formAction = $isUserInscribed
        ? route('tournament.results.store', ['eventId' => $event->id])
        : route('events.assist', ['event' => $event->id]);
@endphp

<div class="modal fade" id="formModal" aria-labelledby="formModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content" style="background-color: #0f172a;">

            <div class="modal-header border-bottom border-secondary" style="background-color: #1e293b;">
                <h5 class="modal-title fw-bold text-white font-Oswald fs-3" id="formModalLabel">
                    <i class="fas fa-layer-group me-2" style="color: var(--sbbl-gold);"></i>
                    <span id="deckModalTitleText">{{ $isUserInscribed ? 'ACTUALIZAR MI DECK' : 'REGISTRO OFICIAL DE TORNEO' }}</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="background-color: #0f172a;">
                <div class="container py-3" style="max-width: 1200px;">

                    {{-- Banner de aviso cuando un Juez está editando el deck de otro jugador --}}
                    <div id="judgeEditingBanner" class="alert border-info bg-info bg-opacity-10 text-info p-3 shadow-sm mb-4 d-none d-flex justify-content-between align-items-center">
                        <div class="fw-bold fs-5 font-Oswald">
                            <i class="fas fa-user-shield me-2"></i> MODO JUEZ — EDITANDO DECK DE: <span id="judgeTargetName" class="text-white text-uppercase"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light fw-bold" onclick="resetModalToOwnUser()">
                            Volver a mi Deck
                        </button>
                    </div>

                    @if(!$isUserInscribed)
                        <div id="protocoloInscripcionAlert" class="alert alert-warning border-warning bg-warning bg-opacity-10 text-white p-4 shadow-sm mb-4">
                            <h5 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2 text-warning"></i> PROTOCOLO DE INSCRIPCIÓN</h5>
                            <p class="mb-0">Para participar en este evento oficial, debes registrar el Deck que vas a utilizar. Si aún no estás seguro de tu combo, marca la opción "Inscripción Tentativa".</p>
                        </div>
                    @endif

                    <div class="alert alert-info border-info bg-info bg-opacity-10 text-white p-4 shadow-sm mb-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h5 class="fw-bold mb-1"><i class="fas fa-info-circle me-2 text-info"></i> BEYS COMPLETOS</h5>
                                <p class="mb-0 small">Para los combos que no utilicen ratchet o bit se han creado las opciones UX Expanded y Without Bit respectivamente.</p>
                            </div>
                            <button type="button" onclick="cargarUltimoDeckWeb()" id="btnCargarUltimoDeckWeb" class="btn btn-shonen btn-shonen-info fw-bold px-4 py-2 shrink-0">
                                <span class="text-white"><i class="fas fa-history me-2"></i> CARGAR MI ÚLTIMO DECK</span>
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ $formAction }}" id="deckForm" novalidate>
                        @csrf
                        <input type="hidden" id="web_target_user_id" name="target_user_id" value="">

                        {{-- SECCIÓN TENTATIVO --}}
                        <div class="card bg-dark border-secondary mb-4 shadow" id="tentativeCardBox">
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

                        {{-- NUEVO: SECCIÓN 2X1 PARA JUECES (Solo en la primera inscripción) --}}
                        @if(!$isUserInscribed && Auth::user()->hasAnyRole(['admin', 'juez']))
                        <div class="card bg-dark border-warning mb-4 shadow">
                            <div class="card-body p-4 text-center">
                                <div class="form-check form-switch d-inline-block p-0">
                                    <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" role="switch" id="alsoJudgeCheck" name="also_judge" value="1" style="width: 3.5em; height: 1.75em; cursor: pointer; border-color: #eab308; background-color: #334155;">
                                    <label class="form-check-label text-warning fw-bold fs-5 text-uppercase align-middle" for="alsoJudgeCheck" style="cursor: pointer;">
                                        <i class="fas fa-gavel me-2"></i> APUNTARME TAMBIÉN COMO JUEZ
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- SECCIÓN DE BEYS --}}
                        <div class="row justify-content-center" id="deckContainer">
                            @foreach(range(1, $numBeysModal) as $index)
                                <div class="col-12 {{ $numBeysModal == 1 ? 'col-xl-6' : 'col-xl-4' }} mb-4">
                                    <div class="card h-100 border-secondary shadow-lg" style="background-color: #1e293b;">
                                        <div class="card-header border-secondary bg-black text-white fw-bold font-Oswald fs-4 d-flex justify-content-between align-items-center">
                                            <span>#{{ $index }} BEYBLADE</span>
                                            <i class="fas fa-compact-disc text-info"></i>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Blade *</label>
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
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Ratchet *</label>
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
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Bit *</label>
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
                                                    <label class="form-label small text-uppercase text-light fw-bold mb-1">Over (Opcional)</label>
                                                    <select class="form-select select2-modal deck-input" id="over_{{ $index }}" name="over_blade[{{ Auth::id() }}][]">
                                                        <option value="">-</option>
                                                        @foreach($overBladeOptions as $option)
                                                            <option value="{{ $option }}" {{ (isset($results[$index-1]) && $results[$index-1]->over_blade == $option) ? 'selected' : '' }}>
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

                                                <input type="hidden" name="victorias[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->victorias : 0 }}">
                                                <input type="hidden" name="derrotas[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->derrotas : 0 }}">
                                                <input type="hidden" name="puntos_ganados[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->puntos_ganados : 0 }}">
                                                <input type="hidden" name="puntos_perdidos[{{ Auth::id() }}][]" value="{{ isset($results[$index-1]) ? $results[$index-1]->puntos_perdidos : 0 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="modal-footer border-top border-secondary justify-content-start" style="background-color: #1e293b;">
                            <button type="button" class="btn fw-bold bg-dark text-white border-secondary px-4" data-bs-dismiss="modal">CANCELAR</button>
                            <button type="submit" class="btn btn-success fw-bold px-5 font-Oswald fs-5" id="submitDeckBtn">
                                <i class="fas fa-save me-2"></i> <span id="submitDeckBtnText">{{ $isUserInscribed ? 'GUARDAR DECK' : 'CONFIRMAR INSCRIPCIÓN' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const currentEventIdWeb = {{ $event->id }};
    const numBeysModalWeb = {{ $numBeysModal }};

    document.addEventListener("DOMContentLoaded", function () {
        const tentativeCheck = document.getElementById('tentativeCheck');
        const deckContainer = document.getElementById('deckContainer');
        const inputs = document.querySelectorAll('.deck-input');
        const warning = document.getElementById('tentativeWarning');
        const submitBtn = document.getElementById('submitDeckBtn');
        const deckForm = document.getElementById('deckForm');

        window.toggleTentativeWeb = function() {
            if(!tentativeCheck) return;
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
                    if(!input.id.includes('assist') && !input.id.includes('over')) {
                        input.setAttribute('required', 'required');
                    }
                });
            }
        };

        if(tentativeCheck) {
            tentativeCheck.addEventListener('change', window.toggleTentativeWeb);
            window.toggleTentativeWeb();
        }

        // Si un Juez está editando el Deck de otro jugador, interceptamos el submit y lo enviamos al endpoint del Hub (/api/torneos/{id}/deck)
        if (deckForm) {
            deckForm.addEventListener('submit', async function(e) {
                const targetUid = document.getElementById('web_target_user_id').value;
                if (!targetUid) return; // Flujo normal para el propio jugador

                e.preventDefault();
                const isTent = tentativeCheck && tentativeCheck.checked;
                const fd = new FormData();
                fd.append('target_user_id', targetUid);
                fd.append('is_tentative', isTent ? 'true' : 'false');

                for (let i = 1; i <= numBeysModalWeb; i++) {
                    fd.append('blade[]', document.getElementById(`blade_${i}`)?.value || '');
                    fd.append('ratchet[]', document.getElementById(`ratchet_${i}`)?.value || '');
                    fd.append('bit[]', document.getElementById(`bit_${i}`)?.value || '');
                    fd.append('over_blade[]', document.getElementById(`over_${i}`)?.value || '');
                    fd.append('assist_blade[]', document.getElementById(`assist_${i}`)?.value || '');
                }

                submitBtn.disabled = true;
                try {
                    const res = await fetch(`/api/torneos/${currentEventIdWeb}/deck`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: fd
                    });
                    const data = await res.json();
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al guardar el Deck del participante.');
                        submitBtn.disabled = false;
                    }
                } catch (err) {
                    alert('Error de red al guardar el Deck.');
                    submitBtn.disabled = false;
                }
            });
        }
    });

    // Cargar último deck registrado por el usuario
    async function cargarUltimoDeckWeb() {
        const btn = document.getElementById('btnCargarUltimoDeckWeb');
        const oldHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span><i class="fas fa-spinner fa-spin me-2"></i> BUSCANDO...</span>';

        try {
            const res = await fetch('/api/ultimo-deck');
            const deck = await res.json();
            if (!deck || deck.length === 0) {
                alert('No tienes Decks registrados en torneos anteriores.');
                return;
            }

            const check = document.getElementById('tentativeCheck');
            if (check && check.checked) {
                check.checked = false;
                window.toggleTentativeWeb();
            }

            deck.forEach((bey, idx) => {
                const i = idx + 1;
                if (i > numBeysModalWeb) return;
                setSelect2ValWeb(`blade_${i}`, bey.blade);
                setSelect2ValWeb(`ratchet_${i}`, bey.ratchet);
                setSelect2ValWeb(`bit_${i}`, bey.bit);
                setSelect2ValWeb(`over_${i}`, bey.over_blade && bey.over_blade !== '-' ? bey.over_blade : '');
                setSelect2ValWeb(`assist_${i}`, bey.assist_blade && bey.assist_blade !== '-' ? bey.assist_blade : '');
            });
        } catch (e) {
            alert('Error al recuperar tu último Deck.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }

    function setSelect2ValWeb(elementId, value) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const cleanVal = value ? value.trim() : '';
        el.value = cleanVal;
        if (window.jQuery && jQuery(el).hasClass('select2-hidden-accessible')) {
            jQuery(el).val(cleanVal).trigger('change');
        }
    }

    // Abrir el modal en Modo Juez para editar el Deck de cualquier participante
    async function abrirModalDeckParticipanteWeb(userId, userName) {
        document.getElementById('web_target_user_id').value = userId;
        document.getElementById('judgeTargetName').innerText = userName;
        document.getElementById('judgeEditingBanner').classList.remove('d-none');
        document.getElementById('deckModalTitleText').innerText = `EDITAR DECK DE: ${userName.toUpperCase()}`;
        document.getElementById('submitDeckBtnText').innerText = `GUARDAR DECK DE ${userName.toUpperCase()}`;
        
        const btnUlt = document.getElementById('btnCargarUltimoDeckWeb');
        if (btnUlt) btnUlt.classList.add('d-none');

        const check = document.getElementById('tentativeCheck');
        if (check) {
            check.checked = false;
            window.toggleTentativeWeb();
        }

        // Limpiamos los selects antes de cargar los del jugador
        for (let i = 1; i <= numBeysModalWeb; i++) {
            setSelect2ValWeb(`blade_${i}`, '');
            setSelect2ValWeb(`ratchet_${i}`, '');
            setSelect2ValWeb(`bit_${i}`, '');
            setSelect2ValWeb(`over_${i}`, '');
            setSelect2ValWeb(`assist_${i}`, '');
        }

        const modalEl = document.getElementById('formModal');
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        bsModal.show();

        try {
            const res = await fetch(`/api/torneos/${currentEventIdWeb}/mi-deck?user_id=${userId}`);
            if (res.ok) {
                const deck = await res.json();
                deck.forEach((bey, idx) => {
                    const i = idx + 1;
                    if (i > numBeysModalWeb) return;
                    setSelect2ValWeb(`blade_${i}`, bey.blade);
                    setSelect2ValWeb(`ratchet_${i}`, bey.ratchet);
                    setSelect2ValWeb(`bit_${i}`, bey.bit);
                    setSelect2ValWeb(`over_${i}`, bey.over_blade && bey.over_blade !== '-' ? bey.over_blade : '');
                    setSelect2ValWeb(`assist_${i}`, bey.assist_blade && bey.assist_blade !== '-' ? bey.assist_blade : '');
                });
            }
        } catch (e) {
            console.warn('Sin deck previo para este jugador');
        }
    }

    function resetModalToOwnUser() {
        window.location.reload();
    }
</script>