<!-- MODAL DE BATALLA (INTERFAZ TÁCTIL PRO DE JUEZ - COMPACTA SIN SCROLL) -->
<div id="batallaModal" class="fixed inset-0 bg-[#05070e] z-[95] hidden flex-col select-none">    
    
    <!-- CABECERA SUPERIOR BLINDADA -->
    <div class="bg-gray-900 border-b border-gray-800 px-3 py-2 flex justify-between items-center shadow-lg relative z-[100] shrink-0">
        <div class="flex items-center gap-2 pointer-events-none">
            <span class="bg-yellow-500/10 border border-yellow-500/40 text-yellow-400 px-2 py-0.5 rounded-md text-[11px] font-black font-mono">
                <i class="fas fa-gavel"></i> ARBITRAJE
            </span>
            <span id="batalla_match_badge" class="text-xs font-mono font-bold text-gray-300 tracking-wider">PARTIDA #</span>
        </div>
        
        <div class="flex items-center gap-1.5 relative z-[100]">
            <button type="button" id="top_btn_lados" onclick="alternarLadosBatalla()" class="min-h-[36px] bg-gray-800 hover:bg-gray-700 active:bg-cyan-900 text-cyan-400 border border-cyan-700/70 px-2.5 py-1.5 rounded-xl text-[10px] font-black tracking-widest uppercase flex items-center gap-1 shadow-md active:scale-95 transition-all cursor-pointer pointer-events-auto">
                <i class="fas fa-exchange-alt"></i> LADOS
            </button>
            <button type="button" id="top_btn_undo" onclick="deshacerUltimoRegistro()" class="min-h-[36px] min-w-[38px] text-orange-400 hover:text-white bg-orange-950/60 hover:bg-orange-600 border border-orange-700/70 px-2.5 py-1.5 rounded-xl text-xs font-black flex items-center justify-center transition-all active:scale-95 shadow-md cursor-pointer pointer-events-auto" title="Deshacer última acción">
                <i class="fas fa-undo"></i>
            </button>
            <button type="button" id="top_btn_close" onclick="cerrarBatalla()" class="min-h-[36px] min-w-[38px] text-gray-300 hover:text-white bg-gray-800 hover:bg-red-600 border border-gray-600 rounded-xl flex items-center justify-center text-base transition-all active:scale-95 shadow-md cursor-pointer pointer-events-auto" title="Cerrar ventana">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- ZONA DE JUGADORES, MARCADOR Y "NO PRESENTADO" -->
    <div id="batallaPlayersHeader" class="flex justify-between items-stretch px-3 py-2.5 gap-2 bg-gray-950 border-b border-gray-800/80 shadow-inner shrink-0 relative z-20">
        <!-- Jugador 1 -->
        <div class="flex-1 bg-gradient-to-b from-gray-900 to-gray-900/60 border-t-4 border-cyan-500 rounded-xl p-2 text-center flex flex-col justify-between relative overflow-hidden shadow-md">
            <div class="flex justify-between items-center w-full z-20">
                <button type="button" onclick="lanzarWarning('p1')" id="btn_warning_p1" class="text-gray-500 hover:text-yellow-400 bg-gray-950/90 border border-gray-800 rounded px-1.5 py-0.5 transition-all text-[10px] flex items-center gap-1 cursor-pointer" title="Asignar Warning / Falta">
                    <i class="fas fa-exclamation-triangle"></i> <span class="text-[8px] font-bold">WARN</span>
                </button>
            </div>
            
            <div class="my-0.5 relative z-10">
                <span id="batalla_p1_name" class="text-white font-black text-xs uppercase truncate block mb-0.5 px-1">JUGADOR 1</span>
                <span id="batalla_score_p1" class="text-3xl sm:text-4xl font-black font-mono text-cyan-400 leading-none block transition-transform duration-200">0</span>
                <input type="hidden" id="batalla_p1_id">
            </div>

            <button type="button" onclick="declararNoPresentado('p1')" class="mt-1.5 w-full bg-gray-950/70 hover:bg-red-950/60 text-gray-500 hover:text-red-300 border border-gray-800 hover:border-red-800/60 py-1 rounded-lg text-[8px] font-bold tracking-wider uppercase transition-colors relative z-20">
                <i class="fas fa-user-clock mr-0.5"></i> No presentado
            </button>
        </div>
        
        <div class="flex flex-col items-center justify-center px-0.5">
            <span class="text-gray-600 font-black italic font-mono text-xs">VS</span>
            <span class="text-[7px] text-gray-600 font-mono font-bold uppercase tracking-widest mt-0.5">MAX 4</span>
        </div>
        
        <!-- Jugador 2 -->
        <div class="flex-1 bg-gradient-to-b from-gray-900 to-gray-900/60 border-t-4 border-red-500 rounded-xl p-2 text-center flex flex-col justify-between relative overflow-hidden shadow-md">
            <div class="flex justify-end items-center w-full z-20">
                <button type="button" onclick="lanzarWarning('p2')" id="btn_warning_p2" class="text-gray-500 hover:text-yellow-400 bg-gray-950/90 border border-gray-800 rounded px-1.5 py-0.5 transition-all text-[10px] flex items-center gap-1 cursor-pointer" title="Asignar Warning / Falta">
                    <span class="text-[8px] font-bold">WARN</span> <i class="fas fa-exclamation-triangle"></i>
                </button>
            </div>
            
            <div class="my-0.5 relative z-10">
                <span id="batalla_p2_name" class="text-white font-black text-xs uppercase truncate block mb-0.5 px-1">JUGADOR 2</span>
                <span id="batalla_score_p2" class="text-3xl sm:text-4xl font-black font-mono text-red-400 leading-none block transition-transform duration-200">0</span>
                <input type="hidden" id="batalla_p2_id">
            </div>

            <button type="button" onclick="declararNoPresentado('p2')" class="mt-1.5 w-full bg-gray-950/70 hover:bg-red-950/60 text-gray-500 hover:text-red-300 border border-gray-800 hover:border-red-800/60 py-1 rounded-lg text-[8px] font-bold tracking-wider uppercase transition-colors relative z-20">
                <i class="fas fa-user-clock mr-0.5"></i> No presentado
            </button>
        </div>
    </div>

    <!-- Cinta de Historial de Lanzamientos (Bitácora en vivo) -->
    <div id="batalla_history_bar" class="bg-gray-900/90 border-b border-gray-800 px-3 py-1 flex items-center justify-between gap-2 overflow-x-auto no-scrollbar shrink-0 min-h-[30px] relative z-20">
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <span class="text-[9px] font-bold text-gray-500 uppercase tracking-widest shrink-0"><i class="fas fa-history mr-1"></i> LOG:</span>
            <div id="batalla_history_pills" class="flex items-center gap-1.5 text-[10px] font-mono">
                <span class="text-gray-600 italic text-[10px]">Esperando primer lanzamiento...</span>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal Compactado -->
    <div id="batallaScrollContainer" class="flex-1 overflow-y-auto px-3 py-2.5 space-y-3 scroll-smooth relative z-10">
        
        <!-- MODO MARCADOR MANUAL (PLEGABLE) -->
        <div class="bg-gray-900/80 border border-gray-800 rounded-xl overflow-hidden">
            <button type="button" onclick="toggleMarcadorManualBox()" class="w-full px-3 py-2 flex justify-between items-center text-left text-[9px] font-bold text-gray-400 hover:text-white tracking-widest uppercase transition-colors">
                <span><i class="fas fa-sliders-h text-cyan-400 mr-1.5"></i> ANOTAR MARCADOR MANUAL (SIN STATS DE BEYS)</span>
                <i id="icon_manual_score" class="fas fa-chevron-down text-xs transition-transform"></i>
            </button>
            
            <div id="manual_score_box" class="hidden p-3 pt-2 border-t border-gray-800/80 bg-gray-950/60">
                <div id="batallaManualScoreInputs" class="flex items-center justify-between gap-3 mb-2.5">
                    <div class="flex-1 text-center">
                        <label id="manual_lbl_p1" class="text-[9px] font-bold text-cyan-400 uppercase block truncate mb-1">JUGADOR 1</label>
                        <input type="number" id="manual_input_p1" min="0" max="4" value="4" class="w-full bg-gray-900 border border-cyan-500/50 text-white text-center font-mono font-black text-lg py-1.5 rounded-lg outline-none">
                    </div>
                    <span class="font-mono font-black text-gray-600 text-lg mt-4">-</span>
                    <div class="flex-1 text-center">
                        <label id="manual_lbl_p2" class="text-[9px] font-bold text-red-400 uppercase block truncate mb-1">JUGADOR 2</label>
                        <input type="number" id="manual_input_p2" min="0" max="4" value="0" class="w-full bg-gray-900 border border-red-500/50 text-white text-center font-mono font-black text-lg py-1.5 rounded-lg outline-none">
                    </div>
                </div>
                <button type="button" onclick="guardarMarcadorManual()" class="w-full bg-gray-800 hover:bg-cyan-700 text-cyan-300 hover:text-white border border-cyan-500/40 font-black py-2 rounded-lg text-[10px] tracking-widest uppercase transition-all shadow-md">
                    <i class="fas fa-save mr-1"></i> GUARDAR MARCADOR Y CERRAR
                </button>
            </div>
        </div>

        <!-- PASO 1: DECK CHECK -->
        <div>
            <div class="flex justify-between items-center mb-1.5">
                <h4 class="text-[10px] text-cyan-400 font-black tracking-widest uppercase"><i class="fas fa-compact-disc mr-1"></i> 1. SELECCIONA BEYS EN LA ARENA</h4>
                <span id="batalla_cycle_badge" class="text-[9px] bg-gray-800 text-gray-400 px-2 py-0.5 rounded font-mono font-bold">LANZAMIENTO #1</span>
            </div>
            <div id="batallaDecksContainer" class="flex justify-between gap-2 transition-all duration-300">
                <div id="deck_p1_container" class="w-1/2 space-y-1.5"></div>
                <div id="deck_p2_container" class="w-1/2 space-y-1.5"></div>
            </div>
        </div>

        <!-- ZONA DE RESULTADOS (Se desbloquea al elegir ambos beys) -->
        <div id="zona_resultados" class="opacity-30 pointer-events-none transition-all duration-300 space-y-3 pt-2 border-t border-gray-800/80">
            
            <!-- PASO 2: GANADOR DEL CHOQUE -->
            <div id="paso_ganador_anchor">
                <h4 class="text-[10px] text-yellow-400 font-black tracking-widest uppercase mb-1.5"><i class="fas fa-trophy mr-1"></i> 2. ¿QUIÉN GANÓ EL LANZAMIENTO?</h4>
                <div id="batallaFinishButtonsContainer" class="flex gap-2">
                    <button type="button" id="btn_ganador_p1" onclick="seleccionarGanador('p1')" class="flex-1 bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-lg truncate">GANA P1</button>
                    <button type="button" id="btn_ganador_p2" onclick="seleccionarGanador('p2')" class="flex-1 bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-lg truncate">GANA P2</button>
                </div>
            </div>

            <!-- PASO 3: TIPO DE FINAL (ICONOS CENTRADOS EN TRANSPARENCIA) -->
            <div>
                <h4 class="text-[10px] text-fuchsia-400 font-black tracking-widest uppercase mb-1.5"><i class="fas fa-bolt mr-1"></i> 3. TIPO DE FINAL</h4>
                <div class="grid grid-cols-2 gap-2">
                    <!-- SPIN FINISH -->
                    <button type="button" id="btn_fin_spin" onclick="seleccionarFinal('spin')" class="relative overflow-hidden bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-3 rounded-xl text-xs tracking-wider uppercase transition-all active:scale-95 shadow-md flex items-center justify-between">
                        <i class="fas fa-sync text-5xl text-blue-400 opacity-15 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <span class="relative z-10 truncate pr-1">SPIN FINISH</span>
                        <span class="relative z-10 text-[9px] font-bold text-blue-300 bg-blue-950/80 border border-blue-700/50 px-1.5 py-0.5 rounded shrink-0">+1 PT</span>
                    </button>
                    
                    <!-- OVER FINISH -->
                    <button type="button" id="btn_fin_over" onclick="seleccionarFinal('over')" class="relative overflow-hidden bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-3 rounded-xl text-xs tracking-wider uppercase transition-all active:scale-95 shadow-md flex items-center justify-between">
                        <i class="fas fa-arrow-up text-5xl text-yellow-400 opacity-15 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <span class="relative z-10 truncate pr-1">OVER FINISH</span>
                        <span class="relative z-10 text-[9px] font-bold text-yellow-300 bg-yellow-950/80 border border-yellow-700/50 px-1.5 py-0.5 rounded shrink-0">+2 PTS</span>
                    </button>
                    
                    <!-- BURST FINISH -->
                    <button type="button" id="btn_fin_burst" onclick="seleccionarFinal('burst')" class="relative overflow-hidden bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-3 rounded-xl text-xs tracking-wider uppercase transition-all active:scale-95 shadow-md flex items-center justify-between">
                        <i class="fas fa-bomb text-5xl text-orange-400 opacity-15 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <span class="relative z-10 truncate pr-1">BURST FINISH</span>
                        <span class="relative z-10 text-[9px] font-bold text-orange-300 bg-orange-950/80 border border-orange-700/50 px-1.5 py-0.5 rounded shrink-0">+2 PTS</span>
                    </button>
                    
                    <!-- XTREME FINISH -->
                    <button type="button" id="btn_fin_xtreme" onclick="seleccionarFinal('xtreme')" class="relative overflow-hidden bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-3 rounded-xl text-xs tracking-wider uppercase transition-all active:scale-95 shadow-md flex items-center justify-between">
                        <i class="fas fa-bolt text-5xl text-fuchsia-400 opacity-15 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <span class="relative z-10 truncate pr-1">XTREME FINISH</span>
                        <span class="relative z-10 text-[9px] font-bold text-fuchsia-300 bg-fuchsia-950/80 border border-fuchsia-700/50 px-1.5 py-0.5 rounded shrink-0">+3 PTS</span>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="h-14"></div>
    </div>

    <!-- Botón Fijo Inferior -->
    <div class="bg-gray-900/95 backdrop-blur-md border-t border-gray-800 p-2.5 absolute bottom-0 w-full z-30">
        <button type="button" id="btn_guardar_batalla" onclick="guardarBatalla()" class="w-full bg-gray-800 text-gray-500 font-black py-3 rounded-xl text-xs sm:text-sm tracking-widest uppercase transition-all shadow-lg pointer-events-none">
            SELECCIONA BEYS Y RESULTADO
        </button>
    </div>
</div>