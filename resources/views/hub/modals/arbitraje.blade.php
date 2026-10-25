<div id="arbitrajeModal" class="modal-overlay fixed inset-0 z-[70] items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-gray-900 w-full sm:w-[540px] h-[96vh] sm:h-[88vh] rounded-t-2xl sm:rounded-2xl flex flex-col border border-gray-700 shadow-[0_0_30px_rgba(0,0,0,0.95)] overflow-hidden">
        
        <!-- Cabecera del Panel -->
        <div class="p-3.5 sm:p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-2xl shrink-0">
            <div class="truncate pr-2">
                <div class="flex items-center gap-2">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                    </span>
                    <h3 class="text-white font-black text-base sm:text-lg font-mono leading-none tracking-wide">CONTROL DE ARENA</h3>
                </div>
                <p id="arbitraje_event_name" class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase mt-1 truncate">Cargando...</p>
            </div>
            
            <div class="flex items-center gap-1.5 shrink-0">
                <!-- Botón Copiar Link Público (Discord) -->
                <button onclick="copiarEnlaceCuadroPublico()" title="Copiar enlace público para Discord" class="bg-indigo-950/80 hover:bg-indigo-900 text-indigo-300 border border-indigo-500/40 px-2.5 h-9 rounded-lg text-[10px] font-bold tracking-wider flex items-center gap-1.5 transition-all active:scale-95 shadow-sm">
                    <i class="fas fa-share-alt text-indigo-400"></i> <span class="hidden sm:inline">DISCORD</span>
                </button>
                <!-- Botón Refrescar Arena -->
                <button onclick="refrescarVistaArenaActual()" title="Sincronizar Arena" class="bg-gray-800 hover:bg-gray-700 text-cyan-400 border border-gray-700 w-9 h-9 rounded-lg flex items-center justify-center transition-all active:scale-90">
                    <i class="fas fa-sync-alt text-xs"></i>
                </button>
                <!-- Botón Cerrar -->
                <button onclick="cerrarPanelArbitraje()" class="text-gray-400 hover:text-white text-xl w-9 h-9 flex items-center justify-center rounded-lg bg-gray-800 hover:bg-red-900/60 border border-gray-700 transition-colors">&times;</button>
            </div>
        </div>

        <!-- Área Dinámica (Cruces / Tabla / Cuadro) -->
        <div class="p-3.5 sm:p-4 overflow-y-auto flex-1 bg-[#060911] no-scrollbar relative" id="arbitrajeContent">
            <div class="text-center mt-10">
                <i class="fas fa-circle-notch fa-spin text-yellow-500 text-3xl mb-3"></i>
                <p class="text-gray-500 text-sm font-bold tracking-widest">SINTONIZANDO ARENA...</p>
            </div>
        </div>

        <!-- Botonera Inferior del Juez (Ronda 1) -->
        <div id="arbitrajeFooter" class="p-3.5 bg-gray-950 border-t border-gray-800 shrink-0 hidden">
            <button onclick="generarSiguienteRonda()" id="btnGenerarRonda" class="w-full bg-green-700 hover:bg-green-600 active:bg-green-800 text-white font-black py-3.5 rounded-xl text-sm tracking-widest uppercase shadow-[0_0_15px_rgba(22,163,74,0.4)] border border-green-500 transition-all">
                <i class="fas fa-cogs mr-2"></i> GENERAR RONDA 1
            </button>
        </div>
    </div>
</div>