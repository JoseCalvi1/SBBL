@extends('layouts.app')

@section('styles')
    <style>
        body {
            background-color: #0d1b2a;
            color: #f8f9fa;
        }
        .auth-wrapper {
            background-color: #13293d;
            padding: 2.5rem 2rem;
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
        .form-label {
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
    </style>
@endsection

@section('content')
<div class="container py-5" style="min-height: 70vh;">
    <div class="row justify-content-center align-items-center">
        <div class="col-md-8 col-lg-6">
            <div class="auth-wrapper">
                <h3 class="text-center mb-4 text-primary fw-bold">Restablecer contraseña</h3>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input
                            id="email"
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            name="email"
                            value="{{ $email ?? old('email') }}"
                            required
                            autocomplete="email"
                            autofocus
                        >
                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña (mín. 8 caracteres)</label>
                        <input
                            id="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        >
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password-confirm" class="form-label">Confirmar nueva contraseña</label>
                        <input
                            id="password-confirm"
                            type="password"
                            class="form-control"
                            name="password_confirmation"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary w-100">
                            Guardar nueva contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
