@if($winnersBracket->isEmpty() && $losersBracket->isEmpty() && $finals->isEmpty())
    <div class="text-center py-10 mt-6 bg-gray-900/50 rounded-xl border border-gray-800">
        <i class="fas fa-project-diagram text-4xl text-gray-700 mb-3"></i>
        <p class="text-gray-400 font-bold text-xs tracking-widest uppercase">CUADRO ELIMINATORIO AÚN NO GENERADO</p>
    </div>
@else
    <div class="flex justify-between items-center mb-3 bg-gray-950/90 border border-gray-800 px-3 py-2 rounded-xl">
        <span class="text-[10px] text-gray-400 font-bold tracking-wider uppercase">
            <i class="fas fa-hand-pointer text-cyan-400 mr-1"></i> Toca una partida para arbitrarla
        </span>
        <a href="/torneos/{{ request()->route('eventId') }}/cuadro" target="_blank" class="bg-indigo-950 hover:bg-indigo-900 text-indigo-300 border border-indigo-500/40 px-2.5 py-1 rounded-lg text-[10px] font-black tracking-wider uppercase transition-all">
            <i class="fas fa-external-link-alt mr-1"></i> PANTALLA COMPLETA
        </a>
    </div>

    <div id="arena-bracket-wrapper" class="overflow-x-auto pb-6 no-scrollbar select-none">
        <div id="arena-bracket-tree" class="relative inline-flex flex-col gap-12 min-w-max p-2">
            
            <svg id="arena-bracket-svg" class="absolute inset-0 pointer-events-none z-0" style="width: 100%; height: 100%; overflow: visible;">
                <defs>
                    <marker id="ar-win-fuchsia" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                        <path d="M 0 1 L 8 5 L 0 9 z" fill="#d946ef" />
                    </marker>
                    <marker id="ar-win-orange" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                        <path d="M 0 1 L 8 5 L 0 9 z" fill="#f97316" />
                    </marker>
                    <marker id="ar-win-yellow" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                        <path d="M 0 1 L 8 5 L 0 9 z" fill="#eab308" />
                    </marker>
                    <marker id="ar-pending" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="4" markerHeight="4" orient="auto-start-reverse">
                        <path d="M 0 2 L 7 5 L 0 8 z" fill="#4b5563" />
                    </marker>
                </defs>
            </svg>

            <!-- WINNERS + GRAN FINAL -->
            <div class="flex items-stretch gap-12 relative z-10">
                @if(isset($winnersBracket) && $winnersBracket->count() > 0)
                <div class="flex flex-col">
                    <div class="text-fuchsia-400 font-black tracking-widest font-mono text-xs uppercase mb-3 pl-2 border-l-4 border-fuchsia-500">
                        <i class="fas fa-trophy mr-1"></i> WINNERS BRACKET
                    </div>
                    <div class="flex items-stretch gap-10 flex-1">
                        @foreach($winnersBracket as $roundNum => $matches)
                        <div class="arena-b-col flex flex-col w-[215px] shrink-0" data-bracket="winners" data-round="{{ $roundNum }}">
                            <div class="text-center text-fuchsia-300 bg-fuchsia-950/30 border border-fuchsia-500/30 rounded-lg py-1 text-[10px] font-black tracking-widest mb-3 uppercase">
                                RONDA {{ $roundNum }}
                            </div>
                            <div class="flex flex-col justify-around flex-1 gap-4">
                                @foreach($matches as $idx => $match)
                                    @include('hub.partials.bracket_card', [
                                        'match' => $match,
                                        'theme' => 'fuchsia',
                                        'roundNumber' => $roundNum,
                                        'index' => $idx
                                    ])
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($finals) && $finals->count() > 0)
                <div class="flex flex-col justify-center">
                    <div class="text-yellow-400 font-black tracking-widest font-mono text-xs uppercase mb-3 pl-2 border-l-4 border-yellow-500">
                        <i class="fas fa-crown mr-1"></i> GRAN FINAL
                    </div>
                    <div class="flex items-center gap-10 flex-1">
                        @foreach($finals as $fIdx => $match)
                            <div class="arena-b-col flex flex-col w-[225px] shrink-0 justify-center" data-bracket="finals" data-round="{{ $fIdx + 1 }}">
                                <div class="text-center text-yellow-300 bg-yellow-950/40 border border-yellow-500/40 rounded-lg py-1 text-[10px] font-black tracking-widest mb-3 uppercase">
                                    {{ $match->bracket_type === 'grand_final_reset' ? 'GRAN FINAL (RESET)' : 'FINAL ABSOLUTA' }}
                                </div>
                                @include('hub.partials.bracket_card', [
                                    'match' => $match,
                                    'theme' => 'yellow',
                                    'roundNumber' => $fIdx + 1,
                                    'index' => 0
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- LOSERS BRACKET -->
            @if(isset($losersBracket) && $losersBracket->count() > 0)
            <div class="flex flex-col relative z-10 pt-4 border-t border-gray-800">
                <div class="text-orange-400 font-black tracking-widest font-mono text-xs uppercase mb-3 pl-2 border-l-4 border-orange-500">
                    <i class="fas fa-fire mr-1"></i> LOSERS BRACKET
                </div>
                <div class="flex items-stretch gap-10">
                    @foreach($losersBracket as $roundNum => $matches)
                    <div class="arena-b-col flex flex-col w-[215px] shrink-0" data-bracket="losers" data-round="{{ $roundNum }}">
                        <div class="text-center text-orange-300 bg-orange-950/30 border border-orange-500/30 rounded-lg py-1 text-[10px] font-black tracking-widest mb-3 uppercase">
                            LOSERS R{{ $roundNum }}
                        </div>
                        <div class="flex flex-col justify-around flex-1 gap-4">
                            @foreach($matches as $idx => $match)
                                @include('hub.partials.bracket_card', [
                                    'match' => $match,
                                    'theme' => 'orange',
                                    'roundNumber' => $roundNum,
                                    'index' => $idx
                                ])
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
@endif