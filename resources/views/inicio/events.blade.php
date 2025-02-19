@extends('layouts.app')

@section('styles')
<style>
    .calendar {
        display: flex;
        flex-wrap: wrap;
        max-width: 100%;
        margin: 0 auto;
    }

    .day {
        width: calc(100% / 7); /* 7 columnas para pantallas grandes */
        border: 1px solid #ccc;
        padding: 10px;
        box-sizing: border-box;
        height: 120px;
        overflow-y: auto;
        position: relative;
    }

    .day::-webkit-scrollbar {
        width: 6px;
    }

    .day::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }

    .day::-webkit-scrollbar-track {
        background-color: #f2f2f2;
        border-radius: 4px;
    }

    .day-label {
        font-weight: bold;
        text-align: center;
        padding: 5px;
        background-color: #f2f2f2;
    }

    .event {
        background-color: #ffd700;
        padding: 5px 10px;
        margin-bottom: 2px;
        border-radius: 5px;
        cursor: pointer;
        display: block;
        text-decoration: none;
        color: #000;
        font-weight: bold;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .event:hover {
        background-color: #f0e68c;
    }

    .navigation {
        margin-bottom: 10px;
        text-align: center;
    }

    .navigation button {
        margin: 0 5px;
        cursor: pointer;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .navigation button:hover {
        background-color: #0056b3;
    }

    .current-month {
        text-align: center;
        margin-bottom: 10px;
    }

    .today {
        background-color: #f8b66a;
    }

    .current-day-button {
        margin-top: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 10px 20px;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .current-day-button:hover {
        background-color: #3270b1;
    }

    #loading {
        display: none;
        text-align: center;
        margin: 20px;
    }

    #loading img {
        width: 50px;
        height: 50px;
    }

    /* Cambio a 2 columnas en pantallas móviles */
    @media (max-width: 768px) {
        .calendar-box {
            display: none; /* Ocultar el calendario */
        }

        #weekEvents {
            display: block; /* Mostrar los eventos de la semana */
        }
    }

    /* Ajuste para pantallas muy pequeñas, en caso de ser necesario */
    @media (max-width: 576px) {
        .day {
            width: calc(50% - 10px); /* Mantener 2 columnas */
            margin: 5px;
        }
    }
</style>
@endsection

@section('content')

<h1 class="current-month mt-5" style="color: white;">Calendario de Eventos
    @if (
        $countEvents < 2 ||
        (Auth::user() && (Auth::user()->is_refereea ||
        Auth::user()->created_at->diffInMonths(now()) >= 3))
        )
        <a href="{{ route('events.create') }}" class="btn btn-outline-warning text-uppercase font-weight-bold">
            Crear evento
        </a>
    @endif
</h1>

<div class="navigation mt-5">
    <button class="prev-button" onclick="prevMonth()"><i class="fas fa-chevron-left"></i> Anterior</button>
    <button class="current-day-button" onclick="goToToday()">Ir al día actual</button>
    <button class="next-button" onclick="nextMonth()">Siguiente <i class="fas fa-chevron-right"></i></button>
</div>

<h3 class="current-month" id="currentMonth" style="color: white;"></h3>

<div id="loading">
    <img src="/storage/loading.gif" alt="Cargando...">
</div>

<!-- Calendario para pantallas grandes -->
<div class="calendar-box p-2">
    <div id="calendar" class="calendar mb-3" style="color: white;"></div>
</div>

<!-- Sección para eventos de la semana (solo en dispositivos móviles) -->
<div id="weekEvents" style="display: none; color: white;" class="p-4">
    <h3>Eventos del mes</h3>
    <div id="weekTitle"></div>
    <div id="eventList" class="pt-1 pb-1"></div>
</div>

@endsection

@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<script>
    let currentYear, currentMonth;

    document.addEventListener('DOMContentLoaded', function () {
        const today = new Date();
        currentYear = today.getFullYear();
        currentMonth = today.getMonth();

        buildCalendar(currentYear, currentMonth);

        if (window.innerWidth <= 768) {
            showMobileEvents(currentYear, currentMonth); // Pasar valores correctos
        }
    });

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        buildCalendar(currentYear, currentMonth);
        showMobileEvents(currentYear, currentMonth);
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        buildCalendar(currentYear, currentMonth);
        showMobileEvents(currentYear, currentMonth);
    }

    function buildCalendar(year, month) {
        showLoading();

        fetch('/eventos/fetch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ year, month: month + 1 })
        })
        .then(response => response.json())
        .then(events => {
            const calendarEl = document.getElementById('calendar');
            const monthEl = document.getElementById('currentMonth');
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDayOfMonth = (new Date(year, month, 1).getDay() || 7); // Lunes = 1, Domingo = 7
            let calendarHTML = '';
            let weekHTML = '<div class="week" style="display: flex;">';

            // Días vacíos antes del inicio del mes
            for (let i = 1; i < firstDayOfMonth; i++) {
                weekHTML += '<div class="day"></div>';
            }

            // Días del mes
            for (let i = 1; i <= daysInMonth; i++) {
                const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                const eventsOnDate = events.filter(event => event.date === date);
                const eventsHTML = eventsOnDate.map(event => `
                    <a class="event" href="/events/${event.id}" target="_blank">
                        ${event.city ? event.city : event.region.name} (${event.mode === 'beybladex' ? 'X' : 'Burst'})
                    </a>

                `).join('');

                const isToday = new Date().toDateString() === new Date(year, month, i).toDateString();
                weekHTML += `
                    <div class="day ${isToday ? 'today' : ''}">
                        ${i}
                        ${eventsHTML}
                    </div>
                `;

                // Cerrar fila al terminar la semana
                if ((i + firstDayOfMonth - 1) % 7 === 0 || i === daysInMonth) {
                    weekHTML += '</div>';
                    calendarHTML += weekHTML;
                    weekHTML = '<div class="week" style="display: flex;">';
                }
            }

            calendarEl.innerHTML = calendarHTML;
            monthEl.textContent = `${getMonthName(month)} ${year}`;
            hideLoading();
        })
        .catch(() => {
            alert('Error al cargar los eventos.');
            hideLoading();
        });
    }

    function showMobileEvents(year, month) {
    showLoading();

    const startOfMonth = new Date(year, month, 1);
    const endOfMonth = new Date(year, month + 1, 0);

    fetch('/eventos/fetch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ year: year, month: month + 1 }) // +1 porque los meses van de 0 a 11
    })
    .then(response => response.json())
    .then(events => {
        document.getElementById('weekTitle').textContent =
            `${getMonthName(month)} ${year}`;

        const eventListEl = document.getElementById('eventList');
        eventListEl.innerHTML = events
        .sort((a, b) => new Date(a.date) - new Date(b.date)) // Ordenar por fecha
        .map(event => `
            <div class="event">
                <a href="/events/${event.id}" target="_blank">
                    ${event.city ? event.city : event.region.name}
                    (${event.mode === 'beybladex' ? 'X' : 'Burst'}) -
                    ${new Date(event.date).toLocaleDateString('es-ES', {
                        day: 'numeric', month: 'long', year: 'numeric'
                    })}
                </a>
            </div>
        `).join('');

        eventListEl.style.display = events.length > 0 ? 'block' : 'none';
        document.getElementById('weekEvents').style.display = 'block';

        hideLoading();
    })
    .catch(() => {
        alert('Error al cargar los eventos del mes.');
        hideLoading();
    });
}


    function showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('calendar').style.display = 'none';
        document.getElementById('weekEvents').style.display = 'none';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('calendar').style.display = 'block';
    }

    function getMonthName(month) {
        const monthNames = [
            "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
        ];
        return monthNames[month];
    }

    function goToToday() {
        const today = new Date();
        currentYear = today.getFullYear();
        currentMonth = today.getMonth();

        buildCalendar(currentYear, currentMonth);
        showMobileEvents(currentYear, currentMonth);
    }

</script>
@endsection
