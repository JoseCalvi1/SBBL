@extends('layouts.app')

@section('title', 'Reglamento Beyblade X - S2')

@section('content')

<div class="container my-5 text-white">
    <h2 class="text-primary">REGLAMENTO SBBL V2.1</h2>

    <h3 class="mt-4">🌀 SOBRE LOS BEYBLADES</h3>
    <ul>
        <li>Solo podrán utilizarse Beyblades (en adelante, Beys) de la generación Beyblade X (marca Hasbro o Takara Tomy).</li>
        <li>Un Beyblade está compuesto por tres partes: Blade, Ratchet y Bit. En el sistema CX, cada Blade se divide a su vez en otros tres componentes: Lock Chip, Main Blade y Assist Blade.</li>
        <li>En el caso de los Lock Chips Valkyrie y Emperor, ambos pueden incluirse en el deck, pero solo se permite uno de cada tipo. Es decir, se puede llevar un Valkyrie y un Emperor, pero no más de uno de cada uno.</li>
        <li>Ninguna parte de un Bey podrá ser modificada, alterada o pintada excepto en las zonas marcadas en la siguiente imagen. En los lugares permitidos se pueden usar rotuladores, lápices, bolígrafos o similares para hacer pequeñas marcas. No se permiten pinturas de ningún tipo (óleo, acrílico, etc).</li>
    </ul>

    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <img src="{{ asset('images/partes_bey.png') }}" class="img-fluid rounded shadow-sm border" alt="zonas que no hagan contacto con el Bey rival">
            <p class="text-white small mt-1">zonas que no hagan contacto con el Bey rival</p>
        </div>
    </div>

    <ul>
        <li>No se permite el uso de pegatinas que no procedan de las marcas oficiales o de la SBBL. No está permitido pegar pegatinas unas encima de otras.</li>
        <li>Todos los Beys, lanzadores y grips utilizados en el torneo deberán ser oficiales y permanecer sin modificar. No se permite el uso de accesorios no oficiales o piezas alteradas que puedan afectar el desempeño (por ejemplo, slip grips, adaptadores, piezas impresas en 3D, etc.).</li>
        <li>No está permitido el uso de piezas partidas o agrietadas.</li>
        <li>El cambio de modo de las piezas mediante la función Cambio de Modo, así como la modificación de la orientación de componentes como los ratchets, se puede realizar en cada batalla únicamente después de que los jugadores hayan mostrado sus beys y tras la autorización del árbitro. Estas acciones deben completarse dentro del límite de tiempo establecido por el árbitro.</li>
        <li>Si las decoraciones del lanzador, del grip o de cualquier otro elemento del equipo del jugador interfieren con el desarrollo del combate, el árbitro podrá solicitar que sean retiradas.</li>
        <li>En caso de rotura de alguna pieza durante el transcurso del torneo, esta deberá ser sustituida por otra del mismo tipo (por ejemplo, si es 1-60, deberá reemplazarse por otro 1-60, independientemente del color) previa autorización del árbitro. Si la sustitución de la pieza no es posible, el jugador quedará descalificado del torneo.</li>
        <li>No se permite el uso simultáneo, dentro del mismo deck, de piezas con moldes de Hasbro y Takara Tomy que sean idénticos en su forma, independientemente del nombre, color o pegatinas de las mismas.</li>
        <li>Todas las piezas deben ser usadas y ensambladas tal como fueron diseñadas por el fabricante. No se permite omitir partes ni montar las piezas de manera distinta a su orientación y función originales. Por ejemplo, Hells Hammer no puede ser lanzada con un lanzador de giro izquierdo.</li>
        <li>Si tienes alguna pregunta con respecto a la verificación del Bey, informa a un árbitro  o juez presente en el torneo de ser posible. Las opiniones después de que haya comenzado el combate o de cualquier persona que no sea el jugador no serán aceptadas.</li>
        <li>Los bits excesivamente desgastados no serán permitidos y deberán ser sustituidos para poder participar en un torneo oficial.</li>
    </ul>

    <h3 class="mt-4">🏆 SISTEMA DE PUNTUACIÓN</h3>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Final</th>
                <th>Descripción</th>
                <th>Puntos</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Xtreme Finish</td>
                <td>El Beyblade rival entra en la zona Xtreme sin posibilidad de regresar al estadio</td>
                <td>3</td>
            </tr>
            <tr>
                <td>Over Finish</td>
                <td>El Beyblade rival entra en la zona Over sin posibilidad de regresar al estadio</td>
                <td>2</td>
            </tr>
            <tr>
                <td>Burst Finish</td>
                <td>El Beyblade rival se desmonta antes que el propio Beyblade o antes de cualquier otro tipo de finalización</td>
                <td>2</td>
            </tr>
            <tr>
                <td>Spin Finish</td>
                <td>El Beyblade rival se detiene o comienza a girar en sentido contrario</td>
                <td>1</td>
            </tr>
            <tr>
                <td>Punto de Penalti</td>
                <td>El Beyblade realiza un recorrido por el estadio y sale por la zona Over o Xtreme sin contacto con el Beyblade rival</td>
                <td>1</td>
            </tr>
        </tbody>
    </table>

        <div class="row mb-4">

        <div class="col-md-6 text-center">

            <img src="{{ asset('images/estadio_zonas_1.png') }}" class="img-fluid rounded shadow-sm border" alt="Terreno de juego: Zonas Xtreme y Over">

        </div>

        <div class="col-md-6 text-center">

            <img src="{{ asset('images/estadio_zonas_2.png') }}" class="img-fluid rounded shadow-sm border" alt="Terreno de juego: Zonas Xtreme y Over">

        </div>

    </div>

    <h3 class="mt-4">⚔️ SOBRE LOS COMBATES</h3>
    <ul>
        <li>Antes de empezar la batalla los oponentes sortearán el lado del estadio, para ello pueden utilizar piedra, papel o tijera. En el estadio “Infinity Stadium” (BX-46) no es necesario.</li>
        <li>Los lanzamientos deben producirse desde una altura máxima de 20 cm respecto al terreno de juego. Como referencia, el bit del Bey tiene que quedar a la altura del anillo de plástico de la cúpula del estadio que limita la zona de lanzamiento o inferior.</li>
        <li>Los oponentes se mostrarán entre sí la parte frontal y posterior de sus Beys, sin entregarlos unos a otros, para su verificación.</li>
        <li>Está prohibido interferir en el lanzamiento del rival, incluida la invasión de su zona de lanzamiento. En caso de interferencia, consúltalo con el árbitro o juez.</li>
    </ul>

    <div class="row mb-4">
        <div class="col-md-12 text-center">
             <img src="{{ asset('images/lanzamiento_ok.png') }}" class="img-fluid rounded shadow-sm border" alt="Lanzamiento permitido y no permitido">
        </div>
    </div>

    <ul>
        <li>Se lanza con la cuenta: "3, 2, 1, Go Shoot", debiendo el Bey salir del lanzador en el momento del “Shoot”. Antes de iniciar la cuenta, el árbitro se asegurará de que ambos bladers estén preparados.</li>
        <li>Cualquier colisión que ocurra en el aire antes de que los Beys toquen el terreno de juego será considerada snipe/aerial. Esto incluye tanto el choque entre ambos Beys en el aire como el caso en que un Bey caiga sobre otro que ya se encuentre girando dentro del estadio. En cualquiera de estas situaciones, la batalla deberá reiniciarse.</li>
        <li>Si, habiendo lanzado a la vez, uno de los  jugadores lanza su Bey hacia el área de lanzamiento del rival y provoca que ambos Beys choquen en el aire, dicho jugador podrá considerarse responsable de la colisión. En este caso, será sancionado con un Warning.</li>
        <li>Si uno o ambos Beys se desmontan antes de tocar el estadio, sin que haya iniciado la batalla, se considerará fallo de lanzamiento y se penalizará con un Warning, repitiéndose el lanzamiento con los mismos Beys.</li>
        <li>Si el Bey no sale del lanzador al lanzar, si se lanza fuera del área de lanzamiento o si el Bey toca la cúpula del estadio, se considerará fallo de lanzamiento y será penalizado con un Warning, reiniciando la batalla con los mismos Beys.</li>
        <li>Cualquier lanzamiento, tanto tardío como prematuro, que provoque que los Beys toquen el terreno de juego en tiempos muy dispares serán considerados error de lanzamiento y serán sancionados con un Warning, reiniciando la batalla con los mismos Beys.</li>
        <li>Cada 2 Warnings acumulados por un mismo jugador en el mismo combate el oponente recibirá un punto de penalti, reiniciando la batalla en curso con los mismos Beys.</li>
        <li>Si durante el lanzamiento un jugador mueve el estadio y/o el stand/mesa con cualquier parte de su cuerpo o lanzador se le sancionará con un Warning, reiniciando la batalla con los mismos Beys.</li>
        <li>No está permitido mirar por encima del estadio, puede ser peligroso.</li>
        <li>No se debe tocar un Bey dentro del estadio antes de que el árbitro declare un ganador. Interferir de esta u otra manera en el desarrollo del combate, podrá suponer perder el combate a criterio del árbitro.</li>
        <li>Si un Bey sale por la zona de Over/Xtreme sin haber realizado un contacto previo con el Bey del oponente pero habiendo realizado recorrido por el Terreno de juego, el rival recibe un punto de penalti y se reinicia la batalla con los mismos Beys. En caso de que el Bey vaya directo a la zona de Over/Xtreme se considerará error de lanzamiento y el jugador será penalizado con un Warning. La batalla se reiniciará con los mismos Beys.</li>
        <li>No están permitidos los lanzamientos “catapulta” (se considera catapulta cuando el Bey se eleva por encima de los 20 cm permitidos justo en el momento de lanzar y es liberado a esa altura o superior, acompañado de un descenso brusco de la mano para aumentar la potencia del lanzamiento) se penalizará con un Warning y se reiniciará la batalla con los mismos Beys.</li>
        <li>Si una batalla termina en empate, el combate se repetirá utilizando los mismos Beys hasta obtener un final claro.</li>
        <li>Si una puntuación no puede ser determinada se repetirá la batalla. Ej: Si el Bey sale por la parte superior del estadio, la batalla se considera nula y se reinicia.</li>
        <li>La resolución de puntos se realizará en orden de sucesión de eventos, si uno de los eventos no se finaliza con éxito, pasaría al siguiente en la cola de eventos.</li>
        <li>Ejemplo: Si durante una ronda un Bey provoca el Burst Finish de su oponente y, de forma inmediatamente posterior, sale del estadio por Over Finish, se considerará válido el Burst Finish, al haber finalizado con éxito antes que la salida del estadio.</li>
        <li>Está prohibido cualquier comportamiento que pueda causar molestias a quienes le rodean o que pueda interferir con el funcionamiento del evento o torneo.</li>
        <li>Se prohíben los consejos que puedan interferir con un juego limpio o el apoyo excesivo que pueda intimidar a los jugadores.</li>
        <li>El árbitro tiene la autoridad final sobre cualquier decisión tomada durante el combate. En caso de que haya un juez presente en el torneo, un jugador podrá solicitar una segunda opinión. La resolución de dicha consulta deberá realizarse después de que el juez haya escuchado a ambos jugadores y al árbitro presente en el combate. La decisión del juez será definitiva.</li>
        <li>Gana quien llegue primero a 4 puntos siendo 4 el máximo de puntos obtenible.</li>
    </ul>

    <h3 class="mt-4">📅 SOBRE LOS TORNEOS</h3>
    <ul>
        <li>Número mínimo de bladers para realizar un torneo: 4</li>
        <li>Antes de cada enfrentamiento el árbitro deberá revisar los decks de los participantes y se deberán mostrar a cámara (no necesariamente al rival) para que se vean bien los combos de ambos jugadores.</li>
        <li>Se considerará árbitro de un combate al árbitro o juez que regule dicho enfrentamiento. Si el torneo no cuenta con una figura oficial de la SBBL, se considerará árbitro en funciones a cualquier persona voluntaria que desempeñe este papel tras la correcta lectura y entendimiento del presente reglamento.</li>
        <li>El orden de los Beys no puede ser modificado una vez haya sido establecido. En caso de que ninguno de los dos jugadores llegue a 4 puntos después de realizar la tercera batalla, se reorganizará el deck para seguir combatiendo hasta que alguno de los dos llegue a la puntuación de victoria.</li>
        <li>Todo el torneo debe jugarse el mismo día.</li>
        <li>Máximo número de torneos al mes: 2 (siendo en semanas naturales distintas, de lunes a domingo).</li>
        <li>Todos deben estar inscritos desde la web del evento.</li>
        <li>El cuadro del torneo debe gestionarse en Challonge.com. <a href="https://www.youtube.com/watch?v=yJWkLDigm-c&list=PLMfAGE1LtmR0Y1HHUvVAIjmsSBLLbcP3b&index=2">Link del tutorial aquí</a>.</li>
        <li>Se admiten exclusivamente los formatos de eliminación simple y doble para los torneos de ranking.</li>
        <li>En Challonge, la máxima puntuación a registrar del ganador de un combate es 4.</li>
        <li>Los estadios permitidos para torneos de ranking son el estadio Xtreme Beystadium de Hasbro, el estadio standard de Takara Tommy/Hasbro “Xtreme Stadium” (BX-10) y el estadio de Takara Tommy “Infinity Stadium” (BX-46).</li>
        <li>El torneo deberá desarrollarse íntegramente en un único tipo de estadio, desde el comienzo hasta la finalización del mismo, independientemente de la cantidad de estadios utilizados.</li>
        <li>Antes de iniciar el torneo, deben añadirse los participantes al torneo generado en Challonge. Para facilitar esta tarea puede utilizarse la opción “copiar nombres” en la parte superior de la lista de participantes de la página del torneo, añadiendoles al Challonge mediante la opción “Bulk Add”.</li>
        <li>Ha de compartirse el link del Challonge del torneo por el canal del servidor de Discord dedicado a ello antes de iniciar el torneo.</li>
        <li>Introducir resultados en vivo en Challonge.</li>
        <li>Finalizar el torneo en Challonge para mostrar los ganadores.</li>
        <li>En caso de que un torneo quede invalidado, el participante perderá uno de los dos tickets de participación mensual.</li>
        <li>Para que el torneo puntúe debe ser grabado desde el principio del torneo hasta el final sin cortes desde una perspectiva isométrica permitiendo ver claramente:
            <ul>
                <li>El estadio completo</li>
                <li>Ambos Beys durante la batalla</li>
                <li>La puntuación de los jugadores y warnings acumulados con tarjetas o marcador</li>
            </ul>
        </li>
        <li>Si hay varios estadios, se grabarán cada uno de ellos.</li>
        <li>Una vez finalizado el torneo, se introducirán los puestos en el listado de participantes en la web y se adjuntará el vídeo del torneo (link de Youtube, Drive o similares) y el link de Challonge. Si esto no se hace o el video no es accesible para su revisión, no se procederá a la revisión del torneo.</li>
        <li>Únicamente se considerará válido como vídeo de un estadio del torneo el enlace a un streaming si al menos un juez estuvo presente en dicho estadio. En cualquier otro caso, el vídeo adjunto deberá ser una grabación, no un directo.</li>
        <li>Calidad mínima de video 720p e idealmente 60 fps.</li>
        <li>Para que el vídeo sea válido para puntuar, deberá enviarse antes de que finalice el mes en que se celebra el torneo.</li>
        <li>El torneo será revisado por tres árbitros y jueces externos a la comunidad donde se haya jugado este, y se valorará si es validable o se han cometido errores importantes que alteren los puestos finales de los jugadores.</li>
        <li>Se considera FALLO GRAVE cualquier incumplimiento del reglamento que:
            <ul>
                <li>Impida la correcta revisión del torneo,</li>
                <li>Derivado de una decisión arbitral incorrecta, provoque que el resultado final del torneo (clasificación, posiciones o podio) no sea el que debería haberse producido conforme al reglamento.</li>
            </ul>
        </li>
        <li>Un fallo grave supondrá la invalidación del torneo.</li>
        <li>Se considera FALLO LEVE todo incumplimiento del reglamento que no impida la revisión del torneo ni altere directamente su resultado final.</li>
        <li>Los fallos leves reiterados se considerarán FALLO GRAVE cuando afecten de forma sistemática al desarrollo del torneo.</li>
        <li>A efectos de cómputo, 3 fallos leves del mismo tipo equivaldrán a 1 fallo grave.</li>
        <li>Los torneos que se creen a través de la web serán de carácter gratuito, salvo aquellos organizados con fines recaudatorios destinados a la comunidad de la SBBL. De forma excepcional, podrá establecerse un precio de participación en los siguientes supuestos:
            <ul>
                <li>Cuando, por causas excepcionales, sea necesario alquilar un local, estableciéndose en este caso un precio simbólico y de carácter voluntario, destinado exclusivamente a cubrir dicho gasto.</li>
            </ul>
        </li>
        <li>Siga las normas y disfrute de su participación en eventos y competiciones. El incumplimiento reiterado de estas podrá conllevar la descalificación del torneo o expulsión de la liga.</li>
        <li>Si no puede utilizar sus Beys según el criterio del personal de la SBBL, deberá abstenerse de participar en el evento o torneo.</li>
        <li>Al inscribirse a un torneo de ranking se considerará que se ha leído y comprendido la normativa.</li>
    </ul>

    <div class="table-responsive">
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
                <tr><td>4-5</td><td>2</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td></tr>
                <tr><td>6-8</td><td>3</td><td>2</td><td>1</td><td>1</td><td>1</td><td>1</td><td>1</td></tr>
                <tr><td>9-16</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>1</td><td>1</td></tr>
                <tr><td>17-24</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td><td>1</td></tr>
                <tr><td>25-32</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td><td>1</td></tr>
                <tr><td>33 en adelante</td><td>7</td><td>6</td><td>5</td><td>4</td><td>3</td><td>2</td><td>1</td></tr>
            </tbody>
        </table>
    </div>

    <h3 class="mt-4">SOBRE EL FORMATO</h3>
    <ul>
        <li>Formato 3on3 (todos los jugadores deben llevar un deck compuesto por 3 Beys).</li>
        <li>No se pueden repetir piezas entre Beys en un mismo deck.</li>
        <li>Todas las piezas que componen el Bey deben ser oficiales (marcas Takara Tommy o Hasbro).</li>
        <li>El árbitro podrá desensamblar el Bey de un jugador para comprobar cada parte.</li>
        <li>Antes de empezar la partida, se decidirá el orden en el que se van a utilizar los Beys ocultándoselo al rival.</li>
        <li>Se jugarán los Beys de izquierda a derecha (1ºvs1º, 2ºvs2º y 3ºvs3º).</li>
        <li>Los combates se jugarán a 4 puntos. El primer blader en alcanzarlos, será el vencedor.</li>
        <li>En caso de que ninguno de los dos jugadores llegue a 4 puntos después de realizar la tercera batalla, se reorganizará el deck para seguir combatiendo hasta que alguno de los dos llegue a la puntuación necesaria para la victoria.</li>
    </ul>

    <h3 class="mt-4">SOBRE LOS DUELOS DE EQUIPO</h3>
    <ul>
        <li>Los duelos de equipos forman parte de un ranking independiente.</li>
        <li>Cada equipo deberá estar formado por un mínimo de 3 bladers y un máximo de 6.</li>
        <li>Para registrar un equipo vaya a la sección Equipos en la web <a href="https://sbbl.es/equipos">sbbl.es</a>.</li>
        <li>Siga las instrucciones de estilo para crear el logo de su equipo.</li>
        <li>Cada blader usará un único Bey durante el duelo.</li>
        <li>En el equipo no podrán repetirse piezas entre los integrantes que vayan a jugar el duelo.</li>
        <li>Para que un duelo pueda llevarse a cabo, deberán estar presentes al menos 3 miembros de cada equipo.</li>
        <li>Antes de iniciar el duelo deberán decidir el orden en el que cada miembro participará hasta acabar el duelo.</li>
        <li>Dicho orden y el Bey que utilizará cada blader deberá ser apuntado. Antes de iniciar el duelo, el orden se mostrará al equipo rival para que haya transparencia en el desarrollo del mismo.</li>
        <li>Una vez establecido el orden, cada blader se enfrentará con su rival con su posición correspondiente, 1ºvs1º, 2ºvs2º, 3ºvs3º, etc.</li>
        <li>Si uno de los dos equipos tiene más participantes, el equipo con menos participantes volverá a jugar con el 1º, 2º, etc. (sin cambiar el orden) hasta que todos los miembros del otro equipo hayan jugado.</li>
        <li>El equipo con más puntuación al acabar todos los combates ganará un punto en el ranking de equipos y deberá introducir el resultado en la web y enviar el video del duelo completo sin cortes.</li>
        <li>En caso de que haya un empate a puntos, los dos capitanes se enfrentarán con su combo seleccionado en una única batalla de desempate.</li>
        <li>En caso de no estar presente un capitán, la batalla de desempate la librará el jugador seleccionado como 1º en el duelo.</li>
        <li>El duelo ha de ser grabado de principio a fin y se mandará a revisiones@sbbl.es para su revisión.</li>
        <li>Los videos deben ser enviados para su revisión antes de que finalice el mes en el que se ha realizado el duelo.</li>
    </ul>
</div>

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
