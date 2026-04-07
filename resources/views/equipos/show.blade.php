@extends('layouts.app')

@section('title', 'Control de equipo')

@section('styles')
<style>
    /* =========================================
       ESTILOS GENERALES Y PANELES SHONEN
       ========================================= */

    /* --- BANNER DE CUARTEL GENERAL --- */
    .hq-banner {
        position: relative;
        height: 280px;
        background-color: #000;
        background-size: cover;
        background-position: center;
        border-bottom: 4px solid var(--sbbl-gold);
        overflow: hidden;
        border-radius: 0 0 0 30px;
        box-shadow: 0 8px 15px rgba(0,0,0,0.8);
    }

    .hq-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.3) 100%);
        z-index: 1;
    }

    .hq-content {
        position: absolute; bottom: 0; left: 0; width: 100%;
        z-index: 2; padding: 20px 30px;
        display: flex; align-items: flex-end; flex-wrap: wrap;
        gap: 20px;
    }

    .hq-logo-container {
        width: 140px; height: 140px;
        background: #000;
        border: 3px solid var(--sbbl-gold);
        padding: 5px;
        box-shadow: 5px 5px 0 #000;
        transform: rotate(-5deg);
    }
    .hq-logo { width: 100%; height: 100%; object-fit: contain; transform: rotate(5deg); }

    .hq-title h1 {
        font-family: 'Oswald', cursive;
        color: var(--sbbl-gold);
        margin: 0;
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-red);
        font-size: 4rem; letter-spacing: 2px;
        line-height: 1;
    }

    /* --- PANELES TÁCTICOS --- */
    .intel-panel {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        border-radius: 0 20px 0 20px;
        box-shadow: 8px 8px 0 #000;
        padding: 25px;
        margin-bottom: 30px;
    }

    .level-box {
        background: #000;
        border: 3px solid var(--sbbl-gold);
        padding: 20px; border-radius: 0;
        text-align: center;
        box-shadow: 6px 6px 0 var(--sbbl-blue-3);
        height: 100%;
        transform: skewX(-5deg);
    }
    .level-box > * { transform: skewX(5deg); }

    .level-number { font-family: 'Oswald', cursive; font-size: 5rem; color: var(--sbbl-gold); line-height: 1; text-shadow: 3px 3px 0 #000; }

    .xp-bar { height: 15px; background: #222; border-radius: 0; margin-top: 15px; overflow: hidden; border: 2px solid #fff; }
    .xp-fill { height: 100%; background: var(--sbbl-gold); }

    /* =========================================
       CARTAS DE AGENTE (ROSTER) - SHONEN STYLE
       ========================================= */
    .agent-card {
        background-color: var(--sbbl-blue-3);
        border: 3px solid #000;
        border-radius: 0;
        overflow: hidden;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: 0.2s;
        box-shadow: 6px 6px 0 #000;
    }

    .agent-card:hover {
        transform: translate(-3px, -3px);
        border-color: var(--sbbl-gold);
        box-shadow: 9px 9px 0 var(--shonen-red);
        background-color: var(--sbbl-blue-2);
    }

    /* Cabecera de la carta (Imagen de fondo) */
    .agent-header {
        height: 120px;
        background-color: #000;
        background-size: cover;
        background-position: center;
        position: relative;
        border-bottom: 3px solid #000;
    }

    .agent-header::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 100%);
    }

    /* Contenedor Avatar 100% Circular */
    .agent-avatar-container {
        width: 80px;
        height: 80px;
        position: absolute;
        top: 75px; /* Se solapa entre header y body */
        left: 20px;
        z-index: 10;
    }

    .agent-avatar-img {
        width: 100%; height: 100%;
        border-radius: 50%;
        object-fit: cover;
        background-color: #000;
        border: 3px solid var(--sbbl-gold);
        box-shadow: 0 0 10px rgba(0,0,0,0.8);
    }

    .agent-avatar-frame {
        position: absolute; top: 0; left: 0;
        width: 100%; height: 100%;
        border-radius: 50%;
        pointer-events: none;
    }

    /* Cuerpo de la carta */
    .agent-body {
        padding: 45px 20px 20px 20px; /* Padding superior para dejar sitio al avatar */
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .agent-name {
        font-family: 'Oswald', cursive;
        font-size: 1.8rem;
        color: #fff;
        text-transform: uppercase;
        margin-bottom: 0;
        line-height: 1.1;
        letter-spacing: 1px;
        text-shadow: 2px 2px 0 #000;
    }

    .agent-role {
        font-size: 0.85rem;
        color: var(--shonen-cyan);
        font-weight: 900;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .captain-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--shonen-red); color: #fff;
        font-family: 'Oswald', cursive; font-size: 1.1rem; padding: 4px 12px;
        border: 2px solid #000; z-index: 20;
        text-transform: uppercase;
        box-shadow: 2px 2px 0 #000;
        transform: skewX(-10deg);
        letter-spacing: 1px;
    }
    .captain-badge > span { display: block; transform: skewX(10deg); }

    /* --- BOTONES DE ACCIÓN VISIBLES --- */
    .agent-actions {
        margin-top: auto;
        display: grid;
        gap: 10px;
    }

    .btn-action {
        font-family: 'Oswald', cursive;
        font-size: 1.1rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 8px;
        border-radius: 0;
        border: 3px solid #000;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: 0.2s;
        cursor: pointer;
        width: 100%;
        transform: skewX(-5deg);
    }
    .btn-action > * { transform: skewX(5deg); display: block;}

    /* Botón Ascender */
    .btn-promote { background-color: var(--sbbl-gold); color: #000; box-shadow: 3px 3px 0 #000; }
    .btn-promote:hover { background-color: #fff; transform: translate(-2px, -2px) skewX(-5deg); box-shadow: 5px 5px 0 var(--shonen-cyan); }

    /* Botón Expulsar */
    .btn-kick { background-color: var(--shonen-red); color: #fff; box-shadow: 3px 3px 0 #000; }
    .btn-kick:hover { background-color: #fff; color: #000; transform: translate(-2px, -2px) skewX(-5deg); box-shadow: 5px 5px 0 var(--shonen-red); }

    /* Botón Abandonar */
    .btn-leave { background-color: #000; border-color: var(--shonen-red); color: var(--shonen-red); box-shadow: 3px 3px 0 var(--shonen-red); }
    .btn-leave:hover { background-color: var(--shonen-red); color: white; border-color: #000; box-shadow: 3px 3px 0 #000; transform: translate(-2px, -2px) skewX(-5deg); }

    /* Ajuste móvil */
    @media (max-width: 768px) {
        .hq-content { flex-direction: column; text-align: center; align-items: center; }
        .agent-body { text-align: center; }
        .agent-avatar-container { left: 50%; transform: translateX(-50%); }
        .hq-logo-container { transform: none; }
        .hq-logo { transform: none; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0 mb-5">

    {{-- ALERTAS DEL SISTEMA --}}
    @if(session('success') || session('error'))
        <div class="container mt-3">
            @if(session('success'))
                <div class="alert alert-shonen alert-shonen-success"><span style="display:block; transform:skewX(2deg);"><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</span></div>
            @else
                <div class="alert alert-shonen alert-shonen-danger"><span style="display:block; transform:skewX(2deg);"><i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}</span></div>
            @endif
        </div>
    @endif

    {{-- 1. BANNER --}}
    @php
        $bgImage = $equipo->image ? "data:image/png;base64,{$equipo->image}" : asset('images/FONDO_BX.webp');
        $logoImage = $equipo->logo ? "data:image/png;base64,{$equipo->logo}" : asset('images/logo_new.png');
    @endphp

    <div class="hq-banner" style="background-image: url('{{ $bgImage }}');">
        <div class="hq-overlay"></div>
        <div class="container h-100 position-relative">
            <div class="hq-content">
                <div class="hq-logo-container">
                    <img src="{{ $logoImage }}" class="hq-logo">
                </div>
                <div class="hq-title mb-3">
                    <h1>{{ $equipo->name }}</h1>
                    <div class="mt-2">
                        <span class="badge bg-black border border-white text-white me-2 py-2 px-3 font-Oswald fs-5" style="box-shadow: 2px 2px 0 var(--sbbl-gold);"><i class="fas fa-users me-1"></i> {{ $miembros->count() }} OPERATIVOS</span>
                        <span class="badge bg-black border border-white text-white py-2 px-3 font-Oswald fs-5" style="box-shadow: 2px 2px 0 var(--shonen-red);"><i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $equipo->region->name ?? 'GLOBAL' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row g-4">
            {{-- DESCRIPCIÓN --}}
            <div class="col-lg-8">
                <div class="intel-panel h-100">
                    <h4 class="text-white font-Oswald fs-2 mb-3 border-bottom border-dark pb-3" style="text-shadow: 2px 2px 0 #000;"><i class="fas fa-file-alt me-2" style="color: var(--sbbl-gold);"></i> ARCHIVO DEL EQUIPO</h4>
                    <p class="text-white fw-bold" style="line-height: 1.6; font-size: 1.1rem;">{{ $equipo->description ?: 'Sin descripción disponible en la base de datos.' }}</p>
                </div>
            </div>

            {{-- NIVEL --}}
            <div class="col-lg-4">
                <div class="level-box">
                    <div class="text-white font-Oswald fs-4 mb-2" style="letter-spacing: 1px;">NIVEL OPERATIVO</div>
                    <div class="level-number">{{ floor($totalPoints / 10) }}</div>

                    @php
                        $remainder = $totalPoints % 10;
                        $fillPercentage = ($remainder / 10) * 100;
                    @endphp

                    <div class="d-flex justify-content-between text-white fw-bold small mt-3 px-1">
                        <span>PROGRESO</span>
                        <span>{{ $remainder }} / 10 XP</span>
                    </div>
                    <div class="xp-bar">
                        <div class="xp-fill" style="width: {{ $fillPercentage }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. ROSTER DE AGENTES --}}
        <h2 class="text-white font-Oswald mt-5 mb-4" style="font-size: 3rem; text-shadow: 2px 2px 0 #000; letter-spacing: 2px;">
            <i class="fas fa-users me-2" style="color: var(--shonen-cyan);"></i> ROSTER DE OPERATIVOS
        </h2>

        <div class="row g-4">
            @foreach ($miembros as $miembroId)
                @php
                    $miembro = App\Models\User::find($miembroId->id);
                    $profile = $miembro->profile;
                    $esCapitan = $miembroId->pivot->is_captain;
                    $headerStyle = $profile->fondo_url ? "background-image: url('{$profile->fondo_url}');" : "";
                @endphp

                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="agent-card">
                        {{-- Badge de Capitán --}}
                        @if ($esCapitan)
                            <div class="captain-badge"><span><i class="fas fa-crown"></i> LÍDER</span></div>
                        @endif

                        {{-- Cabecera --}}
                        <div class="agent-header" style="{{ $headerStyle }}"></div>

                        {{-- Avatar superpuesto (Redondo) --}}
                        <div class="agent-avatar-container">
                            <img src="{{ $profile->avatar_url }}" class="agent-avatar-img" loading="lazy">
                            @if($profile->marco_url)
                                <img src="{{ $profile->marco_url }}" class="agent-avatar-frame" loading="lazy">
                            @endif
                        </div>

                        {{-- Cuerpo --}}
                        <div class="agent-body">
                            <div class="agent-name">{{ $miembro->name }}</div>
                            <div class="agent-role"><i class="fas fa-map-pin me-1"></i> {{ $profile->region->name ?? 'SIN ASIGNAR' }}</div>

                            {{-- Botones de Acción --}}
                            <div class="agent-actions">
                                @if ($equipo->captain_id === Auth::user()->id)
                                    @if (!$esCapitan)
                                        <form action="{{ route('equipos.changeCaptain', [$equipo, $miembroId]) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn-action btn-promote" onclick="return confirm('¿TRANSFERIR EL MANDO DE LA FACCIÓN A ESTE OPERATIVO?')">
                                                <span><i class="fas fa-angle-double-up me-1"></i> ASCENDER</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('equipos.removeMember', [$equipo, $miembroId]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-kick" onclick="return confirm('¿EXPULSAR A ESTE OPERATIVO DEL EQUIPO?')">
                                                <span><i class="fas fa-ban me-1"></i> EXPULSAR</span>
                                            </button>
                                        </form>
                                    @endif
                                @elseif ($miembro->id === Auth::user()->id)
                                    <form action="{{ route('equipos.removeMember', [$equipo, Auth::user()]) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-action btn-leave" onclick="return confirm('¿SEGURO QUE DESEAS DESERTAR DE ESTA FACCIÓN?')">
                                            <span><i class="fas fa-sign-out-alt me-1"></i> ABANDONAR</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 5. BARRA DE COMANDOS INFERIOR --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 p-4" style="background: var(--sbbl-blue-2); border: 4px solid #000; box-shadow: 8px 8px 0 #000;">
            <div class="d-flex flex-wrap gap-3">
                @if ($equipo->captain_id === Auth::user()->id)
                    <a href="{{ route('equipos.edit', $equipo) }}" class="btn-shonen btn-shonen-warning">
                        <span><i class="fas fa-cog me-2"></i> CONFIGURAR EQUIPO</span>
                    </a>
                    <button type="button" class="btn-shonen btn-shonen-info" data-bs-toggle="modal" data-bs-target="#sendInvitationModal">
                        <span><i class="fas fa-user-plus me-2"></i> RECLUTAR AGENTE</span>
                    </button>
                @endif
            </div>

            <a href="{{ route('equipos.index') }}" class="btn-shonen mt-3 mt-md-0" style="background: #000; color: #fff;">
                <span><i class="fas fa-arrow-left me-2"></i> VOLVER AL REGISTRO</span>
            </a>
        </div>

    </div>
</div>


@endsection

@section('scripts')
{{-- MODAL INVITACIÓN --}}
<div class="modal fade" id="sendInvitationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--sbbl-blue-2); border: 4px solid #000; border-radius: 0; box-shadow: 8px 8px 0 #000;">
            <div class="modal-header" style="background: #000; border-bottom: 4px solid var(--shonen-cyan); border-radius: 0;">
                <h5 class="modal-title font-Oswald fs-3 text-white"><i class="fas fa-satellite-dish me-2" style="color: var(--shonen-cyan);"></i> TRANSMISIÓN SEGURA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('equipos.sendInvitation', $equipo) }}" id="invitationForm">
                @csrf
                <div class="modal-body p-4">
                    <label class="form-label text-white font-Oswald fs-4" style="letter-spacing: 1px;">SELECCIONAR OPERATIVO:</label>
                    <select name="user_id" id="user_id" class="form-select bg-black text-white border-dark fw-bold" required>
                        <option value="" disabled selected>-- BÚSQUEDA EN BASE DE DATOS --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer border-top border-dark" style="background: #000;">
                    <button type="button" class="btn fw-bold text-white bg-dark border border-secondary" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="submit" class="btn fw-bold" style="background: var(--shonen-cyan); color: #000; border: 2px solid #000;">ENVIAR SOLICITUD</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('#user_id').select2({
            dropdownParent: $('#sendInvitationModal'),
            width: '100%',
            theme: 'bootstrap-5'
        });
    }
});
</script>
@endsection
