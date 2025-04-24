@extends('layouts.app')

@section('title', 'Salón de la Fama')

@section('styles')
<style>
    .glow-frame {
        border-radius: 15px;
        box-shadow: 0 0 15px 5px rgba(255, 215, 0, 0.7);
        animation: pulseGlow 2s infinite ease-in-out;
    }

    .ice-frame {
        border: 5px solid rgba(173, 216, 230, 0.8);
        border-radius: 15px;
        backdrop-filter: blur(2px);
        box-shadow: 0 0 10px rgba(173, 216, 230, 0.6);
    }

    .fire-frame {
        border-radius: 15px;
        box-shadow: 0 0 10px #ff6a00, 0 0 20px #ff6a00, 0 0 30px #ff0000;
        animation: fireGlow 1.5s infinite alternate;
    }
</style>

@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12 mb-5">
    <h1 class="text-4xl font-bold text-center text-white mt-3 mb-5">🏆 Salón de la Fama Beyblade 🏆</h1>

    {{-- Campeones Temporadas Burst --}}
    <h2 class="text-2xl font-semibold text-center text-white mb-8">🔥 Campeones de Temporadas Burst</h2>
    <div class="flex flex-wrap justify-center gap-6 mb-5 row p-4">
        @foreach ($burstusers->take(2) as $blader)
        <div class="col-md-6 text-center p-2">
            <div class="glow-frame position-relative d-flex flex-column align-items-center user-card" style="background-image: url('/storage/{{ $blader->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                    background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                <div class="position-relative mt-4">
                    @if ($blader->profile->marco)
                        <img src="/storage/{{ $blader->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                    @else
                        <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                    @endif
                    @if ($blader->profile->imagen)
                        <img src="/storage/{{ $blader->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($blader->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                    @else
                        <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                    @endif
                </div>
                <h3 class="user-name" style="color: white; margin-top: 10px; font-size: 1.5rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);">{{ $blader->name }}</h3>
                <!-- Subtítulo personalizado -->
                <p class="user-title" style="color: #ccc; font-size: 1rem; margin-top: 5px; font-style: italic;">GANADOR BEYBLADE BURST</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row mt-4 mb-2">

        <div class="col-md-6">
            @if ($topAttender)
                <h2 class="text-2xl font-semibold text-center text-white mb-8">👥 Mayor Asistencia</h2>
                <div class="col-md-12 text-center p-2">
                    <div class="fire-frame position-relative d-flex flex-column align-items-center user-card pb-4" style="background-image: url('/storage/{{ $topAttender->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topAttender->profile->marco)
                                <img src="/storage/{{ $topAttender->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @endif
                            @if ($topAttender->profile->imagen)
                                <img src="/storage/{{ $topAttender->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($topAttender->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                            @endif
                        </div>
                            <div class="nombre suscripcion-nivel-2 text-lg font-semibold">{{ $topAttender->name }}</div>
                            <div class="region text-xs">{{ $topAttender->profile->region->name ?? 'No definida' }}</div>
                            <div class="subtitulo text-sm">🎯 Más Asistencias</div>
                            <div class="region text-xs">Nº de asistencias: {{ $topAttender->total_assists ?? '0' }}</div>
                    </div>
                </div>
                @endif
        </div>

        <div class="col-md-6">
            @if ($topWinner)
                <h2 class="text-2xl font-semibold text-center text-white">🥇 Más Victorias</h2>
                <div class="col-md-12 text-center p-2">
                    <div class="fire-frame position-relative d-flex flex-column align-items-center user-card pb-4" style="background-image: url('/storage/{{ $topWinner->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topWinner->profile->marco)
                                <img src="/storage/{{ $topWinner->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @endif
                            @if ($topWinner->profile->imagen)
                                <img src="/storage/{{ $topWinner->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($topWinner->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                            @endif
                        </div>
                        <div class="nombre suscripcion-nivel-1 text-lg font-semibold">{{ $topWinner->name }}</div>
                        <div class="region text-xs">{{ $topWinner->profile->region->name ?? 'No definida' }}</div>
                        <div class="subtitulo text-sm">🥇 Más torneos ganados</div>
                        <div class="region text-xs">Nº de torneos ganados: {{ $topWinner->total_wins ?? '0' }}</div>
                    </div>
                </div>
            @endif
        </div>

    </div>
        <div class="row mt-4 mb-2">

        <div class="col-md-6">
            @if ($topDueler)
                <h2 class="text-2xl font-semibold text-center text-white mb-8">🥊 Más Duelos Jugados</h2>
                <div class="col-md-12 text-center p-2">
                    <div class="fire-frame position-relative d-flex flex-column align-items-center user-card pb-4"
                        style="background-image: url('/storage/{{ $topDueler->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topDueler->profile->marco)
                                <img src="/storage/{{ $topDueler->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @endif
                            @if ($topDueler->profile->imagen)
                                <img src="/storage/{{ $topDueler->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($topDueler->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                            @endif
                        </div>
                        <div class="nombre suscripcion-nivel-2 text-lg font-semibold">{{ $topDueler->name }}</div>
                        <div class="region text-xs">{{ $topDueler->profile->region->name ?? 'No definida' }}</div>
                        <div class="subtitulo text-sm">⚔️ Duelos Jugados</div>
                        <div class="region text-xs">Total: {{ $topDueler->total_duels ?? '0' }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6">
            @if ($topDuelWinner)
                <h2 class="text-2xl font-semibold text-center text-white mb-8">⚡ Más Duelos Ganados</h2>
                <div class="col-md-12 text-center p-2">
                    <div class="fire-frame position-relative d-flex flex-column align-items-center user-card pb-4"
                        style="background-image: url('/storage/{{ $topDuelWinner->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topDuelWinner->profile->marco)
                                <img src="/storage/{{ $topDuelWinner->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @endif
                            @if ($topDuelWinner->profile->imagen)
                                <img src="/storage/{{ $topDuelWinner->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($topDuelWinner->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                            @endif
                        </div>
                        <div class="nombre suscripcion-nivel-2 text-lg font-semibold">{{ $topDuelWinner->name }}</div>
                        <div class="region text-xs">{{ $topDuelWinner->profile->region->name ?? 'No definida' }}</div>
                        <div class="subtitulo text-sm">💥 Duelos Ganados</div>
                        <div class="region text-xs">Total: {{ $topDuelWinner->total_wins ?? '0' }}</div>
                    </div>
                </div>
            @endif
        </div>

        </div>
        <div class="row mt-4 mb-2">

        <div class="col-md-6">
            @if ($topRegister)
                <h2 class="text-2xl font-semibold text-center text-white mb-8">📝 Más Registros</h2>
                <div class="col-md-12 text-center p-2">
                    <div class="fire-frame position-relative d-flex flex-column align-items-center user-card pb-4"
                        style="background-image: url('/storage/{{ $topRegister->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topRegister->profile->marco)
                                <img src="/storage/{{ $topRegister->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @endif
                            @if ($topRegister->profile->imagen)
                                <img src="/storage/{{ $topRegister->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($topRegister->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                            @endif
                        </div>
                        <div class="nombre suscripcion-nivel-2 text-lg font-semibold">{{ $topRegister->name }}</div>
                        <div class="region text-xs">{{ $topRegister->profile->region->name ?? 'No definida' }}</div>
                        <div class="subtitulo text-sm">📝 Registros Hechos</div>
                        <div class="region text-xs">Total: {{ $topRegister->total_registers ?? '0' }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6">
            @if ($topPoints)
                <h2 class="text-2xl font-semibold text-center text-white mb-8">🏆 Más Puntos</h2>
                <div class="col-md-12 text-center p-2">
                    <div class="fire-frame position-relative d-flex flex-column align-items-center user-card pb-4"
                        style="background-image: url('/storage/{{ $topPoints->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topPoints->profile->marco)
                                <img src="/storage/{{ $topPoints->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: -5px; z-index: 2">
                            @endif
                            @if ($topPoints->profile->imagen)
                                <img src="/storage/{{ $topPoints->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;{{ strpos($topPoints->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 0; z-index: 1;">
                            @endif
                        </div>
                        <div class="nombre suscripcion-nivel-2 text-lg font-semibold">{{ $topPoints->name }}</div>
                        <div class="region text-xs">{{ $topPoints->profile->region->name ?? 'No definida' }}</div>
                        <div class="subtitulo text-sm">🏆 Puntos Totales</div>
                        <div class="region text-xs">Total: {{ $topPoints->total_points ?? '0' }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4"><h2 class="text-2xl font-semibold text-center text-white mb-8">🏆 Bladers del mes</h2></div>
    <div class="row">
        @if ($mejoresPorMes)
        @foreach ($mejoresPorMes as $mes => $topPoints)
            <div class="col-md-3">
                <div class="col-md-12 text-center p-2">
                    <div class="ice-frame position-relative d-flex flex-column align-items-center user-card pb-4"
                        style="background-image: url('/storage/{{ $topPoints->profile->fondo ?? 'upload-profiles/Fondos/SBBLFondo.png' }}');
                            background-size: cover;background-repeat: repeat;background-position: center;color: white;">
                        <div class="position-relative mt-4">
                            @if ($topPoints->profile->marco)
                                <img src="/storage/{{ $topPoints->profile->marco }}" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: 0; z-index: 2">
                            @else
                                <img src="/storage/upload-profiles/Marcos/BaseBlue.png" class="rounded-circle" width="110" height="110" style="position: relative; top: -5px; left: 0; z-index: 2">
                            @endif

                            @if ($topPoints->profile->imagen)
                                <img src="/storage/{{ $topPoints->profile->imagen }}" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 5px; z-index: 1;{{ strpos($topPoints->profile->imagen, '.gif') !== false ? 'padding: 10px;' : '' }}">
                            @else
                                <img src="/storage/upload-profiles/Base/DranDagger.webp" class="rounded-circle" width="100" height="100" style="position: absolute; top: 0; left: 5px; z-index: 1;">
                            @endif
                        </div>
                        <div class="nombre suscripcion-nivel-2 text-lg font-semibold">{{ $topPoints->name }}</div>
                        <div class="region text-xs">{{ \Carbon\Carbon::parse($mes)->translatedFormat('F Y') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
        @endif
    </div>
</div>
@endsection
