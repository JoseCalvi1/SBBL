@extends('layouts.app')

@section('title', 'Salón de la Fama - SBBL')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: SALÓN DE LA FAMA SHONEN (Hereda de layout)
       ==================================================================== */

    /* --- TÍTULOS --- */
    .hall-title {
        font-family: 'Bangers', cursive;
        font-size: 4rem;
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-red);
        letter-spacing: 3px;
        text-transform: uppercase;
        margin: 2rem 0 0.5rem;
        text-align: center;
        line-height: 1;
    }

    .hall-subtitle {
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 5px;
        text-align: center;
        text-shadow: 2px 2px 0 #000;
        background: #000;
        display: inline-block;
        padding: 5px 20px;
        border: 2px solid var(--sbbl-gold);
        transform: skewX(-10deg);
        box-shadow: 4px 4px 0 var(--shonen-blue);
    }
    .hall-subtitle-container { text-align: center; margin-bottom: 3rem; }
    .hall-subtitle > span { display: block; transform: skewX(10deg); }

    .section-separator {
        border-bottom: 4px dashed #000;
        margin: 4rem 0 3rem;
        position: relative;
    }
    .section-badge {
        position: absolute; top: -20px; left: 50%; transform: translateX(-50%) skewX(-10deg);
        background: var(--shonen-cyan); padding: 5px 20px;
        color: #000; font-family: 'Bangers', cursive; font-size: 1.5rem; text-transform: uppercase;
        border: 3px solid #000; box-shadow: 4px 4px 0 var(--shonen-red);
    }
    .section-badge > span { display: block; transform: skewX(10deg); }

    /* --- TARJETAS DE CAMPEONES (BURST / X) --- */
    .era-badge {
        font-family: 'Bangers', cursive;
        font-size: 1.5rem;
        border: 3px solid #000;
        border-radius: 0;
        transform: skewX(-5deg);
        box-shadow: 3px 3px 0 #000;
        padding: 5px 15px;
    }
    .era-badge > span { display: block; transform: skewX(5deg); }
    .era-burst { background: var(--sbbl-gold); color: #000; }
    .era-x { background: var(--shonen-cyan); color: #000; }

    .champ-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .champ-card {
        background: var(--sbbl-blue-3);
        border: 3px solid #000;
        border-radius: 0;
        padding: 20px;
        text-align: center;
        transition: 0.2s;
        position: relative;
        overflow: hidden;
        box-shadow: 6px 6px 0 #000;
    }

    .champ-card:hover {
        transform: translate(-2px, -2px);
        border-color: var(--sbbl-gold);
        box-shadow: 8px 8px 0 var(--shonen-red);
    }

    .champ-img-container {
        width: 110px; height: 110px; margin: 0 auto 15px;
        position: relative;
    }

    .champ-season-badge {
        position: absolute; top: 0; right: 0;
        font-family: 'Bangers', cursive;
        font-size: 1.2rem;
        padding: 2px 10px; border-bottom: 3px solid #000; border-left: 3px solid #000;
        box-shadow: -3px 3px 0 rgba(0,0,0,0.5);
    }
    .champ-season-burst { background: var(--sbbl-gold); color: #000; }
    .champ-season-x { background: var(--shonen-cyan); color: #000; }

    /* --- PODIO NACIONAL --- */
    .podium-container {
        display: flex; align-items: flex-end; justify-content: center;
        gap: 15px; margin: 3rem 0;
    }
    .podium-step {
        text-align: center; color: white;
        background: rgba(0,0,0,0.6);
        border: 3px solid #000;
        border-bottom: none;
        padding: 15px 10px 10px;
        position: relative;
        box-shadow: inset 0 -20px 20px rgba(0,0,0,0.8);
    }
    .podium-1 { order: 2; height: 280px; border-top: 5px solid var(--sbbl-gold); width: 150px; background: rgba(255, 193, 7, 0.1); }
    .podium-2 { order: 1; height: 220px; border-top: 5px solid #e2e8f0; width: 130px; }
    .podium-3 { order: 3; height: 180px; border-top: 5px solid #ff9d47; width: 130px; }

    .podium-avatar {
        width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
        margin-bottom: 10px; border: 3px solid #000; background: #fff;
    }
    .podium-1 .podium-avatar { width: 110px; height: 110px; border-color: var(--sbbl-gold); box-shadow: 0 0 0 3px #000; }
    .podium-medal { font-size: 2rem; margin-bottom: 5px; display: block; text-shadow: 2px 2px 0 #000; }

    .podium-name { font-family: 'Bangers', cursive; font-size: 1.5rem; letter-spacing: 1px; text-shadow: 2px 2px 0 #000;}

    /* --- ESTADÍSTICAS GENERALES --- */
    .stat-row {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        padding: 15px;
        margin-bottom: 10px;
        display: flex; align-items: center; justify-content: space-between;
        transition: 0.2s;
        transform: skewX(-2deg);
        box-shadow: 4px 4px 0 #000;
    }
    .stat-row > * { transform: skewX(2deg); }
    .stat-row:hover { background: #000; border-color: var(--shonen-cyan); box-shadow: 5px 5px 0 var(--sbbl-blue-3); transform: translate(-2px, -2px) skewX(-2deg); }

    .stat-icon {
        width: 45px; height: 45px; background: #000;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid var(--shonen-cyan); color: var(--shonen-cyan);
        font-size: 1.2rem; margin-right: 15px; box-shadow: 2px 2px 0 #000;
    }

    /* --- BLADERS DEL MES --- */
    .month-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px;
    }
    .month-tile {
        background: var(--sbbl-blue-3); border: 3px solid #000;
        padding: 15px; text-align: center;
        transition: 0.2s; box-shadow: 4px 4px 0 #000;
    }
    .month-tile:hover { border-color: var(--sbbl-gold); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--shonen-red); }
    .month-date { font-family: 'Bangers', cursive; font-size: 1.1rem; color: var(--sbbl-gold); margin-top: 5px; letter-spacing: 1px; text-shadow: 1px 1px 0 #000; }

</style>
@endsection

@section('content')
<div class="container py-4">

    {{-- TÍTULO PRINCIPAL --}}
    <h1 class="hall-title">
        <i class="fas fa-university me-2" style="color: #fff; text-shadow: none;"></i>Salón de la Fama
    </h1>
    <div class="hall-subtitle-container">
        <div class="hall-subtitle"><span>Leyendas de la SBBL</span></div>
    </div>

    {{-- 1. CAMPEONES HISTÓRICOS (BURST & X) --}}
    <div class="row mt-5">
        {{-- Columna Burst --}}
        <div class="col-lg-6 mb-5">
            <div class="d-flex align-items-center justify-content-center mb-4">
                <div class="era-badge era-burst me-3"><span>ERA BURST</span></div>
                <h3 class="text-white font-bangers fs-2 mb-0" style="text-shadow: 2px 2px 0 #000;">Campeones de Liga</h3>
            </div>

            <div class="champ-grid">
                @foreach ($burstusers as $key => $blader)
                <div class="champ-card">
                    <div class="champ-season-badge champ-season-burst">S{{ $key+1 }}</div>
                    <div class="champ-img-container">
                        {{-- Lógica de imagen --}}
                        @php
                            $imgSrc = $blader->profile->imagen
                                ? (strpos($blader->profile->imagen, 'upload-profiles/') === 0 ? asset('storage/'.$blader->profile->imagen) : asset('storage/upload-profiles/'.$blader->profile->imagen))
                                : asset('storage/upload-profiles/Base/DranDagger.webp');
                        @endphp
                        <img src="{{ $imgSrc }}" class="w-100 h-100 rounded-circle object-fit-cover border border-2 border-dark" style="box-shadow: 0 0 0 2px var(--sbbl-gold);">
                    </div>
                    <h5 class="font-bangers fs-4 text-white mb-0 text-truncate" style="letter-spacing: 1px; text-shadow: 1px 1px 0 #000;">{{ $blader->name }}</h5>
                    <small class="fw-bold" style="color: var(--sbbl-gold);">Temporada {{ $key+1 }}</small>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Columna X --}}
        <div class="col-lg-6 mb-5">
            <div class="d-flex align-items-center justify-content-center mb-4">
                <div class="era-badge era-x me-3"><span>ERA X</span></div>
                <h3 class="text-white font-bangers fs-2 mb-0" style="text-shadow: 2px 2px 0 #000;">Campeones de Liga</h3>
            </div>

            <div class="champ-grid">
                @foreach ($xusers as $key => $blader)
                <div class="champ-card">
                    <div class="champ-season-badge champ-season-x">S{{ $key+1 }}</div>
                    <div class="champ-img-container">
                        @php
                            $imgSrc = $blader->profile->imagen
                                ? (strpos($blader->profile->imagen, 'upload-profiles/') === 0 ? asset('storage/'.$blader->profile->imagen) : asset('storage/upload-profiles/'.$blader->profile->imagen))
                                : asset('storage/upload-profiles/Base/DranDagger.webp');
                        @endphp
                        <img src="{{ $imgSrc }}" class="w-100 h-100 rounded-circle object-fit-cover border border-2 border-dark" style="box-shadow: 0 0 0 2px var(--shonen-cyan);">
                    </div>
                    <h5 class="font-bangers fs-4 text-white mb-0 text-truncate" style="letter-spacing: 1px; text-shadow: 1px 1px 0 #000;">{{ $blader->name }}</h5>
                    <small class="fw-bold" style="color: var(--shonen-cyan);">Temporada {{ $key+1 }}</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SEPARADOR --}}
    <div class="section-separator">
        <div class="section-badge"><span>Torneo Nacional 2025</span></div>
    </div>

    {{-- 2. PODIO NACIONAL 2025 --}}
    <div class="text-center mb-5">
        <h2 class="text-white font-bangers" style="font-size: 3rem; text-shadow: 2px 2px 0 #000;">Campeonato de España 2025</h2>
        <p class="text-white fw-bold bg-dark d-inline-block px-3 py-1 border border-secondary">Los mejores bladers del país</p>

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
                    <div class="podium-name text-truncate w-100" style="color: #e2e8f0;">{{ $podio[2]->name }}</div>
                @endif
            </div>

            {{-- 1º Lugar --}}
            <div class="podium-step podium-1">
                <span class="podium-medal">👑</span>
                @if($podio[1])
                    <img src="{{ $podio[1]->profile->avatar_url }}" class="podium-avatar">
                    <div class="podium-name text-truncate w-100" style="color: var(--sbbl-gold);">{{ $podio[1]->name }}</div>
                    <span class="badge bg-black text-white border border-warning mt-2" style="font-family: 'Bangers', cursive; font-size: 1rem;">CAMPEÓN</span>
                @endif
            </div>

            {{-- 3º Lugar --}}
            <div class="podium-step podium-3">
                <span class="podium-medal">🥉</span>
                @if($podio[3])
                    <img src="{{ $podio[3]->profile->avatar_url }}" class="podium-avatar">
                    <div class="podium-name text-truncate w-100" style="color: #ff9d47;">{{ $podio[3]->name }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- SEPARADOR --}}
    <div class="section-separator">
        <div class="section-badge" style="background: var(--shonen-red);"><span>Registros Históricos</span></div>
    </div>

    {{-- 3. ESTADÍSTICAS GENERALES Y BLADERS DEL MES --}}
    <div class="row pb-5">
        {{-- Columna Izquierda: Récords --}}
        <div class="col-lg-5 mb-5">
            <h4 class="text-white font-bangers fs-2 mb-4" style="text-shadow: 2px 2px 0 #000;"><i class="fas fa-chart-bar text-info me-2"></i>Récords Históricos</h4>

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
                                <div class="text-white fw-bold text-uppercase" style="font-size: 0.75rem;">{{ $rec['label'] }}</div>
                                <div class="text-white font-bangers fs-5" style="letter-spacing: 1px;">{{ $rec['user']->name }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="font-bangers fs-2 text-white" style="text-shadow: 2px 2px 0 #000;">{{ $rec['val'] }}</span>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Columna Derecha: Bladers del Mes --}}
        <div class="col-lg-7">
            <h4 class="text-white font-bangers fs-2 mb-4" style="text-shadow: 2px 2px 0 #000;"><i class="fas fa-calendar-alt text-warning me-2"></i>Muro de Honor Mensual</h4>

            <div class="month-grid">
                @if ($mejoresPorMes)
                    @foreach ($mejoresPorMes as $mes => $blader)
                    <div class="month-tile">
                        <img src="{{ $blader->profile->avatar_url }}" class="rounded-circle mb-3" width="70" height="70" style="object-fit: cover; border: 3px solid #000; background: #fff;">
                        <div class="text-white fw-bold text-truncate fs-6">{{ $blader->name }}</div>
                        <div class="month-date">{{ \Carbon\Carbon::parse($mes)->translatedFormat('M Y') }}</div>
                    </div>
                    @endforeach
                @else
                    <div class="alert alert-dark border-secondary text-center" style="grid-column: 1 / -1; background: rgba(0,0,0,0.5) !important; border: 3px solid #000 !important; border-radius: 0;">
                        <span class="font-bangers fs-4 text-white">NO HAY REGISTROS MENSUALES DISPONIBLES.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
