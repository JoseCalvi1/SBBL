@extends('layouts.app')

@section('title', 'Mis Papeletas SBBL')

@section('content')
<div class="container py-5 text-white">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom border-secondary pb-3">
        <div>
            <h2 class="font-Oswald m-0" style="font-size: 2.6rem; color: var(--sbbl-gold);">
                <i class="fas fa-ticket-alt me-2 text-white"></i> MIS PAPELETAS DE RIFA
            </h2>
            <p class="text-white-50 mb-0 fw-bold">Tus participaciones quedan vinculadas de forma permanente a tu cuenta de blader.</p>
        </div>
        @if($activeRaffle)
            <a href="{{ route('tickets.buy') }}" class="btn-shonen btn-shonen-warning px-4 py-2 fs-5">
                <span><i class="fas fa-plus-circle me-2"></i> COMPRAR MÁS PAPELETAS</span>
            </a>
        @endif
    </div>

    @if($tickets->isEmpty())
        <div class="p-5 text-center border border-secondary" style="background: var(--sbbl-blue-2); box-shadow: 6px 6px 0 #000;">
            <i class="fas fa-receipt fa-3x text-secondary mb-3"></i>
            <h4 class="font-Oswald text-white">AÚN NO TIENES PAPELETAS</h4>
            <p class="text-white-50 mb-0">Cuando participes en una rifa oficial de la SBBL, tus códigos aparecerán aquí.</p>
        </div>
    @else
        @php
            $grouped = $tickets->groupBy(fn($t) => $t->raffle ? $t->raffle->title : 'Sorteo General');
        @endphp

        @foreach($grouped as $raffleTitle => $items)
            @php
                $raffleObj = $items->first()->raffle;
                $winningCodes = collect($raffleObj->winners_json ?? [])->pluck('ticket')->all();
            @endphp
            <div class="mb-4 p-4 border border-dark" style="background: var(--sbbl-blue-2); box-shadow: 6px 6px 0 #000;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="font-Oswald text-info m-0">{{ $raffleTitle }}</h4>
                    <span class="badge bg-black border border-secondary fs-6">{{ $items->count() }} papeleta(s)</span>
                </div>
                <div class="row g-3">
                    @foreach($items as $t)
                        @php $isWinner = in_array($t->identifier, $winningCodes); @endphp
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="p-3 bg-black border {{ $isWinner ? 'border-warning' : 'border-secondary' }} text-center">
                                <div class="font-Oswald fs-4 {{ $isWinner ? 'text-warning' : 'text-white' }}">
                                    @if($isWinner) 🏆 @endif {{ $t->identifier }}
                                </div>
                                <div class="small text-white-50">{{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '' }}</div>
                                @if($isWinner)
                                    <span class="badge bg-warning text-dark fw-bold mt-2">¡PAPELETA PREMIADA!</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
