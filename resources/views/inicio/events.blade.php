@extends('layouts.app')

@section('title', 'Eventos Beyblade X')

@section('styles')
<style>
    /* --- VARIABLES TEMA AZUL OSCURO PROFUNDO --- */
    :root {
        --bg-main: #0f172a;          /* Fondo principal (Slate 900) */
        --bg-calendar-cell: #1e293b; /* Fondo de las celdas (Slate 800) */
        --bg-calendar-hover: #334155;/* Hover en celdas (Slate 700) */
        --border-color: #1e293b;     /* Color de las líneas de la cuadrícula */
        --text-primary: #f1f5f9;     /* Texto principal claro (Slate 100) */
        --text-muted: #94a3b8;       /* Texto secundario (Slate 400) */
        --accent-color: #38bdf8;     /* Azul brillante para resaltar (Sky 400) */

        /* Colores de eventos (Pastel brillante para contraste) */
        --color-ranking: #ffd700;    /* Amarillo oro */
        --color-grancopa: #7dd3fc;   /* Azul cielo */
        --color-paypal: #e2e8f0;     /* Blanco/Gris claro */
        --color-quedada: #86efac;    /* Verde menta */
    }

    /* Contenedor principal para asegurar el fondo oscuro si no lo tiene el layout */
    body {
        background-color: var(--bg-main);
        color: var(--text-primary);
    }

    /* ESTILOS DEL CALENDARIO */
    .calendar-container {
        max-width: 1200px;
        margin: 0 auto;
        background-color: var(--bg-main);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5); /* Sombra suave */
        border: 1px solid var(--bg-calendar-cell);
    }

    /* La cuadrícula */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px; /* Espacio para las líneas */
        background-color: var(--border-color); /* Color de las líneas */
        border-bottom: 1px solid var(--border-color);
    }

    /* Celdas individuales */
    .day-cell {
        background-color: var(--bg-calendar-cell);
        min-height: 120px;
        padding: 8px;
        position: relative;
        transition: all 0.2s ease;
    }

    .day-cell:hover {
         background-color: var(--bg-calendar-hover);
    }

    /* Número del día */
    .day-number {
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
        text-align: right;
        color: var(--text-primary);
    }

    /* DÍA ACTUAL (Resaltado) */
    .day-cell.today {
        background-color: rgba(56, 189, 248, 0.1) !important; /* Tinte azul muy sutil */
        box-shadow: inset 0 0 0 2px var(--accent-color); /* Borde interno brillante */
    }
    .day-cell.today .day-number {
         color: var(--accent-color); /* Número en azul brillante */
    }

    /* Días de OTROS MESES (Apagados) */
    .day-cell.other-month {
        background-color: #0b1120; /* Un poco más oscuro que el fondo normal */
        opacity: 0.7;
    }
    .day-cell.other-month .day-number {
         color: var(--text-muted);
    }

    /* --- EVENTOS (Badges) --- */
    .event-badge {
        display: block;
        padding: 5px 10px;
        margin-bottom: 5px;
        border-radius: 6px;
        font-size: 0.8rem;
        /* Texto oscuro OBLIGATORIO para contraste con fondos pastel */
        color: #1e293b !important;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        border-left: 3px solid rgba(0,0,0,0.2);
    }

    .event-badge:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
        color: #000 !important;
        z-index: 10;
    }

    /* Asignación de colores de fondo */
    .bg-ranking { background-color: var(--color-ranking); }
    .bg-grancopa { background-color: var(--color-grancopa); }
    .bg-paypal { background-color: var(--color-paypal); }
    .bg-quedada { background-color: var(--color-quedada); }

    /* --- LEYENDA --- */
    .legend-box {
         background: var(--bg-calendar-cell);
         border: 1px solid var(--bg-calendar-hover);
         padding: 15px;
         border-radius: 12px;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin: 0 12px;
        font-size: 0.9rem;
        color: var(--text-primary);
        font-weight: 500;
    }
    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 4px; /* Cuadrados redondeados */
        margin-right: 8px;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.1);
    }

    /* --- NAVEGACIÓN --- */
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 0 10px;
    }

    #currentMonthLabel {
        font-size: 1.75rem;
        color: var(--text-primary);
        letter-spacing: 1px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .nav-btn {
        background-color: var(--bg-calendar-cell);
        border: 1px solid var(--bg-calendar-hover);
        color: var(--text-primary);
        padding: 10px 20px;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 600;
    }
    .nav-btn:hover {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        color: var(--bg-main); /* Texto oscuro al hacer hover */
        box-shadow: 0 0 15px -3px var(--accent-color);
    }

    /* --- RESPONSIVE & Cabecera Semanal --- */
    @media (max-width: 768px) {
        .calendar-grid, .weekdays-grid { display: none !important; } /* Ocultar grid en móvil */
        #mobile-event-list { display: block; }
        .calendar-container { background-color: transparent; box-shadow: none; border: none; }
    }

    @media (min-width: 769px) {
        #mobile-event-list { display: none; }
    }

    /* Días de la semana (LUN, MAR...) */
    .weekdays-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        text-align: center;
        font-weight: 800;
        padding: 15px 0;
        color: var(--accent-color); /* Color de acento */
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
        background-color: var(--bg-calendar-cell);
        border-bottom: 2px solid var(--bg-main);
    }
