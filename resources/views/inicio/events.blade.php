@extends('layouts.app')

@section('title', 'Eventos Beyblade X')

@section('styles')
<style>
    /* ====================================================================
       ESTILOS ESPECÍFICOS: CALENDARIO TÁCTICO (Hereda de layout)
       ==================================================================== */

    :root {
        /* Colores de eventos (Brillantes para contrastar con el fondo oscuro) */
        --color-ranking: var(--sbbl-gold);
        --color-grancopa: var(--shonen-cyan);
        --color-paypal: #fff;
        --color-quedada: #00ff00;
    }

    /* --- TÍTULO DE PÁGINA --- */
    .page-title {
        font-family: 'Oswald', cursive;
        font-size: 3.5rem;
        color: var(--sbbl-gold);
        text-shadow: 3px 3px 0 #000, 6px 6px 0 var(--shonen-red);
        letter-spacing: 2px;
        margin: 0;
        line-height: 1;
    }

    /* --- LEYENDA --- */
    .legend-wrapper {
        background: var(--sbbl-blue-2);
        border: 3px solid #000;
        box-shadow: 5px 5px 0 #000;
        transform: skewX(-2deg);
        padding: 15px;
    }
    .legend-wrapper > * { transform: skewX(2deg); }
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin: 0 15px;
        font-family: 'Oswald', cursive;
        font-size: 1.2rem;
        color: #fff;
        letter-spacing: 1px;
        text-shadow: 1px 1px 0 #000;
    }
    .legend-color {
        width: 18px;
        height: 18px;
        border: 2px solid #000;
        margin-right: 8px;
        box-shadow: 2px 2px 0 rgba(0,0,0,0.5);
    }

    /* --- NAVEGACIÓN --- */
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    #currentMonthLabel {
        font-family: 'Oswald', cursive;
        font-size: 2.5rem;
        color: #fff;
        letter-spacing: 2px;
        text-shadow: 2px 2px 0 #000;
        text-transform: uppercase;
        margin: 0;
    }

    /* --- ESTILOS DEL CALENDARIO --- */
    .calendar-container {
        width: 100%;
        background-color: #000;
        border: 4px solid #000;
        box-shadow: 10px 10px 0 #000;
        overflow: hidden;
    }

    /* Días de la semana (LUN, MAR...) */
    .weekdays-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        font-family: 'Oswald', cursive;
        font-size: 1.5rem;
        color: var(--sbbl-gold);
        background-color: var(--sbbl-blue-3);
        border-bottom: 4px solid #000;
        padding: 10px 0;
        text-shadow: 2px 2px 0 #000;
    }

    /* La cuadrícula */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 3px; /* Espacio para las líneas negras */
        background-color: #000; /* Color de las líneas */
    }

    /* Celdas individuales */
    .day-cell {
        background-color: var(--sbbl-blue-2);
        min-height: 130px;
        padding: 8px;
        position: relative;
        transition: 0.2s;
        overflow: hidden;
    }
    .day-cell:hover {
        background-color: var(--sbbl-blue-3);
    }

    /* Número del día */
    .day-number {
        font-family: 'Oswald', cursive;
        font-size: 1.8rem;
        color: #fff;
        text-shadow: 2px 2px 0 #000;
        margin-bottom: 8px;
        display: block;
        text-align: right;
        line-height: 1;
    }

    /* DÍA ACTUAL (Resaltado) */
    .day-cell.today {
        background-color: rgba(0, 255, 204, 0.15) !important;
        box-shadow: inset 0 0 0 4px var(--shonen-cyan);
    }
    .day-cell.today .day-number {
        color: var(--shonen-cyan);
    }

    /* Días de OTROS MESES */
    .day-cell.other-month {
        background-color: #111;
        opacity: 0.8;
    }
    .day-cell.other-month .day-number {
        color: #555;
        text-shadow: none;
    }

    /* --- EVENTOS (Badges) --- */
    .event-badge {
        display: block;
        padding: 5px 8px;
        margin-bottom: 5px;
        font-weight: 900;
        font-size: 0.8rem;
        color: #000 !important;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        border: 2px solid #000;
        box-shadow: 2px 2px 0 #000;
        transition: 0.2s;
        text-transform: uppercase;
    }
    .event-badge:hover {
        transform: translate(-2px, -2px);
        box-shadow: 4px 4px 0 #000;
        z-index: 10;
        position: relative;
    }

    /* Asignación de colores de fondo */
    .bg-ranking { background-color: var(--color-ranking); }
    .bg-grancopa { background-color: var(--color-grancopa); }
    .bg-paypal { background-color: var(--color-paypal); }
    .bg-quedada { background-color: var(--color-quedada); }

    /* --- RESPONSIVE & MOBILE FIXES --- */
    @media (min-width: 769px) {
        #mobile-event-list { display: none; }
    }

    @media (max-width: 768px) {
        .calendar-grid, .weekdays-grid { display: none !important; }
        #mobile-event-list { display: block; }
        .calendar-container { background-color: transparent; box-shadow: none; border: none; }

        .page-title { font-size: 2.5rem; text-align: center; width: 100%; margin-bottom: 15px !important; }

        .legend-wrapper {
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            text-align: left !important;
        }
        .legend-item { margin: 0 !important; font-size: 1rem; }

        .calendar-nav { flex-direction: column; gap: 15px; }
        #currentMonthLabel { order: -1; font-size: 2rem; }
        .nav-buttons-container { width: 100%; display: flex; gap: 10px; }
        .btn-shonen { flex: 1; text-align: center; padding: 10px; font-size: 1rem; }
        .nav-btn-prev { width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <h1 class="page-title"><i class="fas fa-calendar-alt me-2 text-white" style="text-shadow:none;"></i>CALENDARIO</h1>

        @if ($countEvents < 2 || (Auth::user() && (Auth::user()->hasRole('arbitro') || Auth::user()->created_at->diffInMonths(now()) >= 3)))
            <a href="{{ route('events.create') }}" class="btn-shonen btn-shonen-warning mt-3 mt-md-0" style="padding: 10px 25px; font-size: 1.4rem;">
                <span style="display: block; transform: skewX(5deg);"><i class="fas fa-plus me-1"></i> CREAR EVENTO</span>
            </a>
        @endif
    </div>

    <div class="legend-wrapper text-center mb-5">
        <span class="legend-item"><span class="legend-color bg-ranking"></span> Ranking / Plus</span>
        <span class="legend-item"><span class="legend-color bg-grancopa"></span> Gran Copa</span>
        <span class="legend-item"><span class="legend-color bg-paypal"></span> Copa Conqueror</span>
        <span class="legend-item"><span class="legend-color bg-quedada"></span> Quedada</span>
    </div>

    <div class="calendar-nav">
        <button class="btn-shonen btn-shonen-info nav-btn-prev" onclick="changeMonth(-1)">
            <span><i class="fas fa-chevron-left me-1"></i> ANTERIOR</span>
        </button>

        <h2 id="currentMonthLabel"></h2>

        <div class="nav-buttons-container d-flex gap-3">
            <button class="btn-shonen btn-shonen-warning" onclick="goToToday()">
                <span>HOY</span>
            </button>
            <button class="btn-shonen btn-shonen-info" onclick="changeMonth(1)">
                <span>SIGUIENTE <i class="fas fa-chevron-right ms-1"></i></span>
            </button>
        </div>
    </div>

    <div id="loading" class="text-center my-5" style="display: none;">
        <div class="spinner-border text-warning" style="width: 3rem; height: 3rem; border-width: 0.3rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="font-Oswald fs-4 mt-3" style="color: var(--sbbl-gold); text-shadow: 2px 2px 0 #000;">SINCRONIZANDO DATOS...</p>
    </div>

    <div class="calendar-container">
        <div class="weekdays-grid d-none d-md-grid">
            <div>LUN</div><div>MAR</div><div>MIÉ</div><div>JUE</div><div>VIE</div><div>SÁB</div><div>DOM</div>
        </div>
        <div id="calendarGrid" class="calendar-grid"></div>
    </div>

    <div id="mobile-event-list" class="mt-3"></div>

</div>
@endsection

@section('scripts')
<script>
    let currentDate = new Date();

    // Configuración inicial
    document.addEventListener('DOMContentLoaded', () => {
        renderCalendar();
    });

    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        renderCalendar();
    }

    function goToToday() {
        currentDate = new Date();
        renderCalendar();
    }

    function getMonthName(date) {
        return date.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
    }

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth(); // 0-11
        const monthLabel = document.getElementById('currentMonthLabel');
        const grid = document.getElementById('calendarGrid');
        const mobileList = document.getElementById('mobile-event-list');
        const loader = document.getElementById('loading');

        monthLabel.textContent = getMonthName(currentDate);

        // Mostrar loading, ocultar contenido
        loader.style.display = 'block';
        grid.style.opacity = '0.5';
        mobileList.style.opacity = '0.5';

        // Fetch de eventos
        fetch('/eventos/fetch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ year: year, month: month + 1 }) // Laravel espera 1-12
        })
        .then(res => res.json())
        .then(events => {
            // 1. Renderizar Escritorio
            renderDesktopGrid(year, month, events);

            // 2. Renderizar Móvil
            renderMobileList(year, month, events);
        })
        .catch(err => console.error(err))
        .finally(() => {
            loader.style.display = 'none';
            grid.style.opacity = '1';
            mobileList.style.opacity = '1';
        });
    }

    function renderDesktopGrid(year, month, events) {
        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = ''; // Limpiar

        const firstDay = new Date(year, month, 1).getDay();
        // Ajustar para que Lunes sea 0 (Europeo): Domingo(0)->6, Lunes(1)->0
        const startOffset = (firstDay === 0 ? 6 : firstDay - 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const todayStr = new Date().toISOString().split('T')[0];

        // Celdas vacías iniciales
        for(let i = 0; i < startOffset; i++) {
            const cell = document.createElement('div');
            cell.className = 'day-cell other-month';
            grid.appendChild(cell);
        }

        // Días del mes
        for(let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const cell = document.createElement('div');
            cell.className = 'day-cell';
            if(dateStr === todayStr) cell.classList.add('today');

            // Número del día
            cell.innerHTML = `<span class="day-number">${d}</span>`;

            // Filtrar eventos de este día
            const dayEvents = events.filter(e => e.date.startsWith(dateStr));

            dayEvents.forEach(evt => {
                const link = document.createElement('a');
                link.href = `/events/${evt.id}`;
                link.target = "_blank";
                link.className = `event-badge ${getEventColorClass(evt.beys)}`;
                link.title = `${evt.city || evt.region.name} (${evt.mode === 'beybladex' ? 'X' : 'Burst'})`;
                link.innerHTML = `
                    ${evt.beys === 'copapaypal' ? '<i class="fab fa-paypal"></i> ' : ''}
                    ${evt.city || evt.region.name} <span style="opacity:0.6">(${evt.mode === 'beybladex' ? 'X' : 'Burst'})</span>
                `;
                cell.appendChild(link);
            });

            grid.appendChild(cell);
        }
    }

    function renderMobileList(year, month, events) {
        const list = document.getElementById('mobile-event-list');
        list.innerHTML = ''; // Limpiar

        if(events.length === 0) {
            list.innerHTML = '<div class="alert alert-dark text-center font-Oswald fs-3 text-white" style="background: rgba(0,0,0,0.5) !important; border: 3px solid #000 !important; border-radius: 0;">NO HAY EVENTOS ESTE MES.</div>';
            return;
        }

        events.sort((a, b) => new Date(a.date) - new Date(b.date));

        events.forEach(evt => {
            const dateObj = new Date(evt.date);
            const dayName = dateObj.toLocaleDateString('es-ES', { weekday: 'short' }).toUpperCase();
            const dayNum = dateObj.getDate();
            const monthName = dateObj.toLocaleDateString('es-ES', { month: 'short' }).toUpperCase();

            const item = document.createElement('div');
            // Estilo Shonen para las tarjetas de móvil
            item.className = `p-3 mb-3 position-relative ${getEventColorClass(evt.beys)}`;
            item.style.border = '3px solid #000';
            item.style.boxShadow = '5px 5px 0 #000';
            item.style.transform = 'skewX(-2deg)';
            item.style.color = '#000';

            item.innerHTML = `
                <div style="transform: skewX(2deg);">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex flex-column lh-1">
                            <span class="font-Oswald fs-4" style="color:inherit;">${dayName}, ${dayNum} ${monthName}</span>
                        </div>
                        <span class="badge bg-black text-white font-Oswald fs-6" style="border: 2px solid #fff; border-radius: 0; box-shadow: 2px 2px 0 rgba(0,0,0,0.5);">${evt.mode === 'beybladex' ? 'Beyblade X' : 'Beyblade Burst'}</span>
                    </div>

                    <a href="/events/${evt.id}" class="text-decoration-none" style="color: inherit;">
                        <h4 class="m-0 fw-bold d-flex align-items-center gap-2 text-uppercase" style="font-size: 1.3rem;">
                            ${evt.beys === 'copapaypal' ? '<i class="fab fa-paypal"></i>' : ''}
                            ${evt.city || evt.region.name}
                        </h4>
                        <div class="mt-2 small fw-bold text-uppercase d-flex align-items-center" style="opacity: 0.8;">
                           Ver reporte <i class="fas fa-arrow-right ms-2"></i>
                        </div>
                    </a>
                </div>
            `;
            list.appendChild(item);
        });
    }

    function getEventColorClass(type) {
        switch(type) {
            case 'ranking':
            case 'rankingplus': return 'bg-ranking';
            case 'grancopa': return 'bg-grancopa';
            case 'copapaypal': return 'bg-paypal';
            case 'quedada': return 'bg-quedada';
            default: return 'bg-light';
        }
    }
</script>
@endsection
