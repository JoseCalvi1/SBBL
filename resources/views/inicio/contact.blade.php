@extends('layouts.app')

@section('title', 'Sección de contacto')

@section('styles')
<style>
    .contact-form {
        background: #1d2a3a;
        color: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }

    .form-control {
        background-color: #2e3e52;
        border: none;
        color: white;
    }

    .form-control::placeholder {
        color: #cfd3db;
    }

    .btn-enviar {
        background-color: #ffc107 !important;
        border: none;
        font-weight: bold;
    }

    .btn-enviar:hover {
        background-color: #e0a800;
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
        margin-bottom: 20px;
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 text-center">
            <h3 class="text-white">Liga Española de Beybattle</h3>
            <iframe src="https://discord.com/widget?id=875324662010228746&theme=dark" width="400" height="550" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
         </div>
        <div class="col-md-7">
            <div class="contact-form">
                <h2 class="text-center mb-4">Contacto</h2>

                {{-- MENSJES DE ÉXITO --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- MENSAJES DE ERROR (Validación y Captcha) --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('contacto.enviar') }}">
                    @csrf

                    {{-- --- TRAMPA PARA BOTS (HONEYPOT) --- --}}
                    {{-- Si un bot rellena esto, lo bloqueamos en el controlador --}}
                    <div class="honey-pot">
                        <label for="website_check">Si eres humano, deja este campo vacío</label>
                        <input type="text" name="website_check" id="website_check" tabindex="-1" autocomplete="off">
                    </div>
                    {{-- ----------------------------------- --}}

                    <div class="form-group mb-3">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="motivo">Motivo de contacto</label>
                        <select name="motivo" id="motivo" class="form-control" required>
                            <option value="" disabled selected>Selecciona un motivo</option>
                            <option value="Duda general">Duda general</option>
                            <option value="Problemas técnicos">Problemas técnicos</option>
                            <option value="Colaboración">Colaboración</option>
                            <option value="Sugerencias">Sugerencias</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="mensaje">Mensaje</label>
                        <textarea name="mensaje" id="mensaje" rows="6" class="form-control" required>{{ old('mensaje') }}</textarea>
                    </div>

                    {{-- GOOGLE RECAPTCHA V2 --}}
                    <div class="captcha-wrapper">
                        {{-- Tu clave de sitio --}}
                        <div class="g-recaptcha" data-sitekey="6LcShF8sAAAAAHSqGNgrFi3wuChEq64RN2Pmi-EN"></div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-enviar px-4 py-2">Enviar mensaje</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {{-- LIBRERÍAS DE BOOTSTRAP/JQUERY --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

    {{-- SCRIPT OBLIGATORIO DE GOOGLE RECAPTCHA --}}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection
