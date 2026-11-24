@extends('layouts.app')

@section('title', 'Papeletas Vendidas - ' . $raffle->title)

@section('content')
<div class="container py-4 text-white">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-Oswald text-warning m-0"><i class="fas fa-ticket-alt me-2"></i> {{ $raffle->title }}</h2>
            <p class="text-white-50 mb-0">Total papeletas emitidas: <strong>{{ $tickets->count() }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tickets.sorteo', ['raffle_id' => $raffle->id]) }}" class="btn btn-warning text-dark fw-bold">
                <i class="fas fa-dice me-1"></i> Ir al Sorteo
            </a>
            <a href="{{ route('admin.raffles.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i> Volver a Rifas
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-striped table-bordered align-middle">
            <thead>
                <tr class="text-info font-Oswald">
                    <th>Papeleta</th>
                    <th>Blader</th>
                    <th>Email</th>
                    <th class="text-end">Importe</th>
                    <th>ID Captura PayPal</th>
                    <th>Fecha Compra</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                    <tr>
                        <td class="fw-bold text-warning"><code>{{ $t->identifier }}</code></td>
                        <td class="fw-bold">{{ $t->user ? $t->user->name : 'Usuario #' . $t->user_id }}</td>
                        <td class="text-white-50">{{ $t->user ? $t->user->email : '-' }}</td>
                        <td class="text-end">{{ number_format($t->price_paid, 2, ',', '.') }} €</td>
                        <td><code>{{ $t->paypal_capture_id ?? '-' }}</code></td>
                        <td>{{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-white-50 py-4">Aún no se ha vendido ninguna papeleta en esta rifa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
