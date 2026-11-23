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
                <h3 class="text-center mb-3 text-primary fw-bold">Confirmar contraseña</h3>
                <p class="text-center text-muted small mb-4">
                    Por seguridad, confirma tu contraseña actual antes de continuar.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input
                            id="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" class="btn btn-primary">
                            Confirmar contraseña
                        </button>

                        @if (Route::has('password.request'))
                            <a class="text-primary small" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
