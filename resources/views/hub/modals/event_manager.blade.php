<!-- MODAL EVENT MANAGER -->
<div id="eventManagerModal" class="modal-overlay fixed inset-0 z-[100] items-center justify-center p-4">
    <div class="bg-gray-900 border border-cyan-500/50 w-full max-w-lg rounded-2xl shadow-[0_0_30px_rgba(6,182,212,0.15)] flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b border-gray-800">
            <h3 id="eventManagerTitle" class="text-white font-bold text-lg font-mono uppercase tracking-widest"><i class="fas fa-calendar-plus text-cyan-400 mr-2"></i> NUEVO EVENTO</h3>
            <button onclick="cerrarModalEventManager()" class="text-gray-500 hover:text-white transition-colors p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Formulario Scrolleable -->
        <div class="p-4 overflow-y-auto no-scrollbar flex-1">
            <form id="eventManagerForm" onsubmit="enviarEventManager(event)">
                <input type="hidden" id="manage_event_id" name="event_id" value="">

                <div class="space-y-4">
                    <!-- Título -->
                    <div>
                        <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Nombre (Opcional)</label>
                        <input type="text" name="name" id="manage_name" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" placeholder="Ej. Gran Torneo de Madrid">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Modalidad -->
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Juego *</label>
                            <select name="mode" id="manage_mode" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required onchange="actualizarReglasEventManager()">
                                <option value="beybladex">BEYBLADE X</option>
                                <option value="beybladeburst">BURST</option>
                                <option value="beyblademetal">METAL FIGHT</option>
                            </select>
                        </div>
                        <!-- Categoría -->
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Categoría *</label>
                            <select name="imagen" id="manage_imagen" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required onchange="actualizarReglasEventManager()">
                                <option value="quedada">Quedada</option>
                                <option value="ranking">Ranking</option>
                                <option value="crew_battle">Torneo Equipos</option>
                                <option value="hasbro">Hasbro</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Deck -->
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Formato Deck *</label>
                            <select name="deck" id="manage_deck" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required>
                                <option value="3on3">3on3 (3 Beys)</option>
                                <option value="5g">5G (5 Beys)</option>
                                <option value="1on1">1 Beyblade</option>
                            </select>
                        </div>
                        <!-- Formato -->
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Cuadro *</label>
                            <select name="configuration" id="manage_configuration" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required>
                                <option value="Swiss">Suizo</option>
                                <option value="DoubleElimination">Doble Eliminación</option>
                                <option value="SingleElimination">Eliminación Simple</option>
                                <option value="RoundRobin">Round Robin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Región *</label>
                            <select name="region_id" id="manage_region_id" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required>
                                <!-- Se rellena con JS -->
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Ciudad *</label>
                            <input type="text" name="city" id="manage_city" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Ubicación Exacta *</label>
                        <input type="text" name="location" id="manage_location" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" placeholder="Ej. Parque del Retiro" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Fecha *</label>
                            <input type="date" name="event_date" id="manage_date" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required>
                        </div>
                        <div>
                            <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Hora *</label>
                            <input type="time" name="event_time" id="manage_time" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" required>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase block mb-1">Notas Adicionales</label>
                        <textarea name="note" id="manage_note" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none" placeholder="Reglas extra, punto de encuentro..."></textarea>
                    </div>

                    <!-- Aforo -->
                    <div class="grid grid-cols-2 gap-3 p-3 bg-gray-950 rounded-lg border border-gray-800">
                        <div>
                            <label class="text-[10px] text-gray-500 font-bold tracking-widest uppercase block mb-1"><i class="fas fa-dungeon mr-1"></i> ESTADIOS *</label>
                            <input type="number" name="stadiums" id="manage_stadiums" value="1" min="1" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none text-center" required>
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 font-bold tracking-widest uppercase block mb-1"><i class="fas fa-users mr-1"></i> MAX JUGADORES</label>
                            <input type="number" name="max_players" id="manage_max_players" class="w-full bg-gray-800 border border-gray-700 text-white text-sm p-3 rounded-lg focus:border-cyan-400 outline-none text-center" placeholder="Auto">
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer Botones -->
        <div class="p-4 border-t border-gray-800 flex gap-2" id="eventManagerFooter">
            <!-- Se rellenará dinámicamente con Guardar / Actualizar / Eliminar -->
        </div>
    </div>
</div>