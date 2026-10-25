@if($winnersBracket->isEmpty() && $losersBracket->isEmpty() && $finals->isEmpty())
    <div class="text-center py-10 mt-6 bg-gray-900/50 rounded-xl border border-gray-800">
        <i class="fas fa-project-diagram text-4xl text-gray-700 mb-3"></i>
        <p class="text-gray-400 font-bold text-xs tracking-widest uppercase">CUADRO ELIMINATORIO AÚN NO GENERADO</p>
    </div>
@else
    <!-- Barra de herramientas rápida del Cuadro -->
    <div class="flex justify-between items-center bg-gray-900 border border-gray-800 rounded-lg px-3 py-2 mb-3">
        <span class="text-[10px] text-gray-400 font-bold tracking-wider uppercase"><i class="fas fa-hand-pointer text-cyan-400 mr-1"></i> Toca una partida para arbitrarla</span>
        <a href="/torneos/{{ $event->id ?? request()->route('eventId') }}/cuadro" target="_blank" class="bg-indigo-950 hover:bg-indigo-900 text-indigo-300 border border-indigo-500/40 px-2.5 py-1 rounded text-[10px] font-bold tracking-wider uppercase flex items-center gap-1 transition-colors">
            <i class="fas fa-external-link-alt"></i> Pantalla Completa
        </a>
    </div>

    <!-- Contenedor con Scroll Horizontal Suave y Árbol SVG -->
    <div id="arena-bracket-wrapper" class="overflow-x-auto no-scrollbar pb-8 pt-2 relative">
        <div id="arena-bracket-tree" class="relative inline-flex flex-col gap-12 min-w-max pr-6">
            
            <!-- CAPA SVG DE LÍNEAS Y FLECHAS -->
            <svg id="arena-bracket-svg" class="absolute inset-0 pointer-events-none z-0" style="width: 100%; height: 100%; overflow: visible;">
                <defs>
                    <marker id="ar-win-fuchsia" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse"><path d="M 0 1 L 8 5 L 0 9 z" fill="#d946ef" /></marker>
                    <marker id="ar-win-orange" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse"><path d="M 0 1 L 8 5 L 0 9 z" fill="#f97316" /></marker>
                    <marker id="ar-win-yellow" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse"><path d="M 0 1 L 8 5 L 0 9 z" fill="#eab308" /></marker>
                    <marker id="ar-pending" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="4" markerHeight="4" orient="auto-start-reverse"><path d="M 0 2 L 7 5 L 0 8 z" fill="#4b5563" /></marker>
                </defs>
            </svg>

            <!-- WINNERS + GRAN FINAL -->
            <div class="flex items-stretch gap-12 relative z-10">
                @if($winnersBracket->count() > 0)
                <div class="flex flex-col">
                    <div class="border-l-4 border-fuchsia-500 pl-2.5 mb-4">
                        <h3 class="text-fuchsia-400 font-black tracking-widest font-mono text-sm m-0 uppercase"><i class="fas fa-trophy mr-1.5"></i> WINNERS BRACKET</h3>
                    </div>
                    <div class="flex items-stretch gap-10 flex-1">
                        @foreach($winnersBracket as $roundNumber => $matches)
                            <div class="arena-b-col flex flex-col w-[230px] shrink-0" data-bracket="winners" data-round="{{ $roundNumber }}">
                                <div class="text-center bg-fuchsia-950/30 border border-fuchsia-500/30 rounded py-1 text-[10px] font-black tracking-widest text-fuchsia-400 mb-4 uppercase">
                                    RONDA {{ $roundNumber }}
                                </div>
                                <div class="flex flex-col justify-around flex-1 gap-4">
                                    @foreach($matches as $match)
                                        @include('hub.partials.bracket_card', ['match' => $match, 'theme' => 'fuchsia'])
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($finals->count() > 0)
                <div class="flex flex-col justify-center">
                    <div class="border-l-4 border-yellow-400 pl-2.5 mb-4">
                        <h3 class="text-yellow-400 font-black tracking-widest font-mono text-sm m-0 uppercase"><i class="fas fa-crown mr-1.5"></i> GRAN FINAL</h3>
                    </div>
                    <div class="flex items-center gap-10 flex-1">
                        @foreach($finals as $fIdx => $match)
                            <div class="arena-b-col flex flex-col w-[240px] shrink-0 justify-center" data-bracket="finals" data-round="{{ $fIdx + 1 }}">
                                <div class="text-center bg-yellow-950/40 border border-yellow-500/40 rounded py-1 text-[10px] font-black tracking-widest text-yellow-400 mb-4 uppercase">
                                    {{ str_replace('_', ' ', mb_strtoupper($match->bracket_type)) }}
                                </div>
                                @include('hub.partials.bracket_card', ['match' => $match, 'theme' => 'yellow'])
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- LOSERS BRACKET -->
            @if($losersBracket->count() > 0)
            <div class="flex flex-col relative z-10 pt-6 border-t border-gray-800/80">
                <div class="border-l-4 border-orange-500 pl-2.5 mb-4">
                    <h3 class="text-orange-400 font-black tracking-widest font-mono text-sm m-0 uppercase"><i class="fas fa-fire mr-1.5"></i> LOSERS BRACKET</h3>
                </div>
                <div class="flex items-stretch gap-10">
                    @foreach($losersBracket as $roundNumber => $matches)
                        <div class="arena-b-col flex flex-col w-[230px] shrink-0" data-bracket="losers" data-round="{{ $roundNumber }}">
                            <div class="text-center bg-orange-950/30 border border-orange-500/30 rounded py-1 text-[10px] font-black tracking-widest text-orange-400 mb-4 uppercase">
                                RONDA {{ $roundNumber }}
                            </div>
                            <div class="flex flex-col justify-around flex-1 gap-4">
                                @foreach($matches as $match)
                                    @include('hub.partials.bracket_card', ['match' => $match, 'theme' => 'orange'])
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