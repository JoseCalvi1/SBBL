<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>En Directo - {{ $event->name }}</title>
    
    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Librería Panzoom para experiencia tipo Google Maps / Start.gg -->
    <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body { 
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #030712;
            overflow: hidden; /* Evitamos scroll nativo en vista cuadro */
            user-select: none;
        }
        /* Fondo de cuadrícula táctica eSports */
        .bg-grid-pattern {
            background-color: #030712;
            background-image: 
                linear-gradient(to right, rgba(31, 41, 55, 0.25) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(31, 41, 55, 0.25) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        /* Efectos de resaltado al buscar o clicar un jugador */
        .player-row { transition: all 0.25s ease; cursor: pointer; }
        .player-row:hover { background-color: rgba(6, 182, 212, 0.15) !important; }
        .player-highlight { 
            background-color: rgba(6, 182, 212, 0.25) !important; 
            box-shadow: inset 0 0 12px rgba(6, 182, 212, 0.5);
        }
        .card-dimmed { opacity: 0.25; filter: grayscale(70%); transition: all 0.3s ease; }
        .card-focused { 
            transform: scale(1.03); 
            box-shadow: 0 0 25px rgba(6, 182, 212, 0.4) !important; 
            border-color: #22d3ee !important;
            z-index: 30;
        }
        /* Animación de flujo para líneas ganadoras */
        @keyframes pulseGlow {
            0%, 100% { filter: drop-shadow(0 0 4px currentColor); }
            50% { filter: drop-shadow(0 0 10px currentColor); }
        }
        .line-active { animation: pulseGlow 2.5s infinite; }
    </style>
</head>
<body class="text-white h-screen flex flex-col">

    <!-- CABECERA SUPERIOR FIJA -->
    <header class="p-3 sm:p-4 border-b border-gray-800 bg-gray-900/95 backdrop-blur-md z-50 shadow-2xl shrink-0">
        <div class="flex justify-between items-center flex-wrap gap-3 max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-950 border border-indigo-500/50 flex items-center justify-center shadow-[0_0_15px_rgba(99,102,241,0.3)]">
                    <i class="fas fa-sitemap text-indigo-400 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg sm:text-2xl font-black text-cyan-400 uppercase tracking-wide leading-none m-0">
                        {{ $event->name }}
                    </h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <p class="text-[10px] sm:text-xs text-gray-400 font-bold tracking-widest m-0 uppercase">ARENA EN DIRECTO</p>
                    </div>
                </div>
            </div>
            
            <!-- Buscador de Blader + Botón Actualizar -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <div id="search-container" class="relative flex-1 sm:w-56">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-500 text-xs"></i>
                    <input type="text" id="player-search" oninput="filtrarJugador(this.value)" placeholder="Buscar Blader en el árbol..." 
                        class="w-full bg-gray-950 border border-gray-700 focus:border-cyan-400 text-white text-xs rounded-lg pl-8 pr-7 py-2 outline-none transition-all shadow-inner">
                    <button id="clear-search" onclick="limpiarBusqueda()" class="hidden absolute right-2.5 top-2 text-gray-500 hover:text-white text-xs">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>

                <button onclick="window.location.reload()" class="bg-gray-800 hover:bg-gray-700 active:scale-95 text-white border border-gray-600 px-3.5 py-2 rounded-lg font-bold flex items-center gap-2 text-xs transition-all shadow-md shrink-0">
                    <i class="fas fa-sync-alt text-cyan-400"></i> <span class="hidden sm:inline">ACTUALIZAR</span>
                </button>
            </div>
        </div>

        <!-- PESTAÑAS DE VISTA -->
        <div class="flex gap-2 mt-3 max-w-7xl mx-auto w-full">
            <button id="btn-cuadro" onclick="switchPublicView('cuadro')" class="flex-1 bg-gray-800 text-cyan-400 border border-cyan-500/40 py-2.5 rounded-lg font-bold tracking-widest transition-all shadow-[0_0_15px_rgba(6,182,212,0.15)] text-xs flex items-center justify-center gap-2">
                <i class="fas fa-project-diagram"></i> ÁRBOL DEL TORNEO
            </button>
            <button id="btn-tabla" onclick="switchPublicView('tabla')" class="flex-1 bg-gray-950/60 text-gray-400 border border-gray-800 hover:border-gray-700 py-2.5 rounded-lg font-bold tracking-widest transition-all text-xs flex items-center justify-center gap-2">
                <i class="fas fa-list-ol"></i> CLASIFICACIÓN GENERAL
            </button>
        </div>
    </header>

    <!-- ========================================== -->
    <!-- ZONA 1: LIENZO INTERACTIVO DEL CUADRO      -->
    <!-- ========================================== -->
    <div id="view-cuadro" class="relative flex-1 w-full h-full overflow-hidden bg-grid-pattern cursor-grab active:cursor-grabbing">
        
        <!-- Controles Flotantes de Zoom y Navegación -->
        <div class="absolute bottom-5 right-5 z-40 flex flex-col gap-2 bg-gray-900/90 p-1.5 rounded-xl border border-gray-700 shadow-2xl backdrop-blur-md">
            <button onclick="zoomIn()" title="Acercar" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 text-cyan-400 rounded-lg flex items-center justify-center text-base transition-colors border border-gray-700 active:scale-90">
                <i class="fas fa-plus"></i>
            </button>
            <button onclick="zoomOut()" title="Alejar" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 text-cyan-400 rounded-lg flex items-center justify-center text-base transition-colors border border-gray-700 active:scale-90">
                <i class="fas fa-minus"></i>
            </button>
            <button onclick="resetZoom()" title="Centrar Cuadro" class="w-10 h-10 bg-gray-800 hover:bg-gray-700 text-yellow-400 rounded-lg flex items-center justify-center text-base transition-colors border border-gray-700 active:scale-90">
                <i class="fas fa-expand"></i>
            </button>
        </div>

        <!-- Leyenda Flotante Interactiva -->
        <div class="absolute bottom-5 left-5 z-40 hidden sm:flex items-center gap-4 bg-gray-900/90 px-4 py-2 rounded-xl border border-gray-800 text-[11px] text-gray-400 font-bold tracking-wider backdrop-blur-md pointer-events-none">
            <span><i class="fas fa-mouse-pointer text-cyan-400 mr-1"></i> Arrastra para mover</span>
            <span><i class="fas fa-search-plus text-fuchsia-400 mr-1"></i> Rueda/Pellizca para Zoom</span>
            <span><i class="fas fa-hand-pointer text-yellow-400 mr-1"></i> Toca un Blader para ver su ruta</span>
        </div>

        <!-- ESCENARIO PANZOOM -->
        <div id="panzoom-scene" class="inline-block p-12 sm:p-20 min-w-max origin-top-left">
            
            <!-- CONTENEDOR RELATIVO PARA EL ÁRBOL Y EL SVG DE FLECHAS -->
            <div id="bracket-tree-container" class="relative flex flex-col gap-20">
                
                <!-- CAPA SVG PARA LAS LÍNEAS Y FLECHAS DE CONEXIÓN -->
                <svg id="bracket-svg-lines" class="absolute inset-0 pointer-events-none z-0" style="width: 100%; height: 100%; overflow: visible;">
                    <defs>
                        <!-- Flecha Ganador (Amarillo/Dorado) -->
                        <marker id="arrow-win-winners" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 8 5 L 0 9 z" fill="#d946ef" />
                        </marker>
                        <marker id="arrow-win-losers" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 8 5 L 0 9 z" fill="#f97316" />
                        </marker>
                        <marker id="arrow-win-finals" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 8 5 L 0 9 z" fill="#eab308" />
                        </marker>
                        <!-- Flecha Pendiente (Gris) -->
                        <marker id="arrow-pending" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                            <path d="M 0 2 L 7 5 L 0 8 z" fill="#4b5563" />
                        </marker>
                    </defs>
                </svg>

                <!-- BLOQUE SUPERIOR: WINNERS BRACKET + GRAN FINAL A LA DERECHA -->
                <div class="flex items-stretch gap-24 relative z-10">
                    
                    <!-- 1. WINNERS BRACKET -->
                    @if($winnersBracket->count() > 0)
                    <div class="flex flex-col">
                        <div class="flex items-center gap-3 mb-8 pl-3 border-l-4 border-fuchsia-500">
                            <span class="text-fuchsia-400 font-black tracking-widest font-mono text-lg uppercase drop-shadow-[0_0_8px_rgba(217,70,239,0.5)]">
                                <i class="fas fa-crown mr-1"></i> WINNERS BRACKET
                            </span>
                        </div>
                        
                        <div class="flex items-stretch gap-20 flex-1">
                            @foreach($winnersBracket as $roundNumber => $matches)
                                <div class="bracket-round-col flex flex-col w-[270px] shrink-0" data-bracket="winners" data-round="{{ $roundNumber }}">
                                    <div class="text-center text-fuchsia-400 bg-fuchsia-950/30 border border-fuchsia-500/30 rounded-lg py-2 text-[11px] font-black tracking-widest mb-6 uppercase shadow-inner">
                                        RONDA {{ $roundNumber }}
                                    </div>
                                    <!-- justify-around crea el efecto de árbol real centrando hijos respecto a padres -->
                                    <div class="flex flex-col justify-around flex-1 gap-6">
                                        @foreach($matches as $index => $match)
                                            @php
                                                $isTeam = !is_null($match->team1_id) && !is_null($match->team2_id);
                                                $p1Id = $isTeam ? $match->team1_id : $match->player1_id;
                                                $p2Id = $isTeam ? $match->team2_id : $match->player2_id;
                                                $p1Name = $isTeam ? ($match->team1->name ?? 'ESPERANDO...') : ($match->player1->name ?? 'ESPERANDO...');
                                                $p2Name = $isTeam ? ($match->team2->name ?? 'ESPERANDO...') : ($match->player2->name ?? 'ESPERANDO...');
                                                $isBye = ($match->status === 'completed' && is_null($p2Id));
                                                if($isBye) $p2Name = 'BYE (AVANZA)';
                                                
                                                $p1Won = ($match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
                                                $p2Won = ($match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
                                            @endphp
                                            <div class="match-node relative bg-gray-900/95 border-2 {{ $match->status === 'playing' ? 'border-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.5)]' : ($match->status === 'completed' ? 'border-gray-700' : 'border-fuchsia-900/60') }} rounded-xl overflow-hidden shadow-2xl transition-all"
                                                 id="match-{{ $match->id }}"
                                                 data-match-id="{{ $match->id }}"
                                                 data-bracket="winners"
                                                 data-round="{{ $roundNumber }}"
                                                 data-index="{{ $index }}"
                                                 data-status="{{ $match->status }}"
                                                 data-winner="{{ $match->winner_id }}"
                                                 data-p1="{{ $p1Id }}"
                                                 data-p2="{{ $p2Id }}">
                                                
                                                <!-- Cabecera de Tarjeta -->
                                                <div class="bg-gray-950 px-3 py-1.5 flex justify-between items-center border-b border-gray-800 text-[10px] font-mono font-bold text-gray-500">
                                                    <span>PARTIDA #{{ $match->match_number ?? $match->id }}</span>
                                                    @if($match->status === 'playing')
                                                        <span class="text-cyan-400 animate-pulse"><i class="fas fa-circle text-[7px] mr-1"></i> EN JUEGO</span>
                                                    @elseif($match->status === 'completed')
                                                        <span class="text-green-500"><i class="fas fa-check mr-1"></i> FINALIZADO</span>
                                                    @else
                                                        <span>PENDIENTE</span>
                                                    @endif
                                                </div>

                                                <!-- Jugador 1 -->
                                                <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p1Name)) }}')" 
                                                     class="player-row flex items-center justify-between px-3.5 py-2.5 border-b border-gray-800/80 {{ $p1Won ? 'bg-fuchsia-950/30' : '' }}"
                                                     data-player-name="{{ mb_strtoupper($p1Name) }}">
                                                    <div class="flex items-center gap-2 truncate pr-2">
                                                        @if($p1Won)
                                                            <i class="fas fa-caret-right text-fuchsia-400 text-sm shrink-0"></i>
                                                        @endif
                                                        <span class="text-xs font-bold truncate {{ $p1Won ? 'text-white' : (is_null($p1Id) ? 'text-gray-600 italic' : 'text-gray-400') }}">
                                                            {{ mb_strtoupper($p1Name) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p1Won ? 'bg-fuchsia-500 text-white shadow-[0_0_10px_rgba(217,70,239,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                        {{ $match->player1_score ?? 0 }}
                                                    </span>
                                                </div>

                                                <!-- Jugador 2 -->
                                                <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p2Name)) }}')" 
                                                     class="player-row flex items-center justify-between px-3.5 py-2.5 {{ $p2Won ? 'bg-fuchsia-950/30' : '' }}"
                                                     data-player-name="{{ mb_strtoupper($p2Name) }}">
                                                    <div class="flex items-center gap-2 truncate pr-2">
                                                        @if($p2Won)
                                                            <i class="fas fa-caret-right text-fuchsia-400 text-sm shrink-0"></i>
                                                        @endif
                                                        <span class="text-xs font-bold truncate {{ $p2Won ? 'text-white' : (is_null($p2Id) ? 'text-gray-600 italic' : 'text-gray-400') }}">
                                                            {{ mb_strtoupper($p2Name) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p2Won ? 'bg-fuchsia-500 text-white shadow-[0_0_10px_rgba(217,70,239,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                        {{ $isBye ? '-' : ($match->player2_score ?? 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- 2. GRAN FINAL (Situada estratégicamente al final del árbol) -->
                    @if($finals->count() > 0)
                    <div class="flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-8 pl-3 border-l-4 border-yellow-500">
                            <span class="text-yellow-400 font-black tracking-widest font-mono text-lg uppercase drop-shadow-[0_0_10px_rgba(234,179,8,0.6)]">
                                <i class="fas fa-trophy mr-1"></i> GRAN FINAL
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-20 flex-1">
                            @foreach($finals as $fIndex => $match)
                                @php
                                    $isTeam = !is_null($match->team1_id) && !is_null($match->team2_id);
                                    $p1Id = $isTeam ? $match->team1_id : $match->player1_id;
                                    $p2Id = $isTeam ? $match->team2_id : $match->player2_id;
                                    $p1Name = $isTeam ? ($match->team1->name ?? 'CAMPEÓN WINNERS') : ($match->player1->name ?? 'CAMPEÓN WINNERS');
                                    $p2Name = $isTeam ? ($match->team2->name ?? 'CAMPEÓN LOSERS') : ($match->player2->name ?? 'CAMPEÓN LOSERS');
                                    $p1Won = ($match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
                                    $p2Won = ($match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
                                @endphp
                                <div class="bracket-round-col flex flex-col w-[300px] shrink-0 justify-center" data-bracket="finals" data-round="{{ $fIndex + 1 }}">
                                    <div class="text-center text-yellow-400 bg-yellow-950/40 border border-yellow-500/40 rounded-lg py-2 text-xs font-black tracking-widest mb-6 uppercase shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                                        <i class="fas fa-star mr-1"></i> {{ $match->bracket_type === 'grand_final_reset' ? 'GRAN FINAL (RESET)' : 'GRAN FINAL' }}
                                    </div>

                                    <div class="match-node relative bg-gray-900 border-2 border-yellow-500/80 rounded-xl overflow-hidden shadow-[0_0_30px_rgba(234,179,8,0.25)] transition-all"
                                         id="match-{{ $match->id }}"
                                         data-match-id="{{ $match->id }}"
                                         data-bracket="finals"
                                         data-round="{{ $fIndex + 1 }}"
                                         data-index="0"
                                         data-status="{{ $match->status }}"
                                         data-winner="{{ $match->winner_id }}"
                                         data-p1="{{ $p1Id }}"
                                         data-p2="{{ $p2Id }}">
                                        
                                        <div class="bg-gradient-to-r from-yellow-950/80 via-gray-950 to-yellow-950/80 px-3 py-2 flex justify-between items-center border-b border-yellow-500/30 text-[10px] font-mono font-bold text-yellow-500">
                                            <span>COMBATE POR EL TÍTULO</span>
                                            @if($match->status === 'completed')
                                                <span class="text-yellow-400"><i class="fas fa-crown mr-1"></i> FINALIZADO</span>
                                            @else
                                                <span class="animate-pulse">EN ESPERA</span>
                                            @endif
                                        </div>

                                        <!-- Finalista 1 -->
                                        <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p1Name)) }}')" 
                                             class="player-row flex items-center justify-between px-4 py-3.5 border-b border-gray-800 {{ $p1Won ? 'bg-yellow-950/40' : '' }}"
                                             data-player-name="{{ mb_strtoupper($p1Name) }}">
                                            <div class="flex items-center gap-2 truncate pr-2">
                                                @if($p1Won) <i class="fas fa-trophy text-yellow-400 text-sm shrink-0 animate-bounce"></i> @endif
                                                <span class="text-sm font-black truncate {{ $p1Won ? 'text-yellow-400' : 'text-white' }}">{{ mb_strtoupper($p1Name) }}</span>
                                            </div>
                                            <span class="text-base font-black font-mono px-2.5 py-0.5 rounded {{ $p1Won ? 'bg-yellow-500 text-black shadow-[0_0_12px_rgba(234,179,8,0.8)]' : 'text-gray-400 bg-gray-950' }}">
                                                {{ $match->player1_score ?? 0 }}
                                            </span>
                                        </div>

                                        <!-- Finalista 2 -->
                                        <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p2Name)) }}')" 
                                             class="player-row flex items-center justify-between px-4 py-3.5 {{ $p2Won ? 'bg-yellow-950/40' : '' }}"
                                             data-player-name="{{ mb_strtoupper($p2Name) }}">
                                            <div class="flex items-center gap-2 truncate pr-2">
                                                @if($p2Won) <i class="fas fa-trophy text-yellow-400 text-sm shrink-0 animate-bounce"></i> @endif
                                                <span class="text-sm font-black truncate {{ $p2Won ? 'text-yellow-400' : 'text-white' }}">{{ mb_strtoupper($p2Name) }}</span>
                                            </div>
                                            <span class="text-base font-black font-mono px-2.5 py-0.5 rounded {{ $p2Won ? 'bg-yellow-500 text-black shadow-[0_0_12px_rgba(234,179,8,0.8)]' : 'text-gray-400 bg-gray-950' }}">
                                                {{ $match->player2_score ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- 3. BLOQUE INFERIOR: LOSERS BRACKET -->
                @if($losersBracket->count() > 0)
                <div class="flex flex-col relative z-10 pt-8 border-t border-gray-800/80">
                    <div class="flex items-center gap-3 mb-8 pl-3 border-l-4 border-orange-500">
                        <span class="text-orange-400 font-black tracking-widest font-mono text-lg uppercase drop-shadow-[0_0_8px_rgba(249,115,22,0.5)]">
                            <i class="fas fa-fire mr-1"></i> LOSERS BRACKET (REPESCA)
                        </span>
                    </div>
                    
                    <div class="flex items-stretch gap-20">
                        @foreach($losersBracket as $roundNumber => $matches)
                            <div class="bracket-round-col flex flex-col w-[270px] shrink-0" data-bracket="losers" data-round="{{ $roundNumber }}">
                                <div class="text-center text-orange-400 bg-orange-950/30 border border-orange-500/30 rounded-lg py-2 text-[11px] font-black tracking-widest mb-6 uppercase shadow-inner">
                                    RONDA LOSERS {{ $roundNumber }}
                                </div>
                                <div class="flex flex-col justify-around flex-1 gap-6">
                                    @foreach($matches as $index => $match)
                                        @php
                                            $isTeam = !is_null($match->team1_id) && !is_null($match->team2_id);
                                            $p1Id = $isTeam ? $match->team1_id : $match->player1_id;
                                            $p2Id = $isTeam ? $match->team2_id : $match->player2_id;
                                            $p1Name = $isTeam ? ($match->team1->name ?? 'ESPERANDO...') : ($match->player1->name ?? 'ESPERANDO...');
                                            $p2Name = $isTeam ? ($match->team2->name ?? 'ESPERANDO...') : ($match->player2->name ?? 'ESPERANDO...');
                                            $p1Won = ($match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
                                            $p2Won = ($match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
                                        @endphp
                                        <div class="match-node relative bg-gray-900/95 border-2 {{ $match->status === 'playing' ? 'border-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.5)]' : ($match->status === 'completed' ? 'border-gray-700' : 'border-orange-900/60') }} rounded-xl overflow-hidden shadow-2xl transition-all"
                                             id="match-{{ $match->id }}"
                                             data-match-id="{{ $match->id }}"
                                             data-bracket="losers"
                                             data-round="{{ $roundNumber }}"
                                             data-index="{{ $index }}"
                                             data-status="{{ $match->status }}"
                                             data-winner="{{ $match->winner_id }}"
                                             data-p1="{{ $p1Id }}"
                                             data-p2="{{ $p2Id }}">
                                            
                                            <div class="bg-gray-950 px-3 py-1.5 flex justify-between items-center border-b border-gray-800 text-[10px] font-mono font-bold text-gray-500">
                                                <span>PARTIDA #{{ $match->match_number ?? $match->id }}</span>
                                                @if($match->status === 'playing')
                                                    <span class="text-cyan-400 animate-pulse"><i class="fas fa-circle text-[7px] mr-1"></i> EN JUEGO</span>
                                                @elseif($match->status === 'completed')
                                                    <span class="text-green-500"><i class="fas fa-check mr-1"></i> FINALIZADO</span>
                                                @else
                                                    <span>PENDIENTE</span>
                                                @endif
                                            </div>

                                            <!-- Jugador 1 -->
                                            <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p1Name)) }}')" 
                                                 class="player-row flex items-center justify-between px-3.5 py-2.5 border-b border-gray-800/80 {{ $p1Won ? 'bg-orange-950/30' : '' }}"
                                                 data-player-name="{{ mb_strtoupper($p1Name) }}">
                                                <div class="flex items-center gap-2 truncate pr-2">
                                                    @if($p1Won) <i class="fas fa-caret-right text-orange-400 text-sm shrink-0"></i> @endif
                                                    <span class="text-xs font-bold truncate {{ $p1Won ? 'text-white' : (is_null($p1Id) ? 'text-gray-600 italic' : 'text-gray-400') }}">
                                                        {{ mb_strtoupper($p1Name) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p1Won ? 'bg-orange-500 text-white shadow-[0_0_10px_rgba(249,115,22,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                    {{ $match->player1_score ?? 0 }}
                                                </span>
                                            </div>

                                            <!-- Jugador 2 -->
                                            <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p2Name)) }}')" 
                                                 class="player-row flex items-center justify-between px-3.5 py-2.5 {{ $p2Won ? 'bg-orange-950/30' : '' }}"
                                                 data-player-name="{{ mb_strtoupper($p2Name) }}">
                                                <div class="flex items-center gap-2 truncate pr-2">
                                                    @if($p2Won) <i class="fas fa-caret-right text-orange-400 text-sm shrink-0"></i> @endif
                                                    <span class="text-xs font-bold truncate {{ $p2Won ? 'text-white' : (is_null($p2Id) ? 'text-gray-600 italic' : 'text-gray-400') }}">
                                                        {{ mb_strtoupper($p2Name) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p2Won ? 'bg-orange-500 text-white shadow-[0_0_10px_rgba(249,115,22,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                    {{ $match->player2_score ?? 0 }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ZONA 2: TABLA DE CLASIFICACIÓN             -->
    <!-- ========================================== -->
    <div id="view-tabla" class="hidden flex-1 overflow-y-auto p-4 sm:p-8 bg-[#030712]">
        <div class="max-w-4xl mx-auto w-full pb-12">
            <div id="tabla-content" class="bg-gray-900/90 border border-gray-800 rounded-xl shadow-2xl overflow-hidden backdrop-blur-sm">
                <div class="text-center p-12">
                    <i class="fas fa-circle-notch fa-spin text-cyan-500 text-4xl mb-4"></i>
                    <p class="text-gray-500 font-bold tracking-widest text-xs">SINTONIZANDO CLASIFICACIÓN...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MOTOR GRÁFICO Y LÓGICA JAVASCRIPT          -->
    <!-- ========================================== -->
    <script>
        let panzoomInstance = null;
        let tablaYaCargada = false;
        let jugadorSeleccionado = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarLienzoInteractivo();
            // Dibujamos las flechas tras asegurar que el DOM está renderizado
            setTimeout(dibujarConexionesArbol, 150);
            window.addEventListener('resize', dibujarConexionesArbol);
        });

        // 1. INICIALIZAR PAN & ZOOM
        function iniciarLienzoInteractivo() {
            const elem = document.getElementById('panzoom-scene');
            const viewCuadro = document.getElementById('view-cuadro');
            
            panzoomInstance = Panzoom(elem, {
                maxScale: 2.2,
                minScale: 0.25,
                step: 0.15,
                canvas: true,
                startScale: window.innerWidth < 640 ? 0.65 : 0.9,
                startX: 0,
                startY: 0
            });

            // Permitir zoom con la rueda del ratón en PC
            viewCuadro.addEventListener('wheel', panzoomInstance.zoomWithWheel);
        }

        function zoomIn() { if(panzoomInstance) panzoomInstance.zoomIn(); }
        function zoomOut() { if(panzoomInstance) panzoomInstance.zoomOut(); }
        function resetZoom() { 
            if(panzoomInstance) {
                panzoomInstance.reset();
                setTimeout(dibujarConexionesArbol, 300);
            }
        }

        // 2. MOTOR INTELIGENTE DE CONEXIONES Y FLECHAS (SVG)
        function dibujarConexionesArbol() {
            const svg = document.getElementById('bracket-svg-lines');
            const container = document.getElementById('bracket-tree-container');
            if (!svg || !container) return;

            // Limpiamos líneas previas conservando los <defs> de las puntas de flecha
            const defs = svg.querySelector('defs').outerHTML;
            svg.innerHTML = defs;

            // Conectamos Winners Bracket
            conectarSeccionBracket('winners', '#d946ef', 'arrow-win-winners');
            // Conectamos Losers Bracket
            conectarSeccionBracket('losers', '#f97316', 'arrow-win-losers');
            // Conectamos hacia la Gran Final
            conectarGranFinal();
        }

        function conectarSeccionBracket(bracketType, activeColor, markerId) {
            const cols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="${bracketType}"]`));
            
            for (let c = 0; c < cols.length - 1; c++) {
                const currentMatches = Array.from(cols[c].querySelectorAll('.match-node'));
                const nextMatches = Array.from(cols[c + 1].querySelectorAll('.match-node'));

                currentMatches.forEach((matchEl, idx) => {
                    const winnerId = matchEl.getAttribute('data-winner');
                    let targetMatch = null;

                    // Estrategia A: Si ya avanzó el ganador, buscamos exactamente en qué tarjeta de la siguiente ronda está su ID
                    if (winnerId) {
                        targetMatch = nextMatches.find(m => m.getAttribute('data-p1') === winnerId || m.getAttribute('data-p2') === winnerId);
                    }

                    // Estrategia B: Si aún no se ha jugado, deducimos la conexión por geometría del árbol
                    if (!targetMatch && nextMatches.length > 0) {
                        if (currentMatches.length === nextMatches.length) {
                            // Ronda de consolidación (ej. en Losers donde caen de Winners) -> 1 a 1
                            targetMatch = nextMatches[idx];
                        } else {
                            // Reducción estándar de árbol (2 partidas alimentan a 1)
                            targetMatch = nextMatches[Math.floor(idx / 2)] || nextMatches[nextMatches.length - 1];
                        }
                    }

                    if (targetMatch) {
                        const isCompleted = matchEl.getAttribute('data-status') === 'completed' && winnerId;
                        trazarCurvaSVG(matchEl, targetMatch, isCompleted ? activeColor : '#374151', isCompleted ? markerId : 'arrow-pending', isCompleted);
                    }
                });
            }
        }

        function conectarGranFinal() {
            const finalsCols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="finals"]`));
            if (finalsCols.length === 0) return;

            const grandFinalCard = finalsCols[0].querySelector('.match-node');
            if (!grandFinalCard) return;

            // Conectar Campeón de Winners -> Gran Final
            const winnersCols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="winners"]`));
            if (winnersCols.length > 0) {
                const lastWinnerMatch = winnersCols[winnersCols.length - 1].querySelector('.match-node');
                if (lastWinnerMatch) {
                    const isComp = lastWinnerMatch.getAttribute('data-status') === 'completed';
                    trazarCurvaSVG(lastWinnerMatch, grandFinalCard, isComp ? '#eab308' : '#374151', isComp ? 'arrow-win-finals' : 'arrow-pending', isComp);
                }
            }

            // Conectar Campeón de Losers -> Gran Final
            const losersCols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="losers"]`));
            if (losersCols.length > 0) {
                const lastLoserMatch = losersCols[losersCols.length - 1].querySelector('.match-node');
                if (lastLoserMatch) {
                    const isComp = lastLoserMatch.getAttribute('data-status') === 'completed';
                    trazarCurvaSVG(lastLoserMatch, grandFinalCard, isComp ? '#eab308' : '#374151', isComp ? 'arrow-win-finals' : 'arrow-pending', isComp);
                }
            }

            // Si hay Bracket Reset (Gran Final 1 -> Gran Final 2)
            if (finalsCols.length > 1) {
                const resetCard = finalsCols[1].querySelector('.match-node');
                if (resetCard) {
                    trazarCurvaSVG(grandFinalCard, resetCard, '#eab308', 'arrow-win-finals', true);
                }
            }
        }

        function trazarCurvaSVG(sourceEl, targetEl, color, markerId, isAnimated) {
            const svg = document.getElementById('bracket-svg-lines');
            
            // Calculamos coordenadas locales dentro del árbol (independientes del nivel de Zoom)
            const startX = getRelativeOffsetLeft(sourceEl) + sourceEl.offsetWidth;
            const startY = getRelativeOffsetTop(sourceEl) + (sourceEl.offsetHeight / 2);
            
            const endX = getRelativeOffsetLeft(targetEl) - 4; // -4px para que la punta de la flecha encaje limpia
            const endY = getRelativeOffsetTop(targetEl) + (targetEl.offsetHeight / 2);

            const deltaX = (endX - startX) * 0.55;
            const pathData = `M ${startX} ${startY} C ${startX + deltaX} ${startY}, ${endX - deltaX} ${endY}, ${endX} ${endY}`;

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathData);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', color);
            path.setAttribute('stroke-width', isAnimated ? '3' : '2');
            if (!isAnimated) path.setAttribute('stroke-dasharray', '6,6');
            path.setAttribute('marker-end', `url(#${markerId})`);
            if (isAnimated) path.classList.add('line-active');

            svg.appendChild(path);
        }

        function getRelativeOffsetLeft(elem) {
            let left = 0;
            const container = document.getElementById('bracket-tree-container');
            while (elem && elem !== container) {
                left += elem.offsetLeft;
                elem = elem.offsetParent;
            }
            return left;
        }

        function getRelativeOffsetTop(elem) {
            let top = 0;
            const container = document.getElementById('bracket-tree-container');
            while (elem && elem !== container) {
                top += elem.offsetTop;
                elem = elem.offsetParent;
            }
            return top;
        }

        // 3. RASTREADOR Y BUSCADOR DE BLADERS EN EL ÁRBOL
        function resaltarJugador(nombre) {
            if (!nombre || nombre.includes('ESPERANDO') || nombre.includes('BYE')) return;
            
            const searchInput = document.getElementById('player-search');
            if (jugadorSeleccionado === nombre) {
                limpiarBusqueda();
                return;
            }
            
            jugadorSeleccionado = nombre;
            searchInput.value = nombre;
            filtrarJugador(nombre);
        }

        function filtrarJugador(query) {
            const cleanQuery = query.trim().toUpperCase();
            const clearBtn = document.getElementById('clear-search');
            const allCards = document.querySelectorAll('.match-node');
            const allRows = document.querySelectorAll('.player-row');

            if (cleanQuery.length === 0) {
                clearBtn.classList.add('hidden');
                jugadorSeleccionado = null;
                allCards.forEach(c => c.classList.remove('card-dimmed', 'card-focused'));
                allRows.forEach(r => r.classList.remove('player-highlight'));
                return;
            }

            clearBtn.classList.remove('hidden');

            allCards.forEach(card => {
                const rows = card.querySelectorAll('.player-row');
                let matchFoundInCard = false;

                rows.forEach(row => {
                    const pName = row.getAttribute('data-player-name') || '';
                    if (pName.includes(cleanQuery) && !pName.includes('ESPERANDO')) {
                        row.classList.add('player-highlight');
                        matchFoundInCard = true;
                    } else {
                        row.classList.remove('player-highlight');
                    }
                });

                if (matchFoundInCard) {
                    card.classList.remove('card-dimmed');
                    card.classList.add('card-focused');
                } else {
                    card.classList.remove('card-focused');
                    card.classList.add('card-dimmed');
                }
            });
        }

        function limpiarBusqueda() {
            const searchInput = document.getElementById('player-search');
            searchInput.value = '';
            filtrarJugador('');
        }

        // 4. CAMBIO DE PESTAÑAS (CUADRO / CLASIFICACIÓN)
        function switchPublicView(view) {
            const viewCuadro = document.getElementById('view-cuadro');
            const viewTabla = document.getElementById('view-tabla');
            const searchContainer = document.getElementById('search-container');
            
            viewCuadro.style.display = view === 'cuadro' ? 'block' : 'none';
            viewTabla.style.display = view === 'tabla' ? 'block' : 'none';
            searchContainer.style.display = view === 'cuadro' ? 'block' : 'none';
            
            const btnCuadro = document.getElementById('btn-cuadro');
            const btnTabla = document.getElementById('btn-tabla');
            
            if(view === 'cuadro') {
                btnCuadro.className = "flex-1 bg-gray-800 text-cyan-400 border border-cyan-500/40 py-2.5 rounded-lg font-bold tracking-widest transition-all shadow-[0_0_15px_rgba(6,182,212,0.15)] text-xs flex items-center justify-center gap-2";
                btnTabla.className = "flex-1 bg-gray-950/60 text-gray-400 border border-gray-800 hover:border-gray-700 py-2.5 rounded-lg font-bold tracking-widest transition-all text-xs flex items-center justify-center gap-2";
                setTimeout(dibujarConexionesArbol, 50);
            } else {
                btnTabla.className = "flex-1 bg-gray-800 text-cyan-400 border border-cyan-500/40 py-2.5 rounded-lg font-bold tracking-widest transition-all shadow-[0_0_15px_rgba(6,182,212,0.15)] text-xs flex items-center justify-center gap-2";
                btnCuadro.className = "flex-1 bg-gray-950/60 text-gray-400 border border-gray-800 hover:border-gray-700 py-2.5 rounded-lg font-bold tracking-widest transition-all text-xs flex items-center justify-center gap-2";
            }

            if (view === 'tabla' && !tablaYaCargada) {
                cargarTablaPublica({{ $event->id }});
            }
        }

        // 5. CARGA DE LA CLASIFICACIÓN GENERAL
        async function cargarTablaPublica(eventId) {
            const container = document.getElementById('tabla-content');
            try {
                const res = await fetch(`/api/torneos/${eventId}/clasificacion`);
                const data = await res.json();
                
                const isEliminatoria = data.is_bracket;
                const thPts = isEliminatoria ? 'RESULTADO' : 'PTS';
                
                let htmlTemp = `
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-300">
                            <thead class="bg-gray-950/90 text-[10px] sm:text-xs uppercase text-gray-500 font-bold tracking-widest border-b border-gray-800">
                                <tr>
                                    <th class="px-4 py-4 w-12 text-center">#</th>
                                    <th class="px-3 py-4">BLADER</th>
                                    <th class="px-3 py-4 text-center">${thPts}</th>
                                    ${!isEliminatoria ? '<th class="px-4 py-4 text-center">BCH</th>' : ''}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/80">
                `;
                
                const jugadores = data.clasificacion || data.standings || [];
                
                if (jugadores.length > 0) {
                    let currentRank = 1;
                    let prevPlayer = null;

                    jugadores.forEach((player, index) => {
                        const actualPosition = index + 1;
                        
                        if (prevPlayer) {
                            let isTie = false;
                            if (isEliminatoria && player.bracket_score > 0 && prevPlayer.bracket_score > 0) {
                                isTie = (player.bracket_score === prevPlayer.bracket_score) && (player.puesto === prevPlayer.puesto);
                            } else {
                                isTie = (player.bracket_score === prevPlayer.bracket_score) && (player.points === prevPlayer.points) && (player.buchholz === prevPlayer.buchholz) && (player.puesto === prevPlayer.puesto);
                            }
                            if (!isTie) currentRank = actualPosition;
                        } else {
                            currentRank = 1;
                        }
                        prevPlayer = player;

                        const isTop = currentRank <= 4 ? 'bg-cyan-900/10' : ''; 
                        let rankColor = currentRank === 1 ? 'text-yellow-400' : (currentRank <= 4 ? 'text-cyan-400' : 'text-gray-500');
                        
                        let ptsColumn = `<td class="px-3 py-4 text-center font-black font-mono text-yellow-400 text-base">${player.points}</td>`;
                        let bchColumn = `<td class="px-4 py-4 text-center font-mono text-gray-400 text-xs">${player.buchholz}</td>`;
                        
                        if (isEliminatoria) {
                            bchColumn = '';
                            if (currentRank === 1 && player.bracket_score > 8000) {
                                ptsColumn = `<td class="px-3 py-4 text-center"><span class="font-bold text-[10px] text-yellow-400 bg-yellow-900/30 border border-yellow-700/50 px-3 py-1.5 rounded-full uppercase tracking-widest whitespace-nowrap"><i class="fas fa-trophy mr-1"></i> CAMPEÓN</span></td>`;
                            } else if (currentRank === 2 && player.bracket_score > 8000) {
                                ptsColumn = `<td class="px-3 py-4 text-center"><span class="font-bold text-[10px] text-gray-300 bg-gray-700/50 border border-gray-600 px-3 py-1.5 rounded-full uppercase tracking-widest whitespace-nowrap">FINALISTA</span></td>`;
                            } else if (currentRank === 3 && player.bracket_score > 0) {
                                ptsColumn = `<td class="px-3 py-4 text-center"><span class="font-bold text-[10px] text-orange-400 bg-orange-900/30 border border-orange-800/50 px-3 py-1.5 rounded-full uppercase tracking-widest whitespace-nowrap">TOP 3</span></td>`;
                            } else if (currentRank === 4 && player.bracket_score > 0) {
                                ptsColumn = `<td class="px-3 py-4 text-center"><span class="font-bold text-[10px] text-cyan-400 bg-cyan-900/30 border border-cyan-800/50 px-3 py-1.5 rounded-full uppercase tracking-widest whitespace-nowrap">TOP 4</span></td>`;
                            } else if (player.bracket_score > 0) {
                                ptsColumn = `<td class="px-3 py-4 text-center"><span class="font-bold text-[10px] text-fuchsia-400 uppercase tracking-widest">TOP ${currentRank}</span></td>`;
                            } else {
                                ptsColumn = `<td class="px-3 py-4 text-center"><span class="font-mono text-gray-600 text-[10px] tracking-widest">SUÍZO</span></td>`; 
                            }
                        }
                        
                        htmlTemp += `
                            <tr class="${isTop} hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-4 text-center font-mono font-black text-lg ${rankColor}">${currentRank}</td>
                                <td class="px-3 py-4 font-black text-white uppercase tracking-widest">${player.name || player.user_name}</td>
                                ${ptsColumn}
                                ${bchColumn}
                            </tr>
                        `;
                    });
                } else {
                    htmlTemp += `<tr><td colspan="4" class="px-4 py-12 text-center text-gray-500 font-bold tracking-widest text-xs">NO HAY RESULTADOS AÚN</td></tr>`;
                }
                
                htmlTemp += `</tbody></table></div>`;
                container.innerHTML = htmlTemp;
                tablaYaCargada = true; 
                
            } catch (error) {
                container.innerHTML = '<p class="text-red-500 text-center font-bold p-10">Error al conectar con la Arena.</p>';
            }
        }
    </script>
</body>
</html>