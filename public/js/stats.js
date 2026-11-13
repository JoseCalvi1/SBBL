    let labRadarChartInstance = null;
    let comboTrendChartInstance = null;
    let sidebarVisible = true;
    let currentTourneyDataCache = null;
    let firstTourneyLoaded = false;

    function toggleStatsSidebar() {
        const sidebarCol = document.getElementById('statsSidebarCol');
        const mainCol    = document.getElementById('statsMainContentCol');
        const lbl        = document.getElementById('lblToggleSidebar');

        sidebarVisible = !sidebarVisible;
        if (sidebarVisible) {
            sidebarCol.classList.remove('d-none');
            mainCol.className = 'col-12 col-lg-9';
            if (lbl) lbl.innerText = 'OCULTAR FILTROS';
        } else {
            sidebarCol.classList.add('d-none');
            mainCol.className = 'col-12';
            if (lbl) lbl.innerText = 'MOSTRAR FILTROS Y MENÚ';
        }
    }

    function abrirInfoModal(titulo, texto) {
        document.getElementById('infoExplainTitle').innerText = titulo;
        document.getElementById('infoExplainBody').innerText  = texto;
        const m = new bootstrap.Modal(document.getElementById('infoExplainModal'));
        m.show();
    }

    function switchStatsMainTab(tab) {
        ['combos', 'tierlist', 'partes', 'lab', 'torneos', 'h2h'].forEach(t => {
            const el = document.getElementById(`main-tab-${t}`);
            if (el) el.classList.toggle('d-none', t !== tab);
        });

        const selectEl = document.getElementById('sidebarModuleSelect');
        if (selectEl && selectEl.value !== tab) {
            selectEl.value = tab;
        }

        const hiddenVista = document.getElementById('hidden_vista_input');
        if (hiddenVista) {
            hiddenVista.value = tab;
        }

        if (tab === 'torneos' && !firstTourneyLoaded) {
            const firstBtn = document.querySelector('#tourneySelectorList .tourney-pill-btn');
            if (firstBtn) firstBtn.click();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if ((window.SBBL_CONFIG?.activeTabInitial || '') === 'torneos') {
            const firstBtn = document.querySelector('#tourneySelectorList .tourney-pill-btn');
            if (firstBtn) firstBtn.click();
        }
    });

    function switchTierCategory(catKey) {
        ['blades', 'ratchets', 'bits', 'over_blades', 'assist_blades'].forEach(k => {
            document.getElementById(`tier-container-${k}`).classList.toggle('d-none', k !== catKey);
            const btn = document.getElementById(`btn-tier-${k}`);
            if (btn) {
                btn.className = (k === catKey) ? 'btn btn-sm btn-warning fw-bold font-Oswald' : 'btn btn-sm btn-outline-light fw-bold font-Oswald';
            }
        });
    }

    function switchPartSubTab(partKey) {
        ['blades', 'over_blades', 'assist_blades', 'ratchets', 'bits'].forEach(k => {
            document.getElementById(`subtab-part-${k}`).classList.toggle('d-none', k !== partKey);
            const btn = document.getElementById(`btn-part-${k}`);
            if (btn) {
                btn.className = (k === partKey) ? 'btn btn-sm btn-warning fw-bold font-Oswald' : 'btn btn-sm btn-outline-light fw-bold font-Oswald';
            }
        });
    }

    // =========================================================================
    // GRÁFICO DE EVOLUCIÓN MENSUAL DEL COMBO (/beyblade-database/stats/combo-trend)
    // =========================================================================
    async function abrirModalEvolucionCombo(blade, ratchet, bit) {
        document.getElementById('comboTrendModalTitle').innerText = `EVOLUCIÓN MENSUAL: ${blade} ${ratchet} ${bit}`;
        const modal = new bootstrap.Modal(document.getElementById('comboTrendModal'));
        modal.show();

        try {
            const res = await fetch(`/beyblade-database/stats/combo-trend?blade=${encodeURIComponent(blade)}&ratchet=${encodeURIComponent(ratchet)}&bit=${encodeURIComponent(bit)}`);
            const data = await res.json();
            const timeline = data.timeline || [];

            const ctx = document.getElementById('chartComboTrendCanvas');
            if (comboTrendChartInstance) comboTrendChartInstance.destroy();

            comboTrendChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: timeline.map(r => r.ym),
                    datasets: [
                        {
                            label: 'Winrate (%)',
                            data: timeline.map(r => r.winrate),
                            borderColor: '#00ffcc',
                            backgroundColor: 'rgba(0, 255, 204, 0.15)',
                            tension: 0.3,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Combates Jugados',
                            type: 'bar',
                            data: timeline.map(r => r.total_matches),
                            backgroundColor: 'rgba(255, 193, 7, 0.5)',
                            borderColor: '#ffc107',
                            borderWidth: 1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { ticks: { color: '#cbd5e1', font: { family: 'Oswald' } } },
                        y: { position: 'left', min: 0, max: 100, ticks: { color: '#00ffcc' }, title: { display: true, text: 'Winrate %', color: '#00ffcc' } },
                        y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#ffc107' }, title: { display: true, text: 'Combates', color: '#ffc107' } }
                    },
                    plugins: { legend: { labels: { color: '#fff', font: { family: 'Oswald' } } } }
                }
            });
        } catch (e) {
            alert('Error al cargar la evolución histórica del combo.');
        }
    }

    // =========================================================================
    // MÓDULO 05: TELEMETRÍA DE TORNEOS + CARGAR DECK EN BEY-LAB + REPLAY VISUAL
    // =========================================================================
    async function cargarTelemetriaTorneo(eventId, btnEl = null) {
        firstTourneyLoaded = true;
        document.querySelectorAll('#tourneySelectorList .tourney-pill-btn').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');

        const container = document.getElementById('tourneyTelemetryContainer');
        container.innerHTML = `<div class="text-center py-5 font-Oswald text-secondary fs-5">CARGANDO DATOS DEL TORNEO...</div>`;

        try {
            const res = await fetch(`/beyblade-database/stats/tournament/${eventId}`);
            const data = await res.json();
            currentTourneyDataCache = data;
            renderVistaTorneo('general');
        } catch (e) {
            container.innerHTML = `<div class="text-center text-danger font-Oswald py-4">Error al cargar las estadísticas del torneo.</div>`;
        }
    }

    function cargarDeckJugadorEnLab(playerId, side = 'my') {
        if (!currentTourneyDataCache) return;
        const player = (currentTourneyDataCache.standings || []).find(p => String(p.id) === String(playerId));
        if (!player || !player.deck || player.deck.length === 0) {
            alert('Este jugador no tiene un Deck registrado en este torneo.');
            return;
        }

        for (let i = 1; i <= 3; i++) limpiarSlotWebLab(side, i);

        player.deck.slice(0, 3).forEach((d, idx) => {
            const s = idx + 1;
            document.getElementById(`wlab_${side}_blade_${s}`).value   = d.blade || '';
            document.getElementById(`wlab_${side}_over_${s}`).value    = d.over_blade || '';
            document.getElementById(`wlab_${side}_assist_${s}`).value  = d.assist_blade || '';
            document.getElementById(`wlab_${side}_ratchet_${s}`).value = d.ratchet || '';
            document.getElementById(`wlab_${side}_bit_${s}`).value     = d.bit || '';
        });

        if (side === 'my') validarLegalidadWebLab();
        switchStatsMainTab('lab');
        document.getElementById('main-tab-lab').scrollIntoView({ behavior: 'smooth' });
    }

    function renderVistaTorneo(selectedPlayerId = 'general') {
        if (!currentTourneyDataCache) return;
        const data = currentTourneyDataCache;
        const ev   = data.event;
        const bs   = data.battle_stats;
        const standings = data.standings || [];
        const container = document.getElementById('tourneyTelemetryContainer');

        const totBouts = Math.max(1, bs.total_bouts);
        const pSpin    = Math.round((bs.spins / totBouts) * 100);
        const pOver    = Math.round((bs.overs / totBouts) * 100);
        const pBurst   = Math.round((bs.bursts / totBouts) * 100);
        const pXtreme  = Math.round((bs.xtremes / totBouts) * 100);

        let html = `
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center bg-black border border-secondary p-3 mb-4 gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark font-Oswald">${ev.type}</span>
                        <span class="text-info font-Oswald small">${ev.date} · ${ev.city} (${ev.region})</span>
                    </div>
                    <h3 class="font-Oswald text-white m-0 mt-1">${ev.name}</h3>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <label class="font-Oswald text-warning small me-1">INSPECCIONAR:</label>
                    <select id="tourneyPlayerSelect" onchange="renderVistaTorneo(this.value)" class="form-select form-select-sm bg-dark text-white border-warning font-Oswald fs-6" style="min-width: 240px;">
                        <option value="general" ${selectedPlayerId === 'general' ? 'selected' : ''}>VISTA GENERAL DEL TORNEO (${standings.length} BLADERS)</option>
                        ${standings.map(pl => `
                            <option value="${pl.id}" ${String(selectedPlayerId) === String(pl.id) ? 'selected' : ''}>
                                #${pl.shared_rank}º — ${pl.name} (${pl.wins}V - ${pl.losses}D)
                            </option>
                        `).join('')}
                    </select>
                    <a href="${ev.bracket_url}" target="_blank" class="btn btn-sm btn-outline-info font-Oswald">
                        VER CUADRO
                    </a>
                </div>
            </div>
        `;

        if (selectedPlayerId === 'general') {
            const top3 = standings.slice(0, 3);
            const medals = ['1º CAMPEÓN', '2º FINALISTA', '3º PUESTO'];

            html += `
                <div class="bg-black border border-secondary p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <span class="font-Oswald text-info">TELEMETRÍA DE LA ARENA (${bs.total_bouts} LANZAMIENTOS REGISTRADOS)</span>
                        <div class="d-flex gap-3 fw-bold small">
                            <span class="text-success">SPIN: ${bs.spins} (${pSpin}%)</span>
                            <span class="text-info">OVER: ${bs.overs} (${pOver}%)</span>
                            <span class="text-warning">BURST: ${bs.bursts} (${pBurst}%)</span>
                            <span style="color:#e879f9;">XTREME: ${bs.xtremes} (${pXtreme}%)</span>
                        </div>
                    </div>
                    <div class="d-flex w-100 bg-dark border border-secondary" style="height: 10px;">
                        <div style="width:${pSpin}%; background:#10b981;"></div>
                        <div style="width:${pOver}%; background:#0ea5e9;"></div>
                        <div style="width:${pBurst}%; background:#f59e0b;"></div>
                        <div style="width:${pXtreme}%; background:#d946ef;"></div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    ${top3.map((pl, idx) => `
                        <div class="col-md-4">
                            <div class="bg-black border ${idx === 0 ? 'border-warning' : 'border-secondary'} p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-2">
                                        <span class="font-Oswald ${idx === 0 ? 'text-warning' : 'text-info'}">${medals[idx]}</span>
                                        <button type="button" onclick="renderVistaTorneo('${pl.id}')" class="btn btn-xs btn-outline-warning font-Oswald py-0 px-2" style="font-size:0.75rem;">VER FICHA</button>
                                    </div>
                                    <div class="font-Oswald fs-4 text-white text-uppercase mb-2">${pl.name} <small class="text-secondary fs-6">(${pl.wins}V - ${pl.losses}D)</small></div>
                                    <div>
                                        ${pl.deck && pl.deck.length > 0 ? pl.deck.map(d => `
                                            <div class="d-flex justify-content-between align-items-center bg-dark px-2 py-1 mb-1 border border-secondary">
                                                <span class="small fw-bold text-white text-truncate pe-2">${d.combo}</span>
                                                <span class="badge bg-black text-warning font-Oswald shrink-0">${d.wins}W-${d.losses}L</span>
                                            </div>
                                        `).join('') : '<div class="text-secondary small fst-italic">Sin deck registrado</div>'}
                                    </div>
                                </div>
                                ${pl.deck && pl.deck.length > 0 ? `
                                    <div class="d-flex gap-1 mt-3 pt-2 border-top border-secondary">
                                        <button type="button" onclick="cargarDeckJugadorEnLab('${pl.id}', 'my')" class="btn btn-xs btn-outline-info font-Oswald flex-fill py-1" style="font-size:0.72rem;">CARGAR EN MI LAB</button>
                                        <button type="button" onclick="cargarDeckJugadorEnLab('${pl.id}', 'riv')" class="btn btn-xs btn-outline-danger font-Oswald flex-fill py-1" style="font-size:0.72rem;">USAR COMO RIVAL</button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">BEYBLADES MVP DEL TORNEO</h5>
                        ${data.top_combos && data.top_combos.length > 0 ? data.top_combos.map((tc, cIdx) => `
                            <div class="d-flex justify-content-between align-items-center bg-black border border-secondary p-2.5 mb-2">
                                <div>
                                    <span class="badge bg-warning text-dark font-Oswald me-1">#${cIdx + 1}</span>
                                    <span class="font-Oswald text-white fs-5">${tc.combo_name}</span>
                                    <div class="small text-secondary">Blader: <b class="text-info">${tc.owner_name}</b></div>
                                </div>
                                <div class="text-end font-Oswald">
                                    <div class="fs-5 text-success">${tc.wins} WINS</div>
                                    <div class="small text-warning">${tc.pts_generated} PTS GENERADOS</div>
                                </div>
                            </div>
                        `).join('') : '<div class="text-secondary small fst-italic">Sin combates individuales registrados en este torneo.</div>'}
                    </div>

                    <div class="col-lg-7">
                        <h5 class="font-Oswald text-info border-bottom border-secondary pb-2 mb-3">CLASIFICACIÓN GENERAL (TOCA UN BLADER PARA VER SU FICHA Y REPLAYS)</h5>
                        <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr class="font-Oswald small text-secondary">
                                        <th>POS</th>
                                        <th>BLADER</th>
                                        <th class="text-center">V - D</th>
                                        <th class="text-center text-warning">PTS</th>
                                        <th class="text-center text-info">BCH</th>
                                        <th class="text-center">DIF</th>
                                        <th class="text-end">FICHA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${standings.map(pl => `
                                        <tr style="cursor:pointer;" onclick="renderVistaTorneo('${pl.id}')">
                                            <td class="font-Oswald fs-5 ${pl.shared_rank <= 4 ? 'text-warning' : 'text-secondary'}">#${pl.shared_rank}º</td>
                                            <td class="font-Oswald text-white text-uppercase">${pl.name}</td>
                                            <td class="text-center font-Oswald"><span class="text-success">${pl.wins}V</span> - <span class="text-danger">${pl.losses}D</span></td>
                                            <td class="text-center font-Oswald text-warning fs-5">${pl.points}</td>
                                            <td class="text-center font-Oswald text-info">${pl.buchholz}</td>
                                            <td class="text-center font-Oswald ${pl.point_diff >= 0 ? 'text-success' : 'text-danger'}">${pl.point_diff > 0 ? '+' + pl.point_diff : pl.point_diff}</td>
                                            <td class="text-end"><button type="button" class="btn btn-xs btn-outline-info font-Oswald py-0 px-2">VER DECK</button></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } else {
            const player = standings.find(p => String(p.id) === String(selectedPlayerId));
            if (!player) return renderVistaTorneo('general');

            html += `
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <button type="button" onclick="renderVistaTorneo('general')" class="btn btn-sm btn-outline-warning font-Oswald">
                        VOLVER A LA VISTA GENERAL DEL TORNEO
                    </button>
                    ${player.deck && player.deck.length > 0 ? `
                        <div class="d-flex gap-2">
                            <button type="button" onclick="cargarDeckJugadorEnLab('${player.id}', 'my')" class="btn btn-sm btn-outline-info font-Oswald">
                                CARGAR ESTE DECK EN MI BEY-LAB
                            </button>
                            <button type="button" onclick="cargarDeckJugadorEnLab('${player.id}', 'riv')" class="btn btn-sm btn-outline-danger font-Oswald">
                                CARGAR COMO DECK RIVAL EN BEY-LAB
                            </button>
                        </div>
                    ` : ''}
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="kpi-box">
                            <div class="text-secondary font-Oswald small">POSICIÓN FINAL</div>
                            <div class="font-Oswald fs-2 text-warning lh-1 mt-1">#${player.shared_rank}º (${player.puesto})</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="kpi-box" style="border-left-color:#10b981;">
                            <div class="text-secondary font-Oswald small">BALANCE DE RONDAS</div>
                            <div class="font-Oswald fs-2 text-white lh-1 mt-1"><span class="text-success">${player.wins}V</span> - <span class="text-danger">${player.losses}D</span></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="kpi-box" style="border-left-color:var(--shonen-cyan);">
                            <div class="text-secondary font-Oswald small">PUNTOS / BUCHHOLZ</div>
                            <div class="font-Oswald fs-2 text-info lh-1 mt-1">${player.points} PTS <small class="fs-6 text-secondary">(${player.buchholz} BCH)</small></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="kpi-box" style="border-left-color:#d946ef;">
                            <div class="text-secondary font-Oswald small">DIFERENCIA DE PUNTOS (+/-)</div>
                            <div class="font-Oswald fs-2 ${player.point_diff >= 0 ? 'text-success' : 'text-danger'} lh-1 mt-1">${player.point_diff > 0 ? '+' + player.point_diff : player.point_diff}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-5">
                        <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">
                            RENDIMIENTO DEL DECK DE ${player.name.toUpperCase()}
                        </h5>
                        ${player.deck && player.deck.length > 0 ? player.deck.map((d, idx) => `
                            <div class="bg-black border border-secondary p-3 mb-2.5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-info text-dark font-Oswald me-1">BEY #0${idx + 1}</span>
                                        <span class="font-Oswald fs-4 text-warning">${d.combo}</span>
                                    </div>
                                    <div class="text-end font-Oswald">
                                        <span class="fs-4 ${d.winrate >= 50 ? 'text-success' : 'text-warning'}">${d.winrate}% WR</span>
                                        <div class="small text-secondary">${d.wins}W - ${d.losses}L (${d.pts_for} pts anotados)</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between small fw-bold bg-dark px-3 py-1.5 border border-secondary">
                                    <span class="text-success">SPIN: ${d.spins}</span>
                                    <span class="text-info">OVER: ${d.overs}</span>
                                    <span class="text-warning">BURST: ${d.bursts}</span>
                                    <span style="color:#e879f9;">XTREME: ${d.xtremes}</span>
                                </div>
                            </div>
                        `).join('') : '<div class="text-secondary fst-italic">Este jugador no registró piezas o no tuvo combates individuales registrados.</div>'}
                    </div>

                    <div class="col-lg-7">
                        <h5 class="font-Oswald text-info border-bottom border-secondary pb-2 mb-3">
                            HOJA DE RUTA Y REPLAY VISUAL LANCE POR LANCE (${player.matches.length})
                        </h5>
                        <div class="d-flex flex-column gap-2">
                            ${player.matches && player.matches.length > 0 ? player.matches.map((m, mIdx) => {
                                const hasReplay = m.timeline && m.timeline.length > 0;
                                return `
                                    <div class="bg-black border ${m.is_win ? 'border-success' : 'border-danger'} p-2.5">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-dark border border-secondary text-info font-Oswald me-2">${m.round} · ${m.phase}</span>
                                                <span class="font-Oswald text-white fs-5 text-uppercase">vs ${m.rival}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="font-Oswald fs-4 ${m.is_win ? 'text-success' : 'text-danger'}">
                                                    ${m.my_score} - ${m.riv_score}
                                                </span>
                                                <span class="badge ${m.is_win ? 'bg-success text-dark' : 'bg-danger text-white'} font-Oswald">${m.is_win ? 'WIN' : 'LOSS'}</span>
                                                ${hasReplay ? `<button type="button" onclick="document.getElementById('replay-box-${mIdx}').classList.toggle('d-none')" class="btn btn-xs btn-outline-light font-Oswald py-0.5 px-2" style="font-size:0.72rem;">VER REPLAY (${m.timeline.length})</button>` : ''}
                                            </div>
                                        </div>
                                        ${hasReplay ? `
                                            <div id="replay-box-${mIdx}" class="d-none mt-3 pt-2 border-top border-secondary">
                                                <div class="small font-Oswald text-warning mb-2">LÍNEA DE TIEMPO DEL ENFRENTAMIENTO:</div>
                                                ${m.timeline.map(b => `
                                                    <div class="timeline-step">
                                                        <div class="d-flex justify-content-between align-items-center bg-dark px-2.5 py-1.5 border border-secondary">
                                                            <div class="small">
                                                                <span class="text-secondary fw-bold me-1">LANCE #${b.bout_num}:</span>
                                                                <span class="text-info fw-bold">${b.my_combo}</span>
                                                                <span class="text-secondary mx-1">vs</span>
                                                                <span class="text-danger fw-bold">${b.riv_combo}</span>
                                                            </div>
                                                            <div class="font-Oswald small">
                                                                <span class="badge ${b.i_won ? 'bg-success text-dark' : 'bg-danger text-white'} me-1">${b.finish} (+${b.points})</span>
                                                                <span class="text-white">Marcador: ${b.running}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : ''}
                                    </div>
                                `;
                            }).join('') : '<div class="text-secondary fst-italic">Sin rondas registradas.</div>'}
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    // =========================================================================
    // MÓDULO 06: CARA A CARA ENTRE JUGADORES (/beyblade-database/stats/player-h2h)
    // =========================================================================
    async function ejecutarComparativaH2H() {
        const p1 = document.getElementById('h2h_player_1')?.value;
        const p2 = document.getElementById('h2h_player_2')?.value;

        if (!p1 || !p2 || p1 === p2) {
            alert('Selecciona dos jugadores distintos para comparar su historial.');
            return;
        }

        const container = document.getElementById('h2hResultsContainer');
        container.classList.remove('d-none');
        container.innerHTML = `<div class="lab-card p-5 text-center font-Oswald text-secondary fs-5">BUSCANDO HISTORIAL DE ENFRENTAMIENTOS...</div>`;

        try {
            const res = await fetch(`/beyblade-database/stats/player-h2h?p1=${p1}&p2=${p2}`);
            const data = await res.json();

            if (!data.success) {
                container.innerHTML = `<div class="lab-card p-4 text-center text-danger font-Oswald">${data.message || 'Error al comparar jugadores.'}</div>`;
                return;
            }

            const tot = Math.max(1, data.total_matches);
            const p1Pct = data.total_matches > 0 ? Math.round((data.p1.match_wins / tot) * 100) : 50;

            let html = `
                <div class="lab-card p-4 mb-4">
                    <div class="row align-items-center text-center g-3 mb-3">
                        <div class="col-md-4 text-md-start">
                            <div class="font-Oswald fs-3 text-info text-uppercase">${data.p1.name}</div>
                            <div class="small text-secondary">${data.p1.bouts_won} lances ganados · ${data.p1.total_pts} pts totales</div>
                            <div class="mt-2">
                                ${(data.p1.top_combos || []).map(c => `<span class="badge bg-dark border border-secondary text-light me-1">${c.combo} (${c.wins}W)</span>`).join('')}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small font-Oswald text-secondary">HISTORIAL DIRECTO (${data.total_matches} PARTIDAS)</div>
                            <div class="font-Oswald display-4 text-white lh-1 my-1">
                                <span class="text-info">${data.p1.match_wins}</span>
                                <span class="text-secondary mx-2">-</span>
                                <span class="text-danger">${data.p2.match_wins}</span>
                            </div>
                        </div>

                        <div class="col-md-4 text-md-end">
                            <div class="font-Oswald fs-3 text-danger text-uppercase">${data.p2.name}</div>
                            <div class="small text-secondary">${data.p2.bouts_won} lances ganados · ${data.p2.total_pts} pts totales</div>
                            <div class="mt-2">
                                ${(data.p2.top_combos || []).map(c => `<span class="badge bg-dark border border-secondary text-light ms-1">${c.combo} (${c.wins}W)</span>`).join('')}
                            </div>
                        </div>
                    </div>

                    <div class="energy-bar-bg" style="height: 14px; background:#dc2626;">
                        <div class="energy-bar-fill" style="width: ${p1Pct}%; background:#00ffcc;"></div>
                    </div>
                </div>

                <div class="lab-card p-4">
                    <h5 class="font-Oswald text-warning border-bottom border-secondary pb-2 mb-3">
                        REGISTRO DE PARTIDAS ENTRE ${data.p1.name.toUpperCase()} Y ${data.p2.name.toUpperCase()}
                    </h5>
                    ${data.matches && data.matches.length > 0 ? data.matches.map((m, idx) => {
                        const hasTl = m.timeline && m.timeline.length > 0;
                        return `
                            <div class="bg-black border ${m.p1_won ? 'border-info' : 'border-danger'} p-3 mb-2.5">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <span class="badge bg-warning text-dark font-Oswald me-2">${m.date}</span>
                                        <span class="font-Oswald text-white fs-5">${m.event_name}</span>
                                        <span class="text-secondary small ms-2">(${m.round})</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="font-Oswald fs-4">
                                            <span class="${m.p1_won ? 'text-info' : 'text-secondary'}">${data.p1.name} ${m.p1_score}</span>
                                            <span class="text-secondary mx-1">-</span>
                                            <span class="${!m.p1_won ? 'text-danger' : 'text-secondary'}">${m.p2_score} ${data.p2.name}</span>
                                        </span>
                                        ${hasTl ? `<button type="button" onclick="document.getElementById('h2h-tl-${idx}').classList.toggle('d-none')" class="btn btn-xs btn-outline-light font-Oswald py-0.5 px-2" style="font-size:0.75rem;">VER REPLAY (${m.timeline.length})</button>` : ''}
                                    </div>
                                </div>
                                ${hasTl ? `
                                    <div id="h2h-tl-${idx}" class="d-none mt-3 pt-2 border-top border-secondary">
                                        ${m.timeline.map(b => `
                                            <div class="timeline-step">
                                                <div class="d-flex justify-content-between align-items-center bg-dark px-2.5 py-1.5 border border-secondary">
                                                    <div class="small">
                                                        <span class="text-secondary fw-bold me-1">LANCE #${b.bout_num}:</span>
                                                        <span class="text-info fw-bold">${b.p1_combo}</span>
                                                        <span class="text-secondary mx-1">vs</span>
                                                        <span class="text-danger fw-bold">${b.p2_combo}</span>
                                                    </div>
                                                    <div class="font-Oswald small">
                                                        <span class="badge ${b.winner_p1 ? 'bg-info text-dark' : 'bg-danger text-white'} me-1">GANA ${b.winner_name.toUpperCase()} · ${b.finish} (+${b.points})</span>
                                                        <span class="text-white">Marcador: ${b.running}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('') : '<div class="text-secondary font-Oswald py-3 text-center">Estos dos Bladers aún no se han enfrentado directamente en ningún torneo registrado.</div>'}
                </div>
            `;

            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = `<div class="lab-card p-4 text-center text-danger font-Oswald">Error de red al consultar el Cara a Cara.</div>`;
        }
    }

    // =========================================================================
    // MÓDULO 04: BEY-LAB PRO Y EXPORTACIÓN DE INFORME TÁCTICO A PNG
    // =========================================================================
    function setWebLabMode(mode) {
        document.getElementById('web_lab_mode').value = mode;
        const bGlobal   = document.getElementById('btn-mode-global');
        const bPersonal = document.getElementById('btn-mode-personal');
        if (bGlobal) bGlobal.classList.toggle('active', mode === 'global_vs_global');
        if (bPersonal) bPersonal.classList.toggle('active', mode === 'personal_vs_global');

        if (!document.getElementById('web-lab-results').classList.contains('d-none')) {
            ejecutarAnalisisWebLab();
        }
    }

    function enviarComboAlLab(blade, over, assist, ratchet, bit) {
        switchStatsMainTab('lab');
        let targetSlot = 1;
        for (let i = 1; i <= 3; i++) {
            if (!document.getElementById(`wlab_my_blade_${i}`).value) {
                targetSlot = i;
                break;
            }
        }
        document.getElementById(`wlab_my_blade_${targetSlot}`).value   = blade || '';
        document.getElementById(`wlab_my_over_${targetSlot}`).value    = (over && over !== '-') ? over : '';
        document.getElementById(`wlab_my_assist_${targetSlot}`).value  = (assist && assist !== '-') ? assist : '';
        document.getElementById(`wlab_my_ratchet_${targetSlot}`).value = ratchet || '';
        document.getElementById(`wlab_my_bit_${targetSlot}`).value     = bit || '';
        validarLegalidadWebLab();
        document.getElementById('main-tab-lab').scrollIntoView({ behavior: 'smooth' });
    }

    function limpiarSlotWebLab(side, idx) {
        ['blade', 'over', 'assist', 'ratchet', 'bit'].forEach(p => {
            const el = document.getElementById(`wlab_${side}_${p}_${idx}`);
            if (el) el.value = '';
        });
        if (side === 'my') validarLegalidadWebLab();
    }

    function validarLegalidadWebLab() {
        const warn = document.getElementById('wlab-legality-warning');
        const blades = [], overs = [], assists = [], ratchets = [], bits = [];
        for (let i = 1; i <= 3; i++) {
            const b  = document.getElementById(`wlab_my_blade_${i}`)?.value;
            const ob = document.getElementById(`wlab_my_over_${i}`)?.value;
            const ab = document.getElementById(`wlab_my_assist_${i}`)?.value;
            const r  = document.getElementById(`wlab_my_ratchet_${i}`)?.value;
            const bt = document.getElementById(`wlab_my_bit_${i}`)?.value;
            if (b) blades.push(b);
            if (ob) overs.push(ob);
            if (ab) assists.push(ab);
            if (r && r !== 'UX Expanded') ratchets.push(r);
            if (bt && bt !== 'Without Bit') bits.push(bt);
        }
        const dup = arr => arr.filter((v, idx, a) => a.indexOf(v) !== idx);
        const errs = [];
        if (dup(blades).length) errs.push(`Blade (${dup(blades)[0]})`);
        if (dup(overs).length) errs.push(`Over (${dup(overs)[0]})`);
        if (dup(assists).length) errs.push(`Assist (${dup(assists)[0]})`);
        if (dup(ratchets).length) errs.push(`Ratchet (${dup(ratchets)[0]})`);
        if (dup(bits).length) errs.push(`Bit (${dup(bits)[0]})`);

        if (errs.length > 0) {
            warn.innerText = `Piezas repetidas en formato 3on3: ${errs.join(' · ')}`;
            warn.classList.remove('d-none');
        } else {
            warn.classList.add('d-none');
        }
    }

    async function importarMiUltimoDeckWeb() {
        try {
            const res = await fetch('/api/ultimo-deck');
            const deck = await res.json();
            if (!deck || deck.length === 0) {
                alert('No tienes ningún Deck registrado recientemente.');
                return;
            }
            deck.forEach((bey, idx) => {
                const i = idx + 1;
                if (i > 3) return;
                document.getElementById(`wlab_my_blade_${i}`).value   = bey.blade || '';
                document.getElementById(`wlab_my_over_${i}`).value    = (bey.over_blade && bey.over_blade !== '-') ? bey.over_blade : '';
                document.getElementById(`wlab_my_assist_${i}`).value  = (bey.assist_blade && bey.assist_blade !== '-') ? bey.assist_blade : '';
                document.getElementById(`wlab_my_ratchet_${i}`).value = bey.ratchet || '';
                document.getElementById(`wlab_my_bit_${i}`).value     = bey.bit || '';
            });
            validarLegalidadWebLab();
        } catch (e) {
            alert('Error al importar tu último Deck.');
        }
    }

    function cargarMetaBossWebLab() {
        for (let i = 1; i <= 3; i++) limpiarSlotWebLab('riv', i);
        ejecutarAnalisisWebLab();
    }

    function usarCounterRecomendado(blade, ratchet, bit, targetSlot = 1) {
        const slot = targetSlot || 1;
        document.getElementById(`wlab_my_blade_${slot}`).value   = blade || '';
        document.getElementById(`wlab_my_over_${slot}`).value    = '';
        document.getElementById(`wlab_my_assist_${slot}`).value  = '';
        document.getElementById(`wlab_my_ratchet_${slot}`).value = ratchet || '';
        document.getElementById(`wlab_my_bit_${slot}`).value     = bit || '';
        validarLegalidadWebLab();
        ejecutarAnalisisWebLab();
    }

    async function ejecutarAnalisisWebLab() {
        const miDeck = [];
        const rivalDeck = [];

        for (let i = 1; i <= 3; i++) {
            const mb = document.getElementById(`wlab_my_blade_${i}`)?.value;
            if (mb) {
                miDeck.push({
                    blade: mb,
                    over_blade: document.getElementById(`wlab_my_over_${i}`)?.value || '',
                    assist_blade: document.getElementById(`wlab_my_assist_${i}`)?.value || '',
                    ratchet: document.getElementById(`wlab_my_ratchet_${i}`)?.value || '',
                    bit: document.getElementById(`wlab_my_bit_${i}`)?.value || ''
                });
            }
            const rb = document.getElementById(`wlab_riv_blade_${i}`)?.value;
            if (rb) {
                rivalDeck.push({
                    blade: rb,
                    over_blade: document.getElementById(`wlab_riv_over_${i}`)?.value || '',
                    assist_blade: document.getElementById(`wlab_riv_assist_${i}`)?.value || '',
                    ratchet: document.getElementById(`wlab_riv_ratchet_${i}`)?.value || '',
                    bit: document.getElementById(`wlab_riv_bit_${i}`)?.value || ''
                });
            }
        }

        if (miDeck.length === 0) {
            alert('Selecciona al menos 1 Beyblade en tu Deck (Banco 01).');
            return;
        }

        const btn = document.getElementById('btnRunWebLab');
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<span>PROCESANDO SIMULACIÓN...</span>';

        try {
            const activeMode = document.getElementById('web_lab_mode')?.value || 'global_vs_global';
            const res = await fetch(window.SBBL_CONFIG?.routes?.labAnalyze || '/beyblade-database/stats/lab-analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mi_deck: miDeck,
                    rival_deck: rivalDeck,
                    mode: activeMode,
                    tipo_evento: document.querySelector('select[name="tipo_evento"]')?.value || '',
                    fecha_inicio: document.querySelector('input[name="fecha_inicio"]')?.value || '',
                    fecha_fin: document.querySelector('input[name="fecha_fin"]')?.value || ''
                })
            });
            const data = await res.json();

            if (rivalDeck.length === 0 && data.rival_deck_stats) {
                data.rival_deck_stats.forEach((r, idx) => {
                    const i = idx + 1;
                    if (i <= 3) {
                        document.getElementById(`wlab_riv_blade_${i}`).value   = r.blade || '';
                        document.getElementById(`wlab_riv_over_${i}`).value    = r.over_blade || '';
                        document.getElementById(`wlab_riv_assist_${i}`).value  = r.assist_blade || '';
                        document.getElementById(`wlab_riv_ratchet_${i}`).value = r.ratchet || '';
                        document.getElementById(`wlab_riv_bit_${i}`).value     = r.bit || '';
                    }
                });
            }

            document.getElementById('web-lab-results').classList.remove('d-none');

            const modeBanner = document.getElementById('wlab-mode-banner');
            if (data.mode === 'personal_vs_global') {
                modeBanner.innerHTML = `MODO ACTIVO: <b>TUS ESTADÍSTICAS PERSONALES VS META GLOBAL</b> (${data.personal_matches_found} combates tuyos encontrados)`;
            } else {
                modeBanner.innerHTML = `MODO ACTIVO: <b>ESTADÍSTICAS GLOBALES DE LA LIGA VS GLOBAL</b>`;
            }

            const orderStr = (data.recommended_order || []).map((c, idx) => `<span class="badge bg-dark border border-info text-white me-1">${idx + 1}º ${c}</span>`).join(' ');

            const countersList = data.recommended_counters || [];
            const counterHtml = countersList.length > 0
                ? `<div class="d-flex flex-column gap-1.5 mt-2">
                    ${countersList.map((c, cIdx) => `
                        <div class="d-flex justify-content-between align-items-center bg-dark border border-secondary px-2 py-1">
                            <div class="text-truncate pe-2">
                                <span class="badge bg-warning text-dark font-Oswald me-1">#${cIdx + 1}</span>
                                <span class="text-white fw-bold small">${c.combo}</span>
                                <span class="text-success fw-bold ms-1" style="font-size:0.75rem;">(${c.winrate}% WR · ${c.wins}W/${c.total}G)</span>
                            </div>
                            <button type="button"
                                    onclick="usarCounterRecomendado('${c.blade}','${c.ratchet}','${c.bit}', ${c.replace_slot || 1})"
                                    class="btn btn-xs btn-warning fw-bold py-0 px-2 shrink-0"
                                    style="font-size:0.72rem;">
                                CAMBIAR POR #0${c.replace_slot || 1}
                            </button>
                        </div>
                    `).join('')}
                   </div>`
                : `<div class="text-secondary small mt-1">Sin datos de counter directo adicionales</div>`;

            document.getElementById('wlab-tactical-advisor').innerHTML = `
                <div class="col-md-4">
                    <div class="kpi-box h-100" style="border-left-color: #00ffcc;">
                        <div class="text-info font-Oswald small">ORDEN ÓPTIMO DE LANZAMIENTO</div>
                        <div class="mt-2">${orderStr || '---'}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="kpi-box h-100" style="border-left-color: #ff2a2a;">
                        <div class="text-danger font-Oswald small">ESLABÓN MÁS EXPUESTO (#0${data.weak_slot || 1})</div>
                        <div class="font-Oswald fs-5 text-white mt-1">${data.weak_link || '---'}</div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="kpi-box h-100" style="border-left-color: #ffc107;">
                        <div class="text-warning font-Oswald small">TOP 3 COUNTERS LEGALES POR % DE VICTORIA</div>
                        ${counterHtml}
                    </div>
                </div>
            `;

            const wr = data.overall_winrate;
            document.getElementById('wlab-overall-wr').innerText = `${wr}%`;
            document.getElementById('wlab-rival-wr').innerText   = `${(100 - wr).toFixed(1)}%`;
            document.getElementById('wlab-overall-bar').style.width = `${wr}%`;

            let matrixHtml = `<table class="table table-dark table-bordered text-center align-middle mb-0"><thead><tr><th class="text-start text-info font-Oswald">TU BEY \\ RIVAL</th>`;
            data.rival_deck_stats.forEach(riv => {
                matrixHtml += `<th class="text-danger font-Oswald">${riv.short_name}<div class="small text-light opacity-75 fw-normal">${riv.parts_label}</div></th>`;
            });
            matrixHtml += `</tr></thead><tbody>`;

            data.matrix.forEach((row, rIdx) => {
                const mySt = data.my_deck_stats[rIdx];
                matrixHtml += `<tr><td class="text-start font-Oswald text-info">${mySt.short_name}<div class="small text-light opacity-75 fw-normal">${mySt.parts_label}</div></td>`;
                row.forEach(cell => {
                    const cWr = cell.winrate;
                    const badgeCol = cWr >= 55 ? 'bg-success bg-opacity-25 text-success border-success' : (cWr >= 45 ? 'bg-secondary bg-opacity-25 text-light border-secondary' : 'bg-danger bg-opacity-25 text-danger border-danger');
                    matrixHtml += `<td><div class="border ${badgeCol} p-2"><div class="font-Oswald fs-4 lh-1">${cWr}%</div><div class="small opacity-75">${cell.source}</div></div></td>`;
                });
                matrixHtml += `</tr>`;
            });
            matrixHtml += `</tbody></table>`;
            document.getElementById('wlab-matrix-container').innerHTML = matrixHtml;

            let cardsHtml = '';
            data.my_deck_stats.forEach(st => {
                let deltaBadge = '';
                if (st.delta_vs_global !== null && st.delta_vs_global !== undefined) {
                    const sign = st.delta_vs_global >= 0 ? '+' : '';
                    const dCol = st.delta_vs_global >= 0 ? 'bg-success text-dark' : 'bg-danger text-white';
                    deltaBadge = `<span class="badge ${dCol} mt-1 d-inline-block">${sign}${st.delta_vs_global}% vs Media Global (${st.global_winrate}%)</span>`;
                }

                cardsHtml += `
                    <div class="col-md-4">
                        <div class="bg-black border border-secondary p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="font-Oswald fs-4 text-warning">${st.short_name}</div>
                                    <div class="text-white font-Oswald small">${st.parts_label}</div>
                                    <div class="small text-secondary mt-1">${st.wins}W - ${st.losses}L (${st.sample_size} comb. · ${st.precision})</div>
                                </div>
                                <div class="text-end">
                                    <span class="font-Oswald fs-3 text-info">${st.winrate}% WR</span>
                                    <div>${deltaBadge}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between small fw-bold mt-2 mb-1">
                                <span class="text-success">SPN ${st.spin_pct}%</span>
                                <span class="text-info">OVR ${st.over_pct}%</span>
                                <span class="text-warning">BRS ${st.burst_pct}%</span>
                                <span style="color:#e879f9;">XTR ${st.xtreme_pct}%</span>
                            </div>
                            <div class="d-flex w-100 bg-dark" style="height: 8px;">
                                <div style="width:${st.spin_pct}%; background:#10b981;"></div>
                                <div style="width:${st.over_pct}%; background:#0ea5e9;"></div>
                                <div style="width:${st.burst_pct}%; background:#f59e0b;"></div>
                                <div style="width:${st.xtreme_pct}%; background:#d946ef;"></div>
                            </div>
                            <div class="row g-2 mt-2 text-center font-Oswald">
                                <div class="col-4"><div class="bg-dark p-1 border border-secondary"><div class="small text-secondary">PTS/WIN</div><div class="text-success">${st.avg_dmg_dealt.toFixed(2)}</div></div></div>
                                <div class="col-4"><div class="bg-dark p-1 border border-secondary"><div class="small text-secondary">PTS/LOSS</div><div class="text-danger">${st.avg_dmg_taken.toFixed(2)}</div></div></div>
                                <div class="col-4"><div class="bg-dark p-1 border border-secondary"><div class="small text-secondary">RIESGO BURST</div><div class="text-warning">${st.burst_risk}%</div></div></div>
                            </div>
                        </div>
                    </div>
                `;
            });
            document.getElementById('wlab-my-cards-container').innerHTML = cardsHtml;

            const ctxRadar = document.getElementById('chartLabRadar');
            if (labRadarChartInstance) labRadarChartInstance.destroy();
            labRadarChartInstance = new Chart(ctxRadar, {
                type: 'radar',
                data: {
                    labels: ['Winrate Global', 'Explosividad Xtreme', 'Poder Burst', 'Control Spin/Over', 'Defensa Anti-Burst'],
                    datasets: [
                        {
                            label: 'Tu Deck',
                            data: data.radar_my_deck,
                            backgroundColor: 'rgba(0, 255, 204, 0.25)',
                            borderColor: '#00ffcc',
                            pointBackgroundColor: '#00ffcc',
                            borderWidth: 2
                        },
                        {
                            label: 'Deck Rival / Meta',
                            data: data.radar_rival_deck,
                            backgroundColor: 'rgba(255, 42, 42, 0.25)',
                            borderColor: '#ff2a2a',
                            pointBackgroundColor: '#ff2a2a',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            angleLines: { color: 'rgba(255,255,255,0.15)' },
                            grid: { color: 'rgba(255,255,255,0.15)' },
                            pointLabels: { color: '#fff', font: { family: 'Oswald', size: 12 } },
                            ticks: { display: false }
                        }
                    },
                    plugins: { legend: { labels: { color: '#fff', font: { family: 'Oswald' } } } }
                }
            });

        } catch (e) {
            alert('Error al ejecutar la simulación en el Bey-Lab.');
        } finally {
            btn.innerHTML = oldHtml;
        }
    }

    function exportarInformeLabPNG(btnEl) {
        const node = document.getElementById('wlab-export-capture-area');
        const oldTxt = btnEl.innerText;
        btnEl.innerText = 'GENERANDO IMAGEN...';

        html2canvas(node, {
            scale: 2,
            backgroundColor: "#060911",
            useCORS: true
        }).then(canvas => {
            btnEl.innerText = oldTxt;
            const link = document.createElement('a');
            link.download = 'sbbl_informe_beylab.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        }).catch(() => {
            btnEl.innerText = oldTxt;
            alert('Error al generar la imagen del informe.');
        });
    }

    function downloadImage() {
        const node = document.getElementById('export-top-node');
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>PROCESANDO...</span>';

        html2canvas(node, {
            scale: 2,
            backgroundColor: "#0b0f19",
            useCORS: true
        }).then(canvas => {
            btn.innerHTML = originalText;
            let link = document.createElement('a');
            link.download = 'sbbl_top_semanal.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
        }).catch(() => {
            btn.innerHTML = originalText;
            alert("Error al generar la imagen.");
        });
    }
