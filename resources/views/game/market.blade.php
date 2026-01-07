@extends('layouts.game')

@section('title', 'Mercado')

@section('content')
<div class="container mx-auto px-4 py-8 relative">

    {{-- 1. BOTÓN VOLVER AL MAPA --}}
    <div class="absolute top-0 left-4">
        <a href="{{ route('conquest.map') }}" class="flex items-center gap-2 text-cyan-400 hover:text-white transition-colors uppercase tracking-widest text-xs font-bold border-b border-cyan-500/30 pb-1 hover:border-white">
            <span>⬅</span> VOLVER AL MAPA
        </a>
    </div>

    {{-- 2. ALERTAS DEL SISTEMA --}}
    @if(session('success'))
        <div class="bg-green-900/50 border-l-4 border-green-500 text-green-300 p-4 mb-6 mt-8 shadow-[0_0_15px_rgba(34,197,94,0.3)] animate-pulse">
            <p class="font-bold">✅ TRANSACCIÓN ACEPTADA</p>
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-900/50 border-l-4 border-red-500 text-red-300 p-4 mb-6 mt-8 shadow-[0_0_15px_rgba(239,68,68,0.3)] animate-bounce">
            <p class="font-bold">⛔ ERROR DE TRANSACCIÓN</p>
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    {{-- CABECERA: DINERO Y RULETA --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 mt-12 bg-black/50 p-6 rounded border border-gray-800 backdrop-blur-sm">
        <div class="text-2xl text-yellow-500 font-bold mb-4 md:mb-0 neon-text-yellow">
            🦎 FONDOS: <span id="user-coins" class="text-white">{{ Auth::user()->coins }}</span> <span class="text-xs text-gray-400">LAGARTOS</span>
        </div>

        @if($canSpin)
            <button onclick="spinRoulette()" id="btn-spin" class="bg-gradient-to-r from-purple-700 to-pink-600 hover:from-purple-600 hover:to-pink-500 text-white font-bold py-3 px-8 rounded-full shadow-[0_0_20px_purple] animate-pulse border border-white/20 transition-all transform hover:scale-105">
                🎡 GIRAR RULETA DIARIA
            </button>
        @else
            <div class="text-gray-500 text-xs font-mono border border-gray-800 p-2 rounded bg-black/50">
                ⏳ SISTEMA DE RECOMPENSA ENFRIÁNDOSE...
            </div>
        @endif
    </div>

    <h2 class="text-4xl text-center md:text-left text-red-600 font-black uppercase mb-8 italic tracking-tighter" style="text-shadow: 0 0 10px rgba(220, 38, 38, 0.5);">
        MERCADO NEGRO
    </h2>

    {{-- NUEVO: SECCIÓN DEL INTENDENTE L.G.R.T. --}}
    <div class="max-w-4xl mx-auto mb-12 bg-gray-900/80 border-l-4 border-yellow-500 p-6 rounded-r shadow-[0_0_20px_rgba(234,179,8,0.1)] flex items-start gap-5 relative overflow-hidden">
        {{-- Fondo decorativo sutil --}}
        <div class="absolute right-0 top-0 opacity-10 text-9xl transform rotate-12 pointer-events-none grayscale">🦎</div>

        <div class="flex-shrink-0 z-10">
            <div class="w-16 h-16 bg-yellow-900/40 rounded-full border-2 border-yellow-500 flex items-center justify-center text-4xl shadow-[0_0_15px_rgba(234,179,8,0.5)]">
                🦎
            </div>
        </div>
        <div class="z-10">
            <div class="flex items-baseline gap-2 mb-1">
                <h3 class="text-yellow-500 font-black text-sm uppercase tracking-[0.2em]">INTENDENTE L.G.R.T.</h3>
                <span class="text-[10px] text-yellow-700 font-mono">Logistics & Global Resource Trader</span>
            </div>

            {{-- La frase se rellena con JS --}}
            <p id="lgrt-quote" class="text-gray-300 italic font-mono text-sm leading-relaxed border-l-2 border-gray-700 pl-3">
                "Cargando módulo de voz..."
            </p>
        </div>
    </div>

    {{-- BLOQUE DE SISTEMAS ACTIVOS (BUFFS) --}}
    @php
        $activeBuffs = \App\Models\TeamActiveBuff::where('team_id', Auth::user()->activeTeam->id)->get();
    @endphp

    @if($activeBuffs->count() > 0)
        <div class="max-w-4xl mx-auto mb-8 animate-pulse">
            <h4 class="text-green-400 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-2">
                <span>🟢</span> SISTEMAS DE COMBATE EN LÍNEA
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activeBuffs as $buff)
                    <div class="bg-green-900/20 border border-green-500/50 p-4 rounded flex justify-between items-center shadow-[0_0_10px_rgba(34,197,94,0.1)]">
                        <div>
                            <span class="text-white font-bold block text-sm">
                                @if(Str::contains($buff->item_code, 'attack')) ⚔️ POTENCIADOR DE DAÑO
                                @elseif(Str::contains($buff->item_code, 'defense')) 🛡️ ESCUDOS DE ZONA
                                @else ⚡ MEJORA TÁCTICA @endif
                            </span>
                            <span class="text-[10px] text-green-300 uppercase tracking-widest">
                                Caduca: DOMINGO 23:59
                            </span>
                        </div>
                        <div class="text-3xl font-mono font-bold text-green-400">
                            x{{ $buff->multiplier }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- LISTA DE ITEMS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($items as $item)
            <div class="bg-gray-900/80 border border-gray-700 p-5 rounded-lg relative overflow-hidden group hover:border-red-500 transition-colors shadow-lg flex flex-col">
                <div class="absolute inset-0 bg-red-500/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-white group-hover:text-red-400 transition-colors">{{ $item->name }}</h3>

                    {{-- CAMBIO: ICONOS EN LUGAR DE IMÁGENES --}}
                    <div class="w-12 h-12 flex items-center justify-center bg-black/40 rounded-full border border-gray-600 shadow-inner text-2xl group-hover:scale-110 transition-transform">
                        @if(Str::contains($item->code, 'radar'))
                            📡
                        @elseif(Str::contains($item->code, 'attack'))
                            ⚔️
                        @elseif(Str::contains($item->code, 'defense'))
                            🛡️
                        @else
                            📦
                        @endif
                    </div>
                </div>

                <p class="text-gray-400 text-xs flex-grow leading-relaxed mb-4">{{ $item->description }}</p>

                <div class="flex justify-between items-end mt-4 border-t border-gray-800 pt-4">
                    <div class="text-yellow-400 font-mono text-xl font-bold">
                        {{ $item->cost }} <span class="text-[10px] text-gray-500">LAGARTOS</span>
                    </div>

                    <form action="{{ route('market.buy') }}" method="POST">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <button type="submit" class="bg-gray-800 hover:bg-white hover:text-black text-gray-300 font-bold py-2 px-4 rounded text-xs uppercase tracking-widest border border-gray-600 transition-all">
                            ADQUIRIR
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    {{-- INVENTARIO DEL EQUIPO --}}
    <div class="mt-16 border-t border-gray-800 pt-8">
        <h3 class="text-xl text-gray-400 font-bold mb-4 uppercase tracking-widest">📦 ARSENAL DEL EQUIPO: {{ Auth::user()->activeTeam->name }}</h3>
        <div class="bg-black/40 p-6 rounded border border-gray-800 min-h-[100px]">
            @if(Auth::user()->activeTeam && Auth::user()->activeTeam->inventory->count() > 0)
                <ul class="flex gap-4 flex-wrap">
                    @foreach(Auth::user()->activeTeam->inventory as $invItem)
                        @if($invItem->quantity > 0)
                            <li class="bg-gray-800 px-4 py-2 rounded text-sm text-gray-300 border border-gray-600 flex items-center gap-3 shadow-md group">
                                <span class="text-lg">
                                    @if(Str::contains($invItem->item->code, 'radar')) 📡
                                    @elseif(Str::contains($invItem->item->code, 'attack')) ⚔️
                                    @elseif(Str::contains($invItem->item->code, 'defense')) 🛡️
                                    @else 📦 @endif
                                </span>
                                <span class="text-white font-bold">{{ $invItem->item->name }}</span>
                                <span class="bg-cyan-900/50 text-cyan-200 px-2 py-0.5 rounded text-xs font-mono border border-cyan-700">x{{ $invItem->quantity }}</span>

                                @if($invItem->item->code == 'intel_radar_zone')
                                    @if(Auth::user()->id == Auth::user()->activeTeam->captain_id)
                                        <button onclick="openRadarModal()" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 px-4 rounded uppercase tracking-wider shadow-[0_0_10px_blue] flex items-center gap-2 ml-2">
                                            ESCANEAR
                                        </button>
                                    @endif
                                @else
                                    @if(Auth::user()->id == Auth::user()->activeTeam->captain_id)
                                        <form action="{{ route('market.activate') }}" method="POST" class="ml-2">
                                            @csrf
                                            <input type="hidden" name="inventory_id" value="{{ $invItem->id }}">
                                            <button type="submit" class="bg-green-600 hover:bg-green-500 text-white text-xs font-bold py-2 px-4 rounded uppercase tracking-wider shadow-[0_0_10px_green]">
                                                ACTIVAR
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            @else
                <p class="text-gray-600 italic text-sm text-center py-4">Inventario vacío. Compra suministros para obtener ventaja táctica.</p>
            @endif
        </div>
    </div>

</div>

{{-- MODAL DEL RADAR --}}
<div id="radar-modal" class="fixed inset-0 bg-black/95 z-50 hidden flex items-center justify-center backdrop-blur-sm p-4">
    <div class="bg-gray-900 border-2 border-blue-500 w-full max-w-md p-6 rounded-lg shadow-[0_0_50px_rgba(59,130,246,0.5)] relative">
        <h3 class="text-2xl text-blue-400 font-bold mb-4 uppercase flex items-center gap-2">
            <span class="animate-pulse">📡</span> Satélite Espía
        </h3>
        <p class="text-gray-400 text-sm mb-6">Selecciona una zona para interceptar las comunicaciones enemigas y revelar sus fuerzas.</p>
        <div class="mb-6">
            <label class="block text-blue-300 text-xs font-bold mb-2 uppercase">Zona Objetivo</label>
            <select id="radar-zone-select" class="w-full bg-black border border-blue-600 text-white p-3 rounded focus:outline-none focus:shadow-[0_0_15px_blue]">
                @foreach($zones as $zone)
                    <option value="{{ $zone->slug }}">{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="radar-results" class="hidden mb-6 bg-black/50 p-4 border border-blue-500/30 rounded max-h-60 overflow-y-auto">
            <h4 class="text-xs text-gray-500 uppercase mb-2 border-b border-gray-700 pb-1">Informe de Inteligencia:</h4>
            <div id="radar-stats-list" class="space-y-2 text-sm"></div>
        </div>
        <div class="flex gap-4">
            <button onclick="scanZone()" id="btn-scan" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded uppercase tracking-widest shadow-lg transition-all">INICIAR ESCANEO</button>
            <button onclick="closeRadarModal()" id="btn-close-modal" class="px-4 py-3 text-gray-400 hover:text-white border border-gray-700 rounded uppercase text-xs font-bold transition-colors">CANCELAR</button>
        </div>
    </div>
</div>

{{-- SCRIPT: LÓGICA RADAR --}}
<script>
    let reloadOnClose = false;
    function openRadarModal() {
        reloadOnClose = false;
        document.getElementById('radar-modal').classList.remove('hidden');
        document.getElementById('radar-results').classList.add('hidden');
        const btn = document.getElementById('btn-scan');
        btn.disabled = false; btn.innerText = "INICIAR ESCANEO"; btn.className = "flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded uppercase tracking-widest shadow-lg transition-all";
        const closeBtn = document.getElementById('btn-close-modal');
        closeBtn.innerText = "CANCELAR"; closeBtn.className = "px-4 py-3 text-gray-400 hover:text-white border border-gray-700 rounded uppercase text-xs font-bold transition-colors";
    }
    function closeRadarModal() {
        document.getElementById('radar-modal').classList.add('hidden');
        if (reloadOnClose) { window.location.reload(); }
    }
    function scanZone() {
        const zoneSlug = document.getElementById('radar-zone-select').value;
        const btn = document.getElementById('btn-scan');
        const list = document.getElementById('radar-stats-list');
        const results = document.getElementById('radar-results');
        const closeBtn = document.getElementById('btn-close-modal');
        btn.disabled = true; btn.innerText = "INTERCEPTANDO SEÑAL...";
        fetch("{{ route('market.use_radar') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ zone_slug: zoneSlug })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                reloadOnClose = true;
                results.classList.remove('hidden');
                list.innerHTML = '';
                if(data.data.length === 0) {
                    list.innerHTML = '<p class="text-gray-500 italic text-center">📡 No se detectan señales enemigas (0 votos).</p>';
                } else {
                    data.data.forEach(stat => {
                        list.innerHTML += `<div class="mb-2"><div class="flex justify-between items-center"><span class="font-bold" style="color:${stat.color}">${stat.name}</span><span class="text-white font-mono">${stat.total_votes} Votos</span></div><div class="w-full h-1 bg-gray-800 rounded mt-1 mb-2"><div class="h-full rounded" style="width: 100%; background-color: ${stat.color}; opacity: 0.5"></div></div></div>`;
                    });
                }
                btn.innerText = "ESCANEO COMPLETADO"; btn.className = "flex-1 bg-gray-700 text-gray-500 font-bold py-3 rounded uppercase tracking-widest cursor-not-allowed border border-gray-600";
                closeBtn.innerText = "CERRAR Y ACTUALIZAR"; closeBtn.className = "px-4 py-3 bg-green-700 hover:bg-green-600 text-white border border-green-500 rounded uppercase text-xs font-bold transition-colors shadow-lg";
            } else { alert(data.error); btn.disabled = false; btn.innerText = "REINTENTAR"; }
        })
        .catch(err => { console.error(err); btn.innerText = "ERROR DE CONEXIÓN"; btn.classList.add('bg-red-900'); });
    }
</script>

{{-- SCRIPT: LÓGICA DE LA RULETA --}}
<script>
    function spinRoulette() {
        const btn = document.getElementById('btn-spin');
        const originalText = btn.innerText;
        btn.disabled = true; btn.classList.remove('from-purple-700', 'to-pink-600', 'animate-pulse'); btn.classList.add('bg-gray-700', 'cursor-not-allowed'); btn.innerText = "HACKEANDO...";
        fetch("{{ route('market.spin') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                btn.classList.replace('bg-gray-700', 'bg-green-600'); btn.innerText = "¡PREMIO!";
                alert("🎰 RESULTADO DEL GIRO:\n\n" + data.message + "\n\n💰 Nuevo Saldo: " + data.new_balance);
                document.getElementById('user-coins').innerText = data.new_balance;
                setTimeout(() => { btn.style.display = 'none'; }, 1000);
            } else {
                alert(data.error); btn.innerText = originalText; btn.disabled = false; btn.classList.remove('bg-gray-700', 'cursor-not-allowed'); btn.classList.add('bg-gradient-to-r', 'from-purple-700', 'to-pink-600', 'animate-pulse');
            }
        })
        .catch(error => { console.error('Error:', error); btn.innerText = "ERROR DE CONEXIÓN"; btn.classList.add('bg-red-600'); });
    }
</script>

{{-- SCRIPT: FRASES DEL INTENDENTE L.G.R.T. --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const quotes = [
            "Nada es gratis en el Distrito, camarada. Vuelve con crédito.",
            "Tengo suministros frescos. No preguntes de qué camión 'cayeron'.",
            "La información es poder, pero el poder cuesta Lagartos.",
            "¿Ese equipo es reglamentario? No me importa, mientras pagues.",
            "Mis precios son altos, pero tu supervivencia vale más.",
            "Los satélites no se mantienen solos. Alguien tiene que pagar la factura.",
            "He visto imperios caer por falta de munición. No seas el siguiente.",
            "¿Ruleta o estrategia? Tú decides cómo gastar tu fortuna.",
            "Si eres catalán no creo que te interesen estos precios."
        ];

        // Seleccionar frase aleatoria
        const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];

        // Efecto simple de escritura o inserción directa
        document.getElementById('lgrt-quote').innerText = '"' + randomQuote + '"';
    });
</script>
@endsection
