{{-- login.blade.php --}}
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
                <h3 class="text-center mb-4 text-primary fw-bold">Inicia sesión</h3>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Remember Me + Forgot Password --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a class="text-decoration-none small text-primary" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">
                            Entrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
