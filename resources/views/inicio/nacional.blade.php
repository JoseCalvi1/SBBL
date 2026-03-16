@extends('layouts.app')

@section('title', 'BeyCon España 2026 - Nacional SBBL')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Inter:wght@300;400;600&family=Roboto+Mono:wght@500&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-deep-blue: #0a192f;
        --neon-cyan: #00f2fe;
        --neon-magenta: #ff0055;
        --neon-gold: #ffd500;
        --glass-bg: rgba(255, 255, 255, 0.05);
        --text-bright: #ffffff;
        --text-soft: #ccd6f6;
        --text-accent: #00f2fe;
    }

    body {
        background-color: var(--bg-deep-blue);
        /* EFECTO GRID CYBERPUNK EN EL FONDO */
        background-image:
            linear-gradient(rgba(0, 242, 254, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 242, 254, 0.03) 1px, transparent 1px);
        background-size: 40px 40px;
        background-attachment: fixed;
        color: var(--text-soft);
        font-family: 'Inter', sans-serif;
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* ANIMACIONES DE SCROLL (REVEAL) */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    .hero {
        position: relative;
        padding: 150px 20px;
        text-align: center;
        background: linear-gradient(to bottom, rgba(10, 25, 47, 0.4), var(--bg-deep-blue)),
                    url('/../images/fondo_banner_nacional.webp');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        border-bottom: 1px solid rgba(0, 242, 254, 0.3);
    }

    .hero-title {
        font-family: 'Orbitron', sans-serif;
        font-size: clamp(3rem, 8vw, 6rem);
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 2px;
        background: linear-gradient(to right, #ffffff, var(--neon-cyan), #ffffff, var(--neon-magenta));
        background-size: 300% auto;
        color: var(--text-bright);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: textShine 4s linear infinite;
        filter: drop-shadow(0 0 10px rgba(0, 242, 254, 0.4));
    }

    @keyframes textShine {
        to { background-position: 300% center; }
    }

    #countdown {
        font-family: 'Roboto Mono', monospace;
        font-size: clamp(1.5rem, 5vw, 3.5rem);
        color: var(--neon-cyan);
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        display: inline-block;
        padding: 15px 40px;
        border-radius: 12px;
        border: 1px solid rgba(0, 242, 254, 0.5);
        margin: 30px 0;
        box-shadow: 0 0 20px rgba(0, 242, 254, 0.2);
    }

    .section-container {
        max-width: 1050px;
        margin: 80px auto;
        padding: 0 20px;
    }

    .cyber-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 40px;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        transition: transform 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
    }

    .cyber-card::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 50%; height: 100%;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
        transform: skewX(-25deg);
        transition: 0.6s;
    }
    .cyber-card:hover::before { left: 125%; }
    .cyber-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .sec-title {
        font-family: 'Orbitron', sans-serif;
        color: var(--text-bright);
        font-size: 2rem;
        margin-bottom: 35px;
        border-left: 5px solid var(--neon-cyan);
        padding-left: 15px;
        text-shadow: 0 0 10px rgba(0, 242, 254, 0.3);
    }

    .info-grid-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }

    @media (max-width: 768px) { .info-grid-row { grid-template-columns: 1fr; } }

    .stat-module {
        background: rgba(0, 242, 254, 0.03);
        border: 1px solid rgba(0, 242, 254, 0.2);
        padding: 30px 25px;
        border-radius: 12px;
        height: 100%;
        transition: all 0.3s ease;
    }
    .stat-module:hover {
        background: rgba(0, 242, 254, 0.08);
        border-color: rgba(0, 242, 254, 0.5);
        transform: scale(1.02);
    }

    .stat-header {
        font-family: 'Roboto Mono', monospace;
        color: var(--neon-cyan);
        font-size: 0.9rem;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: block;
        letter-spacing: 1px;
    }

    .stat-value {
        font-family: 'Orbitron', sans-serif;
        font-size: 2.5rem;
        color: #fff;
        display: block;
        text-shadow: 0 0 15px rgba(0, 242, 254, 0.4);
    }

    .btn-cyber {
        font-family: 'Orbitron', sans-serif;
        background: var(--neon-cyan);
        color: #0a192f;
        padding: 20px 50px;
        border-radius: 50px;
        font-weight: 900;
        font-size: 1.1rem;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
        box-shadow: 0 0 20px rgba(0, 242, 254, 0.4);
        margin-top: 20px;
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
    }

    .btn-cyber:hover {
        transform: scale(1.05) translateY(-2px);
        box-shadow: 0 0 40px rgba(0, 242, 254, 0.8);
        color: #0a192f;
    }

    .btn-cyber-magenta {
        background: var(--neon-magenta);
        color: white;
        box-shadow: 0 0 20px rgba(255, 0, 85, 0.4);
    }
    .btn-cyber-magenta:hover {
        color: white;
        box-shadow: 0 0 40px rgba(255, 0, 85, 0.8);
    }

    .protocol-item {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 15px;
        transition: all 0.3s;
    }
    .protocol-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--neon-cyan);
    }

    .protocol-item strong {
        color: var(--neon-cyan);
        display: block;
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .stat-desc { color: var(--text-soft); }

    .price-tag {
        font-family: 'Orbitron', sans-serif;
        font-size: 1.8rem;
        color: var(--neon-cyan);
        display: block;
        margin-top: 15px;
        text-shadow: 0 0 10px rgba(0, 242, 254, 0.3);
    }

    .side-event-card {
        background: rgba(255, 0, 85, 0.03);
        border: 1px solid rgba(255, 0, 85, 0.2);
        padding: 30px;
        border-radius: 12px;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s;
    }
    .side-event-card:hover {
        border-color: var(--neon-magenta);
        box-shadow: 0 0 20px rgba(255, 0, 85, 0.15);
        transform: translateY(-5px);
    }

    .side-event-title {
        font-family: 'Orbitron', sans-serif;
        color: var(--neon-magenta);
        font-size: 1.4rem;
        margin-bottom: 20px;
        text-align: center;
        letter-spacing: 1px;
    }

    .side-event-info { flex-grow: 1; }
    .side-event-info strong { color: var(--neon-cyan); }

    .prize-banner {
        background: rgba(255, 213, 0, 0.05);
        border: 1px solid var(--neon-gold);
        color: var(--neon-gold);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        margin-top: 40px;
        font-weight: 600;
        font-family: 'Roboto Mono', monospace;
        letter-spacing: 0.5px;
    }

    /* ESTILOS DEL MINIJUEGO */
    #minigame-container {
        border: 2px solid var(--neon-cyan);
        border-radius: 12px;
        padding: 30px;
        background: rgba(0, 0, 0, 0.5);
        text-align: center;
        margin-top: 30px;
        position: relative;
    }

    #game-level {
        position: absolute;
        top: 20px;
        right: 20px;
        font-family: 'Orbitron', sans-serif;
        font-size: 1.3rem;
        color: var(--neon-cyan);
        text-shadow: 0 0 10px rgba(0, 242, 254, 0.5);
        font-weight: bold;
        transition: color 0.3s, text-shadow 0.3s;
    }

    /* NUEVO: ESTILO PARA EL RÉCORD */
    #game-record {
        position: absolute;
        top: 45px;
        right: 20px;
        font-family: 'Orbitron', sans-serif;
        font-size: 0.9rem;
        color: var(--neon-magenta);
        text-shadow: 0 0 5px rgba(255, 0, 85, 0.5);
        font-weight: bold;
    }

    #game-bar-bg {
        width: 100%;
        height: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        margin: 30px 0;
        border: 1px solid rgba(255,255,255,0.2);
    }

    #game-target {
        position: absolute;
        height: 100%;
        width: 12%;
        background: rgba(0, 255, 136, 0.4);
        left: 75%;
        border-left: 2px solid #00ff88;
        border-right: 2px solid #00ff88;
        box-shadow: 0 0 15px #00ff88;
        transition: left 0.5s ease;
    }

    #game-marker {
        position: absolute;
        height: 100%;
        width: 6px;
        background: var(--neon-magenta);
        left: 0%;
        box-shadow: 0 0 10px var(--neon-magenta);
        border-radius: 5px;
    }

    #game-msg {
        font-family: 'Orbitron', sans-serif;
        font-size: 1.5rem;
        min-height: 2.5rem;
        color: var(--text-bright);
        margin-bottom: 15px;
        margin-top: 10px;
    }

    @media (max-width: 600px) {
        #game-level {
            position: relative;
            top: 0;
            right: 0;
            display: block;
        }
        #game-record {
            position: relative;
            top: 0;
            right: 0;
            display: block;
            margin-bottom: 15px;
        }
    }
