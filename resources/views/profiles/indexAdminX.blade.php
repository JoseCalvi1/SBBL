@extends('layouts.app')

@section('content')
<div class="py-4">
    <h2 class="text-center mb-2 text-white">Usuarios Beyblade X</h2>

    <div class="col-md-10 mx-auto bg-white p-3" style="background-color:transparent !important">
        <div class="table-responsive">
            <table class="table" style="color: white !important">
                <thead class="bg-primary text-light">
                    <tr>
                        <th scole="col">Nombre</th>
                        <th scole="col">Email</th>
                        <th scole="col">Region</th>
                        <th scole="col">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($profiles as $profile)
                        <tr>
                            <td>{{ $profile->user->name }}</td>
                            <td>{{ $profile->user->email }}</td>
                            <td>
                                @if ($profile->region)
                                    {{ $profile->region->name }}
                                @else
                                    Por definir
                                @endif
                            </td>
                            <td>
                                <form style="display: flex; flex-wrap: wrap;" action="{{ route('profiles.updatePointsX', ['profile' => $profile->id]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group mr-2 mb-2">
                                        <input type="text"
                                            name="points_x1"
                                            class="form-control @error('points_x1') is-invalid @enderror"
                                            id="points_x1"
                                            placeholder="Puntos"
                                            value="{{ $profile->points_x1 }}"
                                        />
                                        @error('points_x1')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{$message}}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="submit" class="btn btn-primary" value="Actualizar puntos">
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
