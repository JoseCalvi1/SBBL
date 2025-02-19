@extends('layouts.app')

@section('content')
@if (Auth::user()->is_referee || Auth::user()->is_admin)
<div class="container py-4" style="color: white; border-radius: 10px;">
    <h1 class="text-center mb-4" style="font-weight: bold;">Panel de Administración</h1>
    <div class="row g-4">
        <!-- Eventos -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-calendar-alt fa-3x mb-3" style="color: #4e73df;"></i>
                    <h5 class="card-title">Eventos</h5>
                    <p class="card-text">Gestiona los eventos de la comunidad.</p>
                    <div class="mt-auto">
                        <a href="{{ route('events.indexAdmin') }}" class="btn btn-outline-primary w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duelos -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fa fa-gamepad fa-3x mb-3" style="color: #4e73df;"></i>
                    <h5 class="card-title">Duelos</h5>
                    <p class="card-text">Controla los enfrentamientos entre jugadores.</p>
                    <div class="mt-auto">
                        <a href="{{ route('versus.index') }}" class="btn btn-outline-primary w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipos Duelos -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-users-cog fa-3x mb-3" style="color: #4e73df;"></i>
                    <h5 class="card-title">Equipos Duelos</h5>
                    <p class="card-text">Gestiona los duelos de equipos.</p>
                    <div class="mt-auto">
                        <a href="{{ route('teams_versus.index') }}" class="btn btn-outline-primary w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipos -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-users fa-3x mb-3" style="color: #1cc88a;"></i>
                    <h5 class="card-title">Equipos</h5>
                    <p class="card-text">Administra los equipos registrados.</p>
                    <div class="mt-auto">
                        <a href="{{ route('equipos.indexAdmin') }}" class="btn btn-outline-success w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beyblades partes -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-tools fa-3x mb-3" style="color: #36b9cc;"></i>
                    <h5 class="card-title">Partes Beyblades</h5>
                    <p class="card-text">Gestiona las partes de beyblades.</p>
                    <div class="mt-auto">
                        <a href="{{ route('database.indexPartes') }}" class="btn btn-outline-info w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-tools fa-3x mb-3" style="color: #36b9cc;"></i>
                    <h5 class="card-title">Crear Beyblades</h5>
                    <p class="card-text">Gestiona los beyblades.</p>
                    <div class="mt-auto">
                        <a href="{{ route('database.indexBeys') }}" class="btn btn-outline-info w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        @if (Auth::user()->is_admin)
        <!-- Usuarios Burst -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-user-shield fa-3x mb-3" style="color: #e74a3b;"></i>
                    <h5 class="card-title">Usuarios Burst</h5>
                    <p class="card-text">Gestiona los usuarios de Beyblade Burst.</p>
                    <div class="mt-auto">
                        <a href="{{ route('profiles.indexAdmin') }}" class="btn btn-outline-danger w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuarios X -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-user-cog fa-3x mb-3" style="color: #e74a3b;"></i>
                    <h5 class="card-title">Usuarios X</h5>
                    <p class="card-text">Gestiona los usuarios de Beyblade X.</p>
                    <div class="mt-auto">
                        <a href="{{ route('profiles.indexAdminX') }}" class="btn btn-outline-danger w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asignaciones -->
        <div class="col-md-4 pb-2">
            <div class="card text-center bg-dark text-white h-100 shadow-lg">
                <div class="card-body d-flex flex-column">
                    <i class="fas fa-award fa-3x mb-3" style="color: #f4b400;"></i>
                    <h5 class="card-title">Asignaciones</h5>
                    <p class="card-text">Asigna trofeos y recompensas.</p>
                    <div class="mt-auto">
                        <a href="{{ route('trophies.index') }}" class="btn btn-outline-warning w-100">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@else
<script type="text/javascript">
    window.location = "/";
</script>
@endif
@endsection

@section('styles')
    <style>
        .btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
    </style>
@endsection
