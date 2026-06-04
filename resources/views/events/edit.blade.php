@extends('layouts.app')

@section('content')
<div class="main-container px-lg-5 py-5">

    <div class="d-flex justify-content-between align-items-center mb-5 px-lg-4">
        <h2 class="text-white fw-bold display-6 m-0">Editar Evento</h2>
        <a href="{{ route('events.show', ['event' => $event->id]) }}" class="btn btn-outline-light rounded-pill px-4 hover-effect-btn">
            <i class="fas fa-arrow-left me-2"></i> Volver al Evento
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            {{-- BLOQUE DE ERRORES --}}
            @if ($errors->any())
                <div class="alert alert-danger border-danger bg-danger bg-opacity-10 text-white mb-4 p-4 rounded-3 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle fs-3 me-3 mt-1"></i>
                        <div>
                            <h4 class="fw-bold mb-2">No se pudo actualizar el evento</h4>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <script> document.addEventListener("DOMContentLoaded", function() { window.scrollTo(0, 0); }); </script>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-danger bg-danger bg-opacity-10 text-white mb-4 p-4 rounded-3 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-times-circle fs-3 me-3"></i>
                        <div>
                            <h5 class="fw-bold m-0">Error del Servidor</h5>
                            <p class="m-0">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="px-md-5">
                <form method="POST" action="{{ route('events.update', ['event' => $event->id]) }}" enctype="multipart/form-data" class="mx-auto" style="max-width: 1000px;">
                    @csrf
                    @method('PUT')

                    {{-- PASO 1: BÁSICO --}}
                    <div class="mb-5">
                        <h4 class="text-white mb-4 border-bottom border-secondary pb-3 fw-bold">Información General</h4>

                        @if (Auth::user()->hasAnyRole(['admin', 'arbitro']))
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label text-light-muted small text-uppercase fw-bold">Título del Evento</label>
                                    <input type="text" name="name" id="name" class="form-control form-control-lg dark-input p-3" value="{{ old('name', $event->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light-muted small text-uppercase fw-bold">Imagen Personalizada</label>
                                    <input type="file" class="form-control form-control-lg dark-input p-3" id="image_mod" name="image_mod" accept="image/*">
                                    @if($event->image_mod)
                                        <div class="mt-2 text-info small"><i class="fas fa-image me-1"></i> Imagen actual cargada. Sube otra para reemplazarla.</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="name" value="{{ $event->name }}">
                        @endif

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="mode" class="form-label text-light-muted small text-uppercase fw-bold">Modalidad <span class="text-info">*</span></label>
                                <select name="mode" id="mode" class="form-select form-select-lg dark-input p-3 fs-5" required>
                                    <option value="beybladex" {{ (old('mode', $event->mode) == 'beybladex') ? 'selected' : '' }}>Beyblade X</option>
                                    <option value="beybladeburst" {{ (old('mode', $event->mode) == 'beybladeburst') ? 'selected' : '' }}>Beyblade Burst</option>
                                    <option value="beyblademetal" {{ (old('mode', $event->mode) == 'beyblademetal') ? 'selected' : '' }}>Metal Fight</option>
                                    <option value="beybladebakuten" {{ (old('mode', $event->mode) == 'beybladebakuten') ? 'selected' : '' }}>Bakuten Shoot</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="imagen" class="form-label text-light-muted small text-uppercase fw-bold">Categoría <span class="text-info">*</span></label>
                                <select name="imagen" id="imagen" class="form-select form-select-lg dark-input p-3 fs-5" required>
                                    <option value="quedada" {{ (old('imagen', $event->beys) == 'quedada') ? 'selected' : '' }}>Quedada</option>
                                    <option value="simulacro" {{ (old('imagen', $event->beys) == 'simulacro') ? 'selected' : '' }}>Simulacro ranking</option>
                                    <option value="hasbro" {{ (old('imagen', $event->beys) == 'hasbro' || $event->imagen == 'hasbro') ? 'selected' : '' }}>Formato Hasbro</option>
                                    @if (Auth::user()->hasAnyRole(['admin', 'juez']))
                                        <option value="ranking" {{ (old('imagen', $event->beys) == 'ranking' || $event->imagen == 'upload-events/rankingx.jpg') ? 'selected' : '' }}>Ranking</option>
                                        <option value="grancopa" {{ (old('imagen', $event->beys) == 'grancopa') ? 'selected' : '' }}>Gran Copa (Especial)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- PASO 2: CONFIGURACIÓN --}}
                    <div class="mb-5">
                        <h4 class="text-white mb-4 border-bottom border-secondary pb-3 fw-bold">Reglas del Juego</h4>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Formato Deck <span class="text-info">*</span></label>
                                <select name="deck" id="deck" class="form-select form-select-lg dark-input p-3" required>
                                    <option value="3on3" {{ (old('deck', $event->deck) == '3on3') ? 'selected' : '' }}>3on3 (3 Beys)</option>
                                    <option value="5g" {{ (old('deck', $event->deck) == '5g') ? 'selected' : '' }}>5G (5 Beys)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Sistema Torneo <span class="text-info">*</span></label>
                                <select name="configuration" id="configuration" class="form-select form-select-lg dark-input p-3" required>
                                    <option value="SingleElimination" {{ (old('configuration', $event->configuration) == 'SingleElimination') ? 'selected' : '' }}>Eliminación Simple</option>
                                    <option value="DoubleElimination" {{ (old('configuration', $event->configuration) == 'DoubleElimination') ? 'selected' : '' }}>Doble Eliminación</option>
                                    <option value="RoundRobin" {{ (old('configuration', $event->configuration) == 'RoundRobin') ? 'selected' : '' }}>Round Robin (Liga)</option>
                                    <option value="Swiss" {{ (old('configuration', $event->configuration) == 'Swiss') ? 'selected' : '' }}>Sistema Suizo</option>
                                    <option value="FreeForAll" {{ (old('configuration', $event->configuration) == 'FreeForAll') ? 'selected' : '' }}>Free For All</option>
                                    <option value="Leaderboard" {{ (old('configuration', $event->configuration) == 'Leaderboard') ? 'selected' : '' }}>Leaderboard</option>
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-md-5 mb-3 mb-md-0">
                                <label for="stadiums" class="form-label fw-bold text-white mb-1 fs-5">
                                    <i class="fas fa-dungeon me-2 text-info"></i>Número de Estadios
                                </label>
                                <div class="text-light-muted small">Define la capacidad base del evento.</div>
                            </div>

                            <div class="col-md-3 mb-3 mb-md-0">
                                <input type="number" name="stadiums" id="stadiums" class="form-control form-control-lg dark-input text-center fw-bold fs-4" value="{{ old('stadiums', $event->stadiums) }}" min="1" required>
                                <div id="stadium-limit-helper" class="text-info small fw-bold mt-2 text-center">Aforo: 20 plazas</div>
                            </div>

                            @if(Auth::user()->hasAnyRole(['admin', 'juez', 'arbitro']))
                            <div class="col-md-4">
                                <label for="max_players" class="form-label text-warning small text-uppercase fw-bold m-0"><i class="fas fa-edit me-1"></i> Sobrescribir Límite</label>
                                <input type="number" name="max_players" id="max_players" class="form-control dark-input mt-1" value="{{ old('max_players', $event->max_players) }}" placeholder="Vacío para auto">

                                {{-- Mantenemos el checkbox oculto para no romper lógica de backend si no ponen límite manual --}}
                                <input type="hidden" name="has_stadium_limit" value="1">
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- PASO 3: DETALLES --}}
                    <div class="mb-5">
                        <h4 class="text-white mb-4 border-bottom border-secondary pb-3 fw-bold">Ubicación y Fecha</h4>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Región / C. Autónoma <span class="text-info">*</span></label>
                                <select name="region_id" id="region_id" class="form-select form-select-lg dark-input p-3 fs-5" required>
                                    <option value="" disabled>Selecciona Región...</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" data-provinces='{{ json_encode($region->provinces) }}' {{ old('region_id', $event->region_id) == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Provincia <span class="text-info">*</span></label>
                                <select name="province_id" id="province_id" class="form-select form-select-lg dark-input p-3 fs-5" required disabled>
                                    <option value="" disabled selected>Primero selecciona una región...</option>
                                </select>
                                {{-- Mantener ID en JS --}}
                                <input type="hidden" id="old_province_id" value="{{ old('province_id', $event->province_id) }}">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Ciudad <span class="text-info">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text dark-input border-end-0 text-light-muted"><i class="fas fa-city"></i></span>
                                    <input type="text" name="city" class="form-control dark-input border-start-0 ps-0" placeholder="Ej. Madrid" value="{{ old('city', $event->city) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Lugar Exacto <span class="text-info">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text dark-input border-end-0 text-light-muted"><i class="fas fa-map-pin"></i></span>
                                    <input type="text" name="location" class="form-control dark-input border-start-0 ps-0" placeholder="Ej. El Retiro" value="{{ old('location', $event->location) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Fecha <span class="text-info">*</span></label>
                                <input type="date" name="event_date" class="form-control form-control-lg dark-input p-3" value="{{ old('event_date', $event->date ? \Carbon\Carbon::parse($event->date)->format('Y-m-d') : '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Hora <span class="text-info">*</span></label>
                                <input type="time" name="event_time" class="form-control form-control-lg dark-input p-3" value="{{ old('event_time', $event->time) }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-light-muted small text-uppercase fw-bold">Notas Adicionales</label>
                            <textarea name="note" id="note" rows="4" class="form-control dark-input p-3" placeholder="Material necesario, punto de encuentro...">{{ old('note', $event->note) }}</textarea>
                        </div>
                    </div>

                    @if(!Auth::user()->hasAnyRole(['admin', 'arbitro']))
                        <div class="alert alert-info d-flex align-items-center p-4 bg-opacity-10 border-info text-info mb-4">
                            <i class="fas fa-info-circle me-4 fs-2"></i>
                            <div class="fs-5">Al actualizar, confirmo que sigo cumpliendo las <a href="https://sbbl.es/rules" target="_blank" class="alert-link text-white text-decoration-underline">normas</a> y asumo la responsabilidad del material.</div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end mt-5 pt-4 border-top border-secondary">
                        <button type="submit" class="btn btn-success px-5 py-3 fw-bold rounded-pill fs-5 shadow-sm">
                            Actualizar Evento <i class="fas fa-save ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Tema Azul Oscuro */
    body { background-color: #0f172a !important; color: #f8fafc !important; }
    .text-light-muted { color: #cbd5e1 !important; }
    .dark-input { background-color: #1e293b !important; border: 2px solid #334155 !important; color: #fff !important; }
    .dark-input:focus { background-color: #0f172a !important; border-color: #38bdf8 !important; box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2); }
    .dark-input:disabled { background-color: #0f172a !important; opacity: 0.7; cursor: not-allowed; }
    .input-group-text.dark-input { background-color: #1e293b !important; border-color: #334155 !important; }
    .dark-input::placeholder { color: #64748b !important; opacity: 1; }
    .hover-effect-btn:hover { background-color: rgba(255,255,255,0.1); color: #fff; }

    /* Switch personalizado */
    .form-switch .form-check-input {
        background-color: #334155; border-color: #475569;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }
    .form-switch .form-check-input:checked { background-color: #38bdf8; border-color: #38bdf8; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modeSelect = document.getElementById("mode");
    const deckSelect = document.getElementById("deck");
    const regionSelect = document.getElementById('region_id');
    const provinceSelect = document.getElementById('province_id');
    const oldProvinceId = document.getElementById('old_province_id').value;
    // Cálculo en vivo del aforo de estadios
    const stadiumsInput = document.getElementById('stadiums');
    const limitHelper = document.getElementById('stadium-limit-helper');
    const maxPlayersInput = document.getElementById('max_players');

    function updateCapacityLabel() {
        if(!stadiumsInput || !limitHelper) return;
        const s = parseInt(stadiumsInput.value) || 1;
        let limit = 20;

        if (s === 2) limit = 35;
        else if (s === 3) limit = 45;
        else if (s === 4) limit = 55;
        else if (s >= 5) limit = 64;

        if (maxPlayersInput && maxPlayersInput.value !== "") {
            limitHelper.innerHTML = `<span class="text-warning">Fijado a ${maxPlayersInput.value} plazas</span>`;
        } else {
            limitHelper.innerHTML = `Aforo: ${limit} plazas`;
        }
    }

    if(stadiumsInput) stadiumsInput.addEventListener('input', updateCapacityLabel);
    if(maxPlayersInput) maxPlayersInput.addEventListener('input', updateCapacityLabel);
    updateCapacityLabel(); // Ejecutar al cargar

    function updateDeckOptions() {
        if (modeSelect.value === "beybladex") {
            // Forzar a "3on3" visualmente, pero no bloquear si el backend ya guardó otro
            Array.from(deckSelect.options).forEach(opt => {
                if (opt.value !== "3on3" && !opt.selected) opt.disabled = true;
            });
        } else {
            Array.from(deckSelect.options).forEach(opt => opt.disabled = false);
        }
    }

    function updateProvinces() {
        const selectedOption = regionSelect.options[regionSelect.selectedIndex];
        provinceSelect.innerHTML = '<option value="" disabled>Selecciona Provincia...</option>';

        if (selectedOption && selectedOption.value) {
            provinceSelect.disabled = false;
            try {
                const provinces = JSON.parse(selectedOption.dataset.provinces);
                provinces.forEach(province => {
                    const isSelected = oldProvinceId == province.id;
                    provinceSelect.add(new Option(province.name, province.id, false, isSelected));
                });
            } catch(e) { console.error("Error leyendo provincias", e); }
        } else {
            provinceSelect.disabled = true;
        }
    }

    modeSelect.addEventListener("change", updateDeckOptions);
    regionSelect.addEventListener("change", updateProvinces);

    // Inicializar al cargar (Vital para Edición)
    updateDeckOptions();
    if(regionSelect.value) updateProvinces();
});
</script>
@endsection
