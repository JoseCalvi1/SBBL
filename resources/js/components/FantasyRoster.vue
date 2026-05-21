<template>
    <div class="fantasy-app-container">

        <transition name="slide-down">
            <div v-if="toast.show" class="cyber-toast" :class="toast.type">
                <span class="toast-icon" v-if="toast.type === 'success'">✅</span>
                <span class="toast-icon" v-if="toast.type === 'error'">❌</span>
                <span class="toast-icon" v-if="toast.type === 'info'">ℹ️</span>
                {{ toast.message }}
            </div>
        </transition>

        <div v-if="confirmDialog.show" class="cyber-modal-overlay" style="z-index: 4000;">
            <div class="cyber-modal-content" style="border-top-color: #f2a900; text-align: center;">
                <h3 style="color: #f2a900; margin-bottom: 15px; font-family: 'Share Tech Mono';">// CONFIRMAR ACCIÓN</h3>
                <p style="color: #fff; margin-bottom: 25px;">{{ confirmDialog.message }}</p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button @click="closeConfirm" class="modal-action-btn" style="background: transparent; border: 1px solid #c5c6c7; color: #c5c6c7; flex: 1;">CANCELAR</button>
                    <button @click="executeConfirm" class="modal-action-btn" style="background: #f2a900; color: #000; flex: 1;">PROCEDER</button>
                </div>
            </div>
        </div>

        <header class="cyber-header">
            <div class="header-top">
                <h2 class="cyber-title">FANTASY // <span class="neon-text">SBBL</span></h2>
                <div class="status-bar"><span class="status-dot"></span> CONECTADO</div>
            </div>

            <div class="hud-stats">
                <div class="stat-box coins">
                    <span class="stat-label">CRÉDITOS</span>
                    <div class="stat-value">
                        <span class="coin-icon" style="margin-right: 2px;">⟠</span>
                        {{ (wallet.coins || 0).toLocaleString() }}
                    </div>
                </div>
                <div class="stat-box gems">
                    <span class="stat-label">GEMAS</span>
                    <div class="stat-value"><span class="icon">💎</span> {{ (wallet.gems || 0).toLocaleString() }}</div>
                </div>
                <div class="stat-box points">
                    <span class="stat-label">PUNTOS</span>
                    <div class="stat-value"><span class="icon">🏆</span> {{ (totalPoints || 0).toLocaleString() }}</div>
                </div>
            </div>
        </header>

        <div v-if="currentView === 'home'" class="home-view">
            <div class="cyber-zone" style="text-align: center; border-top: 3px solid #f2a900;">
                <h2 class="neon-text" style="font-size: 1.8rem; margin-bottom: 5px;">CENTRO DE MANDO</h2>
                <p style="color: #c5c6c7; font-size: 0.9rem;">Bienvenido al terminal de Sponsor.</p>
            </div>
            <div class="quick-actions" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button @click="changeView('roster')" class="cyber-btn-save" style="flex: 1; min-width: 45%; padding: 12px; font-size: 0.9rem; clip-path: none; border-radius: 4px;">IR AL EQUIPO</button>
                <button @click="changeView('gacha')" class="cyber-btn-save" style="flex: 1; min-width: 45%; padding: 12px; font-size: 0.9rem; background: #e0129a; color: white; clip-path: none; border-radius: 4px;">ABRIR SOBRES</button>
                <button @click="changeView('history')" class="cyber-btn-save" style="flex: 100%; padding: 12px; font-size: 0.9rem; background: transparent; border: 1px solid #45a29e; color: #45a29e; clip-path: none; border-radius: 4px;">◷ VER MI HISTORIAL</button>
            </div>
        </div>

        <div v-if="currentView === 'history'" class="home-view">
            <h2 class="cyber-title" style="text-align: center; margin-bottom: 20px; color: #45a29e;">// REGISTRO DEL VAR</h2>
            <div v-if="historyData.length === 0" style="text-align: center; color: #c5c6c7; margin-top: 40px;"><p>Aún no has puntuado en ningún torneo.</p></div>
            <div v-else class="history-list">
                <div v-for="(record, index) in historyData" :key="index" class="cyber-zone history-card">
                    <div class="history-header">
                        <span class="history-date">📅 {{ record.date }}</span>
                        <span class="history-points">🏆 +{{ record.total_points }} PTS</span>
                    </div>
                    <div class="history-lineup">
                        <p style="font-size: 0.75rem; color: #45a29e; margin-bottom: 5px; font-family: 'Share Tech Mono';">FOTO FINISH DEL TORNEO:</p>
                        <ul>
                            <li v-for="(player, pIndex) in record.lineup" :key="pIndex" class="history-player-row">
                                <span class="history-player-name">{{ player.player_name }}</span>
                                <div class="history-badges">
                                    <span v-if="player.is_captain" class="h-badge cap">👑 x2</span>
                                    <span v-if="player.level_multiplier > 1.0" class="h-badge prem">⚡ x{{ player.level_multiplier }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="currentView === 'roster'">

            <div class="cyber-zone zone-starters">
                <div class="zone-header">
                    <h3>[ TITULARES ]</h3>
                    <span class="counter" :class="{'limit-reached': titulares.length === 3}">{{ titulares.length }} / 3</span>
                </div>
                <p class="cyber-subtitle" style="font-size: 0.8rem; margin-bottom:10px;">ELIGE A TU CAPITÁN (PUNTÚA X2)</p>
                <draggable v-model="titulares" group="jugadores" class="drag-grid" @change="checkLimit" :disabled="isBulkMode" :fallbackTolerance="5">
                    <div v-for="element in titulares" :key="element.id"
                         class="cyber-card starter-card"
                         :class="{'is-cap-card': element.id === captainId, 'disabled-card': isBulkMode}"
                         :style="{ borderBottom: '4px solid ' + (element.player?.detailed_stats?.rarity_color || '#c5c6c7') }"
                         @click="!isBulkMode && openPlayerModal(element, 'roster')">

                        <div v-if="getTotalMultiplier(element) > 1.0" class="premium-badge"
                             :style="{ backgroundColor: getTotalMultiplier(element) >= 1.5 ? '#00ffcc' : '#f2a900', color: '#000' }">
                            <span>⚡ x{{ getTotalMultiplier(element) }}</span>
                        </div>

                        <button class="captain-btn" :class="{'active': element.id === captainId}" @click.stop="!isBulkMode && setCaptain(element.id)">
                            <span v-if="element.id === captainId">👑 CAPITÁN</span><span v-else>BRAZALETE</span>
                        </button>
                        <span class="player-name" v-if="element.player && element.player.user">{{ element.player.user.name }}</span>
                        <div class="player-price-badge"><span class="coin-icon">⟠</span> {{ element.buy_price }}</div>
                    </div>
                </draggable>
            </div>

            <div class="cyber-zone zone-bench" :class="{'bulk-mode-active': isBulkMode}">
                <div class="zone-header">
                    <h3>[ BANQUILLO ]</h3>
                    <button @click="toggleBulkMode" class="bulk-toggle-btn" :class="{'active': isBulkMode}">
                        <span v-if="!isBulkMode">☑ VENTA MÚLTIPLE</span>
                        <span v-else>✖ CANCELAR</span>
                    </button>
                </div>

                <draggable v-model="banquillo" group="jugadores" class="drag-grid" :disabled="isBulkMode" :fallbackTolerance="5">
                    <div v-for="element in banquillo" :key="element.id"
                         class="cyber-card bench-card"
                         :class="{'selected-for-sell': isBulkMode && selectedBulk.includes(element.id)}"
                         :style="{ borderBottom: '4px solid ' + (element.player?.detailed_stats?.rarity_color || '#c5c6c7') }"
                         @click="handleBenchClick(element)">

                        <div v-if="isBulkMode" class="bulk-checkbox">
                            <span v-if="selectedBulk.includes(element.id)">✓</span>
                        </div>

                        <div v-if="getTotalMultiplier(element) > 1.0" class="premium-badge"
                             :style="{ backgroundColor: getTotalMultiplier(element) >= 1.5 ? '#00ffcc' : '#f2a900', color: '#000' }">
                            <span>⚡ x{{ getTotalMultiplier(element) }}</span>
                        </div>

                        <div v-if="!isBulkMode && getCopiesCount(element.fantasy_real_player_id, element.level_multiplier) >= 3" class="fusion-ready-tag">
                            🧬 LISTO
                        </div>

                        <span class="player-name" v-if="element.player && element.player.user">{{ element.player.user.name }}</span>
                        <div class="player-price-badge"><span class="coin-icon">⟠</span> {{ element.buy_price }}</div>

                        <button v-if="!isBulkMode" @click.stop="sellPlayerConfirm(element.id, element.buy_price)" class="sell-btn">
                            VENDER ({{ Math.round(element.buy_price * 0.8) }})
                        </button>
                    </div>
                </draggable>
            </div>
        </div>

        <div v-if="currentView === 'market'" class="market-view">
            <h2 class="cyber-title" style="text-align: center; margin-bottom: 20px;">// MERCADO</h2>
            <div class="market-filters">
                <input type="text" v-model="searchQuery" placeholder="BUSCAR BLADER..." class="cyber-input">
                <div class="filter-row">
                    <select v-model="selectedRarity" class="cyber-select">
                        <option value="ALL">TODAS LAS RAREZAS</option>
                        <option value="COMÚN">COMÚN</option>
                        <option value="RARO">RARO</option><option value="ÉPICO">ÉPICO</option><option value="LEGENDARIO">LEGENDARIO</option>
                    </select>
                    <select v-model="sortBy" class="cyber-select">
                        <option value="price_desc">PRECIO: MAYOR A MENOR</option><option value="price_asc">PRECIO: MENOR A MAYOR</option>
                    </select>
                </div>
            </div>
            <div class="drag-grid">
                <div v-for="player in filteredMarketPlayers" :key="player.id"
                     class="cyber-card market-card"
                     :style="{ borderBottom: '4px solid ' + (player.detailed_stats?.rarity_color || '#c5c6c7') }"
                     @click="openPlayerModal(player, 'market')">
                    <span class="player-name">{{ player.name }}</span>
                    <div class="player-price-badge" style="margin: 10px 0;"><span class="coin-icon">⟠</span> {{ player.price }}</div>
                    <button @click.stop="buyPlayer(player.id)" class="buy-btn">COMPRAR</button>
                </div>
            </div>
        </div>

        <div v-if="currentView === 'gacha'" class="market-view" style="text-align: center; margin-top: 20px;">
            <h2 class="cyber-title" style="color: #e0129a; margin-bottom: 10px;">// EXTRACCIÓN</h2>
            <p style="color: #c5c6c7; margin-bottom: 30px;">Top 500 Bladers Garantizados.</p>

            <div class="gacha-pack-container" style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                <div class="pack-box normal-pack">
                    <div class="cube-visual" :class="{'shake-animation': isOpeningPack}">📦</div>
                    <h4 style="margin: 10px 0; color: #fff;">ESTÁNDAR (x1.0)</h4>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: 15px;">
                        <button @click="openPack('standard', 1)" class="gacha-btn" :disabled="isOpeningPack">x1 (50 💎)</button>
                        <button @click="openPack('standard', 10)" class="gacha-btn" :disabled="isOpeningPack" style="background:#8a0b5f;">x10 (500 💎)</button>
                    </div>
                </div>

                <div class="pack-box premium-pack">
                    <div class="cube-visual" :class="{'shake-animation': isOpeningPack}" style="filter: drop-shadow(0 0 20px #f2a900);">✨📦✨</div>
                    <h4 style="margin: 10px 0; color: #f2a900;">PREMIUM (x1.2)</h4>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: 15px;">
                        <button @click="openPack('premium', 1)" class="gacha-btn premium-btn" :disabled="isOpeningPack">x1 (150 💎)</button>
                        <button @click="openPack('premium', 10)" class="gacha-btn premium-btn" :disabled="isOpeningPack" style="background:#b37d00 !important;">x10 (1500 💎)</button>
                    </div>
                </div>

                <div class="pack-box item-pack" style="border-color: #45a29e; margin-top: 20px;">
                    <div class="cube-visual" :class="{'shake-animation': isOpeningItem}" style="filter: drop-shadow(0 0 15px #45a29e);">🎒</div>
                    <h4 style="margin: 10px 0; color: #45a29e;">SUMINISTROS</h4>
                    <p style="font-size:0.7rem; color:#c5c6c7;">Potencia a tus titulares con Objetos.</p>
                    <div style="display: flex; gap: 10px; width: 100%; margin-top: 15px;">
                        <button @click="openItemGacha('coins')" class="gacha-btn" :disabled="isOpeningItem" style="background:#45a29e; color:#0b0c10;">150 ⟠</button>
                        <button @click="openItemGacha('gems')" class="gacha-btn" :disabled="isOpeningItem" style="background:#e0129a;">50 💎</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="currentView === 'ranking'" class="market-view">
            <h2 class="cyber-title" style="text-align: center; margin-bottom: 20px; color: #f2a900;">// TOP SPONSORS</h2>
            <div class="market-filters" style="border-color: #f2a900; margin-bottom: 25px;">
                <input type="text" v-model="searchRanking" placeholder="FILTRAR POR NOMBRE..." class="cyber-input" style="border-color: #f2a900; margin-bottom: 10px;">
                <div class="filter-row">
                    <select v-model="sortRankingBy" class="cyber-select" style="border-color: #f2a900;">
                        <option value="points_desc">MAYOR PUNTUACIÓN</option><option value="name_asc">ALFABÉTICO</option>
                    </select>
                    <select v-model="rankingLimit" class="cyber-select" style="border-color: #f2a900; max-width: 100px;">
                        <option value="25">TOP 25</option><option value="100">TOP 100</option><option value="ALL">TODOS</option>
                    </select>
                </div>
            </div>
            <div class="ranking-list" style="display: flex; flex-direction: column; gap: 10px;">
                <div v-for="manager in filteredRanking" :key="manager.position" class="cyber-card" style="height: auto; flex-direction: row; padding: 15px; border-left: 4px solid #f2a900;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #45a29e; width: 50px;">#{{ manager.position }}</div>
                    <div style="flex-grow: 1; font-size: 1.1rem; color: #fff; font-weight: bold;">{{ manager.name }}</div>
                    <div class="player-price-badge" style="background: rgba(242, 169, 0, 0.2);"><span class="icon">🏆</span> {{ manager.points }}</div>
                </div>
            </div>
        </div>

        <nav class="bottom-nav">
            <button @click="changeView('roster')" :class="{'active': currentView === 'roster'}"><span style="font-size: 1.2rem;">⊞</span><span>EQUIPO</span></button>
            <button @click="changeView('market')" :class="{'active': currentView === 'market'}"><span style="font-size: 1.2rem;">⚑</span><span>MERC</span></button>
            <div class="nav-center-wrapper" @click="changeView('home')">
                <div class="nav-center-btn" :class="{'active': currentView === 'home'}"><span style="font-size: 1.8rem; margin-bottom: 4px;">⌂</span></div>
            </div>
            <button @click="changeView('gacha')" :class="{'active': currentView === 'gacha'}"><span style="font-size: 1.2rem;">💎</span><span>TIENDA</span></button>
            <button @click="changeView('ranking')" :class="{'active': currentView === 'ranking'}"><span style="font-size: 1.2rem;">🏆</span><span>RANK</span></button>
        </nav>

        <div class="action-bar" v-if="currentView === 'roster'">
            <button v-if="!isBulkMode" @click="saveLineup" class="cyber-btn-save" :disabled="isSaving">
                <span v-if="!isSaving" class="btn-text">GUARDAR ALINEACIÓN</span><span v-else class="btn-text">PROCESANDO...</span>
            </button>
            <button v-else @click="executeBulkSellConfirm" class="cyber-btn-save" style="background: #ff0055; border:none;" :disabled="selectedBulk.length === 0 || isSaving">
                VENDER {{ selectedBulk.length }} BLADERS (+{{ bulkSellTotal }} ⟠)
            </button>
        </div>

        <div v-if="gachaResults.length > 0" class="cyber-modal-overlay" @click.self="closeGachaModal" style="z-index: 2000; align-items: flex-start; overflow-y: auto; padding-top: 50px;">
            <div class="cyber-modal-content gacha-reveal" style="max-width: 500px; margin-bottom: 50px;">
                <h3 class="gacha-title" style="margin-bottom: 20px;">¡DATOS EXTRAÍDOS!</h3>

                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div v-for="(result, index) in gachaResults" :key="index" class="cyber-card starter-card" style="height: auto; padding: 15px;" :style="{ borderBottom: '4px solid ' + (result.detailed_stats?.rarity_color || '#c5c6c7') }">

                        <div v-if="result.level_multiplier > 1.0" class="premium-badge">⚡ x{{ result.level_multiplier }}</div>
                        <span class="rarity-badge" :style="{ color: result.detailed_stats?.rarity_color, borderColor: result.detailed_stats?.rarity_color }">{{ result.detailed_stats?.rarity || 'COMÚN' }}</span>
                        <span class="player-name" style="font-size: 1.2rem; margin: 10px 0;">{{ result.name }}</span>

                        <div v-if="result.is_duplicate" class="duplicate-warning" style="width: 100%; padding: 5px; margin-bottom: 5px; background: rgba(242, 169, 0, 0.2); border-color: #f2a900; color: #f2a900;">
                            <p style="margin:0;">⚠️ YA LO TENÍAS (Guárdalo para fusionar)</p>
                        </div>

                        <div style="width: 100%; display: flex; gap: 10px; margin-top: 5px;">
                            <div style="flex: 1; text-align: center; color: #66fcf1; font-family: 'Share Tech Mono'; font-size: 0.8rem; padding: 8px; border: 1px solid #1f2833; border-radius: 4px;">✅ Banquillo</div>
                            <button @click="sellFromGacha(index, result.roster_id, result.price)" class="sell-btn" style="flex: 1; margin: 0; padding: 8px;">VENDER (+{{ Math.round(result.price * 0.8) }} ⟠)</button>
                        </div>
                    </div>
                </div>

                <button class="modal-action-btn buy" style="width: 100%; margin-top: 20px;" @click="closeGachaModal">ACEPTAR TODO</button>
            </div>
        </div>

        <div v-if="showRewardModal" class="cyber-modal-overlay" style="z-index: 3000;">
            <div class="cyber-modal-content" style="border-top-color: #66fcf1; text-align: center; animation: popIn 0.5s ease-out forwards;">
                <h2 style="color: #66fcf1; margin-bottom: 5px; font-size: 2rem;">¡BONO DIARIO!</h2>
                <p style="color: #c5c6c7; margin-bottom: 20px; font-family: 'Share Tech Mono';">Día {{ dailyReward?.day || 1 }} de tu racha de conexión.</p>

                <div v-if="dailyReward && dailyReward.coins > 0" style="font-size: 3rem; color: #f2a900; font-weight: bold; margin-bottom: 20px; text-shadow: 0 0 20px rgba(242, 169, 0, 0.4);">
                    +{{ dailyReward.coins }} <span class="coin-icon" style="font-size: 2rem;">⟠</span>
                </div>
                <div v-if="dailyReward && dailyReward.gems > 0" style="font-size: 3rem; color: #e0129a; font-weight: bold; margin-bottom: 20px; text-shadow: 0 0 20px rgba(224, 18, 154, 0.4);">
                    +{{ dailyReward.gems }} <span style="font-size: 2rem;">💎</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 25px; padding: 10px; background: rgba(0,0,0,0.5); border-radius: 8px;">
                    <div v-for="n in 7" :key="n" :style="{
                        width: '35px', height: '35px', borderRadius: '50%',
                        background: n < (dailyReward?.day || 1) ? '#66fcf1' : (n === (dailyReward?.day || 1) ? '#f2a900' : '#1f2833'),
                        color: n <= (dailyReward?.day || 1) ? '#000' : '#45a29e',
                        display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 'bold', fontSize: '0.9rem',
                        boxShadow: n === (dailyReward?.day || 1) ? '0 0 10px #f2a900' : 'none'
                    }">{{ n }}</div>
                </div>

                <button @click="showRewardModal = false" class="cyber-btn-save" style="width: 100%; font-size: 1.2rem;">RECLAMAR</button>
            </div>
        </div>

        <div v-if="selectedPlayer && !isBulkMode" class="cyber-modal-overlay" @click.self="closeModal">
            <div class="cyber-modal-content" :style="{ borderTopColor: selectedPlayer.stats?.rarity_color || '#c5c6c7' }">
                <button class="close-modal-btn" @click="closeModal">X</button>

                <div class="modal-header">
                    <h2 :style="{ color: selectedPlayer.stats?.rarity_color || '#fff' }">{{ selectedPlayer.name }}</h2>

                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px;">
                        <span class="rarity-badge" :style="{ borderColor: selectedPlayer.stats?.rarity_color || '#c5c6c7', color: selectedPlayer.stats?.rarity_color || '#c5c6c7' }">
                            {{ selectedPlayer.stats?.rarity || 'DESCONOCIDO' }}
                        </span>

                        <span v-if="selectedPlayer.total_multiplier > 1.0" class="rarity-badge" style="background: #f2a900; color: #000; border-color: #f2a900; font-weight: bold;">
                            ⚡ TOTAL x{{ selectedPlayer.total_multiplier }}
                        </span>
                    </div>
                </div>

                <div v-if="selectedPlayer.context === 'roster'" class="equipment-slots-grid">
                    <div v-for="slot in ['lanzador', 'equipacion', 'guantes', 'mascara']" :key="slot"
                         class="slot-box" @click="showItemSelector = slot">
                        <div class="slot-label">{{ slot.toUpperCase() }}</div>

                        <div v-if="selectedPlayer.equipped_items && selectedPlayer.equipped_items[slot]" class="equipped-item-info">
                            <div class="item-dot" :style="{ background: selectedPlayer.equipped_items[slot].rarity_color }"></div>
                            <span class="item-name-mini">{{ selectedPlayer.equipped_items[slot].name }}</span>
                            <span class="item-mult-mini">x{{ selectedPlayer.equipped_items[slot].stat_multiplier }}</span>
                        </div>

                        <div v-else class="slot-empty">
                            <span v-if="slot === 'lanzador'">🚀</span><span v-if="slot === 'equipacion'">👕</span>
                            <span v-if="slot === 'guantes'">🧤</span><span v-if="slot === 'mascara'">🎭</span>
                            <p>VACÍO</p>
                        </div>
                    </div>
                </div>

                <div v-if="showItemSelector" class="inventory-selector-overlay">
                    <div class="inventory-content">
                        <div class="inv-header">
                            <h3>ELEGIR {{ showItemSelector.toUpperCase() }}</h3>
                            <button class="close-modal-btn" style="position:relative; top:0; right:0;" @click="showItemSelector = null">X</button>
                        </div>
                        <div class="inv-list">
                            <div class="inv-item none" @click="handleEquip(selectedPlayer.id, null, showItemSelector)">
                                ❌ DESEQUIPAR ACTUAL
                            </div>
                            <div v-for="item in inventoryItems.filter(i => i.slot_type === showItemSelector)"
                                 :key="item.id" class="inv-item"
                                 :style="{borderLeftColor: item.rarity_color}"
                                 @click="handleEquip(selectedPlayer.id, item.id, showItemSelector)">
                                <span>{{ item.name }}</span>
                                <strong>x{{ item.stat_multiplier }}</strong>
                            </div>
                            <p v-if="inventoryItems.filter(i => i.slot_type === showItemSelector).length === 0" style="text-align:center; padding:20px; color:#45a29e; font-family: 'Share Tech Mono';">
                                No tienes objetos sueltos de este tipo.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="performance-chart" style="background: rgba(0,0,0,0.4); padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p class="chart-title" style="font-size: 0.8rem; color: #45a29e; font-family: 'Share Tech Mono'; text-align: center; margin-bottom: 15px; margin-top: 0;">RENDIMIENTO RECIENTE (Max 7 pts)</p>
                    <div class="chart-bars" style="display: flex; justify-content: space-around; align-items: flex-end; height: 80px;">
                        <div v-for="(score, index) in (selectedPlayer.stats?.scores || [0,0,0,0,0])" :key="index" style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <span style="font-size: 0.75rem; color: #fff; font-weight: bold;">{{ score }}</span>
                            <div style="width: 14px; height: 60px; background: #000; border-radius: 2px; display: flex; align-items: flex-end; overflow: hidden;">
                                <div :style="{ height: Math.min((score / 7 * 100), 100) + '%', backgroundColor: selectedPlayer.stats?.rarity_color || '#66fcf1', width: '100%', transition: 'height 0.5s ease-out' }"></div>
                            </div>
                            <span style="font-size: 0.65rem; color: #c5c6c7; font-family: 'Share Tech Mono';">T-{{ 5 - index }}</span>
                        </div>
                    </div>
                </div>
                <div style="text-align: center; margin-bottom: 15px; color: #f2a900; font-family: 'Share Tech Mono'; font-size: 1.1rem;">
                    🏆 Suma últimos 5: {{ selectedPlayer.stats?.total_last_5 || 0 }} pts
                </div>

                <div v-if="selectedPlayer.context === 'roster' && selectedPlayer.level_multiplier < 1.5" style="margin: 15px 0; padding: 12px; background: rgba(18, 20, 26, 0.8); border: 1px dashed #45a29e; border-radius: 8px; text-align: center;">
                    <p style="font-family: 'Share Tech Mono'; font-size: 0.75rem; color: #45a29e; margin: 0 0 10px 0; letter-spacing: 1px;">// CÁMARA DE ALQUIMIA</p>

                    <button @click="fusePlayerConfirm(selectedPlayer.fantasy_real_player_id, selectedPlayer.level_multiplier)"
                            class="modal-action-btn"
                            :disabled="selectedPlayer.copies_count < 3"
                            :style="{ width: '100%', padding: '10px', background: selectedPlayer.copies_count >= 3 ? 'linear-gradient(45deg, #00ffcc, #e0129a)' : '#242831', color: selectedPlayer.copies_count >= 3 ? '#000' : '#45a29e', fontWeight: 'bold', border: 'none', cursor: selectedPlayer.copies_count >= 3 ? 'pointer' : 'not-allowed', boxShadow: selectedPlayer.copies_count >= 3 ? '0 0 15px rgba(0, 255, 204, 0.4)' : 'none' }">
                        🧬 FUSIONAR RANGO ({{ selectedPlayer.copies_count }} / 3)
                    </button>

                    <p v-if="selectedPlayer.copies_count >= 3" style="font-size: 0.7rem; color: #00ffcc; margin: 6px 0 0 0; font-family: 'Share Tech Mono';">¡MUTACIÓN LISTA! Evoluciona al siguiente rango.</p>
                    <p v-else style="font-size: 0.7rem; color: #ff0055; margin: 6px 0 0 0; font-family: 'Share Tech Mono';">Faltan {{ 3 - selectedPlayer.copies_count }} copias idénticas de nivel x{{ selectedPlayer.level_multiplier }} en el banquillo.</p>
                </div>

                <div v-if="selectedPlayer.context === 'roster' && selectedPlayer.level_multiplier >= 1.5" style="margin: 15px 0; padding: 10px; text-align: center; color: #00ffcc; border: 1px solid #00ffcc; background: rgba(0,255,204,0.1); border-radius: 8px; font-family: 'Share Tech Mono'; font-size: 0.8rem; box-shadow: 0 0 10px rgba(0,255,204,0.2);">
                    ✨ CRONOS EVOLUTION: ESTE BLADER SE ENCUENTRA EN SU MÁXIMO RANGO POSIBLE (⚡ x{{ selectedPlayer.level_multiplier }})
                </div>

                <div class="modal-footer" style="display: flex; justify-content: center; margin-top: 10px;">
                    <button v-if="selectedPlayer.context === 'market'" @click.stop="buyPlayer(selectedPlayer.id); closeModal()" class="modal-action-btn buy" style="width: 100%; padding: 12px; font-size: 1rem;">FICHAR (⟠ {{ selectedPlayer.price }})</button>
                    <button v-if="selectedPlayer.context === 'roster'" @click.stop="sellPlayerConfirm(selectedPlayer.id, selectedPlayer.price); closeModal()" class="modal-action-btn sell" style="width: 100%; padding: 12px; font-size: 1rem;">VENDER (⟠ {{ Math.round(selectedPlayer.price * 0.8) }})</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable';
import axios from 'axios';

export default {
    components: { draggable },
    data() {
        return {
            // Notifications Custom
            toast: { show: false, message: '', type: 'info' },
            confirmDialog: { show: false, message: '', onConfirm: null },

            isSaving: false,
            currentView: 'home',
            titulares: [],
            banquillo: [],
            marketPlayers: [],
            inventoryItems: [],
            wallet: { coins: 0, gems: 0 },
            totalPoints: 0,
            rankingPlayers: [],
            selectedPlayer: null,
            captainId: null,
            searchQuery: '',
            selectedRarity: 'ALL',
            sortBy: 'price_desc',
            searchRanking: '',
            sortRankingBy: 'points_desc',
            rankingLimit: 25,
            historyData: [],
            gachaResults: [],
            isOpeningPack: false,
            isOpeningItem: false,
            showItemSelector: null,
            isBulkMode: false,
            selectedBulk: [],
            dailyReward: null,
            showRewardModal: false,
        };
    },
    computed: {
        filteredMarketPlayers() {
            let result = [...this.marketPlayers];
            if (this.searchQuery.trim() !== '') result = result.filter(p => p.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
            if (this.selectedRarity !== 'ALL') result = result.filter(p => p.detailed_stats?.rarity === this.selectedRarity);
            result = result.sort((a, b) => (this.sortBy === 'price_desc') ? b.price - a.price : a.price - b.price);
            return result;
        },
        filteredRanking() {
            let result = [...this.rankingPlayers];
            if (this.searchRanking.trim() !== '') result = result.filter(m => m.name.toLowerCase().includes(this.searchRanking.toLowerCase()));
            result = result.sort((a, b) => {
                if (this.sortRankingBy === 'points_desc') return b.points - a.points;
                if (this.sortRankingBy === 'name_asc') return a.name.localeCompare(b.name);
                return 0;
            });
            if (this.rankingLimit !== 'ALL') result = result.slice(0, parseInt(this.rankingLimit));
            return result;
        },
        bulkSellTotal() {
            return this.banquillo.filter(p => this.selectedBulk.includes(p.id)).reduce((total, p) => total + Math.round(p.buy_price * 0.8), 0);
        }
    },
    created() {
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response && error.response.status === 401) { window.location.href = '/login'; }
                return Promise.reject(error);
            }
        );
    },
    mounted() {
        this.loadTeam(); this.loadMarket(); this.loadHistory(); this.checkDailyReward();
    },
    methods: {
        // --- CUSTOM UI HELPERS ---
        showToast(message, type = 'info') {
            this.toast = { show: true, message, type };
            setTimeout(() => { this.toast.show = false; }, 3000);
        },
        showConfirm(message, callback) {
            this.confirmDialog = { show: true, message, onConfirm: callback };
        },
        closeConfirm() { this.confirmDialog.show = false; },
        executeConfirm() {
            if (this.confirmDialog.onConfirm) this.confirmDialog.onConfirm();
            this.closeConfirm();
        },

        changeView(view) {
            this.currentView = view;
            this.isBulkMode = false;
            this.selectedBulk = [];
            if (view === 'ranking') this.loadRanking();
        },
        async checkDailyReward() {
            try {
                const response = await axios.post('/api/claim-daily-reward');
                if (response.data.status === 'claimed') {
                    this.dailyReward = response.data;
                    this.showRewardModal = true;
                    this.wallet = response.data.wallet;
                }
            } catch (error) { console.log('Sin premio diario nuevo.'); }
        },
        async loadTeam() {
            try {
                const response = await axios.get('/api/my-team');
                const roster = response.data.roster || [];
                this.titulares = roster.filter(p => p.status === 'starter');
                this.banquillo = roster.filter(p => p.status === 'bench');
                this.inventoryItems = response.data.inventory_items || [];
                this.wallet = response.data.wallet || { coins: 0, gems: 0 };
                this.totalPoints = response.data.total_points || 0;
                if (!this.captainId && this.titulares.length > 0) {
                    const cap = this.titulares.find(p => p.is_captain);
                    this.captainId = cap ? cap.id : this.titulares[0].id;
                }
            } catch (error) { console.error(error); }
        },
        async loadMarket() {
            try {
                const response = await axios.get('/api/market-players');
                this.marketPlayers = response.data || [];
            } catch (error) { console.error(error); }
        },
        async loadHistory() {
            try {
                const response = await axios.get('/api/fantasy-history');
                this.historyData = response.data || [];
            } catch (error) { console.error(error); }
        },
        async loadRanking() {
            try {
                const response = await axios.get('/api/fantasy-ranking');
                this.rankingPlayers = response.data || [];
            } catch (error) { console.error(error); }
        },
        async buyPlayer(playerId) {
            try {
                const response = await axios.post('/api/buy-player', { player_id: playerId });
                this.showToast(response.data.message, 'success');
                this.loadTeam(); this.loadMarket();
            } catch (error) { this.showToast(error.response?.data?.error || 'Error al comprar', 'error'); }
        },

        sellPlayerConfirm(rosterId, buyPrice) {
            const sellPrice = Math.round(buyPrice * 0.8);
            this.showConfirm(`¿Vender este Blader por ${sellPrice} Créditos? Los objetos equipados volverán al inventario.`, async () => {
                try {
                    await axios.post('/api/sell-player', { roster_id: rosterId });
                    this.showToast(`Blader vendido por ${sellPrice} Créditos.`, 'success');
                    this.loadTeam(); this.loadMarket();
                } catch (error) { this.showToast('Error al vender', 'error'); }
            });
        },

        setCaptain(rosterId) { this.captainId = rosterId; },
        checkLimit() {
            if (this.titulares.length > 3) {
                this.showToast('⚠️ Máximo 3 titulares permitidos.', 'error');
                this.banquillo.push(this.titulares.pop());
            }
        },
        async saveLineup() {
            this.isSaving = true;
            const allPlayers = [
                ...this.titulares.map(p => ({ id: p.id, status: 'starter', is_captain: p.id === this.captainId })),
                ...this.banquillo.map(p => ({ id: p.id, status: 'bench', is_captain: false }))
            ];
            try {
                await axios.post('/api/save-lineup', { roster: allPlayers });
                this.showToast('✅ Alineación Guardada', 'success');
                this.loadTeam();
            } catch (error) { this.showToast('❌ Error al guardar', 'error'); }
            finally { this.isSaving = false; }
        },

        // --- SISTEMA GACHA (JUGADORES) ---
        async openPack(type, amount) {
            const cost = (type === 'premium' ? 150 : 50) * amount;
            if (this.wallet.gems < cost) return this.showToast(`Necesitas ${cost} 💎`, 'error');
            this.isOpeningPack = true;
            try {
                const response = await axios.post('/api/open-pack', { type: type, amount: amount });
                setTimeout(() => {
                    this.isOpeningPack = false;
                    this.gachaResults = response.data.players;
                    this.loadTeam();
                }, 800);
            } catch (error) {
                this.isOpeningPack = false;
                this.showToast(error.response?.data?.error || 'Error al abrir', 'error');
            }
        },
        async sellFromGacha(index, rosterId, price) {
            try {
                await axios.post('/api/sell-player', { roster_id: rosterId });
                this.showToast(`Vendido por ${Math.round(price * 0.8)} ⟠`, 'success');
                this.gachaResults.splice(index, 1);
                this.loadTeam();
                if (this.gachaResults.length === 0) this.closeGachaModal();
            } catch (error) { this.showToast('Error al vender', 'error'); }
        },
        closeGachaModal() { this.gachaResults = []; },

        // --- SISTEMA GACHA (OBJETOS RPG) ---
        async openItemGacha(currency) {
            if (currency === 'coins' && this.wallet.coins < 150) return this.showToast('Necesitas 150 ⟠', 'error');
            if (currency === 'gems' && this.wallet.gems < 50) return this.showToast('Necesitas 50 💎', 'error');

            this.isOpeningItem = true;
            try {
                const response = await axios.post('/api/open-item-pack', { currency: currency });
                setTimeout(() => {
                    this.isOpeningItem = false;
                    this.showToast(`¡Obtenido: ${response.data.item.name}!`, 'success');
                    this.loadTeam();
                }, 800);
            } catch (error) {
                this.isOpeningItem = false;
                this.showToast(error.response?.data?.error || 'Error de suministros', 'error');
            }
        },

        // --- SISTEMA EQUIPAMIENTO ---
        async handleEquip(rosterId, itemId, slotType) {
            try {
                await axios.post('/api/equip-item', { roster_id: rosterId, item_id: itemId, slot_type: slotType });
                this.showItemSelector = null;
                this.showToast(itemId ? 'Objeto equipado.' : 'Objeto desequipado.', 'success');
                await this.loadTeam();
                const updatedPlayer = this.titulares.find(p => p.id === rosterId) || this.banquillo.find(p => p.id === rosterId);
                if (updatedPlayer) this.selectedPlayer.equipped_items = updatedPlayer.equipped_items;
            } catch (error) { this.showToast(error.response?.data?.error || 'Error al equipar', 'error'); }
        },
        getTotalMultiplier(player) {
            if (!player) return 1.0;
            let total = parseFloat(player.level_multiplier || 1.0);

            if (player.equipped_items) {
                Object.values(player.equipped_items).forEach(item => {
                    if (item && item.stat_multiplier) {
                        // Sumamos solo el extra (Ej: 1.05 aporta +0.05)
                        total += (parseFloat(item.stat_multiplier) - 1.0);
                    }
                });
            }
            return parseFloat(total.toFixed(2));
        },

        // --- SISTEMA FUSIÓN (ALQUIMIA) ---
        getCopiesCount(realPlayerId, multiplier) {
            return this.banquillo.filter(p => p.fantasy_real_player_id === realPlayerId && p.level_multiplier === multiplier).length;
        },
        fusePlayerConfirm(realPlayerId, currentLevel) {
            const txt = currentLevel === 1.0 ? 'Premium x1.2' : 'Súper Cibernético x1.5';
            this.showConfirm(`¿Fusionar 3 copias y evolucionar a versión ${txt}?`, async () => {
                try {
                    const response = await axios.post('/api/fuse-players', { fantasy_real_player_id: realPlayerId, current_level: currentLevel });
                    this.showToast(response.data.message, 'success');
                    this.closeModal();
                    this.loadTeam();
                } catch (error) { this.showToast(error.response?.data?.error || 'Error en fusión', 'error'); }
            });
        },

        // --- MODAL Y BULK SELL ---
        openPlayerModal(item, context) {
            if (context === 'roster') {
                const realPlayerId = item.fantasy_real_player_id;
                const currentMult = item.level_multiplier;
                this.selectedPlayer = {
                    id: item.id, fantasy_real_player_id: realPlayerId, name: item.player.user.name, price: item.buy_price, stats: item.player.detailed_stats,
                    context: 'roster', level_multiplier: currentMult, copies_count: this.getCopiesCount(realPlayerId, currentMult),
                    equipped_items: item.equipped_items || {},
                    total_multiplier: this.getTotalMultiplier(item)
                };
            } else {
                this.selectedPlayer = { id: item.id, name: item.name, price: item.price, stats: item.detailed_stats, context: 'market' };
            }
        },
        closeModal() { this.selectedPlayer = null; this.showItemSelector = null; },
        toggleBulkMode() { this.isBulkMode = !this.isBulkMode; this.selectedBulk = []; },
        handleBenchClick(element) {
            if (this.isBulkMode) {
                const idx = this.selectedBulk.indexOf(element.id);
                if (idx > -1) this.selectedBulk.splice(idx, 1);
                else this.selectedBulk.push(element.id);
            } else { this.openPlayerModal(element, 'roster'); }
        },
        executeBulkSellConfirm() {
            this.showConfirm(`¿Vender ${this.selectedBulk.length} jugadores por ${this.bulkSellTotal} Créditos?`, async () => {
                this.isSaving = true;
                try {
                    await axios.post('/api/bulk-sell', { roster_ids: this.selectedBulk });
                    this.showToast(`Ganaste ${this.bulkSellTotal} Créditos`, 'success');
                    this.toggleBulkMode();
                    this.loadTeam();
                } catch (error) { this.showToast(error.response?.data?.error || 'Error venta', 'error'); }
                finally { this.isSaving = false; }
            });
        }
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&family=Share+Tech+Mono&display=swap');
.fantasy-app-container { background-color: #0b0c10; color: #c5c6c7; min-height: 100vh; padding: 20px 15px 100px 15px; font-family: 'Rajdhani', sans-serif; max-width: 600px; margin: 0 auto; position: relative; overflow-x: hidden; box-sizing: border-box; }

/* TOAST NOTIFICATIONS */
.cyber-toast { position: fixed; top: 20px; left: 50%; transform: translateX(-50%); padding: 12px 20px; background: rgba(18, 20, 26, 0.95); border: 1px solid #333; border-radius: 8px; font-family: 'Share Tech Mono'; font-size: 0.9rem; z-index: 5000; box-shadow: 0 10px 25px rgba(0,0,0,0.8); display: flex; align-items: center; gap: 10px; color: #fff; min-width: 250px; text-align: center; }
.cyber-toast.success { border-bottom: 3px solid #00ffcc; }
.cyber-toast.error { border-bottom: 3px solid #ff0055; }
.cyber-toast.info { border-bottom: 3px solid #66fcf1; }
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.3s ease; }
.slide-down-enter, .slide-down-leave-to { transform: translate(-50%, -100px); opacity: 0; }

.cyber-header { display: flex; flex-direction: column; gap: 15px; border-bottom: 1px solid #1f2833; padding-bottom: 20px; margin-bottom: 25px; }
.header-top { display: flex; justify-content: space-between; align-items: center; }
.cyber-title { font-size: 1.5rem; font-weight: 700; letter-spacing: 2px; }
.neon-text { color: #66fcf1; text-shadow: 0 0 10px rgba(102, 252, 241, 0.5); }
.status-bar { font-family: 'Share Tech Mono', monospace; font-size: 0.8rem; color: #45a29e; display: flex; align-items: center; gap: 5px; }
.status-dot { width: 8px; height: 8px; background-color: #66fcf1; border-radius: 50%; box-shadow: 0 0 8px #66fcf1; }
.hud-stats { display: flex; gap: 10px; }
.stat-box { flex: 1; background: #12141a; border: 1px solid #1f2833; border-radius: 8px; padding: 10px; border-left: 3px solid #ccc; }
.stat-box.coins { border-left-color: #f2a900; }
.stat-box.gems { border-left-color: #e0129a; }
.stat-box.points { border-left-color: #00ffcc; }
.stat-label { font-size: 0.7rem; color: #45a29e; font-family: 'Share Tech Mono'; display: block; }
.stat-value { font-size: 1.1rem; font-weight: 700; color: #fff; }
.drag-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; min-height: 150px; }
.cyber-card { height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 10px 5px; border-radius: 8px; position: relative; overflow: hidden; background: #12141a; border: 1px solid #333; cursor: pointer; transition: 0.2s; }
.disabled-card { opacity: 0.5; filter: grayscale(100%); pointer-events: none; }
.is-cap-card { border-top: 3px solid #f2a900 !important; box-shadow: 0 0 10px rgba(242, 169, 0, 0.3); }

/* Etiquetas Adaptadas */
.premium-badge { position: absolute; top: 0; right: 0; background: #f2a900; color: #000; font-size: 0.6rem; font-weight: bold; padding: 3px 5px; border-bottom-left-radius: 8px; font-family: 'Share Tech Mono', monospace; z-index: 10; }
.captain-btn { background: rgba(0,0,0,0.5); border: 1px solid #f2a900; color: #f2a900; font-size: 0.6rem; padding: 2px 4px; border-radius: 4px; z-index: 5; font-family: 'Share Tech Mono'; cursor: pointer; }
.captain-btn.active { background: #f2a900; color: #000; font-weight: bold; }
.player-name { font-size: 0.8rem; color: #fff; font-weight: 700; text-align: center; z-index: 2; width: 100%; padding: 0 5px; margin-top: 5px; box-sizing: border-box; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.player-price-badge { background: rgba(0,0,0,0.6); color: #f2a900; padding: 2px 6px; border-radius: 4px; font-family: 'Share Tech Mono'; font-size: 0.75rem; }
.cyber-zone { background: #12141a; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #1f2833; }
.zone-starters { border-top: 3px solid #66fcf1; }
.zone-bench { border-top: 3px solid #ff0055; transition: 0.3s; }
.zone-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.zone-header h3 { margin: 0; font-size: 1.2rem; color: #fff; }
.counter { font-family: 'Share Tech Mono', monospace; background: #1f2833; padding: 2px 8px; border-radius: 4px; }
.limit-reached { background: #66fcf1; color: #0b0c10; }
.buy-btn { width: 90%; background: #66fcf1; color: #0b0c10; border: none; padding: 5px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 0.8rem; border-radius: 4px; cursor: pointer; z-index: 10; transition: 0.2s; text-transform: uppercase; letter-spacing: 1px; }
.sell-btn { width: 90%; background: transparent; color: #ff0055; border: 1px solid #ff0055; padding: 3px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 0.7rem; border-radius: 4px; cursor: pointer; z-index: 10; transition: 0.2s; margin-top: 5px; }

/* BULK MODE & FUSION CHIVATO */
.bulk-toggle-btn { background: transparent; color: #45a29e; border: 1px solid #45a29e; font-family: 'Share Tech Mono'; font-size: 0.7rem; padding: 4px 8px; border-radius: 4px; cursor: pointer; }
.bulk-toggle-btn.active { background: #ff0055; border-color: #ff0055; color: white; }
.bulk-mode-active { border-color: #ff0055; box-shadow: inset 0 0 20px rgba(255,0,85,0.1); }
.bulk-checkbox { position: absolute; top: 5px; left: 5px; width: 20px; height: 20px; border: 2px solid #ff0055; border-radius: 4px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.5); color: #ff0055; font-weight: bold; z-index: 20;}
.selected-for-sell { border-color: #ff0055 !important; background: rgba(255,0,85,0.2) !important; }

.fusion-ready-tag { position: absolute; top: 0; left: 0; background: #00ffcc; color: #000; font-size: 0.55rem; font-weight: 900; padding: 3px 6px; border-bottom-right-radius: 8px; font-family: 'Share Tech Mono', monospace; z-index: 10; letter-spacing: 0.5px; box-shadow: 0 0 8px #00ffcc; animation: neonPulse 1.5s infinite ease-in-out; }
@keyframes neonPulse { 0% { opacity: 0.7; box-shadow: 0 0 4px #00ffcc; } 50% { opacity: 1; box-shadow: 0 0 12px #00ffcc; } 100% { opacity: 0.7; box-shadow: 0 0 4px #00ffcc; } }

/* RPG SLOTS & INVENTORY */
.equipment-slots-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 15px 0; }
.slot-box { background: rgba(0,0,0,0.5); border: 1px dashed #45a29e; border-radius: 6px; padding: 8px; height: 65px; display: flex; flex-direction: column; justify-content: center; align-items: center; position: relative; cursor: pointer; transition: 0.2s;}
.slot-box:hover { border-color: #66fcf1; background: rgba(102, 252, 241, 0.1); }
.slot-label { position: absolute; top: 2px; left: 4px; font-size: 0.55rem; color: #45a29e; font-family: 'Share Tech Mono'; }
.slot-empty { text-align: center; opacity: 0.4; }
.slot-empty p { font-size: 0.6rem; margin: 0; }
.equipped-item-info { display: flex; flex-direction: column; align-items: center; text-align: center; }
.item-dot { width: 6px; height: 6px; border-radius: 50%; margin-bottom: 3px; }
.item-name-mini { font-size: 0.65rem; color: #fff; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100px; }
.item-mult-mini { font-size: 0.6rem; color: #66fcf1; font-family: 'Share Tech Mono'; }
.inventory-selector-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #0b0c10; z-index: 50; border-radius: 12px; display: flex; flex-direction: column; }
.inventory-content { padding: 15px; display: flex; flex-direction: column; height: 100%; }
.inv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.inv-header h3 { font-size: 1rem; color: #66fcf1; margin: 0; }
.inv-list { overflow-y: auto; flex-grow: 1; display: flex; flex-direction: column; gap: 8px; }
.inv-item { background: #12141a; padding: 10px; border-radius: 4px; border-left: 3px solid #333; display: flex; justify-content: space-between; cursor: pointer; }
.inv-item.none { border-left-color: #ff0055; color: #ff0055; font-size: 0.8rem; }

.market-filters { background: #12141a; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 10px; border: 1px solid #1f2833; }
.cyber-input { background: #0b0c10; border: 1px solid #45a29e; color: #fff; padding: 8px; border-radius: 4px; font-family: 'Share Tech Mono'; }
.filter-row { display: flex; gap: 8px; }
.cyber-select { flex: 1; background: #0b0c10; color: #fff; border: 1px solid #333; padding: 5px; border-radius: 4px; }
.bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 75px; display: flex; justify-content: space-around; align-items: center; background: #0b0c10; border-top: 1px solid #1f2833; z-index: 100; padding: 0 10px; padding-bottom: calc(10px + env(safe-area-inset-bottom)); box-sizing: border-box; }
.bottom-nav button { flex: 1; background: none; border: none; color: #45a29e; font-family: 'Share Tech Mono', monospace; font-size: 0.65rem; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: 0.3s; }
.bottom-nav button.active { color: #66fcf1; text-shadow: 0 0 8px rgba(102, 252, 241, 0.5); }
.nav-center-wrapper { position: relative; display: flex; justify-content: center; width: 70px; cursor: pointer; transform: translateY(-25px); z-index: 101; }
.nav-center-btn { width: 60px; height: 60px; background: #12141a; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: 3px solid #1f2833; color: #45a29e; box-shadow: 0 5px 15px rgba(0,0,0,0.6); transition: 0.3s; }
.nav-center-btn.active { border-color: #66fcf1; color: #66fcf1; box-shadow: 0 0 20px rgba(102, 252, 241, 0.4); }
.action-bar { position: fixed; bottom: 85px; left: 0; width: 100%; padding: 0 15px; z-index: 90; box-sizing: border-box; }
.cyber-btn-save { width: 100%; background: #66fcf1; color: #000; border: none; padding: 12px; font-weight: 700; clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px); cursor: pointer;}
.cyber-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.cyber-modal-content { background: #12141a; border: 1px solid #1f2833; border-top: 4px solid #fff; border-radius: 12px; padding: 25px; width: 100%; max-width: 400px; position: relative;}
.close-modal-btn { position: absolute; top: 15px; right: 15px; background: transparent !important; border: none !important; color: #c5c6c7; font-size: 1.5rem; font-family: 'Share Tech Mono', monospace; cursor: pointer; transition: 0.2s; padding: 0; z-index: 10; }
.modal-header { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-bottom: 10px; margin-top: 10px; }
.modal-header h2 { margin: 0; font-size: 1.8rem; text-transform: uppercase; word-break: break-word; text-align: center; line-height: 1.1; }
.rarity-badge { padding: 4px 15px; border: 1px solid; border-radius: 20px; font-family: 'Share Tech Mono', monospace; font-size: 0.8rem; background: rgba(0,0,0,0.6); display: inline-block; }
.modal-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; border-top: 1px solid #333; padding-top: 15px; }
.modal-action-btn { padding: 8px 12px; border-radius: 4px; border: none; font-weight: 700; cursor: pointer; }
.modal-action-btn.buy { background: #66fcf1; color: #000; }
.modal-action-btn.sell { background: none; border: 1px solid #ff0055; color: #ff0055; }
.pack-box { background: #12141a; border: 1px solid #1f2833; padding: 20px; border-radius: 8px; width: 80%; max-width: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.premium-pack { border-color: #f2a900; box-shadow: inset 0 0 15px rgba(242, 169, 0, 0.2); }
.premium-btn { background: #f2a900 !important; color: #0b0c10 !important; box-shadow: 0 5px 15px rgba(242, 169, 0, 0.3) !important; }
.gacha-btn { background: #e0129a; color: #fff; border:none; padding:8px; border-radius:4px; font-weight:bold; cursor:pointer; width: 100%; transition: 0.2s;}
.cube-visual { font-size: 3.5rem; filter: drop-shadow(0 0 20px rgba(224, 18, 154, 0.5)); transition: transform 0.1s; text-align: center; margin-bottom: 10px; line-height: 1; }
.shake-animation { animation: shake 0.5s infinite; filter: drop-shadow(0 0 40px rgba(224, 18, 154, 0.8)); }
@keyframes shake { 0% { transform: translate(1px, 1px) rotate(0deg) scale(1); } 20% { transform: translate(-3px, 0px) rotate(5deg) scale(1.1); } 40% { transform: translate(1px, -1px) rotate(5deg) scale(1.1); } 60% { transform: translate(-3px, 1px) rotate(0deg) scale(1.1); } 80% { transform: translate(-1px, -1px) rotate(5deg) scale(1.1); } 100% { transform: translate(1px, -2px) rotate(-5deg) scale(1); } }
.gacha-reveal { animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
@keyframes popIn { 0% { opacity: 0; transform: scale(0.5) translateY(100px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
.gacha-title { text-align: center; color: #fff; font-family: 'Share Tech Mono', monospace; margin: 0; }
.history-list { display: flex; flex-direction: column; gap: 15px; padding-bottom: 20px; }
.history-card { padding: 0; overflow: hidden; border-left: 4px solid #45a29e; }
.history-header { display: flex; justify-content: space-between; align-items: center; background: rgba(69, 162, 158, 0.1); padding: 10px 15px; border-bottom: 1px solid #1f2833; }
.history-date { font-family: 'Share Tech Mono', monospace; color: #c5c6c7; font-size: 0.9rem; }
.history-points { font-weight: 700; color: #f2a900; font-size: 1.2rem; }
.history-lineup { padding: 15px; }
.history-lineup ul { list-style: none; padding: 0; margin: 0; }
.history-player-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #1f2833; }
.history-player-name { color: #fff; font-size: 0.95rem; font-weight: 500; }
.history-badges { display: flex; gap: 5px; }
.h-badge { padding: 2px 6px; border-radius: 4px; font-family: 'Share Tech Mono', monospace; font-size: 0.7rem; font-weight: bold; }
.h-badge.cap { background: #f2a900; color: #000; }
.h-badge.prem { background: #66fcf1; color: #000; }
</style>
