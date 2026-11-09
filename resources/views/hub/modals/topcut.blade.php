<div id="topCutModal" class="modal-overlay fixed inset-0 z-[80] items-center justify-center p-4 hidden">
    <div class="bg-gray-950 w-full max-w-sm rounded-2xl flex flex-col border border-gray-700 shadow-2xl p-5 text-center">
        <h3 class="text-white font-bold text-lg mb-1 text-cyan-400 font-mono">CERRAR SUIZO Y GENERAR TOP CUT</h3>
        <p class="text-gray-400 text-[11px] mb-4 leading-relaxed">
            Jugadores activos: <span id="total_jugadores_top" class="text-white font-bold">0</span>. Si eliges un Top intermedio (ej. Top 10 o Top 12), los mejores clasificados recibirán <b class="text-cyan-400">BYE automático</b> en 1ª ronda.
        </p>

        <div class="grid grid-cols-4 gap-2 mb-4">
            <button onclick="ejecutarTopCut(4)" id="btn_top_4" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 4</button>
            <button onclick="ejecutarTopCut(6)" id="btn_top_6" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 6</button>
            <button onclick="ejecutarTopCut(8)" id="btn_top_8" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 8</button>
            <button onclick="ejecutarTopCut(10)" id="btn_top_10" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 10</button>
            <button onclick="ejecutarTopCut(12)" id="btn_top_12" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 12</button>
            <button onclick="ejecutarTopCut(14)" id="btn_top_14" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 14</button>
            <button onclick="ejecutarTopCut(16)" id="btn_top_16" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 16</button>
            <button onclick="ejecutarTopCut(32)" id="btn_top_32" class="bg-gray-900 border border-gray-700 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 32</button>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-2.5 mb-3">
            <label class="text-[10px] text-gray-400 font-mono font-bold uppercase block mb-1.5">O INDICA UN NÚMERO PERSONALIZADO DE CLASIFICADOS</label>
            <div class="flex gap-2">
                <input type="number" id="custom_top_cut_input" min="4" max="64" placeholder="Ej. 10" class="flex-1 bg-gray-950 border border-gray-700 text-white text-center font-mono font-black text-sm rounded-lg p-2 outline-none focus:border-cyan-400">
                <button type="button" onclick="ejecutarTopCutPersonalizado()" class="bg-cyan-600 hover:bg-cyan-500 text-gray-950 font-black text-xs px-4 rounded-lg uppercase tracking-wider">
                    GENERAR
                </button>
            </div>
        </div>

        <button onclick="document.getElementById('topCutModal').classList.remove('flex', 'active'); document.getElementById('topCutModal').classList.add('hidden');" class="text-gray-500 hover:text-white font-bold text-xs uppercase tracking-widest mt-1">CANCELAR</button>
    </div>
</div>
