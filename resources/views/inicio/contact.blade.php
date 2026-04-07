@extends('layouts.app')

@section('title', 'Contacto - SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: CONTACTO TÁCTICO (Hereda de layout)
       ==================================================================== */

    /* ── TÍTULO DE PÁGINA ── */
    .page-title {
        font-family: 'Oswald', cursive;
        font-size: 3rem;
        color: var(--sbbl-gold);
        text-shadow: 2px 2px 0px #000, 4px 4px 0px var(--shonen-red);
        letter-spacing: 2px;
        margin-bottom: 2rem;
    }

    /* ── FORMULARIO ── */
    .form-label {
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        color: var(--shonen-cyan);
        letter-spacing: 1px;
    }

    .form-control, .form-select {
        background-color: #000 !important;
        border: 2px solid #333 !important;
        color: #fff !important;
        border-radius: 0 !important;
        font-weight: 900;
        padding: 10px;
    }

    .form-control::placeholder { color: #555; }

    .form-control:focus, .form-select:focus {
        border-color: var(--sbbl-gold) !important;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3) !important;
    }

    /* Oculta el campo trampa visualmente (Honeypot) */
    .honey-pot {
        display: none;
        visibility: hidden;
    }

    /* Centrar el contenedor del Captcha */
    .captcha-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
        padding: 10px;
        background: #000;
        border: 2px dashed #333;
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    <div class="text-center mb-4">
        <h1 class="page-title"><i class="fas fa-satellite-dish me-2 text-white" style="text-shadow:none;"></i> CENTRO DE COMUNICACIONES</h1>
        <p class="text-white fw-bold">Ponte en contacto con la Liga Española de Beybattle...</p>
    </div>

    <div class="row justify-content-center align-items-center g-5">

        {{-- PANEL IZQUIERDO: DISCORD WIDGET --}}
        <div class="col-md-5 text-center">
            <div class="command-panel p-4" style="background: var(--sbbl-blue-3);">
                <h3 class="font-Oswald text-white mb-3" style="font-size: 2rem; letter-spacing: 1px;">ÚNETE A LA COMUNIDAD</h3>
                <p class="text-white fw-bold small mb-4">La respuesta más rápida la encontrarás en nuestro servidor oficial.</p>
                <div style="border: 4px solid #000; box-shadow: 6px 6px 0 #000; display: inline-block;">
                    <iframe src="https://discord.com/widget?id=875324662010228746&theme=dark" width="100%" height="450" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts" style="display: block;"></iframe>
                </div>
            </div>
         </div>

        {{-- PANEL DERECHO: FORMULARIO DE CONTACTO --}}
        <div class="col-md-7">
            <div class="command-panel p-0">

                <div class="panel-header border-bottom border-warning">
                    <span><i class="fas fa-envelope me-2" style="color: var(--sbbl-gold);"></i> MENSAJE DE CONTACTO</span>
                </div>

                <div class="p-4 p-md-5">
                    {{-- MENSAJES DE ÉXITO --}}
                    @if(session('success'))
                        <div class="alert alert-shonen alert-shonen-success d-flex align-items-center text-center justify-content-center mb-4">
                            <div><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</div>
                        </div>
                    @endif

                    {{-- MENSAJES DE ERROR (Validación y Captcha) --}}
                    @if ($errors->any())
                        <div class="alert alert-shonen alert-shonen-danger mb-4">
                            <div class="d-flex align-items-center mb-2"><i class="fas fa-exclamation-triangle me-2"></i> <strong>ERROR DE TRANSMISIÓN:</strong></div>
                            <ul class="mb-0 ps-3 fw-bold small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contacto.enviar') }}">
                        @csrf

                        {{-- --- TRAMPA PARA BOTS (HONEYPOT) --- --}}
                        <div class="honey-pot">
                            <label for="website_check">Si eres humano, deja este campo vacío</label>
                            <input type="text" name="website_check" id="website_check" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-group mb-4">
                            <label for="nombre" class="form-label">NOMBRE</label>
                            <div class="input-group">
                                <span class="input-group-text bg-black border-dark text-secondary rounded-0"><i class="fas fa-user"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej. Valt Aoi" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="email" class="form-label">EMAIL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-black border-dark text-secondary rounded-0"><i class="fas fa-at"></i></span>
                                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="tu@correo.com" required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="motivo" class="form-label">MOTIVO</label>
                            <select name="motivo" id="motivo" class="form-select" required>
                                <option value="" disabled selected>Selecciona el tipo de informe...</option>
                                <option value="Duda general" {{ old('motivo') == 'Duda general' ? 'selected' : '' }}>Duda general</option>
                                <option value="Problemas técnicos" {{ old('motivo') == 'Problemas técnicos' ? 'selected' : '' }}>Problemas técnicos</option>
                                <option value="Colaboración" {{ old('motivo') == 'Colaboración' ? 'selected' : '' }}>Colaboración / Patrocinio</option>
                                <option value="Sugerencias" {{ old('motivo') == 'Sugerencias' ? 'selected' : '' }}>Sugerencias tácticas</option>
                                <option value="Otros" {{ old('motivo') == 'Otros' ? 'selected' : '' }}>Otros</option>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="mensaje" class="form-label">MENSAJE</label>
                            <textarea name="mensaje" id="mensaje" rows="5" class="form-control" placeholder="Escribe aquí tu mensaje detallado..." required>{{ old('mensaje') }}</textarea>
                        </div>

                        {{-- GOOGLE RECAPTCHA V2 --}}
                        <div class="captcha-wrapper">
                            <div class="g-recaptcha" data-sitekey="6LcShF8sAAAAAHSqGNgrFi3wuChEq64RN2Pmi-EN"></div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn-shonen btn-shonen-warning w-100" style="padding: 15px; font-size: 1.5rem;" onclick="this.innerHTML='<span><i class=\'fas fa-spinner fa-spin me-2\'></i> ENVIANDO TRANSMISIÓN...</span>'">
                                <span><i class="fas fa-paper-plane me-2"></i> ENVIAR MENSAJE</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {{-- SCRIPT OBLIGATORIO DE GOOGLE RECAPTCHA --}}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection
