@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            
      <div class="container mt-12">
        <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Reglamento general</h3>

        <ol>
            <li>Número mínimo de bladers para realizar un torneo: 4.</li>
            <li>Se tienen que jugar todas las rondas del torneo el mismo día que comienza.</li>
            <li>Para puntuar en un torneo, todos los participantes deben inscribirse con el botón que aparece debajo del nombre en la página del evento.</li>
            <li>El torneo se creará en la web de <a href="https://challonge.com" target="_blank">challonge.com</a></li>
        <li>Ajustes del Torneo:</li>
            Formato: Single elimination</br>
            Incluir un partido para el 3er lugar</br>
            Barajar semillas antes de iniciar el torneo
            <li>Puntuación en torneo: *Floor = Redondear a la baja</li>
            Todo participante recibirá 1 punto de participación.</br>
            La puntuación para el primer puesto se añadirá con la siguiente fórmula: Floor(Número de participantes/4)+2</br>
            La puntuación para el segundo puesto se añadirá con la siguiente fórmula: Floor(Número de participantes/4)+1</br>
            La puntuación para el tercer puesto se añadirá con la siguiente fórmula: Floor(Número de participantes/4)
        <li>Ejemplos:</li>
        El ganador de un torneo de 6 participantes sería Floor(6/4) = 1+2 =3 +1 de participación = 4 puntos en total.</br>
        El ganador de un torneo de 8 jugadores sería: Floor(8/4) = 2+2 =4 +1 de participación = 5 puntos en total.
        <li>Cuando vaya a empezar el torneo o se conozcan los participantes confirmados se deben introducir en el torneo creado en la web de challonge.</li>
        <li>Una vez añadidos los participantes y barajadas las semillas se debe facilitar el código "iframe" que se mostrará pulsando un icono con el símbolo "&lt;&gt;" en la ventana principal del torneo.</li>
        <li>Después de cada duelo se deben ir introduciendo los resultados en el torneo ya que este se actualizará en vivo para que los que no hayan podido asistir puedan ir viendo cómo va el torneo.</li>
        <li>Una vez se hayan jugado todos los duelos, en la página del torneo hay que seleccionar en finalizar el torneo para que se muestre el ganador del mismo.</li>
        <li>No se pueden repetir piezas en un mismo deck ni modificarlas de ninguna forma, ya sea pintura, pegamento para arreglarla, pegatinas, etc.</li>
        <li>El evento debe ser en un lugar público y/o accesible.</li>
        <li>Para que el torneo cuente como oficial tiene que ser grabado desde el principio del torneo hasta el final sin cortes, desde una perspectiva isométrica y sin cortes (A excepción de los torneos que cuenten con la participación de un árbitro designado por la SBBL).</li>
        <li>Todas las piezas que componen el bey deben ser oficiales</li>
        <li>Antes de cada duelo el juez debe revisar el deck. El orden de los beys no puede ser modificado una vez haya sido revisado. En caso de no haber juez debe mostrarse a la cámara anunciando el jugador propietario del deck.</li>
        <li>Las decisiones de los jueces son definitivas.</li>
        <li>El juez podrá desensamblar el bey del compartimento del jugador, comprobar cada parte y las volverá a montar.</li>
        <li>Si un jugador tiene alguna duda sobre un bey, debe informar al juez en el momento. No se aceptarán opiniones una vez iniciado el juego ni de ninguna persona distinta a los jugadores o juez.</li>
        <li>Lanza diciendo "¡Tres, dos, uno, go shoot!"</li>
        <li>No lances sobre el bey en el suelo del estadio. Si el juez determina que se hizo intencionalmente, podrá ser descalificado. Es decir, si un bey ya ha tocado el suelo del estadio no está permitido lanzar sobre él.</li>
        <li>La batalla comenzará cuando los bey pasen por el área de lanzamiento y toquen el propio estadio. Si la batalla no comienza debido a que los bey chocan entre sí o explotan antes de tocar el estadio, la batalla se reiniciará.</li>
        <li>Si el bey no se sale del lanzador al lanzar, si el bey se cae después de prepararse o si lanza fuera del área de tiro, se considerará un error de lanzamiento.</li>
        <li>Los lanzamientos realizados antes o después de la cuenta del juez también serán considerados errores de lanzamiento.</li>
        <li>Si se cometen tres errores de lanzamiento en la misma batalla, el oponente recibirá 1 punto y pasará a la siguiente batalla.</li>
        <li>Está prohibido tocar el estadio y los espacios dentro del estadio hasta que lo autorice el juez. Tampoco está permitido mirar por encima del estadio.</li>
        <li>No tocar el bey dentro del estadio antes de que el juez declare al ganador. Si se toca, el partido se perderá a criterio del juez.</li>
        <li>La interferencia maliciosa en la batalla resultará en la descalificación a discreción del juez.</li>
        <li>Se llevará a cabo una batalla entre los números en el orden decidido por cada uno.</li>
        <li>En caso de empate, se volverá a luchar con el mismo bey usado en la batalla.</li>
        <li>Si no se determina el ganador después de tres batallas, el orden de los bey se reorganizará y la partida continuará.</li>
        <li>Si participa en un evento/competición, se considerará que ha comprendido la normativa.</li>
        <li>Si en un torneo se detectan 3 o más irregularidades el torneo no será puntuado.</li>
        <li>Se prohíben los consejos que puedan interferir con un juego limpio o el apoyo excesivo que pueda intimidar a los jugadores.</li>
        <li>Está prohibido cualquier comportamiento que pueda causar molestias a quienes le rodean o que pueda interferir con el funcionamiento del evento o torneo.</li>
        <li>Siga las regulaciones y diviértase participando en eventos y competencias. El incumplimiento de las normas puede resultar en la descalificación.</li>
        <li>Si no puede utilizar Beyblade según el criterio del personal y los jueces de la SBBL, abstenerse de participar en el evento o torneo.</li>
        <li>Para enviar el vídeo puedes enviarlo por WeTransfer o compartirlo por drive al correo <a href="mailto:sbbl.oficial@gmail.com">sbbl.oficial@gmail.com</a> añadiendo en el título el nombre del torneo, lugar del torneo y fecha (Algo como “CopaBurstMadrid15septiembre”)</li>
        <li>La fecha del torneo será la fecha en la que la SBBL reciba el video, por lo tanto si tienes algún torneo grabado envíalo antes de que termine el mes para que cuente dentro de este y no en el siguiente.</li>
        </ol>
        </div>

<div class="col-md-6 col-sm-12">
        <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Beyblade Burst</h3>
        <ol>
            <li>Número máximo de eventos oficiales al mes por cada blader: 2. (Con límite de 1 a la semana)</li>
            <li>El torneo será 3on3 o 5g (Todos los participantes deben llevar 3 combos si el torneo es 3on3 o 5 combos, según hayan decidido los participantes previamente).</li>
            <li>El Bey Estadio utilizado tendrá que ser de Hasbro (Pro series) o de Takara Tomy (Standard y DB)</li>
            <li>Se podrán utilizar beys de Hasbro o Takara Tomy</li>
            <li>Los combates serán a 3 puntos</li>
            <li>Spin finish 1 punto</li>
            <li>Over finish 2 puntos</li>
            <li>Burst finish 2 puntos</li>
        </ol>
</div>
        
<div class="col-md-6 col-sm-12">
        <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Beyblade X</h3>
        <ol>
            <li>Número máximo de eventos oficiales al mes por cada blader: 2 (Con límite de 1 a la semana)</li>
            <li>El torneo será 3on3 (Todos los participantes deben llevar 3 combos)</li>
            <li>El Bey Estadio utilizado tendrá que ser de Takara Tomy (Extreme y los futuros)</li>
            <li>Los beys a utilizar tendrán que ser Takara Tomy (faltando por confirmar la distribuidora oficial internacional)</li>
            <li>Los combates serán a 4 puntos</li>
            <li>Spin finish 1 punto</li>
            <li>Over finish 2 puntos</li>
            <li>Burst finish 2 puntos</li>
            <li>Extreme finish 3 puntos</li>
        </ol>
</div>




            <div class="col-md-6 col-sm-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos por parejas</h3>
                <ol>
                    <li>Un duelo por parejas es un combate de 2vs2 en el cual cada blader contará con un único combo a su elección.</li>
                    <li>El duelo será a 3 puntos por lo que quien llegue antes a  3 o más puntos ganará el duelo.</li>
                    <li>Tanto ganar por energía, salida de pista o burst contará 1 punto de los 3 necesarios para ganar el duelo.</li>
                    <li>Los estadios permitidos son: Coloso, Decagone, B-50 Wide y B-00 Big Beystadium.</li>
                    <li>Cada parejas de bladers puede participar en un total de 1  duelos semanal.</li>
                    <li>El duelo tiene que ser grabado, enseñando los combos antes de empezar el combate, y desde el lanzamiento hasta que uno de los beys gane. (Para evitar conflictos futuros y comprobar que los combos son legales).</li>
                </ol>
            </div>
            <div class="col-md-6 col-sm-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Torneo de Resistencia</h3>
                <p>El torneo de resistencia es un evento nacional online que se realiza cada 2 o 3 meses para hacer algo junto con toda la comunidad española tanto para Burst como X.</p>
                <p>Este consiste en que cada blader realiza un combo a su elección con piezas de la generación burst/X pudiendo ser estas de Takara Tomy, Hasbro, o marcas fake que se asemejen lo máximo posible a las anteriores mencionadas.</p>
                <p>Una vez tenga el combo, el blader tiene que grabarse lanzándolo <b>en un estadio</b> y a ser posible con un cronómetro o reloj al lado para verificar que el video no ha sufrido ninguna manipulación.</p>
                <p>Este video tiene que ser compartido a la dirección de correo <b>sbbl.oficial@gmail.com</b>.</p>
                <p>Todos los participantes del torneo recibirán 1 punto en el ranking ya que creemos que lo importante de este tipo de torneo es la participación y ganas de querer hacer algo en comunidad con nuestros compañeros.</p>
                <p>Y por supuesto, el ganador recibirá 1 punto extra para el ranking.</p>
            </div>
<!--<div class="col-md-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Reglamento Generations</h3>
                <ol>
                    <li>Las reglas son las mismas que con los duelos normales (puntos en el combate, grabación, etc) pero con algunas excepciones</li>
                    <li>Los duelos están limitados a uno cada 7 días</li>
                    <li>Los decks están compuestos por combos en los que cada uno de ellos tiene que tener un layer de diferente generación (pudiendo llevar por ejemplo un disco de db con un layer de choz)</li>
                    <li>Generaciones: Single/Dual/God Layer, Remakes/ChoZ, GT, Sparking y DB/BU</li>
                    <li>Hay 5 grupos por lo que cada combo tendrá un layer de cada uno de ellos</li>
                    <li>Para competir en esta modalidad es necesario crear un duelo desde <a href="{{ route('generations.create') }}">este apartado</a></li>
                    <li>El sistema aleatorizará los emparejamientos debiendo seguir el orden establecido enfrentando las diferentes categorías entre sí</li>
                    <li>En el duelo se enfrentarán todos los combos y al final se realizará un recuento de cada emparejamiento para determinar al ganador siendo 10 el máximo de puntos que un blader puede ganar si sus combos ganan todos por burst o fuera de pista</li>
                    <li>Al finalizar el duelo, el blader creador del mismo tendrá que introducir los resultados poniendo primero su puntuación, un guión y la puntuación del jugador 2</li>
                    <li>Por ejemplo, si yo he creado el duelo y he conseguido 2 puntos y mi rival 4, debo introducir 2 - 4 en el resultado</li>
                    <li>Una vez hecho esto se tiene que clicar en el botón de cerrar duelo para que cambie de estado a enviado</li>
                    <li>El duelo aparecerá como enviado a los administradores que en cuanto revisen que la grabación es correcta, confirmarán el duelo y se le sumará 1 punto al ganador en el ranking exclusivo</li>
                </ol>
            </div>
        </div>
    </div>
-->

    <div class="container-fluid" style="background-color: red;">
        <div class="row">
            <div class="col-md-12 mt-3 mb-3 text-center">
                <p style="color: white; font-size: 1.4em;">Estas normas están basadas en las de la WBBA por lo que si hay alguna duda de algo que no esté contemplado arriba no dudes en contactar con sbbl.oficial@gmail.com o escribirlo por nuestro <a style="color: white; font-weight: bold;" target="_blank" href="https://discord.gg/vXhY4nGSwZ"> Discord</a></p>
                <p style="color: white; font-size: 1.4em;">Si se observa alguna irregularidad que no esté contemplada en nuestras reglas se revisará el reglamento oficial de la WBBA para aclarar cualquier duda</p>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
