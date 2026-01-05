@extends('layouts.app')

@section('content')
<div class="raffle-container position-relative py-5" style="min-height: 80vh; background: linear-gradient(to bottom, #ff7e5f, #feb47b); overflow: hidden;">

    <h1 class="display-1 fw-bold text-white text-center mb-5 animate__animated animate__pulse">
        🎄 Rifa Navideña 🎄
    </h1>

    @if(session('error'))
        <div class="alert alert-danger w-50 mx-auto">{{ session('error') }}</div>
    @endif

    <div class="container">
        <div class="row g-4 justify-content-center">

            <!-- 🟢 TARJETA CONTROL -->
            <div class="col-lg-5">
                <div class="card shadow-lg border-0 text-center">
                    <div class="card-header bg-success text-white fw-bold fs-4">
                        🎟️ Control de la Rifa
                    </div>

                    <div class="card-body">
                        <p class="fs-5 mb-1">
                            Participantes: <strong>{{ $totalParticipants }}</strong>
                        </p>
                        <p class="fs-5 mb-3">
                            Total de papeletas: <strong>{{ $totalTickets }}</strong>
                        </p>

                        <!-- Botón comenzar -->
                        <form id="raffleForm" class="mb-3">
                            @csrf
                            <button type="button" id="startRaffle"
                                class="btn btn-success btn-lg w-100 shadow animate__animated animate__pulse">
                                ▶️ Comenzar Rifa
                            </button>
                        </form>

                        <!-- Temporizador -->
                        <div id="loading" class="d-none flex-column align-items-center justify-content-center mb-3">
                            <img src="https://i.gifer.com/YCZH.gif" width="350">
                            <p class="mt-2">
                                Revelando ganadores en
                                <strong><span id="countdown">10</span></strong> segundos...
                            </p>
                        </div>

                        <!-- Reset -->
                        <form action="{{ route('tickets.sorteo.reset') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                🔄 Resetear Rifa
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 🏆 TARJETA PREMIADOS -->
            <div class="col-lg-7">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-warning text-dark fw-bold fs-4 text-center">
                        🏆 Premiados
                    </div>

                    <div class="card-body py-5">
                        <div id="winners" class="d-flex flex-wrap justify-content-center gap-4">

                            @foreach($winners as $index => $winner)
                                @php
                                    switch($index) {
                                        case 0: $position = '🥇 1.º Premio'; break;
                                        case 1: $position = '🥈 2.º Premio'; break;
                                        case 2: $position = '🥉 3.º Premio'; break;
                                        default: $position = ($index + 1).'.º Premio'; break;
                                    }
                                @endphp

                                <div class="ticket-card" onclick="this.classList.toggle('flipped')">
                                    <span class="fw-bold text-warning mb-1 d-block text-center">
                                        {{ $position }}
                                    </span>

                                    <div class="ticket-inner rounded shadow-lg">
                                        <div class="ticket-front">
                                            🎫 {{ $winner['ticket'] }}
                                        </div>
                                        <div class="ticket-back">
                                            👤 {{ $winner['user'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if(count($winners) === 0)
                                <p class="text-muted text-center w-100">
                                    Aún no hay ganadores
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ❄️ NIEVE -->
    <div class="snow"></div>
</div>
@endsection

@section('styles')
<style>
@keyframes snowFall {
    0% { transform: translateY(-10%); }
    100% { transform: translateY(100vh); }
}

.snow {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.snow::before, .snow::after {
    content: "❄";
    position: absolute;
    font-size: 2rem;
    color: white;
    animation: snowFall linear infinite;
}
.snow::before { left: 15%; animation-duration: 10s; }
.snow::after { left: 80%; animation-duration: 14s; }

/* Tarjetas flip */
.ticket-card {
    width: 220px;
    height: 130px;
    perspective: 1000px;
    cursor: pointer;
}

.ticket-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transition: transform 0.8s;
    transform-style: preserve-3d;
}

.ticket-card.flipped .ticket-inner {
    transform: rotateY(180deg);
}

.ticket-front,
.ticket-back {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.ticket-front {
    background: #fff;
}

.ticket-back {
    background: #198754;
    color: #fff;
    transform: rotateY(180deg);
}
</style>
@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startRaffle');
    const loading = document.getElementById('loading');
    const countdown = document.getElementById('countdown');

    startBtn.addEventListener('click', function() {
        let timeLeft = 10;
        loading.classList.remove('d-none');

        const interval = setInterval(() => {
            countdown.textContent = timeLeft;
            timeLeft--;
            if(timeLeft < 0){
                clearInterval(interval);

                // Obtener token CSRF directamente desde meta
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch("{{ route('tickets.sorteo.draw') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token
                    },
                    body: JSON.stringify({})
                }).then(response => {
                    if(response.redirected){
                        window.location.href = response.url;
                    }
                });
            }
        }, 1000);
    });
});
</script>
@endsection
