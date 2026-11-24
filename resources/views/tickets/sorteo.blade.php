@extends('layouts.app')

@section('title', 'Sorteo Oficial SBBL')

@section('styles')
<style>
    .raffle-stage {
        min-height: 82vh;
        background: radial-gradient(circle at center, var(--sbbl-blue-1) 0%, #000 100%);
        overflow: hidden;
    }
    .stage-panel {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 8px 8px 0 #000;
    }
    .ticket-card-flip {
        width: 230px;
        height: 140px;
        perspective: 1000px;
        cursor: pointer;
    }
    .ticket-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.7s;
        transform-style: preserve-3d;
    }
    .ticket-card-flip.flipped .ticket-inner {
        transform: rotateY(180deg);
    }
    .ticket-front,
    .ticket-back {
        position: absolute;
        inset: 0;
        backface-visibility: hidden;
        border: 3px solid #000;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        padding: 12px;
        text-align: center;
    }
    .ticket-front {
        background: var(--sbbl-gold);
        color: #000;
    }
    .ticket-back {
        background: #00ff88;
        color: #000;
        transform: rotateY(180deg);
    }
</style>
@endsection

@section('content')
<div class="raffle-stage position-relative py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h1 class="font-bangers m-0" style="font-size: 3.4rem; color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000, 4px 4px 0 var(--shonen-red);">
                🎟️ BOMBO DE SORTEO OFICIAL
            </h1>
            <a href="{{ route('admin.raffles.index') }}" class="btn btn-outline-light fw-bold">
                <i class="fas fa-cog me-1"></i> Gestionar Rifas
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-shonen alert-shonen-success mb-4 text-center"><div>{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="alert alert-shonen alert-shonen-danger mb-4 text-center"><div>{{ session('error') }}</div></div>
        @endif

        @if(!$selectedRaffle)
            <div class="stage-panel p-5 text-center text-white">
                <h3 class="font-Oswald">No hay ninguna rifa creada para sortear.</h3>
                <a href="{{ route('admin.raffles.index') }}" class="btn btn-warning text-dark fw-bold mt-3">Crear Rifa</a>
            </div>
        @else
            <form method="GET" action="{{ route('tickets.sorteo') }}" class="mb-4">
                <div class="input-group" style="max-width: 500px;">
                    <span class="input-group-text bg-black text-warning border-secondary fw-bold">Rifa Seleccionada</span>
                    <select name="raffle_id" class="form-select bg-dark text-white border-secondary fw-bold" onchange="this.form.submit()">
                        @foreach($raffles as $r)
                            <option value="{{ $r->id }}" {{ $selectedRaffle->id === $r->id ? 'selected' : '' }}>
                                {{ $r->title }} ({{ $r->is_active ? 'Activa' : ($r->drawn_at ? 'Sorteada' : 'Pausada') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="stage-panel p-4 text-center text-white h-100">
                        <h3 class="font-Oswald text-info border-bottom border-secondary pb-2 mb-3">
                            {{ $selectedRaffle->title }}
                        </h3>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="bg-black p-3 border border-secondary">
                                    <div class="small text-white-50">PARTICIPANTES</div>
                                    <div class="font-Oswald fs-2 text-info">{{ $totalParticipants }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-black p-3 border border-secondary">
                                    <div class="small text-white-50">PAPELETAS EN BOMBO</div>
                                    <div class="font-Oswald fs-2 text-warning">{{ $totalTickets }}</div>
                                </div>
                            </div>
                        </div>

                        <p class="small text-white-50 mb-3">
                            Se extraerán <strong>{{ $selectedRaffle->winners_count }}</strong> ganador(es) únicos sin repetir blader.
                        </p>

                        <form method="POST" action="{{ route('tickets.sorteo.draw') }}" id="drawRaffleForm" class="mb-3">
                            @csrf
                            <input type="hidden" name="raffle_id" value="{{ $selectedRaffle->id }}">
                            <button type="button" id="startRaffle" class="btn btn-success btn-lg w-100 fw-bold border-2 border-dark">
                                ▶️ INICIAR CUENTA ATRÁS Y SORTEAR
                            </button>
                        </form>

                        <div id="loading" class="d-none flex-column align-items-center justify-content-center my-3 p-3 bg-black border border-warning">
                            <div class="font-bangers text-warning" style="font-size: 3.5rem;" id="countdown">5</div>
                            <p class="mb-0 text-white fw-bold">Extrayendo papeletas ganadoras...</p>
                        </div>

                        @if(count($winners) > 0)
                            <form action="{{ route('tickets.sorteo.reset') }}" method="POST" onsubmit="return confirm('¿Seguro que deseas borrar los ganadores actuales de esta rifa?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="raffle_id" value="{{ $selectedRaffle->id }}">
                                <button type="submit" class="btn btn-outline-danger w-100 fw-bold">
                                    🔄 Reiniciar Ganadores
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="stage-panel p-4 h-100 text-white">
                        <h3 class="font-Oswald text-warning text-center border-bottom border-secondary pb-2 mb-4">
                            🏆 PAPELETAS PREMIADAS (Haz clic para revelar Blader)
                        </h3>

                        <div class="d-flex flex-wrap justify-content-center gap-4 py-3">
                            @forelse($winners as $index => $winner)
                                @php
                                    if ($index === 0) $position = '🥇 1.º Premio';
                                    elseif ($index === 1) $position = '🥈 2.º Premio';
                                    elseif ($index === 2) $position = '🥉 3.º Premio';
                                    else $position = ($index + 1) . '.º Premio';
                                @endphp
                                <div class="ticket-card-flip" onclick="this.classList.toggle('flipped')">
                                    <span class="fw-bold text-warning mb-1 d-block text-center font-Oswald fs-5">
                                        {{ $position }}
                                    </span>
                                    <div class="ticket-inner">
                                        <div class="ticket-front">
                                            <div class="small text-uppercase">Papeleta Premiada</div>
                                            <div class="font-Oswald fs-4">🎫 {{ $winner['ticket'] }}</div>
                                            <div class="small opacity-75 mt-1">(Clic para girar)</div>
                                        </div>
                                        <div class="ticket-back">
                                            <div class="small text-uppercase">Ganador Oficial</div>
                                            <div class="font-Oswald fs-4">👤 {{ $winner['user'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-white-50 py-5">
                                    <i class="fas fa-dice fa-3x mb-3 d-block text-secondary"></i>
                                    Todavía no se ha realizado el sorteo de esta rifa.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
@if($selectedRaffle)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startRaffle');
    const loading = document.getElementById('loading');
    const countdown = document.getElementById('countdown');
    const form = document.getElementById('drawRaffleForm');

    if (!startBtn) return;

    startBtn.addEventListener('click', function() {
        startBtn.disabled = true;
        let timeLeft = 5;
        loading.classList.remove('d-none');
        loading.classList.add('d-flex');
        countdown.textContent = timeLeft;

        const interval = setInterval(() => {
            timeLeft--;
            if (timeLeft > 0) {
                countdown.textContent = timeLeft;
            } else {
                clearInterval(interval);
                form.submit();
            }
        }, 1000);
    });
});
</script>
@endif
@endsection
