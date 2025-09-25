@extends('layouts.app')

@section('title', 'Salón de la Fama')

@section('styles')
<style>
    /* Títulos */
    .hall-title {
        font-size: 2.5rem;
        font-weight: bold;
        text-align: center;
        color: #FFD700;
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.75rem;
        font-weight: 600;
        text-align: center;
        color: #ffffff;
        margin-bottom: 1.5rem;
    }

    /* Campeones de Burst */
    .burst-champions {
        display: flex;
        justify-content: center;
        gap: 2rem;
        flex-wrap: wrap;
        margin-bottom: 3rem;
    }

    .champion-card {
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        min-width: 180px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .champion-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(255, 215, 0, 0.6);
    }

    .champion-card img {
        border-radius: 50%;
        width: 100px;
        height: 100px;
        object-fit: cover;
        margin-bottom: 0.5rem;
    }

    /* Ranking general */
    .ranking-list .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none;
        background: rgba(255,255,255,0.05);
        margin-bottom: 0.5rem;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        color: white;
    }

    .ranking-list img {
        border-radius: 50%;
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 1rem;
    }

    .badge-category {
        background-color: rgba(255, 215, 0, 0.8);
        color: #000;
        padding: 0.3rem 0.6rem;
        border-radius: 0.5rem;
        font-weight: bold;
        min-width: 40px;
        text-align: center;
    }

    /* Bladers del Mes */
    .month-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 3rem;
    }

    .month-card {
        background: rgba(255,255,255,0.05);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .month-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(173, 216, 230, 0.6);
    }

    .month-card img {
        border-radius: 50%;
        width: 80px;
        height: 80px;
        object-fit: cover;
        margin-bottom: 0.5rem;
    }

    .month-card .month-label {
        font-size: 0.9rem;
        color: #ccc;
    }
</style>
@endsection

@section('content')
<div class="container">

    <h1 class="hall-title">🏆 Salón de la Fama Beyblade 🏆</h1>

    {{-- Campeones de Burst --}}
    <h2 class="section-title">🔥 Campeones de Temporadas Burst</h2>
    <div class="burst-champions">
        @foreach ($burstusers as $key => $blader)
        <div class="champion-card text-white">
            <img src="/storage/{{ $blader->profile->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}" alt="{{ $blader->name }}">
            <h5 class="mb-0">{{ $blader->name }}</h5>
            <small>Ganador Temporada {{ $key+1 }}</small>
        </div>
        @endforeach
    </div>

    {{-- Campeones de X --}}
    <h2 class="section-title">🔥 Campeones de Temporadas X</h2>
    <div class="burst-champions">
        @foreach ($xusers as $key => $blader)
        <div class="champion-card text-white">
            <img src="/storage/{{ $blader->profile->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}" alt="{{ $blader->name }}">
            <h5 class="mb-0">{{ $blader->name }}</h5>
            <small>Ganador Temporada {{ $key+1 }}</small>
        </div>
        @endforeach
    </div>

        <h2 class="text-center text-white mb-4">🏆 Nacional SBBL 2025</h2>
        <div class="m-4">
            @php
                // Definir el orden explícito del podio (primer, segundo, tercero)
                $podio = [
                    1 => $nacionalusers2025->where('id', 142)->first(), // Primer puesto
                    2 => $nacionalusers2025->where('id', 766)->first(),   // Segundo puesto
                    3 => $nacionalusers2025->where('id', 579)->first(),  // Tercer puesto
                ];
            @endphp

            <div class="row justify-content-center align-items-end">
                {{-- Segundo puesto --}}
                <div class="col-4 col-md-3 text-center">
                    <div class="border rounded p-3 bg-secondary text-white">
                        <h3 class="mb-2">🥈</h3>
                        <img src="{{ $podio[2]->profile->imagen ? asset('storage/'.$podio[2]->profile->imagen) : asset('storage/upload-profiles/Base/DranDagger.webp') }}"
                            class="rounded-circle img-fluid mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <h5>{{ $podio[2]->name }}</h5>
                    </div>
                </div>

                {{-- Primer puesto --}}
                <div class="col-4 col-md-3 text-center">
                    <div class="border rounded p-3 bg-warning text-dark">
                        <h3 class="mb-2">🥇</h3>
                        <img src="{{ $podio[1]->profile->imagen ? asset('storage/'.$podio[1]->profile->imagen) : asset('storage/upload-profiles/Base/DranDagger.webp') }}"
                            class="rounded-circle img-fluid mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                        <h5>{{ $podio[1]->name }}</h5>
                    </div>
                </div>

                {{-- Tercer puesto --}}
                <div class="col-4 col-md-3 text-center">
                    <div class="border rounded p-3 bg-dark text-white">
                        <h3 class="mb-2">🥉</h3>
                        <img src="{{ $podio[3]->profile->imagen ? asset('storage/'.$podio[3]->profile->imagen) : asset('storage/upload-profiles/Base/DranDagger.webp') }}"
                            class="rounded-circle img-fluid mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        <h5>{{ $podio[3]->name }}</h5>
                    </div>
                </div>
            </div>
        </div>


    {{-- Ranking general --}}
    <h2 class="section-title">📊 Estadísticas Generales</h2>
    <ul class="list-group ranking-list mb-4">
        @foreach ([
            ['user'=>$topAttender, 'label'=>'👥 Más Asistencias', 'value'=>$topAttender->total_assists ?? 0],
            ['user'=>$topWinner, 'label'=>'🥇 Más Victorias', 'value'=>$topWinner->total_wins ?? 0],
            ['user'=>$topDueler, 'label'=>'⚔️ Duelos Jugados', 'value'=>$topDueler->total_duels ?? 0],
            ['user'=>$topDuelWinner, 'label'=>'💥 Duelos Ganados', 'value'=>$topDuelWinner->total_wins ?? 0],
            ['user'=>$topRegister, 'label'=>'📝 Registros', 'value'=>$topRegister->total_registers ?? 0],
            ['user'=>$topPoints, 'label'=>'🏆 Puntos Totales', 'value'=>$topPoints->total_points ?? 0],
        ] as $entry)
            @if($entry['user'])
            <li class="list-group-item">
                <div class="d-flex align-items-center">
                    <img src="/storage/{{ $entry['user']->profile->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}" alt="{{ $entry['user']->name }}">
                    <div>
                        <strong>{{ $entry['user']->name }}</strong><br>
                        <small>{{ $entry['label'] }}</small>
                    </div>
                </div>
                <span class="badge-category">{{ $entry['value'] }}</span>
            </li>
            @endif
        @endforeach
    </ul>

    {{-- Bladers del Mes --}}
    <h2 class="section-title">🏅 Bladers del Mes</h2>
    <div class="month-grid text-white">
        @if ($mejoresPorMes)
            @foreach ($mejoresPorMes as $mes => $blader)
            <div class="month-card">
                <img src="/storage/{{ $blader->profile->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}" alt="{{ $blader->name }}">
                <h6 class="mb-0">{{ $blader->name }}</h6>
                <div class="month-label">{{ \Carbon\Carbon::parse($mes)->translatedFormat('F Y') }}</div>
            </div>
            @endforeach
        @endif
    </div>

</div>
@endsection
