@extends('layouts.app')

@section('content')
<div class="container py-4 text-white">
  <h2>Mis tickets</h2>
  <ul>
  @foreach(auth()->user()->tickets as $t)
    <li>{{ $t->identifier }} — {{ $t->created_at }}</li>
  @endforeach
  </ul>
</div>
@endsection
