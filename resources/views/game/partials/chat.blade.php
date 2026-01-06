<div id="team-chat" class="fixed bottom-0 right-4 w-80 h-96 bg-black/95 border border-yellow-600/50 flex flex-col shadow-[0_0_20px_rgba(255,215,0,0.15)] transition-transform duration-300 z-50 rounded-t-lg translate-y-[calc(100%-40px)]">

        <div class="bg-yellow-900/30 p-2 border-b border-yellow-600/30 flex justify-between items-center cursor-pointer hover:bg-yellow-900/50" onclick="toggleChat()">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-yellow-500 font-bold tracking-widest text-[10px] uppercase">
                    CANAL: {{ Str::limit(Auth::user()->activeTeam->name, 15) }}
                </span>
            </div>
            <span id="chat-arrow" class="text-yellow-500 text-xs transition-transform duration-300 transform">▲</span>
        </div>

        <div id="chat-messages" class="flex-1 overflow-y-auto p-3 space-y-2 text-xs font-mono bg-black/50">
            <p class="text-gray-600 text-center italic mt-4">Estableciendo enlace seguro...</p>
        </div>

        <div class="p-2 border-t border-yellow-600/30 bg-black flex gap-2">
            <input type="text" id="chat-input"
                   class="bg-gray-900 text-yellow-100 w-full text-xs p-2 border border-yellow-800 focus:outline-none focus:border-yellow-500 placeholder-yellow-800/50 rounded-sm"
                   placeholder="Escribir orden..." autocomplete="off">
            <button onclick="enviarMensaje()" class="bg-yellow-700 hover:bg-yellow-600 text-black font-bold px-3 text-xs rounded-sm">></button>
        </div>
    </div>
