<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>En Directo - {{ $event->name }}</title>

    <!-- Tailwind CSS & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Librería Panzoom -->
    <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

    <!-- Google Analytics 4 (Seguimiento del Cuadro en Vivo) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-KSK91ZXWE3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-KSK91ZXWE3', {
            'anonymize_ip': true,
            'page_title': 'SBBL HUB - Cuadro en Vivo',
            'page_path': '/hub/cuadro/{{ $event->id }}'
        });
    </script>

    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #030712;
            overflow: hidden;
            user-select: none;
        }
        .bg-grid-pattern {
            background-color: #030712;
            background-image:
                linear-gradient(to right, rgba(31, 41, 55, 0.25) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(31, 41, 55, 0.25) 1px, transparent 1px);
            background-size: 40px 40px;
        }
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
        @keyframes pulseGlow {
            0%, 100% { filter: drop-shadow(0 0 4px currentColor); }
            50% { filter: drop-shadow(0 0 10px currentColor); }
        }
        .line-active { animation: pulseGlow 2.5s infinite; }
    </style>
</head>
<body class="text-white h-screen flex flex-col">

    @php
        $hasBracketTree = ($winnersBracket->count() > 0 || $losersBracket->count() > 0 || $finals->count() > 0);
        $hasSwissRounds = isset($swissRounds) && $swissRounds->isNotEmpty();
        $initialView = $hasBracketTree ? 'cuadro' : ($hasSwissRounds ? 'suizo' : 'tabla');
    @endphp

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

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <div id="search-container" class="relative flex-1 sm:w-56">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-500 text-xs"></i>
                    <input type="text" id="player-search" oninput="filtrarJugador(this.value)" placeholder="Buscar Blader..."
                        class="w-full bg-gray-950 border border-gray-700 focus:border-cyan-400 text-white text-xs rounded-lg pl-8 pr-7 py-2 outline-none transition-all shadow-inner">
                    <button id="clear-search" onclick="limpiarBusqueda()" class="hidden absolute right-2.5 top-2 text-gray-500 hover:text-white text-xs">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>

                <button id="btn-live-sync" onclick="sincronizarEnVivo(true)" class="bg-gray-800 hover:bg-gray-700 active:scale-95 text-white border border-gray-600 px-3.5 py-2 rounded-lg font-bold flex items-center gap-2 text-xs transition-all shadow-md shrink-0">
                    <i id="icon-live-sync" class="fas fa-sync-alt text-cyan-400"></i>
                    <span class="hidden sm:inline" id="text-live-sync">EN VIVO (AUTO)</span>
                </button>
            </div>
        </div>

        <!-- BARRA DE NAVEGACIÓN DE 3 PESTAÑAS -->
        <div class="flex gap-2 mt-3 max-w-7xl mx-auto w-full">
            <button id="btn-cuadro" onclick="switchPublicView('cuadro')" class="flex-1 py-2.5 px-2 rounded-lg font-bold tracking-widest transition-all text-[11px] sm:text-xs flex items-center justify-center gap-1.5 border">
                <i class="fas fa-project-diagram"></i> <span>ÁRBOL TOP CUT</span>
            </button>
            <button id="btn-suizo" onclick="switchPublicView('suizo')" class="flex-1 py-2.5 px-2 rounded-lg font-bold tracking-widest transition-all text-[11px] sm:text-xs flex items-center justify-center gap-1.5 border">
                <i class="fas fa-layer-group"></i> <span>RONDAS SUIZAS</span>
            </button>
            <button id="btn-tabla" onclick="switchPublicView('tabla')" class="flex-1 py-2.5 px-2 rounded-lg font-bold tracking-widest transition-all text-[11px] sm:text-xs flex items-center justify-center gap-1.5 border">
                <i class="fas fa-list-ol"></i> <span>CLASIFICACIÓN</span>
            </button>
        </div>
    </header>

    <!-- ZONA 1: LIENZO INTERACTIVO DEL CUADRO ELIMINATORIO -->
    <div id="view-cuadro" class="relative flex-1 w-full h-full overflow-hidden bg-grid-pattern cursor-grab active:cursor-grabbing">

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

        <div class="absolute bottom-5 left-5 z-40 hidden sm:flex items-center gap-4 bg-gray-900/90 px-4 py-2 rounded-xl border border-gray-800 text-[11px] text-gray-400 font-bold tracking-wider backdrop-blur-md pointer-events-none">
            <span><i class="fas fa-mouse-pointer text-cyan-400 mr-1"></i> Arrastra para mover</span>
            <span><i class="fas fa-search-plus text-fuchsia-400 mr-1"></i> Rueda/Pellizca para Zoom</span>
            <span><i class="fas fa-hand-pointer text-yellow-400 mr-1"></i> Toca un Blader para ver su ruta</span>
        </div>

        <div id="panzoom-scene" class="inline-block p-12 sm:p-20 min-w-max origin-top-left">
            <div id="bracket-tree-container" class="relative flex flex-col gap-20">

                @if(!$hasBracketTree)
                    <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-10 text-center max-w-md mx-auto shadow-2xl">
                        <i class="fas fa-sitemap text-5xl text-gray-700 mb-4"></i>
                        <h3 class="text-white font-black font-mono text-lg uppercase mb-2">FASE ELIMINATORIA AÚN NO INICIADA</h3>
                        <p class="text-gray-400 text-xs leading-relaxed mb-4">El torneo se encuentra actualmente disputando las Rondas Suizas. Cuando finalice el Suizo y se genere el corte al Top, el árbol eliminatorio aparecerá aquí en directo.</p>
                        <button type="button" onclick="switchPublicView('suizo')" class="bg-cyan-600 hover:bg-cyan-500 text-white font-black text-xs px-4 py-2.5 rounded-xl uppercase tracking-wider">
                            <i class="fas fa-layer-group mr-1.5"></i> VER RONDAS SUIZAS EN JUEGO
                        </button>
                    </div>
                @endif

                <!-- CAPA SVG PARA LAS LÍNEAS Y FLECHAS DE CONEXIÓN -->
                <svg id="bracket-svg-lines" class="absolute inset-0 pointer-events-none z-0" style="width: 100%; height: 100%; overflow: visible;">
                    <defs>
                        <marker id="arrow-win-winners" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 8 5 L 0 9 z" fill="#d946ef" />
                        </marker>
                        <marker id="arrow-win-losers" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 8 5 L 0 9 z" fill="#f97316" />
                        </marker>
                        <marker id="arrow-win-finals" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 8 5 L 0 9 z" fill="#eab308" />
                        </marker>
                        <marker id="arrow-pending" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                            <path d="M 0 2 L 7 5 L 0 8 z" fill="#4b5563" />
                        </marker>
                    </defs>
                </svg>

                <!-- BLOQUE SUPERIOR: WINNERS BRACKET + GRAN FINAL -->
                <div class="flex items-stretch gap-24 relative z-10">

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
                                    <div class="flex flex-col justify-around flex-1 gap-6">
                                        @foreach($matches as $index => $match)
                                            @php
                                                $isPlaceholder = isset($match->is_placeholder) && $match->is_placeholder;
                                                $isTeam = !is_null($match->team1_id ?? null) || !is_null($match->team2_id ?? null);
                                                $p1Id = $isTeam ? ($match->team1_id ?? null) : ($match->player1_id ?? null);
                                                $p2Id = $isTeam ? ($match->team2_id ?? null) : ($match->player2_id ?? null);

                                                $p1Ghost = !$isPlaceholder && is_null($p1Id) && ($match->player1_score ?? 0) == 99;
                                                $p2Ghost = !$isPlaceholder && is_null($p2Id) && ($match->player2_score ?? 0) == 99;

                                                $p1Lbl = $match->p1_placeholder ?? 'ESPERANDO...';
                                                $p2Lbl = $match->p2_placeholder ?? 'ESPERANDO...';
                                                $p1Name = $isTeam ? ($match->team1->name ?? $p1Lbl) : ($match->player1->name ?? $p1Lbl);
                                                $p2Name = $isTeam ? ($match->team2->name ?? $p2Lbl) : ($match->player2->name ?? $p2Lbl);

                                                $p1Won = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
                                                $p2Won = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
                                                $bothEmpty = is_null($p1Id) && is_null($p2Id);

                                                $cardStyle = ($isPlaceholder || $bothEmpty)
                                                    ? 'border-gray-800 border-dashed bg-gray-900/50 opacity-80'
                                                    : ($match->status === 'playing' ? 'border-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.5)] bg-gray-900/95' : ($match->status === 'completed' ? 'border-gray-700 bg-gray-900/95' : 'border-fuchsia-900/60 bg-gray-900/95'));
                                            @endphp
                                            <div class="match-node relative border-2 {{ $cardStyle }} rounded-xl overflow-hidden shadow-2xl transition-all"
                                                 id="match-{{ $match->id }}"
                                                 data-match-id="{{ $match->id }}"
                                                 data-next-win="{{ $match->next_win_match_id ?? '' }}"
                                                 data-bracket="winners"
                                                 data-round="{{ $roundNumber }}"
                                                 data-index="{{ $index }}"
                                                 data-status="{{ $match->status }}"
                                                 data-winner="{{ $match->winner_id ?? '' }}"
                                                 data-p1="{{ $p1Ghost ? '999999' : ($p1Id ?? '') }}"
                                                 data-p2="{{ $p2Ghost ? '999999' : ($p2Id ?? '') }}">

                                                <div class="bg-gray-950 px-3 py-1.5 flex justify-between items-center border-b border-gray-800 text-[10px] font-mono font-bold text-gray-500">
                                                    <span>PARTIDA #{{ $match->match_number ?? $match->id }}</span>
                                                    @if(!$isPlaceholder && $match->status === 'playing')
                                                        <span class="text-cyan-400 animate-pulse"><i class="fas fa-circle text-[7px] mr-1"></i> EN JUEGO</span>
                                                    @elseif(!$isPlaceholder && $match->status === 'completed')
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
                                                        @if($p1Won) <i class="fas fa-caret-right text-fuchsia-400 text-sm shrink-0"></i> @endif
                                                        <span class="text-xs font-bold truncate {{ $p1Won ? 'text-white' : (is_null($p1Id) ? 'text-fuchsia-300/60 italic' : 'text-gray-300') }}">
                                                            {{ mb_strtoupper($p1Name) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p1Won ? 'bg-fuchsia-500 text-white shadow-[0_0_10px_rgba(217,70,239,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                        {{ $bothEmpty ? '-' : (($match->player1_score ?? 0) == 99 ? '-' : ($match->player1_score ?? 0)) }}
                                                    </span>
                                                </div>

                                                <!-- Jugador 2 -->
                                                <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p2Name)) }}')"
                                                     class="player-row flex items-center justify-between px-3.5 py-2.5 {{ $p2Won ? 'bg-fuchsia-950/30' : '' }}"
                                                     data-player-name="{{ mb_strtoupper($p2Name) }}">
                                                    <div class="flex items-center gap-2 truncate pr-2">
                                                        @if($p2Won) <i class="fas fa-caret-right text-fuchsia-400 text-sm shrink-0"></i> @endif
                                                        <span class="text-xs font-bold truncate {{ $p2Won ? 'text-white' : (is_null($p2Id) ? 'text-fuchsia-300/60 italic' : 'text-gray-300') }}">
                                                            {{ mb_strtoupper($p2Name) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p2Won ? 'bg-fuchsia-500 text-white shadow-[0_0_10px_rgba(217,70,239,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                        {{ $bothEmpty ? '-' : (($match->player2_score ?? 0) == 99 ? '-' : ($match->player2_score ?? 0)) }}
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

                    <!-- GRAN FINAL -->
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
                                    $isPlaceholder = isset($match->is_placeholder) && $match->is_placeholder;
                                    $isTeam = !is_null($match->team1_id ?? null) || !is_null($match->team2_id ?? null);
                                    $p1Id = $isTeam ? ($match->team1_id ?? null) : ($match->player1_id ?? null);
                                    $p2Id = $isTeam ? ($match->team2_id ?? null) : ($match->player2_id ?? null);
                                    $p1Lbl = $match->p1_placeholder ?? 'CAMPEÓN WINNERS';
                                    $p2Lbl = $match->p2_placeholder ?? 'CAMPEÓN LOSERS';
                                    $p1Name = $isTeam ? ($match->team1->name ?? $p1Lbl) : ($match->player1->name ?? $p1Lbl);
                                    $p2Name = $isTeam ? ($match->team2->name ?? $p2Lbl) : ($match->player2->name ?? $p2Lbl);
                                    $p1Won = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
                                    $p2Won = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
                                    $bothEmpty = is_null($p1Id) && is_null($p2Id);
                                @endphp
                                <div class="bracket-round-col flex flex-col w-[300px] shrink-0 justify-center" data-bracket="finals" data-round="{{ $fIndex + 1 }}">
                                    <div class="text-center text-yellow-400 bg-yellow-950/40 border border-yellow-500/40 rounded-lg py-2 text-xs font-black tracking-widest mb-6 uppercase shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                                        <i class="fas fa-star mr-1"></i> {{ $match->bracket_type === 'grand_final_reset' ? 'GRAN FINAL (RESET)' : 'GRAN FINAL' }}
                                    </div>

                                    <div class="match-node relative bg-gray-900 border-2 border-yellow-500/80 rounded-xl overflow-hidden shadow-[0_0_30px_rgba(234,179,8,0.25)] transition-all"
                                         id="match-{{ $match->id }}"
                                         data-match-id="{{ $match->id }}"
                                         data-next-win="{{ $match->next_win_match_id ?? '' }}"
                                         data-bracket="finals"
                                         data-round="{{ $fIndex + 1 }}"
                                         data-index="0"
                                         data-status="{{ $match->status }}"
                                         data-winner="{{ $match->winner_id ?? '' }}"
                                         data-p1="{{ $p1Id ?? '' }}"
                                         data-p2="{{ $p2Id ?? '' }}">

                                        <div class="bg-gradient-to-r from-yellow-950/80 via-gray-950 to-yellow-950/80 px-3 py-2 flex justify-between items-center border-b border-yellow-500/30 text-[10px] font-mono font-bold text-yellow-500">
                                            <span>PARTIDA #{{ $match->match_number ?? $match->id }} · TÍTULO</span>
                                            @if(!$isPlaceholder && $match->status === 'completed')
                                                <span class="text-yellow-400"><i class="fas fa-crown mr-1"></i> FINALIZADO</span>
                                            @else
                                                <span class="animate-pulse">EN ESPERA</span>
                                            @endif
                                        </div>

                                        <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p1Name)) }}')"
                                             class="player-row flex items-center justify-between px-4 py-3.5 border-b border-gray-800 {{ $p1Won ? 'bg-yellow-950/40' : '' }}"
                                             data-player-name="{{ mb_strtoupper($p1Name) }}">
                                            <div class="flex items-center gap-2 truncate pr-2">
                                                @if($p1Won) <i class="fas fa-trophy text-yellow-400 text-sm shrink-0 animate-bounce"></i> @endif
                                                <span class="text-sm font-black truncate {{ $p1Won ? 'text-yellow-400' : (is_null($p1Id) ? 'text-yellow-200/60 italic' : 'text-white') }}">{{ mb_strtoupper($p1Name) }}</span>
                                            </div>
                                            <span class="text-base font-black font-mono px-2.5 py-0.5 rounded {{ $p1Won ? 'bg-yellow-500 text-black shadow-[0_0_12px_rgba(234,179,8,0.8)]' : 'text-gray-400 bg-gray-950' }}">
                                                {{ $bothEmpty ? '-' : ($match->player1_score ?? 0) }}
                                            </span>
                                        </div>

                                        <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p2Name)) }}')"
                                             class="player-row flex items-center justify-between px-4 py-3.5 {{ $p2Won ? 'bg-yellow-950/40' : '' }}"
                                             data-player-name="{{ mb_strtoupper($p2Name) }}">
                                            <div class="flex items-center gap-2 truncate pr-2">
                                                @if($p2Won) <i class="fas fa-trophy text-yellow-400 text-sm shrink-0 animate-bounce"></i> @endif
                                                <span class="text-sm font-black truncate {{ $p2Won ? 'text-yellow-400' : (is_null($p2Id) ? 'text-yellow-200/60 italic' : 'text-white') }}">{{ mb_strtoupper($p2Name) }}</span>
                                            </div>
                                            <span class="text-base font-black font-mono px-2.5 py-0.5 rounded {{ $p2Won ? 'bg-yellow-500 text-black shadow-[0_0_12px_rgba(234,179,8,0.8)]' : 'text-gray-400 bg-gray-950' }}">
                                                {{ $bothEmpty ? '-' : ($match->player2_score ?? 0) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- BLOQUE INFERIOR: LOSERS BRACKET -->
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
                                            $isPlaceholder = isset($match->is_placeholder) && $match->is_placeholder;
                                            $isTeam = !is_null($match->team1_id ?? null) || !is_null($match->team2_id ?? null);
                                            $p1Id = $isTeam ? ($match->team1_id ?? null) : ($match->player1_id ?? null);
                                            $p2Id = $isTeam ? ($match->team2_id ?? null) : ($match->player2_id ?? null);
                                            $p1Ghost = !$isPlaceholder && is_null($p1Id) && ($match->player1_score ?? 0) == 99;
                                            $p2Ghost = !$isPlaceholder && is_null($p2Id) && ($match->player2_score ?? 0) == 99;

                                            $p1Lbl = $match->p1_placeholder ?? 'ESPERANDO...';
                                            $p2Lbl = $match->p2_placeholder ?? 'ESPERANDO...';
                                            $p1Name = $isTeam ? ($match->team1->name ?? $p1Lbl) : ($match->player1->name ?? $p1Lbl);
                                            $p2Name = $isTeam ? ($match->team2->name ?? $p2Lbl) : ($match->player2->name ?? $p2Lbl);
                                            $p1Won = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p1Id);
                                            $p2Won = (!$isPlaceholder && $match->status === 'completed' && $match->winner_id && $match->winner_id == $p2Id);
                                            $bothEmpty = is_null($p1Id) && is_null($p2Id);

                                            $cardStyle = ($isPlaceholder || $bothEmpty)
                                                ? 'border-gray-800 border-dashed bg-gray-900/50 opacity-80'
                                                : ($match->status === 'playing' ? 'border-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.5)] bg-gray-900/95' : ($match->status === 'completed' ? 'border-gray-700 bg-gray-900/95' : 'border-orange-900/60 bg-gray-900/95'));
                                        @endphp
                                        <div class="match-node relative border-2 {{ $cardStyle }} rounded-xl overflow-hidden shadow-2xl transition-all"
                                             id="match-{{ $match->id }}"
                                             data-match-id="{{ $match->id }}"
                                             data-next-win="{{ $match->next_win_match_id ?? '' }}"
                                             data-bracket="losers"
                                             data-round="{{ $roundNumber }}"
                                             data-index="{{ $index }}"
                                             data-status="{{ $match->status }}"
                                             data-winner="{{ $match->winner_id ?? '' }}"
                                             data-p1="{{ $p1Ghost ? '999999' : ($p1Id ?? '') }}"
                                             data-p2="{{ $p2Ghost ? '999999' : ($p2Id ?? '') }}">

                                            <div class="bg-gray-950 px-3 py-1.5 flex justify-between items-center border-b border-gray-800 text-[10px] font-mono font-bold text-gray-500">
                                                <span>PARTIDA #{{ $match->match_number ?? $match->id }}</span>
                                                @if(!$isPlaceholder && $match->status === 'playing')
                                                    <span class="text-cyan-400 animate-pulse"><i class="fas fa-circle text-[7px] mr-1"></i> EN JUEGO</span>
                                                @elseif(!$isPlaceholder && $match->status === 'completed')
                                                    <span class="text-green-500"><i class="fas fa-check mr-1"></i> FINALIZADO</span>
                                                @else
                                                    <span>PENDIENTE</span>
                                                @endif
                                            </div>

                                            <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p1Name)) }}')"
                                                 class="player-row flex items-center justify-between px-3.5 py-2.5 border-b border-gray-800/80 {{ $p1Won ? 'bg-orange-950/30' : '' }}"
                                                 data-player-name="{{ mb_strtoupper($p1Name) }}">
                                                <div class="flex items-center gap-2 truncate pr-2">
                                                    @if($p1Won) <i class="fas fa-caret-right text-orange-400 text-sm shrink-0"></i> @endif
                                                    <span class="text-xs font-bold truncate {{ $p1Won ? 'text-white' : (is_null($p1Id) ? 'text-orange-300/60 italic' : 'text-gray-300') }}">
                                                        {{ mb_strtoupper($p1Name) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p1Won ? 'bg-orange-500 text-white shadow-[0_0_10px_rgba(249,115,22,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                    {{ $bothEmpty ? '-' : (($match->player1_score ?? 0) == 99 ? '-' : ($match->player1_score ?? 0)) }}
                                                </span>
                                            </div>

                                            <div onclick="resaltarJugador('{{ addslashes(mb_strtoupper($p2Name)) }}')"
                                                 class="player-row flex items-center justify-between px-3.5 py-2.5 {{ $p2Won ? 'bg-orange-950/30' : '' }}"
                                                 data-player-name="{{ mb_strtoupper($p2Name) }}">
                                                <div class="flex items-center gap-2 truncate pr-2">
                                                    @if($p2Won) <i class="fas fa-caret-right text-orange-400 text-sm shrink-0"></i> @endif
                                                    <span class="text-xs font-bold truncate {{ $p2Won ? 'text-white' : (is_null($p2Id) ? 'text-orange-300/60 italic' : 'text-gray-300') }}">
                                                        {{ mb_strtoupper($p2Name) }}
                                                    </span>
                                                </div>
                                                <span class="text-sm font-black font-mono px-2 py-0.5 rounded {{ $p2Won ? 'bg-orange-500 text-white shadow-[0_0_10px_rgba(249,115,22,0.5)]' : 'text-gray-500 bg-gray-950' }}">
                                                    {{ $bothEmpty ? '-' : (($match->player2_score ?? 0) == 99 ? '-' : ($match->player2_score ?? 0)) }}
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

    <!-- ZONA 2: RONDAS SUIZAS / LIGUILLA -->
    <div id="view-suizo" class="hidden flex-1 overflow-y-auto p-4 sm:p-8 bg-grid-pattern">
        <div id="swiss-rounds-container" class="max-w-5xl mx-auto w-full space-y-6 pb-12">
            @if(isset($swissRounds) && $swissRounds->isNotEmpty())
                @foreach($swissRounds->sortKeysDesc() as $roundNum => $matchesInRound)
                    @php
                        $realMatches = $matchesInRound->filter(function($m) {
                            return !is_null($m->team1_id) || (!is_null($m->player1_id) && !is_null($m->player2_id) && $m->player1_score != 99 && $m->player2_score != 99);
                        });
                        $completedCount = $realMatches->whereIn('status', ['completed', 'cancelled'])->count();
                        $totalRealCount = $realMatches->count();
                    @endphp
                    <div class="bg-gray-900/95 border border-gray-800 rounded-2xl p-4 sm:p-5 shadow-2xl">
                        <div class="flex justify-between items-center border-b border-gray-800 pb-3 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm sm:text-base font-mono font-black text-cyan-400 uppercase tracking-widest">
                                    <i class="fas fa-layer-group mr-1.5"></i> RONDA {{ $roundNum }}
                                </span>
                                @if($completedCount === $totalRealCount && $totalRealCount > 0)
                                    <span class="bg-emerald-950 text-emerald-400 border border-emerald-700/60 text-[9px] font-mono font-bold px-2 py-0.5 rounded uppercase">COMPLETADA</span>
                                @else
                                    <span class="bg-amber-950 text-amber-400 border border-amber-700/60 text-[9px] font-mono font-bold px-2 py-0.5 rounded uppercase animate-pulse">EN CURSO</span>
                                @endif
                            </div>
                            <span class="text-xs font-mono font-bold text-gray-400">
                                {{ $completedCount }} / {{ $totalRealCount }} MESAS
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($matchesInRound as $idx => $m)
                                @php
                                    $isBye = is_null($m->team1_id) && (is_null($m->player2_id) || $m->player1_score == 99 || $m->player2_score == 99);
                                    $p1Name = $m->team1 ? $m->team1->name : ($m->player1 ? $m->player1->name : '---');
                                    $p2Name = $isBye ? 'BYE (PASE DIRECTO)' : ($m->team2 ? $m->team2->name : ($m->player2 ? $m->player2->name : '---'));
                                    $w1 = $isBye || ($m->status === 'completed' && $m->winner_id && ($m->winner_id == $m->player1_id || $m->winner_id == $m->team1_id));
                                    $w2 = !$isBye && $m->status === 'completed' && $m->winner_id && ($m->winner_id == $m->player2_id || $m->winner_id == $m->team2_id);
                                @endphp
                                <div class="swiss-match-card bg-gray-950 border {{ $m->status === 'completed' || $isBye ? 'border-gray-800/90' : 'border-cyan-700/60 shadow-[0_0_12px_rgba(6,182,212,0.12)]' }} rounded-xl p-3 flex items-center justify-between transition-all"
                                     data-p1-name="{{ mb_strtoupper($p1Name) }}"
                                     data-p2-name="{{ mb_strtoupper($p2Name) }}">
                                    <div class="flex-1 truncate pr-2">
                                        <span class="text-xs font-bold uppercase {{ $w1 ? 'text-yellow-400 font-black' : 'text-white' }}">
                                            @if($w1) <i class="fas fa-caret-right text-yellow-400 mr-1"></i> @endif
                                            {{ $p1Name }}
                                        </span>
                                    </div>
                                    <div class="px-3 py-1 rounded-lg bg-gray-900 border border-gray-800 font-mono font-black text-sm shrink-0">
                                        @if($isBye)
                                            <span class="text-cyan-400 text-[10px] tracking-wider">BYE (+3 PTS)</span>
                                        @else
                                            <span class="{{ $w1 ? 'text-yellow-400' : 'text-gray-400' }}">{{ $m->player1_score }}</span>
                                            <span class="text-gray-600 mx-1">-</span>
                                            <span class="{{ $w2 ? 'text-yellow-400' : 'text-gray-400' }}">{{ $m->player2_score }}</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 truncate pl-2 text-right">
                                        <span class="text-xs font-bold uppercase {{ $isBye ? 'text-gray-500 italic text-[10px]' : ($w2 ? 'text-yellow-400 font-black' : 'text-white') }}">
                                            {{ $p2Name }}
                                            @if($w2) <i class="fas fa-caret-left text-yellow-400 ml-1"></i> @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="bg-gray-900/90 border border-gray-800 rounded-2xl p-10 text-center">
                    <i class="fas fa-hourglass-half text-4xl text-gray-600 mb-3"></i>
                    <p class="text-gray-400 font-mono text-xs font-bold uppercase tracking-widest">Aún no se han generado rondas suizas en este torneo.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- ZONA 3: TABLA DE CLASIFICACIÓN GENERAL CON DESEMPATES -->
    <div id="view-tabla" class="hidden flex-1 overflow-y-auto p-4 sm:p-8 bg-grid-pattern">
        <div class="max-w-4xl mx-auto w-full pb-12">
            <div id="tabla-content" class="bg-gray-900/95 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden backdrop-blur-sm">
                <div class="text-center p-12">
                    <i class="fas fa-circle-notch fa-spin text-cyan-500 text-4xl mb-4"></i>
                    <p class="text-gray-500 font-bold tracking-widest text-xs">SINTONIZANDO CLASIFICACIÓN...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let panzoomInstance = null;
        let tablaYaCargada = false;
        let jugadorSeleccionado = null;
        let currentPublicView = '{{ $initialView }}';

        document.addEventListener('DOMContentLoaded', () => {
            iniciarLienzoInteractivo();
            switchPublicView(currentPublicView);
            window.addEventListener('resize', dibujarConexionesArbol);
        });

        function iniciarLienzoInteractivo() {
            const elem = document.getElementById('panzoom-scene');
            const viewCuadro = document.getElementById('view-cuadro');
            if (!elem || !viewCuadro) return;

            panzoomInstance = Panzoom(elem, {
                maxScale: 2.2,
                minScale: 0.25,
                step: 0.15,
                canvas: true,
                startScale: window.innerWidth < 640 ? 0.65 : 0.9,
                startX: 0,
                startY: 0
            });

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

        function dibujarConexionesArbol() {
            const svg = document.getElementById('bracket-svg-lines');
            const container = document.getElementById('bracket-tree-container');
            if (!svg || !container) return;

            const defs = svg.querySelector('defs').outerHTML;
            svg.innerHTML = defs;

            conectarSeccionBracket('winners', '#d946ef', 'arrow-win-winners');
            conectarSeccionBracket('losers', '#f97316', 'arrow-win-losers');
            conectarGranFinal();
        }

        function conectarSeccionBracket(bracketType, activeColor, markerId) {
            const cols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="${bracketType}"]`));

            for (let c = 0; c < cols.length - 1; c++) {
                const currentMatches = Array.from(cols[c].querySelectorAll('.match-node'));
                const nextMatches = Array.from(cols[c + 1].querySelectorAll('.match-node'));
                if (nextMatches.length === 0) continue;

                currentMatches.forEach((matchEl) => {
                    const nextWinId = matchEl.getAttribute('data-next-win');
                    const winnerId = matchEl.getAttribute('data-winner');
                    let targetMatch = null;

                    if (nextWinId && nextWinId !== '') {
                        targetMatch = document.getElementById(`match-${nextWinId}`);
                    }
                    if (!targetMatch && winnerId && winnerId !== '') {
                        targetMatch = nextMatches.find(m => m.getAttribute('data-p1') === winnerId || m.getAttribute('data-p2') === winnerId);
                    }

                    if (targetMatch) {
                        const isCompleted = matchEl.getAttribute('data-status') === 'completed' && Boolean(winnerId);
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

            const winnersCols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="winners"]`));
            if (winnersCols.length > 0) {
                const lastWinnerMatch = winnersCols[winnersCols.length - 1].querySelector('.match-node');
                if (lastWinnerMatch) {
                    const isComp = lastWinnerMatch.getAttribute('data-status') === 'completed';
                    trazarCurvaSVG(lastWinnerMatch, grandFinalCard, isComp ? '#eab308' : '#374151', isComp ? 'arrow-win-finals' : 'arrow-pending', isComp);
                }
            }

            const losersCols = Array.from(document.querySelectorAll(`.bracket-round-col[data-bracket="losers"]`));
            if (losersCols.length > 0) {
                const lastLoserMatch = losersCols[losersCols.length - 1].querySelector('.match-node');
                if (lastLoserMatch) {
                    const isComp = lastLoserMatch.getAttribute('data-status') === 'completed';
                    trazarCurvaSVG(lastLoserMatch, grandFinalCard, isComp ? '#eab308' : '#374151', isComp ? 'arrow-win-finals' : 'arrow-pending', isComp);
                }
            }

            if (finalsCols.length > 1) {
                const resetCard = finalsCols[1].querySelector('.match-node');
                if (resetCard) {
                    trazarCurvaSVG(grandFinalCard, resetCard, '#eab308', 'arrow-win-finals', true);
                }
            }
        }

        function trazarCurvaSVG(sourceEl, targetEl, color, markerId, isAnimated) {
            const svg = document.getElementById('bracket-svg-lines');

            const startX = getRelativeOffsetLeft(sourceEl) + sourceEl.offsetWidth;
            const startY = getRelativeOffsetTop(sourceEl) + (sourceEl.offsetHeight / 2);

            const endX = getRelativeOffsetLeft(targetEl) - 4;
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

        function resaltarJugador(nombre) {
            if (!nombre || nombre.includes('ESPERANDO') || nombre.includes('BYE') || nombre.includes('PENDIENTE') || nombre.includes('GANADOR') || nombre.includes('PERDEDOR')) return;

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
            const swissCards = document.querySelectorAll('.swiss-match-card');

            if (cleanQuery.length === 0) {
                clearBtn.classList.add('hidden');
                jugadorSeleccionado = null;
                allCards.forEach(c => c.classList.remove('card-dimmed', 'card-focused'));
                allRows.forEach(r => r.classList.remove('player-highlight'));
                swissCards.forEach(sc => sc.classList.remove('card-dimmed', 'card-focused'));
                return;
            }

            clearBtn.classList.remove('hidden');

            // Filtrar en Árbol Eliminatorio
            allCards.forEach(card => {
                const rows = card.querySelectorAll('.player-row');
                let matchFoundInCard = false;

                rows.forEach(row => {
                    const pName = row.getAttribute('data-player-name') || '';
                    if (pName.includes(cleanQuery) && !pName.includes('ESPERANDO') && !pName.includes('PENDIENTE') && !pName.includes('GANADOR') && !pName.includes('PERDEDOR')) {
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

            // Filtrar también en las tarjetas de Rondas Suizas
            swissCards.forEach(sc => {
                const n1 = sc.getAttribute('data-p1-name') || '';
                const n2 = sc.getAttribute('data-p2-name') || '';
                if (n1.includes(cleanQuery) || n2.includes(cleanQuery)) {
                    sc.classList.remove('card-dimmed');
                    sc.classList.add('card-focused');
                } else {
                    sc.classList.remove('card-focused');
                    sc.classList.add('card-dimmed');
                }
            });
        }

        function limpiarBusqueda() {
            const searchInput = document.getElementById('player-search');
            searchInput.value = '';
            filtrarJugador('');
        }

        function switchPublicView(view) {
            currentPublicView = view;
            const viewCuadro = document.getElementById('view-cuadro');
            const viewSuizo = document.getElementById('view-suizo');
            const viewTabla = document.getElementById('view-tabla');
            const searchContainer = document.getElementById('search-container');

            viewCuadro.style.display = view === 'cuadro' ? 'block' : 'none';
            viewSuizo.style.display = view === 'suizo' ? 'block' : 'none';
            viewTabla.style.display = view === 'tabla' ? 'block' : 'none';
            searchContainer.style.display = (view === 'cuadro' || view === 'suizo') ? 'block' : 'none';

            const activeClass = "flex-1 bg-gray-800 text-cyan-400 border border-cyan-500/50 py-2.5 px-2 rounded-lg font-black tracking-widest transition-all shadow-[0_0_15px_rgba(6,182,212,0.18)] text-[11px] sm:text-xs flex items-center justify-center gap-1.5";
            const inactiveClass = "flex-1 bg-gray-950/60 text-gray-400 border border-gray-800 hover:border-gray-700 py-2.5 px-2 rounded-lg font-bold tracking-widest transition-all text-[11px] sm:text-xs flex items-center justify-center gap-1.5";

            document.getElementById('btn-cuadro').className = view === 'cuadro' ? activeClass : inactiveClass;
            document.getElementById('btn-suizo').className = view === 'suizo' ? activeClass : inactiveClass;
            document.getElementById('btn-tabla').className = view === 'tabla' ? activeClass : inactiveClass;

            if (view === 'cuadro') {
                setTimeout(dibujarConexionesArbol, 60);
            } else if (view === 'tabla') {
                cargarTablaPublica({{ $event->id }});
            }
        }

        async function cargarTablaPublica(eventId) {
            const container = document.getElementById('tabla-content');
            try {
                const res = await fetch(`/api/torneos/${eventId}/clasificacion`);
                const data = await res.json();

                const isEliminatoria = data.is_bracket;

                let htmlTemp = `
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm text-gray-300">
                            <thead class="bg-gray-950/90 text-[10px] sm:text-xs uppercase text-gray-400 font-bold tracking-wider border-b border-gray-800">
                                <tr>
                                    <th class="px-3 py-4 w-10 text-center">#</th>
                                    <th class="px-3 py-4">BLADER</th>
                                    <th class="px-2 py-4 text-center">V - D</th>
                                    <th class="px-2 py-4 text-center text-yellow-400">PTS</th>
                                    <th class="px-2 py-4 text-center">
                                        BCH
                                        <button type="button" onclick="mostrarInfoDesempate('BUCHHOLZ (BCH)', 'Suma los puntos totales conseguidos por todos los rivales a los que te has enfrentado. Desempata a favor de quien haya jugado contra oponentes más fuertes.')" class="text-cyan-400 hover:text-white ml-0.5"><i class="fas fa-question-circle"></i></button>
                                    </th>
                                    <th class="px-2 py-4 text-center">
                                        DIF
                                        <button type="button" onclick="mostrarInfoDesempate('DIFERENCIA DE PUNTOS (+/-)', 'Resta entre los puntos individuales de Beyblade que has anotado (Spin, Over, Burst, Xtreme) y los que has encajado.')" class="text-cyan-400 hover:text-white ml-0.5"><i class="fas fa-question-circle"></i></button>
                                    </th>
                                    ${isEliminatoria ? '<th class="px-3 py-4 text-center">FASE FINAL</th>' : ''}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/80">
                `;

                const jugadores = data.clasificacion || data.standings || [];

                if (jugadores.length > 0) {
                    jugadores.forEach((player, index) => {
                        const currentRank = index + 1;
                        const isTop = currentRank <= 4 ? 'bg-cyan-900/10' : '';
                        const rankColor = currentRank === 1 ? 'text-yellow-400' : (currentRank <= 4 ? 'text-cyan-400' : 'text-gray-500');

                        const wins = player.wins ?? 0;
                        const losses = player.losses ?? 0;
                        const byes = player.byes ?? 0;
                        const diff = player.point_diff ?? 0;
                        const diffStr = diff > 0 ? `+${diff}` : `${diff}`;
                        const diffColor = diff > 0 ? 'text-emerald-400' : (diff < 0 ? 'text-rose-400' : 'text-gray-500');

                        const h2hBadge = player.h2h_wins > 0
                            ? `<span class="ml-1.5 text-[9px] bg-cyan-950 text-cyan-300 border border-cyan-700/60 px-1.5 py-0.5 rounded font-mono" title="Gana desempate por Enfrentamiento Directo">H2H</span>`
                            : '';
                        const byeBadge = byes > 0
                            ? `<span class="ml-1 text-[9px] bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded font-mono">BYE</span>`
                            : '';

                        let faseCol = '';
                        if (isEliminatoria) {
                            if (currentRank === 1 && player.bracket_score > 8000) {
                                faseCol = `<td class="px-3 py-3.5 text-center"><span class="font-bold text-[10px] text-yellow-400 bg-yellow-900/30 border border-yellow-700/50 px-2.5 py-1 rounded-full uppercase tracking-widest whitespace-nowrap"><i class="fas fa-trophy mr-1"></i> CAMPEÓN</span></td>`;
                            } else if (currentRank === 2 && player.bracket_score > 8000) {
                                faseCol = `<td class="px-3 py-3.5 text-center"><span class="font-bold text-[10px] text-gray-300 bg-gray-700/50 border border-gray-600 px-2.5 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">FINALISTA</span></td>`;
                            } else if (currentRank === 3 && player.bracket_score > 0) {
                                faseCol = `<td class="px-3 py-3.5 text-center"><span class="font-bold text-[10px] text-orange-400 bg-orange-900/30 border border-orange-800/50 px-2.5 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">TOP 3</span></td>`;
                            } else if (currentRank === 4 && player.bracket_score > 0) {
                                faseCol = `<td class="px-3 py-3.5 text-center"><span class="font-bold text-[10px] text-cyan-400 bg-cyan-900/30 border border-cyan-800/50 px-2.5 py-1 rounded-full uppercase tracking-widest whitespace-nowrap">TOP 4</span></td>`;
                            } else if (player.bracket_score > 0) {
                                faseCol = `<td class="px-3 py-3.5 text-center"><span class="font-bold text-[10px] text-fuchsia-400 uppercase tracking-widest">TOP ${currentRank}</span></td>`;
                            } else {
                                faseCol = `<td class="px-3 py-3.5 text-center"><span class="font-mono text-gray-600 text-[10px] tracking-widest">SUIZO</span></td>`;
                            }
                        }

                        htmlTemp += `
                            <tr class="${isTop} hover:bg-gray-800/50 transition-colors">
                                <td class="px-3 py-3.5 text-center font-mono font-black text-base ${rankColor}">${currentRank}</td>
                                <td class="px-3 py-3.5 font-black text-white uppercase tracking-wider">
                                    ${player.name || player.user_name}${h2hBadge}${byeBadge}
                                </td>
                                <td class="px-2 py-3.5 text-center font-mono text-xs">
                                    <span class="text-emerald-400 font-bold">${wins}V</span> <span class="text-gray-600">-</span> <span class="text-rose-400 font-bold">${losses}D</span>
                                </td>
                                <td class="px-2 py-3.5 text-center font-black font-mono text-yellow-400 text-base">${player.points}</td>
                                <td class="px-2 py-3.5 text-center font-mono text-cyan-300 font-bold text-xs">${player.buchholz}</td>
                                <td class="px-2 py-3.5 text-center font-mono font-bold text-xs ${diffColor}" title="${player.pts_for || 0} a favor / ${player.pts_against || 0} en contra">${diffStr}</td>
                                ${faseCol}
                            </tr>
                        `;
                    });
                } else {
                    htmlTemp += `<tr><td colspan="7" class="px-4 py-12 text-center text-gray-500 font-bold tracking-widest text-xs">NO HAY RESULTADOS AÚN</td></tr>`;
                }

                htmlTemp += `
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-950 px-4 py-3 border-t border-gray-800 text-[10px] font-mono text-gray-400 flex flex-wrap justify-between gap-2">
                    <span><b class="text-white">ORDEN DE DESEMPATE SUIZO:</b></span>
                    <span><b class="text-yellow-400">1º PTS</b> (Puntos)</span>
                    <span><b class="text-cyan-300">2º BCH</b> (Buchholz)</span>
                    <span><b class="text-cyan-400">3º H2H</b> (Duelo Directo)</span>
                    <span><b class="text-emerald-400">4º DIF</b> (+/- Puntos Bey)</span>
                </div>`;

                container.innerHTML = htmlTemp;
                tablaYaCargada = true;

            } catch (error) {
                container.innerHTML = '<p class="text-red-500 text-center font-bold p-10">Error al conectar con la Arena.</p>';
            }
        }

        function mostrarInfoDesempate(titulo, desc) {
            const prev = document.getElementById('public-tooltip-modal');
            if (prev) prev.remove();
            const m = document.createElement('div');
            m.id = 'public-tooltip-modal';
            m.className = 'fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4';
            m.onclick = () => m.remove();
            m.innerHTML = `
                <div class="bg-gray-900 border border-cyan-500/60 rounded-2xl max-w-sm w-full p-4 shadow-2xl" onclick="event.stopPropagation()">
                    <div class="flex justify-between items-center border-b border-gray-800 pb-2 mb-2.5">
                        <span class="text-xs font-mono font-black text-cyan-400 uppercase">${titulo}</span>
                        <button type="button" onclick="document.getElementById('public-tooltip-modal').remove()" class="text-gray-500 hover:text-white"><i class="fas fa-times"></i></button>
                    </div>
                    <p class="text-xs text-gray-300 leading-relaxed">${desc}</p>
                </div>`;
            document.body.appendChild(m);
        }

        // =========================================================
        // MOTOR DE AUTO-REFRESCO EN VIVO (ÁRBOL + SUIZO + TABLA)
        // =========================================================
        let isSyncingBracket = false;

        async function sincronizarEnVivo(manual = false) {
            if (isSyncingBracket) return;
            isSyncingBracket = true;

            const icon = document.getElementById('icon-live-sync');
            const text = document.getElementById('text-live-sync');
            if (icon) icon.classList.add('fa-spin');
            if (manual && text) text.innerText = 'SINCRONIZANDO...';

            try {
                if (currentPublicView === 'tabla') {
                    await cargarTablaPublica({{ $event->id }});
                } else {
                    const res = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Cache-Control': 'no-cache' }
                    });

                    if (res.ok) {
                        const htmlText = await res.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(htmlText, 'text/html');

                        // Actualizar Árbol
                        const newTree = doc.getElementById('bracket-tree-container');
                        const currentTree = document.getElementById('bracket-tree-container');
                        if (newTree && currentTree) {
                            currentTree.innerHTML = newTree.innerHTML;
                            if (currentPublicView === 'cuadro') dibujarConexionesArbol();
                        }

                        // Actualizar Rondas Suizas
                        const newSwiss = doc.getElementById('swiss-rounds-container');
                        const currentSwiss = document.getElementById('swiss-rounds-container');
                        if (newSwiss && currentSwiss) {
                            currentSwiss.innerHTML = newSwiss.innerHTML;
                        }

                        const currentSearch = document.getElementById('player-search')?.value || '';
                        if (currentSearch.trim().length > 0) {
                            filtrarJugador(currentSearch);
                        }
                    }
                }
            } catch (e) {
                console.warn("Sincronización en vivo pospuesta:", e);
            } finally {
                if (icon) setTimeout(() => icon.classList.remove('fa-spin'), 400);
                if (text) text.innerText = 'EN VIVO (AUTO)';
                isSyncingBracket = false;
            }
        }

        setInterval(() => {
            if (!document.hidden) {
                sincronizarEnVivo(false);
            }
        }, 8000);
    </script>
</body>
</html>
