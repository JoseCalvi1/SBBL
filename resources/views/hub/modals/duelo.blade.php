<div id="dueloModal" class="modal-overlay fixed inset-0 z-50 items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="bg-gray-900 w-full sm:w-[400px] rounded-t-2xl sm:rounded-2xl max-h-[90vh] flex flex-col border border-gray-700 shadow-[0_0_20px_rgba(0,0,0,0.8)] overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-950 rounded-t-2xl shrink-0">
            <h3 class="text-white font-bold text-lg font-mono"><i class="fas fa-khanda text-red-500 me-2"></i> DECLARAR DUELO</h3>
            <button onclick="cerrarModalDuelo()" class="text-gray-400 hover:text-white text-2xl px-2">&times;</button>
        </div>
        <div class="p-4 overflow-y-auto flex-1 bg-black no-scrollbar">
            <form id="formDuelo" onsubmit="enviarDuelo(event)">
                <div class="bg-gray-800/50 p-3 rounded-xl border border-gray-700/50 mb-3 shadow-inner relative">
                    <label class="text-cyan-400 font-bold text-[10px] tracking-widest block mb-2">EQUIPO LOCAL</label>
                    <div class="relative">
                        <input type="text" id="search_team_1" class="w-full bg-gray-900 border border-gray-700 text-white text-sm p-3 rounded-lg outline-none mb-3" placeholder="Buscar clan..." autocomplete="off" onfocus="abrirBuscador(1)" onblur="cerrarBuscador(1)" oninput="filtrarEquipos(1)">
                        <input type="hidden" id="team_id_1" name="team_id_1">
                        <div id="dropdown_team_1" class="hidden absolute top-full left-0 z-[70] w-full bg-gray-800 border border-gray-600 max-h-40 overflow-y-auto rounded-lg shadow-xl mt-[-10px] mb-3 no-scrollbar"></div>
                    </div>
                    <input type="number" id="result_1" name="result_1" class="w-full bg-gray-900 border border-gray-700 text-white text-center text-xl font-mono font-bold p-2 rounded-lg" placeholder="PUNTOS" required min="0">
                </div>

                <div class="text-center text-red-500 font-mono font-black text-xl italic mb-3">VS</div>

                <div class="bg-gray-800/50 p-3 rounded-xl border border-gray-700/50 mb-3 shadow-inner relative">
                    <label class="text-yellow-400 font-bold text-[10px] tracking-widest block mb-2">EQUIPO VISITANTE</label>
                    <div class="relative">
                        <input type="text" id="search_team_2" class="w-full bg-gray-900 border border-gray-700 text-white text-sm p-3 rounded-lg outline-none mb-3" placeholder="Buscar clan..." autocomplete="off" onfocus="abrirBuscador(2)" onblur="cerrarBuscador(2)" oninput="filtrarEquipos(2)">
                        <input type="hidden" id="team_id_2" name="team_id_2">
                        <div id="dropdown_team_2" class="hidden absolute top-full left-0 z-[70] w-full bg-gray-800 border border-gray-600 max-h-40 overflow-y-auto rounded-lg shadow-xl mt-[-10px] mb-3 no-scrollbar"></div>
                    </div>
                    <input type="number" id="result_2" name="result_2" class="w-full bg-gray-900 border border-gray-700 text-white text-center text-xl font-mono font-bold p-2 rounded-lg" placeholder="PUNTOS" required min="0">
                </div>

                <input type="hidden" name="modalidad" value="beybladex">
                <div class="mb-4">
                    <label class="text-gray-400 font-bold text-[10px] tracking-widest block mb-1">ENLACE DEL VÍDEO (OPCIONAL)</label>
                    <input type="url" name="url" id="url_duelo" class="w-full bg-gray-900 border border-gray-700 text-white text-xs p-3 rounded-lg outline-none" placeholder="https://youtube.com/...">
                </div>
                <div class="p-4 border-t border-gray-800 flex flex-col gap-2">
                <!-- Botón Normal (Solo sube resultados en crudo) -->
                <button type="submit" id="btnSubmitDuelo" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-lg shadow-lg transition-all">
                    CONFIRMAR RESULTADO
                </button>

                <!-- Botón Express Juez (Abre la arena interactiva) -->
                @if(Auth::check() && Auth::user()->hasAnyRole(['admin', 'juez']))
                <button type="button" onclick="enviarDueloRapidoArena()" id="btnSubmitDueloArena" class="w-full bg-gray-900 border-2 border-yellow-500 text-yellow-400 hover:bg-yellow-900/40 font-bold py-3 rounded-lg shadow-[0_0_15px_rgba(234,179,8,0.2)] transition-all">
                    <i class="fas fa-bolt text-yellow-400 mr-2"></i> IR A LA ARENA (JUZGAR)
                </button>
                @endif
            </div>
            </form>
        </div>
    </div>
</div>