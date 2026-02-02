@extends('layouts.app')

@section('title', 'Salón de la Fama - SBBL')

@section('styles')
<style>
    /* --- FONDO Y ESTRUCTURA --- */
    :root {
        --gold-glow: 0 0 15px rgba(255, 215, 0, 0.4);
        --blue-glow: 0 0 15px rgba(13, 202, 240, 0.3);
        --card-bg: rgba(30, 30, 47, 0.8);
    }

    .hall-wrapper {
        background-image: url('../images/webTile2.png');
        background-size: 300px;
        background-repeat: repeat;
        min-height: 100vh;
        padding-bottom: 3rem;
        position: relative;
    }

    .hall-wrapper::before {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(10, 10, 15, 0.92); z-index: 0;
    }

    .content-layer { position: relative; z-index: 2; }

    /* --- TÍTULOS --- */
    .hall-title {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 2px;
        background: linear-gradient(to right, #FFD700, #FDB931);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0px 4px 10px rgba(255, 215, 0, 0.3);
        margin: 2rem 0;
        text-align: center;
    }

    .section-separator {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin: 3rem 0;
        position: relative;
    }
    .section-badge {
        position: absolute; top: -15px; left: 50%; transform: translateX(-50%);
        background: #121212; padding: 0 15px;
        color: #adb5bd; font-family: monospace; text-transform: uppercase;
    }

    /* --- TARJETAS DE CAMPEONES (BURST / X) --- */
    .champ-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .champ-card {
        background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
        border: 1px solid rgba(255, 215, 0, 0.3);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: 0.3s;
        position: relative;
        overflow: hidden;
    }

    .champ-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--gold-glow);
        border-color: #FFD700;
    }

    .champ-img-container {
        width: 100px; height: 100px; margin: 0 auto 15px;
        position: relative;
    }

    .champ-season-badge {
        position: absolute; top: 0; right: 0;
        background: #FFD700; color: #000;
        font-weight: bold; font-size: 0.7rem;
        padding: 2px 8px; border-radius: 0 0 0 8px;
    }

    /* --- PODIO NACIONAL --- */
    .podium-container {
        display: flex; align-items: flex-end; justify-content: center;
        gap: 15px; margin: 3rem 0;
    }
    .podium-step {
        text-align: center; color: white;
        background: linear-gradient(to top, rgba(255,255,255,0.1), transparent);
        border-radius: 8px 8px 0 0;
        padding: 10px;
        position: relative;
    }
    .podium-1 { order: 2; height: 260px; border-top: 4px solid #FFD700; width: 140px; }
    .podium-2 { order: 1; height: 210px; border-top: 4px solid #C0C0C0; width: 120px; }
    .podium-3 { order: 3; height: 180px; border-top: 4px solid #CD7F32; width: 120px; }

    .podium-avatar {
        width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
        margin-bottom: 10px; border: 3px solid rgba(255,255,255,0.2);
    }
    .podium-1 .podium-avatar { width: 100px; height: 100px; border-color: #FFD700; box-shadow: 0 0 20px rgba(255,215,0,0.5); }
    .podium-medal { font-size: 2rem; margin-bottom: 5px; display: block; }

    /* --- ESTADÍSTICAS GENERALES --- */
    .stat-row {
        background: rgba(0,0,0,0.4);
        border-left: 3px solid transparent;
        padding: 15px;
        margin-bottom: 10px;
        display: flex; align-items: center; justify-content: space-between;
        transition: 0.2s;
        border-radius: 4px;
    }
    .stat-row:hover { background: rgba(255,255,255,0.05); border-left-color: #0dcaf0; }

    .stat-icon {
        width: 40px; height: 40px; background: rgba(255,255,255,0.1);
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; margin-right: 15px; color: #0dcaf0;
    }

    /* --- BLADERS DEL MES --- */
    .month-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;
    }
    .month-tile {
        background: #1e1e2f; border: 1px solid #333;
        border-radius: 8px; padding: 15px; text-align: center;
        transition: 0.2s;
    }
    .month-tile:hover { border-color: #adb5bd; transform: scale(1.02); }
    .month-date { font-size: 0.7rem; text-transform: uppercase; color: #888; margin-top: 5px; letter-spacing: 1px; }

</style>
@endsection

@section('content')
<div class="hall-wrapper">
    <div class="container content-layer pt-4">

        {{-- TÍTULO PRINCIPAL --}}
        <h1 class="hall-title">
            <i class="fas fa-university me-2"></i>Salón de la Fama
        </h1>
        <p class="text-center text-white text-uppercase" style="letter-spacing: 3px;">Leyendas de la SBBL</p>

        {{-- 1. CAMPEONES HISTÓRICOS (BURST & X) --}}
        <div class="row mt-5">
            {{-- Columna Burst --}}
            <div class="col-lg-6 mb-4">
                <div class="d-flex align-items-center mb-3 justify-content-center">
                    <span class="badge bg-warning text-dark me-2">ERA BURST</span>
                    <h3 class="h5 text-white text-uppercase mb-0">Campeones de Liga</h3>
                </div>

                <div class="champ-grid">
                    @foreach ($burstusers as $key => $blader)
                    <div class="champ-card">
                        <div class="champ-season-badge">S{{ $key+1 }}</div>
                        <div class="champ-img-container">
                            {{-- Lógica de imagen --}}
                            @php
                                $imgSrc = $blader->profile->imagen
                                    ? (strpos($blader->profile->imagen, 'upload-profiles/') === 0 ? asset('storage/'.$blader->profile->imagen) : asset('storage/upload-profiles/'.$blader->profile->imagen))
                                    : asset('storage/upload-profiles/Base/DranDagger.webp');
                            @endphp
                            <img src="{{ $imgSrc }}" class="w-100 h-100 rounded-circle object-fit-cover border border-secondary">
                        </div>
                        <h5 class="text-white fw-bold mb-0 text-truncate">{{ $blader->name }}</h5>
                        <small class="text-warning">Temporada {{ $key+1 }}</small>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Columna X --}}
            <div class="col-lg-6 mb-4">
                <div class="d-flex align-items-center mb-3 justify-content-center">
                    <span class="badge bg-info text-dark me-2">ERA X</span>
                    <h3 class="h5 text-white text-uppercase mb-0">Campeones de Liga</h3>
                </div>

                <div class="champ-grid">
                    @foreach ($xusers as $key => $blader)
                    <div class="champ-card" style="border-color: rgba(13, 202, 240, 0.3);">
                        <div class="champ-season-badge bg-info text-white">S{{ $key+1 }}</div>
                        <div class="champ-img-container">
                            @php
                                $imgSrc = $blader->profile->imagen
                                    ? (strpos($blader->profile->imagen, 'upload-profiles/') === 0 ? asset('storage/'.$blader->profile->imagen) : asset('storage/upload-profiles/'.$blader->profile->imagen))
                                    : asset('storage/upload-profiles/Base/DranDagger.webp');
                            @endphp
                            <img src="{{ $imgSrc }}" class="w-100 h-100 rounded-circle object-fit-cover border border-secondary">
                        </div>
                        <h5 class="text-white fw-bold mb-0 text-truncate">{{ $blader->name }}</h5>
                        <small class="text-info">Temporada {{ $key+1 }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SEPARADOR --}}
        <div class="section-separator">
            <span class="section-badge">Torneo Nacional 2025</span>
        </div>

        {{-- 2. PODIO NACIONAL 2025 --}}
        <div class="text-center mb-5">
            <h2 class="text-white text-uppercase fw-bold">Campeonato de España 2025</h2>
            <p class="text-white">Los mejores bladers del país</p>

            @php
                $podio = [
                    1 => $nacionalusers2025->where('id', 142)->first(),
                    2 => $nacionalusers2025->where('id', 766)->first(),
                    3 => $nacionalusers2025->where('id', 579)->first(),
                ];
            @endphp

            <div class="podium-container">
                {{-- 2º Lugar --}}
                <div class="podium-step podium-2">
                    <span class="podium-medal">🥈</span>
                    @if($podio[2])
                        <img src="{{ $podio[2]->profile->avatar_url }}" class="podium-avatar">
                        <div class="fw-bold text-truncate w-100">{{ $podio[2]->name }}</div>
                    @endif
                </div>

                {{-- 1º Lugar --}}
                <div class="podium-step podium-1">
                    <span class="podium-medal">👑</span>
                    @if($podio[1])
                        <img src="{{ $podio[1]->profile->avatar_url }}" class="podium-avatar">
                        <div class="fw-bold text-truncate w-100 fs-5">{{ $podio[1]->name }}</div>
                        <span class="badge bg-warning text-dark mt-2">CAMPEÓN</span>
                    @endif
                </div>

                {{-- 3º Lugar --}}
                <div class="podium-step podium-3">
                    <span class="podium-medal">🥉</span>
                    @if($podio[3])
                        <img src="{{ $podio[3]->profile->avatar_url }}" class="podium-avatar">
                        <div class="fw-bold text-truncate w-100">{{ $podio[3]->name }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SEPARADOR --}}
        <div class="section-separator">
            <span class="section-badge">Registros Históricos</span>
        </div>

        {{-- 3. ESTADÍSTICAS GENERALES Y BLADERS DEL MES --}}
        <div class="row">
            {{-- Columna Izquierda: Récords --}}
            <div class="col-lg-5 mb-5">
                <h4 class="text-white mb-4"><i class="fas fa-chart-bar text-info me-2"></i>Récords Históricos</h4>

                <div class="stat-list">
                    @php
                        $records = [
                            ['user'=>$topAttender, 'icon'=>'fa-user-check', 'label'=>'Más Asistencias', 'val'=>$topAttender->total_assists ?? 0],
                            ['user'=>$topWinner, 'icon'=>'fa-trophy', 'label'=>'Más Victorias', 'val'=>$topWinner->total_wins ?? 0],
                            ['user'=>$topDueler, 'icon'=>'fa-fist-raised', 'label'=>'Duelos Jugados', 'val'=>$topDueler->total_duels ?? 0],
                            ['user'=>$topDuelWinner, 'icon'=>'fa-crown', 'label'=>'Duelos Ganados', 'val'=>$topDuelWinner->total_wins ?? 0],
                            ['user'=>$topRegister, 'icon'=>'fa-clipboard-list', 'label'=>'Torneos Registrados', 'val'=>$topRegister->total_registers ?? 0],
                            ['user'=>$topPoints, 'icon'=>'fa-star', 'label'=>'Puntos Totales', 'val'=>$topPoints->total_points ?? 0],
                        ];
                    @endphp

                    @foreach ($records as $rec)
                        @if($rec['user'])
                        <div class="stat-row">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon"><i class="fas {{ $rec['icon'] }}"></i></div>
                                <div>
                                    <div class="text-white small text-uppercase" style="font-size: 0.7rem;">{{ $rec['label'] }}</div>
                                    <div class="text-white fw-bold">{{ $rec['user']->name }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="h4 mb-0 text-white font-monospace">{{ $rec['val'] }}</span>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Columna Derecha: Bladers del Mes --}}
            <div class="col-lg-7">
                <h4 class="text-white mb-4"><i class="fas fa-calendar-alt text-warning me-2"></i>Muro de Honor Mensual</h4>

                <div class="month-grid">
                    @if ($mejoresPorMes)
                        @foreach ($mejoresPorMes as $mes => $blader)
                        <div class="month-tile">
                            <img src="{{ $blader->profile->avatar_url }}" class="rounded-circle mb-2" width="50" height="50" style="object-fit: cover;">
                            <div class="text-white fw-bold text-truncate small">{{ $blader->name }}</div>
                            <div class="month-date">{{ \Carbon\Carbon::parse($mes)->translatedFormat('M Y') }}</div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-white">No hay registros mensuales disponibles.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
