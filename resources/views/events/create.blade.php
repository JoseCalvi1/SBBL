@extends('layouts.app')

@section('content')
<div class="main-container px-lg-5 py-5">

    <div class="d-flex justify-content-between align-items-center mb-5 px-lg-4">
        <h2 class="text-white fw-bold display-6 m-0">Crear Nuevo Evento</h2>
        <a href="{{ route('inicio.events') }}" class="btn btn-outline-light rounded-pill px-4 hover-effect-btn">
            <i class="fas fa-arrow-left me-2"></i> Volver
        </a>
    </div>

    <div class="row justify-content-center">
<div class="col-12 col-xl-10">

    {{-- ======================================================= --}}
    {{-- BLOQUE NUEVO: VISUALIZACIÓN DE ERRORES --}}
    {{-- ======================================================= --}}

    {{-- 1. Errores de Validación (Campos obligatorios, formato incorrecto, etc.) --}}
    @if ($errors->any())
        <div class="alert alert-danger border-danger bg-danger bg-opacity-10 text-white mb-4 p-4 rounded-3 shadow-sm">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fs-3 me-3 mt-1"></i>
                <div>
                    <h4 class="fw-bold mb-2">No se pudo crear el evento</h4>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Script simple para asegurar que si hay error, el usuario lo vea --}}
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                window.scrollTo(0, 0); // Sube al inicio para ver la alerta
            });
        </script>
    @endif

    {{-- 2. Errores de Lógica (Capturados por el try/catch del controlador) --}}
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
    {{-- ======================================================= --}}

    <div class="wizard-header mb-5 p-4">
                <div class="wizard-progress position-relative mt-2 mx-auto" style="max-width: 800px;">
                    <div class="progress" style="height: 3px; background-color: #1e293b;">
                        <div class="progress-bar bg-info" id="progressBarFill" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between position-absolute w-100" style="top: -20px;">
                        <div class="step-indicator active" data-step="1">
                            <div class="step-icon"><i class="fas fa-info"></i></div>
                            <span class="step-label">Básico</span>
                        </div>
                        <div class="step-indicator" data-step="2">
                            <div class="step-icon"><i class="fas fa-cogs"></i></div>
                            <span class="step-label">Config</span>
                        </div>
                        <div class="step-indicator" data-step="3">
                            <div class="step-icon"><i class="fas fa-calendar-alt"></i></div>
                            <span class="step-label">Detalles</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-md-5">
                <form id="eventWizard" method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mx-auto" style="max-width: 1000px;">
                    @csrf

                    {{-- PASO 1: BÁSICO --}}
                    <div class="wizard-step active" data-step="1">
                        <h4 class="text-white mb-4 border-bottom border-secondary pb-3 fw-bold">Información General</h4>

                        @if (Auth::user()->hasAnyRole(['admin', 'arbitro']))
                            <div class="row g-4 mb-5">
                                <div class="col-md-6">
                                    <label for="name" class="form-label text-light-muted small text-uppercase fw-bold">Título (Opcional)</label>
                                    <input type="text" name="name" id="name" class="form-control form-control-lg dark-input p-3" placeholder="Ej. Gran Torneo de Verano" value="{{ old('name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light-muted small text-uppercase fw-bold">Imagen Personalizada</label>
                                    <input type="file" class="form-control form-control-lg dark-input p-3" id="image_mod" name="image_mod" accept="image/*">
                                </div>
                            </div>
                        @endif

                        <div class="mb-5">
                            <label class="form-label text-light-muted small text-uppercase fw-bold mb-4">Modalidad <span class="text-info">*</span></label>
                            <div class="row g-3">
                                @php
                                    $modes = [
                                        'beybladex'       => ['label' => 'Beyblade X',     'icon' => 'fa-bolt'],      // Rayo (Velocidad X)
                                        'beybladeburst'   => ['label' => 'Beyblade Burst', 'icon' => 'fa-bomb'],      // Bomba (Explosión/Burst)
                                        'beyblademetal'   => ['label' => 'Metal Fight',    'icon' => 'fa-cogs'],      // Engranajes (Metal)
                                        'beybladebakuten' => ['label' => 'Bakuten',        'icon' => 'fa-dragon']     // Dragón (Bestias Bit clásicas)
                                    ];
                                @endphp

                                @foreach($modes as $val => $data)
                                    <div class="col-6 col-md-3">
                                        <input type="radio" class="btn-check" name="mode" id="mode_{{$val}}" value="{{$val}}" {{ old('mode') == $val ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-secondary w-100 p-4 h-100 d-flex flex-column align-items-center justify-content-center text-center mode-card gap-3 text-white" for="mode_{{$val}}">
                                            <i class="fas {{$data['icon']}} fs-2"></i>
                                            <span class="fs-5 fw-bold">{{$data['label']}}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-5">
                            <label for="imagen" class="form-label text-light-muted small text-uppercase fw-bold mb-3">Categoría <span class="text-info">*</span></label>
                            <select name="imagen" id="imagen" class="form-select form-select-lg dark-input p-3 fs-5" required>
                                <option value="" disabled {{ old('imagen') ? '' : 'selected' }}>Selecciona tipo de evento...</option>
                                <option value="quedada" {{ old('imagen') == 'quedada' ? 'selected' : '' }}>Quedada</option>
                                <option value="simulacro" {{ old('imagen') == 'simulacro' ? 'selected' : '' }}>Simulacro ranking</option>
                                <option value="hasbro" {{ old('imagen') == 'hasbro' ? 'selected' : '' }}>Formato Hasbro</option>
                                @if (Auth::user()->hasAnyRole(['admin', 'juez']))
                                    <option value="ranking" {{ old('imagen') == 'ranking' ? 'selected' : '' }}>Ranking</option>
                                    <option value="copaespecial1" {{ old('imagen') == 'copaespecial1' ? 'selected' : '' }}>Copa Split 1</option>
                                    <option value="copaespecial2" {{ old('imagen') == 'copaespecial2' ? 'selected' : '' }}>Copa Split 2</option>
                                    <option value="copaespecial3" {{ old('imagen') == 'copaespecial3' ? 'selected' : '' }}>Copa Split 3</option>
                                    <option value="copaespecial4" {{ old('imagen') == 'copaespecial4' ? 'selected' : '' }}>Copa Split 4</option>
                                @endif
                                @if (Auth::user()->hasAnyRole(['admin']))
                                    <option value="grancopa" {{ old('imagen') == 'grancopa' ? 'selected' : '' }}>Gran Copa (Especial)</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- PASO 2: CONFIGURACIÓN --}}
                    <div class="wizard-step" data-step="2">
                        <h4 class="text-white mb-4 border-bottom border-secondary pb-3 fw-bold">Reglas del Juego</h4>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Formato Deck <span class="text-info">*</span></label>
                                <select name="deck" id="deck" class="form-select form-select-lg dark-input p-3" required>
                                    <option value="" disabled selected>Elegir...</option>
                                    {{-- Las opciones se llenan por JS, pero restauramos old con data-old --}}
                                    <option value="3on3" data-old="{{ old('deck') == '3on3' ? '1' : '0' }}">3on3 (3 Beys)</option>
                                    <option value="5g" data-old="{{ old('deck') == '5g' ? '1' : '0' }}">5G (5 Beys)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Sistema Torneo <span class="text-info">*</span></label>
                                <select name="configuration" id="configuration" class="form-select form-select-lg dark-input p-3" required>
                                    <option value="" disabled selected>Elegir...</option>
                                    <option value="SingleElimination" data-old="{{ old('configuration') == 'SingleElimination' ? '1' : '0' }}">Eliminación Simple</option>
                                    <option value="DoubleElimination" data-old="{{ old('configuration') == 'DoubleElimination' ? '1' : '0' }}">Doble Eliminación</option>
                                    <option value="RoundRobin" data-old="{{ old('configuration') == 'RoundRobin' ? '1' : '0' }}">Round Robin (Liga)</option>
                                    <option value="Swiss" data-old="{{ old('configuration') == 'Swiss' ? '1' : '0' }}">Sistema Suizo</option>
                                    <option value="FreeForAll" data-old="{{ old('configuration') == 'FreeForAll' ? '1' : '0' }}">Free For All</option>
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
                                <input type="number" name="stadiums" id="stadiums" class="form-control form-control-lg dark-input text-center fw-bold fs-4" value="{{ old('stadiums', 1) }}" min="1" required>
                                <div id="stadium-limit-helper" class="text-info small fw-bold mt-2 text-center">Aforo: 20 plazas</div>
                            </div>

                            @if(Auth::user()->hasAnyRole(['admin', 'juez']))
                            <div class="col-md-4">
                                <label for="max_players" class="form-label text-warning small text-uppercase fw-bold m-0"><i class="fas fa-edit me-1"></i> Sobrescribir Límite (Juez)</label>
                                <input type="number" name="max_players" id="max_players" class="form-control dark-input mt-1" value="{{ old('max_players') }}" placeholder="Dejar vacío para auto">
                                <input type="hidden" name="has_stadium_limit" value="1">
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- PASO 3: DETALLES --}}
                    <div class="wizard-step" data-step="3">
                        <h4 class="text-white mb-4 border-bottom border-secondary pb-3 fw-bold">Ubicación y Fecha</h4>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Región / C. Autónoma <span class="text-info">*</span></label>
                                <select name="region_id" id="region_id" class="form-select form-select-lg dark-input p-3 fs-5" required>
                                    <option value="" disabled {{ old('region_id') ? '' : 'selected' }}>Selecciona Región...</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}" data-provinces='{{ json_encode($region->provinces) }}' {{ old('region_id') == $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Provincia <span class="text-info">*</span></label>
                                <select name="province_id" id="province_id" class="form-select form-select-lg dark-input p-3 fs-5" required disabled>
                                    <option value="" disabled selected>Primero selecciona una región...</option>
                                    {{-- Las opciones se llenan por JS --}}
                                </select>
                                {{-- Campo oculto para mantener el old value de provincia si hay error de validación --}}
                                <input type="hidden" id="old_province_id" value="{{ old('province_id') }}">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Ciudad <span class="text-info">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text dark-input border-end-0 text-light-muted"><i class="fas fa-city"></i></span>
                                    <input type="text" name="city" class="form-control dark-input border-start-0 ps-0" placeholder="Ej. Madrid" value="{{ old('city') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Lugar Exacto <span class="text-info">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text dark-input border-end-0 text-light-muted"><i class="fas fa-map-pin"></i></span>
                                    <input type="text" name="location" class="form-control dark-input border-start-0 ps-0" placeholder="Ej. El Retiro" value="{{ old('location') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Fecha <span class="text-info">*</span></label>
                                <input type="date" name="event_date" class="form-control form-control-lg dark-input p-3" value="{{ old('event_date') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light-muted small text-uppercase fw-bold">Hora <span class="text-info">*</span></label>
                                <input type="time" name="event_time" class="form-control form-control-lg dark-input p-3" value="{{ old('event_time') }}" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-light-muted small text-uppercase fw-bold">Notas Adicionales</label>
                            <textarea name="note" id="note" rows="4" class="form-control dark-input p-3" placeholder="Material necesario, punto de encuentro...">{{ old('note') }}</textarea>
                        </div>

                        @if(!Auth::user()->hasAnyRole(['admin', 'arbitro']))
                            <div class="alert alert-info d-flex align-items-center p-4 bg-opacity-10 border-info text-info">
                                <i class="fas fa-info-circle me-4 fs-2"></i>
                                <div class="fs-5">Confirmo que he leído las <a href="https://sbbl.es/rules" target="_blank" class="alert-link text-white text-decoration-underline">normas</a> y soy responsable del material.</div>
                            </div>
                        @endif
                    </div>

                    {{-- NAVEGACIÓN --}}
                    <div class="d-flex justify-content-between mt-5 pt-5 border-top border-secondary">
                        <button type="button" class="btn btn-outline-light px-5 py-3 rounded-pill fs-5" id="prevBtn" disabled>
                            <i class="fas fa-arrow-left me-2"></i> Atrás
                        </button>
                        <div>
                            <button type="button" class="btn btn-info px-5 py-3 fw-bold rounded-pill text-dark fs-5 shadow-sm" id="nextBtn">
                                Siguiente <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success px-5 py-3 fw-bold d-none rounded-pill fs-5 shadow-sm" id="submitBtn">
                                Crear Evento <i class="fas fa-check ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* --- TEMA AZUL OSCURO TOTAL (Sin fondos blancos) --- */
    body {
        background-color: #0f172a !important; /* Fondo principal oscuro (Slate 900) */
        color: #f8fafc !important; /* Texto principal blanco */
    }

    /* Clase para forzar textos claros que no son blanco puro */
    .text-light-muted { color: #cbd5e1 !important; } /* Slate 300 */

    /* Inputs oscuros personalizados */
    .dark-input {
        background-color: #1e293b !important; /* Slate 800 */
        border: 2px solid #334155 !important; /* Slate 700 */
        color: #fff !important;
    }
    .dark-input:focus {
        background-color: #0f172a !important;
        border-color: #38bdf8 !important; /* Sky 400 */
        box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.2);
    }
    .dark-input:disabled {
        background-color: #0f172a !important;
        opacity: 0.7;
        cursor: not-allowed;
    }
    .input-group-text.dark-input {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    /* Placeholder color */
    .dark-input::placeholder { color: #64748b !important; opacity: 1; }

    /* Botones Hover */
    .hover-effect-btn:hover { background-color: rgba(255,255,255,0.1); color: #fff; }

    /* --- WIZARD STYLES --- */
    .wizard-header {
        background: transparent; /* Sin fondo */
        border-bottom: 2px solid #334155;
    }

    /* Indicadores de pasos (Más grandes y claros) */
    .step-indicator {
        display: flex; flex-direction: column; align-items: center; width: 33%; cursor: default;
        position: relative; z-index: 10;
    }
    .step-icon {
        width: 50px; height: 50px; border-radius: 50%; background-color: #1e293b; color: #94a3b8;
        display: flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 12px;
        transition: all 0.3s; border: 4px solid #0f172a; font-size: 1.4rem;
    }
    .step-label { font-size: 0.9rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }

    /* Pasos Activos y Completados */
    .step-indicator.active .step-icon { background-color: #38bdf8; color: #0f172a; box-shadow: 0 0 25px rgba(56,189,248,0.6); border-color: #38bdf8; }
    .step-indicator.active .step-label { color: #f1f5f9; }
    .step-indicator.completed .step-icon { background-color: #10b981; color: #fff; border-color: #10b981; }
    .step-indicator.completed .step-label { color: #10b981; }

    /* Botones de Modalidad (Radio Cards Transparentes) */
    .btn-check:checked + .mode-card {
        background-color: rgba(56, 189, 248, 0.2) !important; /* Fondo azul muy transparente */
        border: 2px solid #38bdf8 !important; color: #fff !important;
        box-shadow: 0 8px 20px rgba(56, 189, 248, 0.25); transform: translateY(-5px);
    }
    .mode-card {
        border: 2px solid #334155 !important; transition: all 0.3s; background-color: transparent !important; color: #cbd5e1;
    }
    .mode-card:hover {
        border-color: #94a3b8 !important; background-color: rgba(255,255,255,0.05) !important; color: #fff; transform: translateY(-5px);
    }
    .mode-card i { color: #38bdf8; } /* Iconos azules */

    /* Animación de pasos */
    .wizard-step { display: none; animation: fadeInUp 0.5s ease-out; }
    .wizard-step.active { display: block; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* Switch personalizado más grande */
    .form-switch .form-check-input {
        background-color: #334155; border-color: #475569;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }
    .form-switch .form-check-input:checked {
        background-color: #38bdf8; border-color: #38bdf8;
    }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 0;
    const steps = document.querySelectorAll('.wizard-step');
    const stepIndicators = document.querySelectorAll('.step-indicator');
    const progressBar = document.getElementById('progressBarFill');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Inputs Clave
    const modeRadios = document.querySelectorAll('input[name="mode"]');
    const imagenSelect = document.getElementById('imagen');
    const deckSelect = document.getElementById('deck');
    const configSelect = document.getElementById('configuration');
    const noteInput = document.getElementById('note');

    // Inputs Región y Provincia
    const regionSelect = document.getElementById('region_id');
    const provinceSelect = document.getElementById('province_id');
    const oldProvinceId = document.getElementById('old_province_id').value;

    // Datos dinámicos
    const optionsMap = {
        deck: {
            all: [{val: '3on3', txt: '3on3'}, {val: '5g', txt: '5G'}],
            bx_rank: [{val: '3on3', txt: '3on3 (Oficial)'}]
        },
        config: {
            all: [
                {val: 'SingleElimination', txt: 'Eliminación Simple'},
                {val: 'DoubleElimination', txt: 'Doble Eliminación'},
                {val: 'RoundRobin', txt: 'Round Robin'},
                {val: 'Swiss', txt: 'Suizo'},
                {val: 'FreeForAll', txt: 'Free For All'}
            ],
        }
    };

    // Actualizar UI del Wizard
    function updateUI() {
        steps.forEach((s, i) => s.classList.toggle('active', i === currentStep));
        stepIndicators.forEach((ind, i) => {
            ind.classList.toggle('active', i === currentStep);
            ind.classList.toggle('completed', i < currentStep);
        });
        progressBar.style.width = `${((currentStep) / (steps.length - 1)) * 100}%`;

        prevBtn.disabled = currentStep === 0;
        if (currentStep === steps.length - 1) {
            nextBtn.classList.add('d-none');
            submitBtn.classList.remove('d-none');
        } else {
            nextBtn.classList.remove('d-none');
            submitBtn.classList.add('d-none');
        }
        window.scrollTo(0, 0);
    }

    // Lógica de negocio (Reglas del Torneo)
    function updateLogic() {
        const mode = document.querySelector('input[name="mode"]:checked')?.value;
        let type = imagenSelect.value;
        if(!mode) return; // Si no hay modo elegido, salimos

        // 🔥 1. RESTRICCIÓN DE CATEGORÍA SEGÚN MODALIDAD
        Array.from(imagenSelect.options).forEach(opt => {
            // "Quedada" y el placeholder vacío siempre están disponibles
            if (opt.value === '' || opt.value === 'quedada') {
                opt.hidden = false;
                opt.disabled = false;
            } else {
                // Si no es Beyblade X, escondemos rankings y copas
                if (mode !== 'beybladex') {
                    opt.hidden = true;
                    opt.disabled = true;
                } else {
                    opt.hidden = false;
                    opt.disabled = false;
                }
            }
        });

        // Si cambió de X a un modo clásico y tenía una copa/ranking, forzamos a Quedada
        if (mode !== 'beybladex' && type !== 'quedada' && type !== '') {
            imagenSelect.value = 'quedada';
            type = 'quedada'; // Actualizamos la variable para que el resto del script lo sepa
        }

        if(!type) return;

        // Recuperar selects
        let currentDeck = deckSelect.value;
        if (!currentDeck && Array.from(deckSelect.options).find(opt => opt.dataset.old === '1')) {
            currentDeck = Array.from(deckSelect.options).find(opt => opt.dataset.old === '1').value;
        }

        let currentConfig = configSelect.value;
        if (!currentConfig && Array.from(configSelect.options).find(opt => opt.dataset.old === '1')) {
            currentConfig = Array.from(configSelect.options).find(opt => opt.dataset.old === '1').value;
        }

        deckSelect.innerHTML = '';
        configSelect.innerHTML = '';

        const isBxRank = (mode === 'beybladex' && (type === 'ranking' || type === 'rankingplus'));
        const deckOpts = isBxRank ? optionsMap.deck.bx_rank : optionsMap.deck.all;
        const confOpts = optionsMap.config.all; // Formatos libres

        deckOpts.forEach(o => deckSelect.add(new Option(o.txt, o.val, false, o.val === currentDeck)));
        confOpts.forEach(o => configSelect.add(new Option(o.txt, o.val, false, o.val === currentConfig)));

        // 🔥 AUTO-RELLENADO PARA COPAS ESPECIALES
        const nameInput = document.getElementById('name');
        noteInput.readOnly = false;

        // Plantillas automáticas
        if(type === 'copaespecial1') {
            if(!nameInput.value) nameInput.value = "Gran Copa de Split 1";
            noteInput.value = "Reglamento de la copa de split 1";
        } else if (type === 'copaespecial2') {
            if(!nameInput.value) nameInput.value = "Gran Copa de Split 2";
            noteInput.value = "Reglamento de la copa de split 2";
        } else if (type === 'copaespecial3') {
            if(!nameInput.value) nameInput.value = "Gran Copa de Split 3";
            noteInput.value = "Reglamento de la copa de split 3";
        } else if (type === 'copaespecial4') { // Corregido: antes ponía copaespecial3 de nuevo
            if(!nameInput.value) nameInput.value = "Gran Copa de Split 4";
            noteInput.value = "Reglamento de la copa de split 4";
        } else if (type === 'hasbro') {
            if(!nameInput.value) nameInput.value = "Torneo Formato Hasbro";
            noteInput.value = "🛑 Exclusivo Hasbro: Prohibido usar estadios y piezas de Takara Tomy.";
        } else if (type === 'simulacro') {
            if(!nameInput.value) nameInput.value = "Torneo Simulacro Ranking";
            noteInput.value = "Torneo de simulacro de ranking para aquellas personas que quieran ser Juez.";
        } else {
            // Limpiar si el usuario cambia de copa especial a torneo normal
            if (noteInput.value.includes('Reglamento de la copa') || noteInput.value.includes('🛑 Exclusivo')) {
                noteInput.value = "";
                nameInput.value = "";
            }
        }
    }

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

    // --- NUEVO: Lógica de Regiones y Provincias ---
    function updateProvinces() {
        const selectedOption = regionSelect.options[regionSelect.selectedIndex];

        // Limpiamos el select de provincias
        provinceSelect.innerHTML = '<option value="" disabled selected>Selecciona Provincia...</option>';

        if (selectedOption.value) {
            provinceSelect.disabled = false;

            // Leemos el JSON de provincias incrustado en el atributo data-provinces
            try {
                const provinces = JSON.parse(selectedOption.dataset.provinces);

                provinces.forEach(province => {
                    const isSelected = oldProvinceId == province.id;
                    provinceSelect.add(new Option(province.name, province.id, false, isSelected));
                });
            } catch(e) {
                console.error("Error leyendo provincias", e);
            }
        } else {
            provinceSelect.disabled = true;
        }
    }

    // Event Listeners
    modeRadios.forEach(r => r.addEventListener('change', updateLogic));
    imagenSelect.addEventListener('change', updateLogic);
    regionSelect.addEventListener('change', updateProvinces);

    nextBtn.addEventListener('click', () => {
        const inputs = steps[currentStep].querySelectorAll('input, select, textarea');
        let valid = true;
        // Validación manual simple para mostrar los mensajes del navegador
        inputs.forEach(i => { if(!i.checkValidity()) { i.reportValidity(); valid = false; return; } });

        if(valid) { currentStep++; updateUI(); }
    });

    prevBtn.addEventListener('click', () => { if(currentStep > 0) { currentStep--; updateUI(); } });

    // Inicialización
    updateUI();

    // Intentar ejecutar lógica inicial por si hay valores pre-cargados (old inputs)
    if(document.querySelector('input[name="mode"]:checked') && imagenSelect.value) {
        updateLogic();
    }

    // Inicializar provincias si la región ya estaba seleccionada (por ejemplo tras un error de validación)
    if(regionSelect.value) {
        updateProvinces();
    }
});
</script>
@endsection
