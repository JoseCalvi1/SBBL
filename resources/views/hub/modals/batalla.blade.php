<!-- MODAL DE BATALLA (INTERFAZ TÁCTIL DE JUEZ) -->
<div id="batallaModal" class="fixed inset-0 bg-black/95 z-[80] hidden flex-col transition-all duration-300">    
    <!-- Cabecera -->
    <div class="bg-gray-900 border-b border-gray-800 p-4 flex justify-between items-center shadow-lg relative z-10">
        <h3 class="text-white font-black font-mono text-xl tracking-widest flex items-center">
            <i class="fas fa-tablet-alt text-yellow-500 mr-2"></i> PANEL
        </h3>
        <div class="flex gap-2">
            <button onclick="deshacerUltimoRegistro()" class="text-gray-400 hover:text-cyan-400 bg-gray-800 p-2 rounded-lg text-xl transition-colors shadow-sm" title="Deshacer Acción">
                <i class="fas fa-undo"></i>
            </button>
            <button onclick="cerrarBatalla()" class="text-gray-400 hover:text-red-500 bg-gray-800 p-2 rounded-lg text-xl transition-colors shadow-sm">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Zona de Jugadores y Marcador -->
    <div class="flex justify-between items-stretch p-4 gap-2 bg-gray-950 shadow-inner">
        <!-- Jugador 1 -->
        <div class="flex-1 bg-gray-900 border-t-4 border-cyan-500 rounded-lg p-3 text-center flex flex-col justify-center relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-10"><i class="fas fa-user text-6xl text-cyan-500"></i></div>
            
            <button onclick="lanzarWarning('p1')" id="btn_warning_p1" class="absolute top-2 left-2 text-gray-700 hover:text-yellow-500 transition-colors z-20 text-lg">
                <i class="fas fa-exclamation-triangle"></i>
            </button>
            
            <span id="batalla_p1_name" class="text-white font-bold text-sm uppercase truncate mb-1 relative z-10">JUGADOR 1</span>
            <span id="batalla_score_p1" class="text-4xl font-black font-mono text-cyan-400 relative z-10">0</span>
            <input type="hidden" id="batalla_p1_id">
        </div>
        
        <div class="flex items-center justify-center px-1">
            <span class="text-gray-600 font-black italic font-mono text-xl">VS</span>
        </div>
        
        <!-- Jugador 2 -->
        <div class="flex-1 bg-gray-900 border-t-4 border-red-500 rounded-lg p-3 text-center flex flex-col justify-center relative overflow-hidden">
            <div class="absolute -left-4 -bottom-4 opacity-10"><i class="fas fa-user text-6xl text-red-500"></i></div>
            
            <button onclick="lanzarWarning('p2')" id="btn_warning_p2" class="absolute top-2 right-2 text-gray-700 hover:text-yellow-500 transition-colors z-20 text-lg">
                <i class="fas fa-exclamation-triangle"></i>
            </button>
            
            <span id="batalla_p2_name" class="text-white font-bold text-sm uppercase truncate mb-1 relative z-10">JUGADOR 2</span>
            <span id="batalla_score_p2" class="text-4xl font-black font-mono text-red-400 relative z-10">0</span>
            <input type="hidden" id="batalla_p2_id">
        </div>
    </div>

    <!-- Contenedor scrolleable principal -->
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
        
        <!-- PASO 1: DECK CHECK -->
        <div>
            <h4 class="text-[10px] text-gray-500 font-bold tracking-widest uppercase mb-2"><i class="fas fa-check-circle mr-1"></i> 1. SELECCIONA LOS BEYS EN LA ARENA</h4>
            <div class="grid grid-cols-2 gap-3">
                <div id="deck_p1_container" class="space-y-2"></div>
                <div id="deck_p2_container" class="space-y-2"></div>
            </div>
        </div>

        <!-- ZONA DE RESULTADOS (Se bloquea hasta elegir beys) -->
        <div id="zona_resultados" class="opacity-30 pointer-events-none transition-opacity duration-500 space-y-6">
            
            <!-- PASO 2: GANADOR -->
            <div>
                <h4 class="text-[10px] text-gray-500 font-bold tracking-widest uppercase mb-2"><i class="fas fa-trophy mr-1"></i> 2. ¿QUIÉN HA GANADO?</h4>
                <div class="flex gap-3">
                    <button id="btn_ganador_p1" onclick="seleccionarGanador('p1')" class="flex-1 bg-gray-900 border border-gray-700 text-gray-400 font-bold py-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg">GANA P1</button>
                    <button id="btn_ganador_p2" onclick="seleccionarGanador('p2')" class="flex-1 bg-gray-900 border border-gray-700 text-gray-400 font-bold py-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg">GANA P2</button>
                </div>
            </div>

            <!-- PASO 3: TIPO DE FINAL -->
            <div>
                <h4 class="text-[10px] text-gray-500 font-bold tracking-widest uppercase mb-2"><i class="fas fa-bolt mr-1"></i> 3. TIPO DE FINAL</h4>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Botones táctiles grandes con iconos -->
                    <button id="btn_fin_spin" onclick="seleccionarFinal('spin')" class="bg-gray-900 border border-gray-700 text-gray-400 font-black py-4 rounded-xl text-sm tracking-wider uppercase transition-all active:scale-95 shadow-md flex flex-col items-center gap-1">
                        <i class="fas fa-sync fa-spin text-xl"></i> SPIN FINISH <span class="text-[9px] font-bold text-gray-500 mt-1 bg-gray-800 px-2 py-0.5 rounded">1 PT</span>
                    </button>
                    
                    <button id="btn_fin_over" onclick="seleccionarFinal('over')" class="bg-gray-900 border border-gray-700 text-gray-400 font-black py-4 rounded-xl text-sm tracking-wider uppercase transition-all active:scale-95 shadow-md flex flex-col items-center gap-1">
                        <i class="fas fa-arrow-up text-xl"></i> OVER FINISH <span class="text-[9px] font-bold text-gray-500 mt-1 bg-gray-800 px-2 py-0.5 rounded">2 PTS</span>
                    </button>
                    
                    <button id="btn_fin_burst" onclick="seleccionarFinal('burst')" class="bg-gray-900 border border-gray-700 text-gray-400 font-black py-4 rounded-xl text-sm tracking-wider uppercase transition-all active:scale-95 shadow-md flex flex-col items-center gap-1">
                        <i class="fas fa-bomb text-xl"></i> BURST FINISH <span class="text-[9px] font-bold text-gray-500 mt-1 bg-gray-800 px-2 py-0.5 rounded">2 PTS</span>
                    </button>
                    
                    <button id="btn_fin_xtreme" onclick="seleccionarFinal('xtreme')" class="bg-gray-900 border border-gray-700 text-gray-400 font-black py-4 rounded-xl text-sm tracking-wider uppercase transition-all active:scale-95 shadow-md flex flex-col items-center gap-1">
                        <i class="fas fa-bolt text-xl"></i> XTREME FINISH <span class="text-[9px] font-bold text-gray-500 mt-1 bg-gray-800 px-2 py-0.5 rounded">3 PTS</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Relleno inferior para que el botón flotante no tape contenido -->
        <div class="h-20"></div>
    </div>

    <!-- Botón Fijo Inferior (Solo se activa si todo está seleccionado) -->
    <div class="bg-gray-900 border-t border-gray-800 p-4 absolute bottom-0 w-full z-20">
        <button id="btn_guardar_batalla" onclick="guardarBatalla()" class="w-full bg-gray-800 text-gray-500 font-black py-4 rounded-xl text-base tracking-widest uppercase transition-all shadow-lg pointer-events-none">
            REGISTRAR PUNTOS
        </button>
    </div>
</div>