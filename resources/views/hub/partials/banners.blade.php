<div id="installBanner" class="hidden bg-cyan-950/80 border-b border-cyan-800 p-3 flex justify-between items-center shadow-[0_5px_15px_rgba(0,0,0,0.5)] z-20 relative backdrop-blur-sm">
    <div class="flex items-center gap-3">
        <div class="bg-black border border-cyan-500 w-10 h-10 flex items-center justify-center rounded-lg shadow-inner"><i class="fas fa-download text-cyan-400 text-lg"></i></div>
        <div>
            <p class="text-white font-bold text-sm leading-none font-mono">APP OFICIAL SBBL</p>
            <p class="text-cyan-500 text-[10px] tracking-widest mt-1">JUEGA A PANTALLA COMPLETA</p>
        </div>
    </div>
    <button id="btnInstall" class="bg-cyan-600 active:bg-cyan-500 text-white font-bold py-2 px-4 rounded-lg text-xs tracking-wider shadow-lg transition-colors border border-cyan-400">INSTALAR</button>
</div>
<div id="iosInstallBanner" class="hidden bg-gray-900/95 border-b border-gray-700 p-3 flex justify-between items-center shadow-[0_5px_15px_rgba(0,0,0,0.5)] z-20 relative backdrop-blur-sm">
    <div class="flex items-center gap-3">
        <div class="bg-black border border-gray-600 w-10 h-10 flex items-center justify-center rounded-lg shadow-inner text-gray-300"><i class="fab fa-apple text-xl"></i></div>
        <div>
            <p class="text-white font-bold text-xs leading-none font-mono">INSTALAR EN iPHONE</p>
            <p class="text-gray-400 text-[10px] mt-1 leading-tight">Pulsa <i class="fas fa-share-square mx-0.5 text-cyan-500"></i> en Safari y selecciona <b>"Añadir a la pantalla de inicio"</b>.</p>
        </div>
    </div>
    <button onclick="document.getElementById('iosInstallBanner').classList.add('hidden')" class="text-gray-500 hover:text-white px-3 text-xl font-bold">&times;</button>
</div>