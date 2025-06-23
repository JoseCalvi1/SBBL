@extends('layouts.app')

@section('content')
<a href="{{ route('inicio.events') }}" class="btn btn-outline-primary m-4">← Volver</a>
<h2 class="text-center mb-4 text-white">Crear nuevo evento</h2>

<div class="container bg-dark text-white rounded p-4 mb-5">
    <form id="eventWizard" method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Barra de progreso --}}
        <div class="progress mb-4" style="height: 20px;">
            <div class="progress-bar bg-success" id="wizardProgress" style="width: 33%">Paso 1 de 3</div>
        </div>

        {{-- Paso 1: Básico --}}
        <div class="wizard-step active" data-step="1">
            @if (Auth::user()->is_admin || Auth::user()->is_referee)
                <div class="form-group">
                    <label for="name">Título del evento</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Ej. Torneo Regional">
                </div>

                <div class="form-group">
                    <label for="image_mod">Imagen personalizada</label>
                    <input type="file" class="form-control-file" id="image_mod" name="image_mod" accept="image/*">
                </div>
            @endif

            <div class="form-group">
                <label for="mode">Modalidad <span style="color: red;">*</span></label>
                <select name="mode" id="mode" class="form-control" required>
                    <option value="" disabled selected>- Selecciona -</option>
                    <option value="beybladex">Beyblade X</option>
                    <option value="beybladeburst">Beyblade Burst</option>
                    <option value="beyblademetal">Beyblade Metal</option>
                    <option value="beybladebakuten">Beyblade Bakuten Shoot</option>
                </select>
            </div>

            <div class="form-group">
                <label for="imagen">Categoría <span style="color: red;">*</span></label>
                <select name="imagen" id="imagen" class="form-control" required>
                    <option value="" disabled selected>- Selecciona -</option>
                    <option value="quedada">Quedada</option>
                    <option value="ranking">Ranking</option>
                    @if (Auth::user()->is_admin || Auth::user()->is_referee)
                        <option value="rankingplus">Ranking Plus</option>
                        <option value="grancopa">Gran Copa</option>
                    @endif
                    <option value="hasbro">Hasbro</option>
                    <option value="copalloros">Copa Lloros</option>
                    <option value="copaligera">Copa Ligera</option>
                </select>
            </div>
        </div>

        {{-- Paso 2: Configuración --}}
        <div class="wizard-step" data-step="2">
            <div class="form-group">
                <label for="deck">Deck <span style="color: red;">*</span></label>
                <select name="deck" id="deck" class="form-control" required>
                    <option value="" disabled selected>- Selecciona -</option>
                    <option value="3on3">3on3</option>
                    <option value="5g">5G</option>
                </select>
            </div>

            <div class="form-group">
                <label for="configuration">Formato del torneo <span style="color: red;">*</span></label>
                <select name="configuration" id="configuration" class="form-control" required>
                    <option value="" disabled selected>- Selecciona -</option>
                    <option value="SingleElimination">Eliminación simple</option>
                    <option value="DoubleElimination">Eliminación doble</option>
                    <option value="RoundRobin">Round Robin</option>
                    <option value="Swiss">Suizo</option>
                    <option value="FreeForAll">Free For All</option>
                    <option value="Leaderboard">Leaderboard</option>
                </select>
            </div>
        </div>

        {{-- Paso 3: Detalles --}}
        <div class="wizard-step" data-step="3">
            <div class="form-group">
                <label for="region_id">Región <span style="color: red;">*</span></label>
                <select name="region_id" id="region_id" class="form-control" required>
                    <option value="" disabled selected>- Selecciona -</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="city">Ciudad <span style="color: red;">*</span></label>
                <input type="text" name="city" id="city" class="form-control" placeholder="Ej. Madrid" required>
            </div>

            <div class="form-group">
                <label for="location">Lugar específico <span style="color: red;">*</span></label>
                <input type="text" name="location" id="location" class="form-control" placeholder="Ej. Parque del Retiro" required>
            </div>

            <div class="form-group">
                <label for="note">Notas</label>
                <textarea name="note" id="note" rows="3" class="form-control" placeholder="Información adicional..."></textarea>
            </div>

            <div class="form-group">
                <label for="event_date">Fecha <span style="color: red;">*</span></label>
                <input type="date" name="event_date" id="event_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="event_time">Hora <span style="color: red;">*</span></label>
                <input type="time" name="event_time" id="event_time" class="form-control" required>
            </div>

            @if(!Auth::user()->is_admin && !Auth::user()->is_referee)
                <div class="alert alert-warning text-dark">
                    Al crear este evento, afirmo que he leído <a href="https://sbbl.es/rules" target="_blank">las normas</a> y soy responsable del material necesario.
                </div>
            @endif
        </div>

        {{-- Botones de navegación --}}
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-secondary" id="prevBtn" disabled>← Anterior</button>
            <button type="button" class="btn btn-primary" id="nextBtn">Siguiente →</button>
            <button type="submit" class="btn btn-success d-none" id="submitBtn">Guardar evento</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 0;
    const steps = document.querySelectorAll('.wizard-step');
    const progressBar = document.getElementById('wizardProgress');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Selectores
    const modeSelect = document.getElementById('mode');
    const imagenSelect = document.getElementById('imagen');
    const deckSelect = document.getElementById('deck');
    const configurationSelect = document.getElementById('configuration');
    const noteInput = document.getElementById('note'); // asegúrate que existe este input en el form

    // Opciones originales para deck y configuración
    const deckOptions = {
        all: [
            {value: '3on3', text: '3on3'},
            {value: '5g', text: '5G'}
        ],
        beybladex_ranking: [
            {value: '3on3', text: '3on3'}
        ]
    };

    const configurationOptions = {
        all: [
            {value: 'SingleElimination', text: 'Eliminación simple'},
            {value: 'DoubleElimination', text: 'Eliminación doble'},
            {value: 'RoundRobin', text: 'Round Robin'},
            {value: 'Swiss', text: 'Suizo'},
            {value: 'FreeForAll', text: 'Free For All'},
            {value: 'Leaderboard', text: 'Leaderboard'}
        ],
        beybladex_ranking: [
            {value: 'SingleElimination', text: 'Eliminación simple'},
            {value: 'DoubleElimination', text: 'Eliminación doble'}
        ]
    };

    function updateWizard() {
        steps.forEach((step, i) => {
            step.classList.toggle('active', i === currentStep);
        });

        progressBar.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
        progressBar.innerText = `Paso ${currentStep + 1} de ${steps.length}`;

        prevBtn.disabled = currentStep === 0;
        nextBtn.classList.toggle('d-none', currentStep === steps.length - 1);
        submitBtn.classList.toggle('d-none', currentStep !== steps.length - 1);
    }

    // Actualiza opciones de deck y configuración según modalidad y categoría
    function updateDeckAndConfiguration() {
        const mode = modeSelect.value;
        const imagen = imagenSelect.value;

        // Limpiar opciones actuales
        deckSelect.innerHTML = '<option value="" disabled selected>- Selecciona -</option>';
        configurationSelect.innerHTML = '<option value="" disabled selected>- Selecciona -</option>';

        // 🔒 Si es beybladeburst, forzar imagen = quedada y desactivar el selector
        if (mode === 'beybladeburst' || mode === 'beyblademetal' || mode === 'beybladebakuten') {
            imagenSelect.value = 'quedada';

            // Desactivar las demás opciones excepto quedada
            Array.from(imagenSelect.options).forEach(option => {
                option.disabled = option.value !== 'quedada';
            });

        } else {
            // Reactivar todas las opciones si no es beybladeburst
            Array.from(imagenSelect.options).forEach(option => {
                option.disabled = false;
            });
        }

        // ⚙️ Configuraciones específicas para beybladex + ranking/rankingplus
        if (mode === 'beybladex' && (imagen === 'ranking' || imagen === 'rankingplus')) {
            deckOptions.beybladex_ranking.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                deckSelect.appendChild(option);
            });

            configurationOptions.beybladex_ranking.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                configurationSelect.appendChild(option);
            });

        } else {
            // Otras combinaciones: permitir todo
            deckOptions.all.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                deckSelect.appendChild(option);
            });

            configurationOptions.all.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                configurationSelect.appendChild(option);
            });
        }

        // Resetear selección para evitar inconsistencias
        deckSelect.value = "";
        configurationSelect.value = "";
    }


    // Actualiza campo nota según categoría
    function updateNoteField() {
        if (imagenSelect.value === "copalloros") {
            noteInput.value = "Blades Baneados: Silver Wolf, Wizard Rod. Bits Baneados: Ball, Free Ball, Orb, Elevate";
            noteInput.disabled = true;
        } else if (imagenSelect.value === "copaligera") {
            noteInput.value = "Blades permitidos : Wizard Arrow • Star Scream • Knight Shield • Optimus Prime • Iron Man • Luke Skywalker • Knight Lance • Thanos • Darth Vader • Leon Claw • The Mandalorian • Rhino Horn • Wyvern Gale • Sphinx Cowl • Black Shell • Shinobi Shadow • Ghost Circle • Tusk Mammoth • Savage Bear • Steel Samurai • Yell Kong • Knife Shinobi • Shelter Drake • Dranzer • Drigger • Draciel";
            noteInput.disabled = true;
        } else {
            noteInput.value = "";
            noteInput.disabled = false;
        }
    }

    // Eventos para actualizar dinámicamente
    modeSelect.addEventListener('change', () => {
        updateDeckAndConfiguration();
    });

    imagenSelect.addEventListener('change', () => {
        updateDeckAndConfiguration();
        updateNoteField();
    });

    nextBtn.addEventListener('click', () => {
        const currentFields = steps[currentStep].querySelectorAll('input, select, textarea');
        for (let field of currentFields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                return;
            }
        }

        if (currentStep < steps.length - 1) {
            currentStep++;
            updateWizard();
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            updateWizard();
        }
    });

    // Inicialización al cargar
    updateWizard();
    updateDeckAndConfiguration();
    updateNoteField();
});
</script>

<style>
.wizard-step {
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}
.wizard-step.active {
    display: block;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>
@endsection
