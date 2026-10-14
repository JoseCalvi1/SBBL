<div id="toast" class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-3 rounded-lg shadow-2xl z-50 font-bold border-l-4 transition-opacity duration-300 opacity-0 pointer-events-none text-sm w-11/12 max-w-sm text-center">Mensaje</div>

<script>
    let dbPiezas = null;
    let allTeamsList = [];
    
    // --- UTILIDADES DE VIBRACIÓN HÁPTICA ---
    function triggerHaptic(type = 'light') {
        if (!navigator.vibrate) return;
        if (type === 'light') navigator.vibrate(15);
        else if (type === 'medium') navigator.vibrate(40);
        else if (type === 'heavy') navigator.vibrate([30, 50, 30]);
        else if (type === 'error') navigator.vibrate([50, 100, 50, 100, 50]);
    }

    function mostrarToast(msg, isError = false) {
        const toast = document.getElementById('toast'); toast.innerText = msg;
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 px-4 py-3 rounded-lg shadow-2xl z-50 font-bold border-l-4 transition-opacity duration-300 text-sm w-11/12 max-w-sm text-center ${isError ? 'bg-gray-800 text-red-400 border-red-500' : 'bg-gray-800 text-cyan-400 border-cyan-500'}`;
        toast.style.opacity = 1; setTimeout(() => toast.style.opacity = 0, 3500);
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

    // --- ARENA ---
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
        contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-10 animate-pulse">Sincronizando estadio...</p>';
        try {
            const res = await fetch(`/api/torneos?tipo=${tipo}&region=${region}`);
            if (!res.ok) throw new Error('Error en torneos');
            const torneos = await res.json();
            if (torneos.length === 0) { contenedor.innerHTML = '<p class="text-gray-500 text-sm text-center mt-5 font-bold tracking-widest">NO HAY EVENTOS.</p>'; return; }
            let htmlTemp = '';
            torneos.forEach(torneo => {
                const fecha = new Date(torneo.date).toLocaleDateString('es-ES');
                const isRank = torneo.beys === 'ranking' || torneo.beys === 'rankingplus';
                const badge = isRank ? 'bg-red-900 text-red-400' : 'bg-cyan-900 text-cyan-400';
                const cardTheme = torneo.is_inscribed ? 'bg-cyan-900/20 border-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.15)]' : 'bg-gray-900 border-gray-700 shadow-lg';
                const limitText = torneo.aforo_maximo > 1000 ? '∞' : torneo.aforo_maximo;
                const aforoLleno = torneo.total_inscritos >= torneo.aforo_maximo;

                let botonesHTML = '';
                if (torneo.status === 'LIVE') {
                    if (torneo.is_judge_assigned) {
                        botonesHTML = `<button onclick="abrirPanelArbitraje(${torneo.id})" class="w-full bg-yellow-700 active:bg-yellow-600 border border-yellow-500 text-white font-bold py-3 rounded-lg text-sm tracking-wide shadow-[0_0_15px_rgba(234,179,8,0.3)] transition-colors relative z-10"><i class="fas fa-tablet-alt mr-2"></i> PANEL DE ARENA</button>`;
                    } else {
                        // Vista pública para jugadores normales cuando está en LIVE
                        botonesHTML = `<div class="flex gap-2 relative z-10">
                            <button onclick="abrirArenaPublica(${torneo.id})" class="flex-1 bg-fuchsia-700 active:bg-fuchsia-600 border border-fuchsia-500 text-white font-bold py-3 rounded-lg text-sm tracking-wide shadow-[0_0_15px_rgba(192,38,211,0.3)] transition-colors"><i class="fas fa-eye mr-2"></i> VER ARENA</button>
                            ${torneo.is_inscribed ? `<button onclick="abrirModal(${torneo.id}, '${torneo.status}')" class="w-14 bg-cyan-900/50 border border-cyan-500 text-cyan-400 font-bold py-3 rounded-lg text-sm transition-colors flex items-center justify-center"><i class="fas fa-layer-group"></i></button>` : ''}
                        </div>`;
                    }
                } else if (torneo.is_inscribed) {
                    botonesHTML = `<div class="flex gap-2 relative z-10"><button onclick="abrirModal(${torneo.id}, '${torneo.status}')" class="flex-1 bg-cyan-700 active:bg-cyan-600 border border-cyan-400 text-white font-bold py-3 rounded-lg text-sm tracking-wide transition-colors"><i class="fas fa-layer-group me-1"></i> MI DECK</button><button onclick="cancelarAsistencia(${torneo.id})" class="w-14 bg-red-900/50 active:bg-red-800 border border-red-500 text-red-400 font-bold py-3 rounded-lg text-sm transition-colors flex items-center justify-center"><i class="fas fa-times"></i></button></div>`;
                } else {
                    botonesHTML = `<button onclick="abrirModal(${torneo.id}, '${torneo.status}')" class="w-full bg-cyan-600 active:bg-cyan-500 text-white font-bold py-3 rounded-lg text-sm tracking-wide relative z-10 transition-colors">INSCRIBIRSE</button>`;
                }

                let juecesHTML = '';
                if (torneo.jueces_asignados && torneo.jueces_asignados.length > 0) {
                    const nombresJueces = torneo.jueces_asignados.map(j => j.name).join(', ');
                    juecesHTML = `<div class="mt-3 relative z-10 flex items-start gap-2 bg-gray-950/50 p-2.5 rounded-lg border border-gray-800 shadow-inner"><i class="fas fa-gavel text-yellow-600 mt-0.5"></i><div><span class="text-[9px] text-gray-500 font-bold tracking-widest uppercase block mb-0.5">Staff Oficial</span><span class="text-xs text-gray-300 font-bold leading-tight">${nombresJueces}</span></div></div>`;
                }

                let panelJuez = '';
                if (torneo.user_is_judge) {
                    const btnJuezText = torneo.is_judge_assigned ? '<i class="fas fa-user-minus"></i> SALIR' : '<i class="fas fa-user-plus"></i> ARBITRAR';
                    const btnJuezColor = torneo.is_judge_assigned ? 'bg-red-900/50 border-red-500 text-red-400 hover:bg-red-800' : 'bg-yellow-900/50 border-yellow-500 text-yellow-400 hover:bg-yellow-800';
                    let botonesExclusivos = '';
                    if (torneo.is_judge_assigned) {
                        botonesExclusivos = `<button onclick="copiarNombres(${torneo.id})" class="flex-1 bg-gray-800 border border-gray-600 text-gray-300 hover:text-white font-bold py-2 rounded-lg text-xs transition-colors shadow-sm"><i class="fas fa-copy text-cyan-500 mr-1"></i> LISTA</button>${torneo.status === 'OPEN' ? `<button onclick="comenzarTorneo(${torneo.id})" class="flex-1 bg-green-900/50 border border-green-500 text-green-400 font-bold py-2 rounded-lg text-xs transition-colors shadow-sm"><i class="fas fa-play mr-1"></i> INICIAR</button>` : ''}`;
                    }
                    panelJuez = `<div class="mt-4 pt-3 border-t border-gray-700/50 flex gap-2 relative z-10"><button onclick="toggleJuez(${torneo.id})" class="${torneo.is_judge_assigned ? 'w-24' : 'w-full'} ${btnJuezColor} border font-bold py-2 rounded-lg text-xs transition-colors shadow-sm">${btnJuezText}</button>${botonesExclusivos}</div>`;
                }

                htmlTemp += `
                    <div class="${cardTheme} p-4 rounded-xl transition-all duration-300 border relative overflow-hidden">
                        ${torneo.is_inscribed ? '<div class="absolute top-0 right-0 w-16 h-16 bg-cyan-500/10 rounded-bl-full"></div>' : ''}
                        <div class="flex justify-between items-start mb-3 relative z-10">
                            <h3 class="text-white font-bold text-lg leading-tight">${torneo.name}</h3><span class="text-[10px] ${badge} px-2 py-1 rounded-md font-bold uppercase shrink-0 ms-2">${torneo.beys || 'EVENTO'}</span>
                        </div>
                        <div class="flex justify-between items-end mb-5 relative z-10">
                            <p class="text-xs text-gray-400 flex flex-col gap-2"><span><i class="fas fa-map-marker-alt w-4 text-center text-red-500"></i> ${torneo.location || torneo.city || '---'}</span><span><i class="fas fa-calendar-alt w-4 text-center text-cyan-500"></i> ${fecha}</span></p>
                            <button onclick="abrirListaJugadores(${torneo.id}, ${torneo.aforo_maximo})" class="bg-gray-950 border border-gray-700 rounded-lg p-2 text-center shadow-inner active:bg-gray-800 transition-colors">
                                <div class="text-[10px] text-gray-500 font-bold tracking-widest mb-1">BLADERS</div><div class="font-mono font-bold text-lg leading-none ${aforoLleno ? 'text-red-400' : 'text-cyan-400'}">${torneo.total_inscritos} <span class="text-xs text-gray-600">/ ${limitText}</span></div>
                            </button>
                        </div>
                        ${aforoLleno && !torneo.is_inscribed ? '<p class="text-xs text-red-400 font-bold mb-3 uppercase tracking-wider relative z-10"><i class="fas fa-exclamation-triangle"></i> Entrarás en lista de espera</p>' : ''}
                        ${juecesHTML}
                        <div class="mt-4">${botonesHTML}${panelJuez}</div>
                    </div>`;
            });
            contenedor.innerHTML = htmlTemp;
        } catch (error) { contenedor.innerHTML = '<p class="text-red-500 text-sm text-center">Error al sincronizar.</p>'; }
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
    let metaDataList = []; // Guardamos el array de combos globalmente

    async function cargarMeta() {
        const tiempo = document.getElementById('meta-filtro-tiempo') ? document.getElementById('meta-filtro-tiempo').value : '30';
        const minP = document.getElementById('meta-filtro-rango') ? document.getElementById('meta-filtro-rango').value : '5';
        
        const contenedor = document.getElementById('lista-meta');
        contenedor.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-purple-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">ANALIZANDO EL META ACTUAL...</p></div>';
        
        try {
            // Mandamos los días a la API
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

                let styleIcon = 'fa-star';
                if(combo.estilo_combate === 'BURST ATTACKER') styleIcon = 'fa-bomb';
                if(combo.estilo_combate === 'XTREME ASSASSIN') styleIcon = 'fa-bolt';
                if(combo.estilo_combate === 'SPIN FINISHER') styleIcon = 'fa-sync fa-spin';
                if(combo.estilo_combate === 'OVER FINISHER') styleIcon = 'fa-arrow-up';

                // Ahora la tarjeta NO tiene el onclick en todo el contenedor, sino en los botones
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

                        <!-- FUSIÓN: Botonera de acciones -->
                        <div class="mt-4 flex gap-2 relative z-10">
                            <button onclick="toggleMetaDetails(${index})" class="flex-1 bg-gray-800 hover:bg-gray-700 active:bg-gray-600 text-gray-400 font-bold py-2 rounded-lg text-[10px] tracking-widest uppercase transition-colors border border-gray-700 flex items-center justify-center">
                                <i class="fas fa-chevron-down mr-1"></i> Resumen
                            </button>
                            <button onclick="abrirModalMeta(${index})" class="flex-1 bg-purple-900/40 hover:bg-purple-800/60 active:bg-purple-700 text-purple-400 font-bold py-2 rounded-lg text-[10px] tracking-widest uppercase transition-colors border border-purple-800/50 flex items-center justify-center">
                                <i class="fas fa-chart-pie mr-1"></i> Detalles
                            </button>
                        </div>

                        <!-- INFO DESPLEGABLE (Resumen Rápido) -->
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

    let currentTargetUserId = null; // Guardará el ID si el juez está editando a otro

    // Función puente para cuando el juez pulsa el botón de editar desde la lista
    function abrirModalDeckJuez(eventId, userId, userName) {
        cerrarListaModal();
        abrirModal(eventId, 'OPEN', userId, userName); // Al juez siempre se lo abrimos como 'OPEN' para que pueda editar incluso en LIVE
    }


    async function enviarDeck(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitDeck'); 
        const isTentative = document.getElementById('tentativeCheck').checked;
        
        if(!isTentative) { 
            let valido = true; 
            document.querySelectorAll('.deck-req').forEach(s => { if(!s.value) valido = false; }); 
            if(!valido) { mostrarToast("Faltan piezas obligatorias", true); triggerHaptic('error'); return; } 
        }
        
        triggerHaptic('heavy'); 
        btn.disabled = true; 
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ENVIANDO...';
        
        const eventId = document.getElementById('modal_event_id').value; 
        const formData = new FormData(document.getElementById('deckForm')); 
        formData.append('is_tentative', isTentative);
        
        // Si el juez está guardando el deck de otro jugador, lo enviamos
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
                // Si el juez estaba editando desde la lista, le reabrimos la lista actualizada
                if (wasEditingOther) {
                    abrirListaJugadores(eventId, currentListaAforo);
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

        // Mostrar el modal
        document.getElementById('metaModal').classList.remove('hidden');
        document.getElementById('metaModal').classList.add('flex', 'active');
        document.body.style.overflow = 'hidden';

        // 1. Textos y Cabecera
        document.getElementById('modal_meta_blade').innerText = combo.blade || '---';
        document.getElementById('modal_meta_parts').innerText = `${combo.ratchet} X ${combo.bit}`;
        document.getElementById('modal_meta_style').innerText = combo.estilo_combate || 'ESTÁNDAR';
        document.getElementById('modal_meta_wr').innerText = `${parseFloat(combo.percentage_victories).toFixed(1)}%`;
        document.getElementById('modal_meta_wl').innerText = `${combo.total_victorias || 0}W - ${combo.total_derrotas || 0}L`;

        // 2. Resumen de Finales (Historial de Victorias)
        document.getElementById('modal_meta_spin').innerText = combo.spin_wins || 0;
        document.getElementById('modal_meta_over').innerText = combo.over_wins || 0;
        document.getElementById('modal_meta_burst').innerText = combo.burst_wins || 0;
        document.getElementById('modal_meta_xtreme').innerText = combo.xtreme_wins || 0;

        // 3. Daño Promedio
        document.getElementById('modal_meta_dmg_won').innerText = parseFloat(combo.puntos_ganados_por_combate || 0).toFixed(2);
        document.getElementById('modal_meta_dmg_lost').innerText = parseFloat(combo.puntos_perdidos_por_combate || 0).toFixed(2);

        // 4. Emparejamientos (Mejores y Peores)
        const bestContainer = document.getElementById('modal_meta_best');
        const worstContainer = document.getElementById('modal_meta_worst');
        
        // Función interna adaptada a los datos reales de tu Laravel
        const renderMatchups = (container, list, colorClass, type) => {
            if (!list || list.length === 0) {
                container.innerHTML = '<div class="text-[10px] text-gray-500 font-bold uppercase p-2 text-center border border-gray-800 rounded bg-gray-900/50">Datos insuficientes</div>';
                return;
            }
            let html = '';
            list.forEach(m => {
                // Tu Laravel envía el nombre completo en 'm.combo' y los números en 'm.victorias' o 'm.derrotas'
                const comboName = m.combo || 'DESCONOCIDO';
                const count = type === 'wins' ? (m.victorias || 0) : (m.derrotas || 0);
                const label = type === 'wins' ? 'WINS' : 'LOSSES';

                html += `
                    <div class="flex justify-between items-center bg-gray-900 border border-gray-800 p-2 mb-2 rounded-lg">
                        <span class="text-[10px] font-bold text-white uppercase">${comboName}</span>
                        <span class="text-xs font-mono font-black ${colorClass}">${count} ${label}</span>
                    </div>
                `;
            });
            container.innerHTML = html;
        };

        // Renderizamos pasándole si son victorias o derrotas para leer el dato correcto
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

            // --- INSIGNIA PRINCIPAL ---
            // Buscamos el mejor título desbloqueado
            const mejorTitulo = (data.titulos || []).find(t => t.unlocked) || data.titulos[data.titulos.length - 1];
            
            let colorClasePrincipal = 'border-gray-500 text-gray-400 bg-gray-900/30';
            if(mejorTitulo.color === 'red') colorClasePrincipal = 'border-red-500 text-red-400 bg-red-900/30 shadow-[0_0_8px_rgba(239,68,68,0.5)]';
            else if(mejorTitulo.color === 'yellow') colorClasePrincipal = 'border-yellow-500 text-yellow-400 bg-yellow-900/30 shadow-[0_0_5px_rgba(234,179,8,0.4)]';
            else if(mejorTitulo.color === 'purple') colorClasePrincipal = 'border-purple-500 text-purple-400 bg-purple-900/30';
            else if(mejorTitulo.color === 'cyan') colorClasePrincipal = 'border-cyan-500 text-cyan-400 bg-cyan-900/30';

            const insigniaPrincipalHTML = `<div class="mt-2"><span class="text-[10px] px-3 py-1 rounded-full border font-bold tracking-widest uppercase ${colorClasePrincipal}"><i class="fas fa-medal mr-1"></i> ${mejorTitulo.nombre}</span></div>`;

            // --- ESCAPARATE DE LOGROS ---
            let escaparateLogrosHTML = `<div class="mt-4"><h4 class="text-[10px] font-bold text-gray-500 tracking-widest uppercase mb-2 border-b border-gray-800 pb-1"><i class="fas fa-trophy mr-1"></i> LOGROS DE CARRERA</h4><div class="grid grid-cols-3 gap-2">`;
            
            (data.titulos || []).forEach(t => {
                if (t.unlocked) {
                    let cBorder = 'border-gray-500', cText = 'text-gray-400', cBg = 'bg-gray-800';
                    if(t.color === 'red') { cBorder = 'border-red-500'; cText = 'text-red-400'; cBg = 'bg-red-900/20'; }
                    else if(t.color === 'yellow') { cBorder = 'border-yellow-500'; cText = 'text-yellow-400'; cBg = 'bg-yellow-900/20'; }
                    else if(t.color === 'purple') { cBorder = 'border-purple-500'; cText = 'text-purple-400'; cBg = 'bg-purple-900/20'; }
                    else if(t.color === 'cyan') { cBorder = 'border-cyan-500'; cText = 'text-cyan-400'; cBg = 'bg-cyan-900/20'; }

                    escaparateLogrosHTML += `
                        <div class="flex flex-col items-center justify-center p-2 rounded-lg border ${cBorder} ${cBg} text-center shadow-inner relative overflow-hidden transition-all">
                            <i class="fas fa-award text-2xl mb-1 ${cText} opacity-80"></i>
                            <span class="text-[8px] font-bold tracking-widest uppercase ${cText} leading-tight line-clamp-1">${t.nombre}</span>
                        </div>`;
                } else {
                    escaparateLogrosHTML += `
                        <div class="flex flex-col items-center justify-center p-2 rounded-lg border border-gray-800 bg-gray-950 text-center opacity-60">
                            <i class="fas fa-lock text-gray-600 text-xl mb-1"></i>
                            <span class="text-[7px] font-bold tracking-widest uppercase text-gray-500 leading-tight line-clamp-1 mb-0.5">${t.nombre}</span>
                            <span class="text-[6px] text-gray-600 uppercase tracking-widest">${t.desc}</span>
                        </div>`;
                }
            });
            escaparateLogrosHTML += `</div></div>`;

            // --- TOP 3 COMBOS ESTRELLA ---
            let combosHtml = '';
            if (data.mejores_combos && data.mejores_combos.length > 0) {
                combosHtml += `<div class="mt-4"><h4 class="text-[10px] font-bold text-gray-500 tracking-widest uppercase mb-2 border-b border-gray-800 pb-1"><i class="fas fa-compact-disc mr-1"></i> ARSENAL FAVORITO</h4><div class="space-y-2">`;
                data.mejores_combos.forEach((combo, index) => {
                    const rankIcon = index === 0 ? 'text-yellow-400 fa-trophy' : (index === 1 ? 'text-gray-300 fa-medal' : 'text-orange-400 fa-medal');
                    const rankSize = index === 0 ? 'text-sm font-black' : 'text-xs font-bold opacity-80';
                    
                    combosHtml += `
                        <div class="bg-gray-800/50 border ${index === 0 ? 'border-fuchsia-900/50 shadow-inner' : 'border-gray-700'} p-2.5 rounded-lg flex justify-between items-center transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas ${rankIcon} w-4 text-center"></i>
                                <span class="text-white font-mono uppercase tracking-wide truncate max-w-[160px] sm:max-w-[200px] ${rankSize}">${combo.nombre}</span>
                            </div>
                            <span class="text-[10px] font-bold text-fuchsia-400 bg-fuchsia-900/20 border border-fuchsia-900/50 px-2 py-1 rounded shrink-0">${combo.victorias} WINS</span>
                        </div>`;
                });
                combosHtml += `</div></div>`;
            } else {
                combosHtml += `<div class="mt-4 p-3 bg-gray-900 border border-gray-800 rounded-xl text-center"><p class="text-[10px] text-gray-500 font-bold tracking-widest uppercase">Aún no tiene combos registrados.</p></div>`;
            }

            // --- ENSAMBLAJE FINAL ---
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
                    <div class="text-[9px] text-gray-400 font-bold tracking-widest text-right relative z-10">${nextPts === 0 ? 'RANGO MÁXIMO ALCANZADO' : `FALTAN <span class="text-white">${ptsLeft} PTS</span> PARA${nextRank}`}</div>
                </div>
                
                ${statsHtml}
                ${teamHtml}
                ${escaparateLogrosHTML}
                ${combosHtml}
            `;
        } catch (error) { container.innerHTML = '<p class="text-red-500 text-sm text-center mt-10">Error de comunicación al descifrar el perfil.</p>'; }
    }

    // --- MODALES EXTRA ---
    // Variable global para recordar el aforo cuando recarguemos
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
            if (data.jugadores.length === 0) {
                listaHTML = '<div class="text-center text-gray-500 py-4 font-bold text-sm">La arena está vacía</div>';
            } else { 
                data.jugadores.forEach((jugador, index) => { 
                    const esReserva = (index + 1) > currentListaAforo; 
                    const isDropped = jugador.puesto === 'dropped';
                    const safeName = jugador.name.replace(/'/g, "\\'");
                    
                    // Icono de Estado del Deck (Verde = OK, Amarillo = Pendiente/Tentativo)
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
                        // Botones exclusivos para el Juez: Editar el Deck del jugador + Hacer Drop
                        staffBtns = `
                            <button onclick="abrirModalDeckJuez(${eventId}, ${jugador.id}, '${safeName}')" class="ml-2 bg-cyan-900/50 hover:bg-cyan-600 text-cyan-400 hover:text-white px-2 py-1 rounded border border-cyan-700/50 transition-colors text-xs" title="Meter/Editar Deck">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="ejecutarDrop(${eventId}, ${jugador.id}, '${safeName}')" class="ml-1 bg-red-900/50 hover:bg-red-600 text-red-400 hover:text-white px-2 py-1 rounded border border-red-700/50 transition-colors text-xs" title="Retirar del Torneo">
                                <i class="fas fa-user-slash"></i>
                            </button>`;
                    }

                    listaHTML += `
                        <div class="flex justify-between items-center p-3 rounded-lg border mb-2 ${isDropped ? 'bg-red-950/20 border-red-900/30 opacity-50' : (esReserva ? 'bg-red-950/30 border-red-900/50 text-red-400' : 'bg-gray-800 border-gray-700 text-white')}">
                            <div class="flex items-center truncate pr-2">
                                ${!isDropped ? deckIcon : ''}
                                <span class="font-mono text-sm font-bold truncate ${isDropped ? 'line-through text-gray-500' : ''}">${jugador.name}</span>
                            </div>
                            <div class="flex items-center shrink-0">
                                ${badgeHTML}
                                ${staffBtns}
                            </div>
                        </div>`; 
                }); 
            }
            document.getElementById('listadoJugadoresContent').innerHTML = listaHTML; 
        } catch (error) { 
            document.getElementById('listadoJugadoresContent').innerHTML = '<div class="text-center text-red-500 py-4 font-bold text-sm">Error de conexión</div>';
        }
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
            
            if(data.success) {
                // Ahora reutilizamos el aforo correcto en lugar de 999
                abrirListaJugadores(eventId, currentListaAforo); 
            }
        } catch(e) {
            mostrarToast("Error al ejecutar Drop", true);
        }
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
            
        for (let i = 1; i <= 3; i++) { 
            htmlTemp += `<div class="bg-gray-800/50 p-4 rounded-xl border border-gray-700/50 mb-4 shadow-inner"><div class="flex items-center gap-2 text-cyan-400 font-bold text-xs mb-3 tracking-widest border-b border-gray-700/50 pb-2"><i class="fas fa-compact-disc"></i> BEYBLADE #${i}</div><div class="grid grid-cols-1 gap-3 mb-3">${crearSelect('blade', i, dbPiezas.blades, 'Selecciona Blade...', true)}</div><div class="grid grid-cols-2 gap-3 mb-3">${crearSelect('ratchet', i, dbPiezas.ratchets, 'Ratchet...', true)}${crearSelect('bit', i, dbPiezas.bits, 'Bit...', true)}</div><div class="grid grid-cols-2 gap-3">${crearSelect('over_blade', i, dbPiezas.over_blades, 'Over (Opcional)', false)}${crearSelect('assist_blade', i, dbPiezas.assist_blades, 'Assist (Opcional)', false)}</div></div>`; 
        }
        container.innerHTML = htmlTemp;

        // Inicializamos el buscador SOLO después de inyectar el HTML
        document.querySelectorAll('.tom-select-pieza').forEach(el => {
            new TomSelect(el, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                maxOptions: 500 // Seguro para móviles
            });
        });
    }

    async function abrirModal(eventId, status = 'OPEN', targetUserId = null, targetUserName = null) { 
        triggerHaptic('light'); 
        currentTargetUserId = targetUserId;
        document.getElementById('modal_event_id').value = eventId; 
        
        if (!dbPiezas) {
            await cargarPiezas();
        }
        
        const checkTentative = document.getElementById('tentativeCheck');
        if (checkTentative) checkTentative.checked = false;
        toggleTentative();

        // 1. PRIMERO ABRIMOS EL MODAL (Para que la pantalla le dé un ancho real)
        document.getElementById('deckModal').classList.add('active'); 
        document.body.style.overflow = 'hidden'; 

        // 2. LUEGO GENERAMOS LOS SELECTS CON BUSCADOR
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
                            if (!el.tomselect.options[cleanVal]) {
                                el.tomselect.addOption({value: cleanVal, text: cleanVal});
                            }
                            el.tomselect.setValue(cleanVal);
                        } else {
                            el.value = cleanVal;
                        }
                    };

                    setSelectValue(`blade_${i}`, bey.blade);
                    setSelectValue(`ratchet_${i}`, bey.ratchet);
                    setSelectValue(`bit_${i}`, bey.bit);
                    setSelectValue(`over_blade_${i}`, bey.over_blade);
                    setSelectValue(`assist_blade_${i}`, bey.assist_blade);
                });
            }
        } catch (error) { 
            console.warn("Deck nuevo o error de carga"); 
        }

        const btnSubmit = document.getElementById('btnSubmitDeck');
        const formElements = document.querySelectorAll('#deckForm select, #deckForm input');

        if ((status === 'LIVE' || status === 'COMPLETED') && !targetUserId) {
            formElements.forEach(el => {
                if (el.tomselect) el.tomselect.disable();
                else el.disabled = true;
            });
            btnSubmit.style.display = 'none';
            if (warning) {
                warning.innerHTML = '<span class="text-red-400 font-bold uppercase tracking-widest"><i class="fas fa-lock mr-2"></i> TORNEO EN CURSO. DECK BLOQUEADO.</span>';
                warning.classList.remove('hidden');
            }
        } else {
            formElements.forEach(el => {
                if (el.tomselect) el.tomselect.enable();
                else el.disabled = false;
            });
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
                const i = index + 1; 
                if (i > 3) return; 
                
                const setVal = (field, val) => {
                    const el = document.getElementById(`${field}_${i}`);
                    if (el && val && val !== '-') {
                        if (el.tomselect) el.tomselect.setValue(val);
                        else el.value = val;
                    }
                };
                
                setVal('blade', bey.blade);
                setVal('ratchet', bey.ratchet);
                setVal('bit', bey.bit);
                setVal('over_blade', bey.over_blade);
                setVal('assist_blade', bey.assist_blade);
            });
            mostrarToast("✅ Último Deck cargado");
        } catch (error) { 
            mostrarToast("Error al recuperar el historial.", true); 
        }
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
        const filtered = allTeamsList.filter(t => t.name.toLowerCase().includes(query));
        renderDropdown(num, filtered);
    }

    function renderDropdown(num, list) {
        const dropdown = document.getElementById(`dropdown_team_${num}`);
        if(list.length === 0) { dropdown.innerHTML = '<div class="p-3 text-gray-500 text-xs text-center font-bold">Sin coincidencias</div>'; return; }
        let html = '';
        list.forEach(eq => { html += `<div onclick="seleccionarEquipo(${num}, ${eq.id}, '${eq.name.replace(/'/g, "\\'")}')" class="p-3 text-white text-sm border-b border-gray-700/50 hover:bg-gray-700 active:bg-cyan-900 cursor-pointer transition-colors truncate">${eq.name}</div>`; });
        dropdown.innerHTML = html;
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
            if (res.ok) { mostrarToast(data.message); cerrarModalDuelo(); cargarDuelos(); triggerHaptic('medium'); } else { mostrarToast("Error al registrar el duelo. Comprueba los datos.", true); triggerHaptic('error'); }
        } catch (error) { mostrarToast("Error de transmisión", true); triggerHaptic('error'); }
        btn.disabled = false; btn.innerHTML = 'CONFIRMAR RESULTADO';
    }

    // --- PANEL DE ARBITRAJE ---
    let currentArenaEventId = null;

    async function abrirPanelArbitraje(eventId, round = null) {
        triggerHaptic('light');
        currentArenaEventId = eventId;
        
        const modal = document.getElementById('arbitrajeModal');
        const container = document.getElementById('arbitrajeContent');
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
            
            document.getElementById('arbitraje_event_name').innerText = data.event_name;
            
            if (data.max_round === 0) {
                container.innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-trophy text-6xl text-gray-700 mb-4"></i>
                        <h3 class="text-white font-bold font-mono text-xl mb-2">ARENA PREPARADA</h3>
                        <p class="text-gray-500 text-sm px-4">El torneo está en curso. Todos los decks han sido bloqueados. Es el momento de generar los cruces.</p>
                    </div>`;
                if(btnGenerar) btnGenerar.classList.remove('hidden');
            } else {
                // 1. Selector de Rondas con Semáforo de Colores
                let roundSelectorHTML = `<div id="round-tabs-container" class="flex overflow-x-auto gap-2 mb-4 mt-2 no-scrollbar pb-2 scroll-smooth">`;
                for (let i = 1; i <= data.max_round; i++) {
                    const isAct = (i === data.current_round);
                    const status = data.rounds_status ? data.rounds_status[i] : 'pending';
                    
                    let colorClass = '';
                    
                    if (isAct) {
                        colorClass = 'bg-cyan-600 text-white border-cyan-400 shadow-[0_0_10px_rgba(6,182,212,0.4)]';
                    } else if (status === 'completed') {
                        colorClass = 'bg-green-900/40 text-green-500 border-green-800 hover:bg-green-800/60';
                    } else {
                        colorClass = 'bg-orange-900/40 text-orange-400 border-orange-800 hover:bg-orange-800/60';
                    }
                    
                    roundSelectorHTML += `<button id="tab-round-${i}" onclick="abrirPanelArbitraje(${eventId}, ${i})" class="shrink-0 px-4 py-1.5 rounded-full border text-xs font-bold tracking-widest transition-all ${colorClass}">RONDA ${i}</button>`;
                }
                roundSelectorHTML += `</div>`;

                // Detectar Bracket con la variable del backend
                const isBracket = data.is_bracket;
                const roundTitle = isBracket ? 'FASE ELIMINATORIA' : `CRUCES (R${data.current_round})`;
                const titleColor = isBracket ? 'text-fuchsia-400' : 'text-cyan-400';

                let htmlTemp = roundSelectorHTML + `
                    <div class="flex bg-gray-900 rounded-lg p-1 mb-4 mt-2">
                        <button class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-cyan-400 bg-gray-800 font-bold transition-colors">CRUCES</button>
                        <button onclick="verClasificacionArena(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">TABLA</button>
                        <button onclick="verCuadroArena(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">CUADRO</button>
                    </div>

                    <div class="flex justify-between items-center mb-3 px-1">
                        <h4 class="${titleColor} font-bold font-mono tracking-widest text-sm">${roundTitle}</h4>
                        <span class="text-[10px] bg-gray-800 text-gray-400 px-2 py-1 rounded font-bold shadow-inner border border-gray-700">${data.matches.length} PARTIDAS</span>
                    </div>
                    <div class="space-y-3 pb-4">`;
                
                // 3. Generar las tarjetas
                if (data.matches.length === 0) {
                    // Si la ronda está vacía (Todo eran fantasmas/byes)
                    htmlTemp += `
                        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 text-center mt-4 shadow-inner">
                            <i class="fas fa-ghost text-4xl text-gray-600 mb-3"></i>
                            <h4 class="text-white font-bold tracking-widest mb-1 text-sm">RONDA DE TRANSICIÓN</h4>
                            <p class="text-gray-500 text-xs">Todos los cruces de esta ronda se han resuelto automáticamente mediante Byes.</p>
                        </div>
                    `;
                } else {
                    // Generar las tarjetas de partidas normales (VISTA JUEZ)
                    data.matches.forEach(match => {
                        const p1 = match.player1 ? match.player1.name : 'ESPERANDO RIVAL';
                        const p2 = match.player2 ? match.player2.name : 'ESPERANDO RIVAL';
                        
                        let btnHTML = '';
                        let isLockedByOther = false;
                        let bgContainerClass = match.bracket_type === 'winners' ? 'border-fuchsia-900/80 bg-gray-800/50' : (match.bracket_type === 'losers' ? 'border-orange-900/80 bg-gray-800/50' : 'border-gray-700/80 bg-gray-800/50');
                        
                        // Si está bloqueada por alguien
                        if (match.locked_by) {
                            const miId = document.querySelector('meta[name="user-id"]')?.getAttribute('content'); // Si tienes el ID del usuario en un meta tag
                            if (match.locked_by != miId) { // Si no puedes compararlo, asumiremos que si está locked, es de otro.
                                isLockedByOther = true;
                                bgContainerClass = 'border-orange-500 shadow-[0_0_15px_rgba(249,115,22,0.3)] bg-orange-950/40'; // Semáforo naranja
                            }
                        }

                        if (!match.player1 || !match.player2) {
                            btnHTML = `<div class="bg-gray-800/80 text-gray-500 font-bold py-2 rounded-lg text-[10px] tracking-widest text-center border border-gray-700 animate-pulse"><i class="fas fa-hourglass-half mr-1"></i> ESPERANDO JUGADORES</div>`;
                        } else if (match.status === 'completed') {
                            btnHTML = `<button onclick="abrirBatalla(${match.id})" class="w-full bg-green-900/40 hover:bg-green-800/60 text-green-500 font-bold py-2 rounded-lg text-xs tracking-wider transition-colors border border-green-800/50"><i class="fas fa-check-circle mr-1"></i> PARTIDA FINALIZADA (EDITAR)</button>`;
                        } else if (isLockedByOther) {
                            // BOTÓN NARANJA: MESA OCUPADA
                            const judgeName = match.locked_by_user ? match.locked_by_user.name : 'OTRO JUEZ';
                            btnHTML = `<button class="w-full bg-orange-900/80 text-orange-400 font-bold py-2 rounded-lg text-xs tracking-wider border border-orange-500 pointer-events-none opacity-80"><i class="fas fa-lock mr-1"></i> EN CURSO POR ${judgeName.toUpperCase()}</button>`;
                        } else {
                            btnHTML = `<button onclick="abrirBatalla(${match.id})" class="w-full bg-yellow-600 hover:bg-yellow-500 active:bg-yellow-700 text-white font-bold py-2 rounded-lg text-xs tracking-wider transition-colors shadow-md border border-yellow-500">JUZGAR COMBATE</button>`;
                        }

                        htmlTemp += `
                            <div class="bg-gray-800/50 border ${match.bracket_type === 'winners' ? 'border-fuchsia-900/80' : (match.bracket_type === 'losers' ? 'border-orange-900/80' : 'border-gray-700/80')} rounded-xl p-3 shadow-lg relative overflow-hidden mt-4">
                                <!-- Etiqueta de la partida -->
                                <div class="absolute top-0 left-0 bg-gray-900 text-gray-400 border-b border-r border-gray-700 text-[9px] font-bold px-2 py-0.5 rounded-br-lg z-20">
                                    PARTIDA #${match.match_number || '-'}
                                </div>
                                ${match.bracket_type !== 'swiss' ? `<div class="absolute top-0 left-1/2 transform -translate-x-1/2 ${match.bracket_type === 'winners' ? 'bg-fuchsia-900' : (match.bracket_type === 'losers' ? 'bg-orange-900' : (match.bracket_type === 'grand_final' ? 'bg-yellow-600' : 'bg-red-600'))} text-white text-[9px] font-bold px-3 py-0.5 rounded-b uppercase tracking-widest z-20 shadow-md">${match.bracket_type.replace(/_/g, ' ')}</div>` : ''}
                                
                                <div class="flex justify-between items-center mb-4 mt-4 relative z-10">
                                    <div class="flex-1 flex flex-col items-center">
                                        <span class="text-xs font-bold text-center ${match.player1 ? 'text-white' : 'text-gray-500'} truncate w-full px-1 uppercase tracking-wide mb-2">${p1}</span>
                                        <span class="text-3xl font-black font-mono leading-none ${match.winner_id && match.winner_id === match.player1_id ? 'text-yellow-400' : 'text-gray-400'}">${match.player1_score}</span>
                                    </div>
                                    <div class="px-2 font-mono text-lg font-black italic text-gray-600 shrink-0">VS</div>
                                    <div class="flex-1 flex flex-col items-center">
                                        <span class="text-xs font-bold text-center ${match.player2 ? 'text-white' : 'text-gray-500'} truncate w-full px-1 uppercase tracking-wide mb-2">${p2}</span>
                                        <span class="text-3xl font-black font-mono leading-none ${match.winner_id && match.winner_id === match.player2_id ? 'text-yellow-400' : 'text-gray-400'}">${match.player2_score}</span>
                                    </div>
                                </div>
                                <div class="relative z-10">
                                    ${btnHTML}
                                </div>
                            </div>`;
                    });
                }
                
                htmlTemp += `</div>`; 

                // 4. Lógica del Botón "GENERAR RONDA" / "PASAR AL TOP" / "FINALIZAR"
                if (data.current_round === data.max_round) {
                    const allCompleted = data.matches.every(m => m.status === 'completed');
                    if (allCompleted) {
                        let uniquePlayers = new Set();
                        data.matches.forEach(m => { if(m.player1_id) uniquePlayers.add(m.player1_id); if(m.player2_id) uniquePlayers.add(m.player2_id); });
                        currentTotalPlayers = uniquePlayers.size;
                        if(document.getElementById('total_jugadores_top')) {
                            document.getElementById('total_jugadores_top').innerText = currentTotalPlayers;
                        }

                        htmlTemp += `<div class="flex flex-col gap-2 w-full mt-4 mb-6">`;
                        
                        // Si estamos en suizo, mostramos las opciones para avanzar o crear el Top Cut
                        if (!isBracket) {
                            htmlTemp += `
                                <button onclick="generarSiguienteRonda()" class="w-full bg-green-700 hover:bg-green-600 font-bold py-3.5 rounded-lg text-sm text-white transition-colors"><i class="fas fa-cogs mr-2"></i> GENERAR RONDA ${data.max_round + 1}</button>
                                <button onclick="abrirModalTopCut()" class="w-full bg-cyan-700 hover:bg-cyan-600 font-bold py-3.5 rounded-lg text-sm text-white border border-cyan-500 shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all"><i class="fas fa-trophy mr-2"></i> PASAR AL TOP FINAL</button>`;
                        } else {
                            // Si estamos en eliminatorias y han terminado todas las partidas, sacamos el botón de CERRAR ACTA
                            htmlTemp += `
                                <button onclick="finalizarTorneo(${eventId})" class="w-full bg-red-700 hover:bg-red-600 active:bg-red-800 font-black py-4 rounded-xl text-sm text-white border-2 border-red-500 shadow-[0_0_20px_rgba(239,68,68,0.5)] transition-all uppercase tracking-widest"><i class="fas fa-flag-checkered mr-2"></i> CERRAR ACTA Y FINALIZAR</button>`;
                        }
                        
                        htmlTemp += `</div>`;
                    }
                }

                container.innerHTML = htmlTemp;
                
                if(btnGenerar) btnGenerar.classList.add('hidden');

                // Auto-scroll a la ronda actual
                setTimeout(() => {
                    const activeTab = document.getElementById(`tab-round-${data.current_round}`);
                    if (activeTab) {
                        activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                }, 50);

            } 
        } catch (error) {
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error de conexión.</p>';
        }
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
            
            if (data.success) {
                cerrarPanelArbitraje();
                cargarEventos(); // Refresca la vista para que todos lo vean completado
            }
        } catch (error) {
            mostrarToast("Error de conexión", true);
        }
    }

    async function abrirArenaPublica(eventId, round = null) {
        triggerHaptic('light');
        const modal = document.getElementById('arbitrajeModal'); // Reutilizamos el modal para no ensuciar el HTML
        const container = document.getElementById('arbitrajeContent');
        
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
            
            document.getElementById('arbitraje_event_name').innerText = data.event_name;
            
            // Seguridad visual: Si el botón de generar ronda del staff existe, lo ocultamos
            const btnGenerar = document.getElementById('btnGenerarRonda');
            if (btnGenerar) btnGenerar.classList.add('hidden');
            
            if (data.max_round === 0) {
                container.innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-hourglass-half text-6xl text-gray-700 mb-4"></i>
                        <h3 class="text-white font-bold font-mono text-xl mb-2">ARENA EN PREPARACIÓN</h3>
                        <p class="text-gray-500 text-sm px-4">El torneo está a punto de comenzar. Espera a que los jueces generen los cruces.</p>
                    </div>`;
            } else {
                // Selector de Rondas (Color Fucsia para diferenciar de la vista de Juez)
                let roundSelectorHTML = `<div id="round-tabs-container" class="flex overflow-x-auto gap-2 mb-4 mt-2 no-scrollbar pb-2 scroll-smooth">`;
                for (let i = 1; i <= data.max_round; i++) {
                    const isAct = (i === data.current_round);
                    const colorClass = isAct ? 'bg-fuchsia-600 text-white border-fuchsia-400 shadow-[0_0_10px_rgba(192,38,211,0.4)]' : 'bg-gray-900/80 text-gray-400 border-gray-800';
                    roundSelectorHTML += `<button onclick="abrirArenaPublica(${eventId}, ${i})" class="shrink-0 px-4 py-1.5 rounded-full border text-xs font-bold tracking-widest transition-all ${colorClass}">RONDA ${i}</button>`;
                }
                roundSelectorHTML += `</div>`;

                let htmlTemp = roundSelectorHTML + `
                    <div class="flex bg-gray-900 rounded-lg p-1 mb-4 mt-2">
                        <button class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-fuchsia-400 bg-gray-800 font-bold transition-colors">CRUCES</button>
                        <button onclick="verClasificacionArena(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">TABLA</button>
                        <button onclick="verCuadroArena(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">CUADRO</button>
                    </div>`;
                
                // Pintamos las tarjetas sin botones de arbitraje
                if (data.matches.length === 0) {
                    htmlTemp += `<div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 text-center mt-4 shadow-inner"><p class="text-gray-500 text-xs">Cruces resueltos por byes automáticos.</p></div>`;
                } else {
                    // VISTA PÚBLICA DE LAS PARTIDAS
                    data.matches.forEach(match => {
                        const p1 = match.player1 ? match.player1.name : 'ESPERANDO RIVAL';
                        const p2 = match.player2 ? match.player2.name : 'ESPERANDO RIVAL';
                        
                        let statusBadge = '';
                        if (!match.player1 || !match.player2) {
                            statusBadge = `<span class="text-gray-500 text-[10px] font-bold tracking-widest"><i class="fas fa-hourglass-half"></i> EN ESPERA</span>`;
                        } else if (match.status === 'completed') {
                            statusBadge = `<span class="text-green-500 text-[10px] font-bold tracking-widest"><i class="fas fa-check-circle"></i> FINALIZADO</span>`;
                        } else {
                            statusBadge = `<span class="text-yellow-500 text-[10px] font-bold tracking-widest animate-pulse"><i class="fas fa-bolt"></i> EN JUEGO</span>`;
                        }

                        htmlTemp += `
                            <div class="bg-gray-800/50 border border-gray-700/80 rounded-xl p-3 shadow-lg relative overflow-hidden mt-4">
                                <div class="absolute top-0 left-0 bg-gray-900 text-gray-400 border-b border-r border-gray-700 text-[9px] font-bold px-2 py-0.5 rounded-br-lg z-20">
                                    PARTIDA #${match.match_number || '-'}
                                </div>
                                ${match.bracket_type !== 'swiss' ? `<div class="absolute top-0 left-1/2 transform -translate-x-1/2 bg-gray-700 text-white text-[9px] font-bold px-3 py-0.5 rounded-b uppercase tracking-widest z-20">${match.bracket_type.replace(/_/g, ' ')}</div>` : ''}
                                <div class="flex justify-between items-center mb-2 mt-4 relative z-10">
                                    <div class="flex-1 flex flex-col items-center">
                                        <span class="text-xs font-bold text-center ${match.player1 ? 'text-white' : 'text-gray-500'} truncate w-full px-1 uppercase tracking-wide mb-2">${p1}</span>
                                        <span class="text-3xl font-black font-mono leading-none ${match.winner_id && match.winner_id === match.player1_id ? 'text-fuchsia-400' : 'text-gray-400'}">${match.player1_score}</span>
                                    </div>
                                    <div class="px-2 font-mono text-lg font-black italic text-gray-600 shrink-0">VS</div>
                                    <div class="flex-1 flex flex-col items-center">
                                        <span class="text-xs font-bold text-center ${match.player2 ? 'text-white' : 'text-gray-500'} truncate w-full px-1 uppercase tracking-wide mb-2">${p2}</span>
                                        <span class="text-3xl font-black font-mono leading-none ${match.winner_id && match.winner_id === match.player2_id ? 'text-fuchsia-400' : 'text-gray-400'}">${match.player2_score}</span>
                                    </div>
                                </div>
                                <div class="text-center pt-2 border-t border-gray-800/50">${statusBadge}</div>
                            </div>`;
                    });
                }
                container.innerHTML = htmlTemp;
            } 
        } catch (error) { 
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error de conexión.</p>'; 
        }
    }

    function cerrarPanelArbitraje() {
        triggerHaptic('light');
        document.getElementById('arbitrajeModal').classList.remove('active');
        document.body.style.overflow = 'auto';
        document.getElementById('arbitrajeContent').innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-yellow-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">SINTONIZANDO ARENA...</p></div>';
    }

    async function generarSiguienteRonda() {
        if(!confirm("¿Atención Juez: Generar los emparejamientos de esta ronda?")) return;
        triggerHaptic('heavy');
        
        const btn = document.getElementById('btnGenerarRonda');
        let oldText = '';
        
        // Protegemos el cambio visual por si el botón no tiene ese ID
        if (btn) {
            oldText = btn.innerHTML;
            btn.disabled = true; 
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> GENERANDO...';
        }

        try {
            const res = await fetch(`/api/torneos/${currentArenaEventId}/arena/ronda`, {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                    'Accept': 'application/json' 
                }
            });
            const data = await res.json();
            
            // Verificamos que mostrarToast existe para que no rompa el script
            if (typeof mostrarToast === "function") {
                mostrarToast(data.message, !data.success);
            }
            
            if (data.success) {
                // RECARGA AUTOMÁTICA
                abrirPanelArbitraje(currentArenaEventId); 
            }
        } catch (error) {
            if (typeof mostrarToast === "function") {
                mostrarToast("Error de transmisión con el motor Suizo.", true);
            }
        } finally {
            // Restauramos el botón solo si existe
            if (btn) {
                btn.disabled = false; 
                btn.innerHTML = oldText;
            }
        }
    }

    // --- LÓGICA DE BATALLA (TÁCTIL PRO) ---
    // --- LÓGICA DE BATALLA (TÁCTIL PRO) ---
    let battleState = { matchId: null, p1ComboId: null, p2ComboId: null, winnerId: null, finishType: null };
    let isSideSwapped = false;
    let currentMatchData = null;

    // 1. FUNCIÓN PRINCIPAL PARA ABRIR Y REFRESCAR LA BATALLA
    // Helper que encuentra la caja de combos se llame como se llame en el HTML
    function getDeckContainer(playerKey) {
        return document.getElementById(`deck_${playerKey}_container`) || document.getElementById(`${playerKey}CombosList`);
    }

    // 1. FUNCIÓN PRINCIPAL PARA ABRIR Y REFRESCAR LA BATALLA
    async function abrirBatalla(matchId) {
        // --- NUEVO: Intentar Bloquear la mesa ---
        const lockStatus = await lockMatchOnServer(matchId);
        if (!lockStatus.success) {
            mostrarToast("⚠️ Esta partida ya la está juzgando otro árbitro", true);
            triggerHaptic('error');
            return; // Abortamos, no le abrimos el modal
        }
        // ---------------------------------------

        triggerHaptic('medium');
        document.getElementById('batallaModal').classList.remove('hidden');
        document.getElementById('batallaModal').classList.add('flex');
        
        if (battleState.matchId !== matchId) {
            isSideSwapped = false;
        }

        battleState.matchId = matchId; 
        
        resetBatallaUI();
        aplicarEstadoLados();
        
        const btnW1 = document.getElementById('btn_warning_p1');
        const btnW2 = document.getElementById('btn_warning_p2');
        if (btnW1) btnW1.className = "absolute top-2 left-2 text-gray-700 hover:text-yellow-500 transition-colors z-20 text-lg";
        if (btnW2) btnW2.className = "absolute top-2 right-2 text-gray-700 hover:text-yellow-500 transition-colors z-20 text-lg";

        const c1 = getDeckContainer('p1');
        const c2 = getDeckContainer('p2');
        if (c1) c1.innerHTML = '<div class="animate-pulse h-12 bg-gray-800 rounded-lg"></div>';
        if (c2) c2.innerHTML = '<div class="animate-pulse h-12 bg-gray-800 rounded-lg"></div>';

        try {
            const res = await fetch(`/api/torneos/partida/${matchId}`);
            const data = await res.json();
            currentMatchData = data;
            
            document.getElementById('batalla_p1_name').innerText = data.match.player1.name;
            document.getElementById('batalla_p2_name').innerText = data.match.player2.name;
            document.getElementById('batalla_score_p1').innerText = data.match.player1_score;
            document.getElementById('batalla_score_p2').innerText = data.match.player2_score;
            document.getElementById('batalla_p1_id').value = data.match.player1_id;
            document.getElementById('batalla_p2_id').value = data.match.player2_id;
            
            document.getElementById('btn_ganador_p1').innerText = `GANA ${data.match.player1.name.substring(0, 10).toUpperCase()}`;
            document.getElementById('btn_ganador_p2').innerText = `GANA ${data.match.player2.name.substring(0, 10).toUpperCase()}`;

            // Si hay warnings activos en la base de datos, encendemos el icono amarillo
            if (data.battles && Array.isArray(data.battles)) {
                const w1 = data.battles.some(b => b.finish_type === 'warning_p1');
                const w2 = data.battles.some(b => b.finish_type === 'warning_p2');
                if (w1 && btnW1) btnW1.className = "absolute top-2 left-2 text-yellow-400 animate-pulse transition-colors z-20 text-lg drop-shadow-[0_0_8px_rgba(250,204,21,0.8)]";
                if (w2 && btnW2) btnW2.className = "absolute top-2 right-2 text-yellow-400 animate-pulse transition-colors z-20 text-lg drop-shadow-[0_0_8px_rgba(250,204,21,0.8)]";
            }

            // Calculamos qué combos ya se han usado en este set de 3
            const { p1Used, p2Used } = obtenerCombosUsadosEnCiclo(data.battles || []);

            const d1 = data.deck1 || data.p1_deck || [];
            const d2 = data.deck2 || data.p2_deck || [];

            renderDeckBatalla('p1', d1, p1Used);
            renderDeckBatalla('p2', d2, p2Used);
        } catch (error) {
            console.error(error);
            mostrarToast("Error cargando la partida.", true);
        }
    }

    // 2. PINTAR LOS DECKS OCULTANDO/BLOQUEANDO LOS USADOS EN EL 3vs3
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
                    <div class="w-full text-left bg-gray-950/50 border border-gray-800/80 p-2 rounded-lg flex items-center justify-between opacity-35 select-none pointer-events-none">
                        <div class="truncate pr-1 line-through text-gray-500">
                            <span class="font-bold text-xs uppercase">${idx + 1}. ${bey.blade}</span>
                            <span class="text-[10px] ml-1">${bey.ratchet} ${bey.bit}</span>
                        </div>
                        <span class="text-[8px] bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded font-bold uppercase shrink-0"><i class="fas fa-check"></i> USADO</span>
                    </div>`;
            } else {
                disponiblesIds.push(bey.id);
                html += `
                    <button type="button" onclick="seleccionarBey('${playerKey}', ${bey.id})" id="btn_bey_${bey.id}" class="bey-btn-${playerKey} w-full text-left bg-gray-900 border border-gray-700 hover:border-gray-500 p-2 rounded-lg transition-all flex items-center justify-between shadow-sm">
                        <div class="truncate">
                            <span class="text-white font-bold text-xs uppercase">${idx + 1}. ${bey.blade}</span>
                            <span class="text-gray-400 text-[10px] ml-2">${bey.ratchet} ${bey.bit}</span>
                        </div>
                    </button>`;
            }
        });
        container.innerHTML = html;

        if (disponiblesIds.length === 1) {
            seleccionarBey(playerKey, disponiblesIds[0]);
        }
    }

    function seleccionarBey(playerKey, comboId) {
        triggerHaptic('light');
        if (playerKey === 'p1') battleState.p1ComboId = comboId;
        else battleState.p2ComboId = comboId;

        document.querySelectorAll(`.bey-btn-${playerKey}`).forEach(btn => {
            btn.classList.remove('bg-cyan-900', 'border-cyan-400', 'bg-red-900', 'border-red-400', 'shadow-[0_0_10px_rgba(6,182,212,0.5)]', 'shadow-[0_0_10px_rgba(239,68,68,0.5)]');
            btn.classList.add('bg-gray-900', 'border-gray-700');
        });

        const btn = document.getElementById(`btn_bey_${comboId}`);
        if (btn) {
            btn.classList.remove('bg-gray-900', 'border-gray-700');
            if (playerKey === 'p1') btn.classList.add('bg-cyan-900', 'border-cyan-400', 'shadow-[0_0_10px_rgba(6,182,212,0.5)]');
            else btn.classList.add('bg-red-900', 'border-red-400', 'shadow-[0_0_10px_rgba(239,68,68,0.5)]');
        }

        comprobarEstadoBatalla();
    }

    function seleccionarGanador(playerKey) {
        triggerHaptic('light');
        battleState.winnerId = playerKey === 'p1' ? document.getElementById('batalla_p1_id').value : document.getElementById('batalla_p2_id').value;
        
        const btnP1 = document.getElementById('btn_ganador_p1');
        const btnP2 = document.getElementById('btn_ganador_p2');
        btnP1.className = "flex-1 bg-gray-900 border border-gray-700 text-gray-400 font-bold py-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg";
        btnP2.className = "flex-1 bg-gray-900 border border-gray-700 text-gray-400 font-bold py-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 shadow-lg";
        
        if (playerKey === 'p1') {
            btnP1.className = "flex-1 bg-cyan-700 border-2 border-cyan-400 text-white font-black py-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 shadow-[0_0_15px_rgba(6,182,212,0.4)]";
        } else {
            btnP2.className = "flex-1 bg-red-700 border-2 border-red-400 text-white font-black py-4 rounded-xl text-xs uppercase tracking-widest transition-all active:scale-95 shadow-[0_0_15px_rgba(239,68,68,0.4)]";
        }
        
        comprobarEstadoBatalla();
    }

    function seleccionarFinal(tipo) {
        triggerHaptic('light');
        battleState.finishType = tipo;
        
        ['spin', 'over', 'burst', 'xtreme'].forEach(t => {
            const btn = document.getElementById(`btn_fin_${t}`);
            btn.classList.remove('bg-blue-900/80', 'border-blue-400', 'text-blue-300', 'bg-yellow-900/80', 'border-yellow-400', 'text-yellow-300', 'bg-orange-900/80', 'border-orange-400', 'text-orange-300', 'bg-fuchsia-900/80', 'border-fuchsia-400', 'text-fuchsia-300', 'shadow-[0_0_15px_rgba(192,38,211,0.5)]');
            btn.classList.add('bg-gray-900', 'border-gray-700', 'text-gray-400');
        });
        
        const btnActivo = document.getElementById(`btn_fin_${tipo}`);
        btnActivo.classList.remove('bg-gray-900', 'border-gray-700', 'text-gray-400');
        
        if(tipo === 'spin') btnActivo.classList.add('bg-blue-900/80', 'border-blue-400', 'text-blue-300');
        if(tipo === 'over') btnActivo.classList.add('bg-yellow-900/80', 'border-yellow-400', 'text-yellow-300');
        if(tipo === 'burst') btnActivo.classList.add('bg-orange-900/80', 'border-orange-400', 'text-orange-300');
        if(tipo === 'xtreme') btnActivo.classList.add('bg-fuchsia-900/80', 'border-fuchsia-400', 'text-fuchsia-300', 'shadow-[0_0_15px_rgba(192,38,211,0.5)]');
        
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
        } else {
            btnGuardar.classList.add('bg-gray-800', 'text-gray-500', 'pointer-events-none');
            btnGuardar.classList.remove('bg-green-600', 'text-white', 'hover:bg-green-500', 'shadow-[0_0_20px_rgba(22,163,74,0.6)]');
        }
    }

    function resetBatallaUI() {
        battleState.p1ComboId = null;
        battleState.p2ComboId = null;
        battleState.winnerId = null;
        battleState.finishType = null;
        
        document.getElementById('zona_resultados').classList.add('opacity-30', 'pointer-events-none');
        
        document.getElementById('btn_ganador_p1').className = "flex-1 bg-gray-900 border border-gray-700 text-gray-400 font-bold py-4 rounded-xl text-xs uppercase tracking-widest transition-all shadow-lg";
        document.getElementById('btn_ganador_p2').className = "flex-1 bg-gray-900 border border-gray-700 text-gray-400 font-bold py-4 rounded-xl text-xs uppercase tracking-widest transition-all shadow-lg";

        ['spin', 'over', 'burst', 'xtreme'].forEach(t => {
            const btn = document.getElementById(`btn_fin_${t}`);
            btn.className = "bg-gray-900 border border-gray-700 text-gray-400 font-black py-4 rounded-xl text-sm tracking-wider uppercase transition-all shadow-md flex flex-col items-center gap-1";
        });

        const btnGuardar = document.getElementById('btn_guardar_batalla');
        btnGuardar.className = "w-full bg-gray-800 text-gray-500 font-black py-4 rounded-xl text-base tracking-widest uppercase transition-all shadow-lg pointer-events-none";
        btnGuardar.innerHTML = "REGISTRAR PUNTOS";
    }

    function cerrarBatalla() {
        document.getElementById('batallaModal').classList.add('hidden');
        document.getElementById('batallaModal').classList.remove('flex');

        // --- NUEVO: Soltar la mesa ---
        unlockMatchOnServer(battleState.matchId);
        abrirPanelArbitraje(currentArenaEventId);
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
                    p1Score.classList.add('scale-150', 'text-white'); setTimeout(() => p1Score.classList.remove('scale-150', 'text-white'), 300);
                } else {
                    p2Score.classList.add('scale-150', 'text-white'); setTimeout(() => p2Score.classList.remove('scale-150', 'text-white'), 300);
                }

                abrirPanelArbitraje(currentArenaEventId); 
                
                if (data.match_completed) {
                    mostrarToast("¡PARTIDA FINALIZADA!");
                    setTimeout(() => cerrarBatalla(), 800);
                } else {
                    mostrarToast("Puntos guardados. Siguiente lanzamiento.");
                    // Recargamos la batalla para que actualice los beys tachados del ciclo 3on3
                    await abrirBatalla(battleState.matchId);
                }
            } else {
                mostrarToast(data.message, true);
                btn.classList.remove('pointer-events-none');
                btn.innerHTML = "REGISTRAR PUNTOS";
            }
        } catch (error) {
            mostrarToast("Error de red al guardar", true);
            btn.classList.remove('pointer-events-none');
            btn.innerHTML = "REGISTRAR PUNTOS";
        }
    }

    // --- CLASIFICACIÓN EN VIVO ---
    async function verClasificacionArena(eventId) {
        triggerHaptic('light');
        const container = document.getElementById('arbitrajeContent');
        
        const btnGenerar = document.getElementById('btnGenerarRonda');
        if(btnGenerar) btnGenerar.classList.add('hidden');
        
        container.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-cyan-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">CALCULANDO CLASIFICACIÓN...</p></div>';
        
        try {
            const res = await fetch(`/api/torneos/${eventId}/clasificacion`);
            const data = await res.json();
            
            let htmlTemp = `
                <div class="flex bg-gray-900 rounded-lg p-1 mb-4 mt-2">
                    <button onclick="abrirPanelArbitraje(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">CRUCES</button>
                    <button class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-cyan-400 bg-gray-800 font-bold transition-colors">TABLA</button>
                    <button onclick="verCuadroArena(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">CUADRO</button>
                </div>
                <div class="bg-gray-800/50 border border-gray-700/80 rounded-xl overflow-hidden shadow-lg mt-4">
                    <table class="w-full text-left text-sm text-gray-300">
                        <thead class="bg-gray-900/80 text-[10px] sm:text-xs uppercase text-gray-500 font-bold tracking-widest">
                            <tr>
                                <th class="px-3 py-3 w-8 text-center">#</th>
                                <th class="px-2 py-3">BLADER</th>
                                <th class="px-2 py-3 text-center">PTS</th>
                                <th class="px-3 py-3 text-center">BCH</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
            `;
            
            const jugadores = data.clasificacion || data.standings || [];
            
            if (jugadores.length > 0) {
                jugadores.forEach((player, index) => {
                    const isTop = index < 4 ? 'bg-cyan-900/10' : ''; 
                    const rankColor = index === 0 ? 'text-yellow-400' : (index < 4 ? 'text-cyan-400' : 'text-gray-500');
                    
                    htmlTemp += `
                        <tr class="${isTop} hover:bg-gray-700/30 transition-colors">
                            <td class="px-3 py-3 text-center font-mono font-bold ${rankColor}">${index + 1}</td>
                            <td class="px-2 py-3 font-bold text-white uppercase tracking-wide truncate max-w-[120px]">${player.name || player.user_name}</td>
                            <td class="px-2 py-3 text-center font-black font-mono text-yellow-400">${player.points}</td>
                            <td class="px-3 py-3 text-center font-mono text-gray-400 text-xs">${player.buchholz}</td>
                        </tr>
                    `;
                });
            } else {
                htmlTemp += `
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500 font-bold tracking-widest text-xs">NO HAY RESULTADOS AÚN</td>
                    </tr>`;
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
                btn.disabled = true;
                btn.classList.add('opacity-30', 'pointer-events-none');
            } else {
                btn.disabled = false;
                btn.classList.remove('opacity-30', 'pointer-events-none');
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

    async function verCuadroArena(eventId) {
        triggerHaptic('light');
        const container = document.getElementById('arbitrajeContent');
        container.innerHTML = '<div class="text-center mt-10"><i class="fas fa-circle-notch fa-spin text-fuchsia-500 text-3xl mb-3"></i><p class="text-gray-500 text-sm font-bold tracking-widest">DIBUJANDO CUADRO...</p></div>';
        
        try {
            const res = await fetch(`/api/torneos/${eventId}/cuadro/html`);
            const htmlBracket = await res.text();
            
            let htmlTemp = `
                <div class="flex bg-gray-900 rounded-lg p-1 mb-4 mt-2">
                    <button onclick="abrirPanelArbitraje(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">CRUCES</button>
                    <button onclick="verClasificacionArena(${eventId})" class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-gray-500 hover:text-white transition-colors">TABLA</button>
                    <button class="flex-1 text-center text-[10px] sm:text-xs tracking-widest py-2 rounded text-fuchsia-400 bg-gray-800 font-bold transition-colors">CUADRO</button>
                </div>
            ` + htmlBracket;
            
            container.innerHTML = htmlTemp;
        } catch (error) {
            container.innerHTML = '<p class="text-red-500 text-center font-bold mt-10">Error de conexión al cargar el cuadro.</p>';
        }
    }

    async function deshacerUltimoRegistro() {
        triggerHaptic('heavy');
        if(!confirm("¿Deshacer la última acción de este combate?")) return;
        
        try {
            const res = await fetch(`/api/torneos/partida/${battleState.matchId}/undo`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            if (data.success) {
                mostrarToast(data.message);
                // Recargamos la batalla para actualizar marcador, warnings y desbloquear el bey si procede
                await abrirBatalla(battleState.matchId);
                abrirPanelArbitraje(currentArenaEventId);
            } else {
                mostrarToast(data.message, true);
            }
        } catch (error) {
            mostrarToast("Error de red", true);
        }
    }

    async function lanzarWarning(playerKey) {
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
                document.getElementById('batalla_score_p1').innerText = data.p1_score;
                document.getElementById('batalla_score_p2').innerText = data.p2_score;
                
                const btn = document.getElementById(`btn_warning_${playerKey}`);
                const posClass = playerKey === 'p1' ? 'left-2' : 'right-2';

                if (data.warning_active) {
                    btn.className = `absolute top-2 ${posClass} transition-colors z-20 text-lg text-yellow-500 drop-shadow-[0_0_8px_rgba(234,179,8,0.8)]`;
                } else {
                    btn.className = `absolute top-2 ${posClass} transition-colors z-20 text-lg text-gray-700 hover:text-yellow-500`;
                }
                
                btn.blur();
                abrirPanelArbitraje(currentArenaEventId); 

                if (data.match_completed) {
                    setTimeout(() => cerrarBatalla(), 800);
                }
            }
        } catch (error) {
            mostrarToast("Error al asignar warning", true);
        }
    }

    function alternarLadosBatalla() {
        triggerHaptic('light');
        isSideSwapped = !isSideSwapped;
        aplicarEstadoLados();
    }

    function aplicarEstadoLados() {
        const p1Name = document.getElementById('batalla_p1_name');
        const headerRow = document.getElementById('batallaPlayersHeader') || (p1Name ? p1Name.closest('.grid, .flex') : null);
        const decksRow = document.getElementById('batallaDecksContainer') || document.getElementById('deck_p1_container')?.parentElement?.parentElement;
        const winnersRow = document.getElementById('batallaFinishButtonsContainer') || document.getElementById('btn_ganador_p1')?.parentElement;

        [headerRow, decksRow, winnersRow].forEach(el => {
            if (!el) return;
            if (el.classList.contains('grid-cols-2')) {
                el.classList.remove('grid', 'grid-cols-2');
                el.classList.add('flex', 'w-full');
                Array.from(el.children).forEach(child => child.classList.add('flex-1', 'min-w-0'));
            }
            if (isSideSwapped) {
                el.classList.add('flex-row-reverse');
            } else {
                el.classList.remove('flex-row-reverse');
            }
        });
    }

    function obtenerCombosUsadosEnCiclo(battles) {
        if (!battles || !Array.isArray(battles)) return { p1Used: [], p2Used: [] };

        const realBattles = battles.filter(b => 
            ['spin', 'over', 'burst', 'xtreme'].includes(b.finish_type) && 
            b.player1_combo_id && b.player2_combo_id
        );

        const remainder = realBattles.length % 3;
        if (remainder === 0) {
            return { p1Used: [], p2Used: [] };
        }

        const currentSetBattles = realBattles.slice(-remainder);
        return {
            p1Used: currentSetBattles.map(b => Number(b.player1_combo_id)),
            p2Used: currentSetBattles.map(b => Number(b.player2_combo_id))
        };
    }

    async function lockMatchOnServer(matchId) {
        try {
            const res = await fetch(`/api/torneos/partida/${matchId}/lock`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            });
            return await res.json();
        } catch (e) { return { success: true }; /* Ignoramos fallos de red leves */ }
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

    // --- PWA SETUP ---
    let deferredPrompt; const installBanner = document.getElementById('installBanner'); const btnInstall = document.getElementById('btnInstall');
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream; const isStandalone = window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches;
    if (isIOS && !isStandalone) { document.getElementById('iosInstallBanner').classList.remove('hidden'); }
    window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; installBanner.classList.remove('hidden'); });
    btnInstall.addEventListener('click', async () => { if (deferredPrompt) { deferredPrompt.prompt(); const { outcome } = await deferredPrompt.userChoice; deferredPrompt = null; installBanner.classList.add('hidden'); if (outcome === 'accepted') mostrarToast("⏳ Instalando..."); } });
    window.addEventListener('appinstalled', () => { installBanner.classList.add('hidden'); mostrarToast("✅ App instalada"); });
    if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('/sw.js').catch(e => console.error('Error PWA: ', e)); }); }
</script>