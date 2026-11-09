<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV ESTADIO - {{ $event->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #030712; color: #fff; overflow: hidden; }
        .tv-grid {
            background-color: #030712;
            background-image:
                linear-gradient(to right, rgba(31, 41, 55, 0.25) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(31, 41, 55, 0.25) 1px, transparent 1px);
            background-size: 36px 36px;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes pulseBorder { 0%, 100% { border-color: rgba(6,182,212,0.9); box-shadow: 0 0 18px rgba(6,182,212,0.25); } 50% { border-color: rgba(6,182,212,0.25); box-shadow: none; } }
        .match-live { animation: pulseBorder 2s infinite; }
    </style>
</head>
<body class="tv-grid h-screen w-screen flex flex-col p-5 select-none">

    <!-- CABECERA DEL ESTADIO -->
    <header class="flex justify-between items-center bg-gray-900/95 border border-gray-800 rounded-2xl px-6 py-3.5 mb-5 shadow-2xl shrink-0">
        <!-- Izquierda: Logo y Evento -->
        <div class="flex items-center gap-4">
            <img src="/images/logo_new.png" alt="SBBL" class="h-14 w-auto drop-shadow-[0_0_10px_rgba(6,182,212,0.3)]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> EN VIVO
                    </span>
                    <span class="text-xs font-mono text-cyan-400 font-bold uppercase tracking-widest">{{ $event->region->name ?? 'SBBL' }} · {{ $event->city }}</span>
                    <span id="tv-phase-badge" class="text-[10px] font-mono bg-gray-800 text-gray-300 border border-gray-700 px-2 py-0.5 rounded uppercase font-bold">SUIZO</span>
                </div>
                <h1 class="text-2xl xl:text-3xl font-black uppercase tracking-wider text-white mt-0.5 truncate max-w-[420px] xl:max-w-[580px]">{{ $event->name }}</h1>
            </div>
        </div>

        <!-- Centro: Reloj Local -->
        <div class="bg-gray-950 border border-gray-800 rounded-2xl px-8 py-2 flex flex-col justify-center items-center shadow-inner">
            <div class="text-[9px] font-mono text-gray-500 font-bold uppercase tracking-widest">HORA LOCAL</div>
            <div id="tv-clock-display" class="text-3xl xl:text-4xl font-mono font-black text-white tracking-wider leading-none mt-0.5 drop-shadow-[0_0_8px_rgba(255,255,255,0.3)]">--:--:--</div>
        </div>

        <!-- Derecha: Ronda, Progreso, QR y Pantalla Completa -->
        <div class="flex items-center gap-5">
            <div class="text-right">
                <div class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">RONDA</div>
                <div id="tv-round-badge" class="text-3xl font-black font-mono text-yellow-400 leading-none mt-0.5">R--</div>
            </div>
            <div class="text-right border-l border-gray-800 pl-5">
                <div class="text-[10px] font-mono text-gray-400 uppercase tracking-widest">COMPLETADAS</div>
                <div id="tv-progress-text" class="text-2xl font-black font-mono text-cyan-400 leading-none mt-1">-- / --</div>
            </div>
            <div class="flex items-center gap-2.5 bg-gray-950 border border-gray-800 p-1.5 rounded-xl">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode(route('torneo.bracket', $event->id)) }}" class="w-14 h-14 rounded bg-white p-1" alt="QR Cuadro">
                <div class="pr-2">
                    <div class="text-[9px] font-mono text-cyan-400 font-bold uppercase">CUADRO Y TABLA</div>
                    <div class="text-[11px] font-black text-white uppercase leading-tight">ESCANEA EN<br>TU MÓVIL</div>
                </div>
            </div>
            <button onclick="toggleFullScreen()" class="bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white border border-gray-700 w-10 h-10 rounded-xl flex items-center justify-center transition-colors shadow-lg" title="Pantalla Completa">
                <i class="fas fa-expand"></i>
            </button>
        </div>
    </header>

    <!-- CUERPO DIVIDIDO: MESAS DE LA RONDA (IZQUIERDA 67%) + CLASIFICACIÓN DETALLADA (DERECHA 33%) -->
    <div class="flex-1 grid grid-cols-12 gap-5 overflow-hidden">

        <!-- COLUMNA IZQUIERDA: MESAS Y PARTIDAS -->
        <div class="col-span-8 bg-gray-900/80 border border-gray-800 rounded-2xl p-5 flex flex-col overflow-hidden shadow-2xl">
            <div class="flex justify-between items-center mb-3 pb-2.5 border-b border-gray-800 shrink-0">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-mono font-black uppercase tracking-widest text-cyan-400">
                        <i class="fas fa-bolt mr-1.5"></i>EMPAREJAMIENTOS DE LA RONDA
                    </h2>
                    <span id="tv-bye-banner" class="hidden bg-cyan-950/90 border border-cyan-600/60 text-cyan-300 text-xs font-mono font-bold px-2.5 py-0.5 rounded-lg"></span>
                </div>
                <span id="tv-sync-status" class="text-xs font-mono text-gray-500">Sincronizado</span>
            </div>

            <div id="tv-matches-scroll" class="overflow-y-auto no-scrollbar flex-1 pr-1">
                <div id="tv-matches-grid" class="grid grid-cols-2 gap-3 content-start"></div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: CLASIFICACIÓN EN VIVO CON DESEMPATES -->
        <div class="col-span-4 bg-gray-900/80 border border-gray-800 rounded-2xl p-5 flex flex-col overflow-hidden shadow-2xl">
            <div class="flex justify-between items-center mb-3 pb-2.5 border-b border-gray-800 shrink-0">
                <h2 class="text-sm font-mono font-black uppercase tracking-widest text-yellow-400">
                    <i class="fas fa-trophy mr-1.5"></i>CLASIFICACIÓN EN VIVO
                </h2>
                <span class="text-[10px] font-mono text-gray-400 uppercase">V-D · PTS · BCH · DIF</span>
            </div>

            <div class="overflow-y-auto no-scrollbar flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="text-[10px] font-mono text-gray-500 uppercase border-b border-gray-800">
                        <tr>
                            <th class="py-2 pl-2 w-8">#</th>
                            <th class="py-2">BLADER</th>
                            <th class="py-2 text-center">V-D</th>
                            <th class="py-2 text-center text-yellow-400">PTS</th>
                            <th class="py-2 text-center text-cyan-400">BCH</th>
                            <th class="py-2 pr-2 text-right">DIF</th>
                        </tr>
                    </thead>
                    <tbody id="tv-standings-body" class="divide-y divide-gray-800/70"></tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        const eventId = {{ $event->id }};

        // --- RELOJ LOCAL EN TIEMPO REAL ---
        function actualizarReloj() {
            const ahora = new Date();
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const segundos = String(ahora.getSeconds()).padStart(2, '0');
            document.getElementById('tv-clock-display').innerText = `${horas}:${minutos}:${segundos}`;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();

        // --- PANTALLA COMPLETA ---
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen();
            }
        }

        // --- SINCRONIZACIÓN EN VIVO CON LA ARENA ---
        async function actualizarPantallaEstadio() {
            try {
                const [resArena, resTab] = await Promise.all([
                    fetch(`/api/torneos/${eventId}/arena`),
                    fetch(`/api/torneos/${eventId}/clasificacion`)
                ]);
                const dataArena = await resArena.json();
                const dataTab = await resTab.json();

                document.getElementById('tv-round-badge').innerText = dataArena.max_round > 0 ? `R${dataArena.current_round}` : '--';
                document.getElementById('tv-phase-badge').innerText = dataArena.is_bracket ? 'FASE TOP CUT' : (dataArena.is_team_event ? 'CREW BATTLE' : 'FASE SUIZA');

                // Banner de BYE si la ronda es impar
                const byeBanner = document.getElementById('tv-bye-banner');
                if (dataArena.bye_player) {
                    byeBanner.innerHTML = `<i class="fas fa-forward mr-1"></i> BYE R${dataArena.current_round}: ${dataArena.bye_player.toUpperCase()} (+3 PTS)`;
                    byeBanner.classList.remove('hidden');
                } else {
                    byeBanner.classList.add('hidden');
                }

                const matches = dataArena.matches || [];
                const done = matches.filter(m => m.status === 'completed' || m.status === 'cancelled').length;
                document.getElementById('tv-progress-text').innerText = `${done} / ${matches.length}`;

                const horaSync = new Date();
                const horaFormat = String(horaSync.getHours()).padStart(2, '0') + ':' + String(horaSync.getMinutes()).padStart(2, '0');
                document.getElementById('tv-sync-status').innerText = `Sincronizado ${horaFormat}`;

                // Ordenar: 1º En Juego (locked_by), 2º Pendientes, 3º Completadas
                const sorted = [...matches].sort((a, b) => {
                    const p = m => m.status === 'completed' ? 3 : (m.locked_by ? 1 : 2);
                    return p(a) - p(b);
                });

                const grid = document.getElementById('tv-matches-grid');
                if (sorted.length === 0) {
                    grid.innerHTML = `<div class="col-span-2 text-center py-24 text-gray-500 font-mono text-base uppercase tracking-widest">Esperando generación de emparejamientos...</div>`;
                } else {
                    grid.innerHTML = sorted.map((m, idx) => {
                        const isTeam = m.team1_id != null;
                        const p1 = isTeam ? (m.team1?.name || '---') : (m.player1?.name || m.p1_placeholder || '---');
                        const p2 = isTeam ? (m.team2?.name || '---') : (m.player2?.name || m.p2_placeholder || '---');
                        const isComp = m.status === 'completed';
                        const isPlaying = !isComp && m.locked_by;

                        const w1 = isComp && m.winner_id && (m.winner_id == m.player1_id || m.winner_id == m.team1_id);
                        const w2 = isComp && m.winner_id && (m.winner_id == m.player2_id || m.winner_id == m.team2_id);

                        const cardStyle = isPlaying
                            ? 'bg-cyan-950/30 border-2 border-cyan-400 match-live'
                            : (isComp ? 'bg-gray-950/50 border border-gray-800/70 opacity-65' : 'bg-gray-950 border border-gray-800');

                        const statusTag = isPlaying
                            ? `<span class="text-[10px] font-mono font-black bg-cyan-400 text-gray-950 px-2 py-0.5 rounded uppercase">● EN JUEGO</span>`
                            : (isComp ? `<span class="text-[10px] font-mono font-bold text-emerald-400 uppercase"><i class="fas fa-check mr-1"></i>FINALIZADA</span>` : `<span class="text-[10px] font-mono font-bold text-amber-400 uppercase">PENDIENTE</span>`);

                        return `
                            <div class="${cardStyle} rounded-xl p-3 flex flex-col justify-between">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-mono font-black text-gray-400">PARTIDA #${m.match_number || (idx + 1)}</span>
                                    ${statusTag}
                                </div>
                                <div class="flex justify-between items-center bg-gray-900/95 rounded-lg px-3.5 py-2.5 border border-gray-800">
                                    <span class="font-black text-sm xl:text-base uppercase truncate max-w-[40%] ${w1 ? 'text-yellow-400' : 'text-white'}">${p1}</span>
                                    <span class="font-mono text-xl xl:text-2xl font-black text-cyan-400 px-2 shrink-0">${m.player1_score} - ${m.player2_score}</span>
                                    <span class="font-black text-sm xl:text-base uppercase truncate max-w-[40%] text-right ${w2 ? 'text-yellow-400' : 'text-white'}">${p2}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                }

                // Renderizar Clasificación Detallada (Top 16)
                const standings = (dataTab.clasificacion || []).slice(0, 16);
                const stBody = document.getElementById('tv-standings-body');
                stBody.innerHTML = standings.map((pl, i) => {
                    const wins = pl.wins ?? 0;
                    const losses = pl.losses ?? 0;
                    const diff = pl.point_diff ?? 0;
                    const diffStr = diff > 0 ? `+${diff}` : `${diff}`;
                    const diffColor = diff > 0 ? 'text-emerald-400' : (diff < 0 ? 'text-rose-400' : 'text-gray-500');
                    const h2hTag = pl.h2h_wins > 0 ? `<span class="ml-1 text-[8px] bg-cyan-950 text-cyan-300 border border-cyan-700/60 px-1 py-0.5 rounded font-mono">H2H</span>` : '';

                    return `
                        <tr class="${i < 4 ? 'bg-cyan-950/15' : ''}">
                            <td class="py-2.5 pl-2 font-mono font-black text-sm ${i === 0 ? 'text-yellow-400' : (i < 4 ? 'text-cyan-400' : 'text-gray-500')}">#${i + 1}</td>
                            <td class="py-2.5 font-bold text-white uppercase truncate max-w-[140px]">${pl.name}${h2hTag}</td>
                            <td class="py-2.5 text-center font-mono text-xs">
                                <span class="text-emerald-400 font-bold">${wins}</span><span class="text-gray-600">-</span><span class="text-rose-400 font-bold">${losses}</span>
                            </td>
                            <td class="py-2.5 text-center font-mono font-black text-sm text-yellow-400">${pl.points}</td>
                            <td class="py-2.5 text-center font-mono font-bold text-xs text-cyan-300">${pl.buchholz}</td>
                            <td class="py-2.5 pr-2 text-right font-mono font-bold text-xs ${diffColor}">${diffStr}</td>
                        </tr>
                    `;
                }).join('');

            } catch (e) {
                document.getElementById('tv-sync-status').innerText = 'Reconectando...';
            }
        }

        actualizarPantallaEstadio();
        setInterval(actualizarPantallaEstadio, 8000);
    </script>
</body>
</html>
