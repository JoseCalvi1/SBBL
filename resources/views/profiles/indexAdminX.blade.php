@extends('layouts.app')

@section('content')
<div class="py-4">
    <h2 class="text-center mb-2 text-white">Usuarios Beyblade X</h2>

    <div class="col-md-10 mx-auto bg-white p-3" style="background-color:transparent !important">
        <form action="{{ route('profiles.updateAllPointsX') }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success mb-3">Actualizar Puntos</button>

            <div class="table-responsive">
                <table class="table" style="color: white !important">
                    <thead class="bg-primary text-light">
                        <tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Email</th>
                            <th scope="col">Región</th>
                            <th scope="col">Puntos</th>
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
                                    <input type="text"
                                        name="points_x1[{{ $profile->id }}]"
                                        class="form-control @error('points_x1.' . $profile->id) is-invalid @enderror"
                                        placeholder="Puntos"
                                        value="{{ old('points_x1.' . $profile->id, $profile->points_x1) }}"
                                    />
                                    @error('points_x1.' . $profile->id)
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection
