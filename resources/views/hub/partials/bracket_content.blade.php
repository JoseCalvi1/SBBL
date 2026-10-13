@if($winnersBracket->isEmpty() && $losersBracket->isEmpty() && $finals->isEmpty())
    <div class="text-center py-10 mt-10">
        <i class="fas fa-project-diagram text-4xl text-gray-700 mb-3"></i>
        <p class="text-gray-500 font-bold text-sm tracking-widest">CUADRO NO GENERADO AÚN</p>
    </div>
@else
    <div style="overflow-x: auto; white-space: nowrap; padding-bottom: 20px; margin-top: 10px; -webkit-overflow-scrolling: touch;">
        
        <!-- WINNERS BRACKET -->
        <div style="border-left: 4px solid #d946ef; color: #d946ef; font-weight: 900; letter-spacing: 2px; font-family: monospace; margin-bottom: 20px; padding-left: 10px; font-size: 1.1rem;">WINNERS BRACKET</div>
        <div style="margin-bottom: 30px;">
            @foreach($winnersBracket as $roundNumber => $matches)
                <div style="display: inline-block; vertical-align: top; width: 260px; margin-right: 30px; white-space: normal;">
                    <div style="text-align: center; color: #6b7280; font-size: 11px; font-weight: bold; letter-spacing: 2px; margin-bottom: 15px;">RONDA {{ $roundNumber }}</div>
                    @foreach($matches as $match)
                        @include('hub.partials.bracket_card', ['match' => $match])
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- LOSERS BRACKET -->
        @if($losersBracket->count() > 0)
        <div style="border-left: 4px solid #f97316; color: #f97316; font-weight: 900; letter-spacing: 2px; font-family: monospace; margin-bottom: 20px; padding-left: 10px; font-size: 1.1rem; margin-top: 30px;">LOSERS BRACKET</div>
        <div style="margin-bottom: 30px;">
            @foreach($losersBracket as $roundNumber => $matches)
                <div style="display: inline-block; vertical-align: top; width: 260px; margin-right: 30px; white-space: normal;">
                    <div style="text-align: center; color: #6b7280; font-size: 11px; font-weight: bold; letter-spacing: 2px; margin-bottom: 15px;">RONDA {{ $roundNumber }}</div>
                    @foreach($matches as $match)
                        @include('hub.partials.bracket_card', ['match' => $match])
                    @endforeach
                </div>
            @endforeach
        </div>
        @endif

        <!-- GRAN FINAL -->
        @if($finals->count() > 0)
            <div style="border-left: 4px solid #eab308; color: #eab308; font-weight: 900; letter-spacing: 2px; font-family: monospace; margin-bottom: 20px; padding-left: 10px; font-size: 1.1rem; margin-top: 30px;">GRAN FINAL</div>
            <div>
                @foreach($finals as $match)
                    <div style="display: inline-block; vertical-align: top; width: 260px; margin-right: 30px; white-space: normal;">
                        <div style="text-align: center; color: #6b7280; font-size: 11px; font-weight: bold; letter-spacing: 2px; margin-bottom: 15px;">{{ str_replace('_', ' ', mb_strtoupper($match->bracket_type)) }}</div>
                        @include('hub.partials.bracket_card', ['match' => $match])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif