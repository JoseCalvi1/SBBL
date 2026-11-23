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
                <h3 class="text-center mb-3 text-primary fw-bold">Verifica tu correo electrónico</h3>

                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.
                    </div>
                @endif

                <p class="mb-3">
                    Antes de continuar, revisa tu bandeja de entrada para confirmar tu correo electrónico mediante el enlace que te hemos enviado.
                </p>
                <p class="mb-0 text-muted small">
                    ¿No has recibido el correo?
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link text-primary p-0 m-0 align-baseline">
                            Pulsa aquí para solicitar otro enlace
                        </button>.
                    </form>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
