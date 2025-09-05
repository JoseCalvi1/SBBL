@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-2 mb-2 text-white" style="background-color: unset">
                <div class="card-header">Crear Nuevo Equipo</div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="equipoWizard" method="POST" action="{{ route('equipos.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Barra de progreso --}}
                        <div class="progress mb-4" style="height: 20px;">
                            <div class="progress-bar bg-success" id="wizardProgress" style="width: 33%">Paso 1 de 3</div>
                        </div>

                        {{-- Paso 1: Indicaciones --}}
                        <div class="wizard-step active" data-step="1">
                            <p class="text-white">
                                Antes de crear tu equipo, ten en cuenta lo siguiente:
                            </p>
                            <ul class="text-white">
                                <li>El nombre debe ser único y representativo.</li>
                                <li>Se recomienda usar un logo e imagen propios.</li>
                                <li>El equipo será revisado por árbitros antes de validarse.</li>
                                <li>Queda prohibido el uso de nombres, descripciones e imágenes que inciten o promuevan el odio.</li>
                                <li>Las imágenes deben adaptarse al formato de las plantillas a descargar.</li>
                            </ul>

                            <img src="{{ url('/../images/Guia_Escudo.png') }}" alt="Info" style="width: 300px;">
                            <img src="{{ url('/../images/FondoEquipos.png') }}" alt="Info" style="width: 300px;">

                            <p class="text-white">
                                Descarga la guía de creación de equipos aquí:
                                <a href="{{ url('/../images/PlantillaEscudosEQUIPOS.rar') }}" class="btn btn-outline-info btn-sm" download>📄 Descargar Plantillas</a>
                            </p>
                        </div>

                        {{-- Paso 2: Datos del equipo --}}
                        <div class="wizard-step" data-step="2">
                            <div class="form-group mt-2">
                                <label for="name">Nombre del Equipo:</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="form-group mt-2">
                                <label for="description">Descripción:</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <div class="form-group mt-2" style="color: white;">
                                <label for="logo">Logo del equipo:</label>
                                <input type="file" class="form-control-file" id="logo" name="logo" accept="image/*">
                            </div>

                            <div class="form-group mt-2" style="color: white;">
                                <label for="image">Imagen de equipo:</label>
                                <input type="file" class="form-control-file" id="image" name="image" accept="image/*">
                            </div>
                        </div>

                        {{-- Paso 3: Confirmación --}}
                        <div class="wizard-step" data-step="3">
                            <div class="alert alert-warning text-dark">
                                <h5>⚠️ Aviso</h5>
                                <p>
                                    Una vez creado, tu equipo pasará a revisión por parte de los árbitros.
                                    Solo será visible y estará activo cuando uno de ellos lo valide.
                                </p>
                            </div>
                        </div>

                        {{-- Botones de navegación --}}
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn" disabled>← Anterior</button>
                            <button type="button" class="btn btn-primary" id="nextBtn">Siguiente →</button>
                            <button type="submit" class="btn btn-success d-none" id="submitBtn">Validar Equipo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

    // Inicializar
    updateWizard();
});
</script>
@endsection

@section('styles')
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
