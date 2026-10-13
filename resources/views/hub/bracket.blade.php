@extends('layouts.app')

@section('content')
<!-- Contenedor principal blindado con estilos en línea -->
<div style="background-color: #030712; color: white; padding-bottom: 4rem; min-height: 100vh;">
    
    <!-- Cabecera Adhesiva Principal -->
    <div style="padding: 1rem; border-bottom: 1px solid #1f2937; position: sticky; top: 0; z-index: 50; background-color: #111827; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);">
        <h1 style="font-size: 1.25rem; font-weight: 900; color: #22d3ee; text-transform: uppercase; margin: 0; font-family: sans-serif; display: flex; align-items: center;">
            <i class="fas fa-sitemap mr-2" style="color: #6366f1;"></i> {{ $event->name }}
        </h1>
        <p style="font-size: 0.75rem; color: #6b7280; font-weight: bold; margin: 0; letter-spacing: 1px; margin-top: 4px;">CUADRO DE DOBLE ELIMINACIÓN</p>
    </div>

    <!-- Zona de Scroll Horizontal Magnético -->
    <div style="overflow-x: auto; white-space: nowrap; padding: 20px; min-height: 70vh; -webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory; scroll-behavior: smooth;" class="no-scrollbar">
        
        <!-- WINNERS BRACKET -->
        <div style="border-left: 4px solid #d946ef; color: #d946ef; font-weight: 900; letter-spacing: 2px; font-family: monospace; margin-bottom: 20px; padding-left: 10px; font-size: 1.1rem; position: sticky; left: 20px;">
            WINNERS BRACKET
        </div>
        <div style="margin-bottom: 50px; display: flex; gap: 40px;">
            @foreach($winnersBracket as $roundNumber => $matches)
                <div style="display: flex; flex-direction: column; width: 260px; flex-shrink: 0; scroll-snap-align: start;">
                    <!-- Cabecera de ronda adhesiva al hacer scroll hacia abajo -->
                    <div style="text-align: center; color: #d946ef; background: #030712; font-size: 11px; font-weight: bold; letter-spacing: 2px; padding-bottom: 15px; position: sticky; top: 75px; z-index: 40; border-bottom: 1px solid #1f2937; margin-bottom: 15px;">
                        RONDA {{ $roundNumber }}
                    </div>
                    @foreach($matches as $match)
                        @include('hub.partials.bracket_card', ['match' => $match])
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- LOSERS BRACKET -->
        <div style="border-left: 4px solid #f97316; color: #f97316; font-weight: 900; letter-spacing: 2px; font-family: monospace; margin-bottom: 20px; padding-left: 10px; font-size: 1.1rem; position: sticky; left: 20px;">
            LOSERS BRACKET
        </div>
        <div style="margin-bottom: 50px; display: flex; gap: 40px;">
            @foreach($losersBracket as $roundNumber => $matches)
                <div style="display: flex; flex-direction: column; width: 260px; flex-shrink: 0; scroll-snap-align: start;">
                    <div style="text-align: center; color: #f97316; background: #030712; font-size: 11px; font-weight: bold; letter-spacing: 2px; padding-bottom: 15px; position: sticky; top: 75px; z-index: 40; border-bottom: 1px solid #1f2937; margin-bottom: 15px;">
                        RONDA {{ $roundNumber }}
                    </div>
                    @foreach($matches as $match)
                        @include('hub.partials.bracket_card', ['match' => $match])
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- GRAN FINAL -->
        @if($finals->count() > 0)
            <div style="border-left: 4px solid #eab308; color: #eab308; font-weight: 900; letter-spacing: 2px; font-family: monospace; margin-bottom: 20px; padding-left: 10px; font-size: 1.1rem; position: sticky; left: 20px;">
                GRAN FINAL
            </div>
            <div style="display: flex; gap: 40px;">
                @foreach($finals as $match)
                    <div style="display: flex; flex-direction: column; width: 280px; flex-shrink: 0; scroll-snap-align: start;">
                        <div style="text-align: center; color: #eab308; background: #030712; font-size: 11px; font-weight: bold; letter-spacing: 2px; padding-bottom: 15px; position: sticky; top: 75px; z-index: 40; border-bottom: 1px solid #1f2937; margin-bottom: 15px;">
                            {{ str_replace('_', ' ', mb_strtoupper($match->bracket_type)) }}
                        </div>
                        @include('hub.partials.bracket_card', ['match' => $match])
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection