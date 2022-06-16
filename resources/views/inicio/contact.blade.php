@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-6 p-5">
                <iframe src="https://discord.com/widget?id=839848018538659840&theme=dark" width="350" height="500" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
            </div>
            <div class="col-md-6 p-5">
                <h2 class="text-center mb-5">Contacta con nosotros</h2>

                <div class="row justify-content-center mt-5" style="margin-right: 0px !important;">
                    <div class="col-md-8">
                    <form method="POST" action="" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="asunto">Asunto</label>

                            <select name="asunto" id="asunto" class="form-control">
                                <option disabled selected>- Selecciona un asunto -</option>
                                <option value="duelo">Duelo</option>
                                <option value="duda">Duda o incidencia</option>
                                <option value="resistencia">Torneo de resistencia</option>
                            </select>

                        </div>

                            <div class="form-group">
                                <label for="mensaje">Mensaje</label>

                                <textarea type="text"
                                    name="mensaje"
                                    rows="5"
                                    class="form-control"
                                    id="mensaje"
                                    placeholder="Escribe aquí tú mensaje"
                                    value=""
                                    ></textarea>

                            </div>

                            <div class="form-group">
                                <input type="submit" class="btn btn-primary" value="Enviar">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
