@extends('layouts.app')

@section('content')
    <div id="exTab2" class="container">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#1" data-toggle="tab" class="m-2"><h3>Generations 1</h3></a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="1">
                @php
                    $supremo = 0;
                @endphp
                <div class="row">
                @foreach ($bladers as $blader)
                    <div class="col-md-12 py-2 mb-2">
                        <div class="card" style="{{ ($supremo < 3) ? 'border:2px solid #FFDC00;' : '' }}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h3><b>{{ $blader->user->name }}</b></h3>
                                        <h4>Región: {{ ($blader->region) ? $blader->region->name : 'No definida'}}</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <h4 style="right: 0px;">Puntos: {{ $blader->points_g1 }}</h4>
                                    </div>
                                </div>
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
