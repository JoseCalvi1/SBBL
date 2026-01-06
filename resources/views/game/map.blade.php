@extends('layouts.game')

@section('title', 'Mapa Táctico')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Header: Ajustado padding y flex para móviles --}}
    <div class="mb-6 mt-5 flex flex-col md:flex-row justify-between items-end border-b border-cyan-500/30 pb-4 w-full">
        <div class="text-left w-full md:w-auto">
            <h2 class="text-3xl font-bold neon-text text-white">MAPA TÁCTICO</h2>
            <div class="flex gap-4 items-center">
                <p class="text-cyan-400 text-sm tracking-widest">SISTEMA ACTIVO</p>
                <a href="{{ route('conquest.news') }}" class="flex items-center gap-1 text-[10px] bg-red-900/30 text-red-400 px-2 py-1 border border-red-500/30 hover:bg-red-900/60 transition-colors animate-pulse">
                    <span>📰</span> REPORTES
                </a>
            </div>
        </div>

        <div class="text-right hidden md:block">
            <span class="block text-xs text-gray-400 uppercase">Frecuencia</span>
            @if(Auth::user()->activeTeam)
                <span class="text-xl font-bold uppercase drop-shadow-md flex items-center justify-end gap-2"
                      style="color: {{ Auth::user()->activeTeam->color }}">
                    <span class="w-3 h-3 rounded-full animate-pulse bg-current"></span>
                    {{ Auth::user()->activeTeam->name }}
                </span>
            @else
                <span class="text-xl font-bold text-gray-500">MERCENARIO</span>
            @endif
        </div>
    </div>

    {{-- CONTENEDOR PRINCIPAL:
         - Quitamos 'h-full' fijo para evitar cortes en móvil.
         - Añadimos 'pb-20' para dar espacio al scroll final.
    --}}
    <div class="flex flex-col xl:flex-row gap-6 items-start relative justify-center w-full pb-20">

        {{-- COLUMNA IZQUIERDA: Logs --}}
        @if(Auth::user()->active_team)
        {{-- He cambiado 'hidden xl:block' por 'w-full xl:w-64'.
             Ahora se ve en móvil arriba del mapa. Si prefieres que no se vea en móvil, déjalo como estaba. --}}
        <div class="w-full xl:w-64 flex-shrink-0 z-20 order-2 xl:order-1">
            <div class="bg-black/90 border-l-2 border-cyan-500 p-2 mb-1 backdrop-blur-sm pointer-events-auto">
                <h4 class="text-cyan-400 font-bold text-xs uppercase tracking-widest">CANAL DE MANDO</h4>
                <p class="text-[10px] text-gray-500">{{ Auth::user()->active_team->name }} Log</p>
            </div>
            {{-- Limitamos altura en móvil para que no ocupe toda la pantalla --}}
            <div class="space-y-1 pointer-events-auto max-h-[30vh] xl:max-h-[60vh] overflow-y-auto no-scrollbar mask-gradient-bottom">
                @forelse($teamActivity as $activity)
                    <div class="bg-gray-900/80 p-2 border-l border-gray-700 text-xs shadow-lg animate-fade-in-left">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-white">{{ $activity->user_name }}</span>
                            <span class="text-[9px] text-gray-500 font-mono">{{ \Carbon\Carbon::parse($activity->created_at)->format('H:i') }}</span>
                        </div>
                        <div class="text-gray-400 text-[10px] uppercase">
                            <span class="text-red-400">⚔️ ATACANDO</span> {{ $activity->zone_name }}
                        </div>
                    </div>
                @empty
                    <div class="p-2 text-center text-gray-600 text-[10px] border border-gray-800 bg-black/50">Sin actividad reciente.</div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- COLUMNA CENTRAL: MAPA --}}
        <div class="flex-1 relative flex justify-center w-full order-1 xl:order-2">

            <div class="absolute top-0 left-0 right-0 flex flex-wrap justify-center gap-4 mb-4 text-[10px] md:text-xs tracking-widest uppercase text-gray-400 font-mono z-10 pointer-events-none">
                <div class="flex items-center gap-2 pointer-events-auto bg-black/50 px-2 rounded"><span class="w-2 h-2 border border-cyan-400 bg-transparent"></span> Neutral</div>
                <div class="flex items-center gap-2 pointer-events-auto bg-black/50 px-2 rounded"><span class="w-2 h-2 bg-white shadow-[0_0_5px_white]"></span> Aliado</div>
                <div class="flex items-center gap-2 pointer-events-auto bg-black/50 px-2 rounded"><span class="w-2 h-2 border border-yellow-500 animate-pulse"></span> Conflicto</div>
            </div>

            <div class="relative w-full aspect-video drop-shadow-[0_0_15px_rgba(0,255,255,0.3)]">
                <svg viewBox="0 0 569 392" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto select-none" id="spain-map">
                     @include('game.partials.map-svg')
                </svg>

                {{-- PANEL DE ATAQUE (Tooltip grande) --}}
                <div id="info-panel" class="absolute bottom-4 left-0 right-0 m-auto w-11/12 bg-black/95 border border-cyan-500 p-6 rounded-md hidden backdrop-blur-md transition-all z-40 shadow-2xl">
                    <button onclick="cerrarPanel()" class="absolute top-2 right-4 text-gray-500 hover:text-white text-xl">&times;</button>
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="text-left">
                            <h3 id="panel-title" class="text-2xl font-black italic text-white mb-1 neon-text">ZONA</h3>
                            <p class="text-xs text-gray-400 tracking-widest">PROPIEDAD ACTUAL:</p>
                            <p id="panel-owner" class="text-lg font-bold text-white tracking-widest">NEUTRAL</p>
                        </div>
                        <div class="flex flex-col items-end w-full md:w-auto">
                            <button id="btn-attack" class="btn-cyber w-full md:w-auto px-6 py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 group border border-cyan-500 hover:bg-cyan-900/30 transition-colors text-cyan-300">
                                <span class="group-hover:animate-pulse">⚔️</span>
                                <span id="btn-text">ORDENAR ATAQUE</span>
                            </button>
                            <p id="attack-message" class="mt-2 text-xs font-bold text-center hidden"></p>
                        </div>
                    </div>
                    <div id="battle-stats-container" class="mt-4 pt-4 border-t border-gray-800 hidden">
                        <p class="text-[10px] text-gray-500 uppercase tracking-widest mb-2">SITUACIÓN DE COMBATE</p>
                        <div id="battle-bars" class="space-y-3"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: ESTADÍSTICAS --}}
        <div class="w-full xl:w-1/4 space-y-4 flex-shrink-0 order-3">

            <div class="bg-black/80 border border-cyan-500/30 p-4 rounded backdrop-blur-sm shadow-lg">
                <p class="text-[10px] font-mono tracking-widest mb-2 text-gray-500 border-b border-gray-800 pb-1">ESTADO DEL SISTEMA</p>
                <div class="text-xs font-mono space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">FASE:</span>
                        <span class="{{ $phaseColor }} font-bold uppercase animate-pulse bg-black/50 px-2 py-1 rounded border border-current text-[10px]">{{ $phaseName }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">ENLACE:</span>
                        @if($votingEnabled)
                            <span class="text-green-400 font-bold flex items-center gap-1">ONLINE ●</span>
                        @else
                            <span class="text-red-500 font-bold flex items-center gap-1">OFFLINE ●</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-black/80 border border-cyan-500/30 p-4 rounded backdrop-blur-sm shadow-lg relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 text-6xl opacity-5 group-hover:opacity-10 transition-opacity">⏳</div>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-2">PRÓXIMA RESOLUCIÓN</p>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-2xl animate-pulse text-red-500">⚠️</span>
                    <div class="text-right">
                        <div id="countdown" class="text-2xl font-mono font-bold text-white tracking-widest leading-none">--:--:--</div>
                        <span class="text-[9px] text-gray-600">TIEMPO T-MINUS</span>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-2 flex justify-between items-center">
                    <span class="text-[9px] text-gray-500 uppercase">SECUENCIA</span>
                    <div class="flex items-baseline gap-1 text-xs">
                        <span class="font-bold text-cyan-500">R{{ $currentRound }}</span>
                        <span class="text-gray-600">➜</span>
                        <span class="font-bold text-gray-500">R{{ $nextRound }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-black/80 border border-cyan-500/30 p-3 rounded backdrop-blur-sm shadow-lg max-h-[400px] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center mb-3 border-b border-gray-800 pb-2">
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">DOMINIO GLOBAL</p>
                    <span class="text-[9px] text-cyan-700 bg-cyan-900/20 px-1 rounded">TOP TEAMS</span>
                </div>
                <div class="overflow-y-auto no-scrollbar space-y-1 pr-1 flex-1">
                    @foreach($globalLeaderboard as $rank)
                        <div class="flex justify-between items-center p-2 rounded bg-gradient-to-r from-gray-900/80 to-transparent border-l-2 hover:bg-gray-800 transition-colors"
                             style="border-left-color: {{ $rank->team->color }}">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full shadow-[0_0_5px_currentColor]" style="background-color: {{ $rank->team->color }}; color: {{ $rank->team->color }}"></div>
                                <span class="text-xs font-bold text-gray-300 truncate w-24">{{ $rank->team->name }}</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-sm font-mono font-bold text-white">{{ $rank->total }}</span>
                                <span class="text-[8px] text-gray-600">ZONAS</span>
                            </div>
                        </div>
                    @endforeach
                    @if($globalLeaderboard->isEmpty())
                         <div class="text-center py-4 text-gray-600 text-xs italic">Esperando datos...</div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if(Auth::user()->activeTeam)
        @include('game.partials.chat')
    @endif

@endsection

{{-- TOOLTIP (Fuera del flujo) --}}
<div id="map-tooltip" class="fixed pointer-events-none opacity-0 bg-black/90 border border-white/20 text-white text-xs font-bold px-3 py-1.5 rounded z-50 transition-opacity duration-150 shadow-[0_0_10px_black] uppercase tracking-widest backdrop-blur-sm">
    Cargando...
</div>

@section('scripts')
{{-- Existing JS Logic --}}
<script>
    // --- VARIABLES GLOBALES ---
    const zonesData = @json($zones);
    const teamStats = @json($teamAttackStats ?? []);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ID de mi equipo actual
    const myTeamId = {{ Auth::user()->activeTeam ? Auth::user()->activeTeam->id : 'null' }};
    const myAttackPower = {{ $myPower ?? 0 }};
    const isVotingEnabled = @json($votingEnabled);

    let currentSelectedSlug = null;
    let chatInterval = null;
    const tooltip = document.getElementById('map-tooltip');

    const closingTime = new Date("{{ $nextClose->toIso8601String() }}").getTime();

    // --- FUNCIÓN DEL CONTADOR ---
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = closingTime - now;

        if (distance < 0) {
            const countdownEl = document.getElementById("countdown");
            if(countdownEl) {
                countdownEl.innerHTML = "RESOLVIENDO...";
                countdownEl.classList.add("text-red-500");
            }
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        let text = "";
        if(days > 0) text += days + "d ";
        text += (hours < 10 ? "0"+hours : hours) + "h ";
        text += (minutes < 10 ? "0"+minutes : minutes) + "m ";
        text += (seconds < 10 ? "0"+seconds : seconds) + "s ";

        const el = document.getElementById("countdown");
        if(el) el.innerHTML = text;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    document.addEventListener("DOMContentLoaded", () => {
        // --- CONFIGURACIÓN DEL MAPA ---
        zonesData.forEach(zone => {
            const mapElement = document.getElementById(zone.slug);
            if (mapElement) {
                mapElement.classList.add('zone-path');
                mapElement.style.cursor = 'pointer';
                mapElement.style.transition = 'all 0.3s';

                let fillColor = '#2d3748';
                let strokeColor = '#4a5568';
                let strokeWidth = "0.5px";
                let fillOpacity = "1";
                let isEnemy = false;
                let isMine = false;

                if (zone.team) {
                    if (myTeamId && zone.team.id === myTeamId) {
                        isMine = true;
                        fillColor = zone.team.color;
                        fillOpacity = "0.5";
                        strokeColor = '#ffffff';
                        strokeWidth = "1.5px";
                    } else {
                        isEnemy = true;
                        fillColor = '#7f1d1d';
                        fillOpacity = "0.9";
                        strokeColor = zone.team.color;
                        strokeWidth = "1.0px";
                    }
                }

                mapElement.style.setProperty('fill', fillColor, 'important');
                mapElement.style.setProperty('fill-opacity', fillOpacity, 'important');
                mapElement.style.setProperty('stroke', strokeColor, 'important');
                mapElement.style.setProperty('stroke-width', strokeWidth, 'important');

                const children = mapElement.querySelectorAll('path, polygon, rect');
                if(children.length > 0) {
                    children.forEach(child => {
                        child.style.setProperty('fill', fillColor, 'important');
                        child.style.setProperty('fill-opacity', fillOpacity, 'important');
                        child.style.setProperty('stroke', strokeColor, 'important');
                        child.style.setProperty('stroke-width', strokeWidth, 'important');
                    });
                }

                // Text Labels
                const textLabels = mapElement.querySelectorAll('text, tspan');
                textLabels.forEach(label => {
                    label.style.pointerEvents = 'none';
                    label.style.setProperty('stroke', 'none', 'important');
                    if (isEnemy) {
                        label.style.fill = '#ffe5e5';
                        label.style.textShadow = '0 0 3px #ff0000';
                        label.style.opacity = '1';
                    } else if (isMine) {
                        label.style.fill = '#ffffff';
                        label.style.textShadow = '0 0 5px ' + zone.team.color;
                        label.style.opacity = '1';
                    } else {
                        label.style.fill = '#aaaaaa';
                        label.style.opacity = '0.7';
                        label.style.textShadow = '0 0 2px #000';
                    }
                });

                // Radar
                if(teamStats[zone.id] && teamStats[zone.id].votes > 0) {
                    mapElement.classList.add("animate-pulse");
                    const target = children.length > 0 ? children : [mapElement];
                    target.forEach(el => {
                        el.style.setProperty('stroke', '#FFD700', 'important');
                        el.style.setProperty('stroke-width', '2px', 'important');
                    });
                }

                // Eventos Mouse
                mapElement.addEventListener('mouseenter', function() {
                    tooltip.style.opacity = '1';
                    tooltip.innerText = zone.name.toUpperCase();
                    if (isEnemy) {
                        tooltip.className = "fixed pointer-events-none bg-red-900/95 border border-red-500 text-white text-xs font-bold px-3 py-1.5 rounded z-50 shadow-lg uppercase tracking-widest backdrop-blur-sm";
                        tooltip.innerHTML += ` <span class="text-[9px] block text-red-300 mt-1">OCUPADO POR ${zone.team.name}</span>`;
                    } else if (isMine) {
                        tooltip.className = "fixed pointer-events-none bg-cyan-900/95 border border-cyan-500 text-white text-xs font-bold px-3 py-1.5 rounded z-50 shadow-lg uppercase tracking-widest backdrop-blur-sm";
                        tooltip.innerHTML += ` <span class="text-[9px] block text-cyan-300 mt-1">TU TERRITORIO</span>`;
                    } else {
                        tooltip.className = "fixed pointer-events-none bg-black/90 border border-gray-600 text-gray-300 text-xs font-bold px-3 py-1.5 rounded z-50 shadow-lg uppercase tracking-widest backdrop-blur-sm";
                        tooltip.innerHTML += ` <span class="text-[9px] block text-gray-500 mt-1">NEUTRAL</span>`;
                    }
                });

                mapElement.addEventListener('mousemove', function(e) {
                    tooltip.style.top = (e.clientY - 50) + 'px';
                    tooltip.style.left = (e.clientX + 15) + 'px';
                });

                mapElement.addEventListener('mouseleave', function() {
                    tooltip.style.opacity = '0';
                });

                mapElement.addEventListener('click', function(e) {
                    e.stopPropagation();
                    abrirPanel(zone.slug);
                });
            }
        });

        // Listeners Globales
        const btnAttack = document.getElementById('btn-attack');
        if(btnAttack) {
            btnAttack.addEventListener('click', function() {
                if(!currentSelectedSlug) return;
                enviarVoto(this);
            });
        }

        const chatInput = document.getElementById('chat-input');
        if(chatInput){
            chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') enviarMensaje(); });
            cargarChat();
            chatInterval = setInterval(cargarChat, 4000);
        }
    });

    // --- FUNCIONES DE INTERFAZ ---
    function abrirPanel(slug) {
        currentSelectedSlug = slug;
        const zoneInfo = zonesData.find(z => z.slug === slug);
        if(!zoneInfo) return;

        document.getElementById('info-panel').classList.remove('hidden');
        const statsContainer = document.getElementById('battle-stats-container');
        const barsContainer = document.getElementById('battle-bars');

        barsContainer.innerHTML = '';
        statsContainer.classList.add('hidden');

        const titleEl = document.getElementById('panel-title');
        const ownerEl = document.getElementById('panel-owner');
        const btn = document.getElementById('btn-attack');
        const btnText = document.getElementById('btn-text');

        titleEl.innerText = zoneInfo.name.toUpperCase();

        let ownerName = "TERRITORIO NEUTRAL";
        let ownerColor = "#ffffff";
        let titleClass = "text-white";

        if(zoneInfo.team) {
            ownerName = zoneInfo.team.name;
            if(myTeamId && zoneInfo.team.id === myTeamId) {
                ownerColor = zoneInfo.team.color;
                titleClass = "text-cyan-400";
            } else {
                ownerColor = "#ff5555";
                titleClass = "text-red-500 neon-text-red";
            }
        }

        titleEl.className = `text-2xl font-black italic mb-1 ${titleClass}`;
        ownerEl.innerText = ownerName;
        ownerEl.style.color = ownerColor;

        resetButton();

        const zoneBattleData = teamStats[zoneInfo.id];
        if (zoneBattleData && zoneBattleData.teams && zoneBattleData.teams.length > 0) {
            statsContainer.classList.remove('hidden');
            zoneBattleData.teams.sort((a, b) => b.votes - a.votes);
            zoneBattleData.teams.forEach(t => {
                let percent = (t.votes / zoneBattleData.total_votes) * 100;
                const barHTML = `
                    <div class="group">
                        <div class="flex justify-between text-[10px] uppercase font-bold mb-1">
                            <span style="color:${t.color}">${t.name}</span>
                            <span class="text-gray-400">${t.votes} DAÑO</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-900 rounded-full overflow-hidden border border-white/5">
                            <div class="h-full shadow-[0_0_10px_currentColor]" style="width: ${percent}%; background-color: ${t.color}; box-shadow: 0 0 5px ${t.color}; transition: width 1s ease-out;"></div>
                        </div>
                    </div>`;
                barsContainer.innerHTML += barHTML;
            });
        } else {
             statsContainer.classList.remove('hidden');
             barsContainer.innerHTML = '<p class="text-gray-600 text-[10px] italic text-center">Zona en calma. Sin actividad hostil reciente.</p>';
        }

        if (!isVotingEnabled) {
            btn.disabled = true;
            btnText.innerText = "⛔ FASE CONQUISTA (CERRADO)";
            btn.className = "w-full py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 border border-red-500/30 text-red-500/50 cursor-not-allowed bg-red-900/10 rounded";
            return;
        }

        if (myTeamId && zoneInfo.team_id === myTeamId) {
            btn.disabled = true;
            btnText.innerText = "🛡️ TERRITORIO ASEGURADO";
            btn.className = "w-full py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 border border-green-500/30 text-green-500/50 cursor-not-allowed bg-green-900/10 rounded";
        } else {
            const myAttack = zoneBattleData?.teams?.find(t => t.id === myTeamId);
            btn.className = "btn-cyber w-full md:w-auto px-6 py-3 font-bold text-sm md:text-lg flex justify-center items-center gap-2 group border border-cyan-500 hover:bg-cyan-900/30 transition-colors text-cyan-300";
            if(myAttack) {
                 btn.classList.add('border-yellow-500', 'text-yellow-400');
                 btn.classList.remove('border-cyan-500', 'text-cyan-300');
            }
        }
    }

    function cerrarPanel() { document.getElementById('info-panel').classList.add('hidden'); }

    function resetButton() {
        const btn = document.getElementById('btn-attack');
        const msg = document.getElementById('attack-message');
        if(btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-green-900/10', 'border-green-500/30', 'text-green-500/50');
        }
        if(msg) msg.classList.add('hidden');
    }

    function enviarVoto(btn) {
        const msg = document.getElementById('attack-message');
        const btnText = document.getElementById('btn-text');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btnText.innerText = "ENVIANDO...";

        fetch("{{ route('conquest.vote') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ zone_slug: currentSelectedSlug })
        })
        .then(response => response.json())
        .then(data => {
            msg.classList.remove('hidden');
            if(data.success) {
                msg.className = "mt-2 text-xs font-bold text-center text-green-400 animate-pulse";
                msg.innerText = "ORDEN RECIBIDA. ACTUALIZANDO SATÉLITE...";
                btnText.innerText = "✅ RECARGANDO...";
                setTimeout(() => { window.location.reload(); }, 1000);
            } else {
                msg.className = "mt-2 text-xs font-bold text-center text-red-400";
                msg.innerText = data.error || "Error al procesar";
                resetButton();
                btnText.innerText = "REINTENTAR";
            }
        })
        .catch(error => {
            console.error(error);
            resetButton();
            btnText.innerText = "ERROR DE RED";
        });
    }

    // --- FUNCIONES DEL CHAT ---
    function toggleChat() {
        const chat = document.getElementById('team-chat');
        if(chat) chat.classList.toggle('translate-y-[calc(100%-40px)]');
        const arrow = document.getElementById('chat-arrow');
        if(arrow) arrow.classList.toggle('rotate-180');
    }

    function cargarChat() {
        fetch("{{ route('chat.fetch') }}")
        .then(r => r.json())
        .then(data => {
             const chatBox = document.getElementById('chat-messages');
             if(!chatBox) return;
             chatBox.innerHTML = '';
             if(data.length === 0) {
                 chatBox.innerHTML = '<p class="text-gray-600 text-center italic mt-4">Canal silencioso...</p>';
                 return;
             }
             data.forEach(msg => {
                 const isMe = msg.user.name === "{{ Auth::user()->name }}";
                 const color = isMe ? 'text-cyan-400' : 'text-yellow-500';
                 const align = isMe ? 'text-right' : 'text-left';
                 const bg = isMe ? 'bg-cyan-900/20' : 'bg-transparent';
                 chatBox.innerHTML += `
                    <div class="mb-1 p-1 rounded ${align} ${bg}">
                        <span class="font-bold ${color} text-[10px] uppercase block">${msg.user.name}</span>
                        <span class="text-gray-300 break-words">${msg.message}</span>
                    </div>`;
             });
             chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(console.error);
    }

    function enviarMensaje() {
        const input = document.getElementById('chat-input');
        const txt = input.value.trim();
        if(!txt) return;
        fetch("{{ route('chat.send') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ message: txt })
        }).then(() => {
            input.value = '';
            cargarChat();
        });
    }

    // --- FUNCIONES MOTD (Capitán) ---
    // Make these functions global so `onclick` in HTML works
    window.toggleMotdEdit = function() {
        const display = document.getElementById('pinned-display');
        const edit = document.getElementById('pinned-edit');
        if(display && edit) {
            display.classList.toggle('hidden');
            edit.classList.toggle('hidden');
        }
    };

    window.saveMotd = function() {
        const input = document.getElementById('motd-input');
        const msg = input.value;
        fetch("{{ route('chat.update_motd') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ message: msg })
        })
        .then(r => r.json())
        .then(data => {
            if(data.success) {
                document.getElementById('motd-text').innerText = msg;
                toggleMotdEdit();
            } else {
                alert(data.error || 'Error');
            }
        });
    };
</script>
@endsection

@section('styles')
<style>
    /* Asegura que la transición funcione incluso forzando propiedades */
    #spain-map path, #spain-map polygon, #spain-map g {
        transition: fill 0.3s ease, fill-opacity 0.3s ease, stroke 0.3s ease !important;
    }
    #spain-map g:hover path, #spain-map path:hover {
        filter: brightness(1.2); /* Efecto de brillo al pasar el ratón */
    }
    /* Ocultar scrollbar pero permitir scroll */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    /* Animación suave de entrada para los logs */
    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-fade-in-left {
        animation: fadeInLeft 0.3s ease-out forwards;
    }

    /* Máscara para que la lista se desvanezca abajo suavemente */
    .mask-gradient-bottom {
        mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
        -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
    }
</style>
@endsection
