@extends('layouts.app')

@section('content')
    <div id="exTab2" class="container">
        <ul class="nav nav-tabs">
            <li>
                <a href="#1" data-toggle="tab" class="m-2"><h3>Season 1</h3></a>
            </li>
            <li class="active">
                <a href="#2" data-toggle="tab" class="m-2"><h3>Season 2</h3></a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane" id="1">
                @php
                    $supremo = 0;
                @endphp
                <div class="row">
                @foreach ($bladers as $blader)
                    <div class="col-md-3 py-2 mb-2">
                       <a href="" style="width:100%;" id="editCompany" data-toggle="modal" data-target='#practice_modal{{ $blader->id }}'>
                        <div class="card" style="{{ ($supremo < 4) ? 'border:2px solid #FFDC00;' : '' }}">
                            @if ($blader->imagen)
                                <img src="/storage/{{ $blader->imagen }}"  class="card-img-top">
                            @else
                                <img src="../images/default_user.jpg"  class="card-img-top">
                            @endif

                            <div class="card-body">
                                <h3 class="text-center"><b style="color:#FFDC00;padding">{{ ($supremo < 4) ? '4 SUPREMOS' : '' }}</b></h3>
                                <h3><b>{{ $blader->user->name }}</b></h3>
                                <h4>Región: {{ ($blader->region) ? $blader->region->name : 'No definida'}}</h4>
                                <h4>Puntos: {{ $blader->points }}</h4>
                            </div>
                        </div>
                      </a>
                    </div>
                        @php
                            $supremo += 1;
                        @endphp
                @endforeach
            </div>
            </div>

            <div class="tab-pane active" id="2">
                @php
                    $supremo = 0;
                @endphp
                <div class="row">
                @foreach ($bladers_s2 as $blader)
                    <div class="col-md-3 py-2 mb-2">
                        <a href="" style="width:100%;" id="editCompany" data-toggle="modal" data-target='#practice_modal{{ $blader->id }}'>
                        <div class="card" style="{{ ($supremo < 4) ? 'border:2px solid #FFDC00;' : '' }}">
                            @if ($blader->imagen)
                                <img src="/storage/{{ $blader->imagen }}"  class="card-img-top">
                            @else
                                <img src="../images/default_user.jpg"  class="card-img-top">
                            @endif

                            <div class="card-body">
                                <h3 class="text-center"><b style="color:#FFDC00;padding">{{ ($supremo < 4) ? '4 SUPREMOS' : '' }}</b></h3>
                                <h3><b>{{ $blader->user->name }}</b></h3>
                                <h4>Región: {{ ($blader->region) ? $blader->region->name : 'No definida'}}</h4>
                                <h4>Puntos: {{ $blader->points_s2 }}</h4>
                            </div>
                        </div>
                    </a>
                    </div>
                        @php
                            $supremo += 1;
                        @endphp
                @endforeach
            </div>
            </div>
        </div>
    </div>

    @foreach ($bladers as $blader)
    <div class="modal fade" id="practice_modal{{ $blader->id }}">
        <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-body">
                    <h4 class="titulo-categoria text-uppercase mb-4 mt-4">Palmarés</h4>
                    <div class="row">
                    @if (count($blader->trophies) != 0)
                        @foreach ($blader->trophies as $trophy)
                            <div class="col-md-6">
                                <p class="font-weight-bold">{{ $trophy->pivot->count }}x<svg style="@if($trophy->id == 1 || $trophy->id == 4) fill:gold; @elseif($trophy->id == 2 || $trophy->id == 5) fill:silver; @else fill:rgba(205, 127, 50); @endif" xmlns="http://www.w3.org/2000/svg" width="20" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M572.1 82.38C569.5 71.59 559.8 64 548.7 64h-100.8c.2422-12.45 .1078-23.7-.1559-33.02C447.3 13.63 433.2 0 415.8 0H160.2C142.8 0 128.7 13.63 128.2 30.98C127.1 40.3 127.8 51.55 128.1 64H27.26C16.16 64 6.537 71.59 3.912 82.38C3.1 85.78-15.71 167.2 37.07 245.9c37.44 55.82 100.6 95.03 187.5 117.4c18.7 4.805 31.41 22.06 31.41 41.37C256 428.5 236.5 448 212.6 448H208c-26.51 0-47.99 21.49-47.99 48c0 8.836 7.163 16 15.1 16h223.1c8.836 0 15.1-7.164 15.1-16c0-26.51-21.48-48-47.99-48h-4.644c-23.86 0-43.36-19.5-43.36-43.35c0-19.31 12.71-36.57 31.41-41.37c86.96-22.34 150.1-61.55 187.5-117.4C591.7 167.2 572.9 85.78 572.1 82.38zM77.41 219.8C49.47 178.6 47.01 135.7 48.38 112h80.39c5.359 59.62 20.35 131.1 57.67 189.1C137.4 281.6 100.9 254.4 77.41 219.8zM498.6 219.8c-23.44 34.6-59.94 61.75-109 81.22C426.9 243.1 441.9 171.6 447.2 112h80.39C528.1 135.7 526.5 178.7 498.6 219.8z"/></svg> {{ $trophy->name.' Season '.$trophy->season }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="col-md-6">
                            <p>No hay registros</p>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
