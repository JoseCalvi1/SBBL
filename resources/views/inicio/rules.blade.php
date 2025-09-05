@extends('layouts.app')

@section('title', 'Reglamento Beyblade X')

@section('content')

<div class="container my-5 text-white">
    <h2 class="text-primary">Reglamento Spanish BeyBattle League</h2>

    <h3 class="mt-4">🌀 SOBRE LOS BEYBLADES</h3>
    <ul>
        <li>Solo podrán usarse Beys de la generación <strong>Beyblade X</strong> (Hasbro o Takara Tomy).</li>
        <li>Los Beys están compuestos por: <strong>Blade</strong>, <strong>Ratchet</strong> y <strong>Bit</strong>. No pueden ser modificados, pintados ni alterados salvo en zonas permitidas (solo marcas con lápiz, bolígrafo o rotulador).</li>
        <li>No se permiten pegatinas no oficiales ni superpuestas.</li>
        <li>Los lanzadores deben ser oficiales y sin modificar.</li>
        <li>El juez podrá determinar si se ha infringido alguna norma.</li>
    </ul>

    <h3 class="mt-4">🏆 SISTEMA DE PUNTUACIÓN</h3>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Finalización</th>
                <th>Descripción</th>
                <th>Puntos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Xtreme Finish</strong></td>
                <td>El bey rival entra en la zona Xtreme sin posibilidad de volver</td>
                <td>3</td>
            </tr>
            <tr>
                <td><strong>Over Finish</strong></td>
                <td>El bey rival entra en la zona Over sin posibilidad de volver</td>
                <td>2</td>
            </tr>
            <tr>
                <td><strong>Burst Finish</strong></td>
                <td>El bey rival se desmonta antes que el tuyo o antes de otro final</td>
                <td>2</td>
            </tr>
            <tr>
                <td><strong>Spin Finish</strong></td>
                <td>El bey rival se detiene antes o gira en sentido contrario</td>
                <td>1</td>
            </tr>
        </tbody>
    </table>

    <h3 class="mt-4">⚔️ SOBRE LOS COMBATES</h3>
    <ol>
        <li>Lanzamiento máximo a 20 cm de altura.</li>
        <li>Prohibido interferir en el lanzamiento del rival.</li>
        <li>Se lanza con la cuenta: "3, 2, 1, Go Shoot" (en "Shoot").</li>
        <li>El combate comienza al tocar el estadio; si explotan antes, se reinicia.</li>
        <li><strong>Prohibido el sniping</strong> (lanzar sobre un bey ya en el estadio).</li>
        <li>Errores: no salir del lanzador, caída prematura o fuera del área.</li>
        <li>Lanzamientos fuera de cuenta también son errores.</li>
        <li>
            <strong>Posibles casos en los que un error de lanzamiento puede considerarse <em>warning</em>:</strong><br>
            <ul class="mt-2">
                <li>Provocar contacto entre beys en el aire.</li>
                <li>Reincidencia al lanzar antes o después de la señal "Shoot".</li>
                <li>Lanzar a una altura no válida tras advertencia previa.</li>
                <li>Recoger el bey antes de que el árbitro dicte el veredicto.</li>
                <li>Otras acciones similares que incumplan las normas tras ser advertido.</li>
            </ul>
        </li>
        <li>2 warnings = 1 punto para el oponente.</li>
        <li>No tocar ni mirar dentro del estadio sin permiso.</li>
        <li>No tocar el bey antes del veredicto del juez.</li>
        <li>Si un bey sale por Over/Xtreme sin contacto con el bey del oponente o el estadio, pierde 1 punto y se repite.</li>
        <li>Interferencia maliciosa = descalificación.</li>
        <li>El juez tiene la última palabra.</li>
        <li>Gana quien llegue primero a 4 puntos.</li>
    </ol>

    <h3 class="mt-4">📅 SOBRE LOS TORNEOS</h3>
    <ol>
        <li>Mínimo de 4 participantes.</li>
        <li>Todo el torneo debe jugarse el mismo día.</li>
        <li>Máximo número de torneos al mes: 2 (Siendo en semanas naturales distintas, de Lunes a Domingo).</li>
        <li>Todos deben estar inscritos desde la web del evento.</li>
        <li>El torneo se gestiona en <a href="https://challonge.com" target="_blank">challonge.com</a>.</li>
        <li>Se admiten los formatos de eliminación simple y doble para los torneos de ranking.</li>
        <li>Añadir participantes a Challonge al iniciar (Se puede utilizar la opción "Copiar nombres" que aparece justo encima del listado de jugadores en la página del evento).</li>
        <li>Compartir el link del challonge del torneo por el <a href="https://discord.com/channels/875324662010228746/1095649139162877972" target="_blank">canal del servidor dedicado a ello</a></li>
        <li>Introducir resultados en vivo en Challonge.</li>
        <li>Finalizar el torneo en la plataforma para mostrar ganadores.</li>
        <li>Grabar todo el torneo sin cortes.</li>
        <li>Si hay varios estadios, se grabarán cada uno de ellos.</li>
        <li>Una vez finalizado el torneo, se introducirán los puestos en el listado de participantes en la web y se adjuntarán el vídeo del torneo (link de youtube, drive o similares) y el link de challonge. <strong>Si esto no se hace no se procederá a la revisión del torneo</strong></li>
        <li>Antes de cada combate el árbitro podrá revisar los decks de los participantes y se deberán mostrar a cámara para que se vean bien los combos de ambos jugadores.</li>
        <li>Al inscribirte a un torneo de ranking se considerará que se ha comprendido la normativa.</li>
        <li>El torneo  será revisado por tres árbitros externos a la comunidad donde se haya jugado este y se valorará si es validable o se han cometido errores importantes que alteren los puestos finales de los jugadores.</li>
        <li>Se prohiben los consejos que puedan interferir en un juego limpio o el apoyo excesivo que pueda intimidar a los jugadores.</li>
        <li>Está prohibido cualquier comportamiento que pueda causar molestias a quienes le rodean o que pueda interferir en el desarrollo del torneo o evento.</li>
        <li>Siga las instrucciones y diviértase participando en eventos y competiciones. El incumplimiento de las normas puede resultar en descalificación.</li>
        <li>Si no puede utilizar Beyblade según el criterio del personal y los árbitros de la SBBL, abstenerse de participar en el evento o torneo.</li>
    </ol>

     <h3 class="mt-3">🎯 PUNTUACIÓN EN TORNEOS</h3>
    <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">Nº Jug</th>
                <th scope="col">1º</th>
                <th scope="col">2º</th>
                <th scope="col">3º</th>
                <th scope="col">4º</th>
                <th scope="col">5º</th>
                <th scope="col">7º</th>
                <th scope="col">Resto</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>4–5</td><td>2</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td></tr>
            <tr><td>6–8</td><td>3</td><td>2</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td></tr>
            <tr><td>9–16</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>1</td><td>1</td></tr>
            <tr><td>17–24</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>1</td></tr>
            <tr><td>25–32</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td></tr>
            <tr><td>33 en adelante</td><td>7</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td></tr>
        </tbody>
    </table>

    <h3 class="mt-4">📋 SOBRE EL FORMATO</h3>
    <ol>
        <li>Formato 3on3 (Todos los jugadores deben llevar un deck compuesto por 3 beys).</li>
        <li>No se pueden repetir piezas entre beys en un mismo deck.</li>
        <li>Todas las piezas que componen el bey deben ser oficiales (Takara Tomy o Hasbro).</li>
        <li>El árbitro podrá desensamblar el bey de un jugador para comprobar cada parte.</li>
        <li>Si un jugador tiene alguna duda sobre un bey, debe informar al juez al momento. No se aceptarán opiniones una vez empezada la partida ni de ninguna persona distinta a los jugadores o árbitros.</li>
        <li>Antes de empezar la partida, se decidirá el orden en el que se van a utilizar los beys ocultándolo al rival.</li>
        <li>Se jugará los beys de izquierda a derecha  (1ºvs1º, 2ºvs2º y 3ºvs3º).</li>
        <li>Los combates se jugarán a 4 puntos. El primer blader en alcanzarlos, será el vencedor.</li>
        <li>En caso de que ninguno de los dos jugadores llegue a 4 puntos después de realizar el tercer combate, se reorganizará el deck para seguir combatiendo hasta que alguno de los dos llegue a la puntuación.</li>
        <li>En caso de empate en alguno de los matchups, se volverán a utilizar ambos beys para repetir ese combate hasta que se obtenga un final claro.</li>
        <li>Si una puntuación no puede ser determinada se repetirá el combate (Ej: Sale el bey por la parte superior del estadio).</li>
    </ol>

    <h3 class="mt-4">🆚 SOBRE LOS DUELOS DE EQUIPO</h3>
    <ol>
        <li>Los duelos de equipos forman parte de un ranking independiente.</li>
        <li>Cada equipo deberá estar formado por un mínimo de 3 bladers y un máximo de 6.</li>
        <li>Cada blader usará un único bey que no podrá ser modificado durante el duelo.</li>
        <li>En el equipo no podrán repetirse piezas entre los integrantes que vayan a jugar el duelo.</li>
        <li>Para realizar un duelo deberán estar presentes al menos 3 miembros de ambos equipos.</li>
        <li>Antes de iniciar el duelo deberán decidir el orden en el que cada miembro participará hasta acabar el duelo.</li>
        <li>Dicho orden y el bey que utilizará cada blader deberá ser apuntado. Antes de iniciar el duelo, el orden se mostrará al equipo rival para que haya transparencia en el desarrollo del mismo.</li>
        <li>Una vez establecido el orden, cada blader se enfrentará con su rival con su posición correspondiente, 1ºvs1º, 2ºvs2º, 3ºvs3º, etc.</li>
        <li>Si uno de los dos equipos tiene más participantes, el equipo con menos participantes volverá a jugar con el 1º, 2º, etc (sin cambiar el orden) hasta que todos los miembros del otro equipo hayan jugado.</li>
        <li>El equipo con más puntuación al acabar todos los combates ganará un punto en el ranking de equipos y deberá introducir el resultado en la web y enviar el video del duelo completo sin cortes.</li>
        <li>En caso de que haya un empate a puntos, los dos capitanes se enfrentarán con su combo seleccionado para el duelo.</li>
        <li>En caso de no estar presente algún capitán, lo hará el jugador seleccionado como 1º en el duelo.</li>
    </ol>
</div>



            <div class="container-fluid" style="background-color: red;">
                <div class="row">
                    <div class="col-md-12 mt-3 mb-3 text-center">
                        <p style="color: white; font-size: 1.4em;">Estas normas están basadas en las de B4 por lo que
                            si hay alguna duda de algo que no esté contemplado arriba no dudes en contactar con
                            admin@sbbl.es o escribirlo por nuestro <a style="color: white; font-weight: bold;"
                                target="_blank" href="https://discord.gg/vXhY4nGSwZ"> Discord</a></p>
                        <p style="color: white; font-size: 1.4em;">Si se observa alguna irregularidad que no esté
                            contemplada en nuestras reglas se revisará el reglamento oficial de B4 para aclarar
                            cualquier duda</p>
                    </div>
                </div>
            </div>
    @endsection

    @section('scripts')
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    @endsection
