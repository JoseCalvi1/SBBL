@if($winnersBracket->isEmpty() && $losersBracket->isEmpty() && $finals->isEmpty())
    <div class="text-center py-10 mt-10">
        <i class="fas fa-project-diagram text-4xl text-gray-700 mb-3"></i>
        <p class="text-gray-500 font-bold text-sm tracking-widest">CUADRO NO GENERADO AÚN</p>
    </div>
@else
    <style>
        .bracket-scroll-container {
            display: flex;
            flex-direction: row;
            gap: 2.5rem; /* Separación ancha y limpia entre columnas */
            padding: 1rem 0 2rem 0;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
        }
        
        .bracket-col {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 1.5rem; /* Separación vertical consistente entre tarjetas */
            min-width: 260px;
            scroll-snap-align: start;
        }
    </style>

    <div class="pb-10 mt-2">
        
        <!-- WINNERS BRACKET -->
        <div class="border-l-4 border-fuchsia-500 bg-gradient-to-r from-fuchsia-900/20 to-transparent p-2 rounded-r-lg mb-4 mt-2 shadow-sm">
            <h3 class="text-fuchsia-400 font-black tracking-widest font-mono text-lg m-0 flex items-center">
                <i class="fas fa-trophy mr-2 opacity-80"></i> WINNERS BRACKET
            </h3>
        </div>

        <div class="bracket-scroll-container no-scrollbar">
            @foreach($winnersBracket as $roundNumber => $matches)
                <div class="bracket-col">
                    <div class="text-center bg-gray-900 border border-gray-700 rounded-md py-1.5 text-[10px] font-black tracking-widest text-gray-400 shadow-lg sticky top-0 z-20">
                        RONDA {{ $roundNumber }}
                    </div>
                    
                    @foreach($matches as $match)
                        @include('hub.partials.bracket_card', ['match' => $match, 'theme' => 'fuchsia'])
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- LOSERS BRACKET -->
        @if($losersBracket->count() > 0)
            <div class="border-l-4 border-orange-500 bg-gradient-to-r from-orange-900/20 to-transparent p-2 rounded-r-lg mb-4 mt-8 shadow-sm">
                <h3 class="text-orange-400 font-black tracking-widest font-mono text-lg m-0 flex items-center">
                    <i class="fas fa-skull-crossbones mr-2 opacity-80"></i> LOSERS BRACKET
                </h3>
            </div>
            
            <div class="bracket-scroll-container no-scrollbar">
                @foreach($losersBracket as $roundNumber => $matches)
                    <div class="bracket-col">
                        <div class="text-center bg-gray-900 border border-gray-700 rounded-md py-1.5 text-[10px] font-black tracking-widest text-gray-400 shadow-lg sticky top-0 z-20">
                            RONDA {{ $roundNumber }}
                        </div>
                        
                        @foreach($matches as $match)
                            @include('hub.partials.bracket_card', ['match' => $match, 'theme' => 'orange'])
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

        <!-- GRAN FINAL -->
        @if($finals->count() > 0)
            <div class="border-l-4 border-yellow-400 bg-gradient-to-r from-yellow-900/20 to-transparent p-2 rounded-r-lg mb-4 mt-8 shadow-sm">
                <h3 class="text-yellow-400 font-black tracking-widest font-mono text-lg m-0 flex items-center">
                    <i class="fas fa-crown mr-2 opacity-80"></i> GRAN FINAL
                </h3>
            </div>
            
            <div class="bracket-scroll-container no-scrollbar">
                @foreach($finals as $match)
                    <div class="bracket-col">
                        <div class="text-center bg-yellow-900/50 border border-yellow-600/50 rounded-md py-1.5 text-[10px] font-black tracking-widest text-yellow-500 shadow-lg sticky top-0 z-20">
                            {{ str_replace('_', ' ', mb_strtoupper($match->bracket_type)) }}
                        </div>
                        
                        @include('hub.partials.bracket_card', ['match' => $match, 'theme' => 'yellow'])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif