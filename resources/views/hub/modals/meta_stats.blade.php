<div id="metaModal" class="modal-overlay fixed inset-0 z-[80] items-end sm:items-center justify-center p-0 sm:p-4 hidden">
    <div class="bg-gray-950 w-full sm:w-[500px] h-[90vh] sm:h-[85vh] rounded-t-2xl sm:rounded-2xl flex flex-col border border-gray-700 shadow-[0_0_30px_rgba(168,85,247,0.15)] overflow-hidden">
        
        <!-- Cabecera -->
        <div class="p-4 border-b border-gray-800 flex justify-between items-start bg-black shrink-0 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-purple-600 rounded-full blur-[60px] opacity-20 pointer-events-none"></div>
            <div>
                <h3 id="modal_meta_blade" class="text-yellow-500 font-black text-2xl font-mono leading-none tracking-wide uppercase">BLADE</h3>
                <p id="modal_meta_parts" class="text-white text-sm font-bold mt-1 opacity-75">RATCHET X BIT</p>
                <div id="modal_meta_style" class="mt-2 text-[9px] px-2 py-0.5 rounded border font-bold tracking-widest uppercase inline-block bg-purple-900/40 border-purple-800 text-purple-300">ESTILO</div>
            </div>
            <div class="text-right flex flex-col items-end z-10">
                <button onclick="cerrarModalMeta()" class="text-gray-400 hover:text-white text-3xl leading-none mb-2">&times;</button>
                <span id="modal_meta_wr" class="text-2xl font-black font-mono text-cyan-400">0%</span>
                <p id="modal_meta_wl" class="text-[10px] text-gray-400 font-bold tracking-widest">0W - 0L</p>
            </div>
        </div>

        <!-- Contenido Deslizable -->
        <div class="p-4 overflow-y-auto flex-1 no-scrollbar flex flex-col gap-5">
            
            <!-- Resumen de Finales -->
            <div>
                <h4 class="text-gray-500 font-bold text-[10px] tracking-widest uppercase mb-2 border-b border-gray-800 pb-1"><i class="fas fa-chart-pie mr-1"></i> HISTORIAL DE VICTORIAS</h4>
                <div class="grid grid-cols-4 gap-2">
                    <div class="bg-gray-900 border border-gray-800 p-2 rounded-lg text-center shadow-inner">
                        <div class="text-[9px] text-green-500 font-bold tracking-widest mb-1">SPIN</div>
                        <div id="modal_meta_spin" class="text-lg font-mono font-black text-white">0</div>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 p-2 rounded-lg text-center shadow-inner">
                        <div class="text-[9px] text-cyan-400 font-bold tracking-widest mb-1">OVER</div>
                        <div id="modal_meta_over" class="text-lg font-mono font-black text-white">0</div>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 p-2 rounded-lg text-center shadow-inner">
                        <div class="text-[9px] text-orange-400 font-bold tracking-widest mb-1">BURST</div>
                        <div id="modal_meta_burst" class="text-lg font-mono font-black text-white">0</div>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 p-2 rounded-lg text-center shadow-inner">
                        <div class="text-[9px] text-fuchsia-400 font-bold tracking-widest mb-1">XTREME</div>
                        <div id="modal_meta_xtreme" class="text-lg font-mono font-black text-white">0</div>
                    </div>
                </div>
            </div>

            <!-- Mejores Emparejamientos -->
            <div>
                <h4 class="text-green-500 font-bold text-[10px] tracking-widest uppercase mb-2 border-b border-green-900/50 pb-1"><i class="fas fa-crosshairs mr-1"></i> MEJORES EMPAREJAMIENTOS</h4>
                <div id="modal_meta_best" class="space-y-2">
                    <!-- Rellenado por JS -->
                </div>
            </div>

            <!-- Peores Emparejamientos -->
            <div>
                <h4 class="text-red-500 font-bold text-[10px] tracking-widest uppercase mb-2 border-b border-red-900/50 pb-1"><i class="fas fa-skull mr-1"></i> PEORES EMPAREJAMIENTOS</h4>
                <div id="modal_meta_worst" class="space-y-2">
                    <!-- Rellenado por JS -->
                </div>
            </div>

            <!-- Daño Promedio -->
            <div class="flex justify-around text-center pt-2 pb-6">
                <div class="bg-gray-900/50 p-3 rounded-lg border border-gray-800 w-full mr-2">
                    <div class="text-[9px] text-gray-500 font-bold tracking-widest mb-1">DAÑO PROMEDIO A FAVOR</div>
                    <div id="modal_meta_dmg_won" class="text-green-400 font-bold text-xl font-mono">0.00</div>
                </div>
                <div class="bg-gray-900/50 p-3 rounded-lg border border-gray-800 w-full ml-2">
                    <div class="text-[9px] text-gray-500 font-bold tracking-widest mb-1">DAÑO PROMEDIO EN CONTRA</div>
                    <div id="modal_meta_dmg_lost" class="text-red-400 font-bold text-xl font-mono">0.00</div>
                </div>
            </div>

        </div>
    </div>
</div>