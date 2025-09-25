@extends('layouts.app')

@section('content')
<div class="py-4">
    <h2 class="text-center mb-4 text-white">Gestión de Usuarios</h2>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs m-3" id="userTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="roles-tab" data-bs-toggle="tab" href="#roles" role="tab">Roles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="subs-tab" data-bs-toggle="tab" href="#subs" role="tab">Suscripciones</a>
        </li>
    </ul>

    <div class="tab-content" id="userTabsContent">

        <!-- Pestaña Roles -->
        <div class="tab-pane fade show active" id="roles" role="tabpanel" aria-labelledby="roles-tab">
            <!-- Asignar roles -->
            <div class="col-md-10 mx-auto bg-white p-3 mb-4" style="background-color:transparent !important">
                <h4 class="text-white">Asignar roles a un usuario existente</h4>
                <form action="{{ route('profiles.updateRoles', ['user' => 0]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label class="text-white" for="user_id">Seleccionar usuario</label>
                        <select id="user_id" name="user_id" class="form-control">
                            <option value="">Selecciona un usuario</option>
                            @foreach($allUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_admin" value="1">
                            <label class="form-check-label text-white">Admin</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_jury" value="1">
                            <label class="form-check-label text-white">Jury</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_referee" value="1">
                            <label class="form-check-label text-white">Referee</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_editor" value="1">
                            <label class="form-check-label text-white">Editor</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-2">Asignar Roles</button>
                </form>
            </div>

            <!-- Tabla roles -->
            <div class="col-md-12 mx-auto bg-white p-3" style="background-color:transparent !important">
                <div class="table-responsive">
                    <table class="table" style="color: white !important">
                        <thead class="bg-primary text-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Región</th>
                                <th>Roles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($profiles as $profile)
                                <tr>
                                    <td>{{ $profile->user->name }}</td>
                                    <td>{{ $profile->user->email }}</td>
                                    <td>{{ $profile->region->name ?? 'Por definir' }}</td>
                                    <td>
                                        @php
                                            $roles = [];
                                            if($profile->user->is_admin) $roles[] = 'Admin';
                                            if($profile->user->is_jury) $roles[] = 'Jury';
                                            if($profile->user->is_referee) $roles[] = 'Referee';
                                            if($profile->user->is_editor) $roles[] = 'Editor';
                                        @endphp
                                        {{ implode(', ', $roles) }}
                                    </td>
                                    <td>
                                        <form action="{{ route('profiles.updateRoles', ['user' => $profile->user->id]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group d-flex flex-wrap">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="is_admin" value="1" {{ $profile->user->is_admin ? 'checked' : '' }}>
                                                    <label class="form-check-label text-white">Admin</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="is_jury" value="1" {{ $profile->user->is_jury ? 'checked' : '' }}>
                                                    <label class="form-check-label text-white">Jury</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="is_referee" value="1" {{ $profile->user->is_referee ? 'checked' : '' }}>
                                                    <label class="form-check-label text-white">Referee</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="is_editor" value="1" {{ $profile->user->is_editor ? 'checked' : '' }}>
                                                    <label class="form-check-label text-white">Editor</label>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-primary mt-2">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pestaña Suscripciones -->
        <div class="tab-pane fade" id="subs" role="tabpanel" aria-labelledby="subs-tab">
            <div class="col-md-12 mx-auto bg-white p-3" style="background-color:transparent !important">
                <div class="table-responsive">
                    <table class="table" style="color: white !important">
                        <thead class="bg-primary text-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Periodo</th>
                                <th>Estado</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscriptions as $sub)
                                <tr>
                                    <td>{{ $sub->user->name }}</td>
                                    <td>{{ $sub->user->email }}</td>
                                    <td>{{ $sub->plan->name ?? '-' }}</td>
                                    <td>{{ ucfirst($sub->period) }}</td>
                                    <td>{{ ucfirst($sub->status) }}</td>
                                    <td>{{ $sub->started_at ? $sub->started_at->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $sub->ended_at ? $sub->ended_at->format('d/m/Y') : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#user_id').select2({
            placeholder: 'Selecciona un usuario',
            width: '100%'
        });
    });
</script>
@endsection
