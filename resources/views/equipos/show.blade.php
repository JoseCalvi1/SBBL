@extends('layouts.app')

@section('title', 'Control de equipo')

@section('styles')
<style>
    /* =========================================
       ESTILOS GENERALES
       ========================================= */
    :root {
        --team-primary: #ffc107; /* Oro */
        --team-bg: #1e1e2f;
        --card-bg: #23232e;
        --card-border: rgba(255, 255, 255, 0.1);
    }

    body { background-color: #121212 !important; color: #e0e0e0; }

    /* --- BANNER DE CUARTEL GENERAL --- */
    .hq-banner {
        position: relative;
        height: 280px;
        background-color: #1a1a1a;
        background-size: cover;
        background-position: center;
        border-bottom: 3px solid var(--team-primary);
        overflow: hidden;
    }

    .hq-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, #121212 100%);
        z-index: 1;
    }

    .hq-content {
        position: absolute; bottom: 0; left: 0; width: 100%;
        z-index: 2; padding: 20px;
        display: flex; align-items: flex-end; flex-wrap: wrap;
        gap: 20px;
    }

    .hq-logo-container {
        width: 130px; height: 130px;
    }
    .hq-logo { width: 100%; height: 100%; object-fit: contain; }

    .hq-title h1 {
        font-family: 'Segoe UI', sans-serif;
        font-weight: 900; text-transform: uppercase;
        color: white; margin: 0; text-shadow: 0 4px 10px rgba(0,0,0,0.9);
        font-size: 2.5rem; letter-spacing: 1px;
        line-height: 1;
    }

    /* --- PANELES --- */
    .intel-panel {
        background: var(--team-bg);
        border: 1px solid var(--card-border);
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .level-box {
        background: linear-gradient(145deg, #1e1e2f, #161625);
        border: 1px solid var(--team-primary);
        padding: 20px; border-radius: 8px;
        text-align: center;
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.1);
        height: 100%;
    }

    .level-number { font-size: 3rem; font-weight: 800; color: var(--team-primary); line-height: 1; }
    .xp-bar { height: 10px; background: #333; border-radius: 5px; margin-top: 15px; overflow: hidden; border: 1px solid #444; }
    .xp-fill { height: 100%; background: linear-gradient(90deg, #ffc107, #ff6f00); box-shadow: 0 0 10px #ffc107; }

    /* =========================================
       CARTAS DE AGENTE (ROSTER) - CORREGIDO
       ========================================= */
    .agent-card {
        background-color: var(--card-bg);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, border-color 0.3s ease;
    }

    .agent-card:hover {
        transform: translateY(-5px);
        border-color: var(--team-primary);
        box-shadow: 0 8px 20px rgba(0,0,0,0.6);
    }

    /* Cabecera de la carta (Imagen de fondo) */
    .agent-header {
        height: 100px;
        background-color: #2c2c35; /* Fondo gris oscuro por defecto si no hay imagen */
        background-size: cover;
        background-position: center;
        position: relative;
    }

    /* Sombra para que se lea el badge de líder */
    .agent-header::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(35,35,46,1) 0%, transparent 100%);
    }

    /* Contenedor Avatar - POSICIONADO CORRECTAMENTE */
    .agent-avatar-container {
        width: 70px;
        height: 70px;
        position: absolute;
        top: 65px; /* Se solapa entre header y body */
        left: 15px;
        z-index: 10;
    }

    .agent-avatar-img {
        width: 100%; height: 100%;
        border-radius: 50%;
        object-fit: cover;
        background-color: #000;
        border: 3px solid var(--card-bg); /* Borde del color de la carta para separar */
    }

    .agent-avatar-frame {
        position: absolute; top: -5px; left: -5px;
        width: calc(100% + 10px); height: calc(100% + 10px);
        pointer-events: none;
    }

    /* Cuerpo de la carta */
    .agent-body {
        padding: 45px 15px 15px 15px; /* Padding superior para dejar sitio al avatar */
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .agent-name {
        font-size: 1.1rem;
        font-weight: 800;
        color: white;
        text-transform: uppercase;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .agent-role {
        font-size: 0.8rem;
        color: #aaa;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .captain-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--team-primary); color: #000;
        font-weight: 900; font-size: 0.7rem; padding: 4px 8px;
        border-radius: 4px; z-index: 20;
        text-transform: uppercase;
        box-shadow: 0 2px 5px rgba(0,0,0,0.5);
    }

    /* --- BOTONES DE ACCIÓN VISIBLES --- */
    .agent-actions {
        margin-top: auto; /* Empuja los botones al final de la carta */
        display: grid;
        gap: 10px;
    }

    .btn-action {
        border: none;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 10px;
        border-radius: 6px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: 0.2s;
        cursor: pointer;
        width: 100%;
    }

    /* Botón Ascender (Amarillo Sólido) */
    .btn-promote {
        background-color: #ffc107;
        color: #000;
    }
    .btn-promote:hover { background-color: #e0a800; color: #000; box-shadow: 0 0 10px rgba(255, 193, 7, 0.4); }

    /* Botón Expulsar (Rojo Sólido) */
    .btn-kick {
        background-color: #dc3545;
        color: #fff;
    }
    .btn-kick:hover { background-color: #bb2d3b; color: #fff; box-shadow: 0 0 10px rgba(220, 53, 69, 0.4); }

    /* Botón Abandonar (Borde Rojo) */
    .btn-leave {
        background-color: transparent;
        border: 2px solid #dc3545;
        color: #dc3545;
    }
    .btn-leave:hover { background-color: #dc3545; color: white; }

    /* Ajuste móvil */
    @media (max-width: 768px) {
        .hq-content { flex-direction: column; text-align: center; align-items: center; }
        .agent-body { text-align: center; }
        .agent-avatar-container { left: 50%; transform: translateX(-50%); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0 mb-5">

    {{-- ALERTAS DEL SISTEMA --}}
    @if(session('success') || session('error'))
        <div class="container mt-3">
            @if(session('success'))
                <div class="alert alert-success bg-dark border-success text-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @else
                <div class="alert alert-danger bg-dark border-danger text-danger"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
            @endif
        </div>
    @endif

    {{-- 1. BANNER --}}
    @php
        $bgImage = $equipo->image ? "data:image/png;base64,{$equipo->image}" : asset('images/webTile2.png');
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
                        <span class="badge bg-dark border border-secondary text-white me-2"><i class="fas fa-users"></i> {{ $miembros->count() }} Miembros</span>
                        <span class="badge bg-dark border border-secondary text-white"><i class="fas fa-flag"></i> {{ $equipo->region->name ?? 'Global' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            {{-- DESCRIPCIÓN --}}
            <div class="col-lg-8">
                <div class="intel-panel">
                    <h5 class="text-white text-uppercase mb-3 border-bottom border-secondary pb-2"><i class="fas fa-file-alt me-2"></i>Archivo del Equipo</h5>
                    <p class="text-light" style="line-height: 1.6;">{{ $equipo->description ?: 'Sin descripción disponible.' }}</p>
                </div>
            </div>

            {{-- NIVEL --}}
            <div class="col-lg-4">
                <div class="level-box">
                    <div class="text-white small text-uppercase fw-bold mb-2">Nivel Operativo</div>
                    <div class="level-number">{{ floor($totalPoints / 10) }}</div>

                    @php
                        $remainder = $totalPoints % 10;
                        $fillPercentage = ($remainder / 10) * 100;
                    @endphp

                    <div class="d-flex justify-content-between text-white small mt-2">
                        <span>Progreso</span>
                        <span>{{ $remainder }} / 10 XP</span>
                    </div>
                    <div class="xp-bar">
                        <div class="xp-fill" style="width: {{ $fillPercentage }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. ROSTER DE AGENTES (CORREGIDO) --}}
        <h4 class="text-white text-uppercase mt-4 mb-3 border-start border-warning ps-3 border-4" style="border-color: var(--team-primary) !important;">
            Roster de Operativos
        </h4>

        <div class="row g-4">
            @foreach ($miembros as $miembroId)
                @php
                    $miembro = App\Models\User::find($miembroId->id);
                    $profile = $miembro->profile;
                    $esCapitan = $miembroId->pivot->is_captain;

                    // Si el usuario tiene fondo, úsalo. Si no, dejar que el CSS ponga el gris por defecto.
                    $headerStyle = $profile->fondo_url ? "background-image: url('{$profile->fondo_url}');" : "";

                    $gifPadding = strpos($profile->avatar_url, '.gif') !== false ? 'padding: 5px;' : '';
                @endphp

                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="agent-card">
                        {{-- Badge de Capitán --}}
                        @if ($esCapitan)
                            <div class="captain-badge"><i class="fas fa-crown"></i> LÍDER</div>
                        @endif

                        {{-- Cabecera --}}
                        <div class="agent-header" style="{{ $headerStyle }}"></div>

                        {{-- Avatar superpuesto --}}
                        <div class="agent-avatar-container">
                            <img src="{{ $profile->avatar_url }}" class="agent-avatar-img" style="{{ $gifPadding }}" loading="lazy">
                            <img src="{{ $profile->marco_url }}" class="agent-avatar-frame" loading="lazy">
                        </div>

                        {{-- Cuerpo --}}
                        <div class="agent-body">
                            <div class="agent-name">{{ $miembro->name }}</div>
                            <div class="agent-role">{{ $profile->region->name ?? 'Sin Asignar' }}</div>

                            {{-- Botones de Acción --}}
                            <div class="agent-actions">
                                @if ($equipo->captain_id === Auth::user()->id)
                                    @if (!$esCapitan)
                                        <form action="{{ route('equipos.changeCaptain', [$equipo, $miembroId]) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn-action btn-promote" onclick="return confirm('¿Ascender a este miembro a capitán?')">
                                                <i class="fas fa-angle-double-up"></i> Ascender
                                            </button>
                                        </form>
                                        <form action="{{ route('equipos.removeMember', [$equipo, $miembroId]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-kick" onclick="return confirm('¿Estás seguro de expulsar a este miembro?')">
                                                <i class="fas fa-ban"></i> Expulsar
                                            </button>
                                        </form>
                                    @endif
                                @elseif ($miembro->id === Auth::user()->id)
                                    <form action="{{ route('equipos.removeMember', [$equipo, Auth::user()]) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-action btn-leave" onclick="return confirm('¿Seguro que quieres abandonar el equipo?')">
                                            <i class="fas fa-sign-out-alt"></i> Abandonar
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
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-5 p-3 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
            <div class="d-flex gap-2">
                @if ($equipo->captain_id === Auth::user()->id)
                    <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-primary fw-bold text-uppercase">
                        <i class="fas fa-cog me-2"></i> Configurar
                    </a>
                    <button type="button" class="btn btn-success fw-bold text-uppercase" data-bs-toggle="modal" data-bs-target="#sendInvitationModal">
                        <i class="fas fa-user-plus me-2"></i> Reclutar
                    </button>
                @endif
            </div>

            <a href="{{ route('equipos.index') }}" class="btn btn-outline-light mt-2 mt-md-0 text-uppercase fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Volver
            </a>
        </div>

    </div>
</div>
@endsection

@section('scripts')
{{-- Modal Invitación --}}
<div class="modal fade" id="sendInvitationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-uppercase fw-bold"><i class="fas fa-satellite-dish me-2 text-success"></i> Transmisión Segura</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('equipos.sendInvitation', $equipo) }}" id="invitationForm">
                @csrf
                <div class="modal-body">
                    <label class="form-label text-white text-uppercase small">Seleccionar Operativo</label>
                    <select name="user_id" id="user_id" class="form-select bg-black text-white border-secondary" required>
                        <option value="" disabled selected>-- Buscando en base de datos... --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold">Enviar Solicitud</button>
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
