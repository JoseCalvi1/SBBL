@extends('layouts.app')

@section('content')


<div id="exTab2" class="container">
<ul class="nav nav-tabs">
			<li class="active">
                <a href="#1" data-toggle="tab" class="m-2"><h3>Eventos realizados</h3></a>
			</li>
			<li>
                <a href="#2" data-toggle="tab" class="m-2"><h3>Eventos futuros</h3></a>
			</li>
		</ul>

			<div class="tab-content">
			    <div class="tab-pane active" id="1">

                    <div class="container nuevas-recetas">

                        <div class="row">
                                @foreach ($antiguos as $antiguo)
                                        <div class="col-md-4 card m-2" style="padding:0px !important; max-width: 30%;">
                                            <img src="/storage/{{ $antiguo->imagen }}"  class="card-img-top">

                                            <div class="card-body">
                                                <h3>{{ $antiguo->name }}</h3>

                                                <p>{{ $antiguo->location }}</p>
                                                <p><event-date fecha="{{ $antiguo->date }}"></event-date></p>

                                                <a href="{{ route('events.show', ['event' => $antiguo->id]) }}" class="btn btn-primary d-block font-weight-bold text-uppercase">Ver evento</a>
                                            </div>
                                        </div>
                                @endforeach
                        </div>
                    </div>

                </div>
				<div class="tab-pane" id="2">

                    <div class="container nuevas-recetas">

                        <div class="row">
                                @foreach ($nuevos as $nuevo)
                                        <div class="col-md-4 card m-2" style="padding:0px !important; max-width: 30%;">
                                            <img src="/storage/{{ $nuevo->imagen }}"  class="card-img-top">

                                            <div class="card-body">
                                                <h3>{{ $nuevo->name }}</h3>

                                                <p>{{ $nuevo->location }}</p>
                                                <p><event-date fecha="{{ $nuevo->date }}"></event-date></p>

                                                <a href="{{ route('events.show', ['event' => $nuevo->id]) }}" class="btn btn-primary d-block font-weight-bold text-uppercase">Ver evento</a>
                                            </div>
                                        </div>
                                @endforeach
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
