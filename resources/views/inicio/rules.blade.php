@extends('layouts.app')

@section('title', 'Reglamento Beyblade X')

@section('content')
    <div class="container">
        <div class="row">

            <div class="container mt-12">
                <div class="row">
                    <div class="col-12">
                        <h3 class="text-white">Descargar las reglas completas <a href="/images/sbblrules.pdf" target="_blank">en este enlace</a></h3>

                    </div>
                    <div class="col-12">
                        <img src="/images/sbblrules.png" class="img-fluid w-100" alt="Imagen 1">
                    </div>
                    <div class="col-12">
                        <img src="/images/sbblrules2.png" class="img-fluid w-100" alt="Imagen 2">
                    </div>
                </div>
            </div>

            <div class="container-fluid" style="background-color: red;">
                <div class="row">
                    <div class="col-md-12 mt-3 mb-3 text-center">
                        <p style="color: white; font-size: 1.4em;">Estas normas están basadas en las de la WBBA por lo que
                            si hay alguna duda de algo que no esté contemplado arriba no dudes en contactar con
                            sbbl.oficial@gmail.com o escribirlo por nuestro <a style="color: white; font-weight: bold;"
                                target="_blank" href="https://discord.gg/vXhY4nGSwZ"> Discord</a></p>
                        <p style="color: white; font-size: 1.4em;">Si se observa alguna irregularidad que no esté
                            contemplada en nuestras reglas se revisará el reglamento oficial de la WBBA para aclarar
                            cualquier duda</p>
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
