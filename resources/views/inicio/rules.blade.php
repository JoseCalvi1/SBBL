@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Reglamento Torneos</h3>
                <ol>
                    <li> Número máximo de eventos oficiales al mes: <b>2</b>.</li>
                    <li> Número mínimo de blader: <b>4</b>.</li>
                    <li> Los duelos serán a <b>3 puntos</b> (el primer blader en conseguir 3 puntos ganará).</li>
                    <li> Final superviviente: <b>1 punto</b> | Final fuera de estadio: <b>1 puntos</b> | Burst Finish: <b>2 puntos</b> (Si el estadio es el DB el final fuera de pista sumará <b>2 puntos</b>).</li>
                    <li> Número máximo de combos por persona: <b>5</b>.</li>
                    <li> De dichos combos, <b>solo uno podrá ser de doble giro</b>.</li>
                    <li>Los beys de doble giro podrán cambiarse <b>antes de su encuentro</b> independientemente a su posición inicial. Si ambos beys pueden cambiar de giro, ambos contendientes <b>decidirán una señal</b> para cambiar y otra para dejarlo como está.</li>
                    <li>En caso de empate entre 2 combos, <b>se repetirá el combate entre ellos</b> y, si vuelve a haber empate, <b>se pasará a los siguientes combos</b> del orden previamente establecido.</li>
                    <li>Para participar en la puntuacion de la liga, <b>el jugador debe estar registrado en la web oficial de la SBBL</b>.</li>
                    <li>Es necesario <b>enviar vídeos y/o fotos del evento</b> a los admin de la SBBL.</li>
                    <li>El evento debe ser en un lugar <b>público y accesible</b>.</li>
                    <li>El formato del torneo será de una <b>liga de todos contra todos</b>.</li>
                    <li>Los resultados del evento deben <b>apuntarse y remitirse</b> a los admin de la SBBL.</li>
                    <li>Las victorias y derrotas de los bladers registrados serán recogidas en sus perfiles en la web. Estos datos servirán para dar una posición en el SBBL-Rank.</li>
                    <li>Cada victoria en un evento oficial <b>sumará un 1 pto</b> en el SBBLRank. | Cada evento ganado <b>sumará 2 puntos más</b> en el SBBLRank.</li>
                    <li>El BeyEstadio utilizado será libre elección de los participantes en el evento. Ya sea  TT, Hasbro o cualquier otra marca, siempre que cuente con las <b>medidas oficiales</b>.</li>
                    <li>Se podrá competir con beys tanto TT como Hasbro, de cualquier sistema dentro de la generación Burst, <b>siempre y cuando las piezas cumplan con la normativa y sean lo más similares a las originales</b>.</li>
                    <li><b>No se pueden repetir piezas en un mismo deck ni modificarlas</b>.</li>
                </ol>
            </div>
            <div class="col-md-6 col-sm-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Reglamento Duelos</h3>
                <ol>
                    <li>Un duelo es un combate individual por decks entre <b>dos bladers con las mismas normas y puntuación que en los torneos</b> (3g o 5g)</li>
                    <li>Cada blader puede participar en un total de <b>6 duelos mensuales</b></li>
                    <li>Dos bladers pueden enfrentarse entre ellos un máximo de <b>una vez a la semana</b></li>
                    <li>Al ganador del duelo se le <b>sumará 1 punto</b> en el SBBL-rank</li>
                    <li>El duelo tiene que <b>ser grabado, enseñando los combos antes de emepezar cada combate, y desde el lanzamiento hasta que uno de los beys gane</b>. (Para evitar conflictos futuros y comprobar que los combos son legales)</li>
                </ol>
            </div>
            <div class="col-md-6 col-sm-12">
                <h3 class="titulo-categoria text-uppercase mb-4 mt-4">Reglamento Resistencia</h3>
                <p>El torneo de resistencia es un evento nacional online que se realiza cada 2 o 3 meses para hacer algo junto con toda la comunidad española.</p>
                <p>Este consiste en que cada blader realiza un combo a su elección con piezas de la generación burst pudiendo ser estas de Takara Tomy, Hasbro, o marcas fake que se asemejen lo máximo posible a las anteriorexs mencionadas.</p>
                <p>Una vez tenga el combo, el blader tiene que grabarse lanzándolo <b>en un estadio</b> y a ser posible con un cronómetro o reloj al lado para verificar que el video no ha sufrido ninguna manipulación.</p>
                <p>Este video tiene que ser compartido a la dirección de correo <b>sbbl.oficial@gmail.com</b>.</p>
                <p>Todos los participantes del torneo recibirán 1 punto en el ranking ya que creemos que lo importante de este tipo de torneo es la participación y ganas de querer hacer algo en comunidad con nuestros compañeros.</p>
                <p>Y por supuesto, el ganador recibirá 1 punto extra para el ranking.</p>
            </div>
        </div>
    </div>

    <div class="container-fluid" style="background-color: red;">
        <div class="row">
            <div class="col-md-12 mt-3 mb-3 text-center">
                <p style="color: white; font-size: 1.4em;">Estas normas están basadas en las de la WBBA por lo que si hay alguna duda de algo que no esté contemplado arriba no dudes en contactar con sbbl.oficial@gmail.com o escribirlo por nuestro <a style="color: white; font-weight: bold;" target="_blank" href="https://discord.gg/ve7dgpCF9x"> Discord</a></p>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
