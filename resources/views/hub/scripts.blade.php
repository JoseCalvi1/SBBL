<!-- El Toast lleva style="pointer-events: none !important;" para que JAMÁS bloquee la botonera superior -->
<div id="toast" style="pointer-events: none !important;" class="fixed top-16 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-3 rounded-lg shadow-2xl z-[120] font-bold border-l-4 transition-opacity duration-300 opacity-0 pointer-events-none text-sm w-11/12 max-w-sm text-center">Mensaje</div>

<script>
    let dbPiezas = null;
    let allTeamsList = [];
    let currentEventType = null;
    
    let currentArenaEventId = null;
    let currentArenaRound = null;
    let currentArenaMode = 'judge';
    let currentArenaTab = 'cruces';
    let currentRoundMatchesCache = [];
    let currentMatchFilter = 'all';

    // --- GENERADOR DE BANDERA / IDENTIFICADOR POR REGIÓN ---
    function obtenerEstiloRegion(regionName) {
        if (!regionName) return { flag: 'linear-gradient(180deg, #ef4444 25%, #facc15 25%, #facc15 75%, #ef4444 75%)', border: 'border-gray-600', text: 'text-gray-300', bg: 'bg-gray-900/90' };
        const r = regionName.toUpperCase();
        if (r.includes('ANDALUC')) return { flag: 'linear-gradient(180deg, #16a34a 33%, #ffffff 33%, #ffffff 66%, #16a34a 66%)', border: 'border-green-500/60', text: 'text-green-400', bg: 'bg-green-950/50' };
        if (r.includes('MADRID')) return { flag: 'linear-gradient(135deg, #dc2626 50%, #ffffff 50%)', border: 'border-red-500/60', text: 'text-red-400', bg: 'bg-red-950/50' };
        if (r.includes('CATALU') || r.includes('VALENCIA') || r.includes('ARAG') || r.includes('BALEAR')) return { flag: 'repeating-linear-gradient(180deg, #facc15, #facc15 3px, #dc2626 3px, #dc2626 6px)', border: 'border-yellow-500/60', text: 'text-yellow-400', bg: 'bg-yellow-950/50' };
        if (r.includes('GALICIA')) return { flag: 'linear-gradient(135deg, #ffffff 40%, #38bdf8 40%, #38bdf8 60%, #ffffff 60%)', border: 'border-sky-400/60', text: 'text-sky-300', bg: 'bg-sky-950/50' };
        if (r.includes('VASCO') || r.includes('EUSKADI')) return { flag: 'linear-gradient(135deg, #dc2626 35%, #16a34a 35%, #16a34a 65%, #ffffff 65%)', border: 'border-emerald-500/60', text: 'text-emerald-400', bg: 'bg-emerald-950/50' };
        if (r.includes('CANARIAS')) return { flag: 'linear-gradient(90deg, #ffffff 33%, #3b82f6 33%, #3b82f6 66%, #facc15 66%)', border: 'border-blue-400/60', text: 'text-blue-300', bg: 'bg-blue-950/50' };
        if (r.includes('CASTILLA')) return { flag: 'linear-gradient(90deg, #dc2626 50%, #eab308 50%)', border: 'border-amber-500/60', text: 'text-amber-400', bg: 'bg-amber-950/50' };
        if (r.includes('MURCIA') || r.includes('RIOJA') || r.includes('NAVARRA')) return { flag: 'linear-gradient(180deg, #991b1b 50%, #dc2626 50%)', border: 'border-rose-500/60', text: 'text-rose-400', bg: 'bg-rose-950/50' };
        if (r.includes('ASTURIAS')) return { flag: 'linear-gradient(180deg, #2563eb 45%, #facc15 45%, #facc15 55%, #2563eb 55%)', border: 'border-blue-500/60', text: 'text-blue-400', bg: 'bg-blue-950/50' };
        if (r.includes('EXTREMADURA')) return { flag: 'linear-gradient(180deg, #16a34a 33%, #ffffff 33%, #ffffff 66%, #111827 66%)', border: 'border-emerald-500/60', text: 'text-emerald-400', bg: 'bg-emerald-950/50' };
        return { flag: 'linear-gradient(135deg, #06b6d4 50%, #3b82f6 50%)', border: 'border-cyan-500/60', text: 'text-cyan-300', bg: 'bg-cyan-950/50' };
    }

    function triggerHaptic(type = 'light') {
        if (!navigator.vibrate) return;
        if (type === 'light') navigator.vibrate(15);
        else if (type === 'medium') navigator.vibrate(40);
        else if (type === 'heavy') navigator.vibrate([30, 50, 30]);
        else if (type === 'error') navigator.vibrate([50, 100, 50, 100, 50]);
    }

    function mostrarToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.innerText = msg;
        toast.className = `fixed top-16 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded-lg shadow-2xl z-[120] font-bold border-l-4 transition-opacity duration-300 text-sm w-11/12 max-w-sm text-center pointer-events-none ${isError ? 'bg-gray-800 text-red-400 border-red-500' : 'bg-gray-800 text-cyan-400 border-cyan-500'}`;
        toast.style.opacity = 1;
        setTimeout(() => toast.style.opacity = 0, 3000);
    }

    // --- SISTEMA DE SWIPE ---
    const viewNames = ['eventos', 'ranking', 'duelos', 'meta', 'reglas'];
    const viewColors = ['text-cyan-400', 'text-yellow-400', 'text-red-400', 'text-purple-400', 'text-green-400'];
    let touchStartX = 0; let touchEndX = 0;

    document.getElementById('main-content').addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, {passive: true});
    document.getElementById('main-content').addEventListener('touchend', e => { touchEndX = e.changedTouches[0].screenX; handleSwipe(); }, {passive: true});

    function handleSwipe() {
        const swipeThreshold = 70;
        const activeView = document.querySelector('.view-section.active');
        if (!activeView) return;
        const currentIndex = parseInt(activeView.getAttribute('data-index'));
        if (touchStartX - touchEndX > swipeThreshold && currentIndex < viewNames.length - 1) { triggerHaptic('light'); navigateViews(currentIndex, currentIndex + 1, 'slide-left'); } 
        else if (touchEndX - touchStartX > swipeThreshold && currentIndex > 0) { triggerHaptic('light'); navigateViews(currentIndex, currentIndex - 1, 'slide-right'); }
    }

    function navigateViews(fromIndex, toIndex, animationClass) {
        const targetViewName = viewNames[toIndex];
        const btnElement = document.querySelector(`.nav-btn[data-target="${targetViewName}"]`);
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active', 'slide-left', 'slide-right'));
        document.getElementById('view-' + targetViewName).classList.add('active', animationClass);
        actualizarBotonesNav(btnElement, targetViewName, toIndex);
        document.getElementById('main-content').scrollTop = 0;
    }

    function switchView(viewName, btnElement) {
        triggerHaptic('light');
        const activeView = document.querySelector('.view-section.active');
        const fromIndex = parseInt(activeView.getAttribute('data-index'));
        const toIndex = viewNames.indexOf(viewName);
        if (fromIndex === toIndex) return;
        const animationClass = fromIndex < toIndex ? 'slide-left' : 'slide-right';
        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active', 'slide-left', 'slide-right'));
        document.getElementById('view-' + viewName).classList.add('active', animationClass);
        actualizarBotonesNav(btnElement, viewName, toIndex);
        document.getElementById('main-content').scrollTop = 0;
    }

    function actualizarBotonesNav(btnElement, viewName, toIndex) {
        document.querySelectorAll('.nav-btn').forEach(btn => btn.className = 'nav-btn text-gray-500 flex flex-col items-center justify-center w-full h-full transition-colors');
        btnElement.className = `nav-btn ${viewColors[toIndex]} flex flex-col items-center justify-center w-full h-full transition-colors`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        cargarRegiones(); cargarEventos(); cargarRankingIndividual(); cargarRankingEquipos(); cargarDuelos(); cargarMeta(); cargarPiezas();
    });

    function switchRankTab(type) {
        triggerHaptic('light');
        document.getElementById('tab-individual').classList.remove('active', 'bg-gray-800'); document.getElementById('tab-equipos').classList.remove('active', 'bg-gray-800');
        document.getElementById('lista-ranking-individual').classList.add('hidden'); document.getElementById('lista-ranking-equipos').classList.add('hidden');
        document.getElementById(`tab-${type}`).classList.add('active', 'bg-gray-800'); document.getElementById(`lista-ranking-${type}`).classList.remove('hidden');
    }

    // --- ARENA PRINCIPAL ---
    async function cargarRegiones() {
        try {
            const res = await fetch('/api/regiones'); const regiones = await res.json();
            const selectRegion = document.getElementById('filtro-region');
            regiones.forEach(r => selectRegion.add(new Option(r.name.toUpperCase(), r.id)));
        } catch (error) { console.error(error); }
    }

    async function cargarEventos() {
        triggerHaptic('light');
        const contenedor = document.getElementById('lista-eventos');
        const tipo = document.getElementById('filtro-tipo').value;
        const region = document.getElementById('filtro-region').value;
        const selectPersonal = document.getElementById('filtro-personal');
        const personal = selectPersonal ? selectPersonal.value : '';

        contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-10 animate-pulse">Sincronizando estadio...</p>';
        
        try {
            const res = await fetch(`/api/torneos?tipo=${tipo}&region=${region}&personal=${personal}`);
            if (!res.ok) throw new Error('Error en torneos');
            const torneos = await res.json();
            
            if (torneos.length === 0) {
                contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-5 font-bold tracking-widest">NO HAY EVENTOS.</p>';
                return;
            }
            
            let htmlTemp = '';
            torneos.forEach(torneo => {
                const fecha = new Date(torneo.date).toLocaleDateString('es-ES');
                const hora = torneo.time ? torneo.time.substring(0, 5) : '--:--';
                
                const isRank = torneo.beys === 'ranking' || torneo.beys === 'rankingplus';
                const badge = isRank ? 'bg-red-950/80 text-red-400 border-red-700/50' : 'bg-cyan-950/80 text-cyan-400 border-cyan-700/50';
                const cardTheme = torneo.is_inscribed ? 'bg-cyan-900/20 border-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.15)]' : 'bg-gray-900 border-gray-700 shadow-lg';
                const limitText = torneo.aforo_maximo > 1000 ? '∞' : torneo.aforo_maximo;
                const aforoLleno = torneo.total_inscritos >= torneo.aforo_maximo;
                const colorAforo = aforoLleno ? 'text-red-400' : 'text-cyan-400';

                // Identificador de Región (Bandera + Color)
                const regionName = torneo.region ? torneo.region.name : 'ESPAÑA';
                const cityName = torneo.city || torneo.location || 'SEDE OFICIAL';
                const regStyle = obtenerEstiloRegion(regionName);

                let botonesHTML = '';
                let panelJuez = '';

                if (torneo.status === 'COMPLETED' || torneo.status === 'CLOSED' || torneo.status === 'PENDING' || torneo.status === 'REVIEW' || torneo.status === 'INVALID') {
                    if (torneo.user_is_revisor || torneo.is_judge_assigned) {
                        botonesHTML = `
                            <div class="flex gap-2 relative z-10">
                                <button onclick="abrirArenaPublica(${torneo.id})" class="flex-1 bg-fuchsia-900/80 hover:bg-fuchsia-800 border border-fuchsia-500 text-fuchsia-300 font-bold py-3 rounded-lg text-xs tracking-wide transition-colors"><i class="fas fa-eye mr-1"></i> VER RESULTADOS</button>
                                <button onclick="abrirPanelArbitraje(${torneo.id})" class="bg-gray-800 hover:bg-gray-700 border border-yellow-600/60 text-yellow-400 font-bold px-3.5 py-3 rounded-lg text-xs transition-colors" title="Modo Edición / Revisor"><i class="fas fa-edit"></i></button>
                            </div>`;
                    } else {
                        botonesHTML = `<button onclick="abrirArenaPublica(${torneo.id})" class="w-full bg-fuchsia-900/80 hover:bg-fuchsia-800 border border-fuchsia-500 text-fuchsia-400 font-bold py-3 rounded-lg text-sm tracking-wide shadow-[0_0_15px_rgba(192,38,211,0.3)] transition-colors relative z-10"><i class="fas fa-eye mr-2"></i> VER RESULTADOS / ARENA</button>`;
                    }
                } 
                else if (torneo.status === 'LIVE') {
                    if (torneo.is_judge_assigned || torneo.user_is_revisor) {
                        botonesHTML = `<button onclick="abrirPanelArbitraje(${torneo.id})" class="w-full bg-yellow-600 hover:bg-yellow-500 active:bg-yellow-700 border border-yellow-400 text-white font-black py-3 rounded-lg text-sm tracking-wider shadow-[0_0_15px_rgba(234,179,8,0.4)] transition-colors relative z-10"><i class="fas fa-tablet-alt mr-2"></i> PANEL DE ARENA</button>`;
                    } else {
                        botonesHTML = `<div class="flex gap-2 relative z-10">
                            <button onclick="abrirArenaPublica(${torneo.id})" class="flex-1 bg-fuchsia-700 active:bg-fuchsia-600 border border-fuchsia-500 text-white font-bold py-3 rounded-lg text-sm tracking-wide shadow-[0_0_15px_rgba(192,38,211,0.3)] transition-colors"><i class="fas fa-eye mr-2"></i> VER ARENA</button>
                            ${torneo.is_inscribed ? `<button onclick="abrirModal(${torneo.id}, '${torneo.status}', null, null, '${torneo.beys}')" class="w-14 bg-cyan-900/50 border border-cyan-500 text-cyan-400 font-bold py-3 rounded-lg text-sm transition-colors flex items-center justify-center"><i class="fas fa-layer-group"></i></button>` : ''}
                        </div>`;
                    }

                    if ((torneo.user_is_judge && torneo.is_judge_assigned) || torneo.user_is_revisor) {
                        panelJuez = `
                            <div class="mt-4 pt-3 border-t border-gray-700/50 flex gap-2 relative z-10">
                                <button onclick="copiarNombres(${torneo.id})" class="flex-1 bg-gray-800 border border-gray-600 text-gray-300 hover:text-white font-bold py-2 rounded-lg text-xs transition-colors shadow-sm" title="Copiar Lista"><i class="fas fa-copy text-cyan-500"></i></button>
                                <button onclick='abrirModalEventManager(${JSON.stringify(torneo).replace(/'/g, "&apos;")})' class="flex-1 bg-yellow-900/50 border border-yellow-700 text-yellow-500 hover:text-white font-bold py-2 rounded-lg text-xs transition-colors shadow-sm" title="Editar Evento"><i class="fas fa-pen"></i></button>
                            </div>
                        `;
                    }
                } 
                else {
                    if (torneo.is_inscribed) {
                        botonesHTML = `<div class="flex gap-2 relative z-10"><button onclick="abrirModal(${torneo.id}, '${torneo.status}', null, null, '${torneo.beys}')" class="flex-1 bg-cyan-700 active:bg-cyan-600 border border-cyan-400 text-white font-bold py-3 rounded-lg text-sm tracking-wide transition-colors"><i class="fas fa-layer-group me-1"></i> MI DECK</button><button onclick="cancelarAsistencia(${torneo.id})" class="w-14 bg-red-900/50 active:bg-red-800 border border-red-500 text-red-400 font-bold py-3 rounded-lg text-sm transition-colors flex items-center justify-center"><i class="fas fa-times"></i></button></div>`;
                    } else {
                        botonesHTML = `<button onclick="abrirModal(${torneo.id}, '${torneo.status}', null, null, '${torneo.beys}')" class="w-full bg-cyan-600 active:bg-cyan-500 text-white font-bold py-3 rounded-lg text-sm tracking-wide relative z-10 transition-colors">INSCRIBIRSE</button>`;
                    }

                    if (torneo.user_is_judge) {
                        const mostrarBtnToggleJuez = !torneo.user_is_revisor;
                        const btnJuezText = torneo.is_judge_assigned ? '<i class="fas fa-user-minus"></i> SALIR' : '<i class="fas fa-user-plus"></i> ARBITRAR';
                        const btnJuezColor = torneo.is_judge_assigned ? 'bg-red-900/50 border-red-500 text-red-400 hover:bg-red-800' : 'bg-yellow-900/50 border-yellow-500 text-yellow-400 hover:bg-yellow-800';
                        let botonesExclusivos = '';
                        
                        if (torneo.is_judge_assigned || torneo.user_is_revisor) {
                            botonesExclusivos = `
                                <button onclick="copiarNombres(${torneo.id})" class="flex-1 bg-gray-800 border border-gray-600 text-gray-300 hover:text-white font-bold py-2 rounded-lg text-xs transition-colors shadow-sm" title="Copiar Lista"><i class="fas fa-copy text-cyan-500"></i></button>
                                <button onclick='abrirModalEventManager(${JSON.stringify(torneo).replace(/'/g, "&apos;")})' class="flex-1 bg-yellow-900/50 border border-yellow-700 text-yellow-500 hover:text-white font-bold py-2 rounded-lg text-xs transition-colors shadow-sm" title="Editar Evento"><i class="fas fa-pen"></i></button>
                                <button onclick="comenzarTorneo(${torneo.id})" class="flex-1 bg-green-900/50 border border-green-500 text-green-400 font-bold py-2 rounded-lg text-xs transition-colors shadow-sm"><i class="fas fa-play mr-1"></i> INICIAR</button>
                            `;
                        }
                        panelJuez = `<div class="mt-4 pt-3 border-t border-gray-700/50 flex gap-2 relative z-10">${mostrarBtnToggleJuez ? `<button onclick="toggleJuez(${torneo.id})" class="${torneo.is_judge_assigned ? 'w-24' : 'w-full'} ${btnJuezColor} border font-bold py-2 rounded-lg text-xs transition-colors shadow-sm">${btnJuezText}</button>` : ''}${botonesExclusivos}</div>`;
                    }
                }

                let juecesHTML = '';
                if (torneo.jueces_asignados && torneo.jueces_asignados.length > 0) {
                    const nombresJueces = torneo.jueces_asignados.map(j => j.name).join(', ');
                    juecesHTML = `<div class="mt-3 relative z-10 flex items-start gap-2 bg-gray-950/50 p-2.5 rounded-lg border border-gray-800 shadow-inner"><i class="fas fa-gavel text-yellow-600 mt-0.5"></i><div><span class="text-[9px] text-gray-500 font-bold tracking-widest uppercase block mb-0.5">Staff Oficial</span><span class="text-xs text-gray-300 font-bold leading-tight">${nombresJueces}</span></div></div>`;
                }

                htmlTemp += `
                    <div class="${cardTheme} p-4 rounded-xl transition-all duration-300 border relative overflow-hidden">
                        ${torneo.is_inscribed ? '<div class="absolute top-0 right-0 w-24 h-24 bg-cyan-500/10 rounded-bl-full pointer-events-none z-0"></div>' : ''}
                        
                        <!-- NUEVA CABECERA REGIONAL (PEGADA A LA ESQUINA SUPERIOR IZQUIERDA) -->
                        <div class="-mx-4 -mt-4 mb-3.5 flex justify-between items-start relative z-10">
                            <!-- Bloque Sólido de Color (Región y Ciudad) -->
                            <div class="${regStyle.bg} border-r border-b ${regStyle.border} rounded-br-3xl pl-3.5 pr-5 py-2.5 flex items-center gap-2.5 shadow-[2px_2px_15px_rgba(0,0,0,0.4)] max-w-[75%] backdrop-blur-sm">
                                <span class="w-5 h-3.5 rounded-[3px] inline-block border border-white/20 shrink-0 shadow-sm" style="background: ${regStyle.flag};"></span>
                                <div class="flex flex-col truncate">
                                    <span class="text-[9px] font-black tracking-widest uppercase ${regStyle.text} leading-none mb-1 truncate">${regionName}</span>
                                    <span class="text-[11px] font-bold text-white uppercase tracking-wider leading-none truncate"><i class="fas fa-map-marker-alt ${regStyle.text} text-[10px] mr-1"></i>${cityName}</span>
                                </div>
                            </div>
                            
                            <!-- Etiqueta derecha (Tipo de Evento) -->
                            <div class="pt-3.5 pr-4 shrink-0">
                                <span class="text-[9px] ${badge} border px-2 py-1 rounded font-black uppercase shadow-sm">${torneo.beys || 'EVENTO'}</span>
                            </div>
                        </div>

                        <h3 class="text-white font-black text-xl leading-tight mb-3 relative z-10">${torneo.name}</h3>

                        <div class="flex justify-between items-end mb-5 relative z-10">
                            <p class="text-xs text-gray-400 flex flex-col gap-2">
                                <span><i class="fas fa-map-pin w-4 text-center text-red-500"></i> ${torneo.location || torneo.city || '---'}</span>
                                <span><i class="fas fa-calendar-alt w-4 text-center text-cyan-500"></i> ${fecha} <span class="mx-1 text-gray-600">|</span> <i class="fas fa-clock text-cyan-500 ml-1"></i> ${hora}</span>
                            </p>
                            <button onclick="abrirListaJugadores(${torneo.id}, ${torneo.aforo_maximo})" class="bg-gray-950 border border-gray-700 rounded-lg p-2 text-center shadow-inner active:bg-gray-800 transition-colors">
                                <div class="text-[10px] text-gray-500 font-bold tracking-widest mb-1">BLADERS</div>
                                <div class="font-mono font-bold text-lg leading-none ${colorAforo}">${torneo.total_inscritos} <span class="text-xs text-gray-600">/ ${limitText}</span></div>
                            </button>
                        </div>
                        ${aforoLleno && !torneo.is_inscribed ? '<p class="text-xs text-red-400 font-bold mb-3 uppercase tracking-wider relative z-10"><i class="fas fa-exclamation-triangle"></i> Entrarás en lista de espera</p>' : ''}
                        ${juecesHTML}
                        <div class="mt-4">${botonesHTML}${panelJuez}</div>
                    </div>`;
            });
            contenedor.innerHTML = htmlTemp;
        } catch (error) { 
            contenedor.innerHTML = '<p class="text-red-500 text-sm text-center">Error al sincronizar.</p>'; 
        }
    }

    async function toggleJuez(eventId) {
        triggerHaptic('medium');
        try {
            const res = await fetch(`/api/torneos/${eventId}/juez`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }});
            const data = await res.json();
            mostrarToast(data.message, !res.ok);
            if (res.ok) cargarEventos();
        } catch (error) { mostrarToast("Error de conexión.", true); triggerHaptic('error'); }
    }

    // --- RANKINGS ---
    async function cargarRankingIndividual() {
        const contenedor = document.getElementById('lista-ranking-individual');
        contenedor.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-yellow-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">CARGANDO BLADERS...</p></div>';
        try {
            const res = await fetch('/api/ranking'); const bladers = await res.json();
            if (bladers.length === 0) { contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-5 font-bold tracking-widest">AÚN NO HAY DATOS EN LA LIGA.</p>'; return; }
            let htmlTemp = '';
            bladers.forEach((blader, index) => {
                const rank = index + 1; const pts = blader.points_x3;
                let divTheme = { name: 'BRONCE', color: 'text-orange-400', bg: 'bg-orange-950/40 border-orange-700/50' };
                if (pts >= 75) divTheme = { name: 'XTREME', color: 'text-fuchsia-400', bg: 'bg-fuchsia-950/40 border-fuchsia-700/50' };
                else if (pts >= 66) divTheme = { name: 'MAESTRO', color: 'text-red-500', bg: 'bg-red-950/40 border-red-700/50' };
                else if (pts >= 52) divTheme = { name: 'PLATINO', color: 'text-teal-400', bg: 'bg-teal-950/40 border-teal-700/50' };
                else if (pts >= 35) divTheme = { name: 'ORO', color: 'text-yellow-400', bg: 'bg-yellow-950/40 border-yellow-700/50' };
                else if (pts >= 21) divTheme = { name: 'PLATA', color: 'text-gray-300', bg: 'bg-gray-800/80 border-gray-500/50' };

                htmlTemp += `
                    <div class="flex items-center justify-between p-3 rounded-xl border ${rank <= 3 ? 'border-yellow-500/30 bg-yellow-900/10' : 'border-gray-800 bg-gray-900'} shadow-sm transition-all relative overflow-hidden">
                        <div class="flex items-center gap-3">
                            <div class="font-mono text-2xl font-black italic w-8 text-center ${rank <= 1 ? 'text-yellow-400' : (rank === 2 ? 'text-gray-300' : (rank === 3 ? 'text-orange-400' : 'text-gray-600'))}">#${rank}</div>
                            <div><button onclick="abrirPerfil('${blader.user_id || blader.id}')" class="text-white font-bold text-sm truncate max-w-[150px] leading-tight text-left hover:text-cyan-400 transition-colors focus:outline-none relative z-10">${blader.user ? blader.user.name : 'Desconocido'}</button><p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mt-0.5"><i class="fas fa-map-marker-alt text-red-500/70 mr-1"></i>${blader.region ? blader.region.name : '---'}</p></div>
                        </div>
                        <div class="text-right flex flex-col items-end"><span class="font-mono text-lg font-black text-white">${pts} <span class="text-[10px] text-gray-500">PTS</span></span><span class="text-[8px] px-1.5 py-0.5 rounded border font-bold tracking-widest mt-1 ${divTheme.bg} ${divTheme.color}">${divTheme.name}</span></div>
                    </div>`;
            });
            contenedor.innerHTML = htmlTemp;
        } catch (error) { console.error(error); }
    }

    async function cargarRankingEquipos() {
        const contenedor = document.getElementById('lista-ranking-equipos');
        contenedor.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-yellow-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">CARGANDO EQUIPOS...</p></div>';
        try {
            const res = await fetch('/api/ranking-equipos'); const equipos = await res.json();
            if (equipos.length === 0) { contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-5 font-bold tracking-widest">AÚN NO HAY DATOS DE CLANES.</p>'; return; }
            let htmlTemp = '';
            equipos.forEach((eq, index) => {
                const rank = index + 1; const pts = eq.points_x3;
                let divTheme = { name: 'BRONCE', color: 'text-orange-400', bg: 'bg-orange-950/40 border-orange-700/50' };
                if (pts >= 35) divTheme = { name: 'XTREME', color: 'text-fuchsia-400', bg: 'bg-fuchsia-950/40 border-fuchsia-700/50' };
                else if (pts >= 28) divTheme = { name: 'MAESTRO', color: 'text-red-500', bg: 'bg-red-950/40 border-red-700/50' };
                else if (pts >= 21) divTheme = { name: 'PLATINO', color: 'text-teal-400', bg: 'bg-teal-950/40 border-teal-700/50' };
                else if (pts >= 14) divTheme = { name: 'ORO', color: 'text-yellow-400', bg: 'bg-yellow-950/40 border-yellow-700/50' };
                else if (pts >= 7) divTheme = { name: 'PLATA', color: 'text-gray-300', bg: 'bg-gray-800/80 border-gray-500/50' };

                htmlTemp += `
                    <div class="flex items-center justify-between p-3 rounded-xl border ${rank <= 3 ? 'border-yellow-500/30 bg-yellow-900/10' : 'border-gray-800 bg-gray-900'} shadow-sm transition-all relative overflow-hidden">
                        <div class="flex items-center gap-3">
                            <div class="font-mono text-2xl font-black italic w-8 text-center ${rank <= 1 ? 'text-yellow-400' : (rank === 2 ? 'text-gray-300' : (rank === 3 ? 'text-orange-400' : 'text-gray-600'))}">#${rank}</div>
                            <div><h3 class="text-white font-bold text-sm truncate max-w-[200px] leading-tight">${eq.name}</h3></div>
                        </div>
                        <div class="text-right flex flex-col items-end"><span class="font-mono text-lg font-black text-white">${pts} <span class="text-[10px] text-gray-500">PTS</span></span><span class="text-[8px] px-1.5 py-0.5 rounded border font-bold tracking-widest mt-1 ${divTheme.bg} ${divTheme.color}">${divTheme.name}</span></div>
                    </div>`;
            });
            contenedor.innerHTML = htmlTemp;
        } catch (error) { console.error(error); contenedor.innerHTML = '<p class="text-red-500 text-sm text-center">Error al cargar equipos.</p>'; }
    }

    // --- DUELOS DE EQUIPOS ---
    async function cargarDuelos() {
        const contenedor = document.getElementById('lista-duelos');
        contenedor.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-red-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">SINTONIZANDO COMBATES...</p></div>';
        try {
            const res = await fetch('/api/duelos');
            if (!res.ok) throw new Error('Error en el servidor: ' + res.status);
            const duelos = await res.json();
            if (duelos.length === 0) { contenedor.innerHTML = '<div class="text-center py-5 border border-gray-700 bg-gray-900 rounded-xl"><i class="fas fa-satellite-dish mb-3 text-gray-600 text-4xl"></i><h3 class="text-white font-bold font-mono">SIN REGISTROS</h3><p class="text-gray-500 text-xs mt-1">La arena de equipos está en silencio.</p></div>'; return; }
            let htmlTemp = '';
            duelos.forEach(duelo => {
                const res1 = parseInt(duelo.result_1) || 0; const res2 = parseInt(duelo.result_2) || 0;
                const win1 = res1 > res2; const win2 = res2 > res1;
                htmlTemp += `
                    <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden shadow-lg relative mb-4">
                        ${duelo.status === 'OPEN' ? '<span class="absolute top-2 right-2 bg-yellow-500 text-black text-[8px] font-bold px-2 py-0.5 rounded animate-pulse z-20">PENDIENTE</span>' : ''}
                        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r ${win1 ? 'from-yellow-500 to-gray-800' : (win2 ? 'from-gray-800 to-yellow-500' : 'from-gray-600 to-gray-600')} z-10"></div>
                        <div class="text-[9px] bg-red-900/50 text-white font-bold tracking-widest text-center py-1 border-b border-red-900 uppercase relative z-10">${duelo.matchup === "beybladex" ? "BEYBLADE X" : "BURST"}</div>
                        <div class="flex justify-between items-center p-4 pt-5 relative z-10">
                            <div class="flex-1 flex flex-col items-center"><div class="text-xl font-black font-mono mb-1 ${win1 ? 'text-yellow-400' : 'text-white'}">${res1}</div><span class="text-xs font-bold text-center text-gray-300 truncate w-full px-1">${duelo.versus_1 ? duelo.versus_1.name : '?'}</span></div>
                            <div class="px-2 font-mono text-xl font-black italic text-red-500 opacity-80 shrink-0">VS</div>
                            <div class="flex-1 flex flex-col items-center"><div class="text-xl font-black font-mono mb-1 ${win2 ? 'text-yellow-400' : 'text-white'}">${res2}</div><span class="text-xs font-bold text-center text-gray-300 truncate w-full px-1">${duelo.versus_2 ? duelo.versus_2.name : '?'}</span></div>
                        </div>
                        <div class="bg-gray-950 px-3 py-2 flex justify-between items-center border-t border-gray-800 relative z-10"><span class="text-[10px] text-gray-500 font-bold"><i class="far fa-calendar-alt mr-1"></i> ${new Date(duelo.created_at).toLocaleDateString('es-ES')}</span></div>
                    </div>`;
            });
            contenedor.innerHTML = htmlTemp;
        } catch (error) { console.error(error); contenedor.innerHTML = `<p class="text-red-500 text-sm text-center">Error al conectar con la base de datos (${error.message}).</p>`; }
    }

    // --- META ---
    let metaDataList = [];

    async function cargarMeta() {
        const tiempo = document.getElementById('meta-filtro-tiempo') ? document.getElementById('meta-filtro-tiempo').value : '30';
        const minP = document.getElementById('meta-filtro-rango') ? document.getElementById('meta-filtro-rango').value : '5';
        
        const contenedor = document.getElementById('lista-meta');
        contenedor.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-purple-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">ANALIZANDO EL META ACTUAL...</p></div>';
        
        try {
            const res = await fetch(`/api/meta?min_partidas=${minP}&dias=${tiempo}`); 
            const combos = await res.json();
            metaDataList = combos;
            
            if (combos.length === 0) { 
                contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-5 font-bold tracking-widest">NO HAY DATOS SUFICIENTES.</p>'; 
                return; 
            }
            
            let htmlTemp = ''; 
            combos.forEach((combo, index) => {
                const wr = parseFloat(combo.percentage_victories).toFixed(1); 
                const rank = index + 1;
                
                let cText = 'text-red-500'; let cBg = 'bg-red-600'; 
                if (wr >= 60) { cText = 'text-green-400'; cBg = 'bg-green-500'; } 
                else if (wr >= 50) { cText = 'text-cyan-400'; cBg = 'bg-cyan-500'; } 
                else if (wr >= 40) { cText = 'text-yellow-500'; cBg = 'bg-yellow-500'; }

                htmlTemp += `
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-lg relative overflow-hidden mb-3 transition-all">
                        <div class="flex justify-between items-start mb-2 relative z-10">
                            <div class="flex items-start gap-3">
                                <div class="font-mono text-2xl font-black italic ${rank <= 1 ? 'text-yellow-400' : (rank === 2 ? 'text-gray-300' : (rank === 3 ? 'text-orange-400' : 'text-gray-600'))}">#${rank}</div>
                                <div>
                                    <h3 class="text-yellow-500 font-bold text-lg font-mono leading-none tracking-wide uppercase">${combo.blade}</h3>
                                    <p class="text-white text-sm font-bold mt-1 opacity-75">${combo.ratchet} <span class="text-yellow-500 mx-1">X</span> ${combo.bit}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xl font-black font-mono ${cText}">${wr}%</span>
                                <p class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">${combo.total_partidas} BATALLAS</p>
                            </div>
                        </div>
                        
                        <div class="h-1.5 w-full bg-black border border-gray-800 overflow-hidden mt-3 relative z-10 rounded-full">
                            <div class="h-full ${cBg} transition-all duration-1000" style="width: ${wr}%"></div>
                        </div>

                        <div class="mt-4 flex gap-2 relative z-10">
                            <button onclick="toggleMetaDetails(${index})" class="flex-1 bg-gray-800 hover:bg-gray-700 active:bg-gray-600 text-gray-400 font-bold py-2 rounded-lg text-[10px] tracking-widest uppercase transition-colors border border-gray-700 flex items-center justify-center">
                                <i class="fas fa-chevron-down mr-1"></i> Resumen
                            </button>
                            <button onclick="abrirModalMeta(${index})" class="flex-1 bg-purple-900/40 hover:bg-purple-800/60 active:bg-purple-700 text-purple-400 font-bold py-2 rounded-lg text-[10px] tracking-widest uppercase transition-colors border border-purple-800/50 flex items-center justify-center">
                                <i class="fas fa-chart-pie mr-1"></i> Detalles
                            </button>
                        </div>

                        <div id="meta-details-${index}" class="hidden mt-3 pt-3 border-t border-gray-800 grid grid-cols-2 gap-2 text-center relative z-10">
                            <div class="bg-gray-950 p-2 rounded border border-gray-800">
                                <div class="text-[8px] text-gray-500 font-bold tracking-widest uppercase mb-1">Puntos Generados</div>
                                <div class="font-mono text-cyan-400 font-bold text-sm">${combo.eficiencia || 0}</div>
                            </div>
                            <div class="bg-gray-950 p-2 rounded border border-gray-800">
                                <div class="text-[8px] text-gray-500 font-bold tracking-widest uppercase mb-1">Victorias / Derrotas</div>
                                <div class="font-mono ${cText} font-bold text-sm">${combo.total_victorias || 0}W - ${combo.total_derrotas || 0}L</div>
                            </div>
                        </div>
                    </div>`;
            });
            contenedor.innerHTML = htmlTemp;
        } catch (error) { console.error(error); }
    }

    let currentTargetUserId = null;

    function abrirModalDeckJuez(eventId, userId, userName, eventType = null) {
        cerrarListaModal();
        abrirModal(eventId, 'OPEN', userId, userName, eventType); 
    }

    async function enviarDeck(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitDeck'); 
        const isTentative = document.getElementById('tentativeCheck').checked;
        
        if(!isTentative) { 
            let valido = true; 
            document.querySelectorAll('select.deck-req').forEach(s => { 
                const val = s.tomselect ? s.tomselect.getValue() : s.value;
                if(!val || val.trim() === '') valido = false; 
            });
            if(!valido) { mostrarToast("Faltan piezas obligatorias", true); triggerHaptic('error'); return; } 
        }
        
        triggerHaptic('heavy'); 
        btn.disabled = true; 
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ENVIANDO...';
        
        const eventId = document.getElementById('modal_event_id').value; 
        const formData = new FormData(document.getElementById('deckForm')); 
        formData.append('is_tentative', isTentative);
        
        if (currentTargetUserId) {
            formData.append('target_user_id', currentTargetUserId);
        }

        try {
            const res = await fetch(`/api/torneos/${eventId}/deck`, { 
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                    'Accept': 'application/json' 
                }, 
                body: formData 
            });
            const data = await res.json();
            if (!res.ok) { 
                mostrarToast(data.message || 'Error al procesar inscripción', true); 
                triggerHaptic('error'); 
            } else { 
                mostrarToast(data.message); 
                const wasEditingOther = currentTargetUserId !== null;
                cerrarModal(); 
                cargarEventos(); 
                
                if (wasEditingOther) {
                    const modalBatalla = document.getElementById('batallaModal');
                    if (modalBatalla && !modalBatalla.classList.contains('hidden')) {
                        abrirBatalla(battleState.matchId);
                    } else if (document.getElementById('listaModal').classList.contains('active')) {
                        abrirListaJugadores(eventId, currentListaAforo);
                    }
                }
                triggerHaptic('medium'); 
            }
        } catch (error) { 
            mostrarToast("Error de transmisión", true); 
            triggerHaptic('error'); 
        }
        btn.disabled = false; 
        btn.innerHTML = 'CONFIRMAR DECK';
    }

    function cerrarModalMeta() {
        triggerHaptic('light');
        document.getElementById('metaModal').classList.remove('flex', 'active');
        document.getElementById('metaModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function toggleMetaDetails(index) {
        triggerHaptic('light');
        const el = document.getElementById(`meta-details-${index}`);
        if (el.classList.contains('hidden')) { el.classList.remove('hidden'); el.classList.add('flex'); } 
        else { el.classList.add('hidden'); el.classList.remove('flex'); }
    }
    
    function abrirModalMeta(index) {
        triggerHaptic('medium');
        const combo = metaDataList[index];
        if (!combo) return;

        document.getElementById('metaModal').classList.remove('hidden');
        document.getElementById('metaModal').classList.add('flex', 'active');
        document.body.style.overflow = 'hidden';

        document.getElementById('modal_meta_blade').innerText = combo.blade || '---';
        document.getElementById('modal_meta_parts').innerText = `${combo.ratchet} X ${combo.bit}`;
        document.getElementById('modal_meta_style').innerText = combo.estilo_combate || 'ESTÁNDAR';
        document.getElementById('modal_meta_wr').innerText = `${parseFloat(combo.percentage_victories).toFixed(1)}%`;
        document.getElementById('modal_meta_wl').innerText = `${combo.total_victorias || 0}W - ${combo.total_derrotas || 0}L`;

        document.getElementById('modal_meta_spin').innerText = combo.spin_wins || 0;
        document.getElementById('modal_meta_over').innerText = combo.over_wins || 0;
        document.getElementById('modal_meta_burst').innerText = combo.burst_wins || 0;
        document.getElementById('modal_meta_xtreme').innerText = combo.xtreme_wins || 0;

        document.getElementById('modal_meta_dmg_won').innerText = parseFloat(combo.puntos_ganados_por_combate || 0).toFixed(2);
        document.getElementById('modal_meta_dmg_lost').innerText = parseFloat(combo.puntos_perdidos_por_combate || 0).toFixed(2);

        const bestContainer = document.getElementById('modal_meta_best');
        const worstContainer = document.getElementById('modal_meta_worst');
        
        const renderMatchups = (container, list, colorClass, type) => {
            if (!list || list.length === 0) {
                container.innerHTML = '<div class="text-[10px] text-gray-500 font-bold uppercase p-2 text-center border border-gray-800 rounded bg-gray-900/50">Datos insuficientes</div>';
                return;
            }
            let html = '';
            list.forEach(m => {
                const comboName = m.combo || 'DESCONOCIDO';
                const count = type === 'wins' ? (m.victorias || 0) : (m.derrotas || 0);
                const label = type === 'wins' ? 'WINS' : 'LOSSES';
                html += `
                    <div class="flex justify-between items-center bg-gray-900 border border-gray-800 p-2 mb-2 rounded-lg">
                        <span class="text-[10px] font-bold text-white uppercase">${comboName}</span>
                        <span class="text-xs font-mono font-black ${colorClass}">${count} ${label}</span>
                    </div>`;
            });
            container.innerHTML = html;
        };

        renderMatchups(bestContainer, combo.mejores_emparejamientos, 'text-green-400', 'wins');
        renderMatchups(worstContainer, combo.peores_emparejamientos, 'text-red-400', 'losses');
    }

    // --- PLAYER HUB ---
    function abrirPerfil(id) {
        if (!id || id === 'null' || id === 'undefined' || id === '0' || id === '') return;
        triggerHaptic('light'); document.getElementById('perfilModal').classList.add('active'); document.body.style.overflow = 'hidden'; cargarDatosPerfil(id);
    }

    function cerrarPerfil() {
        triggerHaptic('light'); document.getElementById('perfilModal').classList.remove('active'); document.body.style.overflow = 'auto';
        document.getElementById('perfilContent').innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-cyan-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">DESCIFRANDO DATOS...</p></div>';
    }

    async function cargarDatosPerfil(id) {
        const container = document.getElementById('perfilContent');
        try {
            const res = await fetch(`/api/perfil/${id}`);
            if (!res.ok) throw new Error('Error de red');
            const data = await res.json();

            const pts = data.puntos_x3 || 0;
            let divName = 'BRONCE', nextRank = 'PLATA', nextPts = 21, cText = 'text-orange-400', cBg = 'bg-orange-600';
            if (pts >= 75) { divName = 'XTREME'; nextRank = 'MÁXIMO'; nextPts = 0; cText = 'text-fuchsia-400'; cBg = 'bg-fuchsia-600'; }
            else if (pts >= 66) { divName = 'MAESTRO'; nextRank = 'XTREME'; nextPts = 75; cText = 'text-red-500'; cBg = 'bg-red-600'; }
            else if (pts >= 52) { divName = 'PLATINO'; nextRank = 'MAESTRO'; nextPts = 66; cText = 'text-teal-400'; cBg = 'bg-teal-500'; }
            else if (pts >= 35) { divName = 'ORO'; nextRank = 'PLATINO'; nextPts = 52; cText = 'text-yellow-400'; cBg = 'bg-yellow-500'; }
            else if (pts >= 21) { divName = 'PLATA'; nextRank = 'ORO'; nextPts = 35; cText = 'text-gray-300'; cBg = 'bg-gray-400'; }

            const ptsLeft = nextPts === 0 ? 0 : nextPts - pts;

            let statsHtml = `<div class="grid grid-cols-4 gap-2 mt-4 text-center"><div class="bg-gray-800 p-2 rounded-lg border border-gray-700"><div class="text-[9px] text-gray-400 font-bold tracking-widest">TORNEOS</div><div class="text-white font-mono font-bold text-lg leading-none mt-1">${data.torneos_jugados || 0}</div></div><div class="bg-gray-800 p-2 rounded-lg border border-yellow-700/50"><div class="text-[9px] text-yellow-500 font-bold tracking-widest">1º LUGAR</div><div class="text-yellow-400 font-mono font-bold text-lg leading-none mt-1">${data.primeros || 0}</div></div><div class="bg-gray-800 p-2 rounded-lg border border-gray-500/50"><div class="text-[9px] text-gray-400 font-bold tracking-widest">2º LUGAR</div><div class="text-gray-300 font-mono font-bold text-lg leading-none mt-1">${data.segundos || 0}</div></div><div class="bg-gray-800 p-2 rounded-lg border border-orange-700/50"><div class="text-[9px] text-orange-500 font-bold tracking-widest">3º LUGAR</div><div class="text-orange-400 font-mono font-bold text-lg leading-none mt-1">${data.terceros || 0}</div></div></div>`;
            
            const teamHtml = (data.equipo_nombre && data.equipo_nombre !== 'Ninguno') 
                ? `<div class="mt-4 p-3 bg-gray-800/50 border border-gray-700 rounded-xl flex items-center justify-between shadow-inner"><div class="flex items-center gap-3"><i class="fas fa-shield-alt text-gray-500 text-xl mx-2"></i><span class="text-white font-bold text-sm truncate uppercase">${data.equipo_nombre}</span></div><span class="text-xs font-mono font-bold text-cyan-400 bg-cyan-900/40 border border-cyan-800 px-2 py-1 rounded">${data.equipo_puntos || 0} PTS</span></div>` 
                : `<div class="mt-4 p-3 bg-gray-900 border border-gray-800 rounded-xl text-center text-xs text-gray-500 font-bold tracking-widest uppercase">AGENTE LIBRE</div>`;

            const mejorTitulo = (data.titulos || []).find(t => t.unlocked) || data.titulos[data.titulos.length - 1];
            let colorClasePrincipal = 'border-gray-500 text-gray-400 bg-gray-900/30';
            if(mejorTitulo.color === 'red') colorClasePrincipal = 'border-red-500 text-red-400 bg-red-900/30 shadow-[0_0_8px_rgba(239,68,68,0.5)]';
            else if(mejorTitulo.color === 'yellow') colorClasePrincipal = 'border-yellow-500 text-yellow-400 bg-yellow-900/30 shadow-[0_0_5px_rgba(234,179,8,0.4)]';
            else if(mejorTitulo.color === 'purple') colorClasePrincipal = 'border-purple-500 text-purple-400 bg-purple-900/30';
            else if(mejorTitulo.color === 'cyan') colorClasePrincipal = 'border-cyan-500 text-cyan-400 bg-cyan-900/30';

            const insigniaPrincipalHTML = `<div class="mt-2"><span class="text-[10px] px-3 py-1 rounded-full border font-bold tracking-widest uppercase ${colorClasePrincipal}"><i class="fas fa-medal mr-1"></i> ${mejorTitulo.nombre}</span></div>`;

            let escaparateLogrosHTML = `<div class="mt-4"><h4 class="text-[10px] font-bold text-gray-500 tracking-widest uppercase mb-2 border-b border-gray-800 pb-1"><i class="fas fa-trophy mr-1"></i> LOGROS DE CARRERA</h4><div class="grid grid-cols-3 gap-2">`;
            (data.titulos || []).forEach(t => {
                if (t.unlocked) {
                    let cBorder = 'border-gray-500', cText = 'text-gray-400', cBg = 'bg-gray-800';
                    if(t.color === 'red') { cBorder = 'border-red-500'; cText = 'text-red-400'; cBg = 'bg-red-900/20'; }
                    else if(t.color === 'yellow') { cBorder = 'border-yellow-500'; cText = 'text-yellow-400'; cBg = 'bg-yellow-900/20'; }
                    else if(t.color === 'purple') { cBorder = 'border-purple-500'; cText = 'text-purple-400'; cBg = 'bg-purple-900/20'; }
                    else if(t.color === 'cyan') { cBorder = 'border-cyan-500'; cText = 'text-cyan-400'; cBg = 'bg-cyan-900/20'; }
                    escaparateLogrosHTML += `<div class="flex flex-col items-center justify-center p-2 rounded-lg border ${cBorder} ${cBg} text-center shadow-inner relative overflow-hidden transition-all"><i class="fas fa-award text-2xl mb-1 ${cText} opacity-80"></i><span class="text-[8px] font-bold tracking-widest uppercase ${cText} leading-tight line-clamp-1">${t.nombre}</span></div>`;
                } else {
                    escaparateLogrosHTML += `<div class="flex flex-col items-center justify-center p-2 rounded-lg border border-gray-800 bg-gray-950 text-center opacity-60"><i class="fas fa-lock text-gray-600 text-xl mb-1"></i><span class="text-[7px] font-bold tracking-widest uppercase text-gray-500 leading-tight line-clamp-1 mb-0.5">${t.nombre}</span><span class="text-[6px] text-gray-600 uppercase tracking-widest">${t.desc}</span></div>`;
                }
            });
            escaparateLogrosHTML += `</div></div>`;

            let combosHtml = '';
            if (data.mejores_combos && data.mejores_combos.length > 0) {
                combosHtml += `<div class="mt-4"><h4 class="text-[10px] font-bold text-gray-500 tracking-widest uppercase mb-2 border-b border-gray-800 pb-1"><i class="fas fa-compact-disc mr-1"></i> ARSENAL FAVORITO</h4><div class="space-y-2">`;
                data.mejores_combos.forEach((combo, index) => {
                    const rankIcon = index === 0 ? 'text-yellow-400 fa-trophy' : (index === 1 ? 'text-gray-300 fa-medal' : 'text-orange-400 fa-medal');
                    const rankSize = index === 0 ? 'text-sm font-black' : 'text-xs font-bold opacity-80';
                    combosHtml += `<div class="bg-gray-800/50 border ${index === 0 ? 'border-fuchsia-900/50 shadow-inner' : 'border-gray-700'} p-2.5 rounded-lg flex justify-between items-center transition-all"><div class="flex items-center gap-3"><i class="fas ${rankIcon} w-4 text-center"></i><span class="text-white font-mono uppercase tracking-wide truncate max-w-[160px] sm:max-w-[200px] ${rankSize}">${combo.nombre}</span></div><span class="text-[10px] font-bold text-fuchsia-400 bg-fuchsia-900/20 border border-fuchsia-900/50 px-2 py-1 rounded shrink-0">${combo.victorias} WINS</span></div>`;
                });
                combosHtml += `</div></div>`;
            } else {
                combosHtml += `<div class="mt-4 p-3 bg-gray-900 border border-gray-800 rounded-xl text-center"><p class="text-[10px] text-gray-500 font-bold tracking-widest uppercase">Aún no tiene combos registrados.</p></div>`;
            }

            const isGif = data.avatar_url && data.avatar_url.includes('.gif'); const gifStyle = isGif ? 'padding: 10px;' : '';
            container.innerHTML = `
                <div class="flex flex-col items-center pt-2">
                    <div class="relative w-28 h-28 mb-1 mx-auto flex items-center justify-center">
                        <div class="absolute inset-[-10px] bg-cyan-500 blur-lg opacity-20 z-0 rounded-full"></div>
                        <img src="${data.avatar_url || '/images/default.png'}" class="w-full h-full object-cover rounded-full border-2 border-gray-800 bg-gray-900 relative z-10" style="${gifStyle}" loading="lazy" alt="Avatar">
                        ${data.marco_url ? `<img src="${data.marco_url}" class="absolute inset-0 w-full h-full z-20 pointer-events-none scale-105" loading="lazy" alt="Marco">` : ''}
                    </div>
                    <h2 class="text-white font-black text-2xl tracking-wide text-center uppercase mt-1">${data.nombre || 'Desconocido'}</h2>
                    ${data.subtitulo ? `<p class="text-cyan-400 text-[10px] font-bold tracking-widest uppercase mt-1 bg-cyan-900/30 px-2 py-0.5 rounded border border-cyan-800">${data.subtitulo}</p>` : ''}
                    <p class="text-gray-400 text-[11px] font-bold tracking-widest uppercase mt-2"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i> ${data.region || '---'}</p>
                    ${insigniaPrincipalHTML}
                </div>
                <div class="mt-4 p-4 bg-gray-900 border border-gray-700 rounded-xl relative overflow-hidden shadow-lg">
                    <div class="flex justify-between items-end mb-3 relative z-10"><div><div class="text-[10px] text-gray-400 font-bold tracking-widest">RANGO ACTUAL</div><div class="font-black text-xl ${cText} tracking-wide">${divName}</div></div><div class="text-right"><div class="font-mono text-3xl text-white font-black leading-none" style="text-shadow: 2px 2px 0 #000;">${pts}</div><div class="text-[9px] text-gray-500 font-bold tracking-widest mt-1">PUNTOS S3</div></div></div>
                    <div class="h-2 w-full bg-black border border-gray-800 rounded-full overflow-hidden mb-2 relative z-10"><div class="h-full ${cBg} transition-all duration-1000" style="width: ${nextPts === 0 ? 100 : Math.min(100, (pts / nextPts) * 100)}%"></div></div>
                    <div class="text-[9px] text-gray-400 font-bold tracking-widest text-right relative z-10">${nextPts === 0 ? 'RANGO MÁXIMO ALCANZADO' : `FALTAN <span class="text-white">${ptsLeft} PTS</span> PARA ${nextRank}`}</div>
                </div>
                ${statsHtml}${teamHtml}${escaparateLogrosHTML}${combosHtml}
            `;
        } catch (error) { container.innerHTML = '<p class="text-red-500 text-sm text-center mt-10">Error de comunicación al descifrar el perfil.</p>'; }
    }

    // --- LISTA DE JUGADORES ---
    let currentListaAforo = 999;

    async function abrirListaJugadores(eventId, aforoMaximo) {
        triggerHaptic('light');
        currentListaAforo = aforoMaximo;
        
        document.getElementById('listadoJugadoresContent').innerHTML = '<div class="text-center mt-10"><i class="fas fa-spinner fa-spin text-cyan-500 text-3xl mb-3"></i></div>';
        document.getElementById('listaModal').classList.add('active'); 
        document.body.style.overflow = 'hidden';

        try {
            const res = await fetch(`/api/torneos/${eventId}/jugadores`, { headers: { 'Accept': 'application/json' } }); 
            const data = await res.json();
            
            if (!res.ok || !data.jugadores) { 
                document.getElementById('listadoJugadoresContent').innerHTML = '<div class="text-center text-red-500 py-4 font-bold text-sm">Error o falta de permisos.</div>';
                return; 
            }
            
            let listaHTML = '';
            const eventType = data.event_type;
            
            if (data.is_staff && data.available_users) {
                listaHTML += `
                    <div class="mb-4 bg-gray-900 border border-gray-700 p-3 rounded-lg shadow-inner">
                        <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-2"><i class="fas fa-user-plus mr-1"></i> AÑADIR JUGADOR MANUALMENTE</label>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <select id="staffAddUserSelect" class="w-full" placeholder="Buscar blader..." autocomplete="off">
                                    <option value="">Buscar blader...</option>
                                    ${data.available_users.map(u => `<option value="${u.id}">${u.name}</option>`).join('')}
                                </select>
                            </div>
                            <button onclick="anadirJugadorManual(${eventId})" class="bg-cyan-700 hover:bg-cyan-600 active:bg-cyan-800 text-white px-4 rounded-lg font-bold transition-all shadow-md text-xs">
                                AÑADIR
                            </button>
                        </div>
                    </div>
                `;
            }

            if (data.jugadores.length === 0) {
                listaHTML += '<div class="text-center text-gray-500 py-4 font-bold text-sm">La arena está vacía</div>';
            } else { 
                data.jugadores.forEach((jugador, index) => { 
                    const esReserva = (index + 1) > currentListaAforo; 
                    const isDropped = jugador.puesto === 'dropped';
                    const safeName = jugador.name.replace(/'/g, "\\'");
                    
                    const deckIcon = jugador.has_deck 
                        ? `<span class="text-green-400 text-[10px] bg-green-950/60 border border-green-700/50 px-1.5 py-0.5 rounded font-bold mr-2 shrink-0" title="Deck Registrado"><i class="fas fa-layer-group"></i> OK</span>`
                        : `<span class="text-yellow-500 text-[10px] bg-yellow-950/60 border border-yellow-700/50 px-1.5 py-0.5 rounded font-bold mr-2 shrink-0" title="Sin Deck / Tentativo"><i class="fas fa-exclamation-circle"></i> SIN DECK</span>`;

                    let badgeHTML = esReserva 
                        ? `<span class="bg-red-900 text-white text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-widest shrink-0">Reserva #${(index + 1) - currentListaAforo}</span>` 
                        : `<span class="bg-gray-900 text-gray-400 text-[10px] px-2 py-0.5 rounded font-bold shrink-0">#${index + 1}</span>`; 
                    
                    let staffBtns = '';
                    if (isDropped) {
                        badgeHTML = `<span class="bg-red-600 text-white text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-widest shrink-0"><i class="fas fa-skull"></i> DROPPED</span>`;
                    } else if (data.is_staff) {
                        staffBtns = `
                            <button onclick="abrirModalDeckJuez(${eventId}, ${jugador.id}, '${safeName}', '${eventType}')" class="ml-2 bg-cyan-900/50 hover:bg-cyan-600 text-cyan-400 hover:text-white px-2 py-1 rounded border border-cyan-700/50 transition-colors text-xs" title="Meter/Editar Deck"><i class="fas fa-edit"></i></button>
                            <button onclick="ejecutarDrop(${eventId}, ${jugador.id}, '${safeName}')" class="ml-1 bg-red-900/50 hover:bg-red-600 text-red-400 hover:text-white px-2 py-1 rounded border border-red-700/50 transition-colors text-xs" title="Retirar del Torneo"><i class="fas fa-user-slash"></i></button>`;
                    }

                    listaHTML += `
                        <div class="flex justify-between items-center p-3 rounded-lg border mb-2 ${isDropped ? 'bg-red-950/20 border-red-900/30 opacity-50' : (esReserva ? 'bg-red-950/30 border-red-900/50 text-red-400' : 'bg-gray-800 border-gray-700 text-white')}">
                            <div class="flex items-center truncate pr-2">
                                ${!isDropped && typeof jugador.has_deck !== 'undefined' ? deckIcon : ''}
                                <span class="font-mono text-sm font-bold truncate ${isDropped ? 'line-through text-gray-500' : ''}">${jugador.name}</span>
                            </div>
                            <div class="flex items-center shrink-0">${badgeHTML}${staffBtns}</div>
                        </div>`; 
                }); 
            }
            
            document.getElementById('listadoJugadoresContent').innerHTML = listaHTML; 
            
            if (document.getElementById('staffAddUserSelect')) {
                new TomSelect('#staffAddUserSelect', { create: false, sortField: { field: "text", direction: "asc" }, maxOptions: 100 });
            }
        } catch (error) { 
            document.getElementById('listadoJugadoresContent').innerHTML = '<div class="text-center text-red-500 py-4 font-bold text-sm">Error de conexión</div>';
        }
    }

    async function anadirJugadorManual(eventId) {
        const select = document.getElementById('staffAddUserSelect');
        if (!select || !select.value) { mostrarToast("Por favor, busca y selecciona un jugador.", true); triggerHaptic('error'); return; }
        
        triggerHaptic('medium');
        const userId = select.value;
        const btn = select.closest('.flex').querySelector('button');
        const oldText = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const res = await fetch(`/api/torneos/${eventId}/jugador/add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if(data.success) { cargarEventos(); abrirListaJugadores(eventId, currentListaAforo); } 
            else { btn.disabled = false; btn.innerHTML = oldText; }
        } catch(e) { mostrarToast("Error al añadir jugador.", true); btn.disabled = false; btn.innerHTML = oldText; }
    }

    async function ejecutarDrop(eventId, userId, userName) {
        if(!confirm(`¿Estás seguro de que quieres dar de baja a ${userName}? No será emparejado en las siguientes rondas.`)) return;
        triggerHaptic('heavy');
        try {
            const res = await fetch(`/api/torneos/${eventId}/jugador/${userId}/drop`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if(data.success) abrirListaJugadores(eventId, currentListaAforo); 
        } catch(e) { mostrarToast("Error al ejecutar Drop", true); }
    }
    
    function cerrarListaModal() { triggerHaptic('light'); document.getElementById('listaModal').classList.remove('active'); document.body.style.overflow = 'auto'; }

    // --- DECK ---
    async function cargarPiezas() { try { dbPiezas = await (await fetch('/api/piezas')).json(); } catch (error) { console.error("Error piezas:", error); } }
    function cerrarModal() { triggerHaptic('light'); document.getElementById('deckModal').classList.remove('active'); document.body.style.overflow = 'auto'; document.getElementById('deckForm').reset(); toggleTentative(); }
    
    function toggleTentative() {
        triggerHaptic('light'); const isTentative = document.getElementById('tentativeCheck').checked; const container = document.getElementById('beysContainer'); const btn = document.getElementById('btnSubmitDeck');
        if (isTentative) { container.style.opacity = '0.3'; container.style.pointerEvents = 'none'; document.getElementById('tentativeWarning').classList.remove('hidden'); btn.className = "w-full bg-yellow-500 active:bg-yellow-400 text-black font-bold py-3 mt-6 rounded-lg text-sm tracking-wide shadow-md transition-colors"; } 
        else { container.style.opacity = '1'; container.style.pointerEvents = 'auto'; document.getElementById('tentativeWarning').classList.add('hidden'); btn.className = "w-full bg-cyan-600 active:bg-cyan-500 text-white font-bold py-3 mt-6 rounded-lg text-sm tracking-wide shadow-md transition-colors"; }
    }

    function generarFormularioBeys() {
        if(!dbPiezas) return;
        const container = document.getElementById('beysContainer'); let htmlTemp = '';
        const crearSelect = (name, index, options, placeholder, req = true) => `
            <div class="col-span-1">
                <select id="${name}_${index}" name="${name}[]" class="tom-select-pieza w-full ${req ? 'deck-req' : ''}" placeholder="${placeholder}" autocomplete="off">
                    <option value="">${placeholder}</option>
                    ${options ? options.map(opt => `<option value="${opt}">${opt}</option>`).join('') : ''}
                </select>
            </div>`;
            
        const numBeys = (currentEventType === 'equipos' || currentEventType === 'crew_battle') ? 1 : 3;
        for (let i = 1; i <= numBeys; i++) { 
            htmlTemp += `<div class="bg-gray-800/50 p-4 rounded-xl border border-gray-700/50 mb-4 shadow-inner"><div class="flex items-center gap-2 text-cyan-400 font-bold text-xs mb-3 tracking-widest border-b border-gray-700/50 pb-2"><i class="fas fa-compact-disc"></i> BEYBLADE #${i}</div><div class="grid grid-cols-1 gap-3 mb-3">${crearSelect('blade', i, dbPiezas.blades, 'Selecciona Blade...', true)}</div><div class="grid grid-cols-2 gap-3 mb-3">${crearSelect('ratchet', i, dbPiezas.ratchets, 'Ratchet...', true)}${crearSelect('bit', i, dbPiezas.bits, 'Bit...', true)}</div><div class="grid grid-cols-2 gap-3">${crearSelect('over_blade', i, dbPiezas.over_blades, 'Over (Opcional)', false)}${crearSelect('assist_blade', i, dbPiezas.assist_blades, 'Assist (Opcional)', false)}</div></div>`; 
        }
        container.innerHTML = htmlTemp;

        document.querySelectorAll('.tom-select-pieza').forEach(el => {
            new TomSelect(el, { create: false, sortField: { field: "text", direction: "asc" }, maxOptions: 100 });
        });
    }

    async function abrirModal(eventId, status = 'OPEN', targetUserId = null, targetUserName = null, eventType = null) { 
        triggerHaptic('light'); 
        currentTargetUserId = targetUserId;
        currentEventType = eventType;
        document.getElementById('modal_event_id').value = eventId; 
        
        if (!dbPiezas) await cargarPiezas();
        
        const checkTentative = document.getElementById('tentativeCheck');
        if (checkTentative) checkTentative.checked = false;
        toggleTentative();

        document.getElementById('deckModal').classList.add('active'); 
        document.getElementById('deckModal').style.zIndex = "110";
        document.body.style.overflow = 'hidden';

        generarFormularioBeys(); 

        const warning = document.getElementById('tentativeWarning');
        if (targetUserName && warning) {
            warning.innerHTML = `<span class="text-cyan-400 font-bold uppercase tracking-widest"><i class="fas fa-user-shield mr-2"></i> EDITANDO DECK DE: ${targetUserName}</span>`;
            warning.classList.remove('hidden');
        }

        try {
            let url = `/api/torneos/${eventId}/mi-deck`;
            if (targetUserId) url += `?user_id=${targetUserId}`;

            const res = await fetch(url);
            if (res.ok) {
                const deck = await res.json();
                deck.forEach((bey, index) => {
                    const i = index + 1; 
                    if (i > 3) return; 
                    const setSelectValue = (id, val) => {
                        const el = document.getElementById(id);
                        if (!el || !val || val === '-') return;
                        const cleanVal = val.trim();
                        if (el.tomselect) {
                            if (!el.tomselect.options[cleanVal]) el.tomselect.addOption({value: cleanVal, text: cleanVal});
                            el.tomselect.setValue(cleanVal);
                        } else el.value = cleanVal;
                    };
                    setSelectValue(`blade_${i}`, bey.blade);
                    setSelectValue(`ratchet_${i}`, bey.ratchet);
                    setSelectValue(`bit_${i}`, bey.bit);
                    setSelectValue(`over_blade_${i}`, bey.over_blade);
                    setSelectValue(`assist_blade_${i}`, bey.assist_blade);
                });
            }
        } catch (error) { console.warn("Deck nuevo o error de carga"); }

        const btnSubmit = document.getElementById('btnSubmitDeck');
        const formElements = document.querySelectorAll('#deckForm select, #deckForm input');

        if ((status === 'LIVE' || status === 'COMPLETED') && !targetUserId) {
            formElements.forEach(el => { if (el.tomselect) el.tomselect.disable(); else el.disabled = true; });
            btnSubmit.style.display = 'none';
            if (warning) {
                warning.innerHTML = '<span class="text-red-400 font-bold uppercase tracking-widest"><i class="fas fa-lock mr-2"></i> TORNEO EN CURSO. DECK BLOQUEADO.</span>';
                warning.classList.remove('hidden');
            }
        } else {
            formElements.forEach(el => { if (el.tomselect) el.tomselect.enable(); else el.disabled = false; });
            btnSubmit.style.display = 'block';
        }
    }

    async function autorrellenarDeck() {
        triggerHaptic('medium');
        try {
            mostrarToast("Buscando en el historial..."); 
            const res = await fetch('/api/ultimo-deck'); 
            const deck = await res.json();
            if (!deck || deck.length === 0) { mostrarToast("No tienes Decks anteriores.", true); return; }
            deck.forEach((bey, index) => { 
                const i = index + 1; if (i > 3) return; 
                const setVal = (field, val) => {
                    const el = document.getElementById(`${field}_${i}`);
                    if (el && val && val !== '-') { if (el.tomselect) el.tomselect.setValue(val); else el.value = val; }
                };
                setVal('blade', bey.blade); setVal('ratchet', bey.ratchet); setVal('bit', bey.bit); setVal('over_blade', bey.over_blade); setVal('assist_blade', bey.assist_blade);
            });
            mostrarToast("✅ Último Deck cargado");
        } catch (error) { mostrarToast("Error al recuperar el historial.", true); }
    }

    async function cancelarAsistencia(eventId) {
        triggerHaptic('medium'); if (!confirm('¿Retirar tu inscripción?')) return;
        try {
            const res = await fetch(`/api/torneos/${eventId}/asistencia`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }});
            const data = await res.json(); mostrarToast(data.message, !res.ok); if (res.ok) { cargarEventos(); triggerHaptic('heavy'); }
        } catch (error) { mostrarToast("Error de conexión.", true); triggerHaptic('error'); }
    }

    async function comenzarTorneo(eventId) {
        triggerHaptic('medium'); if (!confirm('¿Atención Juez: Seguro que quieres iniciar el torneo? Se bloquearán inscripciones.')) return;
        try {
            const res = await fetch(`/api/torneos/${eventId}/comenzar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }});
            const data = await res.json(); mostrarToast(data.message, !res.ok); if (res.ok) { cargarEventos(); triggerHaptic('heavy'); }
        } catch (error) { mostrarToast("Error de conexión con el estadio.", true); triggerHaptic('error'); }
    }

    async function copiarNombres(eventId) {
        triggerHaptic('light');
        try {
            const res = await fetch(`/api/torneos/${eventId}/jugadores`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (res.ok) { if (navigator.clipboard) { await navigator.clipboard.writeText(data.nombres); mostrarToast("✅ Nombres listos para Challonge"); } else mostrarToast("El navegador bloqueó el copiado automático.", true); } 
            else mostrarToast(data.message, true);
        } catch (error) { mostrarToast("Error al extraer la lista.", true); }
    }

    // --- BUSCADOR Y DUELOS ---
    function abrirBuscador(num) { triggerHaptic('light'); renderDropdown(num, allTeamsList); document.getElementById(`dropdown_team_${num}`).classList.remove('hidden'); }
    function cerrarBuscador(num) { setTimeout(() => { document.getElementById(`dropdown_team_${num}`).classList.add('hidden'); if (!document.getElementById(`team_id_${num}`).value) document.getElementById(`search_team_${num}`).value = ''; }, 200); }
    function filtrarEquipos(num) {
        document.getElementById(`team_id_${num}`).value = '';
        const query = document.getElementById(`search_team_${num}`).value.toLowerCase();
        renderDropdown(num, allTeamsList.filter(t => t.name.toLowerCase().includes(query)));
    }
    function renderDropdown(num, list) {
        const dropdown = document.getElementById(`dropdown_team_${num}`);
        if(list.length === 0) { dropdown.innerHTML = '<div class="p-3 text-gray-500 text-xs text-center font-bold">Sin coincidencias</div>'; return; }
        dropdown.innerHTML = list.map(eq => `<div onclick="seleccionarEquipo(${num}, ${eq.id}, '${eq.name.replace(/'/g, "\\'")}')" class="p-3 text-white text-sm border-b border-gray-700/50 hover:bg-gray-700 active:bg-cyan-900 cursor-pointer transition-colors truncate">${eq.name}</div>`).join('');
    }
    function seleccionarEquipo(num, id, name) { triggerHaptic('light'); document.getElementById(`team_id_${num}`).value = id; document.getElementById(`search_team_${num}`).value = name; document.getElementById(`dropdown_team_${num}`).classList.add('hidden'); }
    async function cargarEquiposSelect() { try { const res = await fetch('/api/equipos'); allTeamsList = await res.json(); } catch (error) { console.error("Error al cargar equipos", error); } }
    function abrirModalDuelo() { triggerHaptic('light'); cargarEquiposSelect(); document.getElementById('dueloModal').classList.add('active'); document.body.style.overflow = 'hidden'; }
    function cerrarModalDuelo() { triggerHaptic('light'); document.getElementById('dueloModal').classList.remove('active'); document.body.style.overflow = 'auto'; document.getElementById('formDuelo').reset(); document.getElementById('team_id_1').value = ''; document.getElementById('team_id_2').value = ''; }

    async function enviarDuelo(e) {
        e.preventDefault(); const t1 = document.getElementById('team_id_1').value; const t2 = document.getElementById('team_id_2').value;
        if (!t1 || !t2) { mostrarToast("Por favor, selecciona los clanes del desplegable.", true); triggerHaptic('error'); return; }
        if (t1 === t2) { mostrarToast("Un equipo no puede jugar contra sí mismo", true); triggerHaptic('error'); return; }
        triggerHaptic('heavy'); const btn = document.getElementById('btnSubmitDuelo'); btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ENVIANDO...';
        const formData = new FormData(document.getElementById('formDuelo'));
        try {
            const res = await fetch('/api/duelos', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }, body: formData });
            const data = await res.json();
            if (res.ok) { mostrarToast(data.message); cerrarModalDuelo(); cargarDuelos(); triggerHaptic('medium'); } else { mostrarToast("Error al registrar el duelo.", true); triggerHaptic('error'); }
        } catch (error) { mostrarToast("Error de transmisión", true); triggerHaptic('error'); }
        btn.disabled = false; btn.innerHTML = 'CONFIRMAR RESULTADO';
    }

    // =========================================================================
    // PANEL DE ARENA OPTIMIZADO (AVANCE AUTOMÁTICO Y ROLLBACK INTELIGENTE)
    // =========================================================================

    function copiarEnlaceCuadroPublico() {
        if (!currentArenaEventId) return;
        triggerHaptic('medium');
        const publicUrl = `${window.location.origin}/torneos/${currentArenaEventId}/cuadro`;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(publicUrl).then(() => {
                mostrarToast("🔗 Enlace del Cuadro en Vivo copiado para Discord");
            });
        } else {
            prompt("Copia este enlace para Discord:", publicUrl);
        }
    }

    function refrescarVistaArenaActual() {
        if (!currentArenaEventId) return;
        triggerHaptic('light');
        if (currentArenaTab === 'tabla') verClasificacionArena(currentArenaEventId);
        else if (currentArenaTab === 'cuadro') verCuadroArena(currentArenaEventId);
        else {
            if (currentArenaMode === 'judge') abrirPanelArbitraje(currentArenaEventId, currentArenaRound);
            else abrirArenaPublica(currentArenaEventId, currentArenaRound);
        }
    }

    function volverACrucesArena(eventId) {
        if (currentArenaMode === 'judge') abrirPanelArbitraje(eventId, currentArenaRound);
        else abrirArenaPublica(eventId, currentArenaRound);
    }

    async function abrirPanelArbitraje(eventId, round = null) {
        triggerHaptic('light');
        currentArenaEventId = eventId;
        currentArenaMode = 'judge';
        currentArenaTab = 'cruces';
        
        const modal = document.getElementById('arbitrajeModal');
        const container = document.getElementById('arbitrajeContent');
        const footer = document.getElementById('arbitrajeFooter');
        const btnGenerar = document.getElementById('btnGenerarRonda');
        
        if (!modal.classList.contains('active')) {
            container.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-yellow-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">SINTONIZANDO ARENA...</p></div>';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        let url = `/api/torneos/${eventId}/arena`;
        if (round !== null) url += `?round=${round}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            
            currentArenaRound = data.current_round;
            currentRoundMatchesCache = data.matches || [];
            document.getElementById('arbitraje_event_name').innerText = data.event_name;
            
            if (data.max_round === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 px-4">
                        <div class="w-20 h-20 rounded-full bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-4xl text-yellow-500"></i>
                        </div>
                        <h3 class="text-white font-black font-mono text-xl mb-2">ARENA PREPARADA</h3>
                        <p class="text-gray-400 text-xs leading-relaxed">El torneo está en curso y los decks están bloqueados. Pulsa el botón inferior para generar los emparejamientos de la Ronda 1.</p>
                    </div>`;
                if(footer) footer.classList.remove('hidden');
                if(btnGenerar) btnGenerar.classList.remove('hidden');
            } else {
                if(footer) footer.classList.add('hidden');
                if(btnGenerar) btnGenerar.classList.add('hidden');

                let roundSelectorHTML = `<div id="round-tabs-container" class="flex overflow-x-auto gap-2 mb-3 no-scrollbar pb-1.5 scroll-smooth">`;
                for (let i = 1; i <= data.max_round; i++) {
                    const isAct = (i === data.current_round);
                    const status = data.rounds_status ? data.rounds_status[i] : 'pending';
                    let colorClass = isAct 
                        ? 'bg-cyan-600 text-white border-cyan-400 shadow-[0_0_12px_rgba(6,182,212,0.5)]' 
                        : (status === 'completed' ? 'bg-green-950/60 text-green-400 border-green-800/80 hover:bg-green-900/60' : 'bg-orange-950/60 text-orange-400 border-orange-800/80 hover:bg-orange-900/60');
                    
                    roundSelectorHTML += `<button id="tab-round-${i}" onclick="abrirPanelArbitraje(${eventId}, ${i})" class="shrink-0 px-3.5 py-1.5 rounded-lg border text-[11px] font-black font-mono tracking-wider transition-all ${colorClass}">R${i} ${status === 'completed' ? '<i class="fas fa-check ml-0.5 text-[9px]"></i>' : ''}</button>`;
                }
                roundSelectorHTML += `</div>`;

                const isBracket = data.is_bracket;
                const totalMatches = data.matches.length;
                const completedMatches = data.matches.filter(m => m.status === 'completed' || m.status === 'cancelled').length;
                const pendingMatches = totalMatches - completedMatches;
                const progressPct = totalMatches > 0 ? Math.round((completedMatches / totalMatches) * 100) : 0;

                let htmlTemp = roundSelectorHTML + `
                    <div class="flex bg-gray-900 border border-gray-800 rounded-xl p-1 mb-3 shadow-inner">
                        <button class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-cyan-400 bg-gray-800 font-black shadow transition-all"><i class="fas fa-swords mr-1"></i> CRUCES</button>
                        <button onclick="verClasificacionArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-all"><i class="fas fa-list-ol mr-1"></i> TABLA</button>
                        <button onclick="verCuadroArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-all"><i class="fas fa-sitemap mr-1"></i> CUADRO</button>
                    </div>

                    <div class="bg-gray-900/90 border border-gray-800 rounded-xl p-3 mb-3 shadow-md">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-black font-mono tracking-widest uppercase ${isBracket ? 'text-fuchsia-400' : 'text-cyan-400'}">
                                ${isBracket ? '<i class="fas fa-crown mr-1"></i> FASE ELIMINATORIA' : `<i class="fas fa-layer-group mr-1"></i> PROGRESO RONDA ${data.current_round}`}
                            </span>
                            <span class="text-[10px] font-mono font-bold ${progressPct === 100 ? 'text-green-400' : 'text-yellow-400'}">
                                ${completedMatches} / ${totalMatches} COMPLETADAS (${progressPct}%)
                            </span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-950 rounded-full overflow-hidden border border-gray-800">
                            <div class="h-full ${progressPct === 100 ? 'bg-green-500' : 'bg-gradient-to-r from-cyan-500 to-yellow-500'} transition-all duration-500" style="width: ${progressPct}%"></div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2 mt-3">
                            <div class="relative flex-1">
                                <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-500 text-xs"></i>
                                <input type="text" id="arenaMatchSearch" oninput="filtrarTarjetasRonda()" placeholder="Buscar jugador o #partida..." class="w-full bg-gray-950 border border-gray-700 focus:border-cyan-400 text-white text-xs rounded-lg pl-7 pr-3 py-1.5 outline-none">
                            </div>
                            <div class="flex gap-1 shrink-0">
                                <button onclick="setFiltroEstadoMesa('all')" id="flt-mesa-all" class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold tracking-wider border bg-cyan-900/50 border-cyan-500 text-cyan-300">TODAS (${totalMatches})</button>
                                <button onclick="setFiltroEstadoMesa('pending')" id="flt-mesa-pending" class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold tracking-wider border bg-gray-950 border-gray-800 text-gray-400">PEND. (${pendingMatches})</button>
                                <button onclick="setFiltroEstadoMesa('completed')" id="flt-mesa-completed" class="px-2.5 py-1.5 rounded-lg text-[10px] font-bold tracking-wider border bg-gray-950 border-gray-800 text-gray-400">FIN (${completedMatches})</button>
                            </div>
                        </div>
                    </div>

                    <div id="arenaMatchesListContainer" class="space-y-2.5 pb-4"></div>
                `;

                if (data.current_round === data.max_round && totalMatches > 0) {
                    const allCompleted = data.matches.every(m => m.status === 'completed' || m.status === 'cancelled');
                    if (allCompleted) {
                        let uniquePlayers = new Set();
                        data.matches.forEach(m => { if(m.player1_id) uniquePlayers.add(m.player1_id); if(m.player2_id) uniquePlayers.add(m.player2_id); });
                        currentTotalPlayers = uniquePlayers.size;
                        if(document.getElementById('total_jugadores_top')) document.getElementById('total_jugadores_top').innerText = currentTotalPlayers;

                        htmlTemp += `<div class="flex flex-col gap-2.5 w-full mt-2 mb-6 p-3 bg-gray-900/90 border border-green-500/40 rounded-xl shadow-[0_0_20px_rgba(34,197,94,0.15)]">
                            <div class="text-center text-[10px] font-black text-green-400 tracking-widest uppercase"><i class="fas fa-check-double mr-1"></i> RONDA COMPLETADA - SIGUIENTE PASO</div>`;
                        
                        if (!isBracket) {
                            htmlTemp += `
                                <button onclick="generarSiguienteRonda()" class="w-full bg-green-600 hover:bg-green-500 font-black py-3.5 rounded-xl text-xs sm:text-sm text-white tracking-wider uppercase shadow-lg transition-all"><i class="fas fa-cogs mr-2"></i> GENERAR RONDA ${data.max_round + 1}</button>
                                <button onclick="abrirModalTopCut()" class="w-full bg-cyan-700 hover:bg-cyan-600 font-black py-3.5 rounded-xl text-xs sm:text-sm text-white border border-cyan-400 shadow-[0_0_15px_rgba(6,182,212,0.4)] tracking-wider uppercase transition-all"><i class="fas fa-trophy mr-2"></i> CERRAR SUIZO Y PASAR AL TOP</button>`;
                        } else {
                            htmlTemp += `
                                <button onclick="finalizarTorneo(${eventId})" class="w-full bg-red-600 hover:bg-red-500 active:bg-red-700 font-black py-4 rounded-xl text-sm text-white border-2 border-red-400 shadow-[0_0_20px_rgba(239,68,68,0.5)] transition-all uppercase tracking-widest"><i class="fas fa-flag-checkered mr-2"></i> CERRAR ACTA Y FINALIZAR TORNEO</button>`;
                        }
                        htmlTemp += `</div>`;
                    }
                }

                container.innerHTML = htmlTemp;
                currentMatchFilter = 'all';
                renderizarTarjetasRonda(data.matches, true);

                setTimeout(() => {
                    const activeTab = document.getElementById(`tab-round-${data.current_round}`);
                    if (activeTab) activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 50);
            } 
        } catch (error) {
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error de conexión.</p>';
        }
    }

    function setFiltroEstadoMesa(filtro) {
        triggerHaptic('light');
        currentMatchFilter = filtro;
        ['all', 'pending', 'completed'].forEach(f => {
            const btn = document.getElementById(`flt-mesa-${f}`);
            if (!btn) return;
            if (f === filtro) btn.className = "px-2.5 py-1.5 rounded-lg text-[10px] font-bold tracking-wider border bg-cyan-900/50 border-cyan-500 text-cyan-300";
            else btn.className = "px-2.5 py-1.5 rounded-lg text-[10px] font-bold tracking-wider border bg-gray-950 border-gray-800 text-gray-400";
        });
        filtrarTarjetasRonda();
    }

    function filtrarTarjetasRonda() {
        const query = (document.getElementById('arenaMatchSearch')?.value || '').trim().toLowerCase();
        const filtradas = currentRoundMatchesCache.filter(m => {
            const isTeam = m.team1_id != null && m.team2_id != null;
            const n1 = (isTeam ? m.team1?.name : m.player1?.name) || '';
            const n2 = (isTeam ? m.team2?.name : m.player2?.name) || '';
            const num = String(m.match_number || m.id);

            const cumpleTexto = !query || n1.toLowerCase().includes(query) || n2.toLowerCase().includes(query) || num.includes(query);
            let cumpleEstado = true;
            if (currentMatchFilter === 'pending') cumpleEstado = (m.status !== 'completed' && m.status !== 'cancelled');
            if (currentMatchFilter === 'completed') cumpleEstado = (m.status === 'completed' || m.status === 'cancelled');

            return cumpleTexto && cumpleEstado;
        });

        renderizarTarjetasRonda(filtradas, currentArenaMode === 'judge');
    }

    function renderizarTarjetasRonda(matchesArray, isJudgeView = true) {
        const listContainer = document.getElementById('arenaMatchesListContainer');
        if (!listContainer) return;

        if (!matchesArray || matchesArray.length === 0) {
            listContainer.innerHTML = `
                <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-6 text-center mt-2">
                    <i class="fas fa-check-circle text-3xl text-gray-600 mb-2"></i>
                    <p class="text-gray-400 text-xs font-bold tracking-wider uppercase">No hay partidas en este filtro</p>
                </div>`;
            return;
        }

        const sortedMatches = [...matchesArray].sort((a, b) => {
            const getPriority = (m) => {
                if (m.status === 'cancelled') return 4;
                if (m.status === 'completed') return 3;
                const isTeam = m.team1_id != null && m.team2_id != null;
                const missing = isTeam ? (!m.team1 || !m.team2) : (!m.player1 || !m.player2);
                if (missing) return 2;
                return 1;
            };
            const pA = getPriority(a);
            const pB = getPriority(b);
            if (pA !== pB) return pA - pB;
            return (a.match_number || a.id) - (b.match_number || b.id);
        });

        const miId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        let html = '';

        sortedMatches.forEach((match, idx) => {
            const isTeamMatch = match.team1_id != null && match.team2_id != null;
            const p1 = isTeamMatch ? (match.team1 ? match.team1.name : 'ESPERANDO RIVAL') : (match.player1 ? match.player1.name : 'ESPERANDO RIVAL');
            const p2 = isTeamMatch ? (match.team2 ? match.team2.name : 'ESPERANDO RIVAL') : (match.player2 ? match.player2.name : 'ESPERANDO RIVAL');
            const missingParticipants = isTeamMatch ? (!match.team1 || !match.team2) : (!match.player1 || !match.player2);

            const w1 = (match.winner_id && match.winner_id === match.player1_id) || (isTeamMatch && match.winner_id === match.team1_id);
            const w2 = (match.winner_id && match.winner_id === match.player2_id) || (isTeamMatch && match.winner_id === match.team2_id);

            let isLockedByOther = false;
            if (match.locked_by && miId && match.locked_by != miId && match.status !== 'completed') {
                isLockedByOther = true;
            }

            let borderClass = 'border-gray-800 bg-gray-900/90';
            if (match.status === 'completed') borderClass = 'border-gray-800/80 bg-gray-900/40 opacity-85';
            else if (isLockedByOther) borderClass = 'border-orange-500/80 bg-orange-950/20 shadow-[0_0_12px_rgba(249,115,22,0.2)]';
            else if (match.bracket_type === 'winners') borderClass = 'border-fuchsia-900/80 bg-gray-900/90';
            else if (match.bracket_type === 'losers') borderClass = 'border-orange-900/80 bg-gray-900/90';
            else if (!missingParticipants) borderClass = 'border-cyan-900/80 bg-gray-900/90';

            let actionHTML = '';
            if (isJudgeView) {
                if (missingParticipants) {
                    actionHTML = `<div class="bg-gray-950 text-gray-500 font-bold py-2 rounded-lg text-[10px] tracking-widest text-center border border-gray-800"><i class="fas fa-hourglass-half mr-1"></i> ESPERANDO RIVALES DEL CUADRO</div>`;
                } else if (match.status === 'cancelled') {
                    actionHTML = `<div class="w-full bg-red-950/30 text-red-500/70 font-bold py-2 rounded-lg text-[10px] text-center tracking-wider border border-red-900/40"><i class="fas fa-ban mr-1"></i> LÍMITE MENSUAL EXCEDIDO</div>`;
                } else if (match.status === 'completed') {
                    // Avance es 100% automático; si el juez toca aquí puede editar o hacer undo (que revierte el avance solo)
                    actionHTML = `<button onclick="abrirBatalla(${match.id})" class="w-full bg-green-950/40 hover:bg-green-900/60 text-green-400 font-bold py-2 rounded-lg text-[11px] tracking-wider transition-colors border border-green-800/50"><i class="fas fa-check-circle mr-1"></i> PARTIDA FINALIZADA (EDITAR / UNDO)</button>`;
                } else if (isLockedByOther) {
                    const judgeName = match.locked_by_user ? match.locked_by_user.name : 'OTRO JUEZ';
                    actionHTML = `<button class="w-full bg-orange-950/60 text-orange-400 font-bold py-2 rounded-lg text-[11px] tracking-wider border border-orange-500/60 pointer-events-none"><i class="fas fa-lock mr-1"></i> ARBITRANDO: ${judgeName.toUpperCase()}</button>`;
                } else {
                    actionHTML = `<button onclick="abrirBatalla(${match.id})" class="w-full bg-yellow-600 hover:bg-yellow-500 active:scale-[0.99] text-white font-black py-2.5 rounded-lg text-xs tracking-widest uppercase transition-all shadow-md border border-yellow-400"><i class="fas fa-gavel mr-1.5"></i> JUZGAR PARTIDA</button>`;
                }
            } else {
                if (missingParticipants) actionHTML = `<div class="text-center text-gray-500 text-[10px] font-bold tracking-widest pt-1"><i class="fas fa-hourglass-half mr-1"></i> EN ESPERA DE RIVALES</div>`;
                else if (match.status === 'completed') actionHTML = `<div class="text-center text-green-400 text-[10px] font-bold tracking-widest pt-1"><i class="fas fa-check-circle mr-1"></i> COMBATE FINALIZADO</div>`;
                else if (match.locked_by) actionHTML = `<div class="text-center text-cyan-400 text-[10px] font-bold tracking-widest pt-1 animate-pulse"><i class="fas fa-bolt mr-1"></i> EN JUEGO AHORA</div>`;
                else actionHTML = `<div class="text-center text-yellow-500/80 text-[10px] font-bold tracking-widest pt-1"><i class="fas fa-clock mr-1"></i> PENDIENTE</div>`;
            }

            html += `
                <div class="${borderClass} border rounded-xl p-3 shadow-md relative overflow-hidden transition-all">
                    <div class="flex justify-between items-center mb-2.5">
                        <span class="bg-gray-950 text-gray-400 border border-gray-800 text-[9px] font-mono font-bold px-2 py-0.5 rounded">
                            PARTIDA #${match.match_number || (idx + 1)}
                        </span>
                        ${match.bracket_type !== 'swiss' && match.bracket_type !== 'round_robin' ? `<span class="${match.bracket_type === 'winners' ? 'bg-fuchsia-950 text-fuchsia-300 border-fuchsia-800' : (match.bracket_type === 'losers' ? 'bg-orange-950 text-orange-300 border-orange-800' : 'bg-yellow-950 text-yellow-300 border-yellow-700')} border text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest">${match.bracket_type.replace(/_/g, ' ')}</span>` : ''}
                    </div>
                    
                    <div class="flex justify-between items-center mb-3 bg-gray-950/60 p-2.5 rounded-lg border border-gray-800/60">
                        <div class="flex-1 flex items-center justify-between pr-2 border-r border-gray-800">
                            <span class="text-xs font-bold ${!missingParticipants ? (w1 ? 'text-yellow-400' : 'text-white') : 'text-gray-600'} truncate max-w-[115px] sm:max-w-[150px] uppercase">${p1}</span>
                            <span class="text-2xl font-black font-mono ml-2 ${w1 ? 'text-yellow-400' : 'text-gray-400'}">${match.player1_score}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-between pl-2">
                            <span class="text-2xl font-black font-mono mr-2 ${w2 ? 'text-yellow-400' : 'text-gray-400'}">${match.player2_score}</span>
                            <span class="text-xs font-bold text-right ${!missingParticipants ? (w2 ? 'text-yellow-400' : 'text-white') : 'text-gray-600'} truncate max-w-[115px] sm:max-w-[150px] uppercase">${p2}</span>
                        </div>
                    </div>
                    <div>${actionHTML}</div>
                </div>`;
        });

        listContainer.innerHTML = html;
    }

    async function finalizarTorneo(eventId) {
        if (!confirm("🚨 ATENCIÓN JUEZ: ¿Seguro que quieres cerrar el acta y finalizar el torneo? El evento se marcará como Completado.")) return;
        triggerHaptic('heavy');
        try {
            const res = await fetch(`/api/torneos/${eventId}/finalizar`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if (data.success) { cerrarPanelArbitraje(); cargarEventos(); }
        } catch (error) { mostrarToast("Error de conexión", true); }
    }

    async function abrirArenaPublica(eventId, round = null) {
        triggerHaptic('light');
        currentArenaEventId = eventId;
        currentArenaMode = 'public';
        currentArenaTab = 'cruces';

        const modal = document.getElementById('arbitrajeModal');
        const container = document.getElementById('arbitrajeContent');
        const footer = document.getElementById('arbitrajeFooter');
        if (footer) footer.classList.add('hidden');
        
        if (!modal.classList.contains('active')) {
            container.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-fuchsia-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">SINTONIZANDO ARENA...</p></div>';
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        let url = `/api/torneos/${eventId}/arena`;
        if (round !== null) url += `?round=${round}`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            
            currentArenaRound = data.current_round;
            currentRoundMatchesCache = data.matches || [];
            document.getElementById('arbitraje_event_name').innerText = data.event_name;
            
            if (data.max_round === 0) {
                container.innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-hourglass-half text-6xl text-gray-700 mb-4"></i>
                        <h3 class="text-white font-bold font-mono text-xl mb-2">ARENA EN PREPARACIÓN</h3>
                        <p class="text-gray-500 text-sm px-4">El torneo está a punto de comenzar. Espera a que los jueces generen los cruces.</p>
                    </div>`;
            } else {
                let roundSelectorHTML = `<div id="round-tabs-container" class="flex overflow-x-auto gap-2 mb-3 no-scrollbar pb-1.5 scroll-smooth">`;
                for (let i = 1; i <= data.max_round; i++) {
                    const isAct = (i === data.current_round);
                    const colorClass = isAct ? 'bg-fuchsia-600 text-white border-fuchsia-400 shadow-[0_0_10px_rgba(192,38,211,0.4)]' : 'bg-gray-900 text-gray-400 border-gray-800';
                    roundSelectorHTML += `<button onclick="abrirArenaPublica(${eventId}, ${i})" class="shrink-0 px-3.5 py-1.5 rounded-lg border text-xs font-bold tracking-widest transition-all ${colorClass}">RONDA ${i}</button>`;
                }
                roundSelectorHTML += `</div>`;

                let htmlTemp = roundSelectorHTML + `
                    <div class="flex bg-gray-900 border border-gray-800 rounded-xl p-1 mb-3">
                        <button class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-fuchsia-400 bg-gray-800 font-black transition-colors">CRUCES</button>
                        <button onclick="verClasificacionArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-colors">TABLA</button>
                        <button onclick="verCuadroArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-colors">CUADRO</button>
                    </div>
                    <div class="mb-3">
                        <input type="text" id="arenaMatchSearch" oninput="filtrarTarjetasRonda()" placeholder="Buscar jugador en esta ronda..." class="w-full bg-gray-900 border border-gray-800 focus:border-fuchsia-500 text-white text-xs rounded-lg px-3 py-2 outline-none">
                    </div>
                    <div id="arenaMatchesListContainer" class="space-y-2.5 pb-4"></div>`;
                
                container.innerHTML = htmlTemp;
                currentMatchFilter = 'all';
                renderizarTarjetasRonda(data.matches, false);
            } 
        } catch (error) { 
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error de conexión.</p>'; 
        }
    }

    function cerrarPanelArbitraje() {
        triggerHaptic('light');
        document.getElementById('arbitrajeModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    async function generarSiguienteRonda() {
        if(!confirm("¿Atención Juez: Generar los emparejamientos de esta ronda?")) return;
        triggerHaptic('heavy');
        
        const btn = document.getElementById('btnGenerarRonda');
        let oldText = '';
        if (btn) { oldText = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> GENERANDO...'; }

        try {
            const res = await fetch(`/api/torneos/${currentArenaEventId}/arena/ronda`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if (data.success) abrirPanelArbitraje(currentArenaEventId); 
        } catch (error) {
            mostrarToast("Error de transmisión con el motor Suizo.", true);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = oldText; }
        }
    }

    // =========================================================================
    // LÓGICA DE BATALLA TÁCTIL PRO + NO PRESENTADO + MARCADOR MANUAL
    // =========================================================================
    let battleState = { matchId: null, p1ComboId: null, p2ComboId: null, winnerId: null, finishType: null };
    let isSideSwapped = false;
    let currentMatchData = null;

    function getDeckContainer(playerKey) {
        return document.getElementById(`deck_${playerKey}_container`) || document.getElementById(`${playerKey}CombosList`);
    }

    async function abrirBatalla(matchId) {
        const lockStatus = await lockMatchOnServer(matchId);
        if (!lockStatus.success) {
            mostrarToast("⚠️ Esta partida ya la está juzgando otro árbitro", true);
            triggerHaptic('error');
            return; 
        }

        triggerHaptic('medium');
        const modal = document.getElementById('batallaModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        if (battleState.matchId !== matchId) isSideSwapped = false;
        battleState.matchId = matchId; 
        
        resetBatallaUI();
        aplicarEstadoLados();
        
        const btnW1 = document.getElementById('btn_warning_p1');
        const btnW2 = document.getElementById('btn_warning_p2');
        if (btnW1) btnW1.className = "text-gray-500 hover:text-yellow-400 bg-gray-950/90 border border-gray-800 rounded-md px-2 py-1 transition-all text-xs flex items-center gap-1 cursor-pointer";
        if (btnW2) btnW2.className = "text-gray-500 hover:text-yellow-400 bg-gray-950/90 border border-gray-800 rounded-md px-2 py-1 transition-all text-xs flex items-center gap-1 cursor-pointer";

        const c1 = getDeckContainer('p1');
        const c2 = getDeckContainer('p2');
        if (c1) c1.innerHTML = '<div class="animate-pulse h-12 bg-gray-800 rounded-lg"></div>';
        if (c2) c2.innerHTML = '<div class="animate-pulse h-12 bg-gray-800 rounded-lg"></div>';

        try {
            const res = await fetch(`/api/torneos/partida/${matchId}`);
            const data = await res.json();
            currentMatchData = data;
            
            const badgeMesa = document.getElementById('batalla_match_badge');
            if (badgeMesa) badgeMesa.innerText = `PARTIDA #${data.match.match_number || data.match.id}`;

            const n1 = data.is_team_match ? data.match.team1.name : data.match.player1.name;
            const n2 = data.is_team_match ? data.match.team2.name : data.match.player2.name;

            document.getElementById('batalla_p1_name').innerText = n1;
            document.getElementById('batalla_p2_name').innerText = n2;
            document.getElementById('btn_ganador_p1').innerText = `GANA ${n1.substring(0, 12).toUpperCase()}`;
            document.getElementById('btn_ganador_p2').innerText = `GANA ${n2.substring(0, 12).toUpperCase()}`;

            // Etiquetas del bloque de marcador manual
            if (document.getElementById('manual_lbl_p1')) document.getElementById('manual_lbl_p1').innerText = n1;
            if (document.getElementById('manual_lbl_p2')) document.getElementById('manual_lbl_p2').innerText = n2;
            if (document.getElementById('manual_input_p1')) document.getElementById('manual_input_p1').value = data.match.player1_score || 4;
            if (document.getElementById('manual_input_p2')) document.getElementById('manual_input_p2').value = data.match.player2_score || 0;

            if (data.is_team_match) {
                const p1Used = obtenerCombosUsadosEquipo(data.battles || [], 'p1', data.team1_size);
                const p2Used = obtenerCombosUsadosEquipo(data.battles || [], 'p2', data.team2_size);

                renderTeamDeckBatalla('p1', data.p1_deck, p1Used);
                renderTeamDeckBatalla('p2', data.p2_deck, p2Used);

                const cycleBadge = document.getElementById('batalla_cycle_badge');
                if (cycleBadge) cycleBadge.innerText = `DUELO ${data.matches_played + 1} DE ${data.matches_to_play}`;
            } else {
                document.getElementById('batalla_p1_id').value = data.match.player1_id;
                document.getElementById('batalla_p2_id').value = data.match.player2_id;

                const { p1Used, p2Used, realBattlesCount } = obtenerCombosUsadosEnCiclo(data.battles || []);
                renderDeckBatalla('p1', data.p1_deck, p1Used);
                renderDeckBatalla('p2', data.p2_deck, p2Used);

                const cycleBadge = document.getElementById('batalla_cycle_badge');
                if (cycleBadge) cycleBadge.innerText = `LANZAMIENTO #${realBattlesCount + 1} (SET ${Math.floor(realBattlesCount / 3) + 1})`;
            }

            document.getElementById('batalla_score_p1').innerText = data.match.player1_score;
            document.getElementById('batalla_score_p2').innerText = data.match.player2_score;

            renderizarBitacoraBatalla(data.battles || [], data.match);

            if (data.battles && Array.isArray(data.battles)) {
                const w1 = data.battles.some(b => b.finish_type === 'warning_p1');
                const w2 = data.battles.some(b => b.finish_type === 'warning_p2');
                if (w1 && btnW1) btnW1.className = "text-yellow-400 bg-yellow-950/90 border border-yellow-500 rounded-md px-2 py-1 animate-pulse text-xs flex items-center gap-1 shadow-[0_0_8px_rgba(250,204,21,0.6)] cursor-pointer";
                if (w2 && btnW2) btnW2.className = "text-yellow-400 bg-yellow-950/90 border border-yellow-500 rounded-md px-2 py-1 animate-pulse text-xs flex items-center gap-1 shadow-[0_0_8px_rgba(250,204,21,0.6)] cursor-pointer";
            }

        } catch (error) {
            console.error(error);
            mostrarToast("Error cargando la partida.", true);
        }
    }

    // NUEVO: Botón sutil de "No presentado" debajo del jugador ausente -> Da la victoria al otro jugador
    async function declararNoPresentado(absentPlayerSide) {
        const absentName = absentPlayerSide === 'p1' ? document.getElementById('batalla_p1_name').innerText : document.getElementById('batalla_p2_name').innerText;
        const winnerSide = absentPlayerSide === 'p1' ? 'p2' : 'p1';
        const winnerName = winnerSide === 'p1' ? document.getElementById('batalla_p1_name').innerText : document.getElementById('batalla_p2_name').innerText;

        if (!confirm(`¿Marcar a ${absentName} como NO PRESENTADO?\n\nSe otorgará la victoria automáticamente a ${winnerName}.`)) return;
        
        triggerHaptic('heavy');
        try {
            const res = await fetch(`/api/torneos/partida/${battleState.matchId}/victoria-rapida`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ winner_side: winnerSide })
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if (data.success) {
                cerrarBatalla();
            }
        } catch (e) {
            mostrarToast("Error al registrar incomparecencia", true);
        }
    }

    // NUEVO: Desplegar y guardar Marcador Manual sin stats
    function toggleMarcadorManualBox() {
        triggerHaptic('light');
        const box = document.getElementById('manual_score_box');
        const icon = document.getElementById('icon_manual_score');
        if (!box) return;
        box.classList.toggle('hidden');
        if (icon) icon.classList.toggle('rotate-180');
    }

    async function guardarMarcadorManual() {
        const s1 = parseInt(document.getElementById('manual_input_p1').value) || 0;
        const s2 = parseInt(document.getElementById('manual_input_p2').value) || 0;

        if (s1 < 4 && s2 < 4) {
            mostrarToast("⚠️ Uno de los jugadores debe tener 4 puntos para finalizar.", true);
            triggerHaptic('error');
            return;
        }
        if (s1 === 4 && s2 === 4) {
            mostrarToast("⚠️ El resultado no puede ser 4 - 4.", true);
            triggerHaptic('error');
            return;
        }

        if (!confirm(`¿Guardar marcador manual (${s1} - ${s2}) sin estadísticas de Beys y cerrar partida?`)) return;

        triggerHaptic('heavy');
        try {
            const res = await fetch(`/api/torneos/partida/${battleState.matchId}/marcador-manual`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ p1_score: s1, p2_score: s2 })
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if (data.success) {
                cerrarBatalla();
            }
        } catch (e) {
            mostrarToast("Error de red al guardar marcador manual", true);
        }
    }

    function renderizarBitacoraBatalla(battles, match) {
        const container = document.getElementById('batalla_history_pills');
        if (!container) return;

        if (!battles || battles.length === 0) {
            container.innerHTML = '<span class="text-gray-600 italic text-[10px]">Esperando primer lanzamiento...</span>';
            return;
        }

        let boutNum = 0;
        const pills = battles.map(b => {
            if (b.finish_type === 'warning_p1') {
                return `<span class="bg-yellow-950/80 border border-yellow-700/60 text-yellow-400 px-2 py-0.5 rounded shrink-0"><i class="fas fa-exclamation-triangle"></i> Warn P1</span>`;
            }
            if (b.finish_type === 'warning_p2') {
                return `<span class="bg-yellow-950/80 border border-yellow-700/60 text-yellow-400 px-2 py-0.5 rounded shrink-0"><i class="fas fa-exclamation-triangle"></i> Warn P2</span>`;
            }
            boutNum++;
            const isP1Win = b.winner_id == match.player1_id;
            const color = isP1Win ? 'bg-cyan-950/80 border-cyan-700/60 text-cyan-300' : 'bg-red-950/80 border-red-700/60 text-red-300';
            const tag = b.finish_type ? b.finish_type.toUpperCase() : 'PT';
            return `<span class="${color} border px-2 py-0.5 rounded shrink-0 font-bold">#${boutNum}: ${isP1Win ? 'P1' : 'P2'} ${tag} (+${b.points_awarded})</span>`;
        });

        container.innerHTML = pills.join('');
        const bar = document.getElementById('batalla_history_bar');
        if (bar) bar.scrollLeft = bar.scrollWidth;
    }

    function renderDeckBatalla(playerKey, deckArray, usedIds = []) {
        const container = getDeckContainer(playerKey);
        if (!container) return;

        if (!deckArray || deckArray.length === 0) {
            container.innerHTML = '<p class="text-red-500 text-xs font-bold">Sin deck registrado.</p>';
            return;
        }
        
        let html = '';
        let disponiblesIds = [];

        deckArray.forEach((bey, idx) => {
            const yaUsado = usedIds.includes(Number(bey.id));
            if (yaUsado) {
                html += `
                    <div class="w-full text-left bg-gray-950/50 border border-gray-800/80 px-2.5 py-1.5 rounded-xl flex items-center justify-between opacity-35 select-none pointer-events-none">
                        <div class="truncate pr-1 line-through text-gray-500">
                            <span class="font-bold text-[11px] uppercase block truncate leading-tight">${idx + 1}. ${bey.blade}</span>
                            <span class="text-[9px] font-mono leading-none">${bey.ratchet} ${bey.bit}</span>
                        </div>
                        <span class="text-[8px] bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded font-bold uppercase shrink-0"><i class="fas fa-check"></i></span>
                    </div>`;
            } else {
                disponiblesIds.push(bey.id);
                html += `
                    <button type="button" onclick="seleccionarBey('${playerKey}', ${bey.id})" id="btn_bey_${bey.id}" class="bey-btn-${playerKey} w-full text-left bg-gray-900 border-2 border-gray-700 hover:border-gray-500 px-2.5 py-1.5 rounded-xl transition-all flex items-center justify-between shadow-sm active:scale-95">
                        <div class="truncate">
                            <span class="text-white font-black text-[11px] uppercase block truncate leading-tight">${idx + 1}. ${bey.blade}</span>
                            <span class="text-gray-400 text-[9px] font-mono leading-none">${bey.ratchet} · ${bey.bit}</span>
                        </div>
                    </button>`;
            }
        });
        container.innerHTML = html;

        if (disponiblesIds.length === 1) {
            seleccionarBey(playerKey, disponiblesIds[0], true);
        }
    }

    function renderTeamDeckBatalla(playerKey, deckArray, usedIds = []) {
        const container = getDeckContainer(playerKey);
        if (!container) return;

        if (!deckArray || deckArray.length === 0) {
            container.innerHTML = '<p class="text-red-500 text-xs font-bold">Sin alineación.</p>';
            return;
        }
        
        let html = '';
        let disponiblesIds = [];
        
        deckArray.forEach((bey, idx) => {
            const yaUsado = usedIds.includes(Number(bey.id));
            const editBtn = `<button type="button" onclick="event.stopPropagation(); abrirModalDeckJuez(currentArenaEventId, ${bey.user_id}, 'Jugador ${idx + 1}', 'equipos')" class="ml-1.5 text-cyan-400 hover:text-white px-1.5 py-0.5 rounded bg-gray-800 border border-gray-700 transition-colors"><i class="fas fa-edit text-[10px]"></i></button>`;

            if (yaUsado) {
                html += `
                    <div class="w-full text-left bg-gray-950/50 border border-gray-800/80 p-2 rounded-xl flex items-center justify-between opacity-35 select-none pointer-events-none mb-2">
                        <div class="truncate pr-1 text-gray-500">
                            <div class="flex items-center mb-0.5"><span class="font-bold text-[9px] uppercase text-gray-600">JUGADOR ${idx + 1}</span></div>
                            <span class="font-bold text-xs uppercase line-through block truncate">${bey.blade}</span>
                            <span class="text-[10px] line-through font-mono">${bey.ratchet} ${bey.bit}</span>
                        </div>
                        <span class="text-[8px] bg-gray-800 text-gray-500 px-1.5 py-0.5 rounded font-bold uppercase shrink-0"><i class="fas fa-check"></i></span>
                    </div>`;
            } else {
                disponiblesIds.push({ id: bey.id, user_id: bey.user_id });
                html += `
                    <button type="button" onclick="seleccionarBeyEquipo('${playerKey}', ${bey.id}, ${bey.user_id})" id="btn_bey_${bey.id}" class="bey-btn-${playerKey} w-full text-left bg-gray-900 border-2 border-gray-700 hover:border-gray-500 p-2 rounded-xl transition-all flex items-center justify-between shadow-sm mb-2">
                        <div class="truncate">
                            <div class="flex items-center mb-0.5"><span class="font-bold text-[9px] uppercase text-cyan-400">JUGADOR ${idx + 1}</span> ${editBtn}</div>
                            <span class="text-white font-bold text-xs uppercase block truncate ${bey.blade === 'DESCONOCIDO' ? 'text-gray-500 italic' : ''}">${bey.blade}</span>
                            <span class="text-gray-400 text-[10px] font-mono">${bey.ratchet} ${bey.bit}</span>
                        </div>
                    </button>`;
            }
        });
        
        container.innerHTML = html;
        if (disponiblesIds.length > 0) {
            seleccionarBeyEquipo(playerKey, disponiblesIds[0].id, disponiblesIds[0].user_id, true);
        }
    }

    function seleccionarBeyEquipo(playerKey, comboId, userId, isAuto = false) {
        document.getElementById(`batalla_${playerKey}_id`).value = userId;
        seleccionarBey(playerKey, comboId, isAuto);
    }

    async function enviarDueloRapidoArena() {
        triggerHaptic('heavy');
        const t1 = document.getElementById('team_id_1').value; 
        const t2 = document.getElementById('team_id_2').value;
        if (!t1 || !t2) { mostrarToast("Por favor, selecciona los clanes.", true); return; }
        if (t1 === t2) { mostrarToast("No pueden jugar contra sí mismos.", true); return; }
        
        const btn = document.getElementById('btnSubmitDueloArena'); 
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> ABRIENDO ARENA...';
        
        try {
            const res = await fetch('/api/torneos/duelo-rapido', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }, 
                body: JSON.stringify({ team1_id: t1, team2_id: t2 }) 
            });
            const data = await res.json();
            if (res.ok && data.success) { 
                mostrarToast("¡Arena Creada y Lista!"); cerrarModalDuelo(); cargarEventos(); abrirPanelArbitraje(data.event_id);
            } else mostrarToast(data.message || "Error al crear el duelo.", true); 
        } catch (error) { mostrarToast("Error de transmisión", true); }
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-bolt text-yellow-400 mr-2"></i> IR A LA ARENA (JUZGAR)';
    }

    function seleccionarBey(playerKey, comboId, isAuto = false) {
        if (!isAuto) triggerHaptic('light');
        const hadBothBefore = Boolean(battleState.p1ComboId && battleState.p2ComboId);

        if (playerKey === 'p1') battleState.p1ComboId = comboId;
        else battleState.p2ComboId = comboId;

        document.querySelectorAll(`.bey-btn-${playerKey}`).forEach(btn => {
            btn.classList.remove('bg-cyan-950', 'border-cyan-400', 'bg-red-950', 'border-red-400', 'shadow-[0_0_12px_rgba(6,182,212,0.4)]', 'shadow-[0_0_12px_rgba(239,68,68,0.4)]');
            btn.classList.add('bg-gray-900', 'border-gray-700');
        });

        const btn = document.getElementById(`btn_bey_${comboId}`);
        if (btn) {
            btn.classList.remove('bg-gray-900', 'border-gray-700');
            if (playerKey === 'p1') btn.classList.add('bg-cyan-950', 'border-cyan-400', 'shadow-[0_0_12px_rgba(6,182,212,0.4)]');
            else btn.classList.add('bg-red-950', 'border-red-400', 'shadow-[0_0_12px_rgba(239,68,68,0.4)]');
        }

        comprobarEstadoBatalla();

        if (!isAuto && !hadBothBefore && battleState.p1ComboId && battleState.p2ComboId) {
            setTimeout(() => {
                const anchor = document.getElementById('paso_ganador_anchor');
                if (anchor) anchor.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 120);
        }
    }

    function seleccionarGanador(playerKey) {
        triggerHaptic('light');
        battleState.winnerId = playerKey === 'p1' ? document.getElementById('batalla_p1_id').value : document.getElementById('batalla_p2_id').value;
        
        const btnP1 = document.getElementById('btn_ganador_p1');
        const btnP2 = document.getElementById('btn_ganador_p2');
        btnP1.className = "flex-1 bg-gray-900 border-2 border-gray-700 text-gray-400 font-bold py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-lg truncate";
        btnP2.className = "flex-1 bg-gray-900 border-2 border-gray-700 text-gray-400 font-bold py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-lg truncate";
        
        if (playerKey === 'p1') {
            btnP1.className = "flex-1 bg-cyan-700 border-2 border-cyan-300 text-white font-black py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-[0_0_15px_rgba(6,182,212,0.5)] truncate";
        } else {
            btnP2.className = "flex-1 bg-red-700 border-2 border-red-300 text-white font-black py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-[0_0_15px_rgba(239,68,68,0.5)] truncate";
        }
        
        comprobarEstadoBatalla();
    }

    function seleccionarFinal(tipo) {
        triggerHaptic('light');
        battleState.finishType = tipo;
        
        ['spin', 'over', 'burst', 'xtreme'].forEach(t => {
            const btn = document.getElementById(`btn_fin_${t}`);
            btn.classList.remove('bg-blue-900/80', 'border-blue-400', 'text-white', 'bg-yellow-900/80', 'border-yellow-400', 'bg-orange-900/80', 'border-orange-400', 'bg-fuchsia-900/80', 'border-fuchsia-400', 'shadow-[0_0_15px_rgba(192,38,211,0.5)]');
            btn.classList.add('bg-gray-900', 'border-gray-700', 'text-gray-400');
        });
        
        const btnActivo = document.getElementById(`btn_fin_${tipo}`);
        btnActivo.classList.remove('bg-gray-900', 'border-gray-700', 'text-gray-400');
        btnActivo.classList.add('text-white');
        
        if(tipo === 'spin') btnActivo.classList.add('bg-blue-900/80', 'border-blue-400');
        if(tipo === 'over') btnActivo.classList.add('bg-yellow-900/80', 'border-yellow-400');
        if(tipo === 'burst') btnActivo.classList.add('bg-orange-900/80', 'border-orange-400');
        if(tipo === 'xtreme') btnActivo.classList.add('bg-fuchsia-900/80', 'border-fuchsia-400', 'shadow-[0_0_15px_rgba(192,38,211,0.5)]');
        
        comprobarEstadoBatalla();
    }

    function comprobarEstadoBatalla() {
        if (battleState.p1ComboId && battleState.p2ComboId) {
            document.getElementById('zona_resultados').classList.remove('opacity-30', 'pointer-events-none');
        }

        const btnGuardar = document.getElementById('btn_guardar_batalla');
        if (battleState.p1ComboId && battleState.p2ComboId && battleState.winnerId && battleState.finishType) {
            btnGuardar.classList.remove('bg-gray-800', 'text-gray-500', 'pointer-events-none');
            btnGuardar.classList.add('bg-green-600', 'text-white', 'hover:bg-green-500', 'shadow-[0_0_20px_rgba(22,163,74,0.6)]');
            const ptsMap = { spin: 1, over: 2, burst: 2, xtreme: 3 };
            btnGuardar.innerHTML = `<i class="fas fa-check-circle mr-1.5"></i> CONFIRMAR (+${ptsMap[battleState.finishType]} PTS)`;
        } else {
            btnGuardar.classList.add('bg-gray-800', 'text-gray-500', 'pointer-events-none');
            btnGuardar.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-500', 'shadow-[0_0_20px_rgba(22,163,74,0.6)]');
            btnGuardar.innerHTML = "SELECCIONA BEYS Y RESULTADO";
        }
    }

    function resetBatallaUI() {
        battleState.p1ComboId = null;
        battleState.p2ComboId = null;
        battleState.winnerId = null;
        battleState.finishType = null;
        
        document.getElementById('zona_resultados').classList.add('opacity-30', 'pointer-events-none');
        document.getElementById('btn_ganador_p1').className = "flex-1 bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg truncate";
        document.getElementById('btn_ganador_p2').className = "flex-1 bg-gray-900 border-2 border-gray-700 text-gray-300 font-black py-2.5 px-2 rounded-xl text-xs uppercase tracking-wider transition-all shadow-lg truncate";

        const manualBox = document.getElementById('manual_score_box');
        if (manualBox) manualBox.classList.add('hidden');

        ['spin', 'over', 'burst', 'xtreme'].forEach(t => {
            const btn = document.getElementById(`btn_fin_${t}`);
            if(!btn) return;
            btn.classList.remove('bg-blue-900/80', 'border-blue-400', 'text-white', 'bg-yellow-900/80', 'border-yellow-400', 'bg-orange-900/80', 'border-orange-400', 'bg-fuchsia-900/80', 'border-fuchsia-400', 'shadow-[0_0_15px_rgba(192,38,211,0.5)]');
            btn.classList.add('bg-gray-900', 'border-gray-700', 'text-gray-300');
        });

        const btnGuardar = document.getElementById('btn_guardar_batalla');
        btnGuardar.className = "w-full bg-gray-800 text-gray-500 font-black py-3 rounded-xl text-xs sm:text-sm tracking-widest uppercase transition-all shadow-lg pointer-events-none";
        btnGuardar.innerHTML = "SELECCIONA BEYS Y RESULTADO";
        
        const scrollCont = document.getElementById('batallaScrollContainer');
        if (scrollCont) scrollCont.scrollTop = 0;
    }

    function cerrarBatalla() {
        triggerHaptic('light');
        const modal = document.getElementById('batallaModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        unlockMatchOnServer(battleState.matchId);
        refrescarVistaArenaActual();
    }

    async function guardarBatalla() {
        triggerHaptic('heavy');
        const btn = document.getElementById('btn_guardar_batalla');
        btn.classList.add('pointer-events-none'); 
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> REGISTRANDO...';

        try {
            const res = await fetch(`/api/torneos/partida/${battleState.matchId}/batalla`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({
                    player1_combo_id: battleState.p1ComboId,
                    player2_combo_id: battleState.p2ComboId,
                    winner_id: battleState.winnerId,
                    finish_type: battleState.finishType
                })
            });
            const data = await res.json();
            
            if (data.success) {
                const p1Score = document.getElementById('batalla_score_p1');
                const p2Score = document.getElementById('batalla_score_p2');
                p1Score.innerText = data.p1_score;
                p2Score.innerText = data.p2_score;
                
                if(battleState.winnerId == document.getElementById('batalla_p1_id').value) {
                    p1Score.classList.add('scale-125', 'text-white'); setTimeout(() => p1Score.classList.remove('scale-125', 'text-white'), 300);
                } else {
                    p2Score.classList.add('scale-125', 'text-white'); setTimeout(() => p2Score.classList.remove('scale-125', 'text-white'), 300);
                }

                if (data.match_completed) {
                    mostrarToast("🏆 ¡PARTIDA FINALIZADA Y CUADRO ACTUALIZADO!");
                    setTimeout(() => cerrarBatalla(), 650);
                } else {
                    mostrarToast("✅ Punto anotado. Siguiente lanzamiento.");
                    await abrirBatalla(battleState.matchId);
                }
            } else {
                mostrarToast(data.message, true);
                comprobarEstadoBatalla();
            }
        } catch (error) {
            mostrarToast("Error de red al guardar", true);
            comprobarEstadoBatalla();
        }
    }

    // --- CLASIFICACIÓN EN VIVO ---
    async function verClasificacionArena(eventId) {
        triggerHaptic('light');
        currentArenaTab = 'tabla';
        const container = document.getElementById('arbitrajeContent');
        const footer = document.getElementById('arbitrajeFooter');
        if(footer) footer.classList.add('hidden');
        
        container.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-cyan-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">CALCULANDO CLASIFICACIÓN...</p></div>';
        
        try {
            const res = await fetch(`/api/torneos/${eventId}/clasificacion`);
            const data = await res.json();
            
            const isEliminatoria = data.is_bracket;
            const thPts = isEliminatoria ? 'RESULTADO' : 'PTS';
            
            let htmlTemp = `
                <div class="flex bg-gray-900 border border-gray-800 rounded-xl p-1 mb-4">
                    <button onclick="volverACrucesArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-colors"><i class="fas fa-swords mr-1"></i> CRUCES</button>
                    <button class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-cyan-400 bg-gray-800 font-black transition-colors"><i class="fas fa-list-ol mr-1"></i> TABLA</button>
                    <button onclick="verCuadroArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-colors"><i class="fas fa-sitemap mr-1"></i> CUADRO</button>
                </div>
                <div class="bg-gray-900/90 border border-gray-800 rounded-xl overflow-hidden shadow-lg">
                    <table class="w-full text-left text-sm text-gray-300">
                        <thead class="bg-gray-950 text-[10px] sm:text-xs uppercase text-gray-500 font-bold tracking-widest border-b border-gray-800">
                            <tr>
                                <th class="px-3 py-3 w-10 text-center">#</th>
                                <th class="px-2 py-3">BLADER</th>
                                <th class="px-2 py-3 text-center">${thPts}</th>
                                ${!isEliminatoria ? '<th class="px-3 py-3 text-center">BCH</th>' : ''}
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/70">
            `;
            
            const jugadores = data.clasificacion || data.standings || [];
            
            if (jugadores.length > 0) {
                let currentRank = 1;
                let prevPlayer = null;

                jugadores.forEach((player, index) => {
                    const actualPosition = index + 1;
                    if (prevPlayer) {
                        let isTie = false;
                        if (isEliminatoria && player.bracket_score > 0 && prevPlayer.bracket_score > 0) {
                            isTie = (player.bracket_score === prevPlayer.bracket_score) && (player.puesto === prevPlayer.puesto);
                        } else {
                            isTie = (player.bracket_score === prevPlayer.bracket_score) && (player.points === prevPlayer.points) && (player.buchholz === prevPlayer.buchholz) && (player.puesto === prevPlayer.puesto);
                        }
                        if (!isTie) currentRank = actualPosition;
                    } else currentRank = 1;
                    
                    prevPlayer = player;

                    const isTop = currentRank <= 4 ? 'bg-cyan-900/10' : ''; 
                    let rankColor = currentRank === 1 ? 'text-yellow-400' : (currentRank <= 4 ? 'text-cyan-400' : 'text-gray-500');
                    
                    let ptsColumn = `<td class="px-2 py-3 text-center font-black font-mono text-yellow-400">${player.points}</td>`;
                    let bchColumn = `<td class="px-3 py-3 text-center font-mono text-gray-400 text-xs">${player.buchholz}</td>`;
                    
                    if (isEliminatoria) {
                        bchColumn = '';
                        if (currentRank === 1 && player.bracket_score > 8000) ptsColumn = `<td class="px-2 py-3 text-center font-bold text-[10px] text-yellow-400 bg-yellow-900/30 uppercase tracking-widest"><i class="fas fa-trophy mr-1"></i> CAMPEÓN</td>`;
                        else if (currentRank === 2 && player.bracket_score > 8000) ptsColumn = `<td class="px-2 py-3 text-center font-bold text-[10px] text-gray-300 bg-gray-700/50 uppercase tracking-widest">FINALISTA</td>`;
                        else if (currentRank === 3 && player.bracket_score > 0) ptsColumn = `<td class="px-2 py-3 text-center font-bold text-[10px] text-orange-400 bg-orange-900/30 uppercase tracking-widest">TOP 3</td>`;
                        else if (currentRank === 4 && player.bracket_score > 0) ptsColumn = `<td class="px-2 py-3 text-center font-bold text-[10px] text-cyan-400 bg-cyan-900/30 uppercase tracking-widest">TOP 4</td>`;
                        else if (player.bracket_score > 0) ptsColumn = `<td class="px-2 py-3 text-center font-bold text-[10px] text-fuchsia-400 uppercase tracking-widest">TOP ${currentRank}</td>`;
                        else ptsColumn = `<td class="px-2 py-3 text-center font-mono text-gray-600 text-[10px] tracking-widest">SUÍZO</td>`; 
                    }
                    
                    htmlTemp += `
                        <tr class="${isTop} hover:bg-gray-800/40 transition-colors">
                            <td class="px-3 py-3 text-center font-mono font-bold ${rankColor}">${currentRank}</td>
                            <td class="px-2 py-3 font-bold text-white uppercase tracking-wide truncate max-w-[140px]">${player.name || player.user_name}</td>
                            ${ptsColumn}${bchColumn}
                        </tr>`;
                });
            } else {
                htmlTemp += `<tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 font-bold tracking-widest text-xs">NO HAY RESULTADOS AÚN</td></tr>`;
            }
            
            htmlTemp += `</tbody></table></div>`;
            container.innerHTML = htmlTemp;
        } catch (error) {
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error al cargar la clasificación.</p>';
        }
    }

    let currentTotalPlayers = 0;

    function abrirModalTopCut() {
        triggerHaptic('light');
        document.getElementById('topCutModal').classList.remove('hidden');
        document.getElementById('topCutModal').classList.add('flex', 'active');
        
        let minTop = 4;
        if (currentTotalPlayers >= 25) minTop = 8;
        if (currentTotalPlayers >= 41) minTop = 16;
        if (currentTotalPlayers >= 57) minTop = 32;

        [4, 8, 16, 32].forEach(size => {
            const btn = document.getElementById(`btn_top_${size}`);
            if (size < minTop || size > currentTotalPlayers) {
                btn.disabled = true; btn.classList.add('opacity-30', 'pointer-events-none');
            } else {
                btn.disabled = false; btn.classList.remove('opacity-30', 'pointer-events-none');
            }
        });
    }

    async function ejecutarTopCut(size) {
        if(!confirm(`¿Seguro que quieres cerrar la fase suiza y generar el TOP ${size}?`)) return;
        triggerHaptic('heavy');
        document.getElementById('topCutModal').classList.remove('flex', 'active');
        document.getElementById('topCutModal').classList.add('hidden');
        
        try {
            const res = await fetch(`/api/torneos/${currentArenaEventId}/arena/top`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ top_size: size })
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if(data.success) abrirPanelArbitraje(currentArenaEventId);
        } catch(e) { mostrarToast("Error generando top cut", true); }
    }

    // --- CUADRO INTERACTIVO CON FLECHAS DENTRO DEL MODAL ---
    async function verCuadroArena(eventId) {
        triggerHaptic('light');
        currentArenaTab = 'cuadro';
        const container = document.getElementById('arbitrajeContent');
        const footer = document.getElementById('arbitrajeFooter');
        if(footer) footer.classList.add('hidden');

        container.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-fuchsia-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">DIBUJANDO ÁRBOL COMPLETO...</p></div>';
        
        try {
            const res = await fetch(`/api/torneos/${eventId}/cuadro/html`);
            const htmlBracket = await res.text();
            
            let htmlTemp = `
                <div class="flex bg-gray-900 border border-gray-800 rounded-xl p-1 mb-3">
                    <button onclick="volverACrucesArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-colors"><i class="fas fa-swords mr-1"></i> CRUCES</button>
                    <button onclick="verClasificacionArena(${eventId})" class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-gray-400 hover:text-white font-bold transition-colors"><i class="fas fa-list-ol mr-1"></i> TABLA</button>
                    <button class="flex-1 text-center text-[11px] tracking-widest py-2 rounded-lg text-fuchsia-400 bg-gray-800 font-black transition-colors"><i class="fas fa-sitemap mr-1"></i> CUADRO</button>
                </div>
            ` + htmlBracket;
            
            container.innerHTML = htmlTemp;
            setTimeout(dibujarFlechasCuadroModal, 80);
        } catch (error) {
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error de conexión al cargar el cuadro.</p>';
        }
    }

    // Al tocar una partida en el árbol del cuadro, abre directamente la partida para juzgarla o editarla
    function accionRapidaDesdeCuadro(matchId, status) {
        if (currentArenaMode !== 'judge') return;
        triggerHaptic('medium');
        abrirBatalla(matchId);
    }

    function dibujarFlechasCuadroModal() {
        const svg = document.getElementById('arena-bracket-svg');
        const tree = document.getElementById('arena-bracket-tree');
        if (!svg || !tree) return;

        const defs = svg.querySelector('defs').outerHTML;
        svg.innerHTML = defs;

        const getLeft = (el) => { let l = 0; while(el && el !== tree) { l += el.offsetLeft; el = el.offsetParent; } return l; };
        const getTop = (el) => { let t = 0; while(el && el !== tree) { t += el.offsetTop; el = el.offsetParent; } return t; };

        const trazar = (src, tgt, color, marker, isComp) => {
            const x1 = getLeft(src) + src.offsetWidth;
            const y1 = getTop(src) + (src.offsetHeight / 2);
            const x2 = getLeft(tgt) - 4;
            const y2 = getTop(tgt) + (tgt.offsetHeight / 2);
            const dx = (x2 - x1) * 0.5;
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', color);
            path.setAttribute('stroke-width', isComp ? '2.5' : '1.5');
            if (!isComp) path.setAttribute('stroke-dasharray', '4,4');
            path.setAttribute('marker-end', `url(#${marker})`);
            svg.appendChild(path);
        };

        const conectar = (type, color, marker) => {
            const cols = Array.from(tree.querySelectorAll(`.arena-b-col[data-bracket="${type}"]`));
            for (let c = 0; c < cols.length - 1; c++) {
                const curr = Array.from(cols[c].querySelectorAll('.arena-match-node'));
                const next = Array.from(cols[c + 1].querySelectorAll('.arena-match-node'));
                curr.forEach((m, i) => {
                    const winId = m.getAttribute('data-winner');
                    let tgt = winId ? next.find(n => n.getAttribute('data-p1') === winId || n.getAttribute('data-p2') === winId) : null;
                    if (!tgt && next.length > 0) tgt = (curr.length === next.length) ? next[i] : (next[Math.floor(i / 2)] || next[next.length - 1]);
                    if (tgt) {
                        const isComp = m.getAttribute('data-status') === 'completed' && Boolean(winId);
                        trazar(m, tgt, isComp ? color : '#374151', isComp ? marker : 'ar-pending', isComp);
                    }
                });
            }
        };

        conectar('winners', '#d946ef', 'ar-win-fuchsia');
        conectar('losers', '#f97316', 'ar-win-orange');

        const finalsCols = Array.from(tree.querySelectorAll(`.arena-b-col[data-bracket="finals"]`));
        if (finalsCols.length > 0) {
            const gf = finalsCols[0].querySelector('.arena-match-node');
            const wCols = Array.from(tree.querySelectorAll(`.arena-b-col[data-bracket="winners"]`));
            const lCols = Array.from(tree.querySelectorAll(`.arena-b-col[data-bracket="losers"]`));
            if (gf && wCols.length > 0) {
                const lw = wCols[wCols.length - 1].querySelector('.arena-match-node');
                if (lw) trazar(lw, gf, lw.getAttribute('data-status') === 'completed' ? '#eab308' : '#374151', lw.getAttribute('data-status') === 'completed' ? 'ar-win-yellow' : 'ar-pending', lw.getAttribute('data-status') === 'completed');
            }
            if (gf && lCols.length > 0) {
                const ll = lCols[lCols.length - 1].querySelector('.arena-match-node');
                if (ll) trazar(ll, gf, ll.getAttribute('data-status') === 'completed' ? '#eab308' : '#374151', ll.getAttribute('data-status') === 'completed' ? 'ar-win-yellow' : 'ar-pending', ll.getAttribute('data-status') === 'completed');
            }
        }
    }

    async function deshacerUltimoRegistro() {
        if (!battleState.matchId) return;
        triggerHaptic('heavy');
        if(!confirm("¿Deshacer el último lanzamiento o resultado de este combate? (Si el jugador ya había avanzado en el cuadro, se revertirá su avance automáticamente).")) return;
        
        try {
            const res = await fetch(`/api/torneos/partida/${battleState.matchId}/undo`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                mostrarToast(data.message);
                await abrirBatalla(battleState.matchId);
            } else mostrarToast(data.message || "No hay acciones para deshacer", true);
        } catch (error) { mostrarToast("Error de red", true); }
    }

    async function lanzarWarning(playerKey) {
        if (!battleState.matchId) return;
        triggerHaptic('medium');
        try {
            const res = await fetch(`/api/torneos/partida/${battleState.matchId}/warning`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: JSON.stringify({ player: playerKey })
            });
            const data = await res.json();
            
            if (data.success) {
                mostrarToast(data.message);
                await abrirBatalla(battleState.matchId);
                if (data.match_completed) {
                    setTimeout(() => cerrarBatalla(), 700);
                }
            }
        } catch (error) { mostrarToast("Error al asignar warning", true); }
    }

    function alternarLadosBatalla() {
        triggerHaptic('light');
        isSideSwapped = !isSideSwapped;
        aplicarEstadoLados();
    }

    function aplicarEstadoLados() {
        const headerRow = document.getElementById('batallaPlayersHeader');
        const decksRow = document.getElementById('batallaDecksContainer');
        const winnersRow = document.getElementById('batallaFinishButtonsContainer');
        const manualRow = document.getElementById('batallaManualScoreInputs');

        [headerRow, decksRow, winnersRow, manualRow].forEach(el => {
            if (!el) return;
            if (isSideSwapped) el.classList.add('flex-row-reverse');
            else el.classList.remove('flex-row-reverse');
        });
    }

    function obtenerCombosUsadosEnCiclo(battles) {
        if (!battles || !Array.isArray(battles)) return { p1Used: [], p2Used: [], realBattlesCount: 0 };

        const realBattles = battles.filter(b => 
            ['spin', 'over', 'burst', 'xtreme'].includes(b.finish_type) && 
            b.player1_combo_id && b.player2_combo_id
        );

        const remainder = realBattles.length % 3;
        if (remainder === 0) return { p1Used: [], p2Used: [], realBattlesCount: realBattles.length };

        const currentSetBattles = realBattles.slice(-remainder);
        return {
            p1Used: currentSetBattles.map(b => Number(b.player1_combo_id)),
            p2Used: currentSetBattles.map(b => Number(b.player2_combo_id)),
            realBattlesCount: realBattles.length
        };
    }

    function obtenerCombosUsadosEquipo(battles, playerKey, teamSize) {
        if (!battles || !Array.isArray(battles) || teamSize === 0) return [];
        const realBattles = battles.filter(b => ['spin', 'over', 'burst', 'xtreme'].includes(b.finish_type));
        const remainder = realBattles.length % teamSize;
        if (remainder === 0) return [];
        const currentSet = realBattles.slice(-remainder);
        return currentSet.map(b => playerKey === 'p1' ? Number(b.player1_combo_id) : Number(b.player2_combo_id));
    }

    async function lockMatchOnServer(matchId) {
        try {
            const res = await fetch(`/api/torneos/partida/${matchId}/lock`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            return await res.json();
        } catch (e) { return { success: true }; }
    }

    async function unlockMatchOnServer(matchId) {
        if (!matchId) return;
        try {
            await fetch(`/api/torneos/partida/${matchId}/unlock`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
        } catch (e) {}
    }

    // ==========================================
    // CREADOR/EDITOR DE EVENTOS DESDE EL HUB
    // ==========================================
    function abrirModalEventManager(eventData = null) {
        triggerHaptic('medium');
        const modal = document.getElementById('eventManagerModal');
        const form = document.getElementById('eventManagerForm');
        const footer = document.getElementById('eventManagerFooter');
        
        const selectRegionHub = document.getElementById('filtro-region');
        const selectRegionManage = document.getElementById('manage_region_id');
        if (selectRegionManage.options.length <= 1) {
            selectRegionManage.innerHTML = selectRegionHub.innerHTML;
            selectRegionManage.options[0].text = "Selecciona Región...";
            selectRegionManage.options[0].disabled = true;
        }

        form.reset();
        
        if (eventData) {
            document.getElementById('eventManagerTitle').innerHTML = '<i class="fas fa-edit text-yellow-400 mr-2"></i> EDITAR EVENTO';
            document.getElementById('manage_event_id').value = eventData.id;
            document.getElementById('manage_name').value = eventData.name;
            document.getElementById('manage_mode').value = eventData.mode;
            document.getElementById('manage_imagen').value = eventData.beys || 'quedada';
            document.getElementById('manage_region_id').value = eventData.region_id;
            document.getElementById('manage_city').value = eventData.city;
            document.getElementById('manage_location').value = eventData.location;
            document.getElementById('manage_date').value = eventData.date.substring(0, 10);
            document.getElementById('manage_time').value = eventData.time.substring(0, 5);
            document.getElementById('manage_note').value = eventData.note || '';
            document.getElementById('manage_stadiums').value = eventData.stadiums;
            document.getElementById('manage_max_players').value = eventData.max_players || '';
            
            actualizarReglasEventManager(eventData.deck, eventData.configuration);
            
            footer.innerHTML = `
                <button type="button" onclick="eliminarEventoAPI(${eventData.id})" class="flex-none bg-red-900/40 hover:bg-red-800 text-red-400 hover:text-white border border-red-500/50 font-bold p-3 rounded-lg transition-colors"><i class="fas fa-trash"></i></button>
                <button type="submit" form="eventManagerForm" id="btnSubmitManager" class="flex-1 bg-yellow-600 hover:bg-yellow-500 text-white font-bold py-3 rounded-lg shadow-lg transition-all">ACTUALIZAR</button>
            `;
        } else {
            document.getElementById('eventManagerTitle').innerHTML = '<i class="fas fa-calendar-plus text-cyan-400 mr-2"></i> NUEVO EVENTO';
            document.getElementById('manage_event_id').value = '';
            document.getElementById('manage_note').value = '';
            actualizarReglasEventManager();
            
            footer.innerHTML = `
                <button type="button" onclick="cerrarModalEventManager()" class="flex-1 bg-gray-800 text-gray-400 hover:text-white font-bold py-3 rounded-lg transition-colors">CANCELAR</button>
                <button type="submit" form="eventManagerForm" id="btnSubmitManager" class="flex-1 bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-lg shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all">CREAR EVENTO</button>
            `;
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    async function enviarEventManager(e) {
        e.preventDefault();
        triggerHaptic('heavy');
        
        const form = document.getElementById('eventManagerForm');
        const formData = new FormData(form);
        const eventId = document.getElementById('manage_event_id').value;
        const isEdit = eventId !== '';
        
        const url = isEdit ? `/api/eventos/${eventId}` : '/api/eventos';
        if (isEdit) formData.append('_method', 'PUT');

        const btn = document.getElementById('btnSubmitManager');
        const oldText = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GUARDANDO...';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                body: formData
            });
            const data = await res.json();
            if (res.ok && data.success) {
                mostrarToast(data.message); cerrarModalEventManager(); cargarEventos();
            } else {
                mostrarToast(data.message || "Faltan datos obligatorios.", true); triggerHaptic('error');
                btn.disabled = false; btn.innerHTML = oldText;
            }
        } catch (error) {
            mostrarToast("Error de conexión al servidor.", true); triggerHaptic('error');
            btn.disabled = false; btn.innerHTML = oldText;
        }
    }

    function cerrarModalEventManager() {
        triggerHaptic('light');
        document.getElementById('eventManagerModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function actualizarReglasEventManager(forceDeck = null, forceConfig = null) {
        const mode = document.getElementById('manage_mode').value;
        const type = document.getElementById('manage_imagen').value;
        const deckSelect = document.getElementById('manage_deck');
        const configSelect = document.getElementById('manage_configuration');

        const isCrewBattle = (type === 'crew_battle' || type === 'equipos');

        if (isCrewBattle) {
            deckSelect.innerHTML = '<option value="1on1">1 Beyblade por Jugador</option>';
            configSelect.innerHTML = '<option value="RoundRobin">Guerra de Clanes (Round Robin)</option>';
        } else if (mode === 'beybladex') {
            deckSelect.innerHTML = '<option value="3on3">3on3 (3 Beys)</option>';
            configSelect.innerHTML = `<option value="Swiss">Sistema Suizo</option><option value="DoubleElimination">Doble Eliminación</option><option value="SingleElimination">Eliminación Simple</option><option value="RoundRobin">Round Robin</option>`;
        } else {
            deckSelect.innerHTML = '<option value="3on3">3on3 (3 Beys)</option><option value="5g">5G (5 Beys)</option>';
            configSelect.innerHTML = `<option value="Swiss">Sistema Suizo</option><option value="DoubleElimination">Doble Eliminación</option><option value="SingleElimination">Eliminación Simple</option><option value="RoundRobin">Round Robin</option>`;
        }
        
        if (forceDeck) deckSelect.value = forceDeck;
        if (forceConfig) configSelect.value = forceConfig;
    }

    async function eliminarEventoAPI(eventId) {
        if (!confirm("🚨 PELIGRO: ¿Estás seguro de que quieres ELIMINAR este evento por completo?")) return;
        triggerHaptic('error');
        try {
            const res = await fetch(`/api/eventos/${eventId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const data = await res.json();
            mostrarToast(data.message, !data.success);
            if (data.success) { cerrarModalEventManager(); cargarEventos(); }
        } catch (error) { mostrarToast("Error de red.", true); }
    }

    // Exportación global segura para todos los eventos del modal
    window.alternarLadosBatalla = alternarLadosBatalla;
    window.deshacerUltimoRegistro = deshacerUltimoRegistro;
    window.cerrarBatalla = cerrarBatalla;
    window.lanzarWarning = lanzarWarning;
    window.declararNoPresentado = declararNoPresentado;
    window.toggleMarcadorManualBox = toggleMarcadorManualBox;
    window.guardarMarcadorManual = guardarMarcadorManual;
    window.seleccionarGanador = seleccionarGanador;
    window.seleccionarFinal = seleccionarFinal;
    window.guardarBatalla = guardarBatalla;

    // --- PWA SETUP ---
    let deferredPrompt; const installBanner = document.getElementById('installBanner'); const btnInstall = document.getElementById('btnInstall');
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream; const isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;
    if (isIOS && !isStandalone) { document.getElementById('iosInstallBanner').classList.remove('hidden'); }
    window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; installBanner.classList.remove('hidden'); });
    btnInstall.addEventListener('click', async () => { if (deferredPrompt) { deferredPrompt.prompt(); const { outcome } = await deferredPrompt.userChoice; deferredPrompt = null; installBanner.classList.add('hidden'); if (outcome === 'accepted') mostrarToast("⏳ Instalando..."); } });
    window.addEventListener('appinstalled', () => { installBanner.classList.add('hidden'); mostrarToast("✅ App instalada"); });
    if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('/sw.js').catch(e => console.error('Error PWA: ', e)); }); }
</script>