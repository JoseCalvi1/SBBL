<div id="topCutModal" class="modal-overlay fixed inset-0 z-[80] items-center justify-center p-4 hidden">
    <div class="bg-gray-950 w-full max-w-sm rounded-2xl flex flex-col border border-gray-700 shadow-2xl p-5 text-center">
        <h3 class="text-white font-bold text-lg mb-2 text-cyan-400 font-mono">CERRAR SUIZO</h3>
        <p class="text-gray-400 text-xs mb-4">Selecciona el tamaño del cuadro final. Según el número de inscritos (<span id="total_jugadores_top">0</span>), las opciones no válidas están bloqueadas.</p>
        
        <div class="grid grid-cols-2 gap-3 mb-4">
            <button onclick="ejecutarTopCut(4)" id="btn_top_4" class="bg-gray-900 border border-gray-700 text-white font-bold py-3 rounded-lg text-sm hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 4</button>
            <button onclick="ejecutarTopCut(8)" id="btn_top_8" class="bg-gray-900 border border-gray-700 text-white font-bold py-3 rounded-lg text-sm hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 8</button>
            <button onclick="ejecutarTopCut(16)" id="btn_top_16" class="bg-gray-900 border border-gray-700 text-white font-bold py-3 rounded-lg text-sm hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 16</button>
            <button onclick="ejecutarTopCut(32)" id="btn_top_32" class="bg-gray-900 border border-gray-700 text-white font-bold py-3 rounded-lg text-sm hover:bg-cyan-900 hover:border-cyan-500 transition-colors">TOP 32</button>
        </div>
        
        <button onclick="document.getElementById('topCutModal').classList.remove('flex', 'active'); document.getElementById('topCutModal').classList.add('hidden');" class="text-gray-500 font-bold text-xs uppercase tracking-widest mt-2">CANCELAR</button>
    </div>
</div>