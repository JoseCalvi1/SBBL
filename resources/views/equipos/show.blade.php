@extends('layouts.app')

@section('styles')
<style>
    .equipo-banner {
        display: flex;
        align-items: center;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        padding: 80px;
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
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .equipo-logo {
        object-fit: contain;
        margin-right: 20px;
    }

    .equipo-nombre {
        font-size: 3em;
        font-weight: bold;
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
</style>
@endsection

@section('content')
<div class="container-fluid mb-3">
    <div class="row justify-content-center">
        <div class="col-12 p-0 mb-2">
            <div class="equipo-banner" style="background-image: url(data:image/png;base64,{{ $equipo->image }});">
                <div class="equipo-info">
                    <img src="data:image/png;base64,{{ $equipo->logo }}" alt="Logo del Equipo" class="equipo-logo" width="150" >
                    <p class="equipo-nombre">{{ $equipo->name }}</p>
                </div>
            </div>
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
                <div class="col-md-4 mb-3">
                    @php
                        $miembro = App\Models\User::find($miembroId->id);
                    @endphp
                    <div class="tarjeta" style="background-image: url('/storage/{{ ($miembro->profile->fondo) ? $miembro->profile->fondo : "upload-profiles/Fondos/SBBLFondo.png" }}'); background-size: cover; background-repeat: no-repeat; background-position: center; color: white; position: relative; @if ($miembroId->pivot->is_captain) border: 2px solid yellow; @endif">
                        <div style="display: flex; align-items: center;">
                            <div style="position: relative;">
                                @if ($miembro->profile->imagen)
                                    <img src="/storage/{{ $miembro->profile->imagen }}" class="rounded-circle" width="100" style="top: 0; left: 0;">
                                @else
                                    <img src="/storage/upload-profiles/DranDaggerBase.png" class="rounded-circle" width="100" style="top: 0; left: 0;">
                                @endif
                                @if ($miembro->profile->marco)
                                    <img src="/storage/{{ $miembro->profile->marco }}" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 0;">
                                @else
                                    <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="100" style="position: absolute; top: 0; left: 0;">
                                @endif
                            </div>
                            <div class="info">
                                <div class="nombre">{{ $miembro->name }}</div>
                                <div class="region">{{ ($miembro->region) ? $miembro->region->name : 'No definida'}}</div>
                            </div>
                        </div>
                        @if ($equipo->captain_id === Auth::user()->id)
                            @if (!$miembroId->pivot->is_captain)
                                <!-- Formulario para hacer a este miembro el nuevo capitán del equipo -->
                                <form action="{{ route('equipos.changeCaptain', [$equipo, $miembroId]) }}" method="POST" style="position: absolute; bottom: 40px; right: 10px;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Estás seguro de querer hacer a este miembro el nuevo capitán del equipo?')">Hacer Capitán</button>
                                </form>
                            @endif

                            <!-- Formulario para eliminar miembro -->
                            <form action="{{ route('equipos.removeMember', [$equipo, $miembroId]) }}" method="POST" style="position: absolute; bottom: 10px; right: 10px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de querer eliminar a este miembro del equipo?')">Eliminar</button>
                            </form>

                        @elseif ($miembro->id === Auth::user()->id)
                            <!-- Formulario para abandonar el equipo -->
                            <form action="{{ route('equipos.removeMember', [$equipo, Auth::user()]) }}" method="POST" style="position: absolute; bottom: 10px; right: 10px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de querer abandonar el equipo?')">Abandonar Equipo</button>
                            </form>
                        @endif

                        @if ($miembroId->pivot->is_captain)
                            <div style="position: absolute; top: 0px; right: 0px; background-color: yellow; color: black; padding: 5px;">Capitán</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @if ($equipo->captain_id === Auth::user()->id)
                <button type="button" class="btn btn-outline-success mt-3" data-toggle="modal" data-target="#sendInvitationModal">
                    Enviar Invitación
                </button>
            @endif
            <a href="{{ route('equipos.index') }}" class="btn btn-outline-info mt-3">Volver a la lista de equipos</a>
        </div>
    </div>
</div>

<!-- Modal para enviar invitación -->
<div class="modal fade" id="sendInvitationModal" tabindex="-1" role="dialog" aria-labelledby="sendInvitationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendInvitationModalLabel">Enviar Invitación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('equipos.sendInvitation', $equipo) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="user_id">Seleccionar Usuario:</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">Seleccionar Usuario</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Invitación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
