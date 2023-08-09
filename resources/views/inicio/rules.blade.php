@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Reglamento Torneos</h3>
                <ol>
                    <li>Número máximo de eventos oficiales al mes por cada blader: 2. (Con límite de 1 a la semana)</li>
                    <li>Número mínimo de bladers para realizar un torneo: 4.</li>
                    <li>Para puntuar en un torneo, todos los participantes deben inscribirse con el botón que aparece debajo del nombre en la página del evento.</li>
                    <li>Para los duelos de cada ronda se utilizarán las reglas escritas más abajo.</li>
                    <li>El torneo será 3g o 5g (Todos los participantes deben llevar 3 combos si el torneo es 3g o 5 combos si el torneo es 5g, según hayan decidido los participantes previamente).</li>
                    <li>El torneo se creará en la web de challonge.com</li>
                    <li>La configuración del torneo será de Round-Robin y se clasificará por victorias.</li>
                    <li>En ajustes avanzados->tie break->Tie Break #1 se pondrá por diferencia de rondas y en Tie Break #2 se pondrá Participantes ganadores vs empatados.
                    <li>Round-robin significa que el torneo será un todos contra todos en los que cada ronda se compondrá de duelos 1vs1 entre los bladers siendo tantas rondas sean necesarias para que todos se enfrenten con todos una vez.</li>
                    <li>Cuando vaya a empezar el torneo o se conozcan los participantes confirmados se deben introducir en el torneo creado en la web.</li>
                    <li>Una vez añadido los participantes se debe facilitar el código "iframe" que se mostrará pulsando un icono con el símbolo "<>" en la ventana principal del torneo.</li>
                    <li>Después de cada duelo se deben ir introduciendo los resultados en el torneo ya que este se actualizará en vivo para que los que no hayan podido asistir puedan ir viendo como va el torneo.</li>
                    <li>Una vez se hayan jugado todos los duelos, en la página del torneo hay que seleccionar en finalizar el torneo para que se muestre el ganador del mismo.</li>
                    <li>Cada victoria en un evento oficial sumará un 1 punto en el SBBLRank. | Cada evento ganado sumará 2 puntos más en el SBBLRank.</li>
                    <li>El Bey Estadio utilizado podrá ser Takara Tomy, Hasbro o cualquier otra marca, siempre que cuente con las medidas oficiales (Esto significa que no se aceptarán Bey Estadios de Hasbro especiales ni destinados a más de 2 jugadores).</li>
                    <li>Se podrá competir con beys tanto Takara Tomy como Hasbro, de cualquier sistema dentro de la generación Burst pudiendo ser fakes con la forma idéntica a los originales.</li>
                    <li>No se pueden repetir piezas en un mismo deck ni modificarlas.</li>
                    <li>El evento debe ser en un lugar público y/o accesible.</li>
                    <li>Para que el torneo cuente como oficial tiene que ser grabado cada duelo tal como aparece en el reglamento de los duelos.</li>
                </ol>
            </div>
            <div class="col-md-12" id="duelos">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Duelos</h3>
                <ol>
                    <li>Un duelo es un combate entre dos bladers.</li>
                    <li>Los duelos serán de 3g o 5g (Cada blader tiene que llevar 3 o 5 combos)</li>
                    <li>Un bey o combo está constituido por un layer, un disco y un driver. Deberá usarse un lanzador oficial de Takara Tomy o Hasbro (no se permitirán modificaciones de la cuerda) y ambos Bladers deberán lanzar al mismo tiempo. </li>
                    <li>Si uno de los beys es lanzado más tarde o impacta con el rival antes de tocar el estadio el lanzamiento se considera nulo y el infractor recibirá un "aviso". Al segundo "aviso" el combate se considera perdido y se concederá la victoria de ese combate al rival del infractor y se pasará al siguiente combo. En caso de alcanzar 3 puntos el rival tras el "aviso", se considerará que ha ganado el duelo.</li>
                    <li>Antes de empezar el duelo cada blader debe elegir un orden en el que enfrentará sus combos y mostrarlo al rival una vez ambos hayan decidido el orden.</li>
                    <li>Si el duelo es 3g, antes de empezar el duelo se elige un orden con los 3 combos y se enfrentarán 1ºvs1º, 2ºvs2º y 3ºvs3º si un blader no ha llegado o superado antes los 3 puntos. En el caso de no llegar a los 3 puntos una vez enfrentados los 3 combos, se vuelve a elegir el orden de los 3 beys y se vuelven a enfrentar 1ºvs1º, 2ºvs2º y 3ºvs3º hasta que uno de los bladers llegue o supere los 3 puntos.</li>
                    <li>Si el duelo es 5g, antes de empezar el duelo se elige un orden con los 5 combos y se enfrentarán 1ºvs1º, 2ºvs2º hasta llegar a 5ºvs5º si un blader no ha llegado o superado antes los 3 puntos.</li>
                    <li>De dichos combos, solo uno podrá ser en su totalidad de doble giro, pudiendo llevar un layer de doble giro pero un chip que no lo permita o viceversa, a parte del bey que si se pueda cambiar de giro.</li>
                    <li>Los beys de doble giro podrán cambiarse antes de su combate independientemente a su posición inicial. Si ambos beys pueden cambiar de giro, ambos contendientes decidirán una señal para cambiar y otra para dejarlo como está.</li>
                    <li>En caso de empate entre dos combos, se repetirá hasta que uno de los dos combos se declare ganador.</li>
                    <li>Final superviviente: 1 punto | Final fuera de estadio: 2 puntos | Burst Finish: 2 puntos (Si el estadio es el DB o standard TT el final fuera de pista sumará 2 puntos).</li>
                    <li>Para participar en la puntuación de la liga, el jugador debe estar previamente registrado en la web oficial de la SBBL.</li>
                    <li>Cada blader puede participar en un total de 4 duelos mensuales</li>
                    <li>Dos bladers pueden enfrentarse entre ellos una vez cada 15 días empezando desde el día uno del mes (1 duelo en la primera quincena del mes y 1 duelo en la segunda).</li>
                    <li>Al ganador del duelo se le sumará 1 punto en el SBBL-rank</li>
                    <li>El duelo tiene que ser grabado.</li>
                    <li>La grabación tiene que ser desde el inicio del duelo hasta el final, enseñando los combos completos que se van a utilizar antes de cada combate, el lanzamiento y la resolución del mismo.</li>
                    <li>Si quieres que el duelo se suba a instagram y youtube tiene que ser editado añadiendo un marcador al video y quitando las partes que sobren entre combates.</li>
                    <li>Si lo único que se quiere es puntuar no hace falta edición, solo enviarlo.</li>
                    <li>Para enviar el vídeo puedes enviarlo por WeTransfer o compartirlo por drive al correo sbbl.oficial@gmail.com añadiendo en el título la fecha, duelo/torneo y participantes/lugar del torneo con el resultado (Algo como “Jugador0-0JugadorDuelo15Septiembre”)</li>
                    <li>La fecha del duelo será la fecha en la que la SBBL reciba el video, por lo tanto si tienes algún duelo grabado envíalo antes de que termine el mes para que cuente dentro de este y no en el siguiente.</li>
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
                    <li>A cada integrante de la pareja ganadora del duelo se le sumará 1 punto en el SBBL-rank.</li>
                    <li>El duelo tiene que ser grabado, enseñando los combos antes de empezar el combate, y desde el lanzamiento hasta que uno de los beys gane. (Para evitar conflictos futuros y comprobar que los combos son legales).</li>
                </ol>
            </div>
            <div class="col-md-6 col-sm-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Torneo de Resistencia</h3>
                <p>El torneo de resistencia es un evento nacional online que se realiza cada 2 o 3 meses para hacer algo junto con toda la comunidad española.</p>
                <p>Este consiste en que cada blader realiza un combo a su elección con piezas de la generación burst pudiendo ser estas de Takara Tomy, Hasbro, o marcas fake que se asemejen lo máximo posible a las anteriores mencionadas.</p>
                <p>Una vez tenga el combo, el blader tiene que grabarse lanzándolo <b>en un estadio</b> y a ser posible con un cronómetro o reloj al lado para verificar que el video no ha sufrido ninguna manipulación.</p>
                <p>Este video tiene que ser compartido a la dirección de correo <b>sbbl.oficial@gmail.com</b>.</p>
                <p>Todos los participantes del torneo recibirán 1 punto en el ranking ya que creemos que lo importante de este tipo de torneo es la participación y ganas de querer hacer algo en comunidad con nuestros compañeros.</p>
                <p>Y por supuesto, el ganador recibirá 1 punto extra para el ranking.</p>
            </div>
<div class="col-md-12">
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