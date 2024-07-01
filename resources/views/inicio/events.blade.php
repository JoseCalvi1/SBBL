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
        width: calc(100% / 7);
        border: 1px solid #ccc;
        padding: 10px;
        box-sizing: border-box;
        height: 120px; /* Incrementamos un poco la altura para mejor legibilidad */
        overflow-y: auto; /* Solo scroll vertical */
        position: relative;
    }

    .day::-webkit-scrollbar {
        width: 6px;
    }

    .day::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 4px;
    }

    .day::-webkit-scrollbar-thumb:hover {
        background-color: #555;
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

    /* Estilos para pantallas pequeñas */
    @media (max-width: 768px) {
        .calendar {
            display: flex;
            flex-wrap: wrap;
            max-width: 100%;
            margin: 0 auto;
        }

        .day {
            width: calc(50% - 10px); /* Mostrar dos días por fila */
            margin-right: 10px; /* Espacio entre días */
            border: 1px solid #ccc;
            padding: 10px;
            box-sizing: border-box;
            height: 120px; /* Altura fija de cada cuadrícula */
            overflow-y: auto; /* Scroll vertical si es necesario */
            position: relative;
        }
    }
</style>
@endsection

@section('content')

<h1 class="current-month mt-5" style="color: white;">Calendario de Eventos
    @if ($countEvents < 2 || (Auth::user() && Auth::user()->is_admin))
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

<div id="calendar" class="calendar mb-3" style="color: white;"></div>

@endsection

@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<script>
    var currentYear, currentMonth;

    document.addEventListener('DOMContentLoaded', function () {
        var today = new Date();
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();

        buildCalendar(currentYear, currentMonth);
    });

    function prevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        buildCalendar(currentYear, currentMonth);
    }

    function nextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        buildCalendar(currentYear, currentMonth);
    }

    function buildCalendar(year, month) {
        var calendarEl = document.getElementById('calendar');
        var monthEl = document.getElementById('currentMonth');
        var events = {!! json_encode($events) !!};

        var daysInMonth = new Date(year, month + 1, 0).getDate();
        var firstDayOfMonth = new Date(year, month, 1).getDay(); // Día de la semana (0 = Domingo, 1 = Lunes, ..., 6 = Sábado)
        // Ajustamos para que el Lunes sea el primer día de la semana
        if (firstDayOfMonth === 0) { // Si es Domingo
            firstDayOfMonth = 6; // Lo cambiamos a Sábado
        } else {
            firstDayOfMonth--; // Restamos 1 para ajustar el índice de Lunes (1)
        }

        var calendarHTML = '';

        // Rellenar los días previos
        for (var i = 0; i < firstDayOfMonth; i++) {
            calendarHTML += '<div class="day"></div>';
        }

        // Rellenar los días del mes
        for (var i = 1; i <= daysInMonth; i++) {
            var date = new Date(year, month, i);
            var eventsOnDate = events.filter(function (evento) {
                return evento.date === formatDate(date);
            });

            var eventHTML = '';
            eventsOnDate.forEach(function (evento) {
                var eventId = evento.id;
                var eventRelation = evento.region.name; // Accedemos al campo de relación

                // Modifica este enlace para que redirija al detalle del evento
                eventHTML += '<a class="event" href="/events/' + eventId + '" target="_blank">' + eventRelation + ' (' + (evento.mode == 'beybladex' ? 'X' : 'Burst') + ')' + '</a>';
            });

            var today = new Date();
            var isToday = i === today.getDate() && month === today.getMonth() && year === today.getFullYear();
            var dayClass = isToday ? 'today' : '';
            calendarHTML += '<div class="day ' + dayClass + '">' + i + eventHTML + '</div>';

            // Si es la vista móvil y es el segundo día de la fila, cerramos la fila
            if (window.innerWidth <= 768 && i % 2 === 0) {
                calendarHTML += '</div><div class="calendar">';
            }
        }

        calendarEl.innerHTML = calendarHTML;
        monthEl.textContent = getMonthName(month) + ' ' + year;
    }

    function formatDate(date) {
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var day = date.getDate();

        return year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;
    }

    function getMonthName(month) {
        var monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        return monthNames[month];
    }

    function goToToday() {
        var today = new Date();
        currentMonth = today.getMonth();
        currentYear = today.getFullYear();
        buildCalendar(currentYear, currentMonth);
    }
</script>
@endsection
