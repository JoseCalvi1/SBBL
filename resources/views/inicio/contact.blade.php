@extends('layouts.app')

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

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contacto.enviar') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="motivo">Motivo de contacto</label>
                        <select name="motivo" id="motivo" class="form-control" required>
                            <option value="" disabled selected>Selecciona un motivo</option>
                            <option value="Duda general">Duda general</option>
                            <option value="Problemas técnicos">Problemas técnicos</option>
                            <option value="Colaboración">Colaboración</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="mensaje">Mensaje</label>
                        <textarea name="mensaje" id="mensaje" rows="6" class="form-control" required></textarea>
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
