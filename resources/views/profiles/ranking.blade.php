@extends('layouts.app')

@section('title', 'Ranking Beyblade X')

@section('content')
<div class="container">
    <!-- Filtros -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <label for="limitSelect" class="text-white">Mostrar:</label>
            <select id="limitSelect" class="form-control bg-dark text-white" onchange="applyFilters()">
                <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50</option>
                <option value="200" {{ $limit == 200 ? 'selected' : '' }}>200</option>
                <option value="500" {{ $limit == 500 ? 'selected' : '' }}>500</option>
                <option value="1000" {{ $limit == 1000 ? 'selected' : '' }}>1000</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="regionSelect" class="text-white">Filtrar por región:</label>
            <select id="regionSelect" class="form-control bg-dark text-white" onchange="applyFilters()">
                <option value="">Todas las regiones</option>
                @foreach ($regions as $regionOption)
                    <option value="{{ $regionOption }}" {{ $region == $regionOption ? 'selected' : '' }}>
                        {{ $regionOption }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="rankingTabs" role="tablist">
        @php $tabs = ['points' => 'Burst S1', 'points_s2' => 'Burst S2', 'points_s3' => 'Burst S3', 'points_x1' => 'Beyblade X S1', 'points_x2' => 'Beyblade X S2']; @endphp
        @foreach ($tabs as $key => $label)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->last ? 'active' : '' }}" id="{{ $key }}-tab"
                    data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button" role="tab"
                    aria-controls="{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    {{ $label }}
                </button>
            </li>
        @endforeach
    </ul>

    <!-- Contenido de los tabs -->
    <div class="tab-content" id="rankingTabsContent">
        @foreach ($tabs as $key => $label)
            <div class="tab-pane fade {{ $loop->last ? 'show active' : '' }}" id="{{ $key }}" role="tabpanel"
                aria-labelledby="{{ $key }}-tab">
                <div class="clasificacion mb-4">
                    <div class="item encabezado">
                        <span class="posicion">#</span>
                        @if ($key === 'points_x2')
                            <span></span>
                        @endif
                        <span>Blader</span>
                        <span>Región</span>
                        <span>Puntos</span>
                    </div>
                    @foreach (${'bladers_' . $key} ?? [] as $index => $blader)
                        @php
                            $firstTrophyName = $blader->trophies->first()->name ?? '';
                            switch ($firstTrophyName) {
                                case 'SUSCRIPCIÓN NIVEL 3':
                                    $subscriptionClass = 'suscripcion-nivel-3';
                                    break;
                                case 'SUSCRIPCIÓN NIVEL 2':
                                    $subscriptionClass = 'suscripcion-nivel-2';
                                    break;
                                case 'SUSCRIPCIÓN NIVEL 1':
                                    $subscriptionClass = 'suscripcion-nivel-1';
                                    break;
                                default:
                                    $subscriptionClass = '';
                            }
                        @endphp
                        <div class="item {{ $index < 4 ? 'resaltado' : '' }}">
                            <span class="posicion">{{ $index + 1 }}</span>

                            @if ($key === 'points_x2')
                                <span class="profile-container">
                                    <div class="blader-avatar d-none d-sm-block">
                                        <img src="/storage/{{ $blader->imagen ?? 'upload-profiles/Base/DranDagger.webp' }}"
                                             class="blader-image">
                                        <img src="/storage/{{ $blader->marco ?? 'upload-profiles/Marcos/BaseBlue.png' }}"
                                             class="blader-frame">
                                    </div>
                                </span>
                            @endif

                            <span class="{{ $subscriptionClass }}">{{ $blader->user->name }}</span>
                            <span>{{ $blader->region->name ?? 'Región desconocida' }}</span>
                            <span>{{ $blader->{$key} }} puntos</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function applyFilters() {
        const limit = document.getElementById('limitSelect').value;
        const region = document.getElementById('regionSelect').value;
        const url = `?limit=${limit}&region=${region}`;
        window.location.href = url;
    }
</script>
@endsection

@section('styles')
<style>
    body {
        background-color: #121212;
        color: #e0e0e0;
    }

    .nav-tabs .nav-link {
        background-color: #333;
        color: #e0e0e0;
        border: none;
        border-radius: 0;
    }

    .nav-tabs .nav-link.active {
        background-color: #ffca28;
        color: #121212;
        font-weight: bold;
    }

    .clasificacion {
        border-radius: 10px;
        padding: 20px;
        background-color: #1e1e1e;
    }

    .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid #333;
        border-radius: 5px;
        background-color: #2a2a2a;
        color: #e0e0e0;
    }

    .encabezado {
        font-weight: bold;
        background-color: #333;
        color: #fff;
    }

    .item span.posicion {
        flex: 1;
        text-align: center;
        color: #ffca28;
    }

    .item span:nth-child(2) {
        flex: 1;
        text-align: center;
    }

    .item span:nth-child(3) {
        flex: 4;
    }

    .item span:nth-child(4) {
        flex: 3;
    }

    .item span:nth-child(5) {
        flex: 3;
        text-align: center;
    }

    .resaltado {
        background-color: #424242;
    }

    .form-control {
        background-color: #333;
        color: #e0e0e0;
        border: 1px solid #555;
    }

    .form-control:focus {
        border-color: #ffca28;
        box-shadow: none;
    }

    .suscripcion-nivel-3 {
        color: gold;
        font-weight: bold;
    }

    .suscripcion-nivel-2 {
        color: #c0e5fb;
        font-weight: bold;
    }

    .suscripcion-nivel-1 {
        color: #CD7F32;
        font-weight: bold;
    }

    .profile-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
    }

    .blader-avatar {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .blader-image, .blader-frame {
        width: 50px;
        height: 50px;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 50%;
        object-fit: cover;
    }

    .blader-frame {
        z-index: 2;
    }
</style>
@endsection
