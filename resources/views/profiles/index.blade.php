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
                @foreach ($bladers as $blader)
                    <div class="col-md-3 py-2 mb-2">
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
                    </div>
                        @php
                            $supremo += 1;
                        @endphp
                @endforeach
            </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
@endsection