</style>
@endsection

@section('content')
<div class="hero reveal">
    <h1 class="hero-title">BeyCon España</h1>
    <div id="countdown">00D : 00H : 00M : 00S</div>

    <div style="margin-top: 10px;">
        <p style="font-size: 1.3rem; color: var(--text-bright); font-weight: 600; letter-spacing: 1px;">
            Sevilla | 18 de Julio | 09:00 AM
        </p>
        <p style="color: var(--neon-cyan); font-family: 'Roboto Mono'; opacity: 0.9;">HOTEL VÉRTICE ALJARAFE - SEDE OFICIAL NACIONAL</p>
    </div>

    <a href="https://forms.gle/ZWugRhfrr9vLAavQ6" target="_blank" class="btn-cyber mt-4">
       REGISTRO DE BLADERS
    </a>
</div>

<div class="section-container reveal">
    <div class="cyber-card">
        <h2 class="sec-title">Detalles de la Competición</h2>
        <p style="font-size: 1.15rem; color: var(--text-soft); line-height: 1.8;">
            ¡La cita definitiva de Beyblade X en España! La BeyCon acogerá los torneos más importantes de la temporada SBBL'26 en un espacio de <strong style="color:white;">800m²</strong> diseñado para la máxima experiencia Blader.
        </p>

        <div class="info-grid-row mt-5">
            <div class="stat-module">
                <span class="stat-header">Nacional Individual</span>
                <span class="stat-value">96</span>
                <span class="stat-desc">Bladers en formato Suizo G12 + Top 32.</span>
            </div>
            <div class="stat-module">
                <span class="stat-header">Nacional Equipos</span>
                <span class="stat-value">TOP 16</span>
                <span class="stat-desc">Los mejores equipos del ranking nacional.</span>
            </div>
            <div class="stat-module">
                <span class="stat-header">Side Events</span>
                <span class="stat-value">3</span>
                <span class="stat-desc">Torneos paralelos con premios exclusivos.</span>
            </div>
        </div>
    </div>
