@extends('layouts.app')

@section('title', 'Página de equipo')

@section('styles')
<style>
    .equipo-banner {
        display: flex;
        align-items: center;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        padding: 20px;
        position: relative;
        color: white;
        overflow: hidden;
    }

    .equipo-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1;
    }

    .equipo-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .equipo-logo {
        object-fit: contain;
        margin-bottom: 10px;
    }

    .equipo-nombre {
        font-size: 2em;
        font-weight: bold;
    }

    @media (min-width: 768px) {
        .equipo-info {
            flex-direction: row;
            text-align: left;
        }

        .equipo-logo {
            margin-right: 20px;
            margin-bottom: 0;
        }

        .equipo-nombre {
            font-size: 3em;
        }
    }

    .nivel-tarjeta {
        background-color: #2c3e50;
        color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        margin: 20px 0;
    }

    .nivel-tarjeta h3 {
        font-size: 1.5em;
        margin-bottom: 10px;
    }

    .nivel-tarjeta p {
        font-size: 2.5em;
        margin: 0;
    }

    .progress {
        background-color: #e0e0e0;
        border-radius: 20px;
        height: 20px;
        margin-top: 10px;
        overflow: hidden;
    }

    .progress-bar {
        background-color: #28a745;
        height: 100%;
        transition: width 0.6s ease;
    }

    .tarjeta {
        position: relative;
        padding: 20px;
        border-radius: 8px;
        overflow: hidden;
    }

    .tarjeta .info {
        margin-left: 15px;
    }

    .tarjeta .nombre {
        font-weight: bold;
    }

    .tarjeta .region {
        color: #ddd;
    }

    .tarjeta img {
        width: 100px;
        height: 100px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid mb-3">
    <div class="row justify-content-center">
        <div class="col-12 p-0 mb-2">
            @if($equipo->image)
                <div class="equipo-banner" style="background-image: url(data:image/png;base64,{{ $equipo->image }});">
                    <div class="equipo-info">
                        <img src="data:image/png;base64,{{ $equipo->logo }}" alt="Logo del Equipo" class="equipo-logo" width="150" >
                        <p class="equipo-nombre">{{ $equipo->name }}</p>
                    </div>
                </div>
            @else
                <div class="equipo-banner" style="background-image: url('/../images/webTile2.png');">
                    <div class="equipo-info">
                        <img src="../images/logo_new.png" alt="Logo del Equipo" class="equipo-logo" width="150" >
                        <p class="equipo-nombre">{{ $equipo->name }}</p>
                    </div>
                </div>
            @endif

        </div>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @elseif(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>
    <div class="row justify-content-center">
        <div class="col-md-9 p-3">
            <div class="form-group">
                <label for="descripcion" class="text-white">Descripción:</label>
                <p class="text-white">{{ $equipo->description }}</p>
            </div>
        </div>
        <div class="col-md-3 p-3">
            <div class="nivel-tarjeta">
                <h3>Nivel de equipo:</h3>
                <p>{{ floor($totalPoints / 10) }}</p>
                @php
                    $remainder = $totalPoints % 10;
                    $fillPercentage = ($remainder / 10) * 100;
                @endphp
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {{ $fillPercentage }}%;" aria-valuenow="{{ $fillPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h3 class="text-white">Miembros del Equipo</h3>
            <div class="row">
                @foreach ($miembros as $miembroId)
                <div class="col-md-4 col-sm-6 mb-3">
                    @php
                        // Obtenemos el usuario (Si $miembroId ya es el objeto User del loop,
                        // podrías ahorrarte el User::find y usar $miembroId directamente).
                        $miembro = App\Models\User::find($miembroId->id);
                        $profile = $miembro->profile;

                        // Lógica visual calculada aquí para limpiar el HTML
                        $bgStyle = $profile->fondo ? 'background-size: cover; background-repeat: no-repeat;' : 'background-repeat: repeat;';

                        // Padding para GIFs
                        $gifPadding = strpos($profile->avatar_url, '.gif') !== false ? 'padding: 10px;' : '';

                        // Verificamos si es capitán (usando la variable pivot original)
                        $esCapitan = $miembroId->pivot->is_captain;
                    @endphp

                    <div class="tarjeta position-relative"
                        style="background-image: url('{{ $profile->fondo_url }}');
                                {{ $bgStyle }}
                                background-position: center;
                                color: white;
                                height: 100%; /* Asegura altura consistente */
                                padding: 15px;
                                border-radius: 10px;
                                box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                                @if ($esCapitan) border: 2px solid yellow; @endif">

                        <div style="display: flex; align-items: center;">
                            <div style="position: relative; width: 100px; height: 100px; flex-shrink: 0;">

                                {{-- AVATAR (Usando el Accessor) --}}
                                <img src="{{ $profile->avatar_url }}"
                                    class="rounded-circle"
                                    style="width: 100%; height: 100%; object-fit: cover; {{ $gifPadding }}"
                                    loading="lazy"
                                    alt="Avatar">

                                {{-- MARCO (Usando el Accessor) --}}
                                <img src="{{ $profile->marco_url }}"
                                    class="rounded-circle"
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;"
                                    loading="lazy"
                                    alt="Marco">
                            </div>

                            <div class="info ms-3">
                                <div class="nombre fw-bold" style="text-shadow: 1px 1px 2px black;">{{ $miembro->name }}</div>
                                <div class="region small" style="text-shadow: 1px 1px 2px black;">
                                    {{ $profile->region ? $profile->region->name : 'No definida'}}
                                </div>
                            </div>
                        </div>

                        {{-- LÓGICA DE BOTONES (Solo visible si tienes permisos) --}}
                        <div class="mt-3 text-end">
                            @if ($equipo && ($equipo->captain_id === Auth::user()->id))

                                @if (!$esCapitan)
                                    <form action="{{ route('equipos.changeCaptain', [$equipo, $miembroId]) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning btn-sm py-0" onclick="return confirm('¿Nuevo capitán?')">
                                            <i class="fas fa-crown"></i> Ascender
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('equipos.removeMember', [$equipo, $miembroId]) }}" method="POST" class="d-inline-block ms-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm py-0" onclick="return confirm('¿Eliminar miembro?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>

                            @elseif ($miembro->id === Auth::user()->id)
                                <form action="{{ route('equipos.removeMember', [$equipo, Auth::user()]) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm py-0" onclick="return confirm('¿Salir del equipo?')">
                                        Abandonar
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- BADGE DE CAPITÁN --}}
                        @if ($esCapitan)
                            <div style="position: absolute; top: 0; right: 0; background-color: yellow; color: black; padding: 2px 10px; font-weight: bold; border-radius: 0 10px 0 10px; font-size: 0.8rem;">
                                <i class="fas fa-crown me-1"></i> Capitán
                            </div>
                        @endif

                    </div>
                </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-between mt-3">
                <div class="d-flex gap-2">
                    @if ($equipo->captain_id === Auth::user()->id)
                        <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-outline-primary">Editar</a>
                        <button type="button" class="btn btn-outline-success"
                                data-bs-toggle="modal" data-bs-target="#sendInvitationModal">
                            Enviar Invitación
                        </button>
                    @endif
                </div>

                <a href="{{ route('equipos.index') }}" class="btn btn-outline-info">
                    Volver a la lista de equipos →
                </a>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<!-- Modal para enviar invitación -->
<div class="modal fade" id="sendInvitationModal" tabindex="-1" aria-labelledby="sendInvitationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="sendInvitationModalLabel">Enviar Invitación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('equipos.sendInvitation', $equipo) }}" id="invitationForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="user_id">Seleccionar Usuario:</label>
                        <select name="user_id" id="user_id" class="form-control select2" required>
                            <option value="" disabled selected>Seleccionar Usuario</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Invitación</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa Select2 con el modal como parent
    const select2 = $('#user_id').select2({
        dropdownParent: $('#sendInvitationModal'),
        width: '100%'
    });

    // Inicializa el modal de Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('sendInvitationModal'));

    // Manejar el envío del formulario
    $('#invitationForm').on('submit', function(e) {
        if (!$('#user_id').val()) {
            e.preventDefault();
            alert('Por favor selecciona un usuario');
        }
    });

    // Limpiar el select al cerrar el modal
    $('#sendInvitationModal').on('hidden.bs.modal', function () {
        $('#user_id').val(null).trigger('change');
    });
});
</script>
@endsection
