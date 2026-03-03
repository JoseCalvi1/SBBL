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
        --glass-bg: rgba(255, 255, 255, 0.07);
        --text-bright: #ffffff;
        --text-soft: #ccd6f6;
        --text-accent: #00f2fe;
    }

    body {
        background-color: var(--bg-deep-blue);
        color: var(--text-soft);
        font-family: 'Inter', sans-serif;
        line-height: 1.6;
    }

    .hero {
        position: relative;
        padding: 120px 20px;
        text-align: center;
        background: linear-gradient(to bottom, rgba(10, 25, 47, 0.4), var(--bg-deep-blue)),
                    url('/../images/fondo_banner_nacional.webp');
        background-size: cover;
        background-position: center;
        border-bottom: 1px solid rgba(0, 242, 254, 0.3);
    }

    .hero-title {
        font-family: 'Orbitron', sans-serif;
        font-size: clamp(2.5rem, 8vw, 5rem);
        font-weight: 900;
        color: var(--text-bright);
        text-transform: uppercase;
        letter-spacing: 2px;
        filter: drop-shadow(0 0 15px rgba(0, 242, 254, 0.6));
    }

    #countdown {
        font-family: 'Roboto Mono', monospace;
        font-size: clamp(1.5rem, 5vw, 3.5rem);
        color: var(--neon-cyan);
        background: rgba(0, 0, 0, 0.4);
        display: inline-block;
        padding: 10px 30px;
        border-radius: 8px;
        border: 1px solid rgba(0, 242, 254, 0.5);
        margin: 25px 0;
    }

    .section-container {
        max-width: 1000px;
        margin: 60px auto;
        padding: 0 20px;
    }

    .cyber-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 40px;
        border-radius: 15px;
    }

    .sec-title {
        font-family: 'Orbitron', sans-serif;
        color: var(--text-bright);
        font-size: 1.8rem;
        margin-bottom: 30px;
        border-left: 4px solid var(--neon-cyan);
        padding-left: 15px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .stat-module {
        background: rgba(0, 242, 254, 0.05);
        border: 1px solid rgba(0, 242, 254, 0.2);
        padding: 25px;
        border-radius: 12px;
    }

    .stat-header {
        font-family: 'Roboto Mono', monospace;
        color: var(--neon-cyan);
        font-size: 0.85rem;
        text-transform: uppercase;
        margin-bottom: 10px;
        display: block;
    }

    .stat-value {
        font-family: 'Orbitron', sans-serif;
        font-size: 2.2rem;
        color: #fff;
        display: block;
    }

    .btn-cyber {
        font-family: 'Orbitron', sans-serif;
        background: var(--neon-cyan);
        color: #0a192f;
        padding: 18px 45px;
        border-radius: 50px;
        font-weight: 900;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
        box-shadow: 0 0 20px rgba(0, 242, 254, 0.4);
        margin-top: 20px;
    }

    .btn-cyber:hover {
        transform: scale(1.05);
        box-shadow: 0 0 40px rgba(0, 242, 254, 0.7);
    }

    /* Nueva clase para botones secundarios (Contacto) */
    .btn-cyber-magenta {
        background: var(--neon-magenta);
        color: white;
        box-shadow: 0 0 20px rgba(255, 0, 85, 0.4);
    }
    .btn-cyber-magenta:hover {
        color: white;
        box-shadow: 0 0 40px rgba(255, 0, 85, 0.7);
    }

    .protocol-item {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
    }

    .protocol-item strong {
        color: var(--neon-cyan);
        display: block;
        margin-bottom: 8px;
    }

    .placeholder-module {
        text-align: center;
        padding: 30px;
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 12px;
    }
    .stat-desc {
        color: white;
    }

    .price-tag {
        font-family: 'Orbitron', sans-serif;
        font-size: 1.5rem;
        color: var(--neon-cyan);
        display: block;
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="hero">
    <h1 class="hero-title">BeyCon España</h1>
    <div id="countdown">00D : 00H : 00M : 00S</div>

    <div style="margin-top: 10px;">
        <p style="font-size: 1.2rem; color: var(--text-bright); font-weight: 600;">
            Sevilla | 18 de Julio | 09:00 AM
        </p>
        <p style="color: var(--neon-cyan); font-family: 'Roboto Mono';">HOTEL VÉRTICE ALJARAFE - SEDE OFICIAL NACIONAL</p>
    </div>

    <a href="https://forms.gle/ZWugRhfrr9vLAavQ6" target="_blank" class="btn-cyber">
       REGISTRO DE BLADERS
    </a>
</div>

<div class="section-container">
    <div class="cyber-card">
        <h2 class="sec-title">Detalles de la Competición</h2>
        <p style="font-size: 1.1rem; color: var(--text-bright);">
            ¡La cita definitiva de Beyblade X en España! La BeyCon acogerá los torneos más importantes de la temporada SBBL'26 en un espacio de **800m²** diseñado para la máxima experiencia Blader.
        </p>

        <div class="info-grid mt-4">
            <div class="stat-module">
                <span class="stat-header">Nacional Individual</span>
                <span class="stat-value">96</span>
                <span class="stat-desc">Bladers en formato Suizo G16 + Top 24.</span>
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

<div class="section-container">
    <h2 class="sec-title">Pases de Acceso</h2>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="cyber-card text-center h-100" style="border-color: rgba(255,255,255,0.2);">
                <h4 style="color: #fff;">PASE ASISTENTE</h4>
                <p class="stat-desc">Acceso al recinto para no participantes de los Nacionales. Incluye acceso a tiendas y Side Events.</p>
                <span class="price-tag">5€</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="cyber-card text-center h-100" style="border-color: var(--neon-gold);">
                <h4 style="color: var(--neon-gold);">PACK VIP SBBL</h4>
                <p class="stat-desc">Pase de acceso completo + Pack de Merchandising oficial de la SBBL.</p>
                <span class="price-tag" style="color: var(--neon-gold);">15€</span>
            </div>
        </div>
    </div>
</div>

<div class="section-container">
    <h2 class="sec-title">Sistema de Clasificación SBBL Individual</h2>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="cyber-card h-100">
                <h4 style="color: var(--neon-cyan);">Nivel 1: Compromiso</h4>
                <p class="stat-desc">Prioridad según inscripción en Grandes Copas de la temporada:</p>
                <ul style="color: var(--text-bright);">
                    <li>Grupo A: 4 Copas (Prioridad absoluta)</li>
                    <li>Grupo B: 3 Copas</li>
                    <li>Grupo C: 2 Copas</li>
                    <li>Grupo D: 1 Copa</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="cyber-card h-100">
                <h4 style="color: var(--neon-magenta);">Nivel 2: Desempates</h4>
                <p class="stat-desc">Puntos por rendimiento y fidelidad:</p>
                <ul style="color: var(--text-bright);">
                    <li><strong>Factor Campeón:</strong> Medallas conseguidas.</li>
                    <li><strong>Suscripción:</strong> Nivel de sub activa.</li>
                    <li><strong>Ranking:</strong> Posición actual en la liga.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="section-container">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="placeholder-module">
                <h5 style="color: #fff;">TIENDAS</h5>
                <p style="color: var(--neon-cyan);">¿¿ ??</p>
                <p class="stat-desc">Próximamente</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="placeholder-module">
                <h5 style="color: #fff;">SIDE EVENTS</h5>
                <p style="color: var(--neon-magenta);">¿¿ ??</p>
                <p class="stat-desc">Próximamente</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="placeholder-module">
                <h5 style="color: #fff;">COLABORADORES</h5>
                <p style="color: var(--text-soft);">¿¿ ??</p>
                <p class="stat-desc">Próximamente</p>
            </div>
        </div>
    </div>
</div>

<div class="section-container">
    <div class="cyber-card text-center" style="border-left: 4px solid var(--neon-magenta);">
        <h2 class="sec-title" style="border: none; padding-left: 0; margin-bottom: 15px;">¿Quieres Colaborar con la SBBL?</h2>
        <p style="font-size: 1.1rem; color: var(--text-bright);">
            Estamos buscando marcas, tiendas y patrocinadores que quieran formar parte del mayor evento de Beyblade en España.
        </p>
        <p class="stat-desc mb-4">
            Si tienes una propuesta comercial o quieres montar un stand en nuestra zona de tiendas, ponte en coctacto con la organización.
        </p>
        <a href="mailto:info@sbbl.es" class="btn-cyber btn-cyber-magenta">
            [ CONTACTAR: info@sbbl.es ]
        </a>
    </div>
</div>

<div class="section-container">
    <h2 class="sec-title">Preguntas Frecuentes</h2>

    <div class="protocol-item">
        <strong>¿Cuál es el precio de la entrada si no compito en el Nacional?</strong>
        <p class="stat-desc">La entrada general de asistente tiene un coste de 5€. Si quieres llevarte el Merch oficial de la liga, puedes adquirir el Pack VIP por 15€.</p>
    </div>

    <div class="protocol-item">
        <strong>¿Qué requisitos tiene el torneo por equipos?</strong>
        <p class="stat-desc">Estar entre los 16 mejores equipos del ranking y que al menos 3 miembros del equipo estén presentes en la BeyCon.</p>
    </div>

    <div class="protocol-item">
        <strong>¿Hay límite de edad para los Bladers?</strong>
        <p class="stat-desc">No, es un evento para toda la comunidad. Los menores de 15 años deberán ir acompañados por un responsable.</p>
    </div>
</div>

<div class="section-container text-center" style="padding-bottom: 100px;">
    <h2 class="sec-title" style="border:none;">Lista de Bladers Seleccionados</h2>
    <div class="cyber-card" style="border: 2px solid var(--neon-cyan);">
        <p style="font-family: 'Roboto Mono'; font-size: 1.2rem; color: var(--neon-cyan);">[ SINCRONIZANDO RANKING ]</p>
        <p style="color: var(--text-bright);">La lista oficial de los 96 Bladers clasificados se publicará tras el cierre del formulario.</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
</script>
@endsection