</div>

<div class="section-container reveal">
    <h2 class="sec-title">Pases de Acceso (No Participantes)</h2>
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="cyber-card text-center h-100" style="border-top: 3px solid rgba(255,255,255,0.3);">
                <h4 style="color: #fff; font-family: 'Orbitron';">PASE ASISTENTE</h4>
                <p class="stat-desc mt-3">Acceso al recinto para no participantes de los Nacionales. Incluye acceso a tiendas y Side Events.</p>
                <span class="price-tag">5€</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="cyber-card text-center h-100" style="border-top: 3px solid var(--neon-gold);">
                <h4 style="color: var(--neon-gold); font-family: 'Orbitron';">PACK VIP SBBL</h4>
                <p class="stat-desc mt-3">Pase de acceso completo + Pack de Merchandising oficial de la SBBL.</p>
                <span class="price-tag" style="color: var(--neon-gold);">15€</span>
            </div>
        </div>
    </div>
</div>

<div class="section-container reveal">
    <div class="cyber-card">
        <h2 class="sec-title" style="border-left-color: var(--neon-magenta);">Side Events</h2>
        <p style="font-size: 1.1rem; color: var(--text-soft); margin-bottom: 40px;">
            Además de los Nacionales, contaremos con 3 torneos paralelos para todos los asistentes. ¡Inscríbete el mismo día del evento!
        </p>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="side-event-card">
                    <h4 class="side-event-title">EVENTO 1<br>BP POWER</h4>
                    <div class="side-event-info">
                        <p class="stat-desc"><strong>Reglas:</strong> Lanza con todas tus fuerzas con uno de los battlepass del evento para conseguir la mejor puntuación.</p>
                        <p class="stat-desc mt-3"><strong>Premios:</strong> La persona con mayor puntuación se llevará una palmadita en la espalda.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="side-event-card">
                    <h4 class="side-event-title">EVENTO 2<br>REY DE PISTA</h4>
                    <div class="side-event-info">
                        <p class="stat-desc"><strong>Reglas:</strong> En el estadio de 3, se el último en pie y encadena la mayor racha de victorias.</p>
                        <p class="stat-desc mt-3"><strong>Premios:</strong> La persona con mayor racha de victorias podrá llevarse un besito de kaw (si Ana no le deja será de berni).</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="side-event-card">
                    <h4 class="side-event-title">EVENTO 3<br>MEDAL MASTERS</h4>
                    <div class="side-event-info">
                        <p class="stat-desc"><strong>Reglas:</strong> Recibe medallas al inscribirte y reta a tus amigos por ellas.</p>
                        <p class="stat-desc mt-3"><strong>Premios:</strong> La persona con más medallas al cierre de los eventos ganará un estupendo enhorabuena.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="prize-banner">
            🏆 ATENCIÓN: Todos los premios de los Side Events se entregarán en el escenario principal al finalizar el evento. 🏆
        </div>
    </div>