</style>
@endsection

@section('content')
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4 text-white">
        <h1>Calendario de Eventos</h1>
        @if ($countEvents < 2 || (Auth::user() && (Auth::user()->is_referee || Auth::user()->created_at->diffInMonths(now()) >= 3)))
            <a href="{{ route('events.create') }}" class="btn btn-warning fw-bold">
                <i class="fas fa-plus"></i> CREAR EVENTO
            </a>
        @endif
    </div>

    <div class="text-center mb-4 text-white p-3 rounded" style="background: rgba(255,255,255,0.1);">
        <span class="legend-item"><span class="legend-color bg-ranking"></span> Ranking / Plus</span>
        <span class="legend-item"><span class="legend-color bg-grancopa"></span> Gran Copa</span>
        <span class="legend-item"><span class="legend-color bg-paypal"></span> Copa Conqueror</span>
        <span class="legend-item"><span class="legend-color bg-quedada"></span> Quedada</span>
    </div>

    <div class="calendar-nav text-white">
        <button class="nav-btn" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i> Anterior</button>
        <h2 id="currentMonthLabel" class="m-0 text-uppercase fw-bold"></h2>
        <div>
            <button class="nav-btn me-2" onclick="goToToday()">Hoy</button>
            <button class="nav-btn" onclick="changeMonth(1)">Siguiente <i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <div id="loading" class="text-center my-5" style="display: none;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <div class="calendar-container">
        <div class="weekdays-grid d-none d-md-grid">
            <div>LUN</div><div>MAR</div><div>MIÉ</div><div>JUE</div><div>VIE</div><div>SÁB</div><div>DOM</div>
        </div>
        <div id="calendarGrid" class="calendar-grid"></div>
    </div>

    <div id="mobile-event-list" class="text-white">
        </div>

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

        const firstDay = new Date(year, month, 1).getDay(); // 0 (Domingo) - 6 (Sábado)
        // Ajustar para que Lunes sea 0 (Europeo): Domingo(0)->6, Lunes(1)->0, etc.
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
                link.innerHTML = `
                    ${evt.beys === 'copapaypal' ? '<i class="fab fa-paypal"></i> ' : ''}
                    ${evt.city || evt.region.name} (${evt.mode === 'beybladex' ? 'X' : 'Burst'})
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
            list.innerHTML = '<div class="alert alert-info text-center">No hay eventos este mes.</div>';
            return;
        }

        // Agrupar por fecha para móvil queda mejor
        // Pero siguiendo tu estilo lista simple:
        events.sort((a, b) => new Date(a.date) - new Date(b.date));

        events.forEach(evt => {
            const dateObj = new Date(evt.date);
            const dateFmt = dateObj.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', weekday: 'short' });

            const item = document.createElement('div');
            item.className = `p-3 mb-2 rounded text-dark ${getEventColorClass(evt.beys)}`;
            item.style.borderLeft = '5px solid rgba(0,0,0,0.2)';

            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-uppercase">${dateFmt}</span>
                    <span class="badge bg-dark text-white">${evt.mode === 'beybladex' ? 'X' : 'Burst'}</span>
                </div>
                <a href="/events/${evt.id}" target="_blank" class="d-block mt-1 fw-bold text-dark text-decoration-none fs-5">
                    ${evt.beys === 'copapaypal' ? '<i class="fab fa-paypal"></i> ' : ''}
                    ${evt.city || evt.region.name}
                </a>
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
