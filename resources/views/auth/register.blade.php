@extends('layouts.app')

@section('styles')
    <style>
        body {
            background-color: #0d1b2a;
            color: #f8f9fa;
        }

        .login-wrapper {
            background-color: #13293d;
            padding: 3rem 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.4);
        }

        .form-control {
            background-color: #1e2d3d;
            border: 1px solid #3a4c63;
            color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #1e2d3d;
            border-color: #4dabf7;
            color: #fff;
        }

        .form-label,
        .form-check-label {
            color: #dee2e6;
        }

        .btn-primary {
            background-color: #4dabf7;
            border: none;
        }

        .btn-primary:hover {
            background-color: #339af0;
        }

        .text-primary {
            color: #4dabf7 !important;
        }

        a.text-primary:hover {
            color: #ffffff !important;
        }
    </style>
@endsection

@section('content')
<div id="app" class="container py-5" style="min-height: 70vh;">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-8 col-lg-6">
            <div class="login-wrapper">
                <h3 class="text-center mb-4 text-primary fw-bold">Registro de Usuario</h3>

                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de usuario</label>
                        <input id="name" type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email" type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Confirmar Password --}}
                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">Confirmar contraseña</label>
                        <input id="password-confirm" type="password" class="form-control"
                               name="password_confirmation" required autocomplete="new-password">
                    </div>

                    {{-- Botón --}}
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">
                            Registrarse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