</div>

<div class="section-container reveal">
    <h2 class="sec-title">Sistema de Clasificación</h2>
    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="cyber-card h-100" style="border-color: rgba(0, 242, 254, 0.2);">
                <h4 style="color: var(--neon-cyan); font-family: 'Orbitron';">Nivel 1: Compromiso</h4>
                <p class="stat-desc mt-3">Prioridad según inscripción en Grandes Copas de la temporada:</p>
                <ul style="color: var(--text-bright); line-height: 2;">
                    <li><strong style="color: var(--neon-cyan)">Grupo A:</strong> 4 Copas (Prioridad absoluta)</li>
                    <li><strong style="color: var(--neon-cyan)">Grupo B:</strong> 3 Copas</li>
                    <li><strong style="color: var(--neon-cyan)">Grupo C:</strong> 2 Copas</li>
                    <li><strong style="color: var(--neon-cyan)">Grupo D:</strong> 1 Copa</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="cyber-card h-100" style="border-color: rgba(255, 0, 85, 0.2);">
                <h4 style="color: var(--neon-magenta); font-family: 'Orbitron';">Nivel 2: Desempates</h4>
                <p class="stat-desc mt-3">Puntos por rendimiento y fidelidad:</p>
                <ul style="color: var(--text-bright); line-height: 2;">
                    <li><strong style="color: var(--neon-magenta)">Factor Campeón:</strong> Medallas conseguidas.</li>
                    <li><strong style="color: var(--neon-magenta)">Suscripción:</strong> Nivel de sub activa.</li>
                    <li><strong style="color: var(--neon-magenta)">Ranking:</strong> Posición actual en la liga.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="section-container reveal">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="cyber-card h-100" style="border-top: 3px solid var(--neon-cyan); padding: 40px;">
                <h5 style="color: #fff; text-align: center; margin-bottom: 30px; font-family: 'Orbitron'; font-size: 1.4rem;">TIENDAS OFICIALES</h5>

                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded" style="background: rgba(0, 242, 254, 0.05); border-left: 3px solid var(--neon-cyan); transition: 0.3s;" onmouseover="this.style.background='rgba(0, 242, 254, 0.1)'" onmouseout="this.style.background='rgba(0, 242, 254, 0.05)'">
                        <strong style="color: var(--text-bright); font-size: 1.1rem;">🖨️ XTREME Cases</strong>
                        <p class="mb-0 mt-1" style="color: var(--text-soft); font-size: 0.9rem;">Venta de Deckcases, Stands y material 3D.</p>
                    </div>

                    <div class="p-3 rounded" style="background: rgba(0, 242, 254, 0.05); border-left: 3px solid var(--neon-cyan); transition: 0.3s;" onmouseover="this.style.background='rgba(0, 242, 254, 0.1)'" onmouseout="this.style.background='rgba(0, 242, 254, 0.05)'">
                        <strong style="color: var(--text-bright); font-size: 1.1rem;">👕 SBBL Merch</strong>
                        <p class="mb-0 mt-1" style="color: var(--text-soft); font-size: 0.9rem;">Merchandising oficial de la liga.</p>
                    </div>

                    <div class="p-3 rounded" style="background: rgba(0, 242, 254, 0.05); border-left: 3px solid var(--neon-cyan); transition: 0.3s;" onmouseover="this.style.background='rgba(0, 242, 254, 0.1)'" onmouseout="this.style.background='rgba(0, 242, 254, 0.05)'">
                        <strong style="color: var(--text-bright); font-size: 1.1rem;">🎌 B4S</strong>
                        <p class="mb-0 mt-1" style="color: var(--text-soft); font-size: 0.9rem;">Material exclusivo deluxe.</p>
                    </div>

                    <div class="p-3 rounded" style="background: rgba(0, 242, 254, 0.05); border-left: 3px solid var(--neon-cyan); transition: 0.3s;" onmouseover="this.style.background='rgba(0, 242, 254, 0.1)'" onmouseout="this.style.background='rgba(0, 242, 254, 0.05)'">
                        <strong style="color: var(--text-bright); font-size: 1.1rem;">🃏 Gold Saucer Store</strong>
                        <p class="mb-0 mt-1" style="color: var(--text-soft); font-size: 0.9rem;">Beyblades TT y Hasbro.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="cyber-card h-100" style="border-top: 3px solid var(--neon-magenta); padding: 40px;">
                <h5 style="color: #fff; text-align: center; margin-bottom: 30px; font-family: 'Orbitron'; font-size: 1.4rem;">COLABORADORES</h5>

                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <div class="text-center p-4 rounded" style="background: rgba(255, 0, 85, 0.05); border: 1px solid rgba(255, 0, 85, 0.2); width: 45%; transition: 0.3s;" onmouseover="this.style.borderColor='var(--neon-magenta)'" onmouseout="this.style.borderColor='rgba(255, 0, 85, 0.2)'">
                        <span style="font-size: 2.5rem; display: block; margin-bottom: 10px;">📸</span>
                        <strong style="color: var(--neon-magenta); font-size: 1rem;">Sugoi Digital Capture</strong>
                    </div>

                    <div class="text-center p-4 rounded" style="background: rgba(255, 0, 85, 0.05); border: 1px solid rgba(255, 0, 85, 0.2); width: 45%; transition: 0.3s;" onmouseover="this.style.borderColor='var(--neon-magenta)'" onmouseout="this.style.borderColor='rgba(255, 0, 85, 0.2)'">
                        <span style="font-size: 2.5rem; display: block; margin-bottom: 10px;">🌀</span>
                        <strong style="color: var(--neon-magenta); font-size: 1rem;">Beyblade Nexus</strong>
                    </div>

                    <div class="text-center p-4 rounded" style="background: rgba(255, 0, 85, 0.05); border: 1px solid rgba(255, 0, 85, 0.2); width: 45%; transition: 0.3s;" onmouseover="this.style.borderColor='var(--neon-magenta)'" onmouseout="this.style.borderColor='rgba(255, 0, 85, 0.2)'">
                        <span style="font-size: 2.5rem; display: block; margin-bottom: 10px;">🎲</span>
                        <strong style="color: var(--neon-magenta); font-size: 1rem;">Gold Saucer Store</strong>
                    </div>

                    <div class="text-center p-4 rounded" style="background: rgba(255, 0, 85, 0.05); border: 1px solid rgba(255, 0, 85, 0.2); width: 45%; transition: 0.3s;" onmouseover="this.style.borderColor='var(--neon-magenta)'" onmouseout="this.style.borderColor='rgba(255, 0, 85, 0.2)'">
                        <span style="font-size: 2.5rem; display: block; margin-bottom: 10px;">🍔</span>
                        <strong style="color: var(--neon-magenta); font-size: 1rem;">Hamburguesería Mr Dope</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-container reveal">
    <div class="cyber-card text-center" style="border: 2px solid var(--neon-magenta); background: rgba(255,0,85,0.02);">
        <h2 class="sec-title" style="border: none; padding-left: 0; margin-bottom: 20px;">¿Quieres Colaborar con la SBBL?</h2>
        <p style="font-size: 1.15rem; color: var(--text-bright); max-width: 700px; margin: 0 auto;">
            Estamos buscando marcas, tiendas y patrocinadores que quieran formar parte del mayor evento de Beyblade en España.
        </p>
        <p class="stat-desc mb-4 mt-3">
            Si tienes una propuesta comercial o quieres montar un stand en nuestra zona de tiendas, ponte en contacto con la organización.
        </p>
        <a href="mailto:info@sbbl.es" class="btn-cyber btn-cyber-magenta mt-2">
            ✉ CONTACTAR CON EL EQUIPO
        </a>
    </div>
</div>

<div class="section-container reveal">
    <h2 class="sec-title">Preguntas Frecuentes</h2>

    <div class="protocol-item">
        <strong>¿Cuál es el precio de la entrada si no compito en el Nacional?</strong>
        <p class="stat-desc mb-0">La entrada general de asistente tiene un coste de 5€. Si quieres llevarte el Merch oficial de la liga, puedes adquirir el Pack VIP por 15€.</p>
    </div>

    <div class="protocol-item">
        <strong>¿Qué requisitos tiene el torneo por equipos?</strong>
        <p class="stat-desc mb-0">Estar entre los 16 mejores equipos del ranking y que al menos 3 miembros del equipo estén presentes en la BeyCon.</p>
    </div>

    <div class="protocol-item">
        <strong>¿Hay límite de edad para los Bladers?</strong>
        <p class="stat-desc mb-0">No, es un evento para toda la comunidad. Los menores de 15 años deberán ir acompañados por un responsable autorizado.</p>
    </div>
</div>

<div class="section-container reveal">
    <h2 class="sec-title">Entrenamiento: Reflejos Blader</h2>
    <div class="cyber-card">
        <p class="stat-desc text-center">¡Demuestra que estás listo! Detén el indicador de potencia exactamente en la zona verde (Zona Xtreme). Encadena aciertos para subir de nivel.</p>

        <div id="minigame-container">
            <div id="game-level">NIVEL 1</div>
            <div id="game-record">RÉCORD: 1</div>
            <div id="game-msg">ESPERANDO INICIO...</div>

            <div id="game-bar-bg">
                <div id="game-target"></div>
                <div id="game-marker"></div>
            </div>

            <button id="btn-game-action" class="btn-cyber" style="padding: 15px 40px; font-size: 1.2rem;" onclick="toggleGame()">INICIAR ENTRENAMIENTO</button>
        </div>
    </div>
</div>

<div class="section-container text-center reveal" style="padding-bottom: 100px;">
    <h2 class="sec-title" style="border:none; justify-content: center; display: flex;">Lista de Clasificados</h2>
    <div class="cyber-card" style="border: 2px solid var(--neon-cyan); background: rgba(0, 242, 254, 0.05);">
        <div style="font-size: 2rem; margin-bottom: 15px; animation: pulse 2s infinite;">⏱️</div>
        <p style="font-family: 'Roboto Mono'; font-size: 1.2rem; color: var(--neon-cyan); letter-spacing: 2px;">[ SINCRONIZANDO RANKING ]</p>
        <p style="color: var(--text-soft); max-width: 600px; margin: 0 auto;">La lista oficial de los 96 Bladers clasificados se publicará tras el cierre del formulario de registro y la validación de medallas.</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Sistema de cuenta regresiva
    function updateCountdown() {
        const eventDate = new Date('2026-07-18T09:00:00').getTime();
        const now = new Date().getTime();
        const diff = eventDate - now;

        const countdownEl = document.getElementById('countdown');

        if (diff <= 0) {
            countdownEl.innerText = "¡3, 2, 1... GO SHOOT!";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
        const seconds = Math.floor((diff % (1000 * 60)) / 1000).toString().padStart(2, '0');

        countdownEl.innerText = `${days}D : ${hours}H : ${minutes}M : ${seconds}S`;
    }
    setInterval(updateCountdown, 1000);
    updateCountdown();

    // INTERSECTION OBSERVER PARA ANIMACIONES DE SCROLL
    document.addEventListener("DOMContentLoaded", function() {
        const reveals = document.querySelectorAll(".reveal");
        const revealOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };

        const revealOnScroll = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("active");
                    observer.unobserve(entry.target);
                }
            });
        }, revealOptions);

        reveals.forEach(reveal => revealOnScroll.observe(reveal));
    });

    // LÓGICA DEL MINIJUEGO CON NIVELES Y RÉCORD
    let isPlaying = false;
    let markerPos = 0;
    let direction = 1;
    let baseSpeed = 1.5; // Velocidad inicial base
    let currentSpeed = baseSpeed;
    let currentLevel = 1;
    let maxLevel = 1; // Récord de nivel alcanzado
    let gameInterval;

    let targetStart = 75; // Posición de la zona verde
    let targetWidth = 12; // Tamaño de la zona verde (12%)

    function toggleGame() {
        const btn = document.getElementById('btn-game-action');
        const msg = document.getElementById('game-msg');
        const target = document.getElementById('game-target');

        if(!isPlaying) {
            // INICIAR JUEGO
            isPlaying = true;
            btn.innerText = "¡LANZA AHORA!";
            btn.classList.add('btn-cyber-magenta');
            msg.innerText = "Sincronizando...";
            msg.style.color = "var(--neon-cyan)";

            // Randomizar un poco la posición de la zona verde para cada intento
            targetStart = Math.floor(Math.random() * 60) + 20; // Entre 20% y 80%
            target.style.left = targetStart + '%';

            // Calcular velocidad según el nivel actual (cada nivel es más rápido)
            currentSpeed = baseSpeed + (currentLevel * 0.4);

            gameInterval = setInterval(moveMarker, 20);
        } else {
            // DETENER JUEGO Y COMPROBAR
            isPlaying = false;
            clearInterval(gameInterval);
            btn.classList.remove('btn-cyber-magenta');

            checkHit(msg, btn);
        }
    }

    function moveMarker() {
        const marker = document.getElementById('game-marker');
        markerPos += currentSpeed * direction;

        if(markerPos >= 98) {
            direction = -1;
            markerPos = 98;
        } else if (markerPos <= 0) {
            direction = 1;
            markerPos = 0;
        }
        marker.style.left = markerPos + '%';
    }

    function checkHit(msgElement, btnElement) {
        const levelDisplay = document.getElementById('game-level');
        const recordDisplay = document.getElementById('game-record');
        const targetEnd = targetStart + targetWidth;

        // Si acierta en la zona verde
        if(markerPos >= targetStart && markerPos <= targetEnd) {
            msgElement.innerText = "¡PERFECT LAUNCH!";
            msgElement.style.color = "#00ff88";

            document.getElementById('game-target').style.boxShadow = "0 0 30px #00ff88";
            setTimeout(() => { document.getElementById('game-target').style.boxShadow = "0 0 15px #00ff88"; }, 500);

            // Subir de nivel
            currentLevel++;

            // Actualizar el Récord si superaste tu máximo nivel
            if (currentLevel > maxLevel) {
                maxLevel = currentLevel;
                recordDisplay.innerText = "RÉCORD: " + maxLevel;
            }

            levelDisplay.innerText = "NIVEL " + currentLevel;
            levelDisplay.style.color = "var(--neon-gold)";
            levelDisplay.style.textShadow = "0 0 15px var(--neon-gold)";

            btnElement.innerText = "SIGUIENTE NIVEL >>";

        } else {
            // Si falla
            msgElement.innerText = "BURST FINISH. Has perdido la racha.";
            msgElement.style.color = "var(--neon-magenta)";

            // Resetear nivel actual, pero EL RÉCORD SE MANTIENE
            currentLevel = 1;
            levelDisplay.innerText = "NIVEL 1";
            levelDisplay.style.color = "var(--neon-cyan)";
            levelDisplay.style.textShadow = "0 0 10px rgba(0, 242, 254, 0.5)";

            btnElement.innerText = "REINTENTAR";
        }
    }
</script>
<style>
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endsection
